/**+
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

HealthInsurance =
    {
        /**
         *
         * @param code
         */
        edit:               (code) => {
            Jfse.displayView('healthinsurance/edit', 'health_insurance', {code: code})
        },
        /**
         *
         * @param form
         */
        save:               (form) => {
            if (form) {
                Jfse.displayView('healthinsurance/store', 'systemMsg', {form: form})
            }

        },
        /**
         *  Set the autocomplete for health insurances
         */
        searchAutocomplete: () => {
            Jfse.displayAutocomplete(
                'healthinsurance/searchAutocomplete',
                'search_health_insurance',
                null,
                null,
                {
                    updateElement: function (selected) {
                        const name = selected.querySelector('.name').dataset.name,
                            code = selected.querySelector('.code').dataset.code;

                        $('search_health_insurance').value = name;
                    }
                }
            );
        },
        /**
         *
         * @param code
         */
        delete:             (code) => {
            Jfse.displayView('healthinsurance/delete', 'health_insurance',
                {
                    code: code
                })
        }
    };
