<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Gestion du dossier de périnatalité d'une grossesse
 */
class CDossierPerinat extends CMbObject
{
    public const FIELDS_MODELE_SKIPPED = [
        "_shortview",
        "_view"
    ];

    // DB Table key
    public $dossier_perinat_id;

    public $grossesse_id;

    // Renseignements socio-demographiques
    public $activite_pro;
    public $activite_pro_pere;
    public $fatigue_travail;
    public $travail_hebdo;
    public $transport_jour;

    // Contexte psycho-social
    public $rques_social;
    public $enfants_foyer;
    public $situation_part_enfance;
    public $spe_perte_parent;
    public $spe_maltraitance;
    public $spe_mere_placee_enfance;
    public $situation_part_adolescence;
    public $spa_anorexie_boulimie;
    public $spa_depression;
    public $situation_part_familiale;
    public $spf_violences_conjugales;
    public $spf_mere_isolee;
    public $spf_absence_entourage_fam;
    public $stress_agression;
    public $sa_agression_physique;
    public $sa_agression_sexuelle;
    public $sa_harcelement_travail;
    public $rques_psychologie;
    public $situation_accompagnement;
    public $rques_accompagnement;

    // Consommation de produits toxiques
    public $tabac_avant_grossesse;
    public $qte_tabac_avant_grossesse;
    public $tabac_debut_grossesse;
    public $qte_tabac_debut_grossesse;
    public $alcool_debut_grossesse;
    public $qte_alcool_debut_grossesse;
    public $canabis_debut_grossesse;
    public $qte_canabis_debut_grossesse;
    public $subst_avant_grossesse;
    public $mode_subst_avant_grossesse;
    public $nom_subst_avant_grossesse;
    public $subst_subst_avant_grossesse;
    public $subst_debut_grossesse;
    public $subst_debut_grossesse_text;
    public $tabac_pere;
    public $coexp_pere;
    public $alcool_pere;
    public $toxico_pere;
    public $rques_toxico;

    // Antécedents
    public $ant_mater_constantes_id;
    public $pere_constantes_id;
    public $patho_ant;
    public $patho_ant_hta;
    public $patho_ant_diabete;
    public $patho_ant_epilepsie;
    public $patho_ant_asthme;
    public $patho_ant_pulm;
    public $patho_ant_thrombo_emb;
    public $patho_ant_cardio;
    public $patho_ant_auto_immune;
    public $patho_ant_hepato_dig;
    public $patho_ant_thyroide;
    public $patho_ant_uro_nephro;
    public $patho_ant_infectieuse;
    public $patho_ant_hemato;
    public $patho_ant_cancer_non_gyn;
    public $patho_ant_psy;
    public $patho_ant_autre;

    public $chir_ant;
    public $chir_ant_rques;

    public $gyneco_ant_regles;
    public $gyneco_ant_regul_regles;
    public $gyneco_ant_fcv;
    public $gyneco_ant;
    public $gyneco_ant_herpes;
    public $gyneco_ant_lesion_col;
    public $gyneco_ant_conisation;
    public $gyneco_ant_cicatrice_uterus;
    public $gyneco_ant_fibrome;
    public $gyneco_ant_stat_pelv;
    public $gyneco_ant_cancer_sein;
    public $gyneco_ant_cancer_app_genital;
    public $gyneco_ant_malf_genitale;
    public $gyneco_ant_condylomes;
    public $gyneco_ant_distilbene;
    public $gyneco_ant_autre;

    public $gyneco_ant_infert;
    public $gyneco_ant_infert_origine;

    public $pere_serologie_vih;
    public $pere_electrophorese_hb;
    public $pere_patho_ant;
    public $pere_ant_herpes;
    public $pere_ant_autre;

    public $ant_fam;
    public $consanguinite;
    public $ant_fam_mere_gemellite;
    public $ant_fam_pere_gemellite;
    public $ant_fam_mere_malformations;
    public $ant_fam_pere_malformations;
    public $ant_fam_mere_maladie_genique;
    public $ant_fam_pere_maladie_genique;
    public $ant_fam_mere_maladie_chrom;
    public $ant_fam_pere_maladie_chrom;
    public $ant_fam_mere_diabete;
    public $ant_fam_pere_diabete;
    public $ant_fam_mere_hta;
    public $ant_fam_pere_hta;
    public $ant_fam_mere_phlebite;
    public $ant_fam_pere_phlebite;
    public $ant_fam_mere_autre;
    public $ant_fam_pere_autre;

    public $ant_obst_nb_gr_acc;
    public $ant_obst_nb_gr_av_sp;
    public $ant_obst_nb_gr_ivg;
    public $ant_obst_nb_gr_geu;
    public $ant_obst_nb_gr_mole;
    public $ant_obst_nb_gr_img;
    public $ant_obst_nb_gr_amp;
    public $ant_obst_nb_gr_mult;
    public $ant_obst_nb_gr_hta;
    public $ant_obst_nb_gr_map;
    public $ant_obst_nb_gr_diab;
    public $ant_obst_nb_gr_cesar;
    public $ant_obst_nb_gr_prema;

    public $ant_obst_nb_enf_moins_25000;
    public $ant_obst_nb_enf_hypotroph;
    public $ant_obst_nb_enf_macrosome;
    public $ant_obst_nb_enf_morts_nes;
    public $ant_obst_nb_enf_mort_neonat;
    public $ant_obst_nb_enf_mort_postneonat;
    public $ant_obst_nb_enf_malform;

    // Information début de grossesse

    public $souhait_grossesse;
    public $contraception_pre_grossesse;
    public $grossesse_sous_contraception;
    public $rques_contraception;
    public $grossesse_apres_traitement;
    public $type_traitement_grossesse;
    public $origine_ovule;
    public $origine_sperme;
    public $rques_traitement_grossesse;
    public $traitement_peri_conceptionnelle;
    public $type_traitement_peri_conceptionnelle;
    public $arret_traitement_peri_conceptionnelle;
    public $rques_traitement_peri_conceptionnelle;

    // Premier contact
    public $date_premier_contact;
    public $consultant_premier_contact_id;
    public $provenance_premier_contact;
    public $mater_provenance_premier_contact_id;
    public $nivsoins_provenance_premier_contact;
    public $motif_premier_contact;
    public $nb_consult_ant_premier_contact;
    public $sa_consult_ant_premier_contact;
    public $surveillance_ant_premier_contact;
    public $type_surv_ant_premier_contact;
    public $date_declaration_grossesse;
    public $rques_provenance;

    public $reco_aucune;
    public $reco_tabac;
    public $reco_rhesus_negatif;
    public $reco_toxoplasmose;
    public $reco_alcool;
    public $reco_vaccination;
    public $reco_hygiene_alim;
    public $reco_toxicomanie;
    public $reco_brochure;
    public $reco_autre;

    public $souhait_arret_addiction;
    public $souhait_aide_addiction;

    public $info_echographie;
    public $info_despistage_triso21;
    public $test_triso21_propose;
    public $info_orga_maternite;
    public $info_orga_reseau;
    public $info_lien_pmi;

    public $projet_lieu_accouchement;
    public $projet_analgesie_peridurale;
    public $projet_allaitement_maternel;
    public $projet_preparation_naissance;
    public $projet_entretiens_proposes;

    public $bas_risques;
    public $risque_atcd_maternel_med;
    public $risque_atcd_obst;
    public $risque_atcd_familiaux;
    public $risque_patho_mater_grossesse;
    public $risque_patho_foetale_grossesse;
    public $risque_psychosocial_grossesse;
    public $risque_grossesse_multiple;

    public $type_surveillance;
    public $lieu_surveillance;
    public $lieu_accouchement_prevu;
    public $niveau_soins_prevu;
    public $conclusion_premier_contact;

    // Transfert maternel anténatal

    public $transf_antenat;
    public $date_transf_antenat;
    public $lieu_transf_antenat;
    public $etab_transf_antenat;
    public $nivsoins_transf_antenat;
    public $raison_transf_antenat_hors_reseau;
    public $raison_imp_transf_antenat;
    public $motif_tranf_antenat;
    public $type_patho_transf_antenat;
    public $rques_transf_antenat;

    public $mode_transp_transf_antenat;
    public $antibio_transf_antenat;
    public $nom_antibio_transf_antenat;
    public $cortico_transf_antenat;
    public $nom_cortico_transf_antenat;
    public $datetime_cortico_transf_antenat;
    public $tocolytiques_transf_antenat;
    public $nom_tocolytiques_transf_antenat;
    public $antihta_transf_antenat;
    public $nom_antihta_transf_antenat;
    public $autre_ttt_transf_antenat;
    public $nom_autre_ttt_transf_antenat;

    public $retour_mater_transf_antenat;
    public $date_retour_transf_antenat;
    public $devenir_retour_transf_antenat;
    public $rques_retour_transf_antenat;

    // Cloture du dossier sans accouchement

    public $type_terminaison_grossesse;
    public $type_term_hors_etab;
    public $type_term_inf_22sa;

    // Synthèse de la grossesse

    public $date_validation_synthese;
    public $validateur_synthese_id;

    public $nb_consult_total_prenatal;
    public $nb_consult_total_equipe;
    public $entretien_prem_trim;
    public $hospitalisation;
    public $nb_sejours;
    public $nb_total_jours_hospi;
    public $sage_femme_domicile;
    public $transfert_in_utero;
    public $consult_preanesth;
    public $consult_centre_diag_prenat;
    public $preparation_naissance;

    public $conso_toxique_pdt_grossesse;
    public $tabac_pdt_grossesse;
    public $sevrage_tabac_pdt_grossesse;
    public $date_arret_tabac;
    public $alcool_pdt_grossesse;
    public $cannabis_pdt_grossesse;
    public $autres_subst_pdt_grossesse;
    public $type_subst_pdt_grossesse;

    public $profession_pdt_grossesse;
    public $ag_date_arret_travail;
    public $situation_pb_pdt_grossesse;
    public $separation_pdt_grossesse;
    public $deces_fam_pdt_grossesse;
    public $autre_evenement_fam_pdt_grossesse;
    public $perte_emploi_pdt_grossesse;
    public $autre_evenement_soc_pdt_grossesse;

    public $nb_total_echographies;
    public $echo_1er_trim;
    public $resultat_echo_1er_trim;
    public $resultat_autre_echo_1er_trim;
    public $ag_echo_1er_trim;
    public $echo_2e_trim;
    public $resultat_echo_2e_trim;
    public $resultat_autre_echo_2e_trim;
    public $ag_echo_2e_trim;
    public $doppler_2e_trim;
    public $resultat_doppler_2e_trim;
    public $echo_3e_trim;
    public $resultat_echo_3e_trim;
    public $resultat_autre_echo_3e_trim;
    public $ag_echo_3e_trim;
    public $doppler_3e_trim;
    public $resultat_doppler_3e_trim;

    public $prelevements_foetaux;
    public $indication_prelevements_foetaux;
    public $biopsie_trophoblaste;
    public $resultat_biopsie_trophoblaste;
    public $rques_biopsie_trophoblaste;
    public $ag_biopsie_trophoblaste;
    public $amniocentese;
    public $resultat_amniocentese;
    public $rques_amniocentese;
    public $ag_amniocentese;
    public $cordocentese;
    public $resultat_cordocentese;
    public $rques_cordocentese;
    public $ag_cordocentese;
    public $autre_prelevements_foetaux;
    public $rques_autre_prelevements_foetaux;
    public $ag_autre_prelevements_foetaux;

    public $prelevements_bacterio_mater;
    public $prelevement_vaginal;
    public $resultat_prelevement_vaginal;
    public $rques_prelevement_vaginal;
    public $ag_prelevement_vaginal;
    public $prelevement_urinaire;
    public $resultat_prelevement_urinaire;
    public $rques_prelevement_urinaire;
    public $ag_prelevement_urinaire;

    public $marqueurs_seriques;
    public $resultats_marqueurs_seriques;
    public $rques_marqueurs_seriques;

    public $depistage_diabete;
    public $resultat_depistage_diabete;

    public $rai_fin_grossesse;
    public $rubeole_fin_grossesse;
    public $seroconv_rubeole;
    public $ag_seroconv_rubeole;
    public $toxoplasmose_fin_grossesse;
    public $seroconv_toxoplasmose;
    public $ag_seroconv_toxoplasmose;
    public $syphilis_fin_grossesse;
    public $vih_fin_grossesse;
    public $hepatite_b_fin_grossesse;
    public $hepatite_b_aghbspos_fin_grossesse;
    public $hepatite_c_fin_grossesse;
    public $cmvg_fin_grossesse;
    public $cmvm_fin_grossesse;
    public $seroconv_cmv;
    public $ag_seroconv_cmv;
    public $autre_serodiag_fin_grossesse;

    public $pathologie_grossesse;

    public $pathologie_grossesse_maternelle;
    public $metrorragie_1er_trim;
    public $type_metrorragie_1er_trim;
    public $ag_metrorragie_1er_trim;
    public $metrorragie_2e_3e_trim;
    public $type_metrorragie_2e_3e_trim;
    public $ag_metrorragie_2e_3e_trim;
    public $menace_acc_premat;
    public $menace_acc_premat_modif_cerv;
    public $pec_menace_acc_premat;
    public $ag_menace_acc_premat;
    public $ag_hospi_menace_acc_premat;
    public $rupture_premat_membranes;
    public $ag_rupture_premat_membranes;
    public $anomalie_liquide_amniotique;
    public $type_anomalie_liquide_amniotique;
    public $ag_anomalie_liquide_amniotique;
    public $autre_patho_gravidique;
    public $patho_grav_vomissements;
    public $patho_grav_herpes_gest;
    public $patho_grav_dermatose_pup;
    public $patho_grav_placenta_praevia_non_hemo;
    public $patho_grav_chorio_amniotite;
    public $patho_grav_transf_foeto_mat;
    public $patho_grav_beance_col;
    public $patho_grav_cerclage;
    public $ag_autre_patho_gravidique;
    public $hypertension_arterielle;
    public $type_hypertension_arterielle;
    public $ag_hypertension_arterielle;
    public $proteinurie;
    public $type_proteinurie;
    public $ag_proteinurie;
    public $diabete;
    public $type_diabete;
    public $ag_diabete;
    public $infection_urinaire;
    public $type_infection_urinaire;
    public $ag_infection_urinaire;
    public $infection_cervico_vaginale;
    public $type_infection_cervico_vaginale;
    public $autre_infection_cervico_vaginale;
    public $ag_infection_cervico_vaginale;
    public $autre_patho_maternelle;
    public $ag_autre_patho_maternelle;
    public $anemie_mat_pdt_grossesse;
    public $tombopenie_mat_pdt_grossesse;
    public $autre_patho_hemato_mat_pdt_grossesse;
    public $desc_autre_patho_hemato_mat_pdt_grossesse;
    public $faible_prise_poid_mat_pdt_grossesse;
    public $malnut_mat_pdt_grossesse;
    public $autre_patho_endo_mat_pdt_grossesse;
    public $desc_autre_patho_endo_mat_pdt_grossesse;
    public $cholestase_mat_pdt_grossesse;
    public $steatose_hep_mat_pdt_grossesse;
    public $autre_patho_hepato_mat_pdt_grossesse;
    public $desc_autre_patho_hepato_mat_pdt_grossesse;
    public $thrombophl_sup_mat_pdt_grossesse;
    public $thrombophl_prof_mat_pdt_grossesse;
    public $autre_patho_vein_mat_pdt_grossesse;
    public $desc_autre_patho_vein_mat_pdt_grossesse;
    public $asthme_mat_pdt_grossesse;
    public $autre_patho_resp_mat_pdt_grossesse;
    public $desc_autre_patho_resp_mat_pdt_grossesse;
    public $cardiopathie_mat_pdt_grossesse;
    public $autre_patho_cardio_mat_pdt_grossesse;
    public $desc_autre_patho_cardio_mat_pdt_grossesse;
    public $epilepsie_mat_pdt_grossesse;
    public $depression_mat_pdt_grossesse;
    public $autre_patho_neuropsy_mat_pdt_grossesse;
    public $desc_autre_patho_neuropsy_mat_pdt_grossesse;
    public $patho_gyneco_mat_pdt_grossesse;
    public $desc_patho_gyneco_mat_pdt_grossesse;
    public $mst_mat_pdt_grossesse;
    public $desc_mst_mat_pdt_grossesse;
    public $synd_douleur_abdo_mat_pdt_grossesse;
    public $desc_synd_douleur_abdo_mat_pdt_grossesse;
    public $synd_infect_mat_pdt_grossesse;
    public $desc_synd_infect_mat_pdt_grossesse;

