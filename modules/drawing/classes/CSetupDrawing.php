<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Drawing;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupDrawing extends CSetup {

  /** @see parent::__construct() */
  function __construct() {
    parent::__construct();
    $this->mod_name = "drawing";
    $this->makeRevision("0.0");

    $this->makeRevision("0.1");
    $this->addDependency("dPfiles", "0.30");

    $query = "CREATE TABLE `drawing_category` (
                `drawing_category_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `description` VARCHAR (255),
                `creation_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `drawing_category`
                ADD INDEX (`creation_datetime`);";
    $this->addQuery($query);

    $this->makeRevision("0.2");
    $this->addPrefQuery("drawing_background", "ffffff");

    $this->makeRevision("0.3");
    $this->addPrefQuery("drawing_advanced_mode", 0);

    $this->makeRevision("0.4");
    $query = "ALTER TABLE `drawing_category`
                CHANGE `description` `description` TEXT,
                ADD `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                ADD `object_class` VARCHAR (80) NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `drawing_category`
                ADD INDEX (`name`),
                ADD INDEX (`object_id`),
                ADD INDEX (`object_class`);";
    $this->addQuery($query);

    $this->makeRevision("0.5");
    $this->delPrefQuery("drawing_advanced_mode");
    $query = "ALTER TABLE `drawing_category`
                ADD `group_id` INT (11) UNSIGNED,
                ADD `function_id` INT (11) UNSIGNED,
                ADD `user_id` INT (11) UNSIGNED,
                DROP `object_id`,
                DROP `object_class`;";
    $this->addQuery($query);
    $query = "ALTER TABLE `drawing_category`
                ADD INDEX (`group_id`),
                ADD INDEX (`function_id`),
                ADD INDEX (`user_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.6");

    $this->addDefaultConfig(
      "drawing General drawing_allow_external_ressource",
      "drawing drawing_allow_external_ressource"
    );
    $this->addDefaultConfig(
      "drawing General drawing_square",
      "drawing drawing_square"
    );

    $this->makeRevision("0.7");
    $this->setModuleCategory("dossier_patient", "metier");

    $this->mod_version = "0.8";
  }
}
