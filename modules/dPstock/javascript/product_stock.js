/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProductStock = {
  type: null,
  form: null,

  refreshList: function (callback) {
    new Url('stock', 'httpreq_vw_stocks_' + this.type + '_list')
      .addFormData(this.form)
      .requestUpdate('list-stocks-' + this.type, callback);
    return false;
  },

  changePage: function (page) {
    $V(this.form.start, page);
  },

  changeLetter: function (letter) {
    $V(this.form.start, 0, false);
    $V(this.form.letter, letter);
  },

  refreshEditStock: function (stock_id, product_id, callback) {
    new Url('stock', 'httpreq_edit_stock_' + this.type)
      .addParam('stock_' + this.type + '_id', stock_id)
      .addParam('product_id', product_id)
      .requestUpdate(
        'edit-stock-' + this.type,
        function () {
          if (Object.isFunction(callback)) {
            callback();
          }
          var row = $('row-CProductStock' + (this.type.charAt(0).toUpperCase() + this.type.slice(1)) + '-' + stock_id);
          if (!row) {
            return;
          }
          row.addUniqueClassName('selected');
        }.bind(this)
      );
  },

  editStockCallback: function (stock_id) {
    if (stock_id) {
      return this.refreshList(
        function () {
          this.refreshEditStock(stock_id)
        }.bind(this)
      );
    }

    this.refreshEditStock(stock_id, null, this.refreshList.curry(stock_id).bind(this));
  },

  makeAutocompleteLocation: function () {
    new Url('stock', 'httpreq_vw_related_locations')
      .autoComplete(
        ProductStock.form.location_view, null,
        {
          minChars:           1,
          method:             "get",
          select:             "view",
          dropdown:           true,
          callback:           function (input, queryString) {
            return queryString
              + '&owner_guid=' + (ProductStock.type === 'group' ? 'CGroups-' : 'CService-') + $V(ProductStock.form.object_id);
          },
          afterUpdateElement: function (field, selected) {
            $V(ProductStock.form.location_id, selected.className.match(/[a-z]-(\d+)/i)[1]);
          }
        }
      );
  },
  /**
   * Lance la fenêtre d'import des emplacements de stocks
   */
  importStock:              function () {
    new Url('stock', 'ajax_import_csv_file').requestModal(700, '90%');
  },
  /**
   * Lance l'export des emplacements de stocks
   */
  exportStock:              function () {
    new Url('stock', 'ajax_export_product_stock_location').popup(400, 150);
  },
  /**
   * Fonction qui applique le lieu (object_id) à tout les éléments de l'import en cours
   *
   * @param type
   * @return {boolean}
   */
  applyToAll:               function (type) {
    var select = $$("select[name='" + type + "[0][object_id]']");
    if (select[0]["selectedIndex"] === 0) {
      return false;
    }
    var selects = $$("." + type + "_select");
    selects.forEach(
      function (element) {
        element.selectedIndex = select[0]["selectedIndex"];
      }
    );
  },
  /**
   * Fonction qui vérifie que les lieux (object_id) sont bien renseignés pour l'import
   *
   * @return {Boolean|boolean}
   */
  validImport:              function () {
    var form = getForm('affectationProductStockLocation'), nbServices = 0, nbBlocs = 0;
    var selects_services = $$(".psl_s_select"), selects_blocs = $$(".psl_b_select");
    if (selects_services.length) {
      selects_services.forEach(
        function (element) {
          if (element.selectedIndex === 0) {
            nbServices++;
          }
        }
      );
    }
    if (selects_blocs.length) {
      selects_blocs.forEach(
        function (element) {
          if (element.selectedIndex === 0) {
            nbBlocs++;
          }
        }
      );
    }
    if (nbServices !== 0 || nbBlocs !== 0) {
      alert($T('CProductStockLocation-import-error-object_id'));
      return false;
    } else {
      return onSubmitFormAjax(
        form, {
          onComplete: function () {
            Control.Modal.close();
            this.refreshTab('vw_idx_stock_location');
          }
        }
      );
    }
  },

  refreshListStocksService: (product_id) => {
    new Url('stock', 'httpreq_vw_list_stock_services')
      .addParam('product_id', product_id)
      .requestUpdate('list-stock-services');
  }
};
