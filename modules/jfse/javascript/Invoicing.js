/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Invoicing = {
    setThirdPartyPayment: (form) => {
        Jfse.displayView("invoicing/thirdParty/set", 'systemMsg', {form: form}, {});
    },

    reload: (consultation_id, invoice_id) => {
        Jfse.displayView('invoicing/invoice/view', 'jfse_invoice', {consultation_id: consultation_id, invoice_id: invoice_id});
    },

    reloadMessages: (invoice_id) => {
        Jfse.displayView('invoicing/invoice/messages', 'invoice_messages', {invoice_id: invoice_id});
    },

    toggleSecuringModeInput: (element) => {
        if (element.classList.contains('lock')) {
            element.classList.remove('lock');
            element.classList.add('unlock');
            element.next('select').enable();
        } else {
            element.classList.remove('unlock');
            element.classList.add('lock');
            element.next('select').disable();
        }
    },

    selectSecuringMode: (invoice_id, mode, situation_code) => {
        /* If the securing mode is degraded or sesam without vitale, a situation code for the patient must be selected */
        if ((mode == '1' || mode == '5') && !situation_code) {
            Jfse.displayViewModal('invoicing/situationCode/select', 600, 150, {
                invoice_id: invoice_id,
                securing_mode: mode
            }, {showClose: true, title: $T('CBeneficiary-title-select_situation_code')});
        } else {
            let parameters = {
                invoice_id:    invoice_id,
                securing_mode: mode
            };

            if (situation_code) {
                parameters.situation_code = situation_code;
            }

            Jfse.displayView('invoicing/invoice/selectSecuringMode', 'jfse_invoice', parameters);
        }
    },

    switchToApCv: function (invoice_id, mode, content) {
        Jfse.displayView('invoicing/invoice/setApCv', 'jfse_invoice', {
            invoice_id: invoice_id,
            mode: mode,
            qrcode_content: content
        });
    },

    validateInvoice: async(invoice_id, force) => {
        let parameters = {invoice_id: invoice_id};

        if (force) {
            parameters.force_validation = 1;
        }

        Invoicing.showWaitingMessage();

        const response = await Jfse.requestJson('invoicing/invoice/validate', parameters, {});

        Control.Modal.close();
        if (response.success) {
            if (response.html) {
                Jfse.displayContentModal(atob(response.html), response.title);
            } else {
                Reglement.reload();
            }
        } else {
            if (response.messages) {
                Jfse.displayMessagesModal(response.messages);
            } else {
                Jfse.displayErrorMessageModal(response.error);
            }
        }
    },

    createNewInvoice: async(consultation_id, securing_mode, situation_code, vitale_nir, apcv) => {
        let parameters = {
            consultation_id: consultation_id
        };

        if (securing_mode) {
            parameters.securing_mode = securing_mode;
        }

        if (situation_code) {
            parameters.situation_code = situation_code;
        }

        if (apcv) {
            parameters.apcv = 1;
        }

        if (vitale_nir) {
            parameters.vitale_nir = vitale_nir;
        }

        Jfse.displayView('invoicing/invoice/create', 'jfse_invoice', parameters);
    },

    createInvoiceCardlessMode: async(consultation_id, securing_mode) => {
        Jfse.displayViewModal('invoicing/patient/cardlessRequirements', 600, null, {consultation_id: consultation_id, securing_mode: securing_mode}, {
            title: $T('CJfseInvoice-action-set_patient_data.' + securing_mode)
        });
    },

    cancelInvoice: async(invoice_id) => {
        const response = await Jfse.requestJson('invoicing/invoice/cancel', {invoice_id: invoice_id}, {});

        if (response.success) {
            Invoicing.reload(Invoicing.getConsultationId(invoice_id));
        }
    },

    deleteInvoice: async(invoice_id) => {
        const response = await Jfse.requestJson('invoicing/invoice/delete', {invoice_id: invoice_id}, {});

        if (response.success) {
            Invoicing.reload(Invoicing.getConsultationId(invoice_id));
        }
    },

    editPrescription: (invoice_id) => {
        Jfse.displayViewModal('invoicing/prescription/edit', null, null, {invoice_id: invoice_id}, {
            title: $T('CJfseInvoiceView-title-edit_prescription'),
            onClose: Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id)});
    },

    editThirdPartyPayment: (invoice_id, third_party_amc) => {
        Jfse.displayViewModal('thirdPartyPayment/edit', 470, null, {invoice_id: invoice_id, selected_tp_amc: third_party_amc}, {
            title: $T('CJfseInvoiceView-title-edit_third_party_payment'),
            onClose: Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id)});
    },

    anonymize: async(invoice_id) => {
        const response = await Jfse.requestJson('invoicing/invoice/anonymize', {invoice_id: invoice_id}, {});

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseInvoiceView-msg-anonymized');
            Invoicing.reload(Invoicing.getConsultationId(invoice_id), invoice_id);
        } else {
            Jfse.notifyErrorMessage('CJfseUserView-error-anonymize');
        }
    },

    displayQuestions: () => {
        Modal.open($('questions-container'), {});
    },

    sendQuestionsAnswers: async(invoice_id) => {
        let parameters = {
            invoice_id: invoice_id,
            "questions_id[]": [],
            "answers[]": [],
            'natures[]': [],
        };
        let questions = $$('div.jfse-question');
        questions.each((question) => {
            parameters["questions_id[]"].push(
                $V(question.down('form').elements['question_id'])
            );
            parameters["answers[]"].push(
                $V(question.down('form').elements['answer'])
            );
            parameters["natures[]"].push(
                $V(question.down('form').elements['nature'])
            );
        });

        const response = await Jfse.requestJson('invoicing/questions/answer', parameters);

        Control.Modal.close();
        if (response.messages) {
            Jfse.displayMessagesModal(response.messages, Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id));
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error, Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id));
        } else {
            Invoicing.reload(Invoicing.getConsultationId(invoice_id), invoice_id);
        }

        return;
    },

    forceRule: async(invoice_id, rule_id, forcing_type) => {
        let parameters = {
            invoice_id: invoice_id,
            rule_id: rule_id
        }

        if (forcing_type) {
            parameters.forcing_type = forcing_type;
        }

        const response = await Jfse.requestJson('invoicing/rule/force', parameters, {});

        if (response.messages) {
            Jfse.displayMessagesModal(response.messages, Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id));
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error, Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id));
        } else {
            Invoicing.reload(Invoicing.getConsultationId(invoice_id), invoice_id);
        }
    },

    onCommonLawAccidentChange: (input) => {
        if ($V(input) == '1') {
            Jfse.setInputNotNull(input.form.elements['date']);
            $('date_common_law_accident-container').show();
        } else {
            Jfse.setInputNullable(input.form.elements['date']);
            $('date_common_law_accident-container').hide();
            $V(input.form.elements['date_da'], '');
            $V(input.form.elements['date'], '', false);
            Invoicing.saveCommonLawAccident(input.form);
        }
    },

    saveCommonLawAccident: async(form) => {
        if (checkForm(form)) {
            const response = await Jfse.requestJson('invoicing/common_law_accident/store', {form: form});

            let message_element = $('common_law_accident_messages_container');
            if (response.success) {
                Jfse.hideMessageElement(message_element);
                Jfse.notifySuccessMessage(response.message);
                Invoicing.reloadMessages($V(form.elements['invoice_id']));
            } else if (response.error) {
                Jfse.displayErrorMessage(response.error, message_element);
            } else if (response.messages) {
                Jfse.displayMessages(response.messages, message_element);
            }
        }
    },

    editProofAmo: (invoice_id) => {
        Jfse.displayViewModal('proofAMO/add', null, null, {invoice_id: invoice_id}, {
            title: $T('CJfseInvoiceView-proof_amo'),
            onClose: Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id)
        });
    },

    setInsuredParticipationAct: (invoice_id, act_index) => {
        let parameters = {
            invoice_id: invoice_id,
            act_index: act_index,
        };

        const form = getForm('InsuredParticipationInvoice-' + invoice_id);
        parameters.add_insured_participation = form.elements['add_insured_participation_' + act_index].checked ? '1' : '0';
        parameters.amo_amount_reduction = form.elements['amo_amount_reduction_' + act_index].checked ? '1' : '0';

        Jfse.requestJson('invoicing/invoice/pav', parameters, {});
    },

    setTreatmentType: async(form) => {
        const response = await Jfse.requestJson('invoicing/invoice/setTreatmentType', {
            invoice_id: $V(form.elements['invoice_id']),
            treatment_type: $V(form.elements['treatment_type']),
        }, {});

        if (response.success) {
            Jfse.notifySuccessMessage(response.message);
        } else if (response.error) {
            Jfse.notifyErrorMessage(response.error);
        }
    },

    displayChildrenConsultationAssistant: (invoice_id) => {
        Modal.open($('ChildrenConsultationAssistant-' + invoice_id), {
            title: $T('CJfseChildrenConsultationAssistant-title'),
            showClose: true,
            width: '250px',
            onClose: Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id)
        });
    },

    getChildrenConsultationAssistant: async(form) => {
        $('children_consultation_assistant_results').hide();
        const response = await Jfse.requestJson('invoicing/childrenConsultation/assistant', {form: form}, {});

        if (response.success) {
            Jfse.notifySuccessMessage(response.message);
            Control.Modal.close();
        } else {
            if (response.message) {
                let message = DOM.div({class: 'small-warning'}, $T(response.message));
                $('children_consultation_assistant_results').down('td').update(message);
                $('children_consultation_assistant_results').show();
            } else if (response.messages) {
                Jfse.displayMessagesModal(response.messages);
            } else {
                Jfse.displayErrorMessageModal(response.error);
            }
        }
    },

    enableLegacyInvoicing: async(consultation_id) => {
        const response = Jfse.requestJson('invoicing/legacyInvoicing/enable', {consultation_id: consultation_id}, {});

        if (response) {
            Reglement.reload();
        }
    },

    disableLegacyInvoicing: async(consultation_id) => {
        const response = Jfse.requestJson('invoicing/legacyInvoicing/disable', {consultation_id: consultation_id}, {});

        if (response) {
            Reglement.reload();
        }
    },

    dataGroup: {
        display: (invoice_id, type) => {
            Jfse.requestJson('history/dataGroups/view', {invoice_id: invoice_id, type: type});
        },

        displaySsv: (invoice_id) => {
            Invoicing.dataGroup.display(invoice_id, 0);
        },

        displayInputSts: (invoice_id) => {
            Invoicing.dataGroup.display(invoice_id, 1);
        },

        displayOutputSts: (invoice_id) => {
            Invoicing.dataGroup.display(invoice_id, 2);
        },

        displayB2Fse: (invoice_id) => {
            Invoicing.dataGroup.display(invoice_id, 3);
        },

        displayB2Dre: (invoice_id) => {
            Invoicing.dataGroup.display(invoice_id, 4);
        },
    },

    print: {
        cerfa: (invoice_id) => {
            Jfse.pop('print/cerfa', {invoice_id: invoice_id}, 1200, 800, 'print_fse');
        },

        cerfaCopy: (invoice_id) => {
            Jfse.pop('print/cerfaCopy', {invoice_id: invoice_id}, 1200, 800, 'print_fse');
        },

        checkUpReceipt: (invoice_id) => {
            Jfse.pop('print/checkUpReceipt', {invoice_id: invoice_id}, 1200, 800, 'print_fse');
        },

        dreCopy: (invoice_id) => {
            Jfse.pop('print/dreCopy', {invoice_id: invoice_id}, 1200, 800, 'print_fse');
        },

        invoice: (invoice_id) => {
            Jfse.pop('print/invoice', {invoice_id: invoice_id}, 1200, 800, 'print_fse');
        },

        receipt: (invoice_id) => {
            Jfse.pop('print/receipt', {invoice_id: invoice_id}, 1200, 800, 'print_fse');
        },
    },

    getConsultationId: (invoice_id) => {
        return $('CJfseInvoice-' + invoice_id + '-view').get('consultation_id');
    },

    showWaitingMessage: () => {
        let div = DOM.div({style: 'display: none;'}, DOM.div({class:'small-info', style: 'width: 100%;'}, $T('CJfseInvoiceView-message-validation_in_progress')));

        $(document.body).insert(div);
        Modal.open(div);
        WaitingMessage.cover(div);
    },

    closeCotation: function (form) {
        $V(form.elements['valide'], 1);
        return onSubmitFormAjax(form, Reglement.reload);
    },

    openCotation: function (form) {
        $V(form.elements['valide'], 0);
        return onSubmitFormAjax(form, Reglement.reload);
    },

    setCommonLawAccidentNotNull: function (form) {
        Jfse.setInputNotNull($('common_law_accident_0'));
        Jfse.setInputNotNull($('common_law_accident_1'));
    }
};
