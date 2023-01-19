{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  viewActes = function (form){
    var url = new Url("dPplanningOp", "vw_actes_realises");
    url.addFormData(form);
    url.requestUpdate("actes_realises");
  };

  submitActeCCAM = function (oForm, acte_ccam_id, sField){
    if(oForm[sField].value == 1) {
      $V(oForm[sField], 0);
    } else {
      $V(oForm[sField], 1);
    }
    $(sField + '-' + acte_ccam_id).toggleClassName('cancel').toggleClassName('tick');
    return onSubmitFormAjax(oForm, {onComplete: function() { reloadActeCCAM(acte_ccam_id) } });
  }

  reloadActeCCAM = function (acte_ccam_id) {
    var url = new Url("dPplanningOp", "httpreq_vw_reglement_ccam");
    url.addParam("acte_ccam_id", acte_ccam_id);
    url.requestUpdate('divreglement-'+acte_ccam_id);
  }

  viewCCAM = function (codeacte) {
    var url = new Url("dPccam", "viewCcamCode");
    url.addParam("_codes_ccam", codeacte);
    url.popup(800, 600, "Code CCAM");
  };

  Main.add(function () {
    var form = getForm(bilanActes);
    viewActes(form);
  });
</script>

<form name="bilanActes" action="?" method="get">
  <input type="hidden" name="export_csv" value="0">
  <table class="form">
    <tr>
      <th class="title" colspan="6">{{tr}}common-Search criteria{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="_date_min"}}</th>
      <td>
        {{mb_field object=$filter field="_date_min" form="bilanActes" register="true" canNull=false}}
      </td>
      <th>{{tr}}common-Practitioner{{/tr}}</th>
      <td>
        <select name="chir">
          {{foreach from=$praticiens item=_praticien}}
            <option {{if $praticien_id == $_praticien->_id}}selected="selected"{{/if}} value="{{$_praticien->_id}}">{{$_praticien->_view}}</option>
          {{/foreach}}
        </select>
      </td>
      <th>
        <label for="bloc_id">{{tr}}CBlocOperatoire{{/tr}}</label>
      </th>
      <td>
        <select name="bloc_id">
          <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
          {{foreach from=$blocs item=_bloc}}
            <option value="{{$_bloc->_id}}">{{$_bloc}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="_date_max"}}</th>
      <td>
        {{mb_field object=$filter field="_date_max" form="bilanActes" register="true" canNull=false}}
      </td>
      <th>
        <label for="typeVue">{{tr}}CFactureEtablissement-type-view{{/tr}}</label>
      </th>
      <td>
        <select name="typeVue">
          <option value="1">{{tr}}CFactureEtablissement-complete-list{{/tr}}</option>
          <option value="2">{{tr}}CFactureEtablissement-total|pl{{/tr}}</option>
        </select>
      </td>
      <th><label for="order">{{tr}}CFactureEtablissement-show-date{{/tr}}</label></th>
      <td>
        <select name="order">
          <option value="sortie_reelle">{{tr}}CFactureEtablissement-real-output{{/tr}}</option>
          <option value="acte_execution">{{tr}}CFactureEtablissement-date-acte{{/tr}}</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button class="button tick me-primary" type="button" onclick="viewActes(this.form);">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
    </tr>
  </table>
</form>

<div id="actes_realises" class="me-padding-8"></div>
