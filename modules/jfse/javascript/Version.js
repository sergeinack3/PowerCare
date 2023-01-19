/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Version = {
    software: (form) => {
        const jfse_id = form.jfse_user_id.value;

        if (jfse_id) {
            Jfse.displayView('version/software', 'software', {jfse_id: jfse_id}, {});
        }
    },

    api: async () => {
        const cps_code = await Jfse.askCpsCode();

        if (cps_code !== '') {
            Jfse.displayView('version/api', 'api', {code_cps: cps_code}, {});
        }
    },

    searchAutocomplete: () => {
        Jfse.displayAutocomplete(
            'user_management/autocomplete',
            'jfse_users_autocomplete',
            null,
            null,
            {
                updateElement: (selected) => {
                    $('jfse_users_autocomplete').value = selected.querySelector('.view').innerHTML;
                    $('jfse_user_id').value = selected.dataset.jfseUserId;
                }
            }
        );
    }
};
