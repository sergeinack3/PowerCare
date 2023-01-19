{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=courbe_reference_graph ajax=true}}

{{mb_default var=hide_save_button value=0}}
{{mb_default var=callback_administration value=0}}
{{mb_default var=display_graph value=1}}
{{mb_default var=unique_id value=""}}
{{mb_default var=can_create value=0}}
{{mb_default var=show_cat_tabs value='Ox\Mediboard\Patients\CConstantesMedicales::getConfig'|static_call:"show_cat_tabs"}}
{{mb_default var=activate_choice_blood_glucose_units value='Ox\Mediboard\Patients\CConstantesMedicales::getConfig'|static_call:"activate_choice_blood_glucose_units"}}
{{mb_default var=modif_timeout value=0}}
{{mb_default var=disable_edit_motif value=''}}
{{mb_default var=default_tab value=false}}
{{mb_default var=only_buttons value=0}}

{{assign var=use_redon value='dPpatients CConstantesMedicales use_redon'|gconf}}

<script>
    checkFormConstant = function (form) {

        var constanteEmpty = false;
        form.select('.constanteEmpty').each(function (input) {
            if (input.value.trim().length !== 0) {
                constanteEmpty = true;
            }
        });

        if (!constanteEmpty) {
            // Message d'erreur si les champs sont vide
            SystemMessage.notify("<div class='error'>" + $T('CConstantesMedicales-msg-Please fill in at least one constant') + "</div>");
            $('btn_save_constant').enable();
            constanteEmpty = false;

            return false;
        }

        var taille = parseInt($V(form.taille)), poids = $V(form.poids);
        if (poids && taille) {
            var imc = poids / (taille * taille * 0.0001);
            var imc_threshold = parseInt('{{'dPpatients CConstantesMedicales imc_threshold'|gconf}}');
            if (imc_threshold && imc > imc_threshold && !confirm($T('CConstantesMedicales-msg-imc_threshold_reached', imc_threshold, taille, poids))) {
                $('btn_save_constant').enable();
                return false;
            }
        }

        if (!checkForm(form)) {
            $('btn_save_constant').enable();
            return false;
        }

        return true;
    };

    submitConstantesMedicales = function (oForm) {
        $('btn_save_constant').disable();
        return onSubmitFormAjax(oForm, {
            onComplete: function () {
                if ($('glasgow_tooltip')) {
                    Motif.refreshComplement();
                }

                callbackSubmitConstantes(oForm);

                if ($$('.constante-value-container').length) {
                    updateInfosPatient();
                }
            },
            check:      checkFormConstant
        });
    };

    callbackSubmitConstantes = function (form) {
        {{if $display_graph}}
        refreshConstantesMedicales{{$unique_id}}($V(form.context_class) + '-' + $V(form.context_id));
        {{/if}}
    };

    updateInfosPatient = function () {
        var containers = $$('.constante-value-container');

        if (containers.length == 0) {
            return;
        }

        var form = getForm("edit-constantes-medicales{{$unique_id}}");

        var constantes = {};
        containers.each(function (container) {
            constantes[container.get("constante")] = true;
        });

        var url = new Url('soins', 'ajax_update_infos_patient');
        url.addParam('patient_id', $V(form.patient_id));
        url.addParam('constante_names', Object.keys(constantes).join('-'));

        var context_class = $V(form.elements.context_class);
        if (context_class == 'CSejour') {
            url.addParam('sejour_id', $V(form.elements.context_id));
        }

        url.requestJSON(function (data) {
            containers.each(function (container) {
                container.update(data[container.get("constante")]);
            });
        });
    };

    toggleConstantesecondary = function (element) {
        var secondary = $$('.constantes .secondary');
        secondary.invoke('toggle');
        if (secondary[0].visible()) {
            element.removeClassName("down");
            element.addClassName("up");
            element.innerHTML = "Cacher les scd.";
        } else {
            element.removeClassName("up");
            element.addClassName("down");
            element.innerHTML = "Aff. tout";
            $$('.constantes tr:not(.secondary)').invoke('show');

        }

        $V($('filter_constants'), '');
    };

    calculImcVst = function (form) {
      if (!form) {
        return;
      }

        var imcInfo, imc, vst,
          poids = parseFloat($V(form.poids)),
          taille = parseFloat($V(form.taille));

        if (poids && !isNaN(poids) && poids > 0) {
            vst = {{if $constantes->_ref_patient && $constantes->_ref_patient->sexe=="m"}}70{{else}}65{{/if}} * poids;

            if (taille && !isNaN(taille) && taille > 0) {
                imc = Math.round(100 * 100 * 100 * poids / (taille * taille)) / 100; // Math.round(x*100)/100 == round(x, 2)

                if (imc < 15) {
                    imcInfo = "Inanition";
                } else if (imc < 18.5) {
                    imcInfo = "Maigreur";
                } else if (imc > 40) {
                    imcInfo = "Obésité morbide";
                } else if (imc > 35) {
                    imcInfo = "Obésité sévère";
                } else if (imc > 30) {
                    imcInfo = "Obésité modérée";
                } else if (imc > 25) {
                    imcInfo = "Surpoids";
                }
            }
        }

        $V(form._vst, vst);
        $V(form._imc, imc);

        var element = $('constantes_medicales_imc{{$unique_id}}');
        if (element) {
            element.update(imcInfo);
        }

        if (typeof (calculPSA) == 'function' && typeof (calculClairance) == 'function') {
            calculPSA();
            calculClairance();
        }
    };

    emptyAndSubmit = function (const_name) {
        var form = getForm("edit-constantes-medicales{{$unique_id}}");
        const_name.each(function (elem) {
            $V(form[elem], '');
        });
        return submitConstantesMedicales(form);
    };

    displayConstantGraph = function (constant) {
        var checkboxes = $$('input[name="_displayGraph"]:checked');
        var selection = [];
        checkboxes.each(function (checkbox) {
            selection.push(checkbox.getAttribute('data-constant'));
        });

        if (selection.length == 0) {
            alert($T('CConstantesMedicales-You must at least select a constant !'));
            return;
        }

        var form = getForm('edit-constantes-medicales{{$unique_id}}');
        var url = new Url('patients', 'ajax_select_constants_graph_period');
        url.addParam('patient_id', $V(form.patient_id));
        url.addParam('constants', JSON.stringify(selection));
        url.addParam('period', 'month');
        url.addParam('context_guid', '{{$context_guid}}');
        url.pop(950, 400);
    };

    checkGraph = function () {
        var checkboxes = $$('input[name="_displayGraph"]:checked');

        if (checkboxes.length >= 5) {
            checkboxes = $$('input[name="_displayGraph"]:not(:checked)').each(function (elt) {
                elt.disable();
            });
        } else {
            checkboxes = $$('input[name="_displayGraph"]:not(:checked)').each(function (elt) {
                elt.enable();
            });
        }
    };

    addComment = function (form) {
        var comments = [];
        if ($V(form._constant_comments)) {
            comments = JSON.parse($V(form._constant_comments));
        }

        var comment = {
            'constant': $V(form._constant_comment),
            'comment':  $V(form._comment)
        };
        comments.push(comment);
        $V(form._constant_comments, JSON.stringify(comments));
        $V(form._constant_comment, '');
        $V(form._comment, '');
        Control.Modal.close();
    };

    editComment = function (constant, constant_id, comment_id, value) {
        if (!constant_id) {
            var form = getForm('edit-constantes-medicales{{$unique_id}}');
            $V(form._constant_comment, constant);
            Modal.open('add-comment{{$unique_id}}', {
                showClose: true,
                width:     500,
                height:    300
            });
            var validate_comment_cte = $('validate_comment_cte');
            validate_comment_cte.disabled = '';
            if (value == '') {
                validate_comment_cte.disabled = 'disabled';
            }
            $('list_comments{{$unique_id}}').innerHTML = '';
            var urlCte = new Url('patients', 'vw_last_comments_cte');
            urlCte.addParam('constant', constant);
            urlCte.addParam('context_class', $V(form.context_class));
            urlCte.addParam('context_id', $V(form.context_id));
            urlCte.requestUpdate('list_comments{{$unique_id}}');
        } else {
            var url = new Url('patients', 'ajax_edit_constant_comment');
            url.addParam('constant', constant);
            url.addParam('constant_id', constant_id);
            url.addParam('unique_id', '{{$unique_id}}');
            url.requestModal(300, 300);
        }
    };

    filterConstants = function (text, container_id) {
        var table = $(container_id).down('table.main.form.constantes');
        table.select('tr').invoke('show');

        table.select('th.constant_name').each(function (element) {
            if (!element.innerHTML.like(text)) {
                element.up('tr').hide();
            }
        });
    };

    resumeBilanHydrique = function (date_bh, granularite) {
        new Url("patients", "ajax_bilan_hydrique")
          .addParam("sejour_id", "{{$constantes->context_id}}")
          .addNotNullParam("date_bh", date_bh)
          .addNotNullParam("granularite", granularite)
          .requestModal('90%', '90%');
    };

    showRedons = function () {
        var form = getForm('edit-constantes-medicales');
        new Url('patients', 'vw_redons')
          .addParam('sejour_id', '{{$constantes->context_id}}')
          .requestModal('80%', '100%', {onClose: callbackSubmitConstantes.curry(form)});
    };

    Main.add(function () {
        var oForm = getForm('edit-constantes-medicales{{$unique_id}}');
        calculImcVst(oForm);
        if (window.toggleAllGraphs) {
            toggleAllGraphs();
        }

        var dates = {
            limit: {
                stop: "{{'Ox\Core\CMbDT::datetime'|static_call:null}}"
            }
        };

        if (oForm) {
          Calendar.regField(oForm.datetime, dates);
        }

        {{if $show_cat_tabs}}
        var tab = Control.Tabs.create("constantes-by-type{{$unique_id}}");
        {{if $default_tab}}
        tab.setActiveTab('type-{{$default_tab}}{{$unique_id}}');
        {{/if}}
        {{/if}}

      if (oForm) {
        oForm.select('input[type=text]').each(function (input) {
          input.addClassName('constanteEmpty');
          $('edit-constantes-medicales{{$unique_id}}_datetime_da').removeClassName('constanteEmpty')
        });
      }
    });
