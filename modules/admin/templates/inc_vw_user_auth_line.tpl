{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=session_exists value='Ox\Core\Sessions\CSessionHandler::exists'|static_call:$user_auth->session_id}}

<td style="text-align: center;">
  <button type="button" class="edit notext compact" onclick="UserAuth.edit('{{$user_auth->_id}}');"
</td>

<td style="text-align: center;">
  {{assign var=css value='fas fa-sign-in-alt fa-lg'}}
  {{assign var=color value='forestgreen'}}
  {{assign var=title value='Ox\Core\CAppUI::tr'|static_call:'CUserAuthentication-title-The session is currently active'}}

  {{if !$user_auth->isCurrentlyActive()}}
    {{assign var=css value='fas fa-sign-out-alt fa-lg'}}
    {{assign var=color value='firebrick'}}
    {{assign var=title value='Ox\Core\CAppUI::tr'|static_call:'CUserAuthentication-title-The session has expired'}}
  {{/if}}

  <i class="{{$css}}" style="color: {{$color}};" title="{{$title}}"></i>
</td>

<td style="text-align: center;">
  {{if $user_auth->_ref_user->isRobot()}}
    <i class="fas fa-robot fa-lg" style="color: steelblue;" title="{{tr}}common-Bot{{/tr}}"></i>
  {{else}}
    <i class="fas fa-user fa-lg" style="color: forestgreen;" title="{{tr}}common-Human{{/tr}}"></i>
  {{/if}}
</td>

<td style="text-align: center;">
  {{mb_ditto name=date value=$user_auth->datetime_login|date_format:$conf.date}}
</td>

<td style="text-align: center;">
  {{mb_ditto name=time value=$user_auth->datetime_login|date_format:$conf.time}}
</td>

<td style="text-align: center;">
  {{mb_ditto name=expiration_date value=$user_auth->expiration_datetime|date_format:$conf.date}}
</td>

<td style="text-align: center;">
  {{mb_ditto name=expiration_time value=$user_auth->expiration_datetime|date_format:$conf.time}}
</td>

<td style="text-align: center;">
    {{if $dtnow < $user_auth->expiration_datetime}}
      <i class="far fa-clock fa-lg"></i>
    {{/if}}
</td>

<td style="text-align: center;">
  {{mb_value object=$user_auth field=auth_method}}
</td>

<td style="text-align: center;">
  {{assign var=activity_ratio value=$user_auth->getActivityRatio()}}

  <script>
    Main.add(function() {
      ProgressMeter.init('user-auth-activity-line-{{$user_auth->_id}}', '{{$activity_ratio}}');
    });
  </script>

  <div id="user-auth-activity-line-{{$user_auth->_id}}" style="width: 20px; height: 20px;" title="{{$activity_ratio}} %"></div>
</td>

<td>
  {{if $user_auth->_ref_previous_auth && $user_auth->_ref_previous_auth->_id}}
      <i class="fas fa-code-branch fa-lg" onmouseover="ObjectTooltip.createEx(this, '{{$user_auth->_ref_previous_auth->_guid}}');"></i>
  {{/if}}

  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user_auth->_ref_user->_ref_mediuser}}
</td>

<td>
  {{mb_value object=$user_auth field=ip_address}}
</td>

<td>
  {{mb_value object=$user_auth field=user_agent_id tooltip=true}}
</td>

<td class="narrow">
  <code>{{$user_auth->session_id|truncate:12}}</code>
</td>
