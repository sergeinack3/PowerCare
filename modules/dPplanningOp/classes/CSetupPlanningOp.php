<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\CompteRendu\CSetupCompteRendu;

/**
 * @codeCoverageIgnore
 */
class CSetupPlanningOp extends CSetup
{
    /**
     * Add default config CIP
     *
     * @return void
     */
    protected function addDefaultConfigCIP(): void
    {
        $path = 'dPplanningOp CSejour use_charge_price_indicator';

        if (@CAppUI::conf($path)) {
            $query = 'INSERT INTO `configuration` (`feature`, `value`) VALUES (?1, ?2);';
            $query = $this->ds->prepare($query, $path, 'obl');
            $this->addQuery($query);
        }
    }

    /**
     * Mise à jour des timings time => dateTime
     *
     * @return bool
     */
    protected function updateTimingsOperation(): bool
    {
        $timings_change = [
            "debut_prepa_preop",
            "fin_prepa_preop",
            "entree_salle",
            "sortie_salle",
            "remise_chir",
            "tto",
            "pose_garrot",
            "prep_cutanee",
            "debut_op",
            "fin_op",
            "retrait_garrot",
            "entree_reveil",
            "sortie_reveil_possible",
            "sortie_reveil_reel",
            "induction_debut",
            "induction_fin",
            "suture_fin",
            "entree_bloc",
            "cleaning_start",
            "cleaning_end",
            "installation_start",
            "installation_end",
            "incision",
        ];
        $query          = "ALTER TABLE `operations`";
        foreach ($timings_change as $key_timing => $_new_timing) {
            if ($key_timing != 0) {
                $query .= ",";
            }
            $query .= " CHANGE `$_new_timing` `old_$_new_timing` TIME";
        }
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`";
        foreach ($timings_change as $key_timing => $_new_timing) {
            if ($key_timing != 0) {
                $query .= ",";
            }
            $query .= " ADD `$_new_timing` DATETIME";
        }
        $this->addQuery($query);

        foreach ($timings_change as $_new_timing) {
            $query = "UPDATE `operations`
                SET operations.$_new_timing = CONCAT_WS(' ', operations.date, DATE_FORMAT(operations.old_$_new_timing, '%H:%i:%s'))
                WHERE `operations`.`date` IS NOT NULL
                AND operations.old_$_new_timing IS NOT NULL;";
            $this->addQuery($query);
        }

        $query = "ALTER TABLE `operations`";
        foreach ($timings_change as $key_timing => $_new_timing) {
            if ($key_timing != 0) {
                $query .= ",";
            }
            $query .= " DROP `old_$_new_timing`";
        }
        $this->addQuery($query);

        return true;
    }

    /**
     * Move config UF soins
     *
     * @return void
     */
    protected function moveConfigUFSoins(): bool
    {
        $affichage_uf = @CAppUI::conf("dPplanningOp CSejour easy_uf_soins");
        $path         = "dPplanningOp CSejour required_uf_soins";

        $query = "UPDATE `configuration` SET `value` = 'obl' WHERE  `value` = '1' AND `feature` = '$path'";
        $this->ds->exec($query);

        if ($affichage_uf == "0") {
            $query = "UPDATE `configuration` SET `value` = 'no' WHERE  `value` = '0' AND `feature` = '$path'";
        } else {
            $query = "UPDATE `configuration` SET `value` = 'opt' WHERE  `value` = '0' AND `feature` = '$path'";
        }

        $this->ds->exec($query);

        return true;
    }

    /**
     * Move laboratories in specifics tables
     *
     * @return bool
     */
    protected function migrateLabosAmplis(): bool
    {
        $request = new CRequest();
        $request->addSelect('group_id, group_id');
        $request->addTable('groups_mediboard');

        foreach ($this->ds->loadColumn($request->makeSelect()) as $_group_id) {
            $query = "INSERT INTO `ampli` (`libelle`, `group_id`, `actif`)
                      SELECT DISTINCT `ampli_rayons_x`, '{$_group_id}', '1'
                      FROM `operations`
                      WHERE `operations`.`ampli_rayons_x` IS NOT NULL;";

            $this->ds->exec($query);

            $query = "INSERT INTO `laboratoire_anapath` (`libelle`, `group_id`, `actif`)
                      SELECT DISTINCT `labo_anapath`, '{$_group_id}', '1'
                      FROM `operations`
                      WHERE `operations`.`labo_anapath` IS NOT NULL;";

            $this->ds->exec($query);

            $query = "INSERT INTO `laboratoire_bacterio` (`libelle`, `group_id`, `actif`)
                      SELECT DISTINCT `labo_bacterio`, '{$_group_id}', '1'
                      FROM `operations`
                      WHERE `operations`.`labo_bacterio` IS NOT NULL;";

            $this->ds->exec($query);
        }

        $query = "UPDATE `operations`
                  LEFT JOIN `sejour` ON `sejour`. `sejour_id` = `operations`.`sejour_id`
                  SET `operations`.`ampli_id` = (
                      SELECT `ampli_id`
                      FROM `ampli`
                      WHERE `ampli`.`libelle` = `operations`.`ampli_rayons_x`
                      AND `ampli`.`group_id` = `sejour`.`group_id`
                      );";
        $this->ds->exec($query);

        $query = "UPDATE `operations`
                  LEFT JOIN `sejour` ON `sejour`. `sejour_id` = `operations`.`sejour_id`
                  SET `operations`.`labo_anapath_id` = (
                      SELECT `laboratoire_anapath_id`
                      FROM `laboratoire_anapath`
                      WHERE `laboratoire_anapath`.`libelle` = `operations`.`labo_anapath`
                      AND `laboratoire_anapath`.`group_id` = `sejour`.`group_id`
                      );";
        $this->ds->exec($query);

        $query = "UPDATE `operations`
                  LEFT JOIN `sejour` ON `sejour`. `sejour_id` = `operations`.`sejour_id`
                  SET `operations`.`labo_bacterio_id` = (
                      SELECT `laboratoire_bacterio_id`
                      FROM `laboratoire_bacterio`
                      WHERE `laboratoire_bacterio`.`libelle` = `operations`.`labo_bacterio`
                      AND `laboratoire_bacterio`.`group_id` = `sejour`.`group_id`
                      );";

        $this->ds->exec($query);

        return true;
    }

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPplanningOp";

        $this->addDependency("dPpersonnel", "0.11");

        $this->makeRevision("0.0");
        $query = "CREATE TABLE operations (
      operation_id BIGINT(20) UNSIGNED NOT NULL auto_increment,
      pat_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
      chir_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
      plageop_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
      CIM10_code VARCHAR(5) DEFAULT NULL,
      CCAM_code VARCHAR(7) DEFAULT NULL,
      cote ENUM('droit','gauche','bilatéral','total') NOT NULL DEFAULT 'total',
      temp_operation TIME NOT NULL DEFAULT '00:00:00',
      time_operation TIME NOT NULL DEFAULT '00:00:00',
      examen TEXT,
      materiel TEXT,
      commande_mat ENUM('o', 'n') NOT NULL DEFAULT 'n',
      info ENUM('o','n') NOT NULL DEFAULT 'n',
      date_anesth date NOT NULL DEFAULT '0000-00-00',
      time_anesth TIME NOT NULL DEFAULT '00:00:00',
      type_anesth tinyint(4) DEFAULT NULL,
      date_adm date NOT NULL DEFAULT '0000-00-00',
      time_adm TIME NOT NULL DEFAULT '00:00:00',
      duree_hospi tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
      type_adm ENUM('comp','ambu','exte') DEFAULT 'comp',
      chambre ENUM('o','n') NOT NULL DEFAULT 'o',
      ATNC ENUM('o','n') NOT NULL DEFAULT 'n',
      rques TEXT,
      rank tinyint(4) NOT NULL DEFAULT '0',
      admis ENUM('n','o') NOT NULL DEFAULT 'n',
      PRIMARY KEY  (operation_id),
      UNIQUE KEY operation_id (operation_id)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.1");
        $query = "ALTER TABLE operations
      ADD entree_bloc TIME AFTER temp_operation ,
      ADD sortie_bloc TIME AFTER entree_bloc ,
      ADD saisie ENUM( 'n', 'o' ) DEFAULT 'n' NOT NULL ,
      CHANGE plageop_id plageop_id BIGINT( 20 ) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("0.2");
        $query = "ALTER TABLE `operations`
      ADD `convalescence` TEXT AFTER `materiel` ;";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE `operations`
      ADD `depassement` INT( 4 );";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `operations`
      ADD `CCAM_code2` VARCHAR( 7 ) AFTER `CCAM_code`,
      ADD INDEX ( `CCAM_code2` ),
      ADD INDEX ( `CCAM_code` ),
      ADD INDEX ( `pat_id` ),
      ADD INDEX ( `chir_id` ),
      ADD INDEX ( `plageop_id` );";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $query = "ALTER TABLE `operations`
      ADD `modifiee` TINYINT DEFAULT '0' NOT NULL AFTER `saisie`,
      ADD `annulee` TINYINT DEFAULT '0' NOT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.24");
        $query = "ALTER TABLE `operations`
      ADD `compte_rendu` TEXT,
      ADD `cr_valide` TINYINT( 4 ) DEFAULT '0' NOT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "ALTER TABLE `operations`
      ADD `pathologie` VARCHAR( 8 ) DEFAULT NULL,
      ADD `septique` TINYINT DEFAULT '0' NOT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $query = "ALTER TABLE `operations`
      ADD `libelle` TEXT DEFAULT NULL AFTER `CCAM_code2`;";
        $this->addQuery($query);

        // CR passage des champs à enregistrements supprimé car regressif
        //    $this->makeRevision("0.27");

        $this->makeRevision("0.28");
        $query = "ALTER TABLE `operations`
      ADD `codes_ccam` VARCHAR( 160 ) AFTER `CIM10_code`";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `codes_ccam` )";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "UPDATE `operations`
      SET `codes_ccam` = CONCAT_WS('|', `CCAM_code`, `CCAM_code2`)";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `operations`
      ADD `pose_garrot` TIME AFTER `entree_bloc` ,
      ADD `debut_op` TIME AFTER `pose_garrot` ,
      ADD `fin_op` TIME AFTER `debut_op` ,
      ADD `retrait_garrot` TIME AFTER `fin_op` ;";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `operations`
      ADD `salle_id` BIGINT AFTER `plageop_id` ,
      ADD `date` DATE AFTER `salle_id`;";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `operations`
      ADD `venue_SHS` VARCHAR( 8 ) AFTER `chambre`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `venue_SHS` );";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "ALTER TABLE `operations`
      ADD `code_uf` VARCHAR( 3 ) AFTER `venue_SHS`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD `libelle_uf` VARCHAR( 40 ) AFTER `code_uf`;";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `operations`
      ADD `entree_reveil` TIME AFTER `sortie_bloc` ,
      ADD `sortie_reveil` TIME AFTER `entree_reveil` ;";
        $this->addQuery($query);

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `operations`
      ADD `entree_adm` DATETIME AFTER `admis`;";
        $this->addQuery($query);
        $query = "UPDATE `operations` SET
      `entree_adm` = ADDTIME(date_adm, time_adm)
      WHERE `admis` = 'o'";
        $this->addQuery($query);

        $this->makeRevision("0.35.1");
        $this->addDependency("dPbloc", "0.15");

        $this->makeRevision("0.36");
        // Réparation des opérations avec `duree_hospi` = '255'
        $query = "UPDATE `operations`, `plagesop` SET
      `operations`.`date_adm` = `plagesop`.`date`,
      `operations`.`duree_hospi` = '1'
      WHERE `operations`.`duree_hospi` = '255'
      AND `operations`.`plageop_id` = `plagesop`.`plageop_id`";
        $this->addQuery($query);

        // Création de la table
        $query = "CREATE TABLE `sejour` (
      `sejour_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
      `patient_id` INT UNSIGNED NOT NULL ,
      `praticien_id` INT UNSIGNED NOT NULL ,
      `entree_prevue` DATETIME NOT NULL ,
      `sortie_prevue` DATETIME NOT NULL ,
      `entree_reelle` DATETIME,
      `sortie_reelle` DATETIME,
      `chambre_seule` ENUM('o','n') NOT NULL DEFAULT 'o',
      PRIMARY KEY ( `sejour_id` )
    ) /*! ENGINE=MyISAM */";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `patient_id` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `praticien_id` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `entree_prevue` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `sortie_prevue` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `entree_reelle` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `sortie_reelle` )";
        $this->addQuery($query);