    public $therapeutique_grossesse_maternelle;
    public $antibio_pdt_grossesse;
    public $type_antibio_pdt_grossesse;
    public $tocolyt_pdt_grossesse;
    public $mode_admin_tocolyt_pdt_grossesse;
    public $cortico_pdt_grossesse;
    public $nb_cures_cortico_pdt_grossesse;
    public $etat_dern_cure_cortico_pdt_grossesse;
    public $gammaglob_anti_d_pdt_grossesse;
    public $antihyp_pdt_grossesse;
    public $aspirine_a_pdt_grossesse;
    public $barbit_antiepilept_pdt_grossesse;
    public $psychotropes_pdt_grossesse;
    public $subst_nicotine_pdt_grossesse;
    public $autre_therap_mater_pdt_grossesse;
    public $desc_autre_therap_mater_pdt_grossesse;

    public $patho_foetale_in_utero;
    public $anomalie_croiss_intra_uterine;
    public $type_anomalie_croiss_intra_uterine;
    public $ag_anomalie_croiss_intra_uterine;
    public $signes_hypoxie_foetale_chronique;
    public $ag_signes_hypoxie_foetale_chronique;
    public $hypoxie_foetale_anomalie_doppler;
    public $hypoxie_foetale_anomalie_rcf;
    public $hypoxie_foetale_alter_profil_biophy;
    public $anomalie_constit_foetus;
    public $ag_anomalie_constit_foetus;
    public $malformation_isolee_foetus;
    public $anomalie_chromo_foetus;
    public $synd_polymalform_foetus;
    public $anomalie_genique_foetus;
    public $rques_anomalies_foetus;
    public $foetopathie_infect_acquise;
    public $type_foetopathie_infect_acquise;
    public $autre_foetopathie_infect_acquise;
    public $ag_foetopathie_infect_acquise;
    public $autre_patho_foetale;
    public $ag_autre_patho_foetale;
    public $allo_immun_anti_rh_foetale;
    public $autre_allo_immun_foetale;
    public $anas_foeto_plac_non_immun;
    public $trouble_rcf_foetus;
    public $foetopathie_alcoolique;
    public $grosse_abdo_foetus_viable;
    public $mort_foetale_in_utero_in_22sa;
    public $autre_patho_foetale_autre;
    public $desc_autre_patho_foetale_autre;
    public $patho_foetale_gross_mult;
    public $ag_patho_foetale_gross_mult;
    public $avort_foetus_gross_mult;
    public $mort_foetale_in_utero_gross_mutl;
    public $synd_transf_transf_gross_mult;

    public $therapeutique_foetale;
    public $amnioinfusion;
    public $chirurgie_foetale;
    public $derivation_foetale;
    public $tranfusion_foetale_in_utero;
    public $ex_sanguino_transfusion_foetale;
    public $autre_therapeutiques_foetales;
    public $reduction_embryonnaire;
    public $type_reduction_embryonnaire;
    public $photocoag_vx_placentaires;
    public $rques_therapeutique_foetale;

    // Conduite à tenir pour l'accouchement

    public $presentation_fin_grossesse;
    public $autre_presentation_fin_grossesse;
    public $version_presentation_manoeuvre_ext;
    public $rques_presentation_fin_grossesse;
    public $etat_uterus_fin_grossesse;
    public $autre_anomalie_uterus_fin_grossesse;
    public $nb_cicatrices_uterus_fin_grossesse;
    public $date_derniere_hysterotomie;
    public $rques_etat_uterus;
    public $appreciation_clinique_etat_bassin;
    public $desc_appreciation_clinique_etat_bassin;
    public $pelvimetrie;
    public $desc_pelvimetrie;
    public $diametre_transverse_median;
    public $diametre_promonto_retro_pubien;
    public $diametre_bisciatique;
    public $indice_magnin;
    public $date_echo_fin_grossesse;
    public $sa_echo_fin_grossesse;
    public $bip_fin_grossesse;
    public $est_pond_fin_grossesse;
    public $est_pond_2e_foetus_fin_grossesse;
    public $conduite_a_tenir_acc;
    public $niveau_alerte_cesar;
    public $date_decision_conduite_a_tenir_acc;
    public $valid_decision_conduite_a_tenir_acc_id;
    public $motif_conduite_a_tenir_acc;
    public $date_prevue_interv;
    public $rques_conduite_a_tenir;
    public $facteur_risque;

    // Admission
    public $admission_id;
    public $adm_mater_constantes_id;
    public $adm_sage_femme_resp_id;
    public $ag_admission;
    public $ag_jours_admission;
    public $motif_admission;
    public $ag_ruptures_membranes;
    public $ag_jours_ruptures_membranes;
    public $delai_rupture_travail_jours;
    public $delai_rupture_travail_heures;
    public $date_ruptures_membranes;
    public $rques_admission;

    public $exam_entree_oedeme;
    public $exam_entree_bruits_du_coeur;
    public $exam_entree_mvt_actifs_percus;
    public $exam_entree_contractions;
    public $exam_entree_presentation;
    public $exam_entree_col;
    public $exam_entree_liquide_amnio;
    public $exam_entree_indice_bishop;

    public $exam_entree_prelev_urine;
    public $exam_entree_proteinurie;
    public $exam_entree_glycosurie;
    public $exam_entree_prelev_vaginal;
    public $exam_entree_prelev_vaginal_desc;
    public $exam_entree_rcf;
    public $exam_entree_rcf_desc;
    public $exam_entree_amnioscopie;
    public $exam_entree_amnioscopie_desc;
    public $exam_entree_autres;
    public $exam_entree_autres_desc;
    public $rques_exam_entree;

    // Résumé d'accouchement
    public $lieu_accouchement;
    public $autre_lieu_accouchement;
    public $nom_maternite_externe;
    public $lieu_delivrance;
    public $ag_accouchement;
    public $ag_jours_accouchement;
    public $mode_debut_travail;
    public $rques_debut_travail;

    public $datetime_declenchement;
    public $motif_decl_conv;
    public $motif_decl_gross_prol;
    public $motif_decl_patho_mat;
    public $motif_decl_patho_foet;
    public $motif_decl_rpm;
    public $motif_decl_mort_iu;
    public $motif_decl_img;
    public $motif_decl_autre;
    public $motif_decl_autre_details;
    public $moyen_decl_ocyto;
    public $moyen_decl_prosta;
    public $moyen_decl_autre_medic;
    public $moyen_decl_meca;
    public $moyen_decl_rupture;
    public $moyen_decl_autre;
    public $moyen_decl_autre_details;
    public $score_bishop;
    public $score_bishop_dilatation;
    public $score_bishop_longueur;
    public $score_bishop_consistance;
    public $score_bishop_position;
    public $score_bishop_presentation;
    public $remarques_declenchement;

    public $surveillance_travail;
    public $tocographie;
    public $type_tocographie;
    public $anomalie_contractions;
    public $rcf;
    public $type_rcf;
    public $desc_trace_rcf;
    public $anomalie_rcf;
    public $ecg_foetal;
    public $anomalie_ecg_foetal;
    public $prelevement_sang_foetal;
    public $anomalie_ph_sang_foetal;
    public $detail_anomalie_ph_sang_foetal;
    public $valeur_anomalie_ph_sang_foetal;
    public $anomalie_lactates_sang_foetal;
    public $detail_anomalie_lactates_sang_foetal;
    public $valeur_anomalie_lactates_sang_foetal;
    public $oxymetrie_foetale;
    public $anomalie_oxymetrie_foetale;
    public $detail_anomalie_oxymetrie_foetale;
    public $autre_examen_surveillance;
    public $desc_autre_examen_surveillance;
    public $anomalie_autre_examen_surveillance;
    public $rques_surveillance_travail;

    public $therapeutique_pdt_travail;
    public $antibio_pdt_travail;
    public $antihypertenseurs_pdt_travail;
    public $antispasmodiques_pdt_travail;
    public $tocolytiques_pdt_travail;
    public $ocytociques_pdt_travail;
    public $opiaces_pdt_travail;
    public $sedatifs_pdt_travail;
    public $amnioinfusion_pdt_travail;
    public $autre_therap_pdt_travail;
    public $desc_autre_therap_pdt_travail;
    public $rques_therap_pdt_travail;

    public $pathologie_accouchement;
    public $fievre_pdt_travail;
    public $fievre_travail_constantes_id;

    public $anom_av_trav;
    public $anom_pres_av_trav;
    public $anom_pres_av_trav_siege;
    public $anom_pres_av_trav_transverse;
    public $anom_pres_av_trav_face;
    public $anom_pres_av_trav_anormale;
    public $anom_pres_av_trav_autre;
    public $anom_bassin_av_trav;
    public $anom_bassin_av_trav_bassin_retreci;
    public $anom_bassin_av_trav_malform_bassin;
    public $anom_bassin_av_trav_foetus;
    public $anom_bassin_av_trav_disprop_foetopelv;
    public $anom_bassin_av_trav_disprop_difform_foet;
    public $anom_bassin_av_trav_disprop_sans_prec;
    public $anom_genit_hors_trav;
    public $anom_genit_hors_trav_uterus_cicat;
    public $anom_genit_hors_trav_rupt_uterine;
    public $anom_genit_hors_trav_fibrome_uterin;
    public $anom_genit_hors_trav_malform_uterine;
    public $anom_genit_hors_trav_anom_vaginales;
    public $anom_genit_hors_trav_chir_ant_perinee;
    public $anom_genit_hors_trav_prolapsus_vaginal;
    public $anom_plac_av_trav;
    public $anom_plac_av_trav_plac_prae_sans_hemo;
    public $anom_plac_av_trav_plac_prae_avec_hemo;
    public $anom_plac_av_trav_hrp_avec_trouble_coag;
    public $anom_plac_av_trav_hrp_sans_trouble_coag;
    public $anom_plac_av_trav_autre_hemo_avec_trouble_coag;
    public $anom_plac_av_trav_autre_hemo_sans_trouble_coag;
    public $anom_plac_av_trav_transf_foeto_mater;
    public $anom_plac_av_trav_infect_sac_membranes;
    public $rupt_premat_membranes;
    public $rupt_premat_membranes_rpm_inf37sa_sans_toco;
    public $rupt_premat_membranes_rpm_inf37sa_avec_toco;
    public $rupt_premat_membranes_rpm_sup37sa;
    public $patho_foet_chron;
    public $patho_foet_chron_retard_croiss;
    public $patho_foet_chron_macrosom_foetale;
    public $patho_foet_chron_immun_antirh;
    public $patho_foet_chron_autre_allo_immun;
    public $patho_foet_chron_anasarque_non_immun;
    public $patho_foet_chron_anasarque_immun;
    public $patho_foet_chron_hypoxie_foetale;
    public $patho_foet_chron_trouble_rcf;
    public $patho_foet_chron_mort_foatale_in_utero;
    public $patho_mat_foet_av_trav;
    public $patho_mat_foet_av_trav_hta_gravid;
    public $patho_mat_foet_av_trav_preec_moderee;
    public $patho_mat_foet_av_trav_preec_severe;
    public $patho_mat_foet_av_trav_hellp;
    public $patho_mat_foet_av_trav_preec_hta;
    public $patho_mat_foet_av_trav_eclamp;
    public $patho_mat_foet_av_trav_diabete_id;
    public $patho_mat_foet_av_trav_diabete_nid;
    public $patho_mat_foet_av_trav_steatose_grav;
    public $patho_mat_foet_av_trav_herpes_genit;
    public $patho_mat_foet_av_trav_condylomes;
    public $patho_mat_foet_av_trav_hep_b;
    public $patho_mat_foet_av_trav_hep_c;
    public $patho_mat_foet_av_trav_vih;
    public $patho_mat_foet_av_trav_sida;
    public $patho_mat_foet_av_trav_fievre;
    public $patho_mat_foet_av_trav_gross_prolong;
    public $patho_mat_foet_av_trav_autre;
    public $autre_motif_cesarienne;
    public $autre_motif_cesarienne_conv;
    public $autre_motif_cesarienne_mult;

