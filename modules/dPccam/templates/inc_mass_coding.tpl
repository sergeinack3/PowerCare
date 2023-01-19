{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPccam" script="code_ccam" ajax=$ajax}}
{{mb_script module="planningOp" script="ccam_selector" ajax=$ajax}}

{{mb_include module=salleOp template=js_codage_ccam do_subject_aed="" object=$subject}}


<script>
  refreshMassCoding = function() {
    Control.Modal.close();
    new Url('ccam', 'massCoding')
      .addParam('model_codage_id', '{{$subject->_id}}')
      .addParam('chir_id', '{{$praticien->_id}}')
      .addParam('object_class', '{{$object_class}}')
      .requestModal(-10, -50, {showClose: 0, showReload: 0, method: 'post', getParameters: {m: 'ccam', a: 'massCoding'}});
  };

  changeCodageMode = function(element, codage_id) {
    var codageForm = getForm("formCodageRules_codage-" + codage_id);
    if($V(element)) {
      $V(codageForm.association_mode, "user_choice");
    }
    else {
      $V(codageForm.association_mode, "auto");
    }
    codageForm.onsubmit();
  };

  onChangeDepassement = function(element, view) {
    {{if $app->user_prefs.default_qualif_depense != ''}}
      if ($V(element)) {
        $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, '{{$app->user_prefs.default_qualif_depense}}');
      }
      else {
        $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, '');
      }
    {{/if}}

    syncCodageField(element, view);
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

  checkModificateurs = function(acte, input) {
    var exclusive_modifiers = ['F', 'P', 'S', 'U', 'O'];
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
      if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
        checkbox.disabled = (!checkbox.checked && nb_checked == 4) || checkbox.get('price') == '0' ||
          (exclusive_modifiers.indexOf(exclusive_modifier) != -1 && exclusive_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && exclusive_modifier_checked);
      }
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

  setRule = function(element, codage_id) {
    var codageForm = getForm("formCodageRules_codage-" + codage_id);
    $V(codageForm.association_mode, "user_choice", false);
    var inputs = document.getElementsByName("association_rule");
    for(var i = 0; i < inputs.length; i++) {
      inputs[i].disabled = false;
    }
    $V(codageForm.association_rule, $V(element), false);
    codageForm.onsubmit();
  };

  switchViewActivite = function(value, activite) {
    if(value) {
      $$('.activite-'+activite).each(function(oElement) {oElement.show()});
    }
    else {
      $$('.activite-'+activite).each(function(oElement) {oElement.hide()});
    }
  };

  addActeAnesthComp = function(acte, auto) {
    if (auto || confirm("Voulez-vous ajouter l'acte d'anesthésie complémentaire " + acte + '?')) {
      var on_change = CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange;
      CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange = Prototype.emptyFunction;
      CCAMField{{$subject->_class}}{{$subject->_id}}.add(acte, true);
      onSubmitFormAjax(getForm('addActes-{{$subject->_guid}}'));
      CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange = on_change;
    }
  }

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
      return onSubmitFormAjax(form, {onComplete: refreshMassCoding.curry()});
    }
  };

  setDents = function(form) {
    var url = new Url('ccam', 'setDentsCodage');
    url.addParam('acte_view', form.get('view'));
    url.addParam('code', $V(form.code_acte));
    url.addParam('activite', $V(form.code_activite));
    url.addParam('phase', $V(form.code_phase));
    url.addParam('date', $V(form.execution));
    url.requestModal();
  };

  Main.add(function() {
    {{if $codages|@count != 1}}
      Control.Tabs.create('codages-tab', true);
    {{/if}}
    Control.Tabs.create('mass_coding_acts', true);
  });
</script>

<ul id="mass_coding_acts" class="control_tabs">
  <li><a href="#mass_coding_ccam_acts">{{tr}}CActeCCAM{{/tr}}</a></li>
  <li><a href="#mass_coding_ngap_acts">{{tr}}CActeNGAP|pl{{/tr}}</a></li>
</ul>
<div id="mass_coding_ccam_acts" style="display: none;">
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
                    return onSubmitFormAjax(form, refreshMassCoding.curry());
                  },
                  sProps : "notNull code ccam"
                } );
              })
            </script>
          </form>
        </div>

        <i class="me-icon warning me-warning"></i>
        Codage en masse de {{$subject->_objects_count}} interventions{{if $subject->libelle != ''}} dont le motif est : <span style="font-style: italic;">{{$subject->libelle}}</span>{{/if}}
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
        {{if !$praticien->spec_cpam_id || !$praticien->secteur}}
          <div>
            <div class="small-warning" style="display: inline-block; max-width: 600px; font-weight: normal; color: black; font-size: 10px; text-shadow: none;">
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
                </a>
              </li>
            {{/foreach}}
            <li>
              Total activités : {{$total|number_format:2:',':' '}} {{$conf.currency_symbol|html_entity_decode}}
            </li>
          </ul>

          {{foreach from=$codages item=_codage}}
            <div id="codage-{{$_codage->_id}}" style="display: none;">
              {{mb_include module=ccam template=inc_mass_coding_edit codage=$_codage}}
            </div>
          {{/foreach}}
        {{else}}
          {{mb_include module=ccam template=inc_mass_coding_edit codage=$codages|@first}}
        {{/if}}
      </td>
    </tr>
  </table>
</div>
<div id="mass_coding_ngap_acts" style="display:none;">
    <div id="listActesNGAP" data-object_id="{{$subject->_id}}" data-object_class="{{$subject->_class}}">
      {{assign var="_object_class" value="CDevisCodage"}}
      {{mb_include module=cabinet template=inc_codage_ngap object=$subject}}
    </div>
</div>

<div style="text-align: center;">
  <form name="applyCodage" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.close();}});">
    <input type="hidden" name="m" value="ccam" />
    <input type="hidden" name="dosql" value="applyModelCodage" />
    <input type="hidden" name="apply" value="1"/>
    <input type="hidden" name="export" value="0"/>
    <input type="hidden" name="model_codage_id" value="{{$subject->_id}}" />
    <input type="hidden" name="object_class" value="{{$object_class}}" />

    <button id="btn_valid_codage" type="button" class="singleclick tick" onclick="this.form.onsubmit();">Appliquer</button>
    <button type="button" class="singleclick tick" onclick="$V(this.form.export, 1); this.form.onsubmit();">Appliquer et exporter</button>
    <button type="button" class="singleclick cancel" onclick="$V(this.form.apply, 0); this.form.onsubmit();">Annuler</button>
  </form>
</div>
