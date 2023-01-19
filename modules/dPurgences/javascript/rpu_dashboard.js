/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RPUDashboard = {

  /**
   * Refresh RPU list
   * @param order_col
   * @param order_way
   * @param page
   */
  refreshRPUList: function (order_col, order_way, page = 0) {
    new Url('urgences', 'vwList')
      .addParam('page', page)
      .addNotNullParam('order_col', order_col)
      .addNotNullParam('order_way', order_way)
      .addFormData(getForm('filter'))
      .requestUpdate('listRPUs');
  },
  /**
   * Change RPU list sort method
   * @param order_col
   * @param order_way
   */
  changeSort: function (order_col, order_way) {
    let form = getForm('pagination');
    RPUDashboard.refreshRPUList(order_col, order_way, $V(form.page));
  },

  /**
   * Change RPU list pagination
   * @param page
   */
  changePage: function (page) {
    let form = getForm('pagination');
    RPUDashboard.refreshRPUList($V(form.order_col), $V(form.order_way), page);
  },

  /**
   * Autocomplete patient
   * @param form
   */
  searchPatient:function (form) {
    new Url("system", "ajax_seek_autocomplete")
      .addParam("object_class", "CPatient")
      .addParam("field", "patient_id")
      .addParam("input_field", "_seek_patient")
      .autoComplete(form._seek_patient, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          $V(form.patient_id, selected.get('guid').split('-')[1]);
        }
      });
  },
};
