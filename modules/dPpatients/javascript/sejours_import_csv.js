/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SejoursImportCSV = {
  sejour_specs: null,

  updateCountsSejour: function (start, count) {
    var form = getForm("do-import-cegi-sejour");
    $V(form.elements.start, start);
    $V(form.elements.count, count);

    if ($V(form.elements.auto)) {
      form.onsubmit();
    }
  },

  parseSejourCSV: function () {
    $('CSejour_import_results_loading').show();

    var loading_id = 'CSejour_import_results_loading';
    var header_id = 'CSejour_import_results_header';
    var data_id = 'CSejour_import_results_data';

    ImportAnalyzer.init(header_id, data_id, loading_id);
    ImportAnalyzer.fields = this.sejour_specs;

    ImportAnalyzer.props.adeli = ImportAnalyzer.fields.adeli;
    ImportAnalyzer.props.rpps = ImportAnalyzer.fields.rpps;

    ImportAnalyzer.fields.adeli = (function (value, row) {
      // Aucun code
      if (!value && !row.rpps) {
        ImportAnalyzer.ElementChecker.oErrors.push({type: 'adeli', message: 'Codes ADELI et RPPS absents'});
      } else {
        // Adeli absent
        if (!value) {
          var oErrorsRPPS = ImportAnalyzer.checkElement(ImportAnalyzer.props.rpps, row.rpps, 'rpps');

          // RPPS en erreur
          if (oErrorsRPPS.length > 0) {
            // Suppression des erreurs de l'autre champ (concurrence)
            ImportAnalyzer.ElementChecker.oErrors = [];

            ImportAnalyzer.ElementChecker.oErrors.push({type: 'adeli', message: 'Code ADELI absent et code RPPS invalide'});
          }
        } else {
          var oErrorsADELI = ImportAnalyzer.checkElement(ImportAnalyzer.props.adeli, value, 'adeli');

          // Adeli en erreur
          if (oErrorsADELI.length > 0) {
            // RPPS absent
            if (!row.rpps) {
              ImportAnalyzer.ElementChecker.oErrors.push({type: 'adeli', message: 'Code ADELI invalide et code RPPS absent'});
            } else {
              var oErrorsRPPS = ImportAnalyzer.checkElement(ImportAnalyzer.props.rpps, row.rpps, 'rpps');

              // RPPS en erreur
              if (oErrorsRPPS.length > 0) {
                // Suppression des erreurs de l'autre champ (concurrence)
                ImportAnalyzer.ElementChecker.oErrors = oErrorsADELI;

                ImportAnalyzer.ElementChecker.oErrors.push({type: 'adeli', message: 'Codes ADELI et RPPS invalides'});
              }
            }
          }
        }
      }
    });

    ImportAnalyzer.fields.rpps = (function (value, row) {
      // Aucun code
      if (!value && !row.adeli) {
        ImportAnalyzer.ElementChecker.oErrors.push({type: 'rpps', message: 'Codes RPPS et ADELI absents'});
      } else {
        // RPPS absent
        if (!value) {
          var oErrorsADELI = ImportAnalyzer.checkElement(ImportAnalyzer.props.adeli, row.adeli, 'adeli');

          // RPPS en erreur
          if (oErrorsADELI.length > 0) {
            // Suppression des erreurs de l'autre champ (concurrence)
            ImportAnalyzer.ElementChecker.oErrors = [];

            ImportAnalyzer.ElementChecker.oErrors.push({type: 'rpps', message: 'Code RPPS absent et code ADELI invalide'});
          }
        } else {
          var oErrorsRPPS = ImportAnalyzer.checkElement(ImportAnalyzer.props.rpps, value, 'rpps');

          // RPPS en erreur
          if (oErrorsRPPS.length > 0) {
            // Adeli absent
            if (!row.adeli) {
              ImportAnalyzer.ElementChecker.oErrors.push({type: 'rpps', message: 'Code RPPS invalide et code ADELI absent'});
            } else {
              var oErrorsADELI = ImportAnalyzer.checkElement(ImportAnalyzer.props.adeli, row.adeli, 'adeli');

              // RPPS en erreur
              if (oErrorsADELI.length > 0) {
                // Suppression des erreurs de l'autre champ (concurrence)
                ImportAnalyzer.ElementChecker.oErrors = oErrorsRPPS;

                ImportAnalyzer.ElementChecker.oErrors.push({type: 'rpps', message: 'Codes RPPS et ADELI invalides'});
              }
            }
          }
        }
      }
    });

    var input = $('import_CSejour_file');

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
          preview:           $V('CSejour_count_to_display')
        });
      }
    });
  }
}