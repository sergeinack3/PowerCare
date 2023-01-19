/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

FictifDoctor = {
    addEditDoctor: function (doctor_id) {
        new Url("rpps", "add_edit_fictif_doctor")
            .addParam("doctor_id", doctor_id)
            .requestModal("50%", "70%");
        return false;
    },

    refreshListFictifDoctors: function () {
        Control.Modal.close();
        var oform = getForm('find_medecin');
        if (oform) {
            $V(oform.start_med, 0);
            oform.onsubmit();
        }
    }
}
