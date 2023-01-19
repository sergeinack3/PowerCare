{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  toolsIntegrity = function() {
    new Url("files", "ajax_integrity_file")
      .addParam("cron_job_id", "{{$cron_job->_id}}")
      .requestUpdate("area_integrity");
  };
  
  showInstructionModal = function () {
    Modal.open('prepare_integrity', {title: "Informations", width: 900, showClose: true} );
  };

  Main.add(function() {
    Control.Tabs.create('tabs-files-integrity', true);
    toolsIntegrity();
  });
</script>

<ul id="tabs-files-integrity" class="control_tabs">
  <li><a href="#dashboard_files_integrity">Tableau de bord</a></li>
  <li><a href="#check_files_integrity">Vérification de l'intégrité</a></li>
  <li><a href="#file-size-correction">Correction de la taille</a></li>
  <li><a href="#deleted-files">Suppression des fichiers</a></li>
</ul>

<div id="dashboard_files_integrity" style="display: none;">
  {{mb_include template=inc_dashboard_files_integrity}}
</div>

<div id="check_files_integrity" style="display: none;">
  {{mb_include template=inc_check_files_integrity}}
</div>

<div id="file-size-correction" style="display: none;">
  {{mb_include template=inc_correct_file_size}}
</div>

<div id="deleted-files" style="display: none;">
  {{mb_include template=inc_remove_deleted_files}}
</div>
