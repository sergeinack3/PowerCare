<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSetup;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CSetupSsr extends CSetup
{
    /**
     * Change the start time of old SSR events that contain patient group ranges
     *
     * @return bool
     */
    protected function modifyOldSSREvents(): bool
    {
        $ljoin                         = [];
        $ljoin["plage_groupe_patient"] = "plage_groupe_patient.plage_groupe_patient_id = evenement_ssr.plage_groupe_patient_id";
        $ljoin["sejour"]               = "evenement_ssr.sejour_id = sejour.sejour_id";

        $where                                          = [];
        $where["evenement_ssr.plage_groupe_patient_id"] = "IS NOT NULL";
        $where["evenement_ssr.debut"]                   = "IS NOT NULL";
        $where["sejour.annule"]                         = " = '0'";

        $evenement_ssr  = new CEvenementSSR();
        $evenements_ssr = $evenement_ssr->loadList($where, null, null, null, $ljoin);

        $events_by_sejour = [];

        if (is_array($evenements_ssr)) {
            foreach ($evenements_ssr as $_event) {
                $date = CMbDT::date($_event->debut);

                $events_by_sejour[$_event->sejour_id][$_event->plage_groupe_patient_id][$date][$_event->_id] = $_event;
            }
        }

        foreach ($events_by_sejour as $_plages_groupe) {
            foreach ($_plages_groupe as $_dates) {
                foreach ($_plages_groupe as $_dates) {
                    foreach ($_dates as $_events) {
                        if (count($_events) <= 1) {
                            continue;
                        }

                        $datetime_event_debut = "";

                        foreach ($_events as $_event) {
                            if (!$datetime_event_debut) {
                                $datetime_event_debut = $_event->debut;
                            }

                            $_event->debut = $datetime_event_debut;
                            $_event->store();

                            $datetime_event_debut = CMbDT::dateTime("+$_event->_duree minutes", $_event->debut);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Standard constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "ssr";

        $this->makeRevision("0.0");

        // Plateau technique
        $this->makeRevision("0.01");
        $query = "CREATE TABLE `plateau_technique` (
      `plateau_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `group_id` INT (11) UNSIGNED,
      `nom` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `plateau_technique` 
      ADD INDEX (`group_id`);";
        $this->addQuery($query);

        // Equipement
        $this->makeRevision("0.02");
        $query = "CREATE TABLE `equipement` (
      `equipement_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `plateau_id` INT (11) UNSIGNED NOT NULL,
      `nom` VARCHAR (255) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `equipement` 
      ADD INDEX (`plateau_id`);";
        $this->addQuery($query);

        // Technicien
        $this->makeRevision("0.03");
        $this->addDependency("mediusers", "0.1");
        $query = "CREATE TABLE `technicien` (
      `technicien_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `plateau_id` INT (11) UNSIGNED NOT NULL,
      `kine_id` INT (11) UNSIGNED NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `technicien` 
      ADD INDEX (`plateau_id`),
      ADD INDEX (`kine_id`);";
        $this->addQuery($query);

        // Fiche d'autonomie
        $this->makeRevision("0.04");
        $query = "CREATE TABLE `fiche_autonomie` (
      `fiche_autonomie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `sejour_id` INT (11) UNSIGNED NOT NULL,
      `alimentation` ENUM ('autonome','partielle','totale'),
      `toilette` ENUM ('autonome','partielle','totale'),
      `habillage_haut` ENUM ('autonome','partielle','totale'),
      `habillage_bas` ENUM ('autonome','partielle','totale'),
      `utilisation_toilette` ENUM ('sonde','couche','bassin','stomie'),
      `transfert_lit` ENUM ('autonome','partielle','totale'),
      `locomotion` ENUM ('autonome','partielle','totale'),
      `locomotion_materiel` ENUM ('canne','cadre','fauteuil'),
      `escalier` ENUM ('autonome','partielle','totale'),
      `pansement` ENUM ('0','1'),
      `escarre` ENUM ('0','1'),
      `comprehension` ENUM ('intacte','alteree'),
      `expression` ENUM ('intacte','alteree'),
      `memoire` ENUM ('intacte','alteree'),
      `resolution_pb` ENUM ('intacte','alteree'),
      `etat_psychique` TEXT,
      `devenir_envisage` TEXT
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `fiche_autonomie` 
      ADD INDEX (`sejour_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.05");
        $query = "ALTER TABLE `fiche_autonomie` 
      ADD `soins_cutanes` TEXT;";
        $this->addQuery($query);

        // Bilan SSR
        $this->makeRevision("0.06");
        $query = "CREATE TABLE `bilan_ssr` (
      `bilan_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `sejour_id` INT (11) UNSIGNED NOT NULL,
      `kine` VARCHAR (255) NOT NULL,
      `ergo` VARCHAR (255) NOT NULL,
      `psy` VARCHAR (255) NOT NULL,
      `ortho` VARCHAR (255) NOT NULL,
      `diet` VARCHAR (255) NOT NULL,
      `social` VARCHAR (255) NOT NULL,
      `apa` VARCHAR (255) NOT NULL,
      `entree` TEXT,
      `sortie` TEXT
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `bilan_ssr` 
      ADD INDEX (`sejour_id`);";
        $this->addQuery($query);

        // RHS
        $this->makeRevision("0.07");
        $query = "CREATE TABLE `rhs` (
      `rhs_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `sejour_id` INT (11) UNSIGNED NOT NULL,
      `date_monday` DATE NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `rhs` 
      ADD INDEX (`sejour_id`),
      ADD INDEX (`date_monday`);";
        $this->addQuery($query);

        // D�pendances RHS
        $this->makeRevision("0.08");
        $query = "CREATE TABLE `dependances_rhs` (
      `dependances_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `rhs_id` INT (11) UNSIGNED NOT NULL,
      `habillage`    ENUM ('1','2','3','4'),
      `deplacement`  ENUM ('1','2','3','4'),
      `alimentation` ENUM ('1','2','3','4'),
      `continence`   ENUM ('1','2','3','4'),
      `comportement` ENUM ('1','2','3','4'),
      `relation`     ENUM ('1','2','3','4')
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `dependances_rhs` 
      ADD INDEX (`rhs_id`);";
        $this->addQuery($query);

        // Ligne d'activit�s RHS
        $this->makeRevision("0.09");
        $query = "CREATE TABLE `ligne_activites_rhs` (
      `ligne_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `rhs_id` INT (11) UNSIGNED NOT NULL,
      `executant_id` INT (11) UNSIGNED NOT NULL,
      `code_activite_cdarr` CHAR (4),
      `code_intervenant_cdarr` CHAR (2),
      `qty_mon` INT (11),
      `qty_tue` INT (11),
      `qty_wed` INT (11),
      `qty_thu` INT (11),
      `qty_fri` INT (11),
      `qty_sat` INT (11),
      `qty_sun` INT (11)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `ligne_activites_rhs` 
      ADD INDEX (`rhs_id`),
      ADD INDEX (`executant_id`);";
        $this->addQuery($query);

        // Bilan SSR: suppresion des anciennes prescriptions texte, ajout du kine
        $this->makeRevision("0.10");
        $query = "ALTER TABLE `bilan_ssr` 
      ADD `kine_id` INT (11) UNSIGNED,
      DROP COLUMN `kine`,
      DROP COLUMN `ergo`,
      DROP COLUMN `psy`,
      DROP COLUMN `ortho`,
      DROP COLUMN `social`,
      DROP COLUMN `diet`,
      DROP COLUMN `apa`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `bilan_ssr` 
      ADD INDEX (`kine_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "ALTER TABLE `ligne_activites_rhs` 
      CHANGE `code_activite_cdarr` `code_activite_cdarr` CHAR (4) NOT NULL,
      CHANGE `qty_mon` `qty_mon` TINYINT (4) UNSIGNED DEFAULT '0',
      CHANGE `qty_tue` `qty_tue` TINYINT (4) UNSIGNED DEFAULT '0',
      CHANGE `qty_wed` `qty_wed` TINYINT (4) UNSIGNED DEFAULT '0',
      CHANGE `qty_thu` `qty_thu` TINYINT (4) UNSIGNED DEFAULT '0',
      CHANGE `qty_fri` `qty_fri` TINYINT (4) UNSIGNED DEFAULT '0',
      CHANGE `qty_sat` `qty_sat` TINYINT (4) UNSIGNED DEFAULT '0',
      CHANGE `qty_sun` `qty_sun` TINYINT (4) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "CREATE TABLE `element_prescription_to_cdarr` (
      `element_prescription_to_cdarr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `element_prescription_id` INT (11) UNSIGNED NOT NULL,
      `code` CHAR (4) NOT NULL,
      `commentaire` VARCHAR (255)
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `element_prescription_to_cdarr` 
      ADD INDEX (`element_prescription_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "CREATE TABLE `evenement_ssr` (
      `evenement_ssr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `element_prescription_id` INT (11) UNSIGNED NOT NULL,
      `code` CHAR (4) NOT NULL,
      `sejour_id` INT (11) UNSIGNED NOT NULL,
      `debut` DATETIME NOT NULL,
      `duree` INT (11) UNSIGNED NOT NULL,
      `therapeute_id` INT (11) UNSIGNED NOT NULL,
      `realise` ENUM ('0','1') DEFAULT '0'
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `evenement_ssr` 
      ADD INDEX (`element_prescription_id`),
      ADD INDEX (`sejour_id`),
      ADD INDEX (`debut`),
      ADD INDEX (`therapeute_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $query = "ALTER TABLE `evenement_ssr` 
      ADD `equipement_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `evenement_ssr` 
      ADD INDEX (`equipement_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.15");
        $query = "ALTER TABLE `bilan_ssr` 
      ADD `technicien_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `bilan_ssr` 
      ADD INDEX (`technicien_id`);";
        $this->addQuery($query);

        $query = "UPDATE `bilan_ssr`
      SET technicien_id = 
        (SELECT technicien_id 
        FROM technicien
        WHERE kine_id = bilan_ssr.kine_id
        LIMIT 1);";
        $this->addQuery($query);

        $query = "ALTER TABLE `bilan_ssr` 
      DROP kine_id";
        $this->addQuery($query);

        $this->makeRevision("0.16");
        $query = "CREATE TABLE `acte_cdarr` (
      `acte_cdarr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `evenement_ssr_id` INT (11) UNSIGNED NOT NULL,
      `code` CHAR (4) NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_cdarr` 
      ADD INDEX (`evenement_ssr_id`);";
        $this->addQuery($query);

        $query = "INSERT INTO `acte_cdarr` (`evenement_ssr_id`,`code`)
      SELECT `evenement_ssr_id`,`code`
      FROM evenement_ssr";
        $this->addQuery($query);

        $query = "ALTER TABLE `evenement_ssr` 
      DROP `code`;";
        $this->addQuery($query);

        $this->makeRevision("0.17");
        $query = "ALTER TABLE `evenement_ssr` 
      ADD `remarque` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.18");
        $query = "ALTER TABLE `bilan_ssr` 
      ADD `brancardage` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.19");
        $query = "CREATE TABLE `replacement` (
      `replacement_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `sejour_id` INT (11) UNSIGNED NOT NULL,
      `conge_id` INT (11) UNSIGNED NOT NULL,
      `replacer_id` INT (11) UNSIGNED NOT NULL
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `replacement` 
      ADD INDEX (`sejour_id`),
      ADD INDEX (`conge_id`),
      ADD INDEX (`replacer_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.20");
        $query = "ALTER TABLE `fiche_autonomie` 
      ADD `toilettes` ENUM ('autonome','partielle','totale') NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE `fiche_autonomie` 
      ADD `antecedents` TEXT,
      ADD `traitements` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `evenement_ssr` 
              ADD `prescription_line_element_id` INT (11) UNSIGNED NOT NULL";
        $this->addQuery($query);

        if (CModule::getInstalled("dPprescription")) {
            $query = "UPDATE `evenement_ssr`
        SET `prescription_line_element_id` = (
          SELECT `prescription_line_element_id`
          FROM `prescription_line_element`
          LEFT JOIN `prescription` ON `prescription_line_element`.`prescription_id` = `prescription`.`prescription_id`
          WHERE `prescription`.`type` = 'sejour'
          AND `prescription`.`object_id` = `evenement_ssr`.`sejour_id`
          AND `prescription_line_element`.`element_prescription_id` = `evenement_ssr`.`element_prescription_id`
        );";
            $this->addQuery($query);
        }

        $query = "ALTER TABLE `evenement_ssr` 
      DROP `element_prescription_id`";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $query = "ALTER TABLE `bilan_ssr` 
      ADD `planification` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.24");
        $query = "ALTER TABLE `evenement_ssr` 
      CHANGE `sejour_id` `sejour_id` INT (11) UNSIGNED,
      ADD `seance_collective_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `evenement_ssr` 
      ADD INDEX (`prescription_line_element_id`),
      ADD INDEX (`seance_collective_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "ALTER TABLE `evenement_ssr` 
      CHANGE `prescription_line_element_id` `prescription_line_element_id` INT (11) UNSIGNED,
      CHANGE `debut` `debut` DATETIME,
      CHANGE `duree` `duree` INT (11) UNSIGNED,
      CHANGE `therapeute_id` `therapeute_id` INT (11) UNSIGNED,
      CHANGE `realise` `realise` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $this->addPrefQuery("ssr_planning_dragndrop", "0");
        $this->addPrefQuery("ssr_planning_resize", "0");

        $this->makeRevision("0.27");
        $query = "ALTER TABLE `equipement` 
      ADD `visualisable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.28");
        $this->addPrefQuery("ssr_planification_show_equipement", "1");
        $this->addPrefQuery("ssr_planification_duree", "30");

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `rhs` 
      ADD `facture` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `evenement_ssr` 
      ADD `annule` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `technicien` 
      ADD `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `equipement` 
      ADD `actif` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `replacement` 
      ADD `deb` DATE,
      ADD `fin` DATE;";
        $this->addQuery($query);
        $query = "ALTER TABLE `replacement` 
      ADD INDEX (`deb`),
      ADD INDEX (`fin`);";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "ALTER TABLE `plateau_technique`
      ADD `repartition` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `bilan_ssr`
      ADD `hospit_de_jour` ENUM ('0','1') DEFAULT '0',
      ADD `demi_journee_1` ENUM ('0','1') DEFAULT '0',
      ADD `demi_journee_2` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `replacement`
      ADD UNIQUE INDEX replacement (sejour_id, conge_id)";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "ALTER TABLE `ligne_activites_rhs` 
      ADD `auto` ENUM ('0','1') DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.37");
        $query = "ALTER TABLE `ligne_activites_rhs` 
      ADD UNIQUE ligne (rhs_id, executant_id, code_activite_cdarr)";
        $this->addQuery($query);

        $this->makeRevision("0.38");
        $query = "ALTER TABLE `acte_cdarr` 
      ADD `administration_id` INT (11) UNSIGNED,
      CHANGE `evenement_ssr_id` `evenement_ssr_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("0.39");

        $query = "ALTER TABLE `acte_cdarr` ADD INDEX (`administration_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.40");
        $query = "ALTER TABLE `acte_cdarr` ADD `sejour_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_cdarr` ADD INDEX (`sejour_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.41");

        if (CAppUI::conf("ssr recusation use_recuse") == 0) {
            $query = "UPDATE `sejour`
        SET `sejour`.`recuse` = '0'
        WHERE `sejour`.`type` = 'ssr'";
            $this->addQuery($query);
        }

        $this->makeRevision("0.42");

        $query = "CREATE TABLE `element_prescription_to_csarr` (
      `element_prescription_to_csarr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `element_prescription_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      `code` CHAR (7) NOT NULL,
      `commentaire` VARCHAR (255)
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `element_prescription_to_csarr` ADD INDEX (`element_prescription_id`);";
        $this->addQuery($query);


        $this->makeRevision("0.43");

        $query = "CREATE TABLE `acte_csarr` (
      `acte_csarr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `evenement_ssr_id` INT (11) UNSIGNED,
      `administration_id` INT (11) UNSIGNED,
      `sejour_id` INT (11) UNSIGNED,
      `code` CHAR (7) NOT NULL
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_csarr` 
      ADD INDEX (`evenement_ssr_id`),
      ADD INDEX (`administration_id`),
      ADD INDEX (`sejour_id`);";
        $this->addQuery($query);


        $this->makeRevision("0.44");
        if (!CAppUI::conf("ssr recusation use_recuse")) {
            $query = "UPDATE `sejour`
        SET `sejour`.`recuse` = '0'
        WHERE `sejour`.`type` = 'ssr'";
            $this->addQuery($query);
        }

        $this->makeRevision("0.45");
        $query = "ALTER TABLE `ligne_activites_rhs` 
      CHANGE `rhs_id` `rhs_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `executant_id` `executant_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `code_activite_cdarr` `code_activite_cdarr` CHAR (4),
      ADD `code_activite_csarr` CHAR (7);";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "ALTER TABLE `acte_csarr`
      ADD `modulateurs` VARCHAR (20),
      ADD `phases` VARCHAR (3);";
        $this->addQuery($query);
        $this->makeRevision("0.47");

        $query = "ALTER TABLE `evenement_ssr`
                ADD `type_seance` ENUM ('dediee','non_dediee','collective') DEFAULT 'dediee';";
        $this->addQuery($query);

        $query = "UPDATE `evenement_ssr`
        SET `evenement_ssr`.`type_seance` = 'non_dediee'
        WHERE `evenement_ssr`.`equipement_id` IS NOT NULL";
        $this->addQuery($query);

        $query = "UPDATE `evenement_ssr`
        SET `evenement_ssr`.`type_seance` = 'collective'
        WHERE  `evenement_ssr`.`seance_collective_id` IS NOT NULL";
        $this->addQuery($query);

        $query = "UPDATE `evenement_ssr` e
        INNER JOIN `evenement_ssr` s ON s.`seance_collective_id` = e.`evenement_ssr_id`
        SET e.`type_seance` = 'collective'
        WHERE e.`seance_collective_id` IS NULL
        AND s.`type_seance` = 'collective'";
        $this->addQuery($query);
        $this->makeRevision("0.48");

        $query = "ALTER TABLE `evenement_ssr`
                ADD `nb_patient_seance` INT (11);";
        $this->addQuery($query);
        $this->makeRevision("0.49");

        $query = "ALTER TABLE `ligne_activites_rhs`
                ADD `modulateurs` VARCHAR (20),
                ADD `phases` VARCHAR (3),
                ADD `nb_patient_seance` INT (11);";
        $this->addQuery($query);
        $this->makeRevision("0.50");

        $query = "ALTER TABLE `evenement_ssr`
                CHANGE `type_seance` `type_seance` ENUM ('dediee','non_dediee','collective') DEFAULT 'dediee';";
        $this->addQuery($query);

        $query = "UPDATE `evenement_ssr`
        SET `evenement_ssr`.`type_seance` = 'dediee'
        WHERE  `evenement_ssr`.`type_seance` IS NULL";
        $this->addQuery($query);
        $this->makeRevision("0.51");

        $query = "ALTER TABLE `evenement_ssr`
                ADD `nb_intervenant_seance` INT (11);";
        $this->addQuery($query);

        $query = "ALTER TABLE `ligne_activites_rhs`
                ADD `nb_intervenant_seance` INT (11);";
        $this->addQuery($query);

        $query = "ALTER TABLE `acte_csarr`
                ADD `commentaire` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.52");

        $this->addDependency("dPplanningOp", "2.28");

        $query = "UPDATE `sejour`
      LEFT JOIN `bilan_ssr` ON `bilan_ssr`.`sejour_id` = `sejour`.`sejour_id`
      SET `sejour`.`hospit_de_jour` = `bilan_ssr`.`hospit_de_jour`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `bilan_ssr` DROP `hospit_de_jour`;";
        $this->addQuery($query);
        $this->makeRevision("0.53");

        $query = "ALTER TABLE `ligne_activites_rhs`
                ADD `commentaire` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.54");

        $query = "ALTER TABLE `evenement_ssr`
                ADD `therapeute2_id` INT (11) UNSIGNED,
                ADD `therapeute3_id` INT (11) UNSIGNED,
                ADD INDEX (`therapeute2_id`),
                ADD INDEX (`therapeute3_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.55");

        $query = "ALTER TABLE `acte_csarr`
                ADD `extension` CHAR (2);";
        $this->addQuery($query);

        $query = "ALTER TABLE `ligne_activites_rhs`
                ADD `extension` CHAR (2);";
        $this->addQuery($query);
        $this->makeRevision("0.56");

        $query = "ALTER TABLE `plateau_technique` 
                ADD `type` ENUM ('ssr','psy');";
        $this->addQuery($query);

        $this->makeRevision("0.57");

        $query = "ALTER TABLE `rhs` 
                ADD `FPP` VARCHAR (255),
                ADD `MMP` VARCHAR (255),
                ADD `AE` VARCHAR (255),
                ADD `DAS` VARCHAR (255),
                ADD `DAD` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.58");
        $query = "CREATE TABLE `element_prescription_to_presta_ssr` (
                `element_prescription_to_presta_ssr_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `element_prescription_id` INT (11) UNSIGNED NOT NULL,
                `code` VARCHAR (255) NOT NULL,
                `commentaire` VARCHAR (255),
                INDEX (`element_prescription_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `acte_prestation` (
                `acte_prestation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `type` ENUM ('tarmed','presta_ssr'),
                `commentaire` TEXT,
                `evenement_ssr_id` INT (11) UNSIGNED,
                `administration_id` INT (11) UNSIGNED,
                `sejour_id` INT (11) UNSIGNED,
                `code` VARCHAR (255) NOT NULL,
                INDEX (`evenement_ssr_id`),
                INDEX (`administration_id`),
                INDEX (`sejour_id`)
              )/*! ENGINE=MyISAM */ ;";
        $this->addQuery($query);

        $query = "CREATE TABLE `element_prescription_to_tarmed` (
                `element_prescription_to_tarmed_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `element_prescription_id` INT (11) UNSIGNED NOT NULL,
                `code` VARCHAR (10) NOT NULL,
                `commentaire` VARCHAR (255),
                INDEX (`element_prescription_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.59");
        $query = "INSERT INTO `configuration` (`feature`, `value`, `object_id`, `object_class`)
              SELECT REPLACE(`feature`, 'ssr general evenement_no_acte', 'ssr general use_acte_presta'), REPLACE(`value`, 1, 'aucun'), `object_id`, `object_class`
              FROM `configuration`
              WHERE `feature` = 'ssr general evenement_no_acte'
              AND (
                  `object_class` = 'CGroups' OR `object_class` IS NULL AND `object_id` IS NULL
              )";
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $query = "ALTER TABLE `ligne_activites_rhs` 
                ADD `code_activite` VARCHAR (255),
                ADD `type_activite` ENUM ('cdarr','csarr','presta_ssr','tarmed') DEFAULT 'csarr';";
        $this->addQuery($query);
        $this->makeRevision("0.61");

        $query = "CREATE TABLE `plage_collective` (
                `plage_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `element_prescription_id` INT (11) UNSIGNED NOT NULL,
                `trame_id` INT (11) UNSIGNED NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `equipement_id` INT (11) UNSIGNED,
                `nom` VARCHAR (255),
                `debut` TIME NOT NULL,
                `day_week` ENUM ('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
                `duree` INT (11) UNSIGNED NOT NULL,
                INDEX (`element_prescription_id`),
                INDEX (`trame_id`),
                INDEX (`user_id`),
                INDEX (`equipement_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "CREATE TABLE `trame_seance_collective` (
                `trame_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `function_id` INT (11) UNSIGNED NOT NULL,
                `type` ENUM ('ssr','psy') NOT NULL,
                `nom` VARCHAR (255),
                INDEX (`group_id`),
                INDEX (`function_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `evenement_ssr` 
                ADD `plage_id` INT (11) UNSIGNED,
                ADD INDEX (`plage_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.62");

        $this->addPrefQuery("edit_planning_collectif", "0");
        $this->makeRevision("0.63");

        $this->addPrefQuery("select_user_current_ordonnance_sortie", "1");
        $this->makeRevision("0.64");

        $query = "ALTER TABLE `plage_collective` 
                ADD `therapeute2_id` INT (11) UNSIGNED,
                ADD `therapeute3_id` INT (11) UNSIGNED,
                ADD `commentaire` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.65");

        $query = "CREATE TABLE `acte_plage_collective` (
                `acte_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `plage_id` INT (11) UNSIGNED NOT NULL,
                `code` VARCHAR (255) NOT NULL,
                `type` ENUM ('csarr','presta','tarmed'),
                INDEX (`plage_id`)
              )/*! ENGINE=MyISAM */;;";
        $this->addQuery($query);
        $this->makeRevision("0.66");

        $query = "ALTER TABLE `plage_collective` 
                ADD `active` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.67");

        $query = "ALTER TABLE `plage_collective` 
                ADD `niveau` ENUM ('1','2','3','4','5') NOT NULL DEFAULT '3';";
        $this->addQuery($query);

        $this->makeRevision('0.68');

        $query = "CREATE TABLE `favoris_csarr` (
                `favori_csarr_id` INT (11)UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `code` VARCHAR (7) NOT NULL
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('0.69');

        $query = "ALTER TABLE `element_prescription_to_csarr` 
                ADD `modulateurs` VARCHAR (255),
                ADD `code_ext_documentaire` VARCHAR (255),
                ADD `duree` INT(11),
                ADD `type_seance` ENUM ('dediee','non_dediee','collective');";
        $this->addQuery($query);

        $this->makeRevision('0.70');

        $query = "CREATE TABLE `categorie_groupe_patient` (
                `categorie_groupe_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `function_id` INT (11) UNSIGNED NOT NULL,
                `type` ENUM ('ssr','psy') NOT NULL,
                `nom` VARCHAR (255) NOT NULL,
                INDEX (`group_id`),
                INDEX (`function_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `plage_groupe_patient` (
                `plage_groupe_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `categorie_groupe_patient_id` INT (11) UNSIGNED NOT NULL,
                `reeducateur_id` INT (11) UNSIGNED NOT NULL,
                `reeducateur2_id` INT (11) UNSIGNED,
                `reeducateur3_id` INT (11) UNSIGNED,
                `equipement_id` INT (11) UNSIGNED,
                `elements_prescription` VARCHAR (255) NOT NULL,
                `nom` VARCHAR (255),
                `groupe_day` ENUM ('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
                `heure_debut` TIME NOT NULL,
                `heure_fin` TIME NOT NULL,
                `commentaire` TEXT,
                `actif` ENUM ('0','1') DEFAULT '1',
                INDEX (`categorie_groupe_patient_id`),
                INDEX (`reeducateur_id`),
                INDEX (`reeducateur2_id`),
                INDEX (`reeducateur3_id`),
                INDEX (`equipement_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('0.71');

        $query = "ALTER TABLE `evenement_ssr` 
                ADD `plage_groupe_patient_id` INT (11) UNSIGNED,
                ADD INDEX (`plage_groupe_patient_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `categorie_groupe_patient` 
                CHANGE `function_id` `function_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision('0.72');

        $query = "ALTER TABLE `categorie_groupe_patient` 
                DROP `function_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `plage_groupe_patient` 
                DROP `reeducateur_id`,
                DROP `reeducateur2_id`,
                DROP `reeducateur3_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `element_prescription_to_csarr` 
                ADD `default` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.73');

        $query = "CREATE TABLE `code_affectation` (
                `code_affectation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `code_type` ENUM ('H+') DEFAULT 'H+',
                `code` VARCHAR(32) NOT NULL,
                `function_id` INT (11) UNSIGNED NOT NULL,
                INDEX (`code`),
                INDEX (`function_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision('0.74');

        $query = "UPDATE `evenement_ssr`
                LEFT JOIN `evenement_ssr` AS `evt_parent` ON `evt_parent`.`evenement_ssr_id` = `evenement_ssr`.`seance_collective_id`
                SET `evenement_ssr`.`therapeute_id` = evt_parent.therapeute_id,
                `evenement_ssr`.`therapeute2_id` = evt_parent.therapeute2_id,
                `evenement_ssr`.`therapeute3_id` = evt_parent.therapeute3_id,
                `evenement_ssr`.`debut` = evt_parent.debut,
                `evenement_ssr`.`duree` = evt_parent.duree
                WHERE `evenement_ssr`.seance_collective_id IS NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.75");
        $this->setModuleCategory("plateau_technique", "metier");

        $this->makeRevision("0.76");
        $query = "ALTER TABLE `evenement_ssr` 
                ADD `niveau_individuel` ENUM ('1','2','3','4','5');";
        $this->addQuery($query);

        $this->makeRevision("0.77");
        $query = "ALTER TABLE `evenement_ssr` 
                ADD `patient_missing` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.78");

        //Ajout d'une quantité sur les actes d'événement SSR
        $query = "ALTER TABLE `acte_cdarr` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `acte_csarr` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `acte_prestation` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);

        //Ajout d'index
        $query = "ALTER TABLE `plage_collective` 
                ADD INDEX (`therapeute2_id`),
                ADD INDEX (`therapeute3_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `favoris_csarr` 
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.79");
        $query = "ALTER TABLE `ligne_activites_rhs` 
                CHANGE `qty_mon` `qty_mon` FLOAT UNSIGNED DEFAULT '0',
                CHANGE `qty_tue` `qty_tue` FLOAT UNSIGNED DEFAULT '0',
                CHANGE `qty_wed` `qty_wed` FLOAT UNSIGNED DEFAULT '0',
                CHANGE `qty_thu` `qty_thu` FLOAT UNSIGNED DEFAULT '0',
                CHANGE `qty_fri` `qty_fri` FLOAT UNSIGNED DEFAULT '0',
                CHANGE `qty_sat` `qty_sat` FLOAT UNSIGNED DEFAULT '0',
                CHANGE `qty_sun` `qty_sun` FLOAT UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.80");
        $query = "ALTER TABLE `element_prescription_to_cdarr` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `element_prescription_to_csarr` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `element_prescription_to_presta_ssr` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `element_prescription_to_tarmed` 
                ADD `quantite` FLOAT DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.81");
        $query = "ALTER TABLE `acte_plage_collective` 
                ADD `quantite` FLOAT UNSIGNED DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision('0.82');

        $this->addMethod("modifyOldSSREvents");

        $this->makeRevision('0.83');

        $this->addQuery("ALTER TABLE `dependances_rhs` 
                ADD `habillage_bas` ENUM ('1','2','3','4'),
                ADD `deplacement_transfert_toilette` ENUM ('1','2','3','4'),
                ADD `deplacement_transfert_baignoire` ENUM ('1','2','3','4'),
                ADD `deplacement_locomotion` ENUM ('1','2','3','4'),
                ADD `deplacement_escalier`  ENUM ('1','2','3','4'),
                ADD `alimentation_mastication` ENUM ('1','2','3','4'),
                ADD `alimentation_deglutition` ENUM ('1','2','3','4'),
                ADD `continence_controle_defecation` ENUM ('1','2','3','4'),
                ADD `relation_expression_claire` ENUM ('1','2','3','4'),
                CHANGE `habillage` `habillage_haut`  ENUM ('1','2','3','4'),
                CHANGE `deplacement` `deplacement_transfert_lit_chaise` ENUM ('1','2','3','4'),
                CHANGE `alimentation` `alimentation_utilisations_ustensile` ENUM ('1','2','3','4'),
                CHANGE `continence` `continence_controle_miction` ENUM ('1','2','3','4'),
                CHANGE `relation` `relation_comprehension_communication` ENUM ('1','2','3','4');");

        $this->makeRevision('0.84');

        $this->updateConfigurationValue('ssr general use_acte_presta', 'presta_tarmed', "presta");

        $this->addQuery('DROP TABLE `element_prescription_to_tarmed`;');

        $this->addQuery(
            "ALTER TABLE `ligne_activites_rhs`
             MODIFY `type_activite` ENUM ('cdarr','csarr','presta_ssr') DEFAULT 'csarr';"
        );

        $this->addQuery(
            "ALTER TABLE `acte_plage_collective`
             MODIFY `type` ENUM ('csarr','presta','tarmed');"
        );

        $this->addQuery(
            "ALTER TABLE `acte_prestation`
             MODIFY `type` ENUM ('presta_ssr');"
        );

        $this->mod_version = "0.85";

        // Data source query
        $query = "SELECT * FROM `intervenant` WHERE `libelle` LIKE '%DOCUMENTALISTE%'";
        $this->addDatasource("cdarr", $query);

        if (CAppUI::gconf("ssr general use_acte_presta") == 'csarr') {
            $query = "SELECT * FROM `activite` WHERE `code` = 'ALQ+402' /* DETECTION CSARR BASE V2021 */";
            $this->addDatasource("csarr", $query);
        } elseif (CAppUI::gconf("ssr general use_acte_presta") == 'presta') {
            $query = "SELECT * FROM `prestation` WHERE `type` = 'massotherapie' AND `code` = '7002'";
            $this->addDatasource("presta_ssr", $query);
        }

        $query = "-- Modification code intervenant 29 vers 33 --
          SELECT * 
            FROM `intervenant` 
            WHERE `code` = '33' AND `libelle` LIKE '%NEUROPSYCHOLOGUE%';";
        $this->addDatasource("cdarr", $query);
    }
}
