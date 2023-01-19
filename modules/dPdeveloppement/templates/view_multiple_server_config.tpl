{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function viewCurrent() {
    new Url("developpement", "view_server_config")
      .addParam("view_current", 1)
      .requestUpdate("current");
  }

  function keyExists(key, arr) {
    if (arr) {
      if (typeof(arr[key]) !== "undefined") {
        return true;
      }
    }
    return false;
  }

  /**
   * Returns all siblings of 'element'
   * @param element
   */

  Main.add(function () {
    Control.Tabs.create('main_tab_group', true);
    Control.Tabs.create('mysql_tab_group', true);
    ViewPort.SetAvlHeight('tablesList', 0.25)
  });

  //Main.add(getConfigurationValues);
</script>
<style>
  tr.phpIniValueMismatch td {
    font-weight: bold;
  }

  tr.configVarError td {
    background-color: #f99 !important;
    font-weight: bold;
  }
</style>

<ul id="main_tab_group" class="control_tabs">
  <li><a href="#apache">Apache</a></li>
  <li><a href="#php">PHP</a></li>
  <li><a href="#mysql">MySQL</a></li>
  <li><a href="#mediboard">Mediboard</a></li>
</ul>

<div id="apache" style="display:none;">
  <h2>Informations Générales</h2>
  <table class="tbl main">
    <thead>
    <tr>
      <th>IP</th>
      {{foreach from=$serversList item=server}}
        <th>{{$server}}</th>
      {{/foreach}}
    </tr>
    <tr>
      <th>Variable</th>
      {{foreach from=$serversList item=server}}
        <th>Valeur</th>
      {{/foreach}}
    </tr>
    </thead>
    <tr class="configurationRow">
      <td>Version d'Apache</td>
      {{foreach from=$serversConfiguration item=server key=ipAddr}}
        <td class="configurationValue">{{$server.apacheConfiguration.serverInformations.version}}</td>
      {{/foreach}}
    </tr>
    <tr class="configurationRow">
      <td>AllowOverride Défini</td>
      {{foreach from=$serversConfiguration item=server key=ipAddr}}
        <td class="configurationValue">
          {{if $server.apacheConfiguration.serverInformations.allowOverrideDefined}}
            true
          {{else}}
            false
          {{/if}}
        </td>
      {{/foreach}}
    </tr>
  </table>
  <h2>Mods Apache2 requis</h2>
  <table class="tbl main">
    <thead>
    <th>IP</th>
    {{foreach from=$serversList item=server}}
      <th>{{$server}}</th>
    {{/foreach}}
    </thead>
    <thead>
    <th>Nom</th>
    {{foreach from=$serversList item=server}}
      <th>Installé</th>
    {{/foreach}}
    </thead>
    {{* Every apacheConfiguration.mods has the same number on the array *}}
    {{foreach from=$serversConfiguration.$firstLine.apacheConfiguration.mods item=element key=key}}
      <tr>
        <td>{{$element->varName}}</td>
        {{foreach from=$serversConfiguration item=server key=ipAddr}}
          {{assign var=serverElement value=$server.apacheConfiguration.mods.$key}}
          <td style="text-align: center;">
            {{if $serverElement->exists}}
              <span class="fa fa-2x fa-check" style="color:#0AA000"></span>
            {{else}}
              <span class="fas fa-2x fa-times" style="color:#f00"></span>
            {{/if}}
          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="php" style="display:none;">
  <h2>Variables & Infos générales</h2>
  <table class="tbl main">
    <thead>
    <th>IP</th>
    {{foreach from=$serversList item=server}}
      <th colspan="4">{{$server}}</th>
    {{/foreach}}
    </thead>
    <thead>
    <th>Nom</th>
    {{foreach from=$serversList item=server}}
      <th>Globale</th>
      <th>Locale</th>
      <th>Niveau d'accès</th>
      <th>Commentaire</th>
    {{/foreach}}
    </thead>
    <tr class="configurationRow">
      <td>PHP Version</td>
      {{foreach from=$serversConfiguration item=server key=ipAddr}}
        <td colspan="4" style="border-left:2px solid #999; text-align:center;"
            class="configurationValue">{{$server.phpConfiguration.phpversion.fullVersion}}</td>
      {{/foreach}}
    </tr>
    {{foreach from=$serversConfiguration.$firstLine.phpConfiguration.iniVars item=row key=key}}
      {{assign var=varShouldNotExists value=false}}
      {{assign var=varDoesntExists value=false}}
      {{assign var=classIfVarError value=""}}
      <tr class="configurationIniRow">
        <td>{{$row->varName}}</td>
        {{foreach from=$serversConfiguration item=server key=ipAddr}}
          {{assign var=element value=$server.phpConfiguration.iniVars.$key}}
          {{if $element->exists}}
            {{if not $element->mustBeDefined}}
              {{assign var=varShouldNotExists value=true}}
            {{/if}}
          {{else}}
            {{if $element->mustBeDefined}}
              {{assign var=varDoesntExists value=true}}
            {{/if}}
          {{/if}}

          {{if $element->globalValue != $element->localValue}}
            {{assign var=classIfValMismatch value="phpIniValueMismatch"}}
          {{else}}
            {{assign var=classIfValMismatch value=""}}
          {{/if}}

          {{if $varShouldNotExists}}
            {{assign var=classIfVarError value="configVarError"}}
          {{elseif $varDoesntExists && $element->mustBeDefined}}
            {{assign var=classIfVarError value="configVarError"}}
          {{/if}}
          <td style="border-left:2px solid #999;"
              class="configurationIniValue iniGlobal {{$classIfValMismatch}}">{{$element->globalValue}}</td>
          <td class="configurationIniValue iniLocal {{$classIfValMismatch}}">{{$element->localValue}}</td>
          <td class="configurationIniValue iniAccessLevel"> {{$element->accessLevel}}</td>
          <td class="configurationIniValue {{$classIfVarError}}"
              title="{{if $varShouldNotExists}}
              {{$element->varName}} a été défini mais ne devrait pas exister
            {{elseif $varDoesntExists && $element->mustBeDefined}}
              {{$element->varName}} n'existe pas sur ce serveur
            {{/if}}
          ">

          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>

  <h2>Extensions requises</h2>
  <table class="tbl main">
    <thead>
    <th>IP</th>
    {{foreach from=$serversList item=server}}
      <th colspan="2">{{$server}}</th>
    {{/foreach}}
    </thead>
    <thead>
    <th>Nom d'extension</th>
    {{foreach from=$serversList item=server}}
      <th>Installée</th>
      <th>Commentaire</th>
    {{/foreach}}
    </thead>
    {{foreach from=$serversConfiguration.$firstLine.phpConfiguration.requiredExtensions item=row key=index}}
      <tr>
        <td>{{$row->varName}}</td>
        {{foreach from=$serversConfiguration item=server key=ipAddr}}
          {{assign var=classIfNotInstalled value=""}}
          {{assign var=element value=$server.phpConfiguration.requiredExtensions[$index]}}
          {{if not $element->exists}}
            {{assign var=classIfNotInstalled value="class=configVarError"}}
          {{/if}}
          <td {{$classIfNotInstalled}} style="border-left:2px solid #999;">
            {{if $element->exists}}
              <span class="fa fa-2x fa-check" style="color:#0AA000"></span>
            {{else}}
              <span class="fas fa-2x fa-times" style="color:#f00"></span>
            {{/if}}
          </td>
          <td {{$classIfNotInstalled}}>
            {{if not $element->exists}}
              {{$element->varName}} n'est pas installé
            {{/if}}
          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="mysql" style="display:none;">
  <h2>Etat du serveur</h2>
  <table class="tbl main">
    <thead>
    <th>IP</th>
    {{foreach from=$serversList item=server}}
      <th colspan="2">{{$server}}</th>
    {{/foreach}}
    </thead>
    <thead>
    <th>Nom</th>
    {{foreach from=$serversList item=server}}
      <th>Valeur</th>
      <th>Commentaire</th>
    {{/foreach}}
    </thead>
    {{foreach from=$serversConfiguration.$firstLine.mysqlConfiguration.globalStatus item=row key=key}}
      <tr class="configurationRow">
        <td>{{$row->varName}}</td>
        {{ mb_include module=dPdeveloppement template=inc_list_mysql_server_state }}
      </tr>
    {{/foreach}}
  </table>

  <h2>Variables système</h2>
  <table class="tbl main">
    <thead>
    <th>IP</th>
    {{foreach from=$serversList item=server}}
      <th colspan="2">{{$server}}</th>
    {{/foreach}}
    </thead>
    <thead>
    <th>Nom</th>
    {{foreach from=$serversList item=server}}
      <th>Valeur</th>
      <th>Commentaire</th>
    {{/foreach}}
    </thead>
    {{foreach from=$serversConfiguration.$firstLine.mysqlConfiguration.systemVariables item=row key=key}}
      <tr class="configurationRow">
        <td>{{$row->varName}}</td>
        {{ mb_include module=dPdeveloppement template=inc_list_mysql_server_variables }}
      </tr>
    {{/foreach}}
  </table>
  <h2>Informations générale sur les tables</h2>
  <ul id="mysql_tab_group" class="control_tabs">
    <li><a href="#tablesinfos">Info des tables</a></li>
    <!--<li><a href="#biggestTables">Taille des tables</a></li>-->
  </ul>

  <div id="tablesinfos">
    <h2>Moteur de stockage</h2>
    <table class="tbl main">
      <thead>
      <th>IP</th>
      {{foreach from=$serversList item=server}}
        <th colspan="2">{{$server}}</th>
      {{/foreach}}
      </thead>
      <thead>
      <th>Table</th>
      {{foreach from=$serversList item=server}}
        <th>Taille</th>
        <th>Moteur de stockage</th>
      {{/foreach}}
      </thead>
      <tbody id="tablesList">
      {{foreach from=$serversConfiguration.$firstLine.mysqlConfiguration.tablesInformation item=tableDescription key=tablesListKey}}
        <tr class="configurationRow">
          <td><strong>{{$tableDescription.tableSchema}}</strong>.{{$tableDescription.tableName}}</td>
          {{foreach from=$serversConfiguration item=server key=ipAddr}}
            {{assign var=tablesInformationArray value=$server.mysqlConfiguration.tablesInformation.$tablesListKey}}
            {{ mb_include module=dPdeveloppement template=inc_list_mysql_tables }}
          {{/foreach}}
        </tr>
      {{/foreach}}
      </tbody>
    </table>
  </div>
</div>

<div id="mediboard" style="display:none;">
  <h2>Configuration de Mediboard</h2>
  <table class="tbl main">
    <thead>
    <th>IP</th>
    {{foreach from=$serversList item=server}}
      <th>{{$server}}</th>
    {{/foreach}}
    </thead>
    <thead>
    <th>Variable</th>
    {{foreach from=$serversList item=server}}
      <th>Valeur</th>
    {{/foreach}}
    </thead>
    {{foreach from=$serversConfiguration.$firstLine.mediboardConfiguration.mbConfig item=row key=index}}
      <tr class="configurationRow">
        <td>{{$row->varName}}</td>
        {{foreach from=$serversConfiguration item=server key=ipAddr}}
          {{assign var=element value=$server.mediboardConfiguration.mbConfig[$index]}}
          <td style="border-left: 2px solid #999;" class="configurationValue">{{$element->getValue()}}</td>
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
</div>