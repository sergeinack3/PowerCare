/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Pack = {
  edit: function(pack_examens_labo_id) {
    new Url('labo', 'ajax_edit_pack')
      .addParam('pack_examens_labo_id', pack_examens_labo_id)
      .requestModal('50%', null, {onClose: this.refreshList.bind(this)});
  },

  refreshList: function() {
    new Url('labo', 'ajax_list_packs')
      .requestUpdate('list_packs');
  }
};