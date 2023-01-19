{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=obj_guid value=$subject->_guid}}
{{mb_default var=count_codes_ccam value=null}}

{{mb_script module=planningOp script=ccam_selector ajax=true}}

<script>
  editCodage = function(codable_class, codable_id, praticien_id, date) {
    var url = new Url("salleOp", "ajax_edit_codages_ccam");
    url.addParam('codable_class', codable_class);
    url.addParam("codable_id", codable_id);
    url.addParam('praticien_id', praticien_id);
    if (date) {
      url.addParam('date', date);
    }
    url.requestModal(
      -10, -50,
      {onClose: PMSI.reloadActesCCAM.curry(codable_class + '-' + codable_id, '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}})}
    );
    window.urlCodage = url;
  };

  lockCodages = function(praticien_id, codable_class, codable_id, date, export_acts) {
    var url = new Url('ccam', 'lockCodage');
    url.addParam('praticien_id', praticien_id);
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('date', date);
    url.addParam('lock', 1);
    url.addParam('export', export_acts);
    url.requestUpdate('systemMsg', {
      onComplete: PMSI.reloadActesCCAM.curry(codable_class + '-' + codable_id, '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}}),
      method: 'post',
      getParameters: {m: 'ccam',a: 'lockCodage'}
    });
  };

  unlockCodages = function(praticien_id, codable_class, codable_id, date) {
    var url = new Url('ccam', 'lockCodage');
    url.addParam('praticien_id', praticien_id);
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('date', date);
    url.addParam('lock', 0);
    url.requestUpdate('systemMsg', {
      onComplete: PMSI.reloadActesCCAM.curry(codable_class + '-' + codable_id, '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}}),
      method: 'post',
      getParameters: {m: 'ccam',a: 'lockCodage'}
    });
  };

  addActeAnesthComp = function(acte, auto) {
    if (auto || confirm("Voulez-vous ajouter l'acte d'anesthésie complémentaire " + acte + '?')) {
      var on_change = CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange;
      CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange = Prototype.emptyFunction;
      CCAMField{{$subject->_class}}{{$subject->_id}}.add(acte, true);
      onSubmitFormAjax(getForm('addActes-{{$subject->_guid}}'));
      CCAMField{{$subject->_class}}{{$subject->_id}}.options.onChange = on_change;
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

  {{if !$modal}}
    Main.add(function() {
      // Mise à jour du compteur et de la classe du volet correspondant
      var span = $("count_actes_{{$obj_guid}}");
      span.update("{{$subject->_count_actes}}");
      if ({{$subject->_count_actes}} == 0) {
        span.up("a").addClassName("empty");
      }
      else {
        span.up("a").removeClassName("empty");
      }
    });
  {{/if}}
</script>

<table class="tbl">
  {{if $subject|instanceof:'Ox\Mediboard\PlanningOp\CSejour' && !'dPccam codage allow_ccam_cotation_sejour'|gconf}}
    <tr>
      <td colspan="20">
        <div class="small-info">
            {{tr}}CSejour-msg-cotation_ccam_forbidden{{/tr}}
        </div>
      </td>
    </tr>
  {{/if}}
  <tr>
    <th class="title" colspan="20" style="border-bottom: none;">
      {{if $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation' && !$read_only && $modules.dPpmsi->_can->edit && 'dPccam codage delay_auto_relock'|gconf != '0'}}
        <button type="button" style="float: right;" onclick="PMSI.showCodageCredentials('{{$subject->_class}}', '{{$subject->_id}}');">
          <i class="fas fa-shield-alt"></i>
          {{tr}}COperation-action-manage_codage_access{{/tr}}
        </button>
      {{/if}}
      <form name="addActes-{{$obj_guid}}" method="post" onsubmit="return false;">
        {{if $subject|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
          <input type="hidden" name="m" value="cabinet" />
          <input type="hidden" name="dosql" value="do_consultation_aed" />
        {{elseif $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_planning_aed" />
        {{else}}
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_sejour_aed" />
        {{/if}}
        {{mb_key object=$subject}}

        <input type="hidden" name="_class" value="{{$subject->_class}}" />
        <input type="hidden" name="_chir" value="{{$subject->_praticien_id}}" />
        {{if ($subject->_class=="COperation")}}
          <input type="hidden" name="_anesth" value="{{$subject->_ref_plageop->anesth_id}}" />
        {{/if}}

        {{if !$read_only}}
          <div style="float: left" class="me-margin-right-10">
            <input type="hidden" name="_new_code_ccam" value="" onchange="CCAMField{{$subject->_class}}{{$subject->_id}}.add(this.value, true);"/>

            <button id="didac_actes_ccam_tr_modificateurs" class="search me-tertiary" type="button" onclick="CCAMSelector.init()">
              {{tr}}Search{{/tr}}
            </button>

            {{mb_field object=$subject field="codes_ccam" hidden=true onchange="this.form.onsubmit()"}}
            <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init()" style="width: 12em" value="" class="autocomplete" placeholder="Ajoutez un acte" />
            <div style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
                 class="autocomplete" id="_ccam_autocomplete_{{$subject->_guid}}"></div>
            <script>
              Main.add(function() {
                var form = getForm("addActes-{{$obj_guid}}");
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
                url.autoComplete(form._codes_ccam, "_ccam_autocomplete_{{$obj_guid}}", {
                  minChars: 1,
                  dropdown: true,
                  width: "250px",
                  updateElement: function(selected) {
                    CCAMField{{$subject->_class}}{{$subject->_id}}.add(selected.down("strong").innerHTML, true);
                  }
                });
                CCAMField{{$subject->_class}}{{$subject->_id}} = new TokenField(form.elements["codes_ccam"], {
                  onChange : function() {
                    return onSubmitFormAjax(form, PMSI.reloadActesCCAM.curry('{{$obj_guid}}', '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}}))
                  },
                  sProps : "notNull code ccam",
                  serialize: true
                } );
              })
            </script>
          </div>
        {{/if}}
        {{if !$modal}}
          {{tr}}CActeCCAM{{/tr}}
        {{else}}
          {{if $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
            Actes CCAM de l'<span onmouseover="ObjectTooltip.createEx(this, '{{$subject->_guid}}')">
              {{tr}}{{$subject->_class}}{{/tr}}
            </span>
          {{elseif $subject|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
            Actes CCAM du <span onmouseover="ObjectTooltip.createEx(this, '{{$subject->_guid}}')">
              {{tr}}{{$subject->_class}}{{/tr}}
            </span>
          {{/if}}
        {{/if}}
      </form>
    </th>
  </tr>

  {{if $count_codes_ccam}}
    <tr>
      <td colspan="20">
        {{mb_include module=system template=inc_pagination total=$count_codes_ccam current=$page step=10 change_page="PMSI.reloadActesCCAM.curry('$obj_guid', '$read_only', '$modal', getForm('filterActs-$obj_guid'))"}}
      </td>
    </tr>
  {{/if}}

  <tr>
    <th class="narrow">{{mb_title class=CActeCCAM field=code_activite}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=code_extension}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=_tarif_base}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=executant_id}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=facturable}}</th>
    {{if $subject->_class == 'COperation'}}
      <th class="narrow">{{mb_title class=CActeCCAM field=sent}}</th>
    {{/if}}
    <th class="narrow">{{mb_title class=CActeCCAM field=code_association}}</th>
    <th>{{mb_title class=CActeCCAM field=modificateurs}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=extension_documentaire}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=_tarif}}</th>
    <th class="narrow"></th>
    <th class="narrow">{{mb_title class=CActeCCAM field=execution}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=montant_depassement}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=motif_depassement}}</th>
    <th colspan="2" class="narrow">Actions</th>
  </tr>

  {{assign var=date_min value=false}}
  {{assign var=date_max value=false}}
  {{if $subject->_class == 'CSejour'}}
    {{assign var=date_min value=$subject->entree}}
    {{assign var=date_max value=$subject->sortie}}
  {{elseif $subject->_class == 'COperation'}}
    {{assign var=date_min value='Ox\Core\CMbDT::date'|static_call:'-1 day':$subject->date}}
    {{if $date_min < $subject->_ref_sejour->entree}}
      {{assign var=date_min value=$subject->_ref_sejour->entree}}
    {{/if}}
    {{assign var=date_max value='Ox\Core\CMbDT::date'|static_call:'+2 day':$subject->date}}
    {{if $date_max > $subject->_ref_sejour->sortie}}
      {{assign var=date_max value=$subject->_ref_sejour->sortie}}
    {{/if}}
  {{elseif $subject->_class == 'CConsultation'}}
    {{assign var=date_min value=$subject->_date}}
    {{assign var=date_max value=$subject->_date}}
  {{/if}}
  {{assign var=pref_motif_depassement value=$app->user_prefs.default_qualif_depense}}
  {{foreach from=$subject->_ext_codes_ccam item=_code key=_key}}
    <tr class="codeCCAM-line">
      <th class="section" colspan="16" style="text-align: left;">
        {{if !$read_only}}
          {{assign var=can_delete value=1}}
          {{foreach from=$_code->activites item=_activite}}
            {{foreach from=$_activite->phases item=_phase}}
              {{if $can_delete && $_phase->_connected_acte->_id}}
                {{assign var=can_delete value=0}}
              {{/if}}
            {{/foreach}}
          {{/foreach}}
          <span style="float: right;">
            {{if is_array($_code->assos) && count($_code->assos) > 0}}
              {{unique_id var=uid_autocomplete_comp}}
              <form name="addAssoCode{{$uid_autocomplete_comp}}" method="get" onsubmit="return false;">
              <input type="text" size="27em" name="keywords" placeholder="{{$_code->assos|@count}} cmp./sup." onclick="$V(this, '');"/>
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

            {{if $can_delete}}
              <button type="button" class="trash notext" onclick="CCAMField{{$subject->_class}}{{$subject->_id}}.remove('{{$_code->code}}', true);">
              {{tr}}Delete{{/tr}}
            </button>
            {{/if}}
          </span>
        {{else}}
          {{assign var=can_delete value=0}}
        {{/if}}

        {{assign var=line_height value='16px'}}
        {{if $can_delete}}
          {{assign var=line_height value='22px;'}}
        {{/if}}
        <span onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}')"
              style="cursor: pointer; vertical-align: middle; line-height: {{$line_height}};{{if $_code->type == 2}} color: #444;{{/if}}">
          {{$_code->code}} : {{$_code->libelleLong}}
        </span>
        {{if $_code->forfait}}
          <span class="circled" title="{{tr}}CDatedCodeCCAM.forfait.{{$_code->forfait}}-desc{{/tr}}" style="color: firebrick; border-color: firebrick; cursor: help;">
            {{tr}}CDatedCodeCCAM.forfait.{{$_code->forfait}}{{/tr}}
          </span>
        {{/if}}
      </th>
    </tr>
    {{foreach from=$_code->activites item=_activite}}
      {{foreach from=$_activite->phases item=_phase}}
        {{assign var="acte" value=$_phase->_connected_acte}}
        {{if !$read_only || ($read_only && $acte->_id)}}
          {{assign var=view value=$acte->_id|default:$acte->_view}}
          {{assign var="view" value='PMSI-'|cat:$view}}
          {{assign var="view" value="-`$subject->_guid`"|cat:$view}}
          {{assign var="key" value="$_key$view"}}
          {{assign var=codage value=$acte->_ref_codage_ccam}}
          {{assign var=show_depassement value=true}}
          {{if $read_only && ($subject->_class == 'COperation' || $subject->_class == 'CSejour') && !'Ox\Mediboard\Ccam\CCodageCCAM::getVisibiliteDepassement'|static_call:$acte->executant_id}}
            {{assign var=show_depassement value=false}}
          {{/if}}

          <script>
            Main.add(function() {
              var dates = {};
              {{if $date_min && $date_max}}
                dates.limit = {
                  start: '{{$date_min|iso_date}}',
                  stop: '{{$date_max|iso_date}}'
                };
              {{/if}}

              var oForm = getForm("codageActeExecution-{{$view}}");
              if (oForm) {
                Calendar.regField(oForm.execution, dates);
              }
            });
          </script>
          <tr{{if $acte->_id}} class="acteCCAM-line" data-view="{{$view}}"{{/if}}>
            <td class="narrow" style="padding-left: 10px;">
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
                  <select name="code_extension" style="width: 4em;" onchange="CCodageCCAM.syncCodageField(this, '{{$view}}');"{{if 'dPccam codage pmsi_extension_mandatory'|gconf}} class="notNull"{{/if}}{{if $acte->_billed}} disabled{{/if}}>
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
              {{if $read_only}}
                {{mb_value object=$acte field=executant_id}}
              {{else}}
                <select name="executant_id" onchange="CCodageCCAM.syncCodageField(this, '{{$view}}');" style="width: 12em;">
                  <option value="">&mdash; Choisir</option>
                  {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats selected=$acte->executant_id}}
                </select>
                {{if $acte->executant_id && $acte->_id}}
                  <button type="button" class="notext edit me-secondary" onclick="editCodage('{{$subject->_class}}', {{$subject->_id}}, {{$acte->executant_id}}, '{{'Ox\Core\CMbDT::date'|static_call:$acte->execution}}');"
                          title="Modifier le codage">
                    {{tr}}Edit{{/tr}}
                  </button>
                  {{if $codage->_id && $codage->locked == '0' && @$modules.dPpmsi->_can->edit}}
                    <button type="button" class="tick notext me-tertiary" onclick="lockCodages({{$codage->praticien_id}}, '{{$codage->codable_class}}', {{$codage->codable_id}}, '{{$codage->date}}', '{{'dPccam codage export_on_codage_lock'|gconf}}');">
                      {{tr}}CCodageCCAM-action-lock{{/tr}}{{if $subject->_class == 'CSejour'}} {{tr}}date.from{{/tr}} {{$codage->date|date_format:$conf.date}}{{/if}}
                    </button>
                  {{elseif $codage->_id && $codage->locked == '1' && @$modules.dPpmsi->_can->edit}}
                    <button type="button" class="cancel notext me-tertiary me-dark" onclick="unlockCodages({{$codage->praticien_id}}, '{{$codage->codable_class}}', {{$codage->codable_id}}, '{{$codage->date}}');">
                      {{tr}}CCodageCCAM-action-unlock{{/tr}}{{if $subject->_class == 'CSejour'}} {{tr}}date.from{{/tr}} {{$codage->date|date_format:$conf.date}}{{/if}}
                    </button>
                  {{/if}}
                {{/if}}
              {{/if}}
            </td>
            <td>
              {{if $read_only}}
                {{mb_value object=$acte field=facturable}}
              {{else}}
                <form name="codageActeFacturable-{{$view}}" action="?" method="post" onsubmit="return false;">
                  {{mb_field object=$acte field=facturable typeEnum="select" onchange="CCodageCCAM.syncCodageField(this, '$view');" readonly=$acte->_billed}}
                </form>
              {{/if}}
            </td>
            {{if $subject->_class == 'COperation'}}
              <td>
                {{if $acte->_id}}
                  {{mb_value object=$acte field=sent}}
                {{/if}}
              </td>
            {{/if}}
            <td
              {{if $acte->_id && ($acte->code_association != $acte->_guess_association)}}style="background-color: #fc9"{{/if}}>
              {{if $read_only}}
                {{mb_value object=$acte field=code_association}}
              {{else}}
                {{if $acte->_id}}
                  <form name="codageActeCodeAssociation-{{$view}}" action="?" method="post" onsubmit="return false;">
                    {{mb_field object=$acte field=code_association emptyLabel="CActeCCAM.code_association." onchange="CCodageCCAM.syncCodageField(this, '$view');" readonly=$acte->_billed}}
                  </form>
                  {{if $acte->code_association != $acte->_guess_association}}
                    ({{$acte->_guess_association}})
                  {{/if}}
                {{/if}}
              {{/if}}
            </td>
            <td class="greedyPane{{if !$_phase->_modificateurs|@is_countable || !$_phase->_modificateurs|@count}} empty{{/if}}">
              {{assign var=nb_modificateurs value=$acte->modificateurs|strlen}}
              {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                <span class="circled {{if $_mod->_state == 'prechecked'}}ok{{elseif $_mod->_checked && in_array($_mod->_state, array('not_recommended', 'forbidden')) || $_mod->_montant == '0'}}error{{elseif in_array($_mod->_state, array('not_recommended', 'forbidden'))}}warning{{/if}}"
                      title="{{$_mod->libelle}} ({{$_mod->_montant}})" {{if $read_only && !$_mod->_checked}}style="color: grey;"{{/if}}>
                          {{if !$read_only}}
                            <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}" {{if $_mod->_checked}}checked{{elseif $_mod->_montant == 0 || $nb_modificateurs == 4 || $_mod->_state == 'forbidden' || (intval($acte->_exclusive_modifiers) > 0 && in_array($_mod->code, array('F', 'U', 'P', 'S'))) || !$acte->facturable || $acte->_billed}}disabled="disabled"{{/if}}
                                   data-acte="{{$view}}" data-code="{{$_mod->code}}" data-price="{{$_mod->_montant}}" data-double="{{$_mod->_double}}" class="modificateur me-small" onchange="CCodageCCAM.syncCodageField(this, '{{$view}}');"{{if $acte->_billed}} data-billed="true"{{/if}}/>
                          {{/if}}
                  <label for="modificateur_{{$_mod->code}}{{$_mod->_double}}">
                    {{$_mod->code}}
                  </label>
                </span>
              {{foreachelse}}
                <em>{{tr}}None{{/tr}}</em>
              {{/foreach}}
              <script type="text/javascript">
                Main.add(function() {
                  CCodageCCAM.checkModificateurs('{{$view}}');
                });
              </script>
            </td>
            <td class="narrow">
              {{if $acte->code_activite == 4}}
                {{if $read_only}}
                  {{mb_value object=$acte field=extension_documentaire}}
                {{else}}
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
                    {{mb_field object=$acte
                               field=extension_documentaire
                               emptyLabel="CActeCCAM.extension_documentaire."
                               onchange="CCodageCCAM.syncCodageField(this, '$view');"
                               style="width: 13em;"
                               class=$class_ext_doc
                               readonly=$acte->_billed}}
                  </form>
                {{/if}}
              {{/if}}
            </td>
            <td style="text-align: right;{{if $acte->_id && !$acte->facturable}} background-color: #fc9{{/if}}">
              {{mb_value object=$acte field=_tarif}}
            </td>
            <td class="narrow">
              {{if $acte->commentaire}}
                <i class="me-icon comment me-primary" title="{{$acte->commentaire}}"></i>
              {{/if}}
            </td>
            <td>
              {{if $read_only || $acte->_billed}}
                {{mb_value object=$acte field=execution}}
              {{else}}
                <form name="codageActeExecution-{{$view}}" action="?" method="post" onsubmit="return false;">
                  {{mb_field object=$acte field=execution form="codageActeExecution-$view" register=true onchange="CCodageCCAM.syncCodageField(this, '$view');"}}
                </form>
              {{/if}}
            </td>
            <td>
              {{if $read_only}}
                {{if $show_depassement}}
                  {{mb_value object=$acte field=montant_depassement}}
                {{/if}}
              {{else}}
                <form name="codageActeMontantDepassement-{{$view}}" action="?" method="post" onsubmit="return false;">
                  {{mb_field object=$acte field=montant_depassement onchange="CCodageCCAM.onChangeDepassement(this, '$view', '$pref_motif_depassement');" size=1 readonly=$acte->_billed}}
                </form>
              {{/if}}
            </td>
            <td>
              {{if $read_only}}
                {{if $show_depassement}}
                  {{mb_value object=$acte field=motif_depassement}}
                {{/if}}
              {{else}}
                <form name="codageActeMotifDepassement-{{$view}}" action="?" method="post" onsubmit="return false;">
                  {{mb_field object=$acte field=motif_depassement emptyLabel="CActeCCAM-motif_depassement" onchange="CCodageCCAM.syncCodageField(this, '$view');" style="width: 10em;" readonly=$acte->_billed}}
                </form>
              {{/if}}
            </td>
            {{if !$read_only}}
              <td>
                <form name="codageActe-{{$view}}" action="?" method="post"
                      onsubmit="return onSubmitFormAjax(this, PMSI.reloadActesCCAM.curry('{{$obj_guid}}', '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}}));">
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
                  {{mb_field object=$acte field=executant_id hidden=true}}
                  {{mb_field object=$acte field=execution hidden=true}}
                  {{mb_field object=$acte field=montant_depassement hidden=true}}
                  {{mb_field object=$acte field=motif_depassement hidden=true emptyLabel="CActeCCAM-motif_depassement"}}
                  {{mb_field object=$acte field=facturable hidden=true onchange="CCodageCCAM.setFacturableAuto(this)"}}
                  {{mb_field object=$acte field=facturable_auto hidden=true}}
                  {{if 'dPccam codage doc_extension_mandatory'|gconf && $acte->code_activite == 4}}
                    {{mb_field object=$acte field=extension_documentaire hidden=true class=" notNull"}}
                  {{else}}
                    {{mb_field object=$acte field=extension_documentaire hidden=true}}
                  {{/if}}
                  {{mb_field object=$acte field=rembourse hidden=true}}

                  {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                    <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}" {{if $_mod->_checked}}checked{{/if}} class="hidden me-no-display" />
                  {{/foreach}}

                  {{if !$acte->_id}}
                    <button class="add notext compact singleclick" type="submit" {{if $_activite->anesth_comp && !$_activite->anesth_comp|in_array:$subject->_codes_ccam}}
                          onclick="addActeAnesthComp('{{$_activite->anesth_comp}}', {{'dPccam codage add_acte_comp_anesth_auto'|gconf}});"{{/if}}>
                      {{tr}}Add{{/tr}}
                    </button>
                  {{else}}
                    <button class="edit notext compact me-secondary" type="button"{{if $acte->_billed}} disabled{{/if}} onclick="CCodageCCAM.editActe({{$acte->_id}}, '{{$subject->_guid}}', {onClose: PMSI.reloadActesCCAM.curry('{{$obj_guid}}', '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}})});">
                        {{tr}}Edit{{/tr}}
                    </button>
                    <button class="trash notext compact me-tertiary" type="button"{{if $acte->_billed}} disabled{{/if}}
                            onclick="confirmDeletion(this.form,{typeName:'l\'acte',objName:'{{$acte->_view|smarty:nodefaults|JSAttribute}}', ajax: '1'},
                              {onComplete: PMSI.reloadActesCCAM.curry('{{$obj_guid}}', '{{$read_only}}', '{{$modal}}', getForm('filterActs-{{$obj_guid}}'), {{if $count_codes_ccam}}0{{else}}null{{/if}})});">
                      {{tr}}Delete{{/tr}}
                    </button>
                  {{/if}}
                </form>
              </td>
            {{/if}}
            <td class="narrow">
              {{if $acte->_id}}
                {{mb_include module=system template=inc_object_history object=$acte}}
              {{/if}}
            </td>
          </tr>
        {{/if}}
      {{/foreach}}
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="20">{{tr}}CActeCCAM.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
