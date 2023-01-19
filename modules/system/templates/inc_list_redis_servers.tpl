{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $servers|@count == 0}}
  {{assign var=any_usage value=$redis_usage.session+$redis_usage.dshm+$redis_usage.mutex}}

  <div class="small-info">
    Aucun serveur n'est configuré dans le pool.

    {{if $servers_in_config|@count}}
      Voici le paramétrage actuel en configuration :

      <ul>
        {{foreach from=$servers_in_config item=_conf}}
          <li>{{$_conf.0}} : {{$_conf.1}} </li>
        {{/foreach}}
      </ul>
    {{else}}
      Aucun serveur non plus en configuration.
    {{/if}}

    {{if !$any_usage}}
      <br />Redis n'est pas utilisé à l'heure actuelle.
    {{/if}}
  </div>

  {{if $servers_in_config|@count && $any_usage}}
    <button class="tick " onclick="Redis.makeServersFromConfig()">
      {{tr}}CRedisServer-msg-Make from config{{/tr}}
    </button>
  {{/if}}
{{/if}}

<div style="float: right;">
  {{foreach from=$redis_usage item=_bool key=_usage name=_usage}}
    <span title="{{tr}}Usage{{/tr}}: {{tr}}{{$_bool|ternary:'Yes':'No'}}{{/tr}}" style="color: {{$_bool|ternary:green:orange}}">
      {{tr}}CRedisServer.usage.{{$_usage}}{{/tr}}
    </span>
    {{if !$smarty.foreach._usage.last}} - {{/if}}
  {{/foreach}}
</div>

<table class="tbl">
  <tr>
    <th class="narrow">Rôle <br />MB</th>
    <th class="narrow">Rôle <br />service</th>
    <th class="narrow"></th>
    <th class="narrow">{{mb_title class=CRedisServer field=host}}</th>
    <th class="narrow">{{mb_title class=CRedisServer field=port}}</th>
    <th>{{mb_title class=CRedisServer field=latest_change}}</th>
    <th>{{tr}}Keys{{/tr}}</th>
    <th>{{tr}}Clients{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$servers item=_server}}
    <tr class="{{if !$_server->active}} opacity-50 {{/if}} {{if $_server->_id == $redis_server_id}} selected {{/if}}">
      <td style="text-align: center;">
        {{if $_server->is_master}}
          <a href="#1" onclick="Redis.electMaster({{$_server->_id}})" title="Elire en master">
            <i class="fa fa-star fa-lg"></i>
          </a>
        {{else}}
          {{if $_server->_connectivity && $_server->active}}
            <a href="#1" onclick="Redis.electMaster({{$_server->_id}})" title="Elire en master">
              <i class="far fa-star fa-lg"></i>
            </a>
          {{else}}
            <i class="far fa-star fa-lg" style="opacity: 0.3;"></i>
          {{/if}}
        {{/if}}
      </td>

      <td style="text-align: center;">
        {{if $_server->_information}}
          {{if $_server->_information.role == "master"}}
            <i class="fa fa-star fa-lg"
              {{if !$_server->is_master}}
              style="color: red;" title="Le serveur n'est pas SLAVE au niveau Redis"
              {{/if}}></i>
          {{else}}
            <i class="far fa-star fa-lg"
              {{if $_server->is_master}}
              style="color: red;" title="Le serveur n'est pas MASTER au niveau Redis"
              {{/if}}></i>
          {{/if}}
        {{else}}
          <i class="far fa-star fa-lg" style="opacity: 0.3;" title="Le serveur n'est pas accessible"></i>
        {{/if}}
      </td>

      <td>
        <i class="fa fa-circle fa-1x"
          {{if $_server->_connectivity|smarty:nodefaults === false}}
          style="color: red;" title="Inacessible"
          {{elseif $_server->_connectivity|smarty:nodefaults === null}}
          style="color: grey;" title="Inconnu"
          {{else}}
          style="color: green;" title="Temps de réponse: {{$_server->_connectivity}}ms"
          {{/if}}></i>
      </td>
      <td>
        <a href="#1" onclick="Redis.edit({{$_server->_id}}); return false;">
          {{mb_value object=$_server field=host}}
        </a>
      </td>
      <td>
        <a href="#1" onclick="Redis.edit({{$_server->_id}}); return false;">
        {{mb_value object=$_server field=port}}
        </a>
      </td>
      <td>{{mb_value object=$_server field=latest_change}}</td>
      <td>{{$_server->_keys_information|@count}}</td>
      <td>{{$_server->_clients_information|@count}}</td>
      <td>
        <button class="edit notext compact" onclick="Redis.edit({{$_server->_id}});">{{tr}}Edit{{/tr}}</button>
        <button class="right notext compact" onclick="this.up('tr').addUniqueClassName('selected'); Redis.loadServerInfo({{$_server->_id}});"></button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="9">{{tr}}CRedisServer.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>