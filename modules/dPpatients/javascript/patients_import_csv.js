/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatientsImportCSV = window.PatientsImportCSV || {
  patient_specs: '',

  updateCountsPat: function (start, count) {
    var form = getForm("do-import-patient-pat");
    $V(form.elements.start, start);
    $V(form.elements.count, count);

    if ($V(form.elements.auto)) {
      form.onsubmit();
    }
  },

  parsePatientCSV: function () {
    $('CPatient_import_results_loading').show();

    var loading_id = 'CPatient_import_results_loading';
    var header_id = 'CPatient_import_results_header';
    var data_id = 'CPatient_import_results_data';

    ImportAnalyzer.init(header_id, data_id, loading_id);
    ImportAnalyzer.fields = PatientsImportCSV.patient_specs;

    if ($V($('import_IPP'))) {
      ImportAnalyzer.fields._IPP += ' notNull';
    }

    var input = $('import_CPatient_file');

    ImportAnalyzer.run(input, {
      outputLogs: true, displayErrorLines: true, callback: function () {
        ImportAnalyzer.field_errors = {};
        ImportAnalyzer.messages = [];
        ImportAnalyzer.unique_messages = {};
        ImportAnalyzer.parsed_data = [];
        ImportAnalyzer.parsed_fields = [];
        ImportAnalyzer.currentRow = {};
        ImportAnalyzer.currentLineNumber = 1;

        if ($V($('import_IPP'))) {
          ImportAnalyzer.fields._IPP += ' notNull';
        }

        ImportAnalyzer.run(input, {
          outputLogs:        false,
          displayErrorLines: false,
          outputData:        true,
          preview:           $V('CPatient_count_to_display')
        });
      }
    });
  }
};