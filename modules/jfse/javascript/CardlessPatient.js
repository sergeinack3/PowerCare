/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CardlessPatient = {
    consultation_id: null,
    patient_guid: null,
    patient_form: null,
    situation_code_form: null,
    securing_mode: null,

    initializeView: function (consultation_id, patient_guid, securing_mode) {
        this.consultation_id = consultation_id;
        this.patient_guid = patient_guid;
        this.securing_mode = securing_mode;
        this.patient_form = getForm('edit-' + patient_guid);
        this.situation_code_form = getForm('selectCodeSituation');
        if (this.patient_form) {
            this.autocompleteOrganisms();

            let regime_code_field = this.patient_form.elements['code_regime'];
            regime_code_field.observe('change', this.onChangeRegime.bind(this));
            this.setFieldNotNull(regime_code_field);
            this.setFieldNotNull(this.patient_form.elements['caisse_gest']);
            this.setFieldNotNull(this.patient_form.elements['centre_gest']);
            this.setFieldNotNull(this.patient_form.elements['code_gestion']);
        }
    },

    setFieldNotNull: function (input) {
        let label_class = $V(input) != '' ? 'notNullOK' : 'notNull';
        input.labels.forEach((label) => {label.classList.add(label_class);});
        input.observe('change', notNullOK)
            .observe('keyup', notNullOK)
            .observe('ui:change', notNullOK);
    },

    onChangeRegime: function (event) {
        $V(this.patient_form.elements['caisse_gest'], '');
        $V(this.patient_form.elements['center_gest'], '');
        $V(this.patient_form.elements['organism_label'], '');
        if ($V(this.patient_form.elements['code_regime']) != '') {
            this.refreshSituationCodes();
        }
    },

    autocompleteOrganisms: function () {
        Jfse.displayAutocomplete('invoicing/patient/autocompleteOrganisms', this.patient_form.elements['organism_label'], {}, null, {
            dropdown: true,
            updateElement: ((selected) => {
                $V(this.patient_form.elements['caisse_gest'], selected.get('fund_code'));
                $V(this.patient_form.elements['centre_gest'], selected.get('center_code'));
                $V(this.patient_form.elements['organism_label'], selected.get('label'));
            }).bind(this),
            callback: ((input, queryString) => {
                if ($V(this.patient_form.elements['code_regime'])) {
                    queryString = queryString + '&regime_code=' + $V(this.patient_form.elements['code_regime']);
                }

                return queryString;
            }).bind(this)
        });
    },

    refreshSituationCodes: function () {
        Jfse.displayView('invoicing/patient/situationCodesList', 'situation_code_container', {regime_code: $V(this.patient_form.elements['code_regime'])});
    },

    submit: function () {
        if (this.patient_form) {
            let code_caisse = $V(this.patient_form.elements['caisse_gest']);
            let code_centre = $V(this.patient_form.elements['centre_gest']);
            if ((code_caisse === '999' || code_centre === '9999')) {
                Modal.alert($T('CardlessPatient-error-invalid_fund_code'));
                return false;
            }

            onSubmitFormAjax(this.patient_form, (() => {
                Control.Modal.close();
                Invoicing.createNewInvoice(this.consultation_id, this.securing_mode, $V(this.situation_code_form.elements['situation_code']));
            }).bind(this));
        } else {
            Control.Modal.close();
            Invoicing.createNewInvoice(this.consultation_id, this.securing_mode, $V(this.situation_code_form.elements['situation_code']));
        }
    }
};
