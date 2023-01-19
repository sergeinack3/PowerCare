<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\Module\CModule;
use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupSoins extends CSetup {

  function __construct() {
    parent::__construct();

    $this->mod_name = 'soins';

    $this->makeRevision('0.0');

    $this->makeRevision("0.1");

    $query = "CREATE TABLE `sejour_task` (
      `sejour_task_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `sejour_id` INT (11) UNSIGNED NOT NULL,
      `description` TEXT NOT NULL,
      `realise` ENUM ('0','1') DEFAULT '0',
      `resultat` TEXT
      ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `sejour_task` ADD INDEX (`sejour_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "CREATE TABLE `ressource_soin` (
              `ressource_soin_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `libelle` TEXT NOT NULL,
              `cout` FLOAT
            ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "CREATE TABLE `indice_cout` (
              `indice_cout_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `nb` INT (11) NOT NULL,
              `ressource_soin_id` INT (11) UNSIGNED NOT NULL,
              `element_prescription_id` INT (11) UNSIGNED NOT NULL
             ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `indice_cout` 
              ADD INDEX (`ressource_soin_id`),
              ADD INDEX (`element_prescription_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `ressource_soin` 
              CHANGE `libelle` `libelle` VARCHAR (255) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `ressource_soin`
              ADD `code` VARCHAR (255) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    if (CModule::getActive("dPprescription")) {
      $this->moveConf("dPprescription CPrescription max_time_modif_suivi_soins", "soins max_time_modif_suivi_soins");
    }

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `sejour_task`
      ADD `consult_id` INT (11) UNSIGNED,
      ADD INDEX (`consult_id`)";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "UPDATE perm_module
              LEFT JOIN modules ON perm_module.mod_id = modules.mod_id
              SET permission = '2'
              WHERE modules.mod_name = 'soins'";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $this->addPrefQuery("vue_sejours", "standard");

    $this->makeRevision('0.18');

    $query = "ALTER TABLE `sejour_task`
                ADD `date` DATETIME,
                ADD `author_id` INT(11);";
    $this->addQuery($query);
    $this->makeRevision('0.19');

    $this->addDefaultConfig("soins CLit align_right", "soins CLit align_right");
    $this->addDefaultConfig("soins CConstantesMedicales constantes_show", "soins constantes_show");
    $this->addDefaultConfig("soins Pancarte transmissions_hours", "soins transmissions_hours");
    $this->addDefaultConfig("soins Pancarte soin_refresh_pancarte_service", "soins soin_refresh_pancarte_service");
    $this->addDefaultConfig("soins Transmissions cible_mandatory_trans", "soins cible_mandatory_trans");
    $this->addDefaultConfig("soins Transmissions trans_compact", "soins trans_compact");
    $this->addDefaultConfig("soins Sejour refresh_vw_sejours_frequency", "soins refresh_vw_sejours_frequency");
    $this->addDefaultConfig("soins Other show_charge_soins", "soins show_charge_soins");
    $this->addDefaultConfig("soins Other max_time_modif_suivi_soins", "soins max_time_modif_suivi_soins");
    $this->addDefaultConfig("soins Other show_only_lit_bilan", "soins show_only_lit_bilan");
    $this->addDefaultConfig("soins Other ignore_allergies", "soins ignore_allergies");
    $this->addDefaultConfig("soins Other vue_condensee_dossier_soins", "soins vue_condensee_dossier_soins");
    $this->makeRevision('0.20');

    $query = "INSERT INTO `configuration` (`feature`, `value`) VALUES (?1, ?2)";
    $query = $this->ds->prepare($query, "soins Other default_motif_observation", "Observation d'entrée");
    $this->addQuery($query);
    $this->makeRevision('0.21');

    $query = "ALTER TABLE `sejour_task`
                ADD `date_realise` DATETIME,
                ADD `author_realise_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `sejour_task`
                ADD INDEX (`date`),
                ADD INDEX (`author_id`),
                ADD INDEX (`date_realise`),
                ADD INDEX (`author_realise_id`);";
    $this->addQuery($query);
    $this->makeRevision("0.22");

    $this->addPrefQuery("default_services_id", "{}");
    $this->makeRevision("0.23");

    $query = "INSERT INTO `user_preferences` ( `user_id` , `key` , `value` , `pref_id` , `restricted` )
      SELECT user_id, 'default_services_id', value, null, '0'
      FROM `user_preferences`
      WHERE `key` = 'services_ids_hospi'
      AND `value` != '{}'
      AND `user_id` IS NOT NULL
      GROUP BY user_id ;";
    $this->addQuery($query);
    $this->makeRevision("0.24");

    $this->addPrefQuery("hide_line_inactive", "0");
    $this->makeRevision("0.25");

    $this->addPrefQuery("services_ids_soins", "{}");
    $this->makeRevision("0.26");

    $this->addPrefQuery("use_current_day", "0");

    $this->makerevision("0.27");
    $query = "CREATE TABLE `objectif_soin` (
                `objectif_soin_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED NOT NULL,
                `libelle` TEXT NOT NULL,
                `statut` ENUM ('ouvert','atteint','non_atteint') DEFAULT 'ouvert'
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `objectif_soin` ADD INDEX (`sejour_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `objectif_soin_cible` (
                `objectif_soin_cible_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `objectif_soin_id` INT (11) UNSIGNED NOT NULL,
                `object_id` INT (11) UNSIGNED,
                `object_class` ENUM ('CPrescriptionLineElement','CPrescriptionLineMedicament','CPrescriptionLineComment','CCategoryPrescription','CAdministration','CPrescriptionLineMix')
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `objectif_soin_cible`
                ADD INDEX (`objectif_soin_id`),
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `objectif_soin`
                ADD `date` DATETIME NOT NULL,
                ADD `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                ADD `cloture_date` DATETIME,
                ADD `cloture_user_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `objectif_soin`
                ADD INDEX (`date`),
                ADD INDEX (`user_id`),
                ADD INDEX (`cloture_date`),
                ADD INDEX (`cloture_user_id`);";
    $this->addQuery($query);
    $this->makeRevision('0.29');

    $query = "CREATE TABLE `chung_scores` (
                `chung_score_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `vital_signs` ENUM ('0', '1', '2'),
                `activity` ENUM ('0', '1', '2'),
                `nausea` ENUM ('0', '1', '2'),
                `pain` ENUM ('0', '1', '2'),
                `bleeding` ENUM ('0', '1', '2'),
                `total` INT (2),
                INDEX (`sejour_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $this->makeRevision('0.30');

    $this->addPrefQuery("see_plan_semaine", "0");
    $this->makeRevision('0.31');

    $this->addPrefQuery("checked_lines_sortie", "0");
    $this->makeRevision('0.32');

    $this->addPrefQuery("check_show_const_transmission", "0");
    $this->makeRevision('0.33');

    $this->addPrefQuery("check_show_macrocible", "1");
    $this->makeRevision('0.34');

    $this->addPrefQuery("show_categorie_pancarte", "1");
    $this->makeRevision('0.35');

    $this->addPrefQuery("type_view_demande_particuliere", "last_macro");
    $this->makeRevision('0.36');

    $query = "CREATE TABLE `sejour_timing_personnel` (
                `sejour_timing_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `name` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `time_debut` TIME NOT NULL,
                `time_fin` TIME NOT NULL,
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $this->makeRevision('0.37');

    $this->addPrefQuery("preselect_me_care_folder", "1");
    $this->makeRevision('0.38');

    $this->addPrefQuery("check_establishment_grid_mode", "0");
    $this->makeRevision('0.39');

    $this->addPrefQuery("detail_atcd_alle", "1");
    $this->makeRevision('0.40');

    $query = "ALTER TABLE `objectif_soin` 
                ADD `moyens` TEXT,
                ADD `delai` DATE,
                ADD INDEX (`delai`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `objectif_soin_reeval` (
                `objectif_reeval_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `objectif_soin_id` INT (11) UNSIGNED NOT NULL,
                `commentaire` TEXT NOT NULL,
                `date` DATE NOT NULL,
                INDEX (`objectif_soin_id`),
                INDEX (`date`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.41");

    $query = "CREATE TABLE `rdv_externe` (
                `rdv_externe_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `date_debut` DATETIME NOT NULL,
                `duree` INT (11) UNSIGNED,
                 INDEX (`sejour_id`),
                 INDEX (`date_debut`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.42");

    $query = "ALTER TABLE `rdv_externe` 
                ADD `statut` ENUM ('encours','realise','annule') DEFAULT 'encours',
                ADD `commentaire` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.43");
    $this->addPrefQuery("show_bedroom_empty", "0");
    $this->addPrefQuery("show_last_macrocible", "0");

    $this->makeRevision('0.44');

    $query = "ALTER TABLE `objectif_soin` 
                ADD `resultat` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.45");

    $query = "CREATE TABLE `objectif_soin_categorie` (
                `objectif_soin_categorie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelle` VARCHAR (255),
                `description` TEXT,
                `group_id` INT (11) UNSIGNED,
                `actif` ENUM ('0','1') DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `objectif_soin_categorie` 
                ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `objectif_soin` 
                ADD `objectif_soin_categorie_id` INT (11) UNSIGNED,
                ADD `priorite` ENUM ('0','1') DEFAULT '0',
                ADD `intervenants` TEXT,
                ADD `commentaire` TEXT,
                ADD `alerte` ENUM ('0','1') DEFAULT '0';
              ALTER TABLE `objectif_soin` 
                ADD INDEX (`objectif_soin_categorie_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.46");
    $this->addPrefQuery("check_show_diet", "0");

    $this->makeRevision("0.47");

    $query = "ALTER TABLE `objectif_soin` 
                CHANGE `libelle` `libelle` TEXT NOT NULL;";

    $this->addQuery($query);

    $this->makeRevision("0.48");

    $query = "ALTER TABLE `chung_scores`
      ADD `administration_id` INT (11) UNSIGNED AFTER `sejour_id`;";
    $this->addQuery($query);

    $this->makeRevision("0.49");
    $this->setModuleCategory("dossier_patient", "metier");

    $this->makeRevision("0.50");

    $query = "ALTER TABLE `chung_scores`
                ADD INDEX (`administration_id`);";
    $this->addQuery($query);

    $this->mod_version = '0.51';
  }
}
