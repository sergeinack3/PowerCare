/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbreplace', {
  init: function(editor) {
    editor.addCommand('mbreplace', {exec: mbreplace_onclick});
    editor.ui.addButton('mbreplace', {
      label:   'Autocomplétion d\'aide à la saisie',
      command: 'mbreplace',
      icon:    this.path + 'images/mbreplace.png'
    });
    editor.on("instanceReady", function() {
      // On regarde la préférence
      if (Preferences.auto_replacehelper == "1") {
        mbreplace_onclick(editor);
      }
    });
  }
});

function mbreplace_onclick(editor) {
  var command = editor.getCommand('mbreplace');
  command.setState(command.state == CKEDITOR.TRISTATE_ON ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_ON);
}
