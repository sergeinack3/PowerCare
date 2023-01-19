{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPccam" script="code_ccam" ajax=$ajax}}

<script>
    duplicateCodage = function (codage_id, acte_id) {
        var url = new Url('ccam', 'duplicateCodage');
        if (codage_id) {
            url.addParam('codage_id', codage_id);
        }
        if (acte_id) {
            url.addParam('acte_id', acte_id);
        }
        url.requestModal();
    }

    changeCodageMode = function (element, codage_id) {
        var codageForm = getForm("formCodageRules_codage-" + codage_id);
        if ($V(element)) {
            $V(codageForm.association_mode, "user_choice");
        } else {
            $V(codageForm.association_mode, "auto");
        }
        codageForm.onsubmit();
    };

    onChangeDepassement = function (element, view) {
        {{if $app->user_prefs.default_qualif_depense != ''}}
        if ($V(element)) {
            $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, '{{$app->user_prefs.default_qualif_depense}}');
        } else {
            $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, '');
        }
        {{/if}}

        syncCodageField(element, view);
    };

    syncCodageField = function (element, view) {
        var acteForm = getForm('codageActe-' + view);
        var fieldName = element.name;
        var fieldValue = $V(element);
        $V(acteForm[fieldName], fieldValue);
        if ($V(acteForm.acte_id)) {
            acteForm.onsubmit();
        } else {
            checkModificateurs(view, element);
        }
    };

    setFacturableAuto = function (input) {
        $V(input.form.elements['facturable_auto'], '0');
    };

    checkModificateurs = function (acte, input) {
        var exclusive_modifiers = ['F', 'P', 'S', 'U', 'O'];
        var checkboxes = $$('input[data-acte="' + acte + '"].modificateur');
        var nb_checked = 0;
        var exclusive_modifier = '';
        var exclusive_modifier_checked = false;
        var optam_modifiers = ['K', 'T'];
        var optam_modifier = '';
        var optam_modifier_checked = false;
        checkboxes.each(function (checkbox) {
            if (checkbox.checked) {
                nb_checked++;
                if (checkbox.get('double') == 2) {
                    nb_checked++;
                }
                if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
                    exclusive_modifier = checkbox.get('code');
                    exclusive_modifier_checked = true;
                } else if (optam_modifiers.indexOf(checkbox.get('code')) != -1) {
                    optam_modifier = checkbox.get('code');
                    optam_modifier_checked = true;
                }
            }
        });

        checkboxes.each(function (checkbox) {
            if (!checkbox.get('billed')) {
                if (exclusive_modifiers.indexOf(checkbox.get('code')) !== -1) {
                    checkbox.disabled = (!checkbox.checked && nb_checked === 4) || checkbox.get('price') === '0' || checkbox.get('state') === 'forbidden'
                      || (exclusive_modifiers.indexOf(exclusive_modifier) !== -1 && !checkbox.checked && exclusive_modifier_checked);
                } else if (optam_modifiers.indexOf(checkbox.get('code')) !== -1) {
                    checkbox.disabled = (!checkbox.checked && nb_checked === 4) || checkbox.get('price') === '0' || checkbox.get('state') === 'forbidden'
                      || (optam_modifiers.indexOf(optam_modifier) !== -1 && !checkbox.checked && optam_modifier_checked);
                }
            }
        });

        if (input) {
            var container = input.up();
            if (input.checked && container.hasClassName('warning')) {
                container.removeClassName('warning');
                container.addClassName('error');
            } else if (!input.checked && container.hasClassName('error')) {
                container.removeClassName('error');
                container.addClassName('warning');
            }
        }
    };

    setRule = function (element, codage_id) {
        var codageForm = getForm("formCodageRules_codage-" + codage_id);
        $V(codageForm.association_mode, "user_choice", false);
        var inputs = document.getElementsByName("association_rule");
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = false;
        }
        $V(codageForm.association_rule, $V(element), false);
        codageForm.onsubmit();
    };

    switchViewActivite = function (value, activite) {
        if (value) {
            $$('.activite-' + activite).each(function (oElement) {
                oElement.show()
            });
        } else {
            $$('.activite-' + activite).each(function (oElement) {
                oElement.hide()
            });
        }
    };

    addActeAnesthComp = function (acte, auto) {
        if (auto || confirm("Voulez-vous ajouter l'acte d'anesthésie complémentaire " + acte + '?')) {
            var on_change = CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange;
            CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange = Prototype.emptyFunction;
            CCAMField{{$subject->_class}}{{$subject->_id}}.add(acte, true);
            onSubmitFormAjax(getForm('addActes-{{$subject->_guid}}'));
            CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange = on_change;
        }
    }

    CCAMSelector.init = function () {
        this.sForm = "addActes-{{$subject->_guid}}";
        this.sClass = "_class";
        this.sChir = "_chir";
        {{if ($subject->_class=="COperation")}}
        this.sAnesth = "_anesth";
        {{/if}}
        {{if $subject->_class == 'CSejour'}}
        this.sDate = '{{$subject->sortie}}';
        {{else}}
        this.sDate = '{{$subject->_datetime}}';
        {{/if}}
        this.sView = "_new_code_ccam";
        this.pop();
    };

    showAlerteRemboursement = function (code, remboursement, form, view) {
        $('ccam_rembex_alert_content').innerHTML = $T('CDatedCodeCCAM-msg-remboursement.' + remboursement, code);
        $('validateRembex').onclick = syncRembexField.curry(view);
        Modal.open($('ccam_rembex_alert_container'), {showClose: true});
    };

    syncRembexField = function (view) {
        var rembField = getForm('remboursementExceptionnel').rembourse;
        if ($V(rembField) !== null) {
            var acteForm = getForm('codageActe-' + view);
            var fieldValue = $V(rembField);
            $V(acteForm['rembourse'], fieldValue);
            Control.Modal.close();
            acteForm.onsubmit();
        } else {
            Modal.alert('Veuillez saisir une valeur avant de valider');
        }
    };

    submitFormAct = function (form) {
        if ($V(form.acte_id) == '' && form.position_dentaire && $V(form.position_dentaire) == '') {
            setDents(form);
            return false;
        } else {
            return onSubmitFormAjax(form, {
                onComplete: function () {
                    window.urlCodage.refreshModal();
                }
            });
        }
    };

    submitActs = function (codage) {
        var forms = $$('form.new-act-form-' + codage);
        var data = {multiple: 1, objects_count: forms.length};

        forms.each(function (form, index) {
            var modifiers = '';
            form.getInputs().each(function (element) {
                var name = element.name;
                var value = $V(element);

                /* Ajout des paramètres m, dosql et del */
                if (['m', 'dosql', 'del'].indexOf(name) != -1) {
                    if (!data.hasOwnProperty(name)) {
                        data[name] = value;
                    }
                }
                /* Ajout des données de chaque acte */
                else {
                    /* Regroupement des modificateurs */
                    if (name.indexOf('modificateur') != -1) {
                        if (element.checked && name.match(/modificateur_([A-Z0-9])/)) {
                            var matches = name.match(/modificateur_([A-Z0-9])/);
                            if (matches.length == 2) {
                                modifiers = modifiers + matches[1];
                            }
                        }
                    } else {
                        if (!data.hasOwnProperty(name)) {
                            data[name] = [];
                        }

                        data[name].push(value);
                    }
                }
            });

            /* Ajout des modificateurs cochés */
            if (!data.hasOwnProperty('modificateurs')) {
                data['modificateurs'] = [];
            }

            data['modificateurs'].push(modifiers);
        });

        var url = new Url(data.m, data.dosql, 'dosql');

        Object.keys(data).each(function (property) {
            if (Object.isArray(data[property])) {
                url.addParam(property + '[]', data[property], true);
            } else {
                url.addParam(property, data[property]);
            }
        });

        url.requestUpdate(SystemMessage.id, {
            method:        'post',
            getParameters: {m: data.m, dosql: data.dosql},
            onComplete:    window.urlCodage.refreshModal.bind(window.urlCodage)
        });
    };

    setDents = function (form) {
        var url = new Url('ccam', 'setDentsCodage');
        url.addParam('acte_view', form.get('view'));
        url.addParam('code', $V(form.code_acte));
        url.addParam('activite', $V(form.code_activite));
        url.addParam('phase', $V(form.code_phase));
        url.addParam('date', $V(form.execution));
        {{if $subject->_class == 'CModelCodage'}}
        url.addParam('nullable', '1');
        {{/if}}
        url.requestModal();
    };

    deleteCodage = (form) => {
        return onSubmitFormAjax(form, {
            onComplete: window.urlCodage.refreshModal.bind(window.urlCodage)
        });
    };

    {{if $codages|@count != 1}}
    Main.add(function () {
        Control.Tabs.create('codages-tab', true);
    });
    {{/if}}
