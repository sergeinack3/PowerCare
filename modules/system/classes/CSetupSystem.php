<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\CacheManager;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSetup;
use Ox\Core\Module\CModule;
use Ox\Core\Plugin\Button\AbstractAppBarButtonPlugin;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @codeCoverageIgnore
 */
class CSetupSystem extends CSetup
{
    /**
     * Update ExObject tables
     *
     * @return bool
     */
    protected function updateExObjectTables(): bool
    {
        $ds = $this->ds;

        $ex_classes = $ds->loadList("SELECT * FROM ex_class");
        foreach ($ex_classes as $_ex_class) {
            $_ex_class['host_class'] = strtolower($_ex_class['host_class']);

            $old_name = "ex_{$_ex_class['host_class']}_{$_ex_class['event']}_{$_ex_class['ex_class_id']}";
            $new_name = "ex_object_{$_ex_class['ex_class_id']}";

            $query = "RENAME TABLE `$old_name` TO `$new_name`";
            $ds->query($query);
        }

        return true;
    }

    /**
     * Add reference fields to exObjects
     *
     * @return bool
     */
    protected function addExReferenceFields(): bool
    {
        $ds = $this->ds;

        // Changement des chirurgiens
        $query         = "SELECT ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadHashAssoc($query);
        foreach ($list_ex_class as $key => $hash) {
            $query = "ALTER TABLE `ex_object_$key`
          ADD `reference_id` INT (11) UNSIGNED AFTER `object_class`,
          ADD `reference_class` VARCHAR(80) AFTER `object_class`";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Add reference fields to exObjects, again
     *
     * @return bool
     */
    protected function addExReferenceFields2(): bool
    {
        $ds = $this->ds;

        // Changement des chirurgiens
        $query         = "SELECT ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadHashAssoc($query);
        foreach ($list_ex_class as $key => $hash) {
            $query = "ALTER TABLE `ex_object_$key`
      ADD `reference2_id` INT (11) UNSIGNED AFTER `reference_id`,
      ADD `reference2_class` VARCHAR(80) AFTER `reference_id`";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Add reference fields indices
     *
     * @return bool
     */
    protected function addExReferenceFieldsIndex(): bool
    {
        $ds = $this->ds;

        // Changement des chirurgiens
        $query         = "SELECT ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadHashAssoc($query);
        foreach ($list_ex_class as $key => $hash) {
            $query = "ALTER TABLE `ex_object_$key`
          ADD INDEX(`reference_id`),
          ADD INDEX(`reference_class`),
          ADD INDEX(`reference2_id`),
          ADD INDEX(`reference2_class`)";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Add a group_id to all the ex_objects
     *
     * @return bool
     */
    protected function addExObjectGroupId(): bool
    {
        $ds = $this->ds;

        // Changement des ExClasses
        $query         = "SELECT ex_class_id, host_class FROM ex_class";
        $list_ex_class = $ds->loadHashAssoc($query);
        foreach ($list_ex_class as $key => $hash) {
            $query = "ALTER TABLE `ex_object_$key`
      ADD `group_id` INT (11) UNSIGNED NOT NULL AFTER `ex_object_id`";
            $ds->exec($query);

            $field_class = null;
            $field_id    = null;
            switch ($hash["host_class"]) {
                default:
                case "CMbObject":
                    break;

                case "CPrescriptionLineElement":
                case "CPrescriptionLineMedicament":
                case "COperation":
                case "CConsultation":
                case "CConsultAnesth":
                case "CAdministration":
                    $field_class = "reference_class";
                    $field_id    = "reference_id";
                    break;

                case "CSejour":
                    $field_class = "object_class";
                    $field_id    = "object_id";
            }

            if ($field_class && $field_id) {
                $query = "UPDATE `ex_object_$key`
            LEFT JOIN `sejour` ON `ex_object_$key`.`$field_id`    = `sejour`.`sejour_id` AND
                  `ex_object_$key`.`$field_class` = 'CSejour'
            SET `ex_object_$key`.`group_id` = `sejour`.`group_id`";
                $ds->exec($query);
            }
        }

        return true;
    }

    /**
     * Create ex_class_events from events
     *
     * @return bool
     */
    protected function createExClassEvents(): bool
    {
        $ds = $this->ds;

        $ex_classes = $ds->loadList("SELECT * FROM ex_class");

        $specs = [];

        foreach ($ex_classes as $_ex_class) {
            $_ex_class = array_map([$ds, "escape"], $_ex_class);
            extract($_ex_class);

            // Insert events
            $query = "INSERT INTO ex_class_event(ex_class_id, host_class, event_name, disabled, unicity)
                 VALUES ('$ex_class_id', '$host_class', '$event', '$disabled', '$unicity')";
            $ds->query($query);
            $event_id = $ds->insertId();

            // Update constraints to stick to the event
            $query = "UPDATE ex_class_constraint
          SET ex_class_constraint.ex_class_event_id = '$event_id'
          WHERE ex_class_id = '$ex_class_id'";
            $ds->query($query);

            $spec = null;
            if (isset($specs[$host_class])) {
                $spec = $specs[$host_class];
            } elseif ($host_class) {
                $instance = new $host_class();
                $spec     = $specs[$host_class] = $instance->_spec->events;
            }

            if (!$spec) {
                continue;
            }

            // Update host fields to stick to the event and ex_group_id
            $ex_groups = $ds->loadList("SELECT * FROM ex_class_field_group WHERE ex_class_id = '$ex_class_id'");
            foreach ($ex_groups as $_ex_group) {
                $_ex_group    = array_map([$ds, "escape"], $_ex_group);
                $_ex_group_id = $_ex_group["ex_class_field_group_id"];

                // Ex class field report level (HOST)
                $query = "UPDATE ex_class_field
            SET report_class = '$host_class'
            WHERE ex_group_id = '$_ex_group_id' AND report_level = 'host'";
                $ds->query($query);

                // Ex class host field (HOST)
                $query = "UPDATE ex_class_host_field
            SET host_class = '$host_class'
            WHERE ex_group_id = '$_ex_group_id' AND host_type = 'host'";
                $ds->query($query);

                // Ex class field report levl (ref 1 and 2)
                foreach ([1, 2] as $i) {
                    $_class = $spec[$event]["reference$i"][0];

                    // Ex class field report level (REF)
                    $query = "UPDATE ex_class_field
              SET report_class = '$_class'
              WHERE ex_group_id = '$_ex_group_id' AND report_level = '$i'";
                    $ds->query($query);

                    // Ex class host field (REF)
                    $query = "UPDATE ex_class_host_field
              SET host_class = '$_class'
              WHERE ex_group_id = '$_ex_group_id' AND host_type = 'reference$i'";
                    $ds->query($query);
                }
            }
        }

        return true;
    }

    /**
     * Add additionnal object field
     *
     * @return bool
     */
    protected function addExObjectAdditionalObject(): bool
    {
        $ds = $this->ds;

        // Changement des ExClasses
        $query         = "SELECT ex_class_id, ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadHashAssoc($query);

        foreach ($list_ex_class as $key => $hash) {
            $query = "ALTER TABLE `ex_object_$key`
                    ADD `additional_id` INT (11) UNSIGNED AFTER `reference2_class`,
                    ADD `additional_class` VARCHAR(80) AFTER `additional_id`,
                    ADD  INDEX `additional` ( `additional_class`, `additional_id` ),

                    DROP INDEX `object_id`,
                    DROP INDEX `object_class`,
                    ADD  INDEX `object` ( `object_class`, `object_id` ),

                    DROP INDEX `reference_id`,
                    DROP INDEX `reference_class`,
                    ADD  INDEX `reference1` ( `reference_class`, `reference_id` ),

                    DROP INDEX `reference2_id`,
                    DROP INDEX `reference2_class`,
                    ADD  INDEX `reference2` ( `reference2_class`, `reference2_id` );";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Build ExLink table
     *
     * @usedb ex_class.ex_class_id, ex_link
     *
     * @return bool
     */
    protected function buildExLink(): bool
    {
        $ds = $this->ds;

        // Changement des ExClasses
        $query         = "SELECT ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadColumn($query);

        foreach ($list_ex_class as $ex_class_id) {
            $query = "INSERT INTO `ex_link` (`ex_class_id`, `ex_object_id`, `object_id`, `object_class`, `group_id`, `level`)
                     SELECT '$ex_class_id', `ex_object_id`, `object_id`, `object_class`, `group_id`, 'object' FROM `ex_object_$ex_class_id`";
            $ds->exec($query);

            $query = "INSERT INTO `ex_link` (`ex_class_id`, `ex_object_id`, `object_id`, `object_class`, `group_id`, `level`)
                     SELECT '$ex_class_id', `ex_object_id`, `reference_id`, `reference_class`, `group_id`, 'ref1' FROM `ex_object_$ex_class_id`";
            $ds->exec($query);

            $query = "INSERT INTO `ex_link` (`ex_class_id`, `ex_object_id`, `object_id`, `object_class`, `group_id`, `level`)
                     SELECT '$ex_class_id', `ex_object_id`, `reference2_id`, `reference2_class`, `group_id`, 'ref2' FROM `ex_object_$ex_class_id`";
            $ds->exec($query);

            $query = "INSERT INTO `ex_link` (`ex_class_id`, `ex_object_id`, `object_id`, `object_class`, `group_id`, `level`)
                     SELECT '$ex_class_id', `ex_object_id`, `additional_id`, `additional_class`, `group_id`, 'add' FROM `ex_object_$ex_class_id`
                     WHERE `additional_id` IS NOT NULL AND `additional_class` IS NOT NULL";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Remove zombie ex links
     *
     * @return bool
     */
    protected function removeZombieExLinks(): bool
    {
        $ds = $this->ds;

        // Changement des ExClasses
        $query         = "SELECT ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadColumn($query);

        foreach ($list_ex_class as $ex_class_id) {
            $query = "DELETE FROM `ex_link` WHERE
                    `ex_object_id` NOT IN(SELECT `ex_object_id` FROM `ex_object_$ex_class_id`) AND
                    `ex_class_id` = '$ex_class_id';";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Create ex_objects_XX date and owner fields
     *
     * @return bool
     */
    protected function addExObjectDates(): bool
    {
        $ds = $this->ds;

        // Changement des ExClasses
        $query         = "SELECT ex_class_id FROM ex_class";
        $list_ex_class = $ds->loadColumn($query);

        foreach ($list_ex_class as $ex_class_id) {
            $query = "ALTER TABLE `ex_object_$ex_class_id`
                    ADD `datetime_create` DATETIME AFTER `additional_class`,
                    ADD `datetime_edit`   DATETIME AFTER `datetime_create`,
                    ADD `owner_id`        INT(11) UNSIGNED AFTER `datetime_edit`,
                    ADD INDEX (`owner_id`),
                    ADD INDEX (`datetime_create`);";
            $ds->exec($query);
        }

        return true;
    }

    protected function removeDuplicatePreferences(): bool
    {
        $ds = $this->ds;

        // Changement des preferences groupes par user_id
        $query = "SELECT 
                COUNT(*) AS `total`, 
                `key`,
                CAST(GROUP_CONCAT(`pref_id` SEPARATOR ',') AS CHAR) AS `pref_ids`
                FROM `user_preferences` 
                WHERE `user_id` IS NULL 
                GROUP BY `key` 
                HAVING `total` > 1;";
        $list  = $ds->loadList($query);

        foreach ($list as $_row) {
            $_pref_ids = explode(",", $_row["pref_ids"]);
            array_pop($_pref_ids);

            $query = "DELETE FROM `user_preferences`
                    WHERE `pref_id` " . $ds->prepareIn($_pref_ids);
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Fill in CExLink date and owner fields
     *
     * @return bool
     */
    protected function addExLinkDates(): bool
    {
        $ds = $this->ds;

        // Changement des ExClasses
        $query         = "SELECT ex_class_id FROM ex_class;";
        $list_ex_class = $ds->loadColumn($query);

        foreach ($list_ex_class as $ex_class_id) {
            $query = "UPDATE `ex_link`
                  LEFT JOIN `ex_object_$ex_class_id` ON `ex_object_$ex_class_id`.`ex_object_id` = `ex_link`.`ex_object_id` AND `ex_link`.`ex_class_id` = '$ex_class_id'
                  SET
                    `ex_link`.`datetime_create` = `ex_object_$ex_class_id`.`datetime_create`,
                    `ex_link`.`owner_id`        = `ex_object_$ex_class_id`.`owner_id`
                  WHERE
                    `ex_object_$ex_class_id`.`datetime_create` IS NOT NULL AND
                    `ex_link`.`datetime_create` IS NULL;";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Create ex_objects_XX completeness_level and nb_alert_fields fields
     *
     * @return bool
     */
    protected function addExObjectCompleteness(): bool
    {
        $ds = $this->ds;

        $query         = 'SELECT ex_class_id FROM ex_class';
        $list_ex_class = $ds->loadColumn($query);

        foreach ($list_ex_class as $ex_class_id) {
            $query = "ALTER TABLE `ex_object_{$ex_class_id}`
                  ADD `completeness_level` ENUM('none', 'some', 'all') DEFAULT NULL AFTER `owner_id`,
                  ADD `nb_alert_fields`    SMALLINT UNSIGNED           DEFAULT NULL AFTER `completeness_level`;";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Create the index on firstname if it does not exists. Index have been created on some servers before this
     * function is executed
     *
     * @return bool
     */
    protected function addIndexFirstnameIfNotExists(): bool
    {
        $query_check_index = "SHOW INDEX FROM `firstname_to_gender` WHERE Key_name = 'firstname'";
        $index_exists      = $this->ds->countRows($query_check_index);
        if ($index_exists == 0) {
            $query = "ALTER TABLE `firstname_to_gender`
                ADD INDEX firstname (`firstname`);";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function migrateHandlers(): bool
    {
        $object_handlers = CAppUI::conf('object_handlers');
        $index_handlers  = CAppUI::conf('index_handlers');
        $eai_handlers    = CAppUI::conf('eai_handlers');

        if (!is_array($object_handlers)) {
            $object_handlers = [];
        }

        if (!is_array($index_handlers)) {
            $index_handlers = [];
        }

        if (!is_array($eai_handlers)) {
            $eai_handlers = [];
        }

        foreach ($object_handlers as $_class => $_active) {
            $query = "INSERT INTO `configuration` (`feature`, `value`) VALUES (?1, ?2)";
            $query = $this->ds->prepare($query, "system object_handlers {$_class}", $_active);

            $this->ds->exec($query);
        }

        foreach ($index_handlers as $_class => $_active) {
            // MB Host handler is not migrated
            if ($_class === 'CMbHostAuthenticationHandler') {
                continue;
            }

            $query = "INSERT INTO `configuration` (`feature`, `value`) VALUES (?1, ?2)";
            $query = $this->ds->prepare($query, "system index_handlers {$_class}", $_active);

            $this->ds->exec($query);
        }

        foreach ($eai_handlers as $_class => $_active) {
            $query = "INSERT INTO `configuration` (`feature`, `value`) VALUES (?1, ?2)";
            $query = $this->ds->prepare($query, "system eai_handlers {$_class}", $_active);

            $this->ds->exec($query);
        }

        return true;
    }

    /**
     * If CSetupAdmin revision 1.0.60 as change the available auth_method reset them
     */
    protected function changeUserAuthMethods(): bool
    {
        $column_infos = $this->ds->loadHash("SHOW COLUMNS FROM user_authentication WHERE Field = 'auth_method'");

        if (strpos($column_infos['Type'], 'standard') === false) {
            $query = "ALTER TABLE user_authentication
              MODIFY auth_method ENUM('basic', 'substitution', 'token', 'ldap', 'ldap_guid', 'card', 'sso', 'standard') NOT NULL DEFAULT 'standard';";
            if (!$this->ds->exec($query)) {
                trigger_error($this->ds->error(), E_USER_WARNING);
            }
        }

        return true;
    }

    protected function movePlaceholderConfigs(): bool
    {
        if (CModule::getActive('oxERP')) {
            CAppUI::setConf(
                AbstractAppBarButtonPlugin::CONFIG_PREFIX . ' UserChargingShortcut',
                CAppUI::conf('template_placeholders COXERPUserChargingTemplatePlaceholder')
            );
        }

        if (CModule::getActive('monitorClient')) {
            CAppUI::setConf(
                AbstractAppBarButtonPlugin::CONFIG_PREFIX . ' MonitorClientShortCut',
                CAppUI::conf('template_placeholders COXMonitorClientTemplatePlaceholder')
            );
        }

        if (CModule::getActive('oxPresta')) {
            CAppUI::setConf(
                AbstractAppBarButtonPlugin::CONFIG_PREFIX . ' ButtonLignePrestaShortcut',
                CAppUI::conf('template_placeholders COXLignePrestationTemplatePlaceholder')
            );
        }

        if (CModule::getActive('ecap')) {
            CAppUI::setConf(
                AbstractAppBarButtonPlugin::CONFIG_PREFIX . ' ButtonEcapShortcut',
                CAppUI::conf('template_placeholders CEcTemplatePlaceholder')
            );
        }

        if (CModule::getActive('astreintes')) {
            CAppUI::setConf(
                AbstractAppBarButtonPlugin::CONFIG_PREFIX . ' ButtonAstreintesShortcut',
                CAppUI::conf('template_placeholders CAstreintesTemplatePlaceholder')
            );
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    protected function clearLegacyControllerCache(): bool
    {
        CacheManager::clearLegacyControllerCache();

        return true;
    }

    public function __construct()
    {
        parent::__construct();

        $this->mod_type = "core";
        $this->mod_name = "system";

        $this->makeRevision("0.0");

        $this->makeRevision("1.0.13");
        $this->addPrefQuery("touchscreen", "0");

        $this->makeRevision("1.0.14");
        $this->addPrefQuery("tooltipAppearenceTimeout", "medium");

        $this->makeRevision("1.0.15");
        $this->addPrefQuery("showLastUpdate", "0");

        $this->makeRevision("1.0.16");
        $query = "ALTER TABLE `message` 
      ADD INDEX (`module_id`),
      ADD INDEX (`deb`),
      ADD INDEX (`fin`);";
        $this->addQuery($query);

        $query = "ALTER TABLE  `modules` DROP PRIMARY KEY , ADD PRIMARY KEY (`mod_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `modules` 
      DROP `mod_directory`,
      DROP `mod_setup_class`,
      DROP `mod_ui_name`,
      DROP `mod_ui_icon`,
      DROP `mod_description`";
        $this->addQuery($query);

        $this->makeRevision("1.0.17");
        $this->addPrefQuery("showTemplateSpans", "0");

        $this->makeRevision("1.0.18");
        if (!$this->ds->hasField('message', 'group_id')) {
            $query = "ALTER TABLE `message` 
              ADD `group_id` INT (11) UNSIGNED,
              ADD INDEX (`group_id`);";
            $this->addQuery($query);
        }

        $this->makeRevision("1.0.19");
        $query = "ALTER TABLE `user_log` 
      ADD `ip_address` VARBINARY (16) NULL DEFAULT NULL,
      ADD `extra` TEXT,
      ADD INDEX (`ip_address`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.20");
        $query = "CREATE TABLE `alert` (
      `alert_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `tag` VARCHAR (255) NOT NULL,
      `level` ENUM ('low','medium','high') NOT NULL DEFAULT 'medium',
      `comments` TEXT,
      `handled` ENUM ('0','1') NOT NULL DEFAULT '0',
      `object_id` INT (11) UNSIGNED NOT NULL,
      `object_class` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `alert` 
      ADD INDEX (`object_id`),
      ADD INDEX (`object_class`),
      ADD INDEX (`tag`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.26");
        $query = "DELETE FROM `modules` 
      WHERE `mod_name` = 'dPinterop'";
        $this->addQuery($query, true);

        $this->makeRevision("1.0.27");
        $query = "DELETE FROM `modules` 
      WHERE `mod_name` = 'dPmateriel'";
        $this->addQuery($query, true);

        $this->makeRevision("1.0.28");
        $query = "CREATE TABLE IF NOT EXISTS `content_html` (
      `content_id` BIGINT NOT NULL auto_increment PRIMARY KEY,
      `content` TEXT,
      `cr_id` INT
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `content_xml` (
      `content_id` BIGINT NOT NULL auto_increment PRIMARY KEY,
      `content` TEXT,
      `import_id` INT
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.29");
        $query = "ALTER TABLE `content_html`
      CHANGE `content` `content` mediumtext NULL";
        $this->addQuery($query);

        $this->makeRevision("1.0.30");
        $this->addPrefQuery("directory_to_watch", "");

        $this->makeRevision("1.0.31");
        $this->addPrefQuery("debug_yoplet", "0");

        $this->makeRevision("1.0.32");
        $query = "ALTER TABLE `access_log` 
      ADD INDEX ( `period` )";
        $this->addQuery($query);

        $this->makeRevision("1.0.34");
        $query = "CREATE TABLE `source_smtp` (
      `source_smtp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `port` INT (11) DEFAULT '25',
      `email` VARCHAR (50),
      `ssl` ENUM ('0','1') DEFAULT '0',
      `name` VARCHAR  (255) NOT NULL,
      `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif',
      `host` TEXT NOT NULL,
      `user` VARCHAR  (255),
      `password` VARCHAR (50),
      `type_echange` VARCHAR  (255)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.35");
        $query = "CREATE TABLE `ex_class` (
      `host_class` VARCHAR (255) NOT NULL,
      `event` VARCHAR (255) NOT NULL,
      `ex_class_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_class_field` (
      `ex_class_field_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_id` INT (11) UNSIGNED NOT NULL,
      `name` VARCHAR (255) NOT NULL,
      `prop` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field` 
      ADD INDEX (`ex_class_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_class_constraint` (
      `ex_class_constraint_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_id` INT (11) UNSIGNED NOT NULL,
      `field` VARCHAR  (255) NOT NULL,
      `operator` ENUM ('=','!=','>','>=','<','<=','startsWith','endsWith','contains') NOT NULL DEFAULT '=',
      `value` VARCHAR  (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_constraint` 
      ADD INDEX (`ex_class_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_class_field_translation` (
      `ex_class_field_translation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_field_id` INT (11) UNSIGNED NOT NULL,
      `lang` CHAR  (2),
      `std` VARCHAR  (255),
      `desc` VARCHAR  (255),
      `court` VARCHAR  (255)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_translation` 
      ADD INDEX (`ex_class_field_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.36");
        $query = "CREATE TABLE `ex_class_field_enum_translation` (
      `ex_class_field_enum_translation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_field_id` INT (11) UNSIGNED NOT NULL,
      `lang` CHAR  (2),
      `key` VARCHAR  (40),
      `value` VARCHAR  (255)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_enum_translation` 
      ADD INDEX (`ex_class_field_id`),
      ADD INDEX (`lang`),
      ADD INDEX (`key`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.37");
        $query = "ALTER TABLE `ex_class` 
      ADD `name` VARCHAR  (255) NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_translation` 
      ADD INDEX (`lang`)";
        $this->addQuery($query);

        $this->makeRevision("1.0.38");
        $query = "ALTER TABLE `ex_class_field` 
      ADD `coord_field_x` TINYINT (4) UNSIGNED,
      ADD `coord_field_y` TINYINT (4) UNSIGNED,
      ADD `coord_label_x` TINYINT (4) UNSIGNED,
      ADD `coord_label_y` TINYINT (4) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.0.39");
        $query = "CREATE TABLE `ex_class_host_field` (
      `ex_class_host_field_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_id` INT (11) UNSIGNED NOT NULL,
      `field` VARCHAR (80) NOT NULL,
      `coord_label_x` TINYINT (4) UNSIGNED,
      `coord_label_y` TINYINT (4) UNSIGNED,
      `coord_value_x` TINYINT (4) UNSIGNED,
      `coord_value_y` TINYINT (4) UNSIGNED
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_host_field` 
      ADD INDEX (`ex_class_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.40");
        $query = "ALTER TABLE `ex_class_field` 
      CHANGE `ex_class_id` `ex_class_id` INT (11) UNSIGNED,
      ADD `concept_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field` 
      ADD INDEX (`concept_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.41");
        $query = "ALTER TABLE `ex_class` 
      ADD `disabled` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.0.42");
        $query = "CREATE TABLE `content_tabular` (
      `content_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `content` TEXT,
      `import_id` INT (11),
      `separator` CHAR (1)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.43");
        $query = "CREATE TABLE `tag` (
      `tag_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `parent_id` INT (11) UNSIGNED,
      `object_class` VARCHAR (80),
      `name` VARCHAR (255) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tag` 
      ADD INDEX (`parent_id`),
      ADD INDEX (`object_class`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `tag_item` (
      `tag_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `tag_id` INT (11) UNSIGNED NOT NULL,
      `object_id` INT (11) UNSIGNED NOT NULL,
      `object_class` VARCHAR (80) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tag_item` 
      ADD INDEX (`tag_id`),
      ADD INDEX (`object_id`),
      ADD INDEX (`object_class`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.44");
        $query = "ALTER TABLE `tag` 
      ADD `color` VARCHAR (20);";
        $this->addQuery($query);

        $this->makeRevision("1.0.45");
        $query = "CREATE TABLE `ex_list` (
      `ex_list_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `name` VARCHAR (255) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_list_item` (
      `ex_list_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `list_id` INT (11) UNSIGNED NOT NULL,
      `name` VARCHAR (255) NOT NULL,
      `value` INT (11)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_list_item` 
      ADD INDEX (`list_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_concept` (
      `ex_concept_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_list_id` INT (11) UNSIGNED,
      `name` VARCHAR (255) NOT NULL,
      `prop` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_concept` 
      ADD INDEX (`ex_list_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.46");
        $this->addPrefQuery("pdf_and_thumbs", "1");

        $this->makeRevision("1.0.47");
        $query = "ALTER TABLE `ex_list` 
      ADD `coded` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_list_item` 
      ADD `code` CHAR (20);";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_list_item` 
      DROP `value`";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_list_item` 
      CHANGE `list_id` `list_id` INT (11) UNSIGNED,
      ADD `concept_id` INT (11) UNSIGNED,
      ADD `field_id` INT (11) UNSIGNED";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_list_item` 
      ADD INDEX (`concept_id`),
      ADD INDEX (`field_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.48");
        $query = "CREATE TABLE `ex_class_field_group` (
      `ex_class_field_group_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_id` INT (11) UNSIGNED,
      `name` VARCHAR (255) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_group` 
      ADD INDEX (`ex_class_id`);";
        $this->addQuery($query);
        $query = "INSERT INTO `ex_class_field_group` (`name`, `ex_class_id`)
      SELECT 'Groupe principal', `ex_class`.`ex_class_id` FROM `ex_class`";
        $this->addQuery($query);

        // class field
        $query = "ALTER TABLE `ex_class_field` 
      ADD `ex_group_id` INT (11) UNSIGNED";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field` 
      ADD INDEX (`ex_group_id`)";
        $this->addQuery($query);
        $query = "UPDATE `ex_class_field` 
      SET `ex_group_id` = (
        SELECT `ex_class_field_group`.`ex_class_field_group_id` 
        FROM `ex_class_field_group` 
        WHERE `ex_class_field_group`.`ex_class_id` = `ex_class_field`.`ex_class_id`
        LIMIT 1
      )";
        $this->addQuery($query);

        // class host field
        $query = "ALTER TABLE `ex_class_host_field` 
      ADD `ex_group_id` INT (11) UNSIGNED";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_host_field` 
      ADD INDEX (`ex_group_id`)";
        $this->addQuery($query);
        $query = "UPDATE `ex_class_host_field` 
      SET `ex_group_id` = (
        SELECT `ex_class_field_group`.`ex_class_field_group_id` 
        FROM `ex_class_field_group` 
        WHERE `ex_class_field_group`.`ex_class_id` = `ex_class_host_field`.`ex_class_id`
        LIMIT 1
      )";
        $this->addQuery($query);

        $this->makeRevision("1.0.49");
        $query = "ALTER TABLE `ex_class_field` 
      CHANGE `prop` `prop` TEXT NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("1.0.50");
        $query = "CREATE TABLE `source_file_system` (
        `source_file_system_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `name` VARCHAR (255) NOT NULL,
        `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif',
        `host` TEXT NOT NULL,
        `user` VARCHAR (255),
        `password` VARCHAR (50),
        `type_echange` VARCHAR (255)
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.51");

        $this->addMethod("updateExObjectTables");

        $this->makeRevision("1.0.52");
        $query = "CREATE TABLE `view_sender` (
      `sender_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `source_id` INT (11) UNSIGNED,
      `name` VARCHAR (255) NOT NULL,
      `description` TEXT,
      `params` TEXT NOT NULL,
      `period` ENUM ('1','2','3','4','5','6','10','15','20','30'),
      `offset` INT (11) UNSIGNED
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_sender` 
      ADD INDEX (`source_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `view_sender_source` (
      `source_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `name` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_sender` 
      ADD INDEX (`source_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.53");

        $query = "ALTER TABLE `source_smtp` 
      ADD `active` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_file_system` 
      ADD `active` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.0.54");

        $query = "ALTER TABLE `view_sender` 
      ADD `active` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.55");

        $query = "ALTER TABLE `view_sender` 
      CHANGE `offset` `offset` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.56");
        $query = "ALTER TABLE `ex_class` 
      ADD `conditional` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.57");
        $query = "CREATE TABLE `ex_class_field_trigger` (
      `ex_class_field_trigger_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_field_id` INT (11) UNSIGNED NOT NULL,
      `ex_class_triggered_id` INT (11) UNSIGNED NOT NULL,
      `trigger_value` VARCHAR (255) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_trigger` 
      ADD INDEX (`ex_class_field_id`),
      ADD INDEX (`ex_class_triggered_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.58");
        $query = "ALTER TABLE `ex_class_field_group` 
      ADD `formula` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.0.59");
        $query = "ALTER TABLE `ex_class_field_group` 
      ADD `formula_result_field_id` INT (11) UNSIGNED";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_group` 
      ADD INDEX (`formula_result_field_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.60");
        $query = "ALTER TABLE `ex_class_field`
      ADD `formula` TEXT;";
        $this->addQuery($query);
        $query = "UPDATE `ex_class_field` 
      LEFT JOIN `ex_class_field_group` ON `ex_class_field_group`.`formula_result_field_id` = `ex_class_field`.`ex_class_field_id`
      SET `ex_class_field`.`formula` = `ex_class_field_group`.`formula`";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_group` 
      DROP `formula`, 
      DROP `formula_result_field_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.0.61");
        $query = "ALTER TABLE `ex_list` 
      ADD `multiple` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.62");
        $query = "ALTER TABLE `ex_class` 
      ADD `required` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.63");
        $query = "CREATE TABLE `http_redirection` (
       `http_redirection_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
       `priority` INT (11) NOT NULL DEFAULT '0',
       `from` VARCHAR (255) NOT NULL,
       `to` VARCHAR (255) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.64");
        $query = "CREATE TABLE `ex_class_message` (
      `ex_class_message_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_group_id` INT (11) UNSIGNED NOT NULL,
      `type` ENUM ('info','warning','error'),
      `title` VARCHAR (255) NOT NULL,
      `text` TEXT NOT NULL,
      `coord_title_x` TINYINT (4) UNSIGNED,
      `coord_title_y` TINYINT (4) UNSIGNED,
      `coord_text_x` TINYINT (4) UNSIGNED,
      `coord_text_y` TINYINT (4) UNSIGNED
      ) /*! ENGINE=MyISAM */";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_message` 
      ADD INDEX (`ex_group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.65");
        $this->addMethod("addExReferenceFields");
        $query = "ALTER TABLE `ex_class_field`
      ADD `reported` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.0.66");
        $query = "ALTER TABLE `ex_class_message` 
      CHANGE `type` `type` ENUM ('title','info','warning','error');";
        $this->addQuery($query);

        $this->makeRevision("1.0.67");

        $this->addMethod("addExReferenceFields2");

        $query = "ALTER TABLE `ex_class_field` 
      CHANGE `reported` `report_level` ENUM ('1','2')";
        $this->addQuery($query);

        $this->makeRevision("1.0.68");
        $this->addPrefQuery("autocompleteDelay", "short");

        $this->makeRevision("1.0.69");

        $query = "ALTER TABLE `view_sender_source` 
        ADD `libelle` VARCHAR (255),
        ADD `group_id` INT (11) UNSIGNED NOT NULL,
        ADD `actif` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_sender_source` 
        ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.70");

        $query = "ALTER TABLE `view_sender` 
        DROP `source_id`;";
        $this->addQuery($query);

        $query = "CREATE TABLE `source_to_view_sender` (
        `source_to_view_sender_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `source_id` INT (11) UNSIGNED NOT NULL,
        `sender_id` INT (11) UNSIGNED NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.71");

        $query = "ALTER TABLE `view_sender` 
      ADD `max_archives` INT (11) UNSIGNED NOT NULL DEFAULT '10';";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_to_view_sender` 
      ADD `last_datetime` DATETIME,
      ADD `last_status` ENUM ('triggered','uploaded','checked'),
      ADD `last_count` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_to_view_sender` 
      ADD INDEX (`last_datetime`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.72");

        $query = "ALTER TABLE `source_to_view_sender` 
      ADD `last_duration` FLOAT,
      ADD `last_size` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.0.73");

        $this->addPrefQuery("moduleFavicon", "0");

        $this->makeRevision("1.0.74");

        $this->addPrefQuery("showCounterTip", 1);

        $this->makeRevision("1.0.75");

        $query = "ALTER TABLE `access_log`
      ADD `processus` FLOAT,
      ADD `processor` FLOAT,
      ADD `peak_memory` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.0.76");
        $query = "ALTER TABLE `note` 
      CHANGE `degre` `degre` ENUM ('low','medium','high') NOT NULL DEFAULT 'low',
      CHANGE `object_class` `object_class` VARCHAR (80) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.0.77");
        $query = "ALTER TABLE `note` 
      CHANGE `user_id` `user_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.0.78");
        $query = "CREATE TABLE `content_any` (
        `content_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `content` TEXT,
        `import_id` INT (11)
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.79");
        $query = "CREATE TABLE `session` (
       `session_id` VARCHAR(32) NOT NULL PRIMARY KEY,
       `date_creation` INT(11),
       `date_modification` INT(11),
       `user_id` INT (11) NOT NULL DEFAULT '0',
       `user_ip` VARBINARY (16),
       `user_agent` VARCHAR(100),
       `data` BLOB
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.0.80");
        $query = "ALTER TABLE `ex_class` 
      ADD `unicity` ENUM ('no','host','reference1','reference2') NOT NULL DEFAULT 'no';";
        $this->addQuery($query);

        $this->makeRevision("1.0.81");

        $query = "ALTER TABLE `source_smtp` 
      ADD `loggable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_file_system` 
      ADD `loggable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeEmptyRevision("1.0.82");

        $this->makeRevision("1.0.83");
        $query = "ALTER TABLE `ex_class` 
      ADD `group_id` INT (11) UNSIGNED,
      ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.84");
        $this->addPrefQuery("textareaToolbarPosition", "right");

        $this->makeRevision("1.0.85");
        $query = "ALTER TABLE `view_sender_source` 
      ADD `archive` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.0.86");
        $query = "ALTER TABLE `view_sender` 
      CHANGE `period` `period` ENUM ('1','2','3','4','5','6','10','15','20','30','60');";
        $this->addQuery($query);

        $this->makeRevision("1.0.87");
        $query = "ALTER TABLE `source_file_system` 
      ADD `fileextension` VARCHAR (255)";
        $this->addQuery($query);

        $this->makeRevision("1.0.88");
        $query = "ALTER TABLE `source_smtp` 
      ADD `timeout` INT (11) DEFAULT '5',
      ADD `debug` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        /*
        $query = "ALTER TABLE `ex_class_field`
          ADD `coord_field_colspan` TINYINT (4) UNSIGNED NOT NULL DEFAULT '1' AFTER `coord_field_y`,
          ADD `coord_field_rowspan` TINYINT (4) UNSIGNED NOT NULL DEFAULT '1' AFTER `coord_field_colspan`";
        $this->addQuery($query);
        */

        $this->makeRevision("1.0.89");
        $query = "ALTER TABLE `source_file_system` 
      ADD `fileextension_write_end` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.0.90");
        $query = "ALTER TABLE `ex_class_field` 
      CHANGE `report_level` `report_level` ENUM ('1','2','host')";
        $this->addQuery($query);

        $this->makeRevision("1.0.91");
        $query = "ALTER TABLE `ex_class_constraint` 
      ADD INDEX (`field`)";
        $this->addQuery($query);

        $this->addMethod("addExReferenceFieldsIndex");

        $this->makeRevision("1.0.92");

        $query = "CREATE TABLE `sender_file_system` (
        `sender_file_system_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `user_id` INT (11) UNSIGNED,
        `nom` VARCHAR (255) NOT NULL,
        `libelle` VARCHAR (255),
        `group_id` INT (11) UNSIGNED NOT NULL,
        `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sender_file_system` 
      ADD INDEX (`user_id`),
      ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.0.93");
        $query = "ALTER TABLE `ex_class_host_field` 
      ADD `host_type` ENUM ('host','reference1','reference2') DEFAULT 'host'";
        $this->addQuery($query);

        $this->makeRevision("1.0.94");
        $query = "ALTER TABLE `ex_class_message` 
      CHANGE `title` `title` VARCHAR (255)";
        $this->addQuery($query);

        $this->makeRevision("1.0.95");
        $query = "ALTER TABLE `sender_file_system` 
        ADD `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
        ADD `create_ack_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.0.96");

        $this->addMethod("addExObjectGroupId");

        $this->makeRevision("1.0.97");
        $query = "ALTER TABLE `view_sender` 
      ADD `last_duration` FLOAT,
      ADD `last_size` INT (11) UNSIGNED;";
        $this->addQuery($query);

        if ($this->tableExists('configuration')) {
            $this->makeEmptyRevision("1.0.98");
        } else {
            $this->makeRevision("1.0.98");
            $query = "CREATE TABLE `configuration` (
      `configuration_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `feature` VARCHAR (255) NOT NULL,
      `value` VARCHAR (255),
      `object_id` INT (11) UNSIGNED,
      `object_class` VARCHAR (80)
      ) /*! ENGINE=MyISAM */;";
            $this->addQuery($query);

            $query = "ALTER TABLE `configuration` 
      ADD INDEX (`object_id`, `object_class`),
      ADD UNIQUE (`feature`, `object_id`, `object_class`);";
            $this->addQuery($query);
        }

        $this->makeRevision("1.0.99");
        $this->addPrefQuery("MobileUI", 0);

        $this->makeRevision("1.1.00");
        $query = "ALTER TABLE `source_smtp` 
      ADD `auth` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.1.0");
        $query = "CREATE TABLE `ex_class_field_predicate` (
      `ex_class_field_predicate_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_field_id` INT (11) UNSIGNED NOT NULL,
      `operator` ENUM ('=','!=','>','>=','<','<=','startsWith','endsWith','contains') NOT NULL DEFAULT '=',
      `value` VARCHAR (255) NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_predicate` 
      ADD INDEX (`ex_class_field_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field` 
      ADD `predicate_id` INT (11) UNSIGNED,
      ADD INDEX (`predicate_id`)";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class` 
      ADD `native_views` VARCHAR(255),
      ADD INDEX (`native_views`)";
        $this->addQuery($query);

        $this->makeRevision("1.1.01");
        $this->addPrefQuery("MobileDefaultModuleView", 1);

        $this->makeRevision("1.1.02");
        $query = "ALTER TABLE `ex_class_field_group` 
      ADD `rank` TINYINT (4) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.1.03");

        $query = "ALTER TABLE `sender_file_system` 
        ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.1.04");
        $query = "ALTER TABLE `ex_class_field_predicate`
      CHANGE `operator` `operator`
        ENUM ('=','!=','>','>=','<','<=','startsWith','endsWith','contains','hasValue') NOT NULL DEFAULT '=';";
        $this->addQuery($query);

        $this->makeRevision("1.1.05");
        $query = "CREATE TABLE `datasource_log` (
      `datasourcelog_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
      `datasource` VARCHAR( 40 ) NOT NULL ,
      `requests` INT (11) UNSIGNED NOT NULL,
      `duration` FLOAT NOT NULL ,
      `accesslog_id` INT UNSIGNED NOT NULL ,
      PRIMARY KEY ( `datasourcelog_id` )) /*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision("1.1.06");
        $query = "DELETE FROM `datasource_log`";
        $this->addQuery($query);
        $query = "ALTER TABLE `datasource_log` 
        ADD UNIQUE `doublon` (`datasource` , `accesslog_id`)";
        $this->addQuery($query);

        $this->makeRevision("1.1.07");
        $query = "CREATE TABLE `source_http` (
      `source_http_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `name` VARCHAR (255) NOT NULL,
      `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif',
      `host` TEXT NOT NULL,
      `user` VARCHAR (255),
      `password` VARCHAR (50),
      `type_echange` VARCHAR (255),
      `active` ENUM ('0','1') NOT NULL DEFAULT '1',
      `loggable` ENUM ('0','1') NOT NULL DEFAULT '1'
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.08");
        $query = "ALTER TABLE `source_file_system` 
        ADD `fileprefix` VARCHAR (255),
        ADD `sort_files_by` ENUM ('date','name','size') DEFAULT 'name';";
        $this->addQuery($query);

        $this->makeRevision("1.1.09");
        $query = "CREATE TABLE `echange_http` (
        `echange_http_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `http_fault` ENUM ('0','1') DEFAULT '0',
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

        $query = "ALTER TABLE `echange_http` 
        ADD INDEX (`date_echange`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.10");
        $query = "CREATE TABLE `view_access_token` (
      `view_access_token_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `user_id` INT (11) UNSIGNED NOT NULL,
      `datetime_start` DATETIME NOT NULL,
      `ttl_hours` INT (11) UNSIGNED NOT NULL,
      `params` VARCHAR (255) NOT NULL,
      `hash` CHAR (40) NOT NULL
             ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `view_access_token` 
      ADD INDEX (`user_id`),
      ADD INDEX (`datetime_start`),
      ADD INDEX (`hash`);";
        $this->addQuery($query);

        // Moved to "admin"
        $this->makeRevision("1.1.11");
        $query = "DROP TABLE `view_access_token`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.12");
        $this->addPrefQuery("notes_anonymous", "0");

        $this->makeRevision("1.1.13");
        $query = "CREATE TABLE `ex_class_event` (
      `ex_class_event_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ex_class_id` INT (11) UNSIGNED NOT NULL,
      `host_class` VARCHAR (255) NOT NULL,
      `event_name` VARCHAR (255) NOT NULL,
      `disabled` ENUM ('0','1') NOT NULL DEFAULT '1',
      `unicity` ENUM ('no','host') NOT NULL DEFAULT 'no'
             ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_event` 
        ADD INDEX (`ex_class_id`), 
        ADD INDEX (`host_class`), 
        ADD INDEX (`event_name`), 
        ADD INDEX (`disabled`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_constraint` 
        ADD `ex_class_event_id` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field` 
        ADD `report_class` VARCHAR(80) AFTER `report_level`";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_host_field` 
        ADD `host_class` VARCHAR(80) AFTER `host_type`";
        $this->addQuery($query);

        $this->makeRevision("1.1.14");

        $this->addMethod("createExClassEvents");

        $this->makeRevision("1.1.15");
        $query = "CREATE TABLE `ex_class_field_property` (
        `ex_class_field_property_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `object_class` VARCHAR (80),
        `object_id` INT (11) UNSIGNED NOT NULL,
        `type` VARCHAR (60),
        `value` VARCHAR (255),
        `predicate_id` INT (11) UNSIGNED
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_property`
        ADD INDEX (`object_class`),
        ADD INDEX (`object_id`),
        ADD INDEX (`predicate_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.16");
        $query = "ALTER TABLE `ex_class_field` 
        ADD `prefix` VARCHAR (255),
        ADD `suffix` VARCHAR (255)";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field` 
        ADD `coord_left` INT (11) AFTER `coord_label_x`,
        ADD `coord_top` INT (11) AFTER `coord_left`,
        ADD `coord_width` INT (11) UNSIGNED AFTER `coord_top`,
        ADD `coord_height` INT (11) UNSIGNED AFTER `coord_width`,
        ADD `subgroup_id` INT (11) UNSIGNED AFTER `ex_group_id`,
        ADD `show_label` ENUM ('0','1') NOT NULL DEFAULT '1',
        ADD `tab_index` INT (11)";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class` 
        ADD `pixel_positionning` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_message` 
        ADD `coord_left` INT (11),
        ADD `coord_top` INT (11),
        ADD `coord_width` INT (11) UNSIGNED,
        ADD `coord_height` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_host_field` 
        ADD `coord_left` INT (11),
        ADD `coord_top` INT (11),
        ADD `coord_width` INT (11) UNSIGNED,
        ADD `coord_height` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_class_field_subgroup` (
        `ex_class_field_subgroup_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `parent_class` ENUM ('CExClassFieldGroup','CExClassFieldSubgroup') NOT NULL,
        `parent_id` INT (11) UNSIGNED NOT NULL,
        `title` VARCHAR (255),
        `coord_left` INT (11),
        `coord_top` INT (11),
        `coord_width` INT (11) UNSIGNED,
        `coord_height` INT (11) UNSIGNED
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_subgroup` 
        ADD INDEX (`parent_class`),
        ADD INDEX (`parent_id`)";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_message`
        ADD `subgroup_id` INT (11) UNSIGNED AFTER `ex_group_id`";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_message`
        ADD `predicate_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_message`
        ADD INDEX (`predicate_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_subgroup`
        ADD `predicate_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_subgroup`
        ADD INDEX (`predicate_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.17");
        $query = "ALTER TABLE `content_tabular`
        CHANGE `content` `content` LONGTEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.1.18");
        $query = "ALTER TABLE `ex_class_field_predicate`
      CHANGE `operator` `operator`
        ENUM ('=','!=','>','>=','<','<=','startsWith','endsWith','contains','hasValue','hasNoValue') NOT NULL DEFAULT '=';";
        $this->addQuery($query);

        $this->makeRevision("1.1.19");

        $query = "CREATE TABLE `source_pop` (
        `source_pop_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `name` VARCHAR (255) NOT NULL,
        `user` VARCHAR (255) NOT NULL,
        `password` VARCHAR (255) NOT NULL,
        `role` ENUM ('prod', 'qualif') NOT NULL DEFAULT 'qualif',
        `type` ENUM ('pop3','imap') NOT NULL DEFAULT 'imap',
        `active` ENUM ('0','1') NOT NULL DEFAULT '1',
        `loggable` ENUM ('0','1') NOT NULL DEFAULT '1',
        `port` INT (11) NOT NULL,
        `host` VARCHAR (50) NOT NULL,
        `auth_ssl` ENUM ('None','SSL/TLS','STARTTLS') NOT NULL,
        `timeout` INT NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.20");

        $query = "ALTER TABLE `source_pop`
        ADD `libelle` VARCHAR (255) NOT NULL AFTER `name`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.21");
        $query = "ALTER TABLE `source_pop`
        ADD `object_class` VARCHAR (80) NOT NULL,
        ADD `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.22");
        $query = "CREATE TABLE `translation` (
      `translation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `source` VARCHAR (255) NOT NULL,
      `translation` TEXT NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.23");
        $query = "ALTER TABLE `translation`
      ADD `language` CHAR (2) NOT NULL DEFAULT 'fr';";
        $this->addQuery($query);

        $this->makeRevision("1.1.24");
        $query = "ALTER TABLE `source_pop`
      ADD `last_update` DATETIME,
      ADD `type_echange` VARCHAR (255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `source_pop`
      ADD INDEX (`last_update`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.25");
        $query = "ALTER TABLE `source_smtp`
        CHANGE `password` `password` VARCHAR (255),
        ADD `iv` VARCHAR (16) AFTER `password`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_file_system`
        CHANGE `password` `password` VARCHAR (255),
        ADD `iv` VARCHAR (16) AFTER `password`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_http`
        CHANGE `password` `password` VARCHAR (255),
        ADD `iv` VARCHAR (16) AFTER `password`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_pop`
        CHANGE `password` `password` VARCHAR (255),
        ADD `iv` VARCHAR (16) AFTER `password`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.26");
        $query = "ALTER TABLE `ex_class_field`
        ADD `result_in_title` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.27");
        $query = "ALTER TABLE `ex_concept`
        ADD `native_field` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.1.28");
        $query = "CREATE TABLE `config_db` (
      `key` VARCHAR (255) PRIMARY KEY,
      `value` VARCHAR(255)
      )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.29");
        $this->addPrefQuery("sessionLifetime", "");

        $this->makeRevision("1.1.30");
        $this->addPrefQuery("planning_dragndrop", "0");
        $this->addPrefQuery("planning_resize", "0");

        $this->makeRevision("1.1.31");
        $query = "UPDATE `user_preferences`
      SET `value` = 'aero-blue'
      WHERE `key` = 'UISTYLE'
      AND `value` = 'aero'";
        $this->addQuery($query, true);

        $this->makeRevision("1.1.32");
        $query = "DELETE user_preferences
      FROM user_preferences
      LEFT JOIN users ON users.user_id = user_preferences.user_id
      WHERE user_preferences.user_id IS NOT NULL AND user_preferences.user_id <> '0'
      AND  users.user_id IS NULL";
        $this->addQuery($query, true);

        $this->makeRevision("1.1.33");
        $query = "ALTER TABLE `session`
      DROP `date_creation`,
      DROP `date_modification`,
      ADD `expire` INT(11) NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.1.34");
        $query = "ALTER TABLE `session`
      CHANGE `data` `data` LONGBLOB";
        $this->addQuery($query);

        $this->makeRevision("1.1.35");
        $query = "CREATE TABLE `long_request_log` (
        `long_request_log_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `datetime`            DATETIME NOT NULL,
        `duration`            FLOAT UNSIGNED NOT NULL,
        `server_addr`         VARCHAR (255) NOT NULL,
        `user_id`             INT (11) UNSIGNED NOT NULL,
        `query_params_get`    TEXT,
        `query_params_post`   TEXT,
        `session_data`        TEXT
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `long_request_log`
        ADD INDEX (`datetime`),
        ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.36");
        $this->addPrefQuery("accessibility_dyslexic", "0");

        $this->makeRevision("1.1.37");
        $query = "ALTER TABLE `view_sender`
      CHANGE `period` `period` ENUM ('1','2','3','4','5','6','10','15','20','30','60') NOT NULL DEFAULT '30',
      ADD `every` ENUM ('1','2','3','4','6','8','12','24') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_sender` 
      ADD INDEX (`name`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.38");
        $query = "ALTER TABLE `ex_class_field`
                ADD `disabled` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.39");
        $query = "ALTER TABLE `long_request_log`
                ADD `requestUID` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `long_request_log`
                ADD INDEX (`requestUID`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.40");
        $this->addPrefQuery("planning_hour_division", "2");

        // Cration des deux nouveaux champs
        $this->makeRevision("1.1.41");

        $query = "ALTER TABLE `access_log`
      ADD `aggregate` INT(11) UNSIGNED NOT NULL DEFAULT '10',
      ADD `bot` BOOL NOT NULL DEFAULT 0;";
        $this->addQuery($query);

        // Mise  jour du champ
        $query = "UPDATE `access_log`
      SET `aggregate` = '60';";
        $this->addQuery($query);

        /**
         * Suppression de l'index UNIQUE triplet
         * Cration d'un index unique portant sur le prcdent triplet + l'agrgat et le boolen bot
         * Cration d'un simple index triplet
         */
        $query = "ALTER TABLE `access_log`
      DROP INDEX `triplet`,
      ADD UNIQUE `aggregate` (`module`, `action`, `period`, `aggregate`, `bot`),
      ADD INDEX `triplet` (`module`, `action`, `period`);";
        $this->addQuery($query);

        // Ajout de l'index sur l'ID du journal d'accs concern
        $query = "ALTER TABLE `datasource_log`
      ADD INDEX `agregat` (`accesslog_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.42");
        $this->addMethod("addExObjectAdditionalObject");

        $this->makeRevision("1.1.43");
        $query = "CREATE TABLE `error_log` (
                `error_log_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED,
                `server_ip` VARCHAR (80),
                `datetime` DATETIME NOT NULL,
                `request_uid` VARCHAR (255),
                `error_type` ENUM (
                  'exception','error','warning','parse','notice','core_error','core_warning','compile_error',
                  'compile_warning','user_error','user_warning','user_notice','strict','recoverable_error','deprecated',
                  'user_deprecated','js_error'
                 ),
                `text` TEXT,
                `file_name` VARCHAR (255),
                `line_number` INT (11),

                `stacktrace_id` INT (11) UNSIGNED,
                `param_GET_id` INT (11) UNSIGNED,
                `param_POST_id` INT (11) UNSIGNED,
                `session_data_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `error_log`
                ADD INDEX (`user_id`),
                ADD INDEX (`server_ip`),
                ADD INDEX (`datetime`),
                ADD INDEX (`error_type`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `error_log_data` (
                `error_log_data_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `value` LONGTEXT NOT NULL,
                `value_hash` CHAR(32) NOT NULL,
                UNIQUE (`value_hash`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.44");
        $query = "ALTER TABLE `source_pop`
                CHANGE `port` `port` INT (11) DEFAULT '25',
                CHANGE `timeout` `timeout` TINYINT (4) DEFAULT '5',
                CHANGE `type` `type` ENUM ('pop3','imap'),
                ADD `extension` VARCHAR (255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `source_pop`
                ADD INDEX (`object_class`),
                ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.45");
        $query = "ALTER TABLE `source_pop`
                ADD `cron_update` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.1.46");
        $query = "ALTER TABLE `ex_class_field`
                ADD `in_doc_template` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.47");
        $query = "ALTER TABLE `error_log`
                ADD `signature_hash` CHAR(32);";
        $this->addQuery($query);

        $this->makeRevision("1.1.48");
        $query = "ALTER TABLE `source_pop`
                ADD `is_private` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.49");
        $query = "ALTER TABLE `content_html`
      ADD `last_modified` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.1.50");
        $query = "ALTER TABLE `access_log`
      ADD `nb_requests` INT (11) AFTER `request`;";
        $this->addQuery($query);
        $query = "UPDATE `access_log`, `datasource_log`
      SET `access_log`.`nb_requests` = `datasource_log`.`requests`
      WHERE `access_log`.`accesslog_id` = `datasource_log`.`accesslog_id`
        AND `datasource_log`.`datasource` = 'std';";
        $this->addQuery($query);

        $this->makeRevision("1.1.51");
        $this->addPrefQuery("useEditAutocompleteUsers", 1);

        // Meilleurs index pour les notes
        $this->makeRevision("1.1.52");
        $query = "ALTER TABLE `note`
      DROP INDEX `user_id`,
      ADD INDEX (`user_id`),
      ADD INDEX  `object_guid` (`object_id`, `object_class`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.53");
        $query = "CREATE TABLE `ex_link` (
                `ex_link_id`   INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ex_class_id`  INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `ex_object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `level` ENUM('object', 'ref1', 'ref2', 'add') NOT NULL DEFAULT 'object',
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` VARCHAR (80) NOT NULL,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                INDEX (`ex_class_id`),
                INDEX (`ex_object_id`),
                INDEX (`group_id`),
                INDEX `object` (`object_class`, `object_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->addMethod("buildExLink");

        $this->makeRevision("1.1.54");
        $query = "ALTER TABLE `ex_class_constraint`
      CHANGE `operator` `operator` ENUM ('=','!=','>','>=','<','<=','startsWith','endsWith','contains','in') NOT NULL DEFAULT '=',
      CHANGE `value` `value` TEXT NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.1.55");
        $this->addPrefQuery("useMobileSwipe", "0");
        $this->addPrefQuery("MobileDefaultTheme", "a");

        $this->makeRevision("1.1.56");
        $query = "ALTER TABLE `ex_class_field`
                ADD `readonly` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.57");
        $this->addMethod("removeZombieExLinks");

        $this->makeRevision("1.1.58");
        $query = "CREATE TABLE `firstname_to_gender` (
                `first_name_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `firstname`  VARCHAR (255) NOT NULL ,
                `sex` VARCHAR (10) NOT NULL DEFAULT 'u')/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.59");
        $query = "CREATE TABLE `user_agent` (
                `user_agent_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_agent_string` VARCHAR (255) NOT NULL,
                `browser_name` VARCHAR (30),
                `browser_version` VARCHAR (10),
                `platform_name` VARCHAR (30),
                `platform_version` VARCHAR (10),
                `device_name` VARCHAR (30),
                `device_maker` VARCHAR (30),
                `device_type` ENUM ('desktop','mobile','tablet','unknown') NOT NULL DEFAULT 'unknown',
                `pointing_method` ENUM ('mouse','touchscreen','unknown') NOT NULL DEFAULT 'unknown',
                INDEX (`user_agent_string`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `user_authentication` (
                `user_authentication_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `previous_user_id` INT (11) UNSIGNED,
                `auth_method` ENUM ('basic','ldap','ldap_guid','token'),
                `datetime_login` DATETIME NOT NULL,
                `datetime_logout` DATETIME,
                `id_address` CHAR (39) NOT NULL,
                `session_id` CHAR (32) NOT NULL,
                `screen_width` SMALLINT (5),
                `screen_height` SMALLINT (5),
                `user_agent_id` INT (11) UNSIGNED,
                INDEX(`user_id`),
                INDEX(`datetime_login`),
                INDEX(`user_agent_id`),
                INDEX(`session_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "INSERT INTO `user_authentication` (`user_id`, `auth_method`, `datetime_login`)
                SELECT `user_id`, 'basic', `user_last_login` FROM `users` WHERE `user_last_login` IS NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("1.1.60");
        $query = "ALTER TABLE `ex_class_host_field`
                ADD `type` ENUM ('label','value');";
        $this->addQuery($query);

        $this->makeRevision("1.1.61");
        $this->addMethod("addExObjectDates");

        $this->makeRevision("1.1.62");
        $query = "ALTER TABLE `firstname_to_gender`
                CHANGE `sex` `sex` ENUM ('f','m','u') NOT NULL DEFAULT 'u',
                ADD `language` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.1.63");
        $query = "ALTER TABLE `config_db`
      CHANGE `value` `value` VARCHAR(1024);";
        $this->addQuery($query);

        $this->makeRevision("1.1.64");
        $query = "ALTER TABLE `alert`
      ADD `creation_date` DATETIME,
      ADD `handled_date` DATETIME,
      ADD `handled_user_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.1.65");

        $query = "ALTER TABLE `source_smtp`
        ADD `libelle` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_file_system`
        ADD `libelle` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_http`
        ADD `libelle` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.1.66");
        $query = "ALTER TABLE `view_sender` 
                ADD `multipart` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.67");

        $query = "CREATE TABLE `cronjob` (
                `cronjob_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `active` ENUM ('0','1') NOT NULL DEFAULT '1',
                `params` TEXT NOT NULL,
                `execution` VARCHAR (255) NOT NULL,
                `cron_login` VARCHAR (20) NOT NULL,
                `cron_password` VARCHAR (50)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `cronjob_log` (
                `cronjob_log_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `status` ENUM ('started','finished','error') NOT NULL,
                `error` VARCHAR (255),
                `cronjob_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `start_datetime` DATETIME NOT NULL,
                `end_datetime` DATETIME
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `cronjob_log`
                ADD INDEX (`cronjob_id`),
                ADD INDEX (`start_datetime`),
                ADD INDEX (`end_datetime`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.68");
        $query = "CREATE TABLE `module_action` (
                `module_action_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `module` VARCHAR(255) NOT NULL,
                `action` VARCHAR(255) NOT NULL,
                UNIQUE `module_action` (`module`, `action`)
                )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `access_log`
              ADD `module_action_id` INT(11) UNSIGNED NOT NULL AFTER `accesslog_id`,
              ADD INDEX (`module_action_id`);";
        $this->addQuery($query);

        $query = "INSERT IGNORE INTO `module_action` (`module`, `action`)
              SELECT DISTINCT `module`, `action`
              FROM `access_log`;";
        $this->addQuery($query);

        $query = "UPDATE `access_log`
              SET `module_action_id` = (
                SELECT `ma`.`module_action_id`
                FROM `module_action` as `ma`
                WHERE `ma`.`module` = `access_log`.`module`
                  AND `ma`.`action` = `access_log`.`action`
              );";
        $this->addQuery($query);

        $query = "ALTER TABLE `access_log`
                DROP INDEX `aggregate`,
                DROP INDEX `triplet`,
                DROP COLUMN `module`,
                DROP COLUMN `action`,
                ADD UNIQUE `aggregate` (`module_action_id`, `period`, `aggregate`, `bot`),
                ADD INDEX `triplet` (`module_action_id`, `period`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.69");

        $query = "ALTER TABLE `datasource_log`
                ADD COLUMN `module_action_id` INT(11) UNSIGNED NOT NULL AFTER `datasourcelog_id`,
                CHANGE     `datasource` `datasource` CHAR(20) NOT NULL,
                ADD COLUMN `period`           DATETIME NOT NULL,
                ADD COLUMN `aggregate`        INT(11) UNSIGNED NOT NULL DEFAULT '10',
                ADD COLUMN `bot`              BOOL NOT NULL DEFAULT 0,
                ADD INDEX (`module_action_id`),
                ADD INDEX (`period`);";
        $this->addQuery($query);

        $query = "UPDATE `datasource_log`
              JOIN `access_log` ON `access_log`.`accesslog_id` = `datasource_log`.`accesslog_id`
              SET `datasource_log`.`module_action_id` = `access_log`.`module_action_id`,
                  `datasource_log`.`period`           = `access_log`.`period`,
                  `datasource_log`.`aggregate`        = `access_log`.`aggregate`,
                  `datasource_log`.`bot`              = `access_log`.`bot`;";
        $this->addQuery($query);

        // Purge of orphan datasource logs
        $query = "DELETE FROM `datasource_log`
              WHERE `accesslog_id` = '0'
                OR `period` = '0000-00-00 00:00:00';";
        $this->addQuery($query);

        $query = "ALTER TABLE `datasource_log`
                DROP INDEX `doublon`,
                DROP INDEX `agregat`,
                DROP COLUMN `accesslog_id`,
                ADD UNIQUE `aggregate` (`datasource`, `module_action_id`, `period`, `aggregate`, `bot`),
                ADD INDEX `triplet` (`datasource`, `module_action_id`, `period`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.70");
        $query = "ALTER TABLE `cronjob`
                ADD `servers_address` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `cronjob_log`
                ADD `server_address` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.1.71");
        $query = "CREATE TABLE `access_log_archive`
              LIKE `access_log`;";
        $this->addQuery($query);

        $query = "CREATE TABLE `datasource_log_archive`
              LIKE `datasource_log`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.72");
        $query = "INSERT INTO `access_log_archive` (
                SELECT *
                FROM `access_log`
                WHERE `aggregate` > '10'
              );";
        $this->addQuery($query);

        $query = "INSERT INTO `datasource_log_archive` (
                SELECT *
                FROM `datasource_log`
                WHERE `aggregate` > '10'
              );";
        $this->addQuery($query);

        $this->makeRevision("1.1.73");
        $query = "DELETE FROM `access_log`
              WHERE `aggregate` > '10';";
        $this->addQuery($query);

        $query = "DELETE FROM `datasource_log`
              WHERE `aggregate` > '10';";
        $this->addQuery($query);

        $this->makeRevision("1.1.74");
        $query = "ALTER TABLE `ex_class`
                ADD `cross_context_class` ENUM ('CPatient'),
                DROP `host_class`,
                DROP `event`,
                DROP `disabled`,
                DROP `required`,
                DROP `unicity`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_constraint`
                DROP `ex_class_id`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field`
                DROP `ex_class_id`,
                DROP `report_level`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_host_field`
                DROP `ex_class_id`,
                DROP `host_type`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.75");
        $query = "ALTER TABLE `ex_link`
                    ADD `datetime_create` DATETIME,
                    ADD `owner_id`        INT(11) UNSIGNED,
                    ADD INDEX (`owner_id`),
                    ADD INDEX (`datetime_create`);";
        $this->addQuery($query);

        $this->addMethod("addExLinkDates");

        $this->makeRevision("1.1.76");
        $query = "CREATE TABLE `ex_class_picture` (
                `ex_class_picture_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ex_group_id` INT (11) UNSIGNED,
                `subgroup_id` INT (11) UNSIGNED,
                `name` VARCHAR (255) NOT NULL,
                `disabled` ENUM ('0','1') NOT NULL DEFAULT '0',
                `show_label` ENUM ('0','1') NOT NULL DEFAULT '1',
                `predicate_id` INT (11) UNSIGNED,
                `coord_left` INT (11),
                `coord_top` INT (11),
                `coord_width` INT (11) UNSIGNED,
                `coord_height` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_picture`
                ADD INDEX (`ex_group_id`),
                ADD INDEX (`subgroup_id`),
                ADD INDEX (`predicate_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.77");
        $this->addPrefQuery("navigationHistoryLength", 0);

        $this->makeRevision("1.1.78");
        $query = "ALTER TABLE `ex_class_field`
                ADD `hidden` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.79");
        $query = "ALTER TABLE `configuration`
                CHANGE `value` `value` VARCHAR(1024)";
        $this->addQuery($query);

        $this->makeRevision('1.1.80');
        $query = "CREATE TABLE `syslog_source` (
                `syslog_source_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name`             VARCHAR(255) NOT NULL,
                `role`             ENUM('prod','qualif') NOT NULL DEFAULT 'qualif',
                `host`             TEXT NOT NULL,
                `user`             VARCHAR(255),
                `password`         VARCHAR(50),
                `iv`               VARCHAR(255),
                `type_echange`     VARCHAR(255),
                `active`           ENUM('0','1') NOT NULL DEFAULT '1',
                `loggable`         ENUM('0','1') NOT NULL DEFAULT '1',
                `libelle`          VARCHAR(255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.1.81');
        $query = "ALTER TABLE `syslog_source`
                ADD `port` INT(11) DEFAULT '514' NOT NULL,
                ADD `protocol` ENUM('TCP', 'UDP', 'TLS') DEFAULT 'TCP' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.1.82');
        $query = "ALTER TABLE `syslog_source`
                ADD `timeout` TINYINT(4) DEFAULT '5';";
        $this->addQuery($query);

        $this->makeRevision('1.1.83');
        $query = "ALTER TABLE `syslog_source`
              ADD `ssl_enabled`     ENUM ('0','1') NOT NULL DEFAULT '0',
              ADD `ssl_certificate` VARCHAR(255),
              ADD `ssl_passphrase`  VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision('1.1.84');
        $query = "ALTER TABLE `syslog_source`
                CHANGE `password` `password` VARCHAR(255),
                CHANGE `iv`       `iv`       VARCHAR(16) AFTER `password`,
                ADD `iv_passphrase`          VARCHAR(16) AFTER `ssl_passphrase`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.85");

        $this->addMethod("removeDuplicatePreferences");

        $this->makeRevision("1.1.86");
        $query = "ALTER TABLE `error_log`
                ADD `debug` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.87");
        $query = "ALTER TABLE `access_log`
                ADD `session_wait` FLOAT AFTER `duration`,
                ADD `session_read` FLOAT AFTER `session_wait`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `access_log_archive`
                ADD `session_wait` FLOAT AFTER `duration`,
                ADD `session_read` FLOAT AFTER `session_wait`;";
        $this->addQuery($query);

        $this->makeRevision("1.1.88");
        $query = "ALTER TABLE user_authentication ADD INDEX user_auth_date (user_id,datetime_login)";
        $this->addQuery($query, true);

        $this->makeRevision("1.1.89");
        $query = "CREATE TABLE `ex_class_category` (
                `ex_class_category_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `title` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `color` VARCHAR (6),
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class`
                ADD `category_id` INT (11) UNSIGNED,
                ADD INDEX (`category_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.90");
        $query = "ALTER TABLE `ex_class_message`
                ADD `description` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.1.91");
        $query = "ALTER TABLE `ex_class_picture`
                ADD `triggered_ex_class_id` INT (11) UNSIGNED,
                ADD `coord_angle` MEDIUMINT (9),
                ADD `movable` ENUM('0','1') NOT NULL DEFAULT '0',
                ADD INDEX `triggered_ex_class_id` (`triggered_ex_class_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `ex_object_picture` (
                `ex_object_picture_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ex_class_picture_id` INT (11) UNSIGNED NOT NULL,
                `ex_class_id` INT (11) UNSIGNED NOT NULL,
                `ex_object_id` INT (11) UNSIGNED NOT NULL,
                `triggered_ex_class_id` INT (11) UNSIGNED,
                `triggered_ex_object_id` INT (11) UNSIGNED,
                `comment` TEXT,
                `coord_left` INT (11),
                `coord_top` INT (11),
                `coord_width` INT (11) UNSIGNED,
                `coord_height` INT (11) UNSIGNED,
                `coord_angle` MEDIUMINT (9) UNSIGNED,
                INDEX (`ex_class_picture_id`),
                INDEX (`ex_class_id`),
                INDEX (`ex_object_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.1.92");
        $query = "ALTER TABLE `ex_class_field`
                ADD `update_native_data` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class`
                ADD `allow_create_in_column` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.1.93");
        $query = "ALTER TABLE `ex_class_field`
                ADD `load_native_data` ENUM('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.1.94");
        $query = "ALTER TABLE `long_request_log`
      ADD `query_performance` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.1.95");
        $query = "ALTER TABLE `long_request_log`
      ADD `module_action_id` INT (11) UNSIGNED AFTER `server_addr`,
      ADD `datetime_start` DATETIME AFTER `long_request_log_id`,
      ADD `session_id` VARCHAR (32) AFTER `user_id`,
      ADD INDEX `session_id`(`session_id`),
      ADD INDEX `datetime_start`(`datetime_start`);";
        $this->addQuery($query);
        $query = "UPDATE `long_request_log`
      SET `datetime_start` = DATE_SUB(`datetime`, INTERVAL `duration` SECOND);";
        $this->addQuery($query);

        $this->makeRevision("1.1.96");
        $query = "ALTER TABLE `long_request_log`
      CHANGE `datetime` `datetime_end` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.1.97");
        $query = "ALTER TABLE `long_request_log`
      ADD `query_report` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('1.1.98');
        $query = "ALTER TABLE `access_log`
                ADD INDEX `aggregate_period` (`aggregate`, `period`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `access_log_archive`
                ADD INDEX `aggregate_period` (`aggregate`, `period`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `datasource_log`
                ADD INDEX `aggregate_period` (`aggregate`, `period`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `datasource_log_archive`
                ADD INDEX `aggregate_period` (`aggregate`, `period`);";
        $this->addQuery($query);

        $this->makeRevision("1.1.99");
        $query = "ALTER TABLE `alert`
                ADD INDEX `creation_date` (`creation_date`),
                ADD INDEX `handled_date` (`handled_date`),
                ADD INDEX `handled_user_id` (`handled_user_id`);";
        $this->addQuery($query, true);

        $this->makeRevision("1.2.00");
        $query = "DELETE FROM `alert`
      WHERE `creation_date` IS NULL;";
        $this->addQuery($query);

        $query = "DELETE FROM `alert`
      WHERE `creation_date` < '" . CMbDT::dateTime("-90 DAYS") . "'";
        $this->addQuery($query);

        $this->makeRevision("1.2.02");
        $query = "ALTER TABLE `long_request_log`
                ADD INDEX `module_action_id` (`module_action_id`)";
        $this->addQuery($query, true);

        $this->makeRevision("1.2.03");
        $query = "ALTER TABLE `ex_class_message`
                ADD `tab_index` INT (11)";
        $this->addQuery($query);

        $this->makeRevision("1.2.04");
        $this->addFunctionalPermQuery("system_use_advanced_object_history", "0");

        $this->makeRevision("1.2.05");
        $query = "ALTER TABLE `session`
                ADD INDEX `expire` (`expire`)";
        $this->addQuery($query);

        $this->makeRevision("1.2.06");
        $query = "ALTER TABLE `sender_file_system`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
        $this->addQuery($query);

        $this->makeRevision("1.2.07");
        $query = "ALTER TABLE `sender_file_system`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
        $this->addQuery($query);

        $this->makeRevision('1.2.08');

        $query = "ALTER TABLE `source_smtp`
                ADD `asynchronous` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.2.09');

        $query = "ALTER TABLE `cronjob`
                DROP `cron_login`,
                DROP `cron_password`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.10');

        $query = "ALTER TABLE `cronjob_log`
                CHANGE `error` `log` TEXT,
                ADD `severity` TINYINT DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `cronjob_log`
                SET `severity` = '1' 
                WHERE `log` IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.2.11');
        $query = "ALTER TABLE `ex_class_picture`
              ADD `description` TEXT AFTER `name`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.12');
        $query = "CREATE TABLE `redis_server` (
                `redis_server_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `host` VARCHAR (255) NOT NULL,
                `port` INT (11) UNSIGNED NOT NULL DEFAULT '6379',
                `instance_role` ENUM ('prod','qualif'),
                `is_master` ENUM ('0','1') NOT NULL DEFAULT '1',
                `active` ENUM ('0','1') NOT NULL DEFAULT '1',
                `latest_change` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `redis_server` 
                ADD INDEX (`latest_change`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.13');
        $query = "ALTER TABLE `ex_class_picture` 
                ADD `drawable` ENUM ('0','1') NOT NULL DEFAULT '0' AFTER `movable`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.14');
        $query = "ALTER TABLE `datasource_log` 
                ADD `connections` INT (11),
                ADD `ping_duration` FLOAT;";
        $this->addQuery($query);
        $query = "ALTER TABLE `datasource_log_archive` 
                ADD `connections` INT (11),
                ADD `ping_duration` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision('1.2.15');
        $query = "ALTER TABLE `ex_class_picture` 
                ADD `in_doc_template` ENUM ('0','1') NOT NULL DEFAULT '0' AFTER `drawable`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.16');
        $query = "ALTER TABLE `ex_class_constraint` 
                ADD `quick_access` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.2.17');

        $this->makeRevision('1.2.18');
        $query = "ALTER TABLE user_authentication
              MODIFY auth_method ENUM('basic', 'substitution', 'token', 'ldap', 'ldap_guid') NOT NULL DEFAULT 'basic',
              CHANGE previous_user_id previous_auth_id INT(11) UNSIGNED,
              ADD INDEX (`previous_auth_id`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.19');
        $query = "CREATE TABLE `ex_class_field_notification` (
                `ex_class_field_notification_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `predicate_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `target_user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `subject` VARCHAR (255) NOT NULL,
                `body` TEXT NOT NULL,
                INDEX (`predicate_id`),
                INDEX (`target_user_id`)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.2.20');
        $query = "CREATE TABLE `abonnement` (
              `abonnement_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `object_class`  VARCHAR(255)     NOT NULL,
              `object_id`     INT(11) UNSIGNED NOT NULL,
              `datetime`      DATETIME         NOT NULL,
              `user_id`       INT(11) UNSIGNED NOT NULL,
              PRIMARY KEY (`abonnement_id`),
              KEY `object` (`object_class`, `object_id`),
              KEY (`user_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.2.21');
        $query = "ALTER TABLE `ex_class_host_field`
        ADD `subgroup_id` INT (11) UNSIGNED AFTER `ex_group_id`";
        $this->addQuery($query);

        $this->makeRevision('1.2.22');
        $query = "ALTER TABLE `ex_class` 
                ADD `permissions` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('1.2.23');
        $query = "ALTER TABLE `module_action` 
                DROP INDEX `module_action`, 
                ADD INDEX `module_action` (`module`, `action`)";
        $this->addQuery($query);

        $this->makeRevision("1.2.24");

        $query = "ALTER TABLE `echange_http`
                ADD INDEX `purge_echange` (`purge`, `date_echange`);";
        $this->addQuery($query, true);

        $this->makeRevision("1.2.25");

        $query = "CREATE TABLE `sender_http` (
                `sender_http_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED,
                `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
                `create_ack_file` ENUM ('0','1') DEFAULT '1',
                `delete_file` ENUM ('0','1') DEFAULT '1',
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod',
                `exchange_format_delayed` INT (11) UNSIGNED DEFAULT '60'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `sender_http`
                ADD INDEX (`user_id`),
                ADD INDEX (`nom`),
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.2.26");

        $query = "CREATE TABLE `exchange_fs` (
                `exchange_fs_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `emetteur` VARCHAR (255),
                `destinataire` VARCHAR (255),
                `date_echange` DATETIME NOT NULL,
                `function_name` VARCHAR (255) NOT NULL,
                `input` LONGTEXT,
                `output` LONGTEXT,
                `purge` ENUM ('0','1') DEFAULT '0',
                `response_time` FLOAT
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `exchange_fs`
                ADD INDEX (`date_echange`);";
        $this->addQuery($query);

        $this->makeRevision("1.2.27");

        $query = "ALTER TABLE `exchange_fs`
                ADD `response_datetime` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `echange_http`
                ADD `response_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.2.28");

        $query = "ALTER TABLE `echange_http`
                ADD `source_id` INT (11) UNSIGNED AFTER `destinataire`,
                ADD `source_class` VARCHAR (80) AFTER `source_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_fs`
                ADD `source_id` INT (11) UNSIGNED AFTER `destinataire`,
                ADD `source_class` VARCHAR (80) AFTER `source_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.2.29");

        $query = "ALTER TABLE `datasource_log` 
                ADD `connection_time` FLOAT AFTER `ping_duration`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `datasource_log_archive` 
                ADD `connection_time` FLOAT AFTER `ping_duration`;";
        $this->addQuery($query);

        $this->makeRevision("1.2.30");

        if (!$this->ds->hasTable('user_authentication_error', false)) {
            $query = "CREATE TABLE `user_authentication_error` (
                `user_authentication_error_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED,
                `login_value` VARCHAR (80) NOT NULL,
                `datetime` DATETIME NOT NULL,
                `auth_method` VARCHAR (30),
                `identifier` VARCHAR (16) NOT NULL,
                `ip_address` VARCHAR (39) NOT NULL,
                `message` TEXT
              )/*! ENGINE=MyISAM */;";
            $this->addQuery($query);

            $query = "ALTER TABLE `user_authentication_error` 
                ADD INDEX (`user_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`identifier`);";
            $this->addQuery($query);
        }

        $query = "ALTER TABLE `user_authentication` 
                CHANGE `id_address` `ip_address` CHAR (39) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.2.31");

        $query = "DELETE FROM `config_db` WHERE `key` LIKE 'php %';";
        $this->addQuery($query);

        $this->makeRevision('1.2.32');
        $query = "CREATE TABLE `exchange_smtp` (
                `exchange_smtp_id`  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `creation_date`     DATETIME         NOT NULL,
                `subject`           VARCHAR(255)     NOT NULL,
                `cc`                VARCHAR(255),
                `re`                VARCHAR(255),
                `attempts`          TINYINT UNSIGNED NOT NULL DEFAULT '0',
                `emetteur`          VARCHAR(255),
                `destinataire`      VARCHAR(255)     NOT NULL,
                `date_echange`      DATETIME,
                `response_datetime` DATETIME,
                `function_name`     VARCHAR(255)     NOT NULL,
                `input`             MEDIUMTEXT       NOT NULL,
                `output`            MEDIUMTEXT,
                `purge`             ENUM('0','1')    NOT NULL DEFAULT '0',
                `response_time`     FLOAT,
                `source_id`         INT(11) UNSIGNED NOT NULL,
                `source_class`      VARCHAR(255)     NOT NULL,
                KEY (`creation_date`),
                KEY (`date_echange`),
                KEY (`response_datetime`),
                KEY `source` (`source_class`, `source_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.2.33');
        $query = "ALTER TABLE `exchange_smtp`
              ADD `content_hash` CHAR(64) AFTER `attempts`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.34');
        $query = "CREATE TABLE `smtp_buffer` (
                `smtp_buffer_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `creation_date`  DATETIME         NOT NULL,
                `user_id`        INT(11) UNSIGNED NOT NULL,
                `source_id`      INT(11) UNSIGNED NOT NULL,
                `attempts`       TINYINT UNSIGNED NOT NULL DEFAULT '0',
                `input`          MEDIUMTEXT NOT NULL,
                KEY (`user_id`),
                KEY (`source_id`))/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.2.35');
        $query = "ALTER TABLE `ex_class_field_group` 
                ADD `disabled` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);


        if ($this->tableExists('table_status')) {
            $this->makeEmptyRevision('1.2.36');
        } else {
            $this->makeRevision('1.2.36');
            $query = "CREATE TABLE `table_status` (
                `table_status_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `create_time` DATETIME NOT NULL,
                `update_time` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;";
            $this->addQuery($query);

            $query = "ALTER TABLE `table_status`
                ADD INDEX (`name`);";
            $this->addQuery($query);
        }

        $this->makeRevision('1.2.37');
        $query = "ALTER TABLE `ex_class_picture` 
                ADD `report_class` VARCHAR(80);";
        $this->addQuery($query);

        $this->makeRevision('1.2.38');
        $query = "ALTER TABLE `ex_class_field` 
                ADD `result_threshold_high` FLOAT,
                ADD `result_threshold_low` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision('1.2.39');
        $query = "ALTER TABLE `cronjob`
                ADD `mode` ENUM ('acquire','lock') DEFAULT 'acquire';";
        $this->addQuery($query);

        $this->makeRevision('1.2.40');
        $query = "ALTER TABLE `ex_class_event` 
                ADD `tab_name` VARCHAR (255),
                ADD `tab_rank` INT (11),
                ADD `tab_show_header` ENUM('0', '1') NOT NULL DEFAULT '1',
                ADD `tab_show_subtabs` ENUM('0', '1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision('1.2.41');
        $query = "CREATE TABLE `ex_class_field_action_button` (
                `ex_class_field_action_button_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ex_group_id` INT (11) UNSIGNED NOT NULL,
                `subgroup_id` INT (11) UNSIGNED,
                `predicate_id` INT (11) UNSIGNED,
                `ex_class_field_source_id` INT (11) UNSIGNED,
                `ex_class_field_target_id` INT (11) UNSIGNED,
                `action` ENUM ('copy','empty') NOT NULL DEFAULT 'copy',
                `icon` ENUM ('cancel','left','up','right','down') NOT NULL,
                `text` VARCHAR(255),
                `coord_x` TINYINT (4) UNSIGNED,
                `coord_y` TINYINT (4) UNSIGNED,
                `coord_left` INT (11),
                `coord_top` INT (11),
                `coord_width` INT (11) UNSIGNED,
                `coord_height` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_field_action_button` 
                ADD INDEX (`ex_group_id`),
                ADD INDEX (`subgroup_id`),
                ADD INDEX (`predicate_id`),
                ADD INDEX (`ex_class_field_source_id`),
                ADD INDEX (`ex_class_field_target_id`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.42');
        $query = "CREATE TABLE `ex_class_widget` (
                `ex_class_widget_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `ex_group_id` INT (11) UNSIGNED NOT NULL,
                `subgroup_id` INT (11) UNSIGNED,
                `predicate_id` INT (11) UNSIGNED,
                `name` VARCHAR(80) NOT NULL,
                `options` TEXT,
                `coord_left` INT (11),
                `coord_top` INT (11),
                `coord_width` INT (11) UNSIGNED,
                `coord_height` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ex_class_widget` 
                ADD INDEX (`ex_group_id`),
                ADD INDEX (`subgroup_id`),
                ADD INDEX (`predicate_id`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.43');
        $query = "ALTER TABLE `source_file_system` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_http` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision('1.2.44');

        $this->addMethod('addExObjectCompleteness');

        $this->makeRevision('1.2.45');

        $query = "ALTER TABLE `view_sender_source`
              ADD `password` varchar(255);";
        $this->addQuery($query);

        $this->makeRevision('1.2.46');
        $query = "ALTER TABLE `source_http` 
                DROP `delete_file`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.47');
        $query = "CREATE TABLE `ex_class_mandatory_constraint` (
                `ex_class_mandatory_constraint_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ex_class_event_id`                INT(11) UNSIGNED NOT NULL,
                `field`                            VARCHAR(255)     NOT NULL,
                `operator`                         ENUM('=','!=','>','>=','<','<=','startsWith','endsWith','contains','in') NOT NULL DEFAULT '=',
                `value`                            VARCHAR(255)     NOT NULL,
                `reference_value`                  VARCHAR(255),
                KEY (`ex_class_event_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.2.48');

        $query = "ALTER TABLE `source_smtp` ADD `secure` ENUM ('none', 'ssl', 'tls') DEFAULT 'none';";
        $this->addQuery($query);

        $query = "UPDATE `source_smtp` SET `secure` = 'ssl' WHERE `ssl` = '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_smtp` DROP `ssl`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.49');

        $query = "ALTER TABLE `source_file_system` 
                ADD `ack_prefix` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('1.2.50');
        $query = "ALTER TABLE `ex_class_mandatory_constraint` 
                ADD `comment` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('1.2.51');
        $query = "ALTER TABLE `ex_class_field`
                ADD `in_completeness` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.2.52');
        $this->addPrefQuery("displayUTCDate", "0");

        $this->makeRevision('1.2.53');
        $query = "ALTER TABLE `user_authentication`
                CHANGE `datetime_logout` `expiration_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision('1.2.54');
        $query = "CREATE TABLE `patient_log` (
                `patient_log_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_log_id` INT (11) UNSIGNED NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                KEY `user_log_id` (`user_log_id`),
                KEY `patient_id` (`patient_id`)
              )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision('1.2.55');
        $query = "ALTER TABLE user_authentication
              MODIFY auth_method ENUM('basic', 'substitution', 'token', 'ldap', 'ldap_guid', 'card', 'sso') NOT NULL DEFAULT 'basic';";
        $this->addQuery($query);

        $this->makeRevision('1.2.56');
        $query = "ALTER TABLE `user_authentication`
                ADD INDEX (`expiration_datetime`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.57');
        $query = "ALTER TABLE `access_log`
      ADD `nosql_time` FLOAT AFTER `nb_requests`,
      ADD `nosql_requests` INT (11) AFTER `nosql_time`,
      ADD `io_time` FLOAT AFTER `nosql_requests`,
      ADD `io_requests` INT (11) AFTER `io_time`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `access_log_archive`
      ADD `nosql_time` FLOAT AFTER `nb_requests`,
      ADD `nosql_requests` INT (11) AFTER `nosql_time`,
      ADD `io_time` FLOAT AFTER `nosql_requests`,
      ADD `io_requests` INT (11) AFTER `io_time`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.58');
        $query = "ALTER TABLE `user_authentication` 
                ADD `last_session_update` DATETIME AFTER `expiration_datetime`,
                ADD `nb_update` INT (11) UNSIGNED AFTER `last_session_update`,
                ADD `session_lifetime` INT (11) UNSIGNED AFTER `nb_update`;";
        $this->addQuery($query);

        $this->makeRevision("1.2.59");

        $this->addMethod("addIndexFirstnameIfNotExists");

        $this->makeRevision('1.2.60');
        $query = "ALTER TABLE `ex_class_field`
                ADD `auto_increment` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.2.61');
        $query = "CREATE TABLE `geolocalisation` (
                `geolocalisation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `object_class` CHAR(80) NOT NULL,
                `lat_lng` TEXT,
                `commune_insee` CHAR(5),
                `processed` ENUM('0', '1') DEFAULT '0',
                KEY `object` (`object_class`, `object_id`)
              )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision("1.2.62");
        $query = "ALTER TABLE `alert` 
      ADD `creation_user_id` INT (11) UNSIGNED AFTER `creation_date`;";
        $this->addQuery($query);

        $this->makeRevision("1.2.63");
        $query = "ALTER TABLE `view_sender` 
                ADD `last_datetime` DATETIME,
                ADD `last_status` ENUM('triggered', 'producted');";
        $this->addQuery($query);

        $query = "ALTER TABLE `view_sender` 
                ADD INDEX (`last_datetime`),
                ADD INDEX (`last_status`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.64');
        $this->addPrefQuery("devToolBar", 0);

        $this->makeRevision("1.2.65");
        $this->delPrefQuery("showTemplateSpans");

        $this->makeRevision("1.2.66");
        $query = "CREATE TABLE IF NOT EXISTS `user_action` (
              `user_action_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_id` INT(11) UNSIGNED NOT NULL,
              `object_id` INT(11) UNSIGNED NOT NULL,
              `object_class_id` INT(11) UNSIGNED NOT NULL,
              `type` ENUM('create','store','merge','delete') NOT NULL,
              `date` DATETIME NOT NULL,
              `ip_address` VARBINARY(16) NULL DEFAULT NULL,
              PRIMARY KEY (`user_action_id`),
              INDEX `user_id` (`user_id`),
              INDEX `object_ref` (`object_class_id`, `object_id`),
              INDEX `date` (`date`)
            )
            ENGINE=MyISAM
            AUTO_INCREMENT=1000000000;
            ";
        $this->addQuery($query);
        $query = "CREATE TABLE IF NOT EXISTS `user_action_data` (
              `user_action_data_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_action_id` INT(11) UNSIGNED NOT NULL,
              `field` VARCHAR(250) NULL DEFAULT NULL,
              `value` TEXT NULL DEFAULT NULL,
              PRIMARY KEY (`user_action_data_id`),
              INDEX `user_action_id` (`user_action_id`)
            )
            ENGINE=MyISAM;
            ";
        $this->addQuery($query);
        $query = "CREATE TABLE IF NOT EXISTS `object_class` (
              `object_class_id` INT(11) NOT NULL AUTO_INCREMENT,
              `object_class` VARCHAR(250) NOT NULL,
              PRIMARY KEY (`object_class_id`),
              UNIQUE INDEX `object_class` (`object_class`)
            )
            ENGINE=MyISAM;
            ";
        $this->addQuery($query);

        $this->makeRevision('1.2.67');

        $query = "ALTER TABLE `view_sender` 
                ADD `day` VARCHAR (80)";
        $this->addQuery($query);

        $this->makeRevision('1.2.68');
        $query = "ALTER TABLE `ex_class_host_field`
              ADD `display_in_tab` ENUM('0', '1') DEFAULT '0',
              ADD `tab_index`      INT(11)";
        $this->addQuery($query);

        $this->makeRevision('1.2.69');
        $query = "ALTER TABLE `ex_class`
              ADD `nb_columns` SMALLINT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision('1.2.70');
        $query = "ALTER TABLE `source_smtp`
                ADD `address_type` ENUM ('mail','apicrypt', 'mssante') DEFAULT 'mail';";
        $this->addQuery($query);

        $this->makeRevision('1.2.71');
        $this->delPrefQuery("devToolBar");

        $this->makeRevision("1.2.72");
        $this->addPrefQuery("show_performance", "0");

        $this->makeRevision('1.2.73');
        $query = "ALTER TABLE user_authentication
              MODIFY auth_method ENUM('basic', 'substitution', 'token', 'ldap', 'ldap_guid', 'card', 'sso', 'standard') NOT NULL DEFAULT 'standard';";
        $this->addQuery($query);

        $this->makeRevision('1.2.74');
        $this->addQuery($query);
        $query = "UPDATE user_authentication SET auth_method = 'standard' WHERE auth_method = 'basic';";
        $this->addQuery($query);

        $this->makeRevision('1.2.75');
        $query = "CREATE TABLE `aquittement_msg_system` (
                `aquittement_msg_system_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `message_id` INT (11) UNSIGNED NOT NULL,
                `date` DATETIME
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `aquittement_msg_system` 
                              ADD INDEX (`user_id`),
                              ADD INDEX (`message_id`),
                              ADD INDEX (`date`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.76');

        $query = "ALTER TABLE modules MODIFY mod_ui_order SMALLINT UNSIGNED NOT NULL";
        $this->addQuery($query);

        $this->makeRevision('1.2.77');
        $query = "ALTER TABLE user_authentication
              MODIFY auth_method ENUM('basic', 'substitution', 'token', 'ldap', 'ldap_guid', 'card', 'sso', 'standard', 'oauth') NOT NULL DEFAULT 'standard';";
        $this->addQuery($query);

        $this->makeRevision('1.2.78');

        $query = "rename table aquittement_msg_system to acquittement_msg_system;
              alter table acquittement_msg_system change aquittement_msg_system_id acquittement_msg_system_id int(11) unsigned auto_increment;
              ALTER TABLE `acquittement_msg_system` CHANGE `date` `date` DATETIME NOT NULL;";

        $this->addQuery($query);

        $this->makeRevision('1.2.79');
        $query = "ALTER TABLE `echange_http`
                ADD `status_code` SMALLINT;";
        $this->addQuery($query);

        $this->makeRevision('1.2.80');
        $query = "ALTER TABLE `access_log` 
      ADD `transport_tiers_nb` INT (11) UNSIGNED NULL DEFAULT NULL,
      ADD `transport_tiers_time` FLOAT NULL DEFAULT NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.2.81');
        $query = "ALTER TABLE `access_log_archive` 
      ADD `transport_tiers_nb` INT (11) UNSIGNED NULL DEFAULT NULL,
      ADD `transport_tiers_time` FLOAT NULL DEFAULT NULL;";
        $this->addQuery($query);

        if ($this->columnExists('configuration', 'alt_value')) {
            $this->makeEmptyRevision("1.2.82");
        } else {
            $this->makeRevision('1.2.82');
            $query = "ALTER TABLE `configuration`
                ADD `alt_value` VARCHAR(1024) AFTER `value`;";
            $this->addQuery($query);
        }

        $this->makeRevision('1.2.83');
        $query = "TRUNCATE TABLE `redis_server`;";
        $this->addQuery($query);

        $this->makeRevision('1.2.84');
        $this->addPrefQuery("mediboard_ext", "0");

        $this->makeRevision('1.2.85');
        $query = "UPDATE `user_preferences` SET `value` = 2 
                WHERE `key` LIKE 'mediboard_ext' AND `user_id` IS NULL";
        $this->addQuery($query);

        $this->makeRevision('1.2.86');

        $query = "ALTER TABLE `alert` ADD INDEX (`creation_user_id`);";
        $this->addQuery($query);

        $this->makeRevision('1.2.87');

        $query = "ALTER TABLE `cronjob_log` ADD `duration` INT (11);";
        $this->addQuery($query);

        $this->makeRevision('1.2.88');

        $query = "ALTER TABLE `cronjob` 
                CHANGE `mode` `mode` ENUM ('acquire','lock') DEFAULT 'lock',
                ADD `token_id` INT (11) UNSIGNED,
                ADD INDEX (`token_id`);";
        $this->addQuery($query);

        $this->addPrefQuery("FALLBACK_LOCALE", "fr");

        $this->makeRevision('1.2.89');

        $query = "ALTER TABLE `cronjob`
                CHANGE `params` `params` TEXT NULL";
        $this->addQuery($query);

        $this->makeRevision('1.2.90');

        $query = "ALTER TABLE `cronjob_log`
                CHANGE `status` `status` VARCHAR(80) DEFAULT 'started'";
        $this->addQuery($query);

        $this->makeRevision('1.2.91');
        $this->addMethod('migrateHandlers');

        $this->makeEmptyRevision('1.2.91.1');

        $this->makeRevision('1.2.92');

        $query = "ALTER TABLE `view_sender` 
                ADD `last_http_code` INT (11),
                ADD `last_error_datetime` DATETIME;";
        $this->addQuery($query);

        if ($this->ds->hasTable('id_sante400')) {
            $query = "DELETE FROM `id_sante400` WHERE `id_sante400`.tag = 'CViewSender-http-code';";
            $this->addQuery($query);
        }

        if ($this->ds->hasField('user_preferences', 'restricted', false)) {
            $this->makeRevision('1.2.93');
            // Correction des prfrences utilisateurs qui ont parfois une chaine vide au lieu de '0' ou '1' dans le champ restricted
            $query = "UPDATE `user_preferences` SET `user_preferences`.restricted = '0' WHERE `user_preferences`.restricted = '';";
            $this->addQuery($query);
        } else {
            $this->makeEmptyRevision('1.2.93');
        }

        $this->makeRevision('1.2.94');

        $query = "ALTER TABLE `source_http` 
                ADD `token` VARCHAR(80);";
        $this->addQuery($query);

        $this->makeRevision('1.2.95');

        $query = "ALTER TABLE `ex_class_constraint` 
                CHANGE `operator` `operator` ENUM ('=','!=','>','>=','<','<=','startsWith','endsWith','contains','in', 'notIn') NOT NULL DEFAULT '='";
        $this->addQuery($query);

        $this->makeEmptyRevision('1.2.96');
        $this->makeEmptyRevision('1.2.96.1');

        $this->makeRevision('1.2.96.2');

        $this->addPrefQuery("mediboard_ext_dark", "0");

        $this->makeRevision('1.2.97');
        if (!$this->columnExists('ex_class_field_action_button', 'trigger_ex_class_id')) {
            $query = "ALTER TABLE `ex_class_field_action_button`
              MODIFY `action` ENUM ('copy','empty', 'open') NOT NULL DEFAULT 'copy',
              MODIFY `icon` ENUM ('cancel','left','up','right','down', 'new') NOT NULL,
              ADD COLUMN `trigger_ex_class_id` INT(11) UNSIGNED,
              ADD INDEX (`trigger_ex_class_id`);";
            $this->addQuery($query);
        }

        $this->makeRevision('1.2.98');
        $query = "ALTER TABLE `modules` 
      ADD `mod_category` ENUM('autre', 'referentiel', 'plateau_technique', 'parametrage', 'obsolete', 'interoperabilite', 'reporting', 'import', 'erp', 'dossier_patient', 'circuit_patient', 'systeme', 'administratif'),
      ADD `mod_package` ENUM('autre', 'metier', 'administration', 'ox', 'echange', 'referentiel'),
      ADD `mod_custom_color` VARCHAR(6) DEFAULT NULL";
        $this->addQuery($query);
        $this->setModuleCategory("systeme", "administration");

        $this->makeRevision('1.2.99');

        $query = "CREATE TABLE `error_log_whitelist` (
              `error_log_whitelist_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_id` INT(11) UNSIGNED NULL DEFAULT NULL,
              `datetime` DATETIME NOT NULL,
              `hash` CHAR(32) NOT NULL,
              `revision` INT(11) NULL DEFAULT NULL,
              `text`  VARCHAR(255) NOT NULL,
              `type`  VARCHAR(255) NOT NULL,
              `file_name` VARCHAR(255) NULL DEFAULT NULL,
              `line_number` INT(11) NULL DEFAULT NULL,
              `count` INT(11) NOT NULL DEFAULT 0,
              PRIMARY KEY (`error_log_whitelist_id`),
              INDEX `user_id` (`user_id`),
              INDEX `datetime` (`datetime`)
            )
            ENGINE=MyISAM";
        $this->addQuery($query);

        $this->makeRevision('1.3.0');
        $this->config_moves['1.3.0'][] = ['base_url', 'external_url'];

        $this->makeRevision('1.3.01');

        $query = "CREATE TABLE `checklist_config` (
                `checklist_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `module` VARCHAR (255) NOT NULL,
                `key` VARCHAR (255) NOT NULL,
                `value` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `checklist_config` 
                ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $this->makeRevision('1.3.2');

        $query = "ALTER TABLE `sender_file_system` 
                ADD `after_processing_action` ENUM ('none','move','delete') DEFAULT 'none',
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
        $this->addQuery($query);

        $query = "UPDATE `sender_file_system`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1';";
        $this->addQuery($query);

        $query = "UPDATE `sender_file_system`
                SET `after_processing_action` = 'delete'
                WHERE `delete_file` = '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `sender_file_system` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sender_http` 
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
        $this->addQuery($query);

        $query = "UPDATE `sender_http`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1'";
        $this->addQuery($query);

        $query = "ALTER TABLE `sender_http` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
        $this->addQuery($query);

        $this->makeRevision('1.3.3');

        $query = "ALTER TABLE `ex_class_field_translation` CHANGE `lang` `lang` CHAR(5) DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision('1.3.4');
        $query = "ALTER TABLE `configuration`
                ADD `static` ENUM ('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.3.5');
        $query = "UPDATE `user_preferences` SET `value` = '1' WHERE `user_preferences`.`key` = 'mediboard_ext';";
        $this->addQuery($query);

        $this->makeRevision("1.3.6");
        $query = "DROP TABLE `http_redirection`";
        $this->addQuery($query);

        $this->makeRevision("1.3.7");
        $query = "ALTER TABLE `error_log` ADD `count` INT(11) NOT NULL DEFAULT 1;";
        $this->addQuery($query);

        $this->makeRevision('1.3.8');
        $query = "ALTER TABLE `ex_class_event` 
                ADD `constraints_logical_operator` ENUM('and', 'or') NOT NULL DEFAULT 'or',
                ADD `mandatory_constraints_logical_operator` ENUM('and', 'or') NOT NULL DEFAULT 'and';";
        $this->addQuery($query);

        $this->makeRevision('1.3.9');
        $this->addFunctionalPermQuery('system_show_history', '1');

        $this->makeRevision('1.4.0');

        $query = "ALTER TABLE `configuration`
                    DROP INDEX `feature`,
                    ADD UNIQUE INDEX (`feature`, `object_class`, `object_id`)";
        $this->addQuery($query);

        $this->makeRevision('1.4.1');
        $query = "ALTER TABLE `sender_http` 
                ADD `type` VARCHAR (255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `sender_file_system` 
                ADD `type` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('1.4.2');
        $query = "ALTER TABLE `source_smtp` 
                ADD `email_reply_to` VARCHAR (254);";
        $this->addQuery($query);

        $this->makeRevision('1.4.3');
        $query = "ALTER TABLE `long_request_log`
                    MODIFY `user_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('1.4.4');
        $query = "CREATE TABLE `merge_log` (
                `merge_log_id`            INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id`                 INT (11) UNSIGNED NOT NULL,
                `object_class`            VARCHAR (255)     NOT NULL,
                `base_object_id`          INT (11) UNSIGNED NOT NULL,
                `object_ids`              VARCHAR(255)      NOT NULL,
                `fast_merge`              ENUM('0', '1')    NOT NULL,
                `date_start_merge`        DATETIME          NOT NULL,
                `merge_checked`           ENUM('0', '1')    NOT NULL DEFAULT '0',
                `date_before_merge`       DATETIME,
                `date_after_merge`        DATETIME,
                `date_end_merge`          DATETIME,
                `duration`                INT (11),
                `count_merged_relations`  INT (11) UNSIGNED NOT NULL DEFAULT 0,
                `detail_merged_relations` TEXT,
                `last_error_handled`      TEXT,
                INDEX (`user_id`),
                INDEX `base_object` (`object_class`, `base_object_id`),
                INDEX (`date_start_merge`),
                INDEX (`date_before_merge`),
                INDEX (`date_after_merge`),
                INDEX (`date_end_merge`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.4.5');

        $query = "ALTER TABLE `modules`
                    DROP COLUMN `mod_ui_order`";
        $this->addQuery($query);

        $this->makeRevision('1.4.6');

        $query = "ALTER TABLE `modules`
                    ADD COLUMN  `mod_ui_order` tinyint(3) NOT NULL DEFAULT '1'";
        $this->addQuery($query);

        $this->makeRevision('1.4.7');

        $query = "CREATE TABLE `pinned_tab` (
                `pinned_tab_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `module_id` INT (11) UNSIGNED NOT NULL,
                `module_action_id` INT (11) UNSIGNED NOT NULL,
                INDEX (`user_id`, `module_id`, `module_action_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.4.8');

        $query = "CREATE TABLE `tab_hit` (
                    `tab_hit_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `user_id` INT (11) UNSIGNED NOT NULL,
                    `module_action_id` INT (11) UNSIGNED NOT NULL,
                    INDEX (`user_id`, `module_action_id`)
                   )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision('1.4.9');

        $this->addDependency('admin', '1.0.61');
        $this->addMethod('changeUserAuthMethods');

        $this->makeRevision('1.5.0');

        $query = "CREATE TABLE `object_encryption` (
                    `object_encryption_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `object_class` VARCHAR(80) NOT NULL,
                    `object_id` INT(11) UNSIGNED,
                    `iv` VARCHAR(80),
                    `key_id` INT(11) NOT NULL,
                    `hash` VARCHAR(80),
                    INDEX (`object_class`, `object_id`),
                    INDEX (`key_id`),
                    INDEX (`hash`)
                 )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision('1.5.1');
        $query = "CREATE TABLE `key_metadata` (
                `key_metadata_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name`            VARCHAR(255)     NOT NULL,
                `alg`             VARCHAR(255)     NOT NULL,
                `mode`            VARCHAR(255)     NOT NULL,
                `creation_date`   DATETIME,
                UNIQUE `name` (`name`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.5.2');
        $query = "CREATE TABLE `object_uuid` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `uuid` TEXT NULL DEFAULT NULL,
                    `object_class` VARCHAR(255) NOT NULL,
                    `object_id` INT(11) UNSIGNED NOT NULL,
                    `creation_date` DATETIME NOT NULL,
                    `creation_user` INT(11) UNSIGNED NOT NULL,
                    INDEX (`object_class`, `object_id`),
                    INDEX (`creation_date`),
                    INDEX (`creation_user`)
                )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('1.5.3');

        $this->addMethod('movePlaceholderConfigs');

        $this->makeRevision('1.5.4');
        $query = "ALTER TABLE `source_file_system` ADD `client_name`  VARCHAR(255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `source_http` ADD `client_name`  VARCHAR(255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `source_pop` ADD `client_name`  VARCHAR(255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `source_smtp` ADD `client_name`  VARCHAR(255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `syslog_source` ADD `client_name`  VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision('1.5.5');
        $query = "ALTER TABLE `access_log`
                    MODIFY `peak_memory` BIGINT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('1.5.6');
        $query = "ALTER TABLE `access_log_archive`
                    MODIFY `peak_memory` BIGINT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('1.5.7');
        $query = "ALTER TABLE `source_file_system` 
            ADD `retry_strategy` VARCHAR(255),
            ADD `first_call_date` DATETIME";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_http` 
            ADD `retry_strategy` VARCHAR(255),
            ADD `first_call_date` DATETIME";
        $this->addQuery($query);

        $query = "ALTER TABLE `syslog_source` 
            ADD `retry_strategy` VARCHAR(255),
            ADD `first_call_date` DATETIME";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_smtp` 
            ADD `retry_strategy` VARCHAR(255),
            ADD `first_call_date` DATETIME";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_pop` 
         ADD `retry_strategy` VARCHAR(255),
            ADD `first_call_date` DATETIME";
        $this->addQuery($query);

        $query = "CREATE TABLE `exchange_source_statistic` (
                `exchange_source_statistic_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_class` VARCHAR (80) NOT NULL,
                `object_id` INT (11) UNSIGNED,
                `failures` INT (11) DEFAULT 0,
                `failures_average` INT (11),
                `last_status` ENUM ('1','2'),
                `nb_call` INT (11) DEFAULT 0,
                `last_verification_date` DATETIME,
                `last_connexion_date` DATETIME,
                `last_response_time` INT (11)
              )/*! ENGINE=MyISAM */;
                ALTER TABLE `exchange_source_statistic` 
                ADD INDEX object (object_class, object_id),
                ADD INDEX (`last_verification_date`),
                ADD INDEX (`last_connexion_date`);";
        $this->addQuery($query);

        $this->makeRevision('1.5.8');

        $this->addMethod('clearLegacyControllerCache');

        $this->makeRevision('1.5.9');

        $query = "ALTER TABLE `cronjob_log` ADD `request_uid` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision('1.6.0');

        $this->addQuery("ALTER TABLE `error_log` ADD INDEX (`request_uid`);");

        $this->makeRevision('1.6.1');

        $this->addQuery("DROP TABLE `datasource_log`;");
        $this->addQuery("DROP TABLE `datasource_log_archive`;");

        $this->mod_version = '1.6.2';
    }
}
