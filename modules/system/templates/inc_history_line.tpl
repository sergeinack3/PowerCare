{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$logs item=_log}}
  {{assign var=ref_object value=$_log->_ref_object}}
  <tbody class="hoverable">
  <tr {{if $_log->type != "store"}} style="font-weight: bold" {{/if}}>
    {{assign var=field_count value=0}}

    {{if $_log->_fields}}
      {{assign var=field_count value=$_log->_fields|@count}}
    {{/if}}

    <td>
      <a style="float:right;" href="#1" onclick="HistoryViewer.displayHistory('{{$_log->object_class}}', '{{$_log->object_id}}')">
        <i class="fa fa-history"></i>
      </a>
      <label title="{{$_log->object_class}}">{{tr}}{{$_log->object_class}}{{/tr}}</label>
      ({{$_log->object_id}})
    </td>
    <td class="text">
      {{if $ref_object->_id}}
        <label onmouseover="ObjectTooltip.createEx(this, '{{$ref_object->_guid}}');">
          {{$ref_object}}
        </label>
      {{else}}
        {{$ref_object}}
        {{** if $_log->extra}}
          - {{$_log->extra|truncate:120:"...":true}}
        {{/if**}}
      {{/if}}
    </td>
    <td>{{mb_value object=$_log field=ip_address}}</td>
    <td style="text-align: center;">
      <label onmouseover="ObjectTooltip.createEx(this, '{{$_log->_ref_user->_guid}}');">
        {{mb_ditto name=user value=$_log->_ref_user->_view}}
      </label>
    </td>
    <td style="text-align: center;">
      {{mb_ditto name=date value=$_log->date|date_format:$conf.date}}
    </td>
    <td style="text-align: center;">
      <span title="{{$_log->date|iso_time}}">{{mb_ditto name=time value=$_log->date|date_format:$conf.time}}</span>
    </td>
    {{if $app->user_prefs.displayUTCDate}}
      <td rowspan="{{$field_count}}" class="narrow"
          style="text-align: center;">{{mb_ditto name=utc_date value=$_log->date|utc_datetime}}</td>
    {{/if}}
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_log->_guid}}')">
        {{$_log->getTypeIco()|smarty:nodefaults}}
        {{mb_value object=$_log field=type}}
      </span>
    </td>
  </tr>
  </tbody>
  {{foreachelse}}
  <tr>
    <td colspan="20" class="empty">{{tr}}CUserLog.none{{/tr}}</td>
  </tr>
{{/foreach}}