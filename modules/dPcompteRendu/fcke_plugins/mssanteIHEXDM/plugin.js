/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* We msut defin the path of the image outside of the scope of the command */
var url_path = this.path;

CKEDITOR.plugins.add('mssanteIHEXDM', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mssanteIHEXDM', {exec: mssanteIHEXDM_onclick});
    editor.ui.addButton('mssanteIHEXDM', {
      label:   'Envoyer IHE-XDM via MS Santé',
      command: 'mssanteIHEXDM',
      icon:    '../../style/mediboard_ext/images/buttons/medimailIHEXDM.png'
    });
  }
});

function mssanteIHEXDM_onclick() {
  openWindowMedimail(1);
}
