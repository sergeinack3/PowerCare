/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Examen = {
  edit: function(examen_labo_id) {
    new Url('labo', 'ajax_edit_examen')
      .addParam('examen_labo_id', examen_labo_id)
      .requestModal('50%', null, {onClose: this.refreshList.bind(this)});
  },

  refreshList: function(catalogue_labo_id) {
    new Url('labo', 'ajax_list_examens')
      .addNotNullParam('catalogue_labo_id', catalogue_labo_id)
      .requestUpdate('list_examens');
  },

  createSibling: function(oForm) {
    var oEditForm = getForm("editExamen");
    $V(oEditForm.examen_labo_id, "");
    $V(oEditForm.catalogue_labo_id, $V(oForm.catalogue_labo_id));
    return onSubmitFormAjax(oEditForm);
  }
};