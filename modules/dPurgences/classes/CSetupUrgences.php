<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CAppUI;
use Ox\Core\CSetup;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CSetupUrgences extends CSetup
{

    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPurgences";

        $this->makeRevision("0.0");

        $query = "CREATE TABLE `rpu` (
                `rpu_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `sejour_id` INT(11) UNSIGNED NOT NULL,
                `diag_infirmier` TEXT,
                `mode_entree` ENUM('6','7','8'),
                `provenance` ENUM('1','2','3','4','5','8'),
                `transport` ENUM('perso','ambu','vsab','smur','heli','fo'),
                `prise_en_charge` ENUM('med','paramed','aucun'),
                `motif` TEXT,
                `ccmu` ENUM('1','2','3','4','5','P','D') NOT NULL,
                `sortie` DATETIME,
                `mode_sortie` ENUM('6','7','8','9'),
                `destination` ENUM('1','2','3','4','6','7'),
                `orientation` ENUM('HDT','HO','SC','SI','REA','UHCD','MED','CHIR','OBST','FUGUE','SCAM','PSA','REO'),
                KEY `sejour_id` (`sejour_id`),
                KEY `ccmu` (`ccmu`),
                KEY `sortie` (`sortie`),
                PRIMARY KEY (`rpu_id`)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.1");
        $query = "ALTER TABLE `rpu` 
                CHANGE `ccmu` `ccmu` ENUM( '1', 'P', '2', '3', '4', '5', 'D' )";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "ALTER TABLE `rpu`
                ADD `radio_debut` DATETIME,
                ADD `radio_fin` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "ALTER TABLE `rpu`
                DROP `mode_sortie`,
                DROP `sortie`";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "ALTER TABLE `rpu`
                ADD `mutation_sejour_id` INT(11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $query = "ALTER TABLE `rpu`
                ADD `gemsa` ENUM('1','2','3','4','5','6');";
        $this->addQuery($query);

        $this->makeRevision("0.15");
        $query = "ALTER TABLE `rpu`
                ADD `type_pathologie` ENUM('C','E','M','P','T');";
        $this->addQuery($query);

        $this->makeRevision("0.16");
        $query = "ALTER TABLE `rpu`
                CHANGE `mode_entree` `mode_entree` ENUM('6','7','8') NOT NULL,
                CHANGE `transport` `transport` ENUM('perso','perso_taxi','ambu','ambu_vsl','vsab','smur','heli','fo') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.17");
        $query = "ALTER TABLE `rpu`
                CHANGE `prise_en_charge` `pec_transport` ENUM('med','paramed','aucun')";
        $this->addQuery($query);

        $this->makeRevision("0.18");
        $query = "ALTER TABLE `rpu`
                ADD `urprov` ENUM('AM','AT','DO','EC','MT','OT','RA','RC','SP','VP'),
                ADD `urmuta` ENUM('A','D','M','P','X'),
                ADD `urtrau` ENUM('I','S','T');";
        $this->addQuery($query);

        $this->makeRevision("0.19");
        $query = "ALTER TABLE `rpu`
                ADD `box_id` INT(11) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("0.20");
        $query = "ALTER TABLE `rpu`
                ADD `sortie_autorisee` ENUM ('0','1') DEFAULT '0',
                ADD INDEX (`radio_debut`),
                ADD INDEX (`radio_fin`),
                ADD INDEX (`mutation_sejour_id`),
                ADD INDEX (`box_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE `rpu`
                ADD `accident_travail` DATE,
                ADD INDEX (`accident_travail`);";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `rpu` 
                ADD `bio_depart` DATETIME,
                ADD `bio_retour` DATETIME,
                ADD INDEX (`bio_depart`),
                ADD INDEX (`bio_retour`);";
        $this->addQuery($query);

        $this->makeRevision("0.23");

        $query = "CREATE TABLE `extract_passages` (
                `extract_passages_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `date_extract` DATETIME NOT NULL,
                `debut_selection` DATETIME NOT NULL,
                `fin_selection` DATETIME NOT NULL,
                `date_echange` DATETIME,
                `message` MEDIUMTEXT NOT NULL,
                `message_valide` ENUM ('0','1'),
                `nb_tentatives` INT (11)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `extract_passages` 
                ADD INDEX (`date_extract`),
                ADD INDEX (`debut_selection`),
                ADD INDEX (`fin_selection`),
                ADD INDEX (`date_echange`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `rpu_passage` (
                `rpu_passage_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL,
                `extract_passages_id` INT (11) UNSIGNED NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `rpu_passage` 
                ADD INDEX (`rpu_id`),
                ADD INDEX (`extract_passages_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.24");

        $query = "ALTER TABLE `rpu` 
                ADD `specia_att` DATETIME,
                ADD `specia_arr` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rpu` 
                ADD INDEX (`specia_att`),
                ADD INDEX (`specia_arr`);";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $this->addPrefQuery("defaultRPUSort", "ccmu");

        $this->makeRevision("0.26");
        $this->addPrefQuery("showMissingRPU", "0");

        $this->makeRevision("0.27");

        $query = "ALTER TABLE `extract_passages` 
                ADD `type` ENUM ('rpu','urg') DEFAULT 'rpu';";
        $this->addQuery($query);

        $this->makeRevision("0.28");
        $query = "ALTER TABLE `rpu`
                ADD `pec_douleur` TEXT";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `extract_passages` 
                ADD `group_id` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "CREATE TABLE `circonstance` (
                `code` VARCHAR (15) NOT NULL,
                `libelle` VARCHAR (100),
                `commentaire` TEXT
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'AVP', 'AVP', 'Accident de transport de toute nature.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'DEFENEST', 'Chute de grande hauteur', 'Chute supérieure à 3 m.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'AGRESSION', 'Autres agression, rixe ou morsure ', 'Pour toute agression ou rixe" .
            "sans usage d\'arme à feu ou d\'arme blanche. Pour toute morsure ou piqures multiples ou vénéneuses.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'NOYADE', 'Noyade, plongée, eau', 'Pour les noyades, " .
            "accident de plongée ou de décompression.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'ARMEFEU', 'Arme à feu', 'Pour toute agression, rixe, accident et suicide" .
            " ou tentative par agent vulnérant type arme à feu.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'COUTEAU', 'Objet tranchant ou perforant', 'Pour toute agression, rixe," .
            " accident et suicide ou tentative par agent vulnérant type arme blanche.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'SPORT', 'Accident de sport ou de loisir', 'Traumatisme en rapport " .
            "avec une activité sportive ou de loisir.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'PENDU', 'Pendaison, strangulation', 'Pendaison, strangulation " .
            "sans présagé du caractère médico-légal ou non.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'FEU', 'Feu, agent thermique, fumée', 'Toute source de chaleur intense " .
            "ayant provoqué des brulures, un coup de chaleur ou une insolation. Y compris incendie, " .
            "fumée d\'incendie et dégagement de CO au décours d\'un feu.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'EXPLOSIF', 'Explosion', 'Explosion de grande intensité même suivi " .
            "ou précédé d\'un incendie, même si notion d\'écrasement.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'ECRASE', 'Ecrasement', 'Notion d\'écrasement, hors contexe accident " .
            "de circulation, explosion ou incendie.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'TOXIQUE', 'Exposition à produits chimiques ou toxiques', " .
            "'Lésion en rapport avec une exposition à un produit liquide, solide ou gazeux toxique." .
            " Hors contexte NRBC, incendie, intoxication par médicament, alcool ou drogues illicites.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'CHUTE', 'Chute, traumatisme bénin', 'Traumatisme bénin du " .
            "ou non à une chute de sa hauteur ou de très faible hauteur.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'ELEC', 'Electricité, foudre', 'Effet du courant électrique " .
            "par action directe ou à distance (arc électrique, effet de la foudre).');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'PRO', 'Trauma par machine à usage professionnel', 'Toute lésion " .
            "traumatique provoquée par un matériel à usage professionnel.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'DOMJEU', 'Trauma par appareillage domestique', 'Toute lésion " .
            "traumatique provoquée par un matériel à usage domestique ou un accessoire de jeu ou de loisir.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'SECOND', 'Transfert secondaire', 'Pour tout transfert secondaire.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'AUTRE', 'Autres', 'Autre traumatisme avec circonstance particulière'" .
            "'non répertorié.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( 'CATA', 'Accident nombreuses victimes', 'Accident catastrophique mettant " .
            "en cause de nombreuses victimes et nécessitant un plan d\'intervention particulier.');";
        $this->addQuery($query);

        $query = "INSERT INTO `circonstance` VALUES ( '00000', 'Pathologie non traumatique, non cironstancielle', " .
            "'Pathologie médicale non traumatique ou sans circonstance de survenue particulière.');";
        $this->addQuery($query);

        $query = "ALTER TABLE `rpu`
                ADD `circonstance` VARCHAR (50);";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `circonstance`
                ADD `circonstance_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY FIRST;";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `rpu`
                CHANGE `accident_travail` `date_at` DATE DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $this->addDependency("dPplanningOp", "1.23");
        $query = "UPDATE `sejour`,`rpu`
                SET `sejour`.`mode_entree` = `rpu`.`mode_entree`
                WHERE `rpu`.`sejour_id` = `sejour`.`sejour_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rpu`
                DROP `mode_entree`;";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $this->addDependency("dPplanningOp", "1.28");
        $query = "ALTER TABLE `rpu`
                DROP `provenance`,
                DROP `destination`,
                DROP `transport`;";
        $this->addQuery($query);

        $this->makeRevision("0.35");

        $query = "ALTER TABLE `rpu`
                ADD `motif_entree` TEXT";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "ALTER TABLE `rpu`
                ADD `regule_par` ENUM ('centre_15','medecin');";
        $this->addQuery($query);

        $this->makeRevision("0.37");

        $query = "CREATE TABLE `box_urgences` (
                `box_urgences_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `nom` VARCHAR(30) NOT NULL,
                `description` VARCHAR(50),
                `type` ENUM('Suture','Degravillonage','Dechockage','Traumatologie','Radio','Retour_radio','Imagerie','Bio','Echo','Attente','Resultats','Sortie') NOT NULL DEFAULT 'Attente',
                `plan_x` INT(11) NULL,
                `plan_y` INT(11) NULL,
                `color` VARCHAR(6) DEFAULT 'ABE',
                `hauteur` INT(11) NOT NULL DEFAULT '1',
                `largeur` INT(11) NOT NULL DEFAULT '1',
                PRIMARY KEY (`box_urgences_id`)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.38");

        $query = "DROP TABLE `box_urgences`;";
        $this->addQuery($query);
        $this->makeRevision("0.39");

        $query = "ALTER TABLE `rpu` 
                ADD `code_diag` INT (11);";
        $this->addQuery($query);
        $this->makeRevision("0.40");

        $query = "CREATE TABLE `motif_urgence` (
                `motif_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `chapitre_id` INT (11) UNSIGNED,
                `nom` VARCHAR (255),
                `code_diag` INT (11),
                `degre_min` INT (11),
                `degre_max` INT (11)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `motif_urgence` 
                ADD INDEX (`chapitre_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `motif_chapitre` (
                `chapitre_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.41");

        $query = "CREATE TABLE `motif_sfmu` (
                `motif_sfmu_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code` VARCHAR (255),
                `libelle` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.42");

        $query = "ALTER TABLE `rpu`
                ADD `motif_sfmu` INT (11) UNSIGNED";
        $this->addQuery($query);

        $this->makeRevision("0.43");

        $query = "ALTER TABLE `circonstance`
                ADD `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.44");

        $query = "UPDATE `rpu`
                SET `rpu`.`circonstance` = (SELECT `circonstance_id`
                                            FROM `circonstance` WHERE `circonstance`.`code` = `rpu`.`circonstance`)
                WHERE `rpu`.circonstance IS NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.45");

        $query = "ALTER TABLE `motif_sfmu`
                ADD `categorie` VARCHAR (255);";
        $this->addQuery($query);


        $this->makeRevision("0.46");

        $query = "ALTER TABLE `extract_passages`
                CHANGE `type` `type` ENUM ('rpu','urg','activite') DEFAULT 'rpu';";
        $this->addQuery($query);

        $this->makeRevision("0.47");

        $this->addDefaultConfig("dPurgences Display check_cotation", "dPurgences check_cotation");
        $this->addDefaultConfig("dPurgences Display check_gemsa", "dPurgences check_gemsa");
        $this->addDefaultConfig("dPurgences Display check_ccmu", "dPurgences check_ccmu");
        $this->addDefaultConfig("dPurgences Display check_dp", "dPurgences check_dp");
        $this->addDefaultConfig("dPurgences Display check_can_leave", "dPurgences check_can_leave");

        $this->makeRevision("0.48");

        $config_value = @CAppUI::conf("dPurgences gestion_motif_sfmu");

        if ($config_value !== null) {
            if ($config_value == "1") {
                $config_value = "2";
            }
            $query = "INSERT INTO `configuration` (`feature`, `value`) VALUES (%1, %2)";
            $query = $this->ds->prepare($query, "dPurgences CRPU gestion_motif_sfmu", $config_value);
            $this->addQuery($query);
        }
        $this->makeRevision("0.49");

        $this->addDefaultConfig("dPurgences use_vue_topologique", "dPhospi use_vue_topologique");
        $this->makeRevision("0.50");

        $this->addDefaultConfig("dPurgences CRPU diag_prat_view", "dPurgences diag_prat_view");
        $this->addDefaultConfig("dPurgences CRPU display_motif_sfmu", "dPurgences display_motif_sfmu");

        $this->makeRevision("0.51");

        $query = "ALTER TABLE `rpu`
                ADD `ide_responsable_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rpu`
                ADD INDEX (`ide_responsable_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.52");

        $query = "ALTER TABLE `motif_urgence`
                ADD `definition` TEXT,
                ADD `observations` TEXT,
                ADD `param_vitaux` TEXT,
                ADD `recommande` TEXT;";
        $this->addQuery($query);

        $query = "CREATE TABLE `motif_question` (
                `question_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `motif_id` INT (11) UNSIGNED NOT NULL,
                `degre` TINYINT (4),
                `nom` TEXT NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `motif_question`
                ADD INDEX (`motif_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.53");

        $query = "CREATE TABLE `motif_reponse` (
                `reponse_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `question_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `result` ENUM ('0','1') DEFAULT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `motif_reponse`
                ADD INDEX (`rpu_id`),
                ADD INDEX (`question_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.54");

        $query = "CREATE TABLE `echelle_tri` (
                `echelle_tri_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `proteinurie` ENUM ('positive','negative'),
                `liquide` ENUM ('meconial','teinte'),
                `antidiabet_use` ENUM ('NP','oui','non') DEFAULT 'NP',
                `anticoagul_use` ENUM ('NP','oui','non') DEFAULT 'NP',
                `anticoagulant` ENUM ('sintrom','other'),
                `antidiabetique` ENUM ('oral','insuline','oral_insuline'),
                `pupille_droite` TINYINT (4) UNSIGNED NOT NULL DEFAULT '0',
                `pupille_gauche` TINYINT (4) UNSIGNED NOT NULL DEFAULT '0',
                `ouverture_yeux` ENUM ('spontane','bruit','douleur','jamais'),
                `rep_verbale` ENUM ('oriente','confuse','inapproprie','incomprehensible','aucune'),
                `rep_motrice` ENUM ('obeit','oriente','evitement','decortication','decerebration','rien')
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `echelle_tri`
                ADD INDEX (`rpu_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.55");

        $query = "ALTER TABLE `extract_passages`
                CHANGE `type` `type` ENUM ('rpu','urg','activite','uhcd') DEFAULT 'rpu';";
        $this->addQuery($query);
        $this->makeRevision("0.56");

        $query = "ALTER TABLE `extract_passages`
                ADD `rpu_sender` VARCHAR (255);";
        $this->addQuery($query);
        $this->makeRevision("0.57");

        $query = "ALTER TABLE `echelle_tri`
                ADD `reactivite_droite` ENUM ('reactif','non_reactif'),
                ADD `reactivite_gauche` ENUM ('reactif','non_reactif')";
        $this->addQuery($query);
        $this->makeRevision("0.58");

        $query = "ALTER TABLE `echelle_tri`
                ADD `enceinte` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.59");

        $query = "ALTER TABLE `motif_urgence`
                ADD `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `motif_question`
                ADD `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("0.60");

        $query = "ALTER TABLE `echelle_tri`
                ADD `signe_clinique` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.61");

        $query = "ALTER TABLE `echelle_tri`
                ADD `semaine_grossesse` TINYINT (4) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("0.62");

        $query = "ALTER TABLE `echelle_tri`
                ADD `ccmu_manuel` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.63");

        $query = "ALTER TABLE `rpu`
                ADD `pec_inf` DATETIME,
                ADD INDEX (`pec_inf`);";
        $this->addQuery($query);
        $this->makeRevision("0.64");

        $query = "ALTER TABLE `rpu`
                ADD `last_update_ccmu` DATETIME,
                ADD INDEX (`last_update_ccmu`);";
        $this->addQuery($query);
        $this->makeRevision("0.65");

        $query = "ALTER TABLE `rpu`
                DROP `last_update_ccmu`,
                DROP INDEX `last_update_ccmu`;";
        $this->addQuery($query);
        $this->makeRevision("0.66");

        $query = "ALTER TABLE `rpu`
                ADD `radio_demande` DATETIME,
                ADD `radio_type` ENUM ('classic','echo','scanner','irm'),
                ADD `bio_demande` DATETIME,
                ADD INDEX (`radio_demande`),
                ADD INDEX (`bio_demande`);";
        $this->addQuery($query);
        $this->makeRevision("0.67");

        $query = "ALTER TABLE `rpu`
      ADD `ioa_id` INT (11) UNSIGNED,
      ADD `pec_ioa` DATETIME,
      ADD INDEX (`ioa_id`),
      ADD INDEX (`pec_ioa`);";
        $this->addQuery($query);
        $this->makeRevision("0.68");

        $query = "INSERT INTO configuration (feature, value, object_id, object_class)
                SELECT 'dPurgences Display see_type_radio', 1, object_id, object_class
                FROM configuration
                WHERE feature = 'dPurgences Display demandes_radio_bio'
                AND value = '1';";
        $this->addQuery($query);
        $this->makeRevision("0.69");

        $query = "CREATE TABLE `rpu_reservation` (
                `reservation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `lit_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                INDEX (`rpu_id`),
                INDEX (`lit_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.70");

        $this->addDefaultConfig("dPurgences CRPU gerer_hospi", "dPurgences gerer_hospi");
        $this->makeRevision("0.71");

        $query = "ALTER TABLE `rpu`
                ADD `bio_user_id` INT (11) UNSIGNED AFTER `bio_retour`,
                ADD INDEX (`bio_user_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.72");
        $this->addPrefQuery("chooseSortRPU", "DESC");
        $this->makeRevision("0.73");

        $query = "ALTER TABLE `rpu`
                ADD `echelle_tri_valide` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.74");

        $query = "CREATE TABLE `rpu_attente` (
                `attente_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `type_attente` ENUM ('radio','bio','specialiste') NOT NULL,
                `type_radio` ENUM ('classic','echo','scanner','irm'),
                `demande` DATETIME,
                `depart` DATETIME,
                `retour` DATETIME,
                `user_id` INT (11) UNSIGNED,
                INDEX (`rpu_id`),
                INDEX (`demande`),
                INDEX (`depart`),
                INDEX (`retour`),
                INDEX (`user_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.75");

        $query = "INSERT INTO rpu_attente (rpu_id, type_attente, demande, depart, retour, type_radio)
                SELECT rpu.rpu_id, 'radio', rpu.radio_demande, rpu.radio_debut, rpu.radio_fin, rpu.radio_type
                FROM rpu
                WHERE rpu.radio_demande IS NOT NULL
                OR rpu.radio_debut IS NOT NULL
                OR rpu.radio_fin IS NOT NULL;";
        $this->addQuery($query);
        $query = "INSERT INTO rpu_attente (rpu_id, type_attente, demande, depart, retour, user_id)
                SELECT rpu.rpu_id, 'bio', rpu.bio_demande, rpu.bio_depart, rpu.bio_retour, rpu.bio_user_id
                FROM rpu
                WHERE rpu.bio_demande IS NOT NULL
                OR rpu.bio_depart IS NOT NULL
                OR rpu.bio_retour IS NOT NULL;";
        $this->addQuery($query);
        $query = "INSERT INTO rpu_attente (rpu_id, type_attente, depart, retour)
                SELECT rpu.rpu_id, 'specialiste', rpu.specia_att, rpu.specia_arr
                FROM rpu
                WHERE rpu.specia_att IS NOT NULL
                OR rpu.specia_arr IS NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision("0.76");

        $query = "CREATE TABLE `echelle_tri_cte` (
                `echelle_cte_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL,
                `degre` ENUM ('1','2','3','4'),
                `name` VARCHAR (255),
                `value` VARCHAR (255),
                `unit` VARCHAR (255),
                INDEX (`rpu_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.77");
        $query = "ALTER TABLE `rpu_attente`
              CHANGE `type_radio` `type_radio` ENUM ('classic','echo','scanner','irm','scintigraphie');";
        $this->addQuery($query);
        $this->makeRevision("0.78");

        $query = "ALTER TABLE `rpu` 
      ADD `protocole_id` INT (11) UNSIGNED AFTER `sejour_id`,
      ADD INDEX (`protocole_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `protocole_rpu` (
      `protocole_rpu_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `group_id` INT (11) UNSIGNED,
      `libelle` VARCHAR (255) NOT NULL,
      `actif` ENUM ('0','1') DEFAULT '0',
      `responsable_id` INT (11) UNSIGNED,
      `uf_soins_id` INT (11) UNSIGNED,
      `charge_id` INT (11) UNSIGNED,
      `box_id` INT (11) UNSIGNED,
      `mode_entree` ENUM ('6','7','8') NOT NULL,
      `mode_entree_id` INT (11) UNSIGNED,
      `transport` ENUM ('perso','perso_taxi','ambu','ambu_vsl','vsab','smur','heli','fo')
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `protocole_rpu` 
      ADD INDEX (`group_id`),
      ADD INDEX (`responsable_id`),
      ADD INDEX (`uf_soins_id`),
      ADD INDEX (`charge_id`),
      ADD INDEX (`box_id`),
      ADD INDEX (`mode_entree_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.79");

        $query = "ALTER TABLE `rpu`
      ADD `color` VARCHAR (6);";
        $this->addQuery($query);

        $query = "CREATE TABLE `rpu_categorie` (
      `rpu_categorie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `group_id` INT (11) UNSIGNED,
      `motif` VARCHAR (255) NOT NULL,
      `actif` ENUM ('0','1') DEFAULT '0'
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rpu_categorie` 
      ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `rpu_link_cat` (
      `rpu_link_cat_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `rpu_id` INT (11) UNSIGNED,
      `rpu_categorie_id` INT (11) UNSIGNED
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `rpu_link_cat` 
      ADD INDEX (`rpu_id`),
      ADD INDEX (`rpu_categorie_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.80");

        $query = "ALTER TABLE `rpu`
      ADD `commentaire` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.81");

        $query = "ALTER TABLE `protocole_rpu` 
      ADD `default` ENUM ('0','1') DEFAULT '0' AFTER `actif`;";
        $this->addQuery($query);
        $this->makeRevision("0.82");

        $query = "ALTER TABLE `motif_question` 
                ADD `num_group` INT (11);";
        $this->addQuery($query);
        $this->makeRevision("0.83");

        $query = "ALTER TABLE `extract_passages`
                CHANGE `type` `type` ENUM ('rpu','urg','activite','uhcd', 'tension') DEFAULT 'rpu';";
        $this->addQuery($query);
        $this->makeRevision("0.84");

        $query = "ALTER TABLE `extract_passages`
                CHANGE `type` `type` ENUM ('rpu','urg','activite','uhcd', 'tension', 'deces') DEFAULT 'rpu';";
        $this->addQuery($query);
        $this->makeRevision("0.85");

        $query = "ALTER TABLE `rpu`
                DROP `radio_demande`,
                DROP `radio_debut`,
                DROP `radio_fin`,
                DROP `radio_type`,
                DROP `bio_demande`,
                DROP `bio_depart`,
                DROP `bio_retour`,
                DROP `bio_user_id`,
                DROP `specia_att`,
                DROP `specia_arr`;";
        $this->addQuery($query);
        $this->makeRevision("0.86");

        $this->addDefaultConfig("dPurgences CRPU motif_rpu_view", "dPurgences motif_rpu_view");
        $this->makeRevision("0.87");

        $query = "ALTER TABLE `echelle_tri`
                CHANGE `enceinte` `enceinte` ENUM ('0','1');";
        $this->addQuery($query);
        $this->makeRevision("0.88");

        $this->addDefaultConfig("dPurgences CRPU initialiser_sortie_prevue", "dPurgences sortie_prevue");

        $this->makeRevision("0.89");

        $query = "ALTER TABLE `rpu`
                ADD `date_sortie_aut` DATETIME AFTER `sortie_autorisee`";
        $this->addQuery($query);

        $this->makeRevision("0.90");

        $query = "ALTER TABLE `rpu` 
                ADD `cimu` ENUM ('5','4','3','2','1');";
        $this->addQuery($query);

        $this->makeRevision("0.91");

        $query = "ALTER TABLE `extract_passages`
                CHANGE `message` `message` MEDIUMTEXT,
                CHANGE `type` `type` ENUM ('rpu','urg','activite','uhcd', 'tension', 'deces', 'lits') DEFAULT 'rpu';";
        $this->addQuery($query);

        $this->makeRevision("0.92");

        $query = "ALTER TABLE `extract_passages`
                CHANGE `message` `message_xml` MEDIUMTEXT,
                ADD `message_any` MEDIUMTEXT AFTER `message_xml`;";
        $this->addQuery($query);

        $this->makeRevision('0.93');
        $query = "ALTER TABLE `rpu`
                DROP COLUMN `urprov`,
                DROP COLUMN `urmuta`,
                DROP COLUMN `urtrau`;";
        $this->addQuery($query);

        $this->makeRevision('0.94');
        $query = "CREATE TABLE `rpu_reeval_pec` (
                `rpu_reeval_pec_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `rpu_id` INT (11) UNSIGNED NOT NULL,
                `user_id` INT (11) UNSIGNED,
                `datetime` DATETIME NOT NULL,
                `ccmu` ENUM ('1','P','2','3','4','5','D'),
                `cimu` ENUM ('5','4','3','2','1'),
                `commentaire` TEXT,
                 INDEX (`rpu_id`),
                 INDEX (`user_id`),
                 INDEX (`datetime`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.95");

        $query = "ALTER TABLE `rpu` 
                ADD `decision_uhcd` ENUM ('0','1') DEFAULT '0',
                ADD `diag_incertain_pec` ENUM ('0','1') DEFAULT '0',
                ADD `caractere_instable` ENUM ('0','1') DEFAULT '0',
                ADD `surv_hosp_specifique` ENUM ('0','1') DEFAULT '0',
                ADD `exam_comp` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.96');

        $this->addPrefQuery('stats_urgences_age_ranges', '0-14|15-74|75-84|85');

        $this->makeRevision("0.97");
        $this->setModuleCategory("plateau_technique", "metier");

        $this->makeRevision("0.98");

        $query = "ALTER TABLE `protocole_rpu` 
                ADD `provenance` ENUM ('1','2','3','4','5','6','7','8');";
        $this->addQuery($query);

        $this->makeRevision("0.99");

        $this->addQuery(
            " ALTER TABLE `extract_passages`
               CHANGE `type` `type` VARCHAR (255) DEFAULT 'rpu';"
        );

        $this->makeRevision("1.00");

        $this->addQuery(
            "ALTER TABLE `rpu`
               CHANGE `orientation` `orientation` 
                   ENUM('HDT','HO','SC','SI','REA','UHCD','MED','CHIR','OBST','FUGUE','SCAM','PSA','REO','NA');"
        );

        $this->makeRevision("1.01");

        $query = "ALTER TABLE `rpu`
                ADD `french_triage` ENUM ('1', '2', '3A', '3B', '4', '5');";
        $this->addQuery($query);

        $this->makeRevision("1.02");

        $query = "ALTER TABLE `rpu_reeval_pec`
                ADD `french_triage` ENUM ('1', '2', '3A', '3B', '4', '5');";
        $this->addQuery($query);

        $this->makeRevision("1.03");

        $query = "ALTER TABLE `rpu`
                DROP COLUMN `echelle_tri_valide`,
                DROP COLUMN `code_diag`;";
        $this->addQuery($query);

        $query = "DROP TABLE `echelle_tri_cte`;";
        $this->addQuery($query);

        $query = "DROP TABLE `echelle_tri`;";
        $this->addQuery($query);

        $query = "DROP TABLE `motif_urgence`;";
        $this->addQuery($query);

        $query = "DROP TABLE `motif_question`;";
        $this->addQuery($query);

        $query = "DROP TABLE `motif_chapitre`;";
        $this->addQuery($query);

        $query = "DROP TABLE `motif_reponse`;";
        $this->addQuery($query);

        $this->makeRevision("1.04");
        $query = "ALTER TABLE `protocole_rpu`
                ADD `pec_transport` ENUM('med','paramed','aucun')";
        $this->addQuery($query);

        $this->makeRevision("1.05");

        $this->moveConfiguration(
            'dPplanningOp CSejour required_from_when_transfert',
            'dPurgences CRPU required_from_when_transfert'
        );

        $this->mod_version = '1.06';
    }
}
