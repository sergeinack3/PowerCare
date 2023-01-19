{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{tr}}Parameter{{/tr}}</th>
    <th>{{tr}}Property{{/tr}}</th>
    <th>{{tr}}Values{{/tr}}</th>
  </tr>

  {{foreach from=$props key=_name item=_prop}}
  <tr>
    <td>{{$_name}}</td>
    <td>{{$_prop}}</td>
    <td>{{$params->$_name}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="3">{{tr}}CView-parameters-none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>