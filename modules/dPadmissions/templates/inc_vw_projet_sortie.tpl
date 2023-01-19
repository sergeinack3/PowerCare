{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tbody>
    {{foreach from=$sejours item=_sejour}}
      {{assign var=last_operation value=$_sejour->_ref_last_operation}}
      {{assign var=patient value=$_sejour->_ref_patient}}
      <tr>
        <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')" {{if !$_sejour->entree_reelle }}class="patient-not-arrived"{{/if}}>
            {{$patient}}
          </span>

          {{mb_include module=patients template=inc_status_icon}}
          {{mb_include module=patients template=inc_icon_bmr_bhre}}
        </td>
        <td>{{mb_value object=$patient field=tel}}</td>
        <td>{{mb_value object=$patient field=tutelle}}</td>
        <td class="text">{{$_sejour->_ref_curr_affectation->_view}}</td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$last_operation->_guid}}')">{{$last_operation->_view}}</span>
        </td>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}</td>
        <td>{{mb_value object=$_sejour field=entree}}</td>
        <td>{{mb_value object=$_sejour field=sortie}}</td>
        <td>{{mb_value object=$_sejour field=type}}</td>
        <td>
          {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie}}
            {{$_sejour->_ref_mode_sortie->_view}}
          {{elseif $_sejour->mode_sortie}}
            {{mb_value object=$_sejour field=mode_sortie}}
          {{/if}}
        </td>
        <td>
            <ul>
                {{foreach from=$patient->_refs_patient_handicaps item=_handicap}}
                    <li>{{$_handicap}}</li>
                {{/foreach}}
            </ul>
        </td>
        <td>{{mb_value object=$_sejour field=aide_organisee}}</td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="12" class="empty">{{tr}}CSejour.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
    <tr>
      <th class="section" colspan="5">{{tr}}CPatient{{/tr}}</th>
      <th class="section" colspan="7">{{tr}}CSejour{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label class=CSejour field=patient_id}}</th>
      <th>{{mb_label class=CPatient field=tel}}</th>
      <th>{{mb_label class=CPatient field=tutelle}}</th>
      <th>Chambre</th>
      <th>Intervention</th>
      <th>{{mb_label class=CSejour field=praticien_id}}</th>
      <th>{{mb_label class=CSejour field=entree}}</th>
      <th>{{mb_label class=CSejour field=sortie}}</th>
      <th>{{mb_label class=CSejour field=type}}</th>
      <th>{{mb_label class=CSejour field=mode_sortie}}</th>
      <th>{{tr}}CPatientHandicap{{/tr}}</th>
      <th>{{mb_label class=CSejour field=aide_organisee}}</th>
    </tr>
  </thead>
</table>
