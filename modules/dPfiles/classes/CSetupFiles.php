<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CSetup;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CSetupFiles extends CSetup
{
    /**
     * Update group_id for CFileTracability
     *
     * @return bool
     */
    protected function updateGroupIDForCFileTraceability()
    {
        $file_traceability = new CFileTraceability();
        $traces = $file_traceability->loadList();
        if (is_array($traces)) {
            foreach ($traces as $_trace) {
                $_trace->group_id = $_trace->loadRefActor()->group_id;
                $_trace->store();
            }
        }

        return true;
    }

    /**
     * @see parent::__construct()
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPfiles";

        $this->makeRevision("0.0");

        $query = "CREATE TABLE files_mediboard (
                file_id int(11) NOT NULL auto_increment,
                file_real_filename varchar(255) NOT NULL default '',
                file_consultation bigint(20) NOT NULL default '0',
                file_operation bigint(20) NOT NULL default '0',
                file_name varchar(255) NOT NULL default '',
                file_parent int(11) default '0',
                file_description text,
                file_type varchar(100) default NULL,
                file_owner int(11) default '0',
                file_date datetime default NULL,
                file_size int(11) default '0',
                file_version float NOT NULL default '0',
                file_icon varchar(20) default 'obj/',
                PRIMARY KEY  (file_id),
                KEY idx_file_consultation (file_consultation),
                KEY idx_file_operation (file_operation),
                KEY idx_file_parent (file_parent)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "CREATE TABLE files_index_mediboard (
                file_id int(11) NOT NULL default '0',
                word varchar(50) NOT NULL default '',
                word_placement int(11) default '0',
                PRIMARY KEY  (file_id,word),
                KEY idx_fwrd (word),
                KEY idx_wcnt (word_placement)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_mediboard`
                DROP `file_parent`,
                DROP `file_description`,
                DROP `file_version`,
                DROP `file_icon`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_mediboard`
                ADD `file_object_id` INT(11) NOT NULL DEFAULT '0' AFTER `file_real_filename`,
                ADD `file_class` VARCHAR(30) NOT NULL DEFAULT 'CPatients' AFTER `file_object_id`;";
        $this->addQuery($query);
        $query = "UPDATE `files_mediboard`
                SET `file_object_id` = `file_consultation`,
                `file_class` = 'CConsultation'
                WHERE `file_consultation` != 0;";
        $this->addQuery($query);
        $query = "UPDATE `files_mediboard`
                SET `file_object_id` = `file_operation`,
                `file_class` = 'COperation'
                WHERE `file_operation` != 0;";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_mediboard`
                DROP `file_consultation`,
                DROP `file_operation`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_mediboard`
                ADD INDEX (`file_real_filename`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_mediboard`
                ADD UNIQUE (`file_real_filename`);";
        $this->addQuery($query);

        $this->makeRevision("0.1");
        $query = "ALTER TABLE `files_mediboard`
                ADD `file_category_id` INT(11) NOT NULL DEFAULT '1' AFTER `file_type`";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_mediboard`
                ADD INDEX (`file_category_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `files_category` (
                `file_category_id` INT(11) NOT NULL auto_increment,
                `nom` VARCHAR(50) NOT NULL DEFAULT '',
                `class` VARCHAR(30) DEFAULT NULL,
                PRIMARY KEY (file_category_id)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "INSERT INTO `files_category` VALUES('1', 'Divers', NULL)";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "ALTER TABLE `files_mediboard`
                CHANGE `file_category_id` `file_category_id` INT( 11 ) NULL ";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "ALTER TABLE `files_mediboard`
                CHANGE `file_category_id` `file_category_id` INT( 11 ) NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "ALTER TABLE `files_mediboard`
                DROP INDEX `file_real_filename`,
                DROP INDEX `file_real_filename_2`,
                ADD UNIQUE ( `file_real_filename` ),
                ADD INDEX ( `file_class` ) ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_category`
                ADD INDEX ( `class` ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $query = "ALTER TABLE `files_mediboard`
                CHANGE `file_id` `file_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `file_object_id` `file_object_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `file_class` `file_class` varchar(255) NOT NULL DEFAULT 'CPatients',
                CHANGE `file_type` `file_type` varchar(255) NULL,
                CHANGE `file_category_id` `file_category_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `file_owner` `file_owner` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `file_date` `file_date` datetime NOT NULL,
                CHANGE `file_size` `file_size` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `files_category`
                CHANGE `file_category_id` `file_category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `class` `class` varchar(255) NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.15");
        $query = "ALTER TABLE `files_mediboard`
                ADD INDEX ( `file_object_id` );";
        $this->addQuery($query);

        $this->makeRevision("0.16");
        $query = "ALTER TABLE `files_mediboard`
                CHANGE `file_category_id` `file_category_id` int(11) unsigned NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `files_mediboard`
                SET `file_category_id` = NULL WHERE `file_category_id` = '0';";
        $this->addQuery($query);
        $query = "UPDATE `files_mediboard`
                SET `file_owner` = 1 WHERE `file_owner` = '0';";
        $this->addQuery($query);

        $this->makeRevision("0.17");
        $query = "ALTER TABLE `files_mediboard`
                CHANGE `file_object_id` `object_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `file_class` `object_class` varchar(255) NOT NULL DEFAULT 'CPatients';";
        $this->addQuery($query);

        $this->makeRevision("0.18");
        $query = "UPDATE `files_category` 
                SET `class` = 'CSejour'
                WHERE `file_category_id` = 3;";
        $this->addQuery($query);

        $this->makeRevision("0.19");
        $query = "ALTER TABLE `files_category` 
                ADD `validation_auto` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision("0.20");
        $query = "ALTER TABLE `files_mediboard` 
                ADD `etat_envoi` ENUM ('oui','non','obsolete') NOT NULL default 'non';";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE `files_category` 
                CHANGE `validation_auto` `send_auto` ENUM( '0', '1' ) NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `files_mediboard` 
                ADD INDEX (`file_owner`),
                ADD INDEX (`file_date`);";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $query = "ALTER TABLE `files_mediboard` 
                ADD `private` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.24");
        $query = "ALTER TABLE `files_mediboard`
                ADD `rotate` INT (11) NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "ALTER TABLE `files_mediboard`
                CHANGE `rotate` `rotation` ENUM ('0','90','180','270')";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $this->addPrefQuery("directory_to_watch", "");
        $this->addPrefQuery("debug_yoplet", "0");
        $this->addPrefQuery("extensions_yoplet", "gif jpeg jpg pdf png");

        $this->makeRevision("0.27");
        $this->delPrefQuery("extensions_yoplet");

        $this->makeRevision("0.28");
        $query = "ALTER TABLE `files_mediboard`
      CHANGE `file_owner` `author_id` INT(11);";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `files_mediboard`
      ADD `annule` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `files_mediboard`
      CHANGE `rotation` `rotation` INT (11) DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `files_mediboard`
      SET `rotation` = '0' WHERE `rotation` = '1';";
        $this->addQuery($query);

        $query = "UPDATE `files_mediboard`
      SET `rotation` = '90' WHERE `rotation` = '2';";
        $this->addQuery($query);

        $query = "UPDATE `files_mediboard`
      SET `rotation` = '180' WHERE `rotation` = '3';";
        $this->addQuery($query);

        $query = "UPDATE `files_mediboard`
      SET `rotation` = '270' WHERE `rotation` = '4';";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `files_mediboard`
      ADD `language` ENUM ('en-EN','es-ES','fr-CH','fr-FR') DEFAULT 'fr-FR' AFTER `file_type`";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `files_mediboard`
      ADD `type_doc` VARCHAR(128);";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $this->addPrefQuery("mozaic_disposition", "2x2");

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `files_mediboard`
                ADD `type_doc_sisra` VARCHAR(10);";
        $this->addQuery($query);

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `files_category`
                ADD `eligible_file_view` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $this->addPrefQuery("show_file_view", "0");

        $this->makeRevision("0.37");
        $query = "CREATE TABLE `files_user_view` (
                `view_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `file_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `read_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `files_user_view`
                ADD INDEX (`user_id`),
                ADD INDEX (`file_id`),
                ADD INDEX (`read_datetime`);";
        $this->addQuery($query);

        $this->makeRevision("0.38");
        $query = "ALTER TABLE `files_mediboard`
      CHANGE `file_size` `doc_size` int(11) unsigned DEFAULT '0',
      ADD `compression` VARCHAR (255),
      ADD INDEX(`compression`),
      ADD INDEX(`compression`, `object_class`);";
        $this->addQuery($query);

        $this->makeRevision("0.39");
        $this->addPrefQuery("upload_mbhost", "0");

        $this->makeRevision("0.40");
        $query = "ALTER TABLE `files_category`
      ADD `importance` ENUM ('normal','high') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("0.41");
        $query = "CREATE TABLE `destinataire_item` (
      `destinataire_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `dest_class` ENUM ('CCorrespondantPatient','CMedecin','CMediusers','CPatient') NOT NULL,
      `dest_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      `tag` ENUM ('assurance','assure','autre','correspondant','employeur','ìnconnu','other_prat','patient','praticien','prevenir','traitant'),
      `object_class` ENUM ('CCompteRendu','CFile') NOT NULL,
      `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `destinataire_item`
      ADD INDEX (`dest_id`),
      ADD INDEX (`object_id`),
      ADD INDEX (`object_class`);";
        $this->addQuery($query);

        $this->makeRevision("0.42");
        $query = "ALTER TABLE `destinataire_item`
      CHANGE `dest_class` `dest_class` ENUM ('CCorrespondantPatient','CMedecin', 'CPatient') NOT NULL,
      CHANGE `object_class` `docitem_class` ENUM ('CCompteRendu','CFile') NOT NULL,
      CHANGE `object_id` `docitem_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "CREATE TABLE `link_dest_dispatch` (
      `link_dest_dispatch_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `destinataire_item_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      `dispatch_class` ENUM ('CPliPostal','CUserMail','CMSSanteMail'),
      `dispatch_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `link_dest_dispatch`
      ADD INDEX (`destinataire_item_id`),
      ADD INDEX (`dispatch_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.43");

        $query = "ALTER TABLE `files_category`
      ADD `group_id` INT (11) UNSIGNED AFTER `class`;";
        $this->addQuery($query);
        $this->makeRevision("0.44");

        $query = "ALTER TABLE `files_category`
      ADD `nom_court` VARCHAR (25);";
        $this->addQuery($query);
        $this->makeRevision("0.45");

        $query = "DROP TABLE `files_index_mediboard`;";
        $this->addQuery($query);
        $this->makeRevision("0.46");

        $query = "CREATE TABLE `files_cat_default` (
      `files_cat_default_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `file_category_id` INT (11) UNSIGNED,
      `object_class` VARCHAR(30) DEFAULT NULL,
      `owner_class` ENUM ('CMediusers','CFunctions'),
      `owner_id` INT (11) UNSIGNED
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.47");

        $query = "ALTER TABLE `files_category`
      ADD `medicale` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.48");

        $query = "ALTER TABLE `files_category`
      ADD `color` VARCHAR(6) NOT NULL DEFAULT 'ffffff';";
        $this->addQuery($query);

        $this->makeRevision("0.49");
        $query = "ALTER TABLE `files_category`
      CHANGE `color` `color` VARCHAR(6);";
        $this->addQuery($query);
        $this->makeRevision("0.50");

        $query = "ALTER TABLE `files_mediboard` 
      ADD `remis_patient` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.51");
        $this->addPrefQuery("choose_sort_file_date", "ASC");

        $this->makeRevision("0.52");

        $query = "CREATE TABLE `context_doc` (
      `context_doc_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `context_id` INT (11) UNSIGNED NOT NULL,
      `context_class` VARCHAR (255) NOT NULL,
      `type` ENUM ('sejour','operation')
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `context_doc` 
      ADD INDEX context (context_class, context_id);";
        $this->addQuery($query);

        $this->makeRevision("0.53");

        $query = "CREATE TABLE `file_tracability` (
                `file_tracability_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `created_datetime` DATETIME NOT NULL,
                `modified_datetime` DATETIME NOT NULL,
                `sent_datetime` DATETIME NOT NULL,
                `received_datetime` DATETIME NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `file_id` INT (11) UNSIGNED NOT NULL,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` VARCHAR (255),
                `source_name` VARCHAR (255),
                `status` ENUM ('auto','pending','sas_manually','sas_auto','archived') NOT NULL,
                `treated_datetime` DATETIME,
                `comment` TEXT,
                `motif_invalidation` TEXT,
                `attempt_treated` ENUM ('0','1') DEFAULT '0',
                `IPP` VARCHAR (80),
                `NDA` VARCHAR (80),
                `patient_name` VARCHAR (255) NOT NULL,
                `patient_firstname` VARCHAR (255) NOT NULL,
                `patient_date_of_birth` CHAR (10) NOT NULL,
                `datetime_object` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
              
              ALTER TABLE `file_tracability` 
                ADD INDEX (`created_datetime`),
                ADD INDEX (`modified_datetime`),
                ADD INDEX (`sent_datetime`),
                ADD INDEX (`received_datetime`),
                ADD INDEX (`user_id`),
                ADD INDEX (`file_id`),
                ADD INDEX sender (sender_class, sender_id),
                ADD INDEX (`treated_datetime`),
                ADD INDEX (`datetime_object`);";
        $this->addQuery($query);

        $this->makeRevision("0.54");

        $query = "ALTER TABLE `file_tracability` 
                ADD `RPPS` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.55");

        $query = "ALTER TABLE `file_tracability` 
                ADD `praticien_id` INT (11) UNSIGNED,
                ADD `patient_birthname` VARCHAR (255) AFTER `patient_name`,
                DROP `RPPS`";
        $this->addQuery($query);

        $this->makeRevision("0.56");

        $query = "CREATE TABLE `submissionlot` (
                `submissionlot_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `title` VARCHAR (255),
                `comments` VARCHAR (255),
                `date` DATETIME,
                `type` VARCHAR(15) NOT NULL DEFAULT 'XDS'
              )/*! ENGINE=MyISAM */;
            ALTER TABLE `submissionlot` 
                ADD INDEX (`date`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `submissionlot_document` (
                `submissionlot_document_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `submissionlot_id` INT (11) UNSIGNED,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `object_class` ENUM ('CCompteRendu','CFile') NOT NULL
              )/*! ENGINE=MyISAM */;
             ALTER TABLE `submissionlot_document` 
                ADD INDEX (`submissionlot_id`),
                ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $this->makeRevision("0.57");

        $this->addDefaultConfig("dPfiles General upload_max_filesize", "dPfiles upload_max_filesize");
        $this->addDefaultConfig(
            "dPfiles General extensions_yoplet",
            "dPfiles extensions_yoplet",
            "gif jpeg jpg pdf png"
        );
        $this->addDefaultConfig("dPfiles General yoplet_upload_url", "dPfiles yoplet_upload_url");
        $this->addDefaultConfig("dPfiles CFile merge_to_pdf", "dPfiles CFile merge_to_pdf");
        $this->addDefaultConfig("dPfiles CFilesCategory show_empty", "dPfiles CFilesCategory show_empty");
        $this->addDefaultConfig("dPfiles CDocumentSender system_sender", "dPfiles system_sender");
        $this->addDefaultConfig("dPfiles CDocumentSender auto_max_load", "dPfiles CDocumentSender auto_max_load");
        $this->addDefaultConfig("dPfiles CDocumentSender auto_max_send", "dPfiles CDocumentSender auto_max_send");

        $this->makeRevision("0.58");

        $query = "RENAME TABLE `file_tracability` TO `file_traceability`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `file_traceability` 
                CHANGE `file_tracability_id` `file_traceability_id` INT(11)  UNSIGNED  NOT NULL  AUTO_INCREMENT;";
        $this->addQuery($query);

        if ($this->tableExists('files_mediboard')) {
            $this->makeRevision("0.59");
            $query = "UPDATE `files_mediboard` SET `object_class` = 'CFileTraceability' WHERE `object_class` = 'CFileTracability'";
            $this->addQuery($query);
        } else {
            $this->makeEmptyRevision("0.59");
        }

        $this->makeRevision("0.60");

        $query = "CREATE TABLE `document_manifest` (
                `document_manifest_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `version` VARCHAR (255),
                `repositoryUniqueID` VARCHAR (255) NOT NULL,
                `repositoryUniqueIDExternal` VARCHAR (255),
                `created_datetime` DATETIME NOT NULL,
                `last_update` DATETIME NOT NULL,
                `treated_datetime` DATETIME NOT NULL,
                `type` ENUM ('XDS','DMP','FHIR') DEFAULT 'XDS',
                `status` VARCHAR (255),
                `actor_id` INT (11) UNSIGNED NOT NULL,
                `actor_class` VARCHAR (80) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `patient_reference` TEXT,
                `author_given` VARCHAR (255),
                `author_family` VARCHAR (255),
                `initiator` ENUM ('client','server') DEFAULT 'client'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `document_manifest` 
                ADD INDEX (`created_datetime`),
                ADD INDEX (`last_update`),
                ADD INDEX (`treated_datetime`),
                ADD INDEX actor (actor_class, actor_id),
                ADD INDEX (`patient_id`);
                
              CREATE TABLE `document_reference` (
                `document_reference_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `hash` VARCHAR (255),
                `size` INT (11),
                `version` VARCHAR (255),
                `status` VARCHAR (255),
                `security_label` VARCHAR (255),
                `uniqueID` VARCHAR (255) NOT NULL,
                `created_datetime` DATETIME NOT NULL,
                `last_update` DATETIME NOT NULL,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `object_class` ENUM ('CFile','CCompteRendu') NOT NULL,
                `document_manifest_id` INT (11) UNSIGNED NOT NULL,
                `parent_id` INT (11) UNSIGNED,
                `actor_id` INT (11) UNSIGNED NOT NULL,
                `actor_class` VARCHAR (80) NOT NULL,
                `initiator` ENUM ('client','server') DEFAULT 'client'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `document_reference` 
                ADD INDEX (`created_datetime`),
                ADD INDEX (`last_update`),
                ADD INDEX object (object_class, object_id),
                ADD INDEX (`document_manifest_id`),
                ADD INDEX (`parent_id`),
                ADD INDEX actor (actor_class, actor_id);";
        $this->addQuery($query);

        $this->makeRevision("0.61");

        $query = "ALTER TABLE `files_mediboard`
                ADD `date_rotation` DATETIME AFTER `rotation`;";
        $this->addQuery($query);

        $this->makeRevision("0.62");

        $query = "ALTER TABLE `files_category`
                ADD `synchro_zepra` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.63");

        $query = "ALTER TABLE `files_mediboard`
                CHANGE `type_doc` `type_doc_dmp` VARCHAR(128);";
        $this->addQuery($query);

        $query = "ALTER TABLE `files_category`
                DROP `synchro_zepra`;";
        $this->addQuery($query);

        $this->makeRevision('0.64');
        $query = "ALTER TABLE `files_category` 
                ADD `type_doc_dmp` VARCHAR(128),
                ADD `type_doc_sisra` VARCHAR(128);";
        $this->addQuery($query);

        $this->makeRevision('0.65');

        $query = "CREATE TABLE `files_category_to_receiver` (
                `files_category_to_receiver_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `receiver_class` VARCHAR (80),
                `receiver_id` INT (11) UNSIGNED,
                `files_category_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `files_category_to_receiver` 
                ADD INDEX receiver (receiver_class, receiver_id),
                ADD INDEX (`files_category_id`);";
        $this->addQuery($query);

        $this->makeRevision('0.66');

        $query = "CREATE TABLE `document_manifest_to_receiver` (
                `document_manifest_to_receiver_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `receiver_class` VARCHAR (80),
                `receiver_id` INT (11) UNSIGNED,
                `document_manifest_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `document_manifest_to_receiver` 
                ADD INDEX receiver (receiver_class, receiver_id),
                ADD INDEX (`document_manifest_id`);";
        $this->addQuery($query);

        $this->makeRevision('0.67');

        $query = "ALTER TABLE `files_category_to_receiver`
                ADD `active` ENUM ('0','1') DEFAULT '1',
                ADD `description` VARCHAR(256);";
        $this->addQuery($query);

        $this->makeRevision('0.68');

        $query = "ALTER TABLE `files_category_to_receiver` 
                CHANGE `receiver_id` `receiver_id` INT (11) UNSIGNED NOT NULL,
                CHANGE `active` `active` ENUM ('0','1') NOT NULL DEFAULT '1',
                CHANGE `description` `description` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('0.69');

        $query = "ALTER TABLE `document_manifest` 
                CHANGE `type` `type` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision('0.70');

        $query = "ALTER TABLE `document_reference` 
                ADD `metadata` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('0.71');

        $query = "ALTER TABLE `file_traceability` 
                CHANGE `sender_id` `actor_id` INT (11) UNSIGNED NOT NULL,
                CHANGE `sender_class` `actor_class` VARCHAR (255),
                CHANGE `status` status ENUM ('auto','pending','sas_manually','sas_auto','archived', 'rejected') NOT NULL,
                ADD `metadata` TEXT,
                ADD `initiator` ENUM ('client','server') NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `file_traceability` 
                SET `initiator` = 'server';";
        $this->addQuery($query);

        $this->makeRevision('0.72');

        $query = "ALTER TABLE `file_traceability` 
                ADD `object_class` ENUM ('CFile','CCompteRendu') DEFAULT 'CFile',
                CHANGE `file_id` `object_id` INT (11) UNSIGNED,
                ADD `version` INT (11) DEFAULT '1',
                CHANGE `datetime_object` `datetime_object` DATETIME,
                CHANGE `received_datetime` `received_datetime` DATETIME,
                CHANGE `sent_datetime` `sent_datetime` DATETIME,
                CHANGE `patient_name` `patient_name` VARCHAR (255),
                CHANGE `patient_firstname` `patient_firstname` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `file_traceability` 
                ADD INDEX object (object_class, object_id),
                ADD INDEX actor (actor_class, actor_id),
                ADD INDEX (`praticien_id`);";
        $this->addQuery($query);

        $this->makeRevision('0.73');

        $query = "ALTER TABLE `file_traceability` 
                ADD `attempt_sent` INT (11) DEFAULT 0;";
        $this->addQuery($query);

        $this->makeRevision('0.74');

        $query = "ALTER TABLE `file_traceability` 
                ADD `exchange_class` VARCHAR (255),
                ADD `exchange_id` INT UNSIGNED,
                CHANGE `patient_date_of_birth` `patient_date_of_birth` CHAR (10);";
        $this->addQuery($query);

        $this->makeRevision('0.75');

        $query = "ALTER TABLE `file_traceability` 
                ADD `msg_error` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('0.76');

        $query = "ALTER TABLE `file_traceability` 
                ADD `type_request` ENUM ('add','replace','modify','cancel','delete') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('0.77');

        $query = "ALTER TABLE `file_traceability` 
                CHANGE `type_request` `type_request` ENUM ('add','replace','modify','cancel','delete') NOT NULL DEFAULT 'add';";
        $this->addQuery($query);

        $this->makeRevision('0.78');

        $query = "ALTER TABLE `file_traceability` 
                ADD `NIR` VARCHAR (15)";
        $this->addQuery($query);

        $this->makeRevision('0.79');

        $query = "ALTER TABLE `file_traceability` 
                CHANGE `object_id` `object_id` INT (11) UNSIGNED,
                CHANGE `object_class` `object_class` ENUM ('CFile','CCompteRendu'),
                CHANGE `initiator` `initiator` ENUM ('client','server') NOT NULL DEFAULT 'server',
                ADD INDEX exchange (exchange_class, exchange_id);";
        $this->addQuery($query);

        $this->makeEmptyRevision("0.80");

        /*$query = "ALTER TABLE `destinataire_item`
                    ADD INDEX dest (dest_class, dest_id),
                    ADD INDEX docitem (docitem_class, docitem_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `files_mediboard`
                    ADD INDEX (`date_rotation`),
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `files_cat_default`
                    ADD INDEX (`file_category_id`),
                    ADD INDEX owner (owner_class, owner_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `files_category`
                    ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `link_dest_dispatch`
                    ADD INDEX dispatch (dispatch_class, dispatch_id);";
        $this->addQuery($query);*/

        $this->makeRevision("0.81");

        $query = "ALTER TABLE `files_mediboard`
                ADD `send` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.82");
        $this->setModuleCategory("systeme", "administration");

        $this->makeRevision('0.83');

        $query = "ALTER TABLE `file_traceability` 
                    ADD `group_id` INT (11) UNSIGNED NOT NULL AFTER `actor_class`,
                    ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->addMethod("updateGroupIDForCFileTraceability");

        $this->makeRevision('0.84');

        $query = "ALTER TABLE `file_traceability` 
                    ADD `report` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('0.85');

        $query = "ALTER TABLE `file_traceability` 
                    ADD `cancel` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.86');

        $query = "ALTER TABLE `files_mediboard` 
                ADD `nature_file_id` INT (11) UNSIGNED,
                ADD INDEX (`nature_file_id`);";

        $this->addQuery($query);

        $this->makeRevision('0.87');

        $query = "ALTER TABLE `files_category` 
                ADD `is_emergency_tab` ENUM ('0','1') DEFAULT '0',
                ADD INDEX (is_emergency_tab)";

        $this->addQuery($query);

        $this->makeRevision('0.88');

        $this->addQuery(
            'ALTER TABLE `destinataire_item` 
             ADD `medecin_exercice_place_id` INT (11) UNSIGNED AFTER `dest_id`,
             ADD INDEX (`medecin_exercice_place_id`);'
        );

        $this->makeRevision('0.89');

        $query = "ALTER TABLE `file_traceability` 
                    ADD `dissociated` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.90');

        $this->addQuery(
            "ALTER TABLE `files_mediboard`
             ADD `masquage` ENUM ('aucun','praticien','patient','representants_legaux') DEFAULT 'aucun'
             AFTER `object_class`"
        );

        if (CModule::getActive('dmp')) {
            $this->addQuery(
                'UPDATE `files_mediboard`
                 LEFT JOIN `cdmp_document` ON `cdmp_document`.`object_id` = `files_mediboard`.`file_id`
                      AND `cdmp_document`.`object_class` = "CFile"
                 SET `masquage` =
                     CASE `cdmp_document`.`visibilite`
                         WHEN "0" THEN "praticien"
                         WHEN "1" THEN "patient"
                         WHEN "2" THEN "representants_legaux"
                         ELSE "aucun"
                     END
                 WHERE `cdmp_document`.`cdmp_document_id` IS NOT NULL'
            );
        }
                $this->makeRevision('0.91');

        $query = "CREATE TABLE `files_read` (
            file_read_id INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
            `object_class` VARCHAR(250) NOT NULL,
            `object_id` INT (11) UNSIGNED NOT NULL,
            `datetime` DATETIME,
            `user_id` INT (11) UNSIGNED NOT NULL
        )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `files_read`
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision('0.92');
        $query = "ALTER TABLE `files_category_to_receiver` 
                CHANGE `receiver_id` `receiver_id` INT (11) UNSIGNED,
                ADD `type` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('0.93');

        $this->addQuery(
            "ALTER TABLE `files_mediboard`
             ADD `masquage_patient` ENUM ('0','1') DEFAULT '0' AFTER `masquage`,
             ADD `masquage_praticien` ENUM ('0','1') DEFAULT '0' AFTER `masquage_patient`,
             ADD `masquage_representants_legaux` ENUM ('0','1') DEFAULT '0' AFTER `masquage_praticien`;"
        );

        $this->addQuery(
            "UPDATE `files_mediboard`
             SET `masquage_patient` = IF(`masquage` = 'patient', '1', '0'),
                 `masquage_praticien` = IF(`masquage` = 'praticien', '1', '0'),
                 `masquage_representants_legaux` = IF(`masquage` = 'representants_legaux', '1', '0');"
        );

        $this->addQuery('ALTER TABLE `files_mediboard` DROP `masquage`;');

        $this->makeRevision('0.94');

        $this->addQuery("ALTER TABLE `file_traceability` ADD  `patient_sexe` ENUM('m','f','i') NOT NULL DEFAULT 'm'");

        $this->makeRevision('0.95');

        $this->addQuery("ALTER TABLE `file_traceability` ADD  `oid_nir` VARCHAR(80)");

        $this->makeRevision('0.96');

        $this->moveConfiguration('dPfiles General extensions_yoplet', 'dPfiles General extensions');

        $this->mod_version = "0.97";
    }
}
