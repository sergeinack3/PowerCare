{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=user_auth register=true}}

{{if $auth->_id && !$auth->_can->read}}
  <div class="small-info">
      {{tr}}{{$auth->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
    {{mb_return}}
{{/if}}

{{assign var=session_exists value='Ox\Core\Sessions\CSessionHandler::exists'|static_call:$auth->session_id}}

<table class="main layout">
  <tr>
    <td>
      <fieldset>
        <legend>Client</legend>

        <table class="main form">
          <tr>
            <th>{{mb_label class=CUserAuthentication field=user_id}}</th>
            <td>{{mb_value object=$auth field=user_id tooltip=true}}</td>

            <th>{{mb_label class=CUserAuthentication field=auth_method}}</th>
            <td>{{mb_value object=$auth field=auth_method}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=_user_type}}</th>
            <td>{{mb_value object=$auth field=_user_type}}</td>

            <th>{{mb_label class=CUserAuthentication field=previous_auth_id}}</th>
            <td>{{mb_value object=$auth field=previous_auth_id tooltip=true}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=ip_address}}</th>
            <td>{{mb_value object=$auth field=ip_address}}</td>

            <th>{{mb_label class=CUserAuthentication field=authentication_factor_id}}</th>
            <td>{{mb_value object=$auth field=authentication_factor_id tooltip=true}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=screen_width}}</th>
            <td>{{mb_value object=$auth field=screen_width}}</td>

            <th>{{mb_label class=CUserAuthentication field=user_agent_id}}</th>
            <td>{{mb_value object=$auth field=user_agent_id tooltip=true}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=screen_height}}</th>
            <td colspan="3">{{mb_value object=$auth field=screen_height}}</td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>

  <tr>
    <td>
      <fieldset>
        <legend>Session</legend>

        <table class="main form">
          <tr>
            <th>{{mb_label class=CUserAuthentication field=datetime_login}}</th>
            <td>{{mb_value object=$auth field=datetime_login}}</td>

            <th>{{mb_label class=CUserAuthentication field=last_session_update}}</th>
            <td>{{mb_value object=$auth field=last_session_update}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=expiration_datetime}}</th>
            <td>{{mb_value object=$auth field=expiration_datetime}} ({{mb_value object=$auth field=_session_type}})</td>

            <th>{{mb_label class=CUserAuthentication field=session_lifetime}}</th>
            <td>{{mb_value object=$auth field=_session_duration}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=nb_update}}</th>
            <td>{{mb_value object=$auth field=nb_update}}</td>

            <th>{{mb_label class=CUserAuthentication field=session_id}}</th>
            <td><code>{{$auth->session_id|truncate:12}}</code></td>
          </tr>

          <tr>
            <th>{{mb_label class=CUserAuthentication field=_activity_duration}}</th>
            <td>{{mb_value object=$auth field=_activity_duration}}</td>

            <th>Activité</th>
            <td>
                {{assign var=activity_ratio value=$auth->getActivityRatio()}}

              <script>
                Main.add(function() {
                  ProgressMeter.init('user-auth-activity-{{$auth->_id}}', '{{$activity_ratio}}');
                });
              </script>

              <div id="user-auth-activity-{{$auth->_id}}" style="width: 20px; height: 20px; display: inline-block;" title="{{$activity_ratio}} %"></div>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>

  <tr>
    <td class="button">
      <button type="button" class="trash" {{if !$session_exists}}disabled="disabled"{{/if}}
              onclick="Modal.confirm('Êtes-vous certain de vouloir détruire cette session ?', {onOK: UserAuth.destroySession.curry('{{$auth->session_id}}')})">
        Déconnecter l'utilisateur
      </button>
    </td>
  </tr>
</table>
