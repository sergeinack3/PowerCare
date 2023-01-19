/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Catalogue = {
  edit: function(catalogue_labo_id) {
    new Url('labo', 'ajax_edit_catalogue')
      .addParam('catalogue_labo_id', catalogue_labo_id)
      .requestModal('50%', null, {onClose: this.refreshList.bind(this)});
  },

  refreshList: function() {
    new Url('labo', 'ajax_list_catalogues')
      .requestUpdate('list_catalogues');
  },

  checkRefFunction: function(pere) {
    var form = getForm("editCatalogue");
    if (pere) {
      $V(form.function_id, '');
      form.function_id.writeAttribute('disabled', 'disabled');
      form.function_id.hide();
    }
    else {
      form.function_id.writeAttribute('disabled', null);
      form.function_id.show();
    }
  }
};