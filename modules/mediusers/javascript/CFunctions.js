/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function mediuser
 */
CFunctions = {
  editFunction: function(function_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("mediusers", "ajax_edit_function")
      .addParam("function_id", function_id)
      .requestModal(800, 600)
      .modalObject.observe("afterClose", function() {
        getForm('listFilter').onsubmit();
      });
  },

  changePage: function(page) {
    $V(getForm('listFilter').page, page);
  },

  changeFilter : function(order, way) {
    var form = getForm('listFilter');
    $V(form.order_col, order);
    $V(form.order_way, way);

    form.onsubmit();
  },

  vwExport: function() {
    var url = new Url('mediusers', 'vw_export_functions');
    url.requestModal();
  },

  vwImport: function() {
    var url = new Url('mediusers', 'vw_import_functions');
    url.requestModal();
  }
}