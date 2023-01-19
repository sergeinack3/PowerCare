<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupPersonnel extends CSetup {

  function __construct() {
    parent::__construct();

    $this->mod_name = "dPpersonnel";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `affectation_personnel` (
      `affect_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,  
      `user_id` INT(11) UNSIGNED NOT NULL, 
      `realise` ENUM('0','1') NOT NULL, 
      `debut` DATETIME, 
      `fin` DATETIME, 
      `object_id` INT(11) UNSIGNED NOT NULL, 
      `object_class` VARCHAR(25) NOT NULL, 
      PRIMARY KEY (`affect_id`)
      ) /*! ENGINE=MyISAM */ COMMENT='Table des affectations du personnel';";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `affectation_personnel`
      ADD `tag` VARCHAR(80);";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "CREATE TABLE `personnel` (
      `personnel_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
      `user_id` INT(11) UNSIGNED NOT NULL, 
      `emplacement` ENUM('op','reveil','service') NOT NULL, 
      PRIMARY KEY (`personnel_id`)) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `affectation_personnel` 
      CHANGE `user_id` `personnel_id` INT(11) UNSIGNED NOT NULL;";
    $this->addQuery($query);

    $query = "UPDATE `affectation_personnel` 
      SET `tag` = 'op' 
      WHERE `tag` IS NULL;";
    $this->addQuery($query);

    $query = "INSERT INTO `personnel`
      SELECT '', affectation_personnel.personnel_id, affectation_personnel.tag
      FROM `users`, `affectation_personnel`
      WHERE users.user_id = affectation_personnel.personnel_id 
        AND users.user_type = '14' 
        AND users.template = '0'
      GROUP BY users.user_id, affectation_personnel.tag;";
    $this->addQuery($query);

    $query = "UPDATE `affectation_personnel`, `personnel`
      SET affectation_personnel.personnel_id = personnel.personnel_id
      WHERE affectation_personnel.personnel_id = personnel.user_id
      AND affectation_personnel.tag = personnel.emplacement;";
    $this->addQuery($query);

    $query = "ALTER TABLE `affectation_personnel`
      DROP `tag`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `affectation_personnel` 
      ADD INDEX ( `personnel_id` );";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `personnel`
      CHANGE `emplacement` `emplacement` ENUM('op','op_panseuse','reveil','service') NOT NULL;";
    $this->addQuery($query);

    $query = "INSERT INTO `personnel`
      SELECT '', personnel.user_id, 'op_panseuse'
      FROM `personnel`
      WHERE personnel.emplacement = 'op';";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `personnel`
      ADD `actif` ENUM('0','1') DEFAULT '1' NOT NULL";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `affectation_personnel` 
      ADD INDEX (`debut`),
      ADD INDEX (`fin`),
      ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `personnel` 
            ADD INDEX (`user_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `personnel` 
      CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade') NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "CREATE TABLE `plageVacances` (
      `plage_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `date_debut` DATE,
      `date_fin` DATE,
      `libelle` VARCHAR (255)
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `plageVacances` 
      ADD INDEX (`date_debut`),
      ADD INDEX (`date_fin`);";
    $this->addQuery($query);

    $this->makeRevision("0.17");

    $query = "ALTER TABLE `plageVacances` 
      ADD `user_id` INT (11) UNSIGNED;";
    $this->addQuery($query);
    $query = "ALTER TABLE `plageVacances` 
      ADD INDEX (`user_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `plageVacances` 
      CHANGE `user_id` `user_id` INT (11) UNSIGNED NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `plageVacances` 
      CHANGE `date_debut` `date_debut` DATE NOT NULL,
      CHANGE `date_fin` `date_fin` DATE NOT NULL,
      CHANGE `libelle` `libelle` VARCHAR (255) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `plageVacances` 
      ADD `replacer_id` INT (11) UNSIGNED;";
    $this->addQuery($query);
    $query = "ALTER TABLE `plageVacances` 
      ADD INDEX (`replacer_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $query = "RENAME TABLE  `plageVacances` TO  `plageconge`;";
    $this->addQuery($query);

    $this->makeRevision("0.22");
    $query = "ALTER TABLE `personnel` 
              CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade','brancardier') NOT NULL DEFAULT 'op';";
    $this->addQuery($query);

    $this->makeRevision("0.23");

    $query = "ALTER TABLE `personnel`
                CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade','brancardier','sagefemme','manipulateur') NOT NULL DEFAULT 'op';";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `affectation_personnel`
      ADD `parent_affectation_id` INT (11) UNSIGNED AFTER `personnel_id`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `affectation_personnel`
      ADD INDEX (`parent_affectation_id`);";
    $this->addQuery($query);

    $this->makeRevision('0.25');

    $query = "ALTER TABLE `personnel`
                CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade','brancardier','sagefemme','manipulateur', 'aux_puericulture') NOT NULL DEFAULT 'op';";
    $this->addQuery($query);

    $this->makeRevision('0.26');
    $query = "ALTER TABLE `plageconge`
      CHANGE `date_debut` `date_debut` DATETIME NOT NULL,
      CHANGE `date_fin` `date_fin` DATETIME NOT NULL;";
    $this->addQuery($query);

    $query = "UPDATE `plageconge`
      SET `date_fin` = DATE_FORMAT(date_fin, '%Y-%m-%d 23:59:59');";
    $this->addQuery($query);
    $this->makeRevision('0.27');

    $query = "ALTER TABLE `personnel`
      CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade','brancardier','sagefemme','manipulateur','aux_puericulture','instrumentiste') NOT NULL DEFAULT 'op';";
    $this->addQuery($query);
    $this->makeRevision('0.28');

    $query = "ALTER TABLE `plageconge`
                ADD `pct_retrocession` FLOAT DEFAULT '70';";
    $this->addQuery($query);
    $this->makeRevision('0.29');

    $query = "CREATE TABLE `remplacement` (
                `remplacement_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `debut` DATETIME NOT NULL,
                `fin` DATETIME NOT NULL,
                `remplace_id` INT (11) UNSIGNED NOT NULL,
                `remplacant_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                INDEX (`debut`),
                INDEX (`fin`),
                INDEX (`remplace_id`),
                INDEX (`remplacant_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision('0.30');
    $query = "ALTER TABLE `personnel`
      CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade','brancardier','sagefemme','manipulateur','aux_puericulture','instrumentiste','circulante') NOT NULL DEFAULT 'op';";
    $this->addQuery($query);

    $this->makeRevision("0.31");

    $this->addDefaultConfig("dPpersonnel CPlageConge show_replacer");

    $this->makeEmptyRevision("0.32");

    /*$query = "ALTER TABLE `affectation_personnel`
                ADD INDEX object (object_class, object_id);";
    $this->addQuery($query);*/

    $this->makeRevision("0.33");
    $this->setModuleCategory("circuit_patient", "metier");

    $this->makeRevision("0.34");
    $query = "ALTER TABLE `personnel`
       CHANGE `emplacement` `emplacement` ENUM ('op','op_panseuse','reveil','service','iade','brancardier','sagefemme','manipulateur','aux_puericulture','instrumentiste','circulante','aide_soignant') NOT NULL DEFAULT 'op';";
    $this->addQuery($query);

    $this->mod_version = "0.35";
  }
}
    
