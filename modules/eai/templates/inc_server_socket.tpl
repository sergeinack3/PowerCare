{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=hash_ps value=true}}

<tr>
  <td> {{$process_id}} </td>
  <td> {{$_process.port}} </td>
  <td> {{$_process.type}} </td>
  <td>
    {{if $_process && !array_key_exists("ps_name", $_process)}}
      <img class="status" id="{{$uid}}" data-id="{{$process_id}}" src="images/icons/status_red.png" title="Processus absent"/>
      {{assign var=hash_ps value=false}}
    {{else}}
      <img class="status" id="{{$uid}}" data-id="{{$process_id}}" src="images/icons/status_green.png" title="{{$_process.ps_name}}"/> <em>{{$_process.ps_name}}</em>
    {{/if}} 
  </td>
  <td>
      {{$_process.launched}}
  </td>
  <td>
    <button class="trash" onclick="SocketServer.trash('{{$process_id}}', '{{$uid}}')" type="button">{{tr}}Delete{{/tr}}</button>
    <button class="cancel" onclick="SocketServer.action('{{$_process.port}}', '{{$_process.type}}', '{{$process_id}}', '{{$uid}}', 'stop')" type="button" {{if !$hash_ps}}disabled{{/if}}>{{tr}}Stop{{/tr}}</button>
    <button class="change" onclick="SocketServer.action('{{$_process.port}}', '{{$_process.type}}', '{{$process_id}}', '{{$uid}}', 'restart')" type="button" {{if !$hash_ps || $_process.ps_name == "php.exe"}}disabled{{/if}}>{{tr}}Restart{{/tr}}</button>
    <button class="tick" onclick="SocketServer.action('{{$_process.port}}', '{{$_process.type}}', '{{$process_id}}', '{{$uid}}', 'test')" type="button" {{if !$hash_ps}}disabled{{/if}}>{{tr}}Test{{/tr}}</button>
    <button class="tick" onclick="SocketServer.action('{{$_process.port}}', '{{$_process.type}}', '{{$process_id}}', '{{$uid}}', 'stats')" type="button" {{if !$hash_ps}}disabled{{/if}}>Statistiques</button>
  </td>
  <td id="stats_{{$uid}}"></td>
</tr>