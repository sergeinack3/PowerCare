<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CSetup;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * @codeCoverageIgnore
 */
class CSetupEai extends CSetup
{
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "eai";
        $this->makeRevision("0.0");

        $this->makeRevision("0.01");

        $sql = "CREATE TABLE `message_supported` (
              `message_supported_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `object_id` INT (11) UNSIGNED NOT NULL,
              `object_class` VARCHAR (80) NOT NULL,
              `message` VARCHAR (255) NOT NULL,
              `active` ENUM ('0','1') DEFAULT '0'
            ) /*! ENGINE=MyISAM */;";
        $this->addQuery($sql);

        $sql = "ALTER TABLE `message_supported` 
              ADD INDEX (`object_id`),
              ADD INDEX (`object_class`);";
        $this->addQuery($sql);

        $this->makeRevision("0.02");

        $sql = "CREATE TABLE `echange_any` (
              `echange_any_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `group_id` INT (11) UNSIGNED NOT NULL,
              `date_production` DATETIME NOT NULL,
              `emetteur_id` INT (11) NOT NULL,
              `destinataire_id` INT (11) NOT NULL,
              `type` CHAR (100),
              `sous_type` CHAR (100),
              `date_echange` DATETIME,
              `message_content_id` INT (11) UNSIGNED,
              `acquittement_content_id` INT (11) UNSIGNED,
              `statut_acquittement` CHAR (20),
              `message_valide` ENUM ('0','1') DEFAULT '0',
              `acquittement_valide` ENUM ('0','1') DEFAULT '0',
              `id_permanent` CHAR (50),
              `object_id` INT (11) UNSIGNED,
              `object_class` CHAR (80)
            ) /*! ENGINE=MyISAM */;";
        $this->addQuery($sql);

        $sql = "ALTER TABLE `echange_any` 
              ADD INDEX (`group_id`),
              ADD INDEX (`date_production`),
              ADD INDEX (`date_echange`),
              ADD INDEX (`message_content_id`),
              ADD INDEX (`acquittement_content_id`),
              ADD INDEX (`object_id`),
              ADD INDEX (`object_class`);";
        $this->addQuery($sql);

        $this->makeRevision("0.03");

        $query = "ALTER TABLE `echange_any` 
                CHANGE `destinataire_id` `receiver_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.04");

        $query = "ALTER TABLE `echange_any` 
                CHANGE `emetteur_id` `sender_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.05");

        $query = "ALTER TABLE `echange_any` 
                ADD `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderMLLP');";
        $this->addQuery($query);

        $query = "ALTER TABLE `echange_any` 
                ADD INDEX (`sender_id`),
                ADD INDEX (`receiver_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.06");

        $query = "ALTER TABLE `echange_any` 
                CHANGE `sender_class` `sender_class` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("0.07");

        $query = "ALTER TABLE `echange_any` 
                ADD `reprocess` TINYINT (4) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.08");

        $query = "CREATE TABLE `domain` (
                `domain_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `incrementer_id` INT (11) UNSIGNED NOT NULL,
                `actor_id` INT (11) UNSIGNED,
                `tag` VARCHAR (255) NOT NULL,
                `actor_class` VARCHAR (80) NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `domain` 
                ADD INDEX (`incrementer_id`),
                ADD INDEX (`actor_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `group_domain` (
                `group_domain_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `domain_id` INT (11) UNSIGNED NOT NULL,
                `object_class` ENUM ('CPatient','CSejour') NOT NULL,
                `master` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `group_domain` 
                ADD INDEX (`group_id`),
                ADD INDEX (`domain_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.09");

        $query = "CREATE TABLE `object_to_interop_sender` (
                `object_to_interop_sender_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sender_class` VARCHAR (255) NOT NULL,
                `sender_id` INT (11) UNSIGNED NOT NULL,
                `object_class` VARCHAR (255) NOT NULL,
                `object_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `object_to_interop_sender`
                ADD INDEX (`sender_id`),
                ADD INDEX (`object_id`);";

        $this->addQuery($query);

        $this->makeRevision("0.10");

        $query = "ALTER TABLE `domain` 
                CHANGE `incrementer_id` `incrementer_id` INT (11) UNSIGNED,
                CHANGE `actor_class` `actor_class` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("0.11");

        $this->addMethod("createDomain");

        $this->makeRevision("0.12");

        $query = "ALTER TABLE `domain` 
                ADD `libelle` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.13");

        $query = "ALTER TABLE `domain` 
                ADD `derived_from_idex` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.14");

        $query = "ALTER TABLE `domain`
                ADD `OID` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.15");

        $query = "CREATE TABLE `http_tunnel` (
                `http_tunnel_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `address` VARCHAR (255) NOT NULL,
                `status` ENUM ('0','1') NOT NULL DEFAULT '1',
                `start_date` DATETIME,
                `ca_file` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `http_tunnel`
                ADD INDEX (`start_date`);";
        $this->addQuery($query);

        $this->makeRevision("0.16");

        $query = "CREATE TABLE `eai_router` (
                `eai_router_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` CHAR (80),
                `receiver_id` INT (11) UNSIGNED,
                `receiver_class` CHAR (80),
                `active` ENUM ('0','1') DEFAULT '1'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_router`
                ADD INDEX (`sender_id`),
                ADD INDEX (`sender_class`),
                ADD INDEX (`receiver_id`),
                ADD INDEX (`receiver_class`);";
        $this->addQuery($query);

        $this->makeRevision("0.17");

        $this->addDependency("dPsante400", "0.26");

        $this->addMethod("updateRangeDomain");

        $this->makeRevision("0.18");

        $query = "ALTER TABLE `groups_config`
                DROP `ipp_range_min`,
                DROP `ipp_range_max`,
                DROP `nda_range_min`,
                DROP `nda_range_max`;";
        $this->addQuery($query);

        $this->makeRevision("0.19");

        $query = "ALTER TABLE `incrementer`
                DROP `group_id`;";
        $this->addQuery($query);

        $this->makeRevision("0.20");

        $query = "ALTER TABLE `domain`
                ADD `namespace_id` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.21");

        $query = "ALTER TABLE `message_supported`
                ADD `profil` VARCHAR (255),
                ADD `transaction` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.22");

        $query = "ALTER TABLE `eai_router`
                ADD `description` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.23");

        $query = "CREATE TABLE `eai_transformation_ruleset` (
                `eai_transformation_ruleset_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `eai_transformation_rule` (
                `eai_transformation_rule_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `profil` VARCHAR (255),
                `message` VARCHAR (255),
                `transaction` VARCHAR (255),
                `version` VARCHAR (255),
                `extension` VARCHAR (255),
                `component_from` VARCHAR (255),
                `component_to` VARCHAR (255),
                `action` VARCHAR (255),
                `value` VARCHAR (255),
                `active` ENUM ('0','1') DEFAULT '0',
                `rank` INT (11) UNSIGNED,
                `eai_transformation_ruleset_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_transformation_rule`
                ADD INDEX (`eai_transformation_ruleset_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `eai_transformation` (
                `eai_transformation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `actor_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `actor_class` VARCHAR (80) NOT NULL,
                `profil` VARCHAR (255),
                `message` VARCHAR (255),
                `transaction` VARCHAR (255),
                `version` VARCHAR (255),
                `extension` VARCHAR (255),
                `active` ENUM ('0','1') DEFAULT '0',
                `rank` INT (11) UNSIGNED,
                `eai_transformation_rule_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_transformation`
                ADD INDEX (`actor_id`),
                ADD INDEX (`actor_class`),
                ADD INDEX (`eai_transformation_rule_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.24");

        $query = "ALTER TABLE `eai_transformation_ruleset`
                ADD `description` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.25");

        $query = "ALTER TABLE `eai_transformation_rule`
                CHANGE `action` `action_type` ENUM ('add','modify','move','delete');";
        $this->addQuery($query);

        $this->makeRevision("0.26");

        $query = "ALTER TABLE `eai_transformation_rule`
                ADD `domain` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_transformation`
                ADD `domain` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.27");

        $query = "ALTER TABLE `eai_transformation_rule`
                ADD `standard` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_transformation`
                ADD `standard` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.28");

        $query = "ALTER TABLE `domain`
                ADD `active` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `echange_any`
                ADD `master_idex_missing` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `echange_any`
                ADD INDEX (`master_idex_missing`);";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "CREATE TABLE `syslog_receiver` (
                `syslog_receiver_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `OID`                VARCHAR(255),
                `synchronous`        ENUM('0','1') NOT NULL DEFAULT '1',
                `monitor_sources`    ENUM('0','1') NOT NULL DEFAULT '1',
                `nom`                VARCHAR(255) NOT NULL,
                `libelle`            VARCHAR(255),
                `group_id`           INT(11) UNSIGNED NOT NULL DEFAULT '0',
                `actif`              ENUM('0','1') NOT NULL DEFAULT '0',
                INDEX(`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "CREATE TABLE `syslog_exchange` (
                `syslog_exchange_id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `identifiant_emetteur`    VARCHAR(255),
                `initiateur_id`           INT(11) UNSIGNED,
                `group_id`                INT(11) UNSIGNED NOT NULL DEFAULT '0',
                `date_production`         DATETIME NOT NULL,
                `sender_id`               INT(11) UNSIGNED,
                `sender_class`            VARCHAR(255),
                `receiver_id`             INT(11) UNSIGNED,
                `type`                    VARCHAR(255),
                `sous_type`               VARCHAR(255),
                `date_echange`            DATETIME,
                `message_content_id`      INT(11) UNSIGNED,
                `acquittement_content_id` INT(11) UNSIGNED,
                `statut_acquittement`     VARCHAR(255),
                `message_valide`          ENUM('0','1') DEFAULT '0',
                `acquittement_valide`     ENUM('0','1') DEFAULT '0',
                `id_permanent`            VARCHAR(255),
                `object_id`               INT(11) UNSIGNED,
                `object_class`            VARCHAR(255),
                `reprocess`               TINYINT(4) UNSIGNED DEFAULT '0',
                `master_idex_missing`     ENUM('0','1') DEFAULT '0',
                INDEX(`initiateur_id`),
                INDEX(`group_id`),
                INDEX(`date_production`),
                INDEX(`sender_id`),
                INDEX(`receiver_id`),
                INDEX(`date_echange`),
                INDEX(`message_content_id`),
                INDEX(`acquittement_content_id`),
                INDEX(`object_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `syslog_receiver`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "ALTER TABLE `syslog_receiver`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `syslog_exchange`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `echange_any`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
        $this->addQuery($query);

        $this->makeRevision("0.35");

        $query = "ALTER TABLE `echange_any`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `echange_any`
                SET `emptied` = '1'
                WHERE `message_content_id` IS NULL
                AND `acquittement_content_id` IS NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `echange_any`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
        $this->addQuery($query);

        $this->makeRevision("0.36");

        $query = "ALTER TABLE `syslog_exchange`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `syslog_exchange`
                SET `emptied` = '1'
                WHERE `message_content_id` IS NULL
                AND `acquittement_content_id` IS NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `syslog_exchange`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
        $this->addQuery($query);

        $this->makeRevision("0.37");

        $query = "ALTER TABLE `echange_any`
                ADD `response_datetime` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `syslog_exchange`
                ADD `response_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("0.38");

        $query = "ALTER TABLE `echange_any`
                CHANGE `date_echange` `send_datetime` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `syslog_exchange`
                CHANGE `date_echange` `send_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("0.39");

        $query = "ALTER TABLE `echange_any`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `syslog_exchange`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
        $this->addQuery($query);

        $this->makeRevision('0.40');

        $query = "ALTER TABLE `syslog_receiver`
                ADD `type` VARCHAR (255),
                ADD `use_specific_handler` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "SELECT * FROM `authorspecialty_20121112` WHERE `libelle` = 'Pharmacien adjoint - OM';";

        $this->addDatasource("ASIP", $query);

        $this->makeRevision('0.41');

        $query = "ALTER TABLE `eai_transformation_ruleset`
              RENAME TO `transformation_ruleset`";
        $this->addQuery($query);

        $query = "ALTER TABLE `transformation_ruleset` 
                CHANGE `eai_transformation_ruleset_id` `transformation_ruleset_id` INT (11) UNSIGNED AUTO_INCREMENT";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_transformation_rule` 
                CHANGE `eai_transformation_ruleset_id` `transformation_ruleset_id` INT (11) UNSIGNED";
        $this->addQuery($query);

        $query = "ALTER TABLE `eai_transformation_rule`
                ADD INDEX (`transformation_ruleset_id`),
                DROP INDEX `eai_transformation_ruleset_id`;";
        $this->addQuery($query);


        $this->makeRevision('0.42');

        $query = "CREATE TABLE `transformation_rule_sequence` (
                `transformation_rule_sequence_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `description` VARCHAR (255),
                `standard` VARCHAR (255),
                `domain` VARCHAR (255),
                `profil` VARCHAR (255),
                `message_type` VARCHAR (255),
                `message_example` TEXT NOT NULL,
                `transaction` VARCHAR (255),
                `version` VARCHAR (255),
                `extension` VARCHAR (255),
                `source` VARCHAR (255),
                `transformation_ruleset_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;
ALTER TABLE `transformation_rule_sequence` 
                ADD INDEX (`transformation_ruleset_id`);";
        $this->addQuery($query);


        $this->makeRevision('0.43');

        $query = "DROP TABLE `eai_transformation_rule`";
        $this->addQuery($query);

        $query = "CREATE TABLE `transformation_rule` (
                `transformation_rule_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `extension` VARCHAR (255),
                `xpath_source` VARCHAR (255) NOT NULL,
                `xpath_target` VARCHAR (255),
                `action_type` VARCHAR (255),
                `value` VARCHAR (255),
                `active` ENUM ('0','1') DEFAULT '0',
                `rank` INT (11) UNSIGNED,
                `transformation_rule_sequence_id` INT (11) UNSIGNED,
                `params` TEXT
              )/*! ENGINE=MyISAM */;
ALTER TABLE `transformation_rule` 
                ADD INDEX (`transformation_rule_sequence_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        $this->setModuleCategory("interoperabilite", "echange");

        $this->makeRevision('0.45');

        $query = "CREATE TABLE `link_actor_sequence` (
                `link_actor_sequence_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `actor_id` INT (11) UNSIGNED NOT NULL,
                `actor_class` VARCHAR (80) NOT NULL,
                `sequence_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `link_actor_sequence` 
                ADD INDEX actor (actor_class, actor_id),
                ADD INDEX (`sequence_id`);";
        $this->addQuery($query);
        $this->makeRevision('0.46');

        $query = 'ALTER TABLE `message_supported` 
                ADD `version` VARCHAR (255);';
        $this->addQuery($query);

        $this->makeRevision('0.47');

        $this->addDependency("interopResources", "0.02");

        $this->mod_version = "0.48";
    }

    /**
     * Create domains
     *
     * @return bool
     */
    protected function createDomain(): bool
    {
        $ds = $this->ds;

        $groups = $ds->loadList("SELECT * FROM groups_mediboard");

        $tab = [
            "CPatient",
            "CSejour",
        ];

        foreach ($groups as $_group) {
            $group_id = $_group["group_id"];

            $group_configs = $ds->loadHash("SELECT * FROM groups_config WHERE object_id = '$group_id'");

            foreach ($tab as $object_class) {
                if ($object_class == "CPatient") {
                    $tag_group = CPatient::getTagIPP($group_id);
                    if (!$group_configs || !array_key_exists("ipp_range_min", $group_configs)) {
                        continue;
                    }
                    $range_min = $group_configs["ipp_range_min"];
                    $range_max = $group_configs["ipp_range_max"];
                } else {
                    $tag_group = CSejour::getTagNDA($group_id);
                    if (!$group_configs || !array_key_exists("nda_range_min", $group_configs)) {
                        continue;
                    }
                    $range_min = $group_configs["nda_range_min"];
                    $range_max = $group_configs["nda_range_max"];
                }

                if (!$tag_group) {
                    continue;
                }

                // Insert domain
                $query = "INSERT INTO `domain` (`domain_id`, `incrementer_id`, `actor_id`, `actor_class`, `tag`)
                      VALUES (NULL, NULL, NULL, NULL, '$tag_group');";
                $ds->query($query);
                $domain_id = $ds->insertId();

                // Insert group domain
                $query = "INSERT INTO `group_domain` (`group_domain_id`, `group_id`, `domain_id`, `object_class`, `master`)
                      VALUES (NULL, '$group_id', '$domain_id', '$object_class', '1');";
                $ds->query($query);

                // Select incrementer for this group
                $select      = "SELECT *
                     FROM `incrementer`
                     LEFT JOIN `domain` ON `incrementer`.`incrementer_id` = `domain`.`incrementer_id`
                     LEFT JOIN `group_domain` ON `domain`.`domain_id` = `group_domain`.`domain_id`
                     WHERE `incrementer`.`object_class` = '$object_class'
                     AND `group_domain`.`group_id` = '$group_id';";
                $incrementer = $ds->loadHash($select);

                $incrementer_id = $incrementer["incrementer_id"];

                if ($incrementer_id) {
                    // Update domain with incrementer_id
                    $query = "UPDATE `domain`
                      SET `incrementer_id` = '$incrementer_id'
                      WHERE `domain_id` = '$domain_id';";
                    $ds->query($query);

                    // Update incrementer
                    if (!array_key_exists("nda_range_min", $group_configs) || !$range_max || $range_min === null) {
                        continue;
                    }
                    $query = "UPDATE `incrementer`
                      SET `range_min` = '$range_min', `range_max` = '$range_max'
                      WHERE `incrementer_id` = '$incrementer_id';";
                    $ds->query($query);
                }
            }
        }

        // Update constraints to stick to the event
        return true;
    }

    /**
     * Update domain range
     *
     * @return bool
     */
    protected function updateRangeDomain(): bool
    {
        $ds = $this->ds;

        $groups = $ds->loadList("SELECT * FROM groups_mediboard");

        $tab = [
            "CPatient",
            "CSejour",
        ];

        foreach ($groups as $_group) {
            $group_id = $_group["group_id"];

            $group_configs = $ds->loadHash("SELECT * FROM groups_config WHERE object_id = '$group_id'");

            if (!$group_configs) {
                continue;
            }

            foreach ($tab as $object_class) {
                if ($object_class == "CPatient") {
                    if (!array_key_exists("ipp_range_min", $group_configs)) {
                        continue;
                    }
                    $range_min = $group_configs["ipp_range_min"];
                    $range_max = $group_configs["ipp_range_max"];
                } else {
                    if (!array_key_exists("nda_range_min", $group_configs)) {
                        continue;
                    }
                    $range_min = $group_configs["nda_range_min"];
                    $range_max = $group_configs["nda_range_max"];
                }

                // Select incrementer for this group
                $select      = "SELECT *
                     FROM `incrementer`
                     LEFT JOIN `domain` ON `incrementer`.`incrementer_id` = `domain`.`incrementer_id`
                     LEFT JOIN `group_domain` ON `domain`.`domain_id` = `group_domain`.`domain_id`
                     WHERE `incrementer`.`object_class` = '$object_class'
                     AND `group_domain`.`group_id` = '$group_id'";
                $incrementer = $ds->loadHash($select);

                $incrementer_id = $incrementer["incrementer_id"];

                if (!$incrementer_id) {
                    continue;
                }

                // Update incrementer
                if (!array_key_exists("nda_range_min", $group_configs) || !$range_max || $range_min === null) {
                    continue;
                }

                $query = "UPDATE `incrementer`
                    SET `range_min` = '$range_min', `range_max` = '$range_max'
                    WHERE `incrementer_id` = '$incrementer_id';";
                $ds->query($query);
            }
        }

        // Update constraints to stick to the event
        return true;
    }
}
