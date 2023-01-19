/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CategorieFunction = {
  edit : function(categorie_id) {
    var oForm = getForm('category_filters');
    if(!$V(oForm.selCabinet) && !$V(oForm.selPrat)) {
      alert($T('CConsultationCategorie.no_select_cabinet_praticien'));
      return;
    }
    new Url('cabinet', 'ajax_edit_categorie')
      .addParam('categorie_id', categorie_id)
      .addParam('selCabinet', $V(oForm.selCabinet))
      .addParam('selPrat', $V(oForm.selPrat))
      .requestModal(0, 0, {
        onClose: function() { this.refreshList() }.bind(this)
      });
  },

  refreshList : function() {
    var catForm = getForm('category_filters');
    new Url('cabinet', 'ajax_vw_list_categories')
      .addParam('selCabinet', $V(catForm.selCabinet))
      .addParam('selPrat', $V(catForm.selPrat))
      .requestUpdate($('listCategories'));
  }
};
