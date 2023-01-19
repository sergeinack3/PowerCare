{{*
 * @package Mediboard\API_tiers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_configure module=$m}}

  <table class="main form">

      {{foreach from=$liste_apis item=_api}}
        <tr>
          <th class="category" colspan="2">{{tr}}{{$_api}}{{/tr}}</th>
        </tr>
        <tr>
          <th>
            <label title="{{tr}}config-api-{{$_api}}-api_mediuser_id-desc{{/tr}}">{{tr}}config-api-{{$_api}}-title-userAPI{{/tr}}</label>
          </th>
          <td>
              {{assign var=val_default value=$conf.$m.$_api.user_api_id}}
            <input type="hidden" name="{{$m}}[{{$_api}}][user_api_id]" value="{{$val_default}}">
            <select onchange="api.selectIdUser(this, '{{$m}}[{{$_api}}][user_api_id]')">
              <option value="" {{if $val_default !== ""}}selected{{/if}}>{{tr}}Choose{{/tr}}</option>
                {{foreach from=$users item=_user}}
                  <option value="{{$_user->_id}}" {{if $val_default === $_user->_id}}selected{{/if}}>{{$_user->_view}}</option>
                {{/foreach}}
            </select>
          </td>
        </tr>

        {{if $_api === "CFitbitAPI"}}
          {{mb_include module=system class=$_api template=inc_config_bool var='enabled_validation'}}

          {{mb_include module=system class=$_api template=inc_config_str var='token_validation'}}
        {{/if}}
      {{/foreach}}

    <tr>
      <th class="category" colspan="2">{{tr}}CAPITiers-msg-callback url{{/tr}}</th>
    </tr>

      {{mb_include module=system template=inc_config_str var='url_callback'}}

    <tr>
      <th class="category" colspan="2">{{tr}}config-api-msg-configuration{{/tr}}</th>
    </tr>

      {{mb_include module=system template=inc_config_str var='number_request'}}

    <tr>
      <th class="category" colspan="2">{{tr}}config-CAPITiersStackRequest-msg-configuration{{/tr}}</th>
    </tr>
      {{mb_include module=system template=inc_config_str class=CAPITiersStackRequest var='purge_probability'}}
      {{mb_include module=system template=inc_config_str class=CAPITiersStackRequest var='purge_empty_threshold'}}
      {{mb_include module=system template=inc_config_str class=CAPITiersStackRequest var='purge_delete_threshold'}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>