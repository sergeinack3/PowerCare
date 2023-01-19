{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page="changePageSelection"
total=$total current=$start step=20}}

<table class="tbl">
  <tr>
    <th>{{mb_title class=CProductSelection field=name}}</th>
    <th>{{tr}}CProductSelection-back-selection_items{{/tr}}</th>
  </tr>
  {{foreach from=$list item=_selection}}
    <tbody class="hoverable">
    <tr {{if $_selection->_id == $selection->_id}}class="selected"{{/if}}>
      <td style="font-weight: bold;">
        <a href="#1" onclick="return loadSelection({{$_selection->_id}})">
          {{mb_value object=$_selection field=name}}
        </a>
      </td>
      <td>
        {{$_selection->_count.selection_items}}
      </td>
    </tr>
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CProductSelection.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>