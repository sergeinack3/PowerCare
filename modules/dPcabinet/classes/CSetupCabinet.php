<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Admin\CUser;

/**
 * @package Ox\Mediboard\Cabinet
 * @codeCoverageIgnore
 */
class CSetupCabinet extends CSetup
{

    /**
     * crée les préférences pour les médecins visés par la prise de rdv en se basant sur la pref précédente
     *
     * @TODO: virer la pref 'pratOnlyForConsult'
     *
     * @return bool
     */
    protected function prefForConsultPratType()
    {
        $ds        = $this->ds;
        $query     = "SELECT * FROM `user_preferences` WHERE `key` = 'pratOnlyForConsult' AND `value` IS NOT NULL  GROUP BY user_id ;";
        $result    = $ds->loadList($query);
        $insertion = 'INSERT INTO `user_preferences` VALUES ';
        $values    = [];
        foreach ($result as $_result) {
            $only_prat_for_consult = $_result["value"];
            $prefs                 = [
                "take_consult_for_chirurgien"   => 1,
                "take_consult_for_anesthesiste" => 1,
                "take_consult_for_medecin"      => 1,
                "take_consult_for_dentiste"     => 1,
                "take_consult_for_infirmiere"   => 0,
                "take_consult_for_reeducateur"  => 0,
                "take_consult_for_sage_femme"   => 0,
            ];
            foreach ($prefs as $key => $default_pref) {
                $_value   = ($only_prat_for_consult) ? $default_pref : '1';
                $user_id  = $_result["user_id"] ? "'" . $_result["user_id"] . "'" : "NULL";
                $values[] = " (" . $user_id . ", '" . $key . "', '" . $_value . "', '', '')";
            }
        }
        $insertion = $insertion . implode(",", $values) . ";";
        if (!$ds->exec($insertion)) {
            return false;
        }

        return true;
    }

    /**
     * Création des consults anesth liées à des consults d'anesthésistes
     *
     * @return bool
     */
    protected function consultAnesth()
    {
        $ds = $this->ds;

        $utypes_flip  = array_flip(CUser::$types);
        $id_anesth    = $utypes_flip["Anesthésiste"];
        $query        = "SELECT users.user_id
                  FROM users, users_mediboard
                  WHERE users.user_id = users_mediboard.user_id
                  AND users.user_type='$id_anesth'";
        $result       = $ds->loadList($query);
        $listAnesthid = [];
        foreach ($result as $keyresult => $resultAnesth) {
            $listAnesthid[$keyresult] = $result[$keyresult]["user_id"];
        }

        $in_anesth = CSQLDataSource::prepareIn($listAnesthid);
        $query     = "SELECT consultation.consultation_id FROM consultation
                  LEFT JOIN consultation_anesth ON consultation.consultation_id = consultation_anesth.consultation_id
                  LEFT JOIN plageconsult ON consultation.plageconsult_id = plageconsult.plageconsult_id
                  WHERE plageconsult.chir_id $in_anesth AND consultation_anesth.consultation_anesth_id IS NULL";
        $result    = $ds->loadList($query);

        foreach ($result as $keyresult => $resultAnesth) {
            $consultAnesth                         = new CConsultAnesth();
            $consultAnesth->consultation_anesth_id = 0;
            $consultAnesth->consultation_id        = $result[$keyresult]["consultation_id"];
            $consultAnesth->store();
        }

        return true;
    }

    /**
     * Nettoie les operation_id
     *
     * @return bool
     */
    protected function cleanOperationIdError()
    {
        $ds                                        = $this->ds;
        $where                                     = [];
        $where["consultation_anesth.operation_id"] = "!= 0";
        $where[]                                   = "consultation_anesth.operation_id IS NOT NULL";
        $where[]                                   = "(SELECT COUNT(operations.operation_id) FROM operations WHERE operation_id=consultation_anesth.operation_id)=0";

        $query = new CRequest();
        $query->addSelect("consultation_anesth_id");
        $query->addTable("consultation_anesth");
        $query->addWhere($where);
        $aKeyxAnesth = $ds->loadColumn($query->makeSelect());
        if ($aKeyxAnesth === false) {
            return false;
        }
        if (count($aKeyxAnesth)) {
            $query = "UPDATE consultation_anesth SET operation_id = NULL WHERE (consultation_anesth_id " .
                CSQLDataSource::prepareIn($aKeyxAnesth) . ")";
            if (!$ds->exec($query)) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * CSetupCabinet constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPcabinet";

        $this->makeRevision("0.0");
        $query = "CREATE TABLE consultation (
                    consultation_id bigint(20) NOT NULL auto_increment,
                    plageconsult_id bigint(20) NOT NULL default '0',
                    patient_id bigint(20) NOT NULL default '0',
                    heure time NOT NULL default '00:00:00',
                    duree time NOT NULL default '00:00:00',
                    motif text,
                    secteur1 smallint(6) NOT NULL default '0',
                    secteur2 smallint(6) NOT NULL default '0',
                    rques text,
                    PRIMARY KEY  (consultation_id),
                    KEY plageconsult_id (plageconsult_id,patient_id)
                    ) /*! ENGINE=MyISAM */ COMMENT='Table des consultations';";
        $this->addQuery($query);
        $query = "CREATE TABLE plageconsult (
                    plageconsult_id bigint(20) NOT NULL auto_increment,
                    chir_id bigint(20) NOT NULL default '0',
                    date date NOT NULL default '0000-00-00',
                    debut time NOT NULL default '00:00:00',
                    fin time NOT NULL default '00:00:00',
                    PRIMARY KEY  (plageconsult_id),
                    KEY chir_id (chir_id)
                    ) /*! ENGINE=MyISAM */ COMMENT='Table des plages de consultation des médecins';";
        $this->addQuery($query);


        $this->makeRevision("0.1");
        $query = "ALTER TABLE plageconsult ADD freq TIME DEFAULT '00:15:00' NOT NULL AFTER date ;";
        $this->addQuery($query);

