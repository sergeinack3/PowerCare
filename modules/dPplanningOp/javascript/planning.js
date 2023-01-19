/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlanningSejour = {
  callback: null,
  url: null,
  modal: null,
  /**
   * See the schedule of the stay
   *
   * @param sejour_id
   * @param date
   */
  view: function(sejour_id, date) {
    date = date || '';
    var url = new Url('planningOp', 'ajax_vw_planning_sejour');
    url.addParam('sejour_id', sejour_id);
    url.addParam('debut'    , date);
    PlanningSejour.url = url.modal({width: '80%',height: '100%'});
  },
  /**
   * Change date on the schedule
   *
   * @param form
   * @returns {boolean}
   */
  changeDate: function(form) {
    var url = new Url('planningOp', 'ajax_vw_planning_sejour');
    url.addParam('sejour_id', $V(form.sejour_id));
    url.addParam('debut'    , $V(form.debut));
    url.requestUpdate("planning-CSejour-"+$V(form.sejour_id));
    return false;
  },
  /**
   * Print the schedule of the stay
   *
   * @param sejour_guid
   */
  print: function (sejour_guid) {
    new Url('planningOp', 'ajax_vw_print_planning_sejour')
      .addParam('sejour_guid', sejour_guid)
      .popup(1000, 800);
  },

  /**
   * Switch weekly/monthly stay planning view
   */
  changeViewPlanningStay: function () {
    $$('.calendar-view')[0].observe('change', function (element) {
      new Url('planningOp', 'ajax_vw_planning_sejour')
        .addParam('sejour_id', element.target.dataset.stayId)
        .addParam('planning_type', element.target.value)
        .addParam('refresh', '1')
        .requestUpdate('planning-'+element.target.dataset.guid)
    });
  }
};