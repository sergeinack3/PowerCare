{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Haut de page, informations patient et intervention (idem Salle d'op) -->
<table class="tbl">
  {{assign var=patient value=$selOp->_ref_sejour->_ref_patient}}
  <tr>
    <th class="title text" colspan="2">
      <button class="hslip notext me-tertiary me-dark" id="listplages-trigger" type="button" style="float:left">
        {{tr}}Show_or_hide_left_column{{/tr}}
      </button>
      <a class="action" style="float: right;" title="Modifier le dossier administratif"
         href="?m=patients&tab=vw_edit_patients&patient_id={{$patient->_id}}">
        {{me_img_title src="edit.png" icon="edit" class="me-primary"}}
          {{tr}}Edit{{/tr}}
        {{/me_img_title}}
      </a>
      {{$patient->_view}}
      ({{$patient->_age}}
      {{if $patient->_annees != "??"}}- {{mb_value object=$patient field="naissance"}}{{/if}})
      &mdash; Dr {{$selOp->_ref_chir}}
      <br />
      {{if $selOp->libelle}}{{$selOp->libelle}} &mdash;{{/if}}
      {{mb_label object=$selOp field=cote}} : {{mb_value object=$selOp field=cote}}
      &mdash; {{mb_label object=$selOp field=temp_operation}} : {{mb_value object=$selOp field=temp_operation}}
    </th>
  </tr>
</table>