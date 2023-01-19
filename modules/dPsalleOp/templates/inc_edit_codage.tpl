{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
    #test a {
        float: none !important;
    }
</style>

<script type="text/javascript">
    Main.add(function () {
        Control.Tabs.create('rules-tab_codage-{{$codage->_id}}', true);
    });

    checkDhField = function (view) {
        var dhForm = getForm("codageActeMontantDepassement-" + view),
          acteForm = getForm("codageActe-" + view);
        if (acteForm._total_saisi) {
            if ($V(dhForm.montant_depassement)) {
                acteForm._total_saisi.readOnly = true;
            } else if ($V(acteForm._total_saisi)) {
                dhForm.montant_depassement.readOnly = true;
            } else {
                dhForm.montant_depassement.readOnly = false;
                acteForm._total_saisi.readOnly = false;
            }
        }
    }
</script>

<table class="tbl">
    <tr>
        <th class="narrow">{{mb_title class=CActeCCAM field=code_activite}}</th>
        {{if $subject->_class != 'CConsultation' && $subject->_class != 'CDevisCodage'}}
            <th class="narrow">{{mb_title class=CActeCCAM field=code_extension}}</th>
        {{/if}}
        <th class="narrow">{{mb_title class=CActeCCAM field=_tarif_base}}</th>
        <th class="narrow">{{mb_title class=CActeCCAM field=facturable}}</th>
        <th class="narrow">{{mb_title class=CActeCCAM field=code_association}}</th>
        <th>{{mb_title class=CActeCCAM field=modificateurs}}</th>
        <th class="narrow">{{mb_title class=CActeCCAM field=extension_documentaire}}</th>
        {{if ($subject->_class == 'CConsultation' && $subject->concerne_ALD) || ($subject->_class == 'CSejour' && $subject->ald)}}
            <th class="narrow">{{mb_title class=CActeCCAM field=ald}}</th>
        {{/if}}
        <th class="narrow">{{mb_title class=CActeCCAM field=_tarif}}</th>
        <th class="narrow">{{mb_title class=CActeCCAM field=execution}}</th>
        {{if $codage->_show_depassement}}
            <th class="narrow">{{mb_title class=CActeCCAM field=montant_depassement}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=motif_depassement}}</th>
        {{/if}}
        <th colspan="2">Actions</th>
    </tr>

    {{assign var=count_codes_codage value=0}}
    {{assign var=count_actes_non_codes_codage value=0}}
    {{foreach from=$subject->_ext_codes_ccam item=_code key=_key}}
        {{assign var=display_code value=1}}
        {{foreach from=$_code->activites item=_activite}}
            {{assign var="numero" value=$_activite->numero}}
            {{foreach from=$_activite->phases item=_phase}}
                {{assign var="acte" value=$_phase->_connected_acte}}
                {{assign var="view" value=$acte->_id|default:$acte->_view|cat:''|uniqid}}
                {{assign var="key" value="$_key$view"}}
                {{if (!$acte->_id || ($acte->executant_id == $codage->praticien_id && $acte->_id|@array_key_exists:$codage->_ref_actes_ccam)) &&
                (($_activite->numero != '4' && !$codage->activite_anesth) || ($_activite->numero == '4' && $codage->activite_anesth)) && $acte->_display}}
                    {{math assign=count_codes_codage equation="x+1" x=$count_codes_codage}}
                    {{if !$acte->_id}}
                        {{math assign=count_actes_non_codes_codage equation="x+1" x=$count_actes_non_codes_codage}}
                    {{/if}}
                    <script type="application/javascript">
                        Main.add(function () {
                            var dates = {};
                            {{assign var=_date_min value=false}}
                            {{assign var=_date_max value=false}}
                            {{if $subject->_class == 'COperation'}}
                            {{assign var=_date_min value='Ox\Core\CMbDT::date'|static_call:'-1 day':$codage->date}}
                            {{assign var=_date_max value='Ox\Core\CMbDT::date'|static_call:'+2 day':$codage->date}}
                            {{elseif 'CSejour' === $subject->_class}}
                            {{assign var=_date_min value=$codage->date}}
                            {{assign var=_date_max value=$codage->date}}
                            {{/if}}

                            {{if ("pyxVital"|module_active && 'pyxVital General mode'|gconf != 'test') && $_date_min && $_date_max}}
                            dates.limit = {
                                start: '{{$_date_min|iso_date}}',
                                stop:  '{{$_date_max|iso_date}}'
                            };
                            {{/if}}

                            var oForm = getForm("codageActeExecution-{{$view}}");
                            if (oForm) {
                                Calendar.regField(oForm.execution, dates);
                                if ($V(oForm.execution) == "now") {
                                    $V(oForm.execution_da, 'Maintenant');
                                }
                            }
                            checkModificateurs('{{$view}}');
                        });
                    </script>
                {{if $display_code}}
                    {{assign var=display_code value=0}}
                    {{assign var=can_delete value=1}}
                    {{foreach from=$_code->activites item=__activite}}
                        {{foreach from=$__activite->phases item=__phase}}
                            {{if $can_delete && $__phase->_connected_acte->_id}}
                                {{assign var=can_delete value=0}}
                            {{/if}}
                        {{/foreach}}
                    {{/foreach}}
                    <tr>
                        <th class="section" colspan="15" style="text-align: left;">
                <span style="float: right;">
                  {{if is_array($_code->assos) && count($_code->assos) > 0}}
                      {{unique_id var=uid_autocomplete_comp}}
                      <form name="addAssoCode{{$uid_autocomplete_comp}}" method="get" onsubmit="return false;">
                    <input type="text" size="8em" name="keywords" placeholder="{{$_code->assos|@count}} cmp./sup."
                           onclick="$V(this, '');"/>
                  </form>
                      <div
                        style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
                        class="autocomplete" id="_ccam_add_comp_autocomplete_{{$_code->code}}">
                  </div>
                      <script>
                    Main.add(function () {
                        var form = getForm("addAssoCode{{$uid_autocomplete_comp}}");
                        var url = new Url("dPccam", "autocompleteAssociatedCcamCodes");
                        url.addParam("code", "{{$_code->code}}");
                        url.autoComplete(form.keywords, '_ccam_add_comp_autocomplete_{{$_code->code}}', {
                            minChars:      2,
                            dropdown:      true,
                            width:         "250px",
                            updateElement: function (selected) {
                                CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
                            }
                        });
                    });
                  </script>
                  {{/if}}

                    {{if $can_delete}}
                        <button type="button" class="trash notext"
                                onclick="CCAMField{{$subject->_class}}{{$subject->_id}}.remove('{{$_code->code}}', true)">
                    {{tr}}Delete{{/tr}}
                  </button>
                    {{/if}}
                </span>
                            <p onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}')"
                               style="cursor: pointer;{{if $_code->type == 2}} color: #444;{{/if}} min-height: 22px; vertical-align: middle; overflow-wrap: break-word; margin-right: 5px;">
                                {{$_code->code}} : {{$_code->libelleLong}}
                                {{if $_code->forfait}}
                                    <span class="circled"
                                          title="{{tr}}CDatedCodeCCAM.forfait.{{$_code->forfait}}-desc{{/tr}}"
                                          style="color: firebrick; border-color: firebrick; cursor: help;">
                      {{tr}}CDatedCodeCCAM.forfait.{{$_code->forfait}}{{/tr}}
                    </span>
                                {{/if}}
                            </p>
                        </th>
                    </tr>
                {{/if}}
                    <tr {{if !$acte->_id}}class="activite-{{$acte->code_activite}}"{{/if}}>
                        <td class="narrow">
              <span class="circled {{if $acte->_id}}ok{{else}}error{{/if}}">
                {{mb_value object=$acte field=code_activite}}-{{mb_value object=$acte field=code_phase}}
              </span>
                        </td>
                        {{if $subject->_class != 'CConsultation' && $subject->_class != 'CDevisCodage'}}
                            <td class="narrow">
                                {{if $_code->extensions|@count}}
                                    <form name="codageActeExtensionPMSI-{{$view}}" action="?" method="post"
                                          onsubmit="return false;" class="prepared">
                                        {{if 'dPccam codage pmsi_extension_mandatory'|gconf}}
                                            <label for="codageActeExtensionPMSI-{{$view}}_code_extension"
                                                   class="notNull"></label>
                                            <script type="text/javascript">
                                                Main.add(function () {
                                                    getForm('codageActeExtensionPMSI-{{$view}}').elements['code_extension'].observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
                                                    getForm('codageActeExtensionPMSI-{{$view}}').elements['code_extension'].fire('ui:change');
                                                });
                                            </script>
                                        {{/if}}
                                        <select id="codageActeExtensionPMSI-{{$view}}_code_extension"
                                                name="code_extension" style="width: 4em;"
                                                onchange="syncCodageField(this, '{{$view}}');"{{if 'dPccam codage pmsi_extension_mandatory'|gconf}} class="notNull"{{/if}}{{if $acte->_billed}} disabled{{/if}}>
                                            <option value=""{{if !$acte->code_extension}} selected="selected"{{/if}}>
                                                &mdash;
                                            </option>
                                            {{foreach from=$_code->extensions item=_extension}}
                                                <option
                                                  value="{{$_extension->extension}}"{{if $acte->code_extension == $_extension->extension}} selected="selected"{{/if}}>
                                                    {{$_extension->extension}} - {{$_extension->name}}
                                                </option>
                                            {{/foreach}}
                                        </select>
                                    </form>
                                {{/if}}
                            </td>
                        {{/if}}
                        <td>
                            {{mb_value object=$acte field=_tarif_base}}
                        </td>
                        <td>
                            <form name="codageActeFacturable-{{$view}}" action="?" method="post"
                                  onsubmit="return false;" class="prepared">
                                {{mb_field object=$acte field=facturable typeEnum="select" onchange="syncCodageField(this, '$view');" readonly=$acte->_billed}}
                            </form>
                        </td>
                        <td
                          {{if $acte->_id && ($acte->code_association != $acte->_guess_association)}}style="background-color: #fc9"{{/if}}>
                            {{if $acte->_id}}
                                <form name="codageActeCodeAssociation-{{$view}}" action="?" method="post"
                                      onsubmit="return false;" class="prepared">
                                    {{mb_field object=$acte field=code_association emptyLabel="CActeCCAM.code_association." onchange="syncCodageField(this, '$view');" readonly=$acte->_billed}}
                                </form>
                                {{if $acte->code_association != $acte->_guess_association}}
                                    ({{$acte->_guess_association}})
                                {{/if}}
                            {{/if}}
                        </td>
                        <td
                          class="greedyPane{{if !$_phase->_modificateurs|@is_countable || !$_phase->_modificateurs|@count}} empty{{/if}}">
                            {{assign var=nb_modificateurs value=$acte->modificateurs|strlen}}
                            {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                                <span
                                  class="circled {{if $_mod->_state == 'prechecked'}}ok{{elseif ($_mod->_checked && in_array($_mod->_state, array('not_recommended', 'forbidden')) && $_mod->code != 'K') || $_mod->_montant == '0'}}error{{elseif in_array($_mod->_state, array('not_recommended', 'forbidden'))}}warning{{/if}}"
                                  title="{{$_mod->libelle}} ({{$_mod->_montant}})">
                  <label for="modificateur_{{$_mod->code}}{{$_mod->_double}}">
                    <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}"
                           data-state="{{$_mod->_state}}"
                           {{if $_mod->_checked}} checked="checked"{{/if}}{{if $_mod->_montant == 0 || $nb_modificateurs == 4 || $_mod->_state == 'forbidden' || (intval($acte->_exclusive_modifiers) > 0 && in_array($_mod->code, array('F', 'U', 'P', 'S', 'O'))) || !$acte->facturable || $acte->_billed}} disabled="disabled"{{/if}}
                           data-acte="{{$view}}" data-code="{{$_mod->code}}" data-price="{{$_mod->_montant}}"
                           data-double="{{$_mod->_double}}" class="modificateur"
                           onchange="syncCodageField(this, '{{$view}}');"{{if $acte->_billed}} data-billed="true"{{/if}}/>
                    {{$_mod->code}}
                  </label>
                </span>
                                {{foreachelse}}
                                <em>{{tr}}None{{/tr}}</em>
                            {{/foreach}}
                        </td>
                        <td class="narrow">
                            {{if $acte->code_activite == 4}}
                                <form name="codageActeExtDoc-{{$view}}" action="?" method="post"
                                      onsubmit="return false;" class="prepared">
                                    {{if 'dPccam codage doc_extension_mandatory'|gconf}}
                                        <label for="codageActeExtDoc-{{$view}}_extension_documentaire"
                                               class="notNull"></label>
                                        <script type="text/javascript">
                                            Main.add(function () {
                                                var field = getForm('codageActeExtDoc-{{$view}}').elements['extension_documentaire'];
                                                field.addClassName('notNull');
                                                field.observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
                                                field.fire('ui:change');
                                            });
                                        </script>
                                    {{/if}}
                                    {{mb_field object=$acte field=extension_documentaire emptyLabel="CActeCCAM.extension_documentaire." onchange="syncCodageField(this, '$view');" style="width: 13em;" readonly=$acte->_billed id="codageActeExtDoc-$view"|cat:'_extension_documentaire'}}
                                </form>
                            {{/if}}
                        </td>
                        {{if ($subject->_class == 'CConsultation' && $subject->concerne_ALD) || ($subject->_class == 'CSejour' && $subject->ald)}}
                            <td
                              class="narrow">{{mb_field object=$acte field=ald typeEnum='select' onchange="syncCodageField(this, '$view');"}}</td>
                        {{/if}}
                        <td class="narrow"
                            style="text-align: right;{{if $acte->_id && !$acte->facturable}} background-color: #fc9;{{/if}}">
                            {{mb_value object=$acte field=_tarif}}
                        </td>
                        <td>
                            <form name="codageActeExecution-{{$view}}" action="?" method="post"
                                  onsubmit="return false;">
                                {{if !$acte->_billed}}
                                    {{mb_field object=$acte field=execution form="codageActeExecution-$view" onchange="syncCodageField(this, '$view');"}}
                                {{else}}
                                    {{mb_value object=$acte field=execution}}
                                {{/if}}
                            </form>
                        </td>
                        {{if $codage->_show_depassement}}
                            <td>
                                <form name="codageActeMontantDepassement-{{$view}}" action="?" method="post"
                                      onsubmit="return false;" class="prepared">
                                    {{if $acte->_billed}}
                                        {{mb_field object=$acte field=montant_depassement onchange="onChangeDepassement(this, '$view');"
                                        size=5 readonly=true}}
                                    {{else}}
                                        {{mb_field object=$acte field=montant_depassement
                                        onchange="onChangeDepassement(this, '$view');checkDhField('$view');" size=5}}
                                    {{/if}}
                                </form>
                            </td>
                            <td>
                                <form name="codageActeMotifDepassement-{{$view}}" action="?" method="post"
                                      onsubmit="return false;" class="prepared">
                                    {{mb_field object=$acte field=motif_depassement emptyLabel="CActeCCAM-motif_depassement" onchange="syncCodageField(this, '$view');" style="width: 13em;" readonly=$acte->_billed}}
                                </form>
                            </td>
                        {{/if}}
                        <td>
                            <form
                              name="codageActe-{{$view}}"{{if !$acte->_id}} class="new-act-form-{{$codage->_id}}"{{/if}}
                              action="?" method="post" data-view="{{$view}}" onsubmit="return submitFormAct(this);"
                              class="prepared">
                                <input type="hidden" name="m" value="salleOp"/>
                                <input type="hidden" name="dosql" value="do_acteccam_aed"/>
                                <input type="hidden" name="del" value="0"/>
                                {{mb_key object=$acte}}

                                <input type="hidden" name="_calcul_montant_base" value="1"/>
                                <input type="hidden" name="_edit_modificateurs" value="1"/>

                                {{mb_field object=$acte field=object_id hidden=true value=$subject->_id}}
                                {{mb_field object=$acte field=object_class hidden=true value=$subject->_class}}
                                {{mb_field object=$acte field=code_acte hidden=true}}
                                {{mb_field object=$acte field=code_activite hidden=true}}
                                {{if 'dPccam codage pmsi_extension_mandatory'|gconf && $_code->extensions|@count && $subject->_class != 'CConsultation' && $subject->_class != 'CDevisCodage'}}
                                    {{mb_field object=$acte field=code_extension hidden=true class=" notNull"}}
                                {{else}}
                                    {{mb_field object=$acte field=code_extension hidden=true}}
                                {{/if}}
                                {{mb_field object=$acte field=code_phase hidden=true}}
                                {{mb_field object=$acte field=code_association hidden=true emptyLabel="None"}}
                                {{if ($subject->_class == 'CConsultation' && $subject->concerne_ALD) || ($subject->_class == 'CSejour' && $subject->ald)}}
                                    {{mb_field object=$acte field=ald hidden=true}}
                                {{/if}}
                                {{mb_field object=$acte field=executant_id hidden=true value=$codage->praticien_id}}
                                {{mb_field object=$acte field=execution hidden=true}}
                                {{if $codage->_show_depassement}}
                                    {{mb_field object=$acte field=montant_depassement hidden=true}}
                                    {{mb_field object=$acte field=motif_depassement hidden=true emptyLabel="CActeCCAM-motif_depassement"}}
                                {{/if}}
                                {{mb_field object=$acte field=facturable hidden=true onchange="setFacturableAuto(this)"}}
                                {{mb_field object=$acte field=facturable_auto hidden=true}}
                                {{if 'dPccam codage doc_extension_mandatory'|gconf && $acte->code_activite == 4}}
                                    {{mb_field object=$acte field=extension_documentaire hidden=true class=" notNull"}}
                                {{else}}
                                    {{mb_field object=$acte field=extension_documentaire hidden=true}}
                                {{/if}}
                                {{mb_field object=$acte field=rembourse hidden=true}}
                                {{if $_phase->nb_dents}}
                                    {{mb_field object=$acte field=position_dentaire hidden=true}}
                                {{/if}}
                                {{if !$acte->_id}}
                                    {{mb_field object=$acte field="_total_saisi" placeholder="Total" onchange="checkDhField('$view')"}}
                                {{/if}}
                                {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                                    <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}"
                                           {{if $_mod->_checked}}checked="checked"{{/if}} style="display: none;"/>
                                {{/foreach}}

                                {{if !$acte->_id}}
                                    <button class="add notext compact singleclick" type="button" onclick="
                                    {{if $_activite->anesth_comp && !$_activite->anesth_comp|in_array:$subject->_codes_ccam}}addActeAnesthComp('{{$_activite->anesth_comp}}', {{'dPccam codage add_acte_comp_anesth_auto'|gconf}}); {{/if}}
                                      {{if $_code->remboursement == 2 || $_code->remboursement == 3}}showAlerteRemboursement('{{$_code->code}}', {{$_code->remboursement}}, this.form, '{{$view}}'); {{else}}this.form.onsubmit();{{/if}}">
                                        {{tr}}Add{{/tr}}
                                    </button>
                                {{else}}
                                    {{if $codage->codable_class == 'CSejour'}}
                                        <button type="button" class="notext copy compact"
                                                onclick="duplicateCodage({{$codage->_id}}, {{$acte->_id}});"
                                                title="{{tr}}CCodageCCAM-action-duplicate{{/tr}}">
                                            {{tr}}CCodageCCAM-action-duplicate{{/tr}}
                                        </button>
                                    {{/if}}
                                    <button class="edit notext compact" type="button"
                                            onclick="ActesCCAM.edit({{$acte->_id}})"{{if $acte->_billed}} disabled{{/if}}>{{tr}}Edit{{/tr}}</button>
                                    <button class="remove notext compact" type="button"
                                            onclick="confirmDeletion(this.form,{typeName:'l\'acte',objName:'{{$acte->_view|smarty:nodefaults|JSAttribute}}', ajax: '1'},
                                              {onComplete: function() {window.urlCodage.refreshModal()}});"{{if $acte->_billed}} disabled{{/if}}>
                                        {{tr}}Remove{{/tr}}
                                    </button>
                                {{/if}}
                            </form>
                        </td>
                        <td class="narrow">
                            {{if $acte->_id}}
                                {{mb_include module=system template=inc_object_history object=$acte}}
                            {{/if}}
                        </td>
                    </tr>
                {{/if}}
            {{/foreach}}
        {{/foreach}}
    {{/foreach}}
    {{if !$count_codes_codage}}
        <tr>
            <td colspan="15" class="empty">
                {{tr}}CActeCCAM.none{{/tr}}
            </td>
        </tr>
    {{else}}
        <tr>
            <th class="category" colspan="6" style="text-align: right;">
                Montant total
            </th>
            <th class="category" colspan="5" style="text-align: left;">
                {{mb_value object=$codage field=_total}}
            </th>
            <th class="category" colspan="2" style="text-align: left;">
                {{if $count_actes_non_codes_codage}}
                    <button type="button" class="singleclick add notext" onclick="submitActs('{{$codage->_id}}');">
                        {{tr}}CActeCCAM-action-create-multiple{{/tr}}
                    </button>
                {{/if}}
            </th>
        </tr>
    {{/if}}
