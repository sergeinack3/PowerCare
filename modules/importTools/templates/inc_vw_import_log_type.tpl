{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Form pour update la liste -->
<table class="main tbl me-no-align me-no-box-shadow">
  <tr>
    {{assign var=changePage value="$type|$import_mod_name|$import_class_name|$date_log_min|$date_log_max"}}
    {{mb_include module=system template=inc_pagination total=$logs.count current=$page step=50 change_page="CronJobImport.changePage" change_page_arg=$changePage}}
  </tr>
  <tr>
    <th class="narrow">{{tr}}CImportCronLogs-import-mod-name{{/tr}}</th>
    <th class="narrow">{{tr}}CImportCronLogs-import-class-name{{/tr}}</th>
    <th class="narrow">{{tr}}CImportCronLogs-date-log-show{{/tr}}</th>
    <th>{{tr}}CImportCronLogs-text{{/tr}}</th>
  </tr>
  {{if !$logs.data}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}mod-importTools-log-error-none{{/tr}}.
      </td>
    </tr>
  {{else}}
    {{foreach from=$logs.data item=_log}}
      <tr>
        <td>{{tr}}module-{{$_log.import_mod_name}}-court{{/tr}}</td>
        <td>{{$_log.import_class_name}}</td>
        <td class="compact">{{$_log.date_log}}</td>
        <td class="text">{{$_log.text}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>