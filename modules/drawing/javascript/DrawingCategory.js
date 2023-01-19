/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DrawingCategory = {
  editModal : function(_id, mode, mode_id, afterClose) {
    var url = new Url('drawing', 'ajax_edit_drawing_category');
    url.addParam('id', _id);
    if (mode && mode_id) {
      url.addParam('mode', mode+'_id');
      url.addParam('mode_id', mode_id);
    }
    url.requestModal(350, 300);
    url.modalObject.observe('afterClose', function() {
      if (afterClose) {
        afterClose();
      }
    });
  }
};