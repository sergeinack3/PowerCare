/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Deals with the handicaps list especially for forms
 */
PatientHandicap = {
    handicap_list: [],

    /**
     * Add a handicap to the list (canonical names only)
     *
     * @param {string} handicap_name
     */
    addHandicapToList: (handicap_name) => {
        PatientHandicap.handicap_list.push(handicap_name);
        let formSejour = getForm("editSejour");
        if(formSejour) {
            $V(formSejour._handicap, PatientHandicap.handicap_list);
        } else {
            let formPatient = getForm("editFrm");
            $V(formPatient._handicap, PatientHandicap.handicap_list);
        }
    },

    /**
     * Update the handicap list by toggling values.
     * If a checkbox has been "checked", when unchecking it, it will disappear (splice)
     *
     * @param {HTMLInputElement} input
     */
    updateHandicapList: (input) => {
        const index = PatientHandicap.handicap_list.indexOf(input.value);

        if (index > -1) {
            PatientHandicap.handicap_list.splice(index, 1);
        } else {
            PatientHandicap.addHandicapToList(input.value);
        }
    },

    /**
     * Clear all disability checked
     */
    clearDisabilityList: () => {
        $$('input[type=checkbox][class="editSejour_handicap"]:checked').each(
            (_handicap) => {
                $V(_handicap, false);
            }
        );
    },

    /**
     * Checks whether a patient has a disability or not
     *
     * @param {string} patient_id
     */
    checkDisability: (patient_id) => {
        PatientHandicap.clearDisabilityList();

        const oForm = getForm('editSejour');

        new Url('patients', 'checkDisability')
            .addParam('patient_id', patient_id)
            .requestJSON((response) => {
                for (let _handicap of response) {
                    $V(oForm.elements[`handicap-${_handicap.handicap}`], true);
                }
            });
    }
};