        // Migration de l'ancienne table
        $query = "ALTER TABLE `sejour`
      ADD `tmp_operation_id` INT UNSIGNED NOT NULL AFTER `sejour_id`";
        $this->addQuery($query);
        $query = "INSERT INTO `sejour` (
        `sejour_id` ,
        `tmp_operation_id` ,
        `patient_id` ,
        `praticien_id` ,
        `entree_prevue` ,
        `sortie_prevue` ,
        `entree_reelle` ,
        `sortie_reelle` ,
        `chambre_seule`
      )
      SELECT
        '',
        `operation_id`,
        `pat_id`,
        `chir_id`,
        ADDTIME(`date_adm`, `time_adm`),
        ADDDATE(ADDTIME(`date_adm`, `time_adm`), `duree_hospi`),
        `entree_adm` ,
        NULL ,
        `chambre`
      FROM `operations`
      WHERE `operations`.`pat_id` != 0";
        $this->addQuery($query);

        // Ajout d'une référence vers les sejour
        $query = "ALTER TABLE `operations`
      ADD `sejour_id` INT UNSIGNED NOT NULL AFTER `operation_id`";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `sejour_id` )";
        $this->addQuery($query);
        $query = "UPDATE `operations`, `sejour`
      SET `operations`.`sejour_id` = `sejour`.`sejour_id`
      WHERE `sejour`.`tmp_operation_id` = `operations`.`operation_id`";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      DROP `tmp_operation_id` ";
        $this->addQuery($query);

        $this->makeRevision("0.37");
        // Migration de nouvelles propriétés
        $query = "ALTER TABLE `sejour`
      ADD `type` ENUM( 'comp', 'ambu', 'exte' ) DEFAULT 'comp' NOT NULL AFTER `praticien_id` ,
      ADD `annule` TINYINT DEFAULT '0' NOT NULL AFTER `type` ,
      ADD `venue_SHS` VARCHAR( 8 ) AFTER `annule` ,
      ADD `saisi_SHS` ENUM( 'o', 'n' ) DEFAULT 'n' NOT NULL AFTER `venue_SHS` ,
      ADD `modif_SHS` TINYINT DEFAULT '0' NOT NULL AFTER `saisi_SHS`";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `type` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `annule` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `venue_SHS` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `saisi_SHS` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `modif_SHS` )";
        $this->addQuery($query);
        $query = "UPDATE `sejour`, `operations` SET
      `sejour`.`type` = `operations`.`type_adm`,
      `sejour`.`annule` = `operations`.`annulee`,
      `sejour`.`venue_SHS` = `operations`.`venue_SHS`,
      `sejour`.`saisi_SHS` = `operations`.`saisie`,
      `sejour`.`modif_SHS` = `operations`.`modifiee`
      WHERE `operations`.`sejour_id` = `sejour`.`sejour_id`";
        $this->addQuery($query);

        $this->makeRevision("0.38");
        $query = "UPDATE `sejour`, `operations` SET
      `sejour`.`entree_reelle` = NULL
      WHERE `operations`.`sejour_id` = `sejour`.`sejour_id`
      AND `operations`.`admis` = 'n'";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      CHANGE `date_anesth` `date_anesth` DATE";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `date_anesth` = NULL
      WHERE `date_anesth` = '0000-00-00'";
        $this->addQuery($query);

        $this->makeRevision("0.39");
        $query = "ALTER TABLE sejour
      ADD rques TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.40");
        $query = "ALTER TABLE operations
      ADD pause TIME NOT NULL DEFAULT '00:00:00' AFTER temp_operation";
        $this->addQuery($query);

        $this->makeRevision("0.41");
        $query = "ALTER TABLE `sejour`
      ADD `pathologie` VARCHAR( 8 ) DEFAULT NULL,
      ADD `septique` TINYINT DEFAULT '0' NOT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `sejour`, `operations` SET
      `sejour`.`pathologie` = `operations`.`pathologie`,
      `sejour`.`septique` = `operations`.`septique`
      WHERE `operations`.`sejour_id` = `sejour`.`sejour_id`";
        $this->addQuery($query);

        $this->makeRevision("0.42");
        $query = "ALTER TABLE `sejour`
      ADD `code_uf` VARCHAR( 8 ) DEFAULT NULL AFTER venue_SHS,
      ADD `libelle_uf` TINYINT DEFAULT '0' NOT NULL AFTER code_uf;";
        $this->addQuery($query);
        $query = "UPDATE `sejour`, `operations` SET
      `sejour`.`code_uf` = `operations`.`code_uf`,
      `sejour`.`libelle_uf` = `operations`.`libelle_uf`
      WHERE `operations`.`sejour_id` = `sejour`.`sejour_id`";
        $this->addQuery($query);

        $this->makeRevision("0.43");
        $query = "ALTER TABLE `sejour`
      ADD `convalescence` TEXT DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `sejour`, `operations` SET
      `sejour`.`convalescence` = `operations`.`convalescence`
      WHERE `operations`.`sejour_id` = `sejour`.`sejour_id`";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        $query = "ALTER TABLE `sejour`
      DROP `code_uf`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      DROP `libelle_uf`;";
        $this->addQuery($query);
        $query = " ALTER TABLE `sejour`
      ADD `modalite_hospitalisation` ENUM( 'office', 'libre', 'tiers' ) NOT NULL DEFAULT 'libre' AFTER `type`;";
        $this->addQuery($query);

        $this->makeRevision("0.45");
        $query = "ALTER TABLE `operations`
      DROP `entree_adm`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      DROP `admis`;";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "ALTER TABLE `sejour`
      ADD `DP`  VARCHAR(5) DEFAULT NULL AFTER `rques`;";
        $this->addQuery($query);
        $query = "UPDATE `sejour`, `operations` SET
      `sejour`.`DP` = `operations`.`CIM10_code`
      WHERE `operations`.`sejour_id` = `sejour`.`sejour_id`";
        $this->addQuery($query);

        $this->makeRevision("0.47");
        $query = "CREATE TABLE protocole (
      protocole_id INT UNSIGNED NOT NULL auto_increment,
      chir_id INT UNSIGNED NOT NULL DEFAULT '0',
      type ENUM('comp','ambu','exte') DEFAULT 'comp',
      DP VARCHAR(5) DEFAULT NULL,
      convalescence TEXT DEFAULT NULL,
      rques_sejour TEXT DEFAULT NULL,
      pathologie VARCHAR(8) DEFAULT NULL,
      septique TINYINT DEFAULT '0' NOT NULL,
      codes_ccam VARCHAR(160) DEFAULT NULL,
      libelle TEXT DEFAULT NULL,
      temp_operation TIME NOT NULL DEFAULT '00:00:00',
      examen TEXT DEFAULT NULL,
      materiel TEXT DEFAULT NULL,
      duree_hospi TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
      rques_operation TEXT DEFAULT NULL,
      depassement TINYINT DEFAULT NULL,
      PRIMARY KEY  (protocole_id)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `protocole`
      ADD INDEX (`chir_id`)";
        $this->addQuery($query);
        $query = "INSERT INTO `protocole` (
        `protocole_id`, `chir_id`,
        `type`,
        `DP`,
        `convalescence`,
        `rques_sejour`,
        `pathologie`,
        `septique`,
        `codes_ccam`,
        `libelle`,
        `temp_operation`,
        `examen`,
        `materiel`,
        `duree_hospi`,
        `rques_operation`,
        `depassement`
      )
      SELECT
        '',
        `operations`.`chir_id`,
        `operations`.`type_adm`,
        `operations`.`CIM10_code`,
        `operations`.`convalescence`,
        '',
        '',
        '',
        `operations`.`codes_ccam`,
        `operations`.`libelle`,
        `operations`.`temp_operation`,
        `operations`.`examen`,
        `operations`.`materiel`,
        `operations`.`duree_hospi`,
        `operations`.`rques`,
        `operations`.`depassement`
       FROM `operations`
       WHERE `operations`.`pat_id` = 0";
        $this->addQuery($query);
        $query = "DELETE FROM `operations`
      WHERE `pat_id` = 0";
        $this->addQuery($query);

        $this->makeRevision("0.48");
        $query = "ALTER TABLE `sejour`
      CHANGE `modalite_hospitalisation` `modalite` ENUM( 'office', 'libre', 'tiers' ) DEFAULT 'libre' NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.49");
        $query = "UPDATE `operations`
      SET `date` = NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.50");
        $query = "ALTER TABLE `operations`
      ADD `anesth_id` INT UNSIGNED DEFAULT NULL AFTER `chir_id`";
        $this->addQuery($query);

        $this->makeRevision("0.51");
        $this->addDependency("dPetablissement", "0.1");
        $query = "ALTER TABLE `sejour`
      ADD `group_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `praticien_id`";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX ( `group_id` ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.52");
        $query = "ALTER TABLE `operations`
      DROP INDEX `operation_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `anesth_id` ) ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      DROP `pat_id`,
      DROP `CCAM_code`,
      DROP `CCAM_code2`,
      DROP `compte_rendu`,
      DROP `cr_valide`,
      DROP `date_adm`,
      DROP `time_adm`,
      DROP `chambre`,
      DROP `type_adm`,
      DROP `venue_SHS`,
      DROP `saisie`,
      DROP `modifiee`,
      DROP `CIM10_code`,
      DROP `convalescence`,
      DROP `pathologie`,
      DROP `septique` ;";
        $this->addQuery($query);

        $this->makeRevision("0.53");
        $query = "CREATE TABLE `type_anesth` (
      `type_anesth_id` INT UNSIGNED NOT NULL auto_increment,
      `name` VARCHAR(50) DEFAULT NULL,
      PRIMARY KEY  (type_anesth_id)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('1', 'Non définie');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('2', 'Rachi');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('3', 'Rachi + bloc');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('4', 'Anesthésie loco-régionale');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('5', 'Anesthésie locale');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('6', 'Neurolept');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('7', 'Anesthésie générale');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('8', 'Anesthesie generale + bloc');";
        $this->addQuery($query);
        $query = "INSERT INTO `type_anesth`
      VALUES ('9', 'Anesthesie peribulbaire');";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `type_anesth`=`type_anesth`+1;";
        $this->addQuery($query);

        $this->makeRevision("0.54");
        $query = "ALTER TABLE `operations`
      ADD `induction` TIME AFTER `sortie_reveil`";
        $this->addQuery($query);

        $this->makeEmptyRevision("0.55");


        $this->makeEmptyRevision("0.56");

        $query = "ALTER TABLE `operations`
      CHANGE `operation_id` `operation_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      CHANGE `sejour_id` `sejour_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `chir_id` `chir_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `anesth_id` `anesth_id` int(11) UNSIGNED NULL,
      CHANGE `plageop_id` `plageop_id` int(11) UNSIGNED NULL,
      CHANGE `code_uf` `code_uf` VARCHAR(3) NULL,
      CHANGE `libelle_uf` `libelle_uf` VARCHAR(35) NULL,
      CHANGE `salle_id` `salle_id` int(11) UNSIGNED NULL,
      CHANGE `codes_ccam` `codes_ccam` VARCHAR(255) NULL,
      CHANGE `libelle` `libelle` VARCHAR(255) NULL,
      CHANGE `type_anesth` `type_anesth` int(11) UNSIGNED NULL,
      CHANGE `rank` `rank` tinyint NOT NULL DEFAULT '0',
      CHANGE `annulee` `annulee` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `depassement` `depassement` float NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations` DROP `duree_hospi`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `protocole`
      CHANGE `protocole_id` `protocole_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      CHANGE `chir_id` `chir_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `pathologie` `pathologie` VARCHAR(3) NULL,
      CHANGE `codes_ccam` `codes_ccam` VARCHAR(255) NULL,
      CHANGE `libelle` `libelle` VARCHAR(255) NULL,
      CHANGE `duree_hospi` `duree_hospi` mediumint NOT NULL DEFAULT '0',
      CHANGE `septique` `septique` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `depassement` `depassement` float NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      CHANGE `sejour_id` `sejour_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      CHANGE `patient_id` `patient_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `praticien_id` `praticien_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `group_id` `group_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
      CHANGE `venue_SHS` `venue_SHS` int(8) UNSIGNED zerofill NULL,
      CHANGE `annule` `annule` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `modif_SHS` `modif_SHS` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `septique` `septique` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `pathologie` `pathologie` VARCHAR(3) NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `type_anesth`
      CHANGE `type_anesth_id` `type_anesth_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      CHANGE `name` `name` VARCHAR(255) NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      CHANGE `saisi_SHS` `saisi_SHS` ENUM('o','n','0','1') NOT NULL DEFAULT 'n',
      CHANGE `chambre_seule` `chambre_seule` ENUM('o','n','0','1') NOT NULL DEFAULT 'o';";
        $this->addQuery($query);
        $query = "UPDATE `sejour`
      SET `saisi_SHS`='0'
      WHERE `saisi_SHS`='n';";
        $this->addQuery($query);
        $query = "UPDATE `sejour`
      SET `saisi_SHS`='1'
      WHERE `saisi_SHS`='o';";
        $this->addQuery($query);
        $query = "UPDATE `sejour`
      SET `chambre_seule`='0'
      WHERE `chambre_seule`='n';";
        $this->addQuery($query);
        $query = "UPDATE `sejour`
      SET `chambre_seule`='1'
      WHERE `chambre_seule`='o';";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      CHANGE `saisi_SHS` `saisi_SHS` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `chambre_seule` `chambre_seule` ENUM('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      CHANGE `ATNC` `ATNC` ENUM('o','n','0','1') NOT NULL DEFAULT 'n',
      CHANGE `commande_mat` `commande_mat` ENUM('o','n','0','1') NOT NULL DEFAULT 'n',
      CHANGE `info` `info` ENUM('o','n','0','1') NOT NULL DEFAULT 'n';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `ATNC`='0'
      WHERE `ATNC`='n';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `ATNC`='1'
      WHERE `ATNC`='o';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `info`='0'
      WHERE `info`='n';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `info`='1'
      WHERE `info`='o';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `commande_mat`='0'
      WHERE `commande_mat`='n';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `commande_mat`='1'
      WHERE `commande_mat`='o';";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
      CHANGE `ATNC` `ATNC` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `commande_mat` `commande_mat` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `info` `info` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.57");
        $query = "ALTER TABLE `operations`
      DROP `date_anesth`,
      DROP `time_anesth`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      CHANGE `entree_bloc` `entree_salle` TIME NULL,
      CHANGE `sortie_bloc` `sortie_salle` TIME NULL,
      ADD `entree_bloc` TIME NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.58");
        $query = "ALTER TABLE `sejour`
      ADD `ATNC` ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `hormone_croissance` ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `lit_accompagnant`   ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `isolement`          ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `television`         ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `repas_diabete`      ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `repas_sans_sel`     ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `repas_sans_residu`  ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `type` `type` ENUM('comp','ambu','exte','seances','ssr','psy') NOT NULL DEFAULT 'comp';";
        $this->addQuery($query);
        $query = "UPDATE sejour SET ATNC = '1' WHERE sejour_id IN (SELECT sejour_id FROM `operations` WHERE ATNC = '1');";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations` DROP `ATNC`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `protocole` CHANGE `type` `type` ENUM('comp','ambu','exte','seances','ssr','psy') NOT NULL DEFAULT 'comp';";
        $this->addQuery($query);

        $this->makeRevision("0.59");
        $query = "UPDATE `operations` SET annulee = 0 WHERE annulee = ''";
        $this->addQuery($query);
        $query = "UPDATE `sejour` SET annule = 0 WHERE annule = ''";
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `salle_id` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `date` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `time_operation` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX ( `annulee` )";
        $this->addQuery($query);

        $this->makeRevision("0.61");
        $query = "ALTER TABLE `operations`
      CHANGE `induction` `induction_debut` TIME";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD `induction_fin` TIME AFTER `induction_debut`";
        $this->addQuery($query);

        $this->makeRevision("0.62");
        $query = "ALTER TABLE `operations`
      ADD `anapath` ENUM('0','1') NOT NULL DEFAULT '0',
      ADD `labo` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.63");
        $query = "UPDATE `operations`
      SET `anesth_id` = NULL WHERE `anesth_id` = '0';";
        $this->addQuery($query);

        $this->makeRevision("0.64");
        $query = "ALTER TABLE `operations`
      ADD `forfait` FLOAT NULL AFTER `depassement`,
      ADD `fournitures` FLOAT NULL AFTER `forfait`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `protocole`
      ADD `forfait` FLOAT NULL AFTER `depassement`,
      ADD `fournitures` FLOAT NULL AFTER `forfait`;";
        $this->addQuery($query);

        $this->makeRevision("0.65");
        $query = "ALTER TABLE `sejour`
      ADD `codes_ccam` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("0.66");
        $this->addPrefQuery("mode", "1");

        $this->makeRevision("0.67");
        $query = "UPDATE `user_preferences`
      SET `key` = 'mode_dhe' WHERE `key` = 'mode';";
        $this->addQuery($query, true);

        $this->makeRevision("0.68");
        $query = "ALTER TABLE `sejour`
      ADD `mode_sortie` ENUM( 'normal', 'transfert', 'deces' );";
        $this->addQuery($query);

        $this->makeRevision("0.69");
        $query = "ALTER TABLE `sejour`
      ADD `prestation_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.70");
        $query = "ALTER TABLE `sejour`
      ADD `facturable` ENUM('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.71");
        $query = "ALTER TABLE `sejour`
      ADD `reanimation` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `chambre_seule`;";
        $this->addQuery($query);

        $this->makeRevision("0.72");
        $query = "ALTER TABLE `sejour`
      ADD `zt` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `reanimation`;";
        $this->addQuery($query);

        $this->makeRevision("0.73");
        $query = "ALTER TABLE `sejour`
      CHANGE `reanimation` `reanimation` ENUM('0','1') NOT NULL DEFAULT '0',
      CHANGE `zt` `zt` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "UPDATE `sejour` SET `sejour`.`reanimation` = 0, `sejour`.`zt` = 0;";
        $this->addQuery($query);

        $this->makeRevision("0.74");
        $query = "ALTER TABLE `operations`
      CHANGE `cote` `cote` ENUM('droit','gauche','bilatéral','total','inconnu') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.75");
        $query = "ALTER TABLE `sejour`
      ADD `etablissement_transfert_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.76");
        $query = "ALTER TABLE `operations`
      ADD `horaire_voulu` TIME;";
        $this->addQuery($query);

        $this->makeRevision("0.77");
        $query = "ALTER TABLE `sejour`
      CHANGE `type` `type` ENUM('comp','ambu','exte','seances','ssr','psy','urg') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.78");
        $query = "ALTER TABLE `type_anesth`
      ADD `ext_doc` ENUM('1','2','3','4','5','6');";
        $this->addQuery($query);

        $this->makeRevision("0.79");
        $query = "ALTER TABLE `sejour`
      ADD `DR` VARCHAR(5),
      CHANGE `pathologie` `pathologie` CHAR(3)";
        $this->addQuery($query);

        $this->makeRevision("0.80");
        $query = "UPDATE operations, plagesop
      SET operations.salle_id = plagesop.salle_id
      WHERE operations.salle_id IS NULL
      AND operations.plageop_id = plagesop.plageop_id;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      CHANGE `salle_id` `salle_id` INT( 11 ) UNSIGNED NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.81");
        $query = "ALTER TABLE `operations`
      CHANGE `salle_id` `salle_id` INT( 11 ) UNSIGNED DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET salle_id = NULL WHERE salle_id = 0;";
        $this->addQuery($query);

        $this->makeRevision("0.82");
        $this->addDependency("dPsante400", "0.1");
        $query = "INSERT INTO `id_sante400` (id_sante400_id, object_class, object_id, tag, last_update, id400)
      SELECT NULL , 'CSejour', `sejour_id` , 'SHS group:1', NOW( ) , `venue_SHS`
      FROM `sejour`
      WHERE `venue_SHS` IS NOT NULL
      AND `venue_SHS` != 0";
        $this->addQuery($query);

        $this->makeRevision("0.83");
        $query = "ALTER TABLE `sejour`
      DROP `venue_SHS`";
        $this->addQuery($query);

        $this->makeRevision("0.84");
        $query = "ALTER TABLE `sejour`
      ADD `repas_sans_porc` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.85");
        $query = "ALTER TABLE `protocole`
      ADD `protocole_prescription_chir_id` INT (11) UNSIGNED,
      ADD `protocole_prescription_anesth_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD INDEX (`protocole_prescription_chir_id`),
      ADD INDEX (`protocole_prescription_anesth_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.86");
        $query = "ALTER TABLE `operations` ADD `depassement_anesth` FLOAT NULL AFTER `fournitures`;";
        $this->addQuery($query);

        $this->makeRevision("0.87");
        $this->addDependency("dPcompteRendu", "0.1");
        $query = CSetupCompteRendu::renameTemplateFieldQuery("Opération - CCAM - code", "Opération - CCAM1 - code");
        $this->addQuery($query);

        $query = CSetupCompteRendu::renameTemplateFieldQuery(
            "Opération - CCAM - description",
            "Opération - CCAM1 - description"
        );
        $this->addQuery($query);

        $query = CSetupCompteRendu::renameTemplateFieldQuery("Opération - CCAM complet", "Opération - CCAM - codes");
        $this->addQuery($query);

        $this->makeRevision("0.88");
        $query = "ALTER TABLE `operations`
        CHANGE `anapath` `anapath` ENUM ('1','0','?') DEFAULT '?',
        CHANGE `labo` `labo` ENUM ('1','0','?') DEFAULT '?';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `anapath` = '?'
      WHERE `anapath` = '0'";
        $this->addQuery($query);
        $query = "UPDATE `operations`
      SET `labo` = '?'
      WHERE `labo` = '0'";
        $this->addQuery($query);

        $this->makeRevision("0.89");
        $query = "ALTER TABLE `protocole`
      ADD `for_sejour` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.90");
        $query = "ALTER TABLE `sejour`
    ADD `adresse_par_prat_id` INT (11),
    ADD `adresse_par_etab_id` INT (11),
    ADD `libelle` VARCHAR (255)";
        $this->addQuery($query);

        $this->makeRevision("0.91");
        $query = "ALTER TABLE `protocole`
      ADD `libelle_sejour` VARCHAR (255)";
        $this->addQuery($query);

        $this->makeRevision("0.92");
        $query = "ALTER TABLE `operations`
      ADD `cote_admission` ENUM ('droit','gauche') AFTER `horaire_voulu`,
      ADD `cote_consult_anesth` ENUM ('droit','gauche') AFTER `cote_admission`,
      ADD `cote_hospi` ENUM ('droit','gauche') AFTER `cote_consult_anesth`,
      ADD `cote_bloc` ENUM ('droit','gauche') AFTER `cote_hospi`;";
        $this->addQuery($query);

        $this->makeRevision("0.93");
        $query = "ALTER TABLE `operations`
      ADD `prothese` ENUM ('1','0','?')  DEFAULT '?' AFTER `labo`,
      ADD `date_visite_anesth` DATETIME,
      ADD `prat_visite_anesth_id` INT (11) UNSIGNED,
      ADD `rques_visite_anesth` TEXT,
      ADD `autorisation_anesth` ENUM ('0','1');";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX (`date_visite_anesth`),
      ADD INDEX (`prat_visite_anesth_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.94");
        $this->addPrefQuery("dPplanningOp_listeCompacte", "1");

        $this->makeRevision("0.95");
        $query = "ALTER TABLE `sejour`
      ADD `service_id` INT (11) UNSIGNED AFTER `zt`,
      ADD INDEX (`etablissement_transfert_id`),
      ADD INDEX (`service_id`),
      ADD INDEX (`prestation_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.96");
        $query = "ALTER TABLE `protocole`
      ADD `service_id_sejour` INT (11) UNSIGNED,
      ADD INDEX (`temp_operation`),
      ADD INDEX (`service_id_sejour`);";
        $this->addQuery($query);

        $this->makeRevision("0.97");
        $query = "ALTER TABLE `sejour`
      ADD `etablissement_entree_transfert_id` INT (11) UNSIGNED,
      ADD INDEX (`etablissement_entree_transfert_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.98");
        $query = "ALTER TABLE `sejour`
      ADD `facture` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.99");
        $query = "ALTER TABLE `operations`
      ADD `facture` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.00");
        $query = "ALTER TABLE `sejour`
      CHANGE `type` `type` ENUM ('comp','ambu','exte','seances','ssr','psy','urg','consult') NOT NULL;";
        $this->addQuery($query);

        $this->makeEmptyRevision("1.01");

        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
      ADD INDEX (`type_anesth`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
      ADD INDEX (`adresse_par_prat_id`),
      ADD INDEX (`adresse_par_etab_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.02");
        $query = "ALTER TABLE `sejour`
      CHANGE `chambre_seule` `chambre_seule` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.03");
        $query = "UPDATE sejour
      SET sortie_prevue = entree_reelle
      WHERE entree_reelle IS NOT NULL
      AND sortie_prevue < entree_reelle";
        $this->addQuery($query);

        $this->makeRevision("1.04");
        $query = "ALTER TABLE `sejour`
      CHANGE `mode_sortie` `mode_sortie` ENUM ('normal','transfert','mutation','deces') DEFAULT 'normal',
      ADD `service_mutation_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD INDEX (`service_mutation_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.05");
        $query = "ALTER TABLE `sejour`
      ADD `entree` DATETIME AFTER `sortie_reelle`,
      ADD `sortie` DATETIME AFTER `entree`";
        $this->addQuery($query);
        $query = "UPDATE `sejour` SET
      `sejour`.`entree` = IF(`sejour`.`entree_reelle`,`sejour`.`entree_reelle`,`sejour`.`entree_prevue`),
      `sejour`.`sortie` = IF(`sejour`.`sortie_reelle`,`sejour`.`sortie_reelle`,`sejour`.`sortie_prevue`)";
        $this->addQuery($query);

        $this->makeRevision("1.06");
        $query = "ALTER TABLE `sejour`
      ADD INDEX (`entree`),
      ADD INDEX (`sortie`);";
        $this->addQuery($query);

        $this->makeRevision("1.07");
        $query = "ALTER TABLE `sejour`
      ADD `service_entree_mutation_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.08");
        $query = "ALTER TABLE `sejour`
      ADD `forfait_se` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.09");
        $query = "ALTER TABLE `sejour`
      ADD `recuse` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.10");
        $query = "ALTER TABLE `protocole`
      CHANGE `service_id_sejour` `service_id` INT (11) UNSIGNED  NOT NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD INDEX (`service_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.11");
        $query = "ALTER TABLE `protocole`
      CHANGE `service_id` `service_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "UPDATE `protocole`
      SET service_id = NULL
      WHERE service_id = '0'";
        $this->addQuery($query);

        $this->makeRevision("1.12");
        $query = "CREATE TABLE `color_libelle_sejour` (
      `color_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `libelle` VARCHAR (255) NOT NULL,
      `color` CHAR (6) NOT NULL DEFAULT 'ffffff'
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.13");
        $query = "ALTER TABLE `operations`
      ADD `debut_prepa_preop` TIME,
      ADD `fin_prepa_preop` TIME";
        $this->addQuery($query);

        $this->makeRevision("1.14");
        $query = "CREATE TABLE `interv_hors_plages` (
      `interv_hors_plage_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.15");
        $query = "ALTER TABLE sejour
      ADD commentaires_sortie TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.16");
        $query = "ALTER TABLE `protocole`
              CHANGE `protocole_prescription_chir_id` `protocole_prescription_chir_id` VARCHAR (255),
              CHANGE `protocole_prescription_anesth_id` `protocole_prescription_anesth_id` VARCHAR (255);";
        $this->addQuery($query);

        $query = "UPDATE `protocole`
              SET protocole_prescription_chir_id = CONCAT('CPrescription-', protocole_prescription_chir_id)
              WHERE protocole_prescription_chir_id IS NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `protocole`
              SET protocole_prescription_anesth_id = CONCAT('CPrescription-', protocole_prescription_anesth_id)
              WHERE protocole_prescription_anesth_id IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.17");
        $query = "ALTER table `protocole`
              ADD `protocole_prescription_chir_class` ENUM ('CPrescription', 'CPrescriptionProtocolePack') AFTER `protocole_prescription_chir_id`,
              ADD `protocole_prescription_anesth_class` ENUM ('CPrescription', 'CPrescriptionProtocolePack') AFTER `protocole_prescription_anesth_id`;";
        $this->addQuery($query);

        $query = "UPDATE `protocole`
              SET protocole_prescription_chir_class = SUBSTRING_INDEX(protocole_prescription_chir_id, '-', 1),
              protocole_prescription_chir_id = SUBSTRING(protocole_prescription_chir_id, LENGTH(SUBSTRING_INDEX(protocole_prescription_chir_id,'-',1))+2),
              protocole_prescription_anesth_class = SUBSTRING_INDEX(protocole_prescription_anesth_id, '-', 1),
              protocole_prescription_anesth_id = SUBSTRING(protocole_prescription_anesth_id, LENGTH(SUBSTRING_INDEX(protocole_prescription_anesth_id,'-',1))+2);";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
              CHANGE protocole_prescription_chir_id protocole_prescription_chir_id INT(11),
              CHANGE protocole_prescription_anesth_id protocole_prescription_anesth_id INT(11)";

        $this->makeRevision("1.18");
        $query = "ALTER TABLE `sejour`
              ADD `consult_accomp` ENUM ('oui','non','nc') DEFAULT 'nc';";
        $this->addQuery($query);

        $this->makeRevision("1.19");
        $query = "ALTER TABLE `groups_config`
      ADD `dPplanningOp_COperation_DHE_mode_simple` ENUM ('0', '1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.20");

        $query = "ALTER TABLE `protocole`
              CHANGE `chir_id` `chir_id` INT (11) UNSIGNED,
              ADD `function_id` INT (11) UNSIGNED AFTER `chir_id`,
              ADD `group_id` INT (11) UNSIGNED AFTER `function_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
              ADD INDEX (`function_id`),
              ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.21");

        $query = "ALTER TABLE `sejour`
                ADD `discipline_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                ADD INDEX (`discipline_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.22");
        $query = "ALTER TABLE `sejour`
      ADD `mode_entree` ENUM('6','7','8') AFTER `codes_ccam`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD INDEX (`mode_entree`);";
        $this->addQuery($query);

        $this->makeRevision("1.23");

        $query = "ALTER TABLE `protocole`
              ADD `cote` ENUM ('droit','gauche','bilatéral','total','inconnu') AFTER `libelle`";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
              ADD `assurance_maladie` VARCHAR (255),
              ADD `rques_assurance_maladie` TEXT,
              ADD `assurance_accident` VARCHAR (255),
              ADD `rques_assurance_accident` TEXT,
              ADD `date_accident` DATE,
              ADD `nature_accident` ENUM ('P','T','D','S','J','C','L','B','U');";
        $this->addQuery($query);

        $this->makeRevision("1.24");
        $query = "ALTER TABLE `sejour`
      CHANGE `etablissement_entree_transfert_id` `etablissement_entree_id` INT (11) UNSIGNED,
      CHANGE `etablissement_transfert_id` `etablissement_sortie_id` INT(11) UNSIGNED,
      CHANGE `service_entree_mutation_id` `service_entree_id` INT (11) UNSIGNED,
      CHANGE `service_mutation_id` `service_sortie_id` INT (11) UNSIGNED";
        $this->addQuery($query);

        $this->getFieldRenameQueries("CSejour", "etablissement_entree_transfert_id", "etablissement_entree_id");
        $this->getFieldRenameQueries("CSejour", "etablissement_transfert_id", "etablissement_sortie_id");
        $this->getFieldRenameQueries("CSejour", "service_entree_mutation_id", "service_entree_id");
        $this->getFieldRenameQueries("CSejour", "service_mutation_id", "service_sortie_id");

        $this->makeRevision("1.25");
        $query = "ALTER TABLE `sejour`
              ADD `ald` ENUM ('0','1') DEFAULT '0' AFTER discipline_id;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
              CHANGE `recuse` `recuse` ENUM ('-1','0','1') DEFAULT '-1';";
        $this->addQuery($query);

        $this->makeRevision("1.26");
        $query = "UPDATE `sejour`
      SET `etablissement_entree_id` = `sejour`.`adresse_par_etab_id`
      WHERE `etablissement_entree_id` IS NULL;";

        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      DROP `adresse_par_etab_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.27");

        $this->addDependency("dPcompteRendu", "0.1");
        $query = CSetupCompteRendu::renameTemplateFieldQuery("RPU - Provenance", "Sejour - Provenance");
        $this->addQuery($query);

        $query = CSetupCompteRendu::renameTemplateFieldQuery("RPU - Destination", "Sejour - Destination");
        $this->addQuery($query);

        $query = CSetupCompteRendu::renameTemplateFieldQuery("RPU - Tranport", "Sejour - Transport");
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD `provenance` ENUM('1','2','3','4','5','6', '7', '8'),
      ADD `destination` ENUM('1','2','3','4','6','7'),
      ADD `transport` ENUM('perso','perso_taxi','ambu','ambu_vsl','vsab','smur','heli','fo') NOT NULL;";
        $this->addQuery($query);

        // Déplacer les champs que si le module dPurgences est actif
        if (CModule::getActive("dPurgences")) {
            $query = "UPDATE sejour, rpu
      SET `sejour`.`provenance` = `rpu`.`provenance`
      WHERE `rpu`.`sejour_id` = `sejour`.`sejour_id`";
            $this->addQuery($query);

            $query = "UPDATE sejour, rpu
      SET `sejour`.`destination` = `rpu`.`destination`
      WHERE `rpu`.`sejour_id` = `sejour`.`sejour_id`";
            $this->addQuery($query);

            $query = "UPDATE sejour, rpu
        SET `sejour`.`transport` = `rpu`.`transport`
        WHERE `rpu`.`sejour_id` = `sejour`.`sejour_id`";
            $this->addQuery($query);
        }

        $this->makeRevision("1.28");
        $query = "ALTER TABLE `sejour`
      CHANGE `transport` `transport` ENUM('perso','perso_taxi','ambu','ambu_vsl','vsab','smur','heli','fo');";
        $this->addQuery($query);

        $this->makeRevision("1.29");
        $query = "ALTER TABLE `sejour`
      ADD `type_pec` ENUM ('M','C','O');";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD `type_pec` ENUM ('M','C','O');";
        $this->addQuery($query);

        $this->makeRevision("1.30");
        $query = "ALTER TABLE `sejour`
    ADD `grossesse_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.31");
        $query = "ALTER TABLE `sejour`
      ADD `duree_uscpo` INT (11) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD `duree_uscpo` INT (11) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.32");
        $query = "ALTER TABLE `sejour`
                ADD `confirme` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                ADD INDEX ( `confirme` )";
        $this->addQuery($query);

        $this->makeRevision("1.33");

        $query = "ALTER TABLE `operations`
                ADD `duree_uscpo` INT (11) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `operations`
                LEFT JOIN `sejour` ON `sejour`.`sejour_id` = `operations`.`sejour_id`
                SET `operations`.`duree_uscpo` = `sejour`.`duree_uscpo`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                DROP `duree_uscpo`";
        $this->addQuery($query);

        $this->makeRevision("1.34");

        $query = "ALTER TABLE `sejour`
                CHANGE `zt` `UHCD` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.35");
        $query = "ALTER TABLE `operations`
      ADD `cloture_activite_1` ENUM ('0','1') DEFAULT '0',
      ADD `cloture_activite_4` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD `cloture_activite_1` ENUM ('0','1') DEFAULT '0',
      ADD `cloture_activite_4` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.36");
        $query = "ALTER TABLE `sejour`
       CHANGE `saisi_SHS` `entree_preparee` ENUM ('0','1') DEFAULT '0',
       ADD `sortie_preparee` ENUM ('0','1') DEFAULT '0',
       CHANGE `modif_SHS` `entree_modifiee` ENUM ('0','1') DEFAULT '0',
       ADD `sortie_modifiee` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.37");
        $query = "ALTER TABLE `protocole`
      ADD `presence_preop` TIME,
      ADD `presence_postop` TIME;";
        $this->addQuery($query);

        $this->makeRevision("1.38");
        $query = "ALTER TABLE `operations`
      ADD `presence_preop` TIME,
      ADD `presence_postop` TIME;";
        $this->addQuery($query);

        $this->makeRevision("1.39");
        $query = "ALTER TABLE `operations`
              ADD `rank_voulu` TINYINT (4) NOT NULL DEFAULT '0' AFTER `rank`;";
        $this->addQuery($query);

        $this->makeRevision("1.40");
        $query = "ALTER TABLE `sejour`
                ADD `forfait_fsd` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.41");
        $query = "ALTER TABLE `sejour`
                CHANGE `forfait_fsd` `forfait_sd` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.42");
        $query = "ALTER TABLE `operations`
      ADD `duree_preop` TIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD `duree_preop` TIME;";
        $this->addQuery($query);

        $this->makeRevision("1.43");
        $query = "ALTER TABLE `operations`
      ADD `passage_uscpo` ENUM ('0','1') AFTER duree_uscpo;";
        $this->addQuery($query);

        $this->makeRevision("1.44");
        $query = "ALTER TABLE `operations` CHANGE `date_visite_anesth` `date_visite_anesth` DATE;";
        $this->addQuery($query);

        $this->makeRevision("1.45");
        $query = "ALTER TABLE `sejour`
                ADD `transport_sortie` ENUM ('perso','perso_taxi','ambu','ambu_vsl','vsab','smur','heli','fo'),
                ADD `rques_transport_sortie` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.46");
        $query = "ALTER TABLE `protocole`
      ADD `uf_hebergement_id` INT (11) UNSIGNED AFTER `group_id`,
      ADD `uf_medicale_id` INT (11) UNSIGNED AFTER `uf_hebergement_id`,
      ADD `uf_soins_id` INT (11) UNSIGNED AFTER `uf_medicale_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD `uf_hebergement_id` INT (11) UNSIGNED AFTER `group_id`,
      ADD `uf_medicale_id` INT (11) UNSIGNED AFTER `uf_hebergement_id`,
      ADD `uf_soins_id` INT (11) UNSIGNED AFTER `uf_medicale_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD INDEX (`uf_hebergement_id`),
      ADD INDEX (`uf_medicale_id`),
      ADD INDEX (`uf_soins_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD INDEX (`uf_hebergement_id`),
      ADD INDEX (`uf_medicale_id`),
      ADD INDEX (`uf_soins_id`);";

        $this->makeRevision("1.47");

        $query = "ALTER TABLE `type_anesth`
      ADD `actif` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.48");

        $query = "ALTER TABLE `operations`
              ADD `exam_extempo` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.49");
        $query = "CREATE TABLE `pose_dispositif_vasculaire` (
              `pose_dispositif_vasculaire_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `operation_id` INT (11) UNSIGNED,
              `sejour_id` INT (11) UNSIGNED NOT NULL,
              `date` DATETIME NOT NULL,
              `lieu` VARCHAR (255),
              `urgence` ENUM ('0','1') NOT NULL DEFAULT '0',
              `operateur_id` INT (11) UNSIGNED NOT NULL,
              `encadrant_id` INT (11) UNSIGNED,
              `type_materiel` ENUM ('cvc','cvc_tunnelise','cvc_dialyse','cvc_bioactif','chambre_implantable','autre') NOT NULL,
              `voie_abord_vasc` TEXT
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `pose_dispositif_vasculaire`
              ADD INDEX (`operation_id`),
              ADD INDEX (`sejour_id`),
              ADD INDEX (`date`),
              ADD INDEX (`operateur_id`),
              ADD INDEX (`encadrant_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.50");
        $query = "ALTER TABLE `operations`
              ADD `flacons_anapath` TINYINT (4) AFTER `anapath`,
              ADD `labo_anapath` VARCHAR (255)  AFTER `flacons_anapath`,
              ADD `description_anapath` TEXT    AFTER `labo_anapath`;";
        $this->addQuery($query);

        $this->makeRevision("1.51");
        $query = "ALTER TABLE `operations`
      ADD `chir_2_id` INT (11) UNSIGNED AFTER `chir_id`,
      ADD `chir_3_id` INT (11) UNSIGNED AFTER `chir_2_id`,
      ADD `chir_4_id` INT (11) UNSIGNED AFTER `chir_3_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.52");
        $query = "ALTER TABLE `operations`
      ADD `envoi_mail` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.53");
        $query = "UPDATE `sejour`
      SET `sejour`.`recuse` = '0'
      WHERE `sejour`.`type` != 'ssr';";
        $this->addQuery($query);

        $this->makeRevision("1.54");
        $query = "ALTER TABLE `operations`
              ADD `conventionne` ENUM ('0','1') DEFAULT '1' AFTER `depassement`;";
        $this->addQuery($query);

        $this->makeRevision("1.55");
        $query = "ALTER TABLE `operations`
              ADD `flacons_bacterio` TINYINT (4) AFTER `labo`,
              ADD `labo_bacterio` VARCHAR (255)  AFTER `flacons_bacterio`,
              ADD `description_bacterio` TEXT    AFTER `labo_bacterio`;";
        $this->addQuery($query);

        $this->makeRevision("1.56");
        $query = "CREATE TABLE `charge_price_indicator` (
                `charge_price_indicator_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code` VARCHAR (255) NOT NULL,
                `type` ENUM ('comp','ambu','exte','seances','ssr','psy','urg','consult') NOT NULL DEFAULT 'ambu',
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255),
                `actif` ENUM ('0','1') DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `charge_price_indicator`
                ADD INDEX (`group_id`),
                ADD INDEX (`code`);";
        $this->addQuery($query);

        $this->makeRevision("1.57");
        $query = "ALTER TABLE `sejour`
                ADD `charge_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                ADD INDEX (`grossesse_id`),
                ADD INDEX (`service_entree_id`),
                ADD INDEX (`charge_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.58");
        $query = "ALTER TABLE `charge_price_indicator`
              ADD `type_pec` ENUM ('M','C','O'),
              ADD INDEX (`type_pec`)";
        $this->addQuery($query);

        $this->makeRevision("1.59");
        $query = "ALTER TABLE `protocole`
      ADD `exam_extempo` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.60");
        $query = "ALTER TABLE `protocole`
      CHANGE `cote` `cote` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu');";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
      CHANGE `cote` `cote` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu') NOT NULL DEFAULT 'inconnu',
      CHANGE `cote_admission` `cote_admission` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu'),
      CHANGE `cote_consult_anesth` `cote_consult_anesth` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu'),
      CHANGE `cote_hospi` `cote_hospi` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu'),
      CHANGE `cote_bloc` `cote_bloc` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu');";
        $this->addQuery($query);

        $this->makeRevision("1.61");
        $query = "ALTER TABLE `operations`
      ADD `poste_sspi_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
      ADD INDEX (`chir_2_id`),
      ADD INDEX (`chir_3_id`),
      ADD INDEX (`chir_4_id`),
      ADD INDEX (`poste_sspi_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.62");
        $query = "ALTER TABLE `sejour`
      ADD `isolement_date` DATETIME AFTER `isolement`,
      ADD `raison_medicale` TEXT AFTER `isolement_date`;";
        $this->addQuery($query);

        $this->makeRevision("1.63");
        $query = "ALTER TABLE `operations`
      CHANGE `sortie_reveil` `sortie_reveil_possible` TIME,
      ADD `sortie_reveil_reel` TIME AFTER `sortie_reveil_possible`;";
        $this->addQuery($query);

        $query = "UPDATE `operations`
      SET `sortie_reveil_reel` = `sortie_reveil_possible`";
        $this->addQuery($query);

        $this->makeRevision("1.64");
        $query = "ALTER TABLE `sejour`
      ADD `isolement_fin` DATETIME AFTER `isolement_date`;";
        $this->addQuery($query);

        $this->makeRevision("1.65");
        $query = "ALTER TABLE `operations`
      ADD `examen_operation_id` INT (11) UNSIGNED,
      ADD INDEX (`examen_operation_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.66");
        $this->getFieldRenameQueries("COperation", "sortie_reveil", "sortie_reveil_possible");

        $this->makeRevision("1.67");

        $query = "ALTER TABLE `sejour`
                CHANGE `assurance_maladie` `assurance_maladie` INT (11) UNSIGNED,
                CHANGE `assurance_accident` `assurance_accident` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                ADD INDEX (`assurance_maladie`),
                ADD INDEX (`assurance_accident`);";
        $this->addQuery($query);

        $this->makeRevision("1.68");
        $query = "CREATE TABLE `mode_entree_sejour` (
                `mode_entree_sejour_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code` VARCHAR (40) NOT NULL,
                `mode` VARCHAR (20) NOT NULL,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255),
                `actif` ENUM ('0','1') DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `mode_entree_sejour`
              ADD INDEX (`group_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `mode_sortie_sejour` (
                `mode_sortie_sejour_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code` VARCHAR (40) NOT NULL,
                `mode` VARCHAR (20) NOT NULL,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255),
                `actif` ENUM ('0','1') DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `mode_sortie_sejour`
                ADD INDEX (`group_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
                ADD `mode_entree_id` INT (11) UNSIGNED AFTER `mode_entree`,
                ADD `mode_sortie_id` INT (11) UNSIGNED AFTER `mode_sortie`,
                ADD INDEX (`mode_entree_id`),
                ADD INDEX (`mode_sortie_id`);";
        $this->addQuery($query);
        $this->makeRevision("1.69");

        $this->addDependency('dPcabinet', '1.84');

        $query = "ALTER TABLE `protocole` 
              ADD `type_anesth` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations` 
              ADD `ASA` ENUM ('1','2','3','4','5') DEFAULT '1',
              ADD `position` ENUM ('DD','DV','DL','GP','AS','TO','GYN');";
        $this->addQuery($query);

        $query = "UPDATE `operations`, `consultation_anesth`
      SET `operations`.`ASA` = `consultation_anesth`.`ASA`,
      `operations`.`position` = `consultation_anesth`.`position`
      WHERE `consultation_anesth`.`operation_id` = `operations`.`operation_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.70");

        $query = "ALTER TABLE `sejour` DROP INDEX `assurance_maladie`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour` DROP INDEX `assurance_accident`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
           DROP `assurance_accident`,
           DROP `assurance_maladie`,
           DROP `rques_assurance_accident`,
           DROP `rques_assurance_maladie`;";
        $this->addQuery($query);

        $this->makeRevision("1.71");
        $query = "ALTER TABLE `sejour`
      CHANGE `ATNC` `ATNC` ENUM ('0', '1')";
        $this->addQuery($query);

        $query = "UPDATE `sejour`
      SET `ATNC` = NULL
      WHERE `ATNC` = '0';";
        $this->addQuery($query);

        $this->makeRevision("1.72");
        $query = "CREATE TABLE `regle_sectorisation` (
              `regle_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `service_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `function_id` INT (11) UNSIGNED,
              `praticien_id` INT (11) UNSIGNED,
              `duree_min` INT (11),
              `duree_max` INT (11),
              `date_min` DATETIME,
              `date_max` DATETIME,
              `type_adminission` ENUM ('comp','ambu','exte','seances','ssr','psy','urg','consult'),
              `type_pec` ENUM ('M','C','O'),
              `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0')
              /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `regle_sectorisation`
              ADD INDEX (`service_id`),
              ADD INDEX (`function_id`),
              ADD INDEX (`praticien_id`),
              ADD INDEX (`date_min`),
              ADD INDEX (`date_max`),
              ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.73");
        $query = "ALTER TABLE `regle_sectorisation`
    CHANGE `type_adminission` `type_admission` ENUM( 'comp', 'ambu', 'exte', 'seances', 'ssr', 'psy', 'urg', 'consult' )
    NULL DEFAULT NULL ";
        $this->addQuery($query);

        $this->makeRevision("1.74");
        $query = "ALTER TABLE `operations`
                CHANGE `ASA` `ASA` ENUM ('1','2','3','4','5','6') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.75");
        if (!CAppUI::conf("dPplanningOp CSejour use_recuse")) {
            $query = "UPDATE `sejour`
        SET `sejour`.`recuse` = '0'
        WHERE `sejour`.`type` != 'ssr'";
            $this->addQuery($query);
        }

        $this->makeRevision("1.76");

        $query = "ALTER TABLE `sejour`
                CHANGE `mode_sortie` `mode_sortie` ENUM ('normal','transfert','mutation','deces');";
        $this->addQuery($query);

        $this->makeRevision("1.77");
        $query = "ALTER TABLE `operations`
                ADD `graph_pack_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.78");
        $query = "ALTER TABLE `protocole`
                ADD `duree_heure_hospi` TINYINT (4) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.79");
        $query = "ALTER TABLE `operations`
                ADD `tarif` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                ADD `tarif` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.80");
        $query = "ALTER TABLE `operations`
                ADD `remise_chir` TIME,
                ADD `tto` TIME,
                ADD `rques_personnel` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.81");
        $query = "ALTER TABLE `operations`
                CHANGE `ASA` `ASA` ENUM ('1','2','3','4','5','6');";
        $this->addQuery($query);

        $this->makeRevision("1.82");
        $query = "ALTER TABLE `mode_entree_sejour`
                CHANGE `actif` `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.83");
        $query = "ALTER TABLE `mode_sortie_sejour`
                CHANGE `actif` `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.84");
        $query = "CREATE TABLE `operation_workflow` (
      `miner_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `operation_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      `date` DATE NOT NULL,
      `remined` ENUM ('0','1') NOT NULL DEFAULT '0',
      `date_operation` DATETIME NOT NULL,
      `date_creation` DATETIME,
      `date_cancellation` DATETIME,
      `date_consult_chir` DATETIME,
      `date_consult_anesth` DATETIME,
      `date_creation_consult_chir` DATETIME,
      `date_creation_consult_anesth` DATETIME,
      `date_visite_anesth` DATE
    )/*! ENGINE=MyISAM */";
        $this->addQuery($query);
        $query = "ALTER TABLE `operation_workflow`
      ADD INDEX (`operation_id`),
      ADD INDEX (`date`)";
        $this->addQuery($query);

        $this->makeRevision("1.85");
        $query = "ALTER TABLE `protocole`
                ADD `facturable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.86");
        $query = "ALTER TABLE `operations`
      ADD `sortie_locker_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.87");
        $query = "ALTER TABLE `sejour`
      ADD `confirme_date` DATETIME AFTER `confirme`;";
        $this->addQuery($query);

        $query = "UPDATE `sejour`
      SET `confirme_date` = `sejour`.`sortie_prevue`
      WHERE `sejour`.`confirme` = '1'";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      DROP `confirme`,
      CHANGE `confirme_date` `confirme` DATETIME";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      ADD `confirme_user_id` INT (11) UNSIGNED AFTER `confirme`;";
        $this->addQuery($query);
        $this->makeRevision("1.88");

        // Synchronisation de la date de l'intervention avec celle de la plage
        $query = "UPDATE `operations`
                LEFT JOIN plagesop ON plagesop.plageop_id = `operations`.`plageop_id`
                SET `operations`.`date` = plagesop.date
                WHERE `operations`.plageop_id IS NOT NULL";
        $this->addQuery($query);
        $this->makeRevision('1.89');

        $query = "ALTER TABLE `protocole`
                ADD `charge_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->addDefaultConfigCIP();
        $this->makeRevision('1.90');

        $query = "ALTER TABLE `type_anesth`
                ADD `group_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `type_anesth`
                ADD INDEX (`group_id`);";
        $this->addQuery($query);
        $this->makeRevision('1.91');

        $query = "ALTER TABLE `sejour`
                ADD `exec_tarif` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
                ADD INDEX (`exec_tarif`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
                ADD `exec_tarif` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
                ADD INDEX (`exec_tarif`);";
        $this->addQuery($query);
        $this->makeRevision('1.92');

        $query = "ALTER TABLE `sejour`
                ADD `reception_sortie` DATETIME,
                ADD `completion_sortie` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour`
                ADD INDEX (`reception_sortie`),
                ADD INDEX (`completion_sortie`);";
        $this->addQuery($query);

        $this->makeRevision("1.93");
        $query = "ALTER TABLE `charge_price_indicator`
                CHANGE `group_id` `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                ADD `color` VARCHAR (6) NOT NULL DEFAULT 'ffffff';";
        $this->addQuery($query);

        $this->makeRevision("1.94");
        $query = "ALTER TABLE `operation_workflow`
                ADD `postmined` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.95");
        $query = "ALTER TABLE `operations`
                ADD `poste_preop_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.96");
        $query = "ALTER TABLE `sejour`
                ADD `technique_reanimation` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.97");
        $query = "ALTER TABLE `operations`
                ADD `entree_chir` TIME AFTER induction_debut,
                ADD `entree_anesth` TIME AFTER entree_chir;";
        $this->addQuery($query);
        $this->makeRevision("1.98");

        $this->makeRevision("1.99");
        $query = "ALTER TABLE `sejour`
      ADD `consult_related_id` INT (11) UNSIGNED AFTER `group_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
      ADD `consult_related_id` INT (11) UNSIGNED AFTER `salle_id`;";
        $this->addQuery($query);

        $this->addFunctionalPermQuery("allowed_check_entry", "0");
        $this->makeRevision("2.00");

        $query = "CREATE TABLE `sejour_affectation` (
                `sejour_affectation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour_affectation`
                ADD INDEX (`sejour_id`),
                ADD INDEX (`user_id`);";
        $this->addQuery($query);
        $this->makeRevision("2.01");

        $query = "ALTER TABLE `operations`
              DROP `entree_chir`,
              DROP `entree_anesth`;";
        $this->addQuery($query);

        $this->makeRevision("2.02");
        $dsn = CSQLDataSource::get('std');
        if (!$dsn->fetchRow($dsn->exec('SHOW TABLES LIKE \'libelleop\';'))) {
            $query = "CREATE TABLE `libelleop` (
                  `libelleop_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                  `statut` ENUM ('valide','no_valide','indefini'),
                  `nom` VARCHAR (255) NOT NULL,
                  `date_debut` DATETIME,
                  `date_fin` DATETIME,
                  `services` VARCHAR (255),
                  `mots_cles` VARCHAR (255),
                  `numero` INT (11) NOT NULL DEFAULT '0',
                  `version` INT (11) DEFAULT '1',
                  `group_id` INT (11) UNSIGNED
                )/*! ENGINE=MyISAM */;";
            $this->addQuery($query, true);

            $query = "ALTER TABLE `libelleop`
                ADD INDEX `date_debut` (`date_debut`),
                ADD INDEX `group_id` (`group_id`),
                ADD INDEX `date_fin` (`date_fin`);";
            $this->addQuery($query, true);
        }

        $this->makeRevision("2.03");
        if (!$dsn->fetchRow($dsn->exec('SHOW TABLES LIKE \'liaison_libelle\';'))) {
            $query = "CREATE TABLE `liaison_libelle` (
                `liaison_libelle_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelleop_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `operation_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `numero` TINYINT (4) UNSIGNED DEFAULT '1')/*! ENGINE=MyISAM */;";
            $this->addQuery($query, true);

            $query = "ALTER TABLE `liaison_libelle`
                ADD INDEX `libelleop_id` (`libelleop_id`),
                ADD INDEX `operation_id` (`operation_id`);";
            $this->addQuery($query, true);
        }
        $this->makeRevision("2.04");
        $query = "ALTER TABLE `sejour`
                ADD `handicap` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.05');
        $query = "ALTER TABLE `mode_sortie_sejour`
                ADD `destination` ENUM('1','2','3','4','6','7'),
                ADD `orientation` ENUM('HDT','HO','SC','SI','REA','UHCD','MED','CHIR','OBST','FUGUE','SCAM','PSA','REO');";
        $this->addQuery($query);

        $this->makeRevision("2.06");
        $query = "ALTER TABLE `regle_sectorisation`
                ADD `priority` INT (11) NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.07");

        $query = "CREATE TABLE `operation_commande` (
                `commande_materiel_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `operation_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `etat` ENUM ('a_commander','commandee','modify','recue','annulee','a_annuler') NOT NULL DEFAULT 'a_commander',
                `date` DATE,
                `commentaire` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operation_commande`
                ADD INDEX (`operation_id`),
                ADD INDEX (`date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
                ADD `exam_per_op` TEXT;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
                ADD `exam_per_op` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("2.08");

        $query = "INSERT INTO `operation_commande` (`operation_id` ,`etat`,`date`)
        SELECT o.operation_id, 'commandee', p.date
        FROM operations o, plagesop p
        WHERE o.commande_mat = '1'
        AND o.annulee = '0'
        AND o.plageop_id = p.plageop_id;";
        $this->addQuery($query);

        $query = "INSERT INTO `operation_commande` (`operation_id` ,`etat`,`date`)
        SELECT o.operation_id, 'a_annuler', p.date
        FROM operations o, plagesop p
        WHERE o.commande_mat = '1'
        AND o.annulee = '1'
        AND o.plageop_id = p.plageop_id;";
        $this->addQuery($query);

        $this->makeRevision('2.09');

        $query = "ALTER TABLE `mode_sortie_sejour`
                CHANGE `destination` `destination` ENUM('0', '1','2','3','4','6','7');";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
                CHANGE `destination` `destination` ENUM('0', '1','2','3','4','6','7');";
        $this->addQuery($query);

        $this->makeRevision('2.10');
        $query = "ALTER TABLE `sejour`
                ADD `date_entree_reelle_provenance` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision('2.11');
        $query = "ALTER TABLE sejour ADD INDEX consult_related_id (consult_related_id)";
        $this->addQuery($query, true);

        $query = "ALTER TABLE operations ADD INDEX consult_related_id (consult_related_id)";
        $this->addQuery($query, true);

        $this->makeRevision("2.12");
        $query = "ALTER TABLE `regle_sectorisation`
                ADD `age_min` TINYINT UNSIGNED,
                ADD `age_max` TINYINT UNSIGNED,
                ADD `handicap` ENUM('0', '1');";
        $this->addQuery($query);

        $this->makeRevision("2.13");
        $query = "ALTER TABLE `operations`
               CHANGE `position` `position` ENUM ('DD','DV','DL','GP','AS','TO','GYN','DDA')";
        $this->addQuery($query);

        $this->makeRevision("2.14");
        $query = "ALTER TABLE `operations`
      ADD `time_visite_anesth` TIME AFTER `date_visite_anesth`";
        $this->addQuery($query);

        $this->makeRevision("2.15");
        $query = "ALTER TABLE `operations`
      ADD `suture_fin` TIME AFTER `induction_fin`";
        $this->addQuery($query);
        $this->makeRevision("2.16");

        $this->addMethod("moveConfigUFSoins");
        $this->makeRevision("2.17");

        $query = "ALTER TABLE `sejour`
                CHANGE `provenance` `provenance` ENUM ('1','2','3','4','5','6','7','8','R');";
        $this->addQuery($query);
        $this->makeRevision("2.18");

        $query = "CREATE TABLE `sejour_appel` (
                `appel_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `datetime` DATETIME NOT NULL,
                `type` ENUM ('admission','sortie') NOT NULL DEFAULT 'admission',
                `etat` ENUM ('realise','echec') NOT NULL DEFAULT 'realise',
                `commentaire` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour_appel`
                ADD INDEX (`sejour_id`),
                ADD INDEX (`datetime`);";
        $this->addQuery($query);
        $this->makeRevision("2.19");

        $query = "ALTER TABLE `sejour_appel`
                ADD `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `sejour_appel`
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision("2.20");
        $query = "ALTER TABLE `operations`
                ADD `cleaning_start`     TIME,
                ADD `cleaning_end`       TIME,
                ADD `installation_start` TIME,
                ADD `installation_end`   TIME;";
        $this->addQuery($query);

        $this->makeRevision('2.21');
        $query = "CREATE TABLE `operation_garrot` (
                `operation_garrot_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `operation_id`        INT(11) UNSIGNED NOT NULL,
                `cote`                ENUM('gauche', 'droit', 'N/A') NOT NULL DEFAULT 'N/A',
                `datetime_pose`       DATETIME NOT NULL,
                `datetime_retrait`    DATETIME,
                `user_pose_id`        INT(11) UNSIGNED NOT NULL,
                `user_retrait_id`     INT(11) UNSIGNED,
                INDEX (`operation_id`),
                INDEX (`user_pose_id`),
                INDEX (`user_retrait_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('2.22');
        $query = "ALTER TABLE `sejour`
      CHANGE `handicap` `handicap` ENUM ('0','1','2','3') DEFAULT '0',
      ADD `aide_organisee` ENUM ('repas','entretien','soins','mouvoir');";
        $this->addQuery($query);
        $this->makeRevision('2.23');

        $query = "ALTER TABLE `protocole`
                ADD `duree_bio_nettoyage` TIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
                ADD `duree_bio_nettoyage` TIME;";
        $this->addQuery($query);
        $this->makeRevision('2.24');

        $query = "ALTER TABLE `protocole`
                CHANGE `duree_bio_nettoyage` `duree_bio_nettoyage` TIME NOT NULL DEFAULT '00:00:00';";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
                CHANGE `duree_bio_nettoyage` `duree_bio_nettoyage` TIME NOT NULL DEFAULT '00:00:00';";
        $this->addQuery($query);

        $this->makeRevision('2.25');

        $this->addFunctionalPermQuery('create_dhe_with_read_rights', '1');

        $this->makeRevision('2.26');

        $query = "ALTER TABLE `operations`
                ADD `urgence` ENUM('0', '1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.27");

        $query = "ALTER TABLE `sejour`
      ADD `hospit_de_jour` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      ADD `hospit_de_jour` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `sejour`
      CHANGE `type_pec` `type_pec` ENUM ('M','C','O', 'SSR');";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      CHANGE `type_pec` `type_pec` ENUM ('M','C','O', 'SSR');";
        $this->addQuery($query);

        $query = "ALTER TABLE `charge_price_indicator`
      CHANGE `type_pec` `type_pec` ENUM ('M','C','O', 'SSR');";
        $this->addQuery($query);

        $query = "ALTER TABLE `regle_sectorisation`
      CHANGE `type_pec` `type_pec` ENUM ('M','C','O', 'SSR');";
        $this->addQuery($query);

        $query = "ALTER TABLE `charge_price_indicator`
      ADD `hospit_de_jour` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision("2.28");

        $query = "ALTER TABLE `operations`
      ADD `protocole_id` INT (11) UNSIGNED AFTER `consult_related_id`,
      ADD INDEX (`protocole_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.29');

        $query = "ALTER TABLE `sejour`
      ADD `directives_anticipees` ENUM ('0', '1', 'unknown');";
        $this->addQuery($query);
        $this->makeRevision('2.30');

        $query = "ALTER TABLE `operations`
      ADD `prep_cutanee` TIME AFTER `pose_garrot`;";
        $this->addQuery($query);
        $this->makeRevision('2.31');

        $query = "ALTER TABLE `sejour_affectation`
                ADD `debut` DATETIME,
                ADD `fin` DATETIME,
                ADD INDEX (`debut`),
                ADD INDEX (`fin`);";
        $this->addQuery($query);

        $this->makeRevision("2.32");
        $query = "ALTER TABLE `operations`
                ADD `graph_pack_sspi_id` INT (11) UNSIGNED AFTER `graph_pack_id`;";
        $this->addQuery($query);
        $this->makeRevision("2.33");

        $query = "ALTER TABLE `type_anesth`
                ADD `duree_postop` TIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
                ADD `duree_postop` TIME;";
        $this->addQuery($query);
        $this->makeRevision('2.34');

        $query = "ALTER TABLE `protocole`
                ADD `facturation_rapide` ENUM('0', '1') DEFAULT '0',
                ADD `DR` VARCHAR(5) DEFAULT NULL,
                ADD `codage_ccam` VARCHAR(255);";
        $this->addQuery($query);
        $this->makeRevision('2.35');

        $query = "ALTER TABLE `type_anesth`
                CHANGE `duree_postop` `duree_postop` TIME NOT NULL DEFAULT '00:00:00';";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
                CHANGE `duree_postop` `duree_postop` TIME NOT NULL DEFAULT '00:00:00';";
        $this->addQuery($query);
        $this->makeRevision('2.36');

        $query = "ALTER TABLE `operations`
                ADD `incision` TIME;";
        $this->addQuery($query);
        $this->makeRevision('2.37');

        $query = "ALTER TABLE `sejour`
                ADD `entree_preparee_date` DATETIME;";
        $this->addQuery($query);
        $this->makeRevision('2.38');

        $query = "ALTER TABLE `sejour`
      CHANGE `directives_anticipees` `directives_anticipees_status` ENUM ('0', '1', 'unknown'),
      ADD `directives_anticipees` TEXT AFTER `hospit_de_jour`,
      ADD `technique_reanimation_status` ENUM ('0', '1', 'unknown') AFTER `technique_reanimation`";
        $this->addQuery($query);

        $query = "UPDATE `sejour`
      SET `technique_reanimation_status` = '1'
      WHERE `technique_reanimation` IS NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision('2.39');

        $query = "ALTER TABLE `sejour`
      ADD `sans_dmh` ENUM ('0','1') DEFAULT '0' AFTER `completion_sortie`;";
        $this->addQuery($query);
        $this->makeRevision('2.40');

        $query = "ALTER TABLE `sejour`
                ADD `pec_accueil` DATETIME,
                ADD `pec_service` DATETIME,
                ADD INDEX (`pec_accueil`),
                ADD INDEX (`pec_service`);";
        $this->addQuery($query);
        $this->makeRevision('2.41');

        $query = "ALTER TABLE `operations`
                ADD `materiel_pharma` TEXT,
                ADD `commande_mat_pharma` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `operation_commande`
                ADD `type` ENUM ('bloc','pharmacie') NOT NULL DEFAULT 'bloc';";
        $this->addQuery($query);

        $this->makeRevision('2.42');

        $query = "ALTER TABLE `operations` 
                ADD `graph_pack_locked_user_id` INT (11) UNSIGNED AFTER `graph_pack_id`,
                ADD `graph_pack_sspi_locked_user_id` INT (11) UNSIGNED AFTER `graph_pack_sspi_id`;";
        $this->addQuery($query);
        $this->makeRevision('2.43');

        $query = "ALTER TABLE `sejour`
      ADD `pec_ambu` ENUM ('NR','non','oui') DEFAULT 'NR',
      ADD `rques_pec_ambu` TEXT;";
        $this->addQuery($query);
        $this->makeRevision('2.44');

        $query = "ALTER TABLE `operations`
                ADD `materiel_sterilise` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('2.45');

        $query = "ALTER TABLE `sejour`
                ADD `presence_confidentielle` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('2.46');

        $query = "ALTER TABLE `protocole`
                CHANGE `codage_ccam` `codage_ccam_chir` VARCHAR(255),
                ADD `codage_ccam_anesth` VARCHAR(255);";
        $this->addQuery($query);
        $this->makeRevision('2.47');

        $query = "ALTER TABLE `sejour`
                ADD `mode_destination_id` INT (11) UNSIGNED,
                ADD `mode_pec_id` INT (11) UNSIGNED,
                ADD INDEX (`mode_destination_id`),
                ADD INDEX (`mode_pec_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `mode_destination_sejour` (
                `mode_destination_sejour_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `code` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `actif` ENUM ('0','1') DEFAULT '1',
                `default` ENUM ('0','1') DEFAULT '0',
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `mode_pec_sejour` (
                `mode_pec_sejour_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `code` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `actif` ENUM ('0','1') DEFAULT '1',
                `default` ENUM ('0','1') DEFAULT '0',
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision('2.48');

        $this->updateTimingsOperation();
        $this->makeRevision('2.49');

        $query = "ALTER TABLE `protocole`
                ADD `actif` ENUM('0', '1') DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision('2.50');

        $query = "ALTER TABLE `operations`
                ADD `validation_timing` DATETIME;";
        $this->addQuery($query);
        $this->makeRevision('2.51');

        $query = "CREATE TABLE `protocole_doc` (
      `protocole_doc_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `protocole_id` INT (11) UNSIGNED,
      `type` ENUM ('sejour','operation')
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole_doc` 
      ADD INDEX (`protocole_id`);";
        $this->addQuery($query);
        $this->makeRevision('2.52');

        $query = "ALTER TABLE `operations`
                ADD `debut_alr` DATETIME,
                ADD `fin_alr` DATETIME,
                ADD `debut_ag` DATETIME,
                ADD `fin_ag` DATETIME,
                ADD INDEX (`debut_prepa_preop`),
                ADD INDEX (`fin_prepa_preop`),
                ADD INDEX (`entree_bloc`),
                ADD INDEX (`entree_salle`),
                ADD INDEX (`pose_garrot`),
                ADD INDEX (`prep_cutanee`),
                ADD INDEX (`debut_op`),
                ADD INDEX (`fin_op`),
                ADD INDEX (`retrait_garrot`),
                ADD INDEX (`sortie_salle`),
                ADD INDEX (`remise_chir`),
                ADD INDEX (`tto`),
                ADD INDEX (`entree_reveil`),
                ADD INDEX (`sortie_reveil_possible`),
                ADD INDEX (`sortie_reveil_reel`),
                ADD INDEX (`induction_debut`),
                ADD INDEX (`induction_fin`),
                ADD INDEX (`suture_fin`),
                ADD INDEX (`incision`),
                ADD INDEX (`validation_timing`),
                ADD INDEX (`debut_alr`),
                ADD INDEX (`fin_alr`),
                ADD INDEX (`debut_ag`),
                ADD INDEX (`fin_ag`),
                ADD INDEX (`cleaning_start`),
                ADD INDEX (`cleaning_end`),
                ADD INDEX (`installation_start`),
                ADD INDEX (`installation_end`);";
        $this->addQuery($query);

        $this->makeRevision('2.53');
        $query = "ALTER TABLE `operations`
      ADD `rayons_x` ENUM ('1','0','?') DEFAULT '?',
      ADD `ampli_rayons_x` VARCHAR (255),
      ADD `temps_rayons_x` TIME,
      ADD `dose_rayons_x` FLOAT,
      ADD INDEX (`ampli_rayons_x`),
      ADD INDEX (`rayons_x`);";
        $this->addQuery($query);

        $this->makeRevision('2.54');
        $query = "ALTER TABLE `mode_sortie_sejour`
      ADD `etab_externe_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision('2.55');

        $query = "ALTER TABLE `sejour`
                ADD `RRAC` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
                ADD `RRAC` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('2.56');

        $query = "CREATE TABLE `honos` (
                `sejour_id` INT (11) UNSIGNED NOT NULL,
                `type` ENUM ('entree','sortie','other') NOT NULL,
                `type_age` ENUM ('young','adult','old') NOT NULL,
                `date` DATE,
                `fid` INT (11),
                `drop_out` ENUM ('0','1','2'),
                `drop_out_motif` TEXT,
                `hyperactif` ENUM ('0','1','2','3','4','9'),
                `automutilation` ENUM ('0','1','2','3','4','9'),
                `conso_stup` ENUM ('0','1','2','3','4','9'),
                `cognitif` ENUM ('0','1','2','3','4','9'),
                `handicap` ENUM ('0','1','2','3','4','9'),
                `delire` ENUM ('0','1','2','3','4','9'),
                `depression` ENUM ('0','1','2','3','4','9'),
                `mental` ENUM ('0','1','2','3','4','9'),
                `mental_severite` ENUM ('a','b','c','d','e','f','g','h','i','j'),
                `mental_rmq` TEXT,
                `social` ENUM ('0','1','2','3','4','9'),
                `quotidien` ENUM ('0','1','2','3','4','9'),
                `logement` ENUM ('0','1','2','3','4','9'),
                `activite` ENUM ('0','1','2','3','4','9'),
                `med_psy` ENUM ('0','1','2','3','4','9'),
                `perturbateur` ENUM ('0','1','2','3','4','9'),
                `scolarite` ENUM ('0','1','2','3','4','9'),
                `somatique` ENUM ('0','1','2','3','4','9'),
                `emotionnel` ENUM ('0','1','2','3','4','9'),
                `social_young` ENUM ('0','1','2','3','4','9'),
                `absenteisme` ENUM ('0','1','2','3','4','9'),
                `comprehension` ENUM ('0','1','2','3','4','9'),
                `info_ttt` ENUM ('0','1','2','3','4','9'),
                `honos_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                INDEX (`sejour_id`),
                INDEX (`date`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision('2.57');

        $query = "ALTER TABLE `operations`
                ADD `pec_anesth` DATETIME,
                ADD INDEX (`pec_anesth`);";
        $this->addQuery($query);
        $this->makeRevision('2.58');

        $query = "ALTER TABLE `operations`
                ADD `visitors` TEXT;";
        $this->addQuery($query);
        $this->makeRevision('2.59');

        $query = "ALTER TABLE `operations`
                ADD `unite_rayons_x` ENUM ('mA','mGy') DEFAULT 'mA';";
        $this->addQuery($query);

        $this->makeRevision('2.60');
        $query = "ALTER TABLE `sejour`
                ADD `nuit_convenance` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.61');
        $query = "ALTER TABLE `sejour`
      ADD `dmi_prevu` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.62');

        $this->addDefaultConfig(
            'dPplanningOp CSejour default_hours heure_entree_veille',
            'dPplanningOp CSejour heure_entree_veille'
        );
        $this->addDefaultConfig(
            'dPplanningOp CSejour default_hours heure_entree_jour',
            'dPplanningOp CSejour heure_entree_jour'
        );
        $this->addDefaultConfig(
            'dPplanningOp CSejour default_hours heure_sortie_ambu',
            'dPplanningOp CSejour heure_sortie_ambu'
        );
        $this->addDefaultConfig(
            'dPplanningOp CSejour default_hours heure_sortie_autre',
            'dPplanningOp CSejour heure_sortie_autre'
        );
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue comp');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue ambu');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue exte');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue seances');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue ssr');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue psy');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue urg');
        $this->addDefaultConfig('dPplanningOp CSejour sortie_prevue consult');
        $this->makeRevision('2.63');

        $query = "ALTER TABLE `honos` 
                ADD `last_modif` DATETIME,
                ADD `last_send` DATETIME,
                ADD INDEX (`last_modif`),
                ADD INDEX (`last_send`);";
        $this->addQuery($query);
        $this->makeRevision('2.64');

        $query = "ALTER TABLE `sejour` ADD `last_seance` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);;
        $this->makeRevision('2.65');

        $query = "ALTER TABLE `operations` 
                ADD `preparation_op` DATETIME,
                ADD INDEX (`preparation_op`);";
        $this->addQuery($query);

        $this->makeRevision('2.66');

        $query = "ALTER TABLE `operations`
      CHANGE `cote` `cote` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu', 'non_applicable') NOT NULL DEFAULT 'inconnu',
      CHANGE `cote_admission` `cote_admission` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu', 'non_applicable'),
      CHANGE `cote_consult_anesth` `cote_consult_anesth` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu', 'non_applicable'),
      CHANGE `cote_hospi` `cote_hospi` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu', 'non_applicable'),
      CHANGE `cote_bloc` `cote_bloc` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu', 'non_applicable');";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
      CHANGE `cote` `cote` ENUM ('droit','gauche','haut','bas','bilatéral','total','inconnu','non_applicable')";
        $this->addQuery($query);
        $this->makeRevision('2.67');

        $query = "ALTER TABLE `operations`
                ADD `commentaire_depassement_anesth` TEXT AFTER `depassement_anesth`;";
        $this->addQuery($query);
        $this->makeRevision('2.68');

        $query = "ALTER TABLE `sejour` 
                ADD `motif_annulation` ENUM ('doublon','contre_indication','perso','amelioration_sante','not_arrived','problem_bloc','no_lit','other') AFTER `annule`,
                ADD `rques_annulation` TEXT AFTER `motif_annulation`;";
        $this->addQuery($query);
        $this->makeRevision('2.69');

        $query = "ALTER TABLE `operations`
      ADD `description_rayons_x` TEXT AFTER `dose_rayons_x`;";
        $this->addQuery($query);

        $this->makeRevision('2.70');

        $query = "ALTER TABLE `sejour` 
                ADD `last_UHCD` DATETIME AFTER `UHCD`;";
        $this->addQuery($query);
        $this->makeRevision("2.71");

        $query = "ALTER TABLE `sejour`
      ADD `volume_perf_alert` FLOAT DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.72");
        $this->addDependency("dPfiles", "0.53");


        $query = "INSERT INTO context_doc (`context_doc_id`, `context_id`, `context_class`, `type`)
      SELECT `protocole_doc_id`, `protocole_id`, 'CProtocole', `type`
      FROM `protocole_doc`;";
        $this->addQuery($query);

        $query = "DROP TABLE `protocole_doc`;";
        $this->addQuery($query);

        if ($this->columnExists('compte_rendu', 'object_class')) {
            $query = "UPDATE `compte_rendu`
      SET `object_class` = 'CContextDoc'
      WHERE `object_class` = 'CProtocoleDoc';";
            $this->addQuery($query);
        }

        $query = "UPDATE `files_mediboard`
      SET `object_class` = 'CContextDoc'
      WHERE `object_class` = 'CProtocoleDoc';";
        $this->addQuery($query);

        $this->makeRevision("2.73");
        $query = "ALTER TABLE `operations`
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy/m²','mGy/m²') DEFAULT 'mA';";
        $this->addQuery($query);

        $this->makeRevision("2.74");
        $query = "ALTER TABLE `operations`
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy/m²','mGy/m²', 'cGy/cm²') DEFAULT 'mA';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
                SET `unite_rayons_x` = 'cGy/cm²' WHERE `unite_rayons_x` = 'cGy/m²';";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy/cm²','mGy/m²') DEFAULT 'mA';";
        $this->addQuery($query);

        $this->makeRevision("2.75");
        $query = "ALTER TABLE `sejour`
      CHANGE `mode_entree` `mode_entree` ENUM ('8','7','6','0');";
        $this->addQuery($query);
        $this->makeRevision("2.76");

        $query = "ALTER TABLE `operations` 
                ADD `remise_anesth` DATETIME,
                ADD `patient_stable` DATETIME,
                ADD INDEX (`remise_anesth`),
                ADD INDEX (`patient_stable`);";
        $this->addQuery($query);

        $this->makeRevision('2.77');

        $query = "ALTER TABLE `protocole` ADD `codage_ngap_sejour` VARCHAR(255) AFTER `codage_ccam_anesth`;";
        $this->addQuery($query);
        $this->makeRevision('2.78');

        $query = "ALTER TABLE `sejour`
                MODIFY COLUMN `DP` VARCHAR(6),
	              MODIFY COLUMN `DR` VARCHAR(6);";
        $this->addQuery($query, true);

        $this->makeRevision('2.79');

        $query = "ALTER TABLE `protocole` 
                ADD `time_entree_prevue` TIME;";
        $this->addQuery($query);

        $this->makeRevision('2.80');

        $query = "ALTER TABLE `operations`
                ADD `reglement_dh_chir` ENUM ('non_regle', 'cb', 'cheque', 'espece', 'virement') DEFAULT 'non_regle' AFTER `depassement`,
                ADD `reglement_dh_anesth` ENUM ('non_regle', 'cb', 'cheque', 'espece', 'virement') DEFAULT 'non_regle' AFTER `depassement_anesth`;";
        $this->addQuery($query);

        $this->makeRevision('2.81');

        $query = "ALTER TABLE `operations`
      ADD `sspi_id` INT (11) UNSIGNED AFTER `envoi_mail`,
      ADD INDEX (`sspi_id`);";
        $this->addQuery($query);

        $this->makeRevision("2.82");
        $query = "ALTER TABLE `operations`
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy/cm²','mGy/m²', 'cGy.cm²', 'mGy.m²') DEFAULT 'mA';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
                SET `unite_rayons_x` = 'cGy.cm²' WHERE `unite_rayons_x` = 'cGy/cm²';";
        $this->addQuery($query);
        $query = "UPDATE `operations`
                SET `unite_rayons_x` = 'mGy.m²' WHERE `unite_rayons_x` = 'mGy/m²';";
        $this->addQuery($query);
        $query = "ALTER TABLE `operations`
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy.cm²','mGy.m²') DEFAULT 'mA';";
        $this->addQuery($query);

        $this->makeRevision("2.83");

        $query = "ALTER TABLE `sejour`
                CHANGE `mode_sortie` `mode_sortie` ENUM ('normal','transfert','transfert_acte','mutation','deces');";
        $this->addQuery($query);

        $query = "ALTER TABLE `mode_sortie_sejour`
                CHANGE `mode` `mode` ENUM ('normal','transfert','transfert_acte','mutation','deces') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("2.84");

        $query = "ALTER TABLE `sejour`
                CHANGE `mode_entree` `mode_entree` ENUM ('N','8','7','6','0');";
        $this->addQuery($query);

        $query = "ALTER TABLE `mode_entree_sejour`
                CHANGE `mode` `mode` ENUM ('N','8','7','6','0') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('2.85');

        $query = "ALTER TABLE `sejour`
                CHANGE `codes_ccam` `codes_ccam` VARCHAR(512);";
        $this->addQuery($query);

        $this->makeRevision('2.86');

        $query = "ALTER TABLE `operations` 
                ADD `graph_pack_preop_id` INT (11) UNSIGNED AFTER `graph_pack_sspi_id`,
                ADD `graph_pack_preop_locked_user_id` INT (11) UNSIGNED AFTER `graph_pack_preop_id`,
                ADD INDEX (`graph_pack_preop_id`),
                ADD INDEX (`graph_pack_preop_locked_user_id`);";
        $this->addQuery($query);
        $this->makeRevision('2.87');

        $query = "CREATE TABLE `position` (
                `position_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED,
                `code` VARCHAR (20) NOT NULL,
                `libelle` VARCHAR (50),
                `actif` ENUM('0','1') NOT NULL DEFAULT '1',
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations` 
                ADD `position_id` INT (11) UNSIGNED,
                ADD INDEX (`position_id`);";
        $this->addQuery($query);

        $positions = [
            "DD"  => "DD",
            "DV"  => "DV",
            "DL"  => "DL",
            "GP"  => "GP",
            "AS"  => "Assise",
            "TO"  => "Table orthopédique",
            "GYN" => "Gynécologique",
            "DDA" => "DD + 1/2 assis",
        ];

        $num_position = 1;
        foreach ($positions as $code => $libelle) {
            $query = "INSERT INTO `position`(`position_id`, `code`, `libelle`, `actif`)
                VALUES ('$num_position' , '$code', '$libelle', '1') ;";
            $this->addQuery($query);

            $query = "UPDATE `operations`
                SET `position_id` = '" . $num_position . "'
                WHERE `position` = '" . $code . "';";
            $this->addQuery($query);
            $num_position++;
        }
        $this->makeRevision('2.88');

        $this->addDefaultConfig(
            'dPplanningOp CFactureEtablissement use_facture_etab',
            'dPplanningOp CFactureEtablissement use_facture_etab'
        );
        $this->makeRevision('2.89');

        $query = "ALTER TABLE `operations` 
                ADD `sortie_sans_sspi` DATETIME,
                ADD INDEX (`sortie_sans_sspi`);";
        $this->addQuery($query);
        $this->makeRevision('2.90');

        $query = "ALTER TABLE `sejour`
                ADD `circuit_ambu` ENUM ('court','moyen','long');";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
                ADD `circuit_ambu` ENUM ('court','moyen','long');";
        $this->addQuery($query);

        $this->makeRevision('2.91');

        $query = "ALTER TABLE `operations` 
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy.cm²','mGy.m²','mGy.cm²') DEFAULT 'mA';";
        $this->addQuery($query);

        $this->makeRevision('2.92');
        $query = "ALTER TABLE `protocole`
                ADD `admission` ENUM ('veille','jour_meme');";
        $this->addQuery($query);

        $this->makeRevision('2.93');

        $query = "ALTER TABLE `operations` 
                CHANGE `unite_rayons_x` `unite_rayons_x` ENUM ('mA','mGy','cGy.cm²','mGy.m²','mGy.cm²','Gy.cm²') DEFAULT 'mA';";
        $this->addQuery($query);

        $this->makeRevision('2.94');

        $query = "ALTER TABLE `position` 
                CHANGE `code` `code` VARCHAR (255),
                CHANGE `libelle` `libelle` VARCHAR (255) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("2.95");

        $query = "ALTER TABLE `protocole`
                MODIFY `admission` ENUM ('veille','jour');";
        $this->addQuery($query);

        $this->makeRevision("2.96");

        $query = "CREATE TABLE `autorisation_permission` (
                `autorisation_permission_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED,
                `praticien_id` INT (11) UNSIGNED NOT NULL,
                `debut` DATETIME NOT NULL,
                `duree` INT (11) NOT NULL,
                `rques` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `autorisation_permission` 
                ADD INDEX (`sejour_id`),
                ADD INDEX (`praticien_id`),
                ADD INDEX (`debut`);";
        $this->addQuery($query);
        $this->makeRevision("2.97");

        $query = "ALTER TABLE `operations` 
                ADD `fin_pec_anesth` DATETIME,
                ADD INDEX (`fin_pec_anesth`);";
        $this->addQuery($query);

        $this->makeRevision("2.98");

        $query = "ALTER TABLE `operation_garrot` 
                ADD `pression` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("2.99");

        $query = "CREATE TABLE `protocole_operatoire` (
                `protocole_operatoire_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `chir_id` INT (11) UNSIGNED,
                `function_id` INT (11) UNSIGNED,
                `group_id` INT (11) UNSIGNED,
                `libelle` VARCHAR (255) NOT NULL,
                `actif` ENUM ('0','1') DEFAULT '1',
                `numero_version` BIGINT ZEROFILL,
                `remarque` TEXT,
                `validation_praticien_id` INT (11) UNSIGNED,
                `validation_praticien_datetime` DATETIME,
                `validation_cadre_bloc_id` INT (11) UNSIGNED,
                `validation_cadre_bloc_datetime` DATETIME,
                `description_equipement_salle` TEXT,
                `description_installation_patient` TEXT,
                `description_preparation_patient` TEXT,
                `description_instrumentation` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole_operatoire` 
                ADD INDEX (`chir_id`),
                ADD INDEX (`function_id`),
                ADD INDEX (`group_id`),
                ADD INDEX (`validation_praticien_id`),
                ADD INDEX (`validation_praticien_datetime`),
                ADD INDEX (`validation_cadre_bloc_id`),
                ADD INDEX (`validation_cadre_bloc_datetime`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `protocole_operatoire_dhe` (
                `protocole_operatoire_dhe_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `protocole_operatoire_id` INT (11) UNSIGNED,
                `protocole_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole_operatoire_dhe` 
                ADD INDEX (`protocole_operatoire_id`),
                ADD INDEX (`protocole_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `materiel_operatoire` (
                `materiel_operatoire_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `protocole_operatoire_id` INT (11) UNSIGNED,
                `operation_id` INT (11) UNSIGNED,
                `dm_id` INT (11) UNSIGNED,
                `code_cip` INT (7) ZEROFILL,
                `bdm` VARCHAR (255),
                `qte_prevue` FLOAT UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `materiel_operatoire` 
                ADD INDEX (`protocole_operatoire_id`),
                ADD INDEX (`operation_id`),
                ADD INDEX (`dm_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `consommation_materiel` (
                `consommation_materiel_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `materiel_operatoire_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `qte_consommee` INT (11) DEFAULT '1',
                `lot_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `consommation_materiel` 
                ADD INDEX (`materiel_operatoire_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`user_id`),
                ADD INDEX (`lot_id`);";
        $this->addQuery($query);

        $this->makeRevision("3.00");

        $query = "ALTER TABLE `materiel_operatoire` 
                ADD `status` ENUM ('ok','ko'),
                ADD `status_user_id` INT (11) UNSIGNED,
                ADD `status_datetime` DATETIME,
                ADD INDEX (`status_user_id`),
                ADD INDEX (`status_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
                ADD `numero_panier` VARCHAR (50);";
        $this->addQuery($query);

        $this->makeRevision("3.01");
        $this->setModuleCategory("circuit_patient", "metier");

        $this->makeRevision("3.02");

        $query = "ALTER TABLE `operations`
                ADD `consommation_user_id` INT (11) UNSIGNED,
                ADD `consommation_datetime` DATETIME,
                ADD INDEX (`consommation_user_id`),
                ADD INDEX (`consommation_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `operations`
                DROP INDEX `rayons_x`,
                DROP INDEX `exec_tarif`;";
        $this->addQuery($query);

        $this->makeRevision('3.03');

        $query = "ALTER TABLE `sejour` 
                ADD `frais_sejour` FLOAT,
                ADD `reglement_frais_sejour` ENUM ('non_regle', 'cb', 'cheque', 'espece', 'virement') DEFAULT 'non_regle';";
        $this->addQuery($query);

        $this->makeRevision('3.04');

        $query = "ALTER TABLE `protocole_operatoire` 
                CHANGE `numero_version` `numero_version` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('3.05');

        $query = "ALTER TABLE `operations` 
                 ADD `datetime_lock_graph_perop` DATETIME AFTER `graph_pack_locked_user_id`,
                 ADD `datetime_lock_graph_sspi` DATETIME AFTER `graph_pack_sspi_locked_user_id`,
                 ADD `datetime_lock_graph_preop` DATETIME AFTER `graph_pack_preop_locked_user_id`;";
        $this->addQuery($query);

        $query = "UPDATE `operations`
              SET 
                  datetime_lock_graph_preop = DATE_ADD(entree_salle, INTERVAL 4 HOUR),
                  graph_pack_preop_locked_user_id = IFNULL(graph_pack_preop_locked_user_id, chir_id)
              WHERE entree_salle IS NOT NULL AND (entree_salle < NOW());";
        $this->addQuery($query);

        $query = "UPDATE `operations`
              SET 
                  datetime_lock_graph_perop = DATE_ADD(sortie_salle, INTERVAL 4 HOUR),
                  graph_pack_locked_user_id = IFNULL(graph_pack_locked_user_id, chir_id)
              WHERE sortie_salle IS NOT NULL AND (sortie_salle < NOW());";
        $this->addQuery($query);

        $query = "UPDATE `operations`
              SET 
                  datetime_lock_graph_sspi = DATE_ADD(sortie_reveil_reel, INTERVAL 4 HOUR),
                  graph_pack_sspi_locked_user_id = IFNULL(graph_pack_sspi_locked_user_id, chir_id)
              WHERE sortie_reveil_reel IS NOT NULL AND (sortie_reveil_reel < NOW());";
        $this->addQuery($query);

        $this->makeRevision("3.06");

        $query = "ALTER TABLE `operations`
                DROP INDEX `date_visite_anesth`,
                DROP INDEX `debut_prepa_preop`,
                DROP INDEX `fin_prepa_preop`,
                DROP INDEX `pose_garrot`,
                DROP INDEX `retrait_garrot`,
                DROP INDEX `prep_cutanee`,
                DROP INDEX `remise_chir`,
                DROP INDEX `tto`,
                DROP INDEX `induction_debut`,
                DROP INDEX `induction_fin`,
                DROP INDEX `incision`,
                DROP INDEX `suture_fin`,
                DROP INDEX `validation_timing`,
                DROP INDEX `debut_alr`,
                DROP INDEX `fin_alr`,
                DROP INDEX `debut_ag`,
                DROP INDEX `fin_ag`,
                DROP INDEX `cleaning_start`,
                DROP INDEX `cleaning_end`,
                DROP INDEX `installation_start`,
                DROP INDEX `installation_end`,
                DROP INDEX `ampli_rayons_x`,
                DROP INDEX `pec_anesth`,
                DROP INDEX `fin_pec_anesth`,
                DROP INDEX `preparation_op`,
                DROP INDEX `remise_anesth`,
                DROP INDEX `patient_stable`,
                DROP INDEX `consommation_datetime`;";
        $this->addQuery($query, true);

        $this->makeRevision("3.07");
        $query = "ALTER TABLE `sejour` 
                  CHANGE `transport_sortie`
                    `transport_sortie`
                        ENUM ('perso','perso_taxi','ambu','ambu_vsl','vsab','smur','heli','fo','pompes_funebres');";
        $this->addQuery($query);
        $this->makeRevision("3.08");

        $query = "ALTER TABLE `operations`
              DROP `position`;";
        $this->addQuery($query);

        $this->makeRevision("3.09");
        $this->addDefaultConfig(
            "dPplanningOp CSejour check_collisions",
            "dPplanningOp CSejour check_collisions",
            "date"
        );

        $this->makeRevision("3.10");

        $query = "ALTER TABLE `protocole_operatoire` 
                ADD `code` VARCHAR (32) AFTER `libelle`;";
        $this->addQuery($query);

        $this->makeRevision("3.11");
        $query = "ALTER TABLE `autorisation_permission`
            ADD `motif` TEXT AFTER `rques`;";
        $this->addQuery($query);

        $this->makeRevision('3.12');

        $query = "CREATE TABLE `ampli` (
                `ampli_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED,
                `actif` ENUM ('0','1') DEFAULT '1',
                `unite_rayons_x` ENUM ('mA','mGy','cGy.cm²','mGy.m²','mGy.cm²','Gy.cm²'),
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";

        $this->addQuery($query);

        $query = "CREATE TABLE `laboratoire_anapath` (
                    `laboratoire_anapath_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `group_id` INT (11) UNSIGNED NOT NULL,
                    `libelle` VARCHAR (255) NOT NULL,
                    `actif` ENUM ('0','1') DEFAULT '1',
                    `adresse` TEXT,
                    `cp` VARCHAR (10),
                    `ville` VARCHAR (50),
                    `tel` VARCHAR (20),
                    `fax` VARCHAR (20),
                    `mail` VARCHAR (50),
                    INDEX (`group_id`)
                  )/*! ENGINE=MyISAM */;";

        $this->addQuery($query);

        $query = "CREATE TABLE `laboratoire_bacterio` (
                    `laboratoire_bacterio_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    `group_id` INT (11) UNSIGNED NOT NULL,
                    `libelle` VARCHAR (255) NOT NULL,
                    `actif` ENUM ('0','1') DEFAULT '1',
                    `adresse` TEXT,
                    `cp` VARCHAR (10),
                    `ville` VARCHAR (50),
                    `tel` VARCHAR (20),
                    `fax` VARCHAR (20),
                    `mail` VARCHAR (50),
                    INDEX (`group_id`)
                  )/*! ENGINE=MyISAM */;";

        $this->addQuery($query);

        $query = 'ALTER TABLE `operations`
                    ADD `dose_recue_scopie` FLOAT AFTER `dose_rayons_x`,
                    ADD `dose_recue_graphie` FLOAT AFTER `unite_rayons_x`,
                    ADD `nombre_graphie` FLOAT AFTER `dose_recue_graphie`,
                    ADD `ampli_id` INT (11) UNSIGNED AFTER `ampli_rayons_x`,
                    ADD `labo_anapath_id` INT (11) UNSIGNED AFTER `labo_anapath`,
                    ADD `labo_bacterio_id` INT (11) UNSIGNED AFTER `labo_bacterio`,
                    ADD INDEX (`ampli_id`),
                    ADD INDEX (`labo_anapath_id`),
                    ADD INDEX (`labo_bacterio_id`);';

        $this->addQuery($query);

        $this->addMethod('migrateLabosAmplis');

        $this->makeRevision('3.13');

        $query = 'ALTER TABLE `operations`
                    DROP `ampli_rayons_x`,
                    DROP `labo_anapath`,
                    DROP `labo_bacterio`;';

        $this->addQuery($query);

        $this->makeRevision('3.14');

        $query = "ALTER TABLE `operations`
                    ADD `pds` FLOAT,
                    ADD `unite_pds` ENUM ('uGycm²','mGycm²') DEFAULT 'mGycm²',
                    ADD `kerma` FLOAT;";

        $this->addQuery($query);

        $this->makeRevision('3.15');

        $query = "ALTER TABLE `ampli`
                    ADD `unite_pds` ENUM ('uGycm²','mGycm²') DEFAULT 'mGycm²';";

        $this->addQuery($query);

        $this->makeRevision('3.16');
        $query = "ALTER TABLE `sejour`
                ADD `code_EDS` VARCHAR(255),
                ADD INDEX (`code_EDS`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole`
                ADD `code_EDS` VARCHAR(255),
                ADD INDEX (`code_EDS`);";
        $this->addQuery($query);

        $this->makeRevision('3.17');

        $this->addDefaultConfig('dPplanningOp CSejour fields_display accident', 'dPplanningOp CSejour assurances');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display assurances', 'dPplanningOp CSejour assurances');
        $this->addDefaultConfig(
          'dPplanningOp CSejour fields_display show_discipline_tarifaire', 'dPplanningOp CSejour show_discipline_tarifaire'
        );
        $this->addDefaultConfig('dPplanningOp CSejour fields_display fiche_rques_sej', 'dPplanningOp CSejour fiche_rques_sej');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display fiche_conval', 'dPplanningOp CSejour fiche_conval');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_cmu_ald', 'dPplanningOp CSejour show_cmu_ald');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_days_duree', 'dPplanningOp CSejour show_days_duree');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_isolement', 'dPplanningOp CSejour show_isolement');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_chambre_part', 'dPplanningOp CSejour show_chambre_part');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_facturable', 'dPplanningOp CSejour show_facturable');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_atnc', 'dPplanningOp CSejour show_atnc');
        $this->addDefaultConfig('dPplanningOp CSejour fields_display show_hospit_de_jour', 'dPplanningOp CSejour show_hospit_de_jour');
        $this->addDefaultConfig(
          'dPplanningOp CSejour fields_display show_type_pec',
          null,
          CAppUI::conf('dPplanningOp CSejour show_type_pec') ? 'show' : 'hidden'
        );

        $this->makeRevision('3.18');
        $query = "ALTER TABLE `mode_entree_sejour`
                ADD `provenance` ENUM ('1','2','3','4','5','6','7','8','R');";
        $this->addQuery($query);

        $this->makeRevision('3.19');

        $this->moveConfiguration('dPplanningOp CSejour fields_display show_cmu_ald', 'dPplanningOp CSejour fields_display show_c2s_ald');
        $this->moveConf('dPplanningOp CSejour easy_ald_cmu', 'dPplanningOp CSejour easy_ald_c2s');

        $this->makeRevision('3.20');

        $this->addQuery(
            'ALTER TABLE `sejour`
             ADD `adresse_par_exercice_place_id` INT (11) UNSIGNED AFTER `adresse_par_prat_id`,
             ADD INDEX (`adresse_par_exercice_place_id`);'
        );

        $this->makeRevision('3.21');
        $this->addQuery("ALTER TABLE `sejour`
        ADD `medecin_traitant_id` INT (11),
        ADD INDEX (`medecin_traitant_id`);");

        $this->makeRevision('3.22');

        $this->addQuery(
            "ALTER TABLE `operations` 
                ADD INDEX (`sortie_locker_id`),
                ADD INDEX (`graph_pack_sspi_locked_user_id`),
                ADD INDEX (`graph_pack_locked_user_id`);"
        );

        $this->makeRevision('3.23');

        $this->addQuery(
            "ALTER TABLE `ampli` 
                CHANGE `unite_pds` `unite_pds` ENUM ('uGycm²','mGycm²','Gycm²','cGycm²') DEFAULT 'mGycm²';"
        );

        $this->makeRevision('3.24');

        $this->addQuery(
            "ALTER TABLE `operations` 
                CHANGE `unite_pds` `unite_pds` ENUM ('uGycm²','mGycm²','Gycm²','cGycm²') DEFAULT 'mGycm²';"
        );

        $this->makeRevision('3.25');

        $this->addQuery(
            'ALTER TABLE `consommation_materiel`
             ADD `dm_sterilisation_id` INT (11),
             ADD INDEX (`dm_sterilisation_id`);'
        );

        $this->makeRevision('3.26');

        $this->addQuery(
            "ALTER TABLE `materiel_operatoire` 
                ADD `completude_panier` ENUM ('0','1') NOT NULL DEFAULT '1';"
        );

        $this->makeRevision('3.27');
        $this->addQuery(
            "ALTER TABLE `ampli` 
                CHANGE `unite_rayons_x` `unite_rayons_x` 
                    ENUM ('mA','mGy','cGy.cm_carre','mGy.m_carre','mGy.cm_carre','Gy.cm_carre') DEFAULT 'mA',
                CHANGE `unite_pds` `unite_pds` 
                    ENUM ('uGycm_carre','mGycm_carre','Gycm_carre','cGycm_carre') DEFAULT 'mGycm_carre';"
        );

        $this->makeRevision('3.28');
        $this->addQuery(
            "ALTER TABLE `operations` 
                CHANGE `unite_rayons_x` `unite_rayons_x` 
                    ENUM ('mA','mGy','cGy.cm_carre','mGy.m_carre','mGy.cm_carre','Gy.cm_carre') DEFAULT 'mA',
                CHANGE `unite_pds` `unite_pds` 
                    ENUM ('uGycm_carre','mGycm_carre','Gycm_carre','cGycm_carre') DEFAULT 'mGycm_carre';"
        );

        $this->makeRevision('3.29');

        $this->addFunctionalPermQuery('protocole_mandatory', 'config');

        $this->makeRevision('3.30');

        $this->addQuery('ALTER TABLE `sejour` ADD INDEX type_pec (`type_pec`);');

        $this->makeRevision('3.31');

        $this->addQuery("ALTER TABLE `sejour` ADD `bris_de_glace` ENUM ('0','1') DEFAULT '0';");

        $this->mod_version = '3.32';
    }
}
