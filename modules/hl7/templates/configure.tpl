{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function importHL7v2Tables() {
    new Url("hl7", "ajax_import_hl7v2_tables")
      .requestUpdate("import-log");
  }

  Main.add(function() {
    Control.Tabs.create('tabs-configure', true, {afterChange: function(container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('hl7', ['CGroups'], $('CConfigEtab'));
      }
    }});
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CHL7-config">{{tr}}CHL7-config{{/tr}}</a></li>
  <li><a href="#CHL7v2Segment-config">{{tr}}CHL7v2Segment-config{{/tr}}</a></li>
  <li><a href="#config-source">{{tr}}config-hl7v2-source{{/tr}}</a></li>
  <li><a href="#config-hl7v2-tables">{{tr}}config-hl7v2-tables{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}} </a></li>
</ul>

<div id="CHL7-config" style="display: none;">
  {{mb_include module=hl7 template=CHL7_config}}
</div>

<div id="CHL7v2Segment-config" style="display: none;">
  {{mb_include module=hl7 template=CHL7v2Segment_config}}
</div>

<div id="config-source" style="display: none;">
  <h2>Paramètres par défaut du serveur FTP pour HL7 v.2</h2>

  <table class="form">  
    <tr>
      <th class="category">
        {{tr}}config-exchange-source{{/tr}}
      </th>
    </tr>
    <tr>
      <td> {{mb_include module=system template=inc_config_exchange_source source=$hl7v2_source}} </td>
    </tr>
  </table>
</div>

<div id="config-hl7v2-tables" style="display: none;">
  <h2>Paramètres des tables HL7</h2>

  {{mb_include module=system template=configure_dsn dsn=hl7v2}}
 
  <table class="main tbl">
    <tr>
      <th class="title" colspan="2">
        Import des tables
      </th>
     <tr>
       <td class="narrow"><button onclick="importHL7v2Tables()" class="change">{{tr}}Import{{/tr}}</button></td>
      <td id="import-log"></td>
     </tr>
  </table>
</div>

<div id="CConfigEtab" style="display: none"></div>
