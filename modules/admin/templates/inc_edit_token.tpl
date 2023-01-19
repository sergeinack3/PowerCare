{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=CMediusers ajax=1}}

<script>
  showAllowedRoutes = function () {
    $('action-allow-routes-names').addClassName('hide');
    $('div-allow-routes-names').addClassName('show');
  };

  checkParams = function () {
    const form = getForm('edit-token');
    const input = form.params;
    let lines = $V(input).split(/[\r\n]+/);
    let params = {};
    lines.each(function (line) {
      const parts = line.split(/=/);
      params[parts[0]] = parts[1];
    });

    const module = params.m;
    const action = params.a || params.tab || params.dialog || params.ajax || params.raw || params.wsdl || params.info;

    $('params-action').className = action ? 'info' : 'warning';
    $('params-module').className = module ? 'info' : 'warning';
  };

  checkRoutes = function() {
    const form = getForm('edit-token');
    const input = form.routes_names;

    $('params-route').className  = $V(input) ? 'info' : 'warning';
  };

  Main.add(function () {
    checkParams();
    checkRoutes();
  });
</script>

<form name="edit-token" method="post" onsubmit="return onSubmitFormAjax(this, function(){ Control.Modal.close(); getForm('search-token').onsubmit(); });">
  {{mb_key object=$token}}

  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_token_aed" />

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
        <script>
          Main.add(CMediusers.standardAutocomplete.curry('edit-token', 'user_id', '_user_view'));
        </script>
        {{mb_field object=$token field=user_id hidden=1}}
        <input type="text" name="_user_view" style="width: 16em;" class="autocomplete"
               value="{{$token->_ref_user}}"
               onchange="if(!this.value) { this.form.user_id.value='' }" />
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
        {{mb_label object=$token field="params"}}
      </th>

      <td colspan="3">
        {{mb_field object=$token field="params" size=50 onkeyup="checkParams()"}}
        <div style="display: flex; flex-flow: row-reverse nowrap; width: 100%; align-items: center;">
          <div class="warning" id="params-action">Action</div>
          <div class="warning" id="params-module">Module</div>
        </div>
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$token field="routes_names"}}
      </th>

      <td colspan="3">
        <div class="showAllowRoutes {{if $token->routes_names}}hide{{/if}}" id="action-allow-routes-names">
          <button type="button" class="me-tertiary" onclick="showAllowedRoutes();">
            {{tr}}CViewAccessToken-Action-Show allowed routes{{/tr}}
          </button>
        </div>
        <div class="allowedRoutes {{if $token->routes_names}}show{{/if}}" id="div-allow-routes-names">
          {{mb_field object=$token field="routes_names" size=50 onkeyup="checkRoutes()"}}

          <div style="display: flex; flex-flow: row-reverse nowrap; width: 100%; align-items: center;">
            <div class="warning" id="params-route">{{tr}}common-Route{{/tr}}</div>
          </div>
        </div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$token field="restricted"}}</th>
      <td>{{mb_field object=$token field="restricted"}}</td>

      <th>{{mb_label object=$token field='purgeable'}}</th>
      <td>{{mb_field object=$token field='purgeable'}}</td>
    </tr>

    <tr>
      <th colspan="4" class="category">
        {{tr}}CViewAccessToken-title-validity{{/tr}}
      </th>
    </tr>

    {{mb_ternary var=readonly test=$token->_id value=true other=false}}
    <tr>
      <th>{{mb_label object=$token field=_hash_length}}</th>
      <td>
        {{if $token->_id}}
          {{mb_field object=$token field=_hash_length readonly=true}}
        {{else}}
          {{mb_field object=$token field=_hash_length form='edit-token' increment=true}}
        {{/if}}
      </td>

      <th>{{mb_label object=$token field=hash}}</th>
      <td>
        {{if $token->_id}}
          {{mb_field object=$token field=hash readonly=$readonly}}
        {{else}}
          {{mb_field object=$token field=hash canNull=true}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$token field="datetime_start"}}</th>
      <td>{{mb_field object=$token field="datetime_start" register=true form="edit-token"}}</td>
      <th>{{mb_label object=$token field="max_usages"}}</th>
      <td>{{mb_field object=$token field="max_usages" increment=true form="edit-token"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$token field="datetime_end"}}</th>
      <td>{{mb_field object=$token field="datetime_end" register=true form="edit-token"}}</td>
      <th>{{tr}}common-Validity{{/tr}}</th>
      <td>{{if $token->_validity_duration}}{{$token->_validity_duration.locale}}{{/if}}</td>
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
          <option value="" selected>{{tr}}CViewAccessToken-validator.select{{/tr}}</option>

          {{foreach from=$token->_validators item=_validator}}
            <option value="{{$_validator}}" {{if $token->validator == $_validator}}selected{{/if}}>
              {{$_validator}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    {{if $token->_id}}
      <tr>
        <th colspan="4" class="category">
          {{tr}}CViewAccessToken-title-statistics{{/tr}}
        </th>
      </tr>

      <tr>
        <th>{{mb_label object=$token field="first_use"}}</th>
        <td>{{mb_value object=$token field="first_use"}}</td>
        <th>{{mb_label object=$token field="total_use"}}</th>
        <td>{{mb_value object=$token field="total_use"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$token field="latest_use"}}</th>
        <td>{{mb_value object=$token field="latest_use"}}</td>
        <th>{{mb_label object=$token field="_mean_usage_duration"}}</th>
        <td>
            {{if $token->_mean_usage_duration}}
                {{$token->_mean_usage_duration.locale}}
            {{/if}}
        </td>
      </tr>
    {{/if}}

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
                    function(){ Control.Modal.close(); getForm('search-token').onsubmit(); }
                    )"
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
