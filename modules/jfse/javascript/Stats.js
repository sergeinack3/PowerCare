/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Stats = {
    /**
     * Display dates fields
     */
    toggleDatesForm: () => {
        const choices = $$('#form-stats-jfse #choice-3, #form-stats-jfse #choice-4').filter((choice) => choice.checked);

        if (choices.length > 0) {
            $$('#form-stats-jfse tr.dates')[0].show();
            return;
        }

        $$('#form-stats-jfse tr.dates')[0].hide();
    },

    /**
     * Get stats results
     *
     * @param {HTMLIFrameElement} form
     */
    getResults: (form) => {
        const choices = Array.from(form.choice).filter((choice) => choice.checked).map((input) => input.value);

        if (choices.length <= 0) {
            Modal.alert($T('StatsRequest-At least one choice must be selected'));
            return;
        }
        if (choices.indexOf("4") !== -1 && !(form.begin.value && form.end.value)) {
            Modal.alert($T('StatsRequest-Dates are mandatory for this request'));
            return;
        }
        if (!form.jfse_id.value) {
            Modal.alert($T('StatsRequest-User mandatory'));
            return;
        }

        Jfse.displayView('stats/results', 'stats', {
            'choices[]': choices,
            begin:       form.begin.value,
            end:         form.end.value,
            jfse_id:     form.jfse_id.value
        }, {});
    }
};
