/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbthumbs', {
  requires: ['dialog'],
  init: function(editor) {
    editor.addCommand('mbthumbs', {exec: mbthumbs_onclick});
    editor.ui.addButton('mbthumbs', {
      label:   'Rafraichir les vignettes',
      command: 'mbthumbs',
      icon:    '../../style/mediboard_ext/images/buttons/change.png'
    });
    editor.addCommand('mbhidethumbs', {exec: mbhidethumbs_onclick});
    editor.ui.addButton('mbhidethumbs', {
      label:   'Afficher/Cacher les vignettes',
      command: 'mbhidethumbs',
      icon:    '../../style/mediboard_ext/images/buttons/hslip.png'
    });
  }
});

function mbthumbs_onclick(editor) {
  editor.on("key", loadOld);
  Thumb.refreshThumbs();
}

function mbhidethumbs_onclick(editor) {
  var command = editor.getCommand('mbhidethumbs');
  if (command.state == CKEDITOR.TRISTATE_ON) {
    command.setState(CKEDITOR.TRISTATE_OFF);
  }
  else {
    command.setState(CKEDITOR.TRISTATE_ON);
  }

  Thumb.choixAffiche();
}
