/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbpagebreak', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mbpagebreak', {exec: mbpagebreak_onclick});
    editor.ui.addButton('mbpagebreak', {
      label:   'Saut de page',
      command: 'mbpagebreak',
      icon:    this.path + 'images/mbpagebreak.gif'
    });
  }
});

function mbpagebreak_onclick(editor) {
  editor.insertHtml("<hr class='pagebreak' />");
}
