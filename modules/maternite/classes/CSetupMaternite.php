<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupMaternite extends CSetup {

  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "maternite";

    $this->makeRevision("0.0");

    $query = "CREATE TABLE `naissance` (
      `naissance_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `operation_id` INT (11) UNSIGNED,
      `grossesse_id` INT (11) UNSIGNED,
      `sejour_enfant_id` INT (11) UNSIGNED NOT NULL,
      `hors_etab` ENUM ('0','1') DEFAULT '0',
      `heure` TIME NOT NULL,
      `rang` INT (11) UNSIGNED NOT NULL
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `naissance`
      ADD INDEX (`operation_id`),
      ADD INDEX (`grossesse_id`),
      ADD INDEX (`sejour_enfant_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `grossesse` (
      `grosssesse_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `parturiente_id` INT (11) UNSIGNED NOT NULL,
      `terme_prevu` DATE NOT NULL
    ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.01");

    $query = "ALTER TABLE `grossesse`
      ADD `active` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.02");

    $query = "ALTER TABLE `naissance`
      CHANGE `heure` `heure` TIME,
      CHANGE `rang` `rang` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `grossesse`
      ADD `date_dernieres_regles` DATE;";
    $this->addQuery($query);

    $this->makeRevision("0.03");

    $query = "ALTER TABLE `grossesse`
      CHANGE `grosssesse_id` `grossesse_id` INT (11) UNSIGNED";
    $this->addQuery($query);

    $this->makeRevision("0.04");

    $query = "ALTER TABLE `grossesse`
      CHANGE `grossesse_id` `grossesse_id` INT (11) UNSIGNED NOT NULL auto_increment";
    $this->addQuery($query);

    $this->makeRevision("0.05");

    $query = "ALTER TABLE `naissance`
      ADD `sejour_maman_id` INT (11) UNSIGNED NOT NULL,
      ADD INDEX (`sejour_maman_id`)";
    $this->addQuery($query);

    $this->makeRevision("0.06");

    $query = "ALTER TABLE `grossesse` 
      ADD `multiple` ENUM ('0','1') DEFAULT '0',
      ADD INDEX (`parturiente_id`),
      ADD INDEX (`terme_prevu`),
      ADD INDEX (`date_dernieres_regles`);";
    $this->addQuery($query);

    $this->makeRevision("0.07");

    $query = "ALTER TABLE `grossesse` 
      ADD `allaitement_maternel` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.08");

    $query = "ALTER TABLE `grossesse` 
      ADD `date_fin_allaitement` DATE;";
    $this->addQuery($query);

    $this->makeRevision("0.09");

    $query = "ALTER TABLE `naissance`
      ADD `num_naissance` INT (11) UNSIGNED,
      ADD `lieu_accouchement` ENUM ('sur_site','exte') DEFAULT 'sur_site',
      ADD `fausse_couche` ENUM ('inf_15','sup_15'),
      ADD `rques` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.10");

    $query = "ALTER TABLE `naissance`
      DROP `lieu_accouchement`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `grossesse`
      ADD `lieu_accouchement` ENUM ('sur_site','exte') DEFAULT 'sur_site',
      ADD `fausse_couche` ENUM ('inf_15','sup_15'),
      ADD `rques` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.11");

    $query = "ALTER TABLE `grossesse`
      ADD `group_id` INT (11) UNSIGNED AFTER `grossesse_id`,
      ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.12");

    $query = "ALTER TABLE `grossesse`
                ADD `datetime_debut_travail` DATETIME,
                ADD `datetime_accouchement` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("0.13");

    $query = "CREATE TABLE `allaitement` (
      `allaitement_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `patient_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      `grossesse_id` INT (11) UNSIGNED,
      `date_debut` DATETIME NOT NULL,
      `date_fin` DATETIME
    )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `allaitement`
      ADD INDEX (`patient_id`),
      ADD INDEX (`grossesse_id`),
      ADD INDEX (`date_debut`),
      ADD INDEX (`date_fin`);";
    $this->addQuery($query);

    $query = "INSERT INTO `allaitement`
      SELECT null, `parturiente_id`, `grossesse_id`, `terme_prevu`, `date_fin_allaitement`
      FROM `grossesse`
      WHERE `date_fin_allaitement` IS NOT NULL;
    ";
    $this->addQuery($query);


    $query = "ALTER TABLE `grossesse`
      DROP `date_fin_allaitement`;";
    $this->addQuery($query);

    $this->makeRevision("0.14");

    $query = "ALTER TABLE `naissance`
                ADD `date_time` DATETIME AFTER `heure`,
                ADD `by_caesarean` ENUM ('0','1') DEFAULT '0' NOT NULL;";
    $this->addQuery($query);

    $query = "ALTER TABLE `naissance` ADD INDEX (`date_time`)";
    $this->addQuery($query);

    if ($this->tableExists('sejour') && $this->tableExists('patients')) {
      $this->makeRevision("0.15");
      $query = "
      UPDATE naissance
      INNER JOIN sejour ON sejour.sejour_id = naissance.sejour_enfant_id
      INNER JOIN patients ON sejour.patient_id = patients.patient_id
      SET naissance.date_time = CONCAT(patients.naissance, ' ', naissance.heure)
      WHERE naissance.heure IS NOT NULL
        AND patients.naissance IS NOT NULL";
      $this->addQuery($query);
    }
    else {
      $this->makeEmptyRevision("0.15");
    }

    $this->makeRevision("0.16");

    $query = "ALTER TABLE `naissance` DROP `heure`";
    $this->addQuery($query);

    $this->makeRevision("0.17");

    $query = "ALTER TABLE `naissance`
      CHANGE `fausse_couche` `num_semaines` ENUM ('inf_15','15_22','sup_22_sup_500g','sup_15');";
    $this->addQuery($query);

    $query = "ALTER TABLE `naissance`
      ADD `interruption` ENUM ('fausse_couche','IMG','mort_in_utero');";
    $this->addQuery($query);

    $query = "ALTER TABLE `grossesse`
      CHANGE `fausse_couche` `num_semaines` ENUM ('inf_15','15_22','sup_22_sup_500g','sup_15');";
    $this->addQuery($query);

    $this->makeRevision("0.18");

    $query = "ALTER TABLE `grossesse`
                ADD `pere_id` INT (11) UNSIGNED,
                ADD `id_reseau` VARCHAR (255),
                ADD `nb_grossesses_ant` INT (11),
                ADD `nb_accouchements_ant` INT (11),
                CHANGE `allaitement_maternel` `allaitement_maternel` ENUM ('0','1') DEFAULT '0',
                ADD `date_debut_grossesse` DATE,
                ADD `determination_date_grossesse` ENUM ('ddr','ovu','echo','inc'),
                ADD `nb_embryons_debut_grossesse` INT (11),
                ADD `type_embryons_debut_grossesse` ENUM ('mm','mb','bb'),
                ADD `rques_embryons_debut_grossesse` TEXT,
                ADD `rang` INT (11) UNSIGNED,
                ADD INDEX (`pere_id`),
                ADD INDEX (`date_debut_grossesse`),
                ADD INDEX (`datetime_debut_travail`),
                ADD INDEX (`datetime_accouchement`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `dossier_perinat` (
    `dossier_perinat_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `grossesse_id` INT (11) UNSIGNED NOT NULL,
                `activite_pro` ENUM ('a','c','f','cp','e','i','r'),
                `activite_pro_pere` ENUM ('a','c','f','cp','e','i','r'),
                `fatigue_travail` ENUM ('0','1'),
                `travail_hebdo` INT (11) UNSIGNED,
                `transport_jour` INT (11) UNSIGNED,
                `rques_social` TEXT,
                `enfants_foyer` INT (11) UNSIGNED,
                `situation_part_enfance` ENUM ('0','1'),
                `spe_perte_parent` ENUM ('0','1'),
                `spe_maltraitance` ENUM ('0','1'),
                `spe_mere_placee_enfance` ENUM ('0','1'),
                `situation_part_adolescence` ENUM ('0','1'),
                `spa_anorexie_boulimie` ENUM ('0','1'),
                `spa_depression` ENUM ('0','1'),
                `situation_part_familiale` ENUM ('0','1'),
                `spf_violences_conjugales` ENUM ('0','1'),
                `spf_mere_isolee` ENUM ('0','1'),
                `spf_absence_entourage_fam` ENUM ('0','1'),
                `stress_agression` ENUM ('0','1'),
                `sa_agression_physique` ENUM ('0','1'),
                `sa_agression_sexuelle` ENUM ('0','1'),
                `sa_harcelement_travail` ENUM ('0','1'),
                `rques_psychologie` TEXT,
                `situation_accompagnement` ENUM ('n','s','p','sp'),
                `rques_accompagnement` TEXT,
                `tabac_avant_grossesse` ENUM ('0','1'),
                `qte_tabac_avant_grossesse` INT (11) UNSIGNED,
                `tabac_debut_grossesse` ENUM ('0','1'),
                `qte_tabac_debut_grossesse` INT (11) UNSIGNED,
                `alcool_debut_grossesse` ENUM ('0','1'),
                `qte_alcool_debut_grossesse` INT (11) UNSIGNED,
                `canabis_debut_grossesse` ENUM ('0','1'),
                `qte_canabis_debut_grossesse` INT (11) UNSIGNED,
                `subst_avant_grossesse` ENUM ('0','1'),
                `mode_subst_avant_grossesse` ENUM ('iv','po','au'),
                `nom_subst_avant_grossesse` VARCHAR (255),
                `subst_subst_avant_grossesse` VARCHAR (255),
                `subst_debut_grossesse` ENUM ('0','1'),
                `tabac_pere` ENUM ('0','1'),
                `coexp_pere` INT (11),
                `alcool_pere` ENUM ('0','1'),
                `toxico_pere` ENUM ('0','1'),
                `rques_toxico` TEXT,
                `souhait_grossesse` ENUM ('0','1'),
                `contraception_pre_grossesse` ENUM ('none','pilu','ster','pres','impl','prog','avag','autre'),
                `grossesse_sous_contraception` ENUM ('none','ster','pilu','autre'),
                `rques_contraception` TEXT,
                `grossesse_apres_traitement` ENUM ('0','1'),
                `type_traitement_grossesse` ENUM ('ind','fiv','iad','iac','icsi','autre'),
                `origine_ovule` VARCHAR (255),
                `origine_sperme` VARCHAR (255),
                `rques_traitement_grossesse` TEXT,
                `traitement_peri_conceptionnelle` ENUM ('0','1'),
                `type_traitement_peri_conceptionnelle` VARCHAR (255),
                `arret_traitement_peri_conceptionnelle` TINYINT (4),
                `rques_traitement_peri_conceptionnelle` TEXT,
                `date_premier_contact` DATETIME,
                `consultant_premier_contact_id` INT (11) UNSIGNED,
                `provenance_premier_contact` ENUM ('pat','med','tra','aut'),
                `mater_provenance_premier_contact_id` INT (11) UNSIGNED,
                `nivsoins_provenance_premier_contact` ENUM ('1','2','3'),
                `motif_premier_contact` ENUM ('survrout','survspec','consulturg','hospi','acc','autre'),
                `nb_consult_ant_premier_contact` INT (11),
                `sa_consult_ant_premier_contact` INT (11),
                `surveillance_ant_premier_contact` VARCHAR (255),
                `type_surv_ant_premier_contact` ENUM ('generaliste','gynecomater','gynecoautre','pmi','sagefemme','autre'),
                `date_declaration_grossesse` DATE,
                `rques_provenance` TEXT,
                `reco_aucune` ENUM ('0','1'),
                `reco_tabac` ENUM ('0','1'),
                `reco_rhesus_negatif` ENUM ('0','1'),
                `reco_toxoplasmose` ENUM ('0','1'),
                `reco_alcool` ENUM ('0','1'),
                `reco_vaccination` ENUM ('0','1'),
                `reco_hygiene_alim` ENUM ('0','1'),
                `reco_toxicomanie` ENUM ('0','1'),
                `reco_autre` VARCHAR (255),
                `souhait_arret_addiction` ENUM ('0','1'),
                `souhait_aide_addiction` ENUM ('0','1'),
                `info_echographie` ENUM ('0','1'),
                `info_despistage_triso21` ENUM ('0','1'),
                `test_triso21_propose` ENUM ('n','a','r'),
                `info_orga_maternite` ENUM ('0','1'),
                `info_orga_reseau` ENUM ('0','1'),
                `projet_lieu_accouchement` VARCHAR (255),
                `projet_analgesie_peridurale` ENUM ('o','n','s'),
                `projet_allaitement_maternel` ENUM ('o','n','s'),
                `projet_preparation_naissance` VARCHAR (255),
                `projet_entretiens_proposes` ENUM ('0','1'),
                `bas_risques` ENUM ('0','1'),
                `risque_atcd_maternel_med` ENUM ('0','1'),
                `risque_atcd_obst` ENUM ('0','1'),
                `risque_atcd_familiaux` ENUM ('0','1'),
                `risque_patho_mater_grossesse` ENUM ('0','1'),
                `risque_patho_foetale_grossesse` ENUM ('0','1'),
                `risque_psychosocial_grossesse` ENUM ('0','1'),
                `risque_grossesse_multiple` ENUM ('0','1'),
                `type_surveillance` ENUM ('routine','spec','antenat','autre'),
                `lieu_surveillance` ENUM ('mater','amater','ville','sfdom','had'),
                `lieu_accouchement_prevu` VARCHAR (255),
                `niveau_soins_prevu` ENUM ('1','2','3'),
                `conclusion_premier_contact` TEXT
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`grossesse_id`),
                ADD INDEX (`date_premier_contact`),
                ADD INDEX (`consultant_premier_contact_id`),
                ADD INDEX (`mater_provenance_premier_contact_id`),
                ADD INDEX (`date_declaration_grossesse`);";
    $this->addQuery($query);

    $this->makeRevision("0.19");

    $query = "CREATE TABLE `depistage_grossesse` (
                `depistage_grossesse_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `grossesse_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `date` DATE NOT NULL,
                `groupe_sanguin` ENUM ('a','b','ab','o'),
                `rhesus` ENUM ('pos','neg'),
                `rai` ENUM ('neg','pos','nf'),
                `rubeole` ENUM ('nim','im','in'),
                `toxoplasmose` ENUM ('nim','im','in'),
                `syphilis` ENUM ('neg','pos','in'),
                `vih` ENUM ('neg','pos','in'),
                `hepatite_b` ENUM ('neg','pos','in'),
                `hepatite_c` ENUM ('neg','pos','in'),
                `cmvg` ENUM ('neg','pos','in'),
                `cmvm` ENUM ('neg','pos','in'),
                `htlv` ENUM ('neg','pos'),
                `marqueurs_seriques_t21` VARCHAR (255),
                `depistage_diabete` VARCHAR (255),
                `pv` VARCHAR (255),
                `nfs_hb` FLOAT UNSIGNED,
                `nfs_plaquettes` MEDIUMINT (4) UNSIGNED ZEROFILL,
                `tp` FLOAT UNSIGNED,
                `tca` TINYINT (2) UNSIGNED ZEROFILL,
                `tca_temoin` TINYINT (2) UNSIGNED ZEROFILL,
                `electro_hemoglobine_a1` FLOAT UNSIGNED,
                `electro_hemoglobine_a2` FLOAT UNSIGNED,
                `electro_hemoglobine_s` FLOAT UNSIGNED,
                `co_expire` TINYINT (4) UNSIGNED
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `depistage_grossesse` 
                ADD INDEX (`grossesse_id`),
                ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.20");

    $query = "CREATE TABLE `suivi_grossesse` (
                `suivi_grossesse_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `consultation_id` INT (11) UNSIGNED NOT NULL,
                `evenements_anterieurs` TEXT,
                `metrorragies` ENUM ('0','1'),
                `leucorrhees` ENUM ('0','1'),
                `contractions_anormales` ENUM ('0','1'),
                `mouvements_foetaux` ENUM ('0','1'),
                `troubles_digestifs` ENUM ('0','1'),
                `troubles_urinaires` ENUM ('0','1'),
                `autres_anomalies` TEXT,
                `mouvements_actifs` ENUM ('percu','npercu'),
                `auscultation_cardio_pulm` ENUM ('normal','anomalie'),
                `examen_seins` ENUM ('normal','mamomb','autre'),
                `circulation_veineuse` ENUM ('normal','insmod','inssev'),
                `oedeme_membres_inf` ENUM ('0','1'),
                `rques_examen_general` TEXT,
                `bruit_du_coeur` ENUM ('percu','npercu'),
                `col_normal` ENUM ('o','n'),
                `longueur_col` ENUM ('long','milong','court','eff'),
                `position_col` ENUM ('post','inter','ant'),
                `dilatation_col` ENUM ('ferme', 'perm'),
                `consistance_col` ENUM ('ton','moy','mol'),
                `presentation_position` ENUM ('som','sie','tra','inc'),
                `presentation_etat` ENUM ('mob','amo','fix','eng'),
                `segment_inferieur` ENUM ('amp','namp'),
                `membranes` ENUM ('int','romp','susrupt'),
                `bassin` ENUM ('normal','anomalie'),
                `examen_genital` ENUM ('normal','anomalie'),
                `rques_exam_gyneco_obst` TEXT,
                `frottis` ENUM ('fait','nfait'),
                `echographie` ENUM ('fait','nfait'),
                `prelevement_bacterio` ENUM ('fait','nfait'),
                `autre_exam_comp` TEXT,
                `jours_arret_travail` INT (11)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `suivi_grossesse` 
                ADD INDEX (`consultation_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.21");

    $query = "CREATE TABLE `surv_echo_grossesse` (
                `surv_echo_grossesse_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `grossesse_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `date` DATE NOT NULL,
                `lcc` FLOAT UNSIGNED,
                `bip` FLOAT UNSIGNED,
                `pc` FLOAT UNSIGNED,
                `dat` FLOAT UNSIGNED,
                `pa` FLOAT UNSIGNED,
                `lf` FLOAT UNSIGNED,
                `lp` FLOAT UNSIGNED,
                `dfo` FLOAT UNSIGNED,
                `cn` ENUM ('0','1'),
                `opn` ENUM ('0','1'),
                `morphologie` ENUM ('0','1'),
                `remarques` TEXT
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `surv_echo_grossesse` 
                ADD INDEX (`grossesse_id`),
                ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.22");

    $query = "ALTER TABLE `dossier_perinat` 
                ADD `transf_antenat` ENUM ('n','reseau','hreseau','imp'),
                ADD `date_transf_antenat` DATE,
                ADD `lieu_transf_antenat` ENUM ('amater','rea','autre'),
                ADD `etab_transf_antenat` VARCHAR (255),
                ADD `nivsoins_transf_antenat` ENUM ('1','2','3'),
                ADD `raison_transf_antenat_hors_reseau` ENUM ('place','choixmed','choixpat'),
                ADD `raison_imp_transf_antenat` ENUM ('place','patho','dilat','refuspat'),
                ADD `motif_tranf_antenat` ENUM ('pathfoet','pathmat'),
                ADD `type_patho_transf_antenat` ENUM ('map','rpm','vasc','mult','rciusf','malf','autre'),
                ADD `rques_transf_antenat` TEXT,
                ADD `mode_transp_transf_antenat` ENUM ('perso','samu','ambu','autre'),
                ADD `antibio_transf_antenat` ENUM ('0','1'),
                ADD `nom_antibio_transf_antenat` VARCHAR (255),
                ADD `cortico_transf_antenat` ENUM ('0','1'),
                ADD `nom_cortico_transf_antenat` VARCHAR (255),
                ADD `datetime_cortico_transf_antenat` DATETIME,
                ADD `tocolytiques_transf_antenat` ENUM ('0','1'),
                ADD `nom_tocolytiques_transf_antenat` VARCHAR (255),
                ADD `antihta_transf_antenat` ENUM ('0','1'),
                ADD `nom_antihta_transf_antenat` VARCHAR (255),
                ADD `autre_ttt_transf_antenat` ENUM ('0','1'),
                ADD `nom_autre_ttt_transf_antenat` VARCHAR (255),
                ADD `retour_mater_transf_antenat` ENUM ('n','consult','hospi','acc','postacc'),
                ADD `date_retour_transf_antenat` DATE,
                ADD `devenir_retour_transf_antenat` ENUM ('acc','transf'),
                ADD `rques_retour_transf_antenat` TEXT;";
    $this->addQuery($query);

    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`date_transf_antenat`),
                ADD INDEX (`datetime_cortico_transf_antenat`),
                ADD INDEX (`date_retour_transf_antenat`);";
    $this->addQuery($query);

    $this->makeRevision("0.23");

    $query = "ALTER TABLE `dossier_perinat`
                ADD `type_terminaison_grossesse` ENUM ('termhetab','term22','avsp'),
                ADD `type_term_hors_etab` ENUM ('vivant','mortiu','img'),
                ADD `type_term_inf_22sa` ENUM ('avsp','ivg','geu','mole','img');";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `patho_ant` ENUM ('0','1'),
                ADD `patho_ant_hta` ENUM ('0','1'),
                ADD `patho_ant_diabete` ENUM ('0','1'),
                ADD `patho_ant_epilepsie` ENUM ('0','1'),
                ADD `patho_ant_asthme` ENUM ('0','1'),
                ADD `patho_ant_pulm` ENUM ('0','1'),
                ADD `patho_ant_thrombo_emb` ENUM ('0','1'),
                ADD `patho_ant_cardio` ENUM ('0','1'),
                ADD `patho_ant_auto_immune` ENUM ('0','1'),
                ADD `patho_ant_hepato_dig` ENUM ('0','1'),
                ADD `patho_ant_thyroide` ENUM ('0','1'),
                ADD `patho_ant_uro_nephro` ENUM ('0','1'),
                ADD `patho_ant_infectieuse` ENUM ('0','1'),
                ADD `patho_ant_hemato` ENUM ('0','1'),
                ADD `patho_ant_cancer_non_gyn` ENUM ('0','1'),
                ADD `patho_ant_psy` ENUM ('0','1'),
                ADD `patho_ant_autre` TEXT,
                ADD `chir_ant` ENUM ('0','1'),
                ADD `chir_ant_rques` TEXT,
                ADD `gyneco_ant_regles` INT (11),
                ADD `gyneco_ant_regul_regles` ENUM ('regul','irregul'),
                ADD `gyneco_ant_fcv` DATE,
                ADD `gyneco_ant` ENUM ('0','1'),
                ADD `gyneco_ant_herpes` ENUM ('0','1'),
                ADD `gyneco_ant_lesion_col` ENUM ('0','1'),
                ADD `gyneco_ant_conisation` ENUM ('0','1'),
                ADD `gyneco_ant_cicatrice_uterus` ENUM ('0','1'),
                ADD `gyneco_ant_fibrome` ENUM ('0','1'),
                ADD `gyneco_ant_stat_pelv` ENUM ('0','1'),
                ADD `gyneco_ant_cancer_sein` ENUM ('0','1'),
                ADD `gyneco_ant_cancer_app_genital` ENUM ('0','1'),
                ADD `gyneco_ant_malf_genitale` ENUM ('0','1'),
                ADD `gyneco_ant_condylomes` ENUM ('0','1'),
                ADD `gyneco_ant_distilbene` ENUM ('0','1'),
                ADD `gyneco_ant_autre` TEXT,
                ADD `gyneco_ant_infert` ENUM ('0','1'),
                ADD `gyneco_ant_infert_origine` ENUM ('anov','uterine','cervic','idiopath','tubaire','fem','masc','femmasc')";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `pere_serologie_vih` ENUM ('neg','pos','inc') DEFAULT 'inc',
                ADD `pere_electrophorese_hb` VARCHAR (255),
                ADD `pere_patho_ant` ENUM ('0','1'),
                ADD `pere_ant_herpes` ENUM ('0','1'),
                ADD `pere_ant_autre` TEXT;";
    $this->addQuery($query);

    $this->makeRevision('0.26');
    $query = "ALTER TABLE `dossier_perinat`
                ADD `ant_mater_constantes_id` INT (11) UNSIGNED,
                ADD `pere_constantes_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `ant_fam` ENUM ('0','1'),
                ADD `consanguinite` ENUM ('0','1'),
                ADD `ant_fam_mere_gemellite` ENUM ('0','1'),
                ADD `ant_fam_pere_gemellite` ENUM ('0','1'),
                ADD `ant_fam_mere_malformations` ENUM ('0','1'),
                ADD `ant_fam_pere_malformations` ENUM ('0','1'),
                ADD `ant_fam_mere_maladie_genique` ENUM ('0','1'),
                ADD `ant_fam_pere_maladie_genique` ENUM ('0','1'),
                ADD `ant_fam_mere_maladie_chrom` ENUM ('0','1'),
                ADD `ant_fam_pere_maladie_chrom` ENUM ('0','1'),
                ADD `ant_fam_mere_diabete` ENUM ('0','1'),
                ADD `ant_fam_pere_diabete` ENUM ('0','1'),
                ADD `ant_fam_mere_hta` ENUM ('0','1'),
                ADD `ant_fam_pere_hta` ENUM ('0','1'),
                ADD `ant_fam_mere_phlebite` ENUM ('0','1'),
                ADD `ant_fam_pere_phlebite` ENUM ('0','1'),
                ADD `ant_fam_mere_autre` TEXT,
                ADD `ant_fam_pere_autre` TEXT;";
    $this->addQuery($query);

    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`ant_mater_constantes_id`),
                ADD INDEX (`pere_constantes_id`),
                ADD INDEX (`gyneco_ant_fcv`);";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `ant_obst_nb_gr_acc` TINYINT (4),
                ADD `ant_obst_nb_gr_av_sp` TINYINT (4),
                ADD `ant_obst_nb_gr_ivg` TINYINT (4),
                ADD `ant_obst_nb_gr_geu` TINYINT (4),
                ADD `ant_obst_nb_gr_mole` TINYINT (4),
                ADD `ant_obst_nb_gr_img` TINYINT (4),
                ADD `ant_obst_nb_gr_amp` TINYINT (4),
                ADD `ant_obst_nb_gr_mult` TINYINT (4),
                ADD `ant_obst_nb_gr_hta` TINYINT (4),
                ADD `ant_obst_nb_gr_map` TINYINT (4),
                ADD `ant_obst_nb_gr_diab` TINYINT (4),
                ADD `ant_obst_nb_gr_cesar` TINYINT (4),
                ADD `ant_obst_nb_gr_prema` TINYINT (4),
                ADD `ant_obst_nb_enf_moins_25000` TINYINT (4),
                ADD `ant_obst_nb_enf_hypotroph` TINYINT (4),
                ADD `ant_obst_nb_enf_macrosome` TINYINT (4),
                ADD `ant_obst_nb_enf_morts_nes` TINYINT (4),
                ADD `ant_obst_nb_enf_mort_neonat` TINYINT (4),
                ADD `ant_obst_nb_enf_mort_postneonat` TINYINT (4),
                ADD `ant_obst_nb_enf_malform` TINYINT (4);";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `date_validation_synthese` DATE,
                ADD `validateur_synthese_id` INT (11) UNSIGNED,
                ADD `nb_consult_total_prenatal` INT (11),
                ADD `nb_consult_total_equipe` INT (11),
                ADD `entretien_prem_trim` ENUM ('0','1'),
                ADD `hospitalisation` ENUM ('non','mater','had','autre'),
                ADD `nb_sejours` INT (11),
                ADD `nb_total_jours_hospi` INT (11),
                ADD `sage_femme_domicile` ENUM ('0','1'),
                ADD `transfert_in_utero` ENUM ('0','1'),
                ADD `consult_preanesth` ENUM ('0','1'),
                ADD `consult_centre_diag_prenat` ENUM ('non','etab','horsetab'),
                ADD `preparation_naissance` ENUM ('non','int','ext','intext'),
                ADD `conso_toxique_pdt_grossesse` ENUM ('0','1'),
                ADD `tabac_pdt_grossesse` INT (11),
                ADD `sevrage_tabac_pdt_grossesse` ENUM ('0','1'),
                ADD `date_arret_tabac` DATE,
                ADD `alcool_pdt_grossesse` INT (11),
                ADD `cannabis_pdt_grossesse` INT (11),
                ADD `autres_subst_pdt_grossesse` ENUM ('0','1'),
                ADD `type_subst_pdt_grossesse` TEXT,
                ADD `profession_pdt_grossesse` VARCHAR (255),
                ADD `ag_date_arret_travail` INT (11),
                ADD `situation_pb_pdt_grossesse` ENUM ('0','1'),
                ADD `separation_pdt_grossesse` ENUM ('0','1'),
                ADD `deces_fam_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_evenement_fam_pdt_grossesse` VARCHAR (255),
                ADD `perte_emploi_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_evenement_soc_pdt_grossesse` VARCHAR (255),
                ADD `nb_total_echographies` INT (11),
                ADD `echo_1er_trim` ENUM ('0','1'),
                ADD `resultat_echo_1er_trim` ENUM ('normal','anomorpho','corrterme','autre'),
                ADD `resultat_autre_echo_1er_trim` VARCHAR (255),
                ADD `ag_echo_1er_trim` INT (11),
                ADD `echo_2e_trim` ENUM ('0','1'),
                ADD `resultat_echo_2e_trim` ENUM ('normal','anomorpho','corrterme','autre'),
                ADD `resultat_autre_echo_2e_trim` VARCHAR (255),
                ADD `ag_echo_2e_trim` INT (11),
                ADD `doppler_2e_trim` ENUM ('0','1'),
                ADD `resultat_doppler_2e_trim` ENUM ('normal','anomalie'),
                ADD `echo_3e_trim` ENUM ('0','1'),
                ADD `resultat_echo_3e_trim` ENUM ('normal','anomorpho','corrterme','autre'),
                ADD `resultat_autre_echo_3e_trim` VARCHAR (255),
                ADD `ag_echo_3e_trim` INT (11),
                ADD `doppler_3e_trim` ENUM ('0','1'),
                ADD `resultat_doppler_3e_trim` ENUM ('normal','anomalie'),
                ADD `prelevements_foetaux` ENUM ('nonprescrits','refuses','faits'),
                ADD `indication_prelevements_foetaux` ENUM ('agemater','nuque','t21','appelecho','atcd','conv'),
                ADD `biopsie_trophoblaste` ENUM ('0','1'),
                ADD `resultat_biopsie_trophoblaste` ENUM ('normal','anomalie'),
                ADD `rques_biopsie_trophoblaste` VARCHAR (255),
                ADD `ag_biopsie_trophoblaste` INT (11),
                ADD `amniocentese` ENUM ('0','1'),
                ADD `resultat_amniocentese` ENUM ('normal','anomalie'),
                ADD `rques_amniocentese` VARCHAR (255),
                ADD `ag_amniocentese` INT (11),
                ADD `cordocentese` ENUM ('0','1'),
                ADD `resultat_cordocentese` ENUM ('normal','anomalie'),
                ADD `rques_cordocentese` VARCHAR (255),
                ADD `ag_cordocentese` INT (11),
                ADD `autre_prelevements_foetaux` ENUM ('0','1'),
                ADD `rques_autre_prelevements_foetaux` VARCHAR (255),
                ADD `ag_autre_prelevements_foetaux` INT (11),
                ADD `prelevements_bacterio_mater` ENUM ('nonprescrits','refuses','faits'),
                ADD `prelevement_vaginal` ENUM ('0','1'),
                ADD `resultat_prelevement_vaginal` ENUM ('negatif','streptob','autre'),
                ADD `rques_prelevement_vaginal` VARCHAR (255),
                ADD `ag_prelevement_vaginal` INT (11),
                ADD `prelevement_urinaire` ENUM ('0','1'),
                ADD `resultat_prelevement_urinaire` ENUM ('negatif','streptob','autre'),
                ADD `rques_prelevement_urinaire` VARCHAR (255),
                ADD `ag_prelevement_urinaire` INT (11),
                ADD `marqueurs_seriques` ENUM ('nonprescrits','refuses','faits1trim','faits2trim'),
                ADD `resultats_marqueurs_seriques` ENUM ('bas','t21','anfermtn','autre'),
                ADD `rques_marqueurs_seriques` VARCHAR (255),
                ADD `depistage_diabete` ENUM ('nonprescrit','refuse','fait'),
                ADD `resultat_depistage_diabete` ENUM ('normal','anormal'),
                ADD `rai_fin_grossesse` ENUM ('neg','pos','inc'),
                ADD `rubeole_fin_grossesse` ENUM ('nonimmu','immu','inc'),
                ADD `seroconv_rubeole` ENUM ('0','1'),
                ADD `ag_seroconv_rubeole` INT (11),
                ADD `toxoplasmose_fin_grossesse` ENUM ('nonimmu','immu','inc'),
                ADD `seroconv_toxoplasmose` ENUM ('0','1'),
                ADD `ag_seroconv_toxoplasmose` INT (11),
                ADD `syphilis_fin_grossesse` ENUM ('neg','pos','inc'),
                ADD `vih_fin_grossesse` ENUM ('neg','pos','inc'),
                ADD `hepatite_b_fin_grossesse` ENUM ('aghbsm','aghbsp','achbsp','inc'),
                ADD `hepatite_b_aghbspos_fin_grossesse` ENUM ('aghbcp','achbcp','aghbep','achbep'),
                ADD `hepatite_c_fin_grossesse` ENUM ('neg','acvhcp','inc'),
                ADD `cmvg_fin_grossesse` ENUM ('pos','neg','inc'),
                ADD `cmvm_fin_grossesse` ENUM ('pos','neg','inc'),
                ADD `seroconv_cmv` ENUM ('0','1'),
                ADD `ag_seroconv_cmv` INT (11),
                ADD `autre_serodiag_fin_grossesse` TEXT,
                ADD `pathologie_grossesse` ENUM ('0','1'),
                ADD `pathologie_grossesse_maternelle` ENUM ('0','1'),
                ADD `metrorragie_1er_trim` ENUM ('0','1'),
                ADD `type_metrorragie_1er_trim` ENUM ('menavort','autre'),
                ADD `ag_metrorragie_1er_trim` INT (11),
                ADD `metrorragie_2e_3e_trim` ENUM ('0','1'),
                ADD `type_metrorragie_2e_3e_trim` ENUM ('placpraehemo','hrpavectrcoag','hrpsanstrcoag','hemoavectrcoag','hemosanstrcoag'),
                ADD `ag_metrorragie_2e_3e_trim` INT (11),
                ADD `menace_acc_premat` ENUM ('0','1'),
                ADD `menace_acc_premat_modif_cerv` ENUM ('0','1'),
                ADD `pec_menace_acc_premat` ENUM ('ttrepos','ttmedic','hospi'),
                ADD `ag_menace_acc_premat` INT (11),
                ADD `ag_hospi_menace_acc_premat` INT (11),
                ADD `rupture_premat_membranes` ENUM ('0','1'),
                ADD `ag_rupture_premat_membranes` INT (11),
                ADD `anomalie_liquide_amniotique` ENUM ('0','1'),
                ADD `type_anomalie_liquide_amniotique` ENUM ('exces','oligoamnios'),
                ADD `ag_anomalie_liquide_amniotique` INT (11),
                ADD `autre_patho_gravidique` ENUM ('0','1'),
                ADD `patho_grav_vomissements` ENUM ('0','1'),
                ADD `patho_grav_herpes_gest` ENUM ('0','1'),
                ADD `patho_grav_dermatose_pup` ENUM ('0','1'),
                ADD `patho_grav_placenta_praevia_non_hemo` ENUM ('0','1'),
                ADD `patho_grav_chorio_amniotite` ENUM ('0','1'),
                ADD `patho_grav_transf_foeto_mat` ENUM ('0','1'),
                ADD `patho_grav_beance_col` ENUM ('0','1'),
                ADD `patho_grav_cerclage` ENUM ('0','1'),
                ADD `ag_autre_patho_gravidique` INT (11),
                ADD `hypertension_arterielle` ENUM ('0','1'),
                ADD `type_hypertension_arterielle` ENUM ('htachro','htagrav','hellp','preecmod','preechta','preecsev','ecl'),
                ADD `ag_hypertension_arterielle` INT (11),
                ADD `proteinurie` ENUM ('0','1'),
                ADD `type_proteinurie` ENUM ('sansoed','avecoed','oedgen'),
                ADD `ag_proteinurie` INT (11),
                ADD `diabete` ENUM ('0','1'),
                ADD `type_diabete` ENUM ('gestid','gestnid','preexid','preexnid','sansprec'),
                ADD `ag_diabete` INT (11),
                ADD `infection_urinaire` ENUM ('0','1'),
                ADD `type_infection_urinaire` ENUM ('basse','pyelo','nonprec'),
                ADD `ag_infection_urinaire` INT (11),
                ADD `infection_cervico_vaginale` ENUM ('0','1'),
                ADD `type_infection_cervico_vaginale` ENUM ('streptob','autre'),
                ADD `autre_infection_cervico_vaginale` VARCHAR (255),
                ADD `ag_infection_cervico_vaginale` INT (11),
                ADD `autre_patho_maternelle` ENUM ('0','1'),
                ADD `ag_autre_patho_maternelle` INT (11),
                ADD `anemie_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `tombopenie_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_hemato_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_hemato_mat_pdt_grossesse` VARCHAR (255),
                ADD `faible_prise_poid_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `malnut_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_endo_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_endo_mat_pdt_grossesse` VARCHAR (255),
                ADD `cholestase_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `steatose_hep_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_hepato_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_hepato_mat_pdt_grossesse` VARCHAR (255),
                ADD `thrombophl_sup_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `thrombophl_prof_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_vein_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_vein_mat_pdt_grossesse` VARCHAR (255),
                ADD `asthme_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_resp_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_resp_mat_pdt_grossesse` VARCHAR (255),
                ADD `cardiopathie_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_cardio_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_cardio_mat_pdt_grossesse` VARCHAR (255),
                ADD `epilepsie_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `depression_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_patho_neuropsy_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_patho_neuropsy_mat_pdt_grossesse` VARCHAR (255),
                ADD `patho_gyneco_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_patho_gyneco_mat_pdt_grossesse` VARCHAR (255),
                ADD `mst_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_mst_mat_pdt_grossesse` VARCHAR (255),
                ADD `synd_douleur_abdo_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_synd_douleur_abdo_mat_pdt_grossesse` VARCHAR (255),
                ADD `synd_infect_mat_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_synd_infect_mat_pdt_grossesse` VARCHAR (255),
                ADD `therapeutique_grossesse_maternelle` ENUM ('0','1'),
                ADD `antibio_pdt_grossesse` ENUM ('0','1'),
                ADD `type_antibio_pdt_grossesse` VARCHAR (255),
                ADD `tocolyt_pdt_grossesse` ENUM ('0','1'),
                ADD `mode_admin_tocolyt_pdt_grossesse` ENUM ('perf','peros'),
                ADD `cortico_pdt_grossesse` ENUM ('0','1'),
                ADD `nb_cures_cortico_pdt_grossesse` INT (11),
                ADD `etat_dern_cure_cortico_pdt_grossesse` ENUM ('comp','incomp'),
                ADD `gammaglob_anti_d_pdt_grossesse` ENUM ('0','1'),
                ADD `antihyp_pdt_grossesse` ENUM ('0','1'),
                ADD `aspirine_a_pdt_grossesse` ENUM ('0','1'),
                ADD `barbit_antiepilept_pdt_grossesse` ENUM ('0','1'),
                ADD `psychotropes_pdt_grossesse` ENUM ('0','1'),
                ADD `subst_nicotine_pdt_grossesse` ENUM ('0','1'),
                ADD `autre_therap_mater_pdt_grossesse` ENUM ('0','1'),
                ADD `desc_autre_therap_mater_pdt_grossesse` VARCHAR (255),
                ADD `patho_foetale_in_utero` ENUM ('0','1'),
                ADD `anomalie_croiss_intra_uterine` ENUM ('0','1'),
                ADD `type_anomalie_croiss_intra_uterine` ENUM ('retard','macrosomie'),
                ADD `ag_anomalie_croiss_intra_uterine` INT (11),
                ADD `signes_hypoxie_foetale_chronique` ENUM ('0','1'),
                ADD `ag_signes_hypoxie_foetale_chronique` INT (11),
                ADD `hypoxie_foetale_anomalie_doppler` ENUM ('0','1'),
                ADD `hypoxie_foetale_anomalie_rcf` ENUM ('0','1'),
                ADD `hypoxie_foetale_alter_profil_biophy` ENUM ('0','1'),
                ADD `anomalie_constit_foetus` ENUM ('0','1'),
                ADD `ag_anomalie_constit_foetus` INT (11),
                ADD `malformation_isolee_foetus` ENUM ('0','1'),
                ADD `anomalie_chromo_foetus` ENUM ('0','1'),
                ADD `synd_polymalform_foetus` ENUM ('0','1'),
                ADD `anomalie_genique_foetus` ENUM ('0','1'),
                ADD `rques_anomalies_foetus` TEXT,
                ADD `foetopathie_infect_acquise` ENUM ('0','1'),
                ADD `type_foetopathie_infect_acquise` ENUM ('cmv','toxo','rub','parvo','autre'),
                ADD `autre_foetopathie_infect_acquise` VARCHAR (255),
                ADD `ag_foetopathie_infect_acquise` INT (11),
                ADD `autre_patho_foetale` ENUM ('0','1'),
                ADD `ag_autre_patho_foetale` INT (11),
                ADD `allo_immun_anti_rh_foetale` ENUM ('0','1'),
                ADD `autre_allo_immun_foetale` ENUM ('0','1'),
                ADD `anas_foeto_plac_non_immun` ENUM ('0','1'),
                ADD `trouble_rcf_foetus` ENUM ('0','1'),
                ADD `foetopathie_alcoolique` ENUM ('0','1'),
                ADD `grosse_abdo_foetus_viable` ENUM ('0','1'),
                ADD `mort_foetale_in_utero_in_22sa` ENUM ('0','1'),
                ADD `autre_patho_foetale_autre` ENUM ('0','1'),
                ADD `desc_autre_patho_foetale_autre` VARCHAR (255),
                ADD `patho_foetale_gross_mult` ENUM ('0','1'),
                ADD `ag_patho_foetale_gross_mult` INT (11),
                ADD `avort_foetus_gross_mult` ENUM ('0','1'),
                ADD `mort_foetale_in_utero_gross_mutl` ENUM ('0','1'),
                ADD `synd_transf_transf_gross_mult` ENUM ('0','1'),
                ADD `therapeutique_foetale` ENUM ('0','1'),
                ADD `amnioinfusion` ENUM ('0','1'),
                ADD `chirurgie_foetale` ENUM ('0','1'),
                ADD `derivation_foetale` ENUM ('0','1'),
                ADD `tranfusion_foetale_in_utero` ENUM ('0','1'),
                ADD `ex_sanguino_transfusion_foetale` ENUM ('0','1'),
                ADD `autre_therapeutiques_foetales` ENUM ('0','1'),
                ADD `reduction_embryonnaire` ENUM ('0','1'),
                ADD `type_reduction_embryonnaire` ENUM ('reducinf13','reducsup13','select'),
                ADD `photocoag_vx_placentaires` ENUM ('0','1'),
                ADD `rques_therapeutique_foetale` TEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`date_validation_synthese`),
                ADD INDEX (`validateur_synthese_id`),
                ADD INDEX (`date_arret_tabac`),
                ADD INDEX (`profession_pdt_grossesse`);";
    $this->addQuery($query);

    $this->makeRevision("0.30");

    $query = "ALTER TABLE `dossier_perinat`
                ADD `presentation_fin_grossesse` ENUM ('ceph','siege','transv','autre'),
                ADD `autre_presentation_fin_grossesse` VARCHAR (255),
                ADD `version_presentation_manoeuvre_ext` ENUM ('nontente','tenteok','tenteko'),
                ADD `rques_presentation_fin_grossesse` TEXT,
                ADD `etat_uterus_fin_grossesse` ENUM ('norm','cicat','autreano'),
                ADD `autre_anomalie_uterus_fin_grossesse` VARCHAR (255),
                ADD `nb_cicatrices_uterus_fin_grossesse` INT (11),
                ADD `date_derniere_hysterotomie` DATE,
                ADD `rques_etat_uterus` TEXT,
                ADD `appreciation_clinique_etat_bassin` ENUM ('norm','anorm'),
                ADD `desc_appreciation_clinique_etat_bassin` VARCHAR (255),
                ADD `pelvimetrie` ENUM ('norm','anorm'),
                ADD `desc_pelvimetrie` VARCHAR (255),
                ADD `diametre_transverse_median` FLOAT,
                ADD `diametre_promonto_retro_pubien` FLOAT,
                ADD `diametre_bisciatique` FLOAT,
                ADD `indice_magnin` FLOAT,
                ADD `date_echo_fin_grossesse` DATE,
                ADD `sa_echo_fin_grossesse` INT (11),
                ADD `bip_fin_grossesse` INT (11),
                ADD `est_pond_fin_grossesse` INT (11),
                ADD `est_pond_2e_foetus_fin_grossesse` INT (11),
                ADD `conduite_a_tenir_acc` ENUM ('bassespon','bassedecl','cesar'),
                ADD `date_decision_conduite_a_tenir_acc` DATE,
                ADD `valid_decision_conduite_a_tenir_acc_id` INT (11) UNSIGNED,
                ADD `motif_conduite_a_tenir_acc` VARCHAR (255),
                ADD `date_prevue_interv` DATE,
                ADD `rques_conduite_a_tenir` TEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`date_derniere_hysterotomie`),
                ADD INDEX (`date_echo_fin_grossesse`),
                ADD INDEX (`date_decision_conduite_a_tenir_acc`),
                ADD INDEX (`valid_decision_conduite_a_tenir_acc_id`),
                ADD INDEX (`date_prevue_interv`);";
    $this->addQuery($query);

    $this->makeRevision("0.31");

    $query = "ALTER TABLE `dossier_perinat`
                ADD `admission_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.32");
    $query = "CREATE TABLE `examen_nouveau_ne` (
                `examen_nouveau_ne_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `grossesse_id` INT (11) UNSIGNED NOT NULL,
                `examinateur_id` INT (11) UNSIGNED NOT NULL,
                `naissance_id` INT (11) UNSIGNED NOT NULL,
                `date` DATE,
                `poids` INT (11),
                `taille` FLOAT,
                `pc` FLOAT,
                `bip` INT (11),
                `coloration_globale` TEXT,
                `revetement_cutane` TEXT,
                `etat_trophique` TEXT,
                `auscultation` TEXT,
                `pouls_femoraux` TEXT,
                `ta` TEXT,
                `crane` TEXT,
                `face_yeux` TEXT,
                `cavite_buccale` TEXT,
                `fontanelles` TEXT,
                `sutures` TEXT,
                `cou` TEXT,
                `foie` TEXT,
                `rate` TEXT,
                `reins` TEXT,
                `ombilic` TEXT,
                `orifices_herniaires` TEXT,
                `ligne_mediane_posterieure` TEXT,
                `region_sacree` TEXT,
                `anus` TEXT,
                `jet_mictionnel` TEXT,
                `clavicules` TEXT,
                `hanches` TEXT,
                `mains` TEXT,
                `pieds` TEXT,
                `cri` TEXT,
                `reactivite` TEXT,
                `tonus_axial` TEXT,
                `tonus_membres` TEXT,
                `reflexes_archaiques` TEXT,
                `test_audition` TEXT,
                `est_age_gest` INT (11),
                `dev_ponderal` ENUM ('hypo','eutro','hyper'),
                `croiss_ponderale` ENUM ('rest','norm','exces'),
                `croiss_staturale` ENUM ('rest','norm','exces')
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `examen_nouveau_ne` 
                ADD INDEX (`grossesse_id`),
                ADD INDEX (`naissance_id`),
                ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.33");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `adm_mater_constantes_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.34");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `adm_sage_femme_resp_id` INT (11) UNSIGNED,
                ADD `ag_admission` INT (11),
                ADD `ag_jours_admission` INT (11),
                ADD `motif_admission` ENUM ('travsp','travspmbint','travspmbromp','ruptmb','decl','cesar','urg','admpostacc'),
                ADD `ag_ruptures_membranes` INT (11),
                ADD `ag_jours_ruptures_membranes` INT (11),
                ADD `delai_rupture_travail_jours` INT (11),
                ADD `delai_rupture_travail_heures` INT (11),
                ADD `date_ruptures_membranes` DATETIME,
                ADD `rques_admission` TEXT,
                ADD `exam_entree_oedeme` VARCHAR (255),
                ADD `exam_entree_bruits_du_coeur` VARCHAR (255),
                ADD `exam_entree_mvt_actifs_percus` VARCHAR (255),
                ADD `exam_entree_contractions` VARCHAR (255),
                ADD `exam_entree_presentation` VARCHAR (255),
                ADD `exam_entree_col` VARCHAR (255),
                ADD `exam_entree_liquide_amnio` VARCHAR (255),
                ADD `exam_entree_indice_bishop` INT (11),
                ADD `exam_entree_prelev_urine` ENUM ('0','1'),
                ADD `exam_entree_proteinurie` VARCHAR (255),
                ADD `exam_entree_glycosurie` VARCHAR (255),
                ADD `exam_entree_prelev_vaginal` ENUM ('0','1'),
                ADD `exam_entree_prelev_vaginal_desc` VARCHAR (255),
                ADD `exam_entree_rcf` ENUM ('0','1'),
                ADD `exam_entree_rcf_desc` VARCHAR (255),
                ADD `exam_entree_amnioscopie` ENUM ('0','1'),
                ADD `exam_entree_amnioscopie_desc` VARCHAR (255),
                ADD `exam_entree_autres` ENUM ('0','1'),
                ADD `exam_entree_autres_desc` VARCHAR (255),
                ADD `rques_exam_entree` TEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`admission_id`),
                ADD INDEX (`adm_mater_constantes_id`),
                ADD INDEX (`adm_sage_femme_resp_id`),
                ADD INDEX (`date_ruptures_membranes`);";
    $this->addQuery($query);

    $this->makeRevision("0.35");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `lieu_accouchement` ENUM ('mater','dom','autremater','autre'),
                ADD `autre_lieu_accouchement` VARCHAR (255),
                ADD `nom_maternite_externe` VARCHAR (255),
                ADD `lieu_delivrance` VARCHAR (255),
                ADD `ag_accouchement` INT (11),
                ADD `ag_jours_accouchement` INT (11),
                ADD `mode_debut_travail` ENUM ('spon','decl','cesar'),
                ADD `rques_debut_travail` TEXT,
                ADD `datetime_declenchement` DATETIME,
                ADD `motif_decl_conv` ENUM ('0','1'),
                ADD `motif_decl_gross_prol` ENUM ('0','1'),
                ADD `motif_decl_patho_mat` ENUM ('0','1'),
                ADD `motif_decl_patho_foet` ENUM ('0','1'),
                ADD `motif_decl_rpm` ENUM ('0','1'),
                ADD `motif_decl_mort_iu` ENUM ('0','1'),
                ADD `motif_decl_img` ENUM ('0','1'),
                ADD `motif_decl_autre` ENUM ('0','1'),
                ADD `motif_decl_autre_details` VARCHAR (255),
                ADD `moyen_decl_ocyto` ENUM ('0','1'),
                ADD `moyen_decl_prosta` ENUM ('0','1'),
                ADD `moyen_decl_autre_medic` ENUM ('0','1'),
                ADD `moyen_decl_meca` ENUM ('0','1'),
                ADD `moyen_decl_rupture` ENUM ('0','1'),
                ADD `moyen_decl_autre` ENUM ('0','1'),
                ADD `moyen_decl_autre_details` VARCHAR (255),
                ADD `score_bishop` INT (11),
                ADD `remarques_declenchement` TEXT,
                ADD `surveillance_travail` ENUM ('cli','paracli'),
                ADD `tocographie` ENUM ('0','1'),
                ADD `type_tocographie` ENUM ('ext','int'),
                ADD `anomalie_contractions` ENUM ('aucune','hypoci','hyperci','hypoto','hyperto'),
                ADD `rcf` ENUM ('0','1'),
                ADD `type_rcf` ENUM ('ext','int'),
                ADD `desc_trace_rcf` ENUM ('norm','suspect','patho'),
                ADD `anomalie_rcf` ENUM ('bradyc','tachyc','plat','ralprec','raltard','ralvar'),
                ADD `ecg_foetal` ENUM ('0','1'),
                ADD `anomalie_ecg_foetal` ENUM ('0','1'),
                ADD `prelevement_sang_foetal` ENUM ('0','1'),
                ADD `anomalie_ph_sang_foetal` ENUM ('0','1'),
                ADD `detail_anomalie_ph_sang_foetal` VARCHAR (255),
                ADD `valeur_anomalie_ph_sang_foetal` FLOAT,
                ADD `anomalie_lactates_sang_foetal` ENUM ('0','1'),
                ADD `detail_anomalie_lactates_sang_foetal` VARCHAR (255),
                ADD `valeur_anomalie_lactates_sang_foetal` FLOAT,
                ADD `oxymetrie_foetale` ENUM ('0','1'),
                ADD `anomalie_oxymetrie_foetale` ENUM ('0','1'),
                ADD `detail_anomalie_oxymetrie_foetale` VARCHAR (255),
                ADD `autre_examen_surveillance` ENUM ('0','1'),
                ADD `desc_autre_examen_surveillance` VARCHAR (255),
                ADD `anomalie_autre_examen_surveillance` ENUM ('0','1'),
                ADD `rques_surveillance_travail` TEXT,
                ADD `therapeutique_pdt_travail` ENUM ('0','1'),
                ADD `antibio_pdt_travail` ENUM ('0','1'),
                ADD `antihypertenseurs_pdt_travail` ENUM ('0','1'),
                ADD `antispasmodiques_pdt_travail` ENUM ('0','1'),
                ADD `tocolytiques_pdt_travail` ENUM ('0','1'),
                ADD `ocytociques_pdt_travail` ENUM ('0','1'),
                ADD `opiaces_pdt_travail` ENUM ('0','1'),
                ADD `sedatifs_pdt_travail` ENUM ('0','1'),
                ADD `amnioinfusion_pdt_travail` ENUM ('0','1'),
                ADD `autre_therap_pdt_travail` ENUM ('0','1'),
                ADD `desc_autre_therap_pdt_travail` VARCHAR (255),
                ADD `rques_therap_pdt_travail` TEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`datetime_declenchement`);";
    $this->addQuery($query);

    $this->makeRevision("0.36");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `pathologie_accouchement` ENUM ('0','1'),
                ADD `fievre_pdt_travail` ENUM ('0','1'),
                ADD `fievre_travail_constantes_id` INT (11) UNSIGNED,
                ADD `anom_av_trav` ENUM ('0','1'),
                ADD `anom_pres_av_trav` ENUM ('0','1'),
                ADD `anom_pres_av_trav_siege` ENUM ('0','1'),
                ADD `anom_pres_av_trav_transverse` ENUM ('0','1'),
                ADD `anom_pres_av_trav_face` ENUM ('0','1'),
                ADD `anom_pres_av_trav_anormale` ENUM ('0','1'),
                ADD `anom_pres_av_trav_autre` ENUM ('0','1'),
                ADD `anom_bassin_av_trav` ENUM ('0','1'),
                ADD `anom_bassin_av_trav_bassin_retreci` ENUM ('0','1'),
                ADD `anom_bassin_av_trav_malform_bassin` ENUM ('0','1'),
                ADD `anom_bassin_av_trav_foetus` ENUM ('0','1'),
                ADD `anom_bassin_av_trav_disprop_foetopelv` ENUM ('0','1'),
                ADD `anom_bassin_av_trav_disprop_difform_foet` ENUM ('0','1'),
                ADD `anom_bassin_av_trav_disprop_sans_prec` ENUM ('0','1'),
                ADD `anom_genit_hors_trav` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_uterus_cicat` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_rupt_uterine` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_fibrome_uterin` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_malform_uterine` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_anom_vaginales` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_chir_ant_perinee` ENUM ('0','1'),
                ADD `anom_genit_hors_trav_prolapsus_vaginal` ENUM ('0','1'),
                ADD `anom_plac_av_trav` ENUM ('0','1'),
                ADD `anom_plac_av_trav_plac_prae_sans_hemo` ENUM ('0','1'),
                ADD `anom_plac_av_trav_plac_prae_avec_hemo` ENUM ('0','1'),
                ADD `anom_plac_av_trav_hrp_avec_trouble_coag` ENUM ('0','1'),
                ADD `anom_plac_av_trav_hrp_sans_trouble_coag` ENUM ('0','1'),
                ADD `anom_plac_av_trav_autre_hemo_avec_trouble_coag` ENUM ('0','1'),
                ADD `anom_plac_av_trav_autre_hemo_sans_trouble_coag` ENUM ('0','1'),
                ADD `anom_plac_av_trav_transf_foeto_mater` ENUM ('0','1'),
                ADD `anom_plac_av_trav_infect_sac_membranes` ENUM ('0','1'),
                ADD `rupt_premat_membranes` ENUM ('0','1'),
                ADD `rupt_premat_membranes_rpm_inf37sa_sans_toco` ENUM ('0','1'),
                ADD `rupt_premat_membranes_rpm_inf37sa_avec_toco` ENUM ('0','1'),
                ADD `rupt_premat_membranes_rpm_sup37sa` ENUM ('0','1'),
                ADD `patho_foet_chron` ENUM ('0','1'),
                ADD `patho_foet_chron_retard_croiss` ENUM ('0','1'),
                ADD `patho_foet_chron_macrosom_foetale` ENUM ('0','1'),
                ADD `patho_foet_chron_immun_antirh` ENUM ('0','1'),
                ADD `patho_foet_chron_autre_allo_immun` ENUM ('0','1'),
                ADD `patho_foet_chron_anasarque_non_immun` ENUM ('0','1'),
                ADD `patho_foet_chron_anasarque_immun` ENUM ('0','1'),
                ADD `patho_foet_chron_hypoxie_foetale` ENUM ('0','1'),
                ADD `patho_foet_chron_trouble_rcf` ENUM ('0','1'),
                ADD `patho_foet_chron_mort_foatale_in_utero` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_hta_gravid` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_preec_moderee` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_preec_severe` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_hellp` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_preec_hta` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_eclamp` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_diabete_id` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_diabete_nid` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_steatose_grav` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_herpes_genit` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_condylomes` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_hep_b` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_hep_c` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_vih` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_sida` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_fievre` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_gross_prolong` ENUM ('0','1'),
                ADD `patho_mat_foet_av_trav_autre` ENUM ('0','1'),
                ADD `autre_motif_cesarienne` ENUM ('0','1'),
                ADD `autre_motif_cesarienne_conv` ENUM ('0','1'),
                ADD `autre_motif_cesarienne_mult` ENUM ('0','1'),
                ADD `anom_pdt_trav` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav_rcf_isole` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav_la_teinte` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav_rcf_la` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav_anom_ph_foet` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav_anom_ecg_foet` ENUM ('0','1'),
                ADD `hypox_foet_pdt_trav_procidence_cordon` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav_rot_tete_incomp` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav_siege` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav_face` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav_pres_front` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav_pres_transv` ENUM ('0','1'),
                ADD `dysto_pres_pdt_trav_autre_pres_anorm` ENUM ('0','1'),
                ADD `dysto_anom_foet_pdt_trav` ENUM ('0','1'),
                ADD `dysto_anom_foet_pdt_trav_foetus_macrosome` ENUM ('0','1'),
                ADD `dysto_anom_foet_pdt_trav_jumeaux_soudes` ENUM ('0','1'),
                ADD `dysto_anom_foet_pdt_trav_difform_foet` ENUM ('0','1'),
                ADD `echec_decl_travail` ENUM ('0','1'),
                ADD `echec_decl_travail_medic` ENUM ('0','1'),
                ADD `echec_decl_travail_meca` ENUM ('0','1'),
                ADD `echec_decl_travail_sans_prec` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_deform_pelv` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_bassin_retr` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_detroit_sup_retr` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_detroit_moy_retr` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_dispr_foeto_pelv` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_fibrome_pelv` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_stenose_cerv` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_malf_uterine` ENUM ('0','1'),
                ADD `dysto_anom_pelv_mat_pdt_trav_autre` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_demarrage` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_cerv_latence` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_arret_dilat` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_hypertonie_uter` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_dilat_lente_col` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_echec_travail` ENUM ('0','1'),
                ADD `dysto_dynam_pdt_trav_non_engagement` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_hemo_sans_trouble_coag` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_hemo_avec_trouble_coag` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_choc_obst` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_eclampsie` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_rupt_uterine` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_embolie_amnio` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_embolie_pulm` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_complic_acte_obst` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_chorio_amnio` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_infection` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_fievre` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_fatigue_mat` ENUM ('0','1'),
                ADD `patho_mater_pdt_trav_autre_complication` ENUM ('0','1'),
                ADD `anom_expuls` ENUM ('0','1'),
                ADD `anom_expuls_non_progr_pres_foetale` ENUM ('0','1'),
                ADD `anom_expuls_dysto_pres_posterieures` ENUM ('0','1'),
                ADD `anom_expuls_dystocie_epaules` ENUM ('0','1'),
                ADD `anom_expuls_retention_tete` ENUM ('0','1'),
                ADD `anom_expuls_soufrance_foet_rcf` ENUM ('0','1'),
                ADD `anom_expuls_soufrance_foet_rcf_la` ENUM ('0','1'),
                ADD `anom_expuls_echec_forceps_cesar` ENUM ('0','1'),
                ADD `anom_expuls_fatigue_mat` ENUM ('0','1');";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`fievre_travail_constantes_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.37");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `pathologies_suite_couches` ENUM ('0','1'),
                ADD `infection_suite_couches` ENUM ('0','1'),
                ADD `infection_nosoc_suite_couches` ENUM ('0','1'),
                ADD `localisation_infection_suite_couches` ENUM ('abcessein','lympsein','vagin','infur','endopuer','infperin','perit','septi','fievre','infpari','inc'),
                ADD `compl_perineales_suite_couches` ENUM ('0','1'),
                ADD `details_compl_perineales_suite_couches` ENUM ('hematome','suture','abces'),
                ADD `compl_parietales_suite_couches` ENUM ('0','1'),
                ADD `detail_compl_parietales_suite_couches` ENUM ('hematome','suture','abces'),
                ADD `compl_allaitement_suite_couches` ENUM ('0','1'),
                ADD `details_compl_allaitement_suite_couches` ENUM ('crev','allait','autres'),
                ADD `details_comp_compl_allaitement_suite_couches` VARCHAR (255),
                ADD `compl_thrombo_embo_suite_couches` ENUM ('0','1'),
                ADD `detail_compl_thrombo_embo_suite_couches` ENUM ('thrombophlebsup','phleb','thrombophlebpelv','embpulm','thrombveicereb','hemorr'),
                ADD `compl_autre_suite_couches` ENUM ('0','1'),
                ADD `anemie_suite_couches` ENUM ('0','1'),
                ADD `incont_urin_suite_couches` ENUM ('0','1'),
                ADD `depression_suite_couches` ENUM ('0','1'),
                ADD `fract_obst_coccyx_suite_couches` ENUM ('0','1'),
                ADD `hemorragie_second_suite_couches` ENUM ('0','1'),
                ADD `retention_urinaire_suite_couches` ENUM ('0','1'),
                ADD `psychose_puerpuerale_suite_couches` ENUM ('0','1'),
                ADD `eclampsie_suite_couches` ENUM ('0','1'),
                ADD `insuf_reinale_suite_couches` ENUM ('0','1'),
                ADD `disjonction_symph_pub_suite_couches` ENUM ('0','1'),
                ADD `autre_comp_suite_couches` ENUM ('0','1'),
                ADD `desc_autre_comp_suite_couches` VARCHAR (255),
                ADD `compl_anesth_suite_couches` ENUM ('0','1'),
                ADD `compl_anesth_generale_suite_couches` ENUM ('mend','pulm','card','cereb','allerg','autre'),
                ADD `autre_compl_anesth_generale_suite_couches` VARCHAR (255),
                ADD `compl_anesth_locoregion_suite_couches` ENUM ('ceph','hypotens','autre'),
                ADD `autre_compl_anesth_locoregion_suite_couches` VARCHAR (255),
                ADD `traitements_sejour_mere` ENUM ('0','1'),
                ADD `ttt_preventif_sejour_mere` ENUM ('0','1'),
                ADD `antibio_preventif_sejour_mere` ENUM ('0','1'),
                ADD `desc_antibio_preventif_sejour_mere` VARCHAR (255),
                ADD `anticoag_preventif_sejour_mere` ENUM ('0','1'),
                ADD `desc_anticoag_preventif_sejour_mere` VARCHAR (255),
                ADD `antilactation_preventif_sejour_mere` ENUM ('0','1'),
                ADD `ttt_curatif_sejour_mere` ENUM ('0','1'),
                ADD `antibio_curatif_sejour_mere` ENUM ('0','1'),
                ADD `desc_antibio_curatif_sejour_mere` VARCHAR (255),
                ADD `anticoag_curatif_sejour_mere` ENUM ('0','1'),
                ADD `desc_anticoag_curatif_sejour_mere` VARCHAR (255),
                ADD `vacc_gammaglob_sejour_mere` ENUM ('0','1'),
                ADD `gammaglob_sejour_mere` ENUM ('0','1'),
                ADD `vacc_sejour_mere` ENUM ('0','1'),
                ADD `transfusion_sejour_mere` ENUM ('0','1'),
                ADD `nb_unite_transfusion_sejour_mere` INT (11),
                ADD `interv_sejour_mere` ENUM ('0','1'),
                ADD `datetime_interv_sejour_mere` DATETIME,
                ADD `revision_uterine_sejour_mere` ENUM ('0','1'),
                ADD `interv_second_hemorr_sejour_mere` ENUM ('0','1'),
                ADD `type_interv_second_hemorr_sejour_mere` ENUM ('emboart','ligarthypogast','ligartuter','veinecave','hysterect'),
                ADD `autre_interv_sejour_mere` ENUM ('0','1'),
                ADD `type_autre_interv_sejour_mere` ENUM ('repriseparoi','repriseperinee','evacpariet','laparo','thrombhemorr','steril'),
                ADD `jour_deces_sejour_mere` INT (11),
                ADD `deces_cause_obst_sejour_mere` ENUM ('0','1'),
                ADD `autopsie_sejour_mere` ENUM ('nondem','ref','faite'),
                ADD `resultat_autopsie_sejour_mere` ENUM ('sansanom','anom'),
                ADD `anomalie_autopsie_sejour_mere` VARCHAR (255);";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`datetime_interv_sejour_mere`);";
    $this->addQuery($query);

    $this->makeRevision("0.38");
    $query = "CREATE TABLE `grossesse_ant` (
                `grossesse_ant_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `grossesse_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `issue_grossesse` VARCHAR (255),
                `date` DATE,
                `lieu` VARCHAR (255),
                `ag` INT (11),
                `grossesse_apres_amp` ENUM ('0','1') DEFAULT '0',
                `complic_grossesse` VARCHAR (255),
                `transfert_in_utero` ENUM ('0','1') DEFAULT '0',
                `mode_debut_travail` ENUM ('spon','decl','cesar'),
                `mode_accouchement` VARCHAR (255),
                `anesthesie` VARCHAR (255),
                `perinee` VARCHAR (255),
                `delivrance` VARCHAR (255),
                `suite_couches` VARCHAR (255),
                `vecu_grossesse` VARCHAR (255),
                `remarques` TEXT,
                `grossesse_multiple` ENUM ('0','1') DEFAULT '0',
                `nombre_enfants` TINYINT (4) UNSIGNED,
                `sexe_enfant1` ENUM ('m','f'),
                `sexe_enfant2` ENUM ('m','f'),
                `sexe_enfant3` ENUM ('m','f'),
                `poids_naissance_enfant1` INT (11),
                `poids_naissance_enfant2` INT (11),
                `poids_naissance_enfant3` INT (11),
                `etat_nouveau_ne_enfant1` VARCHAR (255),
                `etat_nouveau_ne_enfant2` VARCHAR (255),
                `etat_nouveau_ne_enfant3` VARCHAR (255),
                `allaitement_enfant1` ENUM ('0','1') DEFAULT '0',
                `allaitement_enfant2` ENUM ('0','1') DEFAULT '0',
                `allaitement_enfant3` ENUM ('0','1') DEFAULT '0',
                `malformation_enfant1` VARCHAR (255),
                `malformation_enfant2` VARCHAR (255),
                `malformation_enfant3` VARCHAR (255),
                `maladie_hered_enfant1` VARCHAR (255),
                `maladie_hered_enfant2` VARCHAR (255),
                `maladie_hered_enfant3` VARCHAR (255),
                `pathologie_enfant1` VARCHAR (255),
                `pathologie_enfant2` VARCHAR (255),
                `pathologie_enfant3` VARCHAR (255),
                `transf_mut_enfant1` VARCHAR (255),
                `transf_mut_enfant2` VARCHAR (255),
                `transf_mut_enfant3` VARCHAR (255),
                `deces_enfant1` ENUM ('0','1') DEFAULT '0',
                `deces_enfant2` ENUM ('0','1') DEFAULT '0',
                `deces_enfant3` ENUM ('0','1') DEFAULT '0',
                `age_deces_enfant1` INT (11),
                `age_deces_enfant2` INT (11),
                `age_deces_enfant3` INT (11)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `grossesse_ant` 
                ADD INDEX (`grossesse_id`),
                ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.39");
    $query = "ALTER TABLE `naissance` 
                ADD `presence_pediatre` ENUM ('non','avant','apres'),
                ADD `pediatre_id` INT (11) UNSIGNED,
                ADD `presence_anesth` ENUM ('non','avant','apres'),
                ADD `anesth_id` INT (11) UNSIGNED,
                ADD `apgar_coeur_1` TINYINT (4) UNSIGNED,
                ADD `apgar_coeur_3` TINYINT (4) UNSIGNED,
                ADD `apgar_coeur_5` TINYINT (4) UNSIGNED,
                ADD `apgar_coeur_10` TINYINT (4) UNSIGNED,
                ADD `apgar_respi_1` TINYINT (4) UNSIGNED,
                ADD `apgar_respi_3` TINYINT (4) UNSIGNED,
                ADD `apgar_respi_5` TINYINT (4) UNSIGNED,
                ADD `apgar_respi_10` TINYINT (4) UNSIGNED,
                ADD `apgar_tonus_1` TINYINT (4) UNSIGNED,
                ADD `apgar_tonus_3` TINYINT (4) UNSIGNED,
                ADD `apgar_tonus_5` TINYINT (4) UNSIGNED,
                ADD `apgar_tonus_10` TINYINT (4) UNSIGNED,
                ADD `apgar_reflexes_1` TINYINT (4) UNSIGNED,
                ADD `apgar_reflexes_3` TINYINT (4) UNSIGNED,
                ADD `apgar_reflexes_5` TINYINT (4) UNSIGNED,
                ADD `apgar_reflexes_10` TINYINT (4) UNSIGNED,
                ADD `apgar_coloration_1` TINYINT (4) UNSIGNED,
                ADD `apgar_coloration_3` TINYINT (4) UNSIGNED,
                ADD `apgar_coloration_5` TINYINT (4) UNSIGNED,
                ADD `apgar_coloration_10` TINYINT (4) UNSIGNED,
                ADD `ph_ao` FLOAT,
                ADD `ph_v` FLOAT,
                ADD `base_deficit` VARCHAR (255),
                ADD `pco2` INT (11),
                ADD `lactates` VARCHAR (255),
                ADD `nouveau_ne_endormi` ENUM ('0','1'),
                ADD `accueil_peau_a_peau` ENUM ('0','1'),
                ADD `debut_allait_salle_naissance` ENUM ('0','1'),
                ADD `temp_salle_naissance` FLOAT,
                ADD `monitorage` ENUM ('0','1'),
                ADD `monit_frequence_cardiaque` ENUM ('0','1'),
                ADD `monit_saturation` ENUM ('0','1'),
                ADD `monit_glycemie` ENUM ('0','1'),
                ADD `monit_incubateur` ENUM ('0','1'),
                ADD `monit_remarques` TEXT,
                ADD `reanimation` ENUM ('0','1'),
                ADD `rea_par` ENUM ('sf','ped','anesth','samu','obst'),
                ADD `rea_par_id` INT (11) UNSIGNED,
                ADD `rea_aspi_laryngo` ENUM ('0','1'),
                ADD `rea_ventil_masque` ENUM ('0','1'),
                ADD `rea_o2_sonde` ENUM ('0','1'),
                ADD `rea_ppc_nasale` ENUM ('0','1'),
                ADD `rea_duree_ppc_nasale` INT (11),
                ADD `rea_ventil_tube_endo` ENUM ('0','1'),
                ADD `rea_duree_ventil_tube_endo` INT (11),
                ADD `rea_intub_tracheale` ENUM ('0','1'),
                ADD `rea_min_vie_intub_tracheale` INT (11),
                ADD `rea_massage_card` ENUM ('0','1'),
                ADD `rea_injection_medic` ENUM ('0','1'),
                ADD `rea_injection_medic_adre` ENUM ('0','1'),
                ADD `rea_injection_medic_surfa` ENUM ('0','1'),
                ADD `rea_injection_medic_gluc` ENUM ('0','1'),
                ADD `rea_injection_medic_autre` ENUM ('0','1'),
                ADD `rea_injection_medic_autre_desc` VARCHAR (255),
                ADD `rea_autre_geste` ENUM ('0','1'),
                ADD `rea_autre_geste_desc` VARCHAR (255),
                ADD `duree_totale_rea` INT (11),
                ADD `temp_fin_rea` FLOAT,
                ADD `gly_fin_rea` FLOAT,
                ADD `etat_fin_rea` ENUM ('sat','surv','mut','transf'),
                ADD `rea_remarques` TEXT,
                ADD `prophy_vit_k` ENUM ('0','1'),
                ADD `prophy_vit_k_type` ENUM ('parent','oral'),
                ADD `prophy_desinfect_occulaire` ENUM ('0','1'),
                ADD `prophy_asp_naso_phar` ENUM ('0','1'),
                ADD `prophy_perm_choanes` ENUM ('0','1'),
                ADD `prophy_perm_oeso` ENUM ('0','1'),
                ADD `prophy_perm_anale` ENUM ('0','1'),
                ADD `prophy_emission_urine` ENUM ('0','1'),
                ADD `prophy_emission_meconium` ENUM ('0','1'),
                ADD `prophy_autre` ENUM ('0','1'),
                ADD `prophy_autre_desc` VARCHAR (255),
                ADD `prophy_remarques` TEXT,
                ADD `cortico` ENUM ('0','1'),
                ADD `nb_cures_cortico` INT (11),
                ADD `dern_cure_cortico` ENUM ('comp','incomp'),
                ADD `delai_cortico_acc_j` INT (11),
                ADD `delai_cortico_acc_h` INT (11),
                ADD `prev_cortico_remarques` TEXT,
                ADD `contexte_infectieux` ENUM ('0','1'),
                ADD `infect_facteurs_risque_infect` ENUM ('0','1'),
                ADD `infect_rpm_sup_12h` ENUM ('0','1'),
                ADD `infect_liquide_teinte` ENUM ('0','1'),
                ADD `infect_strepto_b` ENUM ('0','1'),
                ADD `infect_fievre_mat` ENUM ('0','1'),
                ADD `infect_maternelle` ENUM ('0','1'),
                ADD `infect_autre` ENUM ('0','1'),
                ADD `infect_autre_desc` VARCHAR (255),
                ADD `infect_prelev_bacterio` ENUM ('0','1'),
                ADD `infect_prelev_gatrique` ENUM ('0','1'),
                ADD `infect_prelev_autre_periph` ENUM ('0','1'),
                ADD `infect_prelev_placenta` ENUM ('0','1'),
                ADD `infect_prelev_sang` ENUM ('0','1'),
                ADD `infect_antibio` ENUM ('0','1'),
                ADD `infect_antibio_desc` VARCHAR (255),
                ADD `infect_remarques` TEXT,
                ADD `prelev_bacterio_mere` ENUM ('0','1'),
                ADD `prelev_bacterio_vaginal_mere` ENUM ('0','1'),
                ADD `prelev_bacterio_vaginal_mere_germe` VARCHAR (255),
                ADD `prelev_bacterio_urinaire_mere` ENUM ('0','1'),
                ADD `prelev_bacterio_urinaire_mere_germe` VARCHAR (255),
                ADD `antibiotherapie_antepart_mere` ENUM ('0','1'),
                ADD `antibiotherapie_antepart_mere_desc` VARCHAR (255),
                ADD `antibiotherapie_perpart_mere` ENUM ('0','1'),
                ADD `antibiotherapie_perpart_mere_desc` VARCHAR (255),
                ADD `nouveau_ne_constantes_id` INT (11) UNSIGNED,
                ADD `mode_sortie` ENUM ('mere','mut','transfres','transfhres','deces','autre'),
                ADD `mode_sortie_autre` VARCHAR (255),
                ADD `min_vie_transmut` INT (11),
                ADD `resp_transmut_id` INT (11) UNSIGNED,
                ADD `motif_transmut` ENUM ('prema','hypotroph','detrespi','risqueinfect','malform','autrepatho'),
                ADD `detail_motif_transmut` VARCHAR (255),
                ADD `lieu_transf` VARCHAR (255),
                ADD `type_etab_transf` ENUM ('1','2'),
                ADD `dest_transf` ENUM ('rea','intens','neonat','chir','neonatmater','autre'),
                ADD `dest_transf_autre` VARCHAR (255),
                ADD `mode_transf` ENUM ('intra','extra','samuped','samuns','ambulance','voiture'),
                ADD `delai_appel_arrivee_transp` INT (11),
                ADD `dist_mater_transf` INT (11),
                ADD `raison_transf_report` ENUM ('place','transp','autre'),
                ADD `raison_transf_report_autre` VARCHAR (255),
                ADD `remarques_transf` TEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `naissance` 
                ADD INDEX (`pediatre_id`),
                ADD INDEX (`anesth_id`),
                ADD INDEX (`rea_par_id`),
                ADD INDEX (`nouveau_ne_constantes_id`),
                ADD INDEX (`resp_transmut_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.40");
    $query = "ALTER TABLE `naissance` 
                ADD `pathologies` ENUM ('0','1'),
                ADD `lesion_traumatique` ENUM ('0','1'),
                ADD `lesion_faciale` ENUM ('0','1'),
                ADD `paralysie_faciale` ENUM ('0','1'),
                ADD `cephalhematome` ENUM ('0','1'),
                ADD `paralysie_plexus_brachial_sup` ENUM ('0','1'),
                ADD `paralysie_plexus_brachial_inf` ENUM ('0','1'),
                ADD `lesion_cuir_chevelu` ENUM ('0','1'),
                ADD `fracture_clavicule` ENUM ('0','1'),
                ADD `autre_lesion` ENUM ('0','1'),
                ADD `autre_lesion_desc` VARCHAR (255),
                ADD `infection` ENUM ('0','1'),
                ADD `infection_degre` ENUM ('risque','colo','prob','prouv'),
                ADD `infection_origine` ENUM ('materfoet','nosoc','inc'),
                ADD `infection_sang` ENUM ('0','1'),
                ADD `infection_lcr` ENUM ('0','1'),
                ADD `infection_poumon` ENUM ('0','1'),
                ADD `infection_urines` ENUM ('0','1'),
                ADD `infection_digestif` ENUM ('0','1'),
                ADD `infection_ombilic` ENUM ('0','1'),
                ADD `infection_oeil` ENUM ('0','1'),
                ADD `infection_os_articulations` ENUM ('0','1'),
                ADD `infection_peau` ENUM ('0','1'),
                ADD `infection_autre` ENUM ('0','1'),
                ADD `infection_autre_desc` VARCHAR (255),
                ADD `infection_strepto_b` ENUM ('0','1'),
                ADD `infection_autre_strepto` ENUM ('0','1'),
                ADD `infection_staphylo_dore` ENUM ('0','1'),
                ADD `infection_autre_staphylo` ENUM ('0','1'),
                ADD `infection_haemophilus` ENUM ('0','1'),
                ADD `infection_listeria` ENUM ('0','1'),
                ADD `infection_pneumocoque` ENUM ('0','1'),
                ADD `infection_autre_gplus` ENUM ('0','1'),
                ADD `infection_coli` ENUM ('0','1'),
                ADD `infection_proteus` ENUM ('0','1'),
                ADD `infection_klebsiele` ENUM ('0','1'),
                ADD `infection_autre_gmoins` ENUM ('0','1'),
                ADD `infection_chlamydiae` ENUM ('0','1'),
                ADD `infection_mycoplasme` ENUM ('0','1'),
                ADD `infection_candida` ENUM ('0','1'),
                ADD `infection_toxoplasme` ENUM ('0','1'),
                ADD `infection_autre_parasite` ENUM ('0','1'),
                ADD `infection_cmv` ENUM ('0','1'),
                ADD `infection_rubeole` ENUM ('0','1'),
                ADD `infection_herpes` ENUM ('0','1'),
                ADD `infection_varicelle` ENUM ('0','1'),
                ADD `infection_vih` ENUM ('0','1'),
                ADD `infection_autre_virus` ENUM ('0','1'),
                ADD `infection_autre_virus_desc` VARCHAR (255),
                ADD `infection_germe_non_trouve` ENUM ('0','1'),
                ADD `ictere` ENUM ('0','1'),
                ADD `ictere_prema` ENUM ('0','1'),
                ADD `ictere_intense_terme` ENUM ('0','1'),
                ADD `ictere_allo_immun_abo` ENUM ('0','1'),
                ADD `ictere_allo_immun_rh` ENUM ('0','1'),
                ADD `ictere_allo_immun_autre` ENUM ('0','1'),
                ADD `ictere_autre_origine` ENUM ('0','1'),
                ADD `ictere_autre_origine_desc` VARCHAR (255),
                ADD `ictere_phototherapie` ENUM ('0','1'),
                ADD `ictere_type_phototherapie` ENUM ('conv','intens'),
                ADD `trouble_regul_thermique` ENUM ('0','1'),
                ADD `hyperthermie` ENUM ('0','1'),
                ADD `hypothermie_legere` ENUM ('0','1'),
                ADD `hypothermie_grave` ENUM ('0','1'),
                ADD `anom_cong` ENUM ('0','1'),
                ADD `anom_cong_isolee` ENUM ('0','1'),
                ADD `anom_cong_synd_polyformatif` ENUM ('0','1'),
                ADD `anom_cong_tube_neural` ENUM ('0','1'),
                ADD `anom_cong_fente_labio_palatine` ENUM ('0','1'),
                ADD `anom_cong_atresie_oesophage` ENUM ('0','1'),
                ADD `anom_cong_omphalocele` ENUM ('0','1'),
                ADD `anom_cong_reduc_absence_membres` ENUM ('0','1'),
                ADD `anom_cong_hydrocephalie` ENUM ('0','1'),
                ADD `anom_cong_hydrocephalie_type` ENUM ('susp','cert'),
                ADD `anom_cong_malform_card` ENUM ('0','1'),
                ADD `anom_cong_malform_card_type` ENUM ('susp','cert'),
                ADD `anom_cong_hanches_luxables` ENUM ('0','1'),
                ADD `anom_cong_hanches_luxables_type` ENUM ('susp','cert'),
                ADD `anom_cong_malform_reinale` ENUM ('0','1'),
                ADD `anom_cong_malform_reinale_type` ENUM ('susp','cert'),
                ADD `anom_cong_autre` ENUM ('0','1'),
                ADD `anom_cong_autre_desc` VARCHAR (255),
                ADD `anom_cong_chromosomique` ENUM ('0','1'),
                ADD `anom_cong_genique` ENUM ('0','1'),
                ADD `anom_cong_trisomie_21` ENUM ('0','1'),
                ADD `anom_cong_trisomie_type` ENUM ('susp','cert'),
                ADD `anom_cong_chrom_gen_autre` ENUM ('0','1'),
                ADD `anom_cong_chrom_gen_autre_desc` VARCHAR (255),
                ADD `anom_cong_description_clair` TEXT,
                ADD `anom_cong_moment_diag` ENUM ('antenat','neonat','autop'),
                ADD `autre_pathologie` ENUM ('0','1'),
                ADD `patho_resp` ENUM ('0','1'),
                ADD `tachypnee` ENUM ('0','1'),
                ADD `autre_detresse_resp_neonat` ENUM ('0','1'),
                ADD `acces_cyanose` ENUM ('0','1'),
                ADD `apnees_prema` ENUM ('0','1'),
                ADD `apnees_autre` ENUM ('0','1'),
                ADD `inhalation_meco_sans_pneumopath` ENUM ('0','1'),
                ADD `inhalation_meco_avec_pneumopath` ENUM ('0','1'),
                ADD `inhalation_lait` ENUM ('0','1'),
                ADD `patho_cardiovasc` ENUM ('0','1'),
                ADD `trouble_du_rythme` ENUM ('0','1'),
                ADD `hypertonie_vagale` ENUM ('0','1'),
                ADD `souffle_a_explorer` ENUM ('0','1'),
                ADD `patho_neuro` ENUM ('0','1'),
                ADD `hypothonie` ENUM ('0','1'),
                ADD `hypertonie` ENUM ('0','1'),
                ADD `irrit_cerebrale` ENUM ('0','1'),
                ADD `mouv_anormaux` ENUM ('0','1'),
                ADD `convulsions` ENUM ('0','1'),
                ADD `patho_dig` ENUM ('0','1'),
                ADD `alim_sein_difficile` ENUM ('0','1'),
                ADD `alim_lente` ENUM ('0','1'),
                ADD `stagnation_pond` ENUM ('0','1'),
                ADD `perte_poids_sup_10_pourc` ENUM ('0','1'),
                ADD `regurgitations` ENUM ('0','1'),
                ADD `vomissements` ENUM ('0','1'),
                ADD `reflux_gatro_eoso` ENUM ('0','1'),
                ADD `oesophagite` ENUM ('0','1'),
                ADD `hematemese` ENUM ('0','1'),
                ADD `synd_occlusif` ENUM ('0','1'),
                ADD `trouble_succion_deglut` ENUM ('0','1'),
                ADD `patho_hemato` ENUM ('0','1'),
                ADD `anemie_neonat` ENUM ('0','1'),
                ADD `anemie_transf_foeto_mat` ENUM ('0','1'),
                ADD `anemie_transf_foeto_foet` ENUM ('0','1'),
                ADD `drepano_positif` ENUM ('0','1'),
                ADD `maladie_hemo` ENUM ('0','1'),
                ADD `thrombopenie` ENUM ('0','1'),
                ADD `patho_metab` ENUM ('0','1'),
                ADD `hypogly_diab_mere_gest` ENUM ('0','1'),
                ADD `hypogly_diab_mere_nid` ENUM ('0','1'),
                ADD `hypogly_diab_mere_id` ENUM ('0','1'),
                ADD `hypogly_neonat_transitoire` ENUM ('0','1'),
                ADD `hypocalcemie` ENUM ('0','1'),
                ADD `intoxication` ENUM ('0','1'),
                ADD `synd_sevrage_toxico` ENUM ('0','1'),
                ADD `synd_sevrage_medic` ENUM ('0','1'),
                ADD `tabac_maternel` ENUM ('0','1'),
                ADD `alcool_maternel` ENUM ('0','1'),
                ADD `autre_patho_autre` ENUM ('0','1'),
                ADD `rhinite_neonat` ENUM ('0','1'),
                ADD `patho_dermato` ENUM ('0','1'),
                ADD `autre_atho_autre_thesaurus` ENUM ('0','1'),
                ADD `actes_effectues` ENUM ('0','1'),
                ADD `caryotype` ENUM ('0','1'),
                ADD `etf` ENUM ('0','1'),
                ADD `eeg` ENUM ('0','1'),
                ADD `ecg` ENUM ('0','1'),
                ADD `fond_oeil` ENUM ('0','1'),
                ADD `antibiotherapie` ENUM ('0','1'),
                ADD `oxygenotherapie` ENUM ('0','1'),
                ADD `echographie_cardiaque` ENUM ('0','1'),
                ADD `echographie_cerebrale` ENUM ('0','1'),
                ADD `echographie_hanche` ENUM ('0','1'),
                ADD `echographie_hepatique` ENUM ('0','1'),
                ADD `echographie_reinale` ENUM ('0','1'),
                ADD `exsanguino_transfusion` ENUM ('0','1'),
                ADD `intubation` ENUM ('0','1'),
                ADD `incubateur` ENUM ('0','1'),
                ADD `injection_gamma_globulines` ENUM ('0','1'),
                ADD `togd` ENUM ('0','1'),
                ADD `radio_thoracique` ENUM ('0','1'),
                ADD `reeducation` ENUM ('0','1'),
                ADD `autre_acte` ENUM ('0','1'),
                ADD `autre_acte_desc` VARCHAR (255),
                ADD `mesures_prophylactiques` ENUM ('0','1'),
                ADD `hep_b_injection_immunoglob` ENUM ('0','1'),
                ADD `vaccinations` ENUM ('0','1'),
                ADD `vacc_hep_b` ENUM ('0','1'),
                ADD `vacc_hepp_bcg` ENUM ('0','1'),
                ADD `depistage_sanguin` ENUM ('0','1'),
                ADD `hyperphenylalanemie` ENUM ('0','1'),
                ADD `hypothyroidie` ENUM ('0','1'),
                ADD `hyperplasie_cong_surrenales` ENUM ('0','1'),
                ADD `drepanocytose` ENUM ('0','1'),
                ADD `mucoviscidose` ENUM ('0','1'),
                ADD `test_audition` ENUM ('0','1'),
                ADD `etat_test_audition` ENUM ('non','norm','anorm'),
                ADD `supp_vitaminique` ENUM ('0','1'),
                ADD `supp_vitaminique_desc` VARCHAR (255),
                ADD `autre_mesure_proph` ENUM ('0','1'),
                ADD `autre_mesure_proph_desc` VARCHAR (255),
                ADD `mode_sortie_mater` ENUM ('dom','mut','transfres','transfhres','poupon','deces','autre'),
                ADD `mode_sortie_mater_autre` VARCHAR (255),
                ADD `jour_vie_transmut_mater` INT (11),
                ADD `heure_vie_transmut_mater` INT (11),
                ADD `resp_transmut_mater_id` INT (11) UNSIGNED,
                ADD `motif_transmut_mater` ENUM ('malform','infect','ictere','pathrespi','pathcv','pathdig','pathhemato','pathmetab','pathneuro','syndsev','autre'),
                ADD `detail_motif_transmut_mater` VARCHAR (255),
                ADD `lieu_transf_mater` VARCHAR (255),
                ADD `type_etab_transf_mater` ENUM ('1','2'),
                ADD `dest_transf_mater` ENUM ('rea','intens','neonat','chir','neonatmater','autre'),
                ADD `dest_transf_mater_autre` VARCHAR (255),
                ADD `mode_transf_mater` ENUM ('intra','extra','samuped','samuns','ambulance','voiture'),
                ADD `delai_appel_arrivee_transp_mater` INT (11),
                ADD `dist_mater_transf_mater` INT (11),
                ADD `raison_transf_mater_report` ENUM ('place','transp','autre'),
                ADD `raison_transf_report_mater_autre` VARCHAR (255),
                ADD `surv_part_sortie_mater` ENUM ('non','survmed','survpmi','consultspec','autre'),
                ADD `surv_part_sortie_mater_desc` VARCHAR (255),
                ADD `remarques_transf_mater` TEXT,
                ADD `poids_fin_sejour` INT (11),
                ADD `alim_fin_sejour` ENUM ('laitmat','mixte','artif','dietspec'),
                ADD `comp_alim_fin_sejour` ENUM ('0','1'),
                ADD `nature_comp_alim_fin_sejour` ENUM ('eau','eausucree','prepalactee'),
                ADD `moyen_comp_alim_fin_sejour` ENUM ('tasse','cuill','bib'),
                ADD `indic_comp_alim_fin_sejour` ENUM ('pertepoids','patho'),
                ADD `indic_comp_alim_fin_sejour_desc` VARCHAR (255),
                ADD `retour_mater` ENUM ('0','1'),
                ADD `date_retour_mater` DATE,
                ADD `duree_transfert` INT (11),
                ADD `moment_deces` ENUM ('avttrav','ensalle','pdttrav','img','sansprec','neonat'),
                ADD `date_deces` DATE,
                ADD `age_deces_jours` INT (11),
                ADD `age_deces_heures` INT (11),
                ADD `cause_deces` ENUM ('foetneonat','obstmater'),
                ADD `cause_deces_desc` VARCHAR (255),
                ADD `autopsie` ENUM ('nf','resnd','resni','resi');";
    $this->addQuery($query);
    $query = "ALTER TABLE `naissance` 
                ADD INDEX (`resp_transmut_mater_id`),
                ADD INDEX (`date_retour_mater`),
                ADD INDEX (`date_deces`);";
    $this->addQuery($query);

    $this->makeRevision("0.41");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `sage_femme_resp_acct_id` INT (11) UNSIGNED,
                ADD `medecin_resp_acct_id` INT (11) UNSIGNED,
                ADD `acct_effectue_par_type` ENUM ('med','sf','autre'),
                ADD `acct_effectue_par_type_autre` VARCHAR (255),
                ADD `presentation_acct` ENUM ('sommop','sommos','face','bregma','front','siegecomp','siegdecomp','transv'),
                ADD `moment_rupt_membranes` ENUM ('avant','spont','artif','cesar'),
                ADD `qte_liquide_rupt_membranes` ENUM ('norm','oligoa','hydroa','absent'),
                ADD `aspect_liquide_rupt_membranes` ENUM ('clair','meco','sang','teinte','autre'),
                ADD `aspect_liquide_rupt_membranes_desc` VARCHAR (255),
                ADD `aspect_liquide_post_rupt_membranes` ENUM ('clair','meco','sang','teinte','autre'),
                ADD `aspect_liquide_post_rupt_membranes_desc` VARCHAR (255),
                ADD `acct_voie_basse_spont` ENUM ('0','1'),
                ADD `pos_acct_voie_basse_spont` ENUM ('decubdors','decublat','vert'),
                ADD `acct_interv_voie_basse` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_forceps` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_ventouse` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_spatules` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_pet_extr_siege` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_grd_extr_siege` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_autre_man_siege` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_man_dyst_epaules` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_man_dyst_epaules_desc` VARCHAR (255),
                ADD `acct_interv_voie_basse_autre_man` ENUM ('0','1'),
                ADD `acct_interv_voie_basse_autre_man_desc` VARCHAR (255),
                ADD `acct_interv_voie_basse_motif` ENUM ('mat','foet'),
                ADD `acct_interv_voie_basse_motif_asso` VARCHAR (255),
                ADD `acct_cesar_avt_travail` ENUM ('0','1'),
                ADD `acct_cesar_avt_travail_type` ENUM ('prog','urg'),
                ADD `acct_cesar_pdt_travail` ENUM ('0','1'),
                ADD `acct_cesar_pdt_travail_type` ENUM ('urg','prog'),
                ADD `acct_cesar_motif` ENUM ('mat','foet'),
                ADD `acct_cesar_motif_asso` VARCHAR (255),
                ADD `endroit_action_interv_voie_basse` ENUM ('detinf','detmoy','detsup','tete'),
                ADD `type_cesar` ENUM ('segtransv','segvert','corpo','segcorpo','vag'),
                ADD `remarques_cesar` TEXT,
                ADD `actes_associes_cesar` ENUM ('0','1'),
                ADD `actes_associes_cesar_hysterectomie_hemostase` ENUM ('0','1'),
                ADD `actes_associes_cesar_kystectomie_ovarienne` ENUM ('0','1'),
                ADD `actes_associes_cesar_myomectomie_unique` ENUM ('0','1'),
                ADD `actes_associes_cesar_ste_tubaire` ENUM ('0','1'),
                ADD `actes_associes_cesar_interv_gross_abd` ENUM ('0','1'),
                ADD `acct_pb_cordon` ENUM ('0','1'),
                ADD `acct_pb_cordon_procidence` ENUM ('0','1'),
                ADD `acct_pb_cordon_circ_serre` ENUM ('0','1'),
                ADD `acct_pb_cordon_noeud_vrai` ENUM ('0','1'),
                ADD `acct_pb_cordon_brievete` ENUM ('0','1'),
                ADD `acct_pb_cordon_insert_velament` ENUM ('0','1'),
                ADD `acct_pb_cordon_autre` ENUM ('0','1'),
                ADD `acct_pb_cordon_autre_desc` VARCHAR (255),
                ADD `duree_ouverture_oeuf_jours` INT (11),
                ADD `duree_ouverture_oeuf_heures` INT (11),
                ADD `duree_travail_heures` INT (11),
                ADD `duree_travail_de_5cm_heures` INT (11),
                ADD `duree_deambulation_heures` INT (11),
                ADD `duree_deambulation_minutes` INT (11),
                ADD `duree_entre_dilat_efforts_expuls` INT (11),
                ADD `duree_efforts_expuls` INT (11),
                ADD `anesth_avant_naiss` ENUM ('0','1'),
                ADD `datetime_anesth_avant_naiss` DATETIME,
                ADD `anesth_avant_naiss_par_id` INT (11) UNSIGNED,
                ADD `suivi_anesth_avant_naiss_par` VARCHAR (255),
                ADD `alr_avant_naiss` ENUM ('0','1'),
                ADD `alr_peri_avant_naiss` ENUM ('0','1'),
                ADD `alr_peri_avant_naiss_inj_unique` ENUM ('0','1'),
                ADD `alr_peri_avant_naiss_reinj` ENUM ('0','1'),
                ADD `alr_peri_avant_naiss_cat_autopousse` ENUM ('0','1'),
                ADD `alr_peri_avant_naiss_cat_pcea` ENUM ('0','1'),
                ADD `alr_rachi_avant_naiss` ENUM ('0','1'),
                ADD `alr_rachi_avant_naiss_inj_unique` ENUM ('0','1'),
                ADD `alr_rachi_avant_naiss_cat` ENUM ('0','1'),
                ADD `alr_peri_rachi_avant_naiss` ENUM ('0','1'),
                ADD `ag_avant_naiss` ENUM ('0','1'),
                ADD `ag_avant_naiss_directe` ENUM ('0','1'),
                ADD `ag_avant_naiss_apres_peri` ENUM ('0','1'),
                ADD `ag_avant_naiss_apres_rachi` ENUM ('0','1'),
                ADD `al_avant_naiss` ENUM ('0','1'),
                ADD `al_bloc_avant_naiss` ENUM ('0','1'),
                ADD `al_autre_avant_naiss` ENUM ('0','1'),
                ADD `al_autre_avant_naiss_desc` VARCHAR (255),
                ADD `autre_analg_avant_naiss` ENUM ('0','1'),
                ADD `autre_analg_avant_naiss_desc` VARCHAR (255),
                ADD `fibro_laryngee` ENUM ('0','1'),
                ADD `asa_anesth_avant_naissance` INT (11),
                ADD `moment_anesth_avant_naissance` ENUM ('debtrav','intervvb','cesar'),
                ADD `anesth_spec_2eme_enfant` ENUM ('non','ag','rachi','autre'),
                ADD `anesth_spec_2eme_enfant_desc` VARCHAR (255),
                ADD `rques_anesth_avant_naiss` TEXT,
                ADD `comp_anesth_avant_naiss` ENUM ('0','1'),
                ADD `hypotension_alr_avant_naiss` ENUM ('0','1'),
                ADD `autre_comp_alr_avant_naiss` ENUM ('0','1'),
                ADD `autre_comp_alr_avant_naiss_desc` VARCHAR (255),
                ADD `mendelson_ag_avant_naiss` ENUM ('0','1'),
                ADD `comp_pulm_ag_avant_naiss` ENUM ('0','1'),
                ADD `comp_card_ag_avant_naiss` ENUM ('0','1'),
                ADD `comp_cereb_ag_avant_naiss` ENUM ('0','1'),
                ADD `comp_allerg_tox_ag_avant_naiss` ENUM ('0','1'),
                ADD `autre_comp_ag_avant_naiss` ENUM ('0','1'),
                ADD `autre_comp_ag_avant_naiss_desc` VARCHAR (255),
                ADD `anesth_apres_naissance` ENUM ('non','ag','al','autre'),
                ADD `anesth_apres_naissance_desc` VARCHAR (255),
                ADD `rques_anesth_apres_naissance` TEXT,
                ADD `deliv_faite_par` VARCHAR (255),
                ADD `datetime_deliv` DATETIME,
                ADD `type_deliv` ENUM ('dir','nat'),
                ADD `prod_deliv` VARCHAR (255),
                ADD `dose_prod_deliv` VARCHAR (255),
                ADD `datetime_inj_prod_deliv` DATETIME,
                ADD `voie_inj_prod_deliv` VARCHAR (255),
                ADD `modalite_deliv` ENUM ('comp','incomp','retplac'),
                ADD `comp_deliv` ENUM ('0','1'),
                ADD `hemorr_deliv` ENUM ('0','1'),
                ADD `retention_plac_comp_deliv` ENUM ('0','1'),
                ADD `retention_plac_part_deliv` ENUM ('0','1'),
                ADD `atonie_uterine_deliv` ENUM ('0','1'),
                ADD `trouble_coag_deliv` ENUM ('0','1'),
                ADD `transf_deliv` ENUM ('0','1'),
                ADD `nb_unites_transf_deliv` INT (11),
                ADD `autre_comp_deliv` ENUM ('0','1'),
                ADD `retention_plac_comp_sans_hemorr_deliv` ENUM ('0','1'),
                ADD `retention_plac_part_sans_hemorr_deliv` ENUM ('0','1'),
                ADD `inversion_uterine_deliv` ENUM ('0','1'),
                ADD `autre_comp_autre_deliv` ENUM ('0','1'),
                ADD `autre_comp_autre_deliv_desc` VARCHAR (255),
                ADD `total_pertes_sang_deliv` INT (11),
                ADD `actes_pdt_deliv` ENUM ('0','1'),
                ADD `deliv_artificielle` ENUM ('0','1'),
                ADD `rev_uterine_isolee_deliv` ENUM ('0','1'),
                ADD `autres_actes_deliv` ENUM ('0','1'),
                ADD `ligature_art_hypogast_deliv` ENUM ('0','1'),
                ADD `ligature_art_uterines_deliv` ENUM ('0','1'),
                ADD `hysterectomie_hemostase_deliv` ENUM ('0','1'),
                ADD `embolisation_arterielle_deliv` ENUM ('0','1'),
                ADD `reduct_inversion_uterine_deliv` ENUM ('0','1'),
                ADD `cure_chir_inversion_uterine_deliv` ENUM ('0','1'),
                ADD `poids_placenta` INT (11),
                ADD `anomalie_placenta` ENUM ('non','malf','autre'),
                ADD `anomalie_placenta_desc` VARCHAR (255),
                ADD `type_placentation` ENUM ('monomono','monobi','bibi','tritri','autre'),
                ADD `type_placentation_desc` VARCHAR (255),
                ADD `poids_placenta_1_bichorial` INT (11),
                ADD `poids_placenta_2_bichorial` INT (11),
                ADD `exam_anapath_placenta_demande` ENUM ('0','1'),
                ADD `rques_placenta` TEXT,
                ADD `lesion_parties_molles` ENUM ('0','1'),
                ADD `episiotomie` ENUM ('0','1'),
                ADD `dechirure_perineale` ENUM ('0','1'),
                ADD `dechirure_perineale_liste` ENUM ('1','2','3','4'),
                ADD `lesions_traumatiques_parties_molles` ENUM ('0','1'),
                ADD `dechirure_vaginale` ENUM ('0','1'),
                ADD `dechirure_cervicale` ENUM ('0','1'),
                ADD `lesion_urinaire` ENUM ('0','1'),
                ADD `rupt_uterine` ENUM ('0','1'),
                ADD `thrombus` ENUM ('0','1'),
                ADD `autre_lesion` ENUM ('0','1'),
                ADD `autre_lesion_desc` VARCHAR (255),
                ADD `compte_rendu_delivrance` TEXT,
                ADD `consignes_suite_couches` TEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`sage_femme_resp_acct_id`),
                ADD INDEX (`medecin_resp_acct_id`),
                ADD INDEX (`datetime_anesth_avant_naiss`),
                ADD INDEX (`anesth_avant_naiss_par_id`),
                ADD INDEX (`datetime_deliv`),
                ADD INDEX (`datetime_inj_prod_deliv`);";
    $this->addQuery($query);

    $this->makeRevision("0.42");
    $query = "ALTER TABLE `suivi_grossesse` 
                ADD `conclusion` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.43");
    $query = "ALTER TABLE `grossesse_ant` 
                ADD `allaitement_enfant1_desc` VARCHAR (255),
                ADD `allaitement_enfant2_desc` VARCHAR (255),
                ADD `allaitement_enfant3_desc` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.44");
    $query = "ALTER TABLE `suivi_grossesse`
                ADD `type_suivi` ENUM ('surv','urg','autre') DEFAULT 'surv' AFTER `consultation_id`;";
    $this->addQuery($query);

    $this->makeRevision("0.45");
    $query = "ALTER TABLE `grossesse`
      ADD `cycle` TINYINT (4) UNSIGNED DEFAULT '28';";
    $this->addQuery($query);

    $this->makeRevision("0.46");
    $query = "ALTER TABLE `naissance` 
                ADD `enfant_present_postnat` ENUM ('0','1'),
                ADD `etat_enfant_postnat` ENUM ('ok','surv','hosp','deces'),
                ADD `rehospitalisation_postnat` ENUM ('0','1'),
                ADD `motif_rehospitalisation_postnat` VARCHAR (255),
                ADD `poids_postnat` INT (11),
                ADD `date_deces_postnat` DATE,
                ADD `allaitement_postnat` ENUM ('amexclu','ampart','art'),
                ADD `arret_allaitement_postnat` DATE,
                ADD `nb_semaines_allaitement_postnat` INT (11),
                ADD `motif_arret_allaitement_postnat` VARCHAR (255),
                ADD `complement_eau_postnat` ENUM ('0','1'),
                ADD `complement_eau_sucree_postnat` ENUM ('0','1'),
                ADD `complement_prepa_lactee_postnat` ENUM ('0','1'),
                ADD `complement_tasse_postnat` ENUM ('0','1'),
                ADD `complement_cuillere_postnat` ENUM ('0','1'),
                ADD `complement_biberon_postnat` ENUM ('0','1'),
                ADD `indication_complement_postnat` VARCHAR (255);";
    $this->addQuery($query);
    $query = "ALTER TABLE `naissance` 
                ADD INDEX (`date_deces_postnat`),
                ADD INDEX (`arret_allaitement_postnat`);
";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat`
                ADD `date_consult_postnatale` DATE,
                ADD `consultant_consult_postnatale_id` INT (11) UNSIGNED,
                ADD `patho_postacc_consult_postnatale` ENUM ('0','1'),
                ADD `hospi_postacc_consult_postnatale` ENUM ('0','1'),
                ADD `date_hospi_postacc_consult_postnatale` DATE,
                ADD `duree_hospi_postacc_consult_postnatale` INT (11),
                ADD `motif_hospi_postacc_consult_postnatale` VARCHAR (255),
                ADD `troubles_fct_consult_postnatale` ENUM ('0','1'),
                ADD `doul_pelv_consult_postnatale` ENUM ('0','1'),
                ADD `pert_urin_consult_postnatale` ENUM ('0','1'),
                ADD `leucorrhees_consult_postnatale` ENUM ('0','1'),
                ADD `pertes_gaz_consult_postnatale` ENUM ('0','1'),
                ADD `metrorragies_consult_postnatale` ENUM ('0','1'),
                ADD `pertes_fecales_consult_postnatale` ENUM ('0','1'),
                ADD `compl_episio_consult_postnatale` ENUM ('0','1'),
                ADD `baby_blues_consult_postnatale` ENUM ('0','1'),
                ADD `autres_troubles_consult_postnatale` ENUM ('0','1'),
                ADD `desc_autres_troubles_consult_postnatale` VARCHAR (255),
                ADD `retour_couches_consult_postnatale` ENUM ('0','1'),
                ADD `date_retour_couches_consult_postnatale` DATE,
                ADD `reprise_rapports_consult_postnatale` ENUM ('0','1'),
                ADD `contraception_consult_postnatale` ENUM ('aucune','piluleop','sterilet','implant','preserv','progest','anneau','autre'),
                ADD `desc_contraception_consult_postnatale` VARCHAR (255),
                ADD `consult_postnatale_constantes_id` INT (11) UNSIGNED,
                ADD `exam_seins_consult_postnatale` ENUM ('norm','anorm'),
                ADD `exam_cic_perin_consult_postnatale` ENUM ('norm','anorm'),
                ADD `exam_cic_cesar_consult_postnatale` ENUM ('norm','anorm'),
                ADD `exam_speculum_consult_postnatale` ENUM ('norm','anorm'),
                ADD `exam_TV_consult_postnatale` ENUM ('norm','anorm'),
                ADD `exam_stat_pelv_consult_postnatale` ENUM ('norm','anorm'),
                ADD `exam_stat_pelv_testing_consult_postnatale` TINYINT (4) UNSIGNED,
                ADD `exam_autre_consult_postnatale` TEXT,
                ADD `exam_conclusion_consult_postnatale` ENUM ('normal','sequelles'),
                ADD `infos_remises_consult_postnatale` TEXT,
                ADD `exam_comp_FCV_consjult_postnatale` ENUM ('0','1'),
                ADD `exam_comp_biologie_consult_postnatale` ENUM ('0','1'),
                ADD `exam_comp_autre_consult_postnatale` ENUM ('0','1'),
                ADD `exam_comp_autre_desc_consult_postnatale` VARCHAR (255),
                ADD `reeduc_consult_postnatale` ENUM ('0','1'),
                ADD `reeduc_perin_consult_postnatale` ENUM ('0','1'),
                ADD `reeduc_abdo_consult_postnatale` ENUM ('0','1'),
                ADD `reeduc_autre_consult_postnatale` ENUM ('0','1'),
                ADD `reeduc_autre_desc_consult_postnatale` VARCHAR (255),
                ADD `contraception_presc_consult_postnatale` ENUM ('aucune','pilule','preserv','progest','implant','anneau','sterilet','autre'),
                ADD `autre_contraception_presc_consult_postnatale` VARCHAR (255),
                ADD `arret_travail_consult_postnatale` ENUM ('0','1');";
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD INDEX (`date_consult_postnatale`),
                ADD INDEX (`consultant_consult_postnatale_id`),
                ADD INDEX (`date_hospi_postacc_consult_postnatale`),
                ADD INDEX (`date_retour_couches_consult_postnatale`),
                ADD INDEX (`consult_postnatale_constantes_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.47");
    $this->addQuery($query);
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `score_bishop_dilatation` ENUM ('0','1','2','3') AFTER `score_bishop`,
                ADD `score_bishop_longueur` ENUM ('0','1','2','3') AFTER `score_bishop_dilatation`,
                ADD `score_bishop_consistance` ENUM ('0','1','2') AFTER `score_bishop_longueur`,
                ADD `score_bishop_position` ENUM ('0','1','2') AFTER `score_bishop_consistance`,
                ADD `score_bishop_presentation` ENUM ('0','1','2','3') AFTER `score_bishop_position`;";
    $this->addQuery($query);

    $this->makeRevision("0.48");
    $query = "ALTER TABLE `surv_echo_grossesse` 
                ADD `num_enfant` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.49");
    $query = "ALTER TABLE `grossesse` 
                ADD `datetime_debut_surv_post_partum` DATETIME,
                ADD `datetime_fin_surv_post_partum` DATETIME;";
    $this->addQuery($query);
    $query = "ALTER TABLE `grossesse` 
                ADD INDEX (`datetime_debut_surv_post_partum`),
                ADD INDEX (`datetime_fin_surv_post_partum`);";
    $this->addQuery($query);

    $this->makeRevision("0.50");
    $query = "ALTER TABLE `surv_echo_grossesse` 
                ADD `type_echo` ENUM ('t1','t2','t3','autre') DEFAULT 'autre';";
    $this->addQuery($query);

    $this->makeRevision("0.51");
    $query = "CREATE TABLE `depistage_grossesse_custom` (
                `depistage_grossesse_custom_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `depistage_grossesse_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255),
                `valeur` TEXT
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `depistage_grossesse_custom` 
                ADD INDEX (`depistage_grossesse_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.52");
    $query = "ALTER TABLE `dossier_perinat`
                ADD `niveau_alerte_cesar` ENUM ('1','2','3') AFTER `conduite_a_tenir_acc`;";
    $this->addQuery($query);

    $this->makeRevision("0.53");
    $query = "ALTER TABLE `suivi_grossesse` 
      CHANGE `type_suivi` `type_suivi` ENUM ('surv','urg','htp','autre') DEFAULT 'surv';";
    $this->addQuery($query);
    $this->makeRevision("0.54");

    $query = "ALTER TABLE `depistage_grossesse` 
                ADD `rques_immuno` TEXT,
                ADD `rques_serologie` TEXT,
                ADD `rques_biochimie` TEXT,
                ADD `rques_bacteriologie` TEXT,
                ADD `rques_hemato` TEXT;";
    $this->addQuery($query);
    $this->makeRevision("0.55");

    $query = "CREATE TABLE `consultation_post_natale` (
                `consultation_post_natale_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `dossier_perinat_id` INT (11) UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `consultant_id` INT (11) UNSIGNED NOT NULL,
                `patho_postacc` ENUM ('0','1') DEFAULT '0',
                `hospi_postacc` ENUM ('0','1') DEFAULT '0',
                `date_hospi_postacc` DATE,
                `duree_hospi_postacc` INT (11),
                `motif_hospi_postacc` VARCHAR (255),
                `troubles_fct` ENUM ('0','1') DEFAULT '0',
                `doul_pelv` ENUM ('0','1') DEFAULT '0',
                `pert_urin` ENUM ('0','1') DEFAULT '0',
                `leucorrhees` ENUM ('0','1') DEFAULT '0',
                `pertes_gaz` ENUM ('0','1') DEFAULT '0',
                `metrorragies` ENUM ('0','1') DEFAULT '0',
                `pertes_fecales` ENUM ('0','1') DEFAULT '0',
                `compl_episio` ENUM ('0','1') DEFAULT '0',
                `baby_blues` ENUM ('0','1') DEFAULT '0',
                `autres_troubles` ENUM ('0','1') DEFAULT '0',
                `desc_autres_troubles` VARCHAR (255),
                `retour_couches` ENUM ('0','1') DEFAULT '0',
                `date_retour_couches` DATE,
                `reprise_rapports` ENUM ('0','1') DEFAULT '0',
                `contraception` ENUM ('aucune','piluleop','sterilet','implant','preserv','progest','anneau','autre'),
                `desc_contraception` VARCHAR (255),
                `exam_seins` ENUM ('norm','anorm'),
                `exam_cic_perin` ENUM ('norm','anorm'),
                `exam_cic_cesar` ENUM ('norm','anorm'),
                `exam_speculum` ENUM ('norm','anorm'),
                `exam_TV` ENUM ('norm','anorm'),
                `exam_stat_pelv` ENUM ('norm','anorm'),
                `exam_stat_pelv_testing` TINYINT (4) UNSIGNED,
                `exam_autre` TEXT,
                `exam_conclusion` ENUM ('normal','sequelles'),
                `infos_remises` TEXT,
                `exam_comp_FCV` ENUM ('0','1') DEFAULT '0',
                `exam_comp_biologie` ENUM ('0','1') DEFAULT '0',
                `exam_comp_autre` ENUM ('0','1') DEFAULT '0',
                `exam_comp_autre_desc` VARCHAR (255),
                `reeduc` ENUM ('0','1') DEFAULT '0',
                `reeduc_perin` ENUM ('0','1') DEFAULT '0',
                `reeduc_abdo` ENUM ('0','1') DEFAULT '0',
                `reeduc_autre` ENUM ('0','1') DEFAULT '0',
                `reeduc_autre_desc` VARCHAR (255),
                `contraception_presc` ENUM ('aucune','pilule','preserv','progest','implant','anneau','sterilet','autre'),
                `autre_contraception_presc` VARCHAR (255),
                `arret_travail` ENUM ('0','1') DEFAULT '0',
                INDEX (`dossier_perinat_id`),
                INDEX (`date`),
                INDEX (`consultant_id`),
                INDEX (`date_hospi_postacc`),
                INDEX (`date_retour_couches`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "CREATE TABLE `consultation_enfant` (
                `consultation_post_enfant_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `consultation_post_natale_id` INT (11) UNSIGNED NOT NULL,
                `naissance_id` INT (11) UNSIGNED NOT NULL,
                `enfant_present` ENUM ('0','1') DEFAULT '0',
                `etat_enfant` ENUM ('ok','surv','hosp','deces'),
                `rehospitalisation` ENUM ('0','1') DEFAULT '0',
                `motif_rehospitalisation` VARCHAR (255),
                `poids` INT (11),
                `date_deces` DATE,
                `allaitement` ENUM ('amexclu','ampart','art'),
                `arret_allaitement` DATE,
                `nb_semaines_allaitement` INT (11),
                `motif_arret_allaitement` VARCHAR (255),
                `complement_eau` ENUM ('0','1') DEFAULT '0',
                `complement_eau_sucree` ENUM ('0','1') DEFAULT '0',
                `complement_prepa_lactee` ENUM ('0','1') DEFAULT '0',
                `complement_tasse` ENUM ('0','1') DEFAULT '0',
                `complement_cuillere` ENUM ('0','1') DEFAULT '0',
                `complement_biberon` ENUM ('0','1') DEFAULT '0',
                `indication_complement` VARCHAR (255),
                INDEX (`consultation_post_natale_id`),
                INDEX (`naissance_id`),
                INDEX (`date_deces`),
                INDEX (`arret_allaitement`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $this->makeRevision("0.56");

    $query = "INSERT INTO `consultation_post_natale` (`dossier_perinat_id`, `date`, `consultant_id`, `patho_postacc`, `hospi_postacc`,
                `date_hospi_postacc`, `duree_hospi_postacc`, `motif_hospi_postacc`, `troubles_fct`, `doul_pelv`, `pert_urin`,
                `leucorrhees`, `pertes_gaz`, `metrorragies`, `pertes_fecales`, `compl_episio`, `baby_blues`, `autres_troubles`,
                `desc_autres_troubles`, `retour_couches`, `date_retour_couches`, `reprise_rapports`, `contraception`,
                `desc_contraception`, `exam_seins`, `exam_cic_perin`, `exam_cic_cesar`, `exam_speculum`, `exam_TV`, `exam_stat_pelv`,
                `exam_stat_pelv_testing`, `exam_autre`, `exam_conclusion`, `infos_remises`, `exam_comp_FCV`, `exam_comp_biologie`,
                `exam_comp_autre`, `exam_comp_autre_desc`, `reeduc`, `reeduc_perin`, `reeduc_abdo`, `reeduc_autre`,
                `reeduc_autre_desc`, `contraception_presc`, `autre_contraception_presc`, `arret_travail`) 
            SELECT d.dossier_perinat_id, d.date_consult_postnatale, d.consultant_consult_postnatale_id,
            d.patho_postacc_consult_postnatale, d.hospi_postacc_consult_postnatale, d.date_hospi_postacc_consult_postnatale,
            d.duree_hospi_postacc_consult_postnatale, d.motif_hospi_postacc_consult_postnatale, d.troubles_fct_consult_postnatale,
            d.doul_pelv_consult_postnatale, d.pert_urin_consult_postnatale, d.leucorrhees_consult_postnatale,
            d.pertes_gaz_consult_postnatale, d.metrorragies_consult_postnatale, d.pertes_fecales_consult_postnatale,
            d.compl_episio_consult_postnatale, d.baby_blues_consult_postnatale, d.autres_troubles_consult_postnatale,
            d.desc_autres_troubles_consult_postnatale, d.retour_couches_consult_postnatale, d.date_retour_couches_consult_postnatale,
            d.reprise_rapports_consult_postnatale, d.contraception_consult_postnatale, d.desc_contraception_consult_postnatale,
            d.exam_seins_consult_postnatale, d.exam_cic_perin_consult_postnatale, d.exam_cic_cesar_consult_postnatale,
            d.exam_speculum_consult_postnatale, d.exam_TV_consult_postnatale, d.exam_stat_pelv_consult_postnatale,
            d.exam_stat_pelv_testing_consult_postnatale, d.exam_autre_consult_postnatale, d.exam_conclusion_consult_postnatale,
            d.infos_remises_consult_postnatale, d.exam_comp_FCV_consjult_postnatale, d.exam_comp_biologie_consult_postnatale,
            d.exam_comp_autre_consult_postnatale, d.exam_comp_autre_desc_consult_postnatale, d.reeduc_consult_postnatale,
            d.reeduc_perin_consult_postnatale, d.reeduc_abdo_consult_postnatale, d.reeduc_autre_consult_postnatale,
            d.reeduc_autre_desc_consult_postnatale, d.contraception_presc_consult_postnatale,
            d.autre_contraception_presc_consult_postnatale, d.arret_travail_consult_postnatale
            FROM dossier_perinat d
            WHERE d.date_consult_postnatale IS NOT NULL
            AND d.consultant_consult_postnatale_id IS NOT NULL;";
    $this->addQuery($query);

    $query = "INSERT INTO `consultation_enfant` (`consultation_post_natale_id`, `naissance_id`, `enfant_present`, `etat_enfant`,
                `rehospitalisation`, `motif_rehospitalisation`, `poids`, `date_deces`, `allaitement`, `arret_allaitement`,
                `nb_semaines_allaitement`, `motif_arret_allaitement`, `complement_eau`, `complement_eau_sucree`,
                `complement_prepa_lactee`, `complement_tasse`, `complement_cuillere`, `complement_biberon`, `indication_complement`) 
            SELECT c.consultation_post_natale_id, n.naissance_id, n.enfant_present_postnat, n.etat_enfant_postnat,
              n.rehospitalisation_postnat, n.motif_rehospitalisation_postnat, n.poids_postnat, n.date_deces_postnat,
              n.allaitement_postnat, n.arret_allaitement_postnat, n.nb_semaines_allaitement_postnat, n.motif_arret_allaitement_postnat,
              n.complement_eau_postnat, n.complement_eau_sucree_postnat, n.complement_prepa_lactee_postnat, n.complement_tasse_postnat,
              n.complement_cuillere_postnat, n.complement_biberon_postnat, n.indication_complement_postnat
            FROM dossier_perinat d
            LEFT JOIN consultation_post_natale c ON c.dossier_perinat_id = d.dossier_perinat_id
            LEFT JOIN naissance n ON n.grossesse_id = d.grossesse_id
            WHERE d.date_consult_postnatale IS NOT NULL
            AND d.consultant_consult_postnatale_id IS NOT NULL;";
    $this->addQuery($query);
    $this->makeRevision("0.57");

    $query = "CREATE TABLE `accouchement` (
                `accouchement_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `dossier_perinat_id` INT (11) UNSIGNED NOT NULL,
                `date` DATETIME,
                `sage_femme_resp_id` INT (11) UNSIGNED,
                `medecin_resp_id` INT (11) UNSIGNED,
                `effectue_par_type` ENUM ('med','sf','autre'),
                `effectue_par_type_autre` VARCHAR (255),
                `presentation` ENUM ('sommop','sommos','face','bregma','front','siegecomp','siegdecomp','transv'),
                `moment_rupt_membranes` ENUM ('avant','spont','artif','cesar'),
                `qte_liquide_rupt_membranes` ENUM ('norm','oligoa','hydroa','absent'),
                `aspect_liquide_rupt_membranes` ENUM ('clair','meco','sang','teinte','autre'),
                `aspect_liquide_rupt_membranes_desc` VARCHAR (255),
                `aspect_liquide_post_rupt_membranes` ENUM ('clair','meco','sang','teinte','autre'),
                `aspect_liquide_post_rupt_membranes_desc` VARCHAR (255),
                `voie_basse_spont` ENUM ('0','1') DEFAULT '0',
                `pos_voie_basse_spont` ENUM ('decubdors','decublat','vert'),
                `interv_voie_basse` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_forceps` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_ventouse` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_spatules` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_pet_extr_siege` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_grd_extr_siege` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_autre_man_siege` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_man_dyst_epaules` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_man_dyst_epaules_desc` VARCHAR (255),
                `interv_voie_basse_autre_man` ENUM ('0','1') DEFAULT '0',
                `interv_voie_basse_autre_man_desc` VARCHAR (255),
                `interv_voie_basse_motif` ENUM ('mat','foet'),
                `interv_voie_basse_motif_asso` VARCHAR (255),
                `cesar_avt_travail` ENUM ('0','1') DEFAULT '0',
                `cesar_avt_travail_type` ENUM ('prog','urg'),
                `cesar_pdt_travail` ENUM ('0','1') DEFAULT '0',
                `cesar_pdt_travail_type` ENUM ('urg','prog'),
                `cesar_motif` ENUM ('mat','foet'),
                `cesar_motif_asso` VARCHAR (255),
                `endroit_action_interv_voie_basse` ENUM ('detinf','detmoy','detsup','tete'),
                `type_cesar` ENUM ('segtransv','segvert','corpo','segcorpo','vag'),
                `remarques_cesar` TEXT,
                `actes_associes_cesar` ENUM ('0','1') DEFAULT '0',
                `actes_associes_cesar_hysterectomie_hemostase` ENUM ('0','1') DEFAULT '0',
                `actes_associes_cesar_kystectomie_ovarienne` ENUM ('0','1') DEFAULT '0',
                `actes_associes_cesar_myomectomie_unique` ENUM ('0','1') DEFAULT '0',
                `actes_associes_cesar_ste_tubaire` ENUM ('0','1') DEFAULT '0',
                `actes_associes_cesar_interv_gross_abd` ENUM ('0','1') DEFAULT '0',
                `pb_cordon` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_procidence` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_circ_serre` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_noeud_vrai` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_brievete` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_insert_velament` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_autre` ENUM ('0','1') DEFAULT '0',
                `pb_cordon_autre_desc` VARCHAR (255),
                `duree_ouverture_oeuf_jours` INT (11),
                `duree_ouverture_oeuf_heures` INT (11),
                `duree_travail_heures` INT (11),
                `duree_travail_de_5cm_heures` INT (11),
                `duree_deambulation_heures` INT (11),
                `duree_deambulation_minutes` INT (11),
                `duree_entre_dilat_efforts_expuls` INT (11),
                `duree_efforts_expuls` INT (11),
                INDEX (`dossier_perinat_id`),
                INDEX (`date`),
                INDEX (`sage_femme_resp_id`),
                INDEX (`medecin_resp_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $this->makeRevision("0.58");

    $query = "INSERT INTO `accouchement` (`dossier_perinat_id`, `sage_femme_resp_id`, `medecin_resp_id`, `effectue_par_type`,
                `effectue_par_type_autre`, `presentation`, `moment_rupt_membranes`, `qte_liquide_rupt_membranes`,
                `aspect_liquide_rupt_membranes`, `aspect_liquide_rupt_membranes_desc`, `aspect_liquide_post_rupt_membranes`,
                `aspect_liquide_post_rupt_membranes_desc`, `voie_basse_spont`, `pos_voie_basse_spont`, `interv_voie_basse`,
                `interv_voie_basse_forceps`, `interv_voie_basse_ventouse`, `interv_voie_basse_spatules`,
                `interv_voie_basse_pet_extr_siege`, `interv_voie_basse_grd_extr_siege`, `interv_voie_basse_autre_man_siege`,
                `interv_voie_basse_man_dyst_epaules`, `interv_voie_basse_man_dyst_epaules_desc`, `interv_voie_basse_autre_man`,
                `interv_voie_basse_autre_man_desc`, `interv_voie_basse_motif`, `interv_voie_basse_motif_asso`, `cesar_avt_travail`,
                `cesar_avt_travail_type`, `cesar_pdt_travail`, `cesar_pdt_travail_type`, `cesar_motif`, `cesar_motif_asso`,
                `endroit_action_interv_voie_basse`, `type_cesar`, `remarques_cesar`, `actes_associes_cesar`,
                `actes_associes_cesar_hysterectomie_hemostase`, `actes_associes_cesar_kystectomie_ovarienne`,
                `actes_associes_cesar_myomectomie_unique`, `actes_associes_cesar_ste_tubaire`, `actes_associes_cesar_interv_gross_abd`,
                `pb_cordon`, `pb_cordon_procidence`, `pb_cordon_circ_serre`, `pb_cordon_noeud_vrai`, `pb_cordon_brievete`,
                `pb_cordon_insert_velament`, `pb_cordon_autre`, `pb_cordon_autre_desc`, `duree_ouverture_oeuf_jours`,
                `duree_ouverture_oeuf_heures`, `duree_travail_heures`, `duree_travail_de_5cm_heures`, `duree_deambulation_heures`,
                `duree_deambulation_minutes`, `duree_entre_dilat_efforts_expuls`, `duree_efforts_expuls`)
            SELECT d.dossier_perinat_id, d.sage_femme_resp_acct_id, d.medecin_resp_acct_id, d.acct_effectue_par_type,
             d.acct_effectue_par_type_autre, d.presentation_acct, d.moment_rupt_membranes, d.qte_liquide_rupt_membranes,
             d.aspect_liquide_rupt_membranes, d.aspect_liquide_rupt_membranes_desc, d.aspect_liquide_post_rupt_membranes,
             d.aspect_liquide_post_rupt_membranes_desc, d.acct_voie_basse_spont, d.pos_acct_voie_basse_spont, d.acct_interv_voie_basse,
             d.acct_interv_voie_basse_forceps, d.acct_interv_voie_basse_ventouse, d.acct_interv_voie_basse_spatules,
             d.acct_interv_voie_basse_pet_extr_siege, d.acct_interv_voie_basse_grd_extr_siege,
             d.acct_interv_voie_basse_autre_man_siege, d.acct_interv_voie_basse_man_dyst_epaules,
             d.acct_interv_voie_basse_man_dyst_epaules_desc, d.acct_interv_voie_basse_autre_man,
             d.acct_interv_voie_basse_autre_man_desc, d.acct_interv_voie_basse_motif, d.acct_interv_voie_basse_motif_asso,
             d.acct_cesar_avt_travail, d.acct_cesar_avt_travail_type, d.acct_cesar_pdt_travail, d.acct_cesar_pdt_travail_type,
             d.acct_cesar_motif, d.acct_cesar_motif_asso, d.endroit_action_interv_voie_basse, d.type_cesar, d.remarques_cesar,
             d.actes_associes_cesar, d.actes_associes_cesar_hysterectomie_hemostase, d.actes_associes_cesar_kystectomie_ovarienne,
             d.actes_associes_cesar_myomectomie_unique, d.actes_associes_cesar_ste_tubaire, d.actes_associes_cesar_interv_gross_abd,
             d.acct_pb_cordon, d.acct_pb_cordon_procidence, d.acct_pb_cordon_circ_serre, d.acct_pb_cordon_noeud_vrai,
             d.acct_pb_cordon_brievete, d.acct_pb_cordon_insert_velament, d.acct_pb_cordon_autre, d.acct_pb_cordon_autre_desc,
             d.duree_ouverture_oeuf_jours, d.duree_ouverture_oeuf_heures, d.duree_travail_heures, d.duree_travail_de_5cm_heures,
             d.duree_deambulation_heures, d.duree_deambulation_minutes, d.duree_entre_dilat_efforts_expuls, d.duree_efforts_expuls
            FROM dossier_perinat d
            WHERE d.dossier_perinat_id IS NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.59");
    $query = "ALTER TABLE `suivi_grossesse` 
                ADD `hypertension` ENUM ('0','1'),
                ADD `dilatation_col_num` INT (11),
                ADD `col_commentaire` TEXT,
                ADD `hauteur_uterine` INT (11),
                ADD `glycosurie` ENUM ('positif','negatif'),
                ADD `leucocyturie` ENUM ('positif','negatif'),
                ADD `albuminurie` ENUM ('positif','negatif'),
                ADD `nitrites` ENUM ('positif','negatif');";
    $this->addQuery($query);

    $this->makeRevision("0.60");
    $query = "ALTER TABLE `examen_nouveau_ne` 
                ADD `oreille_droite` ENUM ('positif','negatif') AFTER `test_audition`,
                ADD `oreille_gauche` ENUM ('positif','negatif') AFTER `oreille_droite`, 
                ADD `rdv_orl` DATE AFTER `oreille_gauche`,
                ADD `guthrie_envoye` ENUM ('0','1'), 
                ADD INDEX (`rdv_orl`),
                ADD INDEX (`examinateur_id`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `dossier_perinat` 
                ADD `info_lien_pmi` ENUM ('0','1') AFTER `info_orga_reseau`;";
    $this->addQuery($query);

    $this->makeRevision("0.61");

    $this->addDefaultConfig("maternite general days_terme", "maternite days_terme");
    $this->addDefaultConfig("maternite general duree_sejour", "maternite duree_sejour");
    $this->addDefaultConfig("maternite CGrossesse date_regles_obligatoire");
    $this->addDefaultConfig("maternite CGrossesse manage_provisoire");
    $this->addDefaultConfig("maternite CGrossesse audipog");

    $this->makeRevision("0.62");
    $query = "ALTER TABLE `examen_nouveau_ne` 
                ADD `guthrie_datetime` DATETIME AFTER `guthrie_envoye`,
                ADD `guthrie_user_id` INT UNSIGNED AFTER `guthrie_datetime`,
                ADD INDEX (`guthrie_datetime`),
                ADD INDEX (`guthrie_user_id`);";
    $this->addQuery($query);
    $this->makeRevision("0.63");

    $query = "ALTER TABLE `dossier_perinat`
      DROP `date_consult_postnatale`,
      DROP `consultant_consult_postnatale_id`,
      DROP `patho_postacc_consult_postnatale`,
      DROP `hospi_postacc_consult_postnatale`,
      DROP `date_hospi_postacc_consult_postnatale`,
      DROP `duree_hospi_postacc_consult_postnatale`,
      DROP `motif_hospi_postacc_consult_postnatale`,
      DROP `troubles_fct_consult_postnatale`,
      DROP `doul_pelv_consult_postnatale`,
      DROP `pert_urin_consult_postnatale`,
      DROP `leucorrhees_consult_postnatale`,
      DROP `pertes_gaz_consult_postnatale`,
      DROP `metrorragies_consult_postnatale`,
      DROP `pertes_fecales_consult_postnatale`,
      DROP `compl_episio_consult_postnatale`,
      DROP `baby_blues_consult_postnatale`,
      DROP `autres_troubles_consult_postnatale`,
      DROP `desc_autres_troubles_consult_postnatale`,
      DROP `retour_couches_consult_postnatale`,
      DROP `date_retour_couches_consult_postnatale`,
      DROP `reprise_rapports_consult_postnatale`,
      DROP `contraception_consult_postnatale`,
      DROP `desc_contraception_consult_postnatale`,
      DROP `consult_postnatale_constantes_id`,
      DROP `exam_seins_consult_postnatale`,
      DROP `exam_cic_perin_consult_postnatale`,
      DROP `exam_cic_cesar_consult_postnatale`,
      DROP `exam_speculum_consult_postnatale`,
      DROP `exam_TV_consult_postnatale`,
      DROP `exam_stat_pelv_consult_postnatale`,
      DROP `exam_stat_pelv_testing_consult_postnatale`,
      DROP `exam_autre_consult_postnatale`,
      DROP `exam_conclusion_consult_postnatale`,
      DROP `infos_remises_consult_postnatale`,
      DROP `exam_comp_FCV_consjult_postnatale`,
      DROP `exam_comp_biologie_consult_postnatale`,
      DROP `exam_comp_autre_consult_postnatale`,
      DROP `exam_comp_autre_desc_consult_postnatale`,
      DROP `reeduc_consult_postnatale`,
      DROP `reeduc_perin_consult_postnatale`,
      DROP `reeduc_abdo_consult_postnatale`,
      DROP `reeduc_autre_consult_postnatale`,
      DROP `reeduc_autre_desc_consult_postnatale`,
      DROP `contraception_presc_consult_postnatale`,
      DROP `autre_contraception_presc_consult_postnatale`,
      DROP `arret_travail_consult_postnatale`,
      DROP `sage_femme_resp_acct_id`,
      DROP `medecin_resp_acct_id`,
      DROP `acct_effectue_par_type`,
      DROP `acct_effectue_par_type_autre`,
      DROP `presentation_acct`,
      DROP `moment_rupt_membranes`,
      DROP `qte_liquide_rupt_membranes`,
      DROP `aspect_liquide_rupt_membranes`,
      DROP `aspect_liquide_rupt_membranes_desc`,
      DROP `aspect_liquide_post_rupt_membranes`,
      DROP `aspect_liquide_post_rupt_membranes_desc`,
      DROP `acct_voie_basse_spont`,
      DROP `pos_acct_voie_basse_spont`,
      DROP `acct_interv_voie_basse`,
      DROP `acct_interv_voie_basse_forceps`,
      DROP `acct_interv_voie_basse_ventouse`,
      DROP `acct_interv_voie_basse_spatules`,
      DROP `acct_interv_voie_basse_pet_extr_siege`,
      DROP `acct_interv_voie_basse_grd_extr_siege`,
      DROP `acct_interv_voie_basse_autre_man_siege`,
      DROP `acct_interv_voie_basse_man_dyst_epaules`,
      DROP `acct_interv_voie_basse_man_dyst_epaules_desc`,
      DROP `acct_interv_voie_basse_autre_man`,
      DROP `acct_interv_voie_basse_autre_man_desc`,
      DROP `acct_interv_voie_basse_motif`,
      DROP `acct_interv_voie_basse_motif_asso`,
      DROP `acct_cesar_avt_travail`,
      DROP `acct_cesar_avt_travail_type`,
      DROP `acct_cesar_pdt_travail`,
      DROP `acct_cesar_pdt_travail_type`,
      DROP `acct_cesar_motif`,
      DROP `acct_cesar_motif_asso`,
      DROP `endroit_action_interv_voie_basse`,
      DROP `type_cesar`,
      DROP `remarques_cesar`,
      DROP `actes_associes_cesar`,
      DROP `actes_associes_cesar_hysterectomie_hemostase`,
      DROP `actes_associes_cesar_kystectomie_ovarienne`,
      DROP `actes_associes_cesar_myomectomie_unique`,
      DROP `actes_associes_cesar_ste_tubaire`,
      DROP `actes_associes_cesar_interv_gross_abd`,
      DROP `acct_pb_cordon`,
      DROP `acct_pb_cordon_procidence`,
      DROP `acct_pb_cordon_circ_serre`,
      DROP `acct_pb_cordon_noeud_vrai`,
      DROP `acct_pb_cordon_brievete`,
      DROP `acct_pb_cordon_insert_velament`,
      DROP `acct_pb_cordon_autre`,
      DROP `acct_pb_cordon_autre_desc`,
      DROP `duree_ouverture_oeuf_jours`,
      DROP `duree_ouverture_oeuf_heures`,
      DROP `duree_travail_heures`,
      DROP `duree_travail_de_5cm_heures`,
      DROP `duree_deambulation_heures`,
      DROP `duree_deambulation_minutes`,
      DROP `duree_entre_dilat_efforts_expuls`,
      DROP `duree_efforts_expuls`;";
    $this->addQuery($query);

    $this->makeRevision("0.64");
    $query = "ALTER TABLE `surv_echo_grossesse` 
                CHANGE `cn` `cn` FLOAT,
                ADD `poids_foetal` FLOAT,
                ADD `avis_dan` ENUM ('0','1') DEFAULT '0',
                ADD `pos_placentaire` TEXT,
                ADD `bcba` INT (11),
                ADD `mcma` INT (11),
                ADD `mcba` INT (11),
                DROP `morphologie`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `grossesse` 
                ADD `nb_foetus` INT (11) UNSIGNED DEFAULT '1' AFTER `multiple`;";
    $this->addQuery($query);
    $this->makeRevision("0.65");

    $query = "ALTER TABLE `naissance`
                DROP `enfant_present_postnat`,
                DROP `etat_enfant_postnat`,
                DROP `rehospitalisation_postnat`,
                DROP `motif_rehospitalisation_postnat`,
                DROP `poids_postnat`,
                DROP `date_deces_postnat`,
                DROP `allaitement_postnat`,
                DROP `arret_allaitement_postnat`,
                DROP `nb_semaines_allaitement_postnat`,
                DROP `motif_arret_allaitement_postnat`,
                DROP `complement_eau_postnat`,
                DROP `complement_eau_sucree_postnat`,
                DROP `complement_prepa_lactee_postnat`,
                DROP `complement_tasse_postnat`,
                DROP `complement_cuillere_postnat`,
                DROP `complement_biberon_postnat`,
                DROP `indication_complement_postnat`;";
    $this->addQuery($query);

    $this->makeRevision("0.66");
    $this->setModuleCategory("plateau_technique", "metier");

    $this->makeRevision("0.67");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `facteur_risque` TEXT AFTER `rques_conduite_a_tenir`;";
    $this->addQuery($query);

    $this->makeRevision("0.68");
    $query = "ALTER TABLE `grossesse` 
                ADD `datetime_cloture` DATETIME AFTER `active`, 
                ADD INDEX (`datetime_cloture`);";
    $this->addQuery($query);

    $this->makeRevision("0.69");
    $query = "ALTER TABLE `depistage_grossesse` 
                ADD `aci` ENUM ('pos','neg'),
                ADD `rques_aci` TEXT,
                ADD `test_kleihauer` ENUM ('pos','neg'),
                ADD `val_kleihauer` FLOAT UNSIGNED,
                ADD `varicelle` ENUM ('nim','im','in'),
                ADD `parvovirus` ENUM ('neg','pos','in'),
                ADD `TPHA` ENUM ('TPHA','vrdl','BW'),
                ADD `vrdl` ENUM ('neg','pos'),
                ADD `hb` FLOAT UNSIGNED,
                ADD `strepto_b` ENUM ('neg','pos','in'),
                ADD `parasitobacteriologique` ENUM ('neg','pos','in'),
                ADD `amniocentese` FLOAT,
                CHANGE `pv` `pvc` TEXT,
                ADD `dpni` ENUM ('neg','pos','in'),
                ADD `dpni_rques` TEXT,
                ADD `cbu` TEXT,
                ADD `glycosurie` FLOAT,
                ADD `albuminerie` FLOAT,
                ADD `albuminerie_24` FLOAT,
                ADD `t21` FLOAT,
                ADD `pappa` FLOAT,
                ADD `hcg1` FLOAT,
                ADD `rques_t1` TEXT,
                ADD `afp` FLOAT,
                ADD `hcg2` FLOAT,
                ADD `estriol` FLOAT,
                ADD `rques_t2` TEXT,
                ADD `gr` FLOAT,
                ADD `gb` FLOAT,
                ADD `ferritine` FLOAT UNSIGNED,
                ADD `fg` FLOAT UNSIGNED,
                ADD `vgm` FLOAT UNSIGNED,
                ADD `glycemie` FLOAT UNSIGNED,
                ADD `acide_urique` FLOAT,
                ADD `asat` FLOAT,
                ADD `alat` FLOAT,
                ADD `creatininemie` FLOAT,
                ADD `phosphatase` FLOAT,
                ADD `brb` FLOAT,
                ADD `unite_brb` ENUM ('mgL','mmolL') DEFAULT 'mgL',
                ADD `sel_biliaire` FLOAT,
                ADD `rhesus_bb` ENUM ('pos','neg');";
    $this->addQuery($query);

    $this->makeRevision("0.70");
    $query = "ALTER TABLE `depistage_grossesse` 
            CHANGE `rubeole` `rubeole` ENUM ('nim','im','in','douteux');";
    $this->addQuery($query);

    $this->makeRevision("0.71");
    $query = "ALTER TABLE `depistage_grossesse` 
                ADD `rques_vaginal` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.72");
    $query = "ALTER TABLE `examen_nouveau_ne` 
                ADD `administration_id` INT (11) UNSIGNED,
                ADD INDEX (`administration_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.73");
    $query = "ALTER TABLE `naissance` 
                ADD `type_allaitement` ENUM ('maternel','artificiel','mixte');";
    $this->addQuery($query);

    $this->makeRevision("0.74");
    $query = "ALTER TABLE `depistage_grossesse` 
                ADD `genotypage` ENUM ('nonfait','fait','controle'),
                ADD `date_genotypage` DATE,
                ADD `rques_genotypage` TEXT,
                ADD `rhophylac` ENUM ('nonfait','fait'),
                ADD `date_rhophylac` DATE,
                ADD `quantite_rhophylac` FLOAT UNSIGNED,
                ADD `rques_rhophylac` TEXT,
                ADD `datetime_1_determination` DATETIME,
                ADD `datetime_2_determination` DATETIME, 
                ADD INDEX (`date_genotypage`),
                ADD INDEX (`date_rhophylac`),
                ADD INDEX (`datetime_1_determination`),
                ADD INDEX (`datetime_2_determination`);";
    $this->addQuery($query);

    $this->makeRevision("0.75");
    $query = "ALTER TABLE `allaitement` 
                ADD `antecedent_id` INT (11) UNSIGNED,
                ADD INDEX (`antecedent_id`);";
    $this->addQuery($query);

        $this->makeRevision("0.76");

    $query = "ALTER TABLE `dossier_perinat` 
                ADD `subst_debut_grossesse_text` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.77");
    $query = "ALTER TABLE `grossesse` 
                ADD `date_fin_grossesse` DATETIME";
    $this->addQuery($query);

    $this->makeRevision("0.78");
    $query = "ALTER TABLE `grossesse` 
                DROP COLUMN `date_fin_grossesse`";
    $this->addQuery($query);

    $this->makeRevision("0.79");
    $query = "ALTER TABLE `dossier_perinat` 
                ADD `reco_brochure` ENUM ('0','1');";
    $this->addQuery($query);

    $this->makeRevision('0.80');

    $this->addQuery("ALTER TABLE `examen_nouveau_ne` 
                ADD `commentaire` TEXT;");

    $this->makeRevision('0.81');

    $this->addQuery(
        "CREATE TABLE `naissance_rea` (
              `naissance_rea_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `rea_par` ENUM ('sf','ped','anesth','samu','obst'),
              `rea_par_id` INT (11) UNSIGNED NOT NULL,
              `naissance_id` INT (11) UNSIGNED NOT NULL,
              INDEX (`rea_par_id`),
              INDEX (`naissance_id`)
            )/*! ENGINE=MyISAM */;"
    );

    $this->addQuery(
        "INSERT INTO `naissance_rea` (`rea_par`, `rea_par_id`, `naissance_id`) 
        SELECT `rea_par`, `rea_par_id`, `naissance_id` FROM `naissance` WHERE (`rea_par_id` IS NOT NULL OR `rea_par` IS NOT NULL);"
    );

    $this->addQuery("ALTER TABLE `naissance` DROP COLUMN `rea_par`, DROP COLUMN  `rea_par_id`;");

    $this->makeRevision('0.82');

    $this->addQuery("ALTER TABLE `naissance_rea` CHANGE `rea_par` `rea_par` ENUM ('sf','ped','anesth','samu','obst','ide','aux');");

    $this->makeRevision('0.83');

    $this->addQuery(
        "ALTER TABLE `depistage_grossesse` 
                CHANGE `rhesus_bb` `rhesus_bb` ENUM ('pos','neg','indetermine');"
    );

    $this->makeRevision('0.84');
    $this->addQuery(
        "ALTER TABLE `grossesse`
                ADD `estimate_first_ultrasound_date` DATE,
                ADD `estimate_second_ultrasound_date` DATE,
                ADD `estimate_third_ultrasound_date` DATE,
                ADD `estimate_sick_leave_date` DATE,
                ADD INDEX (`estimate_first_ultrasound_date`),
                ADD INDEX (`estimate_second_ultrasound_date`),
                ADD INDEX (`estimate_third_ultrasound_date`),
                ADD INDEX (`estimate_sick_leave_date`);"
    );

    $this->mod_version = '0.85';
  }
}
