<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Gestion des dossiers de naissance associés aux grossesses.
 */
class CNaissance extends CMbObject implements IGroupRelated
{
    // DB Table key
    public $naissance_id;

    // DB References
    public $sejour_maman_id;
    public $sejour_enfant_id;
    public $operation_id;
    public $grossesse_id;

    // DB Fields
    public $hors_etab;
    public $date_time;
    public $rang;
    public $num_naissance;
    public $by_caesarean;

    public $_heure;
    public $_date_min;
    public $_date_max;
    public $_datetime_min;
    public $_datetime_max;

    public $interruption;
    public $num_semaines;
    public $rques;
    public $type_allaitement;

    // Ajout dossier périnatal
    // Nouveau né en salle de naissance
    public $presence_pediatre;
    public $pediatre_id;
    public $presence_anesth;
    public $anesth_id;
    public $apgar_coeur_1;
    public $apgar_coeur_3;
    public $apgar_coeur_5;
    public $apgar_coeur_10;
    public $apgar_respi_1;
    public $apgar_respi_3;
    public $apgar_respi_5;
    public $apgar_respi_10;
    public $apgar_tonus_1;
    public $apgar_tonus_3;
    public $apgar_tonus_5;
    public $apgar_tonus_10;
    public $apgar_reflexes_1;
    public $apgar_reflexes_3;
    public $apgar_reflexes_5;
    public $apgar_reflexes_10;
    public $apgar_coloration_1;
    public $apgar_coloration_3;
    public $apgar_coloration_5;
    public $apgar_coloration_10;
    public $ph_ao;
    public $ph_v;
    public $base_deficit;
    public $pco2;
    public $lactates;
    public $nouveau_ne_endormi;
    public $accueil_peau_a_peau;
    public $debut_allait_salle_naissance;
    public $temp_salle_naissance;

    public $monitorage;
    public $monit_frequence_cardiaque;
    public $monit_saturation;
    public $monit_glycemie;
    public $monit_incubateur;
    public $monit_remarques;

    public $reanimation;
    public $rea_aspi_laryngo;
    public $rea_ventil_masque;
    public $rea_o2_sonde;
    public $rea_ppc_nasale;
    public $rea_duree_ppc_nasale;
    public $rea_ventil_tube_endo;
    public $rea_duree_ventil_tube_endo;
    public $rea_intub_tracheale;
    public $rea_min_vie_intub_tracheale;
    public $rea_massage_card;
    public $rea_injection_medic;
    public $rea_injection_medic_adre;
    public $rea_injection_medic_surfa;
    public $rea_injection_medic_gluc;
    public $rea_injection_medic_autre;
    public $rea_injection_medic_autre_desc;
    public $rea_autre_geste;
    public $rea_autre_geste_desc;
    public $duree_totale_rea;
    public $temp_fin_rea;
    public $gly_fin_rea;
    public $etat_fin_rea;
    public $rea_remarques;

    public $prophy_vit_k;
    public $prophy_vit_k_type;
    public $prophy_desinfect_occulaire;
    public $prophy_asp_naso_phar;
    public $prophy_perm_choanes;
    public $prophy_perm_oeso;
    public $prophy_perm_anale;
    public $prophy_emission_urine;
    public $prophy_emission_meconium;
    public $prophy_autre;
    public $prophy_autre_desc;
    public $prophy_remarques;

    public $cortico;
    public $nb_cures_cortico;
    public $dern_cure_cortico;
    public $delai_cortico_acc_j;
    public $delai_cortico_acc_h;
    public $prev_cortico_remarques;

    public $contexte_infectieux;
    public $infect_facteurs_risque_infect;
    public $infect_rpm_sup_12h;
    public $infect_liquide_teinte;
    public $infect_strepto_b;
    public $infect_fievre_mat;
    public $infect_maternelle;
    public $infect_autre;
    public $infect_autre_desc;
    public $infect_prelev_bacterio;
    public $infect_prelev_gatrique;
    public $infect_prelev_autre_periph;
    public $infect_prelev_placenta;
    public $infect_prelev_sang;
    public $infect_antibio;
    public $infect_antibio_desc;
    public $infect_remarques;

    public $prelev_bacterio_mere;
    public $prelev_bacterio_vaginal_mere;
    public $prelev_bacterio_vaginal_mere_germe;
    public $prelev_bacterio_urinaire_mere;
    public $prelev_bacterio_urinaire_mere_germe;
    public $antibiotherapie_antepart_mere;
    public $antibiotherapie_antepart_mere_desc;
    public $antibiotherapie_perpart_mere;
    public $antibiotherapie_perpart_mere_desc;

    public $nouveau_ne_constantes_id;

    public $mode_sortie;
    public $mode_sortie_autre;
    public $min_vie_transmut;
    public $resp_transmut_id;
    public $motif_transmut;
    public $detail_motif_transmut;
    public $lieu_transf;
    public $type_etab_transf;
    public $dest_transf;
    public $dest_transf_autre;
    public $mode_transf;
    public $delai_appel_arrivee_transp;
    public $dist_mater_transf;
    public $raison_transf_report;
    public $raison_transf_report_autre;
    public $remarques_transf;

    // Résumé du séjour du nouveau né
    public $pathologies;
    public $lesion_traumatique;
    public $lesion_faciale;
    public $paralysie_faciale;
    public $cephalhematome;
    public $paralysie_plexus_brachial_sup;
    public $paralysie_plexus_brachial_inf;
    public $lesion_cuir_chevelu;
    public $fracture_clavicule;
    public $autre_lesion;
    public $autre_lesion_desc;