    public $anom_pdt_trav;
    public $hypox_foet_pdt_trav;
    public $hypox_foet_pdt_trav_rcf_isole;
    public $hypox_foet_pdt_trav_la_teinte;
    public $hypox_foet_pdt_trav_rcf_la;
    public $hypox_foet_pdt_trav_anom_ph_foet;
    public $hypox_foet_pdt_trav_anom_ecg_foet;
    public $hypox_foet_pdt_trav_procidence_cordon;
    public $dysto_pres_pdt_trav;
    public $dysto_pres_pdt_trav_rot_tete_incomp;
    public $dysto_pres_pdt_trav_siege;
    public $dysto_pres_pdt_trav_face;
    public $dysto_pres_pdt_trav_pres_front;
    public $dysto_pres_pdt_trav_pres_transv;
    public $dysto_pres_pdt_trav_autre_pres_anorm;
    public $dysto_anom_foet_pdt_trav;
    public $dysto_anom_foet_pdt_trav_foetus_macrosome;
    public $dysto_anom_foet_pdt_trav_jumeaux_soudes;
    public $dysto_anom_foet_pdt_trav_difform_foet;
    public $echec_decl_travail;
    public $echec_decl_travail_medic;
    public $echec_decl_travail_meca;
    public $echec_decl_travail_sans_prec;
    public $dysto_anom_pelv_mat_pdt_trav;
    public $dysto_anom_pelv_mat_pdt_trav_deform_pelv;
    public $dysto_anom_pelv_mat_pdt_trav_bassin_retr;
    public $dysto_anom_pelv_mat_pdt_trav_detroit_sup_retr;
    public $dysto_anom_pelv_mat_pdt_trav_detroit_moy_retr;
    public $dysto_anom_pelv_mat_pdt_trav_dispr_foeto_pelv;
    public $dysto_anom_pelv_mat_pdt_trav_fibrome_pelv;
    public $dysto_anom_pelv_mat_pdt_trav_stenose_cerv;
    public $dysto_anom_pelv_mat_pdt_trav_malf_uterine;
    public $dysto_anom_pelv_mat_pdt_trav_autre;
    public $dysto_dynam_pdt_trav;
    public $dysto_dynam_pdt_trav_demarrage;
    public $dysto_dynam_pdt_trav_cerv_latence;
    public $dysto_dynam_pdt_trav_arret_dilat;
    public $dysto_dynam_pdt_trav_hypertonie_uter;
    public $dysto_dynam_pdt_trav_dilat_lente_col;
    public $dysto_dynam_pdt_trav_echec_travail;
    public $dysto_dynam_pdt_trav_non_engagement;
    public $patho_mater_pdt_trav;
    public $patho_mater_pdt_trav_hemo_sans_trouble_coag;
    public $patho_mater_pdt_trav_hemo_avec_trouble_coag;
    public $patho_mater_pdt_trav_choc_obst;
    public $patho_mater_pdt_trav_eclampsie;
    public $patho_mater_pdt_trav_rupt_uterine;
    public $patho_mater_pdt_trav_embolie_amnio;
    public $patho_mater_pdt_trav_embolie_pulm;
    public $patho_mater_pdt_trav_complic_acte_obst;
    public $patho_mater_pdt_trav_chorio_amnio;
    public $patho_mater_pdt_trav_infection;
    public $patho_mater_pdt_trav_fievre;
    public $patho_mater_pdt_trav_fatigue_mat;
    public $patho_mater_pdt_trav_autre_complication;

    public $anom_expuls;
    public $anom_expuls_non_progr_pres_foetale;
    public $anom_expuls_dysto_pres_posterieures;
    public $anom_expuls_dystocie_epaules;
    public $anom_expuls_retention_tete;
    public $anom_expuls_soufrance_foet_rcf;
    public $anom_expuls_soufrance_foet_rcf_la;
    public $anom_expuls_echec_forceps_cesar;
    public $anom_expuls_fatigue_mat;

    //Accouchement
    public $anesth_avant_naiss;
    public $datetime_anesth_avant_naiss;
    public $anesth_avant_naiss_par_id;
    public $suivi_anesth_avant_naiss_par;
    public $alr_avant_naiss;
    public $alr_peri_avant_naiss;
    public $alr_peri_avant_naiss_inj_unique;
    public $alr_peri_avant_naiss_reinj;
    public $alr_peri_avant_naiss_cat_autopousse;
    public $alr_peri_avant_naiss_cat_pcea;
    public $alr_rachi_avant_naiss;
    public $alr_rachi_avant_naiss_inj_unique;
    public $alr_rachi_avant_naiss_cat;
    public $alr_peri_rachi_avant_naiss;
    public $ag_avant_naiss;
    public $ag_avant_naiss_directe;
    public $ag_avant_naiss_apres_peri;
    public $ag_avant_naiss_apres_rachi;
    public $al_avant_naiss;
    public $al_bloc_avant_naiss;
    public $al_autre_avant_naiss;
    public $al_autre_avant_naiss_desc;
    public $autre_analg_avant_naiss;
    public $autre_analg_avant_naiss_desc;
    public $fibro_laryngee;
    public $asa_anesth_avant_naissance;
    public $moment_anesth_avant_naissance;
    public $anesth_spec_2eme_enfant;
    public $anesth_spec_2eme_enfant_desc;
    public $rques_anesth_avant_naiss;
    public $comp_anesth_avant_naiss;
    public $hypotension_alr_avant_naiss;
    public $autre_comp_alr_avant_naiss;
    public $autre_comp_alr_avant_naiss_desc;
    public $mendelson_ag_avant_naiss;
    public $comp_pulm_ag_avant_naiss;
    public $comp_card_ag_avant_naiss;
    public $comp_cereb_ag_avant_naiss;
    public $comp_allerg_tox_ag_avant_naiss;
    public $autre_comp_ag_avant_naiss;
    public $autre_comp_ag_avant_naiss_desc;
    public $anesth_apres_naissance;
    public $anesth_apres_naissance_desc;
    public $rques_anesth_apres_naissance;

    public $deliv_faite_par;
    public $datetime_deliv;
    public $type_deliv;
    public $prod_deliv;
    public $dose_prod_deliv;
    public $datetime_inj_prod_deliv;
    public $voie_inj_prod_deliv;
    public $modalite_deliv;
    public $comp_deliv;
    public $hemorr_deliv;
    public $retention_plac_comp_deliv;
    public $retention_plac_part_deliv;
    public $atonie_uterine_deliv;
    public $trouble_coag_deliv;
    public $transf_deliv;
    public $nb_unites_transf_deliv;
    public $autre_comp_deliv;
    public $retention_plac_comp_sans_hemorr_deliv;
    public $retention_plac_part_sans_hemorr_deliv;
    public $inversion_uterine_deliv;
    public $autre_comp_autre_deliv;
    public $autre_comp_autre_deliv_desc;
    public $total_pertes_sang_deliv;
    public $actes_pdt_deliv;
    public $deliv_artificielle;
    public $rev_uterine_isolee_deliv;
    public $autres_actes_deliv;
    public $ligature_art_hypogast_deliv;
    public $ligature_art_uterines_deliv;
    public $hysterectomie_hemostase_deliv;
    public $embolisation_arterielle_deliv;
    public $reduct_inversion_uterine_deliv;
    public $cure_chir_inversion_uterine_deliv;
    public $poids_placenta;
    public $anomalie_placenta;
    public $anomalie_placenta_desc;
    public $type_placentation;
    public $type_placentation_desc;
    public $poids_placenta_1_bichorial;
    public $poids_placenta_2_bichorial;
    public $exam_anapath_placenta_demande;
    public $rques_placenta;
    public $lesion_parties_molles;
    public $episiotomie;
    public $dechirure_perineale;
    public $dechirure_perineale_liste;
    public $lesions_traumatiques_parties_molles;
    public $dechirure_vaginale;
    public $dechirure_cervicale;
    public $lesion_urinaire;
    public $rupt_uterine;
    public $thrombus;
    public $autre_lesion;
    public $autre_lesion_desc;
    public $compte_rendu_delivrance;
    public $consignes_suite_couches;

    // Résumé du séjour de la mère
    public $pathologies_suite_couches;
    public $infection_suite_couches;
    public $infection_nosoc_suite_couches;
    public $localisation_infection_suite_couches;
    public $compl_perineales_suite_couches;
    public $details_compl_perineales_suite_couches;
    public $compl_parietales_suite_couches;
    public $detail_compl_parietales_suite_couches;
    public $compl_allaitement_suite_couches;
    public $details_compl_allaitement_suite_couches;
    public $details_comp_compl_allaitement_suite_couches;
    public $compl_thrombo_embo_suite_couches;
    public $detail_compl_thrombo_embo_suite_couches;
    public $compl_autre_suite_couches;
    public $anemie_suite_couches;
    public $incont_urin_suite_couches;
    public $depression_suite_couches;
    public $fract_obst_coccyx_suite_couches;
    public $hemorragie_second_suite_couches;
    public $retention_urinaire_suite_couches;
    public $psychose_puerpuerale_suite_couches;
    public $eclampsie_suite_couches;
    public $insuf_reinale_suite_couches;
    public $disjonction_symph_pub_suite_couches;
    public $autre_comp_suite_couches;
    public $desc_autre_comp_suite_couches;
    public $compl_anesth_suite_couches;
    public $compl_anesth_generale_suite_couches;
    public $autre_compl_anesth_generale_suite_couches;
    public $compl_anesth_locoregion_suite_couches;
    public $autre_compl_anesth_locoregion_suite_couches;
    public $traitements_sejour_mere;
    public $ttt_preventif_sejour_mere;
    public $antibio_preventif_sejour_mere;
    public $desc_antibio_preventif_sejour_mere;
    public $anticoag_preventif_sejour_mere;
    public $desc_anticoag_preventif_sejour_mere;
    public $antilactation_preventif_sejour_mere;
    public $ttt_curatif_sejour_mere;
    public $antibio_curatif_sejour_mere;
    public $desc_antibio_curatif_sejour_mere;
    public $anticoag_curatif_sejour_mere;
    public $desc_anticoag_curatif_sejour_mere;
    public $vacc_gammaglob_sejour_mere;
    public $gammaglob_sejour_mere;
    public $vacc_sejour_mere;
    public $transfusion_sejour_mere;
    public $nb_unite_transfusion_sejour_mere;
    public $interv_sejour_mere;
    public $datetime_interv_sejour_mere;
    public $revision_uterine_sejour_mere;
    public $interv_second_hemorr_sejour_mere;
    public $type_interv_second_hemorr_sejour_mere;
    public $autre_interv_sejour_mere;
    public $type_autre_interv_sejour_mere;
    public $jour_deces_sejour_mere;
    public $deces_cause_obst_sejour_mere;
    public $autopsie_sejour_mere;
    public $resultat_autopsie_sejour_mere;
    public $anomalie_autopsie_sejour_mere;

    /** @var CGrossesse */
    public $_ref_grossesse;
    /** @var CSejour */
    public $_ref_sejour_accouchement;
    /** @var CConstantesMedicales */
    public $_ref_ant_mater_constantes;
    /** @var CConstantesMedicales */
    public $_ref_pere_constantes;
    /** @var CConstantesMedicales */
    public $_ref_adm_mater_constantes;
    /** @var CConstantesMedicales */
    public $_ref_fievre_travail_constantes;

    /** @var CConsultationPostNatale[] */
    public $_ref_consultations_post_natale;
    /** @var CAccouchement[] */
    public $_ref_accouchements;

    // Organisation du dossier périnatal
    public $_listChapitres;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'dossier_perinat';
        $spec->key   = 'dossier_perinat_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["grossesse_id"] = "ref notNull class|CGrossesse back|dossier_perinat cascade";

        $props["activite_pro"]      = "enum list|a|c|f|cp|e|i|r";
        $props["activite_pro_pere"] = "enum list|a|c|f|cp|e|i|r";
        $props["fatigue_travail"]   = "bool";
        $props["travail_hebdo"]     = "num pos";
        $props["transport_jour"]    = "num pos";

        $props["rques_social"]               = "text helped";
        $props["enfants_foyer"]              = "num min|0";
        $props["situation_part_enfance"]     = "bool";
        $props["spe_perte_parent"]           = "bool";
        $props["spe_maltraitance"]           = "bool";
        $props["spe_mere_placee_enfance"]    = "bool";
        $props["situation_part_adolescence"] = "bool";
        $props["spa_anorexie_boulimie"]      = "bool";
        $props["spa_depression"]             = "bool";
        $props["situation_part_familiale"]   = "bool";
        $props["spf_violences_conjugales"]   = "bool";
        $props["spf_mere_isolee"]            = "bool";
        $props["spf_absence_entourage_fam"]  = "bool";
        $props["stress_agression"]           = "bool";
        $props["sa_agression_physique"]      = "bool";
        $props["sa_agression_sexuelle"]      = "bool";
        $props["sa_harcelement_travail"]     = "bool";
        $props["rques_psychologie"]          = "text helped";
        $props["situation_accompagnement"]   = "enum list|n|s|p|sp";
        $props["rques_accompagnement"]       = "text helped";

        $props["tabac_avant_grossesse"]       = "bool";
        $props["qte_tabac_avant_grossesse"]   = "num pos";
        $props["tabac_debut_grossesse"]       = "bool";
        $props["qte_tabac_debut_grossesse"]   = "num pos";
        $props["alcool_debut_grossesse"]      = "bool";
        $props["qte_alcool_debut_grossesse"]  = "num pos";
        $props["canabis_debut_grossesse"]     = "bool";
        $props["qte_canabis_debut_grossesse"] = "num pos";
        $props["subst_avant_grossesse"]       = "bool";
        $props["mode_subst_avant_grossesse"]  = "enum list|iv|po|au";
        $props["nom_subst_avant_grossesse"]   = "str";
        $props["subst_subst_avant_grossesse"] = "str";
        $props["subst_debut_grossesse"]       = "bool";
        $props["subst_debut_grossesse_text"]  = "text helped";
        $props["tabac_pere"]                  = "bool";
        $props["coexp_pere"]                  = "num";
        $props["alcool_pere"]                 = "bool";
        $props["toxico_pere"]                 = "bool";
        $props["rques_toxico"]                = "text helped";

        $props['ant_mater_constantes_id'] = "ref class|CConstantesMedicales back|cstes_maternelles";

        $props["patho_ant"]                = "bool";
        $props["patho_ant_hta"]            = "bool";
        $props["patho_ant_diabete"]        = "bool";
        $props["patho_ant_epilepsie"]      = "bool";
        $props["patho_ant_asthme"]         = "bool";
        $props["patho_ant_pulm"]           = "bool";
        $props["patho_ant_thrombo_emb"]    = "bool";
        $props["patho_ant_cardio"]         = "bool";
        $props["patho_ant_auto_immune"]    = "bool";
        $props["patho_ant_hepato_dig"]     = "bool";
        $props["patho_ant_thyroide"]       = "bool";
        $props["patho_ant_uro_nephro"]     = "bool";
        $props["patho_ant_infectieuse"]    = "bool";
        $props["patho_ant_hemato"]         = "bool";
        $props["patho_ant_cancer_non_gyn"] = "bool";
        $props["patho_ant_psy"]            = "bool";
        $props["patho_ant_autre"]          = "text helped";

        $props ["chir_ant"]       = "bool";
        $props ["chir_ant_rques"] = "text helped";

        $props["gyneco_ant_regles"]             = "num";
        $props["gyneco_ant_regul_regles"]       = "enum list|regul|irregul";
        $props["gyneco_ant_fcv"]                = "date progressive";
        $props["gyneco_ant"]                    = "bool";
        $props["gyneco_ant_herpes"]             = "bool";
        $props["gyneco_ant_lesion_col"]         = "bool";
        $props["gyneco_ant_conisation"]         = "bool";
        $props["gyneco_ant_cicatrice_uterus"]   = "bool";
        $props["gyneco_ant_fibrome"]            = "bool";
        $props["gyneco_ant_stat_pelv"]          = "bool";
        $props["gyneco_ant_cancer_sein"]        = "bool";
        $props["gyneco_ant_cancer_app_genital"] = "bool";
        $props["gyneco_ant_malf_genitale"]      = "bool";
        $props["gyneco_ant_condylomes"]         = "bool";
        $props["gyneco_ant_distilbene"]         = "bool";
        $props["gyneco_ant_autre"]              = "text helped";

