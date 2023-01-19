/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* We msut defin the path of the image outside of the scope of the command */
var url_path = this.path;

CKEDITOR.plugins.add('mssante', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mssante', {exec: mssante_onclick});
    editor.addCommand('mssante_toggle_icon', {exec: function() {
      $$('span.cke_button__mssante_icon')[0].setStyle({'background-image': 'url(' + url_path + '../../style/mediboard_ext/images/buttons/mailMSSante_send.png)'});
    }});
    editor.ui.addButton('mssante', {
      label:   'Envoyer via Mailiz',
      command: 'mssante',
      icon:    '../../style/mediboard_ext/images/buttons/mailMSSante.png'
    });
  }
});

function mssante_onclick() {
  openWindowMSSante();
}
