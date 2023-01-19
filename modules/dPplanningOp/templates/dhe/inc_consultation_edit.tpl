{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="consultation-edit" style="display: none;">
  <form name="consultationEdit" method="post" action="?" onsubmit="return false;">
    {{mb_class object=$consult}}
    {{mb_key object=$consult}}

    {{mb_field object=$consult field=sejour_id hidden=true}}
    {{mb_field object=$consult field=patient_id hidden=true}}
    {{mb_field object=$consult field=annule hidden=true}}

    <table class="form">
      <tr>
        <th class="quarterPane">
          <label for="chir_id">Praticien</label>
        </th>
        <td class="quarterPane">
          <input type="text" name="_chir_view" value="{{$consult->_ref_praticien}}">
          <input type="hidden" name="chir_id" class="notNull" value="{{if $consult->_ref_praticien}}{{$consult->_ref_praticien->_id}}{{/if}}" onchange="DHE.syncChir('consultation', 'edit', this);">
        </td>
        <td class="text button halfPane" rowspan="3">
          <div class="small-info">
            Les modifications apportées sont reportées automatiquement dans la DHE
          </div>
          <button type="button" class="right" onclick="Control.Modal.close()">Revenir à la DHE</button>
        </td>
      </tr>
      <tr>
        <th class="quarterPane">
          <label for="_type">Type</label>
        </th>
        <td class="quarterPane">
          <select name="_type" onchange="DHE.consult.syncField('edit', this); DHE.consult.setType($V(this));">
            <option value="planned">Planifiée</option>
            <option value="immediate">Immédiate</option>
          </select>
        </td>
      </tr>
      <tr id="consult-edit-date-container" style="display: none;">
        <th class="quarterPane">
          <label for="_datetime">Date</label>
        </th>
        <td class="quarterPane">
          {{mb_field object=$consult field=_datetime register=true form=consultationEdit onchange="DHE.consult.syncField('edit', this, this.form.elements['_datetime_da']);"}}
        </td>
      </tr>
      <tr id="consult-edit-plage-container">
        <th class="quarterPane">
          <label for="plageconsult_id">Date</label>
        </th>
        <td class="quarterPane">
          {{mb_field object=$consult field=plageconsult_id hidden=true onchange="DHE.consult.syncField('edit', this, this.form.elements['_datetime_plage']); DHE.consult.updateDuree();"}}
          <input type="text" name="_datetime_plage" class="dateTime" readonly="readonly" value="{{mb_value object=$consult field=_datetime}}">
          {{mb_field object=$consult field=heure hidden=true}}
        </td>
      </tr>
      <tr>
        <td colspan="2"></td>
      </tr>
    </table>

    <ul id="consult-edit-tabs" class="control_tabs">
      <li><a href="#consult-edit-appointment">Rendez-vous</a></li>
      <li><a href="#consult-edit-examination">Examens</a></li>
      <li><a href="#consult-edit-facturation">Facturation</a></li>
      {{if $consult->_id}}
        <li><a href="#consult-edit-docs">Documents</a></li>
      {{/if}}
    </ul>

    <div id="consult-edit-appointment" style="display: none;">
      {{mb_include module=planningOp template=dhe/consultation/inc_edit_appointment}}
    </div>

    <div id="consult-edit-examination" style="display: none;">
      {{mb_include module=planningOp template=dhe/consultation/inc_edit_examination}}
    </div>

    <div id="consult-edit-facturation" style="display: none;">
      {{mb_include module=planningOp template=dhe/consultation/inc_edit_facturation}}
    </div>
  </form>

  {{if $consult->_id}}
    <div id="consult-edit-docs" style="display: none;">
    </div>
  {{/if}}
</div>