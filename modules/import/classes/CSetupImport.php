<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupImport extends CSetup {
  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "import";
    $this->makeRevision("0.0");

    $this->makeRevision('0.01');

    $query = "CREATE TABLE `import_campaign` (
                `import_campaign_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `creation_date` DATETIME NOT NULL,
                `closing_date` DATETIME,
                `creator_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `import_campaign`
                ADD INDEX (`creation_date`),
                ADD INDEX (`closing_date`),
                ADD INDEX (`creator_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `import_entity` (
                `import_entity_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `import_campaign_id` INT (11) UNSIGNED NOT NULL,
                `last_import_date` DATETIME NOT NULL,
                `external_id` VARCHAR (255) NOT NULL,
                `external_class` VARCHAR (255) NOT NULL,
                `internal_id` INT (11) UNSIGNED,
                `internal_class` VARCHAR (255),
                `reimport` ENUM ('0','1') DEFAULT '0',
                `last_error` TEXT
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `import_entity` 
                ADD INDEX (`import_campaign_id`),
                ADD INDEX (`last_import_date`),
                ADD INDEX (`external_class`, `external_id`),
                ADD INDEX (`internal_class`, `internal_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.02");
    $this->setModuleCategory("import", "echange");

    $this->mod_version = "0.03";
  }
}
