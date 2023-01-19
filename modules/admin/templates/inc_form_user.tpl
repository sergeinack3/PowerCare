{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=password_spec ajax=true}}

<script>
  Main.add(function () {
    var form = getForm('Edit-user');
    warnRobot(form);

    // LDAP linked user cannot change password here
    {{if !$user->_id  || ($user->_id && !$user->_ldap_linked)}}
    PasswordSpec.init(
      {{$password_configuration|@json}},
      '{{$weak_prop}}', '{{$strong_prop}}', '{{$ldap_prop}}', '{{$admin_prop}}'
    );

    {{if $user->_id && $user->_ref_mediuser}}
    PasswordSpec.values.remote = parseInt('{{$user->_ref_mediuser->remote}}');
    {{/if}}

    PasswordSpec.registerUsernameField(form.user_username);
    PasswordSpec.registerTypeField(form.user_type);
    PasswordSpec.registerPasswordField(form._user_password);
    PasswordSpec.registerPassword2Field(form._user_password2);

    PasswordSpec.observe();
    PasswordSpec.check();
    {{/if}}
  });

  duplicateUser = function (form) {
    var login = window.prompt($T('CUser-msg-Please, give a login name'));

    if (login === null) {
      return;
    }

    login = login.trim();

    if (!login) {
      alert($T('common-error-Missing parameter: %s', $T('CUser-user_username-desc')));
      return;
    }

    $V(form.elements._duplicate, '1');
    $V(form.elements._duplicate_username, login);

    if (checkForm(form)) {
      form.submit();
    }

    $V(form.elements._duplicate, '');
    $V(form.elements._duplicate_username, '');

    UserPermission.callback();
  };

  warnRobot = function (form) {
    var is_robot = ($V(form.elements.is_robot) === '1');
    var dont_log_connection = ($V(form.elements.dont_log_connection) === '1');

    toggleRobotWarning((is_robot && !dont_log_connection));
  };

  toggleRobotWarning = function (show) {
    var elt = $('robotMsg');
    elt.setVisible(show);
  }
</script>

<table class="main form">
  <tr>
    <th>{{mb_label object=$user field="user_username"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$user field="user_username"}}
        {{else}}
            {{mb_value object=$user field="user_username"}}
            {{mb_field object=$user field="user_username" hidden=true}}
        {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field="user_type"}}</th>
    <td>
      <select name="user_type" class="{{$user->_props.user_type}}"
              {{if !$is_admin && ($object && $object->_id && $object->isTypeAdmin())}} disabled{{/if}}>
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$utypes key=_key item=type}}
            <option value="{{$_key}}" {{if $_key == $user->user_type}}selected="selected"{{/if}}
                    {{if !$is_admin && $_key == 1}} disabled> {{$type}} ({{tr}}CMbFieldSpec.perm{{/tr}})
                {{else}} > {{$type}}{{/if}}
            </option>
          {{/foreach}}
      </select>

        {{if !$is_admin && ($object && $object->_id && $object->isTypeAdmin())}}
          <div class="small-info">
              {{tr}}CUser-error-You are not allowed to modify an admin user{{/tr}}
          </div>
        {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field="template"}}</th>
    <td>{{mb_field object=$user field="template"}}</td>
  </tr>

    {{if !$readOnlyLDAP}}
      <tr>
        <th><label for="_user_password"
                   title="{{tr}}CUser-Enter the password. Mandatory-desc{{/tr}}">{{tr}}CUser-_user_password{{/tr}}</label></th>
        <td><input type="password" name="_user_password" class="{{$specs._user_password}}{{if !$user->user_id}} notNull{{/if}}"
                   value=""
                   onkeyup="checkFormElement(this)" />
          <span id="editFrm__user_password_message"></span>
        </td>
      </tr>
      <tr>
        <th><label for="_user_password2"
                   title="{{tr}}CUser-Re-Enter the password to confirm. Mandatory-desc{{/tr}}">{{tr}}CUser-_user_password bis{{/tr}}</label>
        </th>
        <td><input type="password" name="_user_password2" class="password sameAs|_user_password" value="" /></td>
      </tr>
    {{/if}}

  <tr>
    <th>{{mb_label object=$user field="user_last_name"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$user field="user_last_name"}}
        {{else}}
            {{mb_value object=$user field="user_last_name"}}
            {{mb_field object=$user field="user_last_name" hidden=true}}
        {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field="user_first_name"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$user field="user_first_name"}}
        {{else}}
            {{mb_value object=$user field="user_first_name"}}
            {{mb_field object=$user field="user_first_name" hidden=true}}
        {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field="user_email"}}</th>
    <td>
        {{if !$readOnlyLDAP}}
            {{mb_field object=$user field="user_email"}}
        {{else}}
            {{mb_value object=$user field="user_email"}}
            {{mb_field object=$user field="user_email" hidden=true}}
        {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field=is_robot}}</th>
    <td>
      <div id="robotMsg" class="small-warning" style="display: none;">
          {{tr}}CUser-msg-This user is a robot, but its connections are logged, are you sure?{{/tr}}
      </div>

        {{mb_field object=$user field=is_robot onchange='warnRobot(this.form);'}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field="dont_log_connection"}}</th>
    <td>{{mb_field object=$user field="dont_log_connection" onchange='warnRobot(this.form);'}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field=force_change_password}}</th>
    <td>{{mb_field object=$user field=force_change_password}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$user field=allow_change_password}}</th>
    <td>{{mb_field object=$user field=allow_change_password}}</td>
  </tr>

  <tr>
    <td class="button" colspan="2">
        {{if $user->_id}}
            {{if $user->isRobot()}}
              <button class="modify"
                      type="button"
                      onclick="CMediusers.confirmMediuserEdition(this.form)">
                  {{tr}}Save{{/tr}}
              </button>
            {{else}}
              <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            {{/if}}

          <button class="duplicate" type="button" onclick="duplicateUser(this.form);">
              {{tr}}Duplicate{{/tr}}
          </button>

          <button class="trash" type="button"
                  onclick="CMediusers.confirmMediuserDeletion(this.form, '{{$user->isRobot()}}')">
              {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
    </td>
  </tr>

  <!-- Link to CMediusers -->
  <tr>
    <td colspan="2" class="button">
        {{if $user->_ref_mediuser && $user->_ref_mediuser->_id}}
          <div class="small-success">
              {{tr}}CMediusers-msg-This user is well integrated into the organization chart{{/tr}}
            <br />
            <a class="button edit" href="?m=mediusers&tab=vw_idx_mediusers&user_id={{$user->_id}}">
                {{tr}}CMediusers-action-Manage this user in the organization chart{{/tr}}
            </a>
          </div>
        {{else}}
            {{if $user->template}}
              <div class="small-info">
                  {{tr}}CMediusers-msg-This user is not in the organization chart. This is normal for a Profile{{/tr}}
              </div>
            {{else}}
              <div class="small-warning">
                  {{tr}}CMediusers-msg-This user is not in the organization chart, This is abnormal for a real user{{/tr}}
                <br />
                <a class="button new" href="?m=mediusers&tab=vw_idx_mediusers&user_id={{$user->_id}}&no_association=1">
                    {{tr}}CMediusers-action-Associate this user with the organization chart{{/tr}}
                </a>
              </div>
            {{/if}}
        {{/if}}
    </td>
  </tr>

</table>
