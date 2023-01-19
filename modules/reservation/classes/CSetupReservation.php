<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Reservation;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupReservation extends CSetup {
  function __construct() {
    parent::__construct();

    $this->mod_name = "reservation";
    $this->makeRevision("0.0");

    $this->makeRevision("0.01");

    $query = "CREATE TABLE `commentaire_planning` (
      `commentaire_planning_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `salle_id` INT (11) UNSIGNED,
      `libelle` VARCHAR (255) NOT NULL,
      `commentaire` TEXT,
      `debut` DATETIME NOT NULL,
      `fin` DATETIME NOT NULL
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `commentaire_planning` 
      ADD INDEX (`salle_id`),
      ADD INDEX (`debut`),
      ADD INDEX (`fin`);";
    $this->addQuery($query);

    $this->makeRevision("0.02");
    $query = "ALTER TABLE `commentaire_planning` 
      ADD `color` CHAR (6) DEFAULT 'DDDDDD' AFTER `commentaire`;";
    $this->addQuery($query);

    $this->makeRevision("0.03");
    $query = "CREATE TABLE `examen_operation` (
      `examen_operation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `completed` ENUM ('0','1') DEFAULT '0',
      `acheminement` ENUM ('courrier','fax','autre'),
      `labo` TEXT,
      `groupe_rh` ENUM ('0','1') DEFAULT '0',
      `flacons` INT (11) UNSIGNED,
      `auto_transfusion` ENUM ('0','1') DEFAULT '0',
      `ecg` ENUM ('0','1') DEFAULT '0',
      `radio_thorax` ENUM ('0','1') DEFAULT '0',
      `radios_autres` TEXT,
      `physio_preop` TEXT,
      `physio_postop` TEXT
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.04");
    $this->addPrefQuery("planning_resa_height", 1500);

    $this->makeRevision("0.05");
    $this->addFunctionalPermQuery("planning_resa_days_limit", '0');

    $this->makeRevision("0.06");
    $this->addFunctionalPermQuery("planning_resa_past_days_limit", '0');

    $this->makeRevision("0.07");
    $this->setModuleCategory("circuit_patient", "metier");

    $this->mod_version = "0.08";
  }
}
