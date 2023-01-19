<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Core\Security\Crypt\Alg;
use Ox\Core\Security\Crypt\Mode;
use Ox\Mediboard\CompteRendu\CSetupCompteRendu;
use Ox\Mediboard\Patients\Updators\IdentityProofTypeUpdator;

/**
 * @codeCoverageIgnore
 */
class CSetupPatients extends CSetup
{
    /**
     * Update soundex data
     *
     * @return bool
     */
    protected function createSoundex()
    {
        $where   = ["nom_soundex2" => "IS NULL", "nom" => "!= ''"];
        $limit   = "0,1000";
        $pat     = new CPatient();
        $listPat = $pat->loadList($where, null, $limit);

        if (!is_countable($listPat)) {
            return true;
        }

        while (count($listPat)) {
            foreach ($listPat as &$pat) {
                if ($msg = $pat->store()) {
                    trigger_error("Erreur store [$pat->_id] : $msg");

                    return false;
                }
            }
            $listPat = $pat->loadList($where, null, $limit);
        }

        return true;
    }

    /**
     * Check if the communes exists in the INSEE database
     *
     * @return void
     */
    protected function checkCommunes()
    {
        $query = "SELECT * FROM `communes_france` WHERE `code_postal` = 71000 AND `commune` = 'Macon';";
        $this->addDatasource("INSEE", $query);

        $query = "SELECT * FROM `communes_france` WHERE `code_postal` = 01300 AND `commune` = 'Arboys en Bugey';";
        $this->addDatasource("INSEE", $query);

        $query = "SELECT * FROM `communes_france` WHERE `code_postal` = 50130 AND `commune` = 'Cherbourg-en-Cotentin';";
        $this->addDatasource("INSEE", $query);
    }

    /**
     * Add constantes ranks
     *
     * @return bool
     */
    protected function addConstantesRank()
    {
        $ds = $this->ds;

        $results = $ds->exec(
            "SELECT * FROM `configuration` WHERE `feature` = 'dPpatients CConstantesMedicales important_constantes';"
        );

        $list = [];
        foreach (CConstantesMedicales::$list_constantes as $_const => $_params) {
            if (!isset($_params["cumul_for"])) {
                $list[] = $_const;
            }
        }

        if ($results) {
            while ($row = $ds->fetchAssoc($results)) {
                if ($row["value"] == "") {
                    continue;
                }

                $constants = explode("|", $row["value"]);

                $object_class = "NULL";
                $object_id    = "NULL";

                if ($row["object_class"]) {
                    $object_class = "'" . $row["object_class"] . "'";
                }

                if ($row["object_id"]) {
                    $object_id = "'" . $row["object_id"] . "'";
                }

                // Ajout des constantes préselectionnées
                foreach ($constants as $_key => $_name) {
                    $rank = $_key + 1;

                    $query = "INSERT INTO `configuration` (`feature`, `value`, `object_id`, `object_class`)
                         VALUES (?1, ?2, $object_id, $object_class)";
                    $query = $ds->prepare($query, "dPpatients CConstantesMedicales selection $_name", $rank);
                    $ds->exec($query);
                }

                // Valeur zéro pour les autres
                foreach ($list as $_name) {
                    if (in_array($_name, $constants)) {
                        continue;
                    }

                    $query = "INSERT INTO `configuration` (`feature`, `value`, `object_id`, `object_class`)
                         VALUES (?1, '0', $object_id, $object_class)";
                    $query = $ds->prepare($query, "dPpatients CConstantesMedicales selection $_name");
                    $ds->exec($query);
                }
            }
        }

        return true;
    }

    /**
     * Update rank configs
     *
     * @return bool
     */
    protected function modifyConstantsRanksConfigs()
    {
        $ds = $this->ds;

        $old_configs = $ds->exec(
            "SELECT * FROM `configuration` WHERE `feature` LIKE 'dPpatients CConstantesMedicales selection%';"
        );

        if ($old_configs) {
            while ($row = $ds->fetchAssoc($old_configs)) {
                $query = "UPDATE `configuration` SET `value` = ?1 WHERE `configuration_id` = ?2";
                $query = $ds->prepare(
                    $query,
                    $row['value'] . '|' . $row['value'] . '|',
                    $row['configuration_id']
                );
                $ds->exec($query);
            }
        }

        return true;
    }

    /**
     * Move configs from 'config_constantes_medicales' to 'configuration'
     *
     * @return bool
     */
    protected function moveConstantesConfigsFromOldTable()
    {
        $ds      = $this->ds;
        $fields  = [
            'show_cat_tabs',
            'show_enable_all_button',

            'diuere_24_reset_hour',
            'redon_cumul_reset_hour',
            'sng_cumul_reset_hour',
            'lame_cumul_reset_hour',
            'drain_cumul_reset_hour',
            'drain_thoracique_cumul_reset_hour',
            'drain_pleural_cumul_reset_hour',
            'drain_mediastinal_cumul_reset_hour',
            'sonde_ureterale_cumul_reset_hour',
            'sonde_nephro_cumul_reset_hour',
            'sonde_vesicale_cumul_reset_hour',

            'important_constantes',
        ];
        $configs = $ds->loadList("SELECT * FROM `config_constantes_medicales`");
        foreach ($configs as $_config) {
            foreach ($fields as $_field) {
                $object_class = null;
                if ($object_id = $_config["service_id"]) {
                    $object_class = "CService";
                } elseif ($object_id = $_config["group_id"]) {
                    $object_class = "CGroups";
                }

                if (!$object_class) {
                    $object_class = "NULL";
                } else {
                    $object_class = "'$object_class'";
                }

                if (!$object_id) {
                    $object_id = "NULL";
                } else {
                    $object_id = "'$object_id'";
                }

                if ($_config[$_field] !== null) {
                    $query = "INSERT INTO `configuration` (`object_class`, `object_id`, `feature`, `value`)
                    VALUES ($object_class, $object_id, ?1, ?2)";
                    $query = $ds->prepare($query, "dPpatients CConstantesMedicales $_field", $_config[$_field]);
                    $ds->exec($query);
                }
            }
        }

        return true;
    }

    /**
     * Update the death date to a datetime
     *
     * @return bool
     */
    function updateDeathDate()
    {
        $ds = $this->ds;

        $query    = "SELECT `patient_id`, `deces` FROM `patients` WHERE `deces` IS NOT NULL;";
        $patients = $ds->loadList($query);

        $query = "ALTER TABLE `patients`
                CHANGE `deces` `deces` DATETIME;";
        $ds->exec($query);

        $query = "UPDATE `patients` SET `deces`=REPLACE(`deces`, '-00', '-01') WHERE `deces` IS NOT NULL;";
        $ds->exec($query);

        $now    = CMbDT::dateTime();
        $insert = [];
        foreach ($patients as $_patient) {
            $patient_id  = $_patient["patient_id"];
            $deces       = $_patient["deces"];
            $deces_after = CMbDT::dateToLocale(str_replace("-00", "-01", $deces));
            $deces       = CMbDT::dateToLocale($deces);
            $insert[]    = [
                "object_id"    => $patient_id,
                "object_class" => "CPatient",
                "date"         => $now,
                "libelle"      => "Date décès",
                "text"         => "Changement automatique de $deces à $deces_after 00:00:00",
            ];
        }

        if (!empty($insert)) {
            $ds->insertMulti("note", $insert, 100);
        }

        return true;
    }

