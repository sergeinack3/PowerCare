/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ReturnForm = window.ReturnForm || {
  statuses: ["new", "pending", "sent"],

  create: function () {
    var url = new Url("stock", "vw_aed_return_form");
    url.pop(1200, 700);
  },

  submitOutput: function (oForm, options) {
    return onSubmitFormAjax(oForm, function () {
      if (options.onComplete) {
        options.onComplete();
      }

      refreshOrder(oForm.order_id.value, options);

      if (!options.noRefresh) {
        refreshOrderItem($V(oForm.order_item_id), options);
      }
    });
  },

  refreshList: function (status, form) {
    var url = new Url("dPstock", "ajax_list_return_forms");
    url.addParam("status", status);

    if (form) {
      url.addFormData(form);
    }

    url.requestUpdate("list-return-forms-" + status);
  },

  print: function (return_form_id) {
    var url = new Url("dPstock", "vw_return_form");
    url.addParam("return_form_id", return_form_id);
    url.popup(1000, 800, "Bon de retour");
  }
};