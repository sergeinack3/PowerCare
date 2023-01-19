/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

WhiteList = {
  edit: function(whitelist_id) {
    new Url("compteRendu", "ajax_edit_whitelist")
      .addParam("whitelist_id", whitelist_id)
      .requestModal("40%", "40%", {onClose: WhiteList.refreshList});
  },

  refreshList: function(page) {
    var url = new Url("compteRendu", "ajax_list_whitelists");

    if (!Object.isUndefined(page)) {
      url.addParam("page", page);
    }

    url.requestUpdate("whitelist_area");
  }
};