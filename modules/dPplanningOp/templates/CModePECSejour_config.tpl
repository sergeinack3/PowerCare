{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new me-primary type="button" onclick="ParametrageMode.editModePec(null)">{{tr}}CModePECSejour-title-create{{/tr}}</button>
<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CModePECSejour field=code}}</th>
    <th>{{mb_title class=CModePECSejour field=libelle}}</th>
    <th>{{mb_title class=CModePECSejour field=actif}}</th>
    <th>{{mb_title class=CModePECSejour field=default}}</th>
  </tr>
  {{foreach from=$list_modes_pec item=_mode_pec}}
    <tr {{if !$_mode_pec->actif}}class="hatching"{{/if}}>
      <td>
        <button type="button" class="edit notext me-tertiary" onclick="ParametrageMode.editModePec({{$_mode_pec->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_mode_pec field=code}}</td>
      <td>{{mb_value object=$_mode_pec field=libelle}}</td>
      <td>{{mb_value object=$_mode_pec field=actif}}</td>
      <td>{{mb_value object=$_mode_pec field=default}}</td>
    </tr {{if !$_mode_pec->actif}}class="hatching"{{/if}}>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CModePECSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>