<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CSetup;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Mediboard\System\Elastic\ErrorLog;

/**
 * @codeCoverageIgnore
 */
class CSetupDeveloppement extends CSetup
{
    /**
     * @see parent::__construct
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPdeveloppement";
        $this->mod_type = "core";

        $this->makeRevision("0.0");

        $this->makeRevision("0.1");

        $query = "CREATE TABLE `ref_check` (
                `ref_check_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `class` VARCHAR (80) NOT NULL,
                `field` VARCHAR (80) NOT NULL,
                `start_date` DATETIME,
                `end_date` DATETIME
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `ref_check` 
                ADD INDEX target (`class`, `field`),
                ADD INDEX (`start_date`),
                ADD INDEX (`end_date`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `ref_progression` (
                `ref_progression_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ref_check_id` INT (11) UNSIGNED NOT NULL,
                `class` VARCHAR (80) NOT NULL,
                `last_id` INT (11) DEFAULT '0',
                `start_date` DATETIME,
                `end_date` DATETIME,
                `count_rows` INT (11),
                `count_null` INT (11)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `ref_progression` 
                ADD INDEX (`ref_check_id`),
                ADD INDEX (`class`),
                ADD INDEX (`start_date`),
                ADD INDEX (`end_date`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `integrity_errors` (
                `integrity_error_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ref_progression_id` INT (11) UNSIGNED NOT NULL,
                `missing_id` INT (11)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `integrity_errors` 
                ADD INDEX (`ref_progression_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.2");

        $query = "DROP TABLE `integrity_errors`";
        $this->addQuery($query);

        $query = "DROP TABLE `ref_progression`";
        $this->addQuery($query);

        $query = "DROP TABLE `ref_check`";
        $this->addQuery($query);

        $query = "CREATE TABLE `ref_check_table` (
                `ref_check_table_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `class` VARCHAR (255) NOT NULL,
                `start_date` DATETIME,
                `end_date` DATETIME,
                `max_id` INT (11) DEFAULT 0,
                `count_rows` INT (11) DEFAULT 0
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `ref_check_table` 
                ADD INDEX (`class`),
                ADD INDEX (`start_date`),
                ADD INDEX (`end_date`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `ref_check_field` (
                `ref_check_field_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ref_check_table_id` INT (11) UNSIGNED NOT NULL,
                `main_ref_check_field_id` INT (11) UNSIGNED,
                `field` VARCHAR (255) NOT NULL,
                `target_class` VARCHAR (255),
                `start_date` DATETIME,
                `end_date` DATETIME,
                `count_nulls` INT (11) DEFAULT 0,
                `last_id` INT (11),
                `max_id` INT (11),
                `count_rows` INT (11) DEFAULT 0
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `ref_check_field` 
                ADD INDEX (`ref_check_table_id`),
                ADD INDEX (`main_ref_check_field_id`),
                ADD INDEX (`target_class`),
                ADD INDEX (`field`),
                ADD INDEX (`start_date`),
                ADD INDEX (`end_date`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `ref_errors` (
                `ref_error_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ref_check_field_id` INT (11) UNSIGNED NOT NULL,
                `missing_id` INT (11),
                `count_use` INT (11) DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `ref_errors` 
                ADD INDEX (`ref_check_field_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.3");

        $query = "ALTER TABLE `ref_errors` 
        ADD INDEX (`missing_id`),
        ADD INDEX field_missing (`ref_check_field_id`, `missing_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.4");

        $this->addDependency('system', '1.2.98');
        $this->setModuleCategory("systeme", "administration");

        $this->makeRevision("0.5");
        $query = "UPDATE modules SET mod_type = 'core', mod_active = '1' WHERE mod_name = 'dPdeveloppement' ";
        $this->addQuery($query);

        $this->makeRevision("0.6");
        $this->addElasticIndexDependency(ApplicationLog::DATASOURCE_NAME, new ApplicationLog());

        $this->makeRevision("0.7");
        $this->addElasticIndexDependency(ErrorLog::DATASOURCE_NAME, new ErrorLog());

        $this->mod_version = "0.8";
    }
}
