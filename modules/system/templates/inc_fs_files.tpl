{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="listFiles">
  <table class="main">
    <tr>
      <td>
        <table class="tbl">
          {{if is_array($files)}}
            <tr>
              <th colspan="6"> Liste des fichiers du dossier </th>
            </tr>
  
            <tr>
              <th class="narrow"></th>
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
            {{foreach from=$files item=_file}}
              {{if $_file.type !== "d"}}
                <tr>
                  <td class="text">
                    <button type="button"
                            class="edit notext compact"
                            onclick="return ExchangeSource.renameFile('{{$exchange_source->_guid}}', '{{$_file.name}}', '{{$current_directory}}')">
                      {{tr}}Delete{{/tr}}
                    </button>
                  </td>
                  <td>
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
                       href="?m=system&a=download_file&filename={{$current_directory}}{{$_file.name}}&exchange_source_guid={{$exchange_source->_guid}}&dialog=1&suppressHeaders=1"
                       class="button download notext compact">
                      {{tr}}Download{{/tr}}
                    </a>
                    <button type="button"
                            class="close notext compact"
                            onclick="ExchangeSource.deleteFile('{{$exchange_source->_guid}}', '{{$_file.name}}', '{{$current_directory}}')">
                      {{tr}}Delete{{/tr}}
                    </button>
                  </td>
                </tr>
              {{/if}}
            {{/foreach}}
          {{else}}
            <tr>
              <th>Impossible de lister les fichiers</th>
            </tr>
          {{/if}}
        </table>
      </td>
    </tr>
  </table>

  <button type="button" class="upload" onclick="ExchangeSource.addFileForm('{{$exchange_source->_guid}}', '{{$current_directory}}')">
    {{tr}}Upload-file{{/tr}}
  </button>
</div>