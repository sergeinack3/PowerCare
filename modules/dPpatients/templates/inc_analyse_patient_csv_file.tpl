{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    PatientsImportCSV.patient_specs = {{$patient_specs|@json}};
  });
</script>

<div>
  <input type="file" id="import_CPatient_file" name="import_file" accept=".csv" />

  <label>
    <input type="checkbox" id="import_IPP" name="import_IPP" checked />
    Importer par IPP
  </label>

  &mdash;
  <label>
    {{tr}}common-Result to display|pl{{/tr}} :
    <input type="text" id="CPatient_count_to_display" name="count_to_display" value="10" size="2" />
  </label>

  <button type="button" class="tick notext singleclick" onclick="PatientsImportCSV.parsePatientCSV();">{{tr}}Validate{{/tr}}</button>

  <div id="CPatient_import_results_loading" class="small-info" style="display: none;">Analyse en cours</div>
  <div id="CPatient_import_results_header"></div>
  <div id="CPatient_import_results_data"></div>
</div>