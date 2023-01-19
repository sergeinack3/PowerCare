{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if is_array($result)}}
  <table class="tbl">
    <tr>
      <th class="title">
        {{tr}}CHTTPTunnelObject-Information-general{{/tr}}
      </th>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Start_date{{/tr}} : {{$result.start_date}}
      </td>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Timer{{/tr}} : {{$result.timer}}
      </td>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Memory{{/tr}} : {{$result.memory}}
      </td>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Memory_peak{{/tr}} : {{$result.memory_peak}}
      </td>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Hits{{/tr}} : {{$result.hits}}
      </td>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Data-sent{{/tr}} : {{$result.data_sent}}
      </td>
    </tr>
    <tr>
      <td>
        {{tr}}CHTTPTunnelObject-Data-received{{/tr}} : {{$result.data_received}}
      </td>
    </tr>
    <tr>
      <th class="section">
        {{tr}}CHTTPTunnelObject-Information-client{{/tr}}
      </th>
    </tr>
    <tr>
      <td {{if !$result.clients}}class="empty"{{/if}}>
  {{foreach from=$result.clients key=_key item=_client}}
      <table class="tbl">
        <tr>
          <th>{{tr}}CHTTPTunnelObject-Client{{/tr}} {{$_key}}</th>
        </tr>
        <tr>
          <td>
            {{tr}}CHTTPTunnelObject-Hits{{/tr}} : {{$_client.hits}}
          </td>
        </tr>
        <tr>
          <td>
            {{tr}}CHTTPTunnelObject-Data-sent{{/tr}} : {{if "data_sent"|array_key_exists:$_client}}{{$_client.data_sent}}{{/if}}
          </td>
        </tr>
        <tr>
          <td>
            {{tr}}CHTTPTunnelObject-Data-received{{/tr}} : {{if "data_received"|array_key_exists:$_client}}{{$_client.data_received}}{{/if}}
          </td>
        </tr>
      </table>
  {{foreachelse}}
    {{tr}}CHTTPTunnelObject-Client-none{{/tr}}
  {{/foreach}}
      </td>
    </tr>
  </table>
{{else}}
  {{tr}}Action-success{{/tr}}
{{/if}}