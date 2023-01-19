{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function showAllPHPconfigs() {
    new Url("developpement", "ajax_php_config")
      .requestModal('100%', '100%');
  }

  Main.add(function () {
    Control.Tabs.create('main_tab_group', true);
  });
</script>
<style>
  tr.phpIniValueMismatch td {
    font-weight: bold;
  }

  .configVarError,
  tr.configVarError td {
    background-color: #f99;
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
    <tr>
      <th>Variable</th>
      <th>Valeur</th>
    </tr>
    <tr>
      <td>Version d'Apache</td>
      <td>{{$apacheConfiguration.serverInformations.version}}</td>
    </tr>
    <tr>
      <td>AllowOverride Défini</td>
      <td>
        {{if $apacheConfiguration.serverInformations.allowOverrideDefined}}
          true
        {{else}}
          false
        {{/if}}
      </td>
    </tr>
  </table>
  <h2>Mods Apache2 requis</h2>
  <table class="tbl main">
    <tr>
      <th>Nom</th>
      <th>Installé</th>
    </tr>
    {{foreach from=$apacheConfiguration.mods item=element key=key}}
      <tr>
        <td>{{$element->varName}}</td>
        <td style="text-align: center;">
          {{if $element->exists}}
            <span class="fa fa-2x fa-check" style="color:#0AA000"></span>
          {{else}}
            <span class="fas fa-2x fa-times" style="color:#f00"></span>
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="php" style="display:none;">
  <h2>
    Variables & Infos générales
    <button class="list" onclick="showAllPHPconfigs()">{{tr}}common-action-Display all{{/tr}}</button>
  </h2>
  <table class="tbl main">
    <tr>
      <th>Nom</th>
      <th>Valeur globale</th>
      <th>Valeur locale</th>
      <th>Niveau d'accès</th>
      <th>Commentaire</th>
    </tr>
    <tr>
      <td>PHP Version</td>
      <td colspan="4">{{$phpConfiguration.phpversion.fullVersion}}</td>
    </tr>
    {{foreach from=$phpConfiguration.iniVars item=element key=key}}
      {{assign var=varShouldNotExists value=false}}
      {{assign var=varDoesntExists value=false}}
      {{assign var=classIfVarError value="class=configVarError"}}

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
        {{assign var=classIfValMismatch value="class=phpIniValueMismatch"}}
      {{else}}
        {{assign var=classIfValMismatch value=""}}
      {{/if}}

      {{if $varShouldNotExists}}
        <tr {{$classIfVarError}}>
          {{elseif $varDoesntExists && $element->mustBeDefined}}
        <tr {{$classIfVarError}}>
      {{else}}
        <tr>
      {{/if}}
      <td>{{$element->varName}}</td>
      <td {{$classIfValMismatch}}>{{$element->globalValue}}</td>
      <td {{$classIfValMismatch}}>{{$element->localValue}}</td>
      <td>{{$element->getAccessLevel()}}</td>
      <td>
        {{if $varShouldNotExists}}
          {{$element->varName}} a été défini mais ne devrait pas exister
        {{elseif $varDoesntExists && $element->mustBeDefined}}
          {{$element->varName}} n'existe pas
        {{/if}}
      </td>
      </tr>
    {{/foreach}}
  </table>

  <h2>Extensions requises</h2>
  <table class="tbl main">
    <tr>
      <th>Nom d'extension</th>
      <th>Installée</th>
      <th>Commentaire</th>
    </tr>
    {{foreach from=$phpConfiguration.requiredExtensions item=element key=key}}
      {{if $element->exists}}
        <tr>
          {{else}}
        <tr class="configVarError">
      {{/if}}
      <td>{{$element->varName}}</td>
      <td>
        {{if $element->exists}}
          <span class="fa fa-2x fa-check" style="color:#0AA000"></span>
        {{else}}
          <span class="fas fa-2x fa-times" style="color:#f00"></span>
        {{/if}}
      </td>
      <td>
        {{if not $element->exists}}
          {{$element->varName}} n'est pas installé
        {{/if}}
      </td>
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="mysql" style="display:none;">
  <h2>Etat du serveur</h2>
  <table class="tbl main">
    <tr>
      <th>Nom</th>
      <th>Valeur</th>
      <th>Commentaire</th>
    </tr>
    {{foreach from=$mysqlConfiguration.globalStatus item=element key=key}}
      {{if not $element->exists}}
        <tr class="configVarError">
          {{else}}
        <tr>
      {{/if}}
      <td>{{$element->varName}}</td>
      <td>{{$element->value}}</td>
      <td>
        {{if not $element->exists}}
          {{$element->varName}} n'existe pas
        {{/if}}
      </td>
      </tr>
    {{/foreach}}
  </table>
  <h2>Variables système</h2>
  <table class="tbl main">
    <tr>
      <th>Nom</th>
      <th>Valeur</th>
      <th>Commentaire</th>
    </tr>
    {{foreach from=$mysqlConfiguration.systemVariables item=element key=key}}
      {{if not $element->exists}}
        <tr class="configVarError">
      {{else}}
        <tr>
      {{/if}}
      <td>{{$element->varName}}</td>
      <td>{{$element->value}}</td>
      <td>
        {{if not $element->exists}}
          {{$element->varName}} n'existe pas
        {{/if}}
      </td>
      </tr>
    {{/foreach}}
  </table>

  <div id="tablesinfos">
    <h2>Moteur de stockage</h2>
    <table class="tbl main">
      <thead>
      <th>Table</th>
      <th>Taille</th>
      <th>Moteur de stockage</th>
      </thead>
      <tbody id="tablesList">
        {{foreach from=$row item=tableDescription key=tablesListKey}}
          {{assign var=tablesInformationArray value=$row}}
          <tr class="configurationRow">
            <td><strong>{{$dbName}}</strong>.{{$tableDescription.tableName}}</td>
            {{ mb_include module=dPdeveloppement template=inc_list_mysql_tables }}
          </tr>
        {{/foreach}}
      </tbody>
    </table>
  </div>
</div>

<div id="mediboard" style="display:none;">
  <h2>Configuration de Mediboard</h2>
  <table class="tbl main">
    <tr>
      <th>Variable</th>
      <th>Valeur</th>
    </tr>
    {{foreach from=$mediboardConfiguration.mbConfig item=element}}
      {{if not $element->exists}}
        <tr class="configVarError">
      {{else}}
        <tr>
      {{/if}}
        <td>{{$element->varName}}</td>
        <td>{{$element->value}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>
