{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    Control.Tabs.create("redis-tabs", true);
  });
</script>

<h3>
  {{assign var=repl value=$server->_information}}

  Rôle: <strong style="text-transform: uppercase;">{{$repl.role}}</strong>

  &mdash;

  {{if $repl.role == "slave"}}
    connecté à : {{$repl.master_host}} : {{$repl.master_port}}
  {{else}}
    esclaves : {{$repl.connected_slaves}}
  {{/if}}
</h3>

<ul class="control_tabs" id="redis-tabs">
  <li><a href="#redis-info">Info</a></li>
  <li><a href="#redis-keys">Clés (<small>{{$server->_keys_information|@count}}</small>)</a></li>
  <li><a href="#redis-clients">Clients (<small>{{$server->_clients_information|@count}}</small>)</a></li>
  <li><a href="#redis-slowlog">SlowLog (<small>{{$server->_slowlog_information|@count}}</small>)</a></li>
</ul>

<table class="main tbl" id="redis-info">
  <tr>
    <th>{{tr}}Key{{/tr}}</th>
    <th>{{tr}}Value{{/tr}}</th>
  </tr>

  {{foreach from=$server->_information key=_key item=_value}}
    <tr>
      <td>{{$_key}}</td>
      <td>{{$_value}}</td>
    </tr>
  {{/foreach}}
</table>

<table class="main tbl" id="redis-keys">
  <tr>
    <th>{{tr}}Key{{/tr}}</th>
    <th>{{tr}}Size{{/tr}}</th>
  </tr>

  {{foreach from=$server->_keys_information key=_key item=_size}}
    <tr>
      <td>{{$_key}}</td>
      <td>{{$_size|decabinary}}</td>
    </tr>
  {{/foreach}}
</table>

<table class="main tbl" id="redis-clients">
  {{if $server->_clients_information|@count > 0}}
    {{assign var=_first_client value=$server->_clients_information.0}}

    <tr>
    {{foreach from=$_first_client key=_col item=_value}}
      <th>{{$_col}}</th>
    {{/foreach}}
    </tr>
  {{/if}}

  {{foreach from=$server->_clients_information key=_key item=_client}}
    {{assign var=_first_client value=$server->_clients_information.0}}

    <tr>
      {{foreach from=$_client key=_col item=_value}}
        <td>{{$_value}}</td>
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>

<table class="main tbl" id="redis-slowlog">
  <tr>
    <th>Id</th>
    <th>{{tr}}Time{{/tr}}</th>
    <th>{{tr}}Duration{{/tr}} (µs)</th>
    <th>{{tr}}Command{{/tr}}</th>
  </tr>

  {{foreach from=$server->_slowlog_information item=_slowlog}}
    {{assign var=_command value=" "|implode:$_slowlog.3}}
    <tr>
      <td>{{$_slowlog.0}}</td>
      <td>{{$_slowlog.datetime}}</td>
      <td>{{$_slowlog.2}}</td>
      <td>{{$_command}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4">{{tr}}None{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>