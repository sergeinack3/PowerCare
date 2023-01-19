/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlageRessource = {
  tabs: null,

  viewPlanning: function(ressource_cab_id) {
    new Url("cabinet", "ajax_vw_planning_ressources")
      .addParam("ressource_cab_id", ressource_cab_id)
      .requestUpdate("planning_ressources");
  },

  viewPlannings: function(function_id, date) {
    if (!$("plannings_area")) {
      return;
    }

    new Url("cabinet", "ajax_vw_plannings_ressources")
      .addNotNullParam("function_id", function_id)
      .addNotNullParam("date", date)
      .requestUpdate("plannings_area");
  },

  changeDate: function(sens) {
    var form = getForm("filterPlanning");
    date = new Date($V(form.date));
    date.addDays(sens);
    $V(form.date, date.toDATE());
    $V(form.date_da, date.toLocaleDate());
  },

  edit: function(plage_ressource_cab_id, ressource_cab_id) {
    new Url("cabinet", "ajax_edit_plage_ressource")
      .addParam("plage_ressource_cab_id", plage_ressource_cab_id)
      .addParam("ressource_cab_id"      , ressource_cab_id)
      .requestModal("80%", "80%", {onClose: function() {
          if (ressource_cab_id) {
            PlageRessource.viewPlanning(ressource_cab_id || PlageRessource.getCurrentRessourceId());
          }

          var form = getForm("filter_day");

          if (form) {
            form.onsubmit();
          }
        }
      });
  },

  getCurrentRessourceId: function() {
    return PlageRessource.tabs ? PlageRessource.tabs.activeContainer.id.split("_")[1] : null;
  }
};