{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPccam"   script="code_ccam"   ajax=$ajax}}
{{mb_script module=planningOp script=ccam_selector register=true}}
{{mb_script module=pmsi       script=PMSI          register=true}}

{{mb_include module=salleOp template=js_codage_ccam do_subject_aed="" object=$subject}}

<script>
  checkModificateurs = function(acte, input) {
    var exclusive_modifiers = ['F', 'P', 'S', 'U'];
    var checkboxes = $$('input[data-acte="' + acte + '"].modificateur');
    var nb_checked = 0;
    var exclusive_modifier = '';
    var exclusive_modifier_checked = false;
    checkboxes.each(function(checkbox) {
      if (checkbox.checked) {
        nb_checked++;
        if (checkbox.get('double') == 2) {
          nb_checked++;
        }
        if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
          exclusive_modifier = checkbox.get('code');
          exclusive_modifier_checked = true;
        }
      }
    });

    checkboxes.each(function(checkbox) {
      checkbox.disabled = (!checkbox.checked && nb_checked == 4) ||
        (exclusive_modifiers.indexOf(exclusive_modifier) != -1 && exclusive_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && exclusive_modifier_checked);
    });

    if (input) {
      var container = input.up();
      if (input.checked && container.hasClassName('warning')) {
        container.removeClassName('warning');
        container.addClassName('error');
      }
      else if (!input.checked && container.hasClassName('error')) {
        container.removeClassName('error');
        container.addClassName('warning');
      }
    }
  };

  syncCodageField = function(element, view) {
    var acteForm = getForm('codageActe-' + view);
    var fieldName = element.name;
    var fieldValue = $V(element);
    $V(acteForm[fieldName], fieldValue);
    if($V(acteForm.acte_id)) {
      acteForm.onsubmit();
    }
    else {
      checkModificateurs(view, element);
    }
  };

  CCAMSelector.init = function() {
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

  submitFormAct = function(form) {
    if ($V(form.acte_id) == '' && form.position_dentaire && $V(form.position_dentaire) == '') {
      setDents(form);
      return false;
    }
    else {
      return onSubmitFormAjax(form, {
        onComplete: PMSI.refreshCoding.curry('{{$subject->_id}}', '{{$rum_id}}')
      });
    }
  };

  {{if $codages|@count != 1}}
    Main.add(function() {
      Control.Tabs.create('codages-tab', true);
    });
  {{/if}}
</script>

<table class="main" style="min-width: 400px; border-spacing: 0px;">
  <tr>
    <th class="title" style="border-bottom: none; border-spacing: 0px;">
      <div style="float: left">
        <form name="addActes-{{$subject->_guid}}" method="post" onsubmit="return false">
          {{mb_class object=$subject}}
          {{mb_key object=$subject}}

          <input type="hidden" name="_class" value="{{$subject->_class}}" />
          <input type="hidden" name="_chir" value="{{$subject->_praticien_id}}" />

          {{mb_field object=$subject field="codes_ccam" hidden=true}}
          <input type="hidden" name="_new_code_ccam" value="" onchange="CCAMField{{$subject->_class}}{{$subject->_id}}.add(this.value, true);"/>

          <button id="didac_actes_ccam_tr_modificateurs" class="search" type="button" onclick="CCAMSelector.init()">
            {{tr}}Search{{/tr}}
          </button>
          <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init()" style="width: 12em" value="" class="autocomplete" placeholder="Ajoutez un acte" />
          <div style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
               class="autocomplete" id="_ccam_autocomplete_{{$subject->_guid}}"></div>
          <script>
            Main.add(function() {
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
                minChars: 1,
                dropdown: true,
                width: "250px",
                updateElement: function(selected) {
                  CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
                }
              });
              CCAMField{{$subject->_class}}{{$subject->_id}} = new TokenField(form.elements["codes_ccam"], {
                onChange : function() {
                  return onSubmitFormAjax(form, PMSI.refreshCoding.curry('{{$subject->_id}}', '{{$rum_id}}'));
                },
                sProps : "notNull code ccam",
                serialize: true
              } );
            })
          </script>
        </form>
      </div>

      {{tr var1=$rum_id}}CRUM-CCAM coding of the RUM number %s{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="title" style="border-top: none; border-spacing: 0px;">
      {{foreach from=$subject->_ext_codes_ccam item=_code}}
        <span id="action-{{$_code->code}}" class="circled" style="background-color: #eeffee; color: black; font-weight: normal; font-size: 0.8em;">
         {{$_code->code}}

          {{if count($_code->assos) > 0}}
            {{unique_id var=uid_autocomplete_comp}}
            <form name="addAssoCode{{$uid_autocomplete_comp}}" method="get" onsubmit="return false;">
              <input type="text" size="8em" name="keywords" value="{{$_code->assos|@count}} cmp./sup." onclick="$V(this, '');"/>
            </form>
            <div style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
                 class="autocomplete" id="_ccam_add_comp_autocomplete_{{$_code->code}}">
            </div>
            <script>
              Main.add(function() {
                var form = getForm("addAssoCode{{$uid_autocomplete_comp}}");
                var url = new Url("dPccam", "autocompleteAssociatedCcamCodes");
                url.addParam("code", "{{$_code->code}}");
                url.autoComplete(form.keywords, '_ccam_add_comp_autocomplete_{{$_code->code}}', {
                  minChars: 2,
                  dropdown: true,
                  width: "250px",
                  updateElement: function(selected) {
                    CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
                  }
                });
              });
            </script>
          {{/if}}

          <button type="button" class="trash notext" onclick="CCAMField{{$subject->_class}}{{$subject->_id}}.remove('{{$_code->code}}', true)">
            {{tr}}Delete{{/tr}}
          </button>
      </span>
      {{/foreach}}
    </th>
  </tr>
  <tr>
    <td>
        {{assign var=codage value=$codages|@first}}
      <div>
        <table class="tbl">
          <tr>
            <th class="narrow">{{mb_title class=CActeCCAM field=code_activite}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=code_extension}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=_tarif_base}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=facturable}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=code_association}}</th>
            <th>{{mb_title class=CActeCCAM field=modificateurs}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=extension_documentaire}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=_tarif}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=execution}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=montant_depassement}}</th>
            <th class="narrow">{{mb_title class=CActeCCAM field=motif_depassement}}</th>
            <th colspan="2">Actions</th>
          </tr>

          {{assign var=count_codes_codage value=0}}
          {{foreach from=$subject->_ext_codes_ccam item=_code key=_key}}
            {{assign var=display_code value=1}}
            {{foreach from=$_code->activites item=_activite}}
              {{assign var="numero" value=$_activite->numero}}
              {{foreach from=$_activite->phases item=_phase}}
                {{assign var="acte" value=$_phase->_connected_acte}}
                {{assign var="view" value=$acte->_id|default:$acte->_view}}
                {{assign var="key" value="$_key$view"}}
                {{math assign=count_codes_codage equation="x+1" x=$count_codes_codage}}

                  <script type="application/javascript">
                    Main.add(function() {
                      var dates = {};
                      dates.limit = {
                        start: '{{$sejour->entree|iso_date}}',
                        stop: '{{$sejour->sortie|iso_date}}'
                      };

                      var oForm = getForm("codageActeExecution-{{$view}}");
                      if (oForm) {
                        Calendar.regField(oForm.execution, dates);
                      }
                      checkModificateurs('{{$view}}');
                    });
                  </script>

                {{if $display_code}}
                  {{assign var=display_code value=0}}
                  <tr>
                    <th class="section" colspan="14" style="text-align: left;">
                      <span onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}')"
                            style="cursor: pointer;{{if $_code->type == 2}} color: #444;{{/if}}">
                        {{$_code->code}} : {{$_code->libelleLong}}
                      </span>
                      {{if $_code->forfait}}
                        <span class="circled" title="{{tr}}CDatedCodeCCAM.forfait.{{$_code->forfait}}-desc{{/tr}}" style="color: firebrick; border-color: firebrick; cursor: help;">
                          {{tr}}CDatedCodeCCAM.forfait.{{$_code->forfait}}{{/tr}}
                        </span>
                      {{/if}}
                    </th>
                  </tr>
                {{/if}}
                  <tr {{if !$acte->_id}}class="activite-{{$acte->code_activite}}"{{/if}}>
                    <td class="narrow">
                      <span class="circled {{if $acte->_id}}ok{{else}}error{{/if}}">
                        {{mb_value object=$acte field=code_activite}}-{{mb_value object=$acte field=code_phase}}
                      </span>
                    </td>
                    <td class="narrow">
                      {{if $_code->extensions|@count}}
                        <form name="codageActeExtensionPMSI-{{$view}}" action="?" method="post" onsubmit="return false;">
                          {{if 'dPccam codage pmsi_extension_mandatory'|gconf}}
                            <label for="code_extension" class="notNull"></label>
                            <script type="text/javascript">
                              Main.add(function() {
                                getForm('codageActeExtensionPMSI-{{$view}}').elements['code_extension'].observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
                              });
                            </script>
                          {{/if}}
                          <select name="code_extension" style="width: 4em;" onchange="syncCodageField(this, '{{$view}}');"{{if 'dPccam codage pmsi_extension_mandatory'|gconf}} class="notNull"{{/if}}>
                            <option value=""{{if !$acte->code_extension}} selected="selected"{{/if}}>&mdash;</option>
                            {{foreach from=$_code->extensions item=_extension}}
                              <option value="{{$_extension->extension}}"{{if $acte->code_extension == $_extension->extension}} selected="selected"{{/if}}>
                                {{$_extension->extension}} - {{$_extension->name}}
                              </option>
                            {{/foreach}}
                          </select>
                        </form>
                      {{/if}}
                    </td>
                    <td>
                      {{mb_value object=$acte field=_tarif_base}}
                    </td>
                    <td>
                      <form name="codageActeFacturable-{{$view}}" action="?" method="post" onsubmit="return false;">
                        {{mb_field object=$acte field=facturable typeEnum="select" onchange="syncCodageField(this, '$view');"}}
                      </form>
                    </td>
                    <td
                      {{if $acte->_id && ($acte->code_association != $acte->_guess_association)}}style="background-color: #fc9"{{/if}}>
                      {{if $acte->_id}}
                        <form name="codageActeCodeAssociation-{{$view}}" action="?" method="post" onsubmit="return false;">
                          {{mb_field object=$acte field=code_association emptyLabel="CActeCCAM.code_association." onchange="syncCodageField(this, '$view');"}}
                        </form>
                        {{if $acte->code_association != $acte->_guess_association}}
                          ({{$acte->_guess_association}})
                        {{/if}}
                      {{/if}}
                    </td>
                    <td class="greedyPane{{if !$_phase->_modificateurs|@count}} empty{{/if}}">
                      {{assign var=nb_modificateurs value=$acte->modificateurs|strlen}}
                      {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                        <span class="circled {{if $_mod->_state == 'prechecked'}}ok{{elseif $_mod->_checked && in_array($_mod->_state, array('not_recommended', 'forbidden'))}}error{{elseif in_array($_mod->_state, array('not_recommended', 'forbidden'))}}warning{{/if}}"
                              title="{{$_mod->libelle}} ({{$_mod->_montant}})">
                          <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}"
                                 {{if $_mod->_checked}}checked="checked"{{elseif $nb_modificateurs == 4 || $_mod->_state == 'forbidden' || (intval($acte->_exclusive_modifiers) > 0 && in_array($_mod->code, array('F', 'U', 'P', 'S'))) || !$acte->facturable}}disabled="disabled"{{/if}}
                                 data-acte="{{$view}}" data-code="{{$_mod->code}}" data-double="{{$_mod->_double}}" class="modificateur" onchange="syncCodageField(this, '{{$view}}');" />
                          <label for="modificateur_{{$_mod->code}}{{$_mod->_double}}">
                            {{$_mod->code}}
                          </label>
                        </span>

                        {{foreachelse}}
                        <em>{{tr}}None{{/tr}}</em>
                      {{/foreach}}
                    </td>
                    <td class="narrow">
                      {{if $acte->code_activite == 4}}
                        <form name="codageActeExtDoc-{{$view}}" action="?" method="post" onsubmit="return false;">
                          {{assign var=class_ext_doc value=''}}
                          {{if 'dPccam codage doc_extension_mandatory'|gconf}}
                            <label for="extension_documentaire" class="notNull"></label>
                            <script type="text/javascript">
                              Main.add(function() {
                                var field = getForm('codageActeExtDoc-{{$view}}').elements['extension_documentaire'];
                                field.addClassName('notNull');
                                field.observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
                              });
                            </script>
                            {{assign var=class_ext_doc value=' notNull'}}
                          {{/if}}
                          {{mb_field object=$acte field=extension_documentaire emptyLabel="CActeCCAM.extension_documentaire." onchange="syncCodageField(this, '$view');" style="width: 13em;" class=$class_ext_doc}}
                        </form>
                      {{/if}}
                    </td>
                    <td class="narrow" style="text-align: right;{{if $acte->_id && !$acte->facturable}} background-color: #fc9;{{/if}}">
                      {{mb_value object=$acte field=_tarif}}
                    </td>
                    <td>
                      <form name="codageActeExecution-{{$view}}" action="?" method="post" onsubmit="return false;">
                        {{mb_field object=$acte field=execution form="codageActeExecution-$view" register=true onchange="CCodageCCAM.syncCodageField(this, '$view');"}}
                      </form>
                    </td>
                    <td>
                      <form name="codageActeMontantDepassement-{{$view}}" action="?" method="post" onsubmit="return false;">
                        {{mb_field object=$acte field=montant_depassement onchange="onChangeDepassement(this, '$view');" size=4}}
                      </form>
                    </td>
                    <td>
                      <form name="codageActeMotifDepassement-{{$view}}" action="?" method="post" onsubmit="return false;">
                        {{mb_field object=$acte field=motif_depassement emptyLabel="CActeCCAM-motif_depassement" onchange="syncCodageField(this, '$view');" style="width: 13em;"}}
                      </form>
                    </td>
                    <td>
                      <form name="codageActe-{{$view}}" action="?" method="post" class="form-act" data-view="{{$view}}" onsubmit="return submitFormAct(this);">
                        <input type="hidden" name="m" value="salleOp" />
                        <input type="hidden" name="dosql" value="do_acteccam_aed" />
                        <input type="hidden" name="del" value="0" />
                        {{mb_key object=$acte}}

                        <input type="hidden" name="_calcul_montant_base" value="1" />
                        <input type="hidden" name="_edit_modificateurs" value="1"/>

                        {{mb_field object=$acte field=object_id hidden=true value=$subject->_id}}
                        {{mb_field object=$acte field=object_class hidden=true value=$subject->_class}}
                        {{mb_field object=$acte field=code_acte hidden=true}}
                        {{mb_field object=$acte field=code_activite hidden=true}}
                        {{if 'dPccam codage pmsi_extension_mandatory'|gconf && $_code->extensions|@count}}
                          {{mb_field object=$acte field=code_extension hidden=true class=" notNull"}}
                        {{else}}
                          {{mb_field object=$acte field=code_extension hidden=true}}
                        {{/if}}
                        {{mb_field object=$acte field=code_phase hidden=true}}
                        {{mb_field object=$acte field=code_association hidden=true emptyLabel="None"}}
                        {{mb_field object=$acte field=executant_id hidden=true value=$codage->praticien_id}}
                        {{mb_field object=$acte field=execution hidden=true}}
                        {{mb_field object=$acte field=montant_depassement hidden=true}}
                        {{mb_field object=$acte field=motif_depassement hidden=true emptyLabel="CActeCCAM-motif_depassement"}}
                        {{mb_field object=$acte field=facturable hidden=true}}
                        {{if 'dPccam codage doc_extension_mandatory'|gconf && $acte->code_activite == 4}}
                          {{mb_field object=$acte field=extension_documentaire hidden=true class=" notNull"}}
                        {{else}}
                          {{mb_field object=$acte field=extension_documentaire hidden=true}}
                        {{/if}}
                        {{mb_field object=$acte field=rembourse hidden=true}}
                        {{if $_phase->nb_dents}}
                          {{mb_field object=$acte field=position_dentaire hidden=true}}
                        {{/if}}

                        {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                          <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}" {{if $_mod->_checked}}checked="checked"{{/if}} hidden="hidden" />
                        {{/foreach}}

                        {{if !$acte->_id}}
                          <button class="add notext compact" type="submit" {{if $_activite->anesth_comp && !$_activite->anesth_comp|in_array:$subject->_codes_ccam}}
                          onclick="addActeAnesthComp('{{$_activite->anesth_comp}}', {{'dPccam codage add_acte_comp_anesth_auto'|gconf}});"{{/if}}>
                            {{tr}}Add{{/tr}}
                          </button>
                        {{else}}
                          <button class="edit notext compact" type="button" onclick="ActesCCAM.edit({{$acte->_id}}, {onClose: PMSI.refreshCoding.curry('{{$subject->_id}}', '{{$rum_id}}')})">{{tr}}Edit{{/tr}}</button>
                          <button class="remove notext compact" type="button"
                                  onclick="confirmDeletion(this.form,{typeName:'l\'acte',objName:'{{$acte->_view|smarty:nodefaults|JSAttribute}}', ajax: '1'},
                                    {onComplete: PMSI.refreshCoding.curry('{{$subject->_id}}', '{{$rum_id}}')});">
                            {{tr}}Remove{{/tr}}
                          </button>
                        {{/if}}
                      </form>
                    </td>
                  </tr>
              {{/foreach}}
            {{/foreach}}
          {{/foreach}}
          {{if !$count_codes_codage}}
            <tr>
              <td colspan="12" class="empty">
                {{tr}}CActeCCAM.none{{/tr}}
              </td>
            </tr>
          {{else}}
            <tr>
              <th class="category" colspan="15"></th>
            </tr>
          {{/if}}
        </table>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button">
      <form name="applyCodage" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.close(); PMSI.loadRSS('{{$sejour->_id}}');}});">
        <input type="hidden" name="m" value="ccam" />
        <input type="hidden" name="dosql" value="applyModelCodage" />
        <input type="hidden" name="apply" value="0"/>
        <input type="hidden" name="export" value="0"/>
        <input type="hidden" name="model_codage_id" value="{{$subject->_id}}" />

        <button type="button" class="tick" onclick="Control.Modal.close(); PMSI.loadRSS('{{$sejour->_id}}');">Appliquer</button>
        <button type="button" class="cancel" onclick="this.form.onsubmit();">Supprimer les actes</button>
      </form>
    </td>
  </tr>
</table>
