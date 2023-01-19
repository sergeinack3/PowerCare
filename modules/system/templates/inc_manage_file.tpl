{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      {{tr}}List-of-file{{/tr}} {{if is_array($files)}}({{$files|@count}}){{/if}}
    </th>
  </tr>
  <tr>
    <th>
      {{tr}}Name{{/tr}}
    </th>
    <th>
      {{tr}}Size{{/tr}}
    </th>
    <th>
      {{tr}}Date{{/tr}}
    </th>
    <th>
      {{tr}}Owner{{/tr}}
    </th>
    <th>
      {{tr}}Action{{/tr}}
    </th>
  </tr>
  {{if is_array($files)}}
    {{foreach from=$files item=_file}}
      {{if $_file.type !== "d"}}
        <tr>
          <td class="text">
            <button type="button"
                    class="edit notext compact"
                    onclick="return ExchangeSource.renameFile('{{$source_guid}}', '{{$_file.name}}', '{{$current_directory}}')">
              {{tr}}Delete{{/tr}}
            </button>
            {{$_file.name|utf8_decode}}
          </td>
          <td>
            {{$_file.size}}
          </td>
          <td>
            <label title="{{$_file.date}}">{{$_file.date|rel_datetime}}</label>
          </td>
          <td>
            {{$_file.user}}
          </td>
          <td class="narrow compact">
            <a target="blank"
               href="?m=system&a=download_file&filename={{$_file.name}}&exchange_source_guid={{$source_guid}}&dialog=1&suppressHeaders=1"
               class="button download notext compact">
              {{tr}}Download{{/tr}}
            </a>
            <button type="button"
                    class="close notext compact"
                    onclick="ExchangeSource.deleteFile('{{$source_guid}}', '{{$_file.name}}', '{{$current_directory}}')">
              {{tr}}Delete{{/tr}}
            </button>
          </td>
        </tr>
      {{/if}}
      {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">
          {{tr}}No-file{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{else}}
    <tr>
      <td colspan="5" class="empty">{{tr}}Error-file{{/tr}}</td>
    </tr>
  {{/if}}
</table>
<button type="button" class="upload" onclick="ExchangeSource.addFileForm('{{$source_guid}}', '{{$current_directory}}')">
  {{tr}}Upload-file{{/tr}}
</button>