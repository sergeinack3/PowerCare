/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CKEDITOR.plugins.add('mbdate', {
  init: function(editor) {
    editor.ui.addButton('mbdate', {
      label:   'Insérer une date',
      command: 'mbdate',
    });
    editor.on('instanceReady', function() {
      var plugin_button = $$('.cke_button__mbdate')[0];

      if (!plugin_button) {
        return;
      }

      var input = DOM.input({name: 'mbdate', type: 'hidden', value: 'now', className: 'notNull', onchange: 'insertDate(this.value);'});
      var input_da = DOM.input({name: 'mbdate_da', type: 'text', style: 'height: 0px; width: 0px; border: none;'});

      plugin_button.insert(input).insert(input_da);

      Calendar.regField(input, {container: plugin_button});

      $$('.inputExtension')[0].observe('click', function() {
        if ($$('.datepickerControl .cancel').length === 0) {
          $$('.datepickerControl')[0].insert({
            top : DOM.button({className: 'cancel notext', style: 'float:right', onclick: '$$(\'.inputExtension\')[0].click()'})
          });
        }
      });
    });
  }
});

function insertDate(value) {
  var editor = CKEDITOR.instances.htmlarea;

  editor.focus();
  editor.insertText(Date.fromDATE(value).toLocaleDate() + ' ');
}