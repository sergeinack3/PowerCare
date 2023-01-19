/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('medimail', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('medimail', {exec: medimail_onclick});
    editor.ui.addButton('medimail', {
      label:   'Envoyer via MS Santé',
      command: 'medimail',
      icon:    '../../style/mediboard_ext/images/buttons/medimail.png'
    });
  }
});

function medimail_onclick() {
  openWindowMedimail();
}
