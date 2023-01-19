{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=salleOp script=geste_perop ajax=true}}

<script>
  Main.add(function () {
    var tabsEvent = Control.Tabs.create('tab_geste_perop', false);
    tabsEvent.setActiveTab("gestes_perop");

    GestePerop.loadlistGestesPerop();
  });
</script>

<ul id="tab_geste_perop" class="control_tabs">
  <li>
    <a href="#event_perop_chapter" onmouseup="GestePerop.loadEventPeropChapitres();">{{tr}}CAnesthPeropChapitre|pl{{/tr}}</a>
  </li>
  <li>
    <a href="#event_perop_category" onmouseup="GestePerop.loadEventPeropCategories();">{{tr}}CAnesthPerop-Category|pl{{/tr}}</a>
  </li>
  <li>
    <a href="#gestes_perop" onmouseup="GestePerop.loadlistGestesPerop();">{{tr}}CGestePerop-tab-Geste perop|pl{{/tr}}</a>
  </li>
  <li>
    <a href="#protocoles_gestes_perop" onmouseup="GestePerop.loadListProtocolesGestesPerop();">{{tr}}CProtocoleGestePerop-Perop gesture protocol|pl{{/tr}}</a>
  </li>

  <li>
    <button type="button" class="fas fa-upload me-primary me-float-right" style="float: right;"
            title="{{tr}}CGestePerop-msg-Export perop gestures and associated objects according to the chosen filter{{/tr}}"
            onclick="Modal.open('filter_geste_perop_export', {showClose: true, title:'Filtres', height: 300, onClose: GestePerop.refreshActiveTab});">
      {{tr}}Export{{/tr}}
    </button>
    <button class="fas fa-download me-primary me-float-right" style="float: right;"
            title="{{tr}}CGestePerop-msg-Import perop gestures and associated objects according to the chosen filter{{/tr}}"
            onclick="Modal.open('filter_geste_perop_import', {showClose: true, title:'Contextes', height: 300, onClose: GestePerop.refreshMainActiveTab});">
      {{tr}}Import{{/tr}}
    </button>
  </li>
</ul>

<div id="event_perop_chapter" style="display: none;"></div>
<div id="event_perop_category" style="display: none;"></div>
<div id="gestes_perop" style="display: none;"></div>
<div id="protocoles_gestes_perop" style="display: none;"></div>

<div id="filter_geste_perop_export" style="display: none;">
  {{mb_include module=salleOp template=inc_vw_export_gestes}}
</div>
<div id="filter_geste_perop_import" style="display: none;">
  {{mb_include module=salleOp template=inc_vw_import_gestes}}
</div>

