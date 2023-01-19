/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mblists', {
  requires: ['dialog'],
  init: function(editor) {
    var me = this;
    var date = new Date();
    date = Math.round(date.getTime()/3600000);

    CKEDITOR.dialog.add('mblists_dialog', function() {
      return {
        buttons: [
          {
            id: 'close_button',
            type: 'button',
            title: 'Fermer',
            label: "Fermer",
            onClick: function(e) { CKEDITOR.dialog.getCurrent().hide(); }
          }
        ],
        title : 'Insérer une liste de choix',
        minWidth : 350,
        minHeight : 210,
        contents : [
          {
            label : 'Insertion de liste de choix',
            expand : true,
            elements :
            [
              {
                type : 'html',
                html : '<iframe id="' + me.name + '_iframe" src="' + me.path + 'dialogs/lists.html?' + date + '" style="width: 100%; height: 100%"></iframe>'
              }
            ]
          }
        ]
      };
    });

    editor.addCommand('mblists', {exec: mblists_onclick});
    editor.ui.addButton('mblists', {
      label:   $T('CCompteRendu-plugin-mblists'),
      command: 'mblists',
      icon:    this.path + 'images/mblists.png'
    });
  }
});

function mblists_onclick(editor) {
  editor.openDialog('mblists_dialog');
}