</script>

<div id="constant_form{{$unique_id}}"
     style="position: absolute; top: 0; left: 0; bottom: 0; min-height: 290px; width: 100%;">
    {{if $constantes->context_class === 'CSejour'}}
      <button type="button" class="stats me-secondary me-margin-top-4" style="float: right;"
              onclick="resumeBilanHydrique();">{{tr}}CConstantesMedicales-_bilan_hydrique-court{{/tr}}</button>
      {{if $use_redon}}
          <button type="button" class="search me-margin-top-4" style="float: right;"
                  onclick="showRedons();">{{tr}}CRedon{{/tr}}</button>
      {{/if}}
    {{/if}}

    {{if $only_buttons}}
</div>
{{mb_return}}
{{/if}}

<form name="edit-constantes-medicales{{$unique_id}}" action="?" method="post"
      onsubmit="return {{if $can_edit}}checkForm(this){{else}}false{{/if}}">
    {{mb_class object=$constantes}}
    {{mb_key object=$constantes}}
    <input type="hidden" name="del" value="0"/>
    {{if !$constantes->_id}}
        <input type="hidden" name="_new_constantes_medicales" value="1"/>
    {{else}}
        <input type="hidden" name="_new_constantes_medicales" value="0"/>
    {{/if}}
    {{mb_field object=$constantes field=_unite_ta hidden=1}}
    {{mb_field object=$constantes field=_unite_glycemie hidden=1}}
    {{mb_field object=$constantes field=_unite_cetonemie hidden=1}}
    {{mb_field object=$constantes field=context_class hidden=1}}
    {{mb_field object=$constantes field=context_id hidden=1}}
    {{mb_field object=$constantes field=patient_id hidden=1}}
    {{if $callback_administration}}
        <input type="hidden" name="callback" value="submitAdmission"/>
    {{/if}}
    {{assign var=const value=$latest_constantes.0}}
    {{assign var=dates value=$latest_constantes.1}}
    {{assign var=all_constantes value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes_type"}}

    <input type="hidden" name="_poids" value="{{$const->poids}}"/>
    <input type="hidden" name="_constant_comments" value=""/>

    {{if $show_cat_tabs}}
        <ul id="constantes-by-type{{$unique_id}}"
            class="control_tabs small me-dossier-soin-tabs-cst me-no-border-radius" style="min-width: 200px;">
            {{foreach from=$all_constantes key=_type item=_list}}
                {{if array_key_exists($_type, $selection)}}
                    <li class="me-no-border-radius">
                        <a href="#type-{{$_type}}{{$unique_id}}">{{tr}}CConstantesMedicales.type.{{$_type}}{{/tr}}</a>
                    </li>
                {{/if}}
            {{/foreach}}
        </ul>
    {{/if}}

    <div class="me-patient-constantes" id="constantes_{{$constantes->_id}}{{$unique_id}}"
         style="position: absolute; top: {{if $show_cat_tabs}}40px{{else}}25px{{/if}}; left: 0px; bottom: 100px; overflow-y: auto; width: 100%;">
        {{if !$can_edit}}
            <div class="small-warning">
                {{if $disable_edit_motif == 'timeout'}}
                    {{tr var1=$modif_timeout}}CConstantes-Medicales-msg-modif-timeout-%s{{/tr}}
                {{elseif !$is_redon}}
                    {{tr}}CConstantes-Medicales-msg-edit_not_creator{{/tr}}
                {{else}}
                    {{tr}}CConstantes-Medicales-msg-Please go to the table of redons if you want to make a modification{{/tr}}
                {{/if}}
            </div>
        {{/if}}
        <table class="main form constantes me-dossier-soin-constantes me-small" style="margin-right:20px;">
            <tr>
                <th class="category narrow">
                    <button class="stats notext me-tertiary me-dark me-btn-small" type="button"
                            onclick="displayConstantGraph();">
                        {{tr}}CConstantGraph-msg-display{{/tr}}
                    </button>
                </th>
                <th class="category me-dossier-soin-label-const" style="text-align: left;">
                    <input type="text" id="filter_constants" name="filter_constants" size="3"
                           onkeyup="filterConstants($V(this), 'constantes_{{$constantes->_id}}{{$unique_id}}');"/>
                </th>
                {{if $can_edit}}
                    <th class="category" colspan="2">Saisie</th>
                {{/if}}
                <th class="category" colspan="{{if $display_graph}}2{{else}}1{{/if}}">Dernières</th>
                <th class="category">
                    {{if $constantes->_id}}
                        {{mb_include module=system template=inc_object_history object=$constantes}}
                    {{/if}}
                </th>
                <th style="width:10px;"></th>
            </tr>

            {{assign var=at_least_one_hidden value=false}}
            {{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes"}}
            {{assign var=noticed_constant value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"_noticed_constant"}}

            {{foreach from=$selection key=_type item=_ranks}}
                <tbody class="me-patient-constantes_data"
                       id="type-{{$_type}}{{$unique_id}}" {{if $show_cat_tabs}} {{if $_type != "vital"}} style="display: none;" {{/if}} {{/if}}>
                {{foreach from=$_ranks key=_rank item=_constants}}
                    {{foreach from=$_constants item=_constant}}
                        <tr {{if $_rank == "hidden" && ($const->$_constant == "" || !$display_graph)}}
                            style="display: none;" class="secondary"
                          {{assign var=at_least_one_hidden value=true}}
                          {{/if}}>
                            <td>
                                <input name="_displayGraph" type="checkbox" data-constant="{{$_constant}}"
                                       onclick="checkGraph();" tabindex="-1"/>
                            </td>
                            <th style="text-align: left;" class="text constant_name">
                                <button type="button" class="stats notext me-tertiary me-dark me-btn-small"
                                        style="float: right; display: none;"
                                        onclick="displayConstantGraph('{{$_constant}}');">
                                    {{tr}}CConstantGraph-msg-display{{/tr}}
                                </button>
                                {{assign var=constantName value=""}}
                                {{if $constantes->_ref_patient->_annees <= "18"}}
                                    {{if $constantes->_ref_patient->_annees <= "5" && $constantes->_ref_patient->_mois <= 60}}
                                        {{assign var=select_graph value=0}}
                                        {{assign var=constantName value="perimetre_cranien"}}
                                    {{else}}
                                        {{assign var=select_graph value=1}}
                                    {{/if}}
                                {{/if}}
                                {{if ($constantes->_ref_patient->_annees <= "18" && in_array($_constant, array("poids", "taille", "_imc", $constantName, "bilirubine_transcutanee", "bilirubine_totale_sanguine")))}}
                                    {{if $_constant == "_imc"}}
                                        {{assign var=constantName value="_imc"}}
                                    {{/if}}
                                    <button type="button" class="stats notext me-tertiary me-dark me-btn-small"
                                            style="float: right; display: block;"
                                            onclick="CourbeReference.showModalGraph('{{$constantes->_ref_patient->_id}}','{{$_constant}}','{{$constantName}}',{{$select_graph}});">
                                        {{tr}}CCourbeReference-action-Display reference curve{{/tr}}
                                    </button>
                                {{/if}}
                                <label for="{{$_constant}}"
                                       title="{{tr}}CConstantesMedicales-{{$_constant}}-desc{{/tr}}" onmouseover="">
                                    <script>
                                        Main.add(function () {
                                            if ($('{{$_constant}}' + '_tooltip')) {
                                                $('labelFor_edit-constantes-medicales' + '{{$unique_id}}' + '_' + '{{$_constant}}').onmouseover = function () {
                                                    ObjectTooltip.createDOM(this, '{{$_constant}}' + '_tooltip');
                                                };
                                            }
                                        });
                                    </script>
                                    {{tr}}CConstantesMedicales-{{$_constant}}-court{{/tr}}

                                    {{assign var=_params value=$constants_list.$_constant}}
                                    {{if $_params.unit && (($_constant != "glycemie") || !$activate_choice_blood_glucose_units)}}
                                        <small class="opacity-50">
                                            ({{$_params.unit}})
                                        </small>
                                    {{/if}}
                                </label>
                                {{if $_constant|in_array:$noticed_constant}}
                                    <span onmouseover="ObjectTooltip.createDOM(this, '{{$_constant}}-notice');"
                                          style="float: right; color: #2946c9; border-bottom: none;">
                        <i class="fa fa-lg fa-info-circle"></i>
                      </span>
                                    <div id="{{$_constant}}-notice" class="me-color-black-high-emphasis"
                                         style="display: none;">
                                        {{tr}}CConstantesMedicales-notice-{{$_constant}}{{/tr}}
                                    </div>
                                {{/if}}

                                {{if $_constant|array_key_exists:$constantes->_refs_comments}}
                                    {{assign var=comment value=$constantes->_refs_comments.$_constant}}
                                    <i class="me-icon comment me-primary" style="float: right;"
                                       title="{{$comment->comment}}"></i>
                                {{/if}}
                            </th>

                            {{assign var=_readonly value=null}}
                            {{if array_key_exists("formfields", $_params) && !array_key_exists("readonly", $_params)}}
                                {{if $can_edit}}
                                    <td>
                                        {{foreach from=$_params.formfields item=_formfield_name key=_key name=_formfield}}
                                            {{assign var=_style value="width:2.1em;"}}
                                            {{assign var=_size value=2}}
                                            {{if $_params.formfields|@count == 1}}
                                                {{assign var=_style value=""}}
                                                {{assign var=_size value=3}}
                                            {{/if}}

                                            {{if !$smarty.foreach._formfield.first}}/{{/if}}
                                            {{mb_field object=$constantes field=$_params.formfields.$_key size=$_size style=$_style}}

                                            {{if $activate_choice_blood_glucose_units && ($_formfield_name == "_glycemie")}}
                                                {{mb_field object=$constantes field=unite_glycemie class="me-small"}}
                                            {{/if}}
                                        {{/foreach}}
                                    </td>
                                    <td class="narrow">
                                        <button id="edit_comment_{{$_constant}}{{$unique_id}}" type="button"
                                                style="float: right;"
                                                class="comment notext me-tertiary me-dark me-btn-small"
                                                onclick="editComment('{{$_constant}}', '{{$constantes->_id}}', {{if $_constant|array_key_exists:$constantes->_refs_comments}}'{{$comment->_id}}'{{else}}null{{/if}}, $V(this.form.{{$_constant}}));"
                                                tabindex="-1">
                                            {{tr}}CConstantComment-action-create{{/tr}}
                                        </button>
                                    </td>
                                {{/if}}
                                <td style="text-align: center" title="{{$dates.$_constant|date_format:$conf.datetime}}">
                                    {{if $const->$_constant}}
                                        {{foreach from=$_params.formfields item=_formfield_name key=_key name=_formfield}}
                                            {{if !$smarty.foreach._formfield.first}}/{{/if}}
                                            {{mb_value object=$const field=$_params.formfields.$_key}}
                                            {{if $_formfield_name != "_glycemie" && $_formfield_name != "glycemie"}}{{$_params.unit}}{{/if}}
                                        {{/foreach}}
                                    {{/if}}
                                </td>
                            {{else}}
                                {{assign var=_hidden value=false}}

                                {{if $_constant.0 == "_" && !array_key_exists('edit', $_params)}}
                                    {{assign var=_readonly value="readonly"}}

                                    {{if array_key_exists("formula", $_params)}}
                                        {{assign var=_hidden value=true}}
                                    {{/if}}
                                {{/if}}

                                {{if $can_edit}}
                                    <td>
                                        {{if array_key_exists("callback", $_params)}}
                                            {{assign var=_callback value=$_params.callback|cat:'(this.form);'}}
                                        {{else}}
                                            {{assign var=_callback value=null}}
                                        {{/if}}

                                        {{if array_key_exists('readonly', $_params)}}
                                            {{assign var=_readonly value='readonly'}}
                                        {{/if}}

                                        {{if $use_redon && 'Ox\Mediboard\Patients\CRedon::isRedon'|static_call:$_constant}}
                                            {{assign var=_readonly value='readonly'}}
                                        {{/if}}

                                        {{mb_field object=$constantes field=$_constant size="3" readonly=$_readonly hidden=$_hidden onkeyup=$_callback}}

                                        {{if $_constant == "_imc"}}
                                            <div id="constantes_medicales_imc{{$unique_id}}" style="color:#F00;"></div>
                                        {{/if}}
                                    </td>
                                    <td class="compact">
                                        <button id="edit_comment_{{$_constant}}{{$unique_id}}" type="button"
                                                style="float: right;"
                                                class="comment notext me-tertiary me-dark me-btn-small"
                                                onclick="editComment('{{$_constant}}', '{{$constantes->_id}}', {{if $_constant|array_key_exists:$constantes->_refs_comments}}'{{$comment->_id}}'{{else}}null{{/if}}, $V(this.form.{{$_constant}}));"
                                                tabindex="-1">
                                            {{tr}}CConstantComment-action-create{{/tr}}
                                        </button>
                                    </td>
                                {{/if}}
                                <td style="text-align: center" title="{{$dates.$_constant|date_format:$conf.datetime}}">
                                    {{mb_value object=$const field=$_constant}}
                                    {{assign var=cumul_field value="_$_constant"|cat:'_cumul'}}
                                    {{if $_constant != '_bilan_hydrique' && isset($_params.cumul_reset_config|smarty:nodefaults) && !isset($_params.formula|smarty:nodefaults) && isset($const->$cumul_field|smarty:nodefaults)}}
                                        {{assign var=cumul_field value="_$_constant"|cat:'_cumul'}}
                                        ({{$const->$cumul_field}})
                                    {{/if}}
                                    <input type="hidden" name="_last_{{$_constant}}" value="{{$const->$_constant}}"/>
                                </td>
                            {{/if}}

                            {{if $display_graph}}
                                <td class="narrow">
                                    {{if $_constant.0 != "_" || !empty($_params.plot|smarty:nodefaults)}}
                                        <input type="checkbox" class="checkbox-constant"
                                               name="checkbox-constantes-medicales-{{$_constant}}"
                                               onclick="window.oGraphs.toggle(this)" tabIndex="100"/>
                                    {{/if}}
                                </td>
                            {{/if}}
                            <td>
                                {{if $_readonly !="readonly" && $can_edit && $constantes->$_constant != ""}}
                                    {{if array_key_exists("formfields", $_params)}}
                                        <button type="button" class="cancel notext compact"
                                                onclick="emptyAndSubmit({{$_params.formfields|@json|smarty:nodefaults|JSAttribute}});"></button>
                                    {{else}}
                                        <button type="button" class="cancel notext compact"
                                                onclick="emptyAndSubmit(['{{$_constant}}']);"></button>
                                    {{/if}}
                                {{/if}}
                            </td>
                        </tr>
                    {{/foreach}}
                {{/foreach}}
                </tbody>
            {{/foreach}}
        </table>
    </div>

    <div class="me-patient-constantes_footer"
         style="position: absolute; bottom:0; text-align:center; height:100px; width: 100%;"
         id="buttons_form_const-{{$unique_id}}">
        {{if $can_edit && !$modif_timeout}}
        {{if $constantes->_id}}
            {{mb_field object=$constantes field=datetime form="edit-constantes-medicales$unique_id" register=true}}
            <button style="display:inline-block;" class="trash notext" type="button"
                    onclick="if (confirm('Etes-vous sûr de vouloir supprimer ce relevé ?')) {$V(this.form.del, 1); return submitConstantesMedicales(this.form);}">
                {{tr}}CConstantesMedicales.delete_all{{/tr}}
            </button>
        {{else}}
            {{mb_field object=$constantes field=datetime form="edit-constantes-medicales$unique_id" register=true}}

        {{if !$constantes->datetime}}
            <script type="text/javascript">
                Main.add(function () {
                    var form = getForm('edit-constantes-medicales{{$unique_id}}');
                    form.datetime.value = "now";
                    form.datetime_da.value = "Maintenant";
                });
            </script>
        {{/if}}
        {{/if}}
            {{mb_field object=$constantes field=comment placeholder="Commentaire" rows=1 form="edit-constantes-medicales$unique_id" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
        {{if !$hide_save_button}}
            <button class="modify me-primary me-small" id="btn_save_constant"
                    onclick="return submitConstantesMedicales(this.form);">
                {{tr}}Save{{/tr}}
            </button>
        {{/if}}
        {{elseif $can_create}}
            <button class="new singleclick me-small" type="button" onclick="newConstants('{{$context_guid}}');">
                {{tr}}New{{/tr}}
            </button>
        {{/if}}

        {{if $at_least_one_hidden}}
            <button class="down me-small" type="button" onclick="toggleConstantesecondary(this);">Aff. tout</button>
        {{/if}}
    </div>

    <div id="add-comment{{$unique_id}}" style="display: none;">
        {{mb_include module=patients template=inc_create_constant_comment}}
    </div>
</form>
</div>
