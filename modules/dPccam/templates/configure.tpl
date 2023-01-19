{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function startCCAM() {
    new Url("ccam", "importCcamDatabase")
    .requestUpdate("ccam");
  }

  function startNGAP(){
    new Url("ccam", "importNgapDatabase")
    .requestUpdate("ngap");
  }

  function startForfaits(){
    new Url("dPccam", "importCccamForfaitsDatabase")
    .requestUpdate("forfaits");
  }

  Main.add(function() {
    Control.Tabs.create('tabs-configure', true);
    Configuration.edit('dPccam', 'CGroups', 'Configs');
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CCAM">{{tr}}CCAM{{/tr}}</a></li>
  <li><a href="#NGAP">{{tr}}NGAP{{/tr}}</a></li>
  <li><a href="#favoris">{{tr}}CFavoriCCAM{{/tr}}</a></li>
  <li><a href="#Configs">{{tr}}CConfiguration{{/tr}}</a></li>
  <li><a href="#maintenance">{{tr}}Maintenance{{/tr}}</a></li>
</ul>

<div id="CCAM" style="display: none;">
  {{mb_include template=inc_config_ccam}}
</div>

<div id="NGAP" style="display: none;">
  {{mb_include template=inc_config_ngap}}
</div>

<div id="favoris" style="display: none;">
  {{mb_include template=inc_config_favoris}}
</div>

<div id="Configs" style="display: none;">

</div>

<div id="maintenance" style="display: none;">
  {{mb_include template=inc_configure_actions}}
</div>
