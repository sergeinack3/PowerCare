{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="sejour-edit" style="display: none;">
  <form name="sejourEdit" action="?m={{$m}}" method="post" onsubmit="return false;">
    {{mb_class object=$sejour}}
    {{mb_key object=$sejour}}

    {{if $sejour->_id}}
      {{mb_field object=$sejour field=annule hidden=true}}
    {{else}}
      <input type="hidden" name="_protocole_prescription_chir_id" value="">
    {{/if}}

    <input type="hidden" name="_curr_op_date" />

    {{if !$can->admin}}
      {{mb_field object=$sejour field=group_id hidden=true}}
    {{/if}}

    <table class="form">
      {{if $can->admin}}
        <tr>
          <th class="quarterPane">
            {{mb_label object=$sejour field=group_id}}
          </th>
          <td class="quarterPane">
            <select name="group_id"
                    onchange="DHE.sejour.syncField('edit', this); DHE.sejour.loadListSejour();"
                    style="width: 18em;"
            >
              <option value="">&mdash; {{tr}}Select{{/tr}}</option>
              {{foreach from=$groups item=_group}}
                <option value="{{$_group->_id}}"{{if $_group->_id == $group->_id}} selected="selected"{{/if}}>
                  {{$_group}}
                </option>
              {{/foreach}}
            </select>
          </td>
          <td class="text button halfPane" rowspan="10">
            <div class="small-info">
              Les modifications apportées sont reportées automatiquement dans la DHE
            </div>
            <button type="button" class="right" onclick="Control.Modal.close()">Revenir à la DHE</button>
          </td>
        </tr>
      {{/if}}
      <tr>
        <th class="quarterPane">
          {{mb_label object=$sejour field=praticien_id}}
        </th>
        <td class="quarterPane">
          <input type="text" name="_chir_view" value="{{$sejour->_ref_praticien}}" style="width: 16em;">
          {{mb_field object=$sejour field=praticien_id hidden=true
          onchange="DHE.syncChir('sejour', 'edit', this);"
          style="width: 16em;"}}
        </td>
        {{if !$can->admin}}
          <td class="text button halfPane" rowspan="10">
            <div class="small-info">
              Les modifications apportées sont reportées automatiquement dans la DHE
            </div>
            <button>Revenir à la DHE</button>
          </td>
        {{/if}}
      </tr>
      <tr>
        <th>
          {{mb_label object=$sejour field=patient_id}}
        </th>
        <td>
          <input type="text" name="_patient_view" value="{{$patient}}" placeholder="{{tr}}Search{{/tr}} {{tr}}CPatient{{/tr}}" style="width: 16em;">
          {{mb_field object=$sejour field=patient_id hidden=true
          onchange="DHE.syncPatient('edit', this);"}}
          <button type="button" class="cancel notext" onclick="DHE.emptyPatient();">{{tr}}Empty{{/tr}}</button>
          <button type="button" class="search notext" onclick="DHE.selectPatient();">Choisir un patient</button>
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label object=$sejour field=libelle}}
        </th>
        <td>
          {{mb_field object=$sejour field=libelle onchange="DHE.sejour.syncField('edit', this);" style="width: 16em;"}}
        </td>
      </tr>
    </table>

    <ul id="sejour-edit-tabs" class="control_tabs">
      <li><a href="#sejour-edit-diags">Diagnostics</a></li>
      <li><a href="#sejour-edit-pec">Prise en charge</a></li>
      <li><a href="#sejour-edit-in-out">Entrée / Sortie</a></li>
      <li><a href="#sejour-edit-presta-host">Prestations / Hébergement</a></li>
      <li><a href="#sejour-edit-medical">Médical</a></li>
      <li><a href="#sejour-edit-patient">Patient</a></li>
      {{if $sejour->_id}}
        {{if "dPprescription"|module_active}}
          <li><a href="#sejour-edit-prescription">Prescription</a></li>
        {{/if}}
        <li><a href="#sejour-edit-docs">Documents</a></li>
      {{/if}}
    </ul>

    <div id="sejour-edit-diags" style="display: none;">
      {{mb_include module=planningOp template=dhe/sejour/inc_edit_diagnostics}}
    </div>
    <div id="sejour-edit-pec" style="display: none;">
      {{mb_include module=planningOp template=dhe/sejour/inc_edit_pec}}
    </div>
    <div id="sejour-edit-in-out" style="display: none;">
      {{mb_include module=planningOp template=dhe/sejour/inc_edit_admission}}
    </div>
    <div id="sejour-edit-presta-host" style="display: none;">
      {{mb_include module=planningOp template=dhe/sejour/inc_edit_hosting}}
    </div>
    <div id="sejour-edit-medical" style="display: none;">
      {{mb_include module=planningOp template=dhe/sejour/inc_edit_medical}}
    </div>
    <div id="sejour-edit-patient" style="display: none;">
      {{mb_include module=planningOp template=dhe/sejour/inc_edit_patient}}
    </div>
  </form>

  {{* Needs to be outside the form element because the requested script will add another form in the div *}}
  {{if $sejour->_id}}
    {{if "dPprescription"|module_active}}
      <div id="sejour-edit-prescription" style="display: none;">
        <div id="prescription_sejour"></div>
      </div>
    {{/if}}
    <div id="sejour-edit-docs" style="display: none;">
    </div>
  {{/if}}
</div>