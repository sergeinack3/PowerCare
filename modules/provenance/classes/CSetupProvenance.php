<?php
/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Provenance;

use Ox\Core\CSetup;

/**
 * @package Ox\Mediboard\Provenance
 * @codeCoverageIgnore
 */
class CSetupProvenance extends CSetup {
  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "provenance";

    $this->makeRevision("0.0");

    $query = "CREATE TABLE `provenance` (
                `provenance_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (50) NOT NULL,
                `desc` VARCHAR (255),
                `actif` ENUM ('0','1') NOT NULL DEFAULT '1'
                )/*! ENGINE=MyISAM */;";

    $this->addQuery($query);

    $query = "ALTER TABLE `provenance`
                ADD INDEX (`group_id`);";

    $this->addQuery($query);

    $query = "CREATE TABLE `provenance_patient` (
                `provenance_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `provenance_id` INT (11) UNSIGNED NOT NULL,
                `commentaire` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";

    $this->addQuery ($query);

    $query = "ALTER TABLE `provenance_patient`
                ADD INDEX (`patient_id`),
                ADD INDEX (`provenance_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.1");

    $this->setModuleCategory("dossier_patient", "metier");

    $this->mod_version = "0.2";
  }
}
