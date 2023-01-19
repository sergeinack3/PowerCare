/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AdvancedSearch = {
    init: (form) => {
        AdvancedSearch.initFormBehaviours(form);
    },

    initFormBehaviours: (form) => {
        Calendar.regField(form._min_date);
        Calendar.regField(form._max_date);

        AdvancedSearch.initPatient(form);
        AdvancedSearch.initUser(form);

        $('patient_autocomplete').on('change', (value) => {
            if (value === '') {
                $('patient_id').value = '';
            }
        });

        $('user_autocomplete').on('change', (value) => {
            if (value === '') {
                $('user_id').value = '';
            }
        });

        $$('#contains_words, #exact, #contains_word').invoke('on', 'keydown', (key, input) => {
            const value = $V(input);
            AdvancedSearch.resetResearchFields(input.id);
            $V(input, value, false);
        });

        $('advanced_search_select_all_types').on('click', (input) => {
            $$('.types').each((e) => e.checked = input.target.checked);
        });

        $$('.types').invoke('on', 'change', () => $('advanced_search_select_all_types').checked = false);
    },

    resetResearchFields: (except_id) => {
        if (except_id !== 'contains_words') {
            $V($('contains_words'), '', false);
        }
        if (except_id !== 'exact') {
            $V($('exact'), '', false);
        }
        if (except_id !== 'contains_word') {
            $V($('contains_word'), '', false);
        }
    },

    initPatient: (form) => {
        new Url("system", "ajax_seek_autocomplete")
            .addParam("object_class", "CPatient")
            .addParam("field", "patient_id")
            .addParam("input_field", "patient_autocomplete")
            .autoComplete(form.patient_autocomplete, null, {
                method:             "get",
                width:              "300px",
                afterUpdateElement: function (field, selected) {
                    $V(field.form.patient_id, selected.get("guid").split("-")[1]);
                    $V(field.form.patient_autocomplete, selected.down('.view').innerHTML);
                }
            });
    },

    initUser: (form) => {
        new Url("system", "ajax_seek_autocomplete")
            .addParam("object_class", "CMediusers")
            .addParam("field", "user_id")
            .addParam("view_field", "_view")
            .addParam("input_field", "user_autocomplete")
            .autoComplete(form.user_autocomplete, null, {
                method:             "get",
                width:              "300px",
                afterUpdateElement: function (field, selected) {
                    $V(field.form.user_id, selected.get("guid").split("-")[1]);
                    $V(field.form.user_autocomplete, selected.down('.view').innerHTML);
                }
            });
    }
};
