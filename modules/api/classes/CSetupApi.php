<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupApi extends CSetup {
  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "api";
    $this->makeRevision("0.0");
    $query = "CREATE TABLE IF NOT EXISTS `mobile_log` (
                `mobile_log_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `url` VARCHAR (255),
                `input` LONGTEXT,
                `output` LONGTEXT,
                `device_uuid` VARCHAR (255),
                `device_platform` VARCHAR (255),
                `device_platform_version` VARCHAR (255),
                `device_model` VARCHAR (255),
                `level` VARCHAR (255),
                `description` VARCHAR (255) NOT NULL,
                `log_datetime` DATETIME NOT NULL,
                `origin` VARCHAR (255),
                `object` LONGTEXT,
                `code` VARCHAR (255),
                `internet_connection_type` VARCHAR (255),
                `execution_time` INT (11),
                `application_name` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `mobile_log` 
                ADD INDEX (`log_datetime`);";
    $this->addQuery($query);

    $this->makeRevision('0.01');
    $query = "CREATE TABLE IF NOT EXISTS `sync_log` (
                `sync_log_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `owner_id`    INT(11) UNSIGNED,
                `owner_class` VARCHAR(40),
                `user_id`     INT(11) UNSIGNED NOT NULL,
                `object_id`   INT(11) UNSIGNED NOT NULL,
                `object_class`VARCHAR(40)      NOT NULL,
                `action`      VARCHAR(255)     NOT NULL,
                `datetime`    DATETIME         NOT NULL,
                PRIMARY KEY    (`sync_log_id`),
                KEY `owner`    (`owner_class`, `owner_id`),
                KEY `user`     (`user_id`),
                KEY `datetime` (`datetime`),
                KEY `object`   (`object_class`, `object_id`)) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision('0.02');
    $query = "ALTER TABLE `sync_log`
                ADD COLUMN `reference_id`    INT(11) UNSIGNED NOT NULL,
                ADD COLUMN `reference_class` VARCHAR(40)      NOT NULL,
                ADD INDEX `reference` (`reference_class`, `reference_id`);";
    $this->addQuery($query, true);

    $this->makeRevision('0.03');
    $query = "ALTER TABLE `sync_log`
                MODIFY `reference_id`    INT(11) UNSIGNED,
                MODIFY `reference_class` VARCHAR(40),
                ADD COLUMN `reference_date` DATE;";
    $this->addQuery($query, true);
    $this->makeRevision('0.04');

    /* ----- PARTIE  API TIERS ------- */
    $query = "CREATE TABLE `patient_user_api` (
                `patient_user_api_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `api_user_id` INT (11) UNSIGNED,
                `api_user_class` VARCHAR (255) NOT NULL,
                `first_call` DATETIME NOT NULL,
                `last_call` DATETIME NOT NULL,
                `synchronized_since` DATE
              )/*! ENGINE=MyISAM */;
ALTER TABLE `patient_user_api` 
                ADD INDEX (`patient_id`),
                ADD INDEX (`api_user_id`),
                ADD INDEX (`first_call`),
                ADD INDEX (`synchronized_since`),
                ADD INDEX (`last_call`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `user_api_withings` (
                `token` VARCHAR (255) NOT NULL,
                `token_refresh` VARCHAR (255) NOT NULL,
                `expiration_date` DATETIME,
                `user_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_api_id` VARCHAR (255) NOT NULL,
                `scope_accepted` VARCHAR (255) NOT NULL,
                `constant_accepted` VARCHAR (255) NOT NULL,
                `subscribe` ENUM ('0','1') DEFAULT '0',
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `obj_weight` INT (11),
                `obj_steps` INT (11)
                                 
              )/*! ENGINE=MyISAM */;
ALTER TABLE `user_api_withings` 
                ADD INDEX (`expiration_date`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `user_api_fitbit` (
                `token` VARCHAR (500) NOT NULL,
                `token_refresh` VARCHAR (255) NOT NULL,
                `expiration_date` DATETIME,
                `user_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_api_id` VARCHAR (255) NOT NULL,
                `scope_accepted` VARCHAR (255) NOT NULL,
                `constant_accepted` VARCHAR (255) NOT NULL,
                `subscribe` ENUM ('0','1') DEFAULT '0',
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `obj_weight` INT (11),
                `obj_steps` INT (11)
              )/*! ENGINE=MyISAM */;
ALTER TABLE `user_api_fitbit` 
                ADD INDEX (`expiration_date`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `api_tiers_stack_request` (
                `api_tiers_stack_request_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `api_id` INT (11) UNSIGNED NOT NULL,
                `api_class` varchar(255) NOT NULL,
                `constant_code` VARCHAR (255) NOT NULL,
                `scope` VARCHAR (255) NOT NULL,
                `datetime_start` DATETIME NOT NULL,
                `datetime_end` DATETIME NOT NULL,
                `receive_datetime` DATETIME,
                `send_datetime` DATETIME,
                `agregate` INT (11) DEFAULT '0',
                `max_attemp` INT (11) DEFAULT '0',
                `nb_request` INT (11) DEFAULT '1',
                `acquittement` LONGTEXT,
                `time_response` INT (11),
                `nb_stored` INT (11) DEFAULT '0',
                `optimized` INT (11) DEFAULT '0'
              )/*! ENGINE=MyISAM */;
ALTER TABLE `api_tiers_stack_request` 
                ADD INDEX (`datetime_start`),
                ADD INDEX (`datetime_end`),
                ADD INDEX (`receive_datetime`),
                ADD INDEX (`send_datetime`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `api_tiers_stack_request` 
                ADD `emptied` ENUM ('0','1') DEFAULT '0',
                ADD `datetime` DATETIME NOT NULL;
ALTER TABLE `api_tiers_stack_request` 
                ADD INDEX (`datetime`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `user_api_fitbit`
                ADD `created_date_api` DATE,
                ADD INDEX (`created_date_api`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `user_api_withings`
                ADD `created_date_api` DATE,
                ADD INDEX (`created_date_api`);";
    $this->addQuery($query);

    $this->makeRevision('0.05');

    $query = "ALTER TABLE `api_tiers_stack_request` 
                ADD `group_id` INT (11) UNSIGNED NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision('0.06');

    $query = "ALTER TABLE `api_tiers_stack_request` 
                ADD `emetteur` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `api_tiers_stack_request` 
                ADD INDEX (`constant_code`),
                ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.07");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->mod_version = '0.08';
  }
}