    public $infection;
    public $infection_degre;
    public $infection_origine;
    public $infection_sang;
    public $infection_lcr;
    public $infection_poumon;
    public $infection_urines;
    public $infection_digestif;
    public $infection_ombilic;
    public $infection_oeil;
    public $infection_os_articulations;
    public $infection_peau;
    public $infection_autre;
    public $infection_autre_desc;
    public $infection_strepto_b;
    public $infection_autre_strepto;
    public $infection_staphylo_dore;
    public $infection_autre_staphylo;
    public $infection_haemophilus;
    public $infection_listeria;
    public $infection_pneumocoque;
    public $infection_autre_gplus;
    public $infection_coli;
    public $infection_proteus;
    public $infection_klebsiele;
    public $infection_autre_gmoins;
    public $infection_chlamydiae;
    public $infection_mycoplasme;
    public $infection_candida;
    public $infection_toxoplasme;
    public $infection_autre_parasite;
    public $infection_cmv;
    public $infection_rubeole;
    public $infection_herpes;
    public $infection_varicelle;
    public $infection_vih;
    public $infection_autre_virus;
    public $infection_autre_virus_desc;
    public $infection_germe_non_trouve;

    public $ictere;
    public $ictere_prema;
    public $ictere_intense_terme;
    public $ictere_allo_immun_abo;
    public $ictere_allo_immun_rh;
    public $ictere_allo_immun_autre;
    public $ictere_autre_origine;
    public $ictere_autre_origine_desc;
    public $ictere_phototherapie;
    public $ictere_type_phototherapie;

    public $trouble_regul_thermique;
    public $hyperthermie;
    public $hypothermie_legere;
    public $hypothermie_grave;

    public $anom_cong;
    public $anom_cong_isolee;
    public $anom_cong_synd_polyformatif;
    public $anom_cong_tube_neural;
    public $anom_cong_fente_labio_palatine;
    public $anom_cong_atresie_oesophage;
    public $anom_cong_omphalocele;
    public $anom_cong_reduc_absence_membres;
    public $anom_cong_hydrocephalie;
    public $anom_cong_hydrocephalie_type;
    public $anom_cong_malform_card;
    public $anom_cong_malform_card_type;
    public $anom_cong_hanches_luxables;
    public $anom_cong_hanches_luxables_type;
    public $anom_cong_malform_reinale;
    public $anom_cong_malform_reinale_type;
    public $anom_cong_autre;
    public $anom_cong_autre_desc;
    public $anom_cong_chromosomique;
    public $anom_cong_genique;
    public $anom_cong_trisomie_21;
    public $anom_cong_trisomie_type;
    public $anom_cong_chrom_gen_autre;
    public $anom_cong_chrom_gen_autre_desc;
    public $anom_cong_description_clair;
    public $anom_cong_moment_diag;

    public $autre_pathologie;
    public $patho_resp;
    public $tachypnee;
    public $autre_detresse_resp_neonat;
    public $acces_cyanose;
    public $apnees_prema;
    public $apnees_autre;
    public $inhalation_meco_sans_pneumopath;
    public $inhalation_meco_avec_pneumopath;
    public $inhalation_lait;
    public $patho_cardiovasc;
    public $trouble_du_rythme;
    public $hypertonie_vagale;
    public $souffle_a_explorer;
    public $patho_neuro;
    public $hypothonie;
    public $hypertonie;
    public $irrit_cerebrale;
    public $mouv_anormaux;
    public $convulsions;
    public $patho_dig;
    public $alim_sein_difficile;
    public $alim_lente;
    public $stagnation_pond;
    public $perte_poids_sup_10_pourc;
    public $regurgitations;
    public $vomissements;
    public $reflux_gatro_eoso;
    public $oesophagite;
    public $hematemese;
    public $synd_occlusif;
    public $trouble_succion_deglut;
    public $patho_hemato;
    public $anemie_neonat;
    public $anemie_transf_foeto_mat;
    public $anemie_transf_foeto_foet;
    public $drepano_positif;
    public $maladie_hemo;
    public $thrombopenie;
    public $patho_metab;
    public $hypogly_diab_mere_gest;
    public $hypogly_diab_mere_nid;
    public $hypogly_diab_mere_id;
    public $hypogly_neonat_transitoire;
    public $hypocalcemie;
    public $intoxication;
    public $synd_sevrage_toxico;
    public $synd_sevrage_medic;
    public $tabac_maternel;
    public $alcool_maternel;
    public $autre_patho_autre;
    public $rhinite_neonat;
    public $patho_dermato;
    public $autre_atho_autre_thesaurus;

    public $actes_effectues;
    public $caryotype;
    public $etf;
    public $eeg;
    public $ecg;
    public $fond_oeil;
    public $antibiotherapie;
    public $oxygenotherapie;
    public $echographie_cardiaque;
    public $echographie_cerebrale;
    public $echographie_hanche;
    public $echographie_hepatique;
    public $echographie_reinale;
    public $exsanguino_transfusion;
    public $intubation;
    public $incubateur;
    public $injection_gamma_globulines;
    public $togd;
    public $radio_thoracique;
    public $reeducation;
    public $autre_acte;
    public $autre_acte_desc;

    public $mesures_prophylactiques;
    public $hep_b_injection_immunoglob;
    public $vaccinations;
    public $vacc_hep_b;
    public $vacc_hepp_bcg;
    public $depistage_sanguin;
    public $hyperphenylalanemie;
    public $hypothyroidie;
    public $hyperplasie_cong_surrenales;
    public $drepanocytose;
    public $mucoviscidose;
    public $test_audition;
    public $etat_test_audition;
    public $supp_vitaminique;
    public $supp_vitaminique_desc;
    public $autre_mesure_proph;
    public $autre_mesure_proph_desc;

