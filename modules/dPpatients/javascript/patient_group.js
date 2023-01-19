/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatientGroup = {
  viewGroups: function (patient_id) {
    var url = new Url('dPpatients', 'vw_patient_groups');
    url.addParam('patient_id', patient_id);

    url.requestModal('60%', '40%');
  }
};