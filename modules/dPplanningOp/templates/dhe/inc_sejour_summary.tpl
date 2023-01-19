{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="sejour-list-container">

</div>

{{if $sejour->annule}}
  <span id="sejour-annule" class="dhe_flag dhe_flag_important" style="display: none; position: absolute; padding: 2px; font-weight: bold;">
    Annulé
  </span>
{{/if}}

<fieldset id="sejour_summary" style="min-height: 400px;">
  <legend>
    {{tr}}CSejour{{/tr}}
    {{if $sejour->_id}}
      <a class="button new notext me-primary" title="{{tr}}New{{/tr}} {{tr}}CSejour{{/tr}}" style="padding-right: 1px;" href="?m=planningOp&tab=vw_dhe&sejour_id=0">
        {{tr}}New{{/tr}}
      </a>
    {{/if}}
  </legend>

  <form name="sejourSummary" method="post" action="?" onsubmit="return false;">
    <fieldset style="height: 80px;" class="me-height-auto me-no-box-shadow me-padding-top-4">
    <table class="form me-no-box-shadow">
      <colgroup>
        <col style="width: 100px;">
        <col>
        <col style="width: 100px;">
      </colgroup>
      <tr>
        {{me_form_field nb_cells=2 class="dhe-summary" mb_object=$sejour mb_field=group_id}}
          {{if $user->isAdmin()}}
            <select name="group_id" class="notNull" onchange="DHE.sejour.syncField('view', this); DHE.sejour.loadListSejour();"
                    style="width: 14em;">
              <option value="">&mdash; {{tr}}Select{{/tr}}</option>
              {{foreach from=$groups item=_group}}
                <option value="{{$_group->_id}}"{{if $_group->_id == $group->_id}} selected="selected"{{/if}}>
                  {{$_group}}
                </option>
              {{/foreach}}
            </select>
          {{else}}
            <div class="me-field-content">
                {{$group}}
            </div>
          {{/if}}
        {{/me_form_field}}

        {{me_form_field nb_cells=2 mb_object=$sejour mb_field="entree_prevue" class="dhe-summary"}}
          {{mb_field object=$sejour field=entree_prevue form="sejourSummary"
          register=true onchange="DHE.sejour.setAdmissionDates(this, 'summary'); DHE.sejour.loadListSejour();"}}
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_object=$sejour mb_field="praticien_id" class="dhe-summary"}}
          <input type="text" name="_chir_view" value="{{$sejour->_ref_praticien}}" style="width: 12em;">
          {{mb_field object=$sejour field=praticien_id hidden=true onchange="DHE.syncChir('sejour', 'summary', this);"}}
        {{/me_form_field}}

        {{me_form_field nb_cells=2 mb_object=$sejour mb_field=type class="dhe-summary"}}
          {{mb_field object=$sejour field=type onchange="DHE.sejour.setAdmissionDates(this, 'summary');" style="width: 14em;"}}
          {{* @todo: Gérer tous les onchange du champ type (voir inc_form_sejour) *}}
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_object=$sejour mb_field=libelle class="dhe-summary"}}
          <input type="text" name="libelle" value="{{$sejour->libelle}}" onchange="DHE.sejour.syncField('summary', this);"
                 style="width: 14em;">
        {{/me_form_field}}

        {{me_form_field nb_cells=2 mb_object=$sejour mb_field="_duree_prevue" class="dhe-summary"}}
          <span id="sejour-summary-duree-unit-nights"{{if !$sejour->_duree_prevue}} style="display: none;"{{/if}}>
            {{mb_field object=$sejour field=_duree_prevue increment=true form=sejourSummary size=2 prop='num min|0' onchange="DHE.sejour.setAdmissionDates(this, 'summary'); DHE.sejour.loadListSejour();"}}
            nuit(s)
          </span>
          <span id="sejour-summary-duree-unit-hours"{{if $sejour->_duree_prevue_heure <= 0}} style="display: none;"{{/if}}>
            {{mb_field object=$sejour field=_duree_prevue_heure increment=true form=sejourSummary size=2 prop='num min|0 max|23' onchange="DHE.sejour.setAdmissionDates(this, 'summary'); DHE.sejour.loadListSejour();"}}
            heure(s)
          </span>
          &mdash; <span id="sejour-summary-view-days">{{$sejour->_duree_prevue + 1}}</span> jour(s)
        {{/me_form_field}}
      </tr>
    </table>
    </fieldset>
  </form>

  <table class="form">
    <tr>
      <th class="dhe-summary" style="margin-right: 3px;">
        <span class="dhe_item" id="diagnostic_category" onclick="DHE.sejour.displayEditView('sejour-edit-diags');">
          Diagnostics
        </span>
      </th>
      <td id="diagnostic_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_diagnostic}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item" id="pec_category" onclick="DHE.sejour.displayEditView('sejour-edit-pec');">
          Prise en charge
        </span>
      </th>
      <td id="pec_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_pec}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item" id="entree_category" onclick="DHE.sejour.displayEditView('sejour-edit-in-out');">
          Entrée
        </span>
      </th>
      <td id="entree_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_entree}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item" id="sortie_category" onclick="DHE.sejour.displayEditView('sejour-edit-in-out');">
          Sortie
        </span>
      </th>
      <td id="sortie_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_sortie}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item circled" id="prestations_category" onclick="DHE.sejour.displayEditView('sejour-edit-presta-host');">
          Prestations
        </span>
      </th>
      <td id="prestations_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_prestations}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item" id="hosting_category" onclick="DHE.sejour.displayEditView('sejour-edit-presta-host');">
          Hébergement
        </span>
      </th>
      <td id="hosting_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_hosting}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item" id="medical_category" onclick="DHE.sejour.displayEditView('sejour-edit-medical');">
          Médical
        </span>
      </th>
      <td id="medical_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_medical}}
      </td>
    </tr>
    <tr>
      <th class="dhe-summary">
        <span class="dhe_item" id="patient_category" onclick="DHE.sejour.displayEditView('sejour-edit-patient');">
          Patient
        </span>
      </th>
      <td id="patient_items" style="vertical-align: middle; height: 25px;" colspan="3">
        {{mb_include module=planningOp template=dhe/sejour/inc_sum_patient}}
      </td>
    </tr>
    {{if $sejour->_id}}
      {{if "dPprescription"|module_active}}
      <tr>
        <th class="dhe-summary">
          <span class="dhe_item" id="prescription_category" onclick="DHE.sejour.displayEditView('sejour-edit-prescription')">
            Prescription
          </span>
        </th>
        <td id="prescription_items" colspan="3">
          {{mb_include module=planningOp template=dhe/sejour/inc_sum_prescription}}
        </td>
      </tr>
      {{/if}}
      <tr>
        <th class="dhe-summary">
          <span class="dhe_item" id="documents_category" onclick="DHE.sejour.displayEditView('sejour-edit-docs');">
            Documents
          </span>
        </th>
        <td id="sejour_documents_items" style="vertical-align: middle; height: 25px;" colspan="3" class="text">
          {{mb_include module=planningOp template=dhe/inc_sum_documents object=$sejour}}
        </td>
      </tr>
    {{/if}}

    {{if !$sejour->_id}}
    <tr>
      <td colspan="4">
        {{mb_include module=files template=inc_button_docitems object=$sejour form=sejourEdit}}
      </td>
    </tr>
    {{/if}}

  </table>
</fieldset>