    public $mode_sortie_mater;
    public $mode_sortie_mater_autre;
    public $jour_vie_transmut_mater;
    public $heure_vie_transmut_mater;
    public $resp_transmut_mater_id;
    public $motif_transmut_mater;
    public $detail_motif_transmut_mater;
    public $lieu_transf_mater;
    public $type_etab_transf_mater;
    public $dest_transf_mater;
    public $dest_transf_mater_autre;
    public $mode_transf_mater;
    public $delai_appel_arrivee_transp_mater;
    public $dist_mater_transf_mater;
    public $raison_transf_mater_report;
    public $raison_transf_report_mater_autre;
    public $surv_part_sortie_mater;
    public $surv_part_sortie_mater_desc;
    public $remarques_transf_mater;

    public $poids_fin_sejour;
    public $alim_fin_sejour;
    public $comp_alim_fin_sejour;
    public $nature_comp_alim_fin_sejour;
    public $moyen_comp_alim_fin_sejour;
    public $indic_comp_alim_fin_sejour;
    public $indic_comp_alim_fin_sejour_desc;

    public $retour_mater;
    public $date_retour_mater;
    public $duree_transfert;

    public $moment_deces;
    public $date_deces;
    public $age_deces_jours;
    public $age_deces_heures;
    public $cause_deces;
    public $cause_deces_desc;
    public $autopsie;

    // Dossier provisoire
    public $_provisoire;

    // dates
    public $_day_relative;

    // Scores Apgar
    public $_apgar_1;
    public $_apgar_3;
    public $_apgar_5;
    public $_apgar_10;

    // Service Néona
    public $_service_neonatalogie;
    public $_consult_pediatre;

    /** @var COperation */
    public $_ref_operation;

    /** @var CGrossesse */
    public $_ref_grossesse;

    /** @var CExamenNouveauNe[] */
    public $_ref_examen_nouveau_ne;

    /** @var CExamenNouveauNe */
    public $_ref_last_examen_nouveau_ne;

    /** @var CSejour */
    public $_ref_sejour_enfant;

    /** @var CSejour */
    public $_ref_sejour_maman;

    /** @var CConstantesMedicales */
    public $_ref_nouveau_ne_constantes;

    /** @var CNaissanceRea[] */
    public $_ref_resuscitators;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'naissance';
        $spec->key   = 'naissance_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                     = parent::getProps();
        $props["operation_id"]     = "ref class|COperation back|naissances";
        $props["grossesse_id"]     = "ref class|CGrossesse back|naissances";
        $props["sejour_maman_id"]  = "ref notNull class|CSejour back|naissances";
        $props["sejour_enfant_id"] = "ref notNull class|CSejour back|naissance";
        $props["hors_etab"]        = "bool default|0";
        $props["date_time"]        = "dateTime";
        $props["rang"]             = "num pos";
        $props["num_naissance"]    = "num pos";
        $props["interruption"]     = "enum list|fausse_couche|IMG|mort_in_utero";
        $props["num_semaines"]     = "enum list|inf_15|15_22|sup_22_sup_500g|sup_15";
        $props["rques"]            = "text helped";
        $props["type_allaitement"] = "enum list|maternel|artificiel|mixte";
        $props["by_caesarean"]     = "bool notNull default|0";

        $props["presence_pediatre"] = "enum list|non|avant|apres";
        $props["pediatre_id"]       = "ref class|CMediusers back|naissances_pediatre";
        $props["presence_anesth"]   = "enum list|non|avant|apres";
        $props["anesth_id"]         = "ref class|CMediusers back|naissances_anesth";

        $props["apgar_coeur_1"]       = "num min|0 max|2";
        $props["apgar_coeur_3"]       = "num min|0 max|2";
        $props["apgar_coeur_5"]       = "num min|0 max|2";
        $props["apgar_coeur_10"]      = "num min|0 max|2";
        $props["apgar_respi_1"]       = "num min|0 max|2";
        $props["apgar_respi_3"]       = "num min|0 max|2";
        $props["apgar_respi_5"]       = "num min|0 max|2";
        $props["apgar_respi_10"]      = "num min|0 max|2";
        $props["apgar_tonus_1"]       = "num min|0 max|2";
        $props["apgar_tonus_3"]       = "num min|0 max|2";
        $props["apgar_tonus_5"]       = "num min|0 max|2";
        $props["apgar_tonus_10"]      = "num min|0 max|2";
        $props["apgar_reflexes_1"]    = "num min|0 max|2";
        $props["apgar_reflexes_3"]    = "num min|0 max|2";
        $props["apgar_reflexes_5"]    = "num min|0 max|2";
        $props["apgar_reflexes_10"]   = "num min|0 max|2";
        $props["apgar_coloration_1"]  = "num min|0 max|2";
        $props["apgar_coloration_3"]  = "num min|0 max|2";
        $props["apgar_coloration_5"]  = "num min|0 max|2";
        $props["apgar_coloration_10"] = "num min|0 max|2";
        $props["_apgar_1"]            = "num min|0 max|10";
        $props["_apgar_3"]            = "num min|0 max|10";
        $props["_apgar_5"]            = "num min|0 max|10";
        $props["_apgar_10"]           = "num min|0 max|10";

        $props["ph_ao"]                        = "float";
        $props["ph_v"]                         = "float";
        $props["base_deficit"]                 = "str";
        $props["pco2"]                         = "num";
        $props["lactates"]                     = "str";
        $props["nouveau_ne_endormi"]           = "bool";
        $props["accueil_peau_a_peau"]          = "bool";
        $props["debut_allait_salle_naissance"] = "bool";
        $props["temp_salle_naissance"]         = "float";

        $props["monitorage"]                = "bool";
        $props["monit_frequence_cardiaque"] = "bool";
        $props["monit_saturation"]          = "bool";
        $props["monit_glycemie"]            = "bool";
        $props["monit_incubateur"]          = "bool";
        $props["monit_remarques"]           = "text";

