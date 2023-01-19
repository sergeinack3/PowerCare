{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      {{tr}}CRedon-List releves{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CReleveRedon field=qte_observee}}</th>
    <th>{{mb_title class=CReleveRedon field=date}}</th>
    <th>{{mb_title class=CReleveRedon field=user_id}}</th>
    <th>{{mb_title class=CReleveRedon field=_qte_cumul}}</th>
  </tr>
  {{foreach from=$redon->_ref_releves item=_releve}}
  <tr>
    <td class="narrow">
      <button type="button" class="edit notext" onclick="Redon.editReleve('{{$_releve->_id}}');">{{tr}}Edit{{/tr}}</button>
    </td>
    <td>
      {{if $_releve->vidange_apres_observation}}
        <i class="fas fa-fill-drip" style="float: right;" title="{{tr}}CReleveRedon-vidange_apres_observation{{/tr}}"></i>
      {{/if}}
      {{mb_value object=$_releve field=qte_observee}} ml
    </td>
    <td>
      {{mb_value object=$_releve field=date}}
    </td>
    <td>
      {{mb_value object=$_releve field=user_id}}
    </td>
    <td>
      {{mb_value object=$_releve field=_qte_cumul}} ml
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="4">{{tr}}CReleveRedon.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>