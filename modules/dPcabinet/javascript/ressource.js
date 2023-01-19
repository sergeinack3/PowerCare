/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Ressource = {
  edit: function(ressource_cab_id, function_id) {
    new Url("cabinet", "ajax_edit_ressource")
      .addParam("ressource_cab_id", ressource_cab_id)
      .addParam("function_id"     , function_id)
      .requestModal("80%", "80%", {onClose: Ressource.refreshList});
  },

  refreshList: function(function_id) {
    new Url("cabinet", "ajax_list_ressources")
      .addNotNullParam("function_id", function_id)
      .requestUpdate("ressources_area");
  }
};