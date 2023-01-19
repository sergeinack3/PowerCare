{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="add notext" onclick="Position.edit('0');">
  {{tr}}CPosition-title-create{{/tr}}
</button>
<input type="checkbox" onchange="Position.refreshList(this.checked ? 1 : 0);" id="showInactive_position"
       {{if $show_inactive}}checked="checked"{{/if}} name="show_inactive" />
<label for="showInactive_position">{{tr}}CPosition-show-inactive{{/tr}}</label>

<table class="main tbl">
  <tr>
    <th colspan="7" class="title">
      {{tr}}CPosition.all{{/tr}} ({{$positions|@count}})
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class= CPosition field=code}}</th>
    <th>{{mb_title class= CPosition field=libelle}}</th>
    <th>{{mb_title class= CPosition field=actif}}</th>
  </tr>
  {{foreach from=$positions item=_position}}
    <tr {{if !$_position->actif}}class="hatching"{{/if}}>
      <td><a href="#" onclick="Position.edit('{{$_position->_id}}');">{{mb_value object=$_position field=code}}</a></td>
      <td>{{mb_value object=$_position field=libelle}}</td>
      <td>{{mb_value object=$_position field=actif}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">{{tr}}CPosition.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>