/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlateauTechnique = {
  current_m : null,

  /**
   * Refresh the list
   *
   * @return void
   */
  refreshList : function() {
    this.urlRequest('refresh_list')
      .requestUpdate('list_plateaux');
  },

  /**
   * Load the editing form part
   *
   * @param {Number} plateauId Plateau id
   * @return void
   */
  loadForm : function(plateauId) {
    this.urlRequest('load_form')
      .addParam('plateau_id', plateauId)
      .requestUpdate('form_plateau');
  },

  /**
   * Get the prepared PlateauTechnique request
   *
   * @param templateMode
   * @returns {*|Url}
   */
  urlRequest : function(templateMode) {
    return new Url(this.current_m, 'vw_idx_plateau')
      .addParam('template_mode', templateMode);
  }
};
