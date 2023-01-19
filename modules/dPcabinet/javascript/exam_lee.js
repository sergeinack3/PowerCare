/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExamLee = {
  /**
   * Update the score result
   *
   * @param form
   */
  updateScore: function (form) {
    var result_score = form.select('input:checked').length;

    $('resutl_score_lee').update(result_score);
    form.onsubmit();
  },
  /**
   * Refresh the score
   *
   * @param consultation_anesth_id
   */
  refreshScoreLee: function (consultation_anesth_id) {
    new Url('cabinet', 'ajax_vw_score_lee')
    .addParam('consultation_anesth_id', consultation_anesth_id)
    .requestUpdate('score_lee');
  }
};