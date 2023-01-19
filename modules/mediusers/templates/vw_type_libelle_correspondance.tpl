{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{tr}}CUser-user_type{{/tr}}</th>
    <th>{{tr}}CUser-user_type.libelle{{/tr}}</th>
  </tr>

  {{foreach from=$types key=_number item=_value}}
    <tr>
      <td align="center">{{$_number}}</td>
      <td align="center">{{$_value}}</td>
    </tr>
  {{/foreach}}
</table>