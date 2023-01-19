/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Permet de d�sactiver le drag and drop dans l'�diteur
 */
CKEDITOR.plugins.add('dropoff', {
  init: function (editor) {
    function rejectDrop(event) {
      event.data.preventDefault(true);
    }

    editor.on('contentDom', function() {
      editor.document.on('drop',rejectDrop);
    });
  }
});
