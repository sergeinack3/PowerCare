/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* We msut defin the path of the image outside of the scope of the command */
var url_path = this.path;

CKEDITOR.plugins.add('apicrypt', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('apicrypt', {exec: apicrypt_onclick});
    editor.addCommand('apicrypt_toggle_icon', {exec: function() {
      $$('span.cke_button__apicrypt_icon')[0].setStyle({'background-image': 'url(' + url_path + '../../style/mediboard_ext/images/buttons/mailApicrypt_send.png)'});
    }});
    editor.ui.addButton('apicrypt',
      {
        label: 'Envoyer via Apicrypt',
        command: 'apicrypt',
        icon: '../../style/mediboard_ext/images/buttons/mailApicrypt.png'
      });
    }
});

function apicrypt_onclick() {
  openWindowApicrypt();
}