    /**
     * Add indexes if they do not exists
     *
     * @return bool
     */
    function addIndexAdeliRppsIfNotExists()
    {
        $query_check_index = "SHOW INDEX FROM `medecin` WHERE Key_name = 'adeli'";
        $index_exists      = $this->ds->countRows($query_check_index);
        if ($index_exists == 0) {
            $query = "ALTER TABLE `medecin`
                ADD INDEX adeli (`adeli`);";
            $this->ds->exec($query);
        }

        $query_check_index = "SHOW INDEX FROM `medecin` WHERE Key_name = 'rpps'";
        $index_exists      = $this->ds->countRows($query_check_index);
        if ($index_exists == 0) {
            $query = "ALTER TABLE `medecin`
                ADD INDEX rpps (`rpps`);";
            $this->ds->exec($query);
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function setPhoneAreaCodeConfig(): bool
    {
        switch (CAppUI::conf('ref_pays')) {
            case 2:
                $default_phone_area_code = 41;
                break;

            case 3:
                $default_phone_area_code = 32;
                break;

            default:
                $default_phone_area_code = 33;
        }

        CAppUI::setConf('system phone_area_code', $default_phone_area_code);

        return true;
    }

    protected function initObversationValuesToConstantTable(): bool
    {
        $ds = CSQLDataSource::get('std');
        if (!$ds->hasTable('observation_values_to_constant')) {
            $query = 'CREATE TABLE `observation_values_to_constant` (
            `observation_value_to_constant_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
            `value_type_id` INT (11) UNSIGNED NOT NULL,
            `value_unit_id` INT (11) UNSIGNED NOT NULL,
            `constant_name` VARCHAR (255) NOT NULL,
            `conversion_ratio` FLOAT DEFAULT 1.0,
            `conversion_operation` ENUM ("*", "/") DEFAULT "*"
          )/*! ENGINE=MyISAM */;';
            $this->ds->exec($query);

            $query = 'ALTER TABLE `observation_values_to_constant` 
          ADD INDEX (`value_type_id`),
          ADD INDEX (`value_unit_id`);';
            $this->ds->exec($query);
        }
        
        return true;
    }
    
    protected function initObversationResultTables(): bool
    {
        $ds = CSQLDataSource::get('std');
        if (!$ds->hasTable('observation_result_set')) {
            $query = "CREATE TABLE `observation_result_set` (
                  `observation_result_set_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                  `patient_id` INT (11) UNSIGNED NOT NULL,
                  `datetime` DATETIME NOT NULL,
                  `context_class` CHAR (80) NOT NULL,
                  `context_id` INT (11) UNSIGNED
                  ) /*! ENGINE=MyISAM */;";
            $this->ds->exec($query);

            $query = "ALTER TABLE `observation_result_set`
                  ADD INDEX (`patient_id`),
                  ADD INDEX (`datetime`),
                  ADD INDEX (`context_id`);";
            $this->ds->exec($query);
        }

        if (!$ds->hasTable('observation_result')) {
            $query = "CREATE TABLE `observation_result` (
          `observation_result_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
          `observation_result_set_id` INT (11) UNSIGNED NOT NULL,
          `value_type_id` INT (11) UNSIGNED NOT NULL,
          `unit_id` INT (11) UNSIGNED NOT NULL,
          `value` CHAR (255) NOT NULL
          ) /*! ENGINE=MyISAM */;";
            $this->ds->exec($query);
            $query = "ALTER TABLE `observation_result`
          ADD INDEX (`observation_result_set_id`),
          ADD INDEX (`value_type_id`),
          ADD INDEX (`unit_id`);";
            $this->ds->exec($query);
        }

        if (!$ds->hasTable('observation_value_type')) {
            $query = "CREATE TABLE `observation_value_type` (
              `observation_value_type_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `datatype` ENUM ('NM','ST','TX') NOT NULL,
              `code` CHAR (40) NOT NULL,
              `label` CHAR (255),
              `coding_system` CHAR (40) NOT NULL
              ) /*! ENGINE=MyISAM */;";
            $this->ds->exec($query);
            $query = "ALTER TABLE `observation_value_type`
              ADD INDEX (`datatype`),
              ADD INDEX (`code`),
              ADD INDEX (`coding_system`);";
            $this->ds->exec($query);
        }

        if (!$ds->hasTable('observation_value_unit')) {
            $query = "CREATE TABLE `observation_value_unit` (
              `observation_value_unit_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `code` CHAR (40) NOT NULL,
              `label` CHAR (255),
              `coding_system` CHAR (40) NOT NULL
              ) /*! ENGINE=MyISAM */;";
            $this->ds->exec($query);
            $query = "ALTER TABLE `observation_value_unit`
              ADD INDEX (`code`),
              ADD INDEX (`coding_system`);";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase1(): bool
    {
        if (!$this->columnExists('observation_result', 'method')) {
            $query = "ALTER TABLE `observation_result`
              ADD `method` VARCHAR (255)";
            $this->ds->exec($query);
            $query = "ALTER TABLE `observation_result`
              ADD INDEX (`method`)";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase2(): bool
    {
        if (!$this->columnExists('observation_result', 'status')) {
            $query = "ALTER TABLE `observation_result`
                ADD `status` ENUM ('C','D','F','I','N','O','P','R','S','U','W','X') DEFAULT 'F';";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase3(): bool
    {
        if ($this->columnExists('observation_result', 'value')) {
            $query = "ALTER TABLE `observation_result`
              CHANGE `value` `value` VARCHAR (255) NOT NULL;";
            $this->ds->exec($query);
        }

        if (!$this->columnExists('observation_result_set', 'context_class')) {
            $query = "ALTER TABLE `observation_result_set`
              CHANGE `context_class` `context_class` VARCHAR (80) NOT NULL;";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase4(): bool
    {
        if (!$this->columnExists('observation_result', 'file_id')) {
            $query = "ALTER TABLE `observation_result`
                ADD `file_id` INT (11) UNSIGNED;";
            $this->ds->exec($query);
        }

        if ($this->columnExists('observation_result', 'unit_id')) {
            $query = "ALTER TABLE `observation_result`
                CHANGE `unit_id` `unit_id` INT (11) UNSIGNED;";
            $this->ds->exec($query);
        }

        if ($this->columnExists('observation_value_type', 'datatype')) {
            $query = "ALTER TABLE `observation_value_type`
                CHANGE `datatype` `datatype` ENUM ('NM','ST','TX','FILE') NOT NULL;";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase5(): bool
    {
        if (!$this->columnExists('observation_result', 'label_id')) {
            $query = "ALTER TABLE `observation_result`
                ADD `label_id` INT (11) UNSIGNED;";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase6(): bool
    {
        if (!$this->columnExists('observation_result_set', 'sender_id')) {
            $query = "ALTER TABLE `observation_result_set` 
                ADD `sender_id` INT (11) UNSIGNED;
              ALTER TABLE `observation_result_set` 
                ADD INDEX (`sender_id`);";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationResultTablePhase7(): bool
    {
        if (!$this->columnExists('observation_result_set', 'sender_class')) {
            $query = "ALTER TABLE `observation_result_set` 
                ADD `sender_class` VARCHAR (255);
              ALTER TABLE `observation_result_set` 
                ADD INDEX sender (sender_class, sender_id);";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationValueTablesPhase1(): bool
    {
        if (!$this->columnExists('observation_value_type', 'desc')) {
            $query = "ALTER TABLE `observation_value_type`
              CHANGE `code` `code` VARCHAR (40) NOT NULL,
              CHANGE `label` `label` VARCHAR (255) NOT NULL,
              CHANGE `coding_system` `coding_system` VARCHAR (40) NOT NULL,
              ADD `desc` VARCHAR (255);";
            $this->ds->exec($query);

            // Ajout des unités et paramètres standards dans les nouvelles constantes
            $mdil_params = [
                "0002-4182" => ["HR", "Rythme cardiaque"],
                "0002-4b60" => ["Tcore", "Température corporelle"],
                "0002-4bb8" => ["SpO2", "SpO2"],
                "0002-5900" => ["HC", "Périmètre cranien"],
                "0002-f093" => ["Weight", "Poids"],
                "0002-f094" => ["Height", "Taille"],
                "0401-0adc" => ["Crea", "Créatinine"],
                "0401-0bc8" => ["Age", "Age"],
            ];
            $values      = [];
            foreach ($mdil_params as $_code => $_labels) {
                [$_label, $_desc] = $_labels;
                $values[] = "('MDIL', '$_code', '$_label', '$_desc', 'NM')";
            }
            $query = "INSERT INTO `observation_value_type` (`coding_system`, `code`, `label`, `desc`, `datatype`) VALUES " .
                implode("\n, ", $values);
            $this->ds->exec($query);
        }

        if (!$this->columnExists('observation_value_unit', 'desc')) {
            $query = "ALTER TABLE `observation_value_unit`
              CHANGE `code` `code` VARCHAR (40) NOT NULL,
              CHANGE `label` `label` VARCHAR (255) NOT NULL,
              CHANGE `coding_system` `coding_system` VARCHAR (40) NOT NULL,
              ADD `desc` VARCHAR (255);";
            $this->ds->exec($query);

            $mdil_units = [
                "0004-0220" => ["%", "%"],
                "0004-0500" => ["m", "m"],
                "0004-0511" => ["cm", "cm"],
                "0004-0512" => ["mm", "mm"],
                "0004-0652" => ["ml", "ml"],
                "0004-06c3" => ["kg", "kg"],
                "0004-0aa0" => ["bpm", "bpm"],
                "0004-0ae0" => ["rpm", "rpm"],
                "0004-0f20" => ["mmHg", "mmHg"],
            ];
            $values     = [];
            foreach ($mdil_units as $_code => $_labels) {
                [$_label, $_desc] = $_labels;
                $values[] = "('MDIL', '$_code', '$_label', '$_desc')";
            }
            $query = "INSERT INTO `observation_value_unit` (`coding_system`, `code`, `label`, `desc`) VALUES " . implode(
                    "\n, ",
                    $values
                );
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationValueTablesPhase2(): bool
    {
        if (!$this->columnExists('observation_value_type', 'group_id')) {
            $query = "ALTER TABLE `observation_value_type`
                ADD `group_id` INT (11) UNSIGNED,
                ADD INDEX (`group_id`);";
            $this->ds->exec($query);
        }

        if (!$this->columnExists('observation_value_unit', 'group_id')) {
            $query = "ALTER TABLE `observation_value_unit` 
                ADD `group_id` INT (11) UNSIGNED,
                ADD INDEX (`group_id`);";
            $this->ds->exec($query);
        }

        return true;
    }

    protected function alterObversationValueTablesPhase3(): bool
    {
        if (!$this->columnExists('observation_value_unit', 'display_text')) {
            $query = "ALTER TABLE `observation_value_unit` ADD `display_text` VARCHAR (255);";
            $this->ds->exec($query);
        }

        return true;
    }

    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPpatients";

        $this->makeRevision("0.0");
        $query = "CREATE TABLE `patients` (
                `patient_id` INT(11) NOT NULL AUTO_INCREMENT,
                `nom` VARCHAR(50) NOT NULL DEFAULT '',
                `prenom` VARCHAR(50) NOT NULL DEFAULT '',
                `naissance` DATE NOT NULL DEFAULT '0000-00-00',
                `sexe` ENUM('m','f') NOT NULL DEFAULT 'm',
                `adresse` VARCHAR(50) NOT NULL DEFAULT '',
                `ville` VARCHAR(50) NOT NULL DEFAULT '',
                `cp` VARCHAR(5) NOT NULL DEFAULT '',
                `tel` VARCHAR(10) NOT NULL DEFAULT '',
                `medecin_traitant` INT(11) NOT NULL DEFAULT '0',
                `incapable_majeur` ENUM('o','n') NOT NULL DEFAULT 'n',
                `ATNC` ENUM('o','n') NOT NULL DEFAULT 'n',
                `matricule` VARCHAR(15) NOT NULL DEFAULT '',
                `SHS` VARCHAR(10) NOT NULL DEFAULT '',
                PRIMARY KEY  (`patient_id`),
                UNIQUE KEY `patient_id` (`patient_id`),
                KEY `matricule` (`matricule`,`SHS`),
                KEY `nom` (`nom`,`prenom`)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.1");
        $query = "ALTER TABLE patients
                ADD tel2 VARCHAR( 10 ) AFTER tel ,
                ADD medecin1 INT( 11 ) AFTER medecin_traitant ,
                ADD medecin2 INT( 11 ) AFTER medecin1 ,
                ADD medecin3 INT( 11 ) AFTER medecin2 ,
                ADD rques TEXT;";
        $this->addQuery($query);
        $query = "CREATE TABLE medecin (
                medecin_id INT(11) NOT NULL AUTO_INCREMENT,
                nom VARCHAR(50) NOT NULL DEFAULT '',
                prenom VARCHAR(50) NOT NULL DEFAULT '',
                tel VARCHAR(10) DEFAULT NULL,
                fax VARCHAR(10) DEFAULT NULL,
                email VARCHAR(50) DEFAULT NULL,
                adresse VARCHAR(50) DEFAULT NULL,
                ville VARCHAR(50) DEFAULT NULL,
                cp VARCHAR(5) DEFAULT NULL,
                PRIMARY KEY  (`medecin_id`)
              )/*! ENGINE=MyISAM */ COMMENT='Table des medecins correspondants';";
        $this->addQuery($query);

        $this->makeRevision("0.2");
        $query = "ALTER TABLE medecin
                ADD specialite TEXT AFTER prenom ;";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE medecin
                ADD disciplines TEXT AFTER prenom ;";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `medecin`
                CHANGE `adresse` `adresse` TEXT DEFAULT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $query = "ALTER TABLE `medecin`
                ADD INDEX ( `nom` ),
                ADD INDEX ( `prenom` ),
                ADD INDEX ( `cp` ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.24");
        $query = "ALTER TABLE `patients`
                ADD `nom_jeune_fille` VARCHAR( 50 ) NOT NULL AFTER `nom` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                CHANGE `sexe` `sexe` ENUM( 'm', 'f', 'j' ) DEFAULT 'm' NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "ALTER TABLE `patients` CHANGE `adresse` `adresse` TEXT NOT NULL ";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $query = "CREATE TABLE `antecedent` (
                `antecedent_id` BIGINT NOT NULL AUTO_INCREMENT ,
                `patient_id` BIGINT NOT NULL ,
                `type` ENUM( 'trans', 'obst', 'chir', 'med' ) DEFAULT 'med' NOT NULL ,
                `date` DATE,
                `rques` TEXT,
                PRIMARY KEY ( `antecedent_id` ) ,
                INDEX ( `patient_id` )
              ) /*! ENGINE=MyISAM */ COMMENT = 'antecedents des patients';";
        $this->addQuery($query);

        $this->makeRevision("0.27");
        $query = "ALTER TABLE `antecedent`
                CHANGE `type` `type` ENUM( 'trans', 'obst', 'chir', 'med', 'fam' ) DEFAULT 'med' NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                ADD `listCim10` TEXT DEFAULT NULL ;";
        $this->addQuery($query);
        $query = "CREATE TABLE `traitement` (
                `traitement_id` BIGINT NOT NULL AUTO_INCREMENT ,
                `patient_id` BIGINT NOT NULL ,
                `debut` DATE DEFAULT '0000-00-00' NOT NULL ,
                `fin` DATE,
                `traitement` TEXT,
                PRIMARY KEY ( `traitement_id` ) ,
                INDEX ( `patient_id` )
              ) /*! ENGINE=MyISAM */ COMMENT = 'traitements des patients';";
        $this->addQuery($query);

        $this->makeRevision("0.28");
        $query = "ALTER TABLE `patients`
                CHANGE `SHS` `regime_sante` VARCHAR( 40 );";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                ADD `SHS` VARCHAR( 8 ) AFTER `matricule`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                ADD INDEX ( `SHS` );";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `patients` DROP INDEX `patient_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` DROP INDEX `nom` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `nom` ) ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `prenom` ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `antecedent` CHANGE `type` `type`
                ENUM( 'trans', 'obst', 'chir', 'med', 'fam', 'alle' ) NOT NULL DEFAULT 'med';";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `patients` ADD `cmu` date NULL AFTER `matricule` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD `ald` text AFTER `rques` ;";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "UPDATE `medecin` SET `tel` = NULL WHERE `tel`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `fax` = NULL WHERE `fax`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `email` = NULL WHERE `email`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `specialite` = NULL WHERE `specialite`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `disciplines` = NULL WHERE `disciplines`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `adresse` = NULL WHERE `adresse`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `ville` = NULL WHERE `ville`='NULL' ;";
        $this->addQuery($query);
        $query = "UPDATE `medecin` SET `cp` = NULL WHERE `cp` LIKE 'NULL%' ;";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "ALTER TABLE `medecin` ADD `jeunefille` VARCHAR( 50 ) AFTER `prenom` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `medecin` ADD `complementaires` TEXT AFTER `disciplines` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `medecin` ADD `orientations` TEXT AFTER `disciplines` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `medecin` DROP `specialite` ;";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `patients`
                ADD `pays` VARCHAR( 50 ),
                ADD `nationalite` ENUM( 'local', 'etranger' ) NOT NULL DEFAULT 'local',
                ADD `lieu_naissance` VARCHAR( 50 ),
                ADD `profession` VARCHAR( 50 ),
                ADD `employeur_nom` VARCHAR( 50 ),
                ADD `employeur_adresse` TEXT,
                ADD `employeur_cp` VARCHAR( 5 ),
                ADD `employeur_ville` VARCHAR( 50 ),
                ADD `employeur_tel` VARCHAR( 10 ),
                ADD `employeur_urssaf` VARCHAR( 11 ),
                ADD `prevenir_nom` VARCHAR( 50 ),
                ADD `prevenir_prenom` VARCHAR( 50 ),
                ADD `prevenir_adresse` TEXT,
                ADD `prevenir_cp` VARCHAR( 5 ),
                ADD `prevenir_ville` VARCHAR( 50 ),
                ADD `prevenir_tel` VARCHAR( 10 ),
                ADD `prevenir_parente` ENUM( 'conjoint', 'enfant', 'ascendant', 'colateral', 'divers' ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `antecedent` CHANGE `type` `type`
                ENUM( 'med', 'alle', 'trans', 'obst', 'chir', 'fam', 'anesth' ) NOT NULL DEFAULT 'med';";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "ALTER TABLE `antecedent`
                CHANGE `antecedent_id` `antecedent_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `patient_id` `patient_id` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `medecin`
                CHANGE `medecin_id` `medecin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `prenom` `prenom` varchar(255) NOT NULL,
                CHANGE `jeunefille` `jeunefille` varchar(255) NULL,
                CHANGE `ville` `ville` varchar(255) NULL,
                CHANGE `cp` `cp` int(5) unsigned zerofill NULL,
                CHANGE `tel` `tel` bigint(10) unsigned zerofill NULL,
                CHANGE `fax` `fax` bigint(10) unsigned zerofill NULL,
                CHANGE `email` `email` varchar(255) NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                CHANGE `patient_id` `patient_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `nom_jeune_fille` `nom_jeune_fille` varchar(255) NULL,
                CHANGE `prenom` `prenom` varchar(255) NOT NULL,
                CHANGE `ville` `ville` varchar(255) NOT NULL,
                CHANGE `medecin_traitant` `medecin_traitant` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `medecin1` `medecin1` int(11) unsigned NULL,
                CHANGE `medecin2` `medecin2` int(11) unsigned NULL,
                CHANGE `medecin3` `medecin3` int(11) unsigned NULL,
                CHANGE `regime_sante` `regime_sante` varchar(255) NULL,
                CHANGE `pays` `pays` varchar(255) NULL,
                CHANGE `cp` `cp` int(5) unsigned zerofill NULL,
                CHANGE `tel` `tel` bigint(10) unsigned zerofill NULL,
                CHANGE `tel2` `tel2` bigint(10) unsigned zerofill NULL,
                CHANGE `SHS` `SHS` int(8) unsigned zerofill NULL,
                CHANGE `employeur_cp` `employeur_cp` int(5) unsigned zerofill NULL,
                CHANGE `employeur_tel` `employeur_tel` bigint(10) unsigned zerofill NULL,
                CHANGE `employeur_urssaf` `employeur_urssaf` bigint(11) unsigned zerofill NULL,
                CHANGE `prevenir_cp` `prevenir_cp` int(5) unsigned zerofill NULL,
                CHANGE `prevenir_tel` `prevenir_tel` bigint(10) unsigned zerofill NULL,
                CHANGE `lieu_naissance` `lieu_naissance` varchar(255) NULL,
                CHANGE `profession` `profession` varchar(255) NULL,
                CHANGE `employeur_nom` `employeur_nom` varchar(255) NULL,
                CHANGE `employeur_ville` `employeur_ville` varchar(255) NULL,
                CHANGE `prevenir_nom` `prevenir_nom` varchar(255) NULL,
                CHANGE `prevenir_prenom` `prevenir_prenom` varchar(255) NULL,
                CHANGE `prevenir_ville` `prevenir_ville` varchar(255) NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `traitement`
                CHANGE `traitement_id` `traitement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `patient_id` `patient_id` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                CHANGE `ATNC` `ATNC` enum('o','n','0','1') NOT NULL DEFAULT 'n',
                CHANGE `incapable_majeur` `incapable_majeur` enum('o','n','0','1') NOT NULL DEFAULT 'n';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `ATNC`='0' WHERE `ATNC`='n';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `ATNC`='1' WHERE `ATNC`='o';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `incapable_majeur`='0' WHERE `incapable_majeur`='n';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `incapable_majeur`='1' WHERE `incapable_majeur`='o';";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                CHANGE `ATNC` `ATNC` enum('0','1') NOT NULL DEFAULT '0',
                CHANGE `incapable_majeur` `incapable_majeur` enum('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.37");
        $query = "ALTER TABLE `patients`
                ADD `nom_soundex2`    VARCHAR(255) DEFAULT NULL AFTER `nom_jeune_fille`,
                ADD `prenom_soundex2` VARCHAR(255) DEFAULT NULL AFTER `nom_soundex2`,
                ADD `nomjf_soundex2`  VARCHAR(255) DEFAULT NULL AFTER `prenom_soundex2`;";
        $this->addQuery($query);
        $this->addMethod("createSoundex");

        $this->makeRevision("0.38");
        $query = "ALTER TABLE `patients` ADD `rang_beneficiaire` enum('1','2','11','12','13') NULL AFTER `ald`;";
        $this->addQuery($query);

        $this->makeRevision("0.39");
        $query = "ALTER TABLE `traitement` CHANGE `debut` `debut` date NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.40");
        $query = "ALTER TABLE `antecedent`
                CHANGE `patient_id` `object_id` int(11) unsigned NOT NULL DEFAULT '0',
                ADD `object_class` enum('CPatient','CConsultAnesth') NOT NULL DEFAULT 'CPatient';";
        $this->addQuery($query);
        $query = "ALTER TABLE `traitement`
                CHANGE `patient_id` `object_id` int(11) unsigned NOT NULL DEFAULT '0',
                ADD `object_class` enum('CPatient','CConsultAnesth') NOT NULL DEFAULT 'CPatient';";
        $this->addQuery($query);

        $this->makeRevision("0.41");
        $query = "ALTER TABLE `patients` CHANGE `medecin_traitant` `medecin_traitant` int(11) unsigned NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `medecin_traitant` = NULL WHERE `medecin_traitant`='0';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `medecin1` = NULL WHERE `medecin1`='0';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `medecin2` = NULL WHERE `medecin2`='0';";
        $this->addQuery($query);
        $query = "UPDATE `patients` SET `medecin3` = NULL WHERE `medecin3`='0';";
        $this->addQuery($query);

        $this->makeRevision("0.42");
        $this->addDependency("dPcabinet", "0.60");
        $query = "ALTER TABLE `addiction`
                CHANGE `object_class` `object_class` enum('CPatient','CConsultAnesth') NOT NULL DEFAULT 'CPatient';";
        $this->addQuery($query);

        $this->makeRevision("0.43");
        $query = "ALTER TABLE `antecedent`
                CHANGE `type` `type` enum('med','alle','trans','obst','chir','fam','anesth','gyn') NOT NULL DEFAULT 'med';";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        $query = "ALTER TABLE `patients`
                ADD `assure_nom` VARCHAR(255),
                ADD `assure_nom_jeune_fille` VARCHAR(255),
                ADD `assure_prenom` VARCHAR(255),
                ADD `assure_naissance` DATE,
                ADD `assure_sexe` ENUM('m','f','j'),
                ADD `assure_adresse` TEXT,
                ADD `assure_ville` VARCHAR(255),
                ADD `assure_cp` INT(5) UNSIGNED ZEROFILL,
                ADD `assure_tel` BIGINT(10) UNSIGNED ZEROFILL,
                ADD `assure_tel2` BIGINT(10) UNSIGNED ZEROFILL,
                ADD `assure_pays` VARCHAR(255),
                ADD `assure_nationalite` ENUM('local','etranger') NOT NULL,
                ADD `assure_lieu_naissance` VARCHAR(255),
                ADD `assure_profession` VARCHAR(255),
                ADD `assure_rques` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.45");
        $query = "ALTER TABLE `patients`
                CHANGE `rang_beneficiaire` `rang_beneficiaire` ENUM('01','02','11','12','13');";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "ALTER TABLE `patients`
                ADD `assure_matricule` VARCHAR(15);";
        $this->addQuery($query);

        $this->makeRevision("0.47");
        $query = "ALTER TABLE `patients`
                ADD `rang_naissance` ENUM('1','2','3','4','5','6');";
        $this->addQuery($query);

        $this->makeRevision("0.48");
        $query = "ALTER TABLE `patients`
                ADD `code_regime` TINYINT(2) UNSIGNED ZEROFILL,
                ADD `caisse_gest` MEDIUMINT(3) UNSIGNED ZEROFILL,
                ADD `centre_gest` MEDIUMINT(4) UNSIGNED ZEROFILL,
                ADD `fin_validite_vitale` DATE;";
        $this->addQuery($query);

        $this->makeRevision("0.49");
        $query = "ALTER TABLE `patients`
                CHANGE `rang_beneficiaire` `rang_beneficiaire` ENUM('01','02','09','11','12','13','14','15','16','31');";
        $this->addQuery($query);


        // Creation de la table dossier medical
        $this->makeRevision("0.50");

        $this->addDependency("dPcabinet", "0.78");

        $query = "CREATE TABLE `dossier_medical` (
                `dossier_medical_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `listCim10` TEXT,
                `object_id` INT(11) UNSIGNED NOT NULL,
                `object_class` VARCHAR(25) NOT NULL,
                PRIMARY KEY (`dossier_medical_id`)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        // Insertion des patients dans la table dossier_medical
        $query = "INSERT INTO `dossier_medical`
                SELECT '', patients.listCim10, patients.patient_id, 'CPatient'
                FROM `patients`;";
        $this->addQuery($query);


        // Insertion des sejours dans la table dossier_medical
        $query = "INSERT INTO `dossier_medical`
                SELECT '', GROUP_CONCAT(consultation_anesth.listCim10 SEPARATOR '|'), sejour.sejour_id, 'CSejour'
                FROM `consultation_anesth`, `operations`,`sejour`
                WHERE consultation_anesth.operation_id = operations.operation_id
                AND operations.sejour_id = sejour.sejour_id
                GROUP BY sejour.sejour_id;";
        $this->addQuery($query);


        // Suppression des '|' en debut de liste
        $query = "UPDATE `dossier_medical` SET `listCim10` = TRIM(LEADING '|' FROM listCim10)
                WHERE listCim10 LIKE '|%'";
        $this->addquery($query);


        // Ajout du champ dossier_medical_id aux tables addiction/antecedent/traitement
        $query = "ALTER TABLE `addiction`
                ADD `dossier_medical_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `addiction`
                ADD INDEX ( `dossier_medical_id` ) ;";
        $this->addQuery($query);


        $query = "ALTER TABLE `antecedent`
                ADD `dossier_medical_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `antecedent`
                ADD INDEX ( `dossier_medical_id` ) ;";
        $this->addQuery($query);


        $query = "ALTER TABLE `traitement`
                ADD `dossier_medical_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `traitement`
                ADD INDEX ( `dossier_medical_id` ) ;";
        $this->addQuery($query);


        // Mise a jour du champ dossier_medical_id dans le cas du Patient
        // Table addiction
        $query = "ALTER TABLE `addiction` ADD INDEX ( `object_id` ) ;";
        $this->addQuery($query);

        $query = "UPDATE `addiction`, `dossier_medical` SET addiction.dossier_medical_id = dossier_medical.dossier_medical_id
                WHERE dossier_medical.object_class = 'CPatient'
                AND dossier_medical.object_id = addiction.object_id
                AND addiction.object_class = 'CPatient'";
        $this->addQuery($query);

        // Table antecedent
        $query = "UPDATE `antecedent`, `dossier_medical` SET antecedent.dossier_medical_id = dossier_medical.dossier_medical_id
                WHERE dossier_medical.object_class = 'CPatient'
                AND dossier_medical.object_id = antecedent.object_id
                AND antecedent.object_class = 'CPatient'";
        $this->addQuery($query);


        // Table Traitement
        $query = "UPDATE `traitement`, `dossier_medical` SET traitement.dossier_medical_id = dossier_medical.dossier_medical_id
                WHERE dossier_medical.object_class = 'CPatient'
                AND dossier_medical.object_id = traitement.object_id
                AND traitement.object_class = 'CPatient'";
        $this->addQuery($query);

        // Mise a jour du champs dossier_medical_id dans le cas du Sejour
        // Table addiction
        $query = "UPDATE `addiction`, `dossier_medical`, `consultation_anesth`, `sejour`, `operations`
            SET addiction.dossier_medical_id = dossier_medical.dossier_medical_id
            WHERE addiction.object_id = consultation_anesth.consultation_anesth_id
            AND addiction.object_class = 'CConsultAnesth'
            AND consultation_anesth.operation_id = operations.operation_id
            AND operations.sejour_id = sejour.sejour_id
            AND dossier_medical.object_class = 'CSejour'
            AND dossier_medical.object_id = sejour.sejour_id;";
        $this->addQuery($query);


        // Table antecedent
        $query = "UPDATE `antecedent`, `dossier_medical`, `consultation_anesth`, `sejour`, `operations`
            SET antecedent.dossier_medical_id = dossier_medical.dossier_medical_id
            WHERE antecedent.object_id = consultation_anesth.consultation_anesth_id
            AND antecedent.object_class = 'CConsultAnesth'
            AND consultation_anesth.operation_id = operations.operation_id
            AND operations.sejour_id = sejour.sejour_id
            AND dossier_medical.object_class = 'CSejour'
            AND dossier_medical.object_id = sejour.sejour_id;";
        $this->addQuery($query);


        // Table traitement
        $query = "UPDATE `traitement`, `dossier_medical`, `consultation_anesth`, `sejour`, `operations`
            SET traitement.dossier_medical_id = dossier_medical.dossier_medical_id
            WHERE traitement.object_id = consultation_anesth.consultation_anesth_id
            AND traitement.object_class = 'CConsultAnesth'
            AND consultation_anesth.operation_id = operations.operation_id
            AND operations.sejour_id = sejour.sejour_id
            AND dossier_medical.object_class = 'CSejour'
            AND dossier_medical.object_id = sejour.sejour_id;";
        $this->addQuery($query);


        // Mise a jour du champ examen de la consultation dans le cas d'antecendent sans operation_id
        $query = "CREATE TEMPORARY TABLE ligneAntecedent (
             consultation_id INT( 11 ) ,
             ligne_antecedent TEXT
            ) AS
              SELECT consultation_anesth.consultation_id,
                CONCAT_WS(' - ', antecedent.type, antecedent.date, antecedent.rques ) AS ligne_antecedent
              FROM `antecedent`, `consultation_anesth`
              WHERE antecedent.object_id = consultation_anesth.consultation_anesth_id
              AND antecedent.dossier_medical_id IS NULL;";
        $this->addQuery($query);

        $query = "CREATE TEMPORARY TABLE blocAntecedent (
             consultation_id INT( 11 ) ,
             bloc_antecedent TEXT
            ) AS
              SELECT consultation_id, GROUP_CONCAT(ligne_antecedent SEPARATOR '\n') AS bloc_antecedent
              FROM `ligneAntecedent`
              GROUP BY consultation_id;";
        $this->addQuery($query);

        $query = "UPDATE `consultation`, `blocAntecedent`
            SET consultation.examen = CONCAT_WS('\n', consultation.examen, blocAntecedent.bloc_antecedent)
            WHERE consultation.consultation_id = blocAntecedent.consultation_id;";
        $this->addQuery($query);


        // Mise a jour du champ examen de la consultation dans le cas d'une addiction sans operation_id
        $query = "CREATE TEMPORARY TABLE ligneAddiction (
             consultation_id INT( 11 ) ,
             ligne_addiction TEXT
            ) AS
              SELECT consultation_anesth.consultation_id, CONCAT_WS(' - ', addiction.type, addiction.addiction ) AS ligne_addiction
              FROM `addiction`, `consultation_anesth`
              WHERE addiction.object_id = consultation_anesth.consultation_anesth_id
              AND addiction.dossier_medical_id IS NULL;";
        $this->addQuery($query);

        $query = "CREATE TEMPORARY TABLE blocAddiction (
             consultation_id INT( 11 ) ,
             bloc_addiction TEXT
            ) AS
              SELECT consultation_id, GROUP_CONCAT(ligne_addiction SEPARATOR '\n') AS bloc_addiction
              FROM `ligneAddiction`
              GROUP BY consultation_id;";
        $this->addQuery($query);

        $query = "UPDATE `consultation`, `blocAddiction`
            SET consultation.examen = CONCAT_WS('\n', consultation.examen, blocAddiction.bloc_addiction)
            WHERE consultation.consultation_id = blocAddiction.consultation_id;";
        $this->addQuery($query);


        // Mise a jour du champ examen de la consultation dans le cas d'un traitement sans operation_id
        $query = "CREATE TEMPORARY TABLE ligneTraitement (
             consultation_id INT( 11 ) ,
             ligne_traitement TEXT
            ) AS
              SELECT consultation_anesth.consultation_id,
                CONCAT_WS(' - ', traitement.debut, traitement.fin, traitement.traitement ) AS ligne_traitement
              FROM `traitement`, `consultation_anesth`
              WHERE traitement.object_id = consultation_anesth.consultation_anesth_id
              AND traitement.dossier_medical_id IS NULL;";
        $this->addQuery($query);


        $query = "CREATE TEMPORARY TABLE blocTraitement (
             consultation_id INT( 11 ) ,
             bloc_traitement TEXT
            ) AS
              SELECT consultation_id, GROUP_CONCAT(ligne_traitement SEPARATOR '\n') AS bloc_traitement
              FROM `ligneTraitement`
              GROUP BY consultation_id;";
        $this->addQuery($query);

        $query = "UPDATE `consultation`, `blocTraitement`
            SET consultation.examen = CONCAT_WS('\n', consultation.examen, blocTraitement.bloc_traitement)
            WHERE consultation.consultation_id = blocTraitement.consultation_id;";
        $this->addQuery($query);

        $query = "ALTER TABLE `addiction`
            DROP `object_id`,
            DROP `object_class`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `antecedent`
            DROP `object_id`,
            DROP `object_class`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `traitement`
            DROP `object_id`,
            DROP `object_class`;";
        $this->addQuery($query);

        $this->makeRevision("0.51");
        $query = "ALTER TABLE `patients`
            DROP `listCim10`;";
        $this->addQuery($query);


        $this->makeRevision("0.52");
        $query = "ALTER TABLE `patients`
           CHANGE `naissance` `naissance` CHAR( 10 ) NULL DEFAULT NULL ";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
           CHANGE `assure_naissance` `assure_naissance` CHAR( 10 ) NULL DEFAULT NULL ";
        $this->addQuery($query);


        $this->makeRevision("0.53");
        $query = "ALTER TABLE `dossier_medical` CHANGE `listCim10` `codes_cim` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.54");
        $query = "ALTER TABLE `patients` ADD INDEX ( `nom_soundex2` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `prenom_soundex2` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `naissance` );";
        $this->addQuery($query);


        $this->makeRevision("0.55");
        $query = "ALTER TABLE `patients` ADD INDEX ( `nom_jeune_fille` );";
        $this->addQuery($query);

        $this->makeRevision("0.56");
        $query = "ALTER TABLE `dossier_medical` ADD INDEX ( `object_id` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `dossier_medical` ADD INDEX ( `object_class` );";
        $this->addQuery($query);

        $this->makeRevision("0.57");
        $query = "ALTER TABLE `antecedent`
            CHANGE `type` `type`
            ENUM('med','alle','trans','obst',
              'chir','fam','anesth','gyn','cardio',
              'pulm','stomato','plast','ophtalmo',
              'digestif','gastro','stomie','uro',
              'ortho','traumato','amput','neurochir',
              'greffe','thrombo','cutane','hemato',
              'rhumato','neuropsy','infect','endocrino',
              'carcino')
            NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.58");
        $query = "ALTER TABLE `patients` ADD INDEX ( `medecin_traitant` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `medecin1` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `medecin2` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD INDEX ( `medecin3` );";
        $this->addQuery($query);

        $this->makeRevision("0.59");
        $query = "ALTER TABLE `patients` CHANGE `ald` `notes_amo` TEXT DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $query = "ALTER TABLE `patients`
            ADD `ald` ENUM('0','1'),
            ADD `code_exo` ENUM('0','5','9') DEFAULT '0',
            ADD `deb_amo` DATE,
            ADD `fin_amo` DATE;";
        $this->addQuery($query);

        $this->makeRevision("0.61");
        $query = "UPDATE `patients`
            SET `fin_amo` = `cmu`";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
            CHANGE `cmu` `cmu` ENUM('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `patients`
            SET `cmu` = '1'
            WHERE `fin_amo` IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.62");
        $query = "ALTER TABLE `antecedent`
            CHANGE `type` `type`
            ENUM('med','alle','trans','obst',
              'chir','fam','anesth','gyn','cardio',
              'pulm','stomato','plast','ophtalmo',
              'digestif','gastro','stomie','uro',
              'ortho','traumato','amput','neurochir',
              'greffe','thrombo','cutane','hemato',
              'rhumato','neuropsy','infect','endocrino',
              'carcino','orl');";
        $this->addQuery($query);
        $query = "ALTER TABLE `addiction`
            CHANGE `type` `type`
            ENUM('tabac', 'oenolisme', 'cannabis');";
        $this->addQuery($query);

        $this->makeRevision("0.63");
        $query = "CREATE TABLE `etat_dent` (
            `etat_dent_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `dossier_medical_id` INT NOT NULL ,
            `dent` TINYINT UNSIGNED NOT NULL ,
            `etat` ENUM('bridge', 'pivot', 'mobile', 'appareil') NULL
            ) /*! ENGINE=MyISAM */ ;";
        $this->addQuery($query);

        $this->makeRevision("0.64");
        $this->addDependency("dPsante400", "0.1");
        $query = "INSERT INTO `id_sante400` (id_sante400_id, object_class, object_id, tag, last_update, id400)
            SELECT NULL, 'CPatient', `patient_id`, 'SHS group:1', NOW(), `SHS`
            FROM `patients`
            WHERE `SHS` IS NOT NULL
            AND `SHS` != 0";
        $this->addQuery($query);

        $this->makeRevision("0.65");
        $query = "ALTER TABLE `patients` DROP `SHS`";
        $this->addQuery($query);

        $this->makeRevision("0.66");
        $this->addDependency("dPcabinet", "0.30");
        $query = "CREATE TABLE `constantes_medicales` (
      `constantes_medicales_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `patient_id` INT (11) UNSIGNED NOT NULL,
      `datetime` DATETIME NOT NULL,
      `context_class` VARCHAR (255),
      `context_id` INT (11) UNSIGNED,
      `poids` FLOAT UNSIGNED,
      `taille` FLOAT,
      `ta` VARCHAR (10),
      `pouls` INT (11) UNSIGNED,
      `spo2` FLOAT
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `constantes_medicales`
      ADD INDEX (`patient_id`),
      ADD INDEX (`datetime`),
      ADD INDEX (`context_id`);";
        $this->addQuery($query);

        $query = "INSERT INTO `constantes_medicales` (
        `context_class`,
        `context_id`,
        `patient_id`,
        `datetime`,
        `poids`,
        `taille`,
        `ta`,
        `pouls`,
        `spo2`
      )
      SELECT
        'CConsultation',
        `consultation`.`consultation_id`,
        `consultation`.`patient_id`,
        CONCAT(`plageconsult`.`date`, ' ', `consultation`.`heure`),
        `consultation_anesth`.`poid`,
        `consultation_anesth`.`taille`,
        IF(`consultation_anesth`.`tasys`, CONCAT(`consultation_anesth`.`tasys`, '|', `consultation_anesth`.`tadias`), NULL),
        `consultation_anesth`.`pouls`,
        `consultation_anesth`.`spo2`
      FROM
        `consultation_anesth`, `consultation`, `plageconsult`
      WHERE
        `consultation`.`consultation_id` = `consultation_anesth`.`consultation_id` AND
        `plageconsult`.`plageconsult_id` = `consultation`.`plageconsult_id`";
        $this->addQuery($query);

        $repl = [
            "Patient - poids",
            "Patient - taille",
            "Patient - Pouls",
            "Patient - IMC",
            "Patient - TA",
        ];

        $find  = [
            "Anesthésie - poids",
            "Anesthésie - taille",
            "Anesthésie - Pouls",
            "Anesthésie - IMC",
            "Anesthésie - TA",
        ];
        $count = count($repl);
        for ($i = 0; $i < $count; $i++) {
            $query = CSetupCompteRendu::renameTemplateFieldQuery($find[$i], $repl[$i]);
            $this->addQuery($query);
        }

        $this->makeRevision("0.67");
        $query = 'ALTER TABLE `constantes_medicales` ADD `temperature` FLOAT';
        $this->addQuery($query);

        $this->makeRevision("0.68");
        $query = "ALTER TABLE `patients`
            ADD `code_sit` MEDIUMINT (4) UNSIGNED ZEROFILL,
            ADD `regime_am` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision("0.69");
        $this->addQuery(
            CSetupCompteRendu::renameTemplateFieldQuery("Patient - antécédents", "Patient - Antécédents -- tous")
        );
        $this->addQuery(CSetupCompteRendu::renameTemplateFieldQuery("Patient - traitements", "Patient - Traitements"));
        $this->addQuery(
            CSetupCompteRendu::renameTemplateFieldQuery("Patient - addictions", "Patient - Addictions -- toutes")
        );
        $this->addQuery(CSetupCompteRendu::renameTemplateFieldQuery("Patient - diagnostics", "Patient - Diagnotics"));

        $this->addQuery(
            CSetupCompteRendu::renameTemplateFieldQuery("Sejour - antécédents", "Sejour - Antécédents -- tous")
        );
        $this->addQuery(CSetupCompteRendu::renameTemplateFieldQuery("Sejour - traitements", "Sejour - Traitements"));
        $this->addQuery(
            CSetupCompteRendu::renameTemplateFieldQuery("Sejour - addictions", "Sejour - Addictions -- toutes")
        );
        $this->addQuery(CSetupCompteRendu::renameTemplateFieldQuery("Sejour - diagnostics", "Sejour - Diagnotics"));

        $this->makeRevision("0.70");
        $query = "ALTER TABLE `patients` ADD `email` VARCHAR (255) AFTER tel2;";
        $this->addQuery($query);

        $this->makeRevision("0.71");
        $query = "CREATE TABLE `correspondant` (
      `correspondant_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `medecin_id` INT (11) UNSIGNED NOT NULL,
      `patient_id` INT (11) UNSIGNED NOT NULL,
      KEY (`medecin_id`),
      KEY (`patient_id`)
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.72");
        $query = "ALTER TABLE `antecedent`
            CHANGE `type` `type`
            ENUM('med','alle','trans','obst',
              'chir','fam','anesth','gyn',
              'cardio','pulm','stomato','plast','ophtalmo',
              'digestif','gastro','stomie','uro',
              'ortho','traumato','amput','neurochir',
              'greffe','thrombo','cutane','hemato',
              'rhumato','neuropsy','infect','endocrino',
              'carcino','orl','addiction','habitus');";
        $this->addQuery($query);

        // If there is a type
        $query = "INSERT INTO `antecedent` (`type`, `rques`, `dossier_medical_id`)
            SELECT 'addiction', CONCAT(UPPER(LEFT(`type`, 1)), LOWER(SUBSTRING(`type`, 2)), ': ', `addiction`), `dossier_medical_id`
            FROM `addiction`
            WHERE `type` IS NOT NULL AND `type` <> '0'";
        $this->addQuery($query);

        // If there is no type
        $query = "INSERT INTO `antecedent` (`type`, `rques`, `dossier_medical_id`)
            SELECT 'addiction', `addiction`, `dossier_medical_id`
            FROM `addiction`
            WHERE `type` IS NULL OR `type` = '0'";
        $this->addQuery($query);

        // If there is a type
        // @todo : A vérifier
        /*$query = "UPDATE `aide_saisie` SET
                  `class` = 'CAntecedent',
                  `field` = 'rques',
                  `name` = CONCAT(UPPER(LEFT(`depend_value`, 1)), LOWER(SUBSTRING(`depend_value`, 2)), ': ', `name`),
                  `text` = CONCAT(UPPER(LEFT(`depend_value`, 1)), LOWER(SUBSTRING(`depend_value`, 2)), ': ', `text`),
                  `depend_value` = 'addiction'
                WHERE
                  `class` = 'CAddiction'
                   AND `depend_value` IS NOT NULL";
        $this->addQuery($query);*/

        // If there is no type
        /*$query = "UPDATE `aide_saisie` SET
                  `class` = 'CAntecedent',
                  `field` = 'rques',
                  `depend_value` = 'addiction'
                WHERE
                  `class` = 'CAddiction'
                   AND `depend_value` IS NULL";
        $this->addQuery($query);*/

        $this->addQuery(
            CSetupCompteRendu::renameTemplateFieldQuery(
                "Sejour - Addictions -- toutes",
                "Sejour - Antécédents - Addictions"
            )
        );
        $this->addQuery(
            CSetupCompteRendu::renameTemplateFieldQuery(
                "Patient - Addictions -- toutes",
                "Patient - Antécédents - Addictions"
            )
        );

        $addiction_types = ['tabac', 'oenolisme', 'cannabis'];
        foreach ($addiction_types as $type) {
            $typeTrad = CAppUI::tr("CAddiction.type.$type");
            $this->addQuery(
                CSetupCompteRendu::renameTemplateFieldQuery(
                    "Sejour - Addictions - $typeTrad",
                    "Sejour - Antécédents - Addictions"
                )
            );
            $this->addQuery(
                CSetupCompteRendu::renameTemplateFieldQuery(
                    "Patient - Addictions - $typeTrad",
                    "Patient - Antécédents - Addictions"
                )
            );
        }

        /*$query = "DROP TABLE `addiction`";
        $this->addQuery($query);*/

        $this->makeRevision("0.73");
        $query = "ALTER TABLE `constantes_medicales`
            ADD `score_sensibilite` FLOAT,
            ADD `score_motricite` FLOAT,
            ADD `EVA` FLOAT,
            ADD `score_sedation` FLOAT,
            ADD `frequence_respiratoire` FLOAT;";
        $this->addQuery($query);


        $this->makeRevision("0.74");
        for ($i = 1; $i <= 3; $i++) {
            $query = "INSERT INTO `correspondant` (`medecin_id`, `patient_id`)
              SELECT `medecin$i`, `patient_id`
              FROM `patients`
              WHERE `medecin$i` IS NOT NULL";
            $this->addQuery($query);
        }
        $query = "ALTER TABLE `patients`
            DROP `medecin1`,
            DROP `medecin2`,
            DROP `medecin3`";
        $this->addQuery($query);

        $this->makeRevision("0.75");
        $query = "UPDATE `constantes_medicales` SET `poids` = NULL WHERE `poids` = 0";
        $this->addQuery($query);

        $query = "UPDATE `constantes_medicales` SET `taille` = NULL WHERE `taille` = 0";
        $this->addQuery($query);

        $query = "DELETE FROM `constantes_medicales` WHERE
            `poids` IS NULL AND
            `taille` IS NULL AND
            `ta` IS NULL AND
            `pouls` IS NULL AND
            `spo2` IS NULL AND
            `temperature` IS NULL AND
            `score_sensibilite` IS NULL AND
            `score_motricite` IS NULL AND
            `EVA` IS NULL AND
            `score_sedation` IS NULL AND
            `frequence_respiratoire` IS NULL";
        $this->addQuery($query);

        $this->makeRevision("0.76");
        $query = "ALTER TABLE `medecin` ADD `type` ENUM ('medecin','kine','sagefemme','infirmier') NOT NULL DEFAULT 'medecin';";
        $this->addQuery($query);

        $this->makeRevision("0.77");
        $query = "ALTER TABLE `antecedent` ADD `annule` ENUM('0','1') DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.78");
        $query = "ALTER TABLE `medecin` ADD `portable` BIGINT(10) UNSIGNED ZEROFILL NULL";
        $this->addQuery($query);

