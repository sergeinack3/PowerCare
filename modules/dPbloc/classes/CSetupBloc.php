<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CAppUI;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Admin\CUser;

/**
 * @codeCoverageIgnore
 */
class CSetupBloc extends CSetup {
  /**
   * Change prat usernames to prat ids
   *
   * @return bool
   */
  protected function swapPratIds() {
    $ds = CSQLDataSource::get("std");

    $user = new CUser();

    // Changement des chirurgiens
    $query      = "SELECT id_chir
        FROM plagesop
        GROUP BY id_chir";
    $listPlages = $ds->loadList($query);
    foreach ($listPlages as $plage) {
      $where["user_username"] = "= '" . $plage["id_chir"] . "'";
      $user->loadObject($where);
      if ($user->user_id) {
        $query = "UPDATE plagesop
            SET chir_id = '$user->user_id'
            WHERE id_chir = '$user->user_username'";
        $ds->exec($query);
        $ds->error();
      }
    }

    //Changement des anesthésistes
    $query      = "SELECT id_anesth
         FROM plagesop
         GROUP BY id_anesth";
    $listPlages = $ds->loadList($query);
    foreach ($listPlages as $plage) {
      $where["user_username"] = "= '" . $plage["id_anesth"] . "'";
      $user->loadObject($where);
      if ($user->user_id) {
        $query = "UPDATE plagesop
            SET anesth_id = '$user->user_id'
            WHERE id_anesth = '$user->user_username'";
        $ds->exec($query);
        $ds->error();
      }
    }

    return true;
  }

  /**
   * Update SSPI against existing blocs
   *
   * @return bool
   */
  protected function updateSSPI() {
    $ds = CSQLDataSource::get("std");

    $query = "INSERT INTO `sspi` (`libelle`, `group_id`) SELECT `nom`, `group_id` FROM `bloc_operatoire`;";
    $ds->exec($query);

    $query = "INSERT INTO `sspi_link` (`bloc_id`, `sspi_id`)
      SELECT `bloc_operatoire_id`, `sspi_id` FROM `bloc_operatoire`
      LEFT JOIN `sspi` ON `sspi`.`libelle` = `bloc_operatoire`.`nom` AND `sspi`.`group_id` = `bloc_operatoire`.`group_id`;";
    $ds->exec($query);

    $query = "UPDATE `poste_sspi` 
      JOIN `sspi_link` ON `sspi_link`.`bloc_id` = `poste_sspi`.`bloc_id`
      SET `poste_sspi`.`sspi_id` = `sspi_link`.`sspi_id`;";
    $ds->exec($query);

    $query = "UPDATE `operations`
      LEFT JOIN `poste_sspi` ON `poste_sspi`.`poste_sspi_id` = `operations`.`poste_sspi_id`
      SET `operations`.`sspi_id` = `poste_sspi`.`sspi_id`
      WHERE `operations`.`poste_sspi_id` IS NOT NULL";
    $ds->exec($query);

    return true;
  }

