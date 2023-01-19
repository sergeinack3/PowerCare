{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  testOperation = function() {
    new Url("files", "ajax_test_files").requestUpdate("test_create");
  };
  listFiles = function() {
    new Url("files", "ajax_repair_files").requestUpdate("list_files");
  };
  shrinkPDF = function() {
    new Url("files", "ajax_test_shrink").
      requestUpdate("shrink_pdf");
  };
  integrityFiles = function() {
    new Url("files", "vw_integrity_files")
      .requestModal("80%", "80%");
  };
  migrateFiles = function () {
    var form = getForm('fileMigration');
    form.elements.migrate_files.disabled = true;
    new Url("files", "ajax_migrate_files")
      .addFormData(form)
      .requestUpdate('migrated_files', {onComplete: function() {
        form.elements.migrate_files.disabled = false;
      }});
  };
  checkMigrationStatus = function () {
    new Url("files", "check_migration_status")
      .requestUpdate('migration_status');
  };
  correctFileSize = function() {
    new Url('files', "vw_file_size_correction").requestModal("50%", "50%");
  };
  parseFile = function () {
    new Url('files', "vw_file_parser").requestModal("50%", "50%");
  };
  showCompteRenduRegeneration = function() {
    new Url('dPcompteRendu', 'vw_regenerate_modele').requestModal("50%", "50%");
  };
  popCorrectConf = function () {
    new Url('files', 'vw_correct_dir').requestModal("50%", "50%");
  }
</script>
<table class="form">
  <tr>
    <th style="width: 50%;">
      <button type="button" class="button search" onclick="testOperation()">{{tr}}CFile-test_create{{/tr}}</button>
    </th>
    <td>
      <div id="test_create"></div>
    </td>
  </tr>
  <tr>
    <th>
      <button type="button" class="button search" onclick="listFiles()">{{tr}}CFile-test_no_size{{/tr}}</button>
    </th>
    <td>
      <div id="list_files"></div>
    </td>
  </tr>
  <tr>
    <th>
      <button class="search" type="button" onclick="shrinkPDF()">Shrink de pdf</button>
    </th>
    <td>
      <div id="shrink_pdf"></div>
    </td>
  </tr>
  <tr>
    <th>
      <button class="search" type="button" onclick="integrityFiles()">Intégrité des fichiers</button>
    </th>
    <td></td>
  </tr>
  <tr>
    <th>
      <form name="fileMigration" method="get">
        <label>
          Auto:
          <input type="checkbox" name="auto" value="1"/>
        </label>
        <input type="text" name="count" value="100" size="2" />
        <button class="change" type="button" name="migrate_files" onclick="migrateFiles()">Migration des fichiers</button>
      </form>
    </th>
    <td>
      <div id="migrated_files"></div>
    </td>
  </tr>
  <tr>
    <th><button class="search" type="button" onclick="checkMigrationStatus()">Vérifier l'état de la migration</button></th>
    <td id="migration_status"></td>
  </tr>
  <tr>
    <th><button class="search" type="button" onclick="correctFileSize();">Corriger les tailles des fichiers</button></th>
    <td><button class="change" type="button" onclick="showCompteRenduRegeneration();">Régénérer les aperçus de compte-rendu</button></td>
  </tr>
  <tr>
    <th>
      <button class="search" type="button" onclick="parseFile()">File parser (Tika server)</button>
    </th>
    <td></td>
  </tr>

  <tr>
    <th>
      <button class="search" type="button" onclick="popCorrectConf()">Corriger les emplacements de fichiers</button>
    </th>
    <td></td>
  </tr>
</table>