        $this->makeRevision("0.79");
        $query = "ALTER TABLE `antecedent`
            ADD `appareil` ENUM ('cardiovasculaire','endocrinien','neuro_psychiatrique','uro_nephrologique','digestif','pulmonaire');";
        $this->addQuery($query);

        $this->makeRevision("0.80");
        $query = "ALTER TABLE patients
            ADD pays_insee INT(11) AFTER pays ,
            ADD prenom_2 VARCHAR(50) AFTER prenom ,
            ADD prenom_3 VARCHAR(50) AFTER prenom_2 ,
            ADD prenom_4 VARCHAR(50) AFTER prenom_3 ,
            ADD cp_naissance VARCHAR(5) AFTER lieu_naissance ,
            ADD pays_naissance_insee INT(11) AFTER cp_naissance,
            ADD assure_pays_insee INT(11) AFTER assure_pays ,
            ADD assure_prenom_2 VARCHAR(50) AFTER assure_prenom ,
            ADD assure_prenom_3 VARCHAR(50) AFTER assure_prenom_2 ,
            ADD assure_prenom_4 VARCHAR(50) AFTER assure_prenom_3 ,
            ADD assure_cp_naissance VARCHAR(5) AFTER assure_lieu_naissance,
            ADD assure_pays_naissance_insee INT(11) AFTER assure_cp_naissance;";

        $this->addQuery($query);

        $this->makeRevision("0.81");

        $query = "ALTER TABLE `patients`
            CHANGE `prenom_2` `prenom_2` VARCHAR (255),
            CHANGE `prenom_3` `prenom_3` VARCHAR (255),
            CHANGE `prenom_4` `prenom_4` VARCHAR (255),
            CHANGE `sexe` `sexe` ENUM ('m','f','j'),
            CHANGE `adresse` `adresse` TEXT,
            CHANGE `ville` `ville` VARCHAR (255),
            CHANGE `incapable_majeur` `incapable_majeur` ENUM ('0','1'),
            CHANGE `ATNC` `ATNC` ENUM ('0','1'),
            CHANGE `matricule` `matricule` VARCHAR (15),
            CHANGE `assure_prenom_2` `assure_prenom_2` VARCHAR (255),
            CHANGE `assure_prenom_3` `assure_prenom_3` VARCHAR (255),
            CHANGE `assure_prenom_4` `assure_prenom_4` VARCHAR (255);";

        $this->addQuery($query);

        $this->makeRevision("0.82");
        $query = "ALTER TABLE `antecedent`
            CHANGE `appareil` `appareil`
            ENUM('cardiovasculaire','digestif','endocrinien',
              'neuro_psychiatrique','pulmonaire',
              'uro_nephrologique','orl','gyneco_obstetrique',
              'orthopedique');";
        $this->addQuery($query);

        $this->makeRevision("0.83");
        $query = "ALTER TABLE `patients`
            CHANGE `pays_insee` `pays_insee` INT(3) UNSIGNED ZEROFILL,
            CHANGE `pays_naissance_insee` `pays_naissance_insee` INT(3) UNSIGNED ZEROFILL,
            CHANGE `assure_pays_insee` `assure_pays_insee` INT(3) UNSIGNED ZEROFILL,
            CHANGE `assure_pays_naissance_insee` `assure_pays_naissance_insee` INT(3) UNSIGNED ZEROFILL;";
        $this->addQuery($query);

        $this->makeRevision("0.84");

        $query = "ALTER TABLE `patients`
        ADD `libelle_exo` TEXT AFTER `rques`,
        ADD `medecin_traitant_declare` ENUM('0', '1') AFTER `email`";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
        CHANGE `code_exo` `code_exo` ENUM('0', '4', '5', '9') NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.85");

        $query = "ALTER TABLE `patients`
            ADD `civilite` ENUM ('m','mme','melle','enf','dr','pr','me','vve') AFTER `sexe`,
            ADD `assure_civilite` ENUM ('m','mme','melle','enf','dr','pr','me','vve') AFTER `assure_sexe`";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `civilite` = 'm' WHERE `sexe` = 'm'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `civilite` = 'mme' WHERE `sexe` = 'f'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `civilite` = 'melle' WHERE `sexe` = 'j'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `civilite` = 'enf' WHERE `naissance` >= " . (date('Y') - 15);
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `assure_civilite` = 'm' WHERE `assure_sexe` = 'm'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `assure_civilite` = 'mme' WHERE `assure_sexe` = 'f'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `assure_civilite` = 'melle' WHERE `assure_sexe` = 'j'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `assure_civilite` = 'enf' WHERE `assure_naissance` >= " . (date('Y') - 15);
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `sexe` = 'f' WHERE `sexe` = 'j'";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
            CHANGE `sexe` `sexe` ENUM ('m','f'),
            CHANGE `assure_sexe` `assure_sexe` ENUM ('m','f')";
        $this->addQuery($query);

        $this->makeRevision("0.86");

        $this->makeRevision("0.87");

        $query = "ALTER TABLE `medecin`
            ADD `adeli` INT (9) UNSIGNED ZEROFILL;";
        $this->addQuery($query);

        $this->makeRevision("0.88");
        $query = "ALTER TABLE `constantes_medicales`
      ADD `glycemie` FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("0.89");
        $query = "ALTER TABLE `medecin`
      CHANGE `type` `type` ENUM ('medecin','kine','sagefemme','infirmier','dentiste','autre')";
        $this->addQuery($query);

        $this->makeRevision("0.90");
        $query = "ALTER TABLE `medecin`
      CHANGE `type` `type` ENUM ('medecin','kine','sagefemme','infirmier','dentiste','podologue','autre');";
        $this->addQuery($query);

