/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbhelpers', {
  requires: ['dialog'],
  init: function(editor) {
    var me = this;
    var date = new Date();
    date = Math.round(date.getTime()/3600000);

    CKEDITOR.dialog.add('mbhelpers_dialog', function() {
      return {
        title : 'Insérer une aide à la saisie',
        buttons: [
          {
             id: 'close_button',
             type: 'button',
             title: 'Fermer',
             label: "Fermer",
             onClick: function() { CKEDITOR.dialog.getCurrent().hide(); }
           }
        ],
        minWidth : 450,
        minHeight : 210,
        contents : [
          {
            label : 'Insertion d\'aide à la saisie',
            expand : true,
            elements : [
              {
                type : 'html',
                html : '<iframe src="' + me.path + 'dialogs/helpers.html?' + date + '" style="width: 100%; height: 100%"></iframe>'
              }
            ]
          }
        ]
      };
    });

    editor.addCommand('mbhelpers', {exec: mbhelpers_onclick});
    editor.ui.addButton('mbhelpers', {
      label:   $T('CCompteRendu-plugin-mbhelpers'),
      command: 'mbhelpers',
      icon:    this.path + 'images/mbhelpers.png'
    });
  }
});

function mbhelpers_onclick() {
  CKEDITOR.instances.htmlarea.openDialog('mbhelpers_dialog');
}