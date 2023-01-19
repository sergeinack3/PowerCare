{{*
 * @package Mediboard\AppFine
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if isset($total|smarty:nodefaults)}}
  {{mb_include module=system template=inc_pagination total=$total current=$page change_page='changePage'}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow"></th>
    <th>{{tr}}CMobileLog-log_datetime{{/tr}}</th>
    <th>{{tr}}CMobileLog-url{{/tr}}</th>
    <th>{{tr}}CMobileLog-application_name{{/tr}}</th>
    <th>{{tr}}CMobileLog-level{{/tr}}</th>
    <th>{{tr}}CMobileLog-device_platform{{/tr}}</th>
    <th>{{tr}}CMobileLog-description{{/tr}}</th>
  </tr>

  {{foreach from=$mobile_logs item=_mobile_log}}
    <tr>
      <td>
        {{mb_include module=system template=inc_object_notes object=$_mobile_log float="left"}}
      </td>
      <td>
        <button type="button" onclick="api.viewMobileLog('{{$_mobile_log->_guid}}')" class="search">{{$_mobile_log->_id}} </button>
      </td>
      <td>
        {{mb_value object=$_mobile_log field="log_datetime" format=relative}}
      </td>
      <td>
        {{mb_value object=$_mobile_log field="url"}}
      </td>
      <td>
        {{mb_value object=$_mobile_log field="application_name"}}
      </td>
      <td>
        {{mb_value object=$_mobile_log field="level"}}
      </td>
      <td>
        {{mb_value object=$_mobile_log field="device_platform"}}
      </td>
      <td class="text compact">
        <span style="white-space: nowrap;">{{$_mobile_log->description|spancate:100}}</span>
      </td>
    </tr>
  {{/foreach}}
</table>