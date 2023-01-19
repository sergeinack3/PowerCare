/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CodeIntervenant = {
  current_m : null,

  /**
   * Change the current page. Use the 'filter-users' form data
   *
   * @param {Number} page Page selected
   * @return void
   */
  changePage: function(page) {
    var oForm = getForm('filter-users');
    $V(oForm.current, page);
    this.listRequest()
      .addFormData(oForm)
      .addParam('exclude_without_code', getForm('searchIntervenant').exclude_without_code.checked ? 1 : 0)
      .requestUpdate('intervenants_list');
  },

  /**
   * Change the selected Intervenant
   *
   * @param {String} excludeWithoutCode Exclude the Intervenants whitout code
   * @param {Number} intervId           Intervenant id
   * @return void
   */
  selectIntervenant: function(excludeWithoutCode, intervId) {
    this.listRequest()
      .addParam('exclude_without_code', excludeWithoutCode)
      .addParam('interv', intervId)
      .requestUpdate('intervenants_list');
  },

  /**
   * Get the prepared request for the Intervenant's list
   *
   * @returns {*|Url}
   */
  listRequest: function() {
    return new Url(this.current_m, 'edit_codes_intervenants')
      .addParam('list_mode', 1);
  }
};