</table>

{{if $codage->locked != '1'}}
    <div style="text-align: center; margin-top: 10px;">
        <button type="button" class="tick"
                {{if !$codage->_ref_actes_ccam|@count && (!$codage->_codage_sibling || ($codage->_codage_sibling->_id && !$codage->_codage_sibling->_ref_actes_ccam|@count))}}disabled="disabled"{{/if}}
                onclick=" Control.Modal.close(); lockCodages({{$codage->praticien_id}}, '{{$codage->codable_class}}', {{$codage->codable_id}}, {{if $codage->codable_class == 'CSejour'}}'{{$codage->date}}', {{/if}}'{{'dPccam codage export_on_codage_lock'|gconf}}');">
            Valider le codage
        </button>
    </div>
{{/if}}

<br style="margin: 10px;"/>

<ul id="rules-tab_codage-{{$codage->_id}}" class="control_tabs">
    <li><a href="#questionRules_codage-{{$codage->_id}}">Informations médicales</a></li>
    <li><a href="#concreteRules_codage-{{$codage->_id}}">Règles de codage</a></li>
    <li>
        <input type="checkbox" name="_association_mode" value="manuel"
               {{if $codage->association_mode == "user_choice"}}checked="checked"{{/if}}
               onchange="changeCodageMode(this, {{$codage->_id}});"/>
        Mode manuel pour les règles d'association
    </li>
    <li style="float: right;" class="me-tabs-flex">
        {{* Formulaire ALD/C2S *}}
        {{if 'dPccam codage display_ald_c2s'|gconf && ($subject->_class == 'COperation' || $subject->_class == 'CSejour')}}
            {{assign var=sejour value=$subject}}
            {{assign var=patient value=$subject->_ref_patient}}
            {{if $subject->_class == 'COperation'}}
                {{assign var=sejour value=$subject->_ref_sejour}}
            {{/if}}
            <form name="patAldForm" method="post" onsubmit="return onSubmitFormAjax(this)">
                <input type="hidden" name="m" value="dPpatients"/>
                <input type="hidden" name="dosql" value="do_patients_aed"/>
                <input type="hidden" name="del" value="0"/>
                <input type="hidden" name="patient_id" value="">
                <input type="hidden" name="ald" value="">
                <input type="hidden" name="c2s" value="">
                <input type="hidden" name="acs" value="">
            </form>
            <form name="editSejour" method="post" onsubmit="return onSubmitFormAjax(this)">
                <input type="hidden" name="m" value="planningOp">
                <input type="hidden" name="dosql" value="do_sejour_aed">
                <input type="hidden" name="patient_id" value="{{$sejour->patient_id}}">
                {{mb_key object=$sejour}}
                <table class="">
                    {{mb_include module=planningOp template=inc_check_ald patient=$patient onchange="this.form.onsubmit()" circled=false}}
                </table>
            </form>
        {{/if}}
    </li>