        $this->makeRevision("0.91");
        $query = "ALTER TABLE `patients`
      ADD `notes_amc` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.92");
        $query = "ALTER TABLE `antecedent`
                ADD INDEX (`date`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `etat_dent`
                ADD INDEX (`dossier_medical_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `traitement`
                ADD INDEX (`debut`),
                ADD INDEX (`fin`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
                ADD INDEX (`deb_amo`),
                ADD INDEX (`fin_amo`),
                ADD INDEX (`fin_validite_vitale`);";
        $this->addQuery($query);

        $this->makeRevision("0.93");
        $query = "ALTER TABLE `antecedent`
              CHANGE `type` `type` VARCHAR (80),
              CHANGE `appareil` `appareil` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("0.94");
        $query = "ALTER TABLE `dossier_medical`
              ADD `risque_thrombo_patient` ENUM ('faible','modere','eleve','majeur','NR') DEFAULT 'NR',
              ADD `risque_MCJ_patient` ENUM ('sans','avec','suspect','atteint','NR') DEFAULT 'NR',
              ADD `risque_thrombo_chirurgie` ENUM ('faible','modere','eleve','NR') DEFAULT 'NR',
              ADD `risque_antibioprophylaxie` ENUM ('oui','non','NR') DEFAULT 'NR',
              ADD `risque_prophylaxie` ENUM ('oui','non','NR') DEFAULT 'NR',
              ADD `risque_MCJ_chirurgie` ENUM ('sans','avec','NR') DEFAULT 'NR';";
        $this->addQuery($query);

        $this->makeRevision("0.95");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `redon` FLOAT UNSIGNED,
              ADD `diurese` FLOAT UNSIGNED,
              ADD `injection` VARCHAR (10);";
        $this->addQuery($query);

        $this->makeRevision("0.96");
        $query = "ALTER TABLE `patients`
              ADD `code_gestion` MEDIUMINT (4) UNSIGNED ZEROFILL,
              ADD `mutuelle_types_contrat` TEXT";
        $this->addQuery($query);

        $this->makeRevision("0.97");
        $query = "ALTER TABLE `patients` ADD `code_gestion2` MEDIUMINT (2) UNSIGNED ZEROFILL";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` CHANGE `code_gestion` `centre_carte` MEDIUMINT (4) UNSIGNED ZEROFILL";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` CHANGE `code_gestion2` `code_gestion` MEDIUMINT (2) UNSIGNED ZEROFILL";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients` ADD `qual_beneficiaire` ENUM ('0','1','2','3','4','5','6','7','8','9')";
        $this->addQuery($query);

        foreach (CPatient::$rangToQualBenef as $from => $to) {
            $query = "UPDATE `patients` SET `qual_beneficiaire` = '$to' WHERE `rang_beneficiaire` = '$from'";
            $this->addQuery($query);
        }

        $this->makeRevision("0.98");
        $query = "ALTER TABLE `patients` CHANGE `code_gestion` `code_gestion` CHAR (2)";
        $this->addQuery($query);

        $this->makeRevision("0.99");

        $query = "ALTER TABLE `patients`
             CHANGE `civilite` `civilite` ENUM ('m','mme','melle','mlle','enf','dr','pr','me','vve') DEFAULT 'm',
             CHANGE `assure_civilite` `assure_civilite` ENUM ('m','mme','melle','mlle','enf','dr','pr','me','vve') DEFAULT 'm';";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `civilite` = 'mlle' WHERE `civilite` = 'melle'";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `assure_civilite` = 'mlle' WHERE `assure_civilite` = 'melle'";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
             CHANGE `civilite` `civilite` ENUM ('m','mme','mlle','enf','dr','pr','me','vve') DEFAULT 'm',
             CHANGE `assure_civilite` `assure_civilite` ENUM ('m','mme','mlle','enf','dr','pr','me','vve') DEFAULT 'm';";
        $this->addQuery($query);

        $this->makeRevision("1.0");
        $query = "ALTER TABLE `constantes_medicales` ADD `ta_droit` VARCHAR (10) AFTER `ta`";
        $this->addQuery($query);

        $this->makeRevision("1.01");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `redon_2` FLOAT UNSIGNED AFTER `redon`,
              ADD `redon_3` FLOAT UNSIGNED AFTER `redon_2`";
        $this->addQuery($query);

        $this->makeRevision("1.02");
        $query = "ALTER TABLE `patients`
      ADD `confiance_nom` VARCHAR( 50 ) AFTER `prevenir_parente`,
      ADD `confiance_prenom` VARCHAR( 50 ) AFTER `confiance_nom`,
      ADD `confiance_adresse` TEXT AFTER `confiance_prenom`,
      ADD `confiance_cp` VARCHAR( 5 ) AFTER `confiance_adresse`,
      ADD `confiance_ville` VARCHAR( 50 ) AFTER `confiance_cp`,
      ADD `confiance_tel` VARCHAR( 10 ) AFTER `confiance_ville`,
      ADD `confiance_parente` ENUM( 'conjoint', 'enfant', 'ascendant', 'colateral', 'divers' ) AFTER `confiance_tel`;";
        $this->addQuery($query);

        $this->makeRevision("1.03");
        $query = "ALTER TABLE `patients` ADD `tel_autre` VARCHAR (20) AFTER `tel2`";
        $this->addQuery($query);

        $this->makeRevision("1.04");
        $this->addPrefQuery("vCardExport", "0");

        $this->makeRevision("1.05");
        $query = "ALTER TABLE `patients` ADD `vip` ENUM ('0','1') NOT NULL DEFAULT '0' AFTER `email`";
        $this->addQuery($query);

        $this->makeRevision("1.06");
        $query = "ALTER TABLE `patients` ADD `date_lecture_vitale` DATETIME";
        $this->addQuery($query);

        $this->makeRevision("1.07");
        $query = "ALTER TABLE `patients`
      DROP `nationalite`,
      DROP `assure_nationalite`;";
        $this->addQuery($query);

        $this->makeRevision("1.08");
        $query = "ALTER TABLE `medecin`
      CHANGE `type` `type` ENUM ('medecin','kine','sagefemme',
        'infirmier','dentiste','podologue', 'pharmacie',
        'maison_medicale', 'autre');";
        $this->addQuery($query);

        $this->makeRevision("1.09");
        $query = "ALTER TABLE `groups_config`
      ADD `dPpatients_CPatient_nom_jeune_fille_mandatory` ENUM ('0', '1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.10");
        $query = "ALTER TABLE `constantes_medicales`
      CHANGE `ta` `ta_gauche` VARCHAR (10),
      ADD `ta` VARCHAR(10) AFTER `taille`,
      ADD PVC FLOAT UNSIGNED,
      ADD perimetre_abdo FLOAT UNSIGNED,
      ADD perimetre_cuisse FLOAT UNSIGNED,
      ADD perimetre_cou FLOAT UNSIGNED,
      ADD perimetre_thoracique FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.11");

        $query = "UPDATE constantes_medicales
              SET ta_gauche = ta, ta = NULL
              WHERE ta IS NOT NULL AND ta_gauche IS NULL";
        $this->addQuery($query);

        $this->makeRevision("1.12");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `diurese_miction` FLOAT UNSIGNED AFTER `diurese`;";
        $this->addQuery($query);

        $this->makeRevision("1.13");
        $query = "ALTER TABLE `patients`
      ADD `deces` DATE AFTER `naissance`;";
        $this->addQuery($query);

        $this->makeRevision("1.14");
        $query = "ALTER TABLE `medecin`
      CHANGE `prenom` `prenom` varchar(255);";
        $this->addQuery($query);

        $this->makeRevision("1.15");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `comment` TEXT";
        $this->addQuery($query);

        $this->makeRevision("1.16");
        $query = "ALTER TABLE `patients`
      ADD INDEX ( `nomjf_soundex2` );";
        $this->addQuery($query);

        $this->makeRevision("1.17");
        $query = "ALTER TABLE `patients`
      ADD `INS` CHAR(22) AFTER `matricule`";
        $this->addQuery($query);

        $this->makeRevision("1.18");
        $query = "ALTER TABLE `patients`
      CHANGE `INS` `INSC` CHAR(22),
      ADD `INSC_date` DATETIME AFTER `INSC`";
        $this->addQuery($query);

        $this->makeRevision("1.19");
        $query = "ALTER TABLE `medecin`
               ADD `rpps` BIGINT (11) UNSIGNED ZEROFILL;";
        $this->addQuery($query);

        $this->makeRevision("1.20");
        $query = "CREATE TABLE `correspondant_patient` (
      `correspondant_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `patient_id` INT (11) UNSIGNED NOT NULL,
      `relation` ENUM ('confiance','prevenir','employeur'),
      `nom` VARCHAR (255),
      `prenom` VARCHAR (255),
      `adresse` TEXT,
      `cp` INT (5) UNSIGNED ZEROFILL,
      `ville` VARCHAR (255),
      `tel` BIGINT (10) UNSIGNED ZEROFILL,
      `urssaf` BIGINT (11) UNSIGNED ZEROFILL,
      `parente` ENUM ('conjoint','enfant','ascendant','colateral','divers'),
      `email` VARCHAR (255),
      `remarques` TEXT
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_patient`
              ADD INDEX (`patient_id`);";
        $this->addQuery($query);

        $query = "INSERT INTO correspondant_patient (patient_id, relation, nom, adresse, cp, ville, tel, urssaf)
      SELECT patient_id, 'employeur',
        employeur_nom, employeur_adresse,
        employeur_cp, employeur_ville,
        employeur_tel, employeur_urssaf
      FROM patients";
        $this->addQuery($query);

        $query = "INSERT INTO correspondant_patient (patient_id, relation, nom, prenom, adresse, cp, ville, tel, parente)
      SELECT patient_id, 'prevenir',
        prevenir_nom, prevenir_prenom,
        prevenir_adresse, prevenir_cp,
        prevenir_ville, prevenir_tel, prevenir_parente
      FROM patients";
        $this->addQuery($query);

        $query = "INSERT INTO correspondant_patient (patient_id, relation, nom, prenom, adresse, cp, ville, tel, parente)
      SELECT patient_id, 'confiance',
        confiance_nom, confiance_prenom,
        confiance_adresse, confiance_cp,
        confiance_ville, confiance_tel, confiance_parente
      FROM patients";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
      DROP `employeur_nom`,
      DROP `employeur_adresse`,
      DROP `employeur_cp`,
      DROP `employeur_ville`,
      DROP `employeur_tel`,
      DROP `employeur_urssaf`,
      DROP `prevenir_nom`,
      DROP `prevenir_prenom`,
      DROP `prevenir_adresse`,
      DROP `prevenir_cp`,
      DROP `prevenir_ville`,
      DROP `prevenir_tel`,
      DROP `prevenir_parente`,
      DROP `confiance_nom`,
      DROP `confiance_prenom`,
      DROP `confiance_adresse`,
      DROP `confiance_cp`,
      DROP `confiance_ville`,
      DROP `confiance_tel`,
      DROP `confiance_parente`";
        $this->addQuery($query);

        $this->makeRevision("1.21");
        $query = "CREATE TABLE `devenir_dentaire` (
      `devenir_dentaire_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `patient_id` INT (11) UNSIGNED NOT NULL
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `devenir_dentaire`
      ADD INDEX (`patient_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `acte_dentaire` (
      `acte_dentaire_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `devenir_dentaire_id` INT (11) UNSIGNED NOT NULL,
      `code` VARCHAR (10) NOT NULL,
      `commentaire` TEXT
      ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_dentaire`
      ADD INDEX (`devenir_dentaire_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.22");
        $query = "DELETE FROM `correspondant_patient`
      WHERE `nom` IS NULL
      AND `prenom` IS NULL
      AND `adresse` IS NULL
      AND `cp` IS NULL
      AND `ville` IS NULL
      AND `tel` IS NULL
      AND `urssaf` IS NULL
      AND `parente` IS NULL
      AND `email` IS NULL
      AND `remarques` IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.23");
        $query = "ALTER TABLE `constantes_medicales`
      ADD `redon_4` FLOAT UNSIGNED AFTER `redon_3`,
      ADD `sng` FLOAT UNSIGNED,
      ADD `lame_1` FLOAT UNSIGNED,
      ADD `lame_2` FLOAT UNSIGNED,
      ADD `lame_3` FLOAT UNSIGNED,
      ADD `drain_1` FLOAT UNSIGNED,
      ADD `drain_2` FLOAT UNSIGNED,
      ADD `drain_3` FLOAT UNSIGNED,
      ADD `drain_thoracique_1` FLOAT UNSIGNED,
      ADD `drain_thoracique_2` FLOAT UNSIGNED,
      ADD `drain_pleural_1` FLOAT UNSIGNED,
      ADD `drain_pleural_2` FLOAT UNSIGNED,
      ADD `drain_mediastinal` FLOAT UNSIGNED,
      ADD `sonde_ureterale_1` FLOAT UNSIGNED,
      ADD `sonde_ureterale_2` FLOAT UNSIGNED,
      ADD `sonde_vesicale` FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.24");
        $query = "CREATE TABLE `config_constantes_medicales` (
              `service_id` INT (11) UNSIGNED,
              `group_id` INT (11) UNSIGNED,
              `config_constantes_medicales_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `important_constantes` TEXT,
              `diuere_24_reset_hour` TINYINT (4) UNSIGNED,
              `redon_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `sng_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `lame_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `drain_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `drain_thoracique_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `drain_pleural_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `drain_mediastinal_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `sonde_ureterale_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `sonde_vesicale_cumul_reset_hour` TINYINT (4) UNSIGNED,
              `show_cat_tabs` ENUM ('0','1')
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `config_constantes_medicales`
              ADD INDEX (`service_id`),
              ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $conf  = CAppUI::conf("dPpatients CConstantesMedicales");
        $query = $this->ds->prepare(
            "INSERT INTO `config_constantes_medicales` (
         `important_constantes` ,
         `diuere_24_reset_hour` ,
         `redon_cumul_reset_hour` ,
         `sng_cumul_reset_hour` ,
         `lame_cumul_reset_hour` ,
         `drain_cumul_reset_hour` ,
         `drain_thoracique_cumul_reset_hour` ,
         `drain_pleural_cumul_reset_hour` ,
         `drain_mediastinal_cumul_reset_hour` ,
         `sonde_ureterale_cumul_reset_hour` ,
         `sonde_vesicale_cumul_reset_hour` ,
         `show_cat_tabs`
       )
       VALUES (
         ?1 , ?2 , ?3 , ?4 , ?5 , ?6 , ?7 , ?8 , ?9 , ?10 , ?11 , '0'
       );",
            CValue::read($conf, "important_constantes"),
            CValue::read($conf, "diuere_24_reset_hour"),
            CValue::read($conf, "redon_cumul_reset_hour"),
            CValue::read($conf, "sng_cumul_reset_hour"),
            CValue::read($conf, "lame_cumul_reset_hour"),
            CValue::read($conf, "drain_cumul_reset_hour"),
            CValue::read($conf, "drain_thoracique_cumul_reset_hour"),
            CValue::read($conf, "drain_pleural_cumul_reset_hour"),
            CValue::read($conf, "drain_mediastinal_cumul_reset_hour"),
            CValue::read($conf, "sonde_ureterale_cumul_reset_hour"),
            CValue::read($conf, "sonde_vesicale_cumul_reset_hour")
        );
        $this->addQuery($query);

        $this->makeRevision("1.25");
        $query = "ALTER TABLE `devenir_dentaire`
              ADD `etudiant_id` INT (11) UNSIGNED,
              ADD INDEX (`etudiant_id`),
              ADD `description` TEXT NOT NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_dentaire`
              ADD `ICR` INT (11) UNSIGNED,
              ADD `consult_id` INT (11) UNSIGNED,
              ADD INDEX (`consult_id`),
              ADD `rank` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.26");
        $query = "ALTER TABLE `correspondant_patient`
              CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir'),
              ADD `relation_autre` VARCHAR (255),
              CHANGE `parente` `parente` ENUM ('ami','ascendant','autre','beau_fils',
                'colateral','collegue','compagnon','conjoint','directeur','divers',
                'employeur','employe','enfant','enfant_adoptif','entraineur','epoux',
                'frere','grand_parent','mere','pere','petits_enfants','proche',
                'proprietaire','soeur','tuteur'),
              ADD `parente_autre` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.27");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `catheter_suspubien` FLOAT UNSIGNED,
              ADD `entree_lavage` FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.28");
        $query = "ALTER TABLE `config_constantes_medicales`
              ADD `show_enable_all_button` ENUM ('0','1');";
        $this->addQuery($query);
        $query = "UPDATE `config_constantes_medicales`
              SET `show_enable_all_button` = '1' WHERE `group_id` IS NULL AND `service_id` IS NULL";
        $this->addQuery($query);

        $this->makeRevision("1.29");
        $query = "ALTER TABLE `patients`
                ADD `csp` TINYINT (2) UNSIGNED ZEROFILL;";
        $this->addQuery($query);

        $this->makeRevision("1.30");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `sonde_nephro_2` FLOAT UNSIGNED AFTER `sonde_ureterale_2`,
              ADD `sonde_nephro_1` FLOAT UNSIGNED AFTER `sonde_ureterale_2`";
        $this->addQuery($query);
        $query = "ALTER TABLE `config_constantes_medicales`
              ADD `sonde_nephro_cumul_reset_hour` TINYINT (4) UNSIGNED AFTER `sonde_ureterale_cumul_reset_hour`;";
        $this->addQuery($query);
        $query = "UPDATE `config_constantes_medicales`
              SET `sonde_nephro_cumul_reset_hour` = '8' WHERE `group_id` IS NULL AND `service_id` IS NULL";
        $this->addQuery($query);

        $this->makeRevision("1.31");
        $query = "ALTER TABLE `constantes_medicales`
      ADD `perimetre_cranien` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.32");
        $query = "ALTER TABLE `patients`
       ADD `tutelle` ENUM ('aucune','tutelle','curatelle') DEFAULT 'aucune';";
        $this->addQuery($query);

        $this->makeRevision("1.33");

        $this->addMethod("initObversationResultTables");

        $this->makeRevision("1.34");

        $this->addMethod("alterObversationResultTablePhase1");

        $query = "ALTER TABLE `etat_dent`
              CHANGE `etat` `etat` ENUM ('bridge','pivot','mobile','appareil','defaut');";
        $this->addQuery($query);

        $this->makeRevision("1.35");

        $this->addMethod("alterObversationValueTablesPhase1");

        $this->makeRevision("1.36");
        $mdil_units = [
            "0004-1140" => ["°F", "°F"],
            "0004-17a0" => ["°C", "°C"],
        ];
        $values     = [];
        foreach ($mdil_units as $_code => $_labels) {
            [$_label, $_desc] = $_labels;
            $values[] = "('MDIL', '$_code', '$_label', '$_desc')";
        }
        $query = "INSERT INTO `observation_value_unit` (`coding_system`, `code`, `label`, `desc`) VALUES " . implode(
                "\n, ",
                $values
            );
        $this->addQuery($query);

        $this->makeRevision("1.37");

        $this->addMethod("alterObversationResultTablePhase2");

        $this->makeRevision("1.38");

        $this->addMethod("alterObversationResultTablePhase3");

        $query = "CREATE TABLE `supervision_graph` (
              `supervision_graph_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `owner_class` ENUM ('CGroups') NOT NULL,
              `owner_id` INT (11) UNSIGNED NOT NULL,
              `title` VARCHAR (255) NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph`
              ADD INDEX (`owner_id`),
              ADD INDEX (`owner_class`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `supervision_graph_axis` (
              `supervision_graph_axis_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `supervision_graph_id` INT (11) UNSIGNED NOT NULL,
              `title` VARCHAR (255) NOT NULL,
              `limit_low` FLOAT,
              `limit_high` FLOAT,
              `display` ENUM ('points','lines','bars'),
              `show_points` ENUM ('0','1') NOT NULL DEFAULT '0',
              `symbol` ENUM ('circle','square','diamond','cross','triangle') NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_axis`
              ADD INDEX (`supervision_graph_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `supervision_graph_series` (
              `supervision_graph_series_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `supervision_graph_axis_id` INT (11) UNSIGNED NOT NULL,
              `title` VARCHAR (255),
              `value_type_id` INT (11) UNSIGNED NOT NULL,
              `value_unit_id` INT (11) UNSIGNED NOT NULL,
              `color` CHAR (6) NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_series`
              ADD INDEX (`supervision_graph_axis_id`),
              ADD INDEX (`value_type_id`),
              ADD INDEX (`value_unit_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.39");
        $query = "ALTER TABLE `supervision_graph`
              ADD `disabled` ENUM ('0','1') NOT NULL DEFAULT '1',
              ADD INDEX (`disabled`);";
        $this->addQuery($query);

        $this->makeRevision("1.40");
        $query = "ALTER TABLE `correspondant_patient`
              ADD `fax` BIGINT (10) UNSIGNED ZEROFILL,
              ADD `mob` BIGINT (10) UNSIGNED ZEROFILL";
        $this->addQuery($query);

        $this->makeRevision("1.41");
        $query = "ALTER TABLE `medecin`
      CHANGE `cp` `cp` VARCHAR(8) DEFAULT ''";
        $this->addQuery($query);

        $this->makeRevision("1.42");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `bricker` FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.43");
        $query = "ALTER TABLE `patients`
              CHANGE `cp` `cp` VARCHAR (5),
              CHANGE `assure_cp` `assure_cp` VARCHAR (5)";
        $this->addQuery($query);

        $this->makeRevision("1.44");
        $query = "ALTER TABLE `antecedent`
            CHANGE `type` `type`
            ENUM('med','alle','trans','obst',
              'chir','fam','anesth','gyn','cardio',
              'pulm','stomato','plast','ophtalmo',
              'digestif','gastro','stomie','uro','ortho',
              'traumato','amput','neurochir','greffe','thrombo',
              'cutane','hemato','rhumato','neuropsy',
              'infect','endocrino','carcino','orl',
              'addiction','habitus', 'deficience');";
        $this->addQuery($query);

        $this->makeRevision("1.45");
        $query = "ALTER TABLE `patients`
              CHANGE `tel` `tel` VARCHAR (20),
              CHANGE `tel2` `tel2` VARCHAR (20),
              CHANGE `tel_autre` `tel_autre` VARCHAR (40),
              CHANGE `assure_tel` `assure_tel` VARCHAR (20),
              CHANGE `assure_tel2` `assure_tel2` VARCHAR (20)";
        $this->addQuery($query);
        $query = "ALTER TABLE `correspondant_patient`
              CHANGE `tel` `tel` VARCHAR (20),
              CHANGE `mob` `mob` VARCHAR (20),
              CHANGE `fax` `fax` VARCHAR (20);";
        $this->addQuery($query);

        $this->makeRevision("1.46");
        $query = "ALTER TABLE `etat_dent`
              CHANGE `etat` `etat` ENUM ('bridge','pivot','mobile','appareil','implant','defaut');";
        $this->addQuery($query);

        $this->makeRevision("1.47");
        $query = "ALTER TABLE `medecin`
              CHANGE `tel` `tel` VARCHAR (20),
              CHANGE `fax` `fax` VARCHAR (20),
              CHANGE `portable` `portable` VARCHAR (20);";
        $this->addQuery($query);

        $this->makeRevision("1.48");
        $query = "ALTER TABLE `patients`
                ADD `patient_link_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.49");
        $query = "ALTER TABLE `traitement`
              ADD `annule` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.50");
        $query = "ALTER TABLE `antecedent`
              CHANGE `type` `type` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("1.51");
        $query = "ALTER TABLE `correspondant_patient`
      ADD `nom_jeune_fille` VARCHAR (255),
      ADD `naissance` DATE;";
        $this->addQuery($query);

        $this->makeRevision("1.52");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `douleur_en` FLOAT UNSIGNED,
              ADD `douleur_doloplus` TINYINT (4) UNSIGNED,
              ADD `douleur_algoplus` TINYINT (4) UNSIGNED,
              ADD `ecpa_avant` TINYINT (4) UNSIGNED,
              ADD `ecpa_apres` TINYINT (4) UNSIGNED,
              ADD `vision_oeil_droit` TINYINT (4) UNSIGNED,
              ADD `vision_oeil_gauche` TINYINT (4) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.53");

        $query = "ALTER TABLE `correspondant_patient`
                 ADD `ean` VARCHAR (30);";
        $this->addQuery($query);
        $this->makeRevision("1.54");

        $query = "ALTER TABLE `correspondant_patient`
              ADD `date_debut` DATE,
              ADD `date_fin` DATE,
              ADD `num_assure` VARCHAR (30),
              ADD `employeur` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `correspondant_patient`
              ADD INDEX (`naissance`),
              ADD INDEX (`date_debut`),
              ADD INDEX (`date_fin`),
              ADD INDEX (`employeur`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `patients`
              ADD `avs` VARCHAR (15),
              ADD `assure_avs` VARCHAR (15);";
        $this->addQuery($query);

        $this->makeRevision("1.55");
        $query = "ALTER TABLE `constantes_medicales` ADD `creatininemie` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.56");
        $query = "ALTER TABLE `constantes_medicales` ADD `glasgow` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.57");
        $query = "ALTER TABLE `constantes_medicales`
              ADD `drain_thoracique_flow` FLOAT UNSIGNED AFTER `drain_thoracique_2`,
              ADD `drain_shirley` FLOAT UNSIGNED AFTER `drain_mediastinal`";
        $this->addQuery($query);

        $this->makeRevision("1.58");

        $query = "CREATE TABLE `correspondant_modele` (
      `correspondant_modele_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `group_id` INT (11) UNSIGNED,
      `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir'),
      `relation_autre` VARCHAR (255),
      `nom` VARCHAR (255),
      `nom_jeune_fille` VARCHAR (255),
      `prenom` VARCHAR (255),
      `naissance` CHAR (10),
      `adresse` TEXT,
      `cp` INT (5) UNSIGNED ZEROFILL,
      `ville` VARCHAR (255),
      `tel` VARCHAR (20),
      `mob` VARCHAR (20),
      `fax` VARCHAR (20),
      `urssaf` BIGINT (11) UNSIGNED ZEROFILL,
      `parente` ENUM ('ami','ascendant','autre','beau_fils','colateral','collegue',
        'compagnon','conjoint','directeur','divers','employeur','employe','enfant',
        'enfant_adoptif','entraineur','epoux','frere','grand_parent','mere','pere',
        'petits_enfants','proche','proprietaire','soeur','tuteur'),
      `parente_autre` VARCHAR (255),
      `email` VARCHAR (255),
      `remarques` TEXT,
      `ean` VARCHAR (30),
      `num_assure` VARCHAR (30),
      `employeur` INT (11) UNSIGNED
     ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_modele`
                ADD INDEX (`group_id`),
                ADD INDEX (`employeur`);";
        $this->addQuery($query);

        $this->makeRevision("1.59");

        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `patient_id` `patient_id` INT (11) UNSIGNED,
                ADD `ean_id` VARCHAR (20)";
        $this->addQuery($query);

        $this->makeRevision("1.60");

        $query = "DROP TABLE `correspondant_modele`";
        $this->addQuery($query);

        $this->makeRevision("1.61");

        $query = "ALTER TABLE `patients`
                CHANGE `avs` `avs` VARCHAR (16);";
        $this->addQuery($query);

        $this->makeRevision("1.62");

        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `cp` `cp` VARCHAR (5)";
        $this->addQuery($query);

        $this->makeRevision("1.63");

        $query = "ALTER TABLE `correspondant_patient`
    ADD `assure_id` VARCHAR (25) AFTER ean";
        $this->addQuery($query);

        $this->makeRevision("1.64");

        $query = "ALTER TABLE `supervision_graph_axis`
                CHANGE `display` `display` ENUM ('points','lines','bars','stack','bandwidth');";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_series`
                ADD `integer_values` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $query = "CREATE TABLE `supervision_graph_pack` (
                `supervision_graph_pack_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `owner_class` ENUM ('CGroups') NOT NULL,
                `owner_id` INT (11) UNSIGNED NOT NULL,
                `title` VARCHAR (255) NOT NULL,
                `disabled` ENUM ('0','1') NOT NULL DEFAULT '1'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_pack`
                ADD INDEX `owner` (`owner_class`, `owner_id`)";
        $this->addQuery($query);
        $query = "CREATE TABLE `supervision_graph_to_pack` (
                `supervision_graph_to_pack_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `graph_class` ENUM ('CSupervisionGraph','CSupervisionTimedData'),
                `graph_id` INT (11) UNSIGNED NOT NULL,
                `pack_id` INT (11) UNSIGNED NOT NULL,
                `rank` INT (11) NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_to_pack`
                ADD INDEX (`graph_id`),
                ADD INDEX (`pack_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph`
                ADD `height` INT (11) UNSIGNED NOT NULL DEFAULT 200;";
        $this->addQuery($query);
        $query = "CREATE TABLE `supervision_timed_data` (
                `supervision_timed_data_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `owner_class` ENUM ('CGroups') NOT NULL,
                `owner_id` INT (11) UNSIGNED NOT NULL,
                `title` VARCHAR (255) NOT NULL,
                `disabled` ENUM ('0','1') NOT NULL DEFAULT '1',
                `period` ENUM ('1','5','10','15','20','30','60') NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_timed_data`
                ADD INDEX (`owner_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.65");
        $query = "ALTER TABLE  `constantes_medicales`
                DROP INDEX `context_id`,
                ADD  INDEX `patient_id_datetime` (`patient_id`, `datetime`),
                ADD  INDEX `context_class` (`context_class`),
                ADD  INDEX `context` (`context_class`, `context_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.66");

        $query = "ALTER TABLE `patients`
              DROP `assure_avs`;";
        $this->addQuery($query);

        $this->makeRevision("1.68");

        $query = "ALTER TABLE `medecin`
                ADD `email_apicrypt` VARCHAR (50),
                ADD `last_ldap_checkout` DATE;";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
                ADD `province` VARCHAR (40) AFTER adresse;";
        $this->addQuery($query);

        $this->makeRevision("1.69");
        $this->addMethod("moveConstantesConfigsFromOldTable");
        // TODO: "DROP TABLE config_constantes_medicales"

        $this->makeRevision("1.70");
        $query = "ALTER TABLE `constantes_medicales`
                ADD `hauteur_uterine` FLOAT UNSIGNED AFTER `perimetre_thoracique`,
                ADD `peak_flow` FLOAT UNSIGNED AFTER `vision_oeil_gauche`;";
        $this->addQuery($query);

        $this->makeRevision("1.71");
        $query = "ALTER TABLE `dossier_medical` 
                ADD `facteurs_risque` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.72");
        $query = "ALTER TABLE `constantes_medicales`
                ADD `hemoglobine_rapide` FLOAT AFTER `glycemie`,
                ADD `gaz` INT (11) UNSIGNED AFTER `injection`,
                ADD `selles` INT (11) UNSIGNED AFTER `gaz`";
        $this->addQuery($query);

        $this->makeRevision("1.73");
        $query = "ALTER TABLE `patients`
                ADD `situation_famille` ENUM ('S','M','G','P','D','W','A')";
        $this->addQuery($query);

        $this->makeRevision("1.74");
        $query = "ALTER TABLE `patients`
                ADD `is_smg` ENUM ('0','1') DEFAULT '0' AFTER `cmu`";
        $this->addQuery($query);

        $this->makeRevision("1.75");
        $query = "ALTER TABLE `constantes_medicales`
                ADD `cetonemie` FLOAT UNSIGNED AFTER `glycemie`";
        $this->addQuery($query);

        $this->makeRevision("1.76");
        $query = "ALTER TABLE `constantes_medicales`
      ADD `redon_5` FLOAT UNSIGNED AFTER `redon_4`;";
        $this->addQuery($query);

        $this->makeRevision("1.77");

        $query = "ALTER TABLE `constantes_medicales` 
                ADD `douleur_evs` TINYINT (4) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.78");
        $this->addMethod("addConstantesRank");
        $this->makeRevision("1.79");

        $query = "UPDATE `patients` SET `INSC` = NULL, `INSC_date` = null;";
        $this->addQuery($query);
        $this->makeRevision("1.80");

        $query = "ALTER TABLE `correspondant_patient` 
                ADD `ean_base` VARCHAR (30),
                ADD `type_pec` ENUM ('TG','TP','TS');";
        $this->addQuery($query);
        $this->makeRevision("1.81");

        $query = "ALTER TABLE `constantes_medicales` 
                ADD `ta_couche` VARCHAR (10),
                ADD `ta_assis` VARCHAR (10),
                ADD `ta_debout` VARCHAR (10),
                ADD `redon_6` FLOAT UNSIGNED,
                ADD `redon_7` FLOAT UNSIGNED,
                ADD `redon_8` FLOAT UNSIGNED,
                ADD `redon_accordeon_1` FLOAT UNSIGNED,
                ADD `redon_accordeon_2` FLOAT UNSIGNED,
                ADD `drain_thoracique_3` FLOAT UNSIGNED,
                ADD `drain_thoracique_4` FLOAT UNSIGNED,
                ADD `drain_dve` FLOAT UNSIGNED,
                ADD `drain_kher` FLOAT UNSIGNED,
                ADD `drain_crins` FLOAT UNSIGNED,
                ADD `drain_sinus` FLOAT UNSIGNED,
                ADD `drain_orifice_1` FLOAT UNSIGNED,
                ADD `drain_orifice_2` FLOAT UNSIGNED,
                ADD `drain_orifice_3` FLOAT UNSIGNED,
                ADD `drain_orifice_4` FLOAT UNSIGNED,
                ADD `drain_ileostomie` FLOAT UNSIGNED,
                ADD `drain_colostomie` FLOAT UNSIGNED,
                ADD `drain_gastrostomie` FLOAT UNSIGNED,
                ADD `drain_jejunostomie` FLOAT UNSIGNED,
                ADD `sonde_rectale` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.82");
        $query = "INSERT INTO `configuration` (`feature`, `value`, `object_id`, `object_class`)
                SELECT REPLACE(`feature`, ' selection ', ' selection_cabinet '), `value`, `object_id`, `object_class`
                FROM `configuration`
                WHERE `feature` LIKE 'dPpatients CConstantesMedicales selection %'
                AND (
                  `object_class` = 'CGroups' OR `object_class` IS NULL AND `object_id` IS NULL
                )";
        $this->addQuery($query);


        $this->makeRevision("1.83");
        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir') DEFAULT 'prevenir',
                ADD `surnom` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.84");
        $query = "ALTER TABLE `constantes_medicales`
                ADD `ph_sanguin` FLOAT UNSIGNED,
                ADD `lactates` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.85");

        $this->addMethod("alterObversationResultTablePhase4");

        $query = "CREATE TABLE `supervision_timed_picture` (
                `supervision_timed_picture_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `owner_class` ENUM ('CGroups') NOT NULL,
                `owner_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `title` VARCHAR (255) NOT NULL,
                `disabled` ENUM ('0','1') NOT NULL DEFAULT '1',
                `value_type_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_timed_picture`
                ADD INDEX `owner` (`owner_class`, `owner_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_to_pack`
                CHANGE `graph_class` `graph_class` VARCHAR (80);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_timed_data`
                CHANGE `period` `period` ENUM ('1','5','10','15','20','30','60'),
                ADD `value_type_id` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_timed_data`
                ADD INDEX (`value_type_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.86");
        $mdil_params = [
            "0002-4a15" => ["ABPs", "ABP (systolic)"],
            "0002-4a16" => ["ABPd", "ABP (diastolic)"],
            "0002-4a17" => ["ABPm", "ABP (mean)"],
        ];
        $values      = [];
        foreach ($mdil_params as $_code => $_labels) {
            [$_label, $_desc] = $_labels;
            $values[] = "('MDIL', '$_code', '$_label', '$_desc', 'NM')";
        }
        $query = "INSERT INTO `observation_value_type` (`coding_system`, `code`, `label`, `desc`, `datatype`) VALUES " .
            implode("\n, ", $values);
        $this->addQuery($query);

        $this->makeRevision("1.87");
        $query = "CREATE TABLE `supervision_graph_value_label` (
                `supervision_graph_value_label_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `supervision_graph_axis_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `value` INT (11) NOT NULL,
                `title` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_value_label`
                ADD INDEX (`supervision_graph_axis_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `supervision_graph_series`
                CHANGE `value_unit_id` `value_unit_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.88");

        $this->addMethod("alterObversationResultTablePhase5");

        $this->makeRevision("1.89");
        $query = "ALTER TABLE `dossier_medical`
                ADD `absence_traitement` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision("1.90");

        $this->addMethod('modifyConstantsRanksConfigs');

        $this->makeRevision('1.91');

        $query = 'ALTER TABLE `constantes_medicales`
                ADD `hemo_glycquee` FLOAT UNSIGNED,
                ADD `saturation_air` FLOAT UNSIGNED,
                ADD `clair_creatinine` FLOAT UNSIGNED;';
        $this->addQuery($query);

        $this->makeRevision("1.92");
        $query = "CREATE TABLE `ins_patient` (
                `ins_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `ins` VARCHAR (255) NOT NULL,
                `type` ENUM ('A','C') NOT NULL,
                `date` DATETIME NOT NULL,
                `provider` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.93");
        $query = "ALTER TABLE `ins_patient`
                ADD INDEX (`patient_id`),
                ADD INDEX (`date`);";
        $this->addQuery($query);

        $this->makeRevision("1.94");
        $query = "ALTER TABLE `patients`
                DROP `INSC`,
                DROP `INSC_DATE`";
        $this->addQuery($query);

        $this->makeRevision("1.95");
        $query = "ALTER TABLE `dossier_medical`
                ADD `groupe_sanguin` ENUM ('?','O','A','B','AB') DEFAULT '?',
                ADD `rhesus` ENUM ('?','NEG','POS') DEFAULT '?',
                ADD `groupe_ok` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.96');
        $this->addDependency("dPcabinet", "1.23");
        //Création des dossiers médicaux manquants correspondant aux consultations d'anesthésie
        $query = "INSERT INTO `dossier_medical` (`object_class`, `object_id`)
                SELECT 'CPatient', c.patient_id
                FROM `consultation_anesth` ca, `consultation` c
                WHERE ca.consultation_id = c.consultation_id
                AND c.patient_id IS NOT NULL
                AND ( (ca.rhesus != '?' AND ca.rhesus != '')
                      OR ca.groupe_ok = '1'
                      OR (ca.groupe != '?' AND ca.groupe != '') )
                AND NOT EXISTS (
                  SELECT * FROM dossier_medical AS d
                  WHERE c.patient_id = d.object_id
                  AND d.object_class = 'CPatient'
                );";
        $this->addQuery($query);

        //Mise à jour des rhésus dans les dossiers médicaux
        $query = "UPDATE `dossier_medical` d, `consultation_anesth` ca, `consultation` c
                SET d.rhesus = ca.rhesus
                WHERE d.object_class = 'CPatient'
                AND ca.consultation_id = c.consultation_id
                AND c.patient_id = d.object_id
                AND c.patient_id IS NOT NULL
                AND ca.rhesus != '?'
                AND ca.rhesus != ''
                AND d.rhesus = '?';";
        $this->addQuery($query);

        //Mise à jour du groupe_ok dans les dossiers médicaux
        $query = "UPDATE `dossier_medical` d, `consultation_anesth` ca, `consultation` c
                SET d.groupe_ok = ca.groupe_ok
                WHERE d.object_class = 'CPatient'
                AND ca.consultation_id = c.consultation_id
                AND c.patient_id = d.object_id
                AND c.patient_id IS NOT NULL
                AND ca.groupe_ok = '1'
                AND d.groupe_ok = '0';";
        $this->addQuery($query);

        //Mise à jour du groupe sanguin dans les dossiers médicaux
        $query = "UPDATE `dossier_medical` d, `consultation_anesth` ca, `consultation` c
                SET d.groupe_sanguin = ca.groupe
                WHERE d.object_class = 'CPatient'
                AND ca.consultation_id = c.consultation_id
                AND c.patient_id = d.object_id
                AND c.patient_id IS NOT NULL
                AND ca.groupe != '?'
                AND ca.groupe != ''
                AND d.groupe_sanguin = '?';";
        $this->addQuery($query);

        // Preference pour ranger les antecedents par date
        $this->makeRevision("1.97");
        $this->addPrefQuery("sort_atc_by_date", "0");

        // Index manquants dans la table Patient
        $this->makeRevision('1.98');
        $query = "ALTER TABLE `patients`
      ADD INDEX (`deces`),
      ADD INDEX (`ville`),
      ADD INDEX (`profession`),
      ADD INDEX (`patient_link_id`),
      ADD INDEX (`assure_profession`),
      ADD INDEX (`date_lecture_vitale`);";
        $this->addQuery($query);

        $this->makeRevision("1.99");
        $query = "ALTER TABLE `patients`
    ADD `allow_sms_notification` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.00");

        //Default's value
        $query = "INSERT INTO configuration (feature, value)
                SELECT 'dPpatients CPatient nom_jeune_fille_mandatory',
                       dPpatients_CPatient_nom_jeune_fille_mandatory
                FROM groups_config
                WHERE object_id IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("2.01");

        $query = "INSERT INTO configuration (feature, value, object_id, object_class)
                SELECT 'dPpatients CPatient nom_jeune_fille_mandatory',
                       dPpatients_CPatient_nom_jeune_fille_mandatory,
                       object_id, 'CGroups'
                FROM groups_config
                WHERE object_id IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("2.02");

        $this->makeRevision("2.03");

        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `nom` `nom` VARCHAR (255) NOT NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
                ADD `function_id` INT (11) UNSIGNED AFTER `patient_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
                ADD INDEX (`function_id`);";
        $this->addQuery($query);

        $this->makeRevision("2.04");

        $query = "ALTER TABLE `medecin`
                ADD `function_id` INT (11) UNSIGNED AFTER `medecin_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin`
                ADD INDEX (`function_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_patient`
                ADD `function_id` INT (11) UNSIGNED AFTER `correspondant_patient_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_patient`
                ADD INDEX (`function_id`);";
        $this->addQuery($query);

        $this->makeRevision("2.05");
        $query = "ALTER TABLE `medecin`
                ADD `sexe` VARCHAR (2) NOT NULL DEFAULT 'u' AFTER `jeunefille` ";
        $this->addQuery($query);

        $this->makeRevision("2.06");

        $query = "ALTER TABLE `constantes_medicales`
                ADD `alat` INT (3),
                ADD `asat` INT (3),
                ADD `broadman` INT (2),
                ADD `cpk` INT (3),
                ADD `hdlc` FLOAT,
                ADD `ipsc` FLOAT,
                ADD `gammagt` INT (3),
                ADD `ldlc` FLOAT,
                ADD `plaquettes` INT (4),
                ADD `potassium` FLOAT,
                ADD `sodium` FLOAT,
                ADD `triglycerides` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("2.07");
        $query = "ALTER TABLE `constantes_medicales`
      ADD `user_id` INT (11) UNSIGNED AFTER `constantes_medicales_id`";
        $this->addQuery($query);

        $this->makeRevision("2.08");
        $query = "ALTER TABLE `patients`
                ADD `allow_sisra_send` ENUM ('0','1') DEFAULT '1'";
        $this->addQuery($query);

        $this->makeRevision("2.09");
        $query = "ALTER TABLE `correspondant_patient`
                ADD `sex` VARCHAR (2) NOT NULL DEFAULT 'u' AFTER `prenom` ";
        $this->addQuery($query);

        $this->makeRevision("2.10");
        $this->addMethod("updateDeathDate");

        $this->makeRevision("2.11");
        $this->addPrefQuery('update_patient_from_vitale_behavior', 'choice');

        $this->makeRevision("2.12");
        $this->addDefaultConfig(
            "dPpatients CPatient allow_anonymous_patient",
            'dPplanningOp CSejour create_anonymous_pat'
        );
        $this->addDefaultConfig("dPpatients CPatient anonymous_sexe", 'dPplanningOp CSejour anonymous_sexe');
        $this->addDefaultConfig("dPpatients CPatient anonymous_naissance", 'dPplanningOp CSejour anonymous_naissance');

        $this->makeRevision("2.13");

        $query = "CREATE TABLE `patient_state` (
                `patient_state_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `mediuser_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `state` ENUM ('PROV','VALI','DPOT','ANOM', 'CACH') NOT NULL,
                `datetime` DATETIME NOT NULL,
                `reason` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `patient_state`
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`mediuser_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
                ADD `status` ENUM ('PROV','VALI','DPOT','ANOM','CACH'),
                ADD INDEX (`status`);";
        $this->addQuery($query);

        $this->makeRevision("2.14");

        $query = "CREATE TABLE `patient_link` (
                `patient_link_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id1` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `patient_id2` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `type` ENUM ('DPOT') DEFAULT 'DPOT',
                `reason` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `patient_link`
                ADD INDEX (`patient_id1`),
                ADD INDEX (`patient_id2`);";

        $this->addQuery($query);

        //cas où des doublons ont été saisie
        $query = "INSERT INTO `patient_link`(`patient_id1`, `patient_id2`)
                SELECT `patient_id`, `patient_link_id` FROM `patients` WHERE `patient_link_id` IS NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("2.15");

        $this->addFunctionalPermQuery("allowed_identity_status", "0");

        $this->makeRevision("2.16");

        $query = "UPDATE `user_preferences` SET `key` = 'LogicielLectureVitale', `value` = 'none'
                WHERE `key` = 'VitaleVision' AND `value` = '0';";
        $this->addQuery($query);
        $query = "UPDATE `user_preferences` SET `key` = 'LogicielLectureVitale', `value` = 'vitaleVision'
                WHERE `key` = 'VitaleVision' AND `value` = '1';";
        $this->addQuery($query);

        $this->makeRevision("2.17");

        $this->addFunctionalPermQuery("allowed_modify_identity_status", "0");

        $this->makeRevision("2.18");
        $this->addPrefQuery("new_date_naissance_selector", "0");

        $this->makeRevision("2.19");
        $query = "ALTER TABLE `etat_dent`
                CHANGE `etat` `etat` ENUM ('bridge','pivot','mobile','appareil','implant','defaut','absence','app-partiel');";
        $this->addQuery($query);

        $this->makeRevision('2.20');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `sens_membre_inf_d` FLOAT,
                ADD `sens_membre_inf_g` FLOAT,
                ADD `tonus_d` FLOAT,
                ADD `tonus_g` FLOAT,
                ADD `motricite_d` FLOAT,
                ADD `motricite_g` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision('2.21');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `perimetre_brachial` FLOAT UNSIGNED AFTER `perimetre_abdo`;";
        $this->addQuery($query);

        $this->makeRevision('2.22');

        $this->addDefaultConfig(
            "dPpatients identitovigilance merge_only_admin",
            "dPpatients CPatient merge_only_admin"
        );
        $this->addDefaultConfig(
            "dPpatients identitovigilance show_patient_link",
            "dPpatients CPatient show_patient_link"
        );
        $this->addDefaultConfig("dPpatients CTraitement enabled", "dPpatients CTraitement enabled");

        $this->makeRevision('2.23');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `creation_date` DATETIME AFTER `user_id`;";
        $this->addQuery($query);

        $this->makeRevision("2.24");
        $query = "ALTER TABLE `supervision_graph`
                ADD `automatic_protocol` ENUM ('Kheops-Concentrator');";
        $this->addQuery($query);

        $query = "CREATE TABLE `supervision_instant_data` (
                `supervision_instant_data_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `value_type_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `value_unit_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `size` TINYINT (4) UNSIGNED NOT NULL DEFAULT '0',
                `color` VARCHAR (6),
                `owner_class` ENUM ('CGroups') NOT NULL,
                `owner_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `title` VARCHAR (255) NOT NULL,
                `disabled` ENUM ('0','1') NOT NULL DEFAULT '1',
                INDEX (`value_type_id`),
                INDEX (`value_unit_id`),
                INDEX `owner` (`owner_class`, `owner_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('2.25');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `echelle_confort` INT(2);";
        $this->addQuery($query);

        $this->makeRevision('2.26');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `pres_artere_invasive` INT(3);";
        $this->addQuery($query);

        $this->makeRevision('2.27');
        $query = "ALTER TABLE `patients`
              ADD `ame` ENUM('0','1') DEFAULT '0' AFTER `cmu`;";
        $this->addQuery($query);

        $this->makeRevision('2.28');
        $query = "ALTER TABLE `patient_state`
                CHANGE `state` `state` ENUM ('PROV','VALI','DPOT','ANOM','CACH','DOUB','DESA','DOUA','COLP','COLV','FILI','HOMD',
                'HOMA','USUR','IDRA','RECD','IDVER') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('2.29');

        $query = "ALTER TABLE `constantes_medicales`
                ADD perimetre_hanches FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('2.30');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `capnometrie` INT(3) UNSIGNED,
                ADD `sortie_lavage` INT(3) UNSIGNED,
                ADD `coloration` INT(1) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('2.31');

        $this->addPrefQuery('constantes_show_comments_tooltip', '0');

        $this->makeRevision('2.32');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `motricite_inf_d` FLOAT,
                ADD `motricite_inf_g` FLOAT,
                ADD `perimetre_taille` INT(3) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('2.33');
        $query = "CREATE TABLE `pathologie` (
                `pathologie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `debut` DATE,
                `fin` DATE,
                `pathologie` TEXT,
                `annule` ENUM ('0','1') DEFAULT '0',
                `dossier_medical_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `pathologie`
                ADD INDEX (`debut`),
                ADD INDEX (`fin`),
                ADD INDEX (`dossier_medical_id`);";
        $this->addQuery($query);
        $this->makeRevision("2.34");
        $query = "ALTER TABLE `supervision_graph_axis`
                ADD `in_doc_template` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_timed_data`
                ADD `in_doc_template` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_timed_picture`
                ADD `in_doc_template` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.35');
        $query = "ALTER TABLE `antecedent`
                ADD `owner_id` INT (11) UNSIGNED,
                ADD `creation_date` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `traitement`
                ADD `owner_id` INT (11) UNSIGNED,
                ADD `creation_date` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `pathologie`
                ADD `owner_id` INT (11) UNSIGNED NOT NULL,
                ADD `creation_date` DATETIME NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("2.36");
        $query = "ALTER TABLE `constantes_medicales`
            ADD `contraction_uterine` FLOAT,
            ADD `bruit_foetal` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("2.37");
        $query = "ALTER TABLE `pathologie`
      ADD `indication_id` INT (11),
      ADD `indication_group_id` INT (11);";
        $this->addQuery($query);

        $this->makeRevision("2.38");
        $query = "ALTER TABLE `antecedent`
      ADD `majeur` ENUM ('0','1') DEFAULT '0' AFTER `annule`;";
        $this->addQuery($query);

        $this->makeRevision("2.39");
        $query = "ALTER TABLE `dossier_medical`
                ADD `medecin_traitant_id` INT (11) UNSIGNED AFTER `dossier_medical_id`,
                ADD `derniere_mapa` DATE AFTER `absence_traitement`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `dossier_medical`
                ADD INDEX (`medecin_traitant_id`),
                ADD INDEX (`derniere_mapa`);";
        $this->addQuery($query);

        $this->makeRevision("2.40");
        $query = "CREATE TABLE `salutation` (
                `salutation_id`    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `owner_id`         INT(11) UNSIGNED NOT NULL,
                `object_class`     VARCHAR(255) NOT NULL,
                `object_id`        INT(11) UNSIGNED NOT NULL,
                `starting_formula` VARCHAR(255) NOT NULL,
                `closing_formula`  VARCHAR(255) NOT NULL,
                INDEX (`owner_id`),
                INDEX `object` (`object_class`, `object_id`),
                UNIQUE (`owner_id`, `object_class`, `object_id`)
                )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("2.41");
        $this->addPrefQuery('constantes_show_view_tableau', '0');

        $this->makeRevision("2.42");
        $query = "ALTER TABLE `medecin`
            ADD `titre` ENUM ('m', 'mme', 'dr', 'pr') AFTER `sexe`;";
        $this->addQuery($query);

        $this->makeRevision("2.43");
        $query = "ALTER TABLE `medecin`
      ADD `actif` ENUM ('0','1') DEFAULT '1' AFTER `sexe`;";
        $this->addQuery($query);

        $this->makeRevision('2.44');
        $query = "ALTER TABLE `patients`
                ADD `tel_autre_mobile` VARCHAR(20);";
        $this->addQuery($query);

        $this->makeRevision('2.45');
        $query = "ALTER TABLE `constantes_medicales`
                ADD `motricite_sup_d` FLOAT,
                ADD `motricite_sup_g` FLOAT;";
        $this->addQuery($query);
        $this->makeRevision('2.46');

        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir','other_prat') DEFAULT 'prevenir';";
        $this->addQuery($query);

        $this->makeRevision('2.47');

        $query = "ALTER TABLE `medecin`
                ADD `mssante_address` VARCHAR (100);";
        $this->addQuery($query);

        $this->makeRevision('2.48');
        $query = "ALTER TABLE `constantes_medicales`
      ADD `entree_hydrique` MEDIUMINT (9) UNSIGNED;";

        $this->addQuery($query);

        $this->makeRevision('2.49');

        $query = "CREATE TABLE `constant_comments` (
                `constant_comment_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `comment` TEXT NOT NULL,
                `constant` VARCHAR (30) NOT NULL,
                `constant_id` INT (11) UNSIGNED NOT NULL DEFAULT 0
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `constant_comments`
                ADD INDEX (`constant_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.50');
        $query = "CREATE TABLE `patient_group` (
                `patient_group_id`  INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id`          INT (11) UNSIGNED NOT NULL,
                `patient_id`        INT (11) UNSIGNED NOT NULL,
                `share`             ENUM('0', '1') NOT NULL DEFAULT '0',
                `last_modification` DATETIME NOT NULL,
                `user_id`           INT (11) UNSIGNED NOT NULL,
                INDEX (`group_id`),
                INDEX (`patient_id`),
                UNIQUE (`group_id`, `patient_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('2.51');

        $query = "ALTER TABLE `constantes_medicales`
                CHANGE `pres_artere_invasive` `pres_artere_inv_moy` INT(3),
                ADD `pres_artere_invasive` VARCHAR (10);";
        $this->addQuery($query);

        $this->makeRevision('2.52');
        $query = "ALTER TABLE `constantes_medicales`
                ADD `bilirubine_transcutanee` INT(4),
                ADD `bilicheck` INT(4);";
        $this->addQuery($query);
        $this->makeRevision('2.53');

        $query = "UPDATE `correspondant_patient` SET `relation` = 'inconnu' WHERE `relation` = 'other_prat';";
        $this->addQuery($query);
        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir') DEFAULT 'prevenir';";
        $this->addQuery($query);

        $this->makeRevision('2.54');

        $query = "ALTER TABLE `patients`
                ADD `acs` ENUM ('0', '1'),
                ADD `acs_type` ENUM ('none', 'a', 'b', 'c');";
        $this->addQuery($query);

        $this->makeRevision('2.55');

        $this->addFunctionalPermQuery("limit_prise_rdv", "0");

        $this->makeRevision('2.56');

        $query = "CREATE TABLE `evenement_patient` (
                `evenement_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `date` DATE,
                `libelle` VARCHAR (255),
                `description` TEXT,
                `dossier_medical_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `type_evenement_patient_id` INT (11) UNSIGNED,
                `owner_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `creation_date` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `evenement_patient`
                ADD INDEX (`date`),
                ADD INDEX (`dossier_medical_id`),
                ADD INDEX (`type_evenement_patient_id`),
                ADD INDEX (`owner_id`),
                ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `type_evenement_patient` (
                `type_evenement_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `function_id` INT (11) UNSIGNED,
                `libelle` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `type_evenement_patient`
                ADD INDEX (`function_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.57');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `redon_9` FLOAT UNSIGNED AFTER `redon_8`,
                ADD `redon_10` FLOAT UNSIGNED AFTER `redon_9`,
                ADD `redon_11` FLOAT UNSIGNED AFTER `redon_10`,
                ADD `redon_12` FLOAT UNSIGNED AFTER `redon_11`,
                ADD `redon_accordeon_3` FLOAT UNSIGNED AFTER `redon_accordeon_2`,
                ADD `redon_accordeon_4` FLOAT UNSIGNED AFTER `redon_accordeon_3`,
                ADD `redon_accordeon_5` FLOAT UNSIGNED AFTER `redon_accordeon_4`,
                ADD `redon_accordeon_6` FLOAT UNSIGNED AFTER `redon_accordeon_5`;";
        $this->addQuery($query);

        $this->makeRevision('2.58');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `evi` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('2.59');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `presence_urine` INT(1);";
        $this->addQuery($query);

        $this->makeRevision('2.60');

        $query = "ALTER TABLE `supervision_graph`
                ADD `display_legend` ENUM('0', '1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision('2.61');

        $query = "ALTER TABLE `supervision_timed_data`
                ADD `items` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('2.62');

        $query = "ALTER TABLE `supervision_timed_data`
                ADD `type` VARCHAR(6) NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `supervision_timed_data`
                SET `type` = 'enum'
                WHERE `items` IS NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `supervision_timed_data`
                SET `type` = 'str'
                WHERE `type` IS NULL OR `type` = '';";
        $this->addQuery($query);

        $this->makeRevision("2.63");

        $query = "ALTER TABLE `evenement_patient`
                ADD `praticien_id` INT (11) UNSIGNED,
                ADD `codes_ccam` VARCHAR (255),
                ADD `facture` ENUM ('0','1') DEFAULT '0',
                ADD `tarif` VARCHAR (255),
                ADD `exec_tarif` DATETIME,
                ADD `consult_related_id` INT (11) UNSIGNED,
                ADD INDEX (`praticien_id`),
                ADD INDEX (`exec_tarif`),
                ADD INDEX (`consult_related_id`);";
        $this->addQuery($query);
        $this->makeRevision("2.64");

        $query = "ALTER TABLE `evenement_patient`
                ADD `valide` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.65');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `meconium` INT(1);";
        $this->addQuery($query);
        $this->makeRevision('2.66');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `cheops` INT(2);";
        $this->addQuery($query);
        $this->makeRevision('2.67');

        $query = "UPDATE `configuration` SET `feature` = 'dPpatients CAntecedent create_antecedent_only_prat' WHERE  `feature` = 'soins Other create_antecedent_only_prat'";
        $this->addQuery($query);

        $this->makeRevision('2.68');

        $query = "ALTER TABLE `medecin` ADD `user_id` INT(11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("2.69");

        $this->addPrefQuery("display_all_docs", "icon");

        $this->makeRevision('2.70');

        $query = "ALTER TABLE `patients`
                ADD `ame_type` ENUM('ame', 'amec') DEFAULT 'ame' AFTER `ame`;";
        $this->addQuery($query);

        $this->makeRevision('2.71');

        $query = "ALTER TABLE `patients` 
                ADD `mdv_familiale` ENUM ('S','C','A'),
                ADD `niveau_etudes` ENUM ('ns','p','c','l','es'),
                ADD `ressources_financieres` ENUM ('tra','cho','rsa','api','non'),
                ADD `hebergement_precaire` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision('2.72');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `albuminemie` INT(2);";
        $this->addQuery($query);

        $this->makeRevision('2.73');

        $this->addFunctionalPermQuery('edit_constant_when_not_creator', '0');

        $this->makeRevision('2.74');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `co_expire` INT(2);";
        $this->addQuery($query);

        $this->makeRevision('2.75');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `bnp` INT (5),
                ADD `score_white` INT (2)";
        $this->addQuery($query);

        $this->makeRevision('2.76');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `dextro_maltose` INT (3),
                ADD `fenouil` INT (3),
                ADD `lait_artificiel` INT (3),
                ADD `lait_maternel` INT (3)";
        $this->addQuery($query);
        $this->makeRevision('2.77');

        $query = "ALTER TABLE `evenement_patient`
                ADD `rappel` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('2.78');

        $query = "ALTER TABLE `patients`
      ADD `tel_pro` VARCHAR (20) AFTER `tel_autre`;";
        $this->addQuery($query);

        $this->makeRevision('2.79');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `reliquat_perf` INT (4);";
        $this->addQuery($query);

        $this->makeRevision('2.80');

        $this->addPrefQuery('constants_table_orientation', 'vertical');
        $this->makeRevision('2.81');

        $query = "ALTER TABLE `patients`
                ADD `pharmacie_id` INT (11) UNSIGNED,
                ADD INDEX (`pharmacie_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.82');

        $query = "ALTER TABLE `patients`
                ADD `assure_rang_naissance` ENUM('1','2','3','4','5','6') AFTER `assure_matricule`;";
        $this->addQuery($query);

        $this->makeRevision('2.83');

        $query = "CREATE TABLE `dossier_medical_tiers` (
                `name` VARCHAR (255),
                `medecin_traitant_id` INT (11) UNSIGNED,
                `dossier_medical_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `codes_cim` TEXT,
                `risque_thrombo_patient` ENUM ('NR','faible','modere','eleve','majeur') DEFAULT 'NR',
                `risque_MCJ_patient` ENUM ('NR','sans','avec','suspect','atteint') DEFAULT 'NR',
                `facteurs_risque` TEXT,
                `absence_traitement` ENUM ('0','1') DEFAULT '0',
                `derniere_mapa` DATE,
                `risque_thrombo_chirurgie` ENUM ('NR','faible','modere','eleve') DEFAULT 'NR',
                `risque_antibioprophylaxie` ENUM ('NR','non','oui') DEFAULT 'NR',
                `risque_prophylaxie` ENUM ('NR','non','oui') DEFAULT 'NR',
                `risque_MCJ_chirurgie` ENUM ('NR','sans','avec') DEFAULT 'NR',
                `groupe_sanguin` ENUM ('?','O','A','B','AB') DEFAULT '?',
                `rhesus` ENUM ('?','NEG','POS') DEFAULT '?',
                `groupe_ok` ENUM ('0','1') DEFAULT '0',
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` ENUM ('CPatient','CSejour')
              )/*! ENGINE=MyISAM */;
            ALTER TABLE `dossier_medical_tiers`
                ADD INDEX (`medecin_traitant_id`),
                ADD INDEX (`derniere_mapa`),
                ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.84');

        $this->addPrefQuery('patient_recherche_avancee_par_defaut', '0');

        $this->makeRevision("2.85");

        $query = "ALTER TABLE `antecedent`
                ADD `doctor` VARCHAR (255),
                ADD `comment` TEXT,
                ADD `verified` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.86");

        $query = "DROP TABLE `dossier_medical_tiers`";
        $this->addQuery($query);

        $query = "CREATE TABLE `dossier_tiers` (
                `dossier_tiers_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255),
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` ENUM ('CPatient','CSejour')
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `dossier_tiers`
                ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `antecedent`
                ADD `dossier_tiers_id` INT (11) UNSIGNED,
                ADD INDEX (`dossier_tiers_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.87');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `variation_poids` VARCHAR (5);";
        $this->addQuery($query);

        $this->makeRevision('2.88');
        $query = "UPDATE `patients` SET `allow_sisra_send` = '1' WHERE `allow_sisra_send` = '0';";
        $this->addQuery($query);

        $this->makeRevision('2.89');

        $this->addMethod("alterObversationValueTablesPhase2");

        $this->makeRevision("2.90");
        $query = "ALTER TABLE `correspondant_patient`
              CHANGE `parente` `parente` ENUM ('ami','ascendant','autre','beau_fils',
                'colateral','collegue','compagnon','conjoint','curateur','directeur','divers',
                'employeur','employe','enfant','enfant_adoptif','entraineur','epoux',
                'frere','grand_parent','mere','pere','petits_enfants','proche',
                'proprietaire','soeur','tuteur');";
        $this->addQuery($query);

        $this->makeRevision('2.91');
        $query = "ALTER TABLE `constantes_medicales`
                ADD `sens_membre_sup_d` FLOAT UNSIGNED,
                ADD `sens_membre_sup_g` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("2.92");
        $query = "ALTER TABLE `antecedent`
                ADD `important` ENUM ('0','1') DEFAULT '0' AFTER `majeur`;";
        $this->addQuery($query);

        $this->makeRevision("2.93");

        $query = "ALTER TABLE `antecedent`
                CHANGE `verified` `verified` ENUM ('0','1','2') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.94");
        $query = "ALTER TABLE `supervision_graph_pack` 
                ADD `timing_fields` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('2.95');

        $query = "ALTER TABLE `constantes_medicales` ADD `conscience` INT (1);";
        $this->addQuery($query);

        $this->makeRevision('2.96');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `echelle_ops` INT (2),
                ADD `echelle_visage` INT (2),
                ADD `score_gir` INT (2),
                ADD `score_norton` INT (2),
                ADD `urines_residuelles` INT (4);";
        $this->addQuery($query);

        $this->makeRevision("2.97");
        $query = "ALTER TABLE `constantes_medicales`
      ADD `drain_pleural_3` FLOAT UNSIGNED AFTER `drain_pleural_2`,
      ADD `drain_pleural_4` FLOAT UNSIGNED AFTER `drain_pleural_3`";
        $this->addQuery($query);

        $this->makeRevision("2.98");
        $query = "ALTER TABLE `constantes_medicales`
      ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.99');
        $this->addPrefQuery('dPpatients_show_forms_resume', '0');

        $this->makeRevision('3.00');
        $query = "ALTER TABLE `constantes_medicales`
                ADD `TOF` FLOAT UNSIGNED,
                ADD `poids_forme` FLOAT UNSIGNED AFTER `poids`;";
        $this->addQuery($query);

        $this->makeRevision('3.01');

        $query = "CREATE TABLE  `patient_signature` (
                `patient_signature_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `patient_id` INT UNSIGNED NOT NULL ,
                `signature` VARCHAR( 100 ) NOT NULL ,
                INDEX (`patient_id`),
                INDEX (`signature`)
              ) /*! ENGINE=MyISAM */;";

        $this->addQuery($query);

        $query = "INSERT INTO `patient_signature` (`patient_id`, `signature`)
              SELECT `patient_id`, CONCAT_WS('_',`nom`,`prenom`,`naissance`) AS signature
              FROM `patients`
              WHERE `naissance` IS NOT NULL
              AND `nom` IS NOT NULL
              AND `prenom` IS NOT NULL;";

        $this->addQuery($query);

        $query = "INSERT INTO `patient_signature` (`patient_id`, `signature`)
              SELECT `patient_id`, CONCAT_WS('_',`nom_jeune_fille`,`prenom`,`naissance`) AS signature
              FROM `patients`
              WHERE `naissance` IS NOT NULL
              AND `nom_jeune_fille` IS NOT NULL
              AND `prenom` IS NOT NULL
              AND `nom_jeune_fille` != `nom`;";

        $this->addQuery($query);

        $query = "UPDATE `patient_signature` 
              SET signature = lower(signature),
              signature = replace(signature,'?','S'),
              signature = replace(signature,'?','s'),
              signature = replace(signature,'Ð','Dj'),
              signature = replace(signature,'?','Z'),
              signature = replace(signature,'À','A'),
              signature = replace(signature,'Á','A'),
              signature = replace(signature,'Â','A'),
              signature = replace(signature,'Ã','A'),
              signature = replace(signature,'Ä','A'),
              signature = replace(signature,'Å','A'),
              signature = replace(signature,'Æ','A'),
              signature = replace(signature,'Ç','C'),
              signature = replace(signature,'È','E'),
              signature = replace(signature,'É','E'),
              signature = replace(signature,'Ê','E'),
              signature = replace(signature,'Ë','E'),
              signature = replace(signature,'Ì','I'),
              signature = replace(signature,'Í','I'),
              signature = replace(signature,'Î','I'),
              signature = replace(signature,'Ï','I'),
              signature = replace(signature,'Ñ','N'),
              signature = replace(signature,'Ò','O'),
              signature = replace(signature,'Ó','O'),
              signature = replace(signature,'Ô','O'),
              signature = replace(signature,'Õ','O'),
              signature = replace(signature,'Ö','O'),
              signature = replace(signature,'Ø','O'),
              signature = replace(signature,'Ù','U'),
              signature = replace(signature,'Ú','U'),
              signature = replace(signature,'Û','U'),
              signature = replace(signature,'Ü','U'),
              signature = replace(signature,'Ý','Y'),  
              signature = replace(signature,'?','s'),
              signature = replace(signature,'Ð','Dj'),
              signature = replace(signature,'?','z'),
              signature = replace(signature,'ß','Ss'),
              signature = replace(signature,'à','a'),
              signature = replace(signature,'á','a'),
              signature = replace(signature,'â','a'),
              signature = replace(signature,'ã','a'),
              signature = replace(signature,'ä','a'),
              signature = replace(signature,'å','a'),
              signature = replace(signature,'æ','a'),
              signature = replace(signature,'ç','c'),
              signature = replace(signature,'è','e'),
              signature = replace(signature,'é','e'),
              signature = replace(signature,'ê','e'),
              signature = replace(signature,'ë','e'),
              signature = replace(signature,'ì','i'),
              signature = replace(signature,'í','i'),
              signature = replace(signature,'î','i'),
              signature = replace(signature,'ï','i'),
              signature = replace(signature,'ð','o'),
              signature = replace(signature,'ñ','n'),
              signature = replace(signature,'ò','o'),
              signature = replace(signature,'ó','o'),
              signature = replace(signature,'ô','o'),
              signature = replace(signature,'õ','o'),
              signature = replace(signature,'ö','o'),
              signature = replace(signature,'ø','o'),
              signature = replace(signature,'ù','u'),
              signature = replace(signature,'ú','u'),
              signature = replace(signature,'û','u'),
              signature = replace(signature,'ý','y'),
              signature = replace(signature,'ý','y'),
              signature = replace(signature,'þ','b'),
              signature = replace(signature,'ÿ','y'),
              signature = replace(signature,'?','f'),
              signature = replace(signature, X'BD', 'oe'),
              signature = replace(signature, ' ', ''),
              signature = replace(signature, '-', ''),
              signature = replace(signature, '\'', ''),
              signature = trim(signature);";

        $this->addQuery($query);

        $query = "ALTER TABLE `patient_link`
              MODIFY COLUMN `type` ENUM('DPOT', 'HOMA');";

        $this->addQuery($query);

        $this->makeRevision('3.02');
        $query = "ALTER TABLE `dossier_medical`
                ADD `absence_allergie` ENUM ('0','1') DEFAULT '0',
                ADD `absence_antecedent` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('3.03');

        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir','representant_th') DEFAULT 'prevenir';";
        $this->addQuery($query);

        $this->makeRevision("3.04");
        $query = "ALTER TABLE `constantes_medicales`
                ADD `ponction_ascite` FLOAT UNSIGNED AFTER `drain_jejunostomie`,
                ADD `ponction_pleurale` FLOAT UNSIGNED AFTER `ponction_ascite`";
        $this->addQuery($query);

        $this->makeRevision("3.05");
        $query = "ALTER TABLE `medecin` 
                ADD `spec_cpam_id` INT (11) UNSIGNED,
                ADD INDEX (`spec_cpam_id`);";
        $this->addQuery($query);

        $this->makeRevision("3.06");
        $query = "ALTER TABLE `dossier_tiers` 
                ADD `absence_traitement` ENUM ('0','1')";
        $this->addQuery($query);

        $this->makeRevision("3.07");
        $query = "ALTER TABLE `constantes_medicales`
                ADD `temps_irradiation` INT (11) UNSIGNED,
                ADD `taux_irradiation` FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("3.08");
        $query = "ALTER TABLE `correspondant_patient`
              ADD `tel_autre` VARCHAR (20) AFTER `fax`;";
        $this->addQuery($query);

        $this->makeRevision("3.09");
        $query = "ALTER TABLE `constantes_medicales`
            DROP `temps_irradiation`,
            DROP `taux_irradiation`;";
        $this->addQuery($query);

        $this->makeRevision("3.10");
        $query = "ALTER TABLE `supervision_graph_pack` 
                ADD `use_contexts` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("3.11");
        $query = "ALTER TABLE `supervision_graph_series` 
                ADD `display_ratio_time` FLOAT,
                ADD `display_ratio_value` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("3.12");
        $query = "ALTER TABLE `medecin`
              ADD `tel_autre` VARCHAR (20) AFTER `fax`;";
        $this->addQuery($query);

        $this->makeRevision('3.13');
        $this->addMethod('setPhoneAreaCodeConfig');

        $query = "ALTER TABLE `patients`
                ADD `phone_area_code` INT (2) UNSIGNED AFTER `cp`;";
        $this->addQuery($query);

        $this->makeRevision('3.14');

        $query = "ALTER TABLE `type_evenement_patient` ADD `notification` ENUM('0', '1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('3.15');

        $query = "ALTER TABLE `patients` 
              ADD `validated` ENUM('0', '1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('3.16');

        $query = "UPDATE `evenement_patient` SET `date` = CONCAT(SUBSTRING(`date`, 1, 5), '01', SUBSTRING(`date`, 8, 3)) WHERE SUBSTRING(`date`, 6, 2) = '00';";
        $this->addQuery($query);

        $query = "UPDATE `evenement_patient` SET `date` = CONCAT(SUBSTRING(`date`, 1, 8), '01') WHERE SUBSTRING(`date`, 9, 2) = '00';";
        $this->addQuery($query);

        $this->makeRevision('3.17');

        $this->addMethod("initObversationValuesToConstantTable");

        $this->makeRevision("3.18");
        $query = "ALTER TABLE `supervision_graph_pack` ADD `planif_display_mode` ENUM ('token','in_place') DEFAULT 'token';";
        $this->addQuery($query);

        $this->makeRevision('3.19');

        $query = "ALTER TABLE `constantes_medicales`
      ADD `tshus` FLOAT UNSIGNED,
      ADD `crp` FLOAT UNSIGNED,
      ADD `ferritnemie` FLOAT UNSIGNED,
      ADD `psa` FLOAT UNSIGNED,
      ADD `vs` FLOAT UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision('3.20');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `poids_moyen` FLOAT UNSIGNED AFTER `poids_forme`,
                ADD `mdrd` FLOAT UNSIGNED AFTER `clair_creatinine`,
                ADD `cockroft` FLOAT UNSIGNED AFTER `clair_creatinine`,
                ADD `prealbuminemie` TINYINT (4) UNSIGNED AFTER `albuminemie`;";
        $this->addQuery($query);
        $this->makeRevision('3.21');

        $query = "CREATE TABLE `patient_family_link` (
                `patient_family_link_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `parent_id_1` INT (11) UNSIGNED,
                `parent_id_2` INT (11) UNSIGNED,
                `type` ENUM ('civil','biologique') DEFAULT 'biologique'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `patient_family_link` 
                ADD INDEX (`parent_id_1`),
                ADD INDEX (`parent_id_2`),
                ADD INDEX (`patient_id`);";
        $this->addQuery($query);

        $this->makeRevision('3.22');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `nausea` INT(2),
                ADD `vomiting` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("3.23");
        $query = "ALTER TABLE `dossier_medical`
                ADD `examen_pieds` DATE AFTER `derniere_mapa`,
                ADD `examen_fond_oeil` DATE AFTER `examen_pieds`,
                ADD `derniere_score_framingham` DATE AFTER `examen_fond_oeil`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `dossier_medical`
                ADD INDEX (`examen_pieds`),
                ADD INDEX (`examen_fond_oeil`),
                ADD INDEX (`derniere_score_framingham`);";
        $this->addQuery($query);
        $this->makeRevision("3.24");

        $query = "ALTER TABLE `correspondant_patient` 
      CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir','representant_th','transport') DEFAULT 'prevenir'";
        $this->addQuery($query);

        $this->makeRevision("3.25");
        $query = "ALTER TABLE `dossier_medical`
                ADD `cancer_colorectal` DATE AFTER `derniere_score_framingham`,
                ADD `frottis` DATE AFTER `cancer_colorectal`,
                ADD `form_reperage_tabac` DATE AFTER `frottis`,
                ADD `form_reperage_alcool` DATE AFTER `form_reperage_tabac`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `dossier_medical`
                ADD INDEX (`cancer_colorectal`),
                ADD INDEX (`frottis`),
                ADD INDEX (`form_reperage_tabac`),
                ADD INDEX (`form_reperage_alcool`);";
        $this->addQuery($query);

        $this->makeRevision("3.26");
        $query = "ALTER TABLE `patients`
                ADD `don_organes` ENUM ('non_renseigne','accord','pas_accord') DEFAULT 'non_renseigne' AFTER `tutelle`;";
        $this->addQuery($query);

        $this->makeRevision("3.27");
        $query = "ALTER TABLE `medecin`
                ADD `ignore_import_rpps` ENUM ('0', '1')  DEFAULT '0'";
        $this->addQuery($query);

        $query = "CREATE TABLE `medecin_conflict` (
                `medecin_conflict_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `medecin_id` INT (11) UNSIGNED NOT NULL,
                `field` VARCHAR (255) NOT NULL,
                `value` VARCHAR (255),
                `audit` ENUM ('0','1') DEFAULT '1'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin_conflict` 
                ADD INDEX (`medecin_id`),
                ADD INDEX (`field`);";
        $this->addQuery($query);

        $this->makeRevision("3.28");
        $query = "ALTER TABLE `medecin_conflict` 
                ADD `file_version` VARCHAR (80);";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin`
                ADD `import_file_version` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision('3.29');

        $query = "ALTER TABLE `constantes_medicales` ADD `alcoolemie` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('3.30');
        $this->addPrefQuery("vue_globale_importance", "");
        $this->addPrefQuery("vue_globale_cats", "");
        $this->addPrefQuery("vue_globale_docs_prat", "");
        $this->addPrefQuery("vue_globale_docs_func", "");

        $this->makeRevision('3.31');

        $query = "ALTER TABLE `patients` CHANGE `qual_beneficiaire` `qual_beneficiaire` 
                ENUM ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '00', '01', '02', '03', '04', '05', '06', '07', '08', '09')";
        $this->addQuery($query);

        $query = "UPDATE `patients` SET `qual_beneficiaire` = LPAD(`qual_beneficiaire`, 2, '0');";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients` CHANGE `qual_beneficiaire` `qual_beneficiaire` 
                ENUM ('00', '01', '02', '03', '04', '05', '06', '07', '08', '09')";
        $this->addQuery($query);

        $this->makeRevision("3.33");
        $query = "ALTER TABLE `patients`
                ADD `assure_naissance_amo` CHAR ( 10 ) NULL DEFAULT NULL AFTER `assure_naissance`;";
        $this->addQuery($query);

        $this->makeRevision("3.34");
        $query = "ALTER TABLE `antecedent`
                ADD `origin` ENUM ('autre','patient','labo') DEFAULT 'patient',
                ADD `origin_autre` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("3.35");
        $query = "ALTER TABLE `patients` 
      ADD `creator_id` INT (11) UNSIGNED AFTER `function_id`,
      ADD `creation_date` DATETIME AFTER `creator_id`;";
        $this->addQuery($query);

        $this->makeRevision("3.36");

        $query = "ALTER TABLE `antecedent`
                ADD `absence` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("3.37");

        $query = "CREATE TABLE `programme_clinique` (
                `programme_clinique_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255) NOT NULL,
                `coordinateur_id` INT (11) UNSIGNED NOT NULL,
                `description` TEXT,
                `annule` ENUM ('0','1') DEFAULT '0'
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `programme_clinique` 
                ADD INDEX (`coordinateur_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `inclusion_programme` (
                `inclusion_programme_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `programme_clinique_id` INT (11) UNSIGNED NOT NULL,
                `date_debut` DATE,
                `date_fin` DATE,
                `commentaire` TEXT
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `inclusion_programme` 
                ADD INDEX (`patient_id`),
                ADD INDEX (`programme_clinique_id`),
                ADD INDEX (`date_debut`),
                ADD INDEX (`date_fin`);";
        $this->addQuery($query);

        $this->makeRevision('3.38');

        $query = "ALTER TABLE `constantes_medicales` ADD `bromage_scale` INT(1);";
        $this->addQuery($query);

        $this->makeRevision("3.39");

        $query = "CREATE TABLE `inclusion_programme_line` (
                `inclusion_programme_line_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `inclusion_programme_id` INT (11) UNSIGNED NOT NULL,
                `line_class` ENUM ('CPrescriptionLineMedicament','CPrescriptionLineMix','CPrescriptionLineElement'),
                `line_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `inclusion_programme_line` 
                ADD INDEX (`inclusion_programme_id`),
                ADD INDEX line (line_class, line_id);";
        $this->addQuery($query);

        $this->makeRevision("3.40");

        $query = "ALTER TABLE `patients` 
                ADD `activite_pro` ENUM ('a','c','f','cp','e','i','r'),
                ADD `fatigue_travail` ENUM ('0','1') DEFAULT '0',
                ADD `travail_hebdo` INT (11) UNSIGNED,
                ADD `transport_jour` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('3.41');

        $query = "ALTER TABLE `dossier_medical`
      CHANGE `risque_MCJ_patient` `risque_MCJ_patient` ENUM ('aucun', 'possible', 'sans','avec','suspect','atteint','NR') DEFAULT 'NR';";
        $this->addQuery($query);

        $this->makeRevision("3.42");
        $query = "ALTER TABLE `patients` 
                ADD `directives_anticipees` ENUM ('0','1') DEFAULT '0' AFTER `don_organes`";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_patient` 
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','detenteur','employeur','inconnu','prevenir','representant_th','transport') DEFAULT 'prevenir'";
        $this->addQuery($query);

        $query = "CREATE TABLE `directive_anticipee` (
                `directive_anticipee_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `description` TEXT,
                `date_recueil` DATE NOT NULL,
                `date_validite` DATE,
                `correspondant_patient_id` INT (11) UNSIGNED NOT NULL,
                 INDEX (`patient_id`),
                 INDEX (`date_recueil`),
                 INDEX (`date_validite`),
                 INDEX (`correspondant_patient_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("3.43");
        $query = "CREATE TABLE `verrou_dossier_patient` (
                `verrou_dossier_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `date` DATETIME,
                `motif` TEXT,
                `coordonnees` TEXT,
                `medical` ENUM ('0','1') DEFAULT '0',
                `administratif` ENUM ('0','1') DEFAULT '0',
                `doc_hash` VARCHAR (255),
                `annule` ENUM ('0','1') DEFAULT '0',
                `annule_user_id` INT (11) UNSIGNED,
                `annule_motif` TEXT,
                `annule_date` DATETIME,
                 INDEX (`patient_id`),
                 INDEX (`user_id`),
                 INDEX (`date`),
                 INDEX (`annule_user_id`),
                 INDEX (`annule_date`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("3.44");

        $this->addPrefQuery("see_statut_patient", "1");

        $this->makeRevision("3.45");
        $query = "CREATE TABLE `search_criteria` (
                `search_criteria_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `title` VARCHAR (255) NOT NULL,
                `owner_id` INT (11) UNSIGNED NOT NULL,
                `created` DATETIME,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `date_min` DATETIME,
                `date_max` DATETIME,
                `patient_id` INT (11) UNSIGNED,
                `pat_name` VARCHAR (255),
                `sexe` ENUM ('m','f'),
                `age_min` INT (11),
                `age_max` INT (11),
                `medecin_traitant` INT (11) UNSIGNED,
                `medecin_traitant_view` VARCHAR (255),
                `only_medecin_traitant` ENUM ('0','1') DEFAULT '0',
                `rques` VARCHAR (255),
                `libelle_evenement` VARCHAR (255),
                `section_choose` ENUM ('consult','sejour','operation') DEFAULT 'consult',
                `motif` VARCHAR (255),
                `rques_consult` VARCHAR (255),
                `examen_consult` VARCHAR (255),
                `conclusion` VARCHAR (255),
                `libelle` VARCHAR (255),
                `type` ENUM ('comp','ambu','exte','seances','ssr','psy','urg','consult'),
                `rques_sejour` VARCHAR (255),
                `convalescence` VARCHAR (255),
                `libelle_interv` VARCHAR (255),
                `rques_interv` VARCHAR (255),
                `examen` VARCHAR (255),
                `materiel` VARCHAR (255),
                `exam_per_op` VARCHAR (255),
                `codes_ccam` VARCHAR (255),
                `produit` VARCHAR (255),
                `code_cis` INT (8) UNSIGNED ZEROFILL,
                `code_ucd` INT (7) UNSIGNED ZEROFILL,
                `libelle_produit` VARCHAR (255),
                `classes_atc` VARCHAR (7),
                `composant` INT (11),
                `keywords_composant` VARCHAR (255),
                `indication` VARCHAR (255),
                `keywords_indication` VARCHAR (255),
                `type_indication` INT (11),
                `commentaire` VARCHAR (255),
                INDEX (`owner_id`),
                INDEX (`created`),
                INDEX (`user_id`),
                INDEX (`date_min`),
                INDEX (`date_max`),
                INDEX (`patient_id`),
                INDEX (`medecin_traitant`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('3.46');

        $query = "RENAME TABLE `medecin_conflict` TO `import_conflict`";
        $this->addQuery($query);

        $query = "ALTER TABLE `import_conflict`
                CHANGE COLUMN `medecin_conflict_id` `import_conflict_id` INT (11) UNSIGNED NOT NULL auto_increment,
                CHANGE COLUMN `medecin_id` `object_id` INT (11) UNSIGNED NOT NULL,
                ADD COLUMN `object_class` VARCHAR (25) NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `import_conflict` SET `object_class` = 'CMedecin' WHERE `object_class` IS NULL OR `object_class` = '';";
        $this->addQuery($query);

        $this->makeRevision('3.47');
        $query = "ALTER TABLE `correspondant_patient` 
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir','representant_th','transport') DEFAULT 'prevenir';";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients` 
                CHANGE `directives_anticipees` `directives_anticipees` ENUM ('1','0','unknown') DEFAULT 'unknown';";
        $this->addQuery($query);

        $query = "ALTER TABLE `directive_anticipee` 
                CHANGE `correspondant_patient_id` `detenteur_id` INT (11) UNSIGNED NOT NULL,
                ADD `detenteur_class` ENUM ('CCorrespondant', 'CCorrespondantPatient', 'CPatient', 'CMedecin'),
                ADD INDEX detenteur (detenteur_class, detenteur_id);";
        $this->addQuery($query);

        $this->makeRevision('3.48');

        $query = "ALTER TABLE `import_conflict`
                ADD COLUMN `import_tag` VARCHAR (50);";
        $this->addQuery($query);

        $this->makeRevision("3.49");
        $query = "ALTER TABLE `dossier_medical`
                ADD `conduites_addictives` ENUM ('0','1') DEFAULT '0'";
        $this->addQuery($query);
        $this->makeRevision("3.50");

        $query = "CREATE TABLE `evenement_alert_user` (
                `alert_user_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `object_class` ENUM ('CRegleAlertePatient','CEvenementPatient') NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                INDEX object (object_class, object_id),
                INDEX (`user_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `evenement_patient` 
                ADD `alerter` ENUM ('0','1') DEFAULT '0',
                ADD `regle_id` INT (11) UNSIGNED,
                ADD `traitement_user_id` INT (11) UNSIGNED,
                ADD INDEX (`regle_id`),
                ADD INDEX (`traitement_user_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `regle_alerte_patient` (
                `regle_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `name` VARCHAR (255) NOT NULL,
                `age_operateur` ENUM ('sup','inf') NOT NULL,
                `age_valeur` INT (11) NOT NULL,
                `sexe` ENUM ('m','f'),
                `diagnostics` TEXT,
                `programme_clinique_id` INT (11) UNSIGNED,
                `nb_anticipation` INT (11) UNSIGNED NOT NULL,
                `periode_refractaire` INT (11) UNSIGNED NOT NULL,
                `actif` ENUM ('0','1') DEFAULT '1',
                INDEX (`group_id`),
                INDEX (`programme_clinique_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("3.51");
        $query = "ALTER TABLE `antecedent` 
                ADD `date_fin` DATE AFTER `date`,
                ADD INDEX (`date_fin`);";
        $this->addQuery($query);

        $this->makeRevision("3.52");
        $query = "ALTER TABLE `antecedent` 
                ADD `degree_certainty` ENUM ('undefined','unprobable','probable','proven','excluded','inexact','duplicate') DEFAULT 'undefined';";
        $this->addQuery($query);

        $this->makeRevision("3.53");
        $query = "CREATE TABLE `acte_snomed` (
                `acte_snomed_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelle` VARCHAR (255),
                `code` VARCHAR (255) NOT NULL,
                `text` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("3.54");

        $this->addMethod("alterObversationResultTablePhase6");

        $this->makeRevision("3.55");

        if ($this->columnExists('constantes_medicales', 'entree_apport_divers')) {
            $this->makeRevision("3.56");
            $query = "ALTER TABLE `constantes_medicales`
      DROP `entree_apport_divers`";
            $this->addQuery($query);
        } else {
            $this->makeEmptyRevision("3.56");
        }

        $this->makeRevision('3.57');

        $query = "ALTER TABLE `constantes_medicales`
      ADD `early_warning_signs` INT (2);";
        $this->addQuery($query);

        $this->makeRevision('3.58');

        $query = "ALTER TABLE `import_conflict` MODIFY `value` TEXT";
        $this->addQuery($query);

        $this->makeRevision('3.59');

        $query = "ALTER TABLE `pathologie`
                ADD `ald` ENUM ('0', '1'),
                ADD `code_ald` INT (2),
                ADD `code_cim10` VARCHAR (10);";
        $this->addQuery($query);

        $this->makeRevision("3.60");

        $query = "CREATE TABLE `bmr_bhre` (
      `bmr_bhre_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `patient_id` INT (11) UNSIGNED NOT NULL,
      `bmr` ENUM ('0','1','NR'),
      `bhre` ENUM ('0','1','NR'),
      `hospi_etranger` ENUM ('0','1','NR'),
      `rapatriement_sanitaire` ENUM ('0','1','NR'),
      `ancien_bhre` ENUM ('0','1','NR'),
      `bhre_contact` ENUM ('0','1','NR'),
      `bhre_contact_debut` DATE,
      `bhre_contact_fin` DATE
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `bmr_bhre` 
      ADD INDEX (`patient_id`),
      ADD INDEX (`bmr`),
      ADD INDEX (`bhre`),
      ADD INDEX (`bhre_contact`);";
        $this->addQuery($query);

        $this->makeRevision("3.61");

        $query = "ALTER TABLE `patients` 
                ADD `condition_hebergement` ENUM ('locataire','proprietaire','precaire'),
                ADD `activite_pro_date` DATE,
                ADD `activite_pro_rques` TEXT,
                ADD INDEX (`activite_pro_date`);";
        $this->addQuery($query);

        $this->makeRevision("3.62");

        $this->addMethod("alterObversationResultTablePhase7");

        $this->makeRevision("3.63");

        $this->addPrefQuery("alert_bmr_bhre", "0");

        $this->makeRevision("3.64");

        $this->addMethod("addIndexAdeliRppsIfNotExists");

        $this->makeRevision("3.65");

        $query = "ALTER TABLE `supervision_graph_axis` 
      ADD `actif` ENUM ('0','1') DEFAULT '1' AFTER `title`;";
        $this->addQuery($query);

        $this->makeRevision("3.66");

        $query = "ALTER TABLE `correspondant_patient`
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir','representant_th','transport','parent_proche','ne_pas_prevenir') DEFAULT 'prevenir';";
        $this->addQuery($query);

        $this->makeRevision("3.67");
        $query = "ALTER TABLE `patients`
                ADD `allow_email` ENUM ('0','1') DEFAULT '1',
                ADD `allow_pers_prevenir` ENUM ('0','1') DEFAULT '1',
                ADD `allow_pers_confiance` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("3.68");

        $query = "ALTER TABLE `antecedent` 
                MODIFY `origin` ENUM ('autre','patient','labo', 'crmedical') DEFAULT 'patient'";
        $this->addQuery($query);

        $this->makeRevision("3.69");

        $query = "ALTER TABLE `constantes_medicales`
      ADD `pH_urinaire` FLOAT UNSIGNED,
      MODIFY `sng` FLOAT,
      MODIFY `drain_gastrostomie` FLOAT,
      MODIFY `drain_jejunostomie` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("3.70");

        $query = "ALTER TABLE `medecin`
      ADD `modalite_publipostage` ENUM ('apicrypt','docapost','mail','mssante');";
        $this->addQuery($query);

        $this->makeRevision("3.71");
        $query = "DROP TABLE IF EXISTS `acte_snomed`;";
        $this->addQuery($query);

        $query = "CREATE TABLE `antecedent_snomed` (
                `antecedent_snomed_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `antecedent_id` INT (11) UNSIGNED,
                `code` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('3.72');

        $query = "ALTER TABLE `constantes_medicales` ADD `jackson` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision('3.73');

        $query = "ALTER TABLE `supervision_graph_pack` ADD `anesthesia_type` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('3.74');

        $query = "ALTER TABLE `supervision_graph_to_pack`
                CHANGE `graph_class` `graph_class` ENUM('CSupervisionGraph', 'CSupervisionTimedData', 'CSupervisionTimedPicture', 'CSupervisionInstantData', 'CSupervisionTable');";
        $this->addQuery($query);

        $query = "CREATE TABLE `supervision_tables` (
                `supervision_table_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `owner_class` ENUM ('CGroups') NOT NULL,
                `owner_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `title` VARCHAR (255) NOT NULL,
                `disabled` ENUM ('0', '1') NOT NULL DEFAULT '1',
                `sampling_frequency` ENUM ('1', '3', '5', '10', '15') DEFAULT '5'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `supervision_table_rows` (
                `supervision_table_row_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `supervision_table_id` INT (11) UNSIGNED NOT NULL,
                `title` VARCHAR (255) NOT NULL,
                `active` ENUM ('0', '1') NOT NULL DEFAULT '1',
                `color` VARCHAR (6),
                `value_type_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `value_unit_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('3.75');

        $this->addMethod("alterObversationValueTablesPhase3");

        $this->makeRevision('3.76');
        $query = "ALTER TABLE `constantes_medicales`
                ADD `scurasil_1` INT(4) UNSIGNED,
                ADD `scurasil_2` INT(4) UNSIGNED,
                ADD `psl`        INT(4) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision('3.77');
        $query = "ALTER TABLE `constantes_medicales`
                CHANGE `albuminemie` `albuminemie` FLOAT UNSIGNED,
                CHANGE `prealbuminemie` `prealbuminemie` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('3.78');
        $this->addDefaultConfig("dPpatients CPatient adult_age");

        $this->makeRevision("3.79");

        $query = "ALTER TABLE `patients`
      DROP `ame_type`";
        $this->addQuery($query);

        $this->makeRevision('3.80');

        $query = "ALTER TABLE `constantes_medicales`
                ADD `perspiration` INT(4) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision('3.81');

        $query = "UPDATE `patients`
      SET `medecin_traitant_declare` = NULL
      WHERE `medecin_traitant_declare` = '0';";
        $this->addQuery($query);

        $this->makeRevision('3.82');

        $query = "ALTER TABLE `patients`
      ADD `tel_refus` ENUM ('0','1') DEFAULT '0' AFTER `tel_pro`,
      ADD `email_refus` ENUM ('0','1') DEFAULT '0' AFTER `email`;";
        $this->addQuery($query);

        $this->makeRevision('3.83');

        $query = "ALTER TABLE `patients`
      ADD `assurance_invalidite` ENUM ('oui','non','en_cours') AFTER `mutuelle_types_contrat`,
      ADD `decision_assurance_invalidite` ENUM ('oui','non','en_cours') AFTER `assurance_invalidite`,
      ADD `niveau_prise_en_charge` ENUM ('leger','moyen','intensif') AFTER `decision_assurance_invalidite`;";
        $this->addQuery($query);

        $this->makeRevision('3.84');

        $query = "ALTER TABLE `supervision_tables` ADD `automatic_protocol` ENUM ('Kheops-Concentrator');";
        $this->addQuery($query);

        $this->makeRevision('3.85');

        $query = "ALTER TABLE `constantes_medicales` ADD `oms` TINYINT (4) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('3.86');

        $query = "ALTER TABLE `constantes_medicales` ADD `debitmetrie_urinaire` FLOAT (4) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('3.87');

        $query = "ALTER TABLE `correspondant_patient` 
                CHANGE `relation` `relation` ENUM ('assurance','autre','confiance','employeur','inconnu','prevenir',
                'representant_legal','representant_th','transport','parent_proche','ne_pas_prevenir') DEFAULT 'prevenir'";
        $this->addQuery($query);

        $this->makeRevision('3.88');

        $query = "ALTER TABLE `search_criteria` 
                ADD `hidden_list_antecedents_cim10` VARCHAR (255),
                ADD `antecedents_text` VARCHAR (255),
                ADD `allergie_text` VARCHAR (255),
                ADD `hidden_list_pathologie_cim10` VARCHAR (255),
                ADD `pathologie_text` VARCHAR (255),
                ADD `hidden_list_probleme_cim10` VARCHAR (255),
                ADD `probleme_text` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('3.89');

        $ds = CSQLDataSource::get('std');
        if (!$ds->hasField('pathologie', 'type')) {
            $query = "ALTER TABLE `pathologie` 
              ADD `type` ENUM ('probleme','pathologie') NOT NULL DEFAULT 'pathologie',
              MODIFY `pathologie` TEXT NOT NULL,
              ADD `resolu` ENUM ('0','1') DEFAULT '0'";
            $this->addQuery($query);
        }

        $this->makeRevision("3.90");

        $query = "ALTER TABLE `patients`
                MODIFY `status` ENUM ('VIDE','PROV','VALI','ANOM')";
        $this->addQuery($query);

        $query = "ALTER TABLE `patient_state`
                MODIFY `state` ENUM (
                  'VIDE','PROV','VALI','DPOT','ANOM','CACH','DOUB','DESA','DOUA','COLP','COLV','FILI','HOMD',
                  'HOMA','USUR','IDRA','RECD','IDVER') NOT NULL;";
        $this->addQuery($query);


        $this->makeRevision("3.91");

        $this->addDefaultConfig("dPpatients CPatient extended_print");
        $this->addDefaultConfig("dPpatients CPatient limit_char_search");
        $this->addDefaultConfig("dPpatients CPatient check_code_insee");
        $this->addDefaultConfig("dPpatients CMedecin medecin_strict");
        $this->addDefaultConfig("dPpatients sharing multi_group", "dPpatients CPatient multi_group");
        $this->addDefaultConfig("dPpatients sharing patient_data_sharing", "dPpatients CPatient patient_data_sharing");

        $this->makeRevision('3.92');

        $query = "ALTER TABLE `constantes_medicales` 
                ADD `inr` FLOAT UNSIGNED,
                ADD `taux_prothrombine` FLOAT UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("3.93");
        $query = "CREATE TABLE `value_enum` (
                `value` VARCHAR (255) NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `value_enum` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `value_int` (
                `value` INT (11) NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `value_int` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `value_text` (
                `value` TEXT NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `value_text` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `datetime_interval` (
                `min_value` DATETIME NOT NULL,
                `max_value` DATETIME NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `datetime_interval` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `state_interval` (
                `state` INT (11) NOT NULL,
                `min_value` DATETIME NOT NULL,
                `max_value` DATETIME NOT NULL,
                `created_datetime` DATETIME NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;
ALTER TABLE `state_interval` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `value_interval` (
                `min_value` INT (11) NOT NULL,
                `max_value` INT (11) NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `value_interval` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `value_float` (
                `value` FLOAT NOT NULL,
                `value_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `spec_id` INT (11) NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `value_float` 
                ADD INDEX (`releve_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `constant_releve` (
                `constant_releve_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `source` ENUM ('self','manuel','device','api') NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                `update` DATETIME,
                `context_id` INT (11) UNSIGNED,
                `context_class` VARCHAR (255),
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `validated` ENUM ('0','1') NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;
ALTER TABLE `constant_releve` 
                ADD INDEX (`patient_id`),
                ADD INDEX (`user_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`update`),
                ADD INDEX context (context_class, context_id);";
        $this->addQuery($query);

        $query = "CREATE TABLE `constant_spec` (
                `constant_spec_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `code` VARCHAR (255) NOT NULL,
                `name` VARCHAR (255) NOT NULL,
                `unit` VARCHAR (255) NOT NULL,
                `value_class` VARCHAR (255) NOT NULL,
                `category` VARCHAR (255) NOT NULL,
                `period` INT (11) NOT NULL,
                `min_value` VARCHAR (255),
                `max_value` VARCHAR (255),
                `list` VARCHAR (255),
                `alert_id` INT (11) UNSIGNED,
                `formule` VARCHAR (255),
                `active` ENUM ('0','1') NOT NULL DEFAULT '0',
                `type` INT (11) NOT NULL,
                `created_datetime` DATETIME NOT NULL
              )/*! ENGINE=MyISAM */;
ALTER TABLE `constant_spec` 
                ADD INDEX (`alert_id`),
                ADD INDEX (`code`),
                ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `constant_alert` (
                `constant_alert_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `spec_id` INT (11) UNSIGNED,
                `seuil_bas_1` VARCHAR (255),
                `seuil_bas_2` VARCHAR (255),
                `seuil_bas_3` VARCHAR (255),
                `seuil_haut_1` VARCHAR (255),
                `seuil_haut_2` VARCHAR (255),
                `seuil_haut_3` VARCHAR (255),
                `comment_bas_1` TEXT,
                `comment_bas_2` TEXT,
                `comment_bas_3` TEXT,
                `comment_haut_1`TEXT,
                `comment_haut_2`TEXT,
                `comment_haut_3` TEXT
              )/*! ENGINE=MyISAM */;
ALTER TABLE `constant_alert` 
                ADD INDEX (`spec_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `constant_spec` AUTO_INCREMENT=100000;";
        $this->addQuery($query);

        $this->makeRevision('3.94');

        $query = "ALTER TABLE `constantes_medicales` ADD `tam_manual` FLOAT UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("3.95");

        $query = "ALTER TABLE `patients`
                CHANGE `decision_assurance_invalidite` `decision_assurance_invalidite` ENUM ('partielle','totale')";
        $this->addQuery($query);

        $this->makeRevision("3.96");

        $query = "ALTER TABLE `constant_releve` 
                ADD `type` INT NOT NULL,
                ADD `created_datetime` DATETIME NOT NULL;
              ALTER TABLE `constant_releve` 
                ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $this->makeRevision("3.97");

        $query = "ALTER TABLE `patients` 
                MODIFY `cp` VARCHAR(15),
                MODIFY `cp_naissance` VARCHAR(15),
                MODIFY `assure_cp` VARCHAR(15),
                MODIFY `assure_cp_naissance` VARCHAR(15);";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_patient` MODIFY `cp` VARCHAR(15);";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin` MODIFY `cp` VARCHAR(15);";
        $this->addQuery($query);
        $this->makeRevision("3.98");

        $query = "ALTER TABLE `constantes_medicales` ADD `taille_reference` FLOAT UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("3.99");

        $query = "ALTER TABLE `antecedent` ADD `reaction_indesirable` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('4.00');

        $query = "ALTER TABLE `supervision_graph_series`
                ADD `import_sampling_frequency` ENUM('1', '2', '3', '5', '10', '15', '20', '30');";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_table_rows`
                ADD `import_sampling_frequency` ENUM('1', '2', '3', '5', '10', '15', '20', '30');";
        $this->addQuery($query);

        $this->makeRevision('4.01');

        $query = "ALTER TABLE `constantes_medicales` MODIFY `bilirubine_transcutanee` VARCHAR (20);";
        $this->addQuery($query);
        $this->makeRevision('4.02');

        $query = "ALTER TABLE `evenement_patient` 
                ADD `type` ENUM ('evt','sejour','intervention') DEFAULT 'evt';";
        $this->addQuery($query);

        $this->makeEmptyRevision("4.03");

        /*$query = "ALTER TABLE `antecedent`
                    ADD INDEX (`owner_id`),
                    ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `antecedent_snomed`
                    ADD INDEX (`antecedent_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `bmr_bhre`
                    ADD INDEX (`bhre_contact_debut`),
                    ADD INDEX (`bhre_contact_fin`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `constantes_medicales`
                    ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `datetime_interval`
                  ADD INDEX (`min_value`),
                  ADD INDEX (`max_value`),
                  ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `dossier_medical`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `dossier_tiers`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin`
                    ADD INDEX (`last_ldap_checkout`),
                    ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `observation_result`
                    ADD INDEX (`file_id`),
                    ADD INDEX (`label_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `observation_result_set`
                    ADD INDEX context (context_class, context_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `pathologie`
                    ADD INDEX (`owner_id`),
                    ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
                    ADD INDEX (`creator_id`),
                    ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `patient_group`
                    ADD INDEX (`last_modification`),
                    ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `state_interval`
                    ADD INDEX (`min_value`),
                    ADD INDEX (`max_value`),
                    ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_graph`
                    ADD INDEX owner (owner_class, owner_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_graph_pack`
                    ADD INDEX (`anesthesia_type`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_graph_to_pack`
                    ADD INDEX graph (graph_class, graph_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_tables`
                    ADD INDEX owner (owner_class, owner_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_table_rows`
                    ADD INDEX (`supervision_table_id`),
                    ADD INDEX (`value_type_id`),
                    ADD INDEX (`value_unit_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_timed_data`
                    ADD INDEX owner (owner_class, owner_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_timed_picture`
                    ADD INDEX (`value_type_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `traitement`
                    ADD INDEX (`owner_id`),
                    ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `value_enum`
                    ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `value_int`
                    ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `value_interval`
                    ADD INDEX (`created_datetime`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `value_text`
                    ADD INDEX (`created_datetime`);";
        $this->addQuery($query);*/

        $this->makeRevision('4.04');

        $query = "ALTER TABLE `supervision_graph_pack` 
                ADD `main_pack` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("4.05");

        $query = "ALTER TABLE `patients` 
                ADD `group_id` INT (11) UNSIGNED AFTER `function_id`;";
        $this->addQuery($query, true);

        $query = "ALTER TABLE `medecin` 
                ADD `group_id` INT (11) UNSIGNED AFTER `function_id`;";
        $this->addQuery($query, true);

        $query = "ALTER TABLE `correspondant_patient` 
                ADD `group_id` INT (11) UNSIGNED AFTER `function_id`";
        $this->addQuery($query, true);

        $this->makeRevision("4.06");

        $query = "ALTER TABLE `medecin`
                ADD FULLTEXT INDEX `seeker` (`nom`,`prenom`,`ville`,`disciplines`,`orientations`,`complementaires`)";
        $this->addQuery($query);
        $this->makeRevision("4.07");

        $query = "ALTER TABLE `evenement_patient` 
                ADD `parent_id` INT (11) UNSIGNED,
                ADD INDEX (`parent_id`);";
        $this->addQuery($query);

        $this->makeRevision('4.08');

        $query = "ALTER TABLE `supervision_graph_series`
                MODIFY `import_sampling_frequency` ENUM('1', '2', '3', '5', '10', '15', '20', '30') DEFAULT '5';";
        $this->addQuery($query);

        $query = "UPDATE `supervision_graph_series`
                SET `import_sampling_frequency` = '5' WHERE `import_sampling_frequency` IS NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `supervision_table_rows`
                MODIFY `import_sampling_frequency` ENUM('1', '2', '3', '5', '10', '15', '20', '30') DEFAULT '5';";
        $this->addQuery($query);

        $query = "UPDATE `supervision_table_rows`
                SET `import_sampling_frequency` = '5' WHERE `import_sampling_frequency` IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("4.09");

        $query = "ALTER TABLE `constant_spec` 
                ADD `alterable` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("4.10");

        $query = "ALTER TABLE `salutation` 
                ADD `tutoiement` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("4.11");

        $query = "CREATE TABLE `patient_ins_nir` (
                `patient_ins_nir_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `created_datetime` DATETIME NOT NULL,
                `last_update` DATETIME NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `ins_nir` VARCHAR (255) NOT NULL,
                `name` VARCHAR (255),
                `firstname` VARCHAR (255),
                `birthdate` CHAR (10),
                `provider` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `patient_ins_nir` 
                ADD INDEX (`created_datetime`),
                ADD INDEX (`last_update`),
                ADD INDEX (`patient_id`);";
        $this->addQuery($query);

        $this->makeRevision('4.12');

        $query = "ALTER TABLE `medecin` MODIFY `email_apicrypt` VARCHAR (100);";
        $this->addQuery($query);

        $this->makeEmptyRevision("4.13");

        $this->makeRevision("4.14");
        $this->setModuleCategory("dossier_patient", "metier");

        $this->makeRevision("4.15");

        $query = "ALTER TABLE `evenement_patient` ADD `cancel` enum('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("4.16");

        $query = "CREATE TABLE `redon` (
                `redon_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `sejour_id` INT (11) UNSIGNED,
                `constante_medicale` ENUM ('redon','redon_2','redon_3','redon_4','redon_5','redon_6','redon_7','redon_8','redon_9','redon_10','redon_11','redon_12','redon_accordeon_1','redon_accordeon_2','redon_accordeon_3','redon_accordeon_4','redon_accordeon_5','redon_accordeon_6','scurasil_1','scurasil_2','drain_1','drain_2','drain_3','lame_1','lame_2','lame_3','drain_orifice_1','drain_orifice_2','drain_orifice_3','drain_orifice_4','drain_pleural_1','drain_pleural_2','drain_pleural_3','drain_pleural_4','drain_thoracique_1','drain_thoracique_2','drain_thoracique_3','drain_thoracique_4','drain_mediastinal') NOT NULL,
                `actif` ENUM ('0','1') DEFAULT '1',
                `sous_vide` ENUM ('0','1') DEFAULT '0',
                `date_pose` DATETIME NOT NULL,
                `date_retrait` DATETIME
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `redon` 
                ADD INDEX (`sejour_id`),
                ADD INDEX (`date_pose`),
                ADD INDEX (`date_retrait`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `releve_redon` (
                `releve_redon_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `redon_id` INT (11) UNSIGNED NOT NULL,
                `date` DATETIME NOT NULL,
                `user_id` INT (11) UNSIGNED,
                `qte_observee` INT (11) UNSIGNED NOT NULL,
                `vidange_apres_observation` ENUM ('0','1') DEFAULT '0',
                `constantes_medicales_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `releve_redon` 
                ADD INDEX (`redon_id`),
                ADD INDEX (`date`),
                ADD INDEX (`user_id`),
                ADD INDEX (`constantes_medicales_id`);";
        $this->addQuery($query);

        $this->makeRevision("4.17");
        $this->addFunctionalPermQuery("allowed_to_edit_treatment", "0");
        $this->addFunctionalPermQuery("allowed_to_edit_atcd", "0");

        $this->makeRevision("4.18");

        $query = "ALTER TABLE `medecin`
               ADD `ean` VARCHAR (30);";
        $this->addQuery($query);
        $this->makeRevision("4.19");

        $query = "CREATE TABLE `releve_comment` (
                `releve_comment_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `releve_id` INT (11) UNSIGNED NOT NULL,
                `value_id` INT (11) UNSIGNED,
                `value_class` ENUM ('CDateTimeInterval','CStateInterval','CValueEnum','CValueFloat','CValueInt','CValueInterval','CValueText'),
                `comment` TEXT
              )/*! ENGINE=MyISAM */;
ALTER TABLE `releve_comment` 
                ADD INDEX (`releve_id`);";
        $this->addQuery($query);

        $this->makeRevision("4.20");

        $query = "ALTER TABLE `constantes_medicales`
                ADD `liquide_gastrique` MEDIUMINT (9) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("4.21");

        $query = "ALTER TABLE `supervision_timed_data` 
                ADD `column` TINYINT (4) UNSIGNED DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("4.22");

        $query = "ALTER TABLE `regle_alerte_patient` 
                CHANGE `group_id` `group_id` INT (11) UNSIGNED,
                ADD `function_id` INT (11) UNSIGNED,
                ADD `user_id` INT (11) UNSIGNED,
                ADD INDEX (`function_id`),
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision("4.23");

        $query = "ALTER TABLE `dossier_medical` 
                ADD `phenotype` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('4.24');

        $query = "DROP TABLE `devenir_dentaire`;";
        $this->addQuery($query);

        $query = "DROP TABLE `acte_dentaire`;";
        $this->addQuery($query);

        $this->makeRevision('4.25');

        $query = "ALTER TABLE `bmr_bhre` 
                ADD `bmr_debut` DATE,
                ADD `bmr_fin` DATE,
                ADD `bhre_debut` DATE,
                ADD `bhre_fin` DATE,
                ADD `hospi_etranger_debut` DATE,
                ADD `hospi_etranger_fin` DATE;";
        $this->addQuery($query);
        $this->makeRevision('4.26');

        $query = "ALTER TABLE `dossier_medical` 
                ADD `risque_viral` ENUM ('NR','aucun','possible') DEFAULT 'NR',
                ADD `risque_viral_rq` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("4.27");

        $query = "CREATE TABLE `consent_patient` (
                `consent_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `tag` INT (11),
                `status` ENUM ('5','6'),
                `acceptance_datetime` DATETIME,
                `refusal_datetime` DATETIME,
                `object_id` INT (11) UNSIGNED NOT NULL,
                `object_class` ENUM ('CPatient') NOT NULL,
                `group_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `consent_patient` 
                ADD INDEX (`acceptance_datetime`),
                ADD INDEX (`refusal_datetime`),
                ADD INDEX object (object_class, object_id),
                ADD INDEX (`group_id`);";

        $this->addQuery($query);

        $this->makeRevision('4.28');

        $query = "CREATE TABLE `medecin_exercice_place` (
                `medecin_exercice_place_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `medecin_id` INT (11) UNSIGNED NOT NULL,
                `siret` CHAR (14),
                `siren` VARCHAR (255),
                `finess` CHAR (9),
                `finess_juridique` CHAR (9),
                `id_technique` VARCHAR (255),
                `raison_sociale` VARCHAR (255),
                `enseigne_comm` VARCHAR (255),
                `comp_destinataire` VARCHAR (255),
                `comp_point_geo` VARCHAR (255),
                `adresse` VARCHAR (255),
                `cp` CHAR (5),
                `commune` VARCHAR (255),
                `pays` VARCHAR (255),
                `tel` VARCHAR (20),
                `tel2` VARCHAR (20),
                `fax` VARCHAR (20),
                `email` VARCHAR (255),
                `departement` VARCHAR (255),
                `annule` ENUM ('0','1') DEFAULT '0',
                INDEX (`medecin_id`),
                INDEX (`finess`),
                FULLTEXT INDEX `seeker` (`adresse`,`cp`,`commune`,`pays`,`departement`)
              )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision('4.29');

        $query = "ALTER TABLE `medecin_exercice_place` 
                ADD `rpps_file_version` VARCHAR(10) NOT NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin`
                ADD `categorie_professionnelle` ENUM ('civil','militaire','etudiant'),
                ADD `mode_exercice` ENUM ('liberal','salarie','benevole'),
                CHANGE `adeli` `adeli` VARCHAR(9);";
        $this->addQuery($query);

        $this->makeRevision('4.30');

        $query = "ALTER TABLE `constantes_medicales` ADD `origin` VARCHAR (255) AFTER `comment`;";
        $this->addQuery($query);

        $this->makeRevision('4.31');

        $query = "ALTER TABLE type_evenement_patient
	              ADD mailing_model_id INT(11) NULL;";
        $this->addQuery($query);

        $query = "CREATE TABLE `patientevent_sent_mail` (
                `patientevent_sent_mail_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_event_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME,
                `type` ENUM ('postal', 'email')
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("4.32");

        $query = "ALTER TABLE `medecin` 
            CHANGE `type` `type` ENUM ('medecin','pharmacie','audio','opticien','assistant_dent','dentiste',
                'assistant_service_social','sagefemme','infirmier','infirmierpsy','kine','osteo','psychotherapeute',
                'chiro','podologue','orthoprot','podoorth','ortho','oculariste','epithesiste','technicien',
                'orthophoniste','orthoptiste','psychologue','ergo','diete','psycho','maniperm',
                'maison_medicale','autre') DEFAULT 'medecin';";
        $this->addQuery($query);

        $this->makeRevision("4.33");

        $query = 'ALTER TABLE `patients` 
                ADD `prenoms` VARCHAR (255) AFTER `prenom_4`,
                ADD `prenom_usuel` VARCHAR (255) AFTER `prenoms`,
                ADD `assure_prenoms` VARCHAR (255) AFTER `assure_prenom_4`,
                ADD `commune_naissance_insee` INT (5) UNSIGNED ZEROFILL AFTER `cp_naissance`;';
        $this->addQuery($query);

        $query = 'UPDATE `patients`
                SET `nom_jeune_fille` = `nom`
                WHERE `nom_jeune_fille` IS NULL;';
        $this->addQuery($query);

        $query = "UPDATE `patients`
                SET `prenoms` = CONCAT_WS(' ', `prenom`, `prenom_2`, `prenom_3`, `prenom_4`),
                    `assure_prenoms` =
                        IF (
                            `assure_prenom` IS NULL AND `assure_prenom_2` IS NULL AND `assure_prenom_3` IS NULL AND `assure_prenom_4` IS NULL,
                            NULL,
                            CONCAT_WS(' ', `assure_prenom`, `assure_prenom_2`, `assure_prenom_3`, `assure_prenom_4`)
                        ),
                    `prenom_usuel` = `prenom`;";
        $this->addQuery($query);

        $query = 'ALTER TABLE `patients`
                DROP `prenom_2`,
                DROP `prenom_3`,
                DROP `prenom_4`,
                DROP `assure_prenom_2`,
                DROP `assure_prenom_3`,
                DROP `assure_prenom_4`;';
        $this->addQuery($query);

        $this->makeRevision("4.34");

        $query = "ALTER TABLE `patients`
                MODIFY COLUMN `status` ENUM ('VIDE','PROV','VALI','ANOM','RECUP','QUAL');";
        $this->addQuery($query);

        $query = "ALTER TABLE `patient_state`
                MODIFY COLUMN `state` ENUM ('VIDE','PROV','VALI','DPOT','ANOM','CACH','DOUB','DESA','DOUA','COLP','COLV','FILI','HOMD','HOMA','USUR','IDRA','RECD','IDVER','DOUT','FICTI') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("4.35");

        $query = "CREATE TABLE `source_identite` (
                `source_identite_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `selected` ENUM ('0','1') DEFAULT '0',
                `mode_obtention` ENUM ('manuel','carte_vitale','insi','code_barre','rfid','import','interop') NOT NULL,
                `type_justificatif` ENUM ('passeport','carte_identite','acte_naissance','carte_sejour','doc_asile','carte_identite_electronique'),
                `nom_naissance` VARCHAR (255) NOT NULL,
                `prenom_naissance` VARCHAR (255) NOT NULL,
                `prenoms` VARCHAR (255) NOT NULL,
                `prenom_usuel` VARCHAR (255),
                `date_naissance` CHAR (10) NOT NULL,
                `sexe` ENUM ('m','f','i'),
                `pays_naissance_insee` MEDIUMINT (3) UNSIGNED ZEROFILL NOT NULL,
                `commune_naissance_insee` INT (5) UNSIGNED ZEROFILL,
                `debut` DATE,
                `fin` DATE,
                 INDEX (`patient_id`),
                 INDEX (`debut`),
                 INDEX (`fin`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("4.36");

        $query = 'ALTER TABLE `source_identite`
                ADD `cp_naissance` INT (5) UNSIGNED ZEROFILL AFTER `commune_naissance_insee`;';
        $this->addQuery($query);

        $query = 'ALTER TABLE `patient_ins_nir` 
                ADD `oid` VARCHAR (255) AFTER `ins_nir`,
                ADD `source_identite_id` INT (11) UNSIGNED AFTER `oid`,
                ADD INDEX (`source_identite_id`);';
        $this->addQuery($query);

        $query = "INSERT INTO `patient_state` (`patient_id`, `mediuser_id`, `state`, `datetime`)
              SELECT `patient_id`, '" . CAppUI::$instance->user_id . "', 'ANOM', NOW()
              FROM `patients`
              WHERE `status` = 'ANOM';";
        $this->addQuery($query);

        $query = "UPDATE `patients`
              SET `status` = 'PROV'
              WHERE `status` = 'ANOM'";
        $this->addQuery($query);

        $query = "ALTER TABLE `patients`
                CHANGE `status` `status` ENUM ('VIDE','PROV','VALI','RECUP','QUAL');";
        $this->addQuery($query);

        $this->makeRevision("4.37");

        $query = 'ALTER TABLE `source_identite`
              MODIFY COLUMN `pays_naissance_insee` MEDIUMINT (3) UNSIGNED ZEROFILL,
              MODIFY COLUMN `commune_naissance_insee` CHAR (5)';
        $this->addQuery($query);

        $query = 'ALTER TABLE `patients`
              MODIFY COLUMN `commune_naissance_insee` CHAR (5);';
        $this->addQuery($query);

        $this->makeRevision('4.38');

        $query = "ALTER TABLE `patient_ins_nir` 
                ADD `is_nia` ENUM ('0','1') DEFAULT '0' AFTER `oid`;";
        $this->addQuery($query);

        $this->makeRevision('4.39');

        $query = "ALTER TABLE `patients` 
                ADD `source_identite_id` INT (11) UNSIGNED AFTER `creation_date`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_identite`
                MODIFY COLUMN `type_justificatif` ENUM ('passeport','carte_identite','acte_naissance','carte_sejour','doc_asile','carte_identite_electronique'),
                ADD `date_naissance_corrigee` ENUM ('0', '1') DEFAULT '0' AFTER `date_naissance`,
                ADD `date_fin_validite` DATE AFTER `type_justificatif`,
                ADD `active` ENUM ('0','1') DEFAULT '0' AFTER `patient_id`,
                ADD `nom` VARCHAR(50) NOT NULL AFTER `type_justificatif`";
        $this->addQuery($query);

        $query = "UPDATE `patients`
              LEFT JOIN `source_identite` ON (`source_identite`.`patient_id` = `patients`.`patient_id`
              AND `source_identite`.`selected` = '1') 
              SET `patients`.`source_identite_id` = `source_identite`.`source_identite_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `source_identite` 
              DROP `selected`";
        $this->addQuery($query);

        $this->makeRevision('4.40');

        $query = "ALTER TABLE `constantes_medicales`
            ADD `bilirubine_totale_sanguine` INT(11);";
        $this->addQuery($query);

        $this->makeRevision('4.41');

        $query = "ALTER TABLE `medecin`
          ADD `authorize_booking_new_patient` BOOL,
          ADD `use_online_appointment_booking` BOOL;";
        $this->addQuery($query);

        $this->makeRevision('4.42');

        $query = "TRUNCATE TABLE `medecin_exercice_place`";
        $this->addQuery($query);

        $query = "ALTER TABLE `medecin_exercice_place`
                DROP `siret`,
                DROP `siren`,
                DROP `finess`,
                DROP `finess_juridique`,
                DROP `id_technique`,
                DROP `raison_sociale`,
                DROP `enseigne_comm`,
                DROP `comp_destinataire`,
                DROP `comp_point_geo`,
                DROP `adresse`,
                DROP `cp`,
                DROP `commune`,
                DROP `pays`,
                DROP `tel`,
                DROP `tel2`,
                DROP `fax`,
                DROP `email`,
                DROP `departement`,
                DROP `annule`,
                ADD COLUMN exercice_place_id INT(11) NOT NULL,
                ADD INDEX exercice_place_id (exercice_place_id)";
        $this->addQuery($query);

        $query = "CREATE TABLE `exercice_place` (
                `exercice_place_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `exercice_place_identifier` VARCHAR(50) NOT NULL,
                `siret` CHAR (14),
                `siren` VARCHAR (255),
                `finess` CHAR (9),
                `finess_juridique` CHAR (9),
                `id_technique` VARCHAR (255),
                `raison_sociale` VARCHAR (255),
                `enseigne_comm` VARCHAR (255),
                `comp_destinataire` VARCHAR (255),
                `comp_point_geo` VARCHAR (255),
                `adresse` VARCHAR (255),
                `cp` CHAR (5),
                `commune` VARCHAR (255),
                `pays` VARCHAR (255),
                `tel` VARCHAR (20),
                `tel2` VARCHAR (20),
                `fax` VARCHAR (20),
                `email` VARCHAR (255),
                `departement` VARCHAR (255),
                `annule` ENUM ('0','1') DEFAULT '0',
                `rpps_file_version` VARCHAR(80),
                INDEX (`finess`),
                INDEX (`siret`),
                INDEX (`siren`),
                INDEX (`id_technique`),
                INDEX (`rpps_file_version`),
                UNIQUE INDEX `exercice_place_identifier` (exercice_place_identifier),
                FULLTEXT INDEX `seeker` (`adresse`,`cp`,`commune`,`pays`,`departement`)
            )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision('4.43');

        $query = "ALTER TABLE `medecin_exercice_place` 
                ADD UNIQUE INDEX `medecin_exercice_place` (medecin_id, exercice_place_id)";
        $this->addQuery($query);

        $this->makeRevision('4.44');

        $query = "ALTER TABLE `patients` 
                CHANGE `nom_jeune_fille` `nom_jeune_fille` VARCHAR (255) NOT NULL";
        $this->addQuery($query);

        $this->makeRevision('4.45');
        $query = "
      ALTER TABLE `medecin` 
              ADD `authorize_teleconsultation` BOOL DEFAULT '1' ;
      ";
        $this->addQuery($query);

        $this->makeRevision('4.46');

        $query = "CREATE TABLE `patient_handicap`(
        `patient_handicap_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `handicap` ENUM ('moteur', 'psychique', 'autonome', 'fauteuil', 'besoin_aidant', 'mal_entendant', 'mal_voyant') NOT NULL,
        `patient_id` INT (11) UNSIGNED NOT NULL
    )/*! ENGINE=MyISAM */";

        $this->addQuery($query);

        $this->makeRevision('4.47');

        $query = "ALTER TABLE `constantes_medicales` 
              ADD `unite_glycemie` ENUM ('g/l','mmol/l','mg/dl','µmol/l') DEFAULT 'g/l';";
        $this->addQuery($query);

        $this->makeRevision('4.48');

        $query = "ALTER TABLE `medecin` 
                ALTER `authorize_teleconsultation` SET DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `medecin` 
                SET `authorize_teleconsultation` = '0';";
        $this->addQuery($query);

        $this->makeRevision('4.49');

        if (!CModule::getActive('appFine')) {
            $query = "ALTER TABLE `exercice_place` 
                ADD INDEX cp (`cp`),
                ADD INDEX commune (`commune`),
                ADD INDEX raison_sociale (`raison_sociale`);";
            $this->addQuery($query);
        }

        $this->makeRevision('4.50');

        $query = "ALTER TABLE `source_identite`
                MODIFY COLUMN `type_justificatif` ENUM ('passeport','carte_identite','acte_naissance','livret_famille','carte_sejour','doc_asile','carte_identite_electronique');";
        $this->addQuery($query);

        $this->makeRevision('4.51');

        $query = "ALTER TABLE `constantes_medicales` 
              ADD `edin` TINYINT (4) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision('4.52');

        if (!$ds->hasField('medecin', 'full_name')) {
            // create field
            $query = "ALTER TABLE `medecin` 
                ADD `full_name` VARCHAR (255);";
            $this->addQuery($query);

            // add index
            $query = "ALTER TABLE `medecin` 
                ADD INDEX (`full_name`);";
            $this->addQuery($query);

            // hydrate field
            $query = "UPDATE medecin 
                    SET `full_name` = CONCAT(`medecin`.`nom`, ' ', `medecin`.`prenom`);";
            $this->addQuery($query);

            // add FULL TEXT index on raison_sociale
            $query = "ALTER TABLE `exercice_place` ADD FULLTEXT INDEX `full_raison_sociale` (raison_sociale);";
            $this->addQuery($query);
        }

        $this->makeRevision('4.53');

        $query = "ALTER TABLE `exercice_place`
                ADD `code_commune` VARCHAR(5),
                ADD INDEX (`code_commune`)";
        $this->addQuery($query);

        $this->makeRevision('4.54');

        if (!$ds->hasField('exercice_place', 'use_online_appointment_booking')) {
            // create field
            $query = "ALTER TABLE `exercice_place`
                ADD `use_online_appointment_booking` BOOL DEFAULT '0';";
            $this->addQuery($query);
        }

        $this->makeRevision('4.55');

        $query = "ALTER TABLE `patient_signature`
                    ADD INDEX pat_signature (`signature`, `patient_id`, `patient_signature_id`)";
        $this->addQuery($query);

        $this->makeRevision('4.57');

        if (!$ds->hasField('exercice_place', 'visibility_booking')) {
            // create field
            $query = "ALTER TABLE `exercice_place`
                ADD `visibility_booking` INT(1) DEFAULT '0';";
            $this->addQuery($query);
        }

        $this->makeRevision('4.58');
        $query = "ALTER TABLE `constantes_medicales`
                DROP COLUMN `saturation_air`;";
        $this->addQuery($query);

        $this->makeRevision('4.59');

        $query = "ALTER TABLE `patients` 
            MODIFY `allow_email` ENUM ('0','1','2') DEFAULT '1'";

        $this->addQuery($query);

        $this->makeRevision('4.60');

        $query = "ALTER TABLE `source_identite`
                MODIFY COLUMN `type_justificatif`
                ENUM ('passeport','carte_identite','acte_naissance','livret_famille','carte_sejour','doc_asile',
                      'carte_identite_electronique', 'absence_justificatif');";
        $this->addQuery($query);

        $this->makeRevision('4.61');
        $query = "ALTER TABLE `constantes_medicales`
                ADD `poids_avant_grossesse` FLOAT UNSIGNED AFTER `poids`;";
        $this->addQuery($query);

        $this->makeRevision('4.62');

        if (!CModule::getActive('appFine')) {
            $query = "ALTER TABLE `medecin` ADD FULLTEXT INDEX `full_text_full_name` (full_name);";
            $this->addQuery($query);
        }

        $this->makeRevision('4.63');

        $this->addQuery("ALTER TABLE patients CHANGE cmu c2s ENUM('0', '1') DEFAULT '0';");

        $this->makeRevision('4.64');

        $query = "ALTER TABLE `medecin_exercice_place`
                   ADD `type` VARCHAR(50),
                   ADD `disciplines` TEXT,
                   ADD `categorie_pro` ENUM ('civil','militaire','etudiant'),
                   ADD `mode_exercice` ENUM ('liberal','salarie','benevole'),
                   ADD `mssante_address` VARCHAR (100),
                  CHANGE `exercice_place_id` `exercice_place_id` INT(11);";
        $this->addQuery($query);

        $this->makeRevision('4.65');

        $this->addQuery(
            'ALTER TABLE `patients`
            ADD `medecin_traitant_exercice_place_id` INT (11) UNSIGNED AFTER `medecin_traitant`,
            ADD INDEX (`medecin_traitant_exercice_place_id`);'
        );

        $this->addQuery(
            'ALTER TABLE `correspondant` 
             ADD `medecin_exercice_place_id` INT (11) UNSIGNED AFTER `medecin_id`,
             ADD INDEX (`medecin_exercice_place_id`);'
        );

        $this->makeRevision('4.66');

        $this->addQuery(
            "ALTER TABLE `medecin_exercice_place`
             ADD `annule` ENUM ('0', '1') DEFAULT '0',
             ADD INDEX (`annule`)"
        );

        $this->makeRevision('4.67');

        $query = "ALTER TABLE `medecin`
                    DROP INDEX `seeker`,
                    ADD FULLTEXT INDEX `seeker` (`nom`, `prenom`)";
        $this->addQuery($query);

        $this->makeRevision('4.68');

        $this->addQuery(
            "CREATE TABLE `identity_proof_types` (
                `identity_proof_type_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `label` VARCHAR (100) NOT NULL,
                `code` VARCHAR (100) NOT NULL,
                `trust_level` ENUM('1', '2', '3') NOT NULL,
                `active` ENUM('0', '1') DEFAULT '1',
                `editable` ENUM('0', '1') DEFAULT '1',
                `group_id` INT (11) UNSIGNED,
                UNIQUE INDEX unique_code (`code`),
                INDEX (`trust_level`),
                INDEX (`active`),
                INDEX (`editable`),
                INDEX (`group_id`)
            )/*! ENGINE=MyISAM */;"
        );

        $this->addQuery(
            "ALTER TABLE `source_identite` ADD identity_proof_type_id INT (11) UNSIGNED AFTER `type_justificatif`,
                        ADD INDEX (identity_proof_type_id);"
        );

        /* Adds the default identity proof types and update the identity sources */
        (new IdentityProofTypeUpdator($this))->makeUpdate();

        $this->addQuery(
            "ALTER TABLE `source_identite` DROP `type_justificatif`;"
        );

        $this->makeRevision('4.69');

        $this->addQuery(
            "ALTER TABLE `source_identite` ADD `validate_identity` ENUM ('0','1') DEFAULT '1';"
        );

        $this->makeRevision('4.70');

        $query = "INSERT INTO `cronjob` (`name`, `description`, `active`, `params`, `execution`, `mode`) VALUES
                    (
                        'Suppression des documents d\'identité',
                        'Suppressions automatique des documents d\'identité de 5 ans ou plus',
                        '0', 'm=patients&raw=taskDeleteExpiredIdentityFiles', '0 0 1 * * *', 'lock'
                    );";
        $this->addQuery($query);

        $this->makeRevision('4.71');

        $this->addFunctionalPermQuery('allow_modify_strict_traits', '0');

        $this->makeRevision('4.72');

        $this->addQuery(
            'ALTER TABLE `source_identite`
                MODIFY `nom` VARCHAR(50),
                MODIFY `nom_naissance` VARCHAR (255),
                MODIFY `prenom_naissance` VARCHAR (255),
                MODIFY `prenoms` VARCHAR (255),
                MODIFY `date_naissance` CHAR (10);'
        );

        $this->makeRevision('4.73');

        $this->addQuery("ALTER TABLE `patients` ADD INDEX (`creator_id`);");
        $this->addQuery("ALTER TABLE `antecedent` ADD INDEX (`owner_id`);");
        $this->addQuery("ALTER TABLE `medecin` ADD INDEX (`user_id`);");
        $this->addQuery("ALTER TABLE `traitement` ADD INDEX (`owner_id`);");

        $this->makeRevision("4.74");
        $query = "ALTER TABLE `constantes_medicales` ADD `selles_volume` VARCHAR (10), ADD `vac` VARCHAR (10)";
        $this->addQuery($query);

        $this->makeRevision('4.75');

        $this->registerSymmetricKey(CSourceIdentite::ENCRYPT_KEY_NAME, Alg::AES(), Mode::CTR());

        $this->makeRevision('4.76');

        $query = "ALTER TABLE `medecin` ADD `medecin_fictif` BOOL DEFAULT 0,
                ADD INDEX (`medecin_fictif`),
                ADD INDEX (`actif`)";
        $this->addQuery($query);

        $this->makeRevision('4.77');

        $query = "ALTER TABLE `patients` ADD INDEX group_id (`group_id`);";
        $this->addQuery($query, true);

        $query = "ALTER TABLE `medecin` ADD INDEX group_id (`group_id`);";
        $this->addQuery($query, true);

        $query = "ALTER TABLE `correspondant_patient` ADD INDEX group_id (`group_id`);";
        $this->addQuery($query, true);

        $this->makeRevision("4.78");
        $query = "ALTER TABLE `dossier_medical`
                ADD `occupational_risk_factor` TEXT,
                ADD `points_attention` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("4.79");

        $this->addQuery(
            "ALTER TABLE `regle_alerte_patient` 
             ADD `type_alerte` ENUM ('board','open') DEFAULT 'board' AFTER `periode_refractaire`,
             ADD `pathologies` VARCHAR (255) AFTER `diagnostics`,
             ADD `ald` ENUM ('0','1') DEFAULT '0' AFTER `pathologies`;"
        );

        $this->makeRevision("4.80");

        $this->addQuery(
            "ALTER TABLE `patient_ins_nir` 
            ADD `ins_temporaire` ENUM ('0','1') DEFAULT '0' AFTER `source_identite_id`;"
        );

        $this->makeRevision("4.81");
        $this->addQuery(
            "ALTER TABLE `antecedent` 
                ADD `family_link` ENUM ('membre_famille','mere','pere','grand_mere','grand_pere','arriere_grand_mere',
            'arriere_grand_pere','frere','soeur','demi_frere','demi_soeur','cousin','cousin_paternel',
            'cousin_maternel','tante','oncle','niece','enfant','fille','fils',
            'petite_fille','petit_fils','mari','femme');"
        );

        $this->makeRevision("4.82");
        $this->addPrefQuery("vue_globale_display_all_forms", '0');

        $this->makeRevision('4.83');
        $this->addQuery("ALTER TABLE `constantes_medicales` ADD `proteinurie` FLOAT UNSIGNED;");

        $this->makeRevision('4.84');

        $this->addQuery("ALTER TABLE `medecin_exercice_place` CHANGE `rpps_file_version` `rpps_file_version` VARCHAR(10)");

        $this->makeRevision('4.85');
        $this->addQuery(
            "ALTER TABLE `identity_proof_types` 
            ADD `validate_identity` ENUM('0', '1') DEFAULT '0' NOT NULL,
            ADD INDEX (`validate_identity`);"
        );

        $this->makeRevision('4.86');

        $this->addQuery("
            ALTER TABLE `dossier_medical` 
                ADD `coloscopie` DATE,
                ADD INDEX (`coloscopie`); 
        ");

        $this->makeRevision('4.87');

        $this->addQuery("
            ALTER TABLE `constantes_medicales`
                ADD `pointure` INT (11);"
        );

        $this->makeRevision('4.88');

        $this->addQuery("
            ALTER TABLE `search_criteria` 
                ADD `ald` ENUM ('0','1') DEFAULT '0';", true);

        $this->makeRevision("4.89");

        $this->addQuery(
            "ALTER TABLE `evenement_patient` 
                CHANGE `date` `date` DATETIME NOT NULL,
                ADD `date_fin_operation` DATETIME,
                ADD INDEX (`date_fin_operation`);"
        );

        $this->makeRevision('4.90');

        $this->addQuery("
            ALTER TABLE `dossier_medical` 
                ADD `fibroscopie` DATE,
                ADD INDEX (`fibroscopie`); 
        ");

        $this->makeRevision('4.91');

        $this->addQuery("ALTER TABLE `search_criteria` 
                ADD `group_by_patient` ENUM ('0','1') DEFAULT '0';");

        $this->makeRevision('4.92');

        /**
         * The table `constant_type_bookmark` allows to highlight a patient's favourite medical constants type
         * according to the connected user.
         *
         * Uses :
         *     - Highlight the latest constants type that are bookmarked in a list.
         */
        $this->addQuery(
            "CREATE TABLE `constant_type_bookmark` (
                `constant_type_bookmark_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `constant_type` VARCHAR (255) NOT NULL,
                INDEX (`user_id`),
                INDEX (`patient_id`),
                INDEX (`constant_type`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('4.93');
        $this->addQuery("ALTER TABLE `correspondant_patient`
                CHANGE `relation` `relation` ENUM (
                    'assurance',
                    'autre',
                    'confiance',
                    'employeur',
                    'inconnu',
                    'prevenir',
                    'representant_legal',
                    'representant_th',
                    'transport',
                    'parent_proche',
                    'ne_pas_prevenir',
                    'soins_domicile'
                ) DEFAULT 'prevenir'");

        $this->mod_version = '4.94';

        // No revisions below this line
        $query = "SELECT * FROM `communes_suisse` WHERE `commune` = 'Rivaz' AND `code_postal` = '1071'";
        $this->addDatasource("INSEE", $query);

        $dsn = CSQLDataSource::get('INSEE', true);
        if ($dsn instanceof CSQLDataSource && array_key_exists('INSEE', CAppUI::conf('db'))) {
            if ($dsn->fetchRow($dsn->exec("SHOW TABLES LIKE 'communes_france';"))) {
                $this->checkCommunes();
            }
        }
    }
}
