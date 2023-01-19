<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupCda extends CSetup
{
    /**
     * @see parent::__construct
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "cda";
        $this->makeRevision("0.0");

        $this->makeRevision("0.01");

        $query = "CREATE TABLE `exchange_cda` (
                `exchange_cda_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `identifiant_emetteur` VARCHAR (255),
                `initiateur_id` INT (11) UNSIGNED,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `date_production` DATETIME NOT NULL,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderFileSystem'),
                `receiver_id` INT (11) UNSIGNED,
                `type` VARCHAR (255),
                `sous_type` VARCHAR (255),
                `send_datetime` DATETIME,
                `response_datetime` DATETIME,
                `message_content_id` INT (11) UNSIGNED,
                `acquittement_content_id` INT (11) UNSIGNED,
                `statut_acquittement` VARCHAR (255),
                `message_valide` ENUM ('0','1') DEFAULT '0',
                `acquittement_valide` ENUM ('0','1') DEFAULT '0',
                `id_permanent` VARCHAR (255),
                `object_id` INT (11) UNSIGNED,
                `object_class` ENUM ('CSejour','CPatient','CConsultation','CCompteRendu','CFile'),
                `reprocess` TINYINT (4) UNSIGNED DEFAULT '0',
                `master_idex_missing` ENUM ('0','1') DEFAULT '0',
                `emptied` ENUM ('0','1') DEFAULT '0'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `exchange_cda` 
                ADD INDEX (`initiateur_id`),
                ADD INDEX (`group_id`),
                ADD INDEX (`date_production`),
                ADD INDEX sender (sender_class, sender_id),
                ADD INDEX (`receiver_id`),
                ADD INDEX (`send_datetime`),
                ADD INDEX (`response_datetime`),
                ADD INDEX (`message_content_id`),
                ADD INDEX (`acquittement_content_id`),
                ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "CREATE TABLE `receiver_cda` (
                `receiver_cda_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `OID` VARCHAR (255),
                `synchronous` ENUM ('0','1') NOT NULL DEFAULT '1',
                `monitor_sources` ENUM ('0','1') NOT NULL DEFAULT '1',
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod',
                `exchange_format_delayed` INT (11) UNSIGNED DEFAULT '60'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `receiver_cda` 
                ADD INDEX (`nom`),
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.02");

        $query = "ALTER TABLE `receiver_cda`
                ADD `type` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.03");

        $query = "ALTER TABLE `receiver_cda`
                ADD `use_specific_handler` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.04");
        $this->setModuleCategory("interoperabilite", "echange");

        $this->makeRevision("0.05");

        $query = "CREATE TABLE `cda_meta` (
                    `cda_meta_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `created_datetime` DATETIME NOT NULL,
                    `target_class` ENUM ('CFile','CObservationResultSet'),
                    `target_id` INT (11) UNSIGNED,
                    `patient_id` INT (11) UNSIGNED NOT NULL,
                    `id` VARCHAR (255) NOT NULL,
                    `relatedDocumentId` VARCHAR (255),
                    `relatedDocumentIdCodeCodeSytem` VARCHAR (255),
                    `code` VARCHAR (255),
                    `codeCodeSystem` VARCHAR (255),
                    `title` VARCHAR (255),
                    `effectiveTime` DATETIME,
                    `confidentialityCode` VARCHAR (255),
                    `confidentialityCodeCodeSytem` VARCHAR (255),
                    `languageCode` VARCHAR (255),
                    `setId` VARCHAR (255),
                    `versionNumber` INT (11)
                  )/*! ENGINE=MyISAM */;
                ALTER TABLE `cda_meta` 
                    ADD INDEX (`created_datetime`),
                    ADD INDEX target (target_class, target_id),
                    ADD INDEX (`patient_id`),
                    ADD INDEX (`effectiveTime`),
                    ADD INDEX (`id`);
                CREATE TABLE `cda_meta_order` (
                    `cda_meta_order_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `created_datetime` DATETIME NOT NULL,
                    `cda_meta_id` INT (11) UNSIGNED NOT NULL,
                    `orderId` VARCHAR (255),
                    `orderCodeSystem` VARCHAR (255),
                    `data` TEXT
                  )/*! ENGINE=MyISAM */;
                ALTER TABLE `cda_meta_order` 
                    ADD INDEX (`created_datetime`),
                    ADD INDEX (`cda_meta_id`);
                CREATE TABLE `cda_meta_participant` (
                    `cda_meta_participant_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `created_datetime` DATETIME NOT NULL,
                    `cda_meta_id` INT (11) UNSIGNED NOT NULL,
                    `type` VARCHAR (255),
                    `target_id` INT (11) UNSIGNED,
                    `target_class` VARCHAR (255),
                    `functionCode` VARCHAR (255),
                    `functionCodeSystem` VARCHAR (255),
                    `assignedAuthorId` VARCHAR (255),
                    `assignedAuthorIdSystem` VARCHAR (255),
                    `data` TEXT
                )/*! ENGINE=MyISAM */;
                ALTER TABLE `cda_meta_participant` 
                    ADD INDEX (`created_datetime`),
                    ADD INDEX (`cda_meta_id`),
                    ADD INDEX target (target_class, target_id);
                CREATE TABLE `cda_meta_documentation_of` (
                    `cda_meta_documentation_of_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `created_datetime` DATETIME NOT NULL,
                    `cda_meta_id` INT (11) UNSIGNED NOT NULL,
                    `serviceEventCode` VARCHAR (255),
                    `serviceEventCodeSystem` VARCHAR (255),
                    `serviceEventStart` DATETIME,
                    `serviceEventEnd` DATETIME,
                    `serviceEventPerformerId` INT (11) UNSIGNED,
                    `serviceEventPerformerClass` ENUM ('CMedecin','CPatient'),
                    `data` TEXT
                  )/*! ENGINE=MyISAM */;
                ALTER TABLE `cda_meta_documentation_of` 
                    ADD INDEX (`created_datetime`),
                    ADD INDEX (`cda_meta_id`),
                    ADD INDEX (`serviceEventStart`),
                    ADD INDEX (`serviceEventEnd`),
                    ADD INDEX serviceEventPerformerId (serviceEventPerformerClass, serviceEventPerformerId);
                CREATE TABLE `cda_meta_type_of_care` (
                    `cda_meta_type_of_care_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `created_datetime` DATETIME NOT NULL,
                    `cda_meta_id` INT (11) UNSIGNED NOT NULL,
                    `encounterId` VARCHAR (255),
                    `encounterIdCodeSystem` VARCHAR (255),
                    `encounterCode` VARCHAR (255),
                    `encounterCodeCodeSystem` VARCHAR (255),
                    `encounterStart` DATETIME,
                    `encounterEnd` DATETIME,
                    `encounterHealthCareFacility_id` INT (11) UNSIGNED,
                    `data` TEXT
                  )/*! ENGINE=MyISAM */;
                ALTER TABLE `cda_meta_type_of_care` 
                    ADD INDEX (`created_datetime`),
                    ADD INDEX (`cda_meta_id`),
                    ADD INDEX (`encounterStart`),
                    ADD INDEX (`encounterEnd`),
                    ADD INDEX (`encounterHealthCareFacility_id`);
                CREATE TABLE `cda_meta_type_of_care_participant` (
                    `cda_meta_type_of_care_participant_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `medecin_id` INT (11) UNSIGNED NOT NULL,
                    `cda_meta_type_of_care_id` INT (11) UNSIGNED NOT NULL
                  )/*! ENGINE=MyISAM */;
                ALTER TABLE `cda_meta_type_of_care_participant` 
                    ADD INDEX (`medecin_id`),
                    ADD INDEX (`cda_meta_type_of_care_id`);";

        $this->addQuery($query);

        $this->makeRevision("0.06");

        $query = "ALTER TABLE `exchange_cda` 
                    ADD `report` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.07");

        $query = "ALTER TABLE `cda_meta_documentation_of`
                    ADD `data_hash` VARCHAR (255);
                  ALTER TABLE `cda_meta_order`
                    ADD `data_hash` VARCHAR (255);
                  ALTER TABLE `cda_meta_participant`
                    ADD `data_hash` VARCHAR (255);
                  ALTER TABLE `cda_meta_type_of_care`
                    ADD `data_hash` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.08");

        $query = "CREATE TABLE `cda_config` (
                    `cda_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `sender_id` INT (11) UNSIGNED,
                    `sender_class` VARCHAR (255),
                    `encoding` ENUM ('UTF-8','ISO-8859-1') DEFAULT 'ISO-8859-1'
                  )/*! ENGINE=MyISAM */;
                  ALTER TABLE `cda_config`
                    ADD INDEX (`sender_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.09");

        $query = "ALTER TABLE `cda_config` 
                    ADD `search_patient` ENUM ('only-ins','only-ipp','ins-ipp','ins-ipp-strict-patient-fields') DEFAULT 'ins-ipp'";
        $this->addQuery($query);

        $this->makeRevision("0.10");

        $query = "ALTER TABLE `cda_meta_type_of_care`
                    ADD `target_id` INT (11) UNSIGNED,
                    ADD `target_class` VARCHAR (255),
                    ADD INDEX target (target_class, target_id);
                  ALTER TABLE `cda_config`
                    ADD INDEX sender (`sender_class`, `sender_id`);";
        $this->addQuery($query);
        $this->makeRevision('0.11');

        $query = "ALTER TABLE `cda_meta_participant` CHANGE `target_class` `target_class` VARCHAR (255)";
        $this->addQuery($query);
        $query = "ALTER TABLE `cda_meta_participant` CHANGE `type` `type` VARCHAR (255)";
        $this->addQuery($query);

        $this->makeRevision("0.12");

        $query = "ALTER TABLE `exchange_cda` 
                    CHANGE `sender_class` `sender_class` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.13");

        $query = "ALTER TABLE `cda_config` 
                    ADD `assigning_cda_to_patient` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('0.14');

        $query = "ALTER TABLE `cda_config` 
                    ADD `search_patient_strategy` VARCHAR(255) DEFAULT 'best',
                    ADD `handle_patient` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->mod_version = "0.15";
    }
}
