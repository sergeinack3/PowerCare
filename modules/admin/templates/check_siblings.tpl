{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{mb_title class=CUser field=user_username}}</th>
    <th>{{tr}}CUser{{/tr}}</th>
  </tr>

  {{foreach from=$siblings key=user_name item=users}}
  <tr>
    <td>{{$user_name}}</td>
    <td>
      {{foreach from=$users item=_user}}
        <a href="?m=admin&tab=vw_edit_users&user_id={{$_user->_id}}">{{$_user}}</a>
      {{/foreach}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="2">{{tr}}CUser-message-nosiblings{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
