/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Codes affectation
 */
CodesAffectation = {
  /**
   * Changes page
   *
   * @param {int} page - which page
   */
  changePage: function (page) {
    var url = new Url('ssr', 'vw_functions')
      .addParam('reload', 1);
    if (page > -1) {
      url.addParam('page', page);
    }
    url.requestUpdate('codes');
  },

  /**
   * Gets all affectations
   *
   * @param {int} function_id - the id of the function
   */
  loadAffectations: function (function_id) {
    new Url('ssr', 'ajax_vw_affectations_function')
      .addParam('function_id', function_id)
      .addParam('reload', 1)
      .requestUpdate('affectations_codes');
  },

  /**
   * Opens an affectation
   *
   * @param {HTMLElement} element - the html element with dataset
   */
  openAffectations: function (element) {
    new Url('ssr', 'ajax_vw_affectations_function')
      .addParam('function_id', element.dataset.functionId)
      .requestModal(
        700,
        500,
        {
          onClose: function () {
            CodesAffectation.changePage(-1);
          }
        }
      );
  },

  /**
   * Removes an affectation
   *
   * @param {HTMLElement} element - the html element with dataset
   */
  deleteCode: function (element) {
    var form = getForm(element.dataset.form);
    form.del.value = '1';
    form.code_affectation_id.value = element.dataset.affectationId;
    form.code.value = element.dataset.code;
    form.onsubmit(this);

    // Reset form
    form.code_affectation_id.value = '';
    form.del.value = '0';
  }
};