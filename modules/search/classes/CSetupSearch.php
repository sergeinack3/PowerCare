<?php

/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupSearch extends CSetup
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name         = "search";
        $this->mod_requires_php = "7.0";

        $this->makeRevision("0.0");

        $this->makeRevision("0.01");
        $query = "CREATE TABLE `search_indexing` (
              `search_indexing_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `object_class` VARCHAR (50) NOT NULL,
              `object_id` INT (11) UNSIGNED NOT NULL,
              `type` ENUM('create','store','delete','merge') NOT NULL DEFAULT 'create',
              `date` DATETIME NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.02");
        $query = "ALTER TABLE `search_indexing`
              CHANGE `object_class` `object_class` CHAR(50) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.03");
        $query = "ALTER TABLE `search_indexing`
              ADD INDEX `index_order` (`object_class`, `type`, `search_indexing_id`);";
        $this->addQuery($query);


        $this->makeRevision("0.04");
        $query = "CREATE TABLE `search_thesaurus_entry` (
              `search_thesaurus_entry_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `entry` TEXT NOT NULL,
              `types` TEXT,
              `titre` VARCHAR (255),
              `contextes` ENUM('generique','pharmacie','pmsi','bloc') NOT NULL DEFAULT 'generique',
              `agregation` ENUM('0','1') NOT NULL DEFAULT '0',
              `group_id` INT(11) UNSIGNED ,
              `function_id` INT(11) UNSIGNED ,
              `user_id` INT(11) UNSIGNED
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.05");
        $query = "
        CREATE TABLE IF NOT EXISTS `rss_search_items` (
          `rss_search_item_id` BIGINT NOT NULL AUTO_INCREMENT ,
          `rss_id` BIGINT NOT NULL ,
          `search_id` BIGINT NOT NULL,
          `search_class` char(40),
          `rmq` text,
          PRIMARY KEY ( `rss_search_item_id` ) ,
          INDEX ( `rss_id`, `search_id`, `search_class`)
          ) /*! ENGINE=MyISAM */ COMMENT = 'Table des Search Items';";
        $this->addQuery($query);

        $this->makeRevision("0.06");
        $query = "
        CREATE TABLE  `search_thesaurus_entry_target` (
          `search_thesaurus_entry_target_id` BIGINT NOT NULL AUTO_INCREMENT ,
          `search_thesaurus_entry_id` BIGINT NOT NULL ,
          `object_class` VARCHAR(50),
          `object_id`  VARCHAR(50),
          PRIMARY KEY (`search_thesaurus_entry_target_id`),
          INDEX (`search_thesaurus_entry_id`),
          INDEX (`object_id`, `object_class`)
          ) /*! ENGINE=MyISAM */ COMMENT = 'Table des cibles de favoris';";
        $this->addQuery($query);

        $this->makeRevision("0.07");

        $query = "
        ALTER TABLE `search_thesaurus_entry`
        CHANGE `contextes` `contextes` VARCHAR(255) NOT NULL DEFAULT 'generique';";
        $this->addQuery($query);


        $query = "
        ALTER TABLE `search_thesaurus_entry_target`
        CHANGE `search_thesaurus_entry_target_id` `search_thesaurus_entry_target_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        CHANGE `search_thesaurus_entry_id` `search_thesaurus_entry_id` INT(11) UNSIGNED
        ;";
        $this->addQuery($query);

        $query = "UPDATE `search_thesaurus_entry` SET `contextes`='prescription' WHERE `contextes`= 'bloc'";
        $this->addQuery($query);

        $query = "
        ALTER TABLE `rss_search_items`
        CHANGE `rss_search_item_id` `rss_search_item_id` INT(11) UNSIGNED,
        CHANGE `rss_id` `rss_id` INT(11) UNSIGNED,
        CHANGE `search_id` `search_id` INT(11) UNSIGNED
        ;";
        $this->addQuery($query);

        $this->makeRevision("0.08");
        $query = "DROP TABLE IF EXISTS `rss_search_items`";
        $this->addQuery($query);

        $query = "CREATE TABLE IF NOT EXISTS `rss_search_items` (
                `rss_search_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rss_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `search_id` INT (11),
                `search_class` VARCHAR (40),
                `rmq` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rss_search_items`
                ADD INDEX (`rss_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.09");

        $query = "ALTER TABLE `search_thesaurus_entry`
                CHANGE `entry` `entry` TEXT,
                CHANGE `types` `types` VARCHAR (255),
                CHANGE `contextes` `contextes` ENUM ('generique','pharmacie','pmsi','prescription','classique'),
                CHANGE `agregation` `agregation` ENUM ('0','1') DEFAULT '0',
                CHANGE `user_id` `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                ADD `search_auto` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `search_thesaurus_entry`
                ADD INDEX (`group_id`),
                ADD INDEX (`function_id`),
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.10");
        $query = "ALTER TABLE `rss_search_items`
                ADD `user_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rss_search_items`
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision('0.11');
        $query = "ALTER TABLE `search_indexing`
                ADD `processed` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.12');
        $query = "ALTER TABLE `search_thesaurus_entry` 	ADD COLUMN `fuzzy` ENUM('0','1') NULL DEFAULT '0' AFTER `agregation`;";
        $this->addQuery($query);

        $this->makeRevision('0.13');
        $query = "CREATE TABLE `search_history` (
                `search_history_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `entry` TEXT,
                `types` VARCHAR (255),
                `contexte` ENUM ('generique','pharmacie','pmsi','prescription','classique'),
                `agregation` ENUM ('0','1') DEFAULT '0',
                `fuzzy` ENUM ('0','1') DEFAULT '0',
                `user_id` INT (11) UNSIGNED NOT NULL,
                `hits` INT (11) DEFAULT '0',
                `date` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `search_history` 
                ADD INDEX (`user_id`),
                ADD INDEX (`date`);";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $this->setModuleCategory("reporting", "metier");

        $this->makeRevision("0.15");

        $query = "ALTER TABLE `search_indexing` ADD INDEX (`object_class`, `object_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.16");

        $query = "ALTER TABLE `search_thesaurus_entry`
                CHANGE `contextes` `contextes` ENUM ('generique','pharmacie','pmsi','prescription')";
        $this->addQuery($query);

        $this->makeRevision("0.17");
        $this->addElasticIndexDependency(Search::DATASOURCE_NAME, new Search());

        $this->mod_version = "0.18";
    }
}
