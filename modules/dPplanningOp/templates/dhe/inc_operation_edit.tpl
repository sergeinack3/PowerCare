{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="operation-edit" style="display: none;">
  <form name="operationEdit" method="post" onsubmit="return false;">
    {{mb_key object=$operation}}

    {{mb_field object=$operation field=annulee hidden=true}}
    {{mb_field object=$operation field=sejour_id hidden=true}}

    <table class="form">
      <tr>
        <th class="quaterPane" style="height: 1%;">
          {{mb_label object=$operation field=chir_id}}
        </th>
        <td class="quaterPane">
          <input type="text" name="_chir_view" value="{{$operation->_ref_chir}}">
          {{mb_field object=$operation field=chir_id hidden=true onchange="DHE.syncChir('operation', 'edit', this);"}}
        </td>
        <td class="text button" rowspan="10">
          <div class="small-info">
            Les modifications apportées sont reportées automatiquement dans la DHE
          </div>
          <button type="button" class="right" onclick="Control.Modal.close()" id="btn_close_operation_edit">
            Revenir à la DHE
          </button>
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label object=$operation field=libelle}}
        </th>
        <td>
          {{mb_field object=$operation field=libelle
                     form=operationEdit
                     autocomplete="true,1,50,true,true"
                     inputWidth="100%"
                     onblur="DHE.operation.syncField('edit', this);"
                     onchange="DHE.operation.syncField('edit', this);"
                     placeholder="Libellé d'intervention"}}
        </td>
      </tr>
    </table>

    <ul id="operation-edit-tabs" class="control_tabs">
      <li><a href="#operation-edit-actes">Actes</a></li>
      <li><a href="#operation-edit-anesth">Anesthésiste</a></li>
      <li><a href="#operation-edit-planification">Planification</a></li>
      <li><a href="#operation-edit-durees">Durées</a></li>
      <li><a href="#operation-edit-tarif">Tarif</a></li>
      <li><a href="#operation-edit-materiel">Matériel</a></li>
      <li><a href="#operation-edit-preop">Pré-Op</a></li>
      <li><a href="#operation-edit-medical">Médical</a></li>
      {{if $operation->_id}}
      <li><a href="#operation-edit-docs">Documents</a></li>
      {{/if}}
    </ul>

    <div id="operation-edit-actes" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_actes}}
    </div>
    <div id="operation-edit-anesth" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_anesth}}
    </div>
    <div id="operation-edit-planification" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_planification}}
    </div>
    <div id="operation-edit-durees" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_durees}}
    </div>
    <div id="operation-edit-tarif" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_tarif}}
    </div>
    <div id="operation-edit-materiel" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_materiel}}
    </div>
    <div id="operation-edit-preop" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_preop}}
    </div>
    <div id="operation-edit-medical" style="display: none;">
      {{mb_include module=planningOp template=dhe/operation/inc_edit_medical}}
    </div>
  </form>

  {{if $operation->_id}}
    <div id="operation-edit-docs" style="display: none;">
    </div>
  {{/if}}
</div>