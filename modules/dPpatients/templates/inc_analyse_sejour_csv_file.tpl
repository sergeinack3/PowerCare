{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <input type="file" id="import_CSejour_file" name="import_file" accept=".csv" />

  &mdash;
  <label>
    {{tr}}common-Result to display|pl{{/tr}} :
    <input type="text" id="CSejour_count_to_display" name="CSejour_count_to_display" value="10" size="2" />
  </label>

  <button type="button" class="tick notext singleclick" onclick="SejoursImportCSV.parseSejourCSV();">
    {{tr}}Validate{{/tr}}
  </button>

  <div id="CSejour_import_results_loading" class="small-info" style="display: none;">Analyse en cours</div>
  <div id="CSejour_import_results_header"></div>
  <div id="CSejour_import_results_data"></div>
</div>