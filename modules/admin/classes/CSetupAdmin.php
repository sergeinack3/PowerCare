<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * @codeCoverageIgnore
 */
class CSetupAdmin extends CSetup
{
    protected function migrateLdapTagToUid(): bool
    {
        $ldap_tag = CAppUI::conf('admin LDAP ldap_tag');
        if (!$ldap_tag) {
            return true;
        }

        $ds    = CSQLDataSource::get('std');
        $where = [
            'object_class' => "= 'CUser'",
            'tag'          => $ds->prepare('= ?', $ldap_tag),
        ];

        $ids = (new CIdSante400())->loadList($where);

        if (!$ids) {
            return true;
        }

        CStoredObject::massLoadFwdRef($ids, 'object_id');

        $ids_to_delete = [];

        /** @var CIdSante400 $_id */
        foreach ($ids as $_id) {
            /** @var CUser $_user */
            $_user = $_id->loadTargetObject();

            if ($_user && $_user->_id && !$_user->ldap_uid) {
                // Only update ldap_uid field
                $_ldap_user           = new CUser();
                $_ldap_user->_id      = $_user->_id;
                $_ldap_user->ldap_uid = $_id->id400;

                if ($_msg = $_ldap_user->store()) {
                    trigger_error($_msg, E_USER_WARNING);

                    return false;
                } else {
                    $ids_to_delete[] = $_id->_id;
                }
            }
        }

        if (count($ids_to_delete) > 0) {
            (new CIdSante400())->deleteAll($ids_to_delete);
        }

        return true;
    }

