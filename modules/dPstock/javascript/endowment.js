/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Endowment = {
  duplicateEndowment: function (endowment_id) {
    var url = new Url("stock", "ajax_duplicate_endowment");
    url.addParam("endowment_id", endowment_id);
    url.requestModal(600, 300);
  },
  changePage:         function (page, endowment_id) {
    var url = new Url("dPstock", "httpreq_vw_endowment_form_list_product")
      .addParam("page", page)
      .addParam('endowment_id', endowment_id)
      .requestUpdate('endowment_list_products');
  }
};