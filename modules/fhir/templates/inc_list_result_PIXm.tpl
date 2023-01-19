{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>Name</th>
    <th>System</th>
    <th>Value</th>
  </tr>

  {{foreach from=$results item=_result}}
    <tr>
      <td>{{$_result.name}}</td>
      <td>{{$_result.system}}</td>
      <td>{{$_result.value}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">{{tr}}No result{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>