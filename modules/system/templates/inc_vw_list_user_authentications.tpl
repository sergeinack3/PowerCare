{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page="changeAuthPage" total=$total current=$start step=100}}

<table class="main tbl">
  <tr>
    <th>{{mb_title class=CUserAuthentication field=datetime_login}}</th>
    <th>{{mb_title class=CUserAuthentication field=ip_address}}</th>
    <th>{{mb_title class=CUserAuthentication field=auth_method}}</th>
    <th>{{mb_title class=CUserAuthentication field=user_id}}</th>
    <th>{{mb_title class=CUserAuthentication field=screen_width}}</th>
    <th>{{mb_title class=CUserAuthentication field=user_agent_id}}</th>
  </tr>

  {{foreach from=$auth_list item=_user_auth}}
    <tr>
      <td class="narrow">{{mb_value object=$_user_auth field=datetime_login}}</td>
      <td class="narrow">{{mb_value object=$_user_auth field=ip_address}}</td>
      <td class="narrow">{{mb_value object=$_user_auth field=auth_method}}</td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_auth->_ref_user->_ref_mediuser}}
      </td>
      <td>
        {{if $_user_auth->screen_width && $_user_auth->screen_height}}
          {{mb_value object=$_user_auth field=screen_width}}
          x
          {{mb_value object=$_user_auth field=screen_height}}
        {{/if}}
      </td>
      <td>{{mb_value object=$_user_auth field=user_agent_id tooltip=true}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CUserAuthentication.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
