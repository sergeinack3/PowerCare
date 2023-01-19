{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="list_container">

</div>

<fieldset style="height: 80px;">
  <form name="operationSummary" method="post" onsubmit="return false;">
    <table class="form">
      <tr>
        <th class="dhe-summary">
          {{mb_label object=$operation field=chir_id}}
        </th>
        <td>
          <input type="text" name="_chir_view" value="{{$operation->_ref_chir}}" style="width: 12em;">
          {{mb_field object=$operation field=chir_id hidden=true onchange="DHE.syncChir('operation', 'summary', this);"}}
        </td>
      </tr>
      <tr>
        <th class="dhe-summary">
          {{mb_label object=$operation field=libelle}}
        </th>
        <td>
          {{mb_field object=$operation field=libelle
                     form=operationSummary
                     autocomplete="true,1,50,true,true"
                     inputWidth="50%"
                     onblur="DHE.operation.syncField('summary', this);"
                     placeholder="Libellé d'intervention"
          }}
        </td>
      </tr>
    </table>
  </form>
</fieldset>
<table class="form">
  <tr>
    <th class="dhe-summary" style="margin-right: 3px;">
      <span class="dhe_item" id="actes_category" onclick="DHE.operation.displayEditView('operation-edit-actes');">
        Actes
      </span>
    </th>
    <td id="actes_items"></td>
  </tr>
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="anesth_category" onclick="DHE.operation.displayEditView('operation-edit-anesth');">
        Anesthésiste
      </span>
    </th>
    <td id="anesth_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_anesth}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="planif_category" onclick="DHE.operation.displayEditView('operation-edit-planification');">
        Planification
      </span>
    </th>
    <td id="planif_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_planification}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="durees_category" onclick="DHE.operation.displayEditView('operation-edit-durees');">
        Durées
      </span>
    </th>
    <td id="durees_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_durees}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="tarif_category" onclick="DHE.operation.displayEditView('operation-edit-tarif');">
        Tarif
      </span>
    </th>
    <td id="planif_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_tarif}}
    </td>
  </tr>
  <tr>
    <th>
      <span class="dhe_item" id="materiel_category" onclick="DHE.operation.displayEditView('operation-edit-materiel');">
        Matériel
      </span>
    </th>
    <td id="planif_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_materiel}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="preop_category" onclick="DHE.operation.displayEditView('operation-edit-preop');">
        Pré-Op
      </span>
    </th>
    <td id="preop_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_preop}}
    </td>
  </tr>
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="medical_category" onclick="DHE.operation.displayEditView('operation-edit-medical');">
        Médical
      </span>
    </th>
    <td id="planif_items">
      {{mb_include module=planningOp template=dhe/operation/inc_sum_medical}}
    </td>
  </tr>
  {{if $operation->_id}}
  <tr>
    <th class="dhe-summary">
      <span class="dhe_item" id="documents_category" onclick="DHE.operation.displayEditView('operation-edit-docs');">
        Documents
      </span>
    </th>
    <td id="operation_documents_items" class="text">
      {{mb_include module=planningOp template=dhe/inc_sum_documents object=$operation}}
    </td>
  </tr>
  {{else}}
  <tr>
    <td colspan="2">
      {{mb_include module=files template=inc_button_docitems context=$operation form=operationEdit}}
    </td>
  </tr>
  {{/if}}
</table>