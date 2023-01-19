/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlageConge = {
  showForUser: function (user_id) {
    new Url("personnel", "ajax_plage_conge").addParam("user_id", user_id).popup(400, 300);
  },

  loadUser: function (user_id, plage_id) {
    var url = new Url("personnel", "ajax_plage_conge");
    url.addParam("plage_id", plage_id);
    url.addParam("user_id", user_id);
    url.requestUpdate("vw_user");

    var user = $("u" + user_id);
    if (user) user.addUniqueClassName("selected");
  },

  // Select plage and open form
  edit: function (plage_id, user_id) {
    var url = new Url("personnel", "ajax_edit_plage_conge");
    url.addParam("plage_id", plage_id);
    url.addParam("user_id", user_id);
    url.requestUpdate("edit_plage");

    var plage = $("p" + plage_id);
    if (plage) plage.addUniqueClassName("selected");
  },

  editModal: function (plage_id, user_id) {
    var url = new Url("personnel", "ajax_edit_plage_conge");
    url.addParam("plage_id", plage_id);
    url.addParam("user_id", user_id);
    url.addParam("is_modal", 1);
    url.requestModal(400, null);
  },

  content: function (type) {
    var url = new Url("personnel", "vw_planning_conge");
    url.addParam("affiche_nom", 0);
    var type_view = type || 'conge';
    url.addParam("type_view", type_view);
    url.requestUpdate("planning" + type_view);
  }
};