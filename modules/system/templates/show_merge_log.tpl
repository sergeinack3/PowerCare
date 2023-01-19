{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <col style="width: 10%;" />

  <tr>
    <th>{{mb_label object=$merge_log field=user_id}}</th>
    <td>{{mb_value object=$merge_log field=user_id}}</td>

    <th>{{tr}}common-Status{{/tr}}</th>
    <td class="{{if $merge_log->wasSuccessful()}}merge-log-status-ok{{else}}merge-log-status-ko{{/if}}">
      &nbsp;
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=merge_checked}}</th>
    <td>{{mb_include module=system template=inc_vw_bool_icon value=$merge_log->merge_checked}}</td>

    <th>{{mb_label object=$merge_log field=fast_merge}}</th>
    <td>{{mb_include module=system template=inc_vw_bool_icon value=$merge_log->fast_merge}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=object_class}}</th>
    <td>{{mb_value object=$merge_log field=object_class}}</td>

    <th>{{mb_label object=$merge_log field=base_object_id}}</th>
    <td>
      {{if $merge_log->_ref_base && $merge_log->_ref_base->_id}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$merge_log->_ref_base->_guid}}');">
          {{$merge_log->_ref_base}}
        </span>
      {{else}}
        {{mb_value object=$merge_log field=base_object_id}}
      {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=object_ids}}</th>
    <td colspan="3">
      <ul>
        {{foreach from=$merge_log->_object_ids item=_object_id}}
          {{assign var=_ref_objects value=$merge_log->_ref_objects}}

          {{if isset($_ref_objects.$_object_id|smarty:nodefaults)}}
            {{assign var=_object value=$_ref_objects.$_object_id}}

            <li>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_object->_guid}}');">
                {{$_object}}
              </span>
            </li>
          {{else}}
            <li>{{$_object_id}}</li>
          {{/if}}
        {{/foreach}}
      </ul>
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=date_start_merge}}</th>
    <td>{{mb_value object=$merge_log field=date_start_merge}}</td>

    <th>{{mb_label object=$merge_log field=date_before_merge}}</th>
    <td {{if !$merge_log->date_before_merge}} class="warning" {{/if}}>{{mb_value object=$merge_log field=date_before_merge}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=date_end_merge}}</th>
    <td {{if !$merge_log->date_end_merge}} class="warning" {{/if}}>{{mb_value object=$merge_log field=date_end_merge}}</td>

    <th>{{mb_label object=$merge_log field=date_after_merge}}</th>
    <td {{if !$merge_log->date_after_merge}} class="warning" {{/if}}>{{mb_value object=$merge_log field=date_after_merge}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=duration}}</th>
    <td>{{mb_value object=$merge_log field=duration}}</td>

    <th>{{mb_label object=$merge_log field=count_merged_relations}}</th>
    <td>{{mb_value object=$merge_log field=count_merged_relations}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$merge_log field=detail_merged_relations}}</th>

    <td colspan="3">
      <code>
        {{$merge_log->_detail_pretty|highlight:json}}
      </code>
    </td>
  </tr>

  <tr>
    <td colspan="4" class="button">
      {{if $merge_log->canBeMergedAgain()}}
        <button type="button" class="merge" onclick="MergeLog.mergeAgain('{{$merge_log->object_class}}', '{{$merge_log->getValidObjectIds()}}');">
          {{tr}}CMergeLog-action-Merge again{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>
