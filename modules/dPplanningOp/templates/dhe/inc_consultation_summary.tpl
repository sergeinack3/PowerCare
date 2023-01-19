{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="consult-list-container">

</div>

<form name="consultationSummary" method="post" action="?" onsubmit="return false;">
  <fieldset style="height: 80px;">
  <table class="form">
    <tr>
      <th class="dhe-summary">
        <label for="chir_id" title="Praticien effectuant la consultation">Praticien</label>
      </th>
      <td>
        <input type="text" name="_chir_view" value="{{$consult->_ref_praticien}}" style="width: 12em;">
        <input type="hidden" name="chir_id" class="notNull" value="{{if $consult->_ref_praticien}}{{$consult->_ref_praticien->_id}}{{/if}}"
               onchange="DHE.syncChir('consultation', 'summary', this);">
        <input type="hidden" name="_function_id" onchange="$V(getForm('selectPlageConsult').elements['_function_id'], $V(this));"
               value="{{if $consult->_ref_praticien}}{{$consult->_ref_praticien->_ref_function->_id}}{{/if}}">
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <label for="_type" title="Type de consultation">Type</label>
      </th>
      <td>
        <select name="_type" onchange="DHE.consult.syncField('summary', this); DHE.consult.setType($V(this));" style="width: 14em;">
          <option value="planned">Planifiée</option>
          <option value="immediate">Immédiate</option>
        </select>
      </td>
    </tr>
    <tr id="date-consult-container" style="display: none;">
      <th class="dhe-summary">
        <label for="_datetime" title="Date et heure de la consultation">Date</label>
      </th>
      <td>
        {{mb_field object=$consult field=_datetime register=true form=consultationSummary
                   onchange="DHE.consult.syncField('summary', this, this.form.elements['_datetime_da']);"}}
      </td>
    </tr>
    <tr id="plage-consult-container">
      <th class="dhe-summary">
        {{mb_label object=$consult field=plageconsult_id}}
      </th>
      <td>
        {{mb_field object=$consult field=plageconsult_id hidden=true
                   onchange="DHE.consult.syncField('summary', this, this.form.elements['_datetime_plage']);"}}
        <input type="text" name="_datetime_plage" class="dateTime" readonly="readonly" value="{{mb_value object=$consult field=_datetime}}">
        {{mb_field object=$consult field=heure hidden=true onchange="DHE.consult.syncField('summary', this);"}}
      </td>
    </tr>
  </table>
  </fieldset>
</form>

<table class="form">
  <tr>
    <th class="dhe-summary" style="margin-right: 3px;">
      <span class="dhe_item" id="consult-appointment-category" onclick="DHE.consult.displayEditView('consult-edit-appointment');">
        Rendez-vous
      </span>
    </th>
    <td id="consult-appointment-items" style="vertical-align: middle; height: 25px;">
      {{mb_include module=planningOp template=dhe/consultation/inc_sum_appointment}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary" style="margin-right: 3px;">
      <span class="dhe_item" id="consultation-examination-category" onclick="DHE.consult.displayEditView('consult-edit-examination');">
        Examen
      </span>
    </th>
    <td id="consult-examination-items" style="vertical-align: middle; height: 25px;">
      {{mb_include module=planningOp template=dhe/consultation/inc_sum_examination}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary" style="margin-right: 3px;">
      <span class="dhe_item" id="consultation-facturation-category" onclick="DHE.consult.displayEditView('consult-edit-facturation');">
        Facturation
      </span>
    </th>
    <td id="consult-facturation-items" style="vertical-align: middle; height: 25px;">
      {{mb_include module=planningOp template=dhe/consultation/inc_sum_facturation}}
    </td>
  </tr>
  {{if $consult->_id}}
    <tr>
      <th class="dhe-summary" style="margin-right: 3px;">
        <span class="dhe_item" id="consultation-documents-category" onclick="DHE.consult.displayEditView('consult-edit-docs');">
          Documents
        </span>
      </th>
      <td id="consult_documents_items" class="text" style="vertical-align: middle; height: 25px;">
        {{mb_include module=planningOp template=dhe/inc_sum_documents object=$consult}}
      </td>
    </tr>
  {{/if}}
</table>