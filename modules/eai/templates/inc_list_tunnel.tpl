{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="3" class="title">
      {{tr}}CHTTPTunnelObject-List-tunnels{{/tr}}
      <button type="button" class="change notext" onclick="CTunnel.refreshList()">
        {{tr}}Refresh{{/tr}}
      </button>
    </th>
  </tr>
  <tr>
    <th style="width: 33%">{{tr}}CHTTPTunnelObject-address{{/tr}}</th>
    <th style="width: 33%">{{tr}}CHTTPTunnelObject-status{{/tr}}</th>
    <th style="width: 33%">{{tr}}Action{{/tr}}</th>
  </tr>
  {{foreach from=$tunnels item=_tunnel}}
    <tr>
      <td>
        <button class="edit notext" onclick="CTunnel.editTunnel('{{$_tunnel->_id}}')">
          {{tr}}Edit{{/tr}}
        </button> {{$_tunnel->address}}
      </td>
      <td>
        {{unique_id var=uid}}
        <script>
          Main.add(CTunnel.verifyAvaibility.curry($('{{$uid}}')));
        </script>

        <i class="fa fa-circle" style="color:grey" id="{{$uid}}" data-id="{{$_tunnel->_id}}"
           data-guid="{{$_tunnel->_guid}}" title="{{$_tunnel->address}}"></i>
      </td>
      <td>
        <button type="button" class="bug compact notext" onclick="CTunnel.proxyAction('setlog', '{{$_tunnel->_id}}')">
          {{tr}}Debug{{/tr}}
        </button>
        <button type="button" class="tick compact notext" onclick="CTunnel.proxyAction('test', '{{$_tunnel->_id}}')">
          {{tr}}Test{{/tr}}
        </button>
        <button type="button" class="stop compact notext" onclick="CTunnel.proxyAction('stop', '{{$_tunnel->_id}}')">
          {{tr}}Stop{{/tr}}
        </button>
        <button type="button" class="stats compact notext" onclick="CTunnel.proxyAction('stat', '{{$_tunnel->_id}}')">
          {{tr}}Statistics{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr><td colspan="3" class="empty">{{tr}}CHTTPTunnelObject.none{{/tr}}</td></tr>
  {{/foreach}}
</table>