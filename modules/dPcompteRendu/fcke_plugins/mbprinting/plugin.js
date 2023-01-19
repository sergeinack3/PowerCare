/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbprinting', {
  requires: ['dialog'],
  init:function(editor){ 
   editor.addCommand('mbprinting', {exec: mbprinting_onclick});
   editor.ui.addButton('mbprinting', {
     label:   'Imprimer par le serveur',
     command: 'mbprinting',
     icon:    this.path + 'images/mbprinting.png'
   });
  }
});

function mbprinting_onclick(editor) {
  if (nb_printers == 0) {
    if (Preferences.pdf_and_thumbs == 1) {
      editor.execCommand("mbprintPDF");
    }
    else {
      mbprint_onclick(editor);
    }
    return;
  }
  Thumb.print = 1;
  submitCompteRendu(null, openModalPrinters);
}

