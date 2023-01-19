/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ViewAccessToken = {
  edit: function(token_id) {
    var url = new Url('admin', 'ajax_edit_token');
    url.addParam('token_id', token_id);
    url.requestModal('700', null, {onClose: ViewAccessToken.list});
  },
  generate: function(params) {
    var url = new Url('admin', 'ajax_generate_token');
    url.addParam('token_default_params', JSON.stringify(params));
    url.addParam('ajax', '1');
    url.requestModal('70%', 'auto');
  }
};
