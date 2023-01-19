<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupPmsi extends CSetup {
  /**
   * Standard constructor
   */
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "dPpmsi";
    
    $this->makeRevision("0.0");
    $this->makeRevision("0.1");
    $query = "CREATE TABLE `ghm` (
      `ghm_id` BIGINT NOT NULL AUTO_INCREMENT ,
      `operation_id` BIGINT NOT NULL ,
      `DR` VARCHAR( 10 ) ,
      `DASs` TEXT,
      `DADs` TEXT,
      PRIMARY KEY ( `ghm_id` ) ,
      INDEX ( `operation_id` )
      ) /*! ENGINE=MyISAM */ COMMENT = 'Table des GHM';";
    $this->addQuery($query);
    
    $this->makeRevision("0.11");
    $this->addDependency("dPplanningOp", "0.38");
    $query = "ALTER TABLE `ghm` ADD `sejour_id` INT NOT NULL AFTER `operation_id`;";
    $this->addQuery($query);
    $query = "UPDATE `ghm`, `operations` SET
      `ghm`.`sejour_id` = `operations`.`sejour_id`
      WHERE `ghm`.`operation_id` = `operations`.`operation_id`";
    $this->addQuery($query);
    
    $this->makeRevision("0.12");
    $query = "ALTER TABLE `ghm` DROP `operation_id` ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `ghm` ADD INDEX ( `sejour_id` ) ;";
    $this->addQuery($query);
    
    $this->makeRevision("0.13");
    $query = "ALTER TABLE `ghm`
      CHANGE `ghm_id` `ghm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      CHANGE `sejour_id` `sejour_id` int(11) unsigned NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "DROP TABLE IF EXISTS `ghm`;";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "CREATE TABLE `traitement_dossier` (
                `traitement_dossier_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `traitement` DATETIME,
                `validate` DATETIME,
                `GHS` VARCHAR (255),
                `rss_id` INT (11) UNSIGNED,
                `sejour_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `traitement_dossier`
                ADD INDEX (`traitement`),
                ADD INDEX (`validate`),
                ADD INDEX (`rss_id`),
                ADD INDEX (`sejour_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "  ALTER TABLE `traitement_dossier`
                CHANGE `rss_id` `rss_id` INT (11) UNSIGNED,
                CHANGE `sejour_id` `sejour_id` INT (11) UNSIGNED,
                ADD `dim_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `traitement_dossier`
                ADD INDEX (`dim_id`);";
    $this->addQuery($query);
    $this->makeRevision("0.17");

    $query = "CREATE TABLE `relance_pmsi` (
      `relance_pmsi_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `sejour_id` INT (11) UNSIGNED,
      `patient_id` INT (11) UNSIGNED,
      `chir_id` INT (11) UNSIGNED,
      `datetime_creation` DATETIME,
      `datetime_relance` DATETIME,
      `datetime_cloture` DATETIME,
      `datetime_med` DATETIME,
      `urgence` ENUM ('normal','urgent') DEFAULT 'normal',
      `cro` ENUM ('0','1') DEFAULT '0',
      `cra` ENUM ('0','1') DEFAULT '0',
      `ls` ENUM ('0','1') DEFAULT '0',
      `commentaire_dim` TEXT,
      `commentaire_med` TEXT
    )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `relance_pmsi` 
      ADD INDEX (`sejour_id`),
      ADD INDEX (`patient_id`),
      ADD INDEX (`chir_id`),
      ADD INDEX (`datetime_creation`),
      ADD INDEX (`datetime_relance`),
      ADD INDEX (`datetime_cloture`),
      ADD INDEX (`datetime_med`);";
    $this->addQuery($query);
    $this->makeRevision("0.18");

    $query = "ALTER TABLE `relance_pmsi` 
      ADD `cotation` ENUM ('0','1') DEFAULT '0',
      ADD `autre` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);
    $this->makeRevision("0.19");

    $query = "ALTER TABLE `relance_pmsi` 
      ADD `description` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.20");

    $query = "ALTER TABLE `relance_pmsi` 
                ADD `crana` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $this->setModuleCategory("administratif", "metier");

    $this->mod_version = "0.22";

    // Data source query
    $query = "SHOW TABLES LIKE 'codes_atih';";
    $this->addDatasource("cim10", $query);
  }
}
