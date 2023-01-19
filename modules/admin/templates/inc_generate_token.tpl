{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=CMediusers ajax=1}}

<script>
  Main.add(function () {
    checkParams = function () {
      var form = getForm('edit-token');
      var input = form.params;
      var lines = $V(input).split(/[\r\n]+/);
      var params = {};
      lines.each(function (line) {
        var parts = line.split(/=/);
        params[parts[0]] = parts[1];
      });

      var module = params.m;
      var action = params.a || params.tab || params.dialog || params.ajax || params.raw || params.wsdl || params.info;

      $('params-action').className = action ? 'info' : 'warning';
      $('params-module').className = module ? 'info' : 'warning';
    };

    checkParams();
  });
</script>

<form name="edit-token" method="post" onsubmit="return onSubmitFormAjax(this, function(){Control.Modal.close();});">
  {{mb_key object=$token}}

  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_token_aed" />

  {{mb_field object=$token field=user_id hidden=true}}

  <table class="main form">
    <col style="width: 30%" />

    {{mb_include module=system template=inc_form_table_header object=$token colspan=4}}

    {{if $token->_id}}
      <tr>
        <th>{{mb_label object=$token field="hash"}}</th>
        <td colspan="3"><tt>{{mb_value object=$token field="hash"}}</tt></td>
      </tr>
    {{/if}}

    <tr>
      <th colspan="4" class="category">
        {{tr}}CViewAccessToken-title-behaviour{{/tr}}
      </th>
    </tr>

    <tr>
      <th>{{mb_label object=$token field=user_id}}</th>
      <td colspan="3">
        {{mb_value object=$token field=user_id tooltip=true}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$token field=label}}
      </th>

      <td colspan="3">
        {{mb_field object=$token field=label size=60}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$token field=_hash_length}}
      </th>

      <td colspan="3">
        {{mb_field object=$token field=_hash_length form='edit-token' increment=true}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$token field="params"}}
      </th>

      <td colspan="3">
        {{mb_field object=$token field="params" size=50 readonly="readonly" onkeyup="checkParams();"}}
        <div class="warning" id="params-action" style="float: right;">Action</div>
        <div class="warning" id="params-module" style="float: right;">Module</div>
      </td>
    </tr>

    <tr>
      <th colspan="4" class="category">
        {{tr}}CViewAccessToken-validator{{/tr}}
      </th>
    </tr>

    <tr>
      <th>{{mb_label object=$token field=validator}}</th>
      <td colspan="3">
        <select name="validator">
          <option value="" {{if !$token->validator}}selected{{else}}disabled{{/if}}>{{tr}}CViewAccessToken-validator.select{{/tr}}</option>

          {{foreach from=$token->_validators item=_validator}}
            <option value="{{$_validator}}" {{if $token->validator == $_validator}}selected{{else}}disabled{{/if}}>
              {{$_validator}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="4">
        {{if $token->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

          <button type="button" class="search"
                  onclick="prompt($T('common-msg-Copy then paste given URL'), '{{$token->_url|smarty:nodefaults|JSAttribute}}');">
            {{tr}}CViewAccessToken-action-Show link{{/tr}}
          </button>

          <a class="button link" target="_blank" href="{{$token->_url|smarty:nodefaults|JSAttribute}}">
            {{tr}}CViewAccessToken-action-Open link{{/tr}}
          </a>

          <button type="button" class="trash"
            onclick="confirmDeletion(
              this.form,
              {typeName:'',objName:'{{$token->_view|smarty:nodefaults|JSAttribute}}'},
              function(){Control.Modal.close();})"
          >
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