        $props["gyneco_ant_infert"]         = "bool";
        $props["gyneco_ant_infert_origine"] = "enum list|anov|uterine|cervic|idiopath|tubaire|fem|masc|femmasc";

        $props['pere_constantes_id']     = 'ref class|CConstantesMedicales back|cstes_paternelles';
        $props["pere_serologie_vih"]     = "enum list|neg|pos|inc default|inc";
        $props["pere_electrophorese_hb"] = "str";
        $props["pere_patho_ant"]         = "bool";
        $props["pere_ant_herpes"]        = "bool";
        $props["pere_ant_autre"]         = "text helped";

        $props["ant_fam"]                      = "bool";
        $props["consanguinite"]                = "bool";
        $props["ant_fam_mere_gemellite"]       = "bool";
        $props["ant_fam_pere_gemellite"]       = "bool";
        $props["ant_fam_mere_malformations"]   = "bool";
        $props["ant_fam_pere_malformations"]   = "bool";
        $props["ant_fam_mere_maladie_genique"] = "bool";
        $props["ant_fam_pere_maladie_genique"] = "bool";
        $props["ant_fam_mere_maladie_chrom"]   = "bool";
        $props["ant_fam_pere_maladie_chrom"]   = "bool";
        $props["ant_fam_mere_diabete"]         = "bool";
        $props["ant_fam_pere_diabete"]         = "bool";
        $props["ant_fam_mere_hta"]             = "bool";
        $props["ant_fam_pere_hta"]             = "bool";
        $props["ant_fam_mere_phlebite"]        = "bool";
        $props["ant_fam_pere_phlebite"]        = "bool";
        $props["ant_fam_mere_autre"]           = "text helped";
        $props["ant_fam_pere_autre"]           = "text helped";

        $props["ant_obst_nb_gr_acc"]   = "num max|20";
        $props["ant_obst_nb_gr_av_sp"] = "num max|20";
        $props["ant_obst_nb_gr_ivg"]   = "num max|20";
        $props["ant_obst_nb_gr_geu"]   = "num max|20";
        $props["ant_obst_nb_gr_mole"]  = "num max|20";
        $props["ant_obst_nb_gr_img"]   = "num max|20";
        $props["ant_obst_nb_gr_amp"]   = "num max|20";
        $props["ant_obst_nb_gr_mult"]  = "num max|20";
        $props["ant_obst_nb_gr_hta"]   = "num max|20";
        $props["ant_obst_nb_gr_map"]   = "num max|20";
        $props["ant_obst_nb_gr_diab"]  = "num max|20";
        $props["ant_obst_nb_gr_cesar"] = "num max|20";
        $props["ant_obst_nb_gr_prema"] = "num max|20";

        $props["ant_obst_nb_enf_moins_25000"]     = "num max|20";
        $props["ant_obst_nb_enf_hypotroph"]       = "num max|20";
        $props["ant_obst_nb_enf_macrosome"]       = "num max|20";
        $props["ant_obst_nb_enf_morts_nes"]       = "num max|20";
        $props["ant_obst_nb_enf_mort_neonat"]     = "num max|20";
        $props["ant_obst_nb_enf_mort_postneonat"] = "num max|20";
        $props["ant_obst_nb_enf_malform"]         = "num max|20";

        $props["souhait_grossesse"]                     = "bool";
        $props["contraception_pre_grossesse"]           = "enum list|none|pilu|ster|pres|impl|prog|avag|autre";
        $props["grossesse_sous_contraception"]          = "enum list|none|ster|pilu|autre";
        $props["rques_contraception"]                   = "text helped";
        $props["grossesse_apres_traitement"]            = "bool";
        $props["type_traitement_grossesse"]             = "enum list|ind|fiv|iad|iac|icsi|autre";
        $props["origine_ovule"]                         = "str";
        $props["origine_sperme"]                        = "str";
        $props["rques_traitement_grossesse"]            = "text helped";
        $props["traitement_peri_conceptionnelle"]       = "bool";
        $props["type_traitement_peri_conceptionnelle"]  = "str";
        $props["arret_traitement_peri_conceptionnelle"] = "num max|42";
        $props["rques_traitement_peri_conceptionnelle"] = "text helped";

        $props["date_premier_contact"]                = "dateTime";
        $props["consultant_premier_contact_id"]       = "ref class|CMediusers back|dossiers_perinat";
        $props["provenance_premier_contact"]          = "enum list|pat|med|tra|aut";
        $props["mater_provenance_premier_contact_id"] = "ref class|CEtabExterne autocomplete|nom back|dossiers_perinat";
        $props["nivsoins_provenance_premier_contact"] = "enum list|1|2|3";
        $props["motif_premier_contact"]               = "enum list|survrout|survspec|consulturg|hospi|acc|autre";
        $props["nb_consult_ant_premier_contact"]      = "num";
        $props["sa_consult_ant_premier_contact"]      = "num";
        $props["surveillance_ant_premier_contact"]    = "str";
        $props["type_surv_ant_premier_contact"]       = "enum list|generaliste|gynecomater|gynecoautre|pmi|sagefemme|autre";
        $props["date_declaration_grossesse"]          = "date";
        $props["rques_provenance"]                    = "text helped";

        $props["reco_aucune"]         = "bool";
        $props["reco_tabac"]          = "bool";
        $props["reco_rhesus_negatif"] = "bool";
        $props["reco_toxoplasmose"]   = "bool";
        $props["reco_alcool"]         = "bool";
        $props["reco_vaccination"]    = "bool";
        $props["reco_hygiene_alim"]   = "bool";
        $props["reco_toxicomanie"]    = "bool";
        $props["reco_brochure"]       = "bool";
        $props["reco_autre"]          = "str";

        $props["souhait_arret_addiction"] = "bool";
        $props["souhait_aide_addiction"]  = "bool";

        $props["info_echographie"]        = "bool";
        $props["info_despistage_triso21"] = "bool";
        $props["test_triso21_propose"]    = "enum list|n|a|r";
        $props["info_orga_maternite"]     = "bool";
        $props["info_orga_reseau"]        = "bool";
        $props["info_lien_pmi"]           = "bool";

        $props["projet_lieu_accouchement"]     = "str";
        $props["projet_analgesie_peridurale"]  = "enum list|o|n|s";
        $props["projet_allaitement_maternel"]  = "enum list|o|n|s";
        $props["projet_preparation_naissance"] = "str";
        $props["projet_entretiens_proposes"]   = "bool";

        $props["bas_risques"]                    = "bool";
        $props["risque_atcd_maternel_med"]       = "bool";
        $props["risque_atcd_obst"]               = "bool";
        $props["risque_atcd_familiaux"]          = "bool";
        $props["risque_patho_mater_grossesse"]   = "bool";
        $props["risque_patho_foetale_grossesse"] = "bool";
        $props["risque_psychosocial_grossesse"]  = "bool";
        $props["risque_grossesse_multiple"]      = "bool";

        $props["type_surveillance"]          = "enum list|routine|spec|antenat|autre";
        $props["lieu_surveillance"]          = "enum list|mater|amater|ville|sfdom|had";
        $props["lieu_accouchement_prevu"]    = "str";
        $props["niveau_soins_prevu"]         = "enum list|1|2|3";
        $props["conclusion_premier_contact"] = "text helped";

        $props["transf_antenat"]                    = "enum list|n|reseau|hreseau|imp";
        $props["date_transf_antenat"]               = "date";
        $props["lieu_transf_antenat"]               = "enum list|amater|rea|autre";
        $props["etab_transf_antenat"]               = "str";
        $props["nivsoins_transf_antenat"]           = "enum list|1|2|3";
        $props["raison_transf_antenat_hors_reseau"] = "enum list|place|choixmed|choixpat";
        $props["raison_imp_transf_antenat"]         = "enum list|place|patho|dilat|refuspat";
        $props["motif_tranf_antenat"]               = "enum list|pathfoet|pathmat";
        $props["type_patho_transf_antenat"]         = "enum list|map|rpm|vasc|mult|rciusf|malf|autre";
        $props["rques_transf_antenat"]              = "text helped";

        $props["mode_transp_transf_antenat"]      = "enum list|perso|samu|ambu|autre";
        $props["antibio_transf_antenat"]          = "bool";
        $props["nom_antibio_transf_antenat"]      = "str";
        $props["cortico_transf_antenat"]          = "bool";
        $props["nom_cortico_transf_antenat"]      = "str";
        $props["datetime_cortico_transf_antenat"] = "dateTime";
        $props["tocolytiques_transf_antenat"]     = "bool";
        $props["nom_tocolytiques_transf_antenat"] = "str";
        $props["antihta_transf_antenat"]          = "bool";
        $props["nom_antihta_transf_antenat"]      = "str";
        $props["autre_ttt_transf_antenat"]        = "bool";
        $props["nom_autre_ttt_transf_antenat"]    = "str";

        $props["retour_mater_transf_antenat"]   = "enum list|n|consult|hospi|acc|postacc";
        $props["date_retour_transf_antenat"]    = "date";
        $props["devenir_retour_transf_antenat"] = "enum list|acc|transf";
        $props["rques_retour_transf_antenat"]   = "text helped";

        $props["type_terminaison_grossesse"] = "enum list|termhetab|term22|avsp";
        $props["type_term_hors_etab"]        = "enum list|vivant|mortiu|img";
        $props["type_term_inf_22sa"]         = "enum list|avsp|ivg|geu|mole|img";

        $props["date_validation_synthese"] = "date";
        $props["validateur_synthese_id"]   = "ref class|CMediusers back|syntheses_validees";

        $props["nb_consult_total_prenatal"]  = "num";
        $props["nb_consult_total_equipe"]    = "num";
        $props["entretien_prem_trim"]        = "bool";
        $props["hospitalisation"]            = "enum list|non|mater|had|autre";
        $props["nb_sejours"]                 = "num";
        $props["nb_total_jours_hospi"]       = "num";
        $props["sage_femme_domicile"]        = "bool";
        $props["transfert_in_utero"]         = "bool";
        $props["consult_preanesth"]          = "bool";
        $props["consult_centre_diag_prenat"] = "enum list|non|etab|horsetab";
        $props["preparation_naissance"]      = "enum list|non|int|ext|intext";

        $props["conso_toxique_pdt_grossesse"] = "bool";
        $props["tabac_pdt_grossesse"]         = "num";
        $props["sevrage_tabac_pdt_grossesse"] = "bool";
        $props["date_arret_tabac"]            = "date";
        $props["alcool_pdt_grossesse"]        = "num";
        $props["cannabis_pdt_grossesse"]      = "num";
        $props["autres_subst_pdt_grossesse"]  = "bool";
        $props["type_subst_pdt_grossesse"]    = "text helped";

        $props["profession_pdt_grossesse"]          = "str autocomplete";
        $props["ag_date_arret_travail"]             = "num";
        $props["situation_pb_pdt_grossesse"]        = "bool";
        $props["separation_pdt_grossesse"]          = "bool";
        $props["deces_fam_pdt_grossesse"]           = "bool";
        $props["autre_evenement_fam_pdt_grossesse"] = "str";
        $props["perte_emploi_pdt_grossesse"]        = "bool";
        $props["autre_evenement_soc_pdt_grossesse"] = "str";

        $props["nb_total_echographies"]        = "num";
        $props["echo_1er_trim"]                = "bool";
        $props["resultat_echo_1er_trim"]       = "enum list|normal|anomorpho|corrterme|autre";
        $props["resultat_autre_echo_1er_trim"] = "str";
        $props["ag_echo_1er_trim"]             = "num";
        $props["echo_2e_trim"]                 = "bool";
        $props["resultat_echo_2e_trim"]        = "enum list|normal|anomorpho|corrterme|autre";
        $props["resultat_autre_echo_2e_trim"]  = "str";
        $props["ag_echo_2e_trim"]              = "num";
        $props["doppler_2e_trim"]              = "bool";
        $props["resultat_doppler_2e_trim"]     = "enum list|normal|anomalie";
        $props["echo_3e_trim"]                 = "bool";
        $props["resultat_echo_3e_trim"]        = "enum list|normal|anomorpho|corrterme|autre";
        $props["resultat_autre_echo_3e_trim"]  = "str";
        $props["ag_echo_3e_trim"]              = "num";
        $props["doppler_3e_trim"]              = "bool";
        $props["resultat_doppler_3e_trim"]     = "enum list|normal|anomalie";

        $props["prelevements_foetaux"]            = "enum list|nonprescrits|refuses|faits";
        $props["indication_prelevements_foetaux"] = "enum list|agemater|nuque|t21|appelecho|atcd|conv";
        $props["biopsie_trophoblaste"]            = "bool";
        $props["resultat_biopsie_trophoblaste"]   = "enum list|normal|anomalie";
        $props["rques_biopsie_trophoblaste"]      = "str";
        $props["ag_biopsie_trophoblaste"]         = "num";
        $props["amniocentese"]                    = "bool";;
        $props["resultat_amniocentese"]            = "enum list|normal|anomalie";
        $props["rques_amniocentese"]               = "str";
        $props["ag_amniocentese"]                  = "num";
        $props["cordocentese"]                     = "bool";
        $props["resultat_cordocentese"]            = "enum list|normal|anomalie";
        $props["rques_cordocentese"]               = "str";
        $props["ag_cordocentese"]                  = "num";
        $props["autre_prelevements_foetaux"]       = "bool";
        $props["rques_autre_prelevements_foetaux"] = "str";
        $props["ag_autre_prelevements_foetaux"]    = "num";

        $props["prelevements_bacterio_mater"]   = "enum list|nonprescrits|refuses|faits";
        $props["prelevement_vaginal"]           = "bool";
        $props["resultat_prelevement_vaginal"]  = "enum list|negatif|streptob|autre";
        $props["rques_prelevement_vaginal"]     = "str";
        $props["ag_prelevement_vaginal"]        = "num";
        $props["prelevement_urinaire"]          = "bool";
        $props["resultat_prelevement_urinaire"] = "enum list|negatif|streptob|autre";
        $props["rques_prelevement_urinaire"]    = "str";
        $props["ag_prelevement_urinaire"]       = "num";

        $props["marqueurs_seriques"]           = "enum list|nonprescrits|refuses|faits1trim|faits2trim";
        $props["resultats_marqueurs_seriques"] = "enum list|bas|t21|anfermtn|autre";
        $props["rques_marqueurs_seriques"]     = "str";

        $props["depistage_diabete"]          = "enum list|nonprescrit|refuse|fait";
        $props["resultat_depistage_diabete"] = "enum list|normal|anormal";


