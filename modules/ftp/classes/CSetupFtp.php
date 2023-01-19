<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupFtp extends CSetup {

  function __construct() {
    parent::__construct();

    $this->mod_name = "ftp";
    $this->makeRevision("0.0");

    $query = "CREATE TABLE IF NOT EXISTS `source_ftp` (
                `source_ftp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `port` INT (11) DEFAULT '21',
                `timeout` INT (11) DEFAULT '90',
                `pasv` ENUM ('0','1') DEFAULT '0',
                `mode` ENUM ('FTP_ASCII','FTP_BINARY') DEFAULT 'FTP_ASCII',
                `fileprefix` VARCHAR (255),
                `fileextension` VARCHAR (255),
                `filenbroll` ENUM ('1','2','3','4'),
                `fileextension_write_end` VARCHAR (255),
                `counter` VARCHAR (255),
                `name` VARCHAR (255) NOT NULL,
                `host` TEXT NOT NULL,
                `user` VARCHAR (255),
                `password` VARCHAR (50)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `source_ftp` 
               ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif';";
    $this->addQuery($query, true);

    $query = "ALTER TABLE `source_ftp` 
               ADD `type_echange` VARCHAR (255);";
    $this->addQuery($query, true);

    $this->makeRevision("0.01");

    $query = "CREATE TABLE `sender_ftp` (
                `sender_ftp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_ftp` 
              ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.02");

    $query = "CREATE TABLE `echange_ftp` (
                `echange_ftp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ftp_fault` ENUM ('0','1') DEFAULT '0',
                `emetteur` VARCHAR (255),
                `destinataire` VARCHAR (255),
                `date_echange` DATETIME NOT NULL,
                `function_name` VARCHAR (255) NOT NULL,
                `input` MEDIUMTEXT,
                `output` MEDIUMTEXT,
                `purge` ENUM ('0','1') DEFAULT '0',
                `response_time` FLOAT
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_ftp` 
                ADD INDEX (`date_echange`);";
    $this->addQuery($query);

    $this->makeRevision("0.03");

    $query = "ALTER TABLE `source_ftp` 
                ADD `active` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.04");

    $query = "ALTER TABLE `source_ftp` 
              CHANGE `timeout` `timeout` INT (11) DEFAULT '5';";
    $this->addQuery($query);

    $this->makeRevision("0.05");

    $query = "ALTER TABLE `sender_ftp` 
                ADD `user_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_ftp` 
                ADD INDEX (`user_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.06");

    $query = "ALTER TABLE `source_ftp` 
              ADD `loggable` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.07");

    $query = "ALTER TABLE `echange_ftp` 
                ADD INDEX (`purge`);";
    $this->addQuery($query);

    $this->makeRevision("0.08");

    $query = "ALTER TABLE `sender_ftp` 
                ADD `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
                ADD `create_ack_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.09");

    $query = "ALTER TABLE `echange_ftp` 
                ADD INDEX (`function_name`);";
    $this->addQuery($query);

    $this->makeRevision("0.10");

    $query = "ALTER TABLE `sender_ftp` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `source_ftp`
                CHANGE `password` `password` VARCHAR (255),
                ADD `iv` VARCHAR (16) AFTER `password`;";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "UPDATE source_ftp
                SET source_ftp.name = Replace(name, 'CReceiverIHE', 'CReceiverHL7v2')
                WHERE source_ftp.name LIKE 'CReceiverIHE-%';";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `source_ftp`
               ADD `ssl` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query, true);

    $this->makeRevision("0.15");
    $query = "CREATE TABLE `source_sftp` (
                `source_sftp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `port` INT (11) DEFAULT '22',
                `timeout` INT (11) DEFAULT '10',
                `name` VARCHAR (255) NOT NULL,
                `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif',
                `host` TEXT NOT NULL,
                `user` VARCHAR (255),
                `password` VARCHAR (50),
                `iv` VARCHAR (255),
                `type_echange` VARCHAR (255),
                `active` ENUM ('0','1') NOT NULL DEFAULT '1',
                `loggable` ENUM ('0','1') NOT NULL DEFAULT '1',
                `fileprefix` VARCHAR (255),
                `fileextension_write_end` VARCHAR (255),
                `fileextension` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "CREATE TABLE `sender_sftp` (
                `sender_sftp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED,
                `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
                `create_ack_file` ENUM ('0','1') DEFAULT '1',
                `delete_file` ENUM ('0','1') DEFAULT '1',
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $query = "ALTER TABLE `sender_sftp`
                ADD INDEX (`user_id`),
                ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `source_ftp`
                ADD `libelle` VARCHAR (255);";
    $this->addQuery($query);

    $query = "ALTER TABLE `source_sftp`
                ADD `libelle` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `sender_sftp`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_ftp`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `sender_ftp`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_sftp`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
    $this->addQuery($query);

    $this->makeRevision("0.21");

    $query = "ALTER TABLE `echange_ftp`
                ADD INDEX `purge_echange` (`purge`, `date_echange`);";
    $this->addQuery($query, true);

    $this->makeRevision("0.22");

    $query = "ALTER TABLE `echange_ftp`
                ADD `response_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("0.23");

    $query = "ALTER TABLE `echange_ftp`
                ADD `source_id` INT (11) UNSIGNED AFTER `destinataire`,
                ADD `source_class` VARCHAR (80) AFTER `source_id`;";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `source_ftp`
                ADD `default_socket_timeout` SMALLINT (4) DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "ALTER TABLE `source_sftp` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `source_ftp` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `source_ftp` 
                ADD `ack_prefix` VARCHAR(255);";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $query = "ALTER TABLE `source_ftp` 
                ADD `timestamp_file` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->makeRevision("0.29");

    $query = "ALTER TABLE `sender_ftp` 
                ADD `after_processing_action` ENUM ('none','move','delete') DEFAULT 'none',
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
    $this->addQuery($query);

    $query = "UPDATE `sender_ftp`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1'";
    $this->addQuery($query);

    $query = "UPDATE `sender_ftp`
                SET `after_processing_action` = 'delete'
                WHERE `delete_file` = '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_ftp` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_sftp` 
                ADD `after_processing_action` ENUM ('none','move','delete') DEFAULT 'none',
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
    $this->addQuery($query);

    $query = "UPDATE `sender_sftp`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1'";
    $this->addQuery($query);

    $query = "UPDATE `sender_sftp`
                SET `after_processing_action` = 'delete'
                WHERE `delete_file` = '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_sftp` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
    $this->addQuery($query);
    $this->makeRevision('0.30');
      $query = "ALTER TABLE `sender_ftp` 
                ADD `type` VARCHAR (255);";
      $this->addQuery($query);
      $query = "ALTER TABLE `sender_sftp` 
                ADD `type` VARCHAR (255);";
      $this->addQuery($query);

      $this->makeRevision('0.31');
      $query = "ALTER TABLE `source_sftp` ADD `client_name`  VARCHAR(255);";
      $this->addQuery($query);
      $query = "ALTER TABLE `source_ftp` ADD `client_name`  VARCHAR(255);";
      $this->addQuery($query);

      $this->makeRevision('0.32');
      
      $query = "ALTER TABLE `source_ftp` 
                ADD `retry_strategy` VARCHAR(255),
                ADD `first_call_date` DATETIME
                ";
      $this->addQuery($query);
      
      $query = "ALTER TABLE `source_sftp` 
                ADD `retry_strategy` VARCHAR(255),
                ADD `first_call_date` DATETIME
                ";
      $this->addQuery($query);

      $this->mod_version = "0.33";
  }
}
