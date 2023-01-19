{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$_operation->_ref_sejour->_ref_patient}}
{{assign var=chir    value=$_operation->_ref_chir}}

<tbody class="hoverable">
  <tr>
    <td rowspan="2" style="vertical-align: middle;">
      {{if $_operation->rank}}
        <div class="rank">{{$_operation->rank}}</div>
      {{elseif $_operation->rank_voulu}}
        <div class="rank desired" title="Pas encore validé par le bloc">{{$_operation->rank_voulu}}</div>
      {{/if}}
    </td>
    <td rowspan="2">
      <strong>
        {{if $_operation->rank}}
          {{mb_value object=$_operation field=time_operation}}
          {{me_img_title src="tick.png" icon="tick" class="me-success"}}
            Validé
          {{/me_img_title}}
        {{elseif $_operation->horaire_voulu}}
          {{mb_value object=$_operation field=horaire_voulu}}
        {{else}}
          NP
        {{/if}}
      </strong>
      <br />
      <em>({{mb_value object=$_operation field=temp_operation}})</em>
    </td>
    <td>
      {{if $patient->_ref_dossier_medical->_id && $patient->_ref_dossier_medical->_count_allergies}}
        {{me_img src="warning.png" icon="warning" class="me-warning" style="float:right" onmouseover="ObjectTooltip.createEx(this, '`$patient->_guid`', 'allergies');"}}
      {{/if}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">{{$patient}}</span>
    </td>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
    </td>
  </tr>
  <tr>
    <td colspan="3">
      {{mb_include template=inc_vw_operation}} ({{mb_label object=$_operation field=cote}} {{mb_value object=$_operation field=cote}})
    </td>
  </tr>
</tbody>
