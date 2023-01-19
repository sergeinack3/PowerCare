/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

urgencesMaintenance = {

  displaySejour : function (form) {
    new Url("urgences", "ajax_doublon_rpu")
      .addFormData(form)
      .requestUpdate("display_sejour");

    return false;
  },

  checkRPU : function () {
    new Url("urgences", "ajax_check_rpu")
      .requestModal(1024, 768);
  },

  importMotif : function () {
    new Url("urgences", "ajax_import_motif_sfmu")
      .requestUpdate("import_sfmu");
  }
};