        $props["rai_fin_grossesse"]                 = "enum list|neg|pos|inc";
        $props["rubeole_fin_grossesse"]             = "enum list|nonimmu|immu|inc";
        $props["seroconv_rubeole"]                  = "bool default|0";
        $props["ag_seroconv_rubeole"]               = "num";
        $props["toxoplasmose_fin_grossesse"]        = "enum list|nonimmu|immu|inc";
        $props["seroconv_toxoplasmose"]             = "bool default|0";
        $props["ag_seroconv_toxoplasmose"]          = "num";
        $props["syphilis_fin_grossesse"]            = "enum list|neg|pos|inc";
        $props["vih_fin_grossesse"]                 = "enum list|neg|pos|inc";
        $props["hepatite_b_fin_grossesse"]          = "enum list|aghbsm|aghbsp|achbsp|inc";
        $props["hepatite_b_aghbspos_fin_grossesse"] = "enum list|aghbcp|achbcp|aghbep|achbep";
        $props["hepatite_c_fin_grossesse"]          = "enum list|neg|acvhcp|inc";
        $props["cmvg_fin_grossesse"]                = "enum list|pos|neg|inc";
        $props["cmvm_fin_grossesse"]                = "enum list|pos|neg|inc";
        $props["seroconv_cmv"]                      = "bool default|0";
        $props["ag_seroconv_cmv"]                   = "num";
        $props["autre_serodiag_fin_grossesse"]      = "text helped";

        $props["pathologie_grossesse"] = "bool";

        $props["pathologie_grossesse_maternelle"]             = "bool";
        $props["metrorragie_1er_trim"]                        = "bool";
        $props["type_metrorragie_1er_trim"]                   = "enum list|menavort|autre";
        $props["ag_metrorragie_1er_trim"]                     = "num";
        $props["metrorragie_2e_3e_trim"]                      = "bool";
        $props["type_metrorragie_2e_3e_trim"]                 = "enum list|placpraehemo|hrpavectrcoag|hrpsanstrcoag|hemoavectrcoag|hemosanstrcoag";
        $props["ag_metrorragie_2e_3e_trim"]                   = "num";
        $props["menace_acc_premat"]                           = "bool";
        $props["menace_acc_premat_modif_cerv"]                = "bool";
        $props["pec_menace_acc_premat"]                       = "enum list|ttrepos|ttmedic|hospi";
        $props["ag_menace_acc_premat"]                        = "num";
        $props["ag_hospi_menace_acc_premat"]                  = "num";
        $props["rupture_premat_membranes"]                    = "bool";
        $props["ag_rupture_premat_membranes"]                 = "num";
        $props["anomalie_liquide_amniotique"]                 = "bool";
        $props["type_anomalie_liquide_amniotique"]            = "enum list|exces|oligoamnios";
        $props["ag_anomalie_liquide_amniotique"]              = "num";
        $props["autre_patho_gravidique"]                      = "bool";
        $props["patho_grav_vomissements"]                     = "bool";
        $props["patho_grav_herpes_gest"]                      = "bool";
        $props["patho_grav_dermatose_pup"]                    = "bool";
        $props["patho_grav_placenta_praevia_non_hemo"]        = "bool";
        $props["patho_grav_chorio_amniotite"]                 = "bool";
        $props["patho_grav_transf_foeto_mat"]                 = "bool";
        $props["patho_grav_beance_col"]                       = "bool";
        $props["patho_grav_cerclage"]                         = "bool";
        $props["ag_autre_patho_gravidique"]                   = "num";
        $props["hypertension_arterielle"]                     = "bool";
        $props["type_hypertension_arterielle"]                = "enum list|htachro|htagrav|hellp|preecmod|preechta|preecsev|ecl";
        $props["ag_hypertension_arterielle"]                  = "num";
        $props["proteinurie"]                                 = "bool";
        $props["type_proteinurie"]                            = "enum list|sansoed|avecoed|oedgen";
        $props["ag_proteinurie"]                              = "num";
        $props["diabete"]                                     = "bool";
        $props["type_diabete"]                                = "enum list|gestid|gestnid|preexid|preexnid|sansprec";
        $props["ag_diabete"]                                  = "num";
        $props["infection_urinaire"]                          = "bool";
        $props["type_infection_urinaire"]                     = "enum list|basse|pyelo|nonprec";
        $props["ag_infection_urinaire"]                       = "num";
        $props["infection_cervico_vaginale"]                  = "bool";
        $props["type_infection_cervico_vaginale"]             = "enum list|streptob|autre";
        $props["autre_infection_cervico_vaginale"]            = "str";
        $props["ag_infection_cervico_vaginale"]               = "num";
        $props["autre_patho_maternelle"]                      = "bool";
        $props["ag_autre_patho_maternelle"]                   = "num";
        $props["anemie_mat_pdt_grossesse"]                    = "bool";
        $props["tombopenie_mat_pdt_grossesse"]                = "bool";
        $props["autre_patho_hemato_mat_pdt_grossesse"]        = "bool";
        $props["desc_autre_patho_hemato_mat_pdt_grossesse"]   = "str";
        $props["faible_prise_poid_mat_pdt_grossesse"]         = "bool";
        $props["malnut_mat_pdt_grossesse"]                    = "bool";
        $props["autre_patho_endo_mat_pdt_grossesse"]          = "bool";
        $props["desc_autre_patho_endo_mat_pdt_grossesse"]     = "str";
        $props["cholestase_mat_pdt_grossesse"]                = "bool";
        $props["steatose_hep_mat_pdt_grossesse"]              = "bool";
        $props["autre_patho_hepato_mat_pdt_grossesse"]        = "bool";
        $props["desc_autre_patho_hepato_mat_pdt_grossesse"]   = "str";
        $props["thrombophl_sup_mat_pdt_grossesse"]            = "bool";
        $props["thrombophl_prof_mat_pdt_grossesse"]           = "bool";
        $props["autre_patho_vein_mat_pdt_grossesse"]          = "bool";
        $props["desc_autre_patho_vein_mat_pdt_grossesse"]     = "str";
        $props["asthme_mat_pdt_grossesse"]                    = "bool";
        $props["autre_patho_resp_mat_pdt_grossesse"]          = "bool";
        $props["desc_autre_patho_resp_mat_pdt_grossesse"]     = "str";
        $props["cardiopathie_mat_pdt_grossesse"]              = "bool";
        $props["autre_patho_cardio_mat_pdt_grossesse"]        = "bool";
        $props["desc_autre_patho_cardio_mat_pdt_grossesse"]   = "str";
        $props["epilepsie_mat_pdt_grossesse"]                 = "bool";
        $props["depression_mat_pdt_grossesse"]                = "bool";
        $props["autre_patho_neuropsy_mat_pdt_grossesse"]      = "bool";
        $props["desc_autre_patho_neuropsy_mat_pdt_grossesse"] = "str";
        $props["patho_gyneco_mat_pdt_grossesse"]              = "bool";
        $props["desc_patho_gyneco_mat_pdt_grossesse"]         = "str";
        $props["mst_mat_pdt_grossesse"]                       = "bool";
        $props["desc_mst_mat_pdt_grossesse"]                  = "str";
        $props["synd_douleur_abdo_mat_pdt_grossesse"]         = "bool";
        $props["desc_synd_douleur_abdo_mat_pdt_grossesse"]    = "str";
        $props["synd_infect_mat_pdt_grossesse"]               = "bool";
        $props["desc_synd_infect_mat_pdt_grossesse"]          = "str";

        $props["therapeutique_grossesse_maternelle"]    = "bool";
        $props["antibio_pdt_grossesse"]                 = "bool";
        $props["type_antibio_pdt_grossesse"]            = "str";
        $props["tocolyt_pdt_grossesse"]                 = "bool";
        $props["mode_admin_tocolyt_pdt_grossesse"]      = "enum list|perf|peros";
        $props["cortico_pdt_grossesse"]                 = "bool";
        $props["nb_cures_cortico_pdt_grossesse"]        = "num";
        $props["etat_dern_cure_cortico_pdt_grossesse"]  = "enum list|comp|incomp";
        $props["gammaglob_anti_d_pdt_grossesse"]        = "bool";
        $props["antihyp_pdt_grossesse"]                 = "bool";
        $props["aspirine_a_pdt_grossesse"]              = "bool";
        $props["barbit_antiepilept_pdt_grossesse"]      = "bool";
        $props["psychotropes_pdt_grossesse"]            = "bool";
        $props["subst_nicotine_pdt_grossesse"]          = "bool";
        $props["autre_therap_mater_pdt_grossesse"]      = "bool";
        $props["desc_autre_therap_mater_pdt_grossesse"] = "str";

        $props["patho_foetale_in_utero"]              = "bool";
        $props["anomalie_croiss_intra_uterine"]       = "bool";
        $props["type_anomalie_croiss_intra_uterine"]  = "enum list|retard|macrosomie";
        $props["ag_anomalie_croiss_intra_uterine"]    = "num";
        $props["signes_hypoxie_foetale_chronique"]    = "bool";
        $props["ag_signes_hypoxie_foetale_chronique"] = "num";
        $props["hypoxie_foetale_anomalie_doppler"]    = "bool";
        $props["hypoxie_foetale_anomalie_rcf"]        = "bool";
        $props["hypoxie_foetale_alter_profil_biophy"] = "bool";
        $props["anomalie_constit_foetus"]             = "bool";
        $props["ag_anomalie_constit_foetus"]          = "num";
        $props["malformation_isolee_foetus"]          = "bool";
        $props["anomalie_chromo_foetus"]              = "bool";
        $props["synd_polymalform_foetus"]             = "bool";
        $props["anomalie_genique_foetus"]             = "bool";
        $props["rques_anomalies_foetus"]              = "text";
        $props["foetopathie_infect_acquise"]          = "bool";
        $props["type_foetopathie_infect_acquise"]     = "enum list|cmv|toxo|rub|parvo|autre";
        $props["autre_foetopathie_infect_acquise"]    = "str";
        $props["ag_foetopathie_infect_acquise"]       = "num";
        $props["autre_patho_foetale"]                 = "bool";
        $props["ag_autre_patho_foetale"]              = "num";
        $props["allo_immun_anti_rh_foetale"]          = "bool";
        $props["autre_allo_immun_foetale"]            = "bool";
        $props["anas_foeto_plac_non_immun"]           = "bool";
        $props["trouble_rcf_foetus"]                  = "bool";
        $props["foetopathie_alcoolique"]              = "bool";
        $props["grosse_abdo_foetus_viable"]           = "bool";
        $props["mort_foetale_in_utero_in_22sa"]       = "bool";
        $props["autre_patho_foetale_autre"]           = "bool";
        $props["desc_autre_patho_foetale_autre"]      = "str";
        $props["patho_foetale_gross_mult"]            = "bool";
        $props["ag_patho_foetale_gross_mult"]         = "num";
        $props["avort_foetus_gross_mult"]             = "bool";
        $props["mort_foetale_in_utero_gross_mutl"]    = "bool";
        $props["synd_transf_transf_gross_mult"]       = "bool";

        $props["therapeutique_foetale"]           = "bool";
        $props["amnioinfusion"]                   = "bool";
        $props["chirurgie_foetale"]               = "bool";
        $props["derivation_foetale"]              = "bool";
        $props["tranfusion_foetale_in_utero"]     = "bool";
        $props["ex_sanguino_transfusion_foetale"] = "bool";
        $props["autre_therapeutiques_foetales"]   = "bool";
        $props["reduction_embryonnaire"]          = "bool";
        $props["type_reduction_embryonnaire"]     = "enum list|reducinf13|reducsup13|select";
        $props["photocoag_vx_placentaires"]       = "bool";
        $props["rques_therapeutique_foetale"]     = "text";

        $props["presentation_fin_grossesse"]             = "enum list|ceph|siege|transv|autre";
        $props["autre_presentation_fin_grossesse"]       = "str";
        $props["version_presentation_manoeuvre_ext"]     = "enum list|nontente|tenteok|tenteko";
        $props["rques_presentation_fin_grossesse"]       = "text";
        $props["etat_uterus_fin_grossesse"]              = "enum list|norm|cicat|autreano";
        $props["autre_anomalie_uterus_fin_grossesse"]    = "str";
        $props["nb_cicatrices_uterus_fin_grossesse"]     = "num";
        $props["date_derniere_hysterotomie"]             = "date";
        $props["rques_etat_uterus"]                      = "text helped";
        $props["appreciation_clinique_etat_bassin"]      = "enum list|norm|anorm";
        $props["desc_appreciation_clinique_etat_bassin"] = "str";
        $props["pelvimetrie"]                            = "enum list|norm|anorm";
        $props["desc_pelvimetrie"]                       = "str";
        $props["diametre_transverse_median"]             = "float";
        $props["diametre_promonto_retro_pubien"]         = "float";
        $props["diametre_bisciatique"]                   = "float";
        $props["indice_magnin"]                          = "float";
        $props["date_echo_fin_grossesse"]                = "date";
        $props["sa_echo_fin_grossesse"]                  = "num";
        $props["bip_fin_grossesse"]                      = "num";
        $props["est_pond_fin_grossesse"]                 = "num";
        $props["est_pond_2e_foetus_fin_grossesse"]       = "num";
        $props["conduite_a_tenir_acc"]                   = "enum list|bassespon|bassedecl|cesar";
        $props["niveau_alerte_cesar"]                    = "enum list|1|2|3";
        $props["date_decision_conduite_a_tenir_acc"]     = "date";
        $props["valid_decision_conduite_a_tenir_acc_id"] = "ref class|CMediusers back|conduites_validees";
        $props["motif_conduite_a_tenir_acc"]             = "str";
        $props["date_prevue_interv"]                     = "date";
        $props["rques_conduite_a_tenir"]                 = "text helped";
        $props["facteur_risque"]                         = "text helped";

        $props["admission_id"]                 = "ref class|CSejour back|dossiers_perinat";
        $props["adm_mater_constantes_id"]      = "ref class|CConstantesMedicales back|cstes_adm_mater";
        $props["adm_sage_femme_resp_id"]       = "ref class|CMediusers back|dossiers_perinat_adm";
        $props["ag_admission"]                 = "num";
        $props["ag_jours_admission"]           = "num";
        $props["motif_admission"]              = "enum list|travsp|travspmbint|travspmbromp|ruptmb|decl|cesar|urg|admpostacc";
        $props["ag_ruptures_membranes"]        = "num";
        $props["ag_jours_ruptures_membranes"]  = "num";
        $props["delai_rupture_travail_jours"]  = "num";
        $props["delai_rupture_travail_heures"] = "num";
        $props["date_ruptures_membranes"]      = "dateTime";
        $props["rques_admission"]              = "text helped";

        $props["exam_entree_oedeme"]            = "str";
        $props["exam_entree_bruits_du_coeur"]   = "str";
        $props["exam_entree_mvt_actifs_percus"] = "str";
        $props["exam_entree_contractions"]      = "str";
        $props["exam_entree_presentation"]      = "str";
        $props["exam_entree_col"]               = "str";
        $props["exam_entree_liquide_amnio"]     = "str";
        $props["exam_entree_indice_bishop"]     = "num";

