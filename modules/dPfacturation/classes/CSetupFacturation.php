<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CSetup;

/**
 * Setup du module
 **/
/**
 * @codeCoverageIgnore
 */
class CSetupFacturation extends CSetup
{
    /**
     * Construct
     **/
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPfacturation";
        $this->makeRevision("0.0");

        $query = "CREATE TABLE `facture` (
       `facture_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
       `date` DATE NOT NULL, 
       `sejour_id` INT(11) UNSIGNED NOT NULL, 
      PRIMARY KEY (`facture_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `factureitem` (
         `factureitem_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
         `facture_id` INT(11) UNSIGNED NOT NULL, 
         `libelle` TEXT NOT NULL, 
         `prix_ht` FLOAT NOT NULL, 
         `taxe` FLOAT, 
      PRIMARY KEY (`factureitem_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.10");
        $query = "ALTER TABLE `facture` ADD `prix` FLOAT NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "ALTER TABLE `facture` 
              ADD INDEX (`date`),
              ADD INDEX (`sejour_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `factureitem` 
              ADD INDEX (`facture_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "CREATE TABLE `facturecatalogueitem` (
              `facturecatalogueitem_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `libelle` TEXT NOT NULL,
              `prix_ht` DECIMAL (10,3) NOT NULL,
              `taxe` FLOAT NOT NULL,
              `type` ENUM ('produit','service')
     ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "ALTER TABLE `factureitem` 
                ADD `facture_catalogue_item_id` INT (11) UNSIGNED NOT NULL,
                ADD `reduction` DECIMAL (10,3);";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureitem` 
                 ADD INDEX (`facture_catalogue_item_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.14");

        $query = "CREATE TABLE `facture_etablissement` (
              `facture_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `dialyse` ENUM ('0','1') DEFAULT '0',
              `rques_assurance_maladie` TEXT,
              `rques_assurance_accident` TEXT,
              `patient_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `praticien_id` INT (11) UNSIGNED,
              `remise` DECIMAL (10,3) DEFAULT '0',
              `ouverture` DATE NOT NULL,
              `cloture` DATE,
              `du_patient` DECIMAL (10,3) NOT NULL DEFAULT '0',
              `du_tiers` DECIMAL (10,3) NOT NULL DEFAULT '0',
              `type_facture` ENUM ('maladie','accident') NOT NULL DEFAULT 'maladie',
              `patient_date_reglement` DATE,
              `tiers_date_reglement` DATE,
              `npq` ENUM ('0','1') NOT NULL DEFAULT '0',
              `cession_creance` ENUM ('0','1') NOT NULL DEFAULT '0',
              `assurance_maladie` INT (11) UNSIGNED,
              `assurance_accident` INT (11) UNSIGNED,
              `send_assur_base` ENUM ('0','1') DEFAULT '0',
              `send_assur_compl` ENUM ('0','1') DEFAULT '0',
              `facture` ENUM ('-1','0','1') NOT NULL DEFAULT '0',
              `ref_accident` TEXT,
              `statut_pro` ENUM ('chomeur','etudiant','non_travailleur','independant','salarie','sans_emploi'),
              `num_reference` VARCHAR (27),
              `envoi_xml` ENUM ('0','1') DEFAULT '1'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_etablissement` 
              ADD INDEX (`patient_id`),
              ADD INDEX (`praticien_id`),
              ADD INDEX (`ouverture`),
              ADD INDEX (`cloture`),
              ADD INDEX (`patient_date_reglement`),
              ADD INDEX (`tiers_date_reglement`),
              ADD INDEX (`assurance_maladie`),
              ADD INDEX (`assurance_accident`);";
        $this->addQuery($query);

        $this->makeRevision("0.15");

        $query = "CREATE TABLE `facture_liaison` (
              `facture_liaison_id` INT (11) NOT NULL auto_increment PRIMARY KEY,
              `facture_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `facture_class` ENUM ('CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CFactureCabinet',
              `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `object_class` VARCHAR (80) NOT NULL DEFAULT 'CConsultation'
                ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_liaison` 
              ADD INDEX (`object_id`),
              ADD INDEX (`object_class`);";
        $this->addQuery($query);
        $this->makeRevision("0.16");

        $query = "ALTER TABLE `factureitem` 
              CHANGE `facture_id` `object_id` INT (11) NOT NULL DEFAULT '0',
              CHANGE `prix_ht` `prix` DECIMAL (10,2) NOT NULL DEFAULT '0',
              ADD `object_class` VARCHAR (80) NOT NULL DEFAULT 'CFactureCabinet',
              ADD `date` DATE NOT NULL,
              ADD `code` TEXT NOT NULL,
              ADD `type` ENUM ('CActeNGAP','CFraisDivers','CActeCCAM','CActeTarmed','CActeCaisse') NOT NULL
              DEFAULT 'CActeCCAM',
              ADD `quantite` INT (11) NOT NULL DEFAULT '0',
              ADD `coeff` DECIMAL (10,2) NOT NULL DEFAULT '0',
              DROP `facture_catalogue_item_id`,
              DROP `taxe`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureitem` 
              ADD INDEX (`object_id`),
              ADD INDEX (`object_class`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureitem` 
              ADD INDEX (`date`);";
        $this->addQuery($query);
        $this->makeRevision("0.17");

        $query = "DROP TABLE facture;";
        $this->addQuery($query);

        $query = "DROP TABLE facturecatalogueitem;";
        $this->addQuery($query);
        $this->makeRevision("0.18");

        $query = "ALTER TABLE `facture_etablissement` 
              ADD `temporaire` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.19");

        $query = "ALTER TABLE `factureitem` 
              ADD `pm` DECIMAL (10,2),
              ADD `pt` DECIMAL (10,2),
              ADD `coeff_pm` DECIMAL (10,2),
              ADD `coeff_pt` DECIMAL (10,2);";
        $this->addQuery($query);
        $this->makeRevision("0.20");

        $query = "ALTER TABLE `factureitem` 
              ADD `use_tarmed_bill` ENUM ('0','1') DEFAULT '0',
              CHANGE `prix` `montant_base` DECIMAL (10,2) NOT NULL DEFAULT '0',
              ADD `montant_depassement` DECIMAL (10,2) DEFAULT '0' AFTER montant_base,
              ADD `code_ref` TEXT,
              ADD `code_caisse` TEXT;";
        $this->addQuery($query);

        /*
         * Pour créer automatiquement les droits des utilisateurs sur le module fse,
         * on a besoin de récupérer l'id du module Facturation dans la table modules.
         * Mais l'entrée correspondante n'est créée qu'à la fin du setup.
         * On doit donc faire le setup en 2 fois.
         */

        /* @todo: check if that stuff is still necessary
        if (count($this->ds->loadList("SELECT * FROM modules WHERE mod_name = 'dPfacturation'")) == 0) {
            $this->mod_version = "0.21";

            return;
        }
        */

        $this->makeRevision("0.21");

        //Ecrit les droits utilisateurs sur le module facturation
        $query = "INSERT INTO `perm_module` (`user_id`, `mod_id`, `permission`, `view`)
              SELECT u.user_id, m.mod_id, 2, 0
              FROM perm_module AS p, modules AS m, modules AS n, users AS u
              WHERE m.mod_name = 'dPfacturation'
              AND u.template = '1'
              AND n.mod_name = 'dPcabinet'
              AND p.mod_id = n.mod_id
              AND p.permission = '2'
              AND p.user_id = u.user_id
              AND NOT EXISTS (
                SELECT * FROM perm_module AS o
                WHERE o.user_id = u.user_id
                AND o.mod_id = m.mod_id
                AND p.permission = '2'
              );";
        $this->addQuery($query);
        $this->makeRevision("0.22");

        $query = "CREATE TABLE `facture_relance` (
              `relance_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `object_class` ENUM ('CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CFactureCabinet',
              `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
              `date` DATE,
              `etat` ENUM ('emise','regle','renouvelle') NOT NULL DEFAULT 'emise',
              `du_patient` DECIMAL (10,2),
              `du_tiers` DECIMAL (10,2),
              `numero` TINYINT (4) UNSIGNED NOT NULL DEFAULT '1'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_relance` 
              ADD INDEX (`object_id`),
              ADD INDEX (`date`);";
        $this->addQuery($query);
        $this->makeRevision("0.23");

        $query = "ALTER TABLE `facture_liaison` 
                CHANGE `facture_id` `facture_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_liaison` 
                ADD INDEX (`facture_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.24");

        $query = "ALTER TABLE `facture_etablissement` 
                CHANGE `statut_pro` `statut_pro`
                ENUM ('chomeur','etudiant','non_travailleur','independant','invalide','militaire','retraite',
                'salarie_fr','salarie_sw','sans_emploi');";
        $this->addQuery($query);
        $this->makeRevision("0.25");

        $query = "CREATE TABLE `retrocession` (
                `retrocession_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `praticien_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `nom` VARCHAR (255) NOT NULL,
                `type` ENUM ('montant','pct','autre') DEFAULT 'montant',
                `valeur` DECIMAL (10,2),
                `pct_pm` FLOAT DEFAULT '0',
                `pct_pt` FLOAT DEFAULT '0',
                `code_class` ENUM ('CActeCCAM','CActeNAGP','CActeTarmed','CActeCaisse') DEFAULT 'CActeCCAM',
                `code` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `retrocession` 
                ADD INDEX (`praticien_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.26");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `annule` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `factureitem`
                ADD `seance` INT (11);";
        $this->addQuery($query);
        $this->makeRevision("0.27");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `definitive` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.28");

        $query = "ALTER TABLE `retrocession`
                ADD `use_pm` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.29");

        $query = "ALTER TABLE `facture_relance`
                ADD `statut` ENUM ('inactive','first','second','third','contentieux','poursuite'),
                ADD `poursuite` ENUM ('defaut','continuation','etranger','faillite','hors_pays','deces','inactive',
                'saisie','introuvable');";
        $this->addQuery($query);
        $this->makeRevision("0.30");

        $query = "ALTER TABLE `retrocession`
                ADD `active` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("0.31");

        $query = "ALTER TABLE `facture_etablissement`
                CHANGE `type_facture` `type_facture` ENUM ('maladie','accident','esthetique')
                NOT NULL DEFAULT 'maladie';";
        $this->addQuery($query);
        $this->makeRevision("0.32");

        $query = "CREATE TABLE `debiteur` (
                `debiteur_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `numero` INT (11) NOT NULL DEFAULT '0',
                `nom` VARCHAR (50) NOT NULL,
                `description` VARCHAR (255)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.33");

        $query = "ALTER TABLE `factureitem`
                ADD `forfait` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.34");

        $query = "ALTER TABLE `factureitem`
                DROP `forfait`;";
        $this->addQuery($query);
        $this->makeRevision("0.35");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `numero` INT (11) UNSIGNED NOT NULL DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("0.36");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `du_tva` DECIMAL (10,2) DEFAULT '0',
                ADD `taux_tva` ENUM ('0', '19.6');";
        $this->addQuery($query);
        $this->makeRevision("0.37");

        $query = "CREATE TABLE `facture_journal` (
                `journal_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` TEXT NOT NULL,
                `type` ENUM ('paiement','debiteur','rappel','checklist'),
                `checklist_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_journal`
                ADD INDEX (`checklist_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `facture_link_journal` (
                `journal_liaison_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` ENUM ('CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CFactureCabinet',
                `journal_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_link_journal`
                ADD INDEX (`object_id`),
                ADD INDEX (`journal_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.38");
        $this->addDependency("dPcabinet", "1.87");

        $query = "INSERT INTO `facture_liaison` (`facture_id`, `facture_class`, `object_id`, `object_class`)
                SELECT f.facture_id, 'CFactureCabinet', c.consultation_id, 'CConsultation'
                FROM `facture_cabinet` f, `consultation` c
                WHERE c.facture_id IS NOT NULL
                AND c.valide = '1'
                AND f.facture_id = c.facture_id
                AND NOT EXISTS (
                  SELECT * FROM facture_liaison AS fl
                  WHERE fl.facture_id = c.facture_id
                  AND fl.facture_class = 'CFactureCabinet'
                  AND fl.object_class = 'CConsultation'
                  AND fl.object_id = c.consultation_id
                )
                GROUP BY f.facture_id;";
        $this->addQuery($query);
        $this->makeRevision("0.39");

        $query = "CREATE TABLE `facture_echeance` (
                `echeance_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` ENUM ('CFactureCabinet','CFactureEtablissement') NOT NULL DEFAULT 'CFactureCabinet',
                `date` DATE NOT NULL,
                `montant` DECIMAL (10,2) NOT NULL DEFAULT '0',
                `description` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_echeance`
                ADD INDEX (`object_id`),
                ADD INDEX (`date`);";
        $this->addQuery($query);
        $this->makeRevision("0.40");

        $query = "ALTER TABLE `facture_etablissement`
                CHANGE `taux_tva` `taux_tva` FLOAT DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.41");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_etablissement`
                ADD INDEX (`group_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.42");

        //Facture d'établissement de séjour
        $query = "UPDATE facture_etablissement, facture_liaison, sejour
          SET facture_etablissement.group_id = sejour.group_id
          WHERE facture_liaison.object_class = 'CSejour'
          AND facture_liaison.object_id = sejour.sejour_id
          AND facture_liaison.facture_id = facture_etablissement.facture_id
          AND facture_liaison.facture_class = 'CFactureEtablissement'";
        $this->addQuery($query);

        //Facture d'établissement de consultation de séjour
        $query = "UPDATE facture_etablissement, facture_liaison, sejour, consultation
          SET facture_etablissement.group_id = sejour.group_id
          WHERE facture_liaison.object_class = 'CConsultation'
          AND facture_liaison.object_id = consultation.consultation_id
          AND facture_liaison.facture_id = facture_etablissement.facture_id
          AND facture_liaison.facture_class = 'CFactureEtablissement'
          AND consultation.sejour_id = sejour.sejour_id";
        $this->addQuery($query);

        $query = "UPDATE facture_etablissement,users_mediboard, functions_mediboard
          SET facture_etablissement.group_id = functions_mediboard.group_id
          WHERE facture_etablissement.group_id = '0'
          AND facture_etablissement.praticien_id = users_mediboard.user_id
          AND functions_mediboard.function_id = users_mediboard.function_id";
        $this->addQuery($query);
        $this->makeRevision("0.43");

        $query = "ALTER TABLE `factureitem`
                ADD `cote` ENUM ('left','right');";
        $this->addQuery($query);
        $this->makeRevision("0.44");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `date_cas` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_etablissement`
                ADD INDEX (`date_cas`);";
        $this->addQuery($query);
        $this->makeRevision("0.45");

        $query = "ALTER TABLE `facture_relance`
                ADD `facture` ENUM ('-1','0','1') NOT NULL DEFAULT '0',
                ADD `envoi_xml` ENUM ('0','1') DEFAULT '1',
                ADD `request_date` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_relance`
                ADD INDEX (`request_date`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_etablissement`
                ADD `request_date` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_etablissement`
                ADD INDEX (`request_date`);";
        $this->addQuery($query);
        $this->makeRevision("0.46");

        $query = "CREATE TABLE `facture_rejet` (
                `facture_rejet_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `praticien_id` INT (11) UNSIGNED,
                `file_name` VARCHAR (255),
                `num_facture` VARCHAR (255),
                `date` DATE,
                `motif_rejet` TEXT,
                `statut` ENUM ('attente','traite') DEFAULT 'attente',
                `name_assurance` VARCHAR (255),
                `traitement` DATETIME,
                `facture_id` INT (11) UNSIGNED,
                `facture_class` ENUM ('CFactureCabinet','CFactureEtablissement')
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_rejet`
                ADD INDEX (`praticien_id`),
                ADD INDEX (`date`),
                ADD INDEX (`traitement`),
                ADD INDEX (`facture_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.47");

        $query = "ALTER TABLE `facture_etablissement`
                CHANGE `statut_pro` `statut_pro`
                ENUM ('chomeur','etudiant','non_travailleur','independant','invalide','militaire','retraite',
                'salarie_fr','salarie_sw','sans_emploi','enfant','enceinte');";
        $this->addQuery($query);
        $this->makeRevision("0.48");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `remarque` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.49");

        $query = "CREATE TABLE `facture_coeff` (
                `facture_coeff_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `praticien_id` INT (11) UNSIGNED,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `coeff` FLOAT NOT NULL DEFAULT '0',
                `nom` VARCHAR (255) NOT NULL,
                `description` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_coeff`
                ADD INDEX (`praticien_id`),
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_etablissement`
                ADD `coeff_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_etablissement`
                ADD INDEX (`coeff_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.50");

        $query = "ALTER TABLE `facture_etablissement`
                ADD `bill_printed` ENUM ('0','1') DEFAULT '0',
                ADD `justif_printed` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('0.51');

        $query = "ALTER TABLE `factureitem`
                CHANGE `type` `type`
                ENUM ('CActeNGAP','CFraisDivers','CActeCCAM','CActeTarmed','CActeCaisse', 'CActeLPP')
                NOT NULL DEFAULT 'CActeCCAM';";
        $this->addQuery($query);
        $this->makeRevision('0.52');

        $query = "ALTER TABLE `factureitem`
                ADD `executant_id` INT (11) UNSIGNED,
                ADD INDEX (`executant_id`);";
        $this->addQuery($query);
        $this->makeRevision('0.53');

        $query = "ALTER TABLE `factureitem`
                ADD `version_tarmed` VARCHAR (255);";
        $this->addQuery($query);
        $this->makeRevision('0.54');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `msg_error_xml` TEXT;";
        $this->addQuery($query);
        $this->makeRevision('0.55');

        $query = "ALTER TABLE `factureitem` 
                CHANGE `quantite` `quantite` FLOAT UNSIGNED NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision('0.56');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `montant_total` DECIMAL (10,3) DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision('0.57');

        $query = "ALTER TABLE `facture_etablissement` 
                CHANGE `statut_pro` `statut_pro` VARCHAR(20)";
        $this->addQuery($query);
        $this->makeRevision('0.58');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `category_id` INT (11) UNSIGNED,
                ADD INDEX (`category_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `facture_category` (
                `facture_category_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `function_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255),
                `code` INT (11),
                INDEX (`group_id`),
                INDEX (`function_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision('0.59');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `rcc` VARCHAR (25);";
        $this->addQuery($query);
        $this->makeRevision('0.60');

        $this->addDefaultConfig("dPfacturation CReglement use_debiteur", "dPfacturation CReglement use_debiteur");
        $this->addDefaultConfig(
            "dPfacturation CReglement add_pay_not_close",
            "dPfacturation CReglement add_pay_not_close"
        );
        $this->addDefaultConfig(
            "dPfacturation CReglement use_lock_acquittement",
            "dPfacturation CReglement use_lock_acquittement"
        );
        $this->addDefaultConfig(
            "dPfacturation CReglement use_mode_default",
            "dPfacturation CReglement use_mode_default"
        );
        $this->addDefaultConfig("dPfacturation CReglement use_echeancier", "dPfacturation CReglement use_echeancier");
        $this->addDefaultConfig(
            "dPfacturation CRetrocession use_retrocessions",
            "dPfacturation CRetrocession use_retrocessions"
        );
        $this->addDefaultConfig(
            "dPfacturation CFactureEtablissement use_temporary_bill",
            "dPfacturation CFactureEtablissement use_temporary_bill"
        );
        $this->addDefaultConfig(
            "dPfacturation CFactureEtablissement use_auto_cloture",
            "dPfacturation CFactureEtablissement use_auto_cloture"
        );
        $this->addDefaultConfig(
            "dPfacturation CFactureEtablissement view_bill",
            "dPfacturation CFactureEtablissement view_bill"
        );
        $this->addDefaultConfig(
            "dPfacturation CFactureCabinet use_auto_cloture",
            "dPfacturation CFactureCabinet use_auto_cloture"
        );
        $this->addDefaultConfig("dPfacturation CFactureCabinet view_bill", "dPfacturation CFactureCabinet view_bill");
        $this->addDefaultConfig("dPfacturation CJournalBill use_journaux", "dPfacturation CJournalBill use_journaux");
        $this->addDefaultConfig("dPfacturation Other use_search_easy", "dPfacturation Other use_search_easy");
        $this->addDefaultConfig("dPfacturation Other use_view_chainage", "dPfacturation Other use_view_chainage");
        $this->addDefaultConfig(
            "dPfacturation Other use_view_quantitynull",
            "dPfacturation Other use_view_quantitynull"
        );
        $this->addDefaultConfig("dPfacturation Other use_strict_cloture", "dPfacturation Other use_strict_cloture");
        $this->addDefaultConfig("dPfacturation Other use_field_definitive", "dPfacturation Other use_field_definitive");
        $this->addDefaultConfig("dPfacturation Other edit_bill_alone", "dPfacturation Other edit_bill_alone");
        $this->addDefaultConfig("dPfacturation Other tag_EAN_fct", "dPfacturation Other tag_EAN_fct");
        $this->addDefaultConfig("dPfacturation Other tag_RCC_fct", "dPfacturation Other tag_RCC_fct");
        $this->addDefaultConfig("dPfacturation Other tag_RSS_praticien", "dPfacturation Other tag_RSS_praticien");
        $this->addDefaultConfig("dPfacturation CRelance use_relances", "dPfacturation CRelance use_relances");
        $this->addDefaultConfig(
            "dPfacturation CRelance nb_days_first_relance",
            "dPfacturation CRelance nb_days_first_relance"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance nb_days_second_relance",
            "dPfacturation CRelance nb_days_second_relance"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance nb_days_third_relance",
            "dPfacturation CRelance nb_days_third_relance"
        );
        $this->addDefaultConfig("dPfacturation CRelance add_first_relance", "dPfacturation CRelance add_first_relance");
        $this->addDefaultConfig(
            "dPfacturation CRelance add_second_relance",
            "dPfacturation CRelance add_second_relance"
        );
        $this->addDefaultConfig("dPfacturation CRelance add_third_relance", "dPfacturation CRelance add_third_relance");
        $this->addDefaultConfig(
            "dPfacturation CRelance nb_generate_pdf_relance",
            "dPfacturation CRelance nb_generate_pdf_relance"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance message_relance1_assur",
            "dPfacturation CRelance message_relance1_assur"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance message_relance2_assur",
            "dPfacturation CRelance message_relance2_assur"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance message_relance3_assur",
            "dPfacturation CRelance message_relance3_assur"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance message_relance1_patient",
            "dPfacturation CRelance message_relance1_patient"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance message_relance2_patient",
            "dPfacturation CRelance message_relance2_patient"
        );
        $this->addDefaultConfig(
            "dPfacturation CRelance message_relance3_patient",
            "dPfacturation CRelance message_relance3_patient"
        );
        $this->makeRevision('0.61');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `no_relance` ENUM ('0','1') DEFAULT '0',
                ADD `compte_ch_id` INT (11) UNSIGNED,
                ADD INDEX (`compte_ch_id`);";
        $this->addQuery($query);
        $this->makeRevision('0.62');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `num_compta` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision('0.63');

        $query = "ALTER TABLE `factureitem`
                MODIFY `coeff` DECIMAL (10,4),
                MODIFY `coeff_pm` DECIMAL (10, 4),
                MODIFY `coeff_pt` DECIMAL (10, 4);";
        $this->addQuery($query);
        $this->makeRevision('0.64');

        $this->addDependency('dPcabinet', '2.68');

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `bill_date_printed`   DATETIME DEFAULT NULL AFTER `bill_printed`,
                ADD `justif_date_printed` DATETIME DEFAULT NULL AFTER `justif_printed`;";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `bill_date_printed` = '1970-01-01'
                WHERE `bill_printed` = '1';";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `justif_date_printed` = '1970-01-01'
                WHERE `justif_printed` = '1';";
        $this->addQuery($query);

        $query = "CREATE TABLE `journal_envoi_xml` (
                `journal_envoi_xml_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `facture_id` INT (11) UNSIGNED NOT NULL,
                `facture_class` ENUM('CFactureCabinet', 'CFactureEtablissement') NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `date_envoi` DATETIME NOT NULL,
                `error` ENUM ('0', '1') DEFAULT '0' NOT NULL,
                `statut` TEXT,
                INDEX (`facture_id`, `facture_class`, `date_envoi`))/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision('0.65');

        $query = "ALTER TABLE `facture_cabinet` 
                DROP COLUMN `bill_printed`,
                DROP COLUMN `justif_printed`;";
        $this->addQuery($query);
        $this->makeRevision('0.66');

        $query = "ALTER TABLE `journal_envoi_xml` 
                ADD INDEX facture (facture_class, facture_id),
                ADD INDEX (`user_id`),
                DROP INDEX `facture_id`;";
        $this->addQuery($query);
        $this->makeRevision('0.67');

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `bill_date_printed`   DATETIME DEFAULT NULL AFTER `bill_printed`,
                ADD `justif_date_printed` DATETIME DEFAULT NULL AFTER `justif_printed`;";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `bill_date_printed` = '1970-01-01'
                WHERE `bill_printed` = '1';";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `justif_date_printed` = '1970-01-01'
                WHERE `justif_printed` = '1';";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_etablissement` 
                DROP COLUMN `bill_printed`,
                DROP COLUMN `justif_printed`;";
        $this->addQuery($query);
        $this->makeRevision('0.68');

        $this->addFunctionalPermQuery("send_bill_unity", "0");

        $this->makeRevision('0.69');
        $query = "ALTER TABLE `facture_etablissement` 
                ADD `bill_user_printed`   INT (11) UNSIGNED DEFAULT NULL AFTER `bill_date_printed`,
                ADD `justif_user_printed` INT (11) UNSIGNED DEFAULT NULL AFTER `justif_date_printed`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet` 
                ADD `bill_user_printed`   INT (11) UNSIGNED DEFAULT NULL AFTER `bill_date_printed`,
                ADD `justif_user_printed` INT (11) UNSIGNED DEFAULT NULL AFTER `justif_date_printed`;";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `bill_user_printed` = `praticien_id`
                WHERE `bill_date_printed` IS NOT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `justif_user_printed` = `praticien_id`
                WHERE `justif_date_printed` IS NOT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `bill_user_printed` = `praticien_id`
                WHERE `bill_date_printed` IS NOT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `justif_user_printed` = `praticien_id`
                WHERE `justif_date_printed` IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('0.70');
        $query = "CREATE TABLE `facture_avoir` (
       `facture_avoir_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
       `object_id`   INT(11) UNSIGNED NOT NULL,
       `object_class` ENUM ('CFactureCabinet', 'CFactureEtablissement') NOT NULL DEFAULT 'CFactureCabinet',
       `date` DATETIME NOT NULL, 
       `montant` DECIMAL (10, 3) NOT NULL, 
       `commentaire` TEXT, 
       INDEX (`date`),
       INDEX object (object_class, object_id),
      PRIMARY KEY (`facture_avoir_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('0.71');
        // Ajout des nouveaux champs de facturation
        $query = "ALTER TABLE `facture_etablissement`
                ADD `statut_envoi` ENUM ('echec', 'non_envoye', 'envoye') DEFAULT 'non_envoye',
                ADD `extourne` ENUM ('0','1') DEFAULT '0',
                ADD `regle` ENUM('0', '1') DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                ADD `statut_envoi` ENUM ('echec', 'non_envoye', 'envoye') DEFAULT 'non_envoye',
                ADD `extourne` ENUM ('0','1') DEFAULT '0',
                ADD `regle` ENUM('0', '1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.72');
        // Mise à jour des données pour les nouveaux champs de facturation d'etablissement
        $query = "UPDATE `facture_etablissement`
                SET `regle` = '1'
                WHERE (du_patient = 0 OR patient_date_reglement IS NOT NULL)
                AND (du_tiers = 0 OR tiers_date_reglement IS NOT NULL);";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `extourne` = '1'
                WHERE `definitive` = '1'
                  AND `annule` = '1'";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `statut_envoi` = 'echec'
                WHERE `facture` = '-1';";
        $this->addQuery($query);
        $query = "UPDATE `facture_etablissement`
                SET `statut_envoi` = 'envoye'
                WHERE `facture` = '1';";
        $this->addQuery($query);

        $this->makeRevision('0.73');
        // Mise à jour des données pour les nouveaux champs de facturation de cabinet
        $query = "UPDATE `facture_cabinet`
                SET `regle` = '1' 
                WHERE (du_patient = 0 OR patient_date_reglement IS NOT NULL)
                AND (du_tiers = 0 OR tiers_date_reglement IS NOT NULL);";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `extourne` = '1' 
                WHERE `definitive` = '1' 
                  AND `annule` = '1'";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `statut_envoi` = 'echec'
                WHERE `facture` = '-1';";
        $this->addQuery($query);
        $query = "UPDATE `facture_cabinet`
                SET `statut_envoi` = 'envoye'
                WHERE `facture` = '1';";
        $this->addQuery($query);

        $this->makeRevision('0.74');
        // Nettoyage des champs
        $query = "ALTER TABLE `facture_etablissement`
                DROP `facture`;";
        $this->addQuery($query);
        $query = "ALTER TABLE `facture_cabinet`
                DROP `facture`;";
        $this->addQuery($query);

        $this->makeRevision('0.75');
        $query = "ALTER TABLE `facture_relance`
                ADD `statut_envoi` ENUM ('echec', 'non_envoye', 'envoye') DEFAULT 'non_envoye';";
        $this->addQuery($query);

        $query = "UPDATE `facture_relance`
                SET `statut_envoi` = 'envoye'
                WHERE `facture` = '1';";
        $this->addQuery($query);

        $query = "UPDATE `facture_relance`
                SET `statut_envoi` = 'echec'
                WHERE `facture` = '-1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_relance`
                DROP `facture`;";
        $this->addQuery($query);

        $this->makeEmptyRevision("0.76");

        /*$query = "ALTER TABLE `facture_echeance`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_cabinet`
                    ADD INDEX (`bill_date_printed`),
                    ADD INDEX (`bill_user_printed`),
                    ADD INDEX (`justif_date_printed`),
                    ADD INDEX (`justif_user_printed`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_category`
                    ADD INDEX (`libelle`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_etablissement`
                    ADD INDEX (`bill_date_printed`),
                    ADD INDEX (`bill_user_printed`),
                    ADD INDEX (`justif_date_printed`),
                    ADD INDEX (`justif_user_printed`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `factureitem`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_liaison`
                    ADD INDEX facture (facture_class, facture_id),
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_rejet`
                    ADD INDEX facture (facture_class, facture_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `journal_envoi_xml`
                    ADD INDEX (`date_envoi`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_link_journal`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_relance`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);*/

        $this->makeRevision('0.77');

        $this->makeEmptyRevision('0.78');
        $query = "ALTER TABLE `facture_cabinet`
                 ADD `delai_envoi_xml` INT(11) DEFAULT NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_etablissement`
                ADD `delai_envoi_xml` INT(11) DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision('0.79');
        $query = "ALTER TABLE `facture_cabinet`
                 ADD `date_envoi_xml` DATE DEFAULT NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_etablissement`
                ADD `date_envoi_xml` DATE DEFAULT NULL";
        $this->addQuery($query);
        $this->makeRevision('0.80');

        $this->makeEmptyRevision('0.81');

        $query = "ALTER TABLE facture_cabinet
                ADD `diagnostic_id` INT (11) UNSIGNED,
                ADD INDEX (`diagnostic_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE facture_etablissement
                ADD `diagnostic_id` INT (11) UNSIGNED,
                ADD INDEX (`diagnostic_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.82");
        $this->setModuleCategory("administratif", "metier");

        $this->makeRevision("0.83");
        $query = "UPDATE facture_cabinet SET regle = '0' WHERE regle = '1' AND cloture IS NULL";
        $this->addQuery($query);

        $query = "UPDATE facture_etablissement SET regle = '0' WHERE regle = '1' AND cloture IS NULL";
        $this->addQuery($query);
        $this->makeRevision("0.84");

        $query = "ALTER TABLE `facture_cabinet` 
                ADD `extourne_id` INT (11) UNSIGNED,
                ADD INDEX (`extourne_id`)";
        $this->addQuery($query);

        $query = "ALTER TABLE `facture_etablissement` 
                ADD `extourne_id` INT (11) UNSIGNED,
                ADD INDEX (`extourne_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.85");

        $query = "ALTER TABLE `facture_echeance` 
                ADD `num_reference` VARCHAR (27);";
        $this->addQuery($query);

        $this->makeRevision('0.86');

        $this->addQuery('ALTER TABLE `facture_etablissement` ADD INDEX (`num_compta`);', true);

        $this->makeRevision('0.87');

        $this->addQuery(
            'ALTER TABLE `facture_cabinet` 
                ADD INDEX (`bill_user_printed`),
                ADD INDEX (`justif_user_printed`);'
        );

        $this->makeRevision('0.88');

        $this->addQuery(
            'ALTER TABLE `facture_cabinet`
             DROP `delai_envoi_xml`,
             DROP `date_envoi_xml`,
             DROP `diagnostic_id`;'
        );

        $this->addQuery(
            'ALTER TABLE `facture_etablissement`
             DROP `envoi_xml`,
             DROP `delai_envoi_xml`,
             DROP `date_envoi_xml`,
             DROP `compte_ch_id`,
             DROP `diagnostic_id`;'
        );

        $this->addQuery(
            "ALTER TABLE `factureitem`
             MODIFY `type` ENUM ('CActeNGAP','CFraisDivers','CActeCCAM','CActeLPP') NOT NULL DEFAULT 'CActeCCAM',
             DROP `use_tarmed_bill`,
             DROP `version_tarmed`;"
        );

        $this->addQuery('DROP TABLE `journal_envoi_xml`;');

        $this->addQuery('ALTER TABLE `facture_relance` DROP `envoi_xml`;');

        $this->addQuery(
            "ALTER TABLE `retrocession`
             MODIFY `code_class` ENUM ('CActeCCAM','CActeNAGP') DEFAULT 'CActeCCAM';"
        );

        $this->makeRevision('0.89');

        $this->addQuery(
            "ALTER TABLE `facture_cabinet`
             MODIFY `ouverture` DATETIME;
                   ALTER TABLE `facture_etablissement`
             MODIFY `ouverture` DATETIME;"
        );

        $this->mod_version = '0.90';
    }
}
