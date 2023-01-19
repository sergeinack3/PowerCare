{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=import_analyzer ajax=1}}

<script>
  Main.add(function () {
    Control.Tabs.create('importation-tabs', true);

    require.config({
      paths: {
        Papa: 'lib/PapaParse/papaparse.min'
      }
    });
  });
</script>

<ul id="importation-tabs" class="control_tabs">
  <li><a href="#patient-import-tab">{{tr}}CCSVImportPatients{{/tr}}</a></li>
  <li><a href="#patient-export-tab">{{tr}}CPatient-export csv{{/tr}}</a></li>
  <li><a href="#sejour-tab">{{tr}}CCSVImportSejours{{/tr}}</a></li>
</ul>

<div id="patient-import-tab" style="display: none;">
  {{mb_include module=dPpatients template=vw_import_patients_csv}}
</div>

<div id="patient-export-tab" style="display: none;">
  {{mb_include module=dPpatients template=vw_export_patients_csv}}
</div>

<div id="sejour-tab" style="display: none;">
  {{mb_include module=dPpatients template=vw_import_sejours_csv}}
</div>