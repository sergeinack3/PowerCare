<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupHprimsante extends CSetup {
  /**
   * @see parent::__construct
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "hprimsante";

    $this->makeRevision("0.0");
    $this->makeRevision("0.1");

    $query = "CREATE TABLE `exchange_hprimsante` (
              `exchange_hprimsante_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `version` VARCHAR(255),
              `nom_fichier` VARCHAR(255),
              `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `date_production` DATETIME NOT NULL,
              `sender_id` INT (11) UNSIGNED,
              `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderMLLP','CSenderFileSystem'),
              `receiver_id` INT (11) UNSIGNED,
              `type` VARCHAR (255),
              `sous_type` VARCHAR (255),
              `date_echange` DATETIME,
              `message_content_id` INT (11) UNSIGNED,
              `acquittement_content_id` INT (11) UNSIGNED,
              `statut_acquittement` VARCHAR (255),
              `message_valide` ENUM ('0','1') DEFAULT '0',
              `acquittement_valide` ENUM ('0','1') DEFAULT '0',
              `id_permanent` VARCHAR (255),
              `object_id` INT (11) UNSIGNED,
              `object_class` ENUM ('CPatient','CSejour','CMedecin'),
              `code` VARCHAR(255) NOT NULL,
              `identifiant_emetteur` VARCHAR (255),
              `reprocess` TINYINT (4) UNSIGNED DEFAULT '0'
          ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `exchange_hprimsante`
                ADD INDEX (`group_id`),
                ADD INDEX (`date_production`),
                ADD INDEX (`sender_id`),
                ADD INDEX (`receiver_id`),
                ADD INDEX (`date_echange`),
                ADD INDEX (`message_content_id`),
                ADD INDEX (`acquittement_content_id`),
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.2");

    $query = "CREATE TABLE `receiver_hprimsante` (
              `receiver_hprimsante_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `nom` VARCHAR (255) NOT NULL,
              `libelle` VARCHAR (255),
              `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `actif` ENUM ('0','1') NOT NULL DEFAULT '0',
              `OID` VARCHAR (255),
              `synchronous` ENUM ('0','1') NOT NULL DEFAULT '1'
            ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `receiver_hprimsante`
              ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.3");

    $query = "CREATE TABLE `hprimsante_config` (
                `hprimsante_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `encoding` ENUM ('UTF-8','ISO-8859-1') DEFAULT 'UTF-8',
                `strict_segment_terminator` ENUM ('0','1') DEFAULT '0',
                `segment_terminator` ENUM ('CR','LF','CRLF'),
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderMLLP','CSenderFileSystem'),
                `action` ENUM ('IPP_NDA','Patient','Sejour','Patient_Sejour') DEFAULT 'IPP_NDA'
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprimsante_config`
                ADD INDEX (`sender_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.4");

    $query = "CREATE TABLE `receiver_hprimsante_config` (
                `receiver_hprimsante_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED,
                `ADM_version` ENUM ('2.1','2.2','2.3','2.4') DEFAULT '2.1',
                `ADM_sous_type` ENUM ('C','L', 'R') DEFAULT 'C'
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `receiver_hprimsante_config`
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.5");

    $query = "ALTER TABLE `receiver_hprimsante`
                ADD `monitor_sources` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.6");

    $query = "ALTER TABLE `hprimsante_config`
                ADD `notifier_entree_reelle` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.7");
    $query = "ALTER TABLE `exchange_hprimsante`
                ADD `master_idex_missing` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.8");
    $query = "ALTER TABLE `receiver_hprimsante`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
    $this->addQuery($query);

    $this->makeRevision("0.9");

    $query = "ALTER TABLE `receiver_hprimsante`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
    $this->addQuery($query);

    $this->makeRevision("1.0");
    $query = "ALTER TABLE `exchange_hprimsante`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
    $this->addQuery($query);

    $this->makeRevision("1.1");

    $query = "ALTER TABLE `exchange_hprimsante`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $query = "UPDATE `exchange_hprimsante`
                SET `emptied` = '1'
                WHERE `message_content_id` IS NULL
                AND `acquittement_content_id` IS NULL";
    $this->addQuery($query);

    $query = "ALTER TABLE `exchange_hprimsante`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
    $this->addQuery($query);

    $this->makeRevision("1.11");

    $query = "ALTER TABLE `exchange_hprimsante`
                ADD `response_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("1.12");

    $query = "ALTER TABLE `exchange_hprimsante`
                CHANGE `date_echange` `send_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("1.13");

    $query = "ALTER TABLE `exchange_hprimsante`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
    $this->addQuery($query);

    $this->makeRevision("1.14");

    $query = "ALTER TABLE `receiver_hprimsante`
                ADD `type` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("1.15");

    $query = "ALTER TABLE `receiver_hprimsante`
                ADD `use_specific_handler` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("1.16");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->makeRevision('1.17');
    $query = "ALTER TABLE `hprimsante_config` 
                ADD `handle_oru_type` VARCHAR(255),
                ADD `handle_oru_patient` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprimsante_config` 
                ADD INDEX sender (sender_class, sender_id);";
    $this->addQuery($query);

    $this->makeRevision('1.18');

    $query = "ALTER TABLE `hprimsante_config`
              ADD `search_patient_strategy` VARCHAR(255) DEFAULT 'best',
              ADD `handle_patient_ORU` ENUM ('0','1') DEFAULT '0',
              DROP COLUMN `handle_oru_patient`;";
    $this->addQuery($query);
    $this->makeRevision('1.19');

    $query = "ALTER TABLE `hprimsante_config`
                ADD `associate_category_to_a_file` ENUM ('0','1') DEFAULT '0',
                ADD `define_name` VARCHAR(255) DEFAULT 'name',
                ADD `id_category_patient` INT (11),
                ADD `object_attach_OBX` VARCHAR(255) DEFAULT 'CMbObject',
                ADD `mode_sas` ENUM ('0','1') DEFAULT '0',
                ADD `creation_date_file_like_treatment` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

      $this->mod_version = "1.20";
  }
}
