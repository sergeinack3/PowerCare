/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * BMRBHRE form js object
 */
BMRBHRE = {
    /**
     * Check BHR, BHRE and Hospit status using dates
     *
     * @param {HTMLInputElement} html_input
     */
    checkRadioEditPatient: function (html_input) {
        var form = html_input.form;

        if (html_input === form.bmr_debut || html_input === form.bmr_fin) {
            BMRBHRE._checkRadioValues(form, form.bmr_debut, form.bmr_fin, form.bmr);
        }
        if (html_input === form.bhre_debut || html_input === form.bhre_fin) {
            BMRBHRE._checkRadioValues(form, form.bhre_debut, form.bhre_fin, form.bhre);
        }
        if (html_input === form.hospi_etranger_debut || html_input === form.hospi_etranger_fin) {
            BMRBHRE._checkRadioValues(form, form.bhre_debut, form.bhre_fin, form.bhre);
            BMRBHRE._checkRadioHospiValues(form, form.hospi_etranger_debut, form.hospi_etranger_fin, form.hospi_etranger);
        }
        if (html_input === form.bhre_contact_debut || html_input === form.bhre_contact_fin) {
            BMRBHRE._checkRadioValues(form, form.bhre_contact_debut, form.bhre_contact_fin, form.bhre_contact);
        }
    },

    /**
     * Check BMR/BHRe values and auto check the right radio button based on the date
     *
     * @param {HTMLFormElement}  form
     * @param {HTMLInputElement} beginning
     * @param {HTMLInputElement} end
     * @param {HTMLInputElement} field
     *
     * @private
     */
    _checkRadioValues: function (form, beginning, end, field) {
        var today = new Date();
        var val_beginning = $V(beginning);
        var val_end = $V(end);

        // If both values exist
        if (val_beginning && val_end) {
            if (new Date(val_beginning) <= today && new Date(val_end) >= today) {
                $V(field, '1', false);
            } else {
                $V(field, '0', false);
            }
        }
        // If the beginning value exists
        else if (val_beginning && !val_end) {
            if (new Date(val_beginning) <= today) {
                $V(field, '1', false);
            } else {
                $V(field, '0', false);
            }
        }
        // If the end value exists
        else if (!val_beginning && val_end) {
            if (new Date(val_end) >= today) {
                $V(field, '1', false);
            } else {
                $V(field, '0', false);
            }
        }
    },

    /**
     * Check BMR/BHRe values and auto check the right radio button based on the date for the foreign stay
     * This is like the _checkValues but the behaviour is slightly different because we are taking into account
     * the last year
     *
     * @param {HTMLFormElement}  form
     * @param {HTMLInputElement} beginning
     * @param {HTMLInputElement} end
     * @param {HTMLInputElement} field
     *
     * @private
     */
    _checkRadioHospiValues: function (form, beginning, end, field) {
        var a_year_ago = new Date();
        a_year_ago.setFullYear(a_year_ago.getFullYear() - 1);
        var val_beginning = $V(beginning);
        var val_end = $V(end);
        var d_beginning = new Date(val_beginning);
        var d_end = new Date(val_end);

        var value = '1';

        if ((val_end && d_end < a_year_ago) || (val_beginning && d_beginning > new Date())) {
            value = '0';
        }

        $V(field, value, false);
    },

    /**
     * Check BMR/BHRe dates from radio inputs. If inputs and dates are incoherent, clear the date fields
     *
     * @param {HTMLInputElement} html_input
     */
    checkDatesEditPatient: function (html_input) {
        var form = html_input.form;
        var input_name = html_input.name;

        if (input_name === 'bmr') {
            BMRBHRE._checkDatesValues(form, form.bmr_debut, form.bmr_fin, form.bmr_debut_da, form.bmr_fin_da, form.bmr);
        }
        if (input_name === 'bhre') {
            BMRBHRE._checkDatesValues(form, form.bhre_debut, form.bhre_fin, form.bhre_debut_da, form.bhre_fin_da, form.bhre);
        }
        if (input_name === 'hospi_etranger') {
            BMRBHRE._checkDatesValues(form, form.hospi_etranger_debut, form.hospi_etranger_fin, form.bhre);
            BMRBHRE._checkDatesHospiValues(form, form.hospi_etranger_debut, form.hospi_etranger_fin, form.hospi_etranger);
        }
        if (input_name === 'bhre_contact') {
            BMRBHRE._checkDatesValues(form, form.bhre_contact_debut, form.bhre_contact_fin, form.bhre_contact_debut_da, form.bhre_contact_fin_da, form.bhre_contact);
        }
    },

    /**
     * Check date values
     *
     * @param {HTMLFormElement}  form         - the bmr bhre form
     * @param {HTMLInputElement} beginning    - beginning field
     * @param {HTMLInputElement} end          - end field
     * @param {HTMLInputElement} beginning_da - fancy beginning date display
     * @param {HTMLInputElement} end_da       - fancy end date display
     * @param {HTMLInputElement} field        - radio field
     * @private
     */
    _checkDatesValues: function (form, beginning, end, beginning_da, end_da, field) {
        var today = new Date();
        var val_end = $V(end);
        var val_beginning = $V(beginning);
        var emptyFields = false;

        var d_beginning = new Date(val_beginning);
        var d_end = new Date(val_end);

        if ($V(field) === '0') {
            if ((val_end && d_end >= today) || (val_beginning && !val_end && d_beginning < today)) {
                emptyFields = true;
            }
        }

        if ($V(field) === '1') {
            if ((val_beginning && d_beginning >= today) || (val_end && d_end <= today)) {
                emptyFields = true;
            }
        }

        if (emptyFields) {
            BMRBHRE._emptyField(beginning);
            BMRBHRE._emptyField(beginning_da);
            BMRBHRE._emptyField(end);
            BMRBHRE._emptyField(end_da);
        }
    },

    /**
     * Check date values for hospit (deal with the last 12 months)
     *
     * @param {HTMLFormElement} form       - the bmr bhre form
     * @param {HTMLInputElement} beginning - beginning date
     * @param {HTMLInputElement} end       - end date
     * @param {HTMLInputElement} field     - radio field
     * @private
     */
    _checkDatesHospiValues: function (form, beginning, end, field) {
        var today = new Date();
        var a_year_ago = new Date();
        a_year_ago.setFullYear(a_year_ago.getFullYear() - 1);

        var val_beginning = $V(beginning);
        var val_end = $V(end);
        var emptyFields = false;

        var d_beginning = new Date(val_beginning);
        var d_end = new Date(val_end);

        if ($V(field) === '0') {
            if ((val_end && d_end < today && d_end >= a_year_ago) || (val_beginning && d_beginning < today && d_beginning > a_year_ago)) {
                emptyFields = true;
            }
        }

        if ($V(field) === '1') {
            if ((val_beginning && d_beginning < a_year_ago) || (val_end && d_end <= a_year_ago)) {
                emptyFields = true;
            }
        }

        if (emptyFields) {
            BMRBHRE._emptyField(beginning);
            BMRBHRE._emptyField(end);
            BMRBHRE._emptyField(form.hospi_etranger_debut_da);
            BMRBHRE._emptyField(form.hospi_etranger_fin_da);
        }
    },

    /**
     * Empty field without firing events
     *
     * @param field
     * @private
     */
    _emptyField: function (field) {
        $V(field, '', false);
    }
};