</ul>

<div id="questionRules_codage-{{$codage->_id}}" style="display: none;">
    <form name="questionRulesForm_codage-{{$codage->_id}}" action="?" method="post" onsubmit="return false;">
        <table class="tbl">
            <tr>
                <th class="title" colspan="2">Les actes que vous codez répondent-ils à un des critères suivants ?</th>
            </tr>
            <tr>
                <th class="category" colspan="2">Pour les interventions chirurgicales</th>
            </tr>
            {{if isset($codage->_possible_rules.EA|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EA}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EA"
                               {{if $codage->association_rule == "EA"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les actes portent sur :
                        <ul>
                            <li><strong>des membres différents ou</strong></li>
                            <li><strong>le tronc et un membre ou</strong></li>
                            <li><strong>la tête et un membre.</strong></li>
                        </ul>
                    </td>
                </tr>
            {{/if}}
            {{if isset($codage->_possible_rules.EB|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EB}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EB"
                               {{if $codage->association_rule == "EB"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les actes visent à traiter des <strong>lésions traumatiques multiples et récentes</strong>
                    </td>
                </tr>
            {{/if}}
            {{if isset($codage->_possible_rules.EC|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EC}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EC"
                               {{if $codage->association_rule == "EC"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les actes décrivent une intervention de <strong>carcinologie ORL</strong> comprenant :
                        <ul>
                            <li>une exérèse et</li>
                            <li>un curage et</li>
                            <li>une reconstruction.</li>
                        </ul>
                    </td>
                </tr>
            {{/if}}
            {{if isset($codage->_possible_rules.EH|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EH}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EH"
                               {{if $codage->association_rule == "EH"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        <strong>Des actes ont précédemment été codés pour ce patient dans cette journée</strong> et les
                        nouveaux actes
                        sont effectués dans un <strong>temps différent et discontinu</strong> des premiers.
                    </td>
                </tr>
            {{/if}}
            {{if isset($codage->_possible_rules.EG6|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EG6}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EG6"
                               {{if $codage->association_rule == "EG6"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les forfaits de <strong>cardiologie</strong>, de <strong>réanimation</strong>, les actes de
                        <strong>surveillance post-op</strong> d'un patient de chirurgie cardiaque avec CEC
                        et les actes <strong>d'accouchements</strong> peuvent être associés à taux plein à un seul des
                        actes introduits par la note "Facturation: éventuellement en supplément"
                    </td>
                </tr>
            {{/if}}
            <tr>
                <th class="category" colspan="2">Pour les actes d'imagerie</th>
            </tr>
            {{if isset($codage->_possible_rules.ED|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.ED}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="ED"
                               {{if $codage->association_rule == "ED"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les actes sont des actes d'<strong>échographie</strong> portant sur <strong>plusieurs régions
                            anatomiques</strong>.
                    </td>
                </tr>
            {{/if}}
            {{if isset($codage->_possible_rules.EE|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EE}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EE"
                               {{if $codage->association_rule == "EE"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les actes sont des actes d'<strong>électromyographie</strong>, de <strong>mesure des vitesses de
                            conduction</strong>, d'<strong>étude des latences et des réflexes</strong> portant sur
                        <strong>plusieurs régions anatomiques</strong>.
                    </td>
                </tr>
            {{/if}}
            {{if isset($codage->_possible_rules.EF|smarty:nodefaults)}}
                <tr>
                    <th class="narrow {{if $codage->_possible_rules.EF}}ok{{/if}}">
                        <input type="radio" name="_association_question" value="EF"
                               {{if $codage->association_rule == "EF"}}checked="checked"{{/if}}
                               onchange="setRule(this, {{$codage->_id}});"/>
                    </th>
                    <td class="text">
                        Les actes sont des actes de <strong>scanographie</strong> portant sur <strong>plusieurs régions
                            anatomiques</strong>.
                    </td>
                </tr>
            {{/if}}
        </table>
    </form>
</div>

<div id="concreteRules_codage-{{$codage->_id}}" style="display: none;">
    <form name="formCodageRules_codage-{{$codage->_id}}" action="?" method="post"
          onsubmit="return onSubmitFormAjax(this, {onComplete: function() {window.urlCodage.refreshModal()}});">
        {{mb_key object=$codage}}
        {{mb_class object=$codage}}
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="association_mode" value="{{$codage->association_mode}}"/>
        <table class="tbl">
            <tr>
                <th class="title" colspan="20">
                    Règles d'association
                </th>
            </tr>
            {{assign var=association_rules value='Ox\Mediboard\Ccam\CCodageCCAM'|static:"association_rules"}}
            {{foreach from=$codage->_possible_rules key=_rulename item=_rule}}
                {{if $_rule || 1}}
                    <tr>
                        <th class="narrow {{if $_rulename == $codage->association_rule}}ok{{/if}}">
                            <input type="radio" name="association_rule" value="{{$_rulename}}"
                                   {{if $_rulename == $codage->association_rule}}checked="checked"{{/if}}
                              {{if $codage->association_mode == "auto"}}disabled="disabled"{{/if}}
                                   onchange="this.form.onsubmit()"/>
                        </th>
                        <td class="{{if $_rule}}ok{{else}}error{{/if}}">
                            {{$_rulename}} {{if $association_rules.$_rulename == 'ask'}}(manuel){{/if}}
                        </td>
                        <td class="text greedyPane">
                            {{tr}}CActeCCAM-regle-association-{{$_rulename}}{{/tr}}
                        </td>
                    </tr>
                {{/if}}
            {{/foreach}}
        </table>
    </form>
</div>
