<?php

/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CAppUI;
use Ox\Core\Import\CExternalDataSourceImport;

class CHospiDiagImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'hospi_diag';

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            null,
            null
        );
    }

    public function importDatabase(?array $types = []): bool
    {
        $ds = $this->setSource();

        if ($ds && !$ds->hasTable('hd_etablissement', false)) {
            $query = "CREATE TABLE `hd_etablissement` (
              `hd_etablissement_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `finess` VARCHAR (9),
              `raison_sociale` VARCHAR (80),
              `champ_pmsi` VARCHAR (3),
              `taa` VARCHAR (3),
              `cat` VARCHAR (3),
              `taille_mco` VARCHAR (2),
              `taille_m` VARCHAR (2),
              `taille_c` VARCHAR (2),
              `taille_o` VARCHAR (2),
              `ville` VARCHAR (50),
              `cp` VARCHAR (10),
              `adresse` TEXT
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_etablissement`
                ADD INDEX (`finess`),
                ADD INDEX (`raison_sociale`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd etab create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_activite', false)) {
            $query = "CREATE TABLE `hd_activite` (
              `hd_activite_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `zone_reg` VARCHAR(9),
              `pm_med_reg` FLOAT,
              `pm_chir_reg` FLOAT,
              `pm_obs_reg` FLOAT,
              `pm_chir_ambu_reg` FLOAT,
              `pm_hospi_cancer_reg` FLOAT,
              `pct_hospi_cancer` FLOAT,
              `pct_ghm` FLOAT,
              `pct_sejours_severite_3_4` FLOAT,
              `indice_enseignement` FLOAT,
              `indice_recherche` FLOAT,
              `pct_entree_prov_urgence` FLOAT,
              `taux_util_lits_med` FLOAT,
              `taux_util_lits_chir` FLOAT,
              `taux_util_lits_obs` FLOAT
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_activite`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd activite create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_activite_zone', false)) {
            $query = "CREATE TABLE `hd_activite_zone` (
              `hd_activite_zone_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `zone` VARCHAR (9) NOT NULL,
              `pm_med` FLOAT,
              `pm_chir` FLOAT,
              `pm_obs` FLOAT,
              `pm_chir_ambu` FLOAT,
              `pm_hospi_cancer` FLOAT,
              `pm_chimio` FLOAT
              ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_activite_zone`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`zone`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd activite zone create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_finance', false)) {
            $query = "CREATE TABLE `hd_finance` (
              `hd_finance_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `marge_brute` FLOAT,
              `caf` FLOAT,
              `caf_nette` FLOAT,
              `duree_dette` FLOAT,
              `inde_finance` FLOAT,
              `intensite_invest` FLOAT,
              `vetuste_equip` FLOAT,
              `vetuste_bat` FLOAT,
              `besoin_fonds_roulement` FLOAT,
              `fond_roulement_net` FLOAT,
              `creances_non_recouvrees` FLOAT,
              `dette_fournisseur` FLOAT
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_finance`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd finances create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_process', false)) {
            $query = "CREATE TABLE `hd_process` (
              `hd_process_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `ip_dms_med` FLOAT,
              `ip_dms_chir` FLOAT,
              `ip_dms_obs` FLOAT,
              `poids_cout_personnel_non_med` FLOAT,
              `poids_cout_personnel_med` FLOAT,
              `poids_cout_personnel_services_medico_technique` FLOAT,
              `pct_depenses_admin_logistique_technque` FLOAT,
              `nb_examens_bio_par_technicien` INT (7),
              `nb_icr_par_salle` INT (7),
              `taux_cesarienne` FLOAT,
              `taux_peridurale` FLOAT,
              `taux_chir_ambu` FLOAT,
              `taux_gestes_marqueurs_chir_ambu` FLOAT,
              `taux_utilisation_places_chir_ambu` FLOAT,
              `indice_facturation` FLOAT,
              `niveau_prerequis_hopital_numerique` FLOAT
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_process`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd process create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_qualite', false)) {
            $query = "CREATE TABLE `hd_qualite` (
              `hd_qualite_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `score_naso` VARCHAR (50),
              `conformite_dossier_patient` VARCHAR (50),
              `conformite_delais_envoie` VARCHAR (50),
              `depistage_nutri` VARCHAR (50),
              `tracabilite_eval_douleur` VARCHAR (50),
              `conformite_dossier_anesth` VARCHAR (50),
              `rcp_cancer` VARCHAR (50),
              `niveau_certif` VARCHAR (1),
              `pep_bloc_op` VARCHAR (1),
              `pep_urg` VARCHAR (1),
              `pep_med` VARCHAR (1)
            
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_qualite`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd qualite create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_resshum', false)) {
            $query = "CREATE TABLE `hd_resshum` (
              `hd_resshum_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `nb_accouchement_par_obs_sage_femme` INT (3),
              `nb_icr_anesth_par_anesth` FLOAT,
              `nb_icr_par_chir` FLOAT,
              `nb_ide_as_par_cadre` FLOAT,
              `nb_iade_par_anesth` FLOAT,
              `nb_sage_femme_par_obs` FLOAT,
              `taux_absenteisme_pnm` FLOAT,
              `turn_over_global` FLOAT,
              `interim_med` FLOAT
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_resshum`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd resshum create', UI_MSG_OK]);
        }

        if (!$ds->hasTable('hd_identite', false)) {
            $query = "CREATE TABLE `hd_identite` (
              `hd_identite_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `hd_etablissement_id` INT (11) NOT NULL,
              `annee` INT (4) NOT NULL,
              `nb_rsa_med` INT (11),
              `nb_rsa_chir` INT (11),
              `nb_rsa_obs` INT (11),
              `nb_rsa_med_ambu` INT (11),
              `nb_rsa_chir_ambu` INT (11),
              `nb_rsa_obs_ambu` INT (11),
              `nb_chimio` INT (11),
              `nb_radio` INT (11),
              `nb_hemo` INT (11),
              `nb_autre` INT (11),
              `nb_accouchement` INT (11),
              `nb_actes_chir` INT (11),
              `nb_atu` INT (11),
              `nb_actes_endo` INT (11),
              `nb_racine_ghm` INT (11),
              `nb_chir_sein` INT (11),
              `nb_cataracte` INT (11),
              `nb_arthro_genou` INT (11),
              `nb_hernie_enfant` INT (11),
              `nb_rtu_prostate` INT (11),
              `nb_hernie_adulte` INT (11),
              `group_chir_1_libelle` VARCHAR (80),
              `group_chir_1` INT (11),
              `group_chir_2_libelle` VARCHAR (80),
              `group_chir_2` INT (11),
              `group_chir_3_libelle` VARCHAR (80),
              `group_chir_3` INT (11),
              `group_chir_4_libelle` VARCHAR (80),
              `group_chir_4` INT (11),
              `group_chir_5_libelle` VARCHAR (80),
              `group_chir_5` INT (11),
              `group_med_1_libelle` VARCHAR (80),
              `group_med_1` INT (11),
              `group_med_2_libelle` VARCHAR (80),
              `group_med_2` INT (11),
              `group_med_3_libelle` VARCHAR (80),
              `group_med_3` INT (11),
              `group_med_4_libelle` VARCHAR (80),
              `group_med_4` INT (11),
              `group_med_5_libelle` VARCHAR (80),
              `group_med_5` INT (11),
              `nb_lits_med` INT (4),
              `nb_lits_soins_intensifs` INT (4),
              `nb_lits_surv_continue` INT (4),
              `nb_lits_reanimation` INT (4),
              `nb_places_med` INT (4),
              `nb_lits_chir` INT (4),
              `nb_places_chir` INT (4),
              `nb_lits_obs` INT (4),
              `nb_places_obs` INT (4),
              `taux_info_ima_bio_ana` INT (3),
              `taux_info_dpii` INT (3),
              `taux_info_presc` INT (3),
              `taux_info_agenda_patient` INT (3),
              `taux_info_med` INT (3),
              `nb_scanners` INT (11),
              `nb_irm` INT (11),
              `nb_tep_scan` INT (11),
              `nb_table_corona` INT (11),
              `nb_salles_radio_vasc` INT (11),
              `nb_salles_interv_chir` INT (11),
              `niveau_mater` INT (11),
              `nb_b` INT (11),
              `nb_examens` INT (11),
              `total_prod` INT (11),
              `prod_taa` INT (11),
              `prod_migac` INT (11),
              `prod_merri` INT (11),
              `prod_ac` INT (11),
              `prod_recette_daf` INT (11),
              `prod_recette_mco` INT (11),
              `total_charges` INT (11),
              `total_charges_mco` INT (11),
              `resultat_consolide` INT (11),
              `resultat_net` INT (11),
              `resultat_consolide_budget_principal` INT (11),
              `caf` INT (11),
              `total_bilan` INT (11),
              `encours_dette` INT (11),
              `fond_roulement_net_global` INT (11),
              `fond_roulement_besoin` INT (11),
              `tresorerie` INT (11),
              `coeff_transition` FLOAT,
              `nb_etp_medicaux` FLOAT,
              `nb_etp_medicaux_med` FLOAT,
              `nb_etp_medicaux_chir` FLOAT,
              `nb_etp_medicaux_anesth` FLOAT,
              `nb_etp_medicaux_gyneco_obs` FLOAT,
              `nb_etp_non_med` FLOAT,
              `nb_etp_non_med_direction_administratif` FLOAT,
              `nb_etp_non_med_educatifs_sociaux` FLOAT,
              `nb_etp_non_med_services_soins` FLOAT,
              `nb_etp_non_med_medico_technique` FLOAT,
              `nb_etp_non_med_techniques_ouvriers` FLOAT	
            ) /*! ENGINE=MyISAM */;";
            $ds->exec($query);

            $query = "ALTER TABLE `hd_identite`
                ADD INDEX (`hd_etablissement_id`),
                ADD INDEX (`annee`);";
            $ds->exec($query);

            $this->addMessage(['hospiDiag-msg-Hd identite create', UI_MSG_OK]);
        }
        return true;
    }
}
