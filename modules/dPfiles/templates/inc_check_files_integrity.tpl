{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  emptyFileReportTable = function () {
    if (confirm("Voulez-vous vraiment vider la table de reporting des fichiers ?")) {
      var url = new Url('files', 'do_empty_report_table', 'dosql');
      url.requestUpdate('systemMsg', {method: 'post'});
    }
  }
</script>

<button type="button" class="info" onclick="showInstructionModal()">
  {{tr}}common-Information|pl{{/tr}}
</button>

<button type="button" class="trash" onclick="emptyFileReportTable()">
  Vider la table de reporting
</button>

<div id="prepare_integrity" style="display: none;">
  <p>Avant l'exécution du <code>find</code>, pensez à cliquer sur le bouton pour obtenir le dernier identifiant de fichier dans les paramètres de la crontab</p><br />
  <p>Exécutez la commande suivante avec les droits root :</p>
  <pre>
    <code>find {{$conf.dPfiles.CFile.upload_directory}}/private/* -type f -exec ls '{}' -dils --time-style=+"%Y-%m-%d_%H:%M:%S" \; > {{$conf.root_dir}}/tmp/find_out</code>
  </pre>
  <br />
  <p>Puis, exécutez les commandes suivantes :</p>
  <pre>
    <code>mysql -u {{$conf.db.std.dbuser}} -p --local-infile=1</code><br />
    <code>use {{$conf.db.std.dbname}};</code><br />
    <code>DROP TABLE IF EXISTS `file_entries`;</code><br />
    <code>CREATE TABLE `file_entries` (
      `file_entries_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `inode` BIGINT UNSIGNED NOT NULL,
      `trois` INT NOT NULL,
      `chmod` CHAR(10) NOT NULL,
      `quatre` INT NOT NULL,
      `file_owner` VARCHAR(20) NOT NULL,
      `file_group` VARCHAR(20) NOT NULL,
      `file_size` BIGINT UNSIGNED NOT NULL,
      `create_date` DATETIME NOT NULL,
      `file_path` VARCHAR(255) NOT NULL,
      PRIMARY KEY (`file_entries_id`),
      UNIQUE `path` (`file_path`)
    ) ENGINE = MyISAM;</code><br />
    <code>DROP TABLE IF EXISTS `file_report`;</code><br />
    <code>CREATE TABLE `file_report` (
      `file_report_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `file_path` VARCHAR(255) NOT NULL,
      `file_hash` VARCHAR(255) NOT NULL,
      `object_class` VARCHAR(40),
      `object_id` INT(11) UNSIGNED,
      `file_size` BIGINT UNSIGNED NOT NULL,
      `file_unfound` ENUM ('0','1') DEFAULT '0' NOT NULL,
      `db_unfound` ENUM ('0','1') DEFAULT '0' NOT NULL,
      `size_mismatch` ENUM ('0','1') DEFAULT '0' NOT NULL,
      `empty_file` ENUM ('0', '1') DEFAULT '0' NOT NULL,
      `date_mismatch` ENUM ('0','1') DEFAULT '0' NOT NULL,
      PRIMARY KEY (`file_report_id`),
      UNIQUE (`file_path`)
    ) ENGINE = MyISAM;</code><br />
    <code>LOAD DATA LOCAL INFILE '{{$conf.root_dir}}/tmp/find_out'
    INTO TABLE file_entries FIELDS TERMINATED BY ' '
    (inode, trois, chmod, quatre, file_owner, file_group, file_size, create_date, file_path);</code>
  </pre><br />
  <p>Vous pouvez ensuite démarrer la vérification de l'intégrité.</p>
</div>

<div id="area_integrity"></div>