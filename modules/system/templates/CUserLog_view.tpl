{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  ins {
    color: #1299DA !important;
    text-decoration: none;
  }

  del {
    color: #b92323 !important;;
  }
</style>

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{assign var=log value=$object}}


<table class="tbl">
  <tr>
    <th class="title text">
      {{$object}}
    </th>
  </tr>
  <tr>
    <td>
      {{foreach from=$object->_specs key=prop item=spec}}
        {{mb_include module=system template=inc_field_view}}
      {{/foreach}}
    </td>
  </tr>
</table>

{{if $log->_old_values}}

  {{$log->undiff_old_Values()}}
  <table class="main tbl">
    <tr>
      <th>{{tr}}Field{{/tr}}</th>
      <th colspan="2">{{tr}}CUserLog-values_before_after{{/tr}}</th>
    </tr>
    {{foreach from=$log->_old_values item=_field key=_field}}
      <tr>
        <td>
          <!-- title -->
          {{mb_label object=$log->_ref_object field=$_field}}
        </td>
        {{ if isset($log->_diff_values[$_field]|smarty:nodefaults) }}
          <td colspan="2" class="text">
            {{ $log->_diff_values[$_field]|smarty:nodefaults }}
          </td>
        {{else}}
          {{if array_key_exists($_field,$log->_old_values)}}
            <td colspan="2" class="text">
              <!-- old -->
              {{assign var=old_value value=$log->_old_values.$_field}}
              <del>
                {{mb_value object=$log->_ref_object field=$_field value=$old_value tooltip=1}}
              </del>
              <span style="padding-left: 2px; padding-right: 2px;">&#8594;</span>
              <!-- new -->
              {{assign var=log_id value=$log->_id}}
              {{if $log->_ref_object->_id}}
                {{assign var=new_value value=$log->_ref_object->_history.$log_id.$_field}}
              {{else}}
                {{assign var=new_value value=""}}
              {{/if}}
              <ins>
                {{mb_value object=$log->_ref_object field=$_field value=$new_value tooltip=1 accept_empty_value=true}}
              </ins>
            </td>
          {{else}}
            <td colspan="2" class="empty">{{tr}}Unavailable information{{/tr}}</td>
          {{/if}}
        {{/if}}

      </tr>
    {{/foreach}}

    <tr>
      <td class="button" colspan="3">
        <form name="process-{{$log->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="callback" value="location.reload"/>
          <input type="hidden" name="m" value="system"/>

          {{ if $log->_id > 1000000000 }}
            <input type="hidden" name="@class" value="CUserAction"/>
            <input type="hidden" name="user_action_id" value="{{$log->_id}}"/>
          {{else}}
            <input type="hidden" name="@class" value="CUserLog"/>
            {{mb_key object=$log}}
          {{/if}}

          {{if $log->_canUndo}}
            <input type="hidden" name="_undo" value="0"/>
            <button class="undo" onclick="$V(this.form._undo, 1)">{{tr}}Revoke{{/tr}}</button>
          {{/if}}
        </form>
      </td>
    </tr>
  </table>
{{/if}}
