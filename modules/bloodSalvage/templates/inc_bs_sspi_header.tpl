{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$blood_salvage->_ref_patient}}
<table class="tbl">
  <tr>
    <th class="title text" colspan="2">
      <a class="action" style="float: right;" title="Modifier le dossier administratif"
         href="?m=patients&tab=vw_edit_patients&patient_id={{$patient->_id}}">
        {{me_img_title src="edit.png" icon="edit" class="me-primary"}}
          {{tr}}Edit{{/tr}}
        {{/me_img_title}}
      </a>
      {{$patient}}
      ({{$patient->_age}}
      {{if $patient->_annees != "??"}}- {{mb_value object=$patient field="naissance"}}{{/if}})
    </th>
  </tr>
</table>