        $props["reanimation"]                    = "bool";
        $props["rea_aspi_laryngo"]               = "bool";
        $props["rea_ventil_masque"]              = "bool";
        $props["rea_o2_sonde"]                   = "bool";
        $props["rea_ppc_nasale"]                 = "bool";
        $props["rea_duree_ppc_nasale"]           = "num";
        $props["rea_ventil_tube_endo"]           = "bool";
        $props["rea_duree_ventil_tube_endo"]     = "num";
        $props["rea_intub_tracheale"]            = "bool";
        $props["rea_min_vie_intub_tracheale"]    = "num";
        $props["rea_massage_card"]               = "bool";
        $props["rea_injection_medic"]            = "bool";
        $props["rea_injection_medic_adre"]       = "bool";
        $props["rea_injection_medic_surfa"]      = "bool";
        $props["rea_injection_medic_gluc"]       = "bool";
        $props["rea_injection_medic_autre"]      = "bool";
        $props["rea_injection_medic_autre_desc"] = "str";
        $props["rea_autre_geste"]                = "bool";
        $props["rea_autre_geste_desc"]           = "str";
        $props["duree_totale_rea"]               = "num";
        $props["temp_fin_rea"]                   = "float";
        $props["gly_fin_rea"]                    = "float";
        $props["etat_fin_rea"]                   = "enum list|sat|surv|mut|transf";
        $props["rea_remarques"]                  = "text";

        $props["prophy_vit_k"]               = "bool";
        $props["prophy_vit_k_type"]          = "enum list|parent|oral";
        $props["prophy_desinfect_occulaire"] = "bool";
        $props["prophy_asp_naso_phar"]       = "bool";
        $props["prophy_perm_choanes"]        = "bool";
        $props["prophy_perm_oeso"]           = "bool";
        $props["prophy_perm_anale"]          = "bool";
        $props["prophy_emission_urine"]      = "bool";
        $props["prophy_emission_meconium"]   = "bool";
        $props["prophy_autre"]               = "bool";
        $props["prophy_autre_desc"]          = "str";
        $props["prophy_remarques"]           = "text";

        $props["cortico"]                             = "bool";
        $props["nb_cures_cortico"]                    = "num";
        $props["dern_cure_cortico"]                   = "enum list|comp|incomp";
        $props["delai_cortico_acc_j"]                 = "num";
        $props["delai_cortico_acc_h"]                 = "num";
        $props["prev_cortico_remarques"]              = "text";
        $props["contexte_infectieux"]                 = "bool";
        $props["infect_facteurs_risque_infect"]       = "bool";
        $props["infect_rpm_sup_12h"]                  = "bool";
        $props["infect_liquide_teinte"]               = "bool";
        $props["infect_strepto_b"]                    = "bool";
        $props["infect_fievre_mat"]                   = "bool";
        $props["infect_maternelle"]                   = "bool";
        $props["infect_autre"]                        = "bool";
        $props["infect_autre_desc"]                   = "str";
        $props["infect_prelev_bacterio"]              = "bool";
        $props["infect_prelev_gatrique"]              = "bool";
        $props["infect_prelev_autre_periph"]          = "bool";
        $props["infect_prelev_placenta"]              = "bool";
        $props["infect_prelev_sang"]                  = "bool";
        $props["infect_antibio"]                      = "bool";
        $props["infect_antibio_desc"]                 = "str";
        $props["infect_remarques"]                    = "text";
        $props["prelev_bacterio_mere"]                = "bool";
        $props["prelev_bacterio_vaginal_mere"]        = "bool";
        $props["prelev_bacterio_vaginal_mere_germe"]  = "str";
        $props["prelev_bacterio_urinaire_mere"]       = "bool";
        $props["prelev_bacterio_urinaire_mere_germe"] = "str";
        $props["antibiotherapie_antepart_mere"]       = "bool";
        $props["antibiotherapie_antepart_mere_desc"]  = "str";
        $props["antibiotherapie_perpart_mere"]        = "bool";
        $props["antibiotherapie_perpart_mere_desc"]   = "str";

        $props['nouveau_ne_constantes_id'] = "ref class|CConstantesMedicales back|cstes_nouveau_ne";

        $props["mode_sortie"]                = "enum list|mere|mut|transfres|transfhres|deces|autre";
        $props["mode_sortie_autre"]          = "str";
        $props["min_vie_transmut"]           = "num";
        $props["resp_transmut_id"]           = "ref class|CMediusers back|naissances_transmut";
        $props["motif_transmut"]             = "enum list|prema|hypotroph|detrespi|risqueinfect|malform|autrepatho";
        $props["detail_motif_transmut"]      = "str";
        $props["lieu_transf"]                = "str";
        $props["type_etab_transf"]           = "enum list|1|2";
        $props["dest_transf"]                = "enum list|rea|intens|neonat|chir|neonatmater|autre";
        $props["dest_transf_autre"]          = "str";
        $props["mode_transf"]                = "enum list|intra|extra|samuped|samuns|ambulance|voiture";
        $props["delai_appel_arrivee_transp"] = "num";
        $props["dist_mater_transf"]          = "num";
        $props["raison_transf_report"]       = "enum list|place|transp|autre";
        $props["raison_transf_report_autre"] = "str";
        $props["remarques_transf"]           = "text";

