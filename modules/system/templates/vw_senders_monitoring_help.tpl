{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{tr}}Name{{/tr}}</th>
    <th>{{tr}}Description{{/tr}}</th>
  </tr>

  {{foreach from=$infos key=_field item=_desc}}
    <tr>
      <td class="{{$_desc.1}}">{{$_field}}</td>
      <td class="text">{{$_desc.0}}</td>
    </tr>
  {{/foreach}}
</table>
