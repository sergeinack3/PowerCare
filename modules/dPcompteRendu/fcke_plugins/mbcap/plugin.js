/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbcap', {
  init: function(editor) {
    editor.addCommand('mbcap', {exec: mbcap_onclick});
    editor.ui.addButton('mbcap', {
      label:   'Majuscule automatique en début de phrase',
      command: 'mbcap',
      icon:    this.path +  'images/icon.png'
    });
    editor.on("instanceReady", function() {
      // On regarde la préférence
      if (Preferences.auto_capitalize == "1") {
        mbcap_onclick(editor);
      }
    });
  }
});

function mbcap_onclick(editor) {
  var command = editor.getCommand('mbcap');

  command.setState(command.state == CKEDITOR.TRISTATE_ON ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_ON);
}

function insertUpperCase(editor, event, keystroke) {
  // insert 'A' instead of 'a' (example)
  editor.insertText(String.fromCharCode(keystroke).toUpperCase());
  event.data.preventDefault();
}