</script>

<table class="main" style="min-width: 400px; border-spacing: 0px;">
    <tr>
        <th id="codages-title" class="title" style="border-bottom: none; border-spacing: 0px;">
            <div style="float: left">
                <form name="addActes-{{$subject->_guid}}" method="post" onsubmit="return false">
                    {{if $subject|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
                        <input type="hidden" name="m" value="cabinet"/>
                        <input type="hidden" name="dosql" value="do_consultation_aed"/>
                    {{elseif $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
                        <input type="hidden" name="m" value="planningOp"/>
                        <input type="hidden" name="dosql" value="do_planning_aed"/>
                    {{elseif $subject|instanceof:'Ox\Mediboard\Patients\CEvenementPatient'}}
                        <input type="hidden" name="del" value="0"/>
                        <input type="hidden" name="m" value="dPpatients"/>
                        <input type="hidden" name="dosql" value="do_evenement_patient_aed"/>
                    {{elseif $subject|instanceof:'Ox\Mediboard\Planningop\CSejour'}}
                        <input type="hidden" name="m" value="planningOp"/>
                        <input type="hidden" name="dosql" value="do_sejour_aed"/>
                    {{/if}}
                    {{mb_class object=$subject}}
                    {{mb_key object=$subject}}

                    <input type="hidden" name="_class" value="{{$subject->_class}}"/>
                    <input type="hidden" name="_chir" value="{{$subject->_praticien_id}}"/>
                    {{if ($subject->_class=="COperation")}}
                        <input type="hidden" name="_anesth" value="{{$subject->_ref_plageop->anesth_id}}"/>
                    {{/if}}
                    {{mb_field object=$subject field="codes_ccam" hidden=true}}
                    <input type="hidden" name="_new_code_ccam" value=""
                           onchange="CCAMField{{$subject->_class}}{{$subject->_id}}.add(this.value, true);"/>

                    <button id="didac_actes_ccam_tr_modificateurs" class="search" type="button"
                            onclick="CCAMSelector.init()">
                        {{tr}}Search{{/tr}}
                    </button>
                    <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init()" style="width: 12em" value=""
                           class="autocomplete" placeholder="Ajoutez un acte"/>
                    <div
                      style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
                      class="autocomplete" id="_ccam_autocomplete_{{$subject->_guid}}"></div>
                    <script>
                        Main.add(function () {
                            var form = getForm("addActes-{{$subject->_guid}}");
                            var url = new Url("ccam", "autocompleteCcamCodes");
                            {{if $subject->_class == 'CSejour'}}
                            url.addParam("date", '{{$subject->sortie}}');
                            {{else}}
                            url.addParam("date", '{{$subject->_datetime}}');
                            {{/if}}
                            url.addParam('user_id', '{{$subject->_praticien_id}}');
                            {{if $subject->_class == 'CSejour' || $subject->_class == 'CConsultation'}}
                            url.addParam('patient_id', '{{$subject->patient_id}}');
                            {{elseif $subject->_class == 'COperation'}}
                            url.addParam('patient_id', '{{$subject->_patient_id}}');
                            {{/if}}
                            url.autoComplete(form._codes_ccam, "_ccam_autocomplete_{{$subject->_guid}}", {
                                minChars:      1,
                                dropdown:      true,
                                width:         "250px",
                                updateElement: function (selected) {
                                    CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
                                }
                            });
                            CCAMField{{$subject->_class}}{{$subject->_id}} = new TokenField(form.elements["codes_ccam"], {
                                onChange:  function () {
                                    return onSubmitFormAjax(form, window.urlCodage.refreshModal.bind(window.urlCodage));
                                },
                                sProps:    "notNull code ccam",
                                serialize: true
                            });
                        })
                    </script>
                </form>
            </div>

            {{if $codages|@count == 1}}
                <div style="float: right;">
                    {{mb_include module=system template=inc_object_history object=$codages|@first}}
                </div>
            {{/if}}

            Actes du Dr {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticien}}
            {{if $remplace}}
                ({{tr}}CRemplacement-msg-remplacant{{/tr}} Dr {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$remplace}})
            {{/if}}
            {{if $subject->_class == 'CSejour'}}
                {{assign var=codage value=$codages|@first}}
                le {{$codage->date|date_format:$conf.datetime}}
            {{/if}}
        </th>
    </tr>
    <tr>
        <th class="title" style="border-top: none; border-spacing: 0px;">
            {{if !$praticien->spec_cpam_id || !$praticien->secteur}}
                <div>
                    <div class="small-warning"
                         style="display: inline-block; max-width: 600px; font-weight: normal; color: black; font-size: 10px; text-shadow: none;">
                        {{tr}}CMediusers-msg-ccam_context_missing_infos{{/tr}}
                    </div>
                </div>
            {{/if}}
        </th>
    </tr>
    <tr>
        <td>
            {{if $codages|@count != 1}}
                {{assign var=total value=0}}
                <ul id="codages-tab" class="control_tabs">
                    {{foreach from=$codages item=_codage}}
                        {{math assign=total equation="x+y" x=$total y=$_codage->_total}}
                        <li>
                            <a href="#codage-{{$_codage->_id}}">
                                {{tr}}CCodageCCAM.activite_anesth.{{$_codage->activite_anesth}}{{/tr}}
                                {{if ($subject->_class == 'COperation' && $subject->date != $_codage->date) || ('CConsultation' === $subject->_class && $subject->_date != $_codage->date)}}&nbsp;{{mb_value object=$_codage field=date}}{{/if}}
                                &nbsp;&nbsp;
                                {{* Le tpl inc_object_history pose problème avec le Control.Tabs et le style des controls tabs (il traite le lien vers l'historique comme un nouveau tab) *}}
                                <i class="me-icon history me-primary" style="float:right;"
                                   onmouseover="ObjectTooltip.createEx(this,'{{$_codage->_guid}}', 'objectViewHistory')"
                                   onclick="guid_log('{{$_codage->_guid}}')">
                                </i>

                                {{if !$_codage->_ref_actes_ccam|@count}}
                                    <form name="formDeleteCodage-{{$_codage->_id}}" action="?" method="post" onsubmit="return deleteCodage(this);">
                                        {{mb_class object=$_codage}}
                                        {{mb_key object=$_codage}}
                                        <input type="hidden" name="del" value="1" />
                                        <button type="button" class="me-no-border me-icon notext trash me-tertiary" style="margin-left: 5px; float: right;" onclick="this.form.onsubmit();">{{tr}}CCodageCCAM-action-delete{{/tr}}</button>
                                    </form>
                                {{/if}}
                            </a>
                        </li>
                    {{/foreach}}
                    <li>
                        Total activités
                        : {{$total|number_format:2:',':' '}} {{$conf.currency_symbol|html_entity_decode}}
                    </li>
                </ul>
                {{foreach from=$codages item=_codage}}
                    <div id="codage-{{$_codage->_id}}" style="display: none;">
                        {{mb_include module=salleOp template=inc_edit_codage codage=$_codage}}
                    </div>
                {{/foreach}}
            {{else}}
                {{mb_include module=salleOp template=inc_edit_codage codage=$codages|@first}}
            {{/if}}
        </td>
    </tr>
</table>

<div id="ccam_rembex_alert_container" style="display: none;">
    <div id="ccam_rembex_alert_content" class="small-warning"></div>
    <div style="text-align: center;">
        <form name="remboursementExceptionnel" action="?" method="post" onsubmit="return false;">
            <table class="form">
                <tr>
                    <th>
                        <label for="rembourse"
                               title="{{tr}}CActeCCAM-rembourse-desc{{/tr}}">{{tr}}CActeCCAM-rembourse{{/tr}}</label>
                    </th>
                    <td>
                        <input type="radio" name="rembourse" value="1"/>
                        <label for="remboursementExceptionnel_rembourse_1">{{tr}}Yes{{/tr}}</label>
                        <input type="radio" name="rembourse" value="0"/>
                        <label for="remboursementExceptionnel_rembourse_0">{{tr}}No{{/tr}}</label>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div style="text-align: center;">
        <button type="button" class="tick" onclick="" id="validateRembex">{{tr}}Validate{{/tr}}</button>
    </div>
</div>
