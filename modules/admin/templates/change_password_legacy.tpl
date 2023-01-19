{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=change_pwd ajax=1}}
{{mb_script module=system script=input_percent_circle ajax=1}}

{{if !$user->canChangePassword()}}
  <div class="small-warning">{{tr}}CUser-password_change_forbidden{{/tr}}</div>
{{else}}
  {{if !$conf.admin.CUser.custom_password_recommendations}}
    <script>
      Main.add(
        function() {
          ChangePwd.init(
            getForm('chpwFrm'),
            $('change_indicator'),
            '{{$user->user_username}}',
            {{$pw_spec->minLength}}
          );
        }
      );
    </script>
  {{/if}}
  <table class="layout">
    <tr>
      <td class="halfPane" style="vertical-align: center;">
        <form name="chpwFrm" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="admin"/>
          <input type="hidden" name="dosql" value="do_chpwd_aed"/>
          <input type="hidden" name="del" value="0"/>

          <input type="hidden" name="@token" value="{{$csrf_token}}" />

          <input type="hidden" name="callback"
                 value="{{if $forceChange}}ChangePwd.goHome{{else}}Control.Modal.close{{/if}}"/>
          {{if !$forceChange}}
            <input type="hidden" name="dialog" value="1" />
          {{else}}
            {{if $user->mustChangePassword()}}
              <div class="small-warning">
                <strong>{{tr}}common-msg-Your password have to be renewed.{{/tr}}</strong>
              </div>
            {{else}}
              <div class="small-warning">
                <strong>{{tr}}common-msg-no_good_security{{/tr}} </strong>.
                {{tr}}common-msg-info_security_mdp{{/tr}}
                <a href="http://mediboard.org/public/Recommandations+de+la+CNIL" target="_blank"> {{tr}}common-msg-recommendation_cnil{{/tr}}</a>.

                {{if $lifeDuration}}
                  <br /><br />
                  <strong>{{tr}}common-msg-mdp_no_change_during{{/tr}} {{tr}}config-admin-CUser-password_life_duration-{{$lifetime}}{{/tr}}.</strong>
                {{/if}}
              </div>
            {{/if}}
          {{/if}}
          {{if $user->checkActivationToken()}}
            <div class="small-info">
              <p>{{tr}}common-msg-Welcome. In order to continue, you have to set your account password.{{/tr}}</p>
              <p>{{tr var1="<strong>`$user->user_username`</strong>"}}common-msg-You username is %s. Take care about it.{{/tr}}</p>
            </div>
          {{/if}}
          <table class="form me-no-box-shadow">
            <tbody>
            {{if !$user->checkActivationToken()}}
              <tr>
                {{me_form_field nb_cells=2 mb_object=$user mb_field=user_username}}
                  <div class="me-field-content">
                    {{mb_value object=$user field=user_username}}
                  </div>
                {{/me_form_field}}
              </tr>
              <tr>
                {{me_form_field nb_cells=2 label=CUser-user_password-current}}
                  <input class="notNull str" type="password" name="old_pwd" />
                {{/me_form_field}}
              </tr>
            {{/if}}
            <tr>
              {{me_form_field nb_cells=2 label=CUser-user_password-new}}
                <input class="notNull" type="password" name="new_pwd1" onkeyup="ChangePwd.newPwd1OC()"/>
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 label='Repeat new password'}}
                <input class="notNull" type="password" name="new_pwd2" onkeyup="ChangePwd.newPwd2OC()"/>
              {{/me_form_field}}
            </tr>
            <tr>
              <td class="button" colspan="2">
                <button class="save" type="submit">{{tr}}Save{{/tr}}</button>
              </td>
            </tr>

            </tbody>
          </table>
        </form>
      </td>
      <td class="halfPane">
        {{if $conf.admin.CUser.custom_password_recommendations}}
          <div class="small-info text markdown">
            {{$conf.admin.CUser.custom_password_recommendations|markdown|purify}}
          </div>
        {{else}}
          <div id="change_indicator">
            <table class="main">
              <tr>
                <td>
                  <div class="indicator-circle">
                  </div>
                  <ul class="indicator-list">
                    <li class="indicator not-containing">
                      <i class="fas"></i>
                      <span>
                        {{tr}}common-error-Must not contains{{/tr}}{{'_user_username'|str_replace:$user->user_username:$pw_spec->notContaining}}
                      </span>
                    </li>
                    <li class="indicator not-near">
                      <i class="fas"></i>
                      <span>
                        {{tr}}common-error-Must be not near{{/tr}}{{'_user_username'|str_replace:$user->user_username:$pw_spec->notNear}}
                      </span>
                    </li>
                    {{if $pw_spec->alphaChars}}
                      <li class="indicator alpha-chars">
                        <i class="fas"></i>
                        <span>
                          {{tr}}common-error-Must contain one char without accent{{/tr}}
                        </span>
                      </li>
                    {{/if}}
                    {{if $pw_spec->numChars}}
                      <li class="indicator num-chars">
                        <i class="fas"></i>
                        <span>
                          {{tr}}common-error-Must contain at least one number{{/tr}}
                        </span>
                      </li>
                    {{/if}}
                    {{if $pw_spec->alphaUpChars}}
                      <li class="indicator caps-chars">
                        <i class="fas"></i>
                        <span>
                          {{tr}}common-error-Must contain at least one uppercase character (without diacritic){{/tr}}
                        </span>
                      </li>
                    {{/if}}
                    {{if $pw_spec->specialChars}}
                      <li class="indicator spec-chars">
                        <i class="fas"></i>
                        <span>
                          {{tr}}common-error-Must contain at least one special character{{/tr}}
                        </span>
                      </li>
                    {{/if}}
                    <li class="indicator min-length">
                      <i class="fas"></i>
                      <span>
                      {{tr var1=$pw_spec->minLength}}common-error-Must contain at least x chars{{/tr}}
                    </span>
                    </li>
                    <li class="indicator same-pwd">
                      <i class="fas"></i>
                      <span>
                        {{tr}}common-error-Password must must be identicals{{/tr}}
                      </span>
                    </li>
                  </ul>
                </td>
              </tr>
            </table>
          </div>
        {{/if}}
      </td>
    </tr>
  </table>
{{/if}}