        $this->makeRevision("0.2");
        $query = "ALTER TABLE consultation ADD compte_rendu TEXT DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE consultation CHANGE duree duree TINYINT DEFAULT '1' NOT NULL ";
        $this->addQuery($query);
        $query = "UPDATE consultation SET duree='1' ";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `consultation`
                ADD `chrono` TINYINT DEFAULT '16' NOT NULL,
                ADD `annule` TINYINT DEFAULT '0' NOT NULL,
                ADD `paye` TINYINT DEFAULT '0' NOT NULL,
                ADD `cr_valide` TINYINT DEFAULT '0' NOT NULL,
                ADD `examen` TEXT,
                ADD `traitement` TEXT";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $query = "ALTER TABLE `consultation` ADD `premiere` TINYINT NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.24");
        $query = "CREATE TABLE `tarifs` (
                `tarif_id` BIGINT NOT NULL AUTO_INCREMENT ,
                `chir_id` BIGINT DEFAULT '0' NOT NULL ,
                `function_id` BIGINT DEFAULT '0' NOT NULL ,
                `description` VARCHAR( 50 ) ,
                `valeur` TINYINT,
                PRIMARY KEY ( `tarif_id` ) ,
                INDEX ( `chir_id`),
                INDEX ( `function_id` )
                ) /*! ENGINE=MyISAM */ COMMENT = 'table des tarifs de consultation';";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` ADD `tarif` TINYINT,
              ADD `type_tarif` ENUM( 'cheque', 'CB', 'especes', 'tiers', 'autre' ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "ALTER TABLE `tarifs` CHANGE `valeur` `secteur1` FLOAT( 6 ) DEFAULT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tarifs` ADD `secteur2` FLOAT( 6 ) NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` CHANGE `secteur1` `secteur1` FLOAT( 6 ) DEFAULT '0' NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` CHANGE `secteur2` `secteur2` FLOAT( 6 ) DEFAULT '0' NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` CHANGE `tarif` `tarif` VARCHAR( 50 ) DEFAULT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `plageconsult` ADD `libelle` VARCHAR( 50 ) DEFAULT NULL AFTER `chir_id` ;";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $query = "ALTER TABLE `consultation`
                ADD `ordonnance` TEXT DEFAULT NULL,
                ADD `or_valide` TINYINT DEFAULT '0' NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.27");
        $query = "ALTER TABLE `consultation`
                ADD `courrier1` TEXT DEFAULT NULL,
                ADD `c1_valide` TINYINT DEFAULT '0' NOT NULL";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation`
                ADD `courrier2` TEXT DEFAULT NULL,
                ADD `c2_valide` TINYINT DEFAULT '0' NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.28");
        $query = "ALTER TABLE `consultation` ADD `date_paiement` DATE AFTER `paye` ;";
        $this->addQuery($query);
        $query = "UPDATE consultation, plageconsult
                SET consultation.date_paiement = plageconsult.date
                WHERE consultation.plageconsult_id = plageconsult.plageconsult_id
                AND consultation.paye = 1";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "CREATE TABLE `consultation_anesth` (
          `consultation_anesth_id` BIGINT NOT NULL AUTO_INCREMENT ,
          `consultation_id` BIGINT DEFAULT '0' NOT NULL ,
          `operation_id` BIGINT DEFAULT '0' NOT NULL ,
          `poid` FLOAT,
          `taille` FLOAT,
          `groupe` ENUM( '0', 'A', 'B', 'AB' ) ,
          `rhesus` ENUM( '+', '-' ) ,
          `antecedents` TEXT,
          `traitements` TEXT,
          `tabac` ENUM( '-', '+', '++' ) ,
          `oenolisme` ENUM( '-', '+', '++' ) ,
          `transfusions` ENUM( '-', '+' ) ,
          `tasys` TINYINT,
          `tadias` TINYINT,
          `listCim10` TEXT,
          `intubation` ENUM( 'dents', 'bouche', 'cou' ) ,
          `biologie` ENUM( 'NF', 'COAG', 'IONO' ) ,
          `commande_sang` ENUM( 'clinique', 'CTS', 'autologue' ) ,
          `ASA` TINYINT,
          PRIMARY KEY ( `consultation_anesth_id` ) ,
          INDEX ( `consultation_id`) ,
          INDEX ( `operation_id` )
          ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        // CR passage des champs à enregistrements supprimé car regressifs
        // $this->makeRevision("0.30");

        $this->makeRevision("0.31");
        $query = "CREATE TABLE `examaudio` (
                `examaudio_id` INT NOT NULL AUTO_INCREMENT ,
                `consultation_id` INT NOT NULL ,
                `gauche_aerien` VARCHAR( 64 ) ,
                `gauche_osseux` VARCHAR( 64 ) ,
                `droite_aerien` VARCHAR( 64 ) ,
                `droite_osseux` VARCHAR( 64 ) ,
                PRIMARY KEY ( `examaudio_id` ) ,
                INDEX ( `consultation_id` )) /*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `examaudio` ADD UNIQUE (`consultation_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "ALTER TABLE `examaudio`
                ADD `remarques` TEXT AFTER `consultation_id`,
                ADD `gauche_conlat` VARCHAR( 64 ) ,
                ADD `gauche_ipslat` VARCHAR( 64 ) ,
                ADD `gauche_pasrep` VARCHAR( 64 ) ,
                ADD `gauche_vocale` VARCHAR( 64 ) ,
                ADD `gauche_tympan` VARCHAR( 64 ) ,
                ADD `droite_conlat` VARCHAR( 64 ) ,
                ADD `droite_ipslat` VARCHAR( 64 ) ,
                ADD `droite_pasrep` VARCHAR( 64 ) ,
                ADD `droite_vocale` VARCHAR( 64 ) ,
                ADD `droite_tympan` VARCHAR( 64 )";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `groupe` `groupe` ENUM( '?', '0', 'A', 'B', 'AB' ) DEFAULT '?' NOT NULL ,
                CHANGE `rhesus` `rhesus` ENUM( '?', '+', '-' ) DEFAULT '?' NOT NULL ,
                CHANGE `tabac` `tabac` ENUM( '?', '-', '+', '++' ) DEFAULT '?' NOT NULL ,
                CHANGE `oenolisme` `oenolisme` ENUM( '?', '-', '+', '++' ) DEFAULT '?' NOT NULL ,
                CHANGE `transfusions` `transfusions` ENUM( '?', '-', '+' ) DEFAULT '?' NOT NULL ,
                CHANGE `intubation` `intubation` ENUM( '?', 'dents', 'bouche', 'cou' ) DEFAULT '?' NOT NULL ,
                CHANGE `biologie` `biologie` ENUM( '?', 'NF', 'COAG', 'IONO' ) DEFAULT '?' NOT NULL ,
                CHANGE `commande_sang` `commande_sang` ENUM( '?', 'clinique', 'CTS', 'autologue' ) DEFAULT '?' NOT NULL ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `tasys` `tasys` INT( 5 ) DEFAULT NULL ,
                CHANGE `tadias` `tadias` INT( 5 ) DEFAULT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `consultation` ADD `arrivee` DATETIME AFTER `type_tarif` ;";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `groupe` `groupe` ENUM( '?', 'O', 'A', 'B', 'AB' ) DEFAULT '?' NOT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.37");
        $this->makeRevision("0.38");

        $this->makeRevision("0.39");
        $query = "ALTER TABLE `consultation_anesth`
              ADD `mallampati` ENUM( 'classe1', 'classe2', 'classe3', 'classe4' ),
              ADD `bouche` ENUM( 'm20', 'm35', 'p35' ),
              ADD `distThyro` ENUM( 'm65', 'p65' ),
              ADD `etatBucco` VARCHAR(50),
              ADD `conclusion` VARCHAR(50),
              ADD `position` ENUM( 'DD', 'DV', 'DL', 'GP', 'AS', 'TO' );";
        $this->addQuery($query);

        $this->makeRevision("0.40");
        $this->makeRevision("0.41");

        $this->makeRevision("0.42");
        $query = "ALTER TABLE `consultation` DROP INDEX `plageconsult_id`  ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` ADD INDEX ( `plageconsult_id` ) ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` ADD INDEX ( `patient_id` ) ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tarifs` DROP INDEX `chir_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tarifs` ADD INDEX ( `chir_id` ) ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tarifs` ADD INDEX ( `function_id` ) ;";
        $this->addQuery($query);

        $this->makeRevision("0.43");
        $query = "ALTER TABLE `consultation_anesth`" .
            "CHANGE `position` `position` ENUM( 'DD', 'DV', 'DL', 'GP', 'AS', 'TO', 'GYN');";
        $this->addQuery($query);
        $query = "CREATE TABLE `techniques_anesth` (
               `technique_id` INT NOT NULL AUTO_INCREMENT ,
               `consultAnesth_id` INT NOT NULL ,
               `technique` TEXT NOT NULL ,
               PRIMARY KEY ( `technique_id` )) /*! ENGINE=MyISAM */";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth`
                ADD `rai` float default NULL,
                ADD `hb` float default NULL,
                ADD `tp` float default NULL,
                ADD `tca` time NOT NULL default '00:00:00',
                ADD `creatinine` float default NULL,
                ADD `na` float default NULL,
                ADD `k` float default NULL,
                ADD `tsivy` time NOT NULL default '00:00:00',
                ADD `plaquettes` INT(7) default NULL,
                ADD `ht` float default NULL,
                ADD `ecbu` ENUM( '?', 'NEG', 'POS' ) DEFAULT '?' NOT NULL,
                ADD `ecbu_detail` TEXT,
                ADD `pouls` INT(4) default NULL,
                ADD `spo2` float default NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` CHANGE `operation_id` `operation_id` BIGINT( 20 ) NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `etatBucco` `etatBucco` TEXT DEFAULT NULL ,
                CHANGE `conclusion` `conclusion` TEXT DEFAULT NULL ";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `tabac` `tabac` TEXT DEFAULT NULL ,
                CHANGE `oenolisme` `oenolisme` TEXT DEFAULT NULL ";
        $this->addQuery($query);
        $query = "CREATE TABLE `exams_comp` (
               `exam_id` INT NOT NULL AUTO_INCREMENT ,
               `consult_id` INT NOT NULL ,
               `examen` TEXT NOT NULL ,
               `fait` tinyint(1) NOT NULL default 0,
               PRIMARY KEY ( `exam_id` )) /*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        $this->addDependency("mediusers", "0.1");
        $this->addMethod("consultAnesth");

        $this->makeRevision("0.45");
        $query = "ALTER TABLE `exams_comp` CHANGE `consult_id` `consultation_id` INT NOT NULL ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `techniques_anesth` CHANGE `consultAnesth_id` `consultation_anesth_id` INT NOT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "ALTER TABLE `consultation_anesth` CHANGE `tca` `tca` TINYINT(2) NULL ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth`
                ADD `tca_temoin` TINYINT(2) NULL AFTER `tca`,
                ADD `ht_final` FLOAT DEFAULT NULL AFTER `ht`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` DROP `transfusions`";
        $this->addQuery($query);

        $this->makeRevision("0.47");
        $query = "ALTER TABLE `consultation_anesth` CHANGE `rhesus` `rhesus` ENUM( '?', '+', '-', 'POS', 'NEG') DEFAULT '?' NOT NULL ;";
        $this->addQuery($query);
        $query = "UPDATE `consultation_anesth` SET `rhesus`='POS' WHERE `rhesus`='+';";
        $this->addQuery($query);
        $query = "UPDATE `consultation_anesth` SET `rhesus`='NEG' WHERE `rhesus`='-';";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` CHANGE `rhesus` `rhesus` ENUM( '?', 'POS', 'NEG') DEFAULT '?' NOT NULL ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` CHANGE `rai` `rai` ENUM( '?', 'POS', 'NEG') DEFAULT '?' NOT NULL ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` DROP `ecbu_detail`";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth`
                ADD `premedication` TEXT,
                ADD `prepa_preop` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.48");
        $query = "ALTER TABLE `consultation_anesth` 
                CHANGE `consultation_anesth_id` `consultation_anesth_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `consultation_id` `consultation_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `operation_id` `operation_id` int(11) unsigned NULL,
                CHANGE `poid` `poid` float unsigned NULL,
                CHANGE `rhesus` `rhesus` enum('?','NEG','POS') NOT NULL DEFAULT '?',
                CHANGE `rai` `rai` enum('?','NEG','POS') NOT NULL DEFAULT '?',
                CHANGE `tasys` `tasys` int(5) unsigned zerofill NULL,
                CHANGE `tadias` `tadias` int(5) unsigned zerofill NULL,
                CHANGE `plaquettes` `plaquettes` int(7) unsigned zerofill NULL,
                CHANGE `pouls` `pouls` mediumint(4) unsigned zerofill NULL,
                CHANGE `ASA` `ASA` enum('1','2','3','4','5') NULL,
                CHANGE `tca` `tca` tinyint(2) unsigned zerofill NULL,
                CHANGE `tca_temoin` `tca_temoin` tinyint(2) unsigned zerofill NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` 
                DROP `listCim10`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` 
                CHANGE `consultation_id` `consultation_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `plageconsult_id` `plageconsult_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `patient_id` `patient_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `duree` `duree` tinyint(1) unsigned zerofill NOT NULL DEFAULT '1',
                CHANGE `annule` `annule` enum('0','1') NOT NULL DEFAULT '0',
                CHANGE `chrono` `chrono` enum('16','32','48','64') NOT NULL DEFAULT '16',
                CHANGE `paye` `paye` enum('0','1') NOT NULL DEFAULT '0',
                CHANGE `premiere` `premiere` enum('0','1') NOT NULL DEFAULT '0',
                CHANGE `tarif` `tarif` varchar(255) NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` 
                DROP `compte_rendu`,
                DROP `cr_valide`,
                DROP `ordonnance`,
                DROP `or_valide`,
                DROP `courrier1`,
                DROP `c1_valide`,
                DROP `courrier2`,
                DROP `c2_valide`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `examaudio` 
                CHANGE `examaudio_id` `examaudio_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `consultation_id` `consultation_id` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `exams_comp` 
                CHANGE `exam_id` `exam_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `consultation_id` `consultation_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `fait` `fait` tinyint(4) NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `plageconsult` 
                CHANGE `plageconsult_id` `plageconsult_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `chir_id` `chir_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `libelle` `libelle` varchar(255) NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `tarifs` 
                CHANGE `tarif_id` `tarif_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `chir_id` `chir_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `description` `description` varchar(255) NOT NULL,
                CHANGE `secteur1` `secteur1` float NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `techniques_anesth` 
                CHANGE `technique_id` `technique_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `consultation_anesth_id` `consultation_anesth_id` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.49");
        $query = "ALTER TABLE `consultation_anesth` 
                CHANGE `tasys` `tasys` TINYINT(4) NULL,
                CHANGE `tadias` `tadias` TINYINT(4) NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.50");
        $query = "ALTER TABLE `consultation` CHANGE `patient_id` `patient_id` int(11) unsigned NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.51");
        $query = "UPDATE `consultation` SET `annule` = '0' WHERE (`annule` = '' OR `annule` IS NULL );";
        $this->addQuery($query);

        $this->makeRevision("0.52");
        $query = "UPDATE `consultation` SET `patient_id` = NULL WHERE (`patient_id` = 0 );";
        $this->addQuery($query);

        $this->makeRevision("0.53");
        $query = "CREATE TABLE `exampossum` (
                    `exampossum_id` int(11) unsigned NOT NULL auto_increment,
                    `consultation_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `age` enum('inf60','61','sup71') NULL,
                    `ouverture_yeux` enum('spontane','bruit','douleur','jamais') NULL,
                    `rep_verbale` enum('oriente','confuse','inapproprie','incomprehensible','aucune') NULL,
                    `rep_motrice` enum('obeit','oriente','evitement','decortication','decerebration','rien') NULL,
                    `signes_respiratoires` enum('aucun','dyspnee_effort','bpco_leger','dyspnee_inval','bpco_modere','dyspnee_repos','fibrose') NULL,
                    `uree` enum('inf7.5','7.6','10.1','sup15.1') NULL,
                    `freq_cardiaque` enum('inf39','40','50','81','101','sup121') NULL,
                    `signes_cardiaques` enum('aucun','diuretique','antiangineux','oedemes','cardio_modere','turgescence','cardio') NULL,
                    `hb` enum('inf9.9','10','11.5','13','16.1','17.1','sup18.1') NULL,
                    `leucocytes` enum('inf3000','3100','4000','10100','sup20100') NULL,
                    `ecg` enum('normal','fa','autre','sup5','anomalie') NULL,
                    `kaliemie` enum('inf2.8','2.9','3.2','3.5','5.1','5.4','sup6.0') NULL,
                    `natremie` enum('inf125','126','131','sup136') NULL,
                    `pression_arterielle` enum('inf89','90','100','110','131','sup171') NULL,
                    `gravite` enum('min','moy','maj','maj+') NULL,
                    `nb_interv` enum('1','2','sup2') NULL,
                    `pertes_sanguines` enum('inf100','101','501','sup1000') NULL,
                    `contam_peritoneale` enum('aucune','mineure','purulente','diffusion') NULL,
                    `cancer` enum('absense','tumeur','ganglion','metastases') NULL,
                    `circonstances_interv` enum('reglee','urg','prgm','sansdelai') NULL,
                    PRIMARY KEY  (`exampossum_id`),
                    KEY `consultation_id` (`consultation_id`)
                    ) /*! ENGINE=MyISAM */ COMMENT='Table pour le calcul possum';";
        $this->addQuery($query);

        $this->makeRevision("0.54");
        $query = "CREATE TABLE `examnyha` (
                    `examnyha_id` int(11) unsigned NOT NULL auto_increment,
                    `consultation_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `q1` enum('0','1') NULL,
                    `q2a` enum('0','1') NULL,
                    `q2b` enum('0','1') NULL,
                    `q3a` enum('0','1') NULL,
                    `q3b` enum('0','1') NULL,
                    `hesitation` enum('0','1') NOT NULL DEFAULT '0',
                    PRIMARY KEY  (`examnyha_id`),
                    KEY `consultation_id` (`consultation_id`)
                    ) /*! ENGINE=MyISAM */ COMMENT='Table pour la classe NYHA';";
        $this->addQuery($query);

        $this->makeRevision("0.55");
        $query = "ALTER TABLE `consultation_anesth` ADD `listCim10` TEXT DEFAULT NULL ;";
        $this->addQuery($query);

        $this->makeRevision("0.56");
        $this->addDependency("dPplanningOp", "0.63");
        $this->addMethod("cleanOperationIdError");

        $this->makeRevision("0.57");
        $query = "ALTER TABLE `consultation`
                ADD INDEX ( `heure` ),
                ADD INDEX ( `annule` ),
                ADD INDEX ( `paye` ),
                ADD INDEX ( `date_paiement` )";
        $this->addQuery($query);
        $query = "ALTER TABLE `plageconsult`
                ADD INDEX ( `date` ),
                ADD INDEX ( `debut` ),
                ADD INDEX ( `fin` )";
        $this->addQuery($query);

        $this->makeRevision("0.58");
        $this->addDependency("dPpatients", "0.41");
        $query = "INSERT INTO antecedent
            SELECT '', consultation_anesth.consultation_anesth_id, antecedent.type,
              antecedent.date, antecedent.rques, 'CConsultAnesth'
            FROM antecedent, consultation_anesth, consultation
            WHERE antecedent.object_class = 'CPatient'
              AND antecedent.object_id = consultation.patient_id
              AND consultation.consultation_id = consultation_anesth.consultation_id";
        $this->addQuery($query);
        $query = "INSERT INTO traitement
            SELECT '', consultation_anesth.consultation_anesth_id, traitement.debut,
              traitement.fin, traitement.traitement, 'CConsultAnesth'
            FROM traitement, consultation_anesth, consultation
            WHERE traitement.object_class = 'CPatient'
              AND traitement.object_id = consultation.patient_id
              AND consultation.consultation_id = consultation_anesth.consultation_id";
        $this->addQuery($query);
        $query = "UPDATE consultation_anesth, consultation, patients
            SET consultation_anesth.listCim10 = patients.listCim10
            WHERE consultation_anesth.consultation_id = consultation.consultation_id
              AND consultation.patient_id = patients.patient_id";
        $this->addQuery($query);

        $this->makeRevision("0.59");
        $query = "ALTER TABLE `exams_comp` ADD `realisation` ENUM( 'avant', 'pendant' ) NOT NULL DEFAULT 'avant' AFTER `consultation_id`;";
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $query = "CREATE TABLE `addiction` (
            `addiction_id` int(11) unsigned NOT NULL auto_increment,
            `object_id` int(11) unsigned NOT NULL default '0',
            `object_class` enum('CConsultAnesth') NOT NULL default 'CConsultAnesth',
            `type` enum('tabac', 'oenolisme', 'cannabis') NOT NULL default 'tabac',
            `addiction` text,
            PRIMARY KEY  (`addiction_id`)
            ) /*! ENGINE=MyISAM */ COMMENT = 'Addictions pour le dossier anesthésie';";
        $this->addQuery($query);

        $this->makeRevision("0.61");
        $this->addPrefQuery("DefaultPeriod", "month");

        $this->makeRevision("0.62");
        $query = "ALTER TABLE `tarifs`
                CHANGE `chir_id` `chir_id` int(11) unsigned NULL DEFAULT NULL,
                CHANGE `function_id` `function_id` int(11) unsigned NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `tarifs` SET function_id = NULL WHERE function_id='0';";
        $this->addQuery($query);
        $query = "UPDATE `tarifs` SET chir_id = NULL WHERE chir_id='0';";
        $this->addQuery($query);
        $query = "DELETE FROM `consultation_anesth` WHERE `consultation_id`= '0'";
        $this->addQuery($query);
        $query = "UPDATE `consultation_anesth` SET operation_id = NULL WHERE operation_id='0';";
        $this->addQuery($query);
        $query = "DELETE FROM `exams_comp` WHERE `consultation_id`= '0'";
        $this->addQuery($query);

        $this->makeRevision("0.63");
        $this->addPrefQuery("simpleCabinet", "0");

        $this->makeRevision("0.64");
        $this->addPrefQuery("GestionFSE", "0");

        $this->makeRevision("0.65");
        $this->addPrefQuery("DossierCabinet", "dPcabinet");

        $this->makeRevision("0.66");
        $query = "UPDATE `consultation` SET  `rques` = NULL  WHERE `rques` = 'NULL'";
        $this->addQuery($query);
        $query = "UPDATE `consultation` SET  `motif` = NULL  WHERE `motif` = 'NULL'";
        $this->addQuery($query);
        $query = "UPDATE `consultation` SET  `traitement` = NULL  WHERE `traitement` = 'NULL'";
        $this->addQuery($query);
        $query = "UPDATE `consultation` SET  `examen` = NULL  WHERE `examen` = 'NULL'";
        $this->addQuery($query);

        $this->makeRevision("0.67");
        $query = "ALTER TABLE `consultation` ADD `codes_ccam` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("0.68");
        $this->addPrefQuery("ccam", "0");

        $this->makeRevision("0.69");
        $query = "ALTER TABLE `tarifs` ADD `codes_ccam` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("0.70");
        $query = "UPDATE `consultation_anesth` SET  `plaquettes` = `plaquettes`/1000";
        $this->addQuery($query);

        $this->makeRevision("0.71");
        $query = "ALTER TABLE `consultation_anesth` " .
            "CHANGE `plaquettes` `plaquettes` int(4) unsigned zerofill NULL";
        $this->addQuery($query);

        $this->makeRevision("0.72");
        $query = "CREATE TABLE `banque` (
             `banque_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
             `nom` VARCHAR(255) NOT NULL, 
             `description` VARCHAR(255), 
              PRIMARY KEY (`banque_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.73");
        $query = "ALTER TABLE `consultation` ADD `banque_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.74");
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'AXA Banque', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque accord', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'LCL', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Populaire', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Natexis', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'La banque Postale', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'BNP Paribas', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Caisse d\'epargne', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Ixis', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Océor', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Palatine', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Crédit Foncier', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Compagnie 1818', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Caisse des dépôts', 'Caisse des dépôts et consignations') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Crédit Agricole', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'HSBC', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Crédit coopératif', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Crédit Mutuel', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'CIC', 'Crédit Industriel et Commercial') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Dexia', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Société générale', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Groupama Banque', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Crédit du Nord', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Courtois', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Tarneaud', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Kolb', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Laydernier', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Nuger', '') ;";
        $this->addQuery($query);
        $query = " INSERT INTO `banque` ( `banque_id` , `nom` , `description` ) VALUES ( '' , 'Banque Rhône-Alpes', '') ;";
        $this->addQuery($query);

        $this->makeRevision("0.75");
        $query = "CREATE TABLE `consultation_cat` (
            `categorie_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
            `function_id` INT(11) UNSIGNED NOT NULL, 
            `nom_categorie` VARCHAR(255) NOT NULL, 
            `nom_icone` VARCHAR(255) NOT NULL, 
             PRIMARY KEY (`categorie_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.76");
        $query = "ALTER TABLE `consultation`
      ADD `categorie_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.77");
        // Tranfert des addictions tabac vers la consultation préanesthésique
        $query = "INSERT INTO `addiction` ( `addiction_id` , `object_id` , `object_class` , `type` , `addiction` )
      SELECT null,`consultation_anesth_id`, 'CConsultAnesth', 'tabac', `tabac` 
      FROM `consultation_anesth` 
      WHERE `tabac` IS NOT NULL
      AND `tabac` <> ''";
        $this->addQuery($query);

        // Tranfert des addictions tabac vers le dossier patient
        $query = "INSERT INTO `addiction` ( `addiction_id` , `object_id` , `object_class` , `type` , `addiction` )
      SELECT null,`patient_id`, 'CPatient', 'tabac', `tabac`
      FROM `consultation_anesth`, `consultation`
      WHERE `tabac` IS NOT NULL
      AND `tabac` <> ''
      AND `consultation`.`consultation_id` = `consultation_anesth`.`consultation_id`";
        $this->addQuery($query);

        // Tranfert des addictions oenolisme vers la consultation préanesthésique
        $query = "INSERT INTO `addiction` ( `addiction_id` , `object_id` , `object_class` , `type` , `addiction` )
      SELECT null,`consultation_anesth_id`, 'CConsultAnesth', 'oenolisme', `oenolisme` 
      FROM `consultation_anesth` 
      WHERE `oenolisme` IS NOT NULL
      AND `oenolisme` <> ''";
        $this->addQuery($query);

        // Tranfert des addictions oenolisme vers le dossier patient
        $query = "INSERT INTO `addiction` ( `addiction_id` , `object_id` , `object_class` , `type` , `addiction` )
      SELECT null,`patient_id`, 'CPatient', 'oenolisme', `oenolisme`
      FROM `consultation_anesth`, `consultation`
      WHERE `oenolisme` IS NOT NULL
      AND `oenolisme` <> ''
      AND `consultation`.`consultation_id` = `consultation_anesth`.`consultation_id`";
        $this->addQuery($query);

        $this->makeRevision("0.78");
        $query = "ALTER TABLE `consultation` ADD `adresse` enum('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);


        $this->makeRevision("0.79");
        // Ne pas supprimer le champs listCim10 de la consultAnesth afin d'avoir fait l'import dans dPpatient
        $this->addDependency("dPpatients", "0.51");
        $query = "ALTER TABLE `consultation_anesth`
                DROP `listCim10`;";
        $this->addQuery($query);

        $this->makeRevision("0.80");
        $query = "CREATE TABLE `acte_ngap` (
            `acte_ngap_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
            `code` VARCHAR(3) NOT NULL, 
            `quantite` INT(11) NOT NULL, 
            `coefficient` FLOAT NOT NULL, 
            `consultation_id` INT(11) UNSIGNED NOT NULL, 
            PRIMARY KEY (`acte_ngap_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.81");
        $query = "ALTER TABLE `tarifs` ADD `codes_ngap` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("0.82");
        $query = "ALTER TABLE `acte_ngap`
            ADD `montant_depassement` FLOAT, 
            ADD `montant_base` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("0.83");
        $query = "ALTER TABLE `consultation`
            ADD `valide` ENUM('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET `valide` = '1'
             WHERE `tarif` IS NOT NULL;";
        $this->addQuery($query);


        $this->makeRevision("0.84");
        $query = "ALTER TABLE `consultation`
            CHANGE `type_tarif` `mode_reglement` ENUM( 'cheque', 'CB', 'especes', 'tiers', 'autre' ),
            CHANGE `paye` `patient_regle` ENUM('0','1');";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
            ADD `total_amc` FLOAT,
            ADD `total_amo` FLOAT,
            ADD `total_assure` FLOAT,
            ADD `facture_acquittee` ENUM('0','1'), 
            ADD `a_regler` FLOAT DEFAULT '0.0';";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET `a_regler` = `secteur1` + `secteur2`
            WHERE `mode_reglement` <> 'tiers'
            OR `mode_reglement` IS NULL;";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET `patient_regle` = '1'
            WHERE `mode_reglement` = 'tiers';";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET `facture_acquittee` = '1'
            WHERE `a_regler` = `secteur1` + `secteur2`
            AND `patient_regle` = '1';";
        $this->addQuery($query);

        $this->makeRevision("0.85");
        $query = "ALTER TABLE `consultation`
            ADD `sejour_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.86");
        $query = "UPDATE `consultation`
            SET `patient_regle` = '1'
            WHERE ROUND(`a_regler`,2) = ROUND(0,2);";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET `facture_acquittee` = '1'
            WHERE ROUND(`a_regler`,2) = ROUND(`secteur1` + `secteur2`, 2)
            AND `patient_regle` = '1'
            AND (`facture_acquittee` <> '1'
                  OR `facture_acquittee` IS NULL);";
        $this->addQuery($query);

        $query = "UPDATE `consultation`, `plageconsult`
            SET `date_paiement` = `plageconsult`.`date`  
            WHERE `patient_regle` = '1'
            AND `date_paiement` IS NULL
            AND `consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`;";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET date_paiement = NULL
            WHERE date_paiement IS NOT NULL
            AND patient_regle <> '1';";
        $this->addQuery($query);

        $this->makeRevision("0.87");
        $query = "ALTER TABLE `consultation` 
            CHANGE `date_paiement` `date_reglement` DATE,
            DROP `patient_regle`;";
        $this->addQuery($query);

        $this->makeRevision("0.88");
        $query = "ALTER TABLE `acte_ngap`
            CHANGE `consultation_id` `object_id` INT(11) UNSIGNED NOT NULL, 
            ADD `object_class` ENUM('COperation','CSejour','CConsultation') NOT NULL default 'CConsultation';";
        $this->addQuery($query);

        $this->makeRevision("0.89");
        $query = "UPDATE `consultation`
            SET `date_reglement` = NULL, `facture_acquittee` = NULL
            WHERE `a_regler` = '0'
            AND (`mode_reglement` IS NULL OR `mode_reglement` = '');";
        $this->addQuery($query);

        $this->makeRevision("0.90");
        $query = "ALTER TABLE `consultation` 
            CHANGE `facture_acquittee` `reglement_AM` ENUM('0','1');";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET `reglement_AM` = '1'
            WHERE ROUND(`a_regler`,2) = ROUND(`secteur1` + `secteur2`, 2)
            AND `valide` = '1';";
        $this->addQuery($query);

        $this->makeRevision("0.91");
        $query = "DELETE FROM `user_preferences` 
      WHERE `key` = 'ccam_consultation'";
        $this->addQuery($query);
        $query = "UPDATE `user_preferences`
      SET `key` = 'ccam_consultation'
      WHERE `key` = 'ccam'";
        $this->addQuery($query);

        $this->makeRevision("0.92");
        $query = "ALTER TABLE `acte_ngap` 
            ADD `demi` ENUM('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.93");
        $query = "CREATE TABLE `examigs` (
           `examigs_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
           `consultation_id` INT(11) UNSIGNED NOT NULL, 
           `age` ENUM('0','7','12','15','16','18'), 
           `FC` ENUM('11','2','0','4','7'), 
           `TA` ENUM('13','5','0','2'), 
           `temperature` ENUM('0','3'), 
           `PAO2_FIO2` ENUM('11','9','6'), 
           `diurese` ENUM('12','4','0'), 
           `uree` ENUM('0','6','10'), 
           `globules_blancs` ENUM('12','0','3'), 
           `kaliemie` ENUM('3a','0','3b'), 
           `natremie` ENUM('5','0','1'), 
           `HCO3` ENUM('6','3','0'), 
           `billirubine` ENUM('0','4','9'), 
           `glascow` ENUM('26','13','7','5','0'), 
           `maladies_chroniques` ENUM('9','10','17'), 
           `admission` ENUM('0','6','8'), 
           `scoreIGS` INT(11), 
             PRIMARY KEY (`examigs_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.94");

        // Ajout de du_tiers
        $query = "ALTER TABLE `consultation`
            ADD `du_tiers` FLOAT DEFAULT 0.0";
        $this->addQuery($query);

        // Calcul de du_tiers
        $query = "UPDATE `consultation`
            SET `du_tiers` = ROUND(`secteur1` + `secteur2` - `a_regler`, 2);";
        $this->addQuery($query);

        // mode_reglement à NULL quand mode_reglement = tiers
        $query = "UPDATE `consultation`
            SET `mode_reglement` = ''
            WHERE `mode_reglement` = 'tiers';";
        $this->addQuery($query);

        // Modification de l'enum de mode_reglement
        $query = "ALTER TABLE `consultation`
            CHANGE `mode_reglement` `mode_reglement` ENUM('cheque','CB','especes','virement','autre');";
        $this->addQuery($query);

        // date_reglement => patient_date_reglement
        $query = "ALTER TABLE `consultation` 
            CHANGE `date_reglement` `patient_date_reglement` DATE;";
        $this->addQuery($query);

        // mode_reglement => patient_mode_reglement
        $query = "ALTER TABLE `consultation` 
            CHANGE `mode_reglement` `patient_mode_reglement` ENUM('cheque','CB','especes','virement','autre');";
        $this->addQuery($query);

        // a_regler => du_patient
        $query = "ALTER TABLE `consultation`
            CHANGE `a_regler` `du_patient` FLOAT DEFAULT '0.0';";
        $this->addQuery($query);

        // Creation d'un tiers_mode_reglement
        $query = "ALTER TABLE `consultation`
            ADD `tiers_mode_reglement` ENUM('cheque','CB','especes','virement','autre');";
        $this->addQuery($query);

        // Creation d'un tiers_date_reglement
        $query = "ALTER TABLE `consultation`
            ADD `tiers_date_reglement` DATE;";
        $this->addQuery($query);

        // On considere que toutes les anciennes consultations ont reglement_AM à 1
        $query = "UPDATE `consultation`, `plageconsult`
            SET `reglement_AM` = '1'  
            WHERE `consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`
            AND `plageconsult`.`date` < '2007-12-01';";
        $this->addQuery($query);

        // On met à jour reglement_AM (reglement_AM à 0 si pas de du_tiers)
        $query = "UPDATE `consultation`
            SET `reglement_AM` = '0'
            WHERE ROUND(`du_tiers`,2)  = ROUND(0,2);";
        $this->addQuery($query);

        // Mise à jour des reglements AM à 1
        $query = "UPDATE `consultation`, `plageconsult`
            SET `tiers_mode_reglement` = 'virement',
                `tiers_date_reglement` = `plageconsult`.`date`
            WHERE `consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`
            AND `consultation`.`reglement_AM` = '1';";
        $this->addQuery($query);

        // Suppression du champ reglement_AM
        $query = "ALTER TABLE `consultation`
            DROP `reglement_AM`;";
        $this->addQuery($query);

        $this->makeRevision("0.95");
        $query = "UPDATE consultation
            SET patient_date_reglement = NULL, patient_mode_reglement = NULL
            WHERE du_patient = 0;";
        $this->addQuery($query);

        $this->makeRevision("0.96");
        $query = "UPDATE consultation 
            SET valide = '0' 
            WHERE valide = '';";
        $this->addQuery($query);

        $this->makeRevision("0.97");
        $query = "ALTER TABLE `consultation`
            ADD `accident_travail` ENUM('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.98");
        $this->addPrefQuery("view_traitement", "1");

        $this->makeRevision("0.99");
        // Table temporaire contenant les consultation_id des accident_travail à 1
        $query = "CREATE TEMPORARY TABLE tbl_accident_travail (
             consultation_id INT( 11 )
            ) AS 
              SELECT consultation_id
              FROM `consultation`
              WHERE accident_travail = '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
            CHANGE `accident_travail` `accident_travail` DATE DEFAULT NULL";
        $this->addQuery($query);

        $query = "UPDATE `consultation`, `plageconsult`, `tbl_accident_travail`
            SET `consultation`.`accident_travail` = `plageconsult`.`date`
            WHERE `consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`
            AND `consultation`.`consultation_id` = `tbl_accident_travail`.`consultation_id`;";
        $this->addQuery($query);

        $query = "UPDATE `consultation`
            SET accident_travail = NULL
            WHERE accident_travail = '0000-00-00';";
        $this->addQuery($query);

        $this->makeRevision("1.00");
        $this->addPrefQuery("autoCloseConsult", "0");

        $this->makeRevision("1.01");
        $query = "ALTER TABLE `acte_ngap` 
            ADD `complement` ENUM('N','F','U');";
        $this->addQuery($query);

        $this->makeRevision("1.02");
        $query = "ALTER TABLE `consultation_anesth`
            ADD `sejour_id` INT(11) UNSIGNED AFTER `operation_id`";
        $this->addQuery($query);

        $this->makeRevision("1.03");
        $query = "ALTER TABLE `consultation_anesth` 
      ADD `examenCardio` TEXT NULL AFTER `etatBucco` ,
      ADD `examenPulmo` TEXT NULL AFTER `examenCardio` ;";
        $this->addQuery($query);

        $this->makeRevision("1.04");
        $query = "CREATE TABLE `reglement` (
      `reglement_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
      `consultation_id` INT( 11 ) UNSIGNED NOT NULL ,
      `banque_id` INT( 11 ) UNSIGNED ,
      `date` DATETIME NOT NULL ,
      `montant` FLOAT NOT NULL ,
      `emetteur` ENUM( 'patient', 'tiers' ) ,
      `mode` ENUM( 'cheque', 'CB', 'especes', 'virement', 'autre' ) ,
      PRIMARY KEY ( `reglement_id` )
      ) /*! ENGINE=MyISAM */ ;";
        $this->addQuery($query);

        // On crée les règlements des patients
        $query = "INSERT INTO `reglement` (
      `emetteur`,
      `consultation_id`, 
      `banque_id`, 
      `date`, 
      `montant`,
      `mode`)
      
      SELECT 
        'patient',
        `consultation_id`, 
        `banque_id`, 
        `patient_date_reglement`, 
        `du_patient`, 
        `patient_mode_reglement`
      FROM 
        `consultation`
      WHERE 
        `patient_date_reglement` IS NOT NULL;";
        $this->addQuery($query);


        // On crée les règlements des tiers
        $query = "INSERT INTO `reglement` (
      `emetteur`,
      `consultation_id`, 
      `date`, 
      `montant`,
      `mode`)
      
      SELECT 
        'tiers',
        `consultation_id`, 
        `tiers_date_reglement`, 
        `du_tiers`, 
        `tiers_mode_reglement`
      FROM 
        `consultation`
      WHERE 
        `tiers_date_reglement` IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.05");
        $query = "ALTER TABLE `acte_ngap` 
      ADD `executant_id` int(11) unsigned NOT NULL DEFAULT '0',
      ADD INDEX (`executant_id`)";
        $this->addQuery($query);

        // COperation : executant_id = operations -> chir_id
        // CSejour : executant_id = sejour -> praticien_id
        // CConsultation : executant_id = consultation -> plageconsult -> chir_id
        $query = "UPDATE `acte_ngap` 
       SET `acte_ngap`.`executant_id` = 
        (SELECT `chir_id` 
         FROM `operations` 
         WHERE `operations`.`operation_id` = `acte_ngap`.`object_id`
         LIMIT 1)
       WHERE 
        `acte_ngap`.`object_class` = 'COperation' AND 
        `acte_ngap`.`executant_id` = 0";
        $this->addQuery($query);

        $query = "UPDATE `acte_ngap` 
       SET `acte_ngap`.`executant_id` = 
        (SELECT `praticien_id` 
         FROM `sejour` 
         WHERE `sejour`.`sejour_id` = `acte_ngap`.`object_id`
         LIMIT 1)
       WHERE 
        `acte_ngap`.`object_class` = 'CSejour' AND 
        `acte_ngap`.`executant_id` = 0";
        $this->addQuery($query);

        $query = "UPDATE `acte_ngap` 
       SET `acte_ngap`.`executant_id` = 
        (SELECT `plageconsult`.`chir_id` 
         FROM `plageconsult`, `consultation`
         WHERE 
           `consultation`.`consultation_id` = `acte_ngap`.`object_id` AND
           `plageconsult`.`plageconsult_id` = `consultation`.`plageconsult_id`
         LIMIT 1)
       WHERE 
        `acte_ngap`.`object_class` = 'CConsultation' AND 
        `acte_ngap`.`executant_id` = 0";
        $this->addQuery($query);

        $this->makeRevision("1.07");
        $this->addPrefQuery("resumeCompta", "1");

        $this->makeRevision("1.08");
        $this->addPrefQuery("VitaleVisionDir", "");
        $this->addPrefQuery("VitaleVision", "0");

        $this->makeRevision("1.09");
        $query = "UPDATE `consultation` 
      SET du_tiers = ROUND(secteur1 + secteur2 - du_patient, 2)
      WHERE ROUND(secteur1 + secteur2 - du_tiers - du_patient, 2) != 0
      AND ABS(ROUND(secteur1 + secteur2 - du_tiers - du_patient, 2)) > 1
      AND valide = '1'";
        $this->addQuery($query);

        $this->makeRevision("1.10");
        $this->addPrefQuery("showDatesAntecedents", "1");

        $this->makeRevision("1.11");
        $query = "ALTER TABLE `consultation_anesth` 
              ADD `chir_id` INT (11) UNSIGNED AFTER `sejour_id`,
              ADD `date_interv` DATE AFTER `chir_id`,
              ADD `libelle_interv` VARCHAR (255) AFTER `date_interv`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation_anesth` 
              ADD INDEX (`sejour_id`),
              ADD INDEX (`chir_id`),
              ADD INDEX (`date_interv`);";
        $this->addQuery($query);

        $this->makeRevision("1.12");
        $query = "ALTER TABLE `consultation`
              ADD `histoire_maladie` TEXT AFTER `traitement`,
              ADD `conclusion` TEXT AFTER `histoire_maladie`";
        $this->addQuery($query);

        $this->makeRevision("1.13");
        $query = "ALTER TABLE `consultation` 
            ADD `concerne_ALD` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.14");
        $query = "ALTER TABLE `consultation_anesth` 
            ADD `date_analyse` DATE;";
        $this->addQuery($query);

        $this->makeRevision("1.15");
        $query = "ALTER TABLE `consultation` 
              ADD `facture` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.16");
        $this->addPrefQuery("dPcabinet_show_program", "1");


        $this->makeRevision("1.17");
        $query = "ALTER TABLE `acte_ngap` 
              ADD INDEX (`object_id`),
              ADD INDEX (`object_class`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation` 
              ADD INDEX (`sejour_id`),
              ADD INDEX (`tiers_date_reglement`),
              ADD INDEX (`arrivee`),
              ADD INDEX (`categorie_id`),
              ADD INDEX (`accident_travail`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation_anesth` 
              ADD INDEX (`date_analyse`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation_cat` 
              ADD INDEX (`function_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `examigs` 
              ADD INDEX (`consultation_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `exams_comp` 
              ADD INDEX (`consultation_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `reglement` 
              ADD INDEX (`consultation_id`),
              ADD INDEX (`banque_id`),
              ADD INDEX (`date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `techniques_anesth` 
              ADD INDEX (`consultation_anesth_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.18");
        $this->addPrefQuery("pratOnlyForConsult", "1");

        $this->makeRevision("1.19");
        $query = "ALTER TABLE `consultation_anesth` 
      DROP `intubation`";
        $this->addQuery($query);

        $this->makeRevision("1.20");
        $query = "UPDATE plageconsult SET fin = '23:59:59' WHERE fin = '00:00:00'";
        $this->addQuery($query);

        $this->makeRevision("1.21");
        $query = "ALTER TABLE `consultation_anesth`
              DROP `biologie`,
              DROP `commande_sang`;";
        $this->addQuery($query);

        $this->makeRevision("1.22");
        $query = "ALTER TABLE `consultation_anesth`
              ADD `groupe_ok` ENUM ('0','1') NOT NULL DEFAULT '0' AFTER `groupe`,
              ADD `fibrinogene` FLOAT AFTER `creatinine`,
              ADD `result_ecg` TEXT AFTER `ht_final`,
              ADD `result_rp` TEXT AFTER `result_ecg`;";
        $this->addQuery($query);

        $this->makeRevision("1.23");
        $query = "ALTER TABLE `consultation`
              ADD `adresse_par_prat_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.24");
        $query = "ALTER TABLE `consultation` ADD `si_desistement` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.25");
        $query = "ALTER TABLE `plageconsult` ADD `locked` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.26");
        $this->addPrefQuery("AFFCONSULT", "0");
        $this->addPrefQuery("MODCONSULT", "0");


        $this->makeRevision("1.27");
        $query = "ALTER TABLE `acte_ngap` ADD `lettre_cle` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $query = "UPDATE `acte_ngap` 
       SET `acte_ngap`.`lettre_cle` = '1'
       WHERE 
        `acte_ngap`.`code` IN ('C','K','KA','KC','KCC','KE','KFA','KFB','KFD','ORT','PRO','PRA'
                               'SCM','V','Z','ZN','LC','LCM','LFA','LFB','LFD','LK','LKC','LKE'
                               'LRA','LRO','LV','LZ','LZM','LZN','CS','VS','LCC','LCS','LVS',
                               'AMI','AIS','DI')";
        $this->addQuery($query);

        $this->makeRevision("1.28");
        $query = "ALTER TABLE `consultation` CHANGE `accident_travail` `date_at` DATE DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("1.29");
        $query = "ALTER TABLE `consultation`
      ADD `fin_at` DATETIME DEFAULT NULL,
      ADD `pec_at` ENUM ('soins', 'arret') DEFAULT NULL,
      ADD `reprise_at` DATETIME DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("1.30");
        $query = "ALTER TABLE `plageconsult`
      ADD `remplacant_id` BIGINT DEFAULT '0' NOT NULL,
      ADD `desistee` ENUM ('0', '1') NOT NULL DEFAULT '0',
      ADD `remplacant_ok` ENUM ('0', '1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.33");
        $query = "ALTER TABLE `plageconsult`
     CHANGE `remplacant_id` `remplacant_id` BIGINT DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("1.34");
        $query = "ALTER TABLE `plageconsult`
     CHANGE `remplacant_id` `remplacant_id` INT(11) UNSIGNED";
        $this->addQuery($query);

        $query = "UPDATE `plageconsult`
     SET `remplacant_id`=NULL WHERE `remplacant_id` = '0'";

        $this->addQuery($query);
        $this->makeRevision("1.35");
        $this->addPrefQuery("displayDocsConsult", "1");
        $this->addPrefQuery("displayPremedConsult", "1");
        $this->addPrefQuery("displayResultsConsult", "1");

        $this->makeRevision("1.36");
        $query = "UPDATE `consultation`
      SET chrono = '16'
      WHERE annule = '1'";
        $this->addQuery($query);

        $this->makeRevision("1.37");
        $query = "ALTER TABLE `consultation_anesth`
              ADD `apfel_femme` ENUM ('0','1') DEFAULT '0',
              ADD `apfel_non_fumeur` ENUM ('0','1') DEFAULT '0',
              ADD `apfel_atcd_nvp` ENUM ('0','1') DEFAULT '0',
              ADD `apfel_morphine` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.38");
        $query = "ALTER TABLE `consultation_anesth` 
      ADD `examenAutre` TEXT NULL AFTER `examenPulmo` ,
      ADD `examenDigest` TEXT NULL AFTER `examenPulmo` ;";
        $this->addQuery($query);

        $this->makeRevision("1.39");
        $query = "ALTER TABLE `consultation` 
      ADD `type` ENUM ('classique','entree') DEFAULT 'classique'";
        $this->addQuery($query);

        $this->makeRevision("1.40");

        $query = "ALTER TABLE `tarifs` ADD `codes_tarmed` VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision("1.41");

        $query = "ALTER TABLE `consultation` 
      ADD `grossesse_id` INT (11) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("1.42");
        $query = "ALTER TABLE `consultation`
      CHANGE `type` `type` ENUM ('classique','entree','chimio') DEFAULT 'classique';";
        $this->addQuery($query);

        $this->makeRevision("1.43");
        $this->addPrefQuery("choosePatientAfterDate", "0");

        $this->makeRevision("1.44");

        $query = "ALTER TABLE `consultation` 
      ADD `remise`  VARCHAR(10) NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.45");
        $query = "ALTER TABLE `consultation_cat` 
      CHANGE `function_id` `function_id` INT (11) UNSIGNED NOT NULL,
      ADD `duree` TINYINT (4) UNSIGNED NOT NULL DEFAULT '1',
      ADD `commentaire` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.46");
        $query = "ALTER TABLE `consultation`
      ADD `at_sans_arret` ENUM ('0','1') DEFAULT '0',
      ADD `arret_maladie` ENUM ('0','1') DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("1.47");

        $query = "ALTER TABLE `consultation` 
      DROP `remise`;";
        $this->addQuery($query);

        $this->makeRevision("1.48");

        $this->addPrefQuery("viewFunctionPrats", "0");

        $this->makeRevision("1.49");

        $query = "CREATE TABLE `factureconsult` (
         `factureconsult_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
         `patient_id` int(11) unsigned NOT NULL,
         `rabais` FLOAT DEFAULT '0' NOT NULL,
         `ouverture` date NOT NULL,
         `cloture` date,
         `du_patient` float NOT NULL DEFAULT '0.0',
         `du_tiers` float NOT NULL DEFAULT '0.0',
         PRIMARY KEY (`factureconsult_id`),
         INDEX (`patient_id`) )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE factureconsult 
              ADD INDEX (`ouverture`), 
              ADD INDEX (`cloture`);";
        $this->addQuery($query);

        $this->makeRevision("1.50");

        $query = "ALTER TABLE `factureconsult` 
              ADD `type_facture` enum ('maladie','accident') NOT NULL default 'maladie';";
        $this->addQuery($query);

        $query = "ALTER TABLE `reglement`
      CHANGE `consultation_id` `object_id` INT(11) UNSIGNED ,
      ADD `object_class`  ENUM ('CConsultation','CFactureConsult') NOT NULL default 'CConsultation';";
        $this->addQuery($query);

        $this->makeRevision("1.51");

        $query = "ALTER TABLE `consultation` 
              ADD `factureconsult_id` INT(11) UNSIGNED NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.52");

        $query = "ALTER TABLE `factureconsult` 
              ADD `patient_date_reglement` DATE,
              ADD `tiers_date_reglement` DATE;";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureconsult`
      CHANGE `rabais` `remise` FLOAT DEFAULT '0' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.53");

        $this->addPrefQuery("viewWeeklyConsultCalendar", "0");

        $this->makeRevision("1.54");

        $query = "ALTER TABLE `consultation` 
              ADD INDEX (`grossesse_id`),
              ADD INDEX (`factureconsult_id`);";
        $this->addQuery($query);
        $this->makeRevision("1.55");

        $query = "ALTER TABLE `factureconsult` 
              CHANGE `remise` `remise` DECIMAL (10,2) DEFAULT  '0';";
        $this->addQuery($query);
        $this->makeRevision("1.56");

        $query = "ALTER TABLE `tarifs` ADD `codes_caisse` VARCHAR(255);";
        $this->addQuery($query);
        $this->makeRevision("1.57");

        $query = "ALTER TABLE `reglement` 
              CHANGE `mode` `mode` ENUM( 'cheque', 'CB', 'especes', 'virement', 'BVR', 'autre' ),
              ADD `num_bvr` VARCHAR(50);";
        $this->addQuery($query);

        $this->makeRevision("1.58");

        $query = "ALTER TABLE `plageconsult`
              ADD `color` VARCHAR(6) NOT NULL DEFAULT 'DDDDDD' ;";
        $this->addQuery($query);

        $this->makeRevision("1.59");

        $query = "ALTER TABLE `factureconsult`
              ADD `npq`  ENUM('0','1') DEFAULT '0',
              ADD `cession_creance` ENUM('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.60");
        $query = "ALTER TABLE `examigs` 
              ADD `sejour_id` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `examigs` ADD INDEX (`sejour_id`);";
        $this->addQuery($query);

        $query = "UPDATE `examigs`
              SET examigs.sejour_id = (SELECT sejour.sejour_id
                               FROM sejour
                               LEFT JOIN consultation_anesth ON sejour.sejour_id = consultation_anesth.sejour_id
                               WHERE consultation_anesth.consultation_id = examigs.consultation_id);";
        $this->addQuery($query);

        $query = "UPDATE `examigs`
              SET sejour_id = (SELECT sejour.sejour_id
                               FROM sejour
                               LEFT JOIN operations ON operations.sejour_id = sejour.sejour_id
                               LEFT JOIN consultation_anesth ON operations.operation_id = consultation_anesth.operation_id
                               WHERE consultation_anesth.consultation_id = examigs.consultation_id)
              WHERE examigs.sejour_id = '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `examigs` DROP `consultation_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.61");

        $query = "UPDATE `plageconsult`
              SET color = 'DDDDDD'
              WHERE color = 'DDD'";
        $this->addQuery($query);

        $this->makeRevision("1.62");
        $query = "ALTER TABLE `consultation`
              ADD `derniere` ENUM ('0','1') DEFAULT '0' AFTER `premiere`;";
        $this->addQuery($query);

        $this->makeRevision("1.63");

        $query = "ALTER TABLE `factureconsult`
              ADD `facture`  ENUM('-1','0','1') DEFAULT '0',
              ADD `assurance`  INT(11) UNSIGNED NULL;";
        $this->addQuery($query);
        $this->makeRevision("1.64");

        $query = "ALTER TABLE `factureconsult` 
              ADD `praticien_id` INT (11) UNSIGNED AFTER `patient_id`,
              CHANGE `npq` `npq` ENUM ('0','1') NOT NULL DEFAULT '0',
              CHANGE `cession_creance` `cession_creance` ENUM ('0','1') NOT NULL DEFAULT '0',
              CHANGE `facture` `facture` ENUM ('-1','0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `factureconsult` 
              ADD INDEX (`praticien_id`),
              ADD INDEX (`ouverture`),
              ADD INDEX (`cloture`),
              ADD INDEX (`patient_date_reglement`),
              ADD INDEX (`tiers_date_reglement`),
              ADD INDEX (`assurance`);";
        $this->addQuery($query);

        $query = "UPDATE `factureconsult`
              SET `factureconsult`.praticien_id = (SELECT plageconsult.chir_id
                               FROM  plageconsult , consultation
                               WHERE consultation.factureconsult_id = `factureconsult`.factureconsult_id
                               AND plageconsult.plageconsult_id = consultation.plageconsult_id
                               LIMIT 1)
              WHERE factureconsult.praticien_id IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.65");

        $query = "ALTER TABLE `factureconsult`
              ADD `ref_accident` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("1.66");
        $query = "ALTER TABLE `consultation`
      ADD `brancardage` TEXT;";

        $this->addQuery($query);

        $this->makeRevision("1.67");

        $query = "ALTER TABLE `consultation_anesth`
      ADD `intub_difficile` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision("1.68");
        $query = "ALTER TABLE `examigs` CHANGE `diurese` `diurese` ENUM('11','12','4','0');";
        $this->addQuery($query);

        $this->makeRevision("1.69");

        $query = "ALTER TABLE `examigs` 
      ADD `date` DATETIME AFTER examigs_id";
        $this->addQuery($query);

        $this->makeRevision("1.70");

        $query = "ALTER TABLE `plageconsult` 
                ADD `pct_retrocession` INT (11) DEFAULT '70';";
        $this->addQuery($query);

        $query = "ALTER TABLE `plageconsult` 
                ADD INDEX (`remplacant_id`);";
        $this->addQuery($query);
        $this->makeRevision("1.71");

        $query = "ALTER TABLE `plageconsult` 
                CHANGE `pct_retrocession` `pct_retrocession` FLOAT DEFAULT '70';";
        $this->addQuery($query);

        $this->makeRevision("1.72");
        $query = "ALTER TABLE `examigs` 
                CHANGE `glascow` `glasgow` ENUM('26','13','7','5','0');";
        $this->addQuery($query);

        $this->makeRevision("1.73");
        $this->addPrefQuery("empty_form_atcd", "0");

        $this->makeRevision("1.74");
        $this->addPrefQuery("new_semainier", "0");

        $this->makeRevision("1.75");
        $query = "UPDATE consultation, factureconsult
                SET consultation.patient_date_reglement = factureconsult.patient_date_reglement
                WHERE consultation.factureconsult_id = factureconsult.factureconsult_id";
        $this->addQuery($query);

        $this->makeRevision("1.76");
        $query = "ALTER TABLE `reglement` 
                ADD `reference` VARCHAR (255)";
        $this->addQuery($query);

        $this->makeRevision("1.77");
        $query = "ALTER TABLE `factureconsult` 
              ADD `statut_pro` ENUM ('chomeur','salarie','independant','non_travailleur','sans_emploi','etudiant');";
        $this->addQuery($query);
        $this->makeRevision("1.78");

        $query = "ALTER TABLE `plageconsult` 
              ADD `pour_compte_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `plageconsult` 
              ADD INDEX (`pour_compte_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.79");

        $query = "ALTER TABLE `factureconsult` 
              CHANGE `assurance` `assurance_base` INT (11) UNSIGNED,
              ADD `assurance_complementaire` INT (11) UNSIGNED,
              CHANGE `statut_pro` `statut_pro` ENUM ('chomeur','etudiant','non_travailleur','independant','salarie','sans_emploi');";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureconsult` 
              ADD INDEX (`assurance_base`),
              ADD INDEX (`assurance_complementaire`);";
        $this->addQuery($query);
        $this->makeRevision("1.80");

        $query = "ALTER TABLE `factureconsult` DROP INDEX `assurance`";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureconsult` 
              ADD `send_assur_base` ENUM ('0','1') DEFAULT '0',
              ADD `send_assur_compl` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("1.81");

        $query = "ALTER TABLE `factureconsult` 
              ADD `num_reference` VARCHAR (27);";
        $this->addQuery($query);

        $this->makeRevision("1.82");
        $this->addPrefQuery("order_mode_grille", "");

        $this->makeRevision("1.83");

        $query = "ALTER TABLE `acte_ngap`
                ADD `facturable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("1.84");

        $query = "ALTER TABLE `factureconsult` 
              CHANGE `assurance_base` `assurance_maladie` INT (11) UNSIGNED,
              CHANGE `assurance_complementaire` `assurance_accident` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.85");

        $this->addDependency("dPplanningOp", "1.70");
        $query = "ALTER TABLE `consultation_anesth` 
              DROP `position`,
              DROP `ASA`;";
        $this->addQuery($query);
        $this->makeRevision("1.86");

        $query = "ALTER TABLE `factureconsult` 
              ADD `envoi_xml` ENUM ('0','1') DEFAULT '1',
              CHANGE `factureconsult_id` `facture_id` INT (11) UNSIGNED NOT NULL auto_increment,
              ADD `rques_assurance_maladie` TEXT,
              ADD `rques_assurance_accident` TEXT;";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation` 
              CHANGE `factureconsult_id` `facture_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `reglement` 
              CHANGE `object_class` `object_class` ENUM ('CConsultation','CFactureConsult','CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CConsultation';";
        $this->addQuery($query);

        $query = "RENAME TABLE `factureconsult` TO `facture_cabinet`;";
        $this->addQuery($query);

        $query = "UPDATE reglement
                SET reglement.object_class = 'CFactureCabinet'
                WHERE reglement.object_class = 'CFactureConsult';";
        $this->addQuery($query);

        $query = "ALTER TABLE `reglement` 
              CHANGE `object_class` `object_class` ENUM ('CConsultation','CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CConsultation';";
        $this->addQuery($query);
        $this->makeRevision("1.87");

        $query = "UPDATE user_log
                SET user_log.object_class = 'CFactureCabinet'
                WHERE user_log.object_class = 'CFactureConsult';";
        $this->addQuery($query);
        $this->makeRevision("1.88");

        $query = "UPDATE id_sante400
                SET id_sante400.object_class = 'CFactureCabinet'
                WHERE id_sante400.object_class = 'CFactureConsult';";
        $this->addQuery($query);

        $this->makeRevision("1.89");

        $query = "ALTER TABLE `acte_ngap`
                ADD `lieu` ENUM('C', 'D') DEFAULT 'C' NOT NULL,
                ADD `exoneration` ENUM('N', '13', '15', '17', '19') DEFAULT 'N' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.90");
        $query = "ALTER TABLE `consultation`
                ADD `num_at` INT (11) AFTER `date_at`,
                ADD `cle_at` INT (11) AFTER `num_at`";
        $this->addQuery($query);

        $this->makeRevision("1.91");
        $query = "ALTER TABLE `consultation`
                ADD `type_assurance` ENUM('classique','at','maternite','smg');";
        $this->addQuery($query);
        $this->makeRevision("1.92");

        $query = "ALTER TABLE `facture_cabinet` 
              ADD `consultation_id` INT (11);";
        $this->addQuery($query);

        $query = "INSERT INTO `facture_cabinet` (`patient_id` ,`praticien_id`,`ouverture`,`cloture`,`du_patient`,`du_tiers`,`patient_date_reglement`,`tiers_date_reglement`, `consultation_id`)
        SELECT c.patient_id, p.chir_id, p.date, p.date, c.du_patient, c.du_tiers,
          c.patient_date_reglement, c.tiers_date_reglement, c.consultation_id
        FROM consultation c, plageconsult p
        WHERE c.facture_id IS NULL
        AND c.plageconsult_id = p.plageconsult_id
        AND c.valide = '1'
        GROUP BY c.consultation_id;";
        $this->addQuery($query);

        $query = "UPDATE consultation c, facture_cabinet f
          SET c.facture_id = f.facture_id
          WHERE c.consultation_id = f.consultation_id;";
        $this->addQuery($query);

        $query = "UPDATE reglement r, consultation c, facture_cabinet f
          SET r.object_id = f.facture_id,
              r.object_class = 'CFactureCabinet'
          WHERE r.object_id = c.consultation_id
          AND r.object_class = 'CConsultation'
          AND f.consultation_id = c.consultation_id;";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_cabinet` 
              DROP `consultation_id`;";
        $this->addQuery($query);
        $this->makeRevision("1.93");

        $query = "ALTER TABLE `reglement` 
              CHANGE `object_class` `object_class` ENUM ('CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CFactureCabinet';";
        $this->addQuery($query);

        $this->makeRevision("1.94");

        $query = "ALTER TABLE `acte_ngap`
              ADD `ald` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.95");
        $this->addPrefQuery("create_dossier_anesth", "1");

        $this->makeRevision("1.96");
        $this->addDependency("dPfacturation", "0.21");

        $this->makeRevision("1.97");

        $query = "UPDATE plageconsult p
              SET p.remplacant_id = NULL
              WHERE p.chir_id = p.remplacant_id;";
        $this->addQuery($query);
        $query = "UPDATE plageconsult p
              SET p.pour_compte_id = NULL
              WHERE p.chir_id = p.pour_compte_id;";
        $this->addQuery($query);
        $this->makeRevision("1.98");

        $query = "ALTER TABLE `tarifs` 
                ADD `group_id` INT (11) UNSIGNED";
        $this->addQuery($query);
        $query = "ALTER TABLE `tarifs` 
                    ADD INDEX (`group_id`);";
        $this->addQuery($query);
        $this->makeRevision("1.99");

        $query = "ALTER TABLE `acte_ngap`
                ADD `numero_dent` TINYINT (4) UNSIGNED,
                ADD `comment` VARCHAR (255);";
        $this->addQuery($query);
        $this->makeRevision("2.00");

        $query = "ALTER TABLE `facture_cabinet` 
                CHANGE `statut_pro` `statut_pro` ENUM ('chomeur','etudiant','non_travailleur','independant','invalide','militaire','retraite','salarie_fr','salarie_sw','sans_emploi');";
        $this->addQuery($query);

        $this->makeRevision("2.01");
        $query = "ALTER TABLE `consultation_anesth`
                ADD `plus_de_55_ans` ENUM ('0','1') DEFAULT '0' AFTER `intub_difficile`,
                ADD `imc_sup_26` ENUM ('0','1') DEFAULT '0' AFTER `plus_de_55_ans`,
                ADD `edentation` ENUM ('0','1') DEFAULT '0' AFTER `imc_sup_26`,
                ADD `ronflements` ENUM ('0','1') DEFAULT '0' AFTER `edentation`,
                ADD `barbe` ENUM ('0','1') DEFAULT '0' AFTER `ronflements`;";
        $this->addQuery($query);

        $this->makeRevision("2.02");

        $query = "ALTER TABLE `acte_ngap`
                ADD `execution` DATETIME NOT NULL;";

        $this->addQuery($query);

        $query = "UPDATE `acte_ngap`
                INNER JOIN `consultation` ON (`acte_ngap`.`object_id` = `consultation`.`consultation_id`)
                INNER JOIN `plageconsult` ON (`consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`)
                SET `acte_ngap`.`execution` = CONCAT(`plageconsult`.`date`, ' ', `consultation`.`heure`)
                WHERE `acte_ngap`.`object_class` = 'CConsultation';";
        $this->addQuery($query);

        $query = "UPDATE `acte_ngap`
                INNER JOIN `operations` ON (`acte_ngap`.`object_id` = `operations`.`operation_id`)
                INNER JOIN `plagesop` ON (`operations`.`plageop_id` = `plagesop`.`plageop_id`)
                SET `acte_ngap`.`execution` = CONCAT(`plagesop`.`date`, ' ', `operations`.`time_operation`)
                WHERE `acte_ngap`.`object_class` = 'COperation'
                AND `operations`.`date` IS NULL;";
        $this->addQuery($query);

        $query = "UPDATE `acte_ngap`
                INNER JOIN `operations` ON (`acte_ngap`.`object_id` = `operations`.`operation_id`)
                SET `acte_ngap`.`execution` = CONCAT(`operations`.`date`, ' ', `operations`.`time_operation`)
                WHERE `acte_ngap`.`object_class` = 'COperation'
                AND `operations`.`date` IS NOT NULL;";
        $this->addQuery($query);

        $query = "UPDATE `acte_ngap`
                INNER JOIN `sejour` ON (`acte_ngap`.`object_id` = `sejour`.`sejour_id`)
                SET `acte_ngap`.`execution` = `sejour`.`entree`
                WHERE `acte_ngap`.`object_class` = 'CSejour';";
        $this->addQuery($query);

        $this->makeRevision("2.03");
        $query = "UPDATE `plageconsult`
                SET `plageconsult`.`freq` = '00:15:00'
                WHERE `plageconsult`.`freq` < '00:05:00';";
        $this->addQuery($query);

        $this->makeRevision("2.04");
        $this->addPrefQuery("showIntervPlanning", "0");

        $this->makeRevision("2.05");
        $query = "ALTER TABLE `facture_cabinet`
                ADD `annule` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.06");

        $query = "ALTER TABLE `plageconsult`
      ADD `pour_tiers` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.07");

        $query = "ALTER TABLE `reglement`
                ADD `tireur` VARCHAR (255);";
        $this->addQuery($query);
        $this->makeRevision("2.08");

        $query = "ALTER TABLE `facture_cabinet`
                ADD `definitive` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.09");

        $this->addPrefQuery("NbConsultMultiple", 4);
        $this->makeRevision("2.10");

        $query = "ALTER TABLE `facture_cabinet`
                CHANGE `type_facture` `type_facture` ENUM ('maladie','accident','esthetique') NOT NULL DEFAULT 'maladie';";
        $this->addQuery($query);
        $this->makeRevision("2.11");

        $query = "ALTER TABLE `reglement`
                ADD `debiteur_id` INT (11) UNSIGNED,
                ADD `debiteur_desc` VARCHAR (255);";
        $this->addQuery($query);
        $query = "ALTER TABLE `reglement`
                ADD INDEX (`debiteur_id`);";
        $this->addQuery($query);
        $this->makeRevision("2.12");

        $query = "ALTER TABLE `consultation_anesth`
              ADD `result_autre` TEXT AFTER `result_rp`;";
        $this->addQuery($query);
        $this->addPrefQuery("viewAutreResult", "0");
        $this->makeRevision("2.13");

        $this->addPrefQuery("use_acte_date_now", "0");
        $this->makeRevision("2.14");

        $query = "ALTER TABLE `acte_ngap`
                ADD `num_facture` INT (11) UNSIGNED NOT NULL DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                ADD `numero` INT (11) UNSIGNED NOT NULL DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("2.15");

        $query = "ALTER TABLE `consultation`
                ADD `secteur3` FLOAT( 6 ) DEFAULT '0' NOT NULL,
                ADD `du_tva` FLOAT( 6 ) DEFAULT '0' NOT NULL,
                ADD `taux_tva` ENUM ('0', '19.6');";
        $this->addQuery($query);

        $query = "ALTER TABLE `tarifs`
                ADD `secteur3` FLOAT( 6 ) DEFAULT '0' NOT NULL,
                ADD `taux_tva` ENUM ('0', '19.6');";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_cabinet`
                ADD `du_tva` DECIMAL (10,2) DEFAULT '0',
                ADD `taux_tva` ENUM ('0', '19.6') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.16");
        $this->addPrefQuery("multi_popups_resume", "1");

        $this->makeRevision("2.17");
        $this->addPrefQuery("allow_plage_holiday", "1");

        $this->makeRevision("2.18");
        $query = "ALTER TABLE `reglement`
                ADD `lock` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.19");
        // On supprime les champs inutiles des consultations
        $query = 'ALTER TABLE `consultation`
      DROP `tiers_mode_reglement`,
      DROP `patient_mode_reglement`,
      DROP `banque_id`;';
        $this->addQuery($query);

        $this->makeRevision("2.20");
        $this->addDependency("dPpatients", "0.67");

        // On supprime les champs inutiles des consultations anesth
        $query = "ALTER TABLE `consultation_anesth`
      DROP `poid`,
      DROP `taille`,
      DROP `tasys`,
      DROP `tadias`,
      DROP `pouls`,
      DROP `spo2`;";
        $this->addQuery($query);
        $this->makeRevision("2.21");
        $this->addPrefQuery("new_consultation", "0");

        $this->makeRevision("2.22");
        $query = "ALTER TABLE `acte_ngap`
                ADD INDEX (`execution`);";
        $this->addQuery($query);

        $this->makeRevision("2.23");
        $this->addPrefQuery("today_ref_consult_multiple", "1");

        $this->makeRevision("2.24");
        $query = "ALTER TABLE `facture_cabinet`
                CHANGE `taux_tva` `taux_tva` FLOAT DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `tarifs`
                CHANGE `taux_tva` `taux_tva` FLOAT DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
                CHANGE `taux_tva` `taux_tva` FLOAT DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.25");

        $query = "ALTER TABLE `facture_cabinet`
                ADD `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        //Facture de cabinet de consultation
        $query = "UPDATE facture_cabinet, users_mediboard, functions_mediboard
          SET facture_cabinet.group_id = functions_mediboard.group_id
          WHERE facture_cabinet.praticien_id = users_mediboard.user_id
          AND functions_mediboard.function_id = users_mediboard.function_id";
        $this->addQuery($query);

        $this->makeRevision("2.26");

        $query = "ALTER TABLE `acte_ngap`
                MODIFY `code` VARCHAR(5) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("2.27");
        $query = "ALTER TABLE `consultation`
      CHANGE `duree` `duree` INT (4) UNSIGNED NOT NULL DEFAULT '1'";
        $this->addQuery($query);

        $this->makeRevision("2.28");

        $query = "ALTER TABLE `acte_ngap`
                ADD `major_pct` INT (11),
                ADD `major_coef` FLOAT,
                ADD `minor_pct` INT (11),
                ADD `minor_coef` FLOAT,
                ADD `numero_forfait_technique` INT (11) UNSIGNED,
                ADD `numero_agrement` BIGINT (20) UNSIGNED,
                ADD `rapport_exoneration` ENUM ('4','7','C','R');";

        $this->addQuery($query);

        $this->makeRevision("2.29");
        $this->addDefaultConfig("dPcabinet CPrescription view_prescription");

        $this->makeRevision('2.30');

        $query = "ALTER TABLE `examigs`
                ADD `simplified_igs` INT(11);";
        $this->addQuery($query);
        $this->makeRevision('2.31');

        $query = "UPDATE consultation_anesth, operations
          SET consultation_anesth.sejour_id = operations.sejour_id
          WHERE consultation_anesth.sejour_id IS NULL
          AND consultation_anesth.operation_id IS NOT NULL
          AND operations.operation_id = consultation_anesth.operation_id";
        $this->addQuery($query);

        $this->makeRevision("2.32");
        $query = "ALTER TABLE `consultation`
      ADD `element_prescription_id` INT (11) UNSIGNED";
        $this->addQuery($query);

        if (CModule::getActive("dPprescription") && $this->tableExists("prescription_line_element") && $this->tableExists("sejour_task")) {
            $query = "UPDATE `consultation`, `sejour_task`, `prescription_line_element`
        SET `consultation`.`element_prescription_id` = `prescription_line_element`.`element_prescription_id`
        WHERE `sejour_task`.`consult_id` = `consultation`.`consultation_id`
        AND   `sejour_task`.`prescription_line_element_id` = `prescription_line_element`.`prescription_line_element_id`";
            $this->addQuery($query);
        }

        $this->makeRevision('2.33');

        $query = "ALTER TABLE `consultation`
                ADD `org_at` INT(9) UNSIGNED ZEROFILL,
                ADD `feuille_at` ENUM('0', '1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('2.34');

        $query = "ALTER TABLE `acte_ngap`
                ADD `gratuit` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.35');
        // check for old preferences
        $this->addMethod("prefForConsultPratType");

        $this->makeRevision('2.36');
        $query = "ALTER TABLE `consultation`
                ADD `exec_tarif` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
                ADD INDEX (`exec_tarif`);";
        $this->addQuery($query);

        $this->makeRevision("2.37");
        $query = "UPDATE `consultation` SET `annule` = '0' WHERE (`annule` = '' OR `annule` IS NULL );";
        $this->addQuery($query);

        $this->makeRevision('2.38');

        $query = "ALTER TABLE `consultation_anesth`
                ADD `passage_uscpo` ENUM ('0','1'),
                ADD `duree_uscpo` INT (11) UNSIGNED DEFAULT '0',
                ADD `type_anesth` INT (11) UNSIGNED NULL,
                ADD `position` ENUM ('DD','DV','DL','GP','AS','TO','GYN'),
                ADD `ASA` ENUM ('1','2','3','4','5','6'),
                ADD `rques` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("2.39");
        $this->addDefaultConfig("dPcabinet CPrescription view_prescription_externe", "dPcabinet CPrescription view_prescription");

        $this->makeRevision("2.40");
        $query = "ALTER TABLE `consultation_anesth`
                ADD `piercing` ENUM ('0','1') DEFAULT '0' AFTER `ronflements`;";
        $this->addQuery($query);

        $this->makeRevision("2.41");
        $this->addPrefQuery("take_consult_for_dieteticien", "0");

        $this->makeRevision("2.42");
        $this->addPrefQuery("height_calendar", "2000");

        $this->makeRevision("2.43");
        $this->addPrefQuery("dPcabinet_displayFirstTab", "AntTrait");

        $this->makeRevision("2.44");
        $query = "ALTER TABLE `consultation`
      ADD `consult_related_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("2.45");

        $this->addPrefQuery("show_replication_duplicate", "0");

        $this->makeRevision("2.46");
        $query = "ALTER TABLE `consultation_anesth`
              ADD `mob_cervicale` ENUM( 'm80', '80m100', 'p100' );";
        $this->addQuery($query);

        $this->makeRevision('2.47');
        $query = "ALTER TABLE `acte_ngap`
                CHANGE `object_class` `object_class` ENUM('COperation','CSejour','CConsultation', 'CDevisCodage') NOT NULL default 'CConsultation';";
        $this->addQuery($query);

        $this->makeRevision('2.48');
        $query = "ALTER TABLE `acte_ngap`
                ADD `prescripteur_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision('2.49');

        $query = "ALTER TABLE `banque`
                ADD `departement` VARCHAR (255),
                ADD `boite_postale` VARCHAR (255),
                ADD `adresse` TEXT,
                ADD `cp` VARCHAR (5),
                ADD `ville` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('2.50');
        $query = "ALTER TABLE `facture_cabinet`
                ADD `date_cas` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                ADD INDEX (`date_cas`);";
        $this->addQuery($query);

        $this->makeRevision("2.51");
        $this->addPrefQuery("show_plage_holiday", "1");
        $this->makeRevision("2.52");

        $query = "ALTER TABLE `facture_cabinet`
                ADD `request_date` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                ADD INDEX (`request_date`);";
        $this->addQuery($query);
        $this->makeRevision("2.53");

        $query = "ALTER TABLE `consultation`
                ADD `motif_annulation` ENUM ('not_arrived','by_patient')";
        $this->addQuery($query);
        $this->makeRevision("2.54");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `result_com` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("2.55");

        $query = "ALTER TABLE `facture_cabinet`
                CHANGE `statut_pro` `statut_pro` ENUM ('chomeur','etudiant','non_travailleur','independant','invalide','militaire','retraite','salarie_fr','salarie_sw','sans_emploi','enfant','enceinte');";
        $this->addQuery($query);
        $this->makeRevision("2.56");

        $query = "ALTER TABLE `consultation` ADD INDEX `consult_related_id` (`consult_related_id`)";
        $this->addQuery($query, true);
        $this->makeRevision("2.57");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `strategie_antibio` TEXT;";
        $this->addQuery($query, true);
        $this->makeRevision("2.58");

        $query = "ALTER TABLE `facture_cabinet`
                ADD `remarque` TEXT;";
        $this->addQuery($query, true);
        $this->makeRevision("2.59");

        $query = "ALTER TABLE `consultation`
                CHANGE `motif_annulation` `motif_annulation` ENUM ('not_arrived','by_patient','other') DEFAULT 'not_arrived';";
        $this->addQuery($query);
        $this->makeRevision('2.60');

        $query = "ALTER TABLE `plageconsult`
                ADD `send_notifications` ENUM('0', '1') DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision('2.61');

        $query = "ALTER TABLE `facture_cabinet`
                ADD `coeff_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                ADD INDEX (`coeff_id`);";
        $this->addQuery($query);
        $this->makeRevision('2.62');

        $query = "ALTER TABLE `exams_comp`
                ADD `date_bilan` DATE,
                ADD `labo` TEXT;";
        $this->addQuery($query);
        $query = "ALTER TABLE `exams_comp`
                ADD INDEX (`date_bilan`);";
        $this->addQuery($query);
        $this->makeRevision('2.63');

        $this->addFunctionalPermQuery("allowed_new_consultation", "1");

        $this->makeRevision("2.64");
        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `position` `position` ENUM( 'DD', 'DV', 'DL', 'GP', 'AS', 'TO', 'GYN', 'DDA');";
        $this->addQuery($query);
        $this->makeRevision("2.65");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `poids_stable` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.66");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `autorisation` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.67");

        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `autorisation` `autorisation` ENUM ('undefined', '0','1') DEFAULT 'undefined';";
        $this->addQuery($query);
        $this->makeRevision("2.68");

        $query = "ALTER TABLE `facture_cabinet`
                ADD `bill_printed` ENUM ('0','1') DEFAULT '0',
                ADD `justif_printed` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("2.69");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `histoire_maladie` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("2.70");

        $this->addPrefQuery("context_print_futurs_rdv", "cabinet");
        $this->makeRevision("2.71");

        $this->addPrefQuery("dPcabinet_offline_mode_frequency", "0");
        $this->makeRevision("2.72");

        $query = "ALTER TABLE `acte_ngap`
                CHANGE `object_class` `object_class` VARCHAR (80) NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision("2.73");

        $query = "CREATE TABLE `info_checklist` (
                `info_checklist_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `libelle` VARCHAR (255) NOT NULL,
                `actif` ENUM ('0','1') DEFAULT '1',
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `info_checklist_item` (
                `info_checklist_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `info_checklist_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `consultation_anesth_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `reponse` ENUM ('0','1') DEFAULT '0',
                INDEX (`info_checklist_id`),
                INDEX (`consultation_anesth_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("2.74");

        $this->makeEmptyRevision('2.75');

        $query = "ALTER TABLE `consultation_anesth`
                ADD `strategie_prevention` TEXT;";
        $this->addQuery($query);
        $this->makeRevision('2.76');

        $query = "ALTER TABLE `acte_ngap`
                CHANGE `exoneration` `exoneration` ENUM('N', '3', '4','5', '6', '7', '9') DEFAULT 'N',
                ADD `qualif_depense` ENUM('d', 'e', 'f', 'g', 'n', 'da', 'm', 'b', 'c', 'l'),
                ADD `accord_prealable` ENUM('0', '1'),
                ADD `date_demande_accord` DATE,
                ADD `reponse_accord` ENUM('no_answer', 'accepted', 'emergency', 'refused');";
        $this->addQuery($query);
        $this->makeRevision('2.77');

        $query = "ALTER TABLE `consultation`
                ADD `visite_domicile` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.78');

        $query = "ALTER TABLE `tarifs`
                ADD `codes_lpp` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('2.79');
        $query = "ALTER TABLE `consultation` 
                CHANGE `concerne_ALD` `concerne_ALD` ENUM ('0','1');";
        $this->addQuery($query);

        $this->makeRevision('2.80');
        $query = "ALTER TABLE `consultation` 
                 ADD `cormack` ENUM ('grade1','grade2','grade3','grade4');";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` 
                DROP `cormack`;";
        $this->addQuery($query);

        $this->makeRevision('2.81');
        $query = "ALTER TABLE `consultation_anesth` 
                 ADD `cormack` ENUM ('grade1','grade2','grade3','grade4');";
        $this->addQuery($query);

        $this->makeRevision('2.82');
        $query = "ALTER TABLE `consultation_anesth` 
                 ADD `com_comrack` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('2.83');
        $query = "ALTER TABLE `consultation_anesth` 
                 CHANGE `com_comrack` `com_cormack` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("2.84");

        $query = "ALTER TABLE `consultation`
      ADD `docs_necessaires` TEXT";
        $this->addQuery($query);
        $this->makeRevision("2.85");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `depassement_anesth` DECIMAL (10,3),
                ADD INDEX (`type_anesth`);";
        $this->addQuery($query);
        $this->makeRevision("2.86");

        $query = "ALTER TABLE `consultation_anesth`
                CHANGE `depassement_anesth` `depassement_anesth` DECIMAL (10,2);";
        $this->addQuery($query);

        $this->makeRevision("2.87");
        $query = "ALTER TABLE `consultation_anesth`
              ADD `tourCou` ENUM('p45');";
        $this->addQuery($query);
        $this->makeRevision("2.88");

        $query = "ALTER TABLE `info_checklist`
                ADD `function_id` INT (11) UNSIGNED AFTER `group_id`,
                CHANGE `actif` `actif` ENUM ('0','1') DEFAULT '1',
                ADD INDEX (`function_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `info_checklist_item`
                CHANGE `consultation_anesth_id` `consultation_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                ADD `consultation_class` ENUM ('CConsultAnesth','CConsultation') NOT NULL DEFAULT 'CConsultAnesth',
                ADD INDEX (`consultation_id`);";
        $this->addQuery($query);

        $this->makeRevision("2.89");
        $query = "ALTER TABLE `consultation` 
                ADD `demande_nominativement` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("2.90");
        $this->addPrefQuery("show_text_complet", "0");

        $this->makeRevision('2.91');
        $query = "ALTER TABLE `consultation` 
                ADD COLUMN `date_creation_anterieure` DATETIME,
                ADD COLUMN `agent`                    VARCHAR(255),
                ADD INDEX (`date_creation_anterieure`);";
        $this->addQuery($query);

        $this->makeRevision("2.92");
        $this->addPrefQuery("search_free_slot", "0");

        $this->makeRevision('2.93');
        $query = "ALTER TABLE `consultation`
      CHANGE `brancardage` `brancardage` TEXT AFTER `histoire_maladie`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
      ADD `projet_soins` TEXT AFTER `brancardage`;";
        $this->addQuery($query);
        $this->makeRevision('2.94');

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `msg_error_xml` TEXT;";
        $this->addQuery($query);

        $this->makeRevision('2.95');
        $query = "CREATE TABLE `groupe_seance` (
                `groupe_seance_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `function_id` INT (11) UNSIGNED NOT NULL,
                `category_id` INT (11) UNSIGNED NOT NULL,
               INDEX (`patient_id`),
               INDEX (`function_id`),
               INDEX (`category_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation_cat` 
                ADD `seance` ENUM ('0','1') DEFAULT '0',
                ADD `max_seances` INT (11) DEFAULT '25',
                ADD `anticipation` INT (11) DEFAULT '5';";
        $this->addQuery($query);

        $this->makeRevision("2.96");
        $query = "CREATE TABLE `ressource_cab` (
      `ressource_cab_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `function_id` INT (11) UNSIGNED NOT NULL,
      `owner_id` INT (11) UNSIGNED NOT NULL,
      `libelle` VARCHAR (255),
      `description` TEXT,
      `color` VARCHAR (6),
      `actif` ENUM ('0','1') DEFAULT '1'
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `ressource_cab` 
      ADD INDEX (`function_id`),
      ADD INDEX (`owner_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `plage_ressource_cab` (
      `plage_ressource_cab_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `ressource_cab_id` INT (11) UNSIGNED NOT NULL,
      `date` DATE NOT NULL,
      `debut` TIME NOT NULL,
      `fin` TIME NOT NULL,
      `libelle` VARCHAR (255) NOT NULL,
      `freq` TIME DEFAULT '00:15:00' NOT NULL,
      `color` VARCHAR (6)
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `plage_ressource_cab` 
      ADD INDEX (`ressource_cab_id`),
      ADD INDEX (`date`),
      ADD INDEX (`debut`),
      ADD INDEX (`fin`);";
        $this->addQuery($query);
        $this->makeRevision("2.97");

        $this->addPrefQuery("see_plages_consult_libelle", "");
        $this->makeRevision("2.98");

        $query = "CREATE TABLE `reservation` (
      `reservation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `plage_ressource_cab_id` INT (11) UNSIGNED NOT NULL,
      `patient_id` INT (11) UNSIGNED NOT NULL,
      `date` DATE NOT NULL,
      `heure` TIME NOT NULL,
      `duree` INT (11) UNSIGNED NOT NULL,
      `motif` VARCHAR (255)
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `reservation` 
      ADD INDEX (`reservation_id`),
      ADD INDEX (`plage_ressource_cab_id`),
      ADD INDEX (`patient_id`),
      ADD INDEX (`date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
      ADD `groupee` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.99");
        $query = "ALTER TABLE `consultation`
      ADD `no_patient` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("3.00");
        $query = "ALTER TABLE `consultation_anesth` 
                CHANGE `mallampati` `mallampati` ENUM ('classe1','classe2','classe3','classe4','no_eval');";
        $this->addQuery($query);

        $this->makeRevision("3.01");

        $query = "ALTER TABLE `consultation_anesth` 
                ADD `bouche_enfant` ENUM ('m3doigts','p3doigts','no_eval') AFTER `bouche`;";
        $this->addQuery($query);
        $this->makeRevision("3.02");

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `montant_total` DECIMAL (10,3) DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("3.03");

        $query = "ALTER TABLE `facture_cabinet` 
                CHANGE `statut_pro` `statut_pro` VARCHAR(20)";
        $this->addQuery($query);
        $this->makeRevision("3.04");
        $this->addDependency("dPpatients", "1.97");
        $this->addDependency("dPfacturation", "0.39");

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `category_id` INT (11) UNSIGNED,
                ADD INDEX (`category_id`);";
        $this->addQuery($query);

        // Suppression de champs inutilisés dans consultation_anesth et consultation
        $query = "ALTER TABLE `consultation_anesth`
                DROP `rhesus`,
                DROP `groupe_ok`,
                DROP `groupe`";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` DROP `facture_id`";
        $this->addQuery($query);
        $this->makeRevision("3.05");

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `rcc` VARCHAR (25);";
        $this->addQuery($query);

        $this->makeRevision("3.06");
        $this->addPrefQuery("hide_diff_func_atcd", "0");

        $this->makeRevision("3.07");
        $this->addPrefQuery("ant_trai_grid_list_mode", "0");

        $this->makeRevision("3.08");
        $query = "ALTER TABLE `consultation` 
                ADD `resultats` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("3.09");

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `no_relance` ENUM ('0','1') DEFAULT '0',
                ADD `compte_ch_id` INT (11) UNSIGNED,
                ADD INDEX (`compte_ch_id`);";
        $this->addQuery($query);
        $this->makeRevision("3.10");

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `num_compta` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("3.11");

        $query = "ALTER TABLE `consultation_anesth`
                ADD `risque_intub` ENUM ('low','medium','high'),
                ADD `au_total` TEXT;";
        $this->addQuery($query);


        $this->makeRevision("3.12");

        $query = "CREATE TABLE `reunion` (
                `reunion_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `motif` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `consultation` 
               	ADD `reunion_id` INT (11) UNSIGNED,
               	ADD `next_meeting` ENUM ('0','1') DEFAULT '0',
               	ADD INDEX (`reunion_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `patient_reunion` (
                `patient_reunion_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `reunion_id` INT (11) UNSIGNED NOT NULL,
                `patient_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `patient_reunion` 
                ADD INDEX (`reunion_id`),
                ADD INDEX (`patient_id`);";
        $this->addQuery($query);
        $this->makeRevision("3.13");

        $query = "CREATE TABLE `accident_travail` (
                `accident_travail_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_class` ENUM ('CConsultation','CSejour') NOT NULL,
                `object_id` INT (11) UNSIGNED,
                `num_at_mp` INT (9) UNSIGNED ZEROFILL,
                `num_organisme` INT (9) UNSIGNED ZEROFILL,
                `nature` ENUM ('AT','MP') NOT NULL,
                `type` ENUM ('I','P','R','F') NOT NULL,
                `feuille_at` ENUM ('0','1') DEFAULT '0',
                `date_debut_arret` DATE,
                `date_fin_arret` DATE,
                `date_debut_travail_leger` DATE,
                `date_fin_travail_leger` DATE,
                `datetime_at_mp` DATETIME,
                `date_constatations` DATE,
                `constatations` VARCHAR (200),
                `patient_employeur_nom` VARCHAR (66),
                `patient_employeur_adresse` VARCHAR (38),
                `patient_employeur_cp` VARCHAR (5),
                `patient_employeur_ville` VARCHAR (38),
                `patient_employeur_phone` VARCHAR (20),
                `patient_employeur_email` VARCHAR (50),
                `patient_visite_escalier` VARCHAR (3),
                `patient_visite_etage` VARCHAR (3),
                `patient_visite_appartement` VARCHAR (5),
                `patient_visite_batiment` VARCHAR (3),
                `patient_visite_code` VARCHAR (8),
                `patient_visite_adresse` VARCHAR (38),
                `patient_visite_cp` VARCHAR(5),
                `patient_visite_ville` VARCHAR (38),
                `patient_visite_phone` VARCHAR (20),
                `date_constat` DATE,
                `constat` ENUM ('1','2','3'),
                `description_constat` VARCHAR (200),
                `date_reprise` DATE,
                `date_sortie_sans_restriction` DATE,
                `motif_sortie_sans_restriction` VARCHAR (180),
                `date_sortie` DATE,
                `date_debut_soins` DATE,
                `date_fin_soins` DATE,
                INDEX object (object_class, object_id),
                INDEX (`date_debut_arret`),
                INDEX (`date_fin_arret`),
                INDEX (`date_debut_travail_leger`),
                INDEX (`date_fin_travail_leger`),
                INDEX (`datetime_at_mp`),
                INDEX (`date_constatations`),
                INDEX (`date_constat`),
                INDEX (`date_reprise`),
                INDEX (`date_sortie_sans_restriction`),
                INDEX (`date_sortie`),
                INDEX (`date_debut_soins`),
                INDEX (`date_fin_soins`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "INSERT INTO `accident_travail` (`object_class`, `object_id`, `date_constatations`, `num_organisme`, `num_at_mp`, `feuille_at`, `nature`, `type`)
                SELECT 'CConsultation', consultation_id, date_at, org_at, CONCAT(num_at, cle_at), feuille_at, 'AT', 'I'
                FROM consultation
                WHERE date_at IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("3.14");
        $this->addPrefQuery("event_remember_date_filter", "1");

        $this->makeRevision("3.15");

        $this->addDefaultConfig(
            "dPcabinet PriseRDV keepchir",
            "dPcabinet keepchir"
        );
        $this->addDefaultConfig(
            "dPcabinet PriseRDV display_nb_consult",
            "dPcabinet display_nb_consult"
        );
        $this->addDefaultConfig(
            "dPcabinet PriseRDV display_practitioner_name_future_rdv",
            "dPcabinet display_practitioner_name_future_rdv"
        );
        $this->addDefaultConfig(
            "dPcabinet CPlageconsult hours_start",
            "dPcabinet CPlageconsult hours_start"
        );
        $this->addDefaultConfig(
            "dPcabinet CPlageconsult hours_stop",
            "dPcabinet CPlageconsult hours_stop"
        );
        $this->addDefaultConfig(
            "dPcabinet CPlageconsult minutes_interval",
            "dPcabinet CPlageconsult minutes_interval"
        );
        $this->addDefaultConfig(
            "dPcabinet CPlageconsult hour_limit_matin",
            "dPcabinet CPlageconsult hour_limit_matin"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation use_last_consult",
            "dPcabinet CConsultation use_last_consult"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation show_examen",
            "dPcabinet CConsultation show_examen"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation show_histoire_maladie",
            "dPcabinet CConsultation show_histoire_maladie"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation show_projet_soins",
            "dPcabinet CConsultation show_projet_soins"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation show_conclusion",
            "dPcabinet CConsultation show_conclusion"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation show_IPP_print_consult",
            "dPcabinet CConsultation show_IPP_print_consult"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation show_motif_consult_immediate",
            "dPcabinet CConsultation show_motif_consult_immediate"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation attach_consult_sejour",
            "dPcabinet CConsultation attach_consult_sejour"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation create_consult_sejour",
            "dPcabinet CConsultation create_consult_sejour"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation minutes_before_consult_sejour",
            "dPcabinet CConsultation minutes_before_consult_sejour"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation hours_after_changing_prat",
            "dPcabinet CConsultation hours_after_changing_prat"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation fix_doc_edit",
            "dPcabinet CConsultation fix_doc_edit"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation search_sejour_all_groups",
            "dPcabinet CConsultation search_sejour_all_groups"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation consult_readonly",
            "dPcabinet CConsultation consult_readonly"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation surbooking_readonly",
            "dPcabinet CConsultation surbooking_readonly"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation tag",
            "dPcabinet CConsultation tag"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation default_taux_tva",
            "dPcabinet CConsultation default_taux_tva"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultation auto_refresh_frequency",
            "dPcabinet CConsultation auto_refresh_frequency"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth feuille_anesthesie",
            "dPcabinet CConsultAnesth feuille_anesthesie"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth format_auto_motif",
            "dPcabinet CConsultAnesth format_auto_motif",
            "Préanesth. %I %L %S"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth format_auto_rques",
            "dPcabinet CConsultAnesth format_auto_rques",
            "%T %E %e"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth view_premedication",
            "dPcabinet CConsultAnesth view_premedication"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth show_facteurs_risque",
            "dPcabinet CConsultAnesth show_facteurs_risque"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth show_mallampati",
            "dPcabinet CConsultAnesth show_mallampati"
        );
        $this->addDefaultConfig(
            "dPcabinet CConsultAnesth check_close",
            "dPcabinet CConsultAnesth check_close"
        );
        $this->addDefaultConfig(
            "dPcabinet Tarifs show_tarifs_etab",
            "dPcabinet Tarifs show_tarifs_etab"
        );
        $this->addDefaultConfig(
            "dPcabinet Comptabilite show_compta_tiers",
            "dPcabinet Comptabilite show_compta_tiers"
        );


        $this->makeRevision("3.16");

        $query = "ALTER TABLE `reunion` 
                ADD `remarques` TEXT;";
        $query .= "ALTER TABLE `patient_reunion` 
                ADD `motif` TEXT,
                ADD `remarques` TEXT,
                ADD `action` TEXT,
                ADD `au_total` TEXT,
                ADD `model_id` INT (11);";
        $this->addQuery($query);

        $this->makeRevision("3.17");

        $query = "ALTER TABLE `acte_ngap`
                ADD `prescription_id` INT (11) UNSIGNED,
                ADD `other_executant_id` INT (11) UNSIGNED,
                ADD `motif` TEXT,
                ADD `motif_unique_cim` VARCHAR (6);";
        $this->addQuery($query);

        $this->makeRevision("3.18");

        $query = "ALTER TABLE `consultation_cat` 
                ADD `couleur` VARCHAR (6);";
        $this->addQuery($query);

        $this->makeRevision("3.19");

        $query = "ALTER TABLE `tarifs` CHANGE `codes_tarmed` `codes_tarmed` VARCHAR(510);";
        $this->addQuery($query);
        $this->makeEmptyRevision("3.20");

        $this->makeRevision("3.21");

        $query = "ALTER TABLE `plageconsult`
                ADD `sync_appfine` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `reunion`
                ADD `rappel` ENUM ('0','1');";
        $this->addQuery($query, true);

        $this->makeRevision("3.22");

        $query = "ALTER TABLE `reservation` 
                CHANGE `motif` `motif` TEXT;";

        $this->addQuery($query);

        $this->makeRevision("3.23");

        $query = "CREATE TABLE `vaccination` (
                `vaccination_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED,
                `practitionner_name` VARCHAR (255),
                `label_id` VARCHAR (255) NOT NULL,
                `injection_date` DATETIME NOT NULL,
                `recall_age` INT (11),
                `batch` VARCHAR (255) NOT NULL,
                `speciality` VARCHAR (255) NOT NULL,
                `remarques` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";

        $this->addQuery($query);
        $this->makeRevision("3.24");

        $query = "ALTER TABLE `vaccination`
                CHANGE `practitionner_name` `practitioner_name` varchar(255);";
        $this->addQuery($query);
        $this->makeRevision("3.25");

        $query = "DROP TABLE `vaccination`";
        $this->addQuery($query);

        $query = "CREATE TABLE `injection` (
                `injection_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED,
                `practitioner_name` VARCHAR (255),
                `injection_date` DATETIME NOT NULL,
                `batch` VARCHAR (255) NOT NULL,
                `speciality` VARCHAR (255) NOT NULL,
                `remarques` TEXT,
                `recall_age` INT (11)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `vaccination` (
                `vaccination_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `injection_id` INT (11) UNSIGNED,
                `type` ENUM ('BCG','DTP','Coqueluche','HIB','HB','Pneumocoque','MeningocoqueC','ROR','HPV','Grippe','Zona','Autre')
              )/*! ENGINE=MyISAM */;
                ALTER TABLE `vaccination` 
                    ADD INDEX (`injection_id`);";
        $this->addQuery($query);
        $this->makeRevision("3.26");

        $query = "ALTER TABLE `consultation_anesth` 
                ADD `accord_patient_debout_aller` ENUM ('0','1') DEFAULT '0',
                ADD `accord_patient_debout_retour` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("3.27");

        $query = "ALTER TABLE `ressource_cab` 
                ADD `in_charge` VARCHAR (50);";
        $this->addQuery($query);

        $this->makeRevision('3.28');

        $query = "ALTER TABLE `consultation` ADD `teleconsultation` ENUM ('0', '1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("3.29");

        $query = "ALTER TABLE `consultation`
                ADD `owner_id` INT UNSIGNED AFTER `consultation_id`,
                ADD `creation_date` DATETIME AFTER `owner_id`,
                ADD INDEX (`owner_id`),
                ADD INDEX (`creation_date`);";
        $this->addQuery($query);

        $this->makeRevision("3.30");
        $query = "ALTER TABLE `consultation_cat` 
                ADD `sync_appfine` ENUM ('0','1') DEFAULT '0' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("3.31");
        $query = "ALTER TABLE `consultation_cat` 
                CHANGE `function_id` `function_id` INT UNSIGNED,
                ADD `praticien_id` INT UNSIGNED,
                ADD INDEX (`praticien_id`);";
        $this->addQuery($query);
        $this->makeRevision("3.32");

        $this->addDependency("dPplanningOp", "2.88");
        $query = "ALTER TABLE `consultation_anesth`
                ADD `position_id` INT (11) UNSIGNED,
                ADD INDEX (`position_id`);";
        $this->addQuery($query);

        $positions = ["DD", "DV", "DL", "GP", "AS", "TO", "GYN", "DDA"];
        foreach ($positions as $position_id => $code) {
            $query = "UPDATE `consultation_anesth`
                SET `position_id` = '" . $position_id . "'
                WHERE `position` = '" . $code . "';";
            $this->addQuery($query);
        }
        $this->makeRevision("3.33");

        $query = "ALTER TABLE `consultation`
      DROP `patient_date_reglement`,
      DROP `tiers_date_reglement`;";
        $this->addQuery($query);

        $this->makeEmptyRevision("3.35");

        /*$query = "ALTER TABLE `acte_ngap`
                    ADD INDEX (`prescripteur_id`),
                    ADD INDEX (`date_demande_accord`),
                    ADD INDEX (`prescription_id`),
                    ADD INDEX (`other_executant_id`),
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation`
                    ADD INDEX (`element_prescription_id`),
                    ADD INDEX (`fin_at`),
                    ADD INDEX (`reprise_at`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `examigs`
                    ADD INDEX (`date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `info_checklist_item`
                    ADD INDEX consultation (consultation_class, consultation_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `reglement`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `injection`
                    ADD INDEX (`patient_id`),
                    ADD INDEX (`injection_date`);";
        $this->addQuery($query);*/

        $this->makeEmptyRevision("3.36");

        /*$query = "ALTER TABLE `reglement`
                    DROP INDEX consultation_id;";
        $this->addQuery($query);*/


        $this->makeRevision("3.37");

        $query = "CREATE TABLE `lieuconsult` (
                `lieuconsult_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `label` VARCHAR (255) NOT NULL,
                `adresse` TEXT NOT NULL,
                `cp` VARCHAR (5) NOT NULL,
                `ville` VARCHAR (255) NOT NULL,
                `sync` ENUM ('0','1') NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `lieuconsult_praticien` (
                `lieuconsult_praticien_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `praticien_id` INT (11) UNSIGNED NOT NULL,
                `lieuconsult_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `plageconsult` 
                ADD `lieuconsult_id` INT (11) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("3.38");

        $query = "ALTER TABLE `consultation` 
                ADD `suspendu` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("3.39");

        $query = "CREATE TABLE `examlee` (
                `examlee_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `consultation_anesth_id` INT (11) UNSIGNED NOT NULL,
                `chirurgie_risque` ENUM ('0','1') DEFAULT '0',
                `coronaropathie` ENUM ('0','1') DEFAULT '0',
                `insuffisance_cardiaque` ENUM ('0','1') DEFAULT '0',
                `antecedent_avc` ENUM ('0','1') DEFAULT '0',
                `diabete` ENUM ('0','1') DEFAULT '0',
                `clairance_creatinine` ENUM ('0','1') DEFAULT '0',
                INDEX (`consultation_anesth_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `exammet` (
                `exammet_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `consultation_anesth_id` INT (11) UNSIGNED NOT NULL,
                `aptitude_physique` ENUM ('0','1','4','7','10'),
                INDEX (`consultation_anesth_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("3.40");

        $query = "ALTER TABLE `lieuconsult` 
                ADD `code` VARCHAR (80) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("3.41");

        $query = "ALTER TABLE `lieuconsult` 
                ADD `group_id` INT (11) UNSIGNED,
                ADD `active` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("3.42");

        $positions    = ["DD", "DV", "DL", "GP", "AS", "TO", "GYN", "DDA"];
        $num_position = 1;
        foreach ($positions as $libelle) {
            $query = "UPDATE `consultation_anesth`
                SET `position_id` = '" . $num_position . "'
                WHERE `position` = '" . $libelle . "';";
            $this->addQuery($query);
            $num_position++;
        }

        $this->makeRevision("3.43");

        $query = "RENAME TABLE `lieuconsult_praticien` TO `agenda_praticien`";
        $this->addQuery($query);

        $query = "ALTER TABLE `agenda_praticien`
                CHANGE `lieuconsult_praticien_id` `agenda_praticien_id` INT (11) UNSIGNED NOT NULL auto_increment,
                ADD `active` ENUM ('0','1') DEFAULT '1',
                ADD `sync` ENUM ('0','1') NOT NULL DEFAULT '0',
                ADD INDEX (`praticien_id`),
                ADD INDEX (`lieuconsult_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `plageconsult`
                CHANGE `lieuconsult_id` `agenda_praticien_id` INT (11) UNSIGNED,
                ADD INDEX (`agenda_praticien_id`);";
        $this->addQuery($query);

        $this->makeRevision("3.44");

        $query = "ALTER TABLE `lieuconsult` 
                DROP `code`;";
        $this->addQuery($query);

        $this->makeRevision('3.45');
        $query = "ALTER TABLE `consultation`
                CHANGE `motif_annulation` `motif_annulation` ENUM ('not_arrived','by_patient','other');";
        $this->addQuery($query);

        $this->makeRevision('3.46');
        $query = "ALTER TABLE `consultation_anesth` 
                CHANGE `bouche` `bouche` ENUM ('m20','m35','p35','m45','45-55','p55')";
        $this->addQuery($query);

        $this->makeRevision("3.47");
        $this->setModuleCategory("dossier_patient", "metier");

        $this->makeRevision("3.48");

        $query = "ALTER TABLE `injection`
                ADD `cip_product` INT(11) UNSIGNED,
                CHANGE `speciality` `speciality` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("3.49");

        $query = "ALTER TABLE `accident_travail` 
                CHANGE `constatations` `constatations` TEXT,
                CHANGE `motif_sortie_sans_restriction` `motif_sortie_sans_restriction` TEXT,
                ADD `sorties_autorisees` ENUM ('0','1') DEFAULT '0',
                ADD `sorties_restriction` ENUM ('0','1') DEFAULT '0',
                ADD `sorties_sans_restriction` ENUM ('0','1') DEFAULT '0',
                ADD `consequences` ENUM ('arret','sans_arret');";
        $this->addQuery($query);

        $this->makeRevision('3.50');

        $query = "ALTER TABLE acte_ngap
                ADD `taux_abattement` FLOAT(3,2) AFTER `coefficient`;";
        $this->addQuery($query);

        $this->makeRevision("3.51");
        $this->addPrefQuery("take_consult_for_assistante_sociale", "0");
        $this->makeRevision("3.52");

        $query = "ALTER TABLE `consultation_anesth`
              DROP `position`;";
        $this->addQuery($query);

        $this->makeRevision("3.53");
        $query = "ALTER TABLE `consultation` 
      ADD COLUMN `soins_infirmiers` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("3.54");
        $query = "CREATE TABLE `examgir` (
                `examgir_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `date` DATETIME NOT NULL,
                `sejour_id` INT (11) UNSIGNED NOT NULL,
                `score_gir` TINYINT NOT NULL DEFAULT '6',
                `creator_id` INT(11) UNSIGNED NOT NULL,
                `coherence_communication` VARCHAR(10),
                `coherence_comportement` VARCHAR(10),
                `orientation_temps` VARCHAR(10),
                `orientation_espace` VARCHAR(10),
                `toilette_haut` VARCHAR(10),
                `toilette_bas` VARCHAR(10),
                `habillage_haut` VARCHAR(10),
                `habillage_moyen` VARCHAR(10),
                `habillage_bas` VARCHAR(10),
                `alimentation_se_servir` VARCHAR(10),
                `alimentation_manger` VARCHAR(10),
                `elimination_urinaire` VARCHAR(10),
                `elimination_fecale` VARCHAR(10),
                `transferts` VARCHAR(10),
                `deplacements_int` VARCHAR(10),
                `deplacements_ext` VARCHAR(10),
                `alerter` VARCHAR(10),
                `gestion` VARCHAR(10),
                `cuisine` VARCHAR(10),
                `menage` VARCHAR(10),
                `transports` VARCHAR(10),
                `achats` VARCHAR(10),
                `suivi_traitement` VARCHAR(10),
                `activites_tps_libre` VARCHAR(10),
                INDEX (`score_gir`),
                INDEX (`date`),
                INDEX (`creator_id`),
                INDEX (`sejour_id`) )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("3.55");
        $query = "CREATE TABLE `exam_hemostase` (
                `exam_hemostase_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `consultation_anesth_id` INT (11) UNSIGNED NOT NULL,
                `coupure_minime` ENUM ('0','1') DEFAULT '0',
                `soin_dentaire` ENUM ('0','1') DEFAULT '0',
                `apres_chirurgie` ENUM ('0','1') DEFAULT '0',
                `hematomes_spontanes` ENUM ('0','1') DEFAULT '0',
                `hemostase_famille` ENUM ('0','1') DEFAULT '0',
                `apres_accouchement` ENUM ('0','1') DEFAULT '0',
                `menometrorragie`  ENUM ('0','1') DEFAULT '0',
                INDEX (`consultation_anesth_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("3.56");

        $query = "UPDATE `consultation_anesth` SET `bouche` = 'p35' WHERE `bouche` IN ('m45','45-55','p55');";
        $this->addQuery($query);

        $query = "ALTER TABLE `consultation_anesth` 
                CHANGE `bouche` `bouche` ENUM ('m20','m35','p35')";
        $this->addQuery($query);

        $this->makeRevision("3.57");

        $query = "ALTER TABLE `consultation` 
                    CHANGE `chrono` `chrono` enum('8','16','32','48','64') NOT NULL DEFAULT '16';";
        $this->addQuery($query);

        $this->makeRevision("3.58");

        $query = "UPDATE `user_preferences`
                SET `value` = '0'
                WHERE `key` = 'view_traitement' 
                AND `user_id` IS NULL;";
        $this->addQuery($query);

        $query = "UPDATE `user_preferences`
                SET `value` = '1'
                WHERE `key` = 'new_semainier' 
                AND `user_id` IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("3.59");

        $this->addQuery(
            "ALTER TABLE `consultation_cat` 
                ADD `authorize_booking_new_patient` BOOL DEFAULT '1' ;"
        );

        $this->makeRevision("3.60");
        $query = "
        ALTER TABLE `plageconsult` 
                ADD `eligible_teleconsultation` ENUM ('0','1') DEFAULT '0';
        ";
        $this->addQuery($query);

        $this->makeRevision("3.61");

        $query = "
        ALTER TABLE `plageconsult` 
                ADD `exercice_place_id` INT (11) UNSIGNED;
        ";
        $this->addQuery($query);

        $this->makeRevision("3.62");

        $this->addQuery(
            "ALTER TABLE `consultation_cat` 
                ADD `exercice_place_id` INT (11) UNSIGNED;"
        );

        $this->makeRevision("3.63");

        $this->makeEmptyRevision("3.64");

        $this->addQuery(
            "ALTER TABLE `consultation` 
                ADD `csnp` BOOL DEFAULT '0',
                ADD `ccmu` ENUM ('1','2','3','4','5','P','D') DEFAULT NULL,
                ADD `cimu` ENUM ('5','4','3','2','1') DEFAULT NULL,
                ADD `sortie` DATETIME DEFAULT NULL;"
        );

        $this->makeRevision('3.65');

        $this->addMethod('addEligibleTeleconsultOnConsult');

        $this->makeRevision('3.66');

        $this->addQuery(
            "ALTER TABLE `consultation` 
                ADD `motif_sfmu_id` INT (11) UNSIGNED,
                ADD INDEX (`motif_sfmu_id`);"
        );

        $this->makeRevision('3.67');

        $this->addQuery(
            "ALTER TABLE `consultation` 
                ADD `lit_id` INT (11) UNSIGNED,
                ADD INDEX (`lit_id`);"
        );

        $this->makeRevision('3.68');

        $this->addQuery('ALTER TABLE `facture_cabinet` ADD INDEX (`num_compta`);', true);

        $this->makeRevision('3.69');

        $this->addQuery(
            "CREATE TABLE `plage_consult_categorie` (
                `plage_consult_categorie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `plage_id` INT (11) UNSIGNED,
                `consult_categorie_id` INT (11) UNSIGNED,
                `praticien_id` INT (11) UNSIGNED,
                `sync_appfine` ENUM ('0','1') DEFAULT '0'
              )/*! ENGINE=MyISAM */;
                ALTER TABLE `plage_consult_categorie` 
                ADD INDEX (`plage_id`),
                ADD INDEX (`consult_categorie_id`),
                ADD INDEX (`praticien_id`);"
        );

        $this->makeRevision('3.70');

        $this->addQuery(
            "ALTER TABLE `banque` 
                ADD `group_id` INT (11) UNSIGNED,
                ADD INDEX (`group_id`);"
        );

        $this->makeRevision('3.71');

        $this->addQuery(
            "ALTER TABLE `plageconsult` 
                ADD `function_id` INT (11) UNSIGNED,
                ADD INDEX (`function_id`);"
        );

        $this->makeRevision('3.72');

        $this->addQuery(
            'ALTER TABLE `consultation`
            ADD `adresse_par_exercice_place_id` INT (11) UNSIGNED AFTER `adresse_par_prat_id`,
            ADD INDEX (`adresse_par_exercice_place_id`);'
        );

        $this->makeRevision('3.73');

        $this->addQuery(
            "CREATE TABLE `bon_a_payer` (
                `bon_a_payer_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `praticien_id` INT (11) UNSIGNED NOT NULL,
                `context_class` ENUM ('CConsultation') NOT NULL,
                `context_id` INT (11) UNSIGNED NOT NULL,
                `creation_datetime` DATETIME,
                `montant` FLOAT,
                `paiement_datetime` DATETIME,
                INDEX (`praticien_id`),
                INDEX context (context_class, context_id),
                INDEX (`creation_datetime`),
                INDEX (`paiement_datetime`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('3.74');

        $this->addQuery(
            " ALTER TABLE `bon_a_payer` 
                CHANGE `montant` `montant` DECIMAL (10,3);"
        );

        $this->makeRevision("3.75");

        $query = "ALTER TABLE `injection`
                ADD `expiration_date` DATE DEFAULT NULL;";
        $this->addQuery($query);

        $this->makeRevision('3.76');

        $this->addQuery("ALTER TABLE `bon_a_payer` ADD `ack` TEXT;");

        $this->makeRevision('3.77');

        $this->addQuery(
            'ALTER TABLE `consultation`
             ADD INDEX adresse_par_prat_id (`adresse_par_prat_id`);',
            true
        );

        $this->makeRevision('3.78');

        $this->addQuery(
            "ALTER TABLE `acte_ngap` 
                ADD INDEX (`prescripteur_id`);"
        );

        $this->makeRevision('3.79');

        $this->addQuery("ALTER TABLE `examaudio` 
                ADD `gauche_aerien_pasrep`  VARCHAR( 64 ),
                ADD `gauche_osseux_pasrep`  VARCHAR( 64 ),
                ADD `droite_aerien_pasrep`  VARCHAR( 64 ),
                ADD `droite_osseux_pasrep`  VARCHAR( 64 );");

        $this->makeRevision('3.80');

        $this->addQuery("ALTER TABLE `injection` 
                CHANGE `batch` `batch` VARCHAR (255);");

        $this->makeRevision('3.81');
        $this->addQuery("ALTER TABLE `consultation` ADD `forfait_peps` ENUM ('0','1') DEFAULT '0';");

        $this->makeRevision('3.82');
        $this->addQuery("ALTER TABLE `consultation` CHANGE `soins_infirmiers` `soins_infirmiers` TEXT;");

        $this->makeRevision('3.83');

        $this->addQuery("
            ALTER TABLE `plageconsult` 
            ADD `nb_places` INT (11) UNSIGNED NOT NULL DEFAULT 1;
        ");

        $this->makeRevision('3.84');

        $this->addQuery("
            ALTER TABLE `acte_ngap`
            ADD `comment_acte` VARCHAR(255);
        ");

        $this->makeRevision('3.85');

        $this->addQuery(
            "CREATE TABLE `slot` (
                `slot_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `plageconsult_id` INT (11) UNSIGNED NOT NULL,
                `consultation_id` INT (11) UNSIGNED,
                `start` DATETIME NOT NULL,
                `end` DATETIME NOT NULL,
                `overbooked` ENUM ('0','1') DEFAULT '0',
                `status` ENUM ('busy','free','busy-unavailable','busy-tentative','entered-in-error') DEFAULT 'free',
                INDEX (`plageconsult_id`),
                INDEX (`consultation_id`),
                INDEX (`start`),
                INDEX (`end`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->addMethod("addSlotFromPlageConsult");

        $this->makeRevision('3.86');

        $this->addMethod("addConsultationToSlot");

        $this->makeRevision('3.87');

        $this->addQuery(
            'ALTER TABLE `tarifs`
            DROP COLUMN `codes_tarmed`,
            DROP COLUMN `codes_caisse`;'
        );

        $this->makeRevision("3.88");

        $this->addQuery("ALTER TABLE `consultation` 
                ADD `type_consultation` ENUM ('consultation','suivi_patient') DEFAULT 'consultation';");

        $this->makeRevision('3.89');

        $this->addMethod('deleteOldCHFactureFields');

        $this->makeRevision('3.90');

        $this->addQuery(
            "CREATE TABLE `todo_list_item` (
            `todo_list_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
            `user_id` INT (11) UNSIGNED NOT NULL,
            `libelle` VARCHAR  (255) NOT NULL,
            `handled_date` DATE,
            INDEX (`user_id`, `handled_date`)
          )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('3.91');
        $this->addQuery("ALTER TABLE `vaccination` 
            MODIFY COLUMN `type` ENUM ('BCG','DTP','Coqueluche','HIB','HB','Pneumocoque','MeningocoqueC', 'MeningocoqueB','ROR','HPV','Grippe','Zona','Autre')");

        $this->mod_version = "3.92";
    }

    public function addEligibleTeleconsultOnConsult(): bool
    {
        $ds = CSQLDataSource::get('std');

        if (!$ds->hasField('consultation_cat', 'eligible_teleconsultation', false)) {
            $query = "ALTER TABLE `consultation_cat` 
                ADD `eligible_teleconsultation` ENUM ('0','1') DEFAULT '0';";
            $ds->exec($query);
        }

        return true;
    }

    public function addSlotFromPlageConsult(): bool
    {
        $start          = 0;
        $step           = 1000;
        $limit          = "$start,$step";
        $where          = ["date" => ">= '" . CMbDT::date() . "'"];
        $request = new CRequest();
        $request->addSelect("plageconsult_id, date, debut, fin, freq");
        $request->addTable("plageconsult");
        $request->addWhere($where);
        $request->setLimit($limit);
        $plages_consult = $this->ds->loadList($request->makeSelect());

        if (!is_countable($plages_consult)) {
            return true;
        }

        $ds = CSQLDataSource::get("std");

        while (count($plages_consult)) {
            $insert = [];
            foreach ($plages_consult as $_plage_consult) {
                $date           = $_plage_consult["date"];
                $datetime_start = $date . " " . CMbDT::time($_plage_consult["debut"]);
                $datetime_end   = $date . " " . CMbDT::time($_plage_consult["fin"]);
                while ($datetime_start < $datetime_end) {
                    $datetime_end_slot  = CMbDT::addDateTime($_plage_consult["freq"], $datetime_start);
                    $insert[] = [
                        "plageconsult_id" => $_plage_consult["plageconsult_id"],
                        "start"        => $datetime_start,
                        "end"          => $datetime_end_slot,
                    ];

                    $datetime_start = $datetime_end_slot;
                }
            }
            if (!empty($insert)) {
                $ds->insertMulti("slot", $insert, 1000);
            }

            $start          += $step;
            $limit          = "$start, $step";
            $request->setLimit($limit);
            $plages_consult = $this->ds->loadList($request->makeSelect());
        }



        return true;
    }

    public function addConsultationToSlot(): bool
    {
        $start          = 0;
        $step           = 1000;
        $limit          = "$start,$step";
        $where          = ["date" => ">= '" . CMbDT::date() . "'"];
        $request = new CRequest();
        $request->addSelect("plageconsult_id, date, debut, fin, freq");
        $request->addTable("plageconsult");
        $request->addWhere($where);
        $request->setLimit($limit);
        $plages_consult = $this->ds->loadList($request->makeSelect());
        $insert = [];
        if (!is_countable($plages_consult)) {
            return true;
        }

        $ds = CSQLDataSource::get("std");

        while (count($plages_consult)) {
            foreach ($plages_consult as $_plage_consult) {
                $request = new CRequest();
                $request->addSelect("annule, heure, duree, consultation_id");
                $request->addTable("consultation");
                $request->addWhere("plageconsult_id = " . $_plage_consult['plageconsult_id']);
                $consultations = $this->ds->loadList($request->makeSelect());
                foreach ($consultations as $_consultation) {
                    if ($_consultation["annule"]) {
                        continue;
                    }
                    $date_debut = $_plage_consult["date"] . " " . $_consultation["heure"];
                    $heure_fin  = CMbDT::addTime($_consultation["heure"], $_plage_consult["freq"]);
                    $date_fin   = $_plage_consult["date"] . " " . CMbDT::addTime(
                        $_consultation["heure"],
                        $_plage_consult["freq"]
                    );
                    for ($i = 1; $i <= $_consultation["duree"]; $i++) {
                        $request = new CRequest();
                        $request->addSelect("slot_id, consultation_id");
                        $request->addTable("slot");
                        $where_slot = [
                            "plageconsult_id" => "= " . $_plage_consult['plageconsult_id'],
                            "start"           => "= '$date_debut'",
                            "end"             => "= '$date_fin'",
                        ];
                        $request->addWhere($where_slot);
                        $slot = $this->ds->loadHash($request->makeSelect());

                        if ($slot && $slot["slot_id"]) {
                            if (!$slot["consultation_id"]) {
                                $query = $ds->prepare(
                                    "UPDATE `slot`
                                        SET consultation_id = ?1, status = 'busy'
                                        WHERE slot_id = ?2;", $_consultation["consultation_id"], $slot ["slot_id"]
                                );
                                if (!$ds->exec($query)) {
                                    trigger_error("Erreur store slot (".$_consultation['consultation_id'].", ".$_plage_consult['plageconsult_id'].")");

                                    return false;
                                }
                            } else {
                                $insert[] = [
                                    "plageconsult_id" => $_plage_consult["plageconsult_id"],
                                    "consultation_id" => $_consultation["consultation_id"],
                                    "start"           => $date_debut,
                                    "end"             => $date_fin,
                                    "overbooked"      => true,
                                    "status"          => "busy",
                                ];
                            }
                        } else {
                            $insert[] = [
                                "plageconsult_id" => $_plage_consult["plageconsult_id"],
                                "consultation_id" => $_consultation["consultation_id"],
                                "start"           => $date_debut,
                                "end"             => $date_fin,
                                "overbooked"      => true,
                                "status"          => "busy",
                            ];
                        }
                        $date_debut = $date_fin;
                        $heure_fin  = CMbDT::addTime($heure_fin, $_plage_consult["freq"]);
                        $date_fin   = $_plage_consult["date"] . " " . $heure_fin;
                    }
                }
            }
            $start          += $step;
            $limit          = "$start, $step";
            $request = new CRequest();
            $request->addSelect("plageconsult_id, date, debut, fin, freq");
            $request->addTable("plageconsult");
            $request->addWhere($where);
            $request->setLimit($limit);
            $plages_consult = $this->ds->loadList($request->makeSelect());
        }

        if (!empty($insert)) {
            $ds->insertMulti("slot", $insert, 1000);
        }

        return true;
    }

    public function deleteOldCHFactureFields(): bool
    {
        if ($this->ds->hasField('facture_cabinet', 'envoi_xml')) {
            $this->ds->exec('ALTER TABLE `facture_cabinet` DROP `envoi_xml`;');
        }

        if ($this->ds->hasField('facture_cabinet', 'compte_ch_id')) {
            $this->ds->exec('ALTER TABLE `facture_cabinet` DROP `compte_ch_id`;');
        }

        return true;
    }
}
