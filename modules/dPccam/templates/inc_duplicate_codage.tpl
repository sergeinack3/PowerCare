{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPccam script=CCodageCCAM ajax=true}}

<script type="text/javascript">
  submiFormDuplicateCodage = function(form) {
    return onSubmitFormAjax(form, {onComplete: function() {
      Control.Modal.closeAll();
      loadCodagesCCAM({{$codage->codable_id}}, '{{$codage->date}}');
    }});
  }

  syncActes = function(input) {
    var acte_id = input.getAttribute('data-acte_id');
    if (input.checked) {
      Actes.add(acte_id);
      $$('#row-CActeCCAM-' + acte_id + ' td').each(function(element) {
        element.removeClassName('disabled');
      });
    }
    else {
      Actes.remove(acte_id);
      $$('#row-CActeCCAM-' + acte_id + ' td').each(function(element) {
        element.addClassName('disabled');
      });
    }
    var date = Date
  }

  syncDate = function(date) {
    $V(getForm('duplicateCodage').date, date);
    //$V(getForm('selectDate')._date, DateFormat.format(new Date(date), 'd/M/y'));
  }

  Main.add(function() {
    var dates = {};
    dates.limit = {
      start: '{{$codage->date|iso_date}}',
      stop: '{{$codage->_ref_codable->sortie|iso_date}}'
    };

    var oForm = getForm("selectDateFin");
    if (oForm) {
      Calendar.regField(oForm._date, dates);
    }

    Actes = new TokenField(getForm('duplicateCodage').actes);
    {{if $acte_id}}
      Actes.add({{$acte_id}});
    {{else}}
      {{foreach from=$codage->_ref_actes_ccam item=_acte}}
        Actes.add({{$_acte->_id}});
      {{/foreach}}
    {{/if}}
  });
</script>

<table class="tbl">
  <tr>
    <th colspan="6" class="title">Duplication d'un codage CCAM</th>
  </tr>
  <tr>
    <th class="category">{{tr}}CCodageCCAM-praticien_id{{/tr}}</th>
    <th class="category">{{tr}}CCodageCCAM-activite_anesth{{/tr}}</th>
    <th class="category">{{tr}}CCodageCCAM-date{{/tr}}</th>
    <th class="category">Actes</th>
    <th class="category">Dupliquer jusqu'au :</th>
  </tr>
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$codage->_ref_praticien}}
    </td>
    <td>
      {{tr}}CCodageCCAM.activite_anesth.{{$codage->activite_anesth}}{{/tr}}
    </td>
    <td>
      {{$codage->date|date_format:$conf.date}}
    </td>
    <td>
      {{if !$codage->_ref_actes_ccam|@count}}
        {{tr}}CActeCCAM.none{{/tr}}
      {{else}}
        <table class="tbl">
          {{foreach from=$codage->_ref_actes_ccam item=_acte}}
            {{assign var=_code_ccam value=$_acte->_ref_code_ccam}}
            {{assign var=code_activite value=$_acte->code_activite}}
            {{assign var=_activite value=$_code_ccam->activites[$code_activite]}}
            {{assign var=code_phase value=$_acte->code_phase}}
            {{assign var=_phase value=$_activite->phases[$code_phase]}}

            <tr id="row-{{$_acte->_guid}}"{{if $acte_id && $acte_id == $_acte->_id}} class="disabled"{{/if}}>
              <td>
                <a href="#" onclick="CodeCCAM.show('{{$_acte->code_acte}}', '{{$codage->codable_class}}');">
                  {{$_acte->code_acte}}
                </a>
              </td>
              <td>
                <span class="circled ok">
                  {{$_acte->code_activite}}-{{$_acte->code_phase}}
                </span>
              </td>
              <td>
                {{if !$_phase|property_exists:'_modificateurs' || !$_phase->_modificateurs|@is_countable || !$_phase->_modificateurs|@count}}
                  <em style="color: #7d7d7d;">Aucun modif. dispo.</em>
                {{elseif !$_acte->modificateurs}}
                  <strong>Aucun modif. codé</strong>
                {{else}}
                  {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                    {{if $_mod->_checked}}
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
              <td>
                {{$_acte->execution|date_format:$conf.time}}
              </td>
              <td>
                {{if $_acte->montant_depassement}}
                  <span class="circled" style="background-color: #aaf" title="{{mb_value object=$_acte field=montant_depassement}}">
                    DH
                  </span>
                {{/if}}
              </td>
              <td>
                {{mb_value object=$_acte field=_tarif}}
              </td>
              <td>
                <input type="checkbox" name="_actes" id="checkbox-{{$_acte->_guid}}" data-acte_id="{{$_acte->_id}}"
                       {{if $acte_id == $_acte->_id || !$acte_id}} checked="checked"{{/if}}
                       onchange="syncActes(this);"/>
              </td>
            </tr>
          {{/foreach}}
        </table>
      {{/if}}
      <input type="hidden" name="actes" value="{{if $acte_id}}{{$acte_id}}{{/if}}"/>
    </td>
    <td>
      <form name="selectDateFin" action="?" method="get" onsubmit="return false">
        <input type="hidden" name="_date" class="date" value="{{$codage->_ref_codable->sortie|date_format:'%Y-%m-%d'}}" onchange="syncDate(this.value);"/>
        <button type="button" class="edit notext" onclick="CCodageCCAM.chooseDateDuplication('{{$codage->_guid}}');">
            {{tr}}Edit{{/tr}}
        </button>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="6" class="button">
      <form name="duplicateCodage" method="post" target="?" onsubmit="return submiFormDuplicateCodage(this);">
        <input type="hidden" name="m" value="ccam"/>
        <input type="hidden" name="dosql" value="duplicateCodageCcam"/>
        <input type="hidden" name="codage_id" value="{{$codage->_id}}"/>
        <input type="hidden" name="codable_class" value="{{$codage->codable_class}}"/>
        <input type="hidden" name="codable_id" value="{{$codage->codable_id}}"/>
        <input type="hidden" name="multiple_date" value=""/>
        <input type="hidden" name="type_of_date" value="one_date"/>
        <input type="hidden" name="date" value="{{$codage->_ref_codable->sortie|date_format:'%Y-%m-%d'}}"/>
        <input type="hidden" name="actes" value="{{if $acte_id}}{{$acte_id}}{{/if}}"/>

        <button type="submit" class="copy" onclick="">
          {{tr}}Duplicate{{/tr}}
        </button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">
          {{tr}}Cancel{{/tr}}
        </button>
      </form>
    </td>
  </tr>
</table>