        $props["pathologies"]                     = "bool";
        $props["lesion_traumatique"]              = "bool";
        $props["lesion_faciale"]                  = "bool";
        $props["paralysie_faciale"]               = "bool";
        $props["cephalhematome"]                  = "bool";
        $props["paralysie_plexus_brachial_sup"]   = "bool";
        $props["paralysie_plexus_brachial_inf"]   = "bool";
        $props["lesion_cuir_chevelu"]             = "bool";
        $props["fracture_clavicule"]              = "bool";
        $props["autre_lesion"]                    = "bool";
        $props["autre_lesion_desc"]               = "str";
        $props["infection"]                       = "bool";
        $props["infection_degre"]                 = "enum list|risque|colo|prob|prouv";
        $props["infection_origine"]               = "enum list|materfoet|nosoc|inc";
        $props["infection_sang"]                  = "bool";
        $props["infection_lcr"]                   = "bool";
        $props["infection_poumon"]                = "bool";
        $props["infection_urines"]                = "bool";
        $props["infection_digestif"]              = "bool";
        $props["infection_ombilic"]               = "bool";
        $props["infection_oeil"]                  = "bool";
        $props["infection_os_articulations"]      = "bool";
        $props["infection_peau"]                  = "bool";
        $props["infection_autre"]                 = "bool";
        $props["infection_autre_desc"]            = "str";
        $props["infection_strepto_b"]             = "bool";
        $props["infection_autre_strepto"]         = "bool";
        $props["infection_staphylo_dore"]         = "bool";
        $props["infection_autre_staphylo"]        = "bool";
        $props["infection_haemophilus"]           = "bool";
        $props["infection_listeria"]              = "bool";
        $props["infection_pneumocoque"]           = "bool";
        $props["infection_autre_gplus"]           = "bool";
        $props["infection_coli"]                  = "bool";
        $props["infection_proteus"]               = "bool";
        $props["infection_klebsiele"]             = "bool";
        $props["infection_autre_gmoins"]          = "bool";
        $props["infection_chlamydiae"]            = "bool";
        $props["infection_mycoplasme"]            = "bool";
        $props["infection_candida"]               = "bool";
        $props["infection_toxoplasme"]            = "bool";
        $props["infection_autre_parasite"]        = "bool";
        $props["infection_cmv"]                   = "bool";
        $props["infection_rubeole"]               = "bool";
        $props["infection_herpes"]                = "bool";
        $props["infection_varicelle"]             = "bool";
        $props["infection_vih"]                   = "bool";
        $props["infection_autre_virus"]           = "bool";
        $props["infection_autre_virus_desc"]      = "str";
        $props["infection_germe_non_trouve"]      = "bool";
        $props["ictere"]                          = "bool";
        $props["ictere_prema"]                    = "bool";
        $props["ictere_intense_terme"]            = "bool";
        $props["ictere_allo_immun_abo"]           = "bool";
        $props["ictere_allo_immun_rh"]            = "bool";
        $props["ictere_allo_immun_autre"]         = "bool";
        $props["ictere_autre_origine"]            = "bool";
        $props["ictere_autre_origine_desc"]       = "str";
        $props["ictere_phototherapie"]            = "bool";
        $props["ictere_type_phototherapie"]       = "enum list|conv|intens";
        $props["trouble_regul_thermique"]         = "bool";
        $props["hyperthermie"]                    = "bool";
        $props["hypothermie_legere"]              = "bool";
        $props["hypothermie_grave"]               = "bool";
        $props["anom_cong"]                       = "bool";
        $props["anom_cong_isolee"]                = "bool";
        $props["anom_cong_synd_polyformatif"]     = "bool";
        $props["anom_cong_tube_neural"]           = "bool";
        $props["anom_cong_fente_labio_palatine"]  = "bool";
        $props["anom_cong_atresie_oesophage"]     = "bool";
        $props["anom_cong_omphalocele"]           = "bool";
        $props["anom_cong_reduc_absence_membres"] = "bool";
        $props["anom_cong_hydrocephalie"]         = "bool";
        $props["anom_cong_hydrocephalie_type"]    = "enum list|susp|cert";
        $props["anom_cong_malform_card"]          = "bool";
        $props["anom_cong_malform_card_type"]     = "enum list|susp|cert";
        $props["anom_cong_hanches_luxables"]      = "bool";
        $props["anom_cong_hanches_luxables_type"] = "enum list|susp|cert";
        $props["anom_cong_malform_reinale"]       = "bool";
        $props["anom_cong_malform_reinale_type"]  = "enum list|susp|cert";
        $props["anom_cong_autre"]                 = "bool";
        $props["anom_cong_autre_desc"]            = "str";
        $props["anom_cong_chromosomique"]         = "bool";
        $props["anom_cong_genique"]               = "bool";
        $props["anom_cong_trisomie_21"]           = "bool";
        $props["anom_cong_trisomie_type"]         = "enum list|susp|cert";
        $props["anom_cong_chrom_gen_autre"]       = "bool";
        $props["anom_cong_chrom_gen_autre_desc"]  = "str";
        $props["anom_cong_description_clair"]     = "text";
        $props["anom_cong_moment_diag"]           = "enum list|antenat|neonat|autop";
        $props["autre_pathologie"]                = "bool";
        $props["patho_resp"]                      = "bool";
        $props["tachypnee"]                       = "bool";
        $props["autre_detresse_resp_neonat"]      = "bool";
        $props["acces_cyanose"]                   = "bool";
        $props["apnees_prema"]                    = "bool";
        $props["apnees_autre"]                    = "bool";
        $props["inhalation_meco_sans_pneumopath"] = "bool";
        $props["inhalation_meco_avec_pneumopath"] = "bool";
        $props["inhalation_lait"]                 = "bool";
        $props["patho_cardiovasc"]                = "bool";
        $props["trouble_du_rythme"]               = "bool";
        $props["hypertonie_vagale"]               = "bool";
        $props["souffle_a_explorer"]              = "bool";
        $props["patho_neuro"]                     = "bool";
        $props["hypothonie"]                      = "bool";
        $props["hypertonie"]                      = "bool";
        $props["irrit_cerebrale"]                 = "bool";
        $props["mouv_anormaux"]                   = "bool";
        $props["convulsions"]                     = "bool";
        $props["patho_dig"]                       = "bool";
        $props["alim_sein_difficile"]             = "bool";
        $props["alim_lente"]                      = "bool";
        $props["stagnation_pond"]                 = "bool";
        $props["perte_poids_sup_10_pourc"]        = "bool";
        $props["regurgitations"]                  = "bool";
        $props["vomissements"]                    = "bool";
        $props["reflux_gatro_eoso"]               = "bool";
        $props["oesophagite"]                     = "bool";
        $props["hematemese"]                      = "bool";
        $props["synd_occlusif"]                   = "bool";
        $props["trouble_succion_deglut"]          = "bool";
        $props["patho_hemato"]                    = "bool";
        $props["anemie_neonat"]                   = "bool";
        $props["anemie_transf_foeto_mat"]         = "bool";
        $props["anemie_transf_foeto_foet"]        = "bool";
        $props["drepano_positif"]                 = "bool";
        $props["maladie_hemo"]                    = "bool";
        $props["thrombopenie"]                    = "bool";
        $props["patho_metab"]                     = "bool";
        $props["hypogly_diab_mere_gest"]          = "bool";
        $props["hypogly_diab_mere_nid"]           = "bool";
        $props["hypogly_diab_mere_id"]            = "bool";
        $props["hypogly_neonat_transitoire"]      = "bool";
        $props["hypocalcemie"]                    = "bool";
        $props["intoxication"]                    = "bool";
        $props["synd_sevrage_toxico"]             = "bool";
        $props["synd_sevrage_medic"]              = "bool";
        $props["tabac_maternel"]                  = "bool";
        $props["alcool_maternel"]                 = "bool";
        $props["autre_patho_autre"]               = "bool";
        $props["rhinite_neonat"]                  = "bool";
        $props["patho_dermato"]                   = "bool";
        $props["autre_atho_autre_thesaurus"]      = "bool";