        $props["exam_entree_prelev_urine"]        = "bool";
        $props["exam_entree_proteinurie"]         = "str";
        $props["exam_entree_glycosurie"]          = "str";
        $props["exam_entree_prelev_vaginal"]      = "bool";
        $props["exam_entree_prelev_vaginal_desc"] = "str";
        $props["exam_entree_rcf"]                 = "bool";
        $props["exam_entree_rcf_desc"]            = "str";
        $props["exam_entree_amnioscopie"]         = "bool";
        $props["exam_entree_amnioscopie_desc"]    = "str";
        $props["exam_entree_autres"]              = "bool";
        $props["exam_entree_autres_desc"]         = "str";
        $props["rques_exam_entree"]               = "text helped";

        $props["lieu_accouchement"]       = "enum list|mater|dom|autremater|autre";
        $props["autre_lieu_accouchement"] = "str";
        $props["nom_maternite_externe"]   = "str";
        $props["lieu_delivrance"]         = "str";
        $props["ag_accouchement"]         = "num";
        $props["ag_jours_accouchement"]   = "num";
        $props["mode_debut_travail"]      = "enum list|spon|decl|cesar";
        $props["rques_debut_travail"]     = "text helped";

        $props["datetime_declenchement"]    = "dateTime";
        $props["motif_decl_conv"]           = "bool";
        $props["motif_decl_gross_prol"]     = "bool";
        $props["motif_decl_patho_mat"]      = "bool";
        $props["motif_decl_patho_foet"]     = "bool";
        $props["motif_decl_rpm"]            = "bool";
        $props["motif_decl_mort_iu"]        = "bool";
        $props["motif_decl_img"]            = "bool";
        $props["motif_decl_autre"]          = "bool";
        $props["motif_decl_autre_details"]  = "str";
        $props["moyen_decl_ocyto"]          = "bool";
        $props["moyen_decl_prosta"]         = "bool";
        $props["moyen_decl_autre_medic"]    = "bool";
        $props["moyen_decl_meca"]           = "bool";
        $props["moyen_decl_rupture"]        = "bool";
        $props["moyen_decl_autre"]          = "bool";
        $props["moyen_decl_autre_details"]  = "str";
        $props["score_bishop"]              = "num";
        $props["score_bishop_dilatation"]   = "enum list|0|1|2|3";
        $props["score_bishop_longueur"]     = "enum list|0|1|2|3";
        $props["score_bishop_consistance"]  = "enum list|0|1|2";
        $props["score_bishop_position"]     = "enum list|0|1|2";
        $props["score_bishop_presentation"] = "enum list|0|1|2|3";
        $props["remarques_declenchement"]   = "text helped";

        $props["surveillance_travail"]                 = "enum list|cli|paracli";
        $props["tocographie"]                          = "bool";
        $props["type_tocographie"]                     = "enum list|ext|int";
        $props["anomalie_contractions"]                = "enum list|aucune|hypoci|hyperci|hypoto|hyperto";
        $props["rcf"]                                  = "bool";
        $props["type_rcf"]                             = "enum list|ext|int";
        $props["desc_trace_rcf"]                       = "enum list|norm|suspect|patho";
        $props["anomalie_rcf"]                         = "enum list|bradyc|tachyc|plat|ralprec|raltard|ralvar";
        $props["ecg_foetal"]                           = "bool";
        $props["anomalie_ecg_foetal"]                  = "bool";
        $props["prelevement_sang_foetal"]              = "bool";
        $props["anomalie_ph_sang_foetal"]              = "bool";
        $props["detail_anomalie_ph_sang_foetal"]       = "str";
        $props["valeur_anomalie_ph_sang_foetal"]       = "float";
        $props["anomalie_lactates_sang_foetal"]        = "bool";
        $props["detail_anomalie_lactates_sang_foetal"] = "str";
        $props["valeur_anomalie_lactates_sang_foetal"] = "float";
        $props["oxymetrie_foetale"]                    = "bool";
        $props["anomalie_oxymetrie_foetale"]           = "bool";
        $props["detail_anomalie_oxymetrie_foetale"]    = "str";
        $props["autre_examen_surveillance"]            = "bool";
        $props["desc_autre_examen_surveillance"]       = "str";
        $props["anomalie_autre_examen_surveillance"]   = "bool";
        $props["rques_surveillance_travail"]           = "text helped";

        $props["therapeutique_pdt_travail"]     = "bool";
        $props["antibio_pdt_travail"]           = "bool";
        $props["antihypertenseurs_pdt_travail"] = "bool";
        $props["antispasmodiques_pdt_travail"]  = "bool";
        $props["tocolytiques_pdt_travail"]      = "bool";
        $props["ocytociques_pdt_travail"]       = "bool";
        $props["opiaces_pdt_travail"]           = "bool";
        $props["sedatifs_pdt_travail"]          = "bool";
        $props["amnioinfusion_pdt_travail"]     = "bool";
        $props["autre_therap_pdt_travail"]      = "bool";
        $props["desc_autre_therap_pdt_travail"] = "str";
        $props["rques_therap_pdt_travail"]      = "text helped";

        $props["pathologie_accouchement"]      = "bool";
        $props["fievre_pdt_travail"]           = "bool";
        $props["fievre_travail_constantes_id"] = "ref class|CConstantesMedicales back|cstes_fievre_travail";

        $props["anom_av_trav"]                                   = "bool";
        $props["anom_pres_av_trav"]                              = "bool";
        $props["anom_pres_av_trav_siege"]                        = "bool";
        $props["anom_pres_av_trav_transverse"]                   = "bool";
        $props["anom_pres_av_trav_face"]                         = "bool";
        $props["anom_pres_av_trav_anormale"]                     = "bool";
        $props["anom_pres_av_trav_autre"]                        = "bool";
        $props["anom_bassin_av_trav"]                            = "bool";
        $props["anom_bassin_av_trav_bassin_retreci"]             = "bool";
        $props["anom_bassin_av_trav_malform_bassin"]             = "bool";
        $props["anom_bassin_av_trav_foetus"]                     = "bool";
        $props["anom_bassin_av_trav_disprop_foetopelv"]          = "bool";
        $props["anom_bassin_av_trav_disprop_difform_foet"]       = "bool";
        $props["anom_bassin_av_trav_disprop_sans_prec"]          = "bool";
        $props["anom_genit_hors_trav"]                           = "bool";
        $props["anom_genit_hors_trav_uterus_cicat"]              = "bool";
        $props["anom_genit_hors_trav_rupt_uterine"]              = "bool";
        $props["anom_genit_hors_trav_fibrome_uterin"]            = "bool";
        $props["anom_genit_hors_trav_malform_uterine"]           = "bool";
        $props["anom_genit_hors_trav_anom_vaginales"]            = "bool";
        $props["anom_genit_hors_trav_chir_ant_perinee"]          = "bool";
        $props["anom_genit_hors_trav_prolapsus_vaginal"]         = "bool";
        $props["anom_plac_av_trav"]                              = "bool";
        $props["anom_plac_av_trav_plac_prae_sans_hemo"]          = "bool";
        $props["anom_plac_av_trav_plac_prae_avec_hemo"]          = "bool";
        $props["anom_plac_av_trav_hrp_avec_trouble_coag"]        = "bool";
        $props["anom_plac_av_trav_hrp_sans_trouble_coag"]        = "bool";
        $props["anom_plac_av_trav_autre_hemo_avec_trouble_coag"] = "bool";
        $props["anom_plac_av_trav_autre_hemo_sans_trouble_coag"] = "bool";
        $props["anom_plac_av_trav_transf_foeto_mater"]           = "bool";
        $props["anom_plac_av_trav_infect_sac_membranes"]         = "bool";
        $props["rupt_premat_membranes"]                          = "bool";
        $props["rupt_premat_membranes_rpm_inf37sa_sans_toco"]    = "bool";
        $props["rupt_premat_membranes_rpm_inf37sa_avec_toco"]    = "bool";
        $props["rupt_premat_membranes_rpm_sup37sa"]              = "bool";
        $props["patho_foet_chron"]                               = "bool";
        $props["patho_foet_chron_retard_croiss"]                 = "bool";
        $props["patho_foet_chron_macrosom_foetale"]              = "bool";
        $props["patho_foet_chron_immun_antirh"]                  = "bool";
        $props["patho_foet_chron_autre_allo_immun"]              = "bool";
        $props["patho_foet_chron_anasarque_non_immun"]           = "bool";
        $props["patho_foet_chron_anasarque_immun"]               = "bool";
        $props["patho_foet_chron_hypoxie_foetale"]               = "bool";
        $props["patho_foet_chron_trouble_rcf"]                   = "bool";
        $props["patho_foet_chron_mort_foatale_in_utero"]         = "bool";
        $props["patho_mat_foet_av_trav"]                         = "bool";
        $props["patho_mat_foet_av_trav_hta_gravid"]              = "bool";
        $props["patho_mat_foet_av_trav_preec_moderee"]           = "bool";
        $props["patho_mat_foet_av_trav_preec_severe"]            = "bool";
        $props["patho_mat_foet_av_trav_hellp"]                   = "bool";
        $props["patho_mat_foet_av_trav_preec_hta"]               = "bool";
        $props["patho_mat_foet_av_trav_eclamp"]                  = "bool";
        $props["patho_mat_foet_av_trav_diabete_id"]              = "bool";
        $props["patho_mat_foet_av_trav_diabete_nid"]             = "bool";
        $props["patho_mat_foet_av_trav_steatose_grav"]           = "bool";
        $props["patho_mat_foet_av_trav_herpes_genit"]            = "bool";
        $props["patho_mat_foet_av_trav_condylomes"]              = "bool";
        $props["patho_mat_foet_av_trav_hep_b"]                   = "bool";
        $props["patho_mat_foet_av_trav_hep_c"]                   = "bool";
        $props["patho_mat_foet_av_trav_vih"]                     = "bool";
        $props["patho_mat_foet_av_trav_sida"]                    = "bool";
        $props["patho_mat_foet_av_trav_fievre"]                  = "bool";
        $props["patho_mat_foet_av_trav_gross_prolong"]           = "bool";
        $props["patho_mat_foet_av_trav_autre"]                   = "bool";
        $props["autre_motif_cesarienne"]                         = "bool";
        $props["autre_motif_cesarienne_conv"]                    = "bool";
        $props["autre_motif_cesarienne_mult"]                    = "bool";

