/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DrawingItem = window.DrawingItem || {
  editModal : function(_id, src_file_id, context_guid, callback) {
    var url = new Url('drawing', 'ajax_draw');
    url.addParam('id', _id);
    url.addParam('src_file_id', src_file_id);
    url.addParam('context_guid', context_guid);
    url.requestModal('1024','680');

    if (callback) {
      url.modalObject.observe('afterClose', function(a) {
        callback();
      });
    }
  }
};