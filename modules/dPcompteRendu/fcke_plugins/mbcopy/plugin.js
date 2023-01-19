/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbcopy', {
  init: function(editor) {
    editor.addCommand('mbcopy', {exec: mbcopy_onclick});
    editor.ui.addButton('mbcopy', {
      label:   'Recopie du contenu d\'un document',
      command: 'mbcopy',
      icon:    this.path +  'images/icon.png'
    });
  }
});

function mbcopy_onclick(editor) {
  var form = getForm("editFrm");
  new Url("compteRendu", "ajax_list_docs")
    .addParam("object_class", $V(form.object_class))
    .addParam("object_id"   , $V(form.object_id))
    .requestModal("80%", "80%");
}
