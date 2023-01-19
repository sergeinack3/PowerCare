/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbfreetext', {
  requires: ['dialog'],
  init: function(editor) {
    var me = this;
    var date = new Date();
    date = Math.round(date.getTime()/3600000);

    CKEDITOR.dialog.add('mbfreetext_dialog', function() {
      return {
        title : 'Insérer une zone de texte libre',
        minWidth : 420,
        minHeight : 150,
        contents :
        [
          {
            id : 'iframe',
            label : 'Insertion de zone de texte libre',
            elements :
              [
                {
                  type : 'html',
                  html: '<iframe id="'+ me.name + '_iframe" src="modules/dPcompteRendu/fcke_plugins/mbfreetext/dialogs/insert_area.html?' + date + '" style="width: 100%; height: 100%;"></iframe>',

                  onShow: function() {
                    if (document.documentMode) {
                      return;
                    }
                    var iframe = document.getElementById(me.name + '_iframe');
                    var iframeWindow = iframe.contentWindow.document;
                    var searchinput = iframeWindow.getElementById("txtData");

                    if (searchinput) {
                      setTimeout(function() {
                        iframeWindow.body.focus();
                        searchinput.focus();
                      }, 100);
                    }
                  }
                }
              ]
          }
        ]
      };
    });
    editor.addCommand('mbfreetext', {exec: mbfreetext_onclick});
    editor.ui.addButton('mbfreetext', {
      label:   $T('CCompteRendu-plugin-mbfreetext'),
      command: 'mbfreetext',
      icon:    this.path + 'images/mbfreetext.png'
    });
  }
});

function mbfreetext_onclick(editor) {
  editor.openDialog('mbfreetext_dialog');
}
