/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ImportMedecinAnalyzer = window.ImportMedecinAnalyzer || {
  medecin_specs: '',

  parseMedecinCSV: function () {
    $('CMedecin_import_results_loading').show();

    var loading_id = 'CMedecin_import_results_loading';
    var header_id = 'CMedecin_import_results_header';
    var data_id = 'CMedecin_import_results_data';

    ImportAnalyzer.init(header_id, data_id, loading_id);
    ImportAnalyzer.fields = ImportMedecinAnalyzer.medecin_specs;

    var input = $("import_CMedecin_file");

    ImportAnalyzer.run(input, {
      outputLogs: true, displayErrorLines: true, callback: function () {
        ImportAnalyzer.field_errors = {};
        ImportAnalyzer.messages = [];
        ImportAnalyzer.unique_messages = {};
        ImportAnalyzer.parsed_data = [];
        ImportAnalyzer.parsed_fields = [];
        ImportAnalyzer.currentRow = {};
        ImportAnalyzer.currentLineNumber = 1;

        ImportAnalyzer.run(input, {
          outputLogs:        false,
          displayErrorLines: false,
          outputData:        true,
          preview:           10
        });
      }
    });
  }
};