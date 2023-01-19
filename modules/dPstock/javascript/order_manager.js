/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

function refreshValue(guid, field, callback, options) {
  if (guid && callback) {
    var url = new Url("system", "ajax_object_value");
    url.addParam("guid", guid);
    url.addParam("field", field);

    if (options) {
      url.addObjectParam("options", options);
    }

    url.requestJSON(callback);
  }
}

/** Submit order function
 *  Used to submit an order : new or edit order
 *  @param oForm The form containing all the info concerning the order to submit
 *  @param options Options used to execute functions after the submit : {refreshLists, close}
 */
function submitOrder(oForm, options) {
  options = Object.extend({
    close:        false,
    confirm:      false,
    refreshLists: false
  }, options);

  options.onComplete = options.onComplete || function () {
    if (options.close && window.opener) {
      window.close();
    } else {
      refreshOrder($V(oForm.order_id), options);
    }
    if (options.refreshLists) {
      refreshLists();
    }
  };

  if (!options.confirm || (options.confirm && confirm('Voulez-vous vraiment effectuer cette action ?'))) {
    return onSubmitFormAjax(oForm, options);
  }
  else {
    return false;
  }
}

/** Submit order item function
 *  Used to submit an order item : new or edit order item
 *  @param oForm The form containing all the info concerning the order item to submit
 *  @param options Options used to execute functions after the submit : {refreshLists, close}
 */
function submitOrderItem(oForm, options) {
  if (options && options.noAjax) {
    oForm.submit();
  } else {
    onSubmitFormAjax(oForm, {
      onComplete: function () {
        if (options.onComplete) {
          options.onComplete();
        }
        refreshOrder(oForm.order_id.value, options);
        if (!options.noRefresh) {
          refreshOrderItem($V(oForm.order_item_id), options);
        }
      }
    });
  }
}

function refreshReception(reception_id, options) {
  var url = new Url("dPstock", "httpreq_vw_reception");
  url.addParam("reception_id", reception_id);
  url.requestUpdate("reception");
}

/** The refresh order function
 *  Used to refresh the view of an order
 */
function refreshOrder(order_id, options) {
  if (options && options.refreshLists) {
    if (options.refreshLists === true) {
      refreshLists();
    } else {
      (window.opener || window).refreshListOrders(options.refreshLists);
    }
  }
  var url = new Url("dPstock", "httpreq_vw_order");
  url.addParam("order_id", order_id);
  url.requestUpdate("order-" + order_id);
}

function refreshOrderItem(order_item_id) {
  var url = new Url("dPstock", "httpreq_vw_order_item");
  url.addParam("order_item_id", order_item_id);
  url.requestUpdate("order-item-" + order_item_id);
}

var orderTypes = ["waiting", "locked", "pending", "received", "cancelled"];

function refreshListOrders(type, form, invoiced) {
  var url = new Url("dPstock", "httpreq_vw_orders_list");
  url.addParam("type", type);

  if (form) {
    url.addFormData(form);
  }
  if (invoiced) {
    url.addParam("invoiced", 1);
  }

  url.requestUpdate("list-orders-" + type);
  return false;
}

function refreshLists(form, invoiced) {
  if (!window.opener || window.opener.closed) {
    // We load the visible one first
    orderTypes.each(function (type) {
      if ($("list-orders-" + type).visible()) {
        refreshListOrders(type, form, invoiced);
      }
    });
    orderTypes.each(function (type) {
      if (!$("list-orders-" + type).visible()) {
        refreshListOrders(type, form, invoiced);
      }
    });
  } else if (window.opener != window && window.opener.refreshLists) {
    window.opener.refreshLists();
  }
  return false;
}

function popupOrder(order_id, width, height, bAutofill) {
  width = width || 1000;
  height = height || 800;

  var url = new Url("dPstock", "vw_aed_order");
  url.setFragment("order-" + order_id);
  if (bAutofill) {
    url.addParam("_autofill", 1);
  }

  url.popup(width, height, "Edition de commande");
}

function popupReception(order_id, width, height) {
  width = width || 1000;
  height = height || 800;

  var url = new Url("dPstock", "vw_edit_reception");
  url.addParam("order_id", order_id);
  url.popup(width, height, "Réception de commande");
}

function editReception(reception_id, width, height) {
  width = width || 1000;
  height = height || 800;

  var url = new Url("dPstock", "vw_edit_reception");
  url.addParam("reception_id", reception_id);
  url.popup(width, height, "Réception de commande");
}

function popupOrderForm(order_id, width, height) {
  width = width || 1000;
  height = height || 800;

  var url = new Url("dPstock", "vw_order_form");
  url.addParam("order_id", order_id);
  url.popup(width, height, "Bon de commande");
}

function printReception(reception_id, width, height) {
  width = width || 1000;
  height = height || 800;

  var url = new Url("dPstock", "print_reception");
  url.addParam("reception_id", reception_id);
  url.popup(width, height, "Bon de reception");
}

function printBarcodeGrid(reception_id, force_print) {
  var url = new Url("dPstock", "print_reception_barcodes");
  url.addParam("reception_id", reception_id);
  url.addParam("force_print", force_print);
  url.addParam("suppressHeaders", true);
  url.popup(800, 800, "Codes barres");
}

function editUnitPrice(order_item_id) {
  var url = new Url("stock", "httpreq_edit_order_item_unit_price");
  url.addParam("order_item_id", order_item_id);
  url.requestModal(500, 200);
}
