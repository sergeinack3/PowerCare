{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{mb_title object=$incrementer field=object_class}}</th>
    <th>{{mb_title object=$incrementer field=value}}</th>
    <th>{{mb_title object=$incrementer field=pattern}}</th>
  </tr>
  {{foreach from=$incrementers item=_incrementer}}
    <tr {{if $_incrementer->_id == $incrementer->_id}}class="selected"{{/if}}
        onclick="showIncrementer('{{$_incrementer->_id}}', this)">
      <td>{{mb_value object=$_incrementer field=object_class}}</td>
      <td>{{mb_value object=$_incrementer field=value}}</td>
      <td>{{mb_value object=$_incrementer field=pattern}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}CIncrementer.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>