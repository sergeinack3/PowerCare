/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ThirdPartyPayment = {
    onSelectPaperModeSituation: (input) => {
        switch ($V(input)) {
            case 'attack_victim':
                $V(input.form.elements['third_party_amo'], '1');
                $V(input.form.elements['third_party_amc'], '0');
                $V(input.form.elements['attack_victim'], '1');
                $('health_insurance-container').hide();
                $('amc-container').hide();
                if (input.form.elements['use_vital_card_additional_health_insurance']) {
                    input.form.elements['use_vital_card_additional_health_insurance'].checked = false;
                }
                Control.Modal.position();
                break;
            case 'c2s':
                $V(input.form.elements['third_party_amo'], '1');
                $V(input.form.elements['attack_victim'], '0');
                $('health_insurance-container').hide();
                $('amc-container').hide();
                if (input.form.elements['use_vital_card_additional_health_insurance']) {
                    input.form.elements['use_vital_card_additional_health_insurance'].checked = false;
                }
                Control.Modal.position();
                break;
            case 'mutuelle':
                $V(input.form.elements['third_party_amo'], '1');
                $V(input.form.elements['attack_victim'], '0');
                $('health_insurance-container').show();
                $('amc-container').hide();
                if (input.form.elements['use_vital_card_additional_health_insurance']) {
                    input.form.elements['use_vital_card_additional_health_insurance'].checked = false;
                }
                Control.Modal.position();
                break;
            case 'amc':
                $V(input.form.elements['third_party_amo'], '1');
                $V(input.form.elements['attack_victim'], '0');
                $('amc-container').show();
                $('health_insurance-container').hide();
                if (input.form.elements['use_vital_card_additional_health_insurance']) {
                    input.form.elements['use_vital_card_additional_health_insurance'].checked = false;
                }
                Control.Modal.position();
                break;
            default:
                $('health_insurance-container').hide();
                $('amc-container').hide();
                Control.Modal.position();
        }
    },

    validateThirdPartyPayment: async(form) => {
        let response = {success: false};
        const invoice_id = $V(form.elements['invoice_id']);
        if ($V(form.elements['third_party_paper_document'])) {
            switch ($V(form.elements['third_party_paper_document'])) {
                case 'attack_victim':
                    response = await ThirdPartyPayment.setAttackVictim(form);
                    break;
                case 'c2s':
                    response = await ThirdPartyPayment.setC2S(form);
                    break;
                case 'mutuelle':
                    response = await ThirdPartyPayment.setHealthInsurance(form);
                    break;
                case 'amc':
                    response = await ThirdPartyPayment.setAdditionalHealthInsurance(form);
                    break;
            }
        } else if (form.elements['use_vital_card_additional_health_insurance'] && form.elements['use_vital_card_additional_health_insurance'].checked) {
            response = await ThirdPartyPayment.setAdditionalHealthInsuranceFromVitalCard(form);
        } else if (form.elements['use_vital_card_health_insurance'] && form.elements['use_vital_card_health_insurance'].checked) {
            response = await ThirdPartyPayment.setHealthInsuranceFromVitalCard(form);
        } else if ($V(form.elements['amo_service_code'])) {
            response = await ThirdPartyPayment.setAmoService(form);
        }

        if (response.success) {
            if (response.html) {
                Jfse.displayContentModal(atob(response.html), response.title);
            } else {
                Jfse.notifySuccessMessage(response.message);
                Control.Modal.close();
            }
        } else if (response.messages) {
            Jfse.displayMessagesModal(response.messages);
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        }
    },

    setC2S: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/c2s/set', {invoice_id: $V(form.elements['invoice_id'])}, {});
    },

    setAttackVictim: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/attackVictim/set', {invoice_id: $V(form.elements['invoice_id'])}, {});
    },

    setHealthInsurance: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/healthInsurance/set', {form: form}, {});
    },

    setHealthInsuranceFromVitalCard: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/healthInsurance/vitalCard', {form: form}, {});
    },

    setAdditionalHealthInsurance: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/additionalHealthInsurance/set', {form: form}, {});
    },

    setAdditionalHealthInsuranceFromVitalCard: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/additionalHealthInsurance/vitalCard', {form: form}, {});
    },

    selectConvention: async(form) => {
        Control.Modal.close();

        const response = await Jfse.requestJson('thirdPartyPayment/convention/select', {form: form}, {});

        if (response.success) {
            if (response.html) {
                Jfse.displayContentModal(atob(response.html), response.title, Control.Modal.close.curry());
            } else {
                Jfse.notifySuccessMessage(response.message);
                Control.Modal.close();
            }
        } else if (response.messages) {
            Jfse.displayMessagesModal(response.messages);
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        }
    },

    onChangeFormula: (input) => {
        const formula_number = $V(input);
        const parameter_container = $('formula_' + formula_number + '_parameters');

        $$('tbody.formulas_parameter_container').invoke('hide');
        if (parameter_container) {
            parameter_container.show();
        }
    },

    selectFormula: async(form) => {
        let parameters = {
            invoice_id: $V(form.elements['invoice_id']),
            formula_number: $V(form.elements['formula']),
            'parameters[]': []
        }

        const formula_parameters_container = $('formula_' + parameters.formula_number + '_parameters');
        if (formula_parameters_container) {
            formula_parameters_container.select('input').each((input) => {
                parameters['parameters[]'].push(Object.toJSON({
                    number: input.get('number'),
                    type: input.get('type'),
                    label: input.get('label'),
                    value: $V(input)
                }));
            });
        }

        const response = await Jfse.requestJson('thirdPartyPayment/formula/select', parameters, {});

        if (response.success) {
            Control.Modal.close();
            Jfse.notifySuccessMessage('CComplementaryHealthInsurance-msg-modified');
        } else if (response.messages) {
            Jfse.displayMessagesModal(response.messages);
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        }
    },

    toggleAmoService: () => {
        $('amo_service-container').show();
    },

    viewConventionsSelection: async(invoice_id) => {
        Control.Modal.close();
        await Jfse.displayViewModal('thirdPartyPayment/conventions/view', null, null, {invoice_id: invoice_id}, {showClose: true, title: $T('CComplementaryHealthInsurance-action-convention_selection')});
    },

    viewFormulasSelection: async(invoice_id) => {
        Control.Modal.close();
        await Jfse.displayViewModal('thirdPartyPayment/formulas/view', null, null, {invoice_id: invoice_id}, {showClose: true, title: $T('CComplementaryHealthInsurance-action-select_formula')});
    },

    onChangeAmoService: (input) => {
        if ($V(input) != '00') {
            input.form.elements['amo_service_begin_date'].enable();
            input.form.elements['amo_service_begin_date_da'].enable();
            input.form.elements['amo_service_end_date'].enable();
            input.form.elements['amo_service_end_date_da'].enable();
            $V(input.form.elements['third_party_paper_document'], '');
            if (input.form.elements['use_vital_card_additional_health_insurance']) {
                input.form.elements['use_vital_card_additional_health_insurance'].checked = false;
            }
        } else {
            input.form.elements['amo_service_begin_date'].disable();
            input.form.elements['amo_service_begin_date_da'].disable();
            input.form.elements['amo_service_end_date'].disable();
            input.form.elements['amo_service_end_date_da'].disable();
        }
    },

    onChangeHealthInsuranceFromVitalCard: (input) => {
        if (input.checked) {
            $V(input.form.elements['third_party_paper_document'], '');
            $V(input.form.elements['amo_service_code'], '00');
            $('amo_service-container').hide();
        }
    },

    setAmoService: async(form) => {
        return await Jfse.requestJson('thirdPartyPayment/amoService/set', {form: form}, {});
    },

    onChangeAmcNumber: (field) => {
        const container = field.up('tbody');
        const inputs = $$('tbody#' + container.id + ' input');
        inputs.each((input) => {
            if (input.name != field.name) {
                $V(input, '');
            }
        });
        const selects = $$('tbody#' + container.id + ' select');
        selects.each((select) => {
            if (select.name != field.name) {
                $V(select, '');
            }
        });
    }
};
