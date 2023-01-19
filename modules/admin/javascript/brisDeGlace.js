/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

BrisDeGlace = {
  refreshList : function(target, user_id) {
    var url = new Url("admin", "ajax_list_bris_de_glace");
    url.addParam("user_id", user_id);
    url.requestUpdate(target);
  },

  listBrisDeGlaceByUser : function(user_id, date_start, date_end, target) {
    var url = new Url("admin", "ajax_search_bris_by_user");
    url.addParam("user_id", user_id);
    url.addParam("date_start", date_start);
    url.addParam("date_end", date_end);
    url.requestUpdate(target);
  },

  listBrisDeGlaceForUser : function(user_id, date_start, date_end, target) {
    var url = new Url("admin", "ajax_search_bris_for_user_object");
    url.addParam("user_id", user_id);
    url.addParam("date_start", date_start);
    url.addParam("date_end", date_end);
    url.requestUpdate(target);
  },

  changeContext: function (value, form) {
    form.onsubmit();
    if ($('patientAutocomplete')) {
      $('patientAutocomplete').style.display = value === "CSejour" ? 'inline-block' : 'none';
    }
  }
};
