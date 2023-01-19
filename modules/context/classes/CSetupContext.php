<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Mediboard\Context;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupContext extends CSetup {

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "context";
    $this->makeRevision("0.0");

    $this->makeRevision("0.1");

    $query = "CREATE TABLE `contextual_integration` (
                `contextual_integration_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `active` ENUM ('0','1') DEFAULT '0',
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `url` VARCHAR (255) NOT NULL,
                `title` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `icon_url` VARCHAR (255),
                `display_mode` ENUM ('modal','popup','tooltip','current_tab','new_tab','none')
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `contextual_integration` 
                ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `contextual_integration_location` (
                `contextual_integration_location_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `integration_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `location` VARCHAR (50) NOT NULL,
                `button_type` ENUM ('icon','button','button_text') NOT NULL
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `contextual_integration_location` 
                ADD INDEX (`integration_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.2");

    $this->addDefaultConfig("context General token_lifetime" , "context token_lifetime");

    $this->makeRevision("0.21");
    $this->setModuleCategory("systeme", "administration");

    $this->makeRevision("0.22");
      $query = "ALTER TABLE `contextual_integration`
                ADD `icon_name` VARCHAR (255);";
      $this->addQuery($query);

    $this->mod_version = "0.23";
  }
}