        $props["anom_pdt_trav"]                                 = "bool";
        $props["hypox_foet_pdt_trav"]                           = "bool";
        $props["hypox_foet_pdt_trav_rcf_isole"]                 = "bool";
        $props["hypox_foet_pdt_trav_la_teinte"]                 = "bool";
        $props["hypox_foet_pdt_trav_rcf_la"]                    = "bool";
        $props["hypox_foet_pdt_trav_anom_ph_foet"]              = "bool";
        $props["hypox_foet_pdt_trav_anom_ecg_foet"]             = "bool";
        $props["hypox_foet_pdt_trav_procidence_cordon"]         = "bool";
        $props["dysto_pres_pdt_trav"]                           = "bool";
        $props["dysto_pres_pdt_trav_rot_tete_incomp"]           = "bool";
        $props["dysto_pres_pdt_trav_siege"]                     = "bool";
        $props["dysto_pres_pdt_trav_face"]                      = "bool";
        $props["dysto_pres_pdt_trav_pres_front"]                = "bool";
        $props["dysto_pres_pdt_trav_pres_transv"]               = "bool";
        $props["dysto_pres_pdt_trav_autre_pres_anorm"]          = "bool";
        $props["dysto_anom_foet_pdt_trav"]                      = "bool";
        $props["dysto_anom_foet_pdt_trav_foetus_macrosome"]     = "bool";
        $props["dysto_anom_foet_pdt_trav_jumeaux_soudes"]       = "bool";
        $props["dysto_anom_foet_pdt_trav_difform_foet"]         = "bool";
        $props["echec_decl_travail"]                            = "bool";
        $props["echec_decl_travail_medic"]                      = "bool";
        $props["echec_decl_travail_meca"]                       = "bool";
        $props["echec_decl_travail_sans_prec"]                  = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav"]                  = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_deform_pelv"]      = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_bassin_retr"]      = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_detroit_sup_retr"] = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_detroit_moy_retr"] = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_dispr_foeto_pelv"] = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_fibrome_pelv"]     = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_stenose_cerv"]     = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_malf_uterine"]     = "bool";
        $props["dysto_anom_pelv_mat_pdt_trav_autre"]            = "bool";
        $props["dysto_dynam_pdt_trav"]                          = "bool";
        $props["dysto_dynam_pdt_trav_demarrage"]                = "bool";
        $props["dysto_dynam_pdt_trav_cerv_latence"]             = "bool";
        $props["dysto_dynam_pdt_trav_arret_dilat"]              = "bool";
        $props["dysto_dynam_pdt_trav_hypertonie_uter"]          = "bool";
        $props["dysto_dynam_pdt_trav_dilat_lente_col"]          = "bool";
        $props["dysto_dynam_pdt_trav_echec_travail"]            = "bool";
        $props["dysto_dynam_pdt_trav_non_engagement"]           = "bool";
        $props["patho_mater_pdt_trav"]                          = "bool";
        $props["patho_mater_pdt_trav_hemo_sans_trouble_coag"]   = "bool";
        $props["patho_mater_pdt_trav_hemo_avec_trouble_coag"]   = "bool";
        $props["patho_mater_pdt_trav_choc_obst"]                = "bool";
        $props["patho_mater_pdt_trav_eclampsie"]                = "bool";
        $props["patho_mater_pdt_trav_rupt_uterine"]             = "bool";
        $props["patho_mater_pdt_trav_embolie_amnio"]            = "bool";
        $props["patho_mater_pdt_trav_embolie_pulm"]             = "bool";
        $props["patho_mater_pdt_trav_complic_acte_obst"]        = "bool";
        $props["patho_mater_pdt_trav_chorio_amnio"]             = "bool";
        $props["patho_mater_pdt_trav_infection"]                = "bool";
        $props["patho_mater_pdt_trav_fievre"]                   = "bool";
        $props["patho_mater_pdt_trav_fatigue_mat"]              = "bool";
        $props["patho_mater_pdt_trav_autre_complication"]       = "bool";

        $props["anom_expuls"]                         = "bool";
        $props["anom_expuls_non_progr_pres_foetale"]  = "bool";
        $props["anom_expuls_dysto_pres_posterieures"] = "bool";
        $props["anom_expuls_dystocie_epaules"]        = "bool";
        $props["anom_expuls_retention_tete"]          = "bool";
        $props["anom_expuls_soufrance_foet_rcf"]      = "bool";
        $props["anom_expuls_soufrance_foet_rcf_la"]   = "bool";
        $props["anom_expuls_echec_forceps_cesar"]     = "bool";
        $props["anom_expuls_fatigue_mat"]             = "bool";

        //Accouchement
        $props["anesth_avant_naiss"]                  = "bool";
        $props["datetime_anesth_avant_naiss"]         = "dateTime";
        $props["anesth_avant_naiss_par_id"]           = "ref class|CMediusers back|anesth_naissance";
        $props["suivi_anesth_avant_naiss_par"]        = "str";
        $props["alr_avant_naiss"]                     = "bool";
        $props["alr_peri_avant_naiss"]                = "bool";
        $props["alr_peri_avant_naiss_inj_unique"]     = "bool";
        $props["alr_peri_avant_naiss_reinj"]          = "bool";
        $props["alr_peri_avant_naiss_cat_autopousse"] = "bool";
        $props["alr_peri_avant_naiss_cat_pcea"]       = "bool";
        $props["alr_rachi_avant_naiss"]               = "bool";
        $props["alr_rachi_avant_naiss_inj_unique"]    = "bool";
        $props["alr_rachi_avant_naiss_cat"]           = "bool";
        $props["alr_peri_rachi_avant_naiss"]          = "bool";
        $props["ag_avant_naiss"]                      = "bool";
        $props["ag_avant_naiss_directe"]              = "bool";
        $props["ag_avant_naiss_apres_peri"]           = "bool";
        $props["ag_avant_naiss_apres_rachi"]          = "bool";
        $props["al_avant_naiss"]                      = "bool";
        $props["al_bloc_avant_naiss"]                 = "bool";
        $props["al_autre_avant_naiss"]                = "bool";
        $props["al_autre_avant_naiss_desc"]           = "str";
        $props["autre_analg_avant_naiss"]             = "bool";
        $props["autre_analg_avant_naiss_desc"]        = "str";
        $props["fibro_laryngee"]                      = "bool";
        $props["asa_anesth_avant_naissance"]          = "num";
        $props["moment_anesth_avant_naissance"]       = "enum list|debtrav|intervvb|cesar";
        $props["anesth_spec_2eme_enfant"]             = "enum list|non|ag|rachi|autre";
        $props["anesth_spec_2eme_enfant_desc"]        = "str";
        $props["rques_anesth_avant_naiss"]            = "text helped";
        $props["comp_anesth_avant_naiss"]             = "bool";
        $props["hypotension_alr_avant_naiss"]         = "bool";
        $props["autre_comp_alr_avant_naiss"]          = "bool";
        $props["autre_comp_alr_avant_naiss_desc"]     = "str";
        $props["mendelson_ag_avant_naiss"]            = "bool";
        $props["comp_pulm_ag_avant_naiss"]            = "bool";
        $props["comp_card_ag_avant_naiss"]            = "bool";
        $props["comp_cereb_ag_avant_naiss"]           = "bool";
        $props["comp_allerg_tox_ag_avant_naiss"]      = "bool";
        $props["autre_comp_ag_avant_naiss"]           = "bool";
        $props["autre_comp_ag_avant_naiss_desc"]      = "str";
        $props["anesth_apres_naissance"]              = "enum list|non|ag|al|autre";
        $props["anesth_apres_naissance_desc"]         = "str";
        $props["rques_anesth_apres_naissance"]        = "text helped";

        $props["deliv_faite_par"]                       = "str";
        $props["datetime_deliv"]                        = "dateTime";
        $props["type_deliv"]                            = "enum list|dir|nat";
        $props["prod_deliv"]                            = "str";
        $props["dose_prod_deliv"]                       = "str";
        $props["datetime_inj_prod_deliv"]               = "dateTime";
        $props["voie_inj_prod_deliv"]                   = "str";
        $props["modalite_deliv"]                        = "enum list|comp|incomp|retplac";
        $props["comp_deliv"]                            = "bool";
        $props["hemorr_deliv"]                          = "bool";
        $props["retention_plac_comp_deliv"]             = "bool";
        $props["retention_plac_part_deliv"]             = "bool";
        $props["atonie_uterine_deliv"]                  = "bool";
        $props["trouble_coag_deliv"]                    = "bool";
        $props["transf_deliv"]                          = "bool";
        $props["nb_unites_transf_deliv"]                = "num";
        $props["autre_comp_deliv"]                      = "bool";
        $props["retention_plac_comp_sans_hemorr_deliv"] = "bool";
        $props["retention_plac_part_sans_hemorr_deliv"] = "bool";
        $props["inversion_uterine_deliv"]               = "bool";
        $props["autre_comp_autre_deliv"]                = "bool";
        $props["autre_comp_autre_deliv_desc"]           = "str";
        $props["total_pertes_sang_deliv"]               = "num";
        $props["actes_pdt_deliv"]                       = "bool";
        $props["deliv_artificielle"]                    = "bool";
        $props["rev_uterine_isolee_deliv"]              = "bool";
        $props["autres_actes_deliv"]                    = "bool";
        $props["ligature_art_hypogast_deliv"]           = "bool";
        $props["ligature_art_uterines_deliv"]           = "bool";
        $props["hysterectomie_hemostase_deliv"]         = "bool";
        $props["embolisation_arterielle_deliv"]         = "bool";
        $props["reduct_inversion_uterine_deliv"]        = "bool";
        $props["cure_chir_inversion_uterine_deliv"]     = "bool";
        $props["poids_placenta"]                        = "num";
        $props["anomalie_placenta"]                     = "enum list|non|malf|autre";
        $props["anomalie_placenta_desc"]                = "str";
        $props["type_placentation"]                     = "enum list|monomono|monobi|bibi|tritri|autre";
        $props["type_placentation_desc"]                = "str";
        $props["poids_placenta_1_bichorial"]            = "num";
        $props["poids_placenta_2_bichorial"]            = "num";
        $props["exam_anapath_placenta_demande"]         = "bool";
        $props["rques_placenta"]                        = "text helped";
        $props["lesion_parties_molles"]                 = "bool";
        $props["episiotomie"]                           = "bool";
        $props["dechirure_perineale"]                   = "bool";
        $props["dechirure_perineale_liste"]             = "enum list|1|2|3|4";
        $props["lesions_traumatiques_parties_molles"]   = "bool";
        $props["dechirure_vaginale"]                    = "bool";
        $props["dechirure_cervicale"]                   = "bool";
        $props["lesion_urinaire"]                       = "bool";
        $props["rupt_uterine"]                          = "bool";
        $props["thrombus"]                              = "bool";
        $props["autre_lesion"]                          = "bool";
        $props["autre_lesion_desc"]                     = "str";
        $props["compte_rendu_delivrance"]               = "text helped";
        $props["consignes_suite_couches"]               = "text helped";

        $props["pathologies_suite_couches"]                    = "bool";
        $props["infection_suite_couches"]                      = "bool";
        $props["infection_nosoc_suite_couches"]                = "bool";
        $props["localisation_infection_suite_couches"]         = "enum list|abcessein|lympsein|vagin|infur|endopuer|infperin|perit|septi|fievre|infpari|inc";
        $props["compl_perineales_suite_couches"]               = "bool";
        $props["details_compl_perineales_suite_couches"]       = "enum list|hematome|suture|abces";
        $props["compl_parietales_suite_couches"]               = "bool";
        $props["detail_compl_parietales_suite_couches"]        = "enum list|hematome|suture|abces";
        $props["compl_allaitement_suite_couches"]              = "bool";
        $props["details_compl_allaitement_suite_couches"]      = "enum list|crev|allait|autres";
        $props["details_comp_compl_allaitement_suite_couches"] = "str";
        $props["compl_thrombo_embo_suite_couches"]             = "bool";
        $props["detail_compl_thrombo_embo_suite_couches"]      = "enum list|thrombophlebsup|phleb|thrombophlebpelv|embpulm|thrombveicereb|hemorr";
        $props["compl_autre_suite_couches"]                    = "bool";
        $props["anemie_suite_couches"]                         = "bool";
        $props["incont_urin_suite_couches"]                    = "bool";
        $props["depression_suite_couches"]                     = "bool";
        $props["fract_obst_coccyx_suite_couches"]              = "bool";
        $props["hemorragie_second_suite_couches"]              = "bool";
        $props["retention_urinaire_suite_couches"]             = "bool";
        $props["psychose_puerpuerale_suite_couches"]           = "bool";
        $props["eclampsie_suite_couches"]                      = "bool";
        $props["insuf_reinale_suite_couches"]                  = "bool";
        $props["disjonction_symph_pub_suite_couches"]          = "bool";
        $props["autre_comp_suite_couches"]                     = "bool";
        $props["desc_autre_comp_suite_couches"]                = "str";
        $props["compl_anesth_suite_couches"]                   = "bool";
        $props["compl_anesth_generale_suite_couches"]          = "enum list|mend|pulm|card|cereb|allerg|autre";
        $props["autre_compl_anesth_generale_suite_couches"]    = "str";
        $props["compl_anesth_locoregion_suite_couches"]        = "enum list|ceph|hypotens|autre";
        $props["autre_compl_anesth_locoregion_suite_couches"]  = "str";
        $props["traitements_sejour_mere"]                      = "bool";
        $props["ttt_preventif_sejour_mere"]                    = "bool";
        $props["antibio_preventif_sejour_mere"]                = "bool";
        $props["desc_antibio_preventif_sejour_mere"]           = "str";
        $props["anticoag_preventif_sejour_mere"]               = "bool";
        $props["desc_anticoag_preventif_sejour_mere"]          = "str";
        $props["antilactation_preventif_sejour_mere"]          = "bool";
        $props["ttt_curatif_sejour_mere"]                      = "bool";
        $props["antibio_curatif_sejour_mere"]                  = "bool";
        $props["desc_antibio_curatif_sejour_mere"]             = "str";
        $props["anticoag_curatif_sejour_mere"]                 = "bool";
        $props["desc_anticoag_curatif_sejour_mere"]            = "str";
        $props["vacc_gammaglob_sejour_mere"]                   = "bool";
        $props["gammaglob_sejour_mere"]                        = "bool";
        $props["vacc_sejour_mere"]                             = "bool";
        $props["transfusion_sejour_mere"]                      = "bool";
        $props["nb_unite_transfusion_sejour_mere"]             = "num";
        $props["interv_sejour_mere"]                           = "bool";
        $props["datetime_interv_sejour_mere"]                  = "dateTime";
        $props["revision_uterine_sejour_mere"]                 = "bool";
        $props["interv_second_hemorr_sejour_mere"]             = "bool";
        $props["type_interv_second_hemorr_sejour_mere"]        = "enum list|emboart|ligarthypogast|ligartuter|veinecave|hysterect";
        $props["autre_interv_sejour_mere"]                     = "bool";
        $props["type_autre_interv_sejour_mere"]                = "enum list|repriseparoi|repriseperinee|evacpariet|laparo|thrombhemorr|steril";
        $props["jour_deces_sejour_mere"]                       = "num";
        $props["deces_cause_obst_sejour_mere"]                 = "bool";
        $props["autopsie_sejour_mere"]                         = "enum list|nondem|ref|faite";
        $props["resultat_autopsie_sejour_mere"]                = "enum list|sansanom|anom";
        $props["anomalie_autopsie_sejour_mere"]                = "str";

        return $props;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        if (!$this->_id) {
            $dossier               = new CDossierPerinat();
            $dossier->grossesse_id = $this->grossesse_id;
            $this->_id             = $dossier->loadMatchingObject();
        }

        return parent::store();
    }

    /**
     * Chargement de la grossesse
     *
     * @return CGrossesse
     */
    function loadRefGrossesse()
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }

    /**
     * Chargement du séjour de l'accouchement
     *
     * @return CSejour
     */
    function loadRefSejourAccouchement()
    {
        return $this->_ref_sejour_accouchement = $this->loadFwdRef("admission_id", true);
    }

    /**
     * Chargement du relevés de constantes des antécédents maternels
     *
     * @return CConstantesMedicales
     */
    public function loadRefConstantesAntecedentsMaternels()
    {
        return $this->_ref_ant_mater_constantes = $this->loadFwdRef('ant_mater_constantes_id');
    }

    /**
     * Chargement du relevés de constantes des antécédents paternels
     *
     * @return CConstantesMedicales
     */
    public function loadRefConstantesAntecedentsPaternels()
    {
        return $this->_ref_pere_constantes = $this->loadFwdRef('pere_constantes_id');
    }

    /**
     * Chargement du relevés de constantes maternels à l'examen d'entrée
     *
     * @return CConstantesMedicales
     */
    public function loadRefConstantesMaternelsAdmission()
    {
        return $this->_ref_adm_mater_constantes = $this->loadFwdRef('adm_mater_constantes_id');
    }

    /**
     * Chargement du relevés de fièvre pendant le travail
     *
     * @return CConstantesMedicales
     */
    public function loadRefConstantesFievreTravail()
    {
        return $this->_ref_fievre_travail_constantes = $this->loadFwdRef('fievre_travail_constantes_id');
    }

    /**
     * Etat de remplissage d'un chapitre du dossier périnatal
     *
     * @return array()
     */
    public function loadEtatDossier()
    {
        $colors = [
            "ndef"    => "transparent",
            "ok"      => "lightCyan",
            "warning" => "orange",
            "error"   => "red",
        ];

        $this->_listChapitres = [
            "debut_grossesse"    => [
                "renseignements"  => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "depistages"      => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "antecedents"     => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "debut_grossesse" => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "premier_contact" => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
            ],
            "suivi_grossesse"    => [
                "tableau_suivi_grossesse"   => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "echographies"              => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "tableau_hospit_grossesse"  => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "transfert"                 => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "cloture_sans_accouchement" => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "synthese_grossesse"        => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "conduite_accouchement"     => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
            ],
            "accouchement"       => [
                "admission"           => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "partogramme"         => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "naissances"          => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "graphique_sspi"      => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "resume_accouchement" => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
            ],
            "suivi_accouchement" => [
                "nouveau_ne_salle_naissance" => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "examens_nouveau_ne"         => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "0",
                    "dev"   => "ok",
                ],
                "resume_sejour_nouveau_ne"   => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "resume_sejour_mere"         => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
                "consult_postnatale"         => [
                    "etat"  => "empty",
                    "color" => $colors["ndef"],
                    "count" => "NA",
                    "dev"   => "ok",
                ],
            ],
        ];

        $grossesse = $this->loadRefGrossesse();
        $grossesse->loadRefsGrossessesAnt();

        // Renseignements généraux
        if (
            $this->activite_pro ||
            $this->activite_pro_pere ||
            $this->fatigue_travail ||
            $this->travail_hebdo ||
            $this->transport_jour
        ) {
            $this->_listChapitres["debut_grossesse"]["renseignements"]["color"] = $colors["ok"];
        }
        // Dépistages
        $this->_listChapitres["debut_grossesse"]["depistages"]["count"] = $grossesse->countBackRefs("depistages");
        if ($this->_listChapitres["debut_grossesse"]["depistages"]["count"]) {
            $this->_listChapitres["debut_grossesse"]["depistages"]["color"] = $colors["ok"];
        }
        // Antécédents
        if (
            $this->patho_ant === "0" ||
            $this->chir_ant === "0" ||
            $this->gyneco_ant === "0" ||
            count($grossesse->_ref_grossesses_ant) > 0
        ) {
            $this->_listChapitres["debut_grossesse"]["antecedents"]["color"] = $colors["ok"];
        }
        if (
            $this->patho_ant === "1" ||
            $this->chir_ant === "1" ||
            $this->gyneco_ant === "1"
        ) {
            $this->_listChapitres["debut_grossesse"]["antecedents"]["color"] = $colors["warning"];
        }
        // Début de grossesse
        if ($this->souhait_grossesse == !"") {
            $this->_listChapitres["debut_grossesse"]["debut_grossesse"]["color"] = $colors["ok"];
        }
        // Premier contact
        if ($this->date_premier_contact) {
            $this->_listChapitres["debut_grossesse"]["premier_contact"]["color"] = $colors["ok"];
        }
        // Suivis de grossesse
        $this->_listChapitres["suivi_grossesse"]["tableau_suivi_grossesse"]["count"] = $grossesse->countBackRefs(
            "consultations"
        );
        if ($this->_listChapitres["suivi_grossesse"]["tableau_suivi_grossesse"]["count"]) {
            $this->_listChapitres["suivi_grossesse"]["tableau_suivi_grossesse"]["color"] = $colors["ok"];
        }
        // Surveillance échographique
        $this->_listChapitres["suivi_grossesse"]["echographies"]["count"] = $grossesse->countBackRefs("echographies");
        if ($this->_listChapitres["suivi_grossesse"]["echographies"]["count"]) {
            $this->_listChapitres["suivi_grossesse"]["echographies"]["color"] = $colors["ok"];
        }
        // Hospitalisations
        $this->_listChapitres["suivi_grossesse"]["tableau_hospit_grossesse"]["count"] = $grossesse->countBackRefs(
            "sejours"
        );
        if ($this->_listChapitres["suivi_grossesse"]["tableau_hospit_grossesse"]["count"]) {
            $this->_listChapitres["suivi_grossesse"]["tableau_hospit_grossesse"]["color"] = $colors["ok"];
        }
        // Transfert maternel anténatal
        if ($this->transf_antenat == "n") {
            $this->_listChapitres["suivi_grossesse"]["transfert"]["color"] = $colors["ok"];
        }
        if ($this->transf_antenat == "imp") {
            $this->_listChapitres["suivi_grossesse"]["transfert"]["color"] = $colors["error"];
        }
        if ($this->transf_antenat == "reseau" || $this->transf_antenat == "hreseau") {
            $this->_listChapitres["suivi_grossesse"]["transfert"]["color"] = $colors["warning"];
        }
        // Cloture du dossier sans accouchement
        if ($this->type_terminaison_grossesse) {
            $this->_listChapitres["suivi_grossesse"]["cloture_sans_accouchement"]["color"] = $colors["warning"];
        }
        // Synthèse de la grossesse
        if ($this->date_validation_synthese) {
            $this->_listChapitres["suivi_grossesse"]["synthese_grossesse"]["color"] = $colors["ok"];
        }
        // Conduite à tenir
        if ($this->date_decision_conduite_a_tenir_acc) {
            $this->_listChapitres["suivi_grossesse"]["conduite_accouchement"]["color"] = $colors["ok"];
        }
        // Admission
        $sejour_accouchement = $this->loadRefSejourAccouchement();
        if ($sejour_accouchement->entree_reelle) {
            $this->_listChapitres["accouchement"]["admission"]["color"] = $colors["ok"];
        }
        // Diagramme d'accouchement
        if ($grossesse->datetime_debut_travail) {
            $this->_listChapitres["accouchement"]["partogramme"]["color"] = $colors["ok"];
        }
        // Naissances
        $this->_listChapitres["accouchement"]["naissances"]["count"] = $grossesse->countBackRefs("naissances");
        if ($this->_listChapitres["accouchement"]["naissances"]["count"]) {
            $this->_listChapitres["accouchement"]["naissances"]["color"] = $colors["ok"];
        }
        // Surveillance de la mère en salle de naissance
        $grossesse->loadLastSejour();
        $operation = new COperation();
        if ($grossesse->_ref_last_sejour) {
            $grossesse->_ref_last_sejour->loadRefsOperations();
            if ($grossesse->_ref_last_sejour->_ref_last_operation->_id) {
                $operation = $grossesse->_ref_last_sejour->_ref_last_operation;
            }
        }
        if ($operation->_id && $operation->graph_pack_sspi_id) {
            $this->_listChapitres["accouchement"]["graphique_sspi"]["color"] = $colors["ok"];
        }
        // Résumé d'accouchement
        if ($this->lieu_accouchement) {
            $this->_listChapitres["accouchement"]["resume_accouchement"]["color"] = $colors["ok"];
        }
        // Nouveau-né en salle de naissance
        $this->_listChapitres["suivi_accouchement"]["nouveau_ne_salle_naissance"]["count"] = $this->_listChapitres["accouchement"]["naissances"]["count"];
        $apgar1ok                                                                          = 0;
        $apgar1ko                                                                          = 0;
        $apgar3ok                                                                          = 0;
        $apgar3ko                                                                          = 0;
        $apgar5ok                                                                          = 0;
        $apgar5ko                                                                          = 0;
        $apgar10ok                                                                         = 0;
        $apgar10ko                                                                         = 0;
        foreach ($this->_ref_grossesse->loadRefsNaissances() as $naissance) {
            if ($naissance->_apgar_1 && $naissance->_apgar_1 < 7) {
                $apgar1ko++;
            } elseif ($naissance->_apgar_1) {
                $apgar1ok++;
            }
            if ($naissance->_apgar_3 && $naissance->_apgar_3 < 7) {
                $apgar3ko++;
            } elseif ($naissance->_apgar_3) {
                $apgar3ok++;
            }
            if ($naissance->_apgar_5 && $naissance->_apgar_5 < 7) {
                $apgar5ko++;
            } elseif ($naissance->_apgar_5) {
                $apgar5ok++;
            }
            if ($naissance->_apgar_10 && $naissance->_apgar_10 < 7) {
                $apgar10ko++;
            } elseif ($naissance->_apgar_10) {
                $apgar10ok++;
            }
        }

        if ($apgar1ok == $this->_listChapitres["suivi_accouchement"]["nouveau_ne_salle_naissance"]["count"] && $this->_listChapitres["suivi_accouchement"]["nouveau_ne_salle_naissance"]["count"] != 0) {
            $this->_listChapitres["suivi_accouchement"]["nouveau_ne_salle_naissance"]["color"] = $colors["ok"];
        } elseif ($apgar1ko) {
            $this->_listChapitres["suivi_accouchement"]["nouveau_ne_salle_naissance"]["color"] = $colors["warning"];
        }
        // Examens du nouveau né
        $this->_listChapitres["suivi_accouchement"]["examens_nouveau_ne"]["count"] = $grossesse->countBackRefs(
            "examens_nouveau_ne"
        );
        if ($this->_listChapitres["suivi_accouchement"]["examens_nouveau_ne"]["count"]) {
            $this->_listChapitres["suivi_accouchement"]["examens_nouveau_ne"]["color"] = $colors["ok"];
        }
        // Résumé du séjour du nouveau né
        foreach ($this->_ref_grossesse->loadRefsNaissances() as $naissance) {
            if ($naissance->loadRefSejourEnfant()->sortie_reelle) {
                $this->_listChapitres["suivi_accouchement"]["resume_sejour_nouveau_ne"]["color"] = $colors["ok"];
            }
        }
        // Résumé du séjour de la mère
        if ($sejour_accouchement->sortie_reelle) {
            $this->_listChapitres["suivi_accouchement"]["resume_sejour_mere"]["color"] = $colors["ok"];
        }

        $this->loadRefsConsultationsPostNatale();
        // Consultation postnatale
        if (count($this->_ref_consultations_post_natale)) {
            $this->_listChapitres["suivi_accouchement"]["consult_postnatale"]["color"] = $colors["ok"];
        }
    }

    /**
     * Chargement des consultations post-natales
     *
     * @return CConsultationPostNatale[]
     */
    function loadRefsConsultationsPostNatale()
    {
        return $this->_ref_consultations_post_natale = $this->loadBackRefs("consultations_post_natales", "date");
    }

    /**
     * Chargement une consultation post natale vierge
     *
     * @return CConsultationPostNatale
     */
    static function emptyConsultationPostNatale()
    {
        $consult_postnatale = new CConsultationPostNatale();
        $consult_postnatale->loadRefConstantesMaternelles();

        return $consult_postnatale;
    }

    /**
     * Chargement des accouchements
     *
     * @return CAccouchement[]
     */
    function loadRefsAccouchement()
    {
        return $this->_ref_accouchements = $this->loadBackRefs("accouchements", "date");
    }

    /**
     * Get the mother's pathologies fields (perinatal folder)
     *
     * @param string $keywords
     *
     * @return array
     */
    public function getMotherPathologiesFields(string $keywords = null): array
    {
        $bool_pathologies_fields = [
            "metrorragie_1er_trim"                 => CAppUI::tr("CDossierPerinat-metrorragie_1er_trim"),
            "metrorragie_2e_3e_trim"               => CAppUI::tr("CDossierPerinat-metrorragie_2e_3e_trim"),
            "menace_acc_premat"                    => CAppUI::tr("CDossierPerinat-menace_acc_premat"),
            "rupture_premat_membranes"             => CAppUI::tr("CDossierPerinat-rupture_premat_membranes"),
            "anomalie_liquide_amniotique"          => CAppUI::tr("CDossierPerinat-anomalie_liquide_amniotique"),
            "autre_patho_gravidique"               => CAppUI::tr("CDossierPerinat-autre_patho_gravidique"),
            "patho_grav_vomissements"              => CAppUI::tr("CDossierPerinat-patho_grav_vomissements"),
            "patho_grav_herpes_gest"               => CAppUI::tr("CDossierPerinat-patho_grav_herpes_gest"),
            "patho_grav_dermatose_pup"             => CAppUI::tr("CDossierPerinat-patho_grav_dermatose_pup"),
            "patho_grav_placenta_praevia_non_hemo" => CAppUI::tr(
                "CDossierPerinat-patho_grav_placenta_praevia_non_hemo"
            ),
            "patho_grav_chorio_amniotite"          => CAppUI::tr("CDossierPerinat-patho_grav_chorio_amniotite"),
            "patho_grav_transf_foeto_mat"          => CAppUI::tr("CDossierPerinat-patho_grav_transf_foeto_mat"),
            "patho_grav_beance_col"                => CAppUI::tr("CDossierPerinat-patho_grav_beance_col"),
            "patho_grav_cerclage"                  => CAppUI::tr("CDossierPerinat-patho_grav_cerclage"),
            "hypertension_arterielle"              => CAppUI::tr("CDossierPerinat-hypertension_arterielle"),
            "proteinurie"                          => CAppUI::tr("CDossierPerinat-proteinurie"),
            "diabete"                              => CAppUI::tr("CDossierPerinat-diabete"),
            "infection_urinaire"                   => CAppUI::tr("CDossierPerinat-infection_urinaire"),
            "infection_cervico_vaginale"           => CAppUI::tr("CDossierPerinat-infection_cervico_vaginale"),
            "anemie_mat_pdt_grossesse"             => CAppUI::tr("CDossierPerinat-anemie_mat_pdt_grossesse"),
            "tombopenie_mat_pdt_grossesse"         => CAppUI::tr("CDossierPerinat-tombopenie_mat_pdt_grossesse"),
            "faible_prise_poid_mat_pdt_grossesse"  => CAppUI::tr("CDossierPerinat-faible_prise_poid_mat_pdt_grossesse"),
            "malnut_mat_pdt_grossesse"             => CAppUI::tr("CDossierPerinat-malnut_mat_pdt_grossesse"),
            "cholestase_mat_pdt_grossesse"         => CAppUI::tr("CDossierPerinat-cholestase_mat_pdt_grossesse"),
            "steatose_hep_mat_pdt_grossesse"       => CAppUI::tr("CDossierPerinat-steatose_hep_mat_pdt_grossesse"),
            "thrombophl_sup_mat_pdt_grossesse"     => CAppUI::tr("CDossierPerinat-thrombophl_sup_mat_pdt_grossesse"),
            "thrombophl_prof_mat_pdt_grossesse"    => CAppUI::tr("CDossierPerinat-thrombophl_prof_mat_pdt_grossesse"),
            "asthme_mat_pdt_grossesse"             => CAppUI::tr("CDossierPerinat-asthme_mat_pdt_grossesse"),
            "cardiopathie_mat_pdt_grossesse"       => CAppUI::tr("CDossierPerinat-cardiopathie_mat_pdt_grossesse"),
            "epilepsie_mat_pdt_grossesse"          => CAppUI::tr("CDossierPerinat-epilepsie_mat_pdt_grossesse"),
            "depression_mat_pdt_grossesse"         => CAppUI::tr("CDossierPerinat-depression_mat_pdt_grossesse"),
            "patho_gyneco_mat_pdt_grossesse"       => CAppUI::tr("CDossierPerinat-patho_gyneco_mat_pdt_grossesse"),
            "mst_mat_pdt_grossesse"                => CAppUI::tr("CDossierPerinat-mst_mat_pdt_grossesse"),
            "synd_douleur_abdo_mat_pdt_grossesse"  => CAppUI::tr("CDossierPerinat-synd_douleur_abdo_mat_pdt_grossesse"),
            "synd_infect_mat_pdt_grossesse"        => CAppUI::tr("CDossierPerinat-synd_infect_mat_pdt_grossesse"),
        ];

        foreach ($bool_pathologies_fields as $patho_key => $patho_name) {
            if ($this->$patho_key) {
                unset($bool_pathologies_fields[$patho_key]);
            }
        }

        if ($keywords) {
            foreach ($bool_pathologies_fields as $patho_key => $patho_name) {
                if (strpos(strtolower($patho_name), strtolower($keywords)) === false) {
                    unset($bool_pathologies_fields[$patho_key]);
                }
            }
        }

        ksort($bool_pathologies_fields);

        return $bool_pathologies_fields;
    }

    /**
     * Chargement un accouchement vierge
     *
     * @return CAccouchement
     */
    static function emptyAccouchement()
    {
        return new CAccouchement();
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    public function fillLimitedTemplate(&$template, $prefix = null)
    {
        $dossier_perinatal_section = CAppUI::tr('CDossierPerinat');

        foreach ($this->_props as $_field => $_prop) {
            // Skip fields
            if (in_array($_field, self::FIELDS_MODELE_SKIPPED)) {
                continue;
            }

            $value = $this->$_field;

            // Only for boolean
            if (strpos($_prop, 'bool') !== false) {
                if ($value == 1) {
                    $value = CAppUI::tr("common-Yes");
                } elseif ($value == 0) {
                    $value = CAppUI::tr("common-No");
                }
            }

            // Only for enum
            if (strpos($_prop, 'enum') !== false) {
                $value = CAppUI::tr("CDossierPerinat.$_field.$value");
            }

            $template->addProperty(
                $prefix . " - " . $dossier_perinatal_section . " - " . CAppUI::tr('CDossierPerinat-' . $_field),
                $value
            );
        }
    }
}
