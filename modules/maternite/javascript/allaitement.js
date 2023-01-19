/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Allaitement = {
  /**
   * View the breastfeeding
   *
   * @param patient_id
   * @param light_view
   */
  viewAllaitements: function (patient_id, light_view) {
    var url = new Url("maternite", "ajax_bind_allaitement");
    url.addParam("patient_id", patient_id);
    url.requestModal(900, 400, {
      onClose: function () {
        if (!Grossesse.modify_grossesse) {
          Grossesse.updateGrossesseArea();
        }
        Grossesse.updateEtatActuel(light_view);
      }
    });
  },

  editAllaitement: function (allaitement_id, patient_id) {
    var url = new Url("maternite", "ajax_edit_allaitement");
    url.addParam("allaitement_id", allaitement_id);
    url.addNotNullParam("patient_id", patient_id);
    url.requestUpdate("edit_allaitement");
  },

  refreshList: function (patient_id, object_guid) {
    var url = new Url("maternite", "ajax_list_allaitements");
    url.addNotNullParam("patient_id", patient_id);
    url.addParam("object_guid", object_guid);
    url.requestUpdate("list_allaitements");
  },

  afterEditAllaitement: function (allaitement_id) {
    Allaitement.editAllaitement(allaitement_id);
    Allaitement.refreshList();
  }
};
