/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExamMet = {
  /**
   * Update the score result
   *
   * @param element
   */
  updateScore: function (element) {
    var result_score = element.value;

    $('resutl_score_met').update(result_score);
    element.form.onsubmit();
  },
  /**
   * Refresh the score
   *
   * @param consultation_anesth_id
   */
  refreshScoreMet: function (consultation_anesth_id) {
    new Url('cabinet', 'ajax_vw_score_met')
    .addParam('consultation_anesth_id', consultation_anesth_id)
    .requestUpdate('score_met');
  }
};