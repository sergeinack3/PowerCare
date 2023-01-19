{{*
 * @package Mediboard\AppFine
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry("tabs-content", true));
</script>

<table class="form">
  <tr>
    <th class="title">
      {{tr}}{{$mobile_log->_class}}{{/tr}} - #{{$mobile_log->_id}}
    </th>
  </tr>
  <tr>
    <td>
      <ul id="tabs-content" class="control_tabs">
        <li><a href="#input">{{mb_title object=$mobile_log field="input"}}</a></li>
        <li><a href="#output">{{mb_title object=$mobile_log field="output"}}</a></li>
        <li><a href="#object">{{mb_title object=$mobile_log field="object"}}</a></li>
        <li><a href="#informations">{{tr}}CMobileLog-title-Informations device{{/tr}}</a></li>
      </ul>

      <div id="input" style="display: none;">
        {{mb_value object=$mobile_log field="input" export=true}}
      </div>

      <div id="output" style="display: none;">
        {{mb_value object=$mobile_log field="output" export=true}}
      </div>

      <div id="object" style="display: none;">
        {{if $mobile_log->origin}}
          <div class="small-info"> <strong>{{tr}}CMobileLog-origin{{/tr}}</strong> : {{mb_value object=$mobile_log field="origin"}} </div>
        {{/if}}

        {{mb_value object=$mobile_log field="object" export=true}}
      </div>

      <div id="informations" style="display: none;">
        <table class="tbl">
          <tr>
            <td>{{tr}}CMobileLog-device_uuid{{/tr}}</td>
            <td>{{mb_value object=$mobile_log field="device_uuid"}}</td>
          </tr>
          <tr>
            <td>{{tr}}CMobileLog-device_platform{{/tr}}</td>
            <td>{{mb_value object=$mobile_log field="device_platform"}}</td>
          </tr>
          <tr>
            <td>{{tr}}CMobileLog-device_platform_version{{/tr}}</td>
            <td>{{mb_value object=$mobile_log field="device_platform_version"}}</td>
          </tr>
          <tr>
            <td>{{tr}}CMobileLog-device_model{{/tr}}</td>
            <td>{{mb_value object=$mobile_log field="device_model"}}</td>
          </tr>
          <tr>
            <td>{{tr}}CMobileLog-internet_connection_type{{/tr}}</td>
            <td>{{mb_value object=$mobile_log field="internet_connection_type"}}</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
</table>
