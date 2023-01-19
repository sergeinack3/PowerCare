/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbprintPDF', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mbprintPDF', {exec: mbprintPDF_onclick});
    editor.ui.addButton('mbprintPDF', {
      label:   'Imprimer en PDF',
      command: 'mbprintPDF',
      icon:   this.path + 'images/mbprintPDF.png'
    });
  }
});

function mbprintPDF_onclick(editor) {
  if (!Thumb.doc_lock) {
    editor.getCommand('mbprintPDF').setState(CKEDITOR.TRISTATE_DISABLED);
  }
  window.parent.Url.ping({onComplete: function() {
    if (Thumb.mode == "doc") {
      if (Thumb.doc_lock) {
        streamPDF(editor);
      }
      else {
        // Mise à jour de la date d'impression
        $V(getForm("editFrm").date_print, "now");
        submitCompteRendu(function() {
          streamPDF(editor);
          editor.getCommand('mbprintPDF').setState(CKEDITOR.TRISTATE_OFF);
        });
      }
    }
    else {
      streamPDF(editor);
      editor.getCommand('mbprintPDF').setState(CKEDITOR.TRISTATE_OFF);
    }
  } });
}

function streamPDF(editor) {
  var form;
  var signature_mandatory = false;
  var valide = 0;

  if (Thumb.mode != "modele") {
    form = getForm("editFrm");
    signature_mandatory = parseInt(form.signature_mandatory.value);
    valide = parseInt(form.valide.value);
  }

  if (isNaN(valide)) {
    valide = 0;
  }

  if (signature_mandatory && !valide && !confirm($T("CCompteRendu.ask_force_print"))) {
    return;
  }

  if (Prototype.Browser.IE) {
    restoreStyle();
  }
  var content = editor.getData();
  if (Prototype.Browser.IE) {
    save_style = deleteStyle();
  }
  form = getForm("download-pdf-form");
  if (editor.readOnly) {
    form.elements.first_time.value = "1";
  }
  else {
    form.elements.first_time.value = "0";
    form.elements.content.value = encodeURIComponent(content);
  }
  form.onsubmit();
}
