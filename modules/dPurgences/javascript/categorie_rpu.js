/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CategorieRPU = {
  edit: function(categorie_rpu_id) {
    Form.onSubmitComplete = categorie_rpu_id ? Prototype.emptyFunction : CategorieRPU.onSubmitComplete;

    new Url("urgences", "ajax_edit_categorie_rpu")
      .addParam("categorie_rpu_id", categorie_rpu_id)
      .requestModal("800", "550", {onClose: CategorieRPU.refreshList});
  },

  onSubmitComplete: function(guid, object) {
    CategorieRPU.edit(guid.split("-")[1]);
  },

  refreshList: function() {
    new Url("urgences", "ajax_list_categories_rpu")
      .requestUpdate("categories_rpu");
  }
};