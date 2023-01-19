/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
TimelineImplement = {
  refreshResume: function(canonical_menu_name, appointment_id) {
    let url = new Url('cabinet', 'ajax_timeline_appointment');
      if (appointment_id) {
        url.addParam('appointment_id', appointment_id);
      }
      url.addParam('menus_filter', JSON.stringify(canonical_menu_name), true);
      url.addParam('refresh', 1);
      url.requestUpdate('main_timeline');
  },

  selectPractitioner: function(appointment_id, menus, filter_user_id) {
    new Url('cabinet', 'ajax_timeline_appointment')
      .addParam('appointment_id', appointment_id)
      .addParam('menus_filter', menus)
      .addParam('filter_user_id', filter_user_id)
      .addParam('refresh', 1)
      .requestUpdate('main_timeline');
  }
};
