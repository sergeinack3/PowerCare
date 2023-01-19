/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

BoardStats = {
  /**
   * Select one of the stats view
   *
   * @param form
   * @returns {boolean}
   */
  selectStats:            function (form) {
    if (!$V(form.stat)) {
      return false;
    }
    let module = $V(form.stat) === 'viewStatsPrescriptions' ? 'prescription' : 'dPboard';
    new Url(module, $V(form.stat))
      .addParam('praticien_id', $V(form.praticien_id))
      .requestUpdate('tdbStats');
  },
  /**
   * Refresh the list of operations after changing the date
   *
   * @param date
   */
  refreshViewTracesCotes: function (date) {
    let form = getForm('changeDate');
    new Url('dPboard', 'viewTraceCotes')
      .addParam('praticien_id', $V(form.praticien_id))
      .addParam('date_interv', date)
      .requestUpdate('tdbStats');
  }
};
