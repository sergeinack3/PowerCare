{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=sejours_import_csv ajax=1}}

<script>
  Main.add(function () {
    Control.Tabs.create('importation-sejours', true);

    $('CSejour_count_to_display').addSpinner({min: 1, max: 50});
  });
  SejoursImportCSV.sejour_specs = {{$sejour_specs|@json}};
</script>

<ul id="importation-sejours" class="control_tabs">
  <li><a href="#sejour-analysis">{{tr}}common-File parsing{{/tr}}</a></li>
  <li><a href="#sejour-import">Importation de séjours</a></li>
</ul>

<div id="sejour-analysis" style="display: none;">
  {{mb_include module=dPpatients template=inc_analyse_sejour_csv_file}}
</div>

<div id="sejour-import" style="display: none;">
  {{mb_include module=dPpatients template=inc_import_sejours_csv class='imports'}}
</div>