{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create("stats_hospi", true);
    refreshStats(tabs.activeLink.key);
  });
  
  refreshStats = function (type, date_min, date_max, service_id, options) {
    if (type == 'constants') {
      return;
    }

    var url = new Url("hospi", "ajax_vw_stats_" + type);
    if (date_min && date_max) {
      url.addParam("date_min", date_min);
      url.addParam("date_max", date_max);
    }
    if (!Object.isUndefined(service_id)) {
      url.addParam("service_id", service_id);
    }
    url.requestUpdate(type);
  }
  
  filtreOccupation = function () {
    var oForm = getForm("filter_occupation");
    var url = new Url("hospi", "ajax_vw_stats_occupation");
    if (oForm.elements["display_stat[ouvert]"].checked) {
      url.addParam("display_stat[ouvert]", 1);
    }
    if (oForm.elements["display_stat[prevu]"].checked) {
      url.addParam("display_stat[prevu]", 1);
    }
    if (oForm.elements["display_stat[affecte]"].checked) {
      url.addParam("display_stat[affecte]", 1);
    }
    if (oForm.elements["display_stat[entree]"].checked) {
      url.addParam("display_stat[entree]", 1);
    }
    url.requestUpdate("occupation");
  }
  
  listOperations = function (date, service_id) {
    var url = new Url("hospi", "ajax_stat_list_operations");
    url.addParam("date", date);
    url.addParam("service_id", service_id);
    url.requestUpdate("list_operations_uscpo");
  }
  
  viewLegend = function () {
    var url = new Url("hospi", "ajax_stat_legend");
    url.requestModal();
  }
</script>

<ul id="stats_hospi" class="control_tabs">
  {{if $conf.dPplanningOp.COperation.show_duree_uscpo}}
    <li>
      <a href="#uscpo" onmousedown="refreshStats('uscpo')">USCPO prévue / placée</a>
    </li>
  {{/if}}
  <li>
    <a href="#occupation" onmousedown="refreshStats('occupation')">Occupation prévue / réalisée</a>
  </li>
  <li>
    <a href="#constants">{{tr}}CConstantesMedicales{{/tr}}</a>
  </li>
</ul>

{{if $conf.dPplanningOp.COperation.show_duree_uscpo}}
  <div id="uscpo" style="display: none;"></div>
{{/if}}
<div id="occupation" style="display: none;"></div>

<div id="constants" style="display: none;">
  {{mb_include module=hospi template=inc_stats_constants_filters}}
</div>
