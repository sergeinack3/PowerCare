/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbspace', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mbspace', {exec: mbspace_onclick});
    editor.ui.addButton('mbspace', {
      label:   'Espace insécable',
      command: 'mbspace',
      icon:    this.path + 'images/icon.png'
    });
  }
});

function mbspace_onclick(editor) {
  editor.focus();
  if (CKEDITOR.env.gecko) {
    insertSpecialChar(editor, '&nbsp;');
  }
  else {
    editor.insertHtml('&nbsp;');
  }
  return true;
}

function insertSpecialChar(editor, specialChar) {
  var selection = editor.getSelection(),
      ranges    = selection.getRanges(),
      range, textNode;

  for (var i = 0, len = ranges.length ; i < len ; i++) {
    range = ranges[i];
    range.deleteContents();
    textNode = CKEDITOR.dom.element.createFromHtml(specialChar);
    range.insertNode(textNode);
  }

  range.moveToPosition(textNode, CKEDITOR.POSITION_AFTER_END);
  range.select();
}