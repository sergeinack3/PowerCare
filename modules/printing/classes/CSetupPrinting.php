<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Printing;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupPrinting extends CSetup {
  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "printing";
    $this->makeRevision("0.0");

    $query = "CREATE TABLE `source_lpr` (
      `source_lpr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `name` VARCHAR (255) NOT NULL,
      `object_id` BIGINT DEFAULT NULL,
      `object_class` VARCHAR(30) DEFAULT NULL,
      `host` TEXT NOT NULL,
      `port` INT (11) DEFAULT NULL,
      `user` VARCHAR (255),
      `printer_name` VARCHAR (50)
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.01");
    $query = "CREATE TABLE `source_smb` (
      `source_smb_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `name` VARCHAR (255) NOT NULL,
      `object_id` BIGINT DEFAULT NULL,
      `object_class` VARCHAR(30) DEFAULT NULL,
      `host` TEXT NOT NULL,
      `port` INT (11) DEFAULT NULL,
      `user` VARCHAR (255),
      `password` VARCHAR (50),
      `workgroup` VARCHAR (50), 
      `printer_name` VARCHAR (50)
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.02");
    $query = "CREATE TABLE IF NOT EXISTS `printer` (
      `printer_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `function_id` INT (11) DEFAULT NULL,
      `object_id` INT (11) DEFAULT NULL,
      `object_class` VARCHAR (255) DEFAULT NULL
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.03");
    $query = "ALTER TABLE `printer`
      ADD `label` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.04");
    $this->setModuleCategory("parametrage", "metier");

    $this->mod_version = "0.05";
  }
}
