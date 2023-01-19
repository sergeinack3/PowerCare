/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MaintenanceConfig = {

    /**
     * Modification des consentements
     */
    editConsentement() {
        new Url('patients', 'ajax_vw_consentement')
            .requestModal('30%', '30%');
    },

    /**
     * Affichage du nombre de patients qui seront impacté par le changement
     *
     * @param form
     */
    seeCountConsentement: function (form) {
        if (form.tag.value !== "" && form.allow_sms_notification.value !== "") {
            new Url('patients', 'ajax_count_consentement')
                .addParam("tag", form.tag.value)
                .addParam("consentement", form.allow_sms_notification.value)
                .requestUpdate('count_consentement', {
                    onComplete: function () {
                        MaintenanceConfig.clicButton($('submit'), false);
                    }
                })
        } else {
            MaintenanceConfig.clicButton($('submit'), true);
        }
    },

    /**
     * Change le consentement des patients
     *
     * @param form
     */
    saveConsentement: function (form) {
        new Url('patients', 'ajax_edit_consentement')
            .addParam("tag", form.tag.value)
            .addParam("consentement", form.allow_sms_notification.value)
            .requestUpdate('systemMsg', {
                onComplete: function () {
                    Control.Modal.close();
                }
            })
    },

    /**
     * Visibilité du bouton pour modifier les consentements
     *
     * @param button
     * @param value
     */
    clicButton: function (button, value) {
        $('submit').disabled = value;
    },

    /**
     * Correction des sources d'identité en doublon
     */
    correctSources: () => {
        new Url('patients', 'correctSources')
            .requestUpdate('source_correction_area');
    },

    ExpiredIdentityFiles: {
        initializeView: function () {
            Calendar.regField(this.getExpirationDateField());
        },

        refresh: function () {
            this.changePage(0);
        },

        changePage: function (start) {
            new Url('patients', 'listPatientsWithExpiredIdentityFiles')
                .addParam('expirationDate', this.getExpirationDate())
                .addParam('start', start)
                .requestUpdate(this.getElement());
        },

        /**
         * Delete the identity files for the selected patients, or all the patients if none is selected
         */
        deleteFilesForPatients: function () {
            let patients = [];

            let checkboxes = this.getElement().select('input[type="checkbox"][name="patients"]:checked');
            if (!checkboxes.length) {
                checkboxes = this.getElement().select('input[type="checkbox"][name="patients"]');
            }

            checkboxes.each((checkbox) => {
                patients.push(checkbox.get('patient_guid'));
            });

            Modal.confirm(
                $T('CSourceIdentite-msg-confirm_delete_expired_identity_files', patients.length),
                {onOK : this.deleteFiles.bind(this, patients)}
            );
        },

        /**
         * Delete the identity files for a single patient
         *
         * @param patient_guid
         */
        deleteFilesForPatient: function (patient_guid) {
            let patient_row = $(patient_guid);
            Modal.confirm(
                $T(
                    'CSourceIdentite-msg-confirm_delete_expired_identity_file',
                    patient_row.down('.last_name').innerText + ' ' + patient_row.down('.first_name').innerText
                ),
                {onOK: this.deleteFiles.bind(this, [patient_guid])}
            );
        },

        deleteFiles: function (patients) {
            new Url('patients', 'deleteExpiredIdentityFiles')
                .addParam('patient_guids[]', patients, true)
                .requestUpdate('systemMsg', {
                    method: 'post',
                    getParameters: {m: 'patients', a: 'deleteExpiredIdentityFiles'},
                    onComplete: this.refresh.bind(this)
                });
        },

        toggleCheckboxes: function () {
            this.getElement().select('input[type="checkbox"][name="patients"]').each((checkbox) => {
                checkbox.checked = !checkbox.checked;
            });
        },

        getElement: function () {
            return $('CSourceIdentite-expired_files');
        },

        getExpirationDateField: function () {
            let field = null;
            let form = getForm('setExpirationDate');
            if (form) {
                field = form.elements['expirationDate'];
            }

            return field;
        },

        getExpirationDate: function () {
            let value = null;
            if (this.getExpirationDateField()) {
                value = $V(this.getExpirationDateField());
            }

            return value;
        }
    }
};
