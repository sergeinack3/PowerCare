/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MedicalActs = {
    applyConsultationTarif: (form, invoice_id) => {
        return onSubmitFormAjax(form, Invoicing.reload.bind(Invoicing, $V(form.elements['consultation_id']), invoice_id));
    },

    setConsultationExecutionDate: (form) => {
        return onSubmitFormAjax(form);
    },

    onChangeTaxesAmount: function (form, invoice_id) {
        const excluding_taxes_amount = $V(form.elements['secteur3']);

        if (excluding_taxes_amount != 0 && excluding_taxes_amount != '') {
            return onSubmitFormAjax(form, Invoicing.reload.bind(Invoicing, $V(form.elements['consultation_id']), invoice_id));
        } else {
            return onSubmitFormAjax(form);
        }
    },

    editActs: (consultation_id, invoice_id) => {
        new Url("cabinet", "ajax_vw_actes")
          .addParam("consult_id", consultation_id)
          .requestModal('80%', 600, {onClose: Invoicing.reload.bind(Invoicing, consultation_id, invoice_id)});
    },

    linkAct: async(invoice_id, act_class, act_id) => {
        const response = await Jfse.requestJson('medicalActs/link', {
            invoice_id: invoice_id, act_class: act_class, act_id: act_id
        });

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseAct-msg-act_linked');
        }
        Invoicing.reload(response.consultation_id, response.invoice_id);
    },

    unlinkAct: async(jfse_act_id) => {
        const response = await Jfse.requestJson('medicalActs/unlink', {
            act_id: jfse_act_id
        });

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseAct-msg-act_unlinked');
        }
        Invoicing.reload(response.consultation_id, response.invoice_id);
    },

    editAct: (invoice_id, act_guid) => {
        Jfse.displayViewModal('medicalActs/edit', 700, null, {act_guid: act_guid}, {
            title: $T('CJfseActView-title-edit'),
            onClose: Invoicing.reload.bind(Invoicing, Invoicing.getConsultationId(invoice_id), invoice_id)
        });
    },

    initializeEditView: (form, execution_date_min) => {
        new Url('mediusers', 'ajax_users_autocomplete')
            .addParam('edit', '1')
            .addParam('prof_sante', '1')
            .addParam('input_field', '_executant_view')
            .autoComplete(form.elements['_executant_view'], null, {
                minChars: 0,
                method: 'get',
                select: 'view',
                dropdown: true,
                afterUpdateElement: ((form, field, selected) => {
                    $V(form._executant_view, selected.down('.view').innerHTML);
                    $V(form.executant_id, selected.getAttribute('id').split('-')[2]);
                }).curry(form)
            });

        let date_limits = {};
        if (execution_date_min) {
            date_limits.limit = {start: execution_date_min};
        }
        Calendar.regField(form.elements['execution'], date_limits);
    },

    toggleDateDEP: (element) => {
        if (element.value == 1) {
            $('accord_prealable-details-row').show();
        } else {
            $('accord_prealable-details-row').hide();
        }
    },

    checkDEP: (form) => {
        let element = $('info_dep');

        if (element != null) {
            if ($V(form.accord_prealable) == '1' && $V(form.date_demande_accord) && $V(form.reponse_accord)) {
                element.setStyle({color: '#197837'});
            } else {
                element.setStyle({color: '#ffa30c'});
            }
        }
    },

    toggleView: (element, view) => {
        $(view + '-container').toggle();

        if (element.down('i').classList.contains('fa-chevron-right')) {
            element.down('i').classList.remove('fa-chevron-right');
            element.down('i').classList.add('fa-chevron-down');
        } else {
            element.down('i').classList.remove('fa-chevron-down');
            element.down('i').classList.add('fa-chevron-right');
        }
        Control.Modal.position();
    },

    store: async(act_guid) => {
        const form = getForm('edit' + act_guid);
        onSubmitFormAjax(form, (async(act_guid) => {
            const form_amo_forcing = getForm('edit' + act_guid + '-amount_AMO');
            const form_amc_forcing = getForm('edit' + act_guid + '-amount_AMC');
            const form_formula = getForm('edit' + act_guid + '-AMC_Formula');

            let parameters = {
                act_guid: act_guid,
                amo_forcing_choice : $V(form_amo_forcing.elements['amount_amo_choice']),
                amo_forcing_computed_amount: $V(form_amo_forcing.elements['amount_amo_computed'])
            };

            if (parameters.amo_forcing_choice == 1) {
                parameters.amo_forcing_modified_amount = $V(form_amo_forcing.elements['amount_amo_modified']);
            }

            if (form_amc_forcing) {
                parameters.amc_forcing_choice = $V(form_amc_forcing.elements['amount_amc_choice']);
                parameters.amc_forcing_computed_amount = $V(form_amc_forcing.elements['amount_amc_computed'])

                if (parameters.amc_forcing_choice == 1) {
                    parameters.amc_forcing_modified_amount = $V(form_amc_forcing.elements['amount_amc_modified']);
                } else if (parameters.amc_forcing_choice == 2) {
                    parameters.amc_forcing_modified_amount = $V(form_amc_forcing.elements['amount_amc_global']);
                }
            }

            if (form_formula) {
                parameters.formula_number = $V(form_formula.elements['formula']);

                let parameters_inputs = $$('form[name="edit' + act_guid + '-AMC_Formula"] tbody#formula_' + parameters.formula_number + '_parameters input');
                if (parameters_inputs && parameters_inputs.length) {
                    parameters['formula_parameters[]'] = [];
                    parameters_inputs.each((input) => {
                        parameters['formula_parameters[]'].push(Object.toJSON({
                            number: input.get('number'),
                            type: input.get('type'),
                            label: input.get('label'),
                            value: $V(input)
                        }));
                    });
                }
            }

            const response = await Jfse.requestJson('medicalActs/store', parameters, {});

            if (response.success) {
                Control.Modal.close();
                Jfse.notifySuccessMessage('CJfeActView-msg-modified');
            } else if (response.messages) {
                Jfse.displayMessagesModal(response.messages);
            } else if (response.error) {
                Jfse.displayErrorMessageModal(response.error);
            }
        }).curry(act_guid));
    },

    delete: (act_guid) => {
        const form = getForm('edit' + act_guid);
        $V(form.elements['del'], 1);
        $V(form.elements['_ignore_eai_handlers'], 0);
        onSubmitFormAjax(form, Control.Modal.close.curry());
    },

    NgapAct: {
        changeTauxAbattement: (input) => {
            if ($V(input) == 0) {
                $V(input.form.elements['gratuit'], 1);
            } else {
                $V(input.form.elements['gratuit'], 0);
            }
        },
    },

    CcamAct: {
        checkModifiers: (input) => {
            const exclusive_modifiers = ['F', 'P', 'S', 'U'];
            let checkboxes = $$('form[name="' + input.form.name + '"] input.modificateur');
            let nb_checked = 0;
            let exclusive_modifier;
            let exclusive_modifier_checked = false;

            checkboxes.each((checkbox) => {
                if (checkbox.checked) {
                    nb_checked++;
                }

                if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
                    exclusive_modifier = checkbox.get('code');
                    exclusive_modifier_checked = true;
                }
            });

            checkboxes.each((checkbox) => {
                if (
                    (!checkbox.checked && nb_checked == 4) ||
                    (exclusive_modifiers.indexOf(exclusive_modifier) != -1 && exclusive_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && exclusive_modifier_checked)
                ) {
                    checkbox.disabled = true;
                } else {
                    checkbox.disabled = false;
                }
            });


            const container = input.up();
            if (input.checked == true && container.hasClassName('warning')) {
                container.removeClassName('warning');
                container.addClassName('error');
            } else if (input.checked == false && container.hasClassName('error')) {
                container.removeClassName('error');
                container.addClassName('warning');
            }
        },

        editTeeth: (act_guid) => {
            Modal.open($('teeth-container-' + act_guid), {showClose: true, title: $T('CActeCCAM-position_dentaire-desc')});
        },

        setTooth: (input) => {
            let teeth = $V(input.form.position_dentaire);
            const tooth = input.getAttribute('data-localisation');

            if (teeth != '') {
                teeth = teeth.split('|');
            } else {
                teeth = [];
            }

            if (input.checked) {
                teeth.push(tooth);
            } else if (!input.checked && teeth.indexOf(tooth) != -1) {
                teeth.splice(teeth.indexOf(tooth), 1);
            }

            const act_guid = input.form.get('act_guid');
            const teeth_to_check = parseInt(input.form.get('concerned_teeth_number'));
            const checked_teeth_modal = $('modal-checked_teeth-' + act_guid);
            const checked_teeth_form = $('checked_teeth-' + act_guid);

            checked_teeth_modal.innerHTML = teeth.length;
            checked_teeth_form.innerHTML = teeth.length;
            if (teeth.length != teeth_to_check) {
                checked_teeth_modal.setStyle({color: 'firebrick'});
                checked_teeth_form.setStyle({color: 'firebrick'});
            } else {
                checked_teeth_modal.setStyle({color: 'forestgreen'});
                checked_teeth_form.setStyle({color: 'forestgreen'});
            }

            $V(input.form.elements['count_teeth_checked'], teeth.length);
            $V(input.form.elements['position_dentaire'], teeth.join('|'));
        },

        setTeeth: (form, act_guid) => {
            console.log($V(form.elements['count_teeth_checked']));
            console.log(parseInt(form.get('concerned_teeth_number')));
            if (parseInt($V(form.elements['count_teeth_checked'])) != parseInt(form.get('concerned_teeth_number'))) {
                Modal.alert($T('CActeCCAM-error-incorrect_teeth_number_checked', form.get('concerned_teeth_number')));
                return false;
            }
            $V($('edit' + act_guid + '_position_dentaire'), $V(form.elements['position_dentaire']));
            Control.Modal.close();
        }
    },

    LppAct: {
        updateAmounts: (form) => {
            let unit_price = parseFloat($V(form.elements['montant_base']));
            let quantity = parseInt($V(form.elements['quantite']));
            let depassement = parseFloat($V(form.elements['montant_depassement']));
            let total_lpp_price = +(Math.round((unit_price * quantity) + "e+2")  + "e-2");

            $V(form.elements['montant_final'], total_lpp_price);
            $V(form.elements['montant_total'], +(Math.round((total_lpp_price + depassement) + "e+2")  + "e-2"))
        }
    }
};
