/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlageAstreinte = {
    module:   'astreintes',
    lastList: '',
    user_id:  '',

    modalList: null,

    showForUser: function (user_id) {
        new Url('astreintes', 'loadAstreintesUser')
            .addParam('user_id', user_id)
            .popup(800, 300);  //popup is better
    },

    loadUser: function (user_id, plage_id) {
        new Url('astreintes', 'loadAstreintesUser')
            .addParam('plage_id', plage_id)
            .addParam('user_id', user_id)
            .requestUpdate('vw_user');

        let user = $('u' + user_id);
        if (user) {
            user.addUniqueClassName('selected');
        }
    },

    // Select plage and open form
    edit: function (plage_id, user_id) {
        new Url('astreintes', 'editPlageAstreinte')
            .addParam('plage_id', plage_id)
            .addParam('user_id', user_id)
            .requestUpdate('edit_plage');

        let plage = $('p' + plage_id);
        if (plage) {
            plage.addUniqueClassName('selected');
        }
    },



    modal: function (plage_id, date, hourstart, minutestart, callback) {
        new Url('astreintes', 'editPlageAstreinte')
            .addParam('plage_id', plage_id)
            .addParam('date', date)
            .addParam('hour', hourstart)
            .addParam('minutes', minutestart)
            .requestModal('1000px', '650px')
            .modalObject.observe('afterClose', function () {
            if (callback) {
                callback();
            } else {
                location.reload();
            }
        });
    },

    modaleastreinteForDay: function (date) {
        let url = new Url('astreintes', 'listAstreintesDay');
        if (date) {
            url.addParam('date', date);
        }
        url.requestModal('800px');
    },


    printShifts: function (formName) {
        let oForm = getForm(formName),
            mode = oForm.mode.value,
            value = oForm.date.value,
            types = null,
            category = oForm.category.value;
        if (oForm['type_names[]'] !== undefined && oForm['type_names[]'].selectedOptions.length) {
            types = $A(oForm['type_names[]'].selectedOptions).pluck('value');
        }

        let url = new Url('astreintes', 'offlineListAstreintes');
        url.addParam('dialog', 1);
        url.addParam('mode', mode);
        url.addParam('date', value);
        url.addParam('category', category);
        if (types) {
            url.addParam('type_names[]', types);
        }
        url.pop(700, 600, 'Liste des astreintes');
    },

    filterCategoryCalendar: function () {
        $('category').observe('change', function (e) {
            e.target.form.submit();
        });
    },

    /**
     * Resize each event of the calendar using the screen width
     */
    resizeEvents: function () {
        let table_width = $$('table.calendar_horizontal')[0].offsetWidth,
            cell_width = $$('table.calendar_horizontal .hoveringTd')[0].offsetWidth;

        $$('.event').forEach(function (event) {
            let max_minutes = 0,
                hours_divider = 0;
            if (event.dataset.mode === 'day') {
                // Minutes per day
                max_minutes = 1440;
                // 1 hour for each column
                hours_divider = 1;
            } else if (event.dataset.mode === 'week') {
                // Minutes per day
                max_minutes = 10079;
                // 4 hours for each column
                hours_divider = 4;
            } else if (event.dataset.mode === 'month') {
                // Minutes per day * the amount of columns
                max_minutes = 1440 * document.querySelectorAll('.dayLabel').length;
            }

            // Compute width
            let event_width = Math.floor(event.dataset.length * table_width / max_minutes);
            event.style.width = event_width + 'px';
            event.style.minWidth = event_width + 'px';

            // Compute left css to align to the right column
            if (event.dataset.mode === 'week' || event.dataset.mode === 'day') {
                let hours_left = parseInt(event.dataset.hour),
                    minutes_left = parseInt(event.dataset.minutes) / 60,
                    left = (hours_left + minutes_left) / hours_divider * cell_width;

                event.style.left = left + 'px';
            } else if (event.dataset.mode === 'month') {
                event.style.left = event.dataset.hour + 'px';
            }
        });
    },

    /**
     * Check if the category is well set
     *
     * @param {HTMLSelectElement} select
     */
    checkIssues: (select) => {
        $$('.issue').invoke('hide');

        if (select.item(select.selectedIndex).dataset.issue === "1") {
            $$('.issue').invoke('show');
        }
    }

};
