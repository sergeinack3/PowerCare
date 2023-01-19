/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
TimelineImplement = {
  refreshResume: function (menus) {
    new Url('soins', 'sejour_timeline')
      .addParam('menu_filter', JSON.stringify(menus), true)
      .addParam('refresh', 1)
      .requestUpdate('sejour_timeline')
  },

  selectPractitioner: function(stay_id, menu_filter, filter_user_id) {
    new Url('soins', 'sejour_timeline')
      .addParam('sejour_id', stay_id)
      .addParam('menu_filter', menu_filter)
      .addParam('practitioner_filter', filter_user_id)
      .addParam('refresh', 1)
      .requestUpdate('sejour_timeline');
  }
};