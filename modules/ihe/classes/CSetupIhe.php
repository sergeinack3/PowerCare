<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupIhe extends CSetup {

  function __construct() {
    parent::__construct();

    $this->mod_name = "ihe";
    $this->makeRevision("0.0");

    $this->makeRevision("0.01");

    $query = "CREATE TABLE `exchange_ihe` (
                `exchange_ihe_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `version` VARCHAR (255),
                `nom_fichier` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `date_production` DATETIME NOT NULL,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CSenderFTP','CSenderSOAP'),
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
                `object_class` ENUM ('CPatient','CSejour','COperation','CAffectation')
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `exchange_ihe` 
                ADD INDEX (`group_id`),
                ADD INDEX (`date_production`),
                ADD INDEX (`sender_id`),
                ADD INDEX (`receiver_id`),
                ADD INDEX (`date_echange`),
                ADD INDEX (`message_content_id`),
                ADD INDEX (`acquittement_content_id`),
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `receiver_ihe` (
                `receiver_ihe_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `receiver_ihe` 
              ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.02");

    $query = "CREATE TABLE `receiver_ihe_config` (
                `receiver_ihe_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED,
                `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5',
                `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5',
                `send_all_patients` ENUM ('0','1') DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.03");

    $query = "ALTER TABLE `exchange_ihe` 
                ADD `code` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.04");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `assigning_authority_namespace_id` VARCHAR (255),
                ADD `assigning_authority_universal_id` VARCHAR (255),
                ADD `assigning_authority_universal_type_id` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.05");

    $query = "ALTER TABLE `exchange_ihe` 
                ADD `identifiant_emetteur` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.06");

    $query = "ALTER TABLE `receiver_ihe_config` 
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','FR_2.1','FR_2.2','FR_2.3') DEFAULT '2.5',
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','FR_2.1','FR_2.2','FR_2.3') DEFAULT '2.5';";
    $this->addQuery($query);

    $this->makeRevision("0.07");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `extension` ENUM ('FR');";
    $this->addQuery($query);

    $this->makeRevision("0.08");

    $query = "ALTER TABLE `receiver_ihe_config` 
                DROP `extension`;";

    $this->makeRevision("0.09");

    $query = "ALTER TABLE `receiver_ihe_config` 
                DROP `extension`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `send_default_affectation` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.10");

    $query = "ALTER TABLE `exchange_ihe` 
                CHANGE `sender_class` `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderMLLP');";
    $this->addQuery($query);

    $this->makeRevision("0.11");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `encoding` ENUM ('UTF-8','ISO-8859-1') DEFAULT 'UTF-8';";
    $this->addQuery($query);

    $this->makeRevision("0.12");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `receiving_application` VARCHAR (255),
                ADD `receiving_facility` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.13");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_mode` ENUM ('normal','simple') DEFAULT 'normal';";
    $this->addQuery($query);

    $this->makeRevision("0.14");

    $query = "ALTER TABLE `exchange_ihe` 
                CHANGE `sender_class` `sender_class` VARCHAR (80);";
    $this->addQuery($query);

    $this->makeRevision("0.15");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_NDA` ENUM ('PID_18','PV1_19') DEFAULT 'PID_18';";
    $this->addQuery($query);

    $this->makeRevision("0.16");

    $query = "ALTER TABLE `receiver_ihe_config` 
              ADD `build_PV1_3_2` ENUM ('name','config_value') DEFAULT 'name',
              ADD `build_PV1_3_5` ENUM ('bed_status','null') DEFAULT 'bed_status',
              ADD `build_PV1_10` ENUM ('discipline','service') DEFAULT 'discipline';";
    $this->addQuery($query);

    $this->makeRevision("0.17");

    $query = "ALTER TABLE `receiver_ihe_config` 
              ADD `build_PV1_3_3` ENUM ('name','config_value') DEFAULT 'name';";
    $this->addQuery($query);

    $this->makeRevision("0.18");

    $query = "ALTER TABLE `receiver_ihe_config` 
              ADD `ER7_segment_terminator` ENUM ('CR','LF','CRLF') DEFAULT 'CR';";
    $this->addQuery($query);

    $this->makeRevision("0.19");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_PV1_14` ENUM ('admit_source','ZFM') DEFAULT 'admit_source';";
    $this->addQuery($query);

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_PV1_36` ENUM ('discharge_disposition','ZFM') DEFAULT 'discharge_disposition';";
    $this->addQuery($query);

     $this->makeRevision("0.20");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `send_change_medical_responsibility` ENUM ('Z80','Z99') DEFAULT 'Z80',
                ADD `send_change_nursing_ward` ENUM ('Z84','Z99') DEFAULT 'Z84';";
    $this->addQuery($query);

    $this->makeRevision("0.21");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `iti30_option_merge` ENUM ('0','1') DEFAULT '1',
                ADD `iti30_option_link_unlink` ENUM ('0','1') DEFAULT '0',
                ADD `iti31_in_outpatient_emanagement` ENUM ('0','1') DEFAULT '1',
                ADD `iti31_pending_event_management` ENUM ('0','1') DEFAULT '0',
                ADD `iti31_advanced_encounter_management` ENUM ('0','1') DEFAULT '1',
                ADD `iti31_temporary_patient_transfer_tracking` ENUM ('0','1') DEFAULT '0',
                ADD `iti31_historic_movement` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.22");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_PV1_7` ENUM ('unique','repeatable') DEFAULT 'unique';";
    $this->addQuery($query);

    $this->makeRevision("0.23");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `send_change_attending_doctor` ENUM ('A54','Z99') DEFAULT 'A54';";
    $this->addQuery($query);

    $this->makeRevision("0.24");

    $query = "ALTER TABLE `receiver_ihe_config` 
                CHANGE `send_change_medical_responsibility` `send_change_medical_responsibility` ENUM ('A02','Z80','Z99') DEFAULT 'Z80',
                CHANGE `send_change_nursing_ward` `send_change_nursing_ward` ENUM ('A02','Z84','Z99') DEFAULT 'Z84';";
    $this->addQuery($query);

    $this->makeRevision("0.25");

    $query = "ALTER TABLE `receiver_ihe_config` 
                CHANGE `build_PV1_3_2` `build_PV1_3_2` ENUM ('name','config_value','idex') DEFAULT 'name',
                CHANGE `build_PV1_3_3` `build_PV1_3_3` ENUM ('name','config_value','idex') DEFAULT 'name';";
    $this->addQuery($query);

    $this->makeRevision("0.26");

    $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `send_provisional_affectation` ENUM ('0','1') DEFAULT '0',
                ADD `send_transfer_patient` ENUM ('A02','Z99') DEFAULT 'A02',
                ADD `send_own_identifier` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $this->addDependency("hl7", "0.69");

    $query = "RENAME TABLE `receiver_ihe_config` TO `receiver_hl7v2_config`;";
    $this->addQuery($query);
    $query = "RENAME TABLE `receiver_ihe` TO `receiver_hl7v2`;";
    $this->addQuery($query);
    $query = "RENAME TABLE `exchange_ihe` TO `exchange_hl7v2`;";
    $this->addQuery($query);

    $query ="ALTER TABLE `exchange_hl7v2`
              CHANGE `exchange_ihe_id` `exchange_hl7v2_id` INT (11) UNSIGNED NOT NULL auto_increment;";
    $this->addQuery($query);

    $query ="ALTER TABLE `receiver_hl7v2`
              CHANGE `receiver_ihe_id` `receiver_hl7v2_id` INT (11) UNSIGNED NOT NULL auto_increment;";
    $this->addQuery($query);

    $query ="ALTER TABLE `receiver_hl7v2_config`
              CHANGE `receiver_ihe_config_id` `receiver_hl7v2_config_id` INT (11) UNSIGNED NOT NULL auto_increment;";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->mod_version = "0.29";
  }
}

