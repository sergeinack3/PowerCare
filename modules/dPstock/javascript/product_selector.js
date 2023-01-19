/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProductSelector = {
  sForm:      null,
  sId:        null,
  sView:      null,
  sQuantity:  null,
  sPackaging: null,
  sUnit:      null,
  options:    {
    width:  800,
    height: 450
  },

  pop: function (product_id) {
    new Url('stock', 'product_selector')
      .addParam('product_id', product_id)
      .popup(this.options.width, this.options.height, 'Sélecteur de produit');
  },

  set: function (product_id, product_name, quantity, unit, packaging) {
    var oForm = getForm(this.sForm);
    $V(oForm[this.sId], product_id, true);
    $V(oForm[this.sView], product_name, true);
    $V(oForm[this.sUnit], unit, true);
    $V(oForm[this.sPackaging], packaging, true);
    $V(oForm[this.sQuantity], quantity, true);
  }
};