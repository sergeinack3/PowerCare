/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbheader', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mbheader', {exec: mbheader_onclick});
    editor.ui.addButton('mbheader', {
      label:   'Entête',
      command: 'mbheader',
      icon:    this.path + 'images/icon.gif'
    });
    editor.on("instanceReady", function() {
      // On regarde la présence d'un entête dans la source une fois que l'éditeur est prêt.
      // S'il y a un entête, alors on coche le bouton, sinon on le désactive.

      if (window.document.getElementById('htmlarea').innerHTML.indexOf("header") != -1) {
        editor.getCommand('mbheader').setState(CKEDITOR.TRISTATE_ON);
        return;
      }
      editor.getCommand('mbheader').setState(CKEDITOR.TRISTATE_DISABLED);
    });
  }
});

function mbheader_onclick(editor) {
  var oHeader = editor.document.getById("header");
  if (!oHeader) return;
  if (oHeader.$.style.display == "block" || oHeader.$.style.display == "") {
    oHeader.setStyle("display", "none");
    editor.getCommand('mbheader').setState(CKEDITOR.TRISTATE_OFF);
    return;
  }
  oHeader.setStyle("display",  "block");
  editor.getCommand('mbheader').setState(CKEDITOR.TRISTATE_ON);
}