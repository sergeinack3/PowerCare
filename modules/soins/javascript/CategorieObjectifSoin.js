/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CategorieObjectifSoin = {

  toggleInactive: function(button, countInactive) {
    if (!button){
      var button = $("buttonShowInactive");
    }
    var newShow = (button.get("show") == "1") ? "0" : "1";
    this.updateButton(button, countInactive);
    $("liste_categories_objectif_soin").select("tr.hatching").invoke("toggle");
    button.set("show", newShow);
  },

  updateButton: function(button, countInactive) {
    var show = button.get("show");
    if(countInactive < 0) {
      button.display = "none";
    }
    if (show === "1") {
      button.update($T("CObjectifSoinCategorie-hide_inactive")+"("+countInactive+")");
    }
    else if (show === "0") {
      button.update($T("CObjectifSoinCategorie-show_inactive")+"("+countInactive+")");
    }
  },

  edit : function(categorie_id) {
    new Url('soins', 'ajax_edit_categorie_objectif_soin')
      .addParam('categorie_id', categorie_id)
      .requestModal("40%", 0, {
        onClose: function() { this.refreshList() }.bind(this)
      });
  },

  refreshList : function() {
    new Url('soins', 'ajax_vw_list_categories_objectif_soin')
      .requestUpdate($('listCategories'));
  }
};