<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;

/**
 * @codeCoverageIgnore
 */
class CSetupCcam extends CSetup
{
    /**
     * @return bool
     * @throws \Exception
     */
    protected function updateDateCodage(): bool
    {
        $ds = $this->ds;

        $query = "SELECT * FROM `codage_ccam`;";
        $rows  = $ds->exec($query);
        while ($_codage = $ds->fetchObject($rows, 'CCodageCCAM')) {
            $_codage->loadCodable();

            $date = null;
            switch ($_codage->codable_class) {
                case 'CConsultation':
                    $_codage->_ref_codable->loadRefPlageConsult();
                    $date = $_codage->_ref_codable->_date;
                    break;
                case 'COperation':
                    $date = $_codage->_ref_codable->date;
                    break;
                case 'CSejour':
                    $date = CMbDT::date('', $_codage->_ref_codable->entree);
                    break;
                default:
            }

            $query = "UPDATE `codage_ccam`
                  SET `date` = '$date' WHERE `codage_ccam_id` = $_codage->_id;";

            $ds->exec($query);
        }

        return true;
    }

    /**
     * Construct
     **/
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPccam";

        $this->makeRevision("0.0");
        $query = "CREATE TABLE `ccamfavoris` (
                `favoris_id` bigint(20) NOT NULL auto_increment,
                `favoris_user` int(11) NOT NULL default '0',
                `favoris_code` varchar(7) NOT NULL default '',
                PRIMARY KEY  (`favoris_id`)
              ) /*! ENGINE=MyISAM */ COMMENT='table des favoris'";
        $this->addQuery($query);

        $this->makeRevision("0.1");
        $query = "ALTER TABLE `ccamfavoris` 
                CHANGE `favoris_id` `favoris_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `favoris_user` `favoris_user` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "ALTER TABLE `ccamfavoris`
                ADD `object_class` VARCHAR(25) NOT NULL DEFAULT 'COperation';";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "ALTER TABLE `ccamfavoris` 
                ADD INDEX (`favoris_user`);";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "CREATE TABLE `frais_divers` (
                `frais_divers_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `type_id` INT (11) UNSIGNED NOT NULL,
                `coefficient` FLOAT NOT NULL DEFAULT '1',
                `quantite` INT (11) UNSIGNED,
                `facturable` ENUM ('0','1') NOT NULL DEFAULT '0',
                `montant_depassement` DECIMAL  (10,3),
                `montant_base` DECIMAL  (10,3),
                `executant_id` INT (11) UNSIGNED NOT NULL,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `object_class` VARCHAR (255) NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `frais_divers` 
                ADD INDEX (`type_id`),
                ADD INDEX (`executant_id`),
                ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `frais_divers_type` (
                `frais_divers_type_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code` VARCHAR (16) NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `tarif` DECIMAL (10,3) NOT NULL,
                `facturable` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $this->addPrefQuery("new_search_ccam", "1");

        $this->makeRevision("0.15");

        $query = "ALTER TABLE `frais_divers` 
                CHANGE `facturable` `facturable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.16");

        $this->addPrefQuery("multiple_select_ccam", "0");

        $this->makeRevision("0.17");

        $this->addPrefQuery("user_executant", "0");

        $this->makeRevision("0.18");
        $this->addDependency("dPcabinet", "0.1");
        $this->addDependency("dPplanningOp", "1.07");

        $query = "ALTER TABLE `frais_divers`
                ADD `execution` DATETIME NOT NULL;";

        $this->addQuery($query);

        $query = "UPDATE `frais_divers`
                INNER JOIN `consultation` ON (`frais_divers`.`object_id` = `consultation`.`consultation_id`)
                INNER JOIN `plageconsult` ON (`consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`)
                SET `frais_divers`.`execution` = CONCAT(`plageconsult`.`date`, ' ', `consultation`.`heure`)
                WHERE `frais_divers`.`object_class` = 'CConsultation';";
        $this->addQuery($query);

        $query = "UPDATE `frais_divers`
                INNER JOIN `operations` ON (`frais_divers`.`object_id` = `operations`.`operation_id`)
                INNER JOIN `plagesop` ON (`operations`.`plageop_id` = `plagesop`.`plageop_id`)
                SET `frais_divers`.`execution` = CONCAT(`plagesop`.`date`, ' ', `operations`.`time_operation`)
                WHERE `frais_divers`.`object_class` = 'COperation'
                AND `operations`.`date` IS NULL;";
        $this->addQuery($query);

        $query = "UPDATE `frais_divers`
                INNER JOIN `operations` ON (`frais_divers`.`object_id` = `operations`.`operation_id`)
                SET `frais_divers`.`execution` = CONCAT(`operations`.`date`, ' ', `operations`.`time_operation`)
                WHERE `frais_divers`.`object_class` = 'COperation'
                AND `operations`.`date` IS NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `frais_divers`
                INNER JOIN `sejour` ON (`frais_divers`.`object_id` = `sejour`.`sejour_id`)
                SET `frais_divers`.`execution` = `sejour`.`entree`
                WHERE `frais_divers`.`object_class` = 'CSejour';";
        $this->addQuery($query);
        $this->makeRevision("0.19");

        $query = "ALTER TABLE `frais_divers`
                ADD `num_facture` INT (11) UNSIGNED NOT NULL DEFAULT '1'";
        $this->addQuery($query);

        $query = "ALTER TABLE `frais_divers`
                ADD INDEX (`execution`),
                ADD INDEX (`object_class`);";
        $this->addQuery($query);

        $this->makeRevision("0.20");

        $this->addDependency("dPsalleOp", "0.1");

        $query = "ALTER TABLE `acte_ccam`
                ADD `position_dentaire` VARCHAR (255),
                ADD `numero_forfait_technique` INT (11) UNSIGNED,
                ADD `numero_agrement` BIGINT (20) UNSIGNED,
                ADD `rapport_exoneration` ENUM ('4','7','C','R');";
        $this->addQuery($query);

        $this->makeRevision('0.21');

        $query = "CREATE TABLE `codage_ccam` (
       `codage_ccam_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
       `association_rule` ENUM('G1', 'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG1', 'EG2',
       'EG3', 'EG4', 'EG5', 'EG6', 'EG7', 'EH', 'EI', 'GA', 'GB', 'G2'),
       `association_mode` ENUM('auto', 'user_choice') DEFAULT 'auto',
       `codable_class` ENUM('CConsultation', 'CSejour', 'COperation') NOT NULL,
       `codable_id` INT (11) UNSIGNED NOT NULL,
       `praticien_id` INT (11) UNSIGNED NOT NULL,
       `locked` ENUM('0', '1') NOT NULL DEFAULT '0'
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `codage_ccam`
      ADD INDEX (`codable_class`, `codable_id`),
      ADD INDEX (`praticien_id`),
      ADD UNIQUE INDEX  (`codable_class`, `codable_id`, `praticien_id`);";
        $this->addQuery($query);

        $this->makeRevision('0.22');

        $query = "ALTER TABLE `codage_ccam`
      ADD `nb_acts` INT (2) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('0.23');

        $query = "ALTER TABLE `codage_ccam`
      CHANGE `association_rule` `association_rule` ENUM('G1', 'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG1', 'EG2',
       'EG3', 'EG4', 'EG5', 'EG6', 'EG7', 'EH', 'EI', 'GA', 'GB', 'G2', 'M');";
        $this->addQuery($query);

        $this->makeRevision('0.24');

        $query = "ALTER TABLE  `codage_ccam`
      DROP `nb_acts`;";
        $this->addQuery($query);

        $this->makeRevision('0.25');

        $query = "ALTER TABLE `codage_ccam` DROP INDEX `codable_class_2`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `codage_ccam`
      ADD `activite_anesth` ENUM('0', '1') NOT NULL DEFAULT '0',
      ADD UNIQUE INDEX uk_codage_ccam (`codable_class`, `codable_id`, `praticien_id`, `activite_anesth`);";
        $this->addQuery($query);

        $this->makeRevision('0.26');

        $this->addPrefQuery('actes_comp_supp_favoris', '1');

        $query = "ALTER TABLE `acte_ccam`
                ADD `accord_prealable` ENUM ('0', '1') DEFAULT '0',
                ADD `date_demande_accord` DATE;";
        $this->addQuery($query);

        $this->makeRevision('0.27');

        $query = "ALTER TABLE `codage_ccam`
      ADD `date` DATE NOT NULL,
      DROP INDEX uk_codage_ccam;";
        $this->addQuery($query);

        $query = "ALTER TABLE `frais_divers`
                ADD `gratuit` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->addMethod('updateDateCodage');

        $this->makeRevision('0.28');

        $query = "CREATE TABLE `devis_codage` (
      `devis_codage_id` INT(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
      `codable_class` ENUM('CConsultation', 'COperation') NOT NULL,
      `codable_id` INT(11) UNSIGNED NOT NULL,
      `patient_id` INT(11) UNSIGNED NOT NULL,
      `praticien_id` INT(11) UNSIGNED NOT NULL,
      `creation_date` DATETIME NOT NULL,
      `date` DATE,
      `event_type` ENUM('CConsultation', 'COperation') DEFAULT 'CConsultation',
      `libelle` VARCHAR (255),
      `comment` TEXT,
      `codes_ccam` VARCHAR(255),
      `facture` ENUM ('0','1') DEFAULT '0',
      `tarif` VARCHAR(50),
      `exec_tarif` DATETIME,
      `consult_related_id` INT (11) UNSIGNED,
      `base` FLOAT(6),
      `dh` FLOAT(6),
      `ht` FLOAT(6),
      `tax_rate` FLOAT
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `devis_codage`
                ADD INDEX (`codable_class`),
                ADD INDEX (`codable_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `codage_ccam`
                CHANGE `codable_class` `codable_class` ENUM('CConsultation', 'CSejour', 'COperation', 'CDevisCodage') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('0.29');

        $this->addPrefQuery('precode_modificateur_7', '0');
        $this->addPrefQuery('precode_modificateur_J', '0');

        $this->makeRevision('0.30');

        $this->makeRevision('0.31');

        $this->addPrefQuery('spread_modifiers', 0);

        $this->makeRevision('0.32');

        $this->addDependency('dPsalleOp', '0.39');

        $query = "ALTER TABLE `acte_ccam`
                CHANGE `motif_depassement` `motif_depassement` ENUM ('d','e','f','n','da');";
        $this->addQuery($query);

        $this->makeRevision('0.33');

        $query = "CREATE TABLE `model_codage` (
      `model_codage_id` INT(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
      `praticien_id` INT(11) UNSIGNED NOT NULL,
      `date` DATE,
      `libelle` VARCHAR (255),
      `objects_guid` TEXT,
      `codes_ccam` VARCHAR(255),
      `facture` ENUM ('0','1') DEFAULT '0',
      `tarif` VARCHAR(50),
      `exec_tarif` DATETIME,
      `consult_related_id` INT (11) UNSIGNED
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `codage_ccam`
                CHANGE `codable_class` `codable_class` ENUM('CConsultation', 'CSejour', 'COperation', 'CDevisCodage', 'CModelCodage') NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision('0.34');

        $query = "ALTER TABLE `codage_ccam`
                CHANGE `codable_class` `codable_class` VARCHAR (80) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('0.35');

        $this->addDependency('dPsalleOp', '0.45');

        $query = "ALTER TABLE `acte_ccam`
                ADD `reponse_accord` ENUM('no_answer', 'accepted', 'emergency', 'refused'),
                CHANGE `exoneration` `exoneration` ENUM('N', '3', '4','5', '6', '7', '9') DEFAULT 'N',
                CHANGE `motif_depassement` `motif_depassement` ENUM('d', 'e', 'f', 'g', 'n', 'da', 'm', 'b', 'c', 'l');";
        $this->addQuery($query);
        $this->makeRevision('0.36');

        $query = "ALTER TABLE `ccamfavoris`
                ADD `rang` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('0.37');

        $query = "ALTER TABLE `acte_ccam`
                ADD `code_extension` VARCHAR (2) AFTER `code_activite`;";
        $this->addQuery($query);

        $this->makeRevision('0.38');

        $query = "ALTER TABLE `model_codage` 
                ADD `anesth_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('0.39');

        $query = "ALTER TABLE `ccamfavoris`
                CHANGE `favoris_user` `favoris_user` INT(11) UNSIGNED,
                ADD `favoris_function` INT(11) UNSIGNED AFTER `favoris_user`;";
        $this->addQuery($query);

        $this->makeRevision('0.40');

        $this->addDefaultConfig("dPccam codage export_on_codage_lock", "dPccam CCodable export_on_codage_lock");

        $this->makeRevision('0.41');

        $this->addPrefQuery('default_qualif_depense', '');

        $this->makeRevision('0.42');

        $query = "ALTER TABLE `devis_codage` CHANGE `event_type` `event_type` ENUM('CConsultation', 'COperation') DEFAULT 'COperation';";
        $this->addQuery($query);

        $this->makeRevision('0.43');

        $query = "ALTER TABLE `acte_ccam` ADD `coding_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision('0.44');

        $this->makeEmptyRevision('0.45');

        $query = "ALTER TABLE `codage_ccam` ADD `date_unlock` DATETIME, ADD INDEX (`date_unlock`);";
        $this->addQuery($query);

        $this->makeRevision('0.46');

        $query = "ALTER TABLE `acte_ccam`
                CHANGE `motif_depassement` `motif_depassement` ENUM('d', 'e', 'f', 'g', 'n', 'da', 'm', 'b', 'c', 'l', 'a');";
        $this->addQuery($query);

        $query = "UPDATE `acte_ccam` SET `motif_depassement` = 'a' WHERE `motif_depassement` = 'da';";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_ccam`
                CHANGE `motif_depassement` `motif_depassement` ENUM('d', 'e', 'f', 'g', 'n', 'a', 'b', 'l');";
        $this->addQuery($query);

        $this->addDefaultConfig("dPccam codage use_cotation_ccam", "dPccam CCodeCCAM use_cotation_ccam");
        $this->addDefaultConfig("dPccam codage use_getMaxCodagesActes", "dPccam CCodable use_getMaxCodagesActes");
        $this->addDefaultConfig("dPccam codage add_acte_comp_anesth_auto", "dPccam CCodable add_acte_comp_anesth_auto");
        $this->addDefaultConfig(
            "dPccam frais_divers use_frais_divers_CConsultation",
            "dPccam CCodable use_frais_divers CConsultation"
        );
        $this->addDefaultConfig(
            "dPccam frais_divers use_frais_divers_COperation",
            "dPccam CCodable use_frais_divers COperation"
        );
        $this->addDefaultConfig(
            "dPccam frais_divers use_frais_divers_CSejour",
            "dPccam CCodable use_frais_divers CSejour"
        );
        $this->addDefaultConfig(
            "dPccam frais_divers use_frais_divers_CEvenementPatient",
            "dPccam CCodable use_frais_divers CEvenementPatient"
        );
        $this->addDefaultConfig("dPccam codage lock_codage_ccam", "dPccam lock_codage_ccam");

        $this->makeRevision('0.47');

        $query = "CREATE TABLE `billing_period` (
                `billing_period_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `codable_class` VARCHAR (80) NOT NULL,
                `codable_id` INT (11) UNSIGNED NOT NULL,
                `period_start` DATE,
                `period_end` DATE,
                `period_statement` ENUM ('0','1','2') NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `billing_period` 
                ADD INDEX codable (codable_class, codable_id),
                ADD INDEX (`period_start`),
                ADD INDEX (`period_end`);";
        $this->addQuery($query);

        $this->makeRevision('0.48');

        /* Suppression des doublons avant de créer la contrainte unique */
        $query = "CREATE TEMPORARY TABLE `codage_ccam_tmp` AS
      SELECT `codage_ccam_id` FROM `codage_ccam` GROUP BY `codable_class`, `codable_id`, `praticien_id`, `activite_anesth`, `date`
      HAVING COUNT(*) > 1 ORDER BY `codage_ccam_id` DESC";
        $this->addQuery($query);

        $query = "DELETE FROM `codage_ccam` WHERE `codage_ccam_id` IN (SELECT * FROM `codage_ccam_tmp`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `codage_ccam`
        ADD UNIQUE INDEX codable_praticien (`codable_class`, `codable_id`, `praticien_id`, `activite_anesth`, `date`);";
        $this->addQuery($query);

        $this->makeRevision('0.49');

        $query = "ALTER TABLE `acte_ccam` ADD `facturable_auto` ENUM ('0', '1') DEFAULT '1' AFTER `facturable`;";
        $this->addQuery($query);

        $query = "UPDATE `acte_ccam` SET `facturable_auto` = '0';";
        $this->addQuery($query);

        $this->makeEmptyRevision('0.50');

        /*$query = "ALTER TABLE `codage_ccam`
                    ADD INDEX codable (codable_class, codable_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `devis_codage`
                    ADD INDEX codable (codable_class, codable_id),
                    ADD INDEX (`patient_id`),
                    ADD INDEX (`praticien_id`),
                    ADD INDEX (`creation_date`),
                    ADD INDEX (`date`),
                    ADD INDEX (`exec_tarif`),
                    ADD INDEX (`consult_related_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `ccamfavoris`
                    ADD INDEX (`favoris_function`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `frais_divers`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `frais_divers_type`
                    ADD INDEX (`code`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `model_codage`
                    ADD INDEX (`praticien_id`),
                    ADD INDEX (`anesth_id`),
                    ADD INDEX (`date`),
                    ADD INDEX (`exec_tarif`),
                    ADD INDEX (`consult_related_id`);";
        $this->addQuery($query);*/

        $this->makeEmptyRevision('0.51');

        /*$query = "ALTER TABLE `frais_divers`
                    DROP INDEX object_id;";
        $this->addQuery($query);*/

        $this->makeEmptyRevision('0.52');

        $this->addPrefQuery('preselected_filters_ngap_sejours', 'CMediusers');

        $this->makeRevision("0.53");
        $this->setModuleCategory("referentiel", "referentiel");

        $this->makeRevision("0.54");

        $query = "ALTER TABLE `codage_ccam` DROP INDEX `codable_praticien`;";
        $this->addQuery($query, true);

        $query = "ALTER TABLE `codage_ccam`
        ADD INDEX codable_praticien (`codable_class`, `codable_id`, `praticien_id`, `activite_anesth`, `date`);";
        $this->addQuery($query, true);

        $this->makeRevision('0.55');

        $this->addPrefQuery('use_ccam_acts', '1');

        $this->makeRevision('0.56');

        $this->moveConfiguration('dPccam codage display_ald_cmu', 'dPccam codage display_ald_c2s');

        $this->makeRevision('0.57');
        $this->addPrefQuery('enabled_majoration_F', '1');

        $this->mod_version = '0.58';

        // Data source query

        // Check the CCAM version
        $this->addDatasource("ccamV2", $this->makeLastCcamDBVersionRequest(CCCAM::getDatabaseVersions()));

        // Forfaits SRC
        if (array_key_exists('ccamV2', CAppUI::conf('db'))) {
            $dsn = CSQLDataSource::get('ccamV2', true);

            if (!$dsn->hasField('p_activite_classif', 'SPECIALITE1')) {
                $this->addDatasource(
                    'ccamV2',
                    "-- CCAM: Ajout des spécialités pouvant exécuter un acte
                    SHOW COLUMNS FROM `p_activite_classif` LIKE 'SPECIALITE1';"
                );
            }

            /* Tarifs NGAP : Acte VL pour les spécialités 22 et 23, Ajout de l'acte TMI pour les Infirmiers */
            $dsn = CSQLDataSource::get('ccamV2');
            if ($dsn->fetchRow($dsn->exec('SHOW TABLES LIKE \'tarif_ngap\';'))) {
                $query = "-- NGAP : Acte VL pour les spécialités 22 et 23, Ajout de l'acte TMI pour les Infirmiers --
          SELECT * FROM `tarif_ngap` 
          WHERE `code` = 'TMI';";
                $this->addDatasource("ccamV2", $query);
            }
        }
    }

    /**
     * To check if the CCAM DB is up to date, a select sql query is made.
     * If a result is returned, the CCAM is up to date
     *
     * @param array $versions
     *
     * @return string
     * @throws CMbException
     */
    private function makeLastCcamDBVersionRequest(array $versions): string
    {
        $versions_keys = array_keys($versions);
        $version_number = end($versions_keys);
        $last_version = end($versions);

        if (!$last_version || !isset($last_version[0]) || !is_array($last_version)) {
            throw new CMbException("Expected an array");
        }

        $last_version = $last_version[0];

        $request = new CRequest();

        $request->addTable($last_version["table_name"]);
        $request->addColumn("*");

        if (isset($last_version["filters"])) {
            foreach ($last_version["filters"] as $_filter_name => $_filter_value) {
                $request->addWhereClause($_filter_name, $_filter_value);
            }
        }

        if (isset($last_version["ljoin"])) {
            foreach ($last_version["ljoin"] as $_ljoin_table => $_ljoin_req) {
                $request->addLJoinClause($_ljoin_table, $_ljoin_req);
            }
        }

        $comment = "-- CCAM v{$version_number} --\n";

        return $comment . $request->makeSelect();
    }
}
