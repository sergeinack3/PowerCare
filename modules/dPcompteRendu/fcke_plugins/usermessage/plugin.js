/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* We msut defin the path of the image outside of the scope of the command */
var url_path = this.path;

CKEDITOR.plugins.add('usermessage', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('usermessage', {exec: usermessage_onclick});
    editor.addCommand('usermessage_toggle_icon', {exec: function() {
      $$('span.cke_button__usermessage_icon')[0].setStyle({'background-image': 'url(' + url_path + '../../style/mediboard_ext/images/buttons/mail_send.png)'});
    }});
    editor.ui.addButton('usermessage', {
      label:   'Envoyer par mail',
      command: 'usermessage',
      icon:    '../../style/mediboard_ext/images/buttons/mail.png'
    });
  }
});

function usermessage_onclick() {
  openWindowMail();
}
