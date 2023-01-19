{{*
 * @package Mediboard\admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $_token && $_token->_id}}
  <td>
    <button class="edit notext compact" onclick="ViewAccessToken.edit({{$_token->_id}}); return false;">
      {{tr}}Edit{{/tr}}
    </button>
  </td>
  <td class="text">
    {{if $_token->hash|strlen === 40}}
      {{math assign=length equation="x/2" x=$_token->hash|strlen}}
      <code>{{$_token->hash|substr:0:$length}} {{$_token->hash|substr:$length:$length}}</code>
    {{else}}
      <code>{{$_token->hash}}</code>
    {{/if}}
  </td>
  <td style="text-align: center;">
    {{if $_token->_fwd && array_key_exists('user_id', $_token->_fwd)}}
      {{assign var=_user_view value=$_token->_fwd.user_id->_view}}
    {{else}}
      {{assign var=_user_view value=$_token->user_id}}
    {{/if}}
    {{mb_ditto name=_user value=$_user_view center=true}}
  </td>
  <td class="text">
    {{mb_value object=$_token field=label}}
  </td>
  <td class="text">
    {{if $_token->_fwd && array_key_exists('module_action_id', $_token->_fwd)}}
      {{assign var=_module value=$_token->_fwd.module_action_id->module}}
      {{assign var=_action value=$_token->_fwd.module_action_id->action}}
      {{if $_module && $_action}}
        {{tr}}module-{{$_module}}-court{{/tr}} &gt; {{tr}}mod-{{$_module}}-tab-{{$_action}}{{/tr}}
      {{/if}}
      <div class="compact">{{mb_value object=$_token field=module_action_id}}</div>
    {{/if}}
  </td>
  <td class="text compact">
    {{foreach from=$_token->_params key=_param item=_value name=params}}
      {{if $smarty.foreach.params.iteration < 4}}
        <div>
          {{$_param}} = {{$_value}}
          {{if $smarty.foreach.params.iteration == 3 && count($_token->_params) > 3 }}
            ...
          {{/if}}
        </div>
      {{/if}}
    {{/foreach}}
  </td>
  <td style="text-align: center;" title="{{mb_value object=$_token field=restricted}}">
    {{mb_include module=system template=inc_vw_bool_icon value=$_token->restricted}}
  </td>
  <td style="text-align: center;" title="{{mb_value object=$_token field=purgeable}}">
    {{mb_include module=system template=inc_vw_bool_icon value=$_token->purgeable}}
  </td>
  <td>
    {{if $_token->datetime_start}}
      <div> &gt;= {{mb_value object=$_token field=datetime_start}}</div>{{/if}}
    {{if $_token->datetime_end  }}
      <div> &lt;= {{mb_value object=$_token field=datetime_end}}</div>{{/if}}
  </td>
  <td>
    {{if $_token->first_use && !$_token->latest_use}}
      = {{mb_value object=$_token field=first_use }}
    {{else}}
      {{if $_token->first_use }}
        <div> &gt;= {{mb_value object=$_token field=first_use }}</div>{{/if}}
      {{if $_token->latest_use}}
        <div> &lt;= {{mb_value object=$_token field=latest_use}}</div>{{/if}}
    {{/if}}
  </td>
  <td style="text-align: center;">{{$_token->max_usages|default:'&infin;'}}</td>
  <td style="text-align: center;">{{$_token->total_use|nozero}}</td>
  <td style="text-align: center;">
      {{if $_token->_mean_usage_duration !== null}}
        {{$_token->_mean_usage_duration.locale}}
      {{/if}}
  </td>
  <td>
    {{mb_value object=$_token field=validator}}
  </td>
  <td style="text-align: center">
    {{$_token->_count.jobs}}
  </td>
{{/if}}


