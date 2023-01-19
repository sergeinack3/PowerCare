{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=user_auth ajax=true}}

<h3 onmouseover="ObjectTooltip.createEx(this, '{{$user->_guid}}')">
  {{$user}}

  <span style="float: right;">
    <span class="auth-count-errors" data-count="{{$user->_count.authentication_errors}}">
      {{$user->_count.authentication_errors|number_format:'0':',':' '}}
    </span>

    {{tr}}CUser-back-authentication_errors{{/tr}}

    {{if $user->dont_log_connection}}
      <button class="trash" onclick="UserAuth.purgeUserAuthentication('{{$user->_id}}', 1)">
        {{tr}}Purge{{/tr}}
      </button>
    {{/if}}
  </span>
</h3>

<table class="main tbl">
  <tr>
    <th>{{mb_title class=CUserAuthenticationError field=auth_method}}</th>
    <th>{{mb_title class=CUserAuthenticationError field=datetime}}</th>
    <th>{{mb_title class=CUserAuthenticationError field=ip_address}}</th>
    <th>{{mb_title class=CUserAuthenticationError field=identifier}}</th>
    <th>{{mb_title class=CUserAuthenticationError field=message}}</th>
  </tr>

  {{foreach from=$list item=_auth}}
    <tr>
      <td>{{mb_value object=$_auth field=auth_method}}</td>
      <td>{{mb_value object=$_auth field=datetime}}</td>
      <td>{{mb_value object=$_auth field=ip_address}}</td>
      <td>{{mb_value object=$_auth field=identifier}}</td>
      <td>{{$_auth->message|smarty:nodefaults|purify}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">{{tr}}CUserAuthenticationError.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
