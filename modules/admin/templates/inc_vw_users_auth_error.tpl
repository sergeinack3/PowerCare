{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    Control.Tabs.setTabCount("users-auth-results-error", '{{$total}}');
  })
</script>

{{mb_include module=system template=inc_pagination change_page="UserAuth.changePageUserAuth.curry('error')" total=$total current=$start step=50}}

<table class="main tbl">
  <tr>
    <th colspan="2" class="narrow">{{mb_title class=CUserAuthenticationError field=datetime}}</th>
    <th class="narrow">{{mb_title class=CUserAuthenticationError field=user_id}}</th>
    <th class="narrow">{{mb_title class=CUserAuthenticationError field=login_value}}</th>
    <th class="narrow">{{mb_title class=CUserAuthenticationError field=auth_method}}</th>
    <th class="narrow">{{mb_title class=CUserAuthenticationError field=ip_address}}</th>
    <th class="narrow">{{mb_title class=CUserAuthenticationError field=identifier}}</th>
    <th>{{mb_title class=CUserAuthenticationError field=message}}</th>
  </tr>
  
  {{foreach from=$user_auths item=_user_auth}}
    {{mb_include module=admin template=inc_vw_user_auth_error_line}}
  {{foreachelse}}
  <tr>
    <td colspan="11" class="empty">
      {{tr}}CUserAuthenticationError.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>