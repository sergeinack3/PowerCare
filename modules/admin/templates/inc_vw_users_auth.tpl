{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.setTabCount("users-auth-results-success", '{{$total}}');
  })
</script>

{{mb_include module=system template=inc_pagination change_page="UserAuth.changePageUserAuth.curry('success')" total=$total current=$start step=50}}

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow"></th>
    <th class="narrow"></th>

    <th colspan="2" class="narrow">{{mb_title class=CUserAuthentication field=datetime_login}}</th>
    <th colspan="3" class="narrow">{{mb_title class=CUserAuthentication field=expiration_datetime}}</th>
    <th class="narrow">{{mb_title class=CUserAuthentication field=auth_method}}</th>
    <th class="narrow" style="text-align: center;">
      <i class="fas fa-circle-notch fa-lg" title="{{tr}}CUserAuthentication-title-Activity ratio{{/tr}}"></i>
    </th>
    <th>{{mb_title class=CUserAuthentication field=user_id}}</th>
    <th class="narrow">{{mb_title class=CUserAuthentication field=ip_address}}</th>
    <th>{{mb_title class=CUserAuthentication field=user_agent_id}}</th>
    <th class="narrow">{{mb_title class=CUserAuthentication field=session_id}}</th>
  </tr>

    {{foreach from=$user_auths item=_user_auth}}
      <tr id="user_auth-line-{{$_user_auth->_id}}">
        {{mb_include module=admin template=inc_vw_user_auth_line user_auth=$_user_auth}}
      </tr>
        {{foreachelse}}
      <tr>
        <td colspan="12" class="empty">
            {{tr}}CUserAuthentication.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
</table>
