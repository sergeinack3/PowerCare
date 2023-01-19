/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Insurance = {
    insurance_type_form: null,
    medical_insurance_form: null,
    maternity_form: null,
    work_accident_form: null,
    free_medical_fees_form: null,

    initialize: function () {
        this.insurance_type_form = getForm('select_type_insurance');
        this.medical_insurance_form = getForm('save_medical_insurance');
        this.maternity_form = getForm('save_maternity_insurance');
        this.work_accident_form = getForm('save_work_accident_insurance');
        this.free_medical_fees_form = getForm('save_fmf_insurance');

        this.switchType(this.insurance_type_form.elements['nature_type'].value, true)
    },

    /**
     * Switch insurance type and display the right form
     *
     * @param {int} type
     */
    switchType: function (type, initialization) {
        this.hideMessageElement();
        $$('div#save_insurance_form table:not(.keep)').invoke('hide');
        $$('table#table-form-' + type).invoke('show');

        switch (type) {
            case '0':
                /* Medical insurance */
                if (!initialization) {
                    this.saveInsurance(this.medical_insurance_form);
                }
                break;
            case '1':
                /* Maternity */
                if (!initialization) {
                    $V(this.maternity_form.elements['date'], '', false);
                    $V(this.maternity_form.elements['date_da'], '', false);
                    this.maternity_form.elements['date'].dispatchEvent(new Event('keyup'));
                }
                break;
            case '2':
                /* Work accident */
                break;
            case '4':
                /* Free medical fees */
                break;
        }

        let work_accident_organism_field = $('save_work_accident_insurance_organism');
        if (type == '2') {
            Jfse.setInputNotNull(work_accident_organism_field);
        } else {
            Jfse.setInputNullable(work_accident_organism_field);
        }
    },

    /**
     * Send the form to jfse
     *
     * @param {HTMLFormElement} form
     */
    saveInsurance: async function (form) {
        if (checkForm(form)) {
            let invoice_id = $V(form.elements['invoice_id']);
            let response = await Jfse.requestJson('insurance/invoice/' + form.nature_route.value + '/store', {form: form}, {});

            if (response.success) {
                Jfse.notifySuccessMessage(response.message);
                Invoicing.reload(Invoicing.getConsultationId(invoice_id), invoice_id);
            } else if (response.error) {
                Jfse.displayMessage(response.message, 'error', this.getMessageElement());
            } else if (response.messages) {
                Jfse.displayMessages(response.messages, this.getMessageElement());
            }
        }
    },

    onSelectWorkAccidentAccidentOrganism: function (input) {
        let form = input.form;
        switch ($V(input)) {
            case 'identical_amo':
                $V(form.elements['is_organisation_identical_amo'], '1');
                $V(form.elements['organisation_vital'], '-1');
                $('work_accident_organisation_support_container').hide();
                $V(form.elements['organisation_support'], '');
                Jfse.setInputNullable(form.elements['organisation_support']);
                this.saveWorkAccidentInsurance();
                break;
            case 'other_organism':
                $V(form.elements['is_organisation_identical_amo'], '0');
                $V(form.elements['organisation_vital'], '-1');
                $('work_accident_organisation_support_container').show();
                $V(form.elements['organisation_support'], '');
                Jfse.setInputNotNull(form.elements['organisation_support']);
                this.saveWorkAccidentInsurance();
                break;
                break;
            case 'unknown_organism':
                $V(form.elements['is_organisation_identical_amo'], '0');
                $V(form.elements['organisation_vital'], '-1');
                $('work_accident_organisation_support_container').hide();
                $V(form.elements['organisation_support'], '');
                Jfse.setInputNullable(form.elements['organisation_support']);
                this.saveWorkAccidentInsurance();
                break;
            default:
                $V(form.elements['is_organisation_identical_amo'], '');
                $V(form.elements['organisation_vital'], $V(input));
                $('work_accident_organisation_support_container').hide();
                $V(form.elements['organisation_support'], '');
                Jfse.setInputNullable(form.elements['organisation_support']);
        }
    },

    onChangeOrganismSupport: function (input) {
        if (input.value != '' && input.value.substr(0, 2) == '06') {
            $('work_accident_shipowner_support_container').show();
            $('work_accident_amount_apias_container').hide();
        } else if (input.value != '' && input.value.substr(0, 2) == '08') {
            $('work_accident_amount_apias_container').show();
            $('work_accident_shipowner_support_container').hide();
        } else {
            $('work_accident_shipowner_support_container').hide();
            $('work_accident_amount_apias_container').hide();
        }

        if (input.value != '') {
            this.saveWorkAccidentInsurance();
        }
    },

    onChangeAccidentNumber: function (input) {
        const number = input.value;
        if (number.length == 8 || number.length == 9) {
            let year = '20' + number.substr(0, 2);
            if (parseInt(number.substr(0, 2)) > 90) {
                year = '19' + number.substr(0, 2);
            }

            const month = number.substr(2, 2);
            const day = number.substr(4, 2);

            $V(this.work_accident_form.elements['date_da'], day + '/' + month + '/' + year);
            $V(this.work_accident_form.elements['date'], year + '-' + month + '-' + day);
        }

        this.saveWorkAccidentInsurance();
    },

    saveWorkAccidentInsurance: function () {
        const form = this.work_accident_form;

        if (
            $V(form.elements['date']) !== ''
            && (
                ($V(form.elements['organism']) !== 'other_organism' && $V(form.elements['organism']) !== '')
                || ($V(form.elements['organism']) === 'other_organism' && $V(form.elements['organisation_support']) != '')
            )
        ) {
            this.saveInsurance(form);
        }
    },

    getMessageElement: function () {
        return $('save_insurance_message');
    },

    hideMessageElement: function () {
        this.getMessageElement().hide();
    }
};