        $props["actes_effectues"]            = "bool";
        $props["caryotype"]                  = "bool";
        $props["etf"]                        = "bool";
        $props["eeg"]                        = "bool";
        $props["ecg"]                        = "bool";
        $props["fond_oeil"]                  = "bool";
        $props["antibiotherapie"]            = "bool";
        $props["oxygenotherapie"]            = "bool";
        $props["echographie_cardiaque"]      = "bool";
        $props["echographie_cerebrale"]      = "bool";
        $props["echographie_hanche"]         = "bool";
        $props["echographie_hepatique"]      = "bool";
        $props["echographie_reinale"]        = "bool";
        $props["exsanguino_transfusion"]     = "bool";
        $props["intubation"]                 = "bool";
        $props["incubateur"]                 = "bool";
        $props["injection_gamma_globulines"] = "bool";
        $props["togd"]                       = "bool";
        $props["radio_thoracique"]           = "bool";
        $props["reeducation"]                = "bool";
        $props["autre_acte"]                 = "bool";
        $props["autre_acte_desc"]            = "str";


        $props["mesures_prophylactiques"]     = "bool";
        $props["hep_b_injection_immunoglob"]  = "bool";
        $props["vaccinations"]                = "bool";
        $props["vacc_hep_b"]                  = "bool";
        $props["vacc_hepp_bcg"]               = "bool";
        $props["depistage_sanguin"]           = "bool";
        $props["hyperphenylalanemie"]         = "bool";
        $props["hypothyroidie"]               = "bool";
        $props["hyperplasie_cong_surrenales"] = "bool";
        $props["drepanocytose"]               = "bool";
        $props["mucoviscidose"]               = "bool";
        $props["test_audition"]               = "bool";
        $props["etat_test_audition"]          = "enum list|non|norm|anorm";
        $props["supp_vitaminique"]            = "bool";
        $props["supp_vitaminique_desc"]       = "str";
        $props["autre_mesure_proph"]          = "bool";
        $props["autre_mesure_proph_desc"]     = "str";

        $props["mode_sortie_mater"]                = "enum list|dom|mut|transfres|transfhres|poupon|deces|autre";
        $props["mode_sortie_mater_autre"]          = "str";
        $props["jour_vie_transmut_mater"]          = "num";
        $props["heure_vie_transmut_mater"]         = "num";
        $props["resp_transmut_mater_id"]           = "ref class|CMediusers back|naissances_transmut_mater";
        $props["motif_transmut_mater"]             = "enum list|malform|infect|ictere|pathrespi|pathcv|pathdig|pathhemato|pathmetab|pathneuro|syndsev|autre";
        $props["detail_motif_transmut_mater"]      = "str";
        $props["lieu_transf_mater"]                = "str";
        $props["type_etab_transf_mater"]           = "enum list|1|2";
        $props["dest_transf_mater"]                = "enum list|rea|intens|neonat|chir|neonatmater|autre";
        $props["dest_transf_mater_autre"]          = "str";
        $props["mode_transf_mater"]                = "enum list|intra|extra|samuped|samuns|ambulance|voiture";
        $props["delai_appel_arrivee_transp_mater"] = "num";
        $props["dist_mater_transf_mater"]          = "num";
        $props["raison_transf_mater_report"]       = "enum list|place|transp|autre";
        $props["raison_transf_report_mater_autre"] = "str";
        $props["surv_part_sortie_mater"]           = "enum list|non|survmed|survpmi|consultspec|autre";
        $props["surv_part_sortie_mater_desc"]      = "str";
        $props["remarques_transf_mater"]           = "text";

        $props["poids_fin_sejour"]                = "num";
        $props["alim_fin_sejour"]                 = "enum list|laitmat|mixte|artif|dietspec";
        $props["comp_alim_fin_sejour"]            = "bool";
        $props["nature_comp_alim_fin_sejour"]     = "enum list|eau|eausucree|prepalactee";
        $props["moyen_comp_alim_fin_sejour"]      = "enum list|tasse|cuill|bib";
        $props["indic_comp_alim_fin_sejour"]      = "enum list|pertepoids|patho";
        $props["indic_comp_alim_fin_sejour_desc"] = "str";

        $props["retour_mater"]      = "bool";
        $props["date_retour_mater"] = "date";
        $props["duree_transfert"]   = "num";

