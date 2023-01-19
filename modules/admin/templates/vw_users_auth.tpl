{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=user_auth ajax=1}}

<script>
  Main.add(function () {
    var form = getForm('search-users-auth');
    
    UserAuth.makeUserAutocomplete(form, form.elements._user_autocomplete);
    
    form.onsubmit();
    
    Control.Tabs.create("auth-control-tab", true);
  });
</script>

<form name="search-users-auth" method="get" onsubmit="return UserAuth.submitAuthFilter(this)">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="ajax_search_users_auth" />
  <input type="hidden" name="type" value="success" />
  <input type="hidden" name="start" value="0" />
  {{mb_field object=$user_auth field=user_id hidden=true canNull=true}}
  
  <table class="main form">
    <tr>
      <th>{{mb_label class=CUserAuthentication field=datetime_login}}</th>
      <td>
        {{mb_field object=$user_auth field=_start_date form='search-users-auth' register=true}}
        &raquo;
        {{mb_field object=$user_auth field=_end_date form='search-users-auth' register=true}}
      </td>
  
      <th>{{mb_label object=$user_auth field=user_id}}</th>
      <td>
        <input type="text" name="_user_autocomplete" class="autocomplete"
               value="{{$user_auth->_ref_user}}" />
    
        <button type="button" class="erase notext compact" onclick="$V(this.form.elements.user_id, '');
        $V(this.form.elements._user_autocomplete, '');"></button>
      </td>

      <th>{{mb_label object=$user_auth field=session_id}} ({{tr}}common-Success{{/tr}})</th>
      <td>{{mb_field object=$user_auth field=session_id canNull=true}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label class=CUserAuthentication field=expiration_datetime}} ({{tr}}common-Success{{/tr}})</th>
      <td>
          {{mb_field object=$user_auth field=_expiration_start_date form='search-users-auth' register=true}}
        &raquo;
          {{mb_field object=$user_auth field=_expiration_end_date form='search-users-auth' register=true}}
      </td>

      <th>{{mb_label object=$user_auth_error field=login_value}} ({{tr}}common-Failure{{/tr}})</th>
      <td>{{mb_field object=$user_auth_error field=login_value canNull=true}}</td>
      
      <th>{{mb_label object=$user_auth_error field=identifier}} ({{tr}}common-Failure{{/tr}})</th>
      <td>{{mb_field object=$user_auth_error field=identifier canNull=true}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$user_auth field=auth_method}}</th>
      <td class="text">{{mb_field object=$user_auth field=_auth_method}}</td>

      <th>{{mb_label object=$user_auth field=ip_address}}</th>
      <td>{{mb_field object=$user_auth field=ip_address canNull=true}}</td>

      <th>{{mb_label object=$user_auth field=_user_type}} ({{tr}}common-Success{{/tr}})</th>
      <td>{{mb_field object=$user_auth field=_user_type typeEnum='radio' value='all'}}</td>
    </tr>

    <tr>
      <th>{{mb_label class=CUserAuthentication field=_session_type}} ({{tr}}common-Success{{/tr}})</th>
      <td>{{mb_field class=CUserAuthentication field=_session_type typeEnum='radio' value='all' onchange='UserAuth.updateExpirationDateFilter(this)'}}</td>
      <th>{{mb_label class=CUserAuthentication field=_domain}}</th>
      <td colspan="3">{{mb_field class=CUserAuthentication field=_domain typeEnum='radio' value='group'}}</td>
    </tr>
    
    <tr>
      <td class="button" colspan="6">
        <button type="button" class="stats" onclick="UserAuth.showUsersAuthStats();">{{tr}}Stats{{/tr}}</button>
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div class="me-padding-left-10 me-padding-right-10">
  <ul class="control_tabs" id="auth-control-tab">
    <li><a href="#users-auth-results-success">{{tr}}common-Success{{/tr}}</a></li>
    <li><a href="#users-auth-results-error">{{tr}}common-Failure{{/tr}}</a></li>
  </ul>
</div>

<div id="users-auth-results-success" class="me-padding-0"></div>
<div id="users-auth-results-error" class="me-padding-0"></div>