  /**
   * @inheritDoc
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "dPbloc";

    $this->addDependency("dPpersonnel", "0.11");

    $this->makeRevision("0.0");
    $query = "CREATE TABLE plagesop (
                id bigint(20) NOT NULL auto_increment,
                id_chir varchar(20) NOT NULL default '0',
                id_anesth varchar(20) default NULL,
                id_spec tinyint(4) default NULL,
                id_salle tinyint(4) NOT NULL default '0',
                date date NOT NULL default '0000-00-00',
                debut time NOT NULL default '00:00:00',
                fin time NOT NULL default '00:00:00',
                PRIMARY KEY  (id)
              ) /*! ENGINE=MyISAM */ COMMENT='Table des plages d opération';";
    $this->addQuery($query);
    $query = "CREATE TABLE sallesbloc (
                id tinyint(4) NOT NULL auto_increment,
                nom varchar(50) NOT NULL default '',
                PRIMARY KEY  (id)
              ) /*! ENGINE=MyISAM */ COMMENT='Table des salles d opération du bloc';";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `plagesop`
                ADD INDEX ( `id_chir` ),
                ADD INDEX ( `id_anesth` ),
                ADD INDEX ( `id_spec` ),
                ADD INDEX ( `id_salle` ),
                ADD INDEX ( `date` )";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `plagesop`
                ADD `chir_id` BIGINT DEFAULT '0' NOT NULL AFTER `id`,
                ADD `anesth_id` BIGINT DEFAULT '0' NOT NULL AFTER `chir_id`,
                ADD INDEX ( `chir_id` ),
                ADD INDEX ( `anesth_id` );";
    $this->addQuery($query);
    $this->addMethod("swapPratIds");

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `sallesbloc` ADD `stats` TINYINT DEFAULT '0' NOT NULL AFTER `nom` ;";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $this->addDependency("dPetablissement", "0.1");
    $query = "ALTER TABLE `sallesbloc` ADD `group_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`;";
    $this->addQuery($query);
    $query = "ALTER TABLE `sallesbloc` ADD INDEX ( `group_id` ) ;";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `plagesop` DROP `id_chir` ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `plagesop` DROP `id_anesth` ;";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `plagesop` CHANGE `id` `plageop_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `sallesbloc` CHANGE `id` `salle_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `plagesop` CHANGE `id_spec` `spec_id` INT( 10 ) DEFAULT NULL ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `plagesop` CHANGE `id_salle` `salle_id` INT( 10 ) DEFAULT '0' NOT NULL ;";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "ALTER TABLE `plagesop` ADD `temps_inter_op` TIME NOT NULL DEFAULT '00:15:00' ;";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $query = "ALTER TABLE `plagesop`
                CHANGE `plageop_id` `plageop_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `chir_id` `chir_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `anesth_id` `anesth_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `spec_id` `spec_id` int(11) unsigned NULL,
                CHANGE `salle_id` `salle_id` int(11) unsigned NOT NULL DEFAULT '0';";
    $this->addQuery($query);
    $query = "ALTER TABLE `sallesbloc`
                CHANGE `salle_id` `salle_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `group_id` `group_id` int(11) unsigned NOT NULL DEFAULT '1',
                CHANGE `stats` `stats` enum('0','1') NOT NULL DEFAULT '0',
                CHANGE `nom` `nom` varchar(255) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `plagesop` ADD INDEX ( `debut` )";
    $this->addQuery($query);
    $query = "ALTER TABLE `plagesop` ADD INDEX ( `fin` )";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `plagesop`
                CHANGE `chir_id` `chir_id` int(11) unsigned NULL DEFAULT NULL,
                CHANGE `anesth_id` `anesth_id` int(11) unsigned NULL DEFAULT NULL;";
    $this->addQuery($query);
    $query = "UPDATE `plagesop` SET `chir_id` = NULL WHERE `chir_id` = '0';";
    $this->addQuery($query);
    $query = "UPDATE `plagesop` SET `anesth_id` = NULL WHERE `anesth_id` = '0';";
    $this->addQuery($query);
    $query = "UPDATE `plagesop` SET `spec_id` = NULL WHERE `spec_id` = '0';";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `plagesop`
            ADD `max_intervention` int(11) unsigned NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $query = "ALTER TABLE `plagesop`
            CHANGE `max_intervention` `max_intervention` INT(11);";
    $this->addQuery($query);

    $this->makeRevision("0.22");
    $query = "CREATE TABLE `bloc_operatoire` (
            `bloc_operatoire_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
            `group_id` INT (11) UNSIGNED NOT NULL,
            `nom` VARCHAR (255) NOT NULL) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `bloc_operatoire` ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $query = "INSERT INTO `bloc_operatoire` (`nom`, `group_id`)
            SELECT 'Bloc principal', `group_id`
            FROM `groups_mediboard`";
    $this->addQuery($query);

    $query = "ALTER TABLE `sallesbloc` 
            CHANGE `group_id` `bloc_id` INT( 11 ) UNSIGNED NOT NULL";
    $this->addQuery($query);

    $query = "UPDATE `sallesbloc` 
            SET `bloc_id` = (
              SELECT `bloc_operatoire_id` 
              FROM `bloc_operatoire` 
              WHERE `sallesbloc`.`bloc_id` = `bloc_operatoire`.`group_id`
              LIMIT 1
            );";
    $this->addQuery($query);

    $this->makeRevision("0.23");
    $query = "ALTER TABLE `plagesop` 
            ADD `spec_repl_id` INT (11) UNSIGNED,
            ADD `delay_repl` INT (11);";
    $this->addQuery($query);
    $query = "ALTER TABLE `plagesop`
            ADD INDEX (`spec_repl_id`),
            ADD INDEX (`delay_repl`)";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `plagesop` 
            ADD `actes_locked` ENUM('0','1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "ALTER TABLE `plagesop` ADD `unique_chir` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `sallesbloc`
              ADD `dh` ENUM ('0','1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $query = "ALTER TABLE `bloc_operatoire`
      ADD `days_locked` INT (11) UNSIGNED DEFAULT '0';";
    $this->addQuery($query);

    global $dPconfig;
    $days_locked = (isset($dPconfig["dPbloc"]["CPlageOp"]["days_locked"]) ?
      CAppUI::conf("dPbloc CPlageOp days_locked") : 0);

    $query = "UPDATE `bloc_operatoire`
     SET days_locked = '$days_locked'";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `plagesop` 
      ADD `verrouillage` ENUM ('defaut','non','oui') DEFAULT 'defaut' AFTER max_intervention;";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $this->addPrefQuery("suivisalleAutonome", 0);

    $this->makeRevision("0.30");
    $query = "CREATE TABLE `blocage` (
      `blocage_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `salle_id` INT (11) UNSIGNED NOT NULL,
      `libelle` VARCHAR (255),
      `deb` DATE NOT NULL,
      `fin` DATE NOT NULL
      ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `blocage` 
      ADD INDEX (`salle_id`),
      ADD INDEX (`deb`),
      ADD INDEX (`fin`);";
    $this->addQuery($query);

    $this->makeRevision("0.31");

    $query = "CREATE TABLE `ressource_materielle` (
      `ressource_materielle_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `type_ressource_id` INT (11) UNSIGNED NOT NULL,
      `group_id` INT (11) UNSIGNED NOT NULL,
      `libelle` VARCHAR (255) NOT NULL,
      `deb_activite` DATE,
      `fin_activite` DATE,
      `retablissement` ENUM ('0','1') DEFAULT '0'
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `ressource_materielle`
      ADD INDEX (`type_ressource_id`),
      ADD INDEX (`group_id`),
      ADD INDEX (`deb_activite`),
      ADD INDEX (`fin_activite`),
      ADD INDEX (`retablissement`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `type_ressource` (
      `type_ressource_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `group_id` INT (11) UNSIGNED NOT NULL,
      `libelle` VARCHAR (255) NOT NULL,
      `description` TEXT
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `type_ressource` 
      ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `usage_ressource` (
      `usage_ressource_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ressource_materielle_id` INT (11) UNSIGNED NOT NULL,
      `besoin_id` INT (11) UNSIGNED NOT NULL,
      `commentaire` TEXT
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `usage_ressource` 
      ADD INDEX (`ressource_materielle_id`),
      ADD INDEX (`besoin_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `indispo_ressource` (
      `indispo_ressource_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ressource_materielle_id` INT (11) UNSIGNED NOT NULL,
      `deb` DATE NOT NULL,
      `fin` DATE NOT NULL,
      `commentaire` TEXT
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "CREATE TABLE `besoin_ressource` (
      `besoin_ressource_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `type_ressource_id` INT (11) UNSIGNED NOT NULL,
      `protocole_id` INT (11) UNSIGNED NOT NULL,
      `operation_id` INT (11) UNSIGNED NOT NULL,
      `commentaire` TEXT
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `besoin_ressource` 
      ADD INDEX (`type_ressource_id`),
      ADD INDEX (`protocole_id`),
      ADD INDEX (`operation_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.32");
    $query = "ALTER TABLE `usage_ressource`
      CHANGE `besoin_id` `besoin_ressource_id` INT (11) UNSIGNED NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.33");
    $query = "ALTER TABLE `ressource_materielle` 
      CHANGE `retablissement` `retablissement` TIME;";
    $this->addQuery($query);

    $this->makeRevision("0.34");
    $query = "UPDATE `ressource_materielle`
      SET `retablissement` = '00:00:00'
      WHERE `retablissement` IS NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.35");
    $query = "CREATE TABLE `poste_sspi` (
      `poste_sspi_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `group_id` INT (11) UNSIGNED NOT NULL,
      `nom` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `poste_sspi`
      ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `bloc_operatoire` 
      ADD `poste_sspi_id` INT (11) UNSIGNED AFTER `group_id`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `bloc_operatoire` 
      ADD INDEX (`poste_sspi_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.36");
    $query = "ALTER TABLE `bloc_operatoire`
      DROP `poste_sspi_id`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `poste_sspi`
                ADD `bloc_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.37");
    $query = "ALTER TABLE `plagesop`
                ADD `secondary_function_id` INT (11) UNSIGNED AFTER `chir_id`,
                ADD INDEX (`secondary_function_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.38");
    $query = "ALTER TABLE `bloc_operatoire`
                ADD `tel` VARCHAR (20),
                ADD `fax` VARCHAR (20);";
    $this->addQuery($query);

    $this->makeRevision("0.39");
    $query = "ALTER TABLE `indispo_ressource`
      CHANGE `deb` `deb` DATETIME NOT NULL,
      CHANGE `fin` `fin` DATETIME NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.40");
    $query = "ALTER TABLE `bloc_operatoire`
                ADD `type` ENUM ('chir','obst') NOT NULL DEFAULT 'chir' AFTER `nom`;";
    $this->addQuery($query);

    $this->makeEmptyRevision("0.41");
    $this->addPrefQuery("startAutoRefreshAtStartup", 0);

    $this->makeRevision("0.42");
    $query = "ALTER TABLE `bloc_operatoire`
                ADD `cheklist_man` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.43");

    $query = "ALTER TABLE `bloc_operatoire`
                DROP `cheklist_man`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `sallesbloc`
                ADD `cheklist_man` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.44");
    $query = "ALTER TABLE `plagesop`
      ADD `original_owner_id` INT (11) UNSIGNED AFTER `salle_id`,
      ADD `original_function_id` INT (11) UNSIGNED AFTER `original_owner_id`,
      ADD INDEX (`original_owner_id`),
      ADD INDEX (`original_function_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.45");
    $query = "ALTER TABLE `poste_sspi`
                ADD `type` ENUM ('sspi','preop') DEFAULT 'sspi';";
    $this->addQuery($query);

    $this->makeRevision("0.46");
    $query = "UPDATE `plagesop`
      SET `original_owner_id` = `chir_id`
      WHERE `original_owner_id` IS NULL";
    $this->addQuery($query);

    $query = "UPDATE `plagesop`
      SET `original_function_id` = `spec_id`
      WHERE `original_function_id` IS NULL";
    $this->addQuery($query);
    $this->makeRevision("0.47");
    $query = "ALTER TABLE `bloc_operatoire`
                ADD `use_brancardage` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.48");
    $query = "
    CREATE TABLE `salle_daily_occupation` (
                `miner_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `salle_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `date` DATE NOT NULL,
                `status` ENUM ('mined','remined','postmined'),
                `cumulative_plages_planned` INT (11),
                `nb_plages_planned` INT (11),
                `nb_plages_planned_valid` INT (11),
                `cumulative_real_interventions` INT (11),
                `nb_real_interventions` INT (11),
                `nb_real_intervention_valid` INT (11),
                `cumulative_opened_patient` INT (11),
                `nb_interventions_opened_patient` INT (11),
                `nb_intervention_opened_patient_valid` INT (11),
                `cumulative_plages_minus_interventions` INT (11),
                `cumulative_interventions_minus_plages` INT (11)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `salle_daily_occupation`
                ADD INDEX (`salle_id`),
                ADD INDEX (`date`);";
    $this->addQuery($query);
    $this->makeRevision("0.49");

    $query = "ALTER TABLE `plagesop`
                ADD `entree_chir` TIME,
                ADD `entree_anesth` TIME;";
    $this->addQuery($query);

    $this->addFunctionalPermQuery("allowed_check_entry_bloc", "0");
    $this->makeRevision("0.50");

    $query = "ALTER TABLE `plagesop`
                ADD `max_ambu` INT (11) UNSIGNED,
                ADD `max_hospi` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.51");
    $query = "ALTER TABLE `plagesop` ADD `prevenance_date` DATETIME;";
    $this->addQuery($query);
    $this->makeRevision('0.52');

    $query = "ALTER TABLE `plagesop` ADD `urgence` ENUM('0', '1') DEFAULT '0';";
    $this->addQuery($query);
    $this->makeRevision('0.53');

    $this->addDefaultConfig("dPbloc CPlageOp locked", "dPbloc CPlageOp locked");
    $this->makeRevision('0.54');

    $query = "ALTER TABLE `plagesop` ADD `status` ENUM('occupied', 'free', 'deleted') DEFAULT 'occupied';";
    $this->addQuery($query);
    $this->makeRevision('0.55');

    $this->addDefaultConfig("dPbloc CPlageOp hours_start", "dPbloc CPlageOp hours_start");
    $this->addDefaultConfig("dPbloc CPlageOp hours_stop", "dPbloc CPlageOp hours_stop");
    $this->makeRevision('0.56');

    $query = "ALTER TABLE `plagesop` 
      ADD `debut_reference` TIME NOT NULL AFTER `fin`,
      ADD `fin_reference` TIME NOT NULL AFTER `debut_reference`,
      ADD INDEX (`debut_reference`),
      ADD INDEX (`fin_reference`);";
    $this->addQuery($query);

    $query = "UPDATE `plagesop`
      SET `debut_reference` = `debut`,
          `fin_reference`   = `fin`;";
    $this->addQuery($query);
    $this->makeRevision('0.57');

    $this->addPrefQuery('bloc_display_duration_intervention', '0');
    $this->makeRevision("0.58");

    $query = "ALTER TABLE `bloc_operatoire`
      ADD `presence_preop_ambu` TIME,
      ADD `duree_preop_ambu` TIME;";
    $this->addQuery($query);
    $this->makeRevision("0.59");

    $this->addDependency("dPsalleOp", "0.71");
    $query = "ALTER TABLE `bloc_operatoire`
                ADD `checklist_everyday` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $query = "UPDATE `bloc_operatoire`, `configuration`
                SET bloc_operatoire.checklist_everyday = '0'
                WHERE configuration.feature = 'dPsalleOp CDailyCheckList active'
                AND configuration.value = '0'
                AND configuration.object_class = 'CGroups'
                AND configuration.object_id = bloc_operatoire.group_id";
    $this->addQuery($query);

    $this->makeRevision('0.60');

    $query = "ALTER TABLE `sallesbloc` ADD `color` VARCHAR (6);";
    $this->addQuery($query);

    $this->makeRevision('0.61');

    $this->addPrefQuery('view_planning_bloc', 'vertical');

    $this->makeRevision('0.62');
    $query = "ALTER TABLE `bloc_operatoire`
                CHANGE `type` `type` ENUM ('chir','coro','endo','exte','obst') NOT NULL DEFAULT 'chir';";
    $this->addQuery($query);

    $this->makeRevision('0.63');

    $this->addPrefQuery('planning_bloc_period_1', 7);
    $this->addPrefQuery('planning_bloc_period_2', 19);
    $this->addPrefQuery('planning_bloc_period_3', '');
    $this->addPrefQuery('planning_bloc_period_4', '');

    $this->makeRevision('0.64');

    $query = "ALTER TABLE `blocage`
                CHANGE `deb` `deb` DATETIME NOT NULL,
                CHANGE `fin` `fin` DATETIME NOT NULL;";
    $this->addQuery($query);

    $query = "UPDATE `blocage` SET `fin` = CONCAT(DATE(`fin`), ' 23:59:59');";
    $this->addQuery($query);

    $this->makeRevision('0.65');

    $this->addFunctionalPermQuery('drag_and_drop_horizontal_planning', '1');

    $this->makeRevision('0.66');
    $this->addPrefQuery("auto_entree_bloc_on_pat_select", "0");

    $this->makeRevision('0.67');

    $query = "ALTER TABLE `bloc_operatoire` 
                ADD `actif` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `sallesbloc` 
                ADD `actif` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision('0.68');
    $this->addDependency("dPplanningOp", "2.81");

    $query = "CREATE TABLE `sspi` (
                `sspi_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelle` VARCHAR (255) NOT NULL,
                `group_id` INT (11) UNSIGNED,
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "CREATE TABLE `sspi_link` (
                `sspi_link_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `bloc_id` INT (11) UNSIGNED NOT NULL,
                `sspi_id` INT (11) UNSIGNED NOT NULL,
                INDEX (`bloc_id`),
                INDEX (`sspi_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `poste_sspi` 
                ADD `sspi_id` INT (11) UNSIGNED AFTER `nom`;";
    $this->addQuery($query);

    $this->addMethod("updateSSPI");

    $this->makeRevision('0.69');

    $query = "ALTER TABLE `poste_sspi`
                DROP `bloc_id`,
                DROP `group_id`;";
    $this->addQuery($query);

    $this->makeRevision("0.70");

    $this->addDefaultConfig('dPbloc CPlageOp minutes_interval', 'dPbloc CPlageOp minutes_interval');
    $this->addDefaultConfig('dPbloc printing plage_vide', 'dPbloc CPlageOp plage_vide');
    $this->addDefaultConfig('dPbloc printing libelle_ccam', 'dPbloc CPlageOp libelle_ccam');
    $this->addDefaultConfig('dPbloc printing view_materiel', 'dPbloc CPlageOp view_materiel');
    $this->addDefaultConfig('dPbloc printing view_missing_materiel', 'dPbloc CPlageOp view_missing_materiel');
    $this->addDefaultConfig('dPbloc printing view_extra', 'dPbloc CPlageOp view_extra');
    $this->addDefaultConfig('dPbloc printing view_duree', 'dPbloc CPlageOp view_duree');
    $this->addDefaultConfig('dPbloc printing view_hors_plage', 'dPbloc CPlageOp view_hors_plage');
    $this->addDefaultConfig('dPbloc printing view_convalescence', 'dPbloc CPlageOp view_convalescence');
    $this->addDefaultConfig('dPbloc printing show_comment_sejour', 'dPbloc CPlageOp show_comment_sejour');
    $this->addDefaultConfig('dPbloc printing show_anesth_alerts', 'dPbloc CPlageOp show_anesth_alerts');
    $this->addDefaultConfig('dPbloc CPlageOp systeme_materiel', 'dPbloc CPlageOp systeme_materiel');
    $this->addDefaultConfig('dPbloc printing_standard col1', 'dPbloc CPlageOp planning col1');
    $this->addDefaultConfig('dPbloc printing_standard col2', 'dPbloc CPlageOp planning col2');
    $this->addDefaultConfig('dPbloc printing_standard col3', 'dPbloc CPlageOp planning col3');
    $this->addDefaultConfig('dPbloc affichage chambre_operation', 'dPbloc CPlageOp chambre_operation');
    $this->addDefaultConfig('dPbloc CPlageOp view_empty_plage_op', 'dPbloc CPlageOp view_empty_plage_op');
    $this->addDefaultConfig('dPbloc printing hour_midi_fullprint', 'dPbloc CPlageOp hour_midi_fullprint');
    $this->addDefaultConfig('dPbloc affichage view_prepost_suivi', 'dPbloc CPlageOp view_prepost_suivi');
    $this->addDefaultConfig('dPbloc affichage time_autorefresh', 'dPbloc CPlageOp time_autorefresh');
    $this->addDefaultConfig('dPbloc affichage view_required_tools', 'dPbloc CPlageOp view_required_tools');
    $this->addDefaultConfig('dPbloc affichage view_tools', 'dPbloc CPlageOp view_tools');
    $this->addDefaultConfig('dPbloc affichage view_rques', 'dPbloc CPlageOp view_rques');
    $this->addDefaultConfig('dPbloc affichage view_anesth_type', 'dPbloc CPlageOp view_anesth_type');

    $this->makeRevision('0.71');

    $this->addFunctionalPermQuery('bloc_planning_visibility', 'user_rights');

    $this->makeEmptyRevision("0.72");

    /*$query = "ALTER TABLE `indispo_ressource`
                ADD INDEX (`ressource_materielle_id`),
                ADD INDEX (`deb`),
                ADD INDEX (`fin`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `plagesop`
                ADD INDEX (`prevenance_date`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `poste_sspi`
                ADD INDEX (`sspi_id`);";
    $this->addQuery($query);*/

    $this->makeEmptyRevision("0.73");

    $query = "ALTER TABLE `sallesbloc` 
                ADD `code` VARCHAR (80);";
    $this->addQuery($query);

    $this->makeRevision('0.74');

    $this->addPrefQuery('planning_bloc_show_cancelled_operations', '0');

    $this->makeRevision("0.75");
    $this->setModuleCategory("plateau_technique", "metier");

    $this->makeRevision('0.76');
    $query = "CREATE TABLE `emplacement_salle` (
                `emplacement_salle_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `salle_id` INT (11) UNSIGNED NOT NULL,
                `plan_x` INT (11) NOT NULL,
                `plan_y` INT (11) NOT NULL,
                `color` VARCHAR (6) NOT NULL DEFAULT 'DDDDDD',
                `hauteur` TINYINT (4) UNSIGNED NOT NULL DEFAULT '1',
                `largeur` TINYINT (4) UNSIGNED NOT NULL DEFAULT '1',
                INDEX (`salle_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision('0.77');
    $query = "ALTER TABLE `poste_sspi` 
                ADD `actif` BOOLEAN NOT NULL DEFAULT 1;";
    $this->addQuery($query);

    $this->makeRevision('0.78');
    $query = "ALTER TABLE `sallesbloc` 
                ADD `checklist_defaut_id` INT (11) UNSIGNED,
                ADD `checklist_defaut_has` VARCHAR (255),
                ADD INDEX (`checklist_defaut_id`);";
    $this->addQuery($query);

    $this->makeRevision('0.79');
    $query = "ALTER TABLE `plagesop` 
                ADD `pause` TIME NOT NULL DEFAULT '00:00:00';";
    $this->addQuery($query);

    $this->mod_version = '0.80';
  }
}