        $props["moment_deces"]     = "enum list|avttrav|ensalle|pdttrav|img|sansprec|neonat";
        $props["date_deces"]       = "date";
        $props["age_deces_jours"]  = "num";
        $props["age_deces_heures"] = "num";
        $props["cause_deces"]      = "enum list|foetneonat|obstmater";
        $props["cause_deces_desc"] = "str";
        $props["autopsie"]         = "enum list|nf|resnd|resni|resi";

        // Filter fields
        $props["_heure"]        = "time notNull";
        $props["_date_min"]     = "date";
        $props["_date_max"]     = "date moreThan|_date_min";
        $props["_datetime_min"] = "dateTime";
        $props["_datetime_max"] = "dateTime moreThan|_datetime_min";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function check()
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        $this->completeField("operation_id", "sejour_maman_id", "grossesse_id");

        // Operation has to be part of sejour
        if ($this->operation_id) {
            $operation = $this->loadRefOperation();
            if ($operation->sejour_id != $this->sejour_maman_id) {
                return "failed-operation-notin-sejour";
            }
        }

        // Sejour has to be part of grossesse
        $sejour = $this->loadRefSejourMaman();
        if ($sejour->grossesse_id != $this->grossesse_id) {
            return "failed-sejour-maman-notin-grossesse";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        if ($this->date_time) {
            $this->_view  = $this->getFormattedValue("date_time");
            $this->_heure = CMbDT::time($this->date_time);
        } else {
            $this->_view = "Dossier provisoire";
        }
        if ($this->rang) {
            $this->_view .= ", rang " . $this->rang;
        }

        $this->_day_relative = abs(CMbDT::daysRelative(CMbDT::date($this->date_time), CMbDT::date()));

        $this->_apgar_1  = $this->apgar_coeur_1 +
            $this->apgar_respi_1 +
            $this->apgar_tonus_1 +
            $this->apgar_reflexes_1 +
            $this->apgar_coloration_1;
        $this->_apgar_3  = $this->apgar_coeur_3 +
            $this->apgar_respi_3 +
            $this->apgar_tonus_3 +
            $this->apgar_reflexes_3 +
            $this->apgar_coloration_3;
        $this->_apgar_5  = $this->apgar_coeur_5 +
            $this->apgar_respi_5 +
            $this->apgar_tonus_5 +
            $this->apgar_reflexes_5 +
            $this->apgar_coloration_5;
        $this->_apgar_10 = $this->apgar_coeur_10 +
            $this->apgar_respi_10 +
            $this->apgar_tonus_10 +
            $this->apgar_reflexes_10 +
            $this->apgar_coloration_10;
    }

    /**
     * @inheritdoc
     */
    function loadRefsFwd()
    {
        $this->loadRefOperation();
        $this->loadRefGrossesse();
    }

    /**
     * Operation reference loader
     *
     * @return COperation
     */
    function loadRefOperation()
    {
        return $this->_ref_operation = $this->loadFwdRef("operation_id", true);
    }

