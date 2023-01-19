<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupLabo extends CSetup {

  /**
   * CSetupdPlabo constructor.
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "dPlabo";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `catalogue_labo` (
                `catalogue_labo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `pere_id` INT(11) UNSIGNED DEFAULT NULL,
                `identifiant` VARCHAR(255) NOT NULL,
                `libelle` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`catalogue_labo_id`),
                INDEX (`pere_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `examen_labo` (
                `examen_labo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `catalogue_labo_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `identifiant` VARCHAR(255) NOT NULL,
                `libelle` VARCHAR(255) NOT NULL,
                `type` ENUM('bool','num','str') NOT NULL DEFAULT 'num',
                `unite` VARCHAR(255) DEFAULT NULL,
                `min` FLOAT DEFAULT NULL,
                `max` FLOAT DEFAULT NULL,
                PRIMARY KEY (`examen_labo_id`),
                INDEX (`catalogue_labo_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "CREATE TABLE `pack_examens_labo` (
                `pack_examens_labo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `function_id` INT(11) UNSIGNED DEFAULT NULL,
                `libelle` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`pack_examens_labo_id`),
                INDEX (`function_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `pack_item_examen_labo` (
                `pack_item_examen_labo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
                `catalogue_labo_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `pack_examens_labo_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `examen_labo_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`pack_item_examen_labo_id`),
                INDEX (`pack_examens_labo_id`),
                INDEX (`examen_labo_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `pack_item_examen_labo` DROP `catalogue_labo_id`";
    $this->addQuery($query);
    $query = "CREATE TABLE `prescription_labo` (
                `prescription_labo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `consultation_id` INT(11) UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`prescription_labo_id`),
                INDEX (`consultation_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `prescription_labo_examen` (
                `prescription_labo_examen_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `prescription_labo_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `examen_labo_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`prescription_labo_examen_id`),
                INDEX (`prescription_labo_id`),
                INDEX (`examen_labo_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $this->addDependency("dPpatients", "0.1");
    $query = "ALTER TABLE `prescription_labo`
                CHANGE `consultation_id` `patient_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;";
    $this->addQuery($query);
    $query = "ALTER TABLE `prescription_labo`
               ADD `praticien_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0 AFTER `patient_id`;";
    $this->addQuery($query);
    $query = "ALTER TABLE `prescription_labo`
                ADD `date` DATETIME DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `prescription_labo_examen`
                ADD `resultat` VARCHAR( 255 ) DEFAULT NULL,
                ADD `date` DATETIME DEFAULT NULL,
                ADD `commentaire` TEXT DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `prescription_labo_examen`
                CHANGE `date` `date` DATE DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `examen_labo`
                ADD `deb_application` DATE,
                ADD `fin_application` DATE,
                ADD `realisateur` INT(11) UNSIGNED,
                ADD `applicabilite` ENUM('homme','femme','unisexe'),
                ADD `age_min` INT(11) UNSIGNED,
                ADD `age_max` INT(11) UNSIGNED,
                ADD `technique` TEXT,
                ADD `materiel` TEXT,
                ADD `type_prelevement` VARCHAR(255),
                ADD `methode_prelevement` TEXT,
                ADD `conservation` TEXT,
                ADD `temps_conservation` INT(11) UNSIGNED,
                ADD `quantité` INT(11) UNSIGNED,
                ADD `jour_execution` VARCHAR(255),
                ADD `duree_execution` INT(11) UNSIGNED,
                ADD `remarques` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "ALTER TABLE `examen_labo`
                CHANGE `quantité` `quantite` INT(11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $query = "ALTER TABLE `examen_labo`
                CHANGE `type_prelevement` `type_prelevement` ENUM('sang','urine','biopsie');";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `examen_labo`
                ADD `execution_lun` ENUM('0','1'),
                ADD `execution_mar` ENUM('0','1'),
                ADD `execution_mer` ENUM('0','1'),
                ADD `execution_jeu` ENUM('0','1'),
                ADD `execution_ven` ENUM('0','1'),
                ADD `execution_sam` ENUM('0','1'),
                ADD `execution_dim` ENUM('0','1'),
                DROP `jour_execution`;";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `examen_labo`
                CHANGE `quantite` `quantite_prelevement` FLOAT,
                ADD `unite_prelevement` VARCHAR(255);";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `prescription_labo`
                ADD `verouillee` ENUM('0','1');";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $query = "ALTER TABLE `prescription_labo`
                ADD `validee` ENUM('0','1');";
    $this->addQuery($query);

    $this->makeRevision("0.22");
    $query = "ALTER TABLE `pack_item_examen_labo`
                ADD UNIQUE (`pack_examens_labo_id` , `examen_labo_id`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `prescription_labo_examen`
                ADD UNIQUE (`prescription_labo_id` , `examen_labo_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.23");
    $query = "ALTER TABLE `prescription_labo`
                ADD `urgence` ENUM('0','1');";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `pack_examens_labo`
                ADD `code` INT(11);";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "ALTER TABLE `prescription_labo_examen`
                ADD `pack_examens_labo_id` INT(11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `catalogue_labo`
                ADD `function_id` INT(11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $query = "ALTER TABLE `examen_labo`
                ADD `obsolete` ENUM('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $query = "ALTER TABLE `catalogue_labo`
                CHANGE `identifiant` `identifiant` VARCHAR(10) NOT NULL, 
                ADD `obsolete` ENUM('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $query = "ALTER TABLE `pack_examens_labo`  
                ADD `obsolete` ENUM('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `catalogue_labo` 
                ADD INDEX (`function_id`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `examen_labo` 
                ADD INDEX (`deb_application`),
                ADD INDEX (`fin_application`),
                ADD INDEX (`realisateur`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `prescription_labo` 
                ADD INDEX (`date`),
                ADD INDEX (`praticien_id`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `prescription_labo_examen` 
                ADD INDEX (`pack_examens_labo_id`),
                ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.29");

    $query = "ALTER TABLE `examen_labo`
                CHANGE `type` `type` ENUM ('bool','num','float','str') NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.30");

    $this->addDefaultConfig("dPlabo CCatalogueLabo remote_name");
    $this->addDefaultConfig("dPlabo CCatalogueLabo remote_url");
    $this->addDefaultConfig("dPlabo CPackExamensLabo remote_url");

    $this->makeRevision("0.31");
    $this->setModuleCategory("plateau_technique", "metier");

    $this->mod_version = "0.32";
  }
}
