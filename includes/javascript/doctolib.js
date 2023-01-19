/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * This file is made to work between the Doctolib plugin and Mediboard
 * These are the handlers of the Doctolib plugin event thrower
 */

if (navigator.userAgent.indexOf("Chrome") != -1 || navigator.userAgent.indexOf("Firefox") != -1) {
    window.zipper = window.zipper || function () {
        (zipper.q = zipper.q || []).push(arguments)
    };

    window.zipper('onInitPatient', function (patient) {
        DoctolibZipper.onInitPatient(patient);
    });
    window.zipper('onOpenPatient', function (patient) {
        DoctolibZipper.onOpenPatient(patient);
    });

    DoctolibZipper = {
        /**
         * Function to fire when creating a patient (Doctolib side)
         *
         * @param patient
         */
        onInitPatient: function (patient) {
            new Url('doctolib', 'search_patient')
                .addParam('patient_id', patient.doctolib_id)
                .addParam('patient_exists_req', 1)
                .requestJSON(function (json) {
                    json = JSON.parse(json);

                    if (!json.docto_patient_exists) {
                        DoctolibZipper.closeDoctolibModals();

                        window.doctolib_modal = new Url('doctolib', 'search_patient')
                            .addParam('prenom', patient.first_name)
                            .addParam('nom', patient.last_name)
                            .addParam('naissance', patient.birthdate)
                            .addParam('sexe', (patient.gender) ? 'f' : 'm')
                            .addParam('patient_id', patient.doctolib_id)
                            .requestModal('90%', '90%');
                    } else {
                        patient.pms_id = patient.doctolib_id;
                        DoctolibZipper.onOpenPatient(patient);
                    }
                });
        },

        /**
         * This is more or less a copy of Patient.edit function but
         * we can't be sure the patient.js file is loaded
         *
         * @param {int} patient - doctolib patient id
         */
        onOpenPatient: function (patient) {
            new Url('doctolib', 'vw_edit_patients')
                .addParam('patient_id', patient.pms_id)
                .requestJSON(function (json) {
                    if (Preferences.UISTYLE === 'tamm') {
                        window.location = window.location.protocol + '//' + window.location.hostname + window.location.pathname + '?m=oxCabinet&tab=vw_tdb&patient_id=' + patient.pms_id;
                        return;
                    }

                    json = JSON.parse(json);

                    if (json.docto_patient_exists) {
                        DoctolibZipper.closeDoctolibModals();

                        window.doctolib_modal = new Url('patients', 'vw_edit_patients')
                            .addParam('patient_id', patient.pms_id)
                            .addParam('modal', 1)
                            .modal({width: '90%', height: '90%'});
                    } else {
                        DoctolibZipper.onInitPatient(patient);
                    }
                });
        },

        /**
         * Closes all Doctolib modals
         */
        closeDoctolibModals: function () {
            if (window.doctolib_modal) {
                Control.Modal.close();
            }
        },

        /**
         * Save the idex then redirect to the timeline if it's Tamm
         *
         * @param form
         */
        saveIdex: function (form) {
            onSubmitFormAjax(
                form,
                {
                    onComplete: function () {
                        if (form.dataset.patientId) {
                            if (Preferences.UISTYLE === 'tamm') {
                                window.location = window.location.protocol + '//' + window.location.hostname + window.location.pathname + '?m=oxCabinet&tab=vw_tdb&patient_id=' + form.dataset.patientId;
                            }
                        }
                    }
                }
            );
        }
    };
}
