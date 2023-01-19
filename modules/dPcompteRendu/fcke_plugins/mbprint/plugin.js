/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbprint', {
  requires: ['iframedialog'],
  init: function(editor) {
    editor.addCommand('mbprint', {exec: mbprint_onclick});
    editor.ui.addButton('mbprint', {
      label:   'Imprimer (ancienne version)',
      command: 'mbprint',
      icon:    this.path + 'images/mbprint.gif'
    });
  }
});

function mbprint_onclick(editor) {
  if (Preferences.saveOnPrint != 0) {
    editor.getCommand('mbprint').setState(CKEDITOR.TRISTATE_DISABLED);
  }

  if (window.parent.same_print == 1) {
    editor.execCommand("mbprintPDF");
  }
  else {
    // Mise à jour de la date d'impression
    $V(getForm("editFrm").date_print, "now");

    var printDoc = function () {
      if (CKEDITOR.env.gecko) {
        editor.window.$.print();
      }
      else {
        editor.document.$.execCommand("Print");
      }
    };
    if (Preferences.saveOnPrint == 0) {
      printDoc();
    }
    else {
      submitCompteRendu(printDoc);
    }
  }
}