    function __construct()
    {
        parent::__construct();

        $this->mod_type = "core";
        $this->mod_name = "admin";

        $this->makeRevision("0.0");

        $this->makeEmptyRevision('1.0.14');

        $this->makeRevision("1.0.15");
        $query = "ALTER TABLE `users`
      ADD INDEX (`user_birthday`),
      ADD INDEX (`user_last_login`),
      ADD INDEX (`profile_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.16");
        $query = "ALTER TABLE `users`
      CHANGE `user_address1` `user_address1` VARCHAR( 255 );";
        $this->addQuery($query);

        $this->makeRevision("1.0.17");
        $query = "ALTER TABLE `users`
      ADD `dont_log_connection` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.18");
        $query = "CREATE TABLE `source_ldap` (
      `source_ldap_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `name` VARCHAR (255) NOT NULL,
      `host` TEXT NOT NULL,
      `port` INT (11) DEFAULT '389',
      `rootdn` VARCHAR (255) NOT NULL,
      `ldap_opt_protocol_version` INT (11) DEFAULT '3',
      `ldap_opt_referrals` ENUM ('0','1') DEFAULT '0'
     ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.19");
        $query = "ALTER TABLE `source_ldap`
                ADD `bind_rdn_suffix` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.0.20");
        $query = "ALTER TABLE `source_ldap`
              ADD `priority` INT (11);";
        $this->addQuery($query);

        $this->makeRevision("1.0.21");
        $query = "ALTER TABLE `source_ldap`
              ADD `secured` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.22");
        $query = "ALTER TABLE `users`
      CHANGE `user_phone`  `user_phone`  VARCHAR (20),
      CHANGE `user_mobile` `user_mobile` VARCHAR (20)";
        $this->addQuery($query);

        $this->makeRevision("1.0.23");
        $query = "ALTER TABLE `users`
      DROP `user_pic`,
      DROP `user_signature`,
      CHANGE `user_password`     `user_password`     VARCHAR(255),
      CHANGE `user_login_errors` `user_login_errors` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `user_type`         `user_type`         TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.24");
        if (!$this->ds->hasField('users', 'user_salt')) {
            $query = "ALTER TABLE `users` ADD `user_salt` CHAR(64) AFTER `user_password`";
            $this->addQuery($query);
        }

        $query = "ALTER TABLE `users` MODIFY `user_password` CHAR(64);";
        $this->addQuery($query);

        $this->makeRevision("1.0.25");
        $this->addDependency("system", "1.1.12");

        $query = "CREATE TABLE `view_access_token` (
      `view_access_token_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `user_id` INT (11) UNSIGNED NOT NULL,
      `datetime_start` DATETIME NOT NULL,
      `ttl_hours` INT (11) UNSIGNED NOT NULL,
      `first_use` DATETIME,
      `params` VARCHAR (255) NOT NULL,
      `hash` CHAR (40) NOT NULL
     ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `view_access_token`
      ADD INDEX (`user_id`),
      ADD INDEX (`datetime_start`),
      ADD INDEX (`first_use`),
      ADD INDEX (`hash`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.26");
        $query = "ALTER TABLE
      `user_preferences` CHANGE `user_id` `user_id` INT( 11 ) UNSIGNED NULL";
        $this->addQuery($query);
        $query = "UPDATE `user_preferences`
      SET `user_id` = NULL
      WHERE `user_id` = '0'";
        $this->addQuery($query);

        $this->makeRevision("1.0.27");

        $query = "ALTER TABLE `users` ADD `user_astreinte` VARCHAR (20)";
        $this->addQuery($query);

        $this->makeRevision("1.0.28");
        $query = "ALTER TABLE `user_preferences`
      CHANGE `value` `value` TEXT;";
        $this->addQuery($query);

        if ($this->ds->hasField('users', 'user_password_last_change')) {
            $this->makeEmptyRevision("1.0.29");
        } else {
            $date  = CMbDT::dateTime();
            $query = "ALTER TABLE `users`
      ADD `user_password_last_change` DATETIME NOT NULL DEFAULT '$date' AFTER `user_password`;";
            $this->addQuery($query);
        }

        $this->makeRevision("1.0.30");
        $query = "ALTER TABLE `users`
                CHANGE `user_birthday` `user_birthday` CHAR (10)";
        $this->addQuery($query);

        $this->makeRevision("1.0.31");
        $query = "UPDATE `users`
                SET `user_birthday` = NULL
                WHERE `user_birthday` = '0000-00-00'";
        $this->addQuery($query);

        $this->makeRevision("1.0.32");
        $this->addPrefQuery("notes_anonymous", "0");

        $this->makeRevision("1.0.33");
        $query = "ALTER TABLE
                `view_access_token` ADD `restricted` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.34");
        $query = "ALTER TABLE `user_preferences`
              ADD `restricted` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.35");
        $query = "CREATE TABLE `log_access_medical_object` (
                `access_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `datetime` DATETIME NOT NULL,
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` VARCHAR (80) NOT NULL,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.36");
        $query = "ALTER TABLE `log_access_medical_object`
                ADD INDEX (`user_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`object_id`),
                ADD INDEX (`object_class`),
                ADD INDEX (`group_id`),
                ADD UNIQUE unique_line (`user_id`, `datetime`, `object_id`, `object_class`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.37");
        $query = "CREATE TABLE `bris_de_glace` (
                `bris_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `date` DATETIME NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` VARCHAR (80) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.38");
        $query = "ALTER TABLE `bris_de_glace`
                ADD INDEX (`date`),
                ADD INDEX (`user_id`),
                ADD INDEX (`object_id`),
                ADD INDEX (`object_class`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.39");
        $query = "ALTER TABLE `bris_de_glace`
                ADD `comment` TEXT NOT NULL,
                ADD `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `bris_de_glace`
                ADD INDEX (`group_id`)";
        $this->addQuery($query);

        $this->makeRevision("1.0.40");
        $query = "ALTER TABLE `users`
      ADD `force_change_password` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.41");
        $query = "ALTER TABLE  `users` ADD INDEX (`user_type`)";
        $this->addQuery($query);

        $this->makeRevision("1.0.42");
        $query = "ALTER TABLE `users`
                ADD `allow_change_password` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makerevision('1.0.43');

        $query = "ALTER TABLE `bris_de_glace`
                ADD `role` ENUM('in_charge', 'consultant'),
                CHANGE `comment` `comment` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.0.44");

        $query = "ALTER TABLE `view_access_token`
      ADD `datetime_end` DATETIME AFTER `datetime_start`,
      ADD `max_usages` INT(11) UNSIGNED,
      ADD `latest_use` DATETIME,
      ADD `total_use` INT(11) UNSIGNED,
      ADD `module_action_id` INT(11) UNSIGNED,
      MODIFY `params` TEXT NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `view_access_token`
      SET
        `datetime_end` = DATE_ADD(`datetime_start`, INTERVAL `ttl_hours` HOUR),
        `params` = REPLACE(`params`, '&', '\n');";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_access_token` DROP `ttl_hours`;";
        $this->addQuery($query);

        $this->makeRevision('1.0.45');
        $query = "ALTER TABLE `view_access_token`
              ADD `purgeable` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.0.46');
        $query = "ALTER TABLE `view_access_token`
              ADD `label` VARCHAR(255) AFTER `view_access_token_id`;";
        $this->addQuery($query);
        $this->makeRevision("1.0.47");

        $query = "ALTER TABLE `users` 
      ADD `user_sexe` ENUM ('u','f','m') DEFAULT 'u' AFTER `user_last_name`;";
        $this->addQuery($query);

        $this->makeRevision('1.0.48');
        $query = "ALTER TABLE `view_access_token`
              ADD `validator` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("1.0.49");
        $query = "ALTER TABLE `users`
              CHANGE `user_username` `user_username` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("1.0.50");

        $query = "CREATE TABLE `user_answer_response` (
                `user_answer_response_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `answer` INT (11) NOT NULL,
                `response` VARCHAR (250),
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `number_tests` TINYINT (4) UNSIGNED DEFAULT '0'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `user_answer_response`
                ADD INDEX (`user_id`);";

        $this->addQuery($query);

        $this->makeRevision("1.0.51");
        $query = "DELETE FROM `user_preferences`
              WHERE `key` = 'showLastUpdate';";
        $this->addQuery($query);

        $this->makeRevision('1.0.52');
        $query = "CREATE TABLE `password_log` (
                `password_log_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id`         INT(11) UNSIGNED NOT NULL,
                `password_date`   DATE             NOT NULL,
                `password_salt`   CHAR(64)         NOT NULL,
                `password_hash`   CHAR(64)         NOT NULL,
                KEY (`user_id`),
                KEY `user_date` (`user_id`, `password_date`))/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.0.53');
        $query = "CREATE TABLE `authentication_factor` (
                `authentication_factor_id` INT(11) UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id`                  INT(11) UNSIGNED     NOT NULL,
                `priority`                 TINYINT UNSIGNED     NOT NULL,
                `enabled`                  ENUM('0', '1')       NOT NULL DEFAULT '0',
                `type`                     ENUM('email', 'sms') NOT NULL DEFAULT 'email',
                `value`                    VARCHAR(255)         NOT NULL,
                `validation_code`          CHAR(6)              NOT NULL,
                `attempts`                 TINYINT UNSIGNED     NOT NULL DEFAULT 0,
                KEY (`user_id`))/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.0.54');
        $this->addDependency("system", "1.1.60");
        $query = "ALTER TABLE `user_authentication`
                ADD `authentication_factor_id` INT(11) UNSIGNED,
                ADD INDEX (`authentication_factor_id`);";
        $this->addQuery($query);

        $this->makeRevision('1.0.55');
        $query = "ALTER TABLE `authentication_factor`
                ADD `generation_date` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision('1.0.56');
        $query = "ALTER TABLE `authentication_factor`
                ADD `validation_date` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.0.57");

        $query = "ALTER TABLE `users` 
                ADD `user_astreinte_autre` VARCHAR (20) AFTER `user_astreinte`";
        $this->addQuery($query);

        $this->makeRevision("1.0.58");
        $query = "ALTER TABLE `log_access_medical_object`
                ADD `context` VARCHAR(20);";
        $this->addQuery($query);

        $this->makeRevision('1.0.59');
        $query = "CREATE TABLE `rgpd_consent` (
              `rgpd_consent_id`     INT(11)  UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `object_class`        VARCHAR(255)      NOT NULL,
              `object_id`           INT(11)  UNSIGNED NOT NULL,
              `tag`                 SMALLINT UNSIGNED NOT NULL,
              `send_datetime`       DATETIME,
              `read_datetime`       DATETIME,
              `acceptance_datetime` DATETIME,
              `refusal_datetime`    DATETIME,
              KEY `object` (`object_class`, `object_id`))/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.0.60');
        $query = "ALTER TABLE `user_authentication` CHANGE `auth_method` `auth_method`
              ENUM('basic','substitution','token','ldap','ldap_guid','card','sso','reactivation') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'basic';";
        $this->addQuery($query);


        $this->makeRevision('1.0.61');
        $query = "ALTER TABLE `source_ldap`
              ADD COLUMN `user`     VARCHAR(255),
              ADD COLUMN `password` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision('1.0.62');
        $query = "CREATE TABLE `source_ldap_link` (
              `source_ldap_link_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `source_ldap_id`      INT(11) UNSIGNED NOT NULL,
              `group_id`            INT(11) UNSIGNED NOT NULL,
              KEY (`source_ldap_id`),
              KEY (`group_id`),
              UNIQUE (`source_ldap_id`, `group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.0.63');
        $query = "ALTER TABLE `users`
              ADD COLUMN `internal_phone` VARCHAR(30) AFTER `user_mobile`;";
        $this->addQuery($query);

        $this->makeRevision('1.0.64');
        $query = "ALTER TABLE `log_access_medical_object`
              DROP INDEX unique_line";
        $this->addQuery($query);

        $this->makeRevision('1.0.65');
        $query = "ALTER TABLE `rgpd_consent`
                ADD COLUMN `generation_datetime` DATETIME              AFTER `tag`,
                ADD COLUMN `status`              VARCHAR(255) NOT NULL AFTER `tag`,
                ADD COLUMN `proof_hash`          CHAR(64),
                ADD COLUMN `last_error`          VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision('1.0.66');
        $query = "ALTER TABLE `rgpd_consent`
                MODIFY `status` SMALLINT UNSIGNED NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.0.67');
        $this->addMethod('addDefaultRGPDConf');

        $this->makeRevision('1.0.68');
        $query = "ALTER TABLE `users`
              ADD COLUMN `is_robot` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `dont_log_connection`;";
        $this->addQuery($query);

        $this->makeRevision('1.0.69');
        $query = "UPDATE `users`
              SET `is_robot` = '1' WHERE `dont_log_connection` = '1';";
        $this->addQuery($query);

        $this->makeRevision('1.0.70');
        $query = "ALTER TABLE `rgpd_consent`
              ADD COLUMN `group_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeEmptyRevision("1.0.71");
        /*$query = "ALTER TABLE `authentication_factor`
                    ADD INDEX (`generation_date`),
                    ADD INDEX (`validation_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `bris_de_glace`
                    ADD INDEX object (object_class, object_id)";
        $this->addQuery($query);

        $query = "ALTER TABLE `log_access_medical_object`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `perm_object`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `rgpd_consent`
                    ADD INDEX (`generation_datetime`),
                    ADD INDEX (`send_datetime`),
                    ADD INDEX (`read_datetime`),
                    ADD INDEX (`acceptance_datetime`),
                    ADD INDEX (`refusal_datetime`),
                    ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `users`
                    ADD INDEX (`user_password_last_change`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_access_token`
                    ADD INDEX (`module_action_id`),
                    ADD INDEX (`datetime_end`),
                    ADD INDEX (`latest_use`);";
        $this->addQuery($query);*/

        $this->makeEmptyRevision("1.0.72");

        /*$query = "ALTER TABLE `bris_de_glace`
                    DROP INDEX object_id,
                    DROP INDEX object_class;";
        $this->addQuery($query);

        $query = "ALTER TABLE `log_access_medical_object`
                    DROP INDEX object_id,
                    DROP INDEX object_class;";
        $this->addQuery($query);*/

        $this->makeRevision('1.0.73');
        $query = "CREATE TABLE `kerberos_ldap_identifier` (
              `kerberos_ldap_identifier_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `username`                    VARCHAR(255)     NOT NULL,
              `user_id`                     INT(11) UNSIGNED NOT NULL,
              KEY (`user_id`),
              UNIQUE (`username`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.74");

        $this->addDependency('system', '1.2.98');
        $this->setModuleCategory("parametrage", "metier");

        $this->makeRevision('1.0.75');
        $this->addFunctionalPermQuery('admin_unique_session', '0');

        $this->makeRevision('1.0.76');
        $query = "ALTER TABLE `view_access_token`
              MODIFY `hash` VARCHAR(255) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.0.77');
        $query = "CREATE TABLE `log_access_medical_data` (
                `access_id`    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id`      INT(11) UNSIGNED NOT NULL,
                `datetime`     DATETIME         NOT NULL,
                `object_id`    INT(11) UNSIGNED NOT NULL,
                `object_class` VARCHAR(80)      NOT NULL,
                `group_id`     INT(11) UNSIGNED NOT NULL,
                `context`      VARCHAR(20),
                KEY (`user_id`),
                KEY (`datetime`),
                KEY (`group_id`),
                KEY `object` (`object_class`, `object_id`),
                UNIQUE `unique_line` (`user_id`, `datetime`, `object_id`, `object_class`))/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "INSERT IGNORE INTO `log_access_medical_data`
              SELECT * FROM `log_access_medical_object`;";
        $this->addQuery($query, true);

        $query = "DROP TABLE `log_access_medical_object`;";
        $this->addQuery($query);

        $this->makeRevision('1.0.78');
        $query = "ALTER TABLE `source_ldap`
                ADD COLUMN `dn_alternatives` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('1.0.79');
        $query = "ALTER TABLE `users`
                ADD COLUMN `lock_datetime` DATETIME AFTER `user_login_errors`;";
        $this->addQuery($query);

        $this->addMethod('setDefaultLockDate');

        $this->makeRevision('1.0.80');

        $query = "ALTER TABLE `perm_object`
                    DROP INDEX `user_id`,
                    ADD UNIQUE INDEX (`user_id`, `object_class`, `object_id`)";
        $this->addQuery($query);

        $this->makeRevision('1.0.81');
        $query = "ALTER TABLE `source_ldap`
                ADD COLUMN `dn_whitelist` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.0.82');
        $query = "DROP TABLE `authentication_factor`;";
        $this->addQuery($query);

        $this->makeRevision('1.0.83');
        $query = "ALTER TABLE `users` ADD `ldap_uid` VARCHAR(255);";
        $this->addQuery($query);

        $this->addMethod('migrateLdapTagToUid');

        // Too heavy
        //        $query = "ALTER TABLE `user_authentication`
        //                    DROP COLUMN `authentication_factor_id`;";
        //        $this->addQuery($query);

        $this->makeRevision('1.0.84');

        $query = "ALTER TABLE `view_access_token`
                    ADD COLUMN `routes_names` TEXT AFTER `params`";
        $this->addQuery($query);

        $this->makeRevision('1.0.85');
        $query = "ALTER TABLE `source_ldap`
                ADD COLUMN `cascade` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.0.86');
        $query = "ALTER TABLE `users` ADD `sub_fc` VARCHAR (120);";
        $this->addQuery($query);

        $this->mod_version = '1.0.87';
    }

    protected function addDefaultRGPDConf(): bool
    {
        $configs = CRGPDManager::getRGPDDefaultConf();

        foreach ($configs as $_class => $_configs) {
            foreach ($_configs as $_key => $_conf) {
                $this->addDefaultTextCConfiguration("admin CRGPDConsent {$_class} rgpd_{$_key}", $_conf);
            }
        }

        return true;
    }

    /**
     * Setting up a default lock date for already locked users edge case.
     * Enabling them to log in after first successful attempt right after the update.
     * Otherwise, lock datetime would be null and manual unlocking would be necessary.
     * If unlocking configuration is kept empty, these users will not be able to log in.
     *
     * @return bool
     * @throws Exception
     */
    protected function setDefaultLockDate(): bool
    {
        $max_attempts = (int)CAppUI::conf('admin CUser max_login_attempts');

        if ($max_attempts <= 0) {
            return true;
        }

        $query = "UPDATE `users` 
                  SET `lock_datetime` = '2000-01-01 00:00:00' 
                  WHERE `user_login_errors` >= '{$max_attempts}';";

        return ($this->ds->exec($query) !== false);
    }
}
