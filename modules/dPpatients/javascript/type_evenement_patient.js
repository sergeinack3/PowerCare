/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TypeEvenementPatient = {

  manage: function (callback) {
    var url = new Url("patients", "ajax_vw_types_evenement_patient");
    url.requestModal(0, 0, {onClose: callback});
  },

  edit: function (type_evenement_patient_id, callback) {
    var url = new Url("patients", "editTypeEvenement");
    if (type_evenement_patient_id != undefined) {
      url.addParam("type_evenement_patient_id", type_evenement_patient_id);
    }
    url.requestModal(null, null, {onClose: callback});
  }
};
