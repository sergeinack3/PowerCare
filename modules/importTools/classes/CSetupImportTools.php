<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupImportTools extends CSetup {
  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "importTools";
    $this->makeRevision("0.0");

    $this->makeRevision('0.01');
    $query = "CREATE TABLE `import_cron_logs` (
                `import_cron_logs_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `import_mod_name` VARCHAR (255) NOT NULL,
                `import_class_name` VARCHAR (255) NOT NULL,
                `date_log` DATETIME NOT NULL,
                `type` ENUM ('warning','info', 'error'),
                `text` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `import_cron_logs` 
                ADD INDEX (`date_log`);";
    $this->addQuery($query);

    $this->makeRevision("0.02");
    $this->setModuleCategory("import", "echange");

    $this->mod_version = "0.03";
  }
}
