<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;

use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CSaeImport;

/**
 * @codeCoverageIgnore
 */
class CSetupEtablissement extends CSetup {

  /**
   * Ajout des codes pour les Groups
   *
   * @return bool
   */
  protected function addCodeToGroups () {
    $ds = CSQLDataSource::get("std");

    $request = new CRequest();
    $request->addSelect(array("group_id", "code", "text"));
    $request->addTable("groups_mediboard");

    $query = $request->makeSelect();
    $groups = $ds->loadList($query);

    foreach ($groups as $_group) {
      $group_id = $_group["group_id"];
      $code = CMbString::makeInitials($_group["text"]);
      $query = $ds->prepare("UPDATE `groups_mediboard` SET `code`=?1 WHERE `group_id`= ?2", $code, $group_id);
      $ds->exec($query);
    }
    return true;
  }

    protected function importSaeDatabase(): bool
    {
        return (new CSaeImport())->importDatabase();
    }

  /**
   * @see parent::__construct();
   */
  function __construct() {
    parent::__construct();

    $this->mod_type = "core";
    $this->mod_name = "dPetablissement";

    $this->makeRevision("0.0");

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `groups_mediboard` 
                ADD INDEX (`service_urgences_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `groups_mediboard` 
                ADD `finess` INT (9) UNSIGNED ZEROFILL;";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $query = "ALTER TABLE `groups_mediboard` 
                ADD `pharmacie_id` INT (11) UNSIGNED,
                ADD INDEX (`pharmacie_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.22");
    $query = "ALTER TABLE `groups_mediboard` 
                ADD `chambre_particuliere` ENUM ('0','1') NOT NULL DEFAULT '0'";
    $this->addQuery($query);

    $this->makeRevision("0.23");
    $query = "CREATE TABLE `groups_config` (
                `groups_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED,
                `max_comp` INT (11) UNSIGNED,
                `max_ambu` INT (11) UNSIGNED
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `groups_config` 
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `groups_config` 
                ADD `codage_prat` ENUM ('0','1');";
    $this->addQuery($query);
    $query = "ALTER TABLE `groups_config` 
                CHANGE `codage_prat` `codage_prat` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "ALTER TABLE `groups_config` 
                ADD `sip_notify_all_actors` ENUM ('0','1') DEFAULT '0',
                ADD `sip_idex_generator` ENUM ('0','1') DEFAULT '0',
                ADD `smp_notify_all_actors` ENUM ('0','1') DEFAULT '0',
                ADD `smp_idex_generator` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `etab_externe` 
                ADD INDEX (`nom`),
                ADD INDEX (`raison_sociale`),
                ADD INDEX (`cp`),
                ADD INDEX (`ville`),
                ADD INDEX (`tel`),
                ADD INDEX (`fax`),
                ADD INDEX (`finess`),
                ADD INDEX (`siret`),
                ADD INDEX (`ape`);";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $query = "ALTER TABLE `groups_config` 
                ADD `ipp_range_min` INT (11) UNSIGNED,
                ADD `ipp_range_max` INT (11),
                ADD `nda_range_min` INT (11) UNSIGNED,
                ADD `nda_range_max` INT (11);";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `etab_externe` 
                CHANGE `tel` `tel` VARCHAR (20),
                CHANGE `fax` `fax` VARCHAR (20);";
    $this->addQuery($query);
    $query = "ALTER TABLE `groups_mediboard` 
                CHANGE `tel` `tel` VARCHAR (20),
                CHANGE `fax` `fax` VARCHAR (20),
                CHANGE `tel_anesth` `tel_anesth` VARCHAR (20);";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $query = "ALTER TABLE `groups_mediboard` 
                ADD `mail_apicrypt` VARCHAR (50);";
    $this->addQuery($query);
    $this->makeRevision("0.30");

    $query = "ALTER TABLE `groups_mediboard`
                ADD `ean` VARCHAR (30),
                ADD `rcc` VARCHAR (30);";
    $this->addQuery($query);

    $this->makeRevision("0.31");
    $query = "ALTER TABLE `groups_mediboard`
                CHANGE `cp` `cp` VARCHAR( 10 ) NULL DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.32");
    $query = "ALTER TABLE `groups_mediboard`
                ADD `lat` FLOAT,
                ADD `lon` FLOAT;";
    $this->addQuery($query);

    $this->makeRevision("0.33");

    $query = "ALTER TABLE `groups_mediboard`
                ADD `code` VARCHAR (80) NOT NULL,
                ADD `short_name` VARCHAR (255),
                ADD `description` TEXT,
                ADD `user_id` INT (11) UNSIGNED,
                ADD `legal_entity_id` INT (11) UNSIGNED,
                ADD `opening_reason` TEXT,
                ADD `opening_date` DATE,
                ADD `closing_reason` TEXT,
                ADD `closing_date` DATE,
                ADD `activation_date` DATE,
                ADD `inactivation_date` DATE
               ;";
    $this->addQuery($query);

    $query="CREATE TABLE `legal_entity` (
                `legal_entity_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `finess` INT (9) UNSIGNED ZEROFILL,
                `rmess` INT (9) UNSIGNED ZEROFILL,
                `address` TEXT,
                `zip_code` VARCHAR (10),
                `city` VARCHAR (50),
                `country` INT (11),
                `insee` MEDIUMINT (3) UNSIGNED ZEROFILL,
                `siren` INT (9) UNSIGNED ZEROFILL,
                `nic` INT (11),
                `legal_status_code` INT (11) UNSIGNED,
                `code` VARCHAR (80) NOT NULL,
                `short_name` VARCHAR (255),
                `description` TEXT,
                `user_id` INT (11) UNSIGNED,
                `opening_reason` TEXT,
                `opening_date` DATE,
                `closing_reason` TEXT,
                `closing_date` DATE,
                `activation_date` DATE,
                `inactivation_date` DATE
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query="ALTER TABLE `legal_entity`
                ADD INDEX (`legal_status_code`),
                ADD INDEX (`user_id`),
                ADD INDEX (`opening_date`),
                ADD INDEX (`closing_date`),
                ADD INDEX (`activation_date`),
                ADD INDEX (`inactivation_date`);";
    $this->addQuery($query);

    $this->addMethod("addCodeToGroups");

    $this->makeRevision('0.34');

    $query = "ALTER TABLE `etab_externe`
                ADD `priority` ENUM ('0', '1') DEFAULT '0';";
    $this->addQuery($query);
    $this->makeRevision('0.35');

    $query = "ALTER TABLE `etab_externe` 
                ADD `provenance` ENUM ('1','2','3','4','5','6','7','8','R'),
                ADD `destination` ENUM ('0','1','2','3','4','6','7');";
    $this->addQuery($query);

    $this->makeRevision('0.36');

    $query = "ALTER TABLE `etab_externe` CHANGE `cp` `cp` VARCHAR (5);";
    $this->addQuery($query);

    $this->makeRevision('0.36.1');

    $this->addMethod("importSaeDatabase");

    $this->makeRevision('0.37');

    if ($ds = CSQLDataSource::get('sae', true)) {
      if ($ds->hasField('type_autorisation_um', 'spec') && !$ds->hasField('type_autorisation_um', 'spec_char')) {
        $query = "ALTER TABLE `type_autorisation_um`
                  CHANGE `spec` `spec_char` CHAR;";
        $this->addQuery($query, false, 'sae');
      }
    }

    $this->makeRevision('0.38');
    $query = "ALTER TABLE `groups_mediboard` ADD `ean_xml_factu` VARCHAR (30);";
    $this->addQuery($query);

    $this->makeRevision('0.39');

    $query = "ALTER TABLE `groups_mediboard` MODIFY `finess` VARCHAR(9);";
    $this->addQuery($query);

    $query = "ALTER TABLE `etab_externe` MODIFY `finess` VARCHAR(9);";
    $this->addQuery($query);

    $this->makeEmptyRevision("0.40");

    /*$query = "ALTER TABLE `groups_mediboard`
                ADD INDEX (`legal_entity_id`),
                ADD INDEX (`user_id`),
                ADD INDEX (`opening_date`),
                ADD INDEX (`closing_date`),
                ADD INDEX (`activation_date`),
                ADD INDEX (`inactivation_date`);";
    $this->addQuery($query);*/

    $this->makeRevision("0.41");

    $this->addDependency('system', '1.2.98');
    $this->setModuleCategory("systeme", "administration");

    $this->makeRevision("0.42");

    $query = "ALTER TABLE `groups_mediboard` ADD `oid` VARCHAR(255)";
    $this->addQuery($query);

    $this->makeRevision('0.43');

    $this->addQuery('ALTER TABLE `groups_mediboard` DROP COLUMN `ean_xml_factu`;');

    $this->mod_version = '0.44';

    // Data source query
    $query = "SHOW TABLES LIKE 'legal_status'";
    $this->addDatasource("sae", $query);

    if (array_key_exists('sae', CAppUI::conf('db')) && ($dsn = CSQLDataSource::get('sae'))) {
      $test = $dsn->exec('SHOW TABLES LIKE \'legal_status\';');
      if ($test && $dsn->fetchRow($test)) {
        $query = "SELECT * FROM `legal_status` WHERE `status_code` = '1';";
        $this->addDatasource("sae", $query);
      }
    }
  }
}
