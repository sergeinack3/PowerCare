{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script>
  importCim10OMS = function() {
    new Url("cim10", "do_import_cim10_oms", 'dosql')
      .addParam('action', 'import')
      .requestUpdate('cim10_oms', {method: 'post', getParameters: {m: 'cim10', dosql: 'do_import_cim10_oms'}});
  };

  updateCim10OMS = function() {
    new Url("cim10", "do_import_cim10_oms", 'dosql')
      .addParam('action', 'update')
      .requestUpdate('cim10_oms_update', {method: 'post', getParameters: {m: 'cim10', dosql: 'do_import_cim10_oms'}});
  };

  importCim10ATIH = function () {
    new Url('cim10', 'do_import_cim10_atih', 'dosql')
      .requestUpdate('cim10_atih', {method: 'post', getParameters: {m: 'cim10', dosql: 'do_import_cim10_atih'}});
  };

  importCim10GM = function() {
    new Url('cim10', 'do_import_cim10_gm', 'dosql')
      .requestUpdate('cim10_gm', {method: 'post', getParameters: {m: 'cim10', dosql: 'do_import_cim10_gm'}});
  };

  importDRC = function() {
    new Url('cim10', 'do_import_drc', 'dosql')
      .requestUpdate('drc_import', {method: 'post', getParameters: {m: 'cim10', dosql: 'do_import_drc'}});
  };

  importCISP = function() {
    new Url('cim10', 'do_import_cisp', 'dosql')
      .requestUpdate('cisp_import', {method: 'post', getParameters: {m: 'cim10', dosql: 'do_import_cisp'}});
  };

  Main.add(function() {
    Control.Tabs.create('tabs-configure', true);
    Configuration.edit('dPcim10', 'CGroups', 'Configs');
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CIM">CIM10</a></li>
  <li><a href="#drc">DRC</a></li>
  <li><a href="#cisp">CISP</a></li>
  <li><a href="#favoris">{{tr}}CFavoriCIM10{{/tr}}</a></li>
  <li><a href="#Configs">{{tr}}CConfiguration{{/tr}}</a></li>
  <li><a href="#outils">{{tr}}Tools{{/tr}}</a></li>
</ul>

<div id="CIM" style="display: none;">
  <form name="editConfig-ccam" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_configure module=$m}}

    <table class="form">
      <tr>
        <th class="category" colspan="2">{{tr}}CConfiguration{{/tr}}</th>
      </tr>
      {{mb_include module=system template=inc_config_enum var=cim10_version values='oms|atih|gm'}}

      <tr>
        <td class="button" colspan="2">
          <button class="modify">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>

  {{mb_include module=system template=configure_dsn dsn=cim10}}

  <h2>{{tr}}CCodeCIM10.import_base.tile{{/tr}}</h2>

  <table class="tbl">
    <tr>
      <th>{{tr}}Action{{/tr}}</th>
      <th>{{tr}}Status{{/tr}}</th>
    </tr>

    <tr>
      <td class="narrow">
        <button id="import_cim10_oms" class="tick" onclick="importCim10OMS()">{{tr}}CCodeCIM10.import_base_oms{{/tr}}</button>
      </td>
      <td id="cim10_oms"></td>
    </tr>

    <tr>
      <td class="narrow">
        <button id="import_cim10_oms_update" class="tick" onclick="updateCim10OMS()">{{tr}}CCodeCIM10.update_base_oms{{/tr}}</button>
      </td>
      <td id="cim10_oms_update"></td>
    </tr>

    <tr>
      <td class="narrow">
        <button id="import_cim10_atih" class="tick" onclick="importCim10ATIH()">{{tr}}CCodeCIM10.import_base_atih{{/tr}}</button>
      </td>
      <td id="cim10_atih"></td>
    </tr>

    <tr>
      <td class="narrow">
        <button id="import_cim10_gm" class="tick" onclick="importCim10GM()">{{tr}}CCodeCIM10.import_base_gm{{/tr}}</button>
      </td>
      <td id="cim10_gm"></td>
    </tr>
  </table>
</div>

<div id="drc" style="display: none;">
  {{mb_include module=system template=configure_dsn dsn=drc}}

  <h2>Import de la base DRC</h2>

  <table class="tbl">
    <tr>
      <th>{{tr}}Action{{/tr}}</th>
      <th>{{tr}}Status{{/tr}}</th>
    </tr>

    <tr>
      <td class="narrow">
        <button class="tick" onclick="importDRC()">Import de la base</button>
      </td>
      <td id="drc_import"></td>
    </tr>
  </table>
</div>

<div id="cisp" style="display: none;">
  {{mb_include module=system template=configure_dsn dsn=cisp}}

  <h2>Import de la base CISP</h2>

  <table class="tbl">
    <tr>
      <th>{{tr}}Action{{/tr}}</th>
      <th>{{tr}}Status{{/tr}}</th>
    </tr>

    <tr>
      <td class="narrow">
        <button class="tick" onclick="importCISP()">Import de la base</button>
      </td>
      <td id="cisp_import"></td>
    </tr>
  </table>
</div>

<div id="favoris" style="display: none;">
  {{mb_include template=inc_config_favoris}}
</div>

<div id="Configs" style="display: none;"></div>

<div id="outils" style="display: none">
  {{mb_include template=inc_outils_cim10}}
</div>