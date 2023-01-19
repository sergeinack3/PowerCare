{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPpmsi" script="PMSI" ajax=$ajax}}
{{mb_script module="dPccam" script="code_ccam" ajax=$ajax}}

<script>
  changeCodeToDel = function(subject_id, code_ccam, actes_ids){
    var oForm = getForm("manageCodes");
    $V(oForm._selCode, code_ccam);
    $V(oForm._actes, actes_ids);
    ActesCCAM.remove(subject_id);
  };

  editCodages = function(codable_class, codable_id, praticien_id) {
    var url = new Url("salleOp", "ajax_edit_codages_ccam");
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('praticien_id', praticien_id);
    url.requestModal(
      -10, -50,
      {onClose: function() {ActesCCAM.refreshList('{{$subject->_id}}','{{$subject->_praticien_id}}')}}
    );
    window.urlCodage = url;
  };

  lockCodages = function(praticien_id, codable_class, codable_id, export_acts) {
    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      var url = new Url('ccam', 'checkLockCodage');
    {{else}}
      var url = new Url('ccam', 'lockCodage');
    {{/if}}
    url.addParam('praticien_id', praticien_id);
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('lock', 1);
    url.addParam('export', export_acts);

    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      url.requestModal(null, null, {onClose: ActesCCAM.notifyChange.curry(codable_id, praticien_id)});
    {{else}}
      url.requestUpdate('systemMsg', {onComplete: ActesCCAM.notifyChange.curry(codable_id, praticien_id), method: 'post', getParameters: {m: 'ccam',a: 'lockCodage'}});
    {{/if}}
  };

  unlockCodages = function(praticien_id, codable_class, codable_id) {
    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      var url = new Url('ccam', 'checkLockCodage');
    {{else}}
      var url = new Url('ccam', 'lockCodage');
    {{/if}}
    url.addParam('praticien_id', praticien_id);
    url.addParam('codable_class', codable_class);
    url.addParam('codable_id', codable_id);
    url.addParam('lock', 0);
    {{if "dPccam codage lock_codage_ccam"|gconf == 'password'}}
      url.requestModal(null, null, {onClose: ActesCCAM.notifyChange.curry(codable_id, praticien_id)});
    {{else}}
      url.requestUpdate('systemMsg', {onComplete: ActesCCAM.notifyChange.curry(codable_id, praticien_id), method: 'post', getParameters: {m: 'ccam',a: 'lockCodage'}});
    {{/if}}
  };

  deleteCodages = function(praticien_id) {
    Modal.confirm('Voulez réellement supprimer les codages CCAM de ce praticien?', {
      onOK: function() {
        var forms = $$('form[data-praticien_id=' + praticien_id + ']');
        forms.each(function(form) {
          $V(form.del, 1);
          form.onsubmit();
        });
      }
    })
  };

  CCAMSelector.init = function(){
    this.sForm = "manageCodes";
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

  Main.add(function() {
    var oForm = getForm("manageCodes");
    var url = new Url("dPccam", "autocompleteCcamCodes");
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
    url.autoComplete(oForm._codes_ccam, '', {
      minChars: 1,
      dropdown: true,
      width: "250px",
      updateElement: function(selected) {
        $V(oForm._codes_ccam, selected.down("strong").innerHTML);
        ActesCCAM.add('{{$subject->_id}}','{{$subject->_praticien_id}}');
      }
    });
  });
</script>

{{assign var=codage_rights value='dPccam codage rights'|gconf}}
<!-- Nouvel affichage en se basant sur le codage de chaque praticien -->
<table class="main layout">
  {{if 'Ox\Mediboard\Ccam\CCodable::hasBillingPeriods'|static_call:$subject}}
    <tr>
      <td colspan="2">
          {{mb_include module=ccam template=inc_billing_periods codable=$subject}}
      </td>
    </tr>
  {{/if}}
  <tr>
    <td class="halfPane">
      <fieldset id="didac_inc_manage_codes_fieldset_executant" class="me-no-box-shadow">
        <legend id="didac_actes_ccam_executant">Ajouter un executant</legend>
        <form name="newCodage" action="?" method="post"
              onsubmit="return onSubmitFormAjax(this, {
                onComplete: ActesCCAM.notifyChange.curry({{$subject->_id}},{{$subject->_praticien_id}}) })">
          <input type="hidden" name="@class" value="CCodageCCAM" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="codage_ccam_id" value="" />
          <input type="hidden" name="codable_class" value="{{$subject->_class}}" />
          <input type="hidden" name="codable_id" value="{{$subject->_id}}" />
          {{if $subject->_class == "COperation" || $subject->_class == "CDevisCodage" || $subject->_class == 'CModelCodage'}}
            {{assign var=date_codable value=$subject->date}}
          {{else}}
            {{assign var=date_codable value=$subject->_date}}
          {{/if}}
          <input type="hidden" name="date" value="{{$date_codable}}"/>
          <select name="praticien_id" class="me-float-none" style="width: 20em; float: left;" onchange="this.form.onsubmit();">
            <option value="">&mdash; Choisir un professionnel de santé</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$listChirs}}
          </select>

          {{if $user->_is_praticien && !$user->_id|@array_key_exists:$subject->_ref_codages_ccam}}
            <div style="float: right;">
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user}}
              <button class="add notext singleclick" type="button" title="Ajouter un codage" onclick="$V(this.form.praticien_id, {{$user->_id}});"></button>
            </div>
          {{/if}}
        </form>
        <table class="tbl">
          <tr>
            <th class="category">Praticien</th>
            <th class="category">Actes cotés</th>
            <th class="category me-text-align-center">Actions</th>
          </tr>
          {{foreach from=$subject->_ref_codages_ccam item=_codages_by_prat name=codages}}
            {{assign var=total value=0}}
            {{foreach from=$_codages_by_prat item=_codage name=codages_by_prat}}
              {{math assign=total equation="x+y" x=$total y=$_codage->_total}}
              <tr>
                {{if $smarty.foreach.codages_by_prat.first}}
                  <td rowspan="{{$_codages_by_prat|@count}}">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_codage->_ref_praticien}}</td>
                {{/if}}

                <td {{if !$_codage->_ref_actes_ccam|@count}}class="empty"{{/if}} {{if !$smarty.foreach.codages_by_prat.last}}style="border-bottom: 1pt dotted #93917e;"{{/if}}>
                  {{if !$_codage->_ref_actes_ccam|@count}}
                    {{tr}}CActeCCAM.none{{/tr}}
                  {{else}}
                    <table class="layout">
                      {{foreach from=$subject->_ext_codes_ccam item=_code key=_key}}
                        {{foreach from=$_code->activites item=_activite}}
                          {{foreach from=$_activite->phases item=_phase}}
                            {{if $_phase->_connected_acte->_id && $_phase->_connected_acte->executant_id == $_codage->praticien_id &&
                                 (($_activite->numero != '4' && !$_codage->activite_anesth) || ($_activite->numero == '4' && $_codage->activite_anesth))
                                 && $_codage->date == $_phase->_connected_acte->execution|date_format:'%Y-%m-%d'}}
                              {{assign var =_acte value=$_phase->_connected_acte}}
                              <tr>
                                <td>
                                  <a href="#" onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}');">
                                    {{$_acte->code_acte}}{{if $_acte->code_extension && $subject->_class != 'CConsultation'}}-{{$_acte->code_extension}}{{/if}}
                                  </a>
                                </td>
                                <td>
                                  <span class="circled ok">
                                    {{$_acte->code_activite}}-{{$_acte->code_phase}}
                                  </span>
                                </td>
                                <td>
                                  {{if !is_countable($_phase->_modificateurs) || !$_phase->_modificateurs|@count}}
                                    <em style="color: #7d7d7d;">Aucun modif. dispo.</em>
                                  {{elseif !$_acte->modificateurs}}
                                    <strong>Aucun modif. codé</strong>
                                  {{else}}
                                    {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                                      {{if $_mod->_checked && in_array($_mod->code, $_acte->_modificateurs)}}
                                        <span class="circled {{if in_array($_mod->_state, array('not_recommended', 'forbidden'))}}error{{/if}}"
                                              title="{{$_mod->libelle}}">
                                          {{$_mod->code}}
                                        </span>
                                      {{/if}}
                                    {{/foreach}}
                                  {{/if}}
                                </td>
                                <td>
                                  {{if $_acte->code_association}}
                                  Asso : {{$_acte->code_association}}
                                  {{/if}}
                                </td>
                                {{if $_acte->montant_depassement && $_codage->_show_depassement}}
                                  <td>
                                    <span class="circled" style="background-color: #aaf" title="{{mb_value object=$_acte field=montant_depassement}}">
                                        DH
                                   </span>
                                  </td>
                                {{/if}}
                              </tr>
                            {{/if}}
                          {{/foreach}}
                        {{/foreach}}
                      {{/foreach}}
                    </table>
                  {{/if}}
                  <form name="formCodage-{{$_codage->_id}}" action="?" method="post" data-praticien_id="{{$_codage->praticien_id}}"
                        onsubmit="return onSubmitFormAjax(this{{if $smarty.foreach.codages_by_prat.last}}, {
                        onComplete: ActesCCAM.notifyChange.curry({{$subject->_id}},{{$subject->_praticien_id}}) }{{/if}});">
                    {{mb_class object=$_codage}}
                    {{mb_key object=$_codage}}
                    <input type="hidden" name="del" value="0" />
                    <input type="hidden" name="locked" value="{{$_codage->locked}}" />
                  </form>
                </td>

                {{if $smarty.foreach.codages_by_prat.first}}
                  {{* On compte le nombre d'actes cotés pour ce praticien *}}
                  {{assign var=count_actes_by_prat value=0}}
                  {{section name=count_actes loop=$smarty.foreach.codages_by_prat.total}}
                    {{math assign=count_actes_by_prat equation="x+y" x=$count_actes_by_prat y=$_codages_by_prat[$smarty.section.count_actes.index]->_ref_actes_ccam|@count}}
                  {{/section}}
                  {{assign var=can_edit value=true}}
                  {{if ($codage_rights == 'self' && ($user->_id != $_codage->praticien_id && !@$modules.dPpmsi->_can->edit)) || ($codage_rights != 'self' && !$_codage->_ref_praticien->getPerm(2) && !@$modules.dPpmsi->_can->edit)}}
                    {{assign var=can_edit value=false}}
                  {{/if}}
                  <td rowspan="{{$_codages_by_prat|@count}}" class="button">
                    {{if !$_codage->locked}}
                      <button type="button" class="notext edit me-tertiary" onclick="editCodages('{{$subject->_class}}', {{$subject->_id}}, {{$_codage->praticien_id}})"
                              title="{{$_codage->association_rule}} ({{mb_value object=$_codage field=association_mode}})" {{if !$can_edit}} disabled="disabled"{{/if}}>
                        {{tr}}Edit{{/tr}}
                      </button>
                    {{/if}}

                    {{if $_codage->locked}}
                      <button type="button" class="notext cancel me-tertiary me-dark"
                              onclick="unlockCodages({{$_codage->praticien_id}}, '{{$_codage->codable_class}}', {{$_codage->codable_id}})" {{if !$can_edit}} disabled="disabled"{{/if}}>
                        {{tr}}CCodageCCAM-action-unlock{{/tr}}
                      </button>
                    {{else}}
                      <button type="button" class="notext tick me-secondary" {{if !$count_actes_by_prat}}disabled="disabled"{{/if}}
                              onclick="lockCodages({{$_codage->praticien_id}}, '{{$_codage->codable_class}}', {{$_codage->codable_id}}, '{{'dPccam codage export_on_codage_lock'|gconf}}')" {{if !$can_edit}} disabled="disabled"{{/if}}>
                        {{tr}}CCodageCCAM-action-lock{{/tr}}
                      </button>
                    {{/if}}
                    {{if !$count_actes_by_prat && !$_codage->locked}}
                      <button type="button" class="notext trash me-tertiary me-dark" onclick="deleteCodages({{$_codage->praticien_id}})" {{if !$can_edit}} disabled="disabled"{{/if}}>
                        {{tr}}Delete{{/tr}}
                      </button>
                    {{/if}}
                  </td>
                {{/if}}
              </tr>
              {{if $smarty.foreach.codages_by_prat.last && $total != 0}}
                <tr{{if !$smarty.foreach.codages.last}} style="border-bottom: 1pt dotted #93917e;"{{/if}}>
                  <td colspan="2" style="text-align: right;">
                    Montant total :
                  </td>
                  <td style="text-align: left;">
                    {{$total|number_format:2:',':' '}} {{$conf.currency_symbol|html_entity_decode}}
                  </td>
                </tr>
              {{/if}}
            {{/foreach}}
          {{foreachelse}}
            <tr>
              <td class="empty" colspan="10">{{tr}}CCodageCCAM.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset class="me-no-box-shadow">
        <legend>Ajouter un code</legend>
        <form name="manageCodes" action="?" method="post">
          {{mb_class object=$subject}}
          {{mb_key object=$subject}}
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="codes_ccam" value="{{$subject->codes_ccam}}" />
          <input type="submit" disabled="disabled" style="display:none;"/>
          <input type="hidden" name="_chir" value="{{$subject->_praticien_id}}" />
          {{if ($subject->_class=="COperation")}}
            <input type="hidden" name="_anesth" value="{{if $subject->anesth_id}}{{$subject->anesth_id}}{{else}}{{$subject->_ref_plageop->anesth_id}}{{/if}}" />
          {{/if}}
          <input type="hidden" name="_class" value="{{$subject->_class}}" />
          <span id="didac_actes_ccam_executant"></span>
          <span id="didac_actes_ccam_button_comment" ></span>
          <input name="_actes" type="hidden" value="" />
          <input name="_selCode" type="hidden" value="" />
          <button id="didac_actes_ccam_tr_modificateurs" class="search me-tertiary" type="button" onclick="CCAMSelector.init()">
            {{tr}}Search{{/tr}}
          </button>
          <input type="hidden" name="_new_code_ccam" value="" onchange="$V(this.form._codes_ccam, this.value); ActesCCAM.add('{{$subject->_id}}','{{$subject->_praticien_id}}');"/>
          <span id="didac_actes_ccam_ext_doc"></span>
          <input type="text" size="10" name="_codes_ccam" />
        </form>
        <table class="tbl">
          <tr>
            <th class="category" colspan="10">Actes disponibles</th>
          </tr>

          {{foreach from=$subject->_ext_codes_ccam item=_code key=_key name=codes_ccam}}
            {{assign var=actes_ids value=''}}
            {{if is_array($subject->_associationCodesActes) && is_array($subject->_associationCodesActes.$_key)}}
              {{assign var=actes_ids value=$subject->_associationCodesActes.$_key.ids}}
            {{/if}}
            {{unique_id var=uid_autocomplete_asso}}
            {{assign var=can_delete value=1}}
            {{foreach from=$_code->activites item=_activite}}
              {{foreach from=$_activite->phases item=_phase}}
                {{if $can_delete && $_phase->_connected_acte->_id}}
                  {{assign var=can_delete value=0}}
                {{/if}}
              {{/foreach}}
            {{/foreach}}
            <tr {{if !$smarty.foreach.codes_ccam.last}}style="border-bottom: 1pt dotted #93917e;"{{/if}}>
              <td>
                <a href="#" onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}');">
                  {{$_code->code}}
                </a>
              </td>
              <td>
                {{foreach from=$_code->activites item=_activite}}
                  {{foreach from=$_activite->phases item=_phase}}
                    {{assign var="acte" value=$_phase->_connected_acte}}
                    {{assign var="view" value=$acte->_id|default:$acte->_view}}
                    {{assign var="key" value="$_key$view"}}
                    <form name="formActe-{{$view}}" action="?" method="post" onsubmit="return checkForm(this)">
                      <input type="hidden" name="m" value="dPsalleOp" />
                      <input type="hidden" name="dosql" value="do_acteccam_aed" />
                      <input type="hidden" name="del" value="0" />
                      <input type="hidden" name="acte_id" value="{{$acte->_id}}" />
                      <input type="hidden" name="object_id" value="{{$acte->object_id}}" />
                      <input type="hidden" name="object_class" value="{{$acte->object_class}}" />
                    </form>
                    <span class="circled {{if $_phase->_connected_acte->_id}}ok{{else}}error{{/if}}">
                      {{$_activite->numero}}-{{$acte->code_phase}}
                    </span>
                  {{/foreach}}
                {{/foreach}}
              </td>
              <td class="text">
                {{$_code->libelleLong}}
              </td>
              <td>
                <!-- Actes complémentaires -->
                {{if count($_code->assos) > 0}}
                  <div class="small" style="float:right;">
                    <form name="addAssoCode{{$uid_autocomplete_asso}}" method="get">
                      <input type="text" size="13em" name="keywords" value="&mdash; {{$_code->assos|@count}} comp./supp." onclick="$V(this, '');"/>
                    </form>
                  </div>
                  <script>
                    Main.add(function() {
                      var form = getForm("addAssoCode{{$uid_autocomplete_asso}}");
                      var url = new Url("dPccam", "autocompleteAssociatedCcamCodes");
                      url.addParam("code", "{{$_code->code}}");
                      url.autoComplete(form.keywords, null, {
                        minChars: 2,
                        dropdown: true,
                        width: "250px",
                        updateElement: function(selected) {
                          var form = getForm('manageCodes');
                          $V(form._codes_ccam, selected.down("strong").innerHTML);
                          ActesCCAM.add('{{$subject->_id}}','{{$subject->_praticien_id}}');
                        }
                      });
                    });
                  </script>
                {{/if}}
              </td>
              <td>
                <button type="button" class="notext add me-secondary" onclick="$V(getForm('manageCodes')._new_code_ccam, '{{$_code->code}}');">
                  {{tr}}Duplicate{{/tr}}
                </button>
                {{if $can_delete}}
                  <button type="button" class="notext trash me-tertiary" onclick="changeCodeToDel('{{$subject->_id}}', '{{$_code->code}}', '{{$actes_ids}}')">
                    {{tr}}Delete{{/tr}}
                  </button>
                {{/if}}
              </td>
            </tr>
          {{/foreach}}
        </table>
      </fieldset>
    </td>
  </tr>
</table>

<!-- Pas d'affichage de inc_manage_codes si la consultation est deja validée -->
 {{*if $subject instanceof CConsultation && !$subject->_coded*}}
  <table class="main layout">
    <tr>
      {{if $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation' || $subject|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
      <td class="halfPane">
        <fieldset>
          <legend>Validation du codage</legend>
          {{if 'dPsalleOp CActeCCAM allow_send_acts_room'|gconf || $m == "dPpmsi" && $subject|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
            {{if !$subject->facture || $m == "dPpmsi" || $can->admin}}
            <script>
              Main.add(function () {
                PMSI.loadExportActes('{{$subject->_id}}', '{{$subject->_class}}', 1, 'dPsalleOp');
              });
            </script>
            {{/if}}
            <table class="main layout">
              <tr>
                <td id="export_{{$subject->_class}}_{{$subject->_id}}">

                </td>
              </tr>
            </table>
          {{/if}}
        </fieldset>
      </td>
      {{/if}}
    </tr>
  </table>
{{*/if*}}

{{if $ajax}}
  <script type="text/javascript">
    oCodesManagerForm = document.manageCodes;
    prepareForm(oCodesManagerForm);
  </script>
{{/if}}
