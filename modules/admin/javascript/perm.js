/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Perm = {
  td: null,

  /**
   * Modification du droit utilisateur sur un module
   *
   * @param user_id
   * @param mod_id
   * @param td
   */
  editPermModule: function(user_id, mod_id, td) {
    this.td = td;
    new Url('admin', 'ajax_manage_perm_module')
      .addParam('user_id', user_id)
      .addParam('mod_id', mod_id)
      .requestModal('300px');
  },

  legend: function() {
    new Url('admin', 'vw_legende')
      .requestModal('40%','40%');
  },

  removeBgSpecifiqTd: function() {
    if (!this.td) {
      return;
    }

    this.td.setStyle({backgroundColor: ''});
  }
};
