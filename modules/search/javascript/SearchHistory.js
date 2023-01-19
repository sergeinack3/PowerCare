/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SearchHistory = window.SearchHistory || {
  /**
   * Method to update the list of the history
   * @param form
   */
  updateListHistory: function (form) {
    var url = new Url('search', 'ajax_result_history');
    url.addFormData(form);
    url.requestUpdate('search_history_results');
  },

  executerHistory: function (search_history_id) {
    var url = new Url('search', 'vw_search');
    url.addParam("search_history_id", search_history_id);
    url.requestModal("80%", "80%");
  },

  deletehistory: function(form) {
    Modal.confirm($T('mod-search-history-delete-confirm'),
      {
        onOK: function () {
          var url = new Url('search', 'ajax_delete_history');
          url.requestUpdate('search_history_results');
        }
      });
  }

};