    /**
     * Grossesse reference loader
     *
     * @return CGrossesse
     */
    function loadRefGrossesse()
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }

    /**
     * Chargement du suivi des examens du nouveau né
     *
     * @return CExamenNouveauNe[]
     */
    function loadRefsExamenNouveauNe()
    {
        return $this->_ref_examen_nouveau_ne = $this->loadBackRefs("exams_bebe");
    }

    /**
     * Chargement du dernier suivi des examens du nouveau né
     *
     * @return CExamenNouveauNe
     */
    function loadRefLastExamenNouveauNe()
    {
        return $this->_ref_last_examen_nouveau_ne = $this->loadLastBackRef("exams_bebe", "date ASC");
    }

    /**
     * Child's sejour reference loader
     *
     * @return CSejour
     */
    function loadRefSejourEnfant()
    {
        $this->_ref_sejour_enfant = $this->loadFwdRef("sejour_enfant_id", true);

        return $this->_ref_sejour_enfant;
    }

    /**
     * Mother's sejour reference loader
     *
     * @return CSejour
     */
    function loadRefSejourMaman()
    {
        return $this->_ref_sejour_maman = $this->loadFwdRef("sejour_maman_id", true);
    }

    /**
     * Chargement du relevés de constantes du nouveau né en salle de naissance
     *
     * @return CConstantesMedicales
     */
    public function loadRefConstantesNouveauNe()
    {
        return $this->_ref_nouveau_ne_constantes = $this->loadFwdRef('nouveau_ne_constantes_id');
    }

    /**
     * @return CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefsResuscitators(): array
    {
        return $this->_ref_resuscitators = $this->loadBackRefs('naissances_rea');
    }

    /**
     * Birth's counter
     *
     * @return int
     */
    static function countNaissances()
    {
        $group_id = CGroups::loadCurrent()->_id;
        $where    = [
            "naissance.num_semaines IS NULL OR naissance.num_semaines IN ('15_22', 'sup_22_sup_500g', 'sup_15')",
            "DATE_FORMAT(patients.naissance, '%Y') = " . CMbDT::transform(CMbDT::date(), null, "%Y"),
            "naissance.date_time IS NOT NULL",
            "naissance.num_naissance IS NOT NULL",
            "sejour.group_id = '$group_id'",
        ];
        $ljoin    = [
            "sejour"   => "naissance.sejour_enfant_id = sejour.sejour_id",
            "patients" => "sejour.patient_id = patients.patient_id",
        ];

        $naissance = new CNaissance();

        return $naissance->countList($where, null, $ljoin);
    }

    public function getNumNaissance()
    {
        $this->completeField("num_naissance", 'date_time');

        if ($this->_id && $this->num_naissance) {
            return $this->num_naissance;
        }

        if (!$this->date_time) {
            return null;
        }

        return CAppUI::gconf("maternite CNaissance num_naissance") + self::countNaissances();
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template)
    {
        $this->loadRefSejourMaman()->loadRefPatient()->fillLimitedTemplate(
            $template,
            CAppUI::tr('CNaissance-Mother'),
            false
        );
    }

    function fillLiteLimitedTemplate(&$template, $champ)
    {
        $bebe = $this->loadRefSejourEnfant()->loadRefPatient();
        $template->addProperty("$champ - " . CAppUI::tr('common-name'), $bebe->nom);
        $template->addProperty("$champ - " . CAppUI::tr('common-first name'), $bebe->prenom);
        $template->addDateProperty("$champ - " . CAppUI::tr('CPatient-_p_birth_date'), $bebe->naissance);
        $template->addTimeProperty("$champ - " . CAppUI::tr('CNaissance-birth time'), $this->_heure);
        $date_naiss_word  = $bebe->naissance ?
            (CMbDT::format($bebe->naissance, "%A") . " " .
                CMbString::toWords(CMbDT::format($bebe->naissance, "%d")) . " " .
                CMbDT::format($bebe->naissance, "%B") . " " .
                CMbString::toWords(CMbDT::format($bebe->naissance, "%Y"))) : "";
        $heure_naiss_word = $this->_heure ?
            (CMbString::toWords(CMbDT::format($this->_heure, "%H")) . " " . CAppUI::tr('common-hour|pl') . " " .
                CMbString::toWords(CMbDT::format($this->_heure, "%M")) . " " . CAppUI::tr('common-minute|pl')) : "";
        $template->addProperty("$champ - " . CAppUI::tr('CNaissance-Date of birth (letter)'), $date_naiss_word);
        $template->addProperty("$champ - " . CAppUI::tr('CNaissance-Birth time (letter)'), $heure_naiss_word);

        $bebe->loadRefLatestConstantes($bebe->naissance, ["poids", "taille", "perimetre_cranien"]);
        $constantes       = CConstantesMedicales::getLatestFor(
            $bebe,
            null,
            ["taille", "perimetre_cranien"],
            $this->_ref_sejour_enfant,
            false,
            $bebe->naissance
        );
        $first_constantes = CConstantesMedicales::getFirstFor(
            $bebe,
            null,
            ["poids"],
            $this->_ref_sejour_enfant,
            false,
            $bebe->naissance
        );

        $template->addProperty(
            "$champ - " . CAppUI::tr('CConstantesMedicales-weight'),
            "{$first_constantes[0]->_poids_g} g"
        );
        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-size'), "{$constantes[0]->taille} cm");
        $template->addProperty(
            "$champ - " . CAppUI::tr('CConstantesMedicales-cranial perimeter'),
            "{$constantes[0]->perimetre_cranien} cm"
        );

        $template->addProperty("$champ - " . CAppUI::tr('CPatient-sexe'), $bebe->getFormattedValue("sexe"));
        $template->addProperty("$champ - " . CAppUI::tr('CNaissance-rang'), $this->rang);
    }

    /**
     * @inheritDoc
     */
    function store()
    {
        $this->num_naissance = $this->getNumNaissance();
        $enfant              = $this->loadRefSejourEnfant()->loadRefPatient();

        // Save the birth rank in CPatient
        if (!$enfant->rang_naissance) {
            $enfant->rang_naissance = $this->rang;
            $enfant->store();
        }

        return parent::store();
    }

    public function loadRelGroup(): CGroups
    {
        return $this->loadRefSejourMaman()->loadRelGroup();
    }

    /**
     * Load Birth objects with guthrie date filter
     *
     * @param array  $naissances
     * @param string $guthrie_date_min
     * @param string $guthrie_date_max
     *
     * @return CNaissance[]
     */
    public function loadNaissancesByGuthrieDateFilter(
        array $naissances,
        string $guthrie_date_min,
        string $guthrie_date_max
    ): array {
        /** @var CNaissance $_naissance */
        foreach ($naissances as $_naissance_id => $_naissance) {
            $sejour             = $_naissance->loadRefSejourEnfant();
            $prescription       = $sejour->loadRefPrescriptionSejour();
            $examens_nouveau_ne = $_naissance->loadRefsExamenNouveauNe();

            $isGuthriePeriod = false;

            //Check Guthrie on new born exam
            if (count($examens_nouveau_ne)) {
                foreach ($examens_nouveau_ne as $_examen) {
                    if ($_examen->guthrie_datetime && ($_examen->guthrie_datetime >= "$guthrie_date_min 00:00:00") && ($_examen->guthrie_datetime <= "$guthrie_date_max 23:59:59")) {
                        $isGuthriePeriod = true;
                    }
                }

                if ($isGuthriePeriod) {
                    continue;
                }
            }

            //Check Guthrie by administration
            if (!$isGuthriePeriod && $prescription->_id) {
                $elt_guthrie_id = explode(":", CAppUI::gconf("maternite CNaissance elt_guthrie"))[0];

                $line_element = new CPrescriptionLineElement();
                $ds           = $line_element->getDS();

                $where = [
                    "prescription_id"         => $ds->prepare("= ?", $prescription->_id),
                    "element_prescription_id" => $ds->prepare("= ?", $elt_guthrie_id),
                ];

                $line_element->loadObject($where);

                if ($line_element->_id) {
                    $where_administrations = [
                        "planification" => $ds->prepare("= '0'"),
                    ];
                    $administrations       = $line_element->loadRefsAdministrations(
                        [CMbDT::date($guthrie_date_min), CMbDT::date($guthrie_date_max)],
                        $where_administrations,
                        "dateTime ASC"
                    );

                    if (!count($administrations)) {
                        unset($naissances[$_naissance_id]);
                        continue;
                    }
                    else {
                        $isGuthriePeriod = true;
                    }
                }
            }

            if (!$isGuthriePeriod) {
                unset($naissances[$_naissance_id]);
            }
        }

        return $naissances;
    }
}
