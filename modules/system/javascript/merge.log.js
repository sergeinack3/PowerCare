/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MergeLog = window.MergeLog || {
  getSearchForm: function () {
    return getForm('search-merge-logs');
  },

  search: function (form) {
    form = form || MergeLog.getSearchForm();

    form.onsubmit();
  },

  show: function (merge_log_id) {
    var url = new Url('system', 'ajax_show_merge_log');
    url.addParam('merge_log_id', merge_log_id);

    url.requestModal(600);
  },

  mergeAgain: function (object_class, object_ids) {
    var url = new Url('system', 'object_merger');
    url.addParam('objects_class', object_class);
    url.addParam('objects_id', object_ids);

    url.popup(800, 600, 'merge-log-merge-again');
  },

  changePage: function (page) {
    var form = MergeLog.getSearchForm();
    $V(form.start, page);

    MergeLog.search(form);
  },

  changeOrder: function (order, way) {
    var form = MergeLog.getSearchForm();
    $V(form.order_col, order);
    $V(form.order_way, way);

    MergeLog.search(form);
  },

  resetPageOffset: function () {
    var form = MergeLog.getSearchForm();
    $V(form.elements.start, 0);
  }
};
