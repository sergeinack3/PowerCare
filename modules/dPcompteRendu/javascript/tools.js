/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Tools = {
  regenerateFiles: function() {
    new Url('compteRendu', 'ajax_regenerate_files')
      .addParam('start', $('start_regenerate').value)
      .requestUpdate('regenerate_area', {
        insertion: function(element, content) {
          element.down('div.ajax-loading').remove();
          element.insert(content);
        },
        onCreate: function() {
          var element = $('regenerate_area');
          WaitingMessage.cover(element);
        },
        onComplete: function(response) {
          if (response.status === 500 && $('auto_regenerate').checked) {
            var input = $('start_regenerate');
            input.value = parseInt(input.value) + 100;
            Tools.regenerateFiles();
          }
        },
        // Présence du onFailure obligatoire pour le lancement du onComplete
        onFailure: function() {}
      });
  },

  exportFields: function() {
    new Url('compteRendu', 'export_fields', 'raw')
      .addParam('object_class', $('object_class_field').value)
      .open();
  },

  correctFields: () => {
      new Url('compteRendu', 'correctFields')
          .requestUpdate('correct_fields_area');
  }
};
