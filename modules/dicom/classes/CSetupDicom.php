<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupDicom extends CSetup {
  /**
   * Update reponse field
   *
   * @return bool
   */
  protected function updateResponseSender() {

  }

  /**
   * The constructor
   *
   * @return void
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "dicom";
    $this->makeRevision("0.0");

    $query = "CREATE TABLE `dicom_sender` (
                `dicom_sender_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `user_id` INT (11) UNSIGNED,
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0',
                `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
                `create_ack_file` ENUM ('0','1') DEFAULT '1',
                `delete_file` ENUM ('0','1') DEFAULT '1'
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "CREATE TABLE `dicom_source` (
                `dicom_source_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `role` ENUM ('prod', 'qualif') NOT NULL DEFAULT 'qualif',
                `host` TEXT NOT NULL,
                `type_echange` VARCHAR (255),
                `active` ENUM ('0','1') NOT NULL DEFAULT '1',
                `loggable` ENUM ('0','1') NOT NULL DEFAULT '1',
                `port` INT (11) NOT NULL
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "CREATE TABLE `dicom_session` (
                `dicom_session_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `receiver` VARCHAR (255) NOT NULL,
                `sender` VARCHAR (255) NOT NULL,
                `begin_date` DATETIME NOT NULL,
                `end_date` DATETIME,
                `messages` TEXT,
                `status` ENUM('Rejected', 'Completed', 'Aborted'),
                `group_id` INT (11) UNSIGNED,
                `sender_id` INT (11) UNSIGNED,
                `receiver_id` INT (11) UNSIGNED,
                `dicom_exchange_id` INT (11) UNSIGNED,
                `state` VARCHAR (255) NOT NULL,
                `presentation_contexts` VARCHAR (255)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `dicom_session`
                ADD INDEX (`begin_date`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `dicom_exchange` (
                `dicom_exchange_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `requests` TEXT NOT NULL,
                `responses` TEXT NOT NULL,
                `presentation_contexts` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `date_production` DATETIME NOT NULL,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CDicomSender'),
                `receiver_id` INT (11) UNSIGNED,
                `type` VARCHAR (255),
                `sous_type` VARCHAR (255),
                `date_echange` DATETIME,
                `statut_acquittement` VARCHAR (255),
                `message_valide` ENUM ('0','1') DEFAULT '0',
                `acquittement_valide` ENUM ('0','1') DEFAULT '0',
                `id_permanent` VARCHAR (255),
                `object_id` INT (11) UNSIGNED,
                `object_class` VARCHAR (80)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `dicom_exchange`
                ADD INDEX (`date_production`);";

    $this->addQuery($query);

    $this->makeRevision("0.1");

//    $query = "CREATE TABLE `dicom_table_entry` (
//                `dicom_table_entry_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
//                `group_number` INT (11) NOT NULL,
//                `element_number` INT (11) NOT NULL,
//                `mb_object_class` VARCHAR (255) NOT NULL,
//                `mb_object_attr` VARCHAR (255) NOT NULL,
//                `group_id` INT (11) NOT NULL
//              ) /*! ENGINE=MyISAM */;";
//    $this->addQuery($query);

    $query = "ALTER TABLE `dicom_exchange`
                MODIFY `requests` TEXT,
                MODIFY `responses` TEXT;";
    $this->addQuery($query);

    $this->makeRevision('0.2');

    $query = "ALTER TABLE `dicom_session`
                MODIFY `presentation_contexts` TEXT;";
    $this->addQuery($query);

    $this->makeRevision('0.3');

    $query = "ALTER TABLE `dicom_exchange`
                MODIFY `presentation_contexts` TEXT;";
    $this->addQuery($query);

    $this->makeRevision('0.4');

    $query = "ALTER TABLE `dicom_source`
                ADD `user` VARCHAR (255),
                ADD `password` VARCHAR (50),
                ADD `iv` VARCHAR (255),
                ADD `libelle` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision('0.5');

    $query = "CREATE TABLE `dicom_configs` (
                `dicom_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CDicomSender'),
                `send_0032_1032` ENUM('0', '1') DEFAULT '0',
                `value_0008_0060` VARCHAR(100)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.6");
    $query = "ALTER TABLE `dicom_sender`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
    $this->addQuery($query);

    $this->makeRevision("0.7");
    $query = "ALTER TABLE `dicom_sender`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
    $this->addQuery($query);

    $this->makeRevision("0.8");
    $query = "ALTER TABLE `dicom_exchange`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
    $this->addQuery($query);

    $this->makeRevision("0.9");

    $query = "ALTER TABLE `dicom_exchange`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $query = "UPDATE `dicom_exchange`
                SET `emptied` = '1'
                WHERE `requests` IS NULL
                AND `responses` IS NULL";
    $this->addQuery($query);

    $query = "ALTER TABLE `dicom_exchange`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
    $this->addQuery($query);

    $this->makeRevision("1.0");

    $query = "ALTER TABLE `dicom_exchange`
                ADD `reprocess` MEDIUMINT (9) UNSIGNED DEFAULT '0',
                ADD `master_idex_missing` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("1.01");

    $query = "ALTER TABLE `dicom_exchange`
                ADD `response_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("1.02");

    $query = "ALTER TABLE `dicom_exchange`
                CHANGE `date_echange` `send_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("1.03");

    $query = "ALTER TABLE `dicom_exchange`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
    $this->addQuery($query);

    $this->makeRevision("1.04");

    $query = "ALTER TABLE `dicom_source` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("1.05");
    $query = "ALTER TABLE `dicom_source` 
                DROP `delete_file`;";
    $this->addQuery($query);

    $this->makeRevision('1.06');

    $query = "ALTER TABLE `dicom_configs` ADD `physician_separator` VARCHAR(10) DEFAULT ' ';";
    $this->addQuery($query);

    $this->makeRevision('1.07');

    $query = "ALTER TABLE `dicom_configs` ADD `uid_0020_000d` ENUM ('0', '1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision('1.08');
    $this->setModuleCategory("interoperabilite", "echange");

    $this->makeRevision('1.09');

    $query = "ALTER TABLE `dicom_sender` 
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
    $this->addQuery($query);

    $query = "UPDATE `dicom_sender`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1'";
    $this->addQuery($query);

    $query = "ALTER TABLE `dicom_sender` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
    $this->addQuery($query);

    $this->makeRevision('1.10');
      $query = "ALTER TABLE `dicom_sender` 
                ADD `type` VARCHAR (255);";
      $this->addQuery($query);
      $this->makeRevision('1.11');

      $query = "ALTER TABLE `dicom_source` ADD `client_name` VARCHAR(255);";
      $this->addQuery($query);

      $this->makeRevision('1.12');
      $query = "ALTER TABLE `dicom_source` 
        ADD `retry_strategy` VARCHAR(255), 
        ADD `first_call_date` DATETIME;";
      $this->addQuery($query);

      $this->mod_version = '1.13';
  }
}
