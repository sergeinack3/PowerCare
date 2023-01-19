{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>
      {{tr}}CMbFieldSpec.type.ipAddress{{/tr}}
    </th>
    <th>
      {{tr}}HTTP-code{{/tr}}
    </th>
    <th>
      {{tr}}Result{{/tr}}
    </th>
  </tr>
  {{foreach from=$result_send key=ip item=_result}}
    <tr>
      <td>
        {{$ip}}
      </td>
      <td>
        {{$_result.code}}
      </td>
      <td>
        {{$_result.body|smarty:nodefaults}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">
        {{tr}}No result{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>