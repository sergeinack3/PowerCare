/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbfields', {
  requires: ['dialog'],
  init: function(editor) {
    CKEDITOR.dialog.add('mbfields_dialog', function() {
      return {
        title : $T('CCompteRendu-plugin-action-Insert a field'),
      };
    });

    editor.addCommand('mbfields', {exec: mbfields_onclick});
    editor.ui.addButton('mbfields', {
      label:   $T('CCompteRendu-plugin-mbfields'),
      command: 'mbfields',
      icon:    this.path + 'images/mbfields.png'
    });
  }
});

function mbfields_onclick(editor) {
  let form = getForm('editFrm');

  new Url('compteRendu', 'viewFields')
    .addParam('object_class', $V(form.object_class))
    .addParam('object_id', $V(form.object_id))
    .addParam('sections[]', Object.keys(window.fields.options), true)
    .addParam('max_sections', window.fields.max_sections)
    .requestModal('80%', 450)
}
