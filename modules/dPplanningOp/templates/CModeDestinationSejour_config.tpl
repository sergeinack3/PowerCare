{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new me-primary" type="button" onclick="ParametrageMode.editModeDestination(null)">
  {{tr}}CModeDestinationSejour-title-create{{/tr}}
</button>
<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CModeDestinationSejour field=code}}</th>
    <th>{{mb_title class=CModeDestinationSejour field=libelle}}</th>
    <th>{{mb_title class=CModeDestinationSejour field=actif}}</th>
    <th>{{mb_title class=CModeDestinationSejour field=default}}</th>
  </tr>
  {{foreach from=$list_modes_destination item=_mode_destination}}
    <tr {{if !$_mode_destination->actif}}class="hatching"{{/if}}>
      <td>
        <button type="button" class="edit notext me-tertiary" onclick="ParametrageMode.editModeDestination({{$_mode_destination->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_mode_destination field=code}}</td>
      <td>{{mb_value object=$_mode_destination field=libelle}}</td>
      <td>{{mb_value object=$_mode_destination field=actif}}</td>
      <td>{{mb_value object=$_mode_destination field=default}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CModeDestinationSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>