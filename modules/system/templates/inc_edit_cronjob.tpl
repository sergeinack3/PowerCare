{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector" ajax=true}}
{{mb_script module="admin" script="view_access_token" ajax=true}}
{{mb_script module=mediusers script=CMediusers ajax=1}}

<script>
  Main.add(function () {
    var form = getForm("editcronjob");
    CronJob.changeField(form._frequently);
  });

  triggerChange = function (elt) {
    if ($V(elt)) {
      elt.form.params.disable();
      elt.form.edit_token.show();
    }
    else {
      $V(elt.form.token_id, '');
      elt.form.params.enable();
      elt.form.edit_token.hide();
    }
  };

  generateTokenFromParams = function () {
    var form = getForm('editcronjob');
    $V(form._generate_token, '1');
    form.onsubmit();
  };

  editToken = function (elt) {
    var token_id = $V(elt.form.token_id);

    if (token_id) {
      ViewAccessToken.edit($V(elt.form.token_id));
    }

    return false;
  }
</script>

<form name="editcronjob" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
  {{mb_class object=$cronjob}}
  {{mb_key object=$cronjob}}
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_generate_token" value="0" />
  <input type="hidden" name="token_class" value="CViewAccessToken"/>

  <table class="form">
    <tr>
      {{if $cronjob->_id}}
      <th class="title modify text" colspan="2">
        {{mb_include module=system template=inc_object_idsante400 object=$cronjob}}
        {{mb_include module=system template=inc_object_history object=$cronjob}}

        {{tr}}{{$cronjob->_class}}-title-modify{{/tr}} '{{$cronjob}}'
        {{else}}
      <th class="title me-th-new" colspan="2">
        {{tr}}{{$cronjob->_class}}-title-create{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="name"}}</th>
      <td>{{mb_field object=$cronjob field="name"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="description"}}</th>
      <td>{{mb_field object=$cronjob field="description"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="active"}}</th>
      <td>{{mb_field object=$cronjob field="active"}}</td>
    </tr>
    <tr>
      {{assign var=token value=false}}
      {{if $cronjob->_token && $cronjob->_token->_id}}
        {{assign var=token value=true}}
      {{/if}}

      <th>
        {{mb_label object=$cronjob field="token_id"}}

        <button type="button" id="edit_token" class="edit notext compact" onclick="editToken(this);" {{if !$token}}style="display: none;"{{/if}}>
          {{tr}}CViewAccessToken-title-modify{{/tr}}
        </button>
      </th>
      <td>
        <input type="text" name="_view" readonly="readonly" value="{{if $token}}{{$cronjob->_token->_view}}{{/if}}"/>
        <input type="hidden" name="token_id" value="{{if $token}}{{$cronjob->_token->_id}}{{/if}}" onchange="triggerChange(this);"/>
        <button type="button" onclick="ObjectSelector.init()" class="search notext">{{tr}}Search{{/tr}}</button>
        <button type="button" onclick="$V(this.form.token_id, ''); $V(this.form._view, '');" class="erase notext">{{tr}}Erase{{/tr}}</button>

        <script type="text/javascript">
          ObjectSelector.init = function() {
            this.sForm     = "editcronjob";
            this.sView     = "_view";
            this.sId       = "token_id";
            this.sClass    = "token_class";
            this.onlyclass = "true";
            this.pop();
          }
        </script>
      </td>
    </tr>


      {{if !$token}}
        <tr>
          <th class="narrow">{{mb_title class=CViewAccessToken field=user_id}}</th>
            <td>
                <script>
                    Main.add(CMediusers.standardAutocomplete.curry('editcronjob', '_user_id', 'user_view'));
                </script>
                <input type="hidden" name="_user_id" />
                <input type="text" name="user_view" class="autocomplete" placeholder="&mdash; {{tr}}All{{/tr}}" />
            </td>
        </tr>
      {{/if}}

    <tr>
      <th>{{mb_label object=$cronjob field="params"}}</th>
      <td>
        {{if $token}}
          {{mb_field object=$cronjob field="params" disabled=true}}
        {{else}}
          {{mb_field object=$cronjob field="params"}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="servers_address"}}</th>
      <td>
        {{mb_field object=$cronjob field="servers_address"}}<br />
        <div style="width: 250px;">
          {{foreach from=$address item=_address}}
            <label style="display: block; float: left; padding-right: 5px;">
              <input type="checkbox" name="address" value="{{$_address}}"
                     onclick="CronJob.setServerAddress(this)"
                     {{if in_array($_address, $cronjob->_servers)}}checked{{/if}}>{{$_address}}
            </label>
          {{/foreach}}
        </div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="mode"}}</th>
      <td>{{mb_field object=$cronjob field="mode"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="execution" canNull=true}}</th>
      <td>{{mb_value object=$cronjob field="execution"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_frequently"}}</th>
      <td>{{mb_field object=$cronjob field="_frequently" emptyLabel="Choose" onchange="CronJob.changeField(this)"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_second"}}</th>
      <td>{{mb_field object=$cronjob field="_second" placeholder="0"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_minute"}}</th>
      <td>{{mb_field object=$cronjob field="_minute" placeholder="*"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_hour"}}</th>
      <td>{{mb_field object=$cronjob field="_hour" placeholder="*"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_day"}}</th>
      <td>{{mb_field object=$cronjob field="_day" placeholder="*"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_month"}}</th>
      <td>{{mb_field object=$cronjob field="_month" placeholder="*"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cronjob field="_week"}}</th>
      <td>{{mb_field object=$cronjob field="_week" placeholder="*"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $cronjob->_id}}
          {{if !$token}}
            <button title="{{tr}}CCronJob-action-Generate token-desc{{/tr}}" type="button" class="modify"
                    onclick="generateTokenFromParams(this.form);">{{tr}}CCronJob-action-Generate token{{/tr}}</button>
          {{/if}}
          <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, null, {onComplete: Control.Modal.close})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          <button title="{{tr}}CCronJob-action-Generate token-desc{{/tr}}" type="button" class="modify"
                  onclick="generateTokenFromParams();">{{tr}}CCronJob-action-Generate token{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
