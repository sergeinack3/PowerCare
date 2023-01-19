/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * ## How to use this object
 *
 * If you need access to the vital card, use the "read" function and use or add an "action" to the function.
 * This way, the cps code will be asked and the beneficiary will be asked (if more than one available) automatically
 *
 * Every action is done in the _doAction function where you can then call an external function.
 * Please avoid putting logic in there.
 */
VitalCard = {
    /**
     * Start any action here.
     *
     * This will read the current vital card and do any given action:
     * - 'search': fills out a form (@see VitalCard.search)
     * - 'edit': (@see VitalCard.editPatient)
     *   - New patient: opens the edit patient page with the form filled out (not saved)
     *   - Edit patient: opens a modal that show differences between both identities
     * - 'confirmed': when the comparison is validated by the user, opens the edit page with the form filled out
     * - 'storeIdentity': updates the patient's identity
     * - 'silentStore': updates a patient's insurance information
     * - 'load': returns a json with the patient's information
     *
     * If there are several beneficiaries, a modal will popup to ask on which beneficiary should be done the action
     *
     * @param {string} action
     * @param {int|null} patient_id
     * @returns {Promise<void>}
     */
    read: async(action, patient_id = null, invoice_context, consultation_id) => {
        let params = {};

        if (invoice_context) {
            params.invoice_context = 1;
        }
        const data = await Jfse.requestJson('vitalCard/beneficiaries', params, {});

        if (invoice_context && consultation_id) {
            data.consultation_id = consultation_id;
        }

        VitalCard.handleReadData(data, action, patient_id);
    },

    handleReadData: async(data, action, patient_id = null) => {
        if (data.beneficiaries.length === 0) {
            Jfse.displayErrorMessageModal($T('VitalCardController-No beneficiaries'));
            return;
        }

        if (data.beneficiaries.length > 1) {
            let parameters = {
                cps_absent: data.cps_absent,
                nir:        data.nir,
                patient_id: patient_id,
                action:     action,
                apcv:       data.apcv ? 1 : 0,
                consultation_id: data.consultation_id
            };

            await Jfse.displayViewModal(
                'vitalCard/beneficiaries/show',
                null,
                null,
                parameters,
                {title: $T('VitalCardController-Show beneficiaries')}
            );
        } else {
            params = {
                nir:        data.nir,
                first_name: data.beneficiaries[0].patient.first_name,
                last_name:  data.beneficiaries[0].patient.last_name,
                birth_date: data.beneficiaries[0].patient.birth_date,
                birth_rank: data.beneficiaries[0].patient.birth_rank,
                quality:    data.beneficiaries[0].quality,
                patient_id: patient_id,
                apcv:       data.apcv ? 1 : 0,
                consultation_id: data.consultation_id
            };

            await Jfse.requestJson('vitalCard/beneficiaries/select', params, {});

            VitalCard._doAction(action, params);
        }
    },

    /**
     * Call different functions using the name of the action
     * Do not put logic here
     *
     * @param {string} action - (@see VitalCard.read() for the available actions)
     * @param {Object} data - data needed to do the action
     * @private
     */
    _doAction: async(action, data) => {
        switch (action) {
            case 'search':
                VitalCard.search(data);
                break;
            case 'edit':
                if (data.nir) {
                    VitalCard._confirmEdit(data);
                } else {
                    Patient.edit(data.patient_id, 1);
                }
                break;
            case 'confirmed':
                // Used in _confirmEdit
                await VitalCard.updatePatientFromVitalCard(data);
                break;
            case 'confirmedIdentity':
                // Used in _confirmEdit
                await VitalCard.confirmIdentity(data);
                break;
            case 'confirmedIdentityFse':
                // Used in _confirmEdit
                await VitalCard.confirmIdentityFse(data);
                break;
            case 'storeIdentity':
                await VitalCard.storeIdentity(data);
                break;
            case 'storeIdentityAndCreateFse':
                await VitalCard.storeIdentity(data, true);
                break;
            case 'createFse':
                await Invoicing.createNewInvoice(data.consultation_id, null, null, data.nir, data.apcv);
                break;
            default:
        }
    },

    /**
     * Selects a beneficiary to do the action (@see VitalCard.read() for the available actions)
     *
     * @param {string} action
     * @param {HTMLButtonElement} button - contains the information
     * @returns {Promise<void>}
     */
    selectBeneficiary: async(action, button) => {
        Control.Modal.close();

        const data = button.dataset;
        const params = {
            nir:        data.nir,
            first_name: data.firstName,
            last_name:  data.lastName,
            birth_date: data.birthDate,
            birth_rank: data.birthRank,
            quality:    data.quality,
            patient_id: data.patientId,
            apcv:       data.apcv === '1' ? 1 : 0,
            consultation_id:       parseInt(data.consultation_id),
        };

        await Jfse.requestJson('vitalCard/beneficiaries/select', params, {});

        VitalCard._doAction(action, params);
    },

    /**
     * Search action
     * This function fills out a form named 'search'. Often used for the patient research (module dpPatients)
     *
     * @param {Object} beneficiary - search data
     */
    search: (beneficiary) => {
        const form = getForm('find');
        $V(form.prenom, beneficiary.first_name);
        $V(form.nom, beneficiary.last_name);

        $V(form.Date_Year, beneficiary.birth_date.substr(0, 4));

        const birth_month = beneficiary.birth_date.substr(5, 2);
        if (birth_month <= 12) {
            $V(form.Date_Month, birth_month);
        }

        const birth_day = beneficiary.birth_date.substr(8, 2);
        if (birth_day <= 31) {
            $V(form.Date_Day, birth_day);
        }
        $V(form.useVitale, 1);

        form.onsubmit();
    },

    /**
     * Confirms the edit of the patient (@see VitalCard.editPatient())
     *
     * @param {Object} data
     * @param {string} callbackAction
     * @private
     */
    _confirmEdit: async(data, callbackAction = 'confirmed') => {
        data.action = callbackAction;

        if (!data.patient_id || data.patient_id === "") {
            VitalCard._doAction(data.action, data);
        }

        let compare = await Jfse.requestJson('vitalCard/beneficiaries/identity/confirm', data, {});
        if (compare.must_confirm) {
            await Jfse.displayViewModal('vitalCard/beneficiaries/store/confirm', null, null, data, {title: $T('VitalCardService-title-confirm_patient')});
        } else {
            await VitalCard._doAction(data.action, data);
        }
    },

    /**
     * Try to store the identity from a set of data which must be confirmed if the patient
     * and the new info are different
     * @param {Object} data
     * @return {Promise<void>}
     */
    storeIdentity: async(data, fse) => {
        const store = await Jfse.requestJson('vitalCard/beneficiaries/identity/confirm', data, {});

        if (store.must_confirm) {
            let action = 'confirmedIdentity';
            if (fse) {
                action = 'confirmedIdentityFse';
            }
            await VitalCard._confirmEdit(data, action);
        } else {
            if (fse) {
                await VitalCard.confirmIdentityFse(data);
            } else {
                await VitalCard.confirmIdentity(data);
            }
        }
    },

    /**
     * Called when the identity is trying to be updated but a confirmation must be made
     * Store the identity then update the patient's info
     *
     * @param data
     * @return {Promise<void>}
     */
    confirmIdentity: async(data) => {
        await Jfse.requestJson('vitalCard/beneficiaries/identity/store', data, {});
        VitalCard.displayPatientFse(data.patient_id, data.apcv);
    },

    /**
     * Called when the identity is trying to be updated but a confirmation must be made
     * Store the identity then update the patient's info
     *
     * @param data
     * @return {Promise<void>}
     */
    confirmIdentityFse: async(data) => {
        await Jfse.requestJson('vitalCard/beneficiaries/identity/store', data, {});
        await Invoicing.createNewInvoice(data.consultation_id, null, null, data.nir, data.apcv === '1' ? 1 : 0);
    },

    /**
     * Updates the patient's insurance information silently (i.e. the user wont see it)
     *
     * @param {Object} patient_id
     */
    silentStorePatient: (patient_id, apcv = false) => {
        Jfse.requestJson('vitalCard/beneficiaries/store/silent', {patient_id: patient_id, apcv: apcv ? 1 : 0}, {});
    },

    /**
     * Unlink a patient from the vital card
     *
     * @param {int} element
     */
    unlink: async(element) => {
        await Jfse.displayView('vitalCard/unlink', 'systemMsg', {link_id: element.dataset.linkId}, {});
        $('vital_card_patient_info').hide();
    },

    /**
     * Display the patient and some infos in a tooltip in the FSE
     *
     * @param {int} patient_id
     * @param {boolean} update_insurance - update the patient's insurance information
     * @return {Promise<void>}
     */
    displayPatientFse: async(patient_id, update_insurance = false, apcv = false) => {
        const info = await Jfse.requestJson('vitalCard/beneficiaries/infos', {patient_id: patient_id, apcv: apcv ? 1 : 0}, {});

        if (info.first_name !== undefined) {
            if (update_insurance) {
                VitalCard.silentStorePatient(patient_id, apcv);
            }

            // $('vital_card_patient_info').hide();
            VitalCard._fillFseTooltip(info);
            if ($('unlink_patient')) {
                $('unlink_patient').dataset.linkId = info.link_id;
            }
        }
    },

    /**
     * Fill in the patient's info
     *
     * @param {Object} info
     * @private
     */
    _fillFseTooltip: (info) => {
        if ($('vital_card_infos') && $('vital_card_patient_info')) {
            $$('.patient-name')[0].innerHTML = info.first_name + ' ' + info.last_name;
            $$('#vital_card_infos .nir')[0].innerHTML = info.nir;
            $$('#vital_card_infos .acs')[0].innerHTML = info.acs;
            $$('#vital_card_infos .regime')[0].innerHTML = info.regime;
            $$('#vital_card_infos .open_rights')[0].innerHTML = (info.open_amo_rights) ? $T('jfse-common-Open|pl') : $T('jfse-common-Closed|pl');

            $('vital_card_patient_info').setStyle({'display': 'inline'});
        }
    },

    updatePatientFromVitalCard: async(data) => {
        const beneficiary = await Jfse.requestJson('vitalCard/beneficiary/get/json', data, {});
        VitalCard.updatePatientForm(beneficiary);
    },

    /**
     * Updates the patient's edit form with the data fetched from Jfse
     * @param beneficiary
     */
    updatePatientForm: (beneficiary) => {
        const form = VitalCard.getEditPatientForm();
        let nir = '';
        /* Patient identity */
        if (beneficiary.patient) {
            $V(form.elements['_force_manual_source'], '0');
            $V(form.elements['nom'], beneficiary.patient.last_name);
            $V(form.elements['prenom'], beneficiary.patient.first_name);
            $V(form.elements['nom_jeune_fille'], beneficiary.patient.birth_name);
            $V(form.elements['naissance'], DateFormat.parse(beneficiary.patient.birth_date, 'y-M-d').format('dd/MM/y'));
            $V(form.elements['rang_naissance'], beneficiary.patient.birth_rank);

            $V(form.elements['_vitale_lastname'], beneficiary.patient.last_name);
            $V(form.elements['_vitale_firstname'], beneficiary.patient.first_name);
            $V(form.elements['_vitale_birthdate'], beneficiary.patient.birth_date);
            $V(form.elements['_vitale_birthrank'], beneficiary.patient.birth_rank);

            if (beneficiary.patient.address) {
                $V(form.elements['adresse'], beneficiary.patient.address);
            }

            if (beneficiary.patient.zip_code) {
                $V(form.elements['cp'], beneficiary.patient.zip_code);
            }

            if (beneficiary.patient.city) {
                $V(form.elements['ville'], beneficiary.patient.city);
            }
        }

        /* Beneficiary data */
        $V(form.elements['qual_beneficiaire'], beneficiary.quality.padStart(2, '0'));
        $V(form.elements['_vitale_quality'], beneficiary.quality);
        if (beneficiary.certified_nir && beneficiary.certified_nir !== '') {
            nir = beneficiary.certified_nir + beneficiary.certified_nir_key;
            $V(form.elements['_vitale_nir_certifie'], beneficiary.certified_nir + beneficiary.certified_nir_key);
            const sex = beneficiary.certified_nir.charAt(0) === '2' ? 'f' : 'm';
            $V(form.elements['sexe'], sex);
        }

        /* Insured data */
        if (beneficiary.insured) {
            if (beneficiary.insured.last_name) {
                $V(form.elements['assure_nom'], beneficiary.insured.last_name);
            }
            if (beneficiary.insured.first_name) {
                $V(form.elements['assure_prenom'], beneficiary.insured.first_name);
            }
            if (beneficiary.insured.birth_name) {
                $V(form.elements['assure_nom_jeune_fille'], beneficiary.insured.birth_name);
            }

            if (beneficiary.insured.nir && beneficiary.insured.nir_key) {
                $V(form.elements['assure_matricule'], beneficiary.insured.nir + beneficiary.insured.nir_key);
                if (!nir) {
                    nir = beneficiary.insured.nir + beneficiary.insured.nir_key;
                }

                const sex = beneficiary.insured.nir.charAt(0) === '2' ? 'f' : 'm';
                $V(form.elements['assure_sexe_' + sex], sex);
            }

            if (beneficiary.insured.address) {
                $V(form.elements['assure_adresse'], beneficiary.insured.address);
                if (beneficiary.insured.address && !beneficiary.patient.address) {
                    $V(form.elements['adresse'], beneficiary.insured.address);
                }
            }

            if (beneficiary.insured.zip_code) {
                $V(form.elements['assure_cp'], beneficiary.insured.zip_code);
                if (beneficiary.insured.zip_code && !beneficiary.patient.zip_code) {
                    $V(form.elements['cp'], beneficiary.insured.zip_code);
                }
            }

            if (beneficiary.insured.city) {
                $V(form.elements['assure_ville'], beneficiary.insured.city);
                if (beneficiary.insured.city && !beneficiary.patient.city) {
                    $V(form.elements['ville'], beneficiary.insured.city);
                }
            }

            /* Invoicing data */
            $V(form.elements['_vitale_nir'], beneficiary.insured.nir + beneficiary.insured.nir_key);
            if (beneficiary.insured.regime_code) {
                $V(form.elements['code_regime'], beneficiary.insured.regime_code);
                $V(form.elements['_vitale_code_regime'], beneficiary.insured.regime_code);
            }
            if (beneficiary.insured.managing_fund) {
                $V(form.elements['caisse_gest'], beneficiary.insured.managing_fund);
                $V(form.elements['_vitale_code_caisse'], beneficiary.insured.managing_fund);
            }
            if (beneficiary.insured.managing_center) {
                $V(form.elements['centre_gest'], beneficiary.insured.managing_center);
                $V(form.elements['_vitale_code_centre'], beneficiary.insured.managing_center);
            }
            if (beneficiary.insured.managing_code) {
                $V(form.elements['code_gestion'], beneficiary.insured.managing_code);
                $V(form.elements['_vitale_code_gestion'], beneficiary.insured.managing_code);
            }
            if (beneficiary.insured.regime_label) {
                $V(form.elements['regime_sante'], beneficiary.insured.regime_label);
            }

            let smg = '0';
            if (
                beneficiary.insured.regime_code == '08' && beneficiary.insured.managing_fund == '835'
                && beneficiary.insured.managing_center == '0300'
            ) {
                smg = '1';
            }
            $V(form.elements['is_smg'], smg);
        }

        if (nir) {
            $V(form.elements['matricule'], nir);
        }

        if (beneficiary.amo_period_rights && beneficiary.amo_period_rights.begin_date) {
            $V(form.elements['deb_amo_da'], DateFormat.parse(beneficiary.amo_period_rights.begin_date, 'y-M-d').format('dd/MM/y'));
            $V(form.elements['deb_amo'], beneficiary.amo_period_rights.begin_date);
        }

        if (beneficiary.amo_period_rights && beneficiary.amo_period_rights.end_date) {
            $V(form.elements['fin_amo_da'], DateFormat.parse(beneficiary.amo_period_rights.end_date, 'y-M-d').format('dd/MM/y'));
            $V(form.elements['fin_amo'], beneficiary.amo_period_rights.end_date);
        }

        /* ALD */
        let ald = '0';
        if (beneficiary.coverage_code_periods) {
            let coverage_code_period = null;
            if (beneficiary.coverage_code_periods.length === 1) {
                coverage_code_period = beneficiary.coverage_code_periods.at(0);
            } else {
                const now = Date.now();
                beneficiary.coverage_code_periods.forEach(period => {
                    if (
                        (period.begin_date === '' || DateFormat.parse(period.begin_date, 'y-M-d') <= now)
                        && (period.end_date === '' || DateFormat.parse(period.end_date, 'y-M-d') >= now)
                    ) {
                        coverage_code_period = period;
                    }
                });
            }

            if (coverage_code_period) {
                ald = coverage_code_period.ald_code !== '0' ? '1' : '0';
            }
        }

        $V(form.elements['_bind_vitale'], 'jfse');
        $V(form.elements['ald'], ald);

        /* ACS */
        $V(form.elements['acs'], beneficiary.acs);
        if (beneficiary.acs) {
            $V(
                form.elements['acs_type'],
                (beneficiary.acs === '1' && beneficiary.acs_type) ? beneficiary.acs_type : 'none'
            );
        }

        /* C2S */
        let c2s = '0';
        if (beneficiary.health_insurance && beneficiary.health_insurance.is_c2s) {
            c2s = '1';
        }
        $V(form.elements['c2s'], c2s);
    },

    getEditPatientForm: () => {
        return getForm('editFrm');
    }
};
