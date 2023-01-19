{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=patients_import_csv ajax=1}}

<script>
  Main.add(function () {
    Control.Tabs.create('importation-patients', true);

    $('CPatient_count_to_display').addSpinner({min: 1, max: 50});
  });
</script>

<ul id="importation-patients" class="control_tabs">
  <li><a href="#patient-analysis">{{tr}}common-File parsing{{/tr}}</a></li>
  <li><a href="#patient-import">Importation de patients</a></li>
</ul>

<div id="patient-analysis" style="display: none;">
  {{mb_include module=dPpatients template=inc_analyse_patient_csv_file}}
</div>

<div id="patient-import" style="display: none;">
  {{mb_include module=dPpatients template=inc_import_patients_csv class='imports'}}
</div>