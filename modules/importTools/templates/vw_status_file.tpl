{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{$file_path}}</h2>

<table class="main tbl">
  <tr>
    <th>{{tr}}importTools-migration-Exection date{{/tr}}</th>
    <th>{{tr}}importTools-migration-Total pat{{/tr}}</th>
    <th>{{tr}}importTools-migration-Current pat{{/tr}}</th>
    <th>{{tr var1=$type}}importTools-migration-Step %s{{/tr}}</th>

    {{if $type == 'export'}}
      <th>{{tr}}Size{{/tr}}</th>
      <th>{{tr}}importTools-migration-Size per pat{{/tr}}</th>
      <th>{{tr}}importTools-migration-Total size{{/tr}}</th>
    {{/if}}

    <th>{{tr}}Duration{{/tr}}</th>
    <th>{{tr}}importTools-migration-Total duration{{/tr}}</th>

    {{if $type == 'import'}}
      <th>
        {{tr}}importTools-migration-Error count{{/tr}}
        <a class="button download notext" title="{{tr}}importTools-migrationDownload errors file{{/tr}}" target="_blank"
          href="?m=importTools&raw=ajax_download_import_errors&file={{$file_path}}"></a>
      </th>
    {{/if}}
  </tr>

  {{foreach from=$status_lines item=_line}}
    <tr>
      <td align="center" title="{{$_line.last_update}}">{{$_line.last_update|date_format:$conf.datetime}}</td>
      <td align="right">{{$_line.patient_total|number_format:0:',':' '}}</td>
      <td align="right" {{if $_line.patient_current >= $_line.patient_total}}class="ok"{{/if}}>
        {{if $_line.patient_current > $_line.patient_total}}
          {{$_line.patient_total|number_format:0:',':' '}}
        {{else}}
          {{$_line.patient_current|number_format:0:',':' '}}
        {{/if}}
      </td>
      <td align="right">{{$_line.patient_count|number_format:0:',':' '}}</td>

      {{if $type == 'export'}}
        <td align="right">{{$_line.increase_size|decasi}}</td>
        <td align="right">{{$_line.size_per_pat|decasi}}</td>
        <td align="right">{{$_line.size|decasi}}</td>
      {{/if}}

      <td align="right" {{if $_line.last_duration > 60}}class="warning"{{/if}}>
        {{$_line.last_duration|number_format:3:',':''}} s
      </td>
      <td align="right" title="{{$_line.duration}}">
        {{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$_line.duration}}
      </td>

      {{if $type == "import"}}
        <td align="right">
          {{$_line.error_count|number_format:0:';':' '}}
        </td>
      {{/if}}
    </tr>
  {{/foreach}}
</table>