{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=colspan value=15}}

{{mb_include module=system template=inc_pagination current=$start step=$step change_page='MergeLog.changePage'}}

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow" title="{{tr}}common-Status{{/tr}}">
      &nbsp;
    </th>

    <th class="narrow">{{mb_title class=CMergeLog field=user_id}}</th>
    <th class="narrow">{{mb_title class=CMergeLog field=object_class}}</th>
    <th class="narrow">{{mb_title class=CMergeLog field=base_object_id}}</th>
    <th class="narrow">{{mb_title class=CMergeLog field=object_ids}}</th>
    <th class="narrow">{{mb_title class=CMergeLog field=merge_checked}}</th>
    <th class="narrow">{{mb_title class=CMergeLog field=fast_merge}}</th>
    <th class="narrow">{{mb_colonne class=CMergeLog field=duration order_col=$order_col order_way=$order_way function='MergeLog.changeOrder'}}</th>
    <th class="narrow">{{mb_colonne class=CMergeLog field=date_start_merge order_col=$order_col order_way=$order_way function='MergeLog.changeOrder'}}</th>
    <th class="narrow">{{mb_colonne class=CMergeLog field=date_before_merge order_col=$order_col order_way=$order_way function='MergeLog.changeOrder'}}</th>
    <th class="narrow">{{mb_colonne class=CMergeLog field=date_after_merge order_col=$order_col order_way=$order_way function='MergeLog.changeOrder'}}</th>
    <th class="narrow">{{mb_colonne class=CMergeLog field=date_end_merge order_col=$order_col order_way=$order_way function='MergeLog.changeOrder'}}</th>
    <th class="narrow">{{mb_title class=CMergeLog field=count_merged_relations}}</th>
    <th>{{mb_title class=CMergeLog field=last_error_handled}}</th>
  </tr>

  {{foreach from=$merge_logs item=_merge_log}}
    <tr>
      <td>
        <button type="button" class="search notext compact" onclick="MergeLog.show('{{$_merge_log->_id}}');">
          {{tr}}common-action-Show{{/tr}}
        </button>
      </td>

      <td class="{{if $_merge_log->wasSuccessful()}}merge-log-status-ok{{else}}merge-log-status-ko{{/if}}"></td>

      <td>
        {{mb_value object=$_merge_log field=user_id tooltip=true}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=object_class}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=base_object_id}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=object_ids}}
      </td>

      <td style="text-align: center;">
        {{mb_include module=system template=inc_vw_bool_icon value=$_merge_log->merge_checked}}
      </td>

      <td style="text-align: center;">
        {{mb_include module=system template=inc_vw_bool_icon value=$_merge_log->fast_merge}}
      </td>

      <td style="text-align: right;">
        {{mb_value object=$_merge_log field=duration}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=date_start_merge}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=date_before_merge}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=date_after_merge}}
      </td>

      <td>
        {{mb_value object=$_merge_log field=date_end_merge}}
      </td>

      <td style="text-align: right;">
        {{mb_value object=$_merge_log field=count_merged_relations}}
      </td>

      <td class="text">
        {{mb_value object=$_merge_log field=last_error_handled}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="{{$colspan}}">
        {{tr}}CMergeLog.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
