/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mblineheight', {
  init: function(editor) {
    var me = this;
    var date = new Date();
    date = Math.round(date.getTime()/3600000);

    CKEDITOR.dialog.add('mblineheight_dialog', function() {
      return {
        title : 'Augmenter / Réduire l\'interligne',
        minWidth : 300,
        minHeight : 90,
        contents : [
          {
            label : 'Augmenter / Réduire l\'interligne',
            expand : true,
            elements : [
              {
                type : 'html',
                html : '<iframe src="' + me.path + 'dialogs/mblineheight.html?' + date + '"></iframe>'
              }
            ]
          }
        ]
      };
    });

    editor.addCommand('mblineheight', {exec: mblineheight_onclick});
    editor.ui.addButton('mblineheight', {
      label:   'Interligne de paragraphe',
      command: 'mblineheight',
      icon:    this.path + 'images/mblineheight.png'
    });
  }
});

function mblineheight_onclick(editor) {
  editor.openDialog('mblineheight_dialog');
}