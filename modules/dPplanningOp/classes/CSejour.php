<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientFolderLiaison;
use Ox\AppFine\Client\CAppFineClientObjectReceived;
use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\AppFine\Client\CAppFineClientOrderPackProtocole;
use Ox\AppFine\Client\CAppFineClientRelaunchFolder;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Exceptions\CanNotMerge;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Interop\SIHCabinet\CSIHCabinet;
use Ox\Interop\Smp\CSmp;
use Ox\Mediboard\Addictologie\CAbsencePatient;
use Ox\Mediboard\Addictologie\CDossierAddictologie;
use Ox\Mediboard\Addictologie\CPassage;
use Ox\Mediboard\Addictologie\CPassageGestion;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Admin\Rgpd\IRGPDEvent;
use Ox\Mediboard\Ameli\CAvisArretTravail;
use Ox\Mediboard\Atih\CRSS;
use Ox\Mediboard\Brancardage\CBrancardage;
use Ox\Mediboard\Brancardage\Utilities\CBrancardageConditionMakerUtility;
use Ox\Mediboard\Brancardage\Utilities\CBrancardageGetUtility;
use Ox\Mediboard\Cabinet\CAccidentTravail;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamGir;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Ccam\CBillingPeriod;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\CompteRendu\CHtmlToPDFConverter;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Dispensation\CProductDelivery;
use Ox\Mediboard\ESatis\CEsatisConsent;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacturable;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Hotellerie\CBedCleanup;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientHandicap;
use Ox\Mediboard\Patients\CRedon;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\Pharmacie\CStockSejour;
use Ox\Mediboard\PlanningOp\Exceptions\CanNotMergeSejour;
use Ox\Mediboard\Pmsi\CRelancePMSI;
use Ox\Mediboard\Pmsi\CTraitementDossier;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CIncrementer;
use Ox\Mediboard\Soins\CChungScore;
use Ox\Mediboard\Soins\CObjectifSoin;
use Ox\Mediboard\Soins\CRDVExterne;
use Ox\Mediboard\Soins\CSejourTask;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CFicheAutonomie;
use Ox\Mediboard\Ssr\CReplacement;
use Ox\Mediboard\Ssr\CRHS;
use Ox\Mediboard\System\CAlert;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Mediboard\Transport\CTransport;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Web100T\CWeb100TSejour;
use Symfony\Component\Routing\RouterInterface;
use Throwable;
use ZipArchive;

/**
 * Séjour d'un patient dans un établissement
 */
class CSejour extends CFacturable implements IPatientRelated, IGroupRelated, IRGPDEvent, ImportableInterface
{

    const RESOURCE_TYPE = 'sejour';

    const FIELDSET_ADMISSION  = "admission";
    const FIELDSET_SORTIE     = "sortie";
    const FIELDSET_ANNULATION = "annulation";
    const FIELDSET_URGENCES   = "urgences";
    const FIELDSET_PLACEMENT  = "placement";
    const FIELDSET_REPAS      = "repas";
    const FIELDSET_COTATION   = "cotation";

    public const RELATION_SERVICE    = "service";
    const RELATION_PRATICIEN  = "praticien";
    const RELATION_PATIENT    = "patient";
    const RELATION_ACTES_CCAM = "actesCcam";
    const RELATIONS_DEFAULT   = [
        self::RELATION_PRATICIEN,
        self::RELATION_PATIENT,
    ];

    //static lists
    static         $types       = ["comp", "ambu", "exte", "seances", "ssr", "psy", "urg", "consult"];
    private static $fields_etiq = [
        "NDOS",
        "NRA",
        "DATE ENT",
        "HEURE ENT",
        "DATE SORTIE",
        "HEURE SORTIE",
        "TYPE HOSPITALISATION",
        "PRAT RESPONSABLE",
        "PRENOM PRAT RESPONSABLE",
        "NOM PRAT RESPONSABLE",
        "CODE BARRE NDOS",
        "CHAMBRE COURANTE",
        "SERVICE COURANT",
        "PRESTATIONS SOUHAITEES",
        "MEDICAMENTS DISPENSES",
        "NOM MAMAN",
        "PRENOM MAMAN",
        "PERFUSIONS",
    ];

    static $destination_values = ["1", "2", "3", "4", "6", "7"];
    static $provenances        = [
        "MCO_SSR" => [
            0   => ["", 1, 2, 3, 4, "R"],
            6   => ["", 1, 2, 3, 4, 6],
            7   => ["", 1, 2, 3, 4, 6, "R"],
            8   => ["", 5, 7, 8],
            "N" => [""],
        ],
        "psy"     => [
            0   => [""],
            6   => ["", 1, 2, 3, 4, 6],
            7   => ["", 1, 2, 3, 4, 6, "R"],
            8   => ["", 5, 7, 8],
            "N" => [""],
        ],
    ];
    static $destinations       = [
        "MCO_SSR" => [
            "transfert_acte" => ["", 1, 2, 3, 4],
            "mutation"       => ["", 1, 2, 3, 4, 6],
            "transfert"      => ["", 1, 2, 3, 4, 6],
            "normal"         => ["", 0, 7],
            "deces"          => ["", 0],
        ],
        "psy"     => [
            "transfert_acte" => ["", 0],
            "mutation"       => ["", 1, 2, 3, 4, 6],
            "transfert"      => ["", 1, 2, 3, 4, 6],
            "normal"         => ["", 0, 7],
            "deces"          => ["", 0],
        ],
    ];

    // Flag pour la mise à jour de la date d'entrée préparée
    // afin d'éviter une boucle infinie
    static $_flag_entree_preparee = false;

    // Flag pour ne pas toucher à l'affectation lors du découpage (changement de lit)
    static $_cutting_affectation = false;

    static $_in_transfert;

    // DB Table key
    public $sejour_id;

    // Clôture des actes
    public $cloture_activite_1;
    public $cloture_activite_4;

    // DB Réference
    public $patient_id;
    public $praticien_id;
    public $group_id;
    public $grossesse_id;
    public $confirme_user_id;
    public $consult_related_id;

    public $uf_hebergement_id; // UF de responsabilité d'hébergement
    public $uf_medicale_id; // UF de responsabilité médicale
    public $uf_soins_id; // UF de responsabilité de soins

    public $etablissement_entree_id;
    public $etablissement_sortie_id;
    public $service_entree_id; // Service d' entrée de mutation
    public $service_sortie_id; // Service de sortie de mutation

    // DB Fields
    public $type;
    public $charge_id;
    public $modalite;
    public $annule;
    public $motif_annulation;
    public $rques_annulation;
    public $recuse;
    public $chambre_seule;
    public $reanimation;
    public $UHCD;
    public $last_UHCD;
    public $service_id;

    public $entree_prevue;
    public $sortie_prevue;
    public $entree_reelle;
    public $sortie_reelle;
    public $entree;
    public $sortie;

    public $entree_preparee;
    public $entree_preparee_date;
    public $sortie_preparee;
    public $entree_modifiee;
    public $sortie_modifiee;

    public $DP;
    public $DR;
    public $pathologie;
    public $septique;
    public $convalescence;

    public $provenance;

    /** @var string */
    public $date_entree_reelle_provenance;

    public $destination;
    public $transport;
    /* @todo Passer en $transport_entree */
    public $transport_sortie;
    public $rques_transport_sortie;

    public $rques;
    public $ATNC;
    public $consult_accomp;
    public $hormone_croissance;
    public $lit_accompagnant;
    public $isolement;
    public $isolement_date;
    public $isolement_fin;
    public $raison_medicale;
    public $television;
    public $repas_diabete;
    public $repas_sans_sel;
    public $repas_sans_residu;
    public $repas_sans_porc;

    public $mode_entree;
    public $mode_entree_id;
    public $mode_sortie;
    public $mode_sortie_id;
    public $mode_destination_id;
    public $mode_pec_id;

    public $confirme;
    public $prestation_id;
    public $facturable;
    public $adresse_par_prat_id;
    public $adresse_par_exercice_place_id;
    public $libelle;
    public $forfait_se;
    public $forfait_sd;
    public $commentaires_sortie;
    public $discipline_id;
    public $ald;
    public $type_pec;
    public $handicap;
    public $bris_de_glace;

    public $date_accident;
    public $nature_accident;

    public $reception_sortie;
    public $completion_sortie;
    public $sans_dmh;

    public $directives_anticipees;
    public $directives_anticipees_status;
    public $technique_reanimation;
    public $technique_reanimation_status;
    public $aide_organisee;

    public $hospit_de_jour;
    public $last_seance;
    public $pec_accueil;
    public $pec_service;
    public $pec_ambu;
    public $rques_pec_ambu;
    public $presence_confidentielle;
    public $RRAC;
    public $nuit_convenance;
    public $dmi_prevu;
    public $volume_perf_alert;
    public $circuit_ambu;
    public $medecin_traitant_id;
    /* @param float Montant des frais du séjour */
    public $frais_sejour;
    /* @param float Etat du règlement des frais du séjour */
    public $reglement_frais_sejour;
    public $code_EDS;

    // Form Fields
    public $_libelle;
    public $_duree_prevue;
    public $_duree_prevue_heure;
    public $_duree_reelle;
    public $_duree;
    public $_date_entree_prevue;
    public $_date_sortie_prevue;
    public $_time_entree_prevue;
    public $_time_sortie_prevue;
    public $_hour_entree_prevue;
    public $_hour_sortie_prevue;
    public $_min_entree_prevue;
    public $_min_sortie_prevue;
    public $_guess_NDA;
    public $_at_midnight;
    public $_couvert_c2s;
    public $_couvert_ald;
    public $_curr_op_id;
    public $_curr_op_date;
    public $_protocole_prescription_anesth_id;
    public $_protocole_prescription_chir_id;
    public $_etat;
    public $_entree_relative;
    public $_sortie_relative;
    public $_not_collides        = ["urg", "consult", "seances", "exte"]; // Séjour dont on ne test pas la collision
    public $_is_proche;
    public $_motif_complet;
    public $_grossesse;
    public $_nb_printers;
    public $_sejours_enfants_ids = [];
    public $_date_deces;
    public $_envoi_mail;
    public $_naissance;
    public $_isolement_date;
    public $_count_modeles_etiq;
    public $_count_tasks;
    public $_count_objectifs_soins;
    public $_count_objectifs_retard;
    public $_count_pending_tasks;
    public $_count_prescriptions;
    public $_count_evenements_ssr;
    public $_count_evenements_ssr_week;
    public $_count_rdv_externe;
    public $_collisions          = [];
    public $_rques_sejour;
    public $_jour_op             = [];
    public $_liaisons_sejour;
    public $_statut_pec;
    public $_sa;
    public $_libelles_interv;
    public $_obs_entree_motif;
    public $_obs_entree_histoire_maladie;
    public $_obs_entree_examen;
    public $_obs_entree_rques;
    public $_obs_entree_conclusion;
    public $_color_prestation;
    public $_title_prestation;
    public $_color_prescription  = [];
    public $_color_constantes    = [];
    public $_alertes_ufs         = [];
    public $_copy_NDA;
    public $_bmr_filter;
    public $_bhre_filter;
    public $_bhre_contact_filter;
    public $_hdj_seance;
    public $_patient_status_ambu = [];
    public $_veille;
    public $_covid_diag;
    public $_ref_redons          = [];
    public $_ref_redons_by_redon = [];
    public $_code_EDS;
    public $_passage_bloc;
    public $_notification_sent;
    public $_ref_notifications;
    // Behaviour fields
    public $_en_mutation;
    public $_unique_lit_id;
    public $_no_synchro              = false;
    public $_no_synchro_eai          = false;
    public $_admit                   = false;
    public $_generate_NDA            = true;
    public $_skip_date_consistencies = false; // On ne check pas la cohérence des dates des consults/intervs
    public $_apply_sectorisation     = true;
    public $_sejour_maman_id;
    public $_create_affectations     = true;
    public $_manage_seance;
    public $_codage_ngap;
    public $_with_sortie_reelle;
    public $_with_bebes;
    public $_ald_pat;
    public $_c2s_pat;
    public $_acs_pat;
    public $_in_permission;

    //Fields for bill
    public $_assurance_maladie;
    public $_rques_assurance_maladie;
    public $_type_sejour;
    public $_statut_pro;
    public $_dialyse;
    public $_cession_creance;
    public $_bill_prat_id;

    //Module Ambu
    public $_ambu_form;
    public $_ambu_time_phase;
    public $_ambu_statut_phase;

    // References
    /** @var COperation */
    public $_ref_first_operation;
    /** @var COperation */
    public $_ref_last_operation;
    /** @var COperation */
    public $_ref_next_operation;
    /** @var  CService */
    public $_ref_service;
    /** @var CAffectation[] */
    public $_ref_affectations = [];
    /** @var CAffectation */
    public $_ref_first_affectation;
    /** @var CAffectation */
    public $_ref_last_affectation;
    /** @var CAffectation */
    public $_ref_curr_affectation;
    /** @var CAffectation */
    public $_ref_prev_affectation;
    /** @var CAffectation */
    public $_ref_next_affectation;
    /** @var CGroups */
    public $_ref_group;
    /** @var CEtabExterne */
    public $_ref_etablissement_transfert;
    /** @var CEtabExterne */
    public $_ref_etablissement_provenance;
    /** @var CService */
    public $_ref_service_mutation;
    /** @var CService */
    public $_ref_service_provenance;
    /** @var CDossierMedical */
    public $_ref_dossier_medical;
    /** @var CRSS */
    public $_ref_rss;
    /** @var CRPU */
    public $_ref_rpu;
    /** @var CBilanSSR */
    public $_ref_bilan_ssr;
    /** @var CFicheAutonomie */
    public $_ref_fiche_autonomie;
    /** @var CConsultAnesth */
    public $_ref_consult_anesth;
    /** @var CConsultAnesth */
    public $_ref_last_cpa;
    /** @var CConsultation */
    public $_ref_consult_atu;
    /** @var CConsultation */
    public $_ref_last_consult;
    /** @var CPrescription */
    public $_ref_last_prescription;
    /** @var CMedecin */
    public $_ref_adresse_par_prat;
    /** @var CIdSante400 */
    public $_ref_NDA;
    /** @var CIdSante400 */
    public $_ref_NPA;
    /** @var CIdSante400 */
    public $_ref_NRA;
    /** @var CReplacement */
    public $_ref_replacement;
    /** @var CMovement */
    public $_ref_hl7_movement;
    /** @var CAffectation */
    public $_ref_hl7_affectation;
    /** @var CGrossesse */
    public $_ref_grossesse;
    /** @var COperation */
    public $_ref_curr_operation;
    /** @var CChargePriceIndicator */
    public $_ref_charge_price_indicator;
    /** @var CModeEntreeSejour */
    public $_ref_mode_entree;
    /** @var CModeSortieSejour */
    public $_ref_mode_sortie;
    /** @var CModePECSejour */
    public $_ref_mode_pec;
    /** @var CModeDestinationSejour */
    public $_ref_mode_destination;
    /** @var CPrestation */
    public $_ref_prestation;
    /** @var CEchangeHprim */
    public $_ref_echange_hprim;
    /** @var CConsultation */
    public $_ref_obs_entree;
    /** @var CMediusers */
    public $_ref_confirme_user;
    /** @var CTraitementDossier */
    public $_ref_traitement_dossier;
    /** @var CDisciplineTarifaire */
    public $_ref_discipline_tarifaire;
    /** @var CAppFineClientFolderLiaison */
    public $_ref_appfine_client_folder;
    /** @var CAppFineClientRelaunchFolder[] */
    public $_refs_appfine_client_folders_relaunch;
    /** @var CRHS */
    public $_ref_last_rhs;
    /** @var CPrescriptionLineElement */
    public $_ref_line_element_visite;

    // Collections
    /** @var COperation[] */
    public $_ref_operations = [];
    /** @var CConsultation[] */
    public $_ref_consultations = [];
    /** @var CPrescription[] */
    public $_ref_prescriptions = [];
    /** @var CMediusers[] */
    public $_ref_prescripteurs = [];
    /** @var CPrescription */
    public $_ref_prescription_sejour;
    /** @var CReplacement[] */
    public $_ref_replacements = [];
    /** @var CSejourTask[] */
    public $_ref_tasks = [];
    /** @var CPrescriptionLineElement[] */
    public $_ref_tasks_not_created = [];
    /** @var CTransmissionMedicale[] */
    public $_ref_transmissions = [];
    /** @var CObservationMedicale[] */
    public $_ref_observations = [];
    /** @var COperation[] */
    public $_ref_curr_operations = [];
    /** @var CExamIgs[] */
    public $_ref_exams_igs = [];
    /** @var CExamIgs */
    public $_ref_last_exam_igs;
    /** @var CExamGir[] */
    public $_ref_exams_gir = [];
    /** @var CExamGir */
    public $_ref_last_exam_gir;
    /** @var CMovement[] */
    public $_ref_movements = [];
    /** @var CMovement */
    public $_ref_first_movement;
    /** @var CMovement */
    public $_ref_last_movement;
    /** @var CMbObject[] */
    public $_ref_suivi_medical = [];
    /** @var CItemPrestation[] */
    public $_ref_prestations = [];
    /** @var CNaissance */
    public $_ref_naissances;
    /** @var CNaissance */
    public $_ref_naissance;
    /** @var CUniteFonctionnelle */
    public $_ref_uf_hebergement;
    /** @var CUniteFonctionnelle */
    public $_ref_uf_soins;
    /** @var CUniteFonctionnelle */
    public $_ref_uf_medicale;
    /** @var CUserSejour[] */
    public $_ref_users_sejour        = [];
    public $_ref_users_by_type       = [];
    public $_ref_group_users_by_type = [];
    /** @var CRelancePMSI */
    public $_ref_relance;
    /** @var CAppelSejour[] */
    public $_ref_appels = [];
    /** @var CAppelSejour[] */
    public $_ref_appels_by_type = [];
    /** @var CBrancardage[] */
    public $_ref_curr_brancardage = [];
    /** @var CObjectifSoin[] */
    public $_ref_objectifs_soins = [];
    /** @var CChungScore[] */
    public $_ref_chung_scores = [];
    /** @var CChungScore */
    public $_ref_last_chung_score;
    /** @var CMediusers[] */
    public $_ref_list_anesth = [];
    /** @var CTransmissionMedicale[] */
    public $_ref_macrocibles = [];
    /** @var CStockSejour[] */
    public $_ref_stock_sejour = [];
    /** @var CEvenementSSR[] */
    public $_ref_evts_ssr_sejour = [];
    /** @var CRDVExterne[] */
    public $_refs_rdv_externes = [];
    /** @var CBedCleanup[] */
    public $_refs_bed_cleanup = [];
    /** @var CItemLiaison[] */
    public $_ref_items_liaisons = [];
    /** @var CTransport[] */
    public $_refs_transports = [];
    /** @var CEsatisConsent */
    public $_ref_esatis_consent;
    /** @var CAppFineClientOrderItem[] */
    public $_ref_orders_item = [];
    /** @var CAppFineClientObjectReceived[] */
    public $_ref_objects_received = [];
    /** @var CDossierAddictologie */
    public $_ref_dossier_addictologie;
    /** @var CAvisArretTravail[] */
    public $_refs_avis_arrets_travail = [];
    /** @var CAccidentTravail */
    public $_ref_accident_travail = [];
    /** @var CPassageGestion[] */
    public $_ref_passages_gestion = [];
    /** @var CPassage */
    public $_ref_passages = [];
    /** @var CAbsencePatient[] */
    public $_ref_absences_patient = [];
    /** @var CAutorisationPermission[] */
    public $_ref_autorisations_permission = [];
    /** @var CAutorisationPermission */
    public $_ref_last_autorisation_permission = [];
    /** @var CRPU|null */
    public $_ref_rpu_mutation;
    /** @var CConsultAnesth[] */
    public $_refs_dossiers_anesth = [];
    /** @var CMedecin */
    public $_ref_medecin_traitant;

    // AppFine
    public $_pack_appFine_ids;
    public $_count_order_sent;

    // External objects
    /** @var CCodeCIM10 */
    public $_ext_diagnostic_principal;
    /** @var CCodeCIM10 */
    public $_ext_diagnostic_relie;

    // Distant fields
    public $_dates_operations;
    public $_dates_consultations;
    public $_codes_ccam_operations;
    public $_NDA; // Numéro Dossier Administratif
    public $_NDA_view; // Vue du NDA
    public $_NPA; // Numéro Pré-Admission
    public $_list_constantes_medicales;
    public $_cancel_alerts;
    public $_diagnostics_associes;
    public $_liaisons_for_prestation       = [];
    public $_liaisons_for_prestation_ponct = [];
    public $_first_liaison_for_prestation;
    public $_cancel_hospitalization;
    public $_latest_chung_score;
    public $_completeness_color_form;

    // Filter Fields
    public $_date_min;
    public $_date_max;
    public $_date_entree;
    public $_date_sortie;
    public $_filter_date_min;
    public $_filter_date_max;
    public $_horodatage;
    public $_admission;
    public $_service;
    public $_type_admission;
    public $_specialite;
    public $_date_min_stat;
    public $_date_max_stat;
    public $_filter_type;
    public $_ccam_libelle;
    public $_coordonnees;
    public $_notes;
    public $_by_date;
    public $_export_csv;
    public $_handicap;

    // Object tool field
    public $_modifier_sortie;
    public $_modifier_entree;

    // Tamm-SIH
    public $_ext_cabinet_id;
    public $_ext_patient_id;
    public $_ext_patient_nom;
    public $_ext_patient_prenom;
    public $_ext_patient_naissance;

    public static $delete_aff_hors_sejours = true;

    /**
     * Standard constructor
     */
    function __construct()
    {
        parent::__construct();

        // Conf cache
        static $conf_locked;
        if (null === $conf_locked) {
            $conf_locked = $this->conf("locked");
        }

        $this->_locked = $conf_locked;
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->table       = 'sejour';
        $spec->key         = 'sejour_id';
        $spec->measureable = true;
        $spec->merge_type  = 'check';

        $references = [
            "reference1" => ["CMediusers", "praticien_id"],
            "reference2" => ["CPatient", "patient_id"],
        ];

        $references_w_mandatory_fields                     = $references;
        $references_w_mandatory_fields['mandatory_fields'] = ['type', 'entree', 'sortie'];

        $tab_dossier_soins        = $references;
        $tab_dossier_soins["tab"] = true;

        $spec->events = [
            "modification"            => $references_w_mandatory_fields,
            "suivi_clinique"          => $references_w_mandatory_fields,
            "preparation_entree"      => $references_w_mandatory_fields,
            "preparation_entree_auto" => [
                "auto"       => true,
                "reference1" => ["CMediusers", "praticien_id"],
                "reference2" => ["CPatient", "patient_id"],
            ],
            "sortie_preparee"         => $references_w_mandatory_fields,
            "sortie_preparee_auto"    => [
                "auto"       => true,
                "reference1" => ["CMediusers", "praticien_id"],
                "reference2" => ["CPatient", "patient_id"],
            ],
            'tab_dossier_soins'       => $tab_dossier_soins,
            'ambu_checklist_entree'   => $references,
            'ambu_checklist_sortie'   => $references,
        ];

        static $appFine = null;
        if ($appFine === null) {
            $appFine = CModule::getActive("appFineClient") !== null;
        }

        if ($appFine) {
            $spec->events["appFine"] = $references;
        }

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('planning_sejour', ["sejour_id" => $this->_id]);
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $service_id_notNull = CAppUI::conf("dPplanningOp CSejour service_id_notNull") == 1;

        $props                         = parent::getProps();
        $props["patient_id"]           = "ref notNull class|CPatient seekable back|sejours";
        $props["praticien_id"]         = "ref notNull class|CMediusers seekable autocomplete|nom back|sejours fieldset|extra";
        $props["group_id"]             = "ref notNull class|CGroups back|sejours";
        $props["grossesse_id"]         = "ref class|CGrossesse unlink back|sejours";
        $props["consult_related_id"]   = "ref class|CConsultation show|0 back|sejours_lies";
        $props["uf_hebergement_id"]    = "ref class|CUniteFonctionnelle seekable back|sejours_hebergement";
        $props["uf_medicale_id"]       = "ref class|CUniteFonctionnelle seekable back|sejours_medical fieldset|default";
        $props["uf_soins_id"]          = "ref class|CUniteFonctionnelle seekable back|sejours_soin fieldset|default";
        $props["type"]                 = "enum notNull list|" . implode("|", self::$types) . " default|ambu fieldset|default";
        $props["charge_id"]            = "ref class|CChargePriceIndicator autocomplete|libelle show|0 back|sejours fieldset|default";
        $props["modalite"]             = "enum notNull list|office|libre|tiers default|libre show|0";
        $props["annule"]               = "bool show|0 fieldset|annulation";
        $props["motif_annulation"]     = "enum list|doublon|contre_indication|perso|amelioration_sante|not_arrived|problem_bloc|no_lit|other fieldset|annulation";
        $props["rques_annulation"]     = "text helped fieldset|annulation";
        $props["recuse"]               = "enum list|-1|0|1 default|0 show|0 fieldset|cotation";
        $props["chambre_seule"]        = "bool notNull show|0 default|" . (CGroups::loadCurrent(
            )->chambre_particuliere ? 1 : 0) . " fieldset|placement";
        $props["reanimation"]          = "bool default|0 fieldset|urgences";
        $props["UHCD"]                 = "bool default|0 fieldset|urgences";
        $props["last_UHCD"]            = "dateTime fieldset|urgences";
        $props["service_id"]           = "ref" . ($service_id_notNull ? ' notNull' : '') . " class|CService seekable back|sejours fieldset|default";
        $props["entree_prevue"]        = "dateTime notNull show|0 fieldset|admission";
        $props["sortie_prevue"]        = "dateTime notNull moreEquals|entree_prevue show|0 fieldset|sortie";
        $props["entree_reelle"]        = "dateTime show|0 fieldset|admission";
        $props["sortie_reelle"]        = "dateTime moreEquals|entree_reelle show|0 fieldset|sortie";
        $props["entree"]               = "dateTime derived show|0 fieldset|admission";
        $props["sortie"]               = "dateTime moreEquals|entree derived show|0 fieldset|sortie";
        $props["entree_preparee"]      = "bool fieldset|admission";
        $props["entree_preparee_date"] = "dateTime fieldset|admission";
        $props["sortie_preparee"]      = "bool fieldset|sortie";
        $props["entree_modifiee"]      = "bool fieldset|admission";
        $props["sortie_modifiee"]      = "bool fieldset|sortie";
        $props["DP"]                   = "code cim10 show|0 fieldset|extra";
        $props["DR"]                   = "code cim10 show|0 fieldset|extra";
        $props["pathologie"]           = "str length|3 show|0 fieldset|placement";
        $props["septique"]             = "bool show|0 fieldset|placement";
        $props["convalescence"]        = "text helped fieldset|default";
        $props["rques"]                = "text helped fieldset|extra";
        $props["ATNC"]                 = "bool show|0 fieldset|default";
        $props["consult_accomp"]       = "enum list|oui|non|nc default|nc fieldset|extra";
        $props["hormone_croissance"]   = "bool";
        $props["lit_accompagnant"]     = "bool";
        $props["isolement"]            = "bool fieldset|placement";
        $props["isolement_date"]       = "dateTime fieldset|placement";
        $props["isolement_fin"]        = "dateTime fieldset|placement";
        $props["raison_medicale"]      = "text helped fieldset|placement";
        $props["television"]           = "bool fieldset|placement";

        $props["repas_diabete"]     = "bool fieldset|repas";
        $props["repas_sans_sel"]    = "bool fieldset|repas";
        $props["repas_sans_residu"] = "bool fieldset|repas";
        $props["repas_sans_porc"]   = "bool fieldset|repas";

        $props["mode_entree"]         = "enum list|8|7|0|6|N fieldset|admission";
        $props["mode_entree_id"]      = "ref class|CModeEntreeSejour autocomplete|libelle|true back|sejours";
        $props["mode_sortie"]         = "enum list|normal|transfert|transfert_acte|mutation|deces fieldset|sortie";
        $props["mode_sortie_id"]      = "ref class|CModeSortieSejour autocomplete|libelle|true back|sejours";
        $props["mode_destination_id"] = "ref class|CModeDestinationSejour back|mode_destination_sejours";
        $props["mode_pec_id"]         = "ref class|CModePECSejour back|mode_pec_sejours";

        $props["confirme"]                      = "dateTime fieldset|default";
        $props["confirme_user_id"]              = "ref class|CMediusers back|sejours_sortie_confirmee";
        $props["prestation_id"]                 = "ref class|CPrestation back|sejours";
        $props["facturable"]                    = "bool notNull default|1 show|0 fieldset|cotation";
        $props["etablissement_sortie_id"]       = "ref class|CEtabExterne autocomplete|nom back|transferts_sortie";
        $props["etablissement_entree_id"]       = "ref class|CEtabExterne autocomplete|nom back|transferts_entree";
        $props["service_entree_id"]             = "ref class|CService autocomplete|nom dependsOn|group_id|cancelled back|services_entree";
        $props["service_sortie_id"]             = "ref class|CService autocomplete|nom dependsOn|group_id|cancelled back|services_sortie";
        $props["adresse_par_prat_id"]           = "ref class|CMedecin back|sejours_adresses";
        $props["adresse_par_exercice_place_id"] = "ref class|CMedecinExercicePlace back|sejours_adresses";
        $props["libelle"]                       = "str seekable autocomplete dependsOn|praticien_id fieldset|default";
        $props["facture"]                       = "bool default|0 fieldset|cotation";
        $props["forfait_se"]                    = "bool default|0 fieldset|extra";
        $props["forfait_sd"]                    = "bool default|0 fieldset|extra";
        $props["commentaires_sortie"]           = "text helped fieldset|sortie";
        $props["discipline_id"]                 = "ref class|CDisciplineTarifaire autocomplete|description show|0 back|sejours";
        $props["ald"]                           = "bool default|0 fieldset|extra";

        $props["provenance"]                    = "enum list|1|2|3|4|5|6|7|8|R fieldset|admission";
        $props["date_entree_reelle_provenance"] = "dateTime fieldset|admission";
        $props["destination"]                   = "enum list|0|" . implode("|", self::$destination_values) . " fieldset|sortie";
        $props["transport"]                     = "enum list|perso|perso_taxi|ambu|ambu_vsl|vsab|smur|heli|fo fieldset|admission";
        $props["transport_sortie"]              = "enum list|perso|perso_taxi|ambu|ambu_vsl|vsab|smur|heli|fo|pompes_funebres fieldset|sortie";
        $props["rques_transport_sortie"]        = "text fieldset|sortie";
        $props["type_pec"]                      = "enum list|M|C|O|SSR fieldset|admission";
        $props["handicap"]                      = "enum list|0|1|2|3 default|0 fieldset|placement";
        $props["bris_de_glace"]                 = "bool default|0";
        $props["_handicap"]                     = "str";
        $props["hospit_de_jour"]                = "bool default|0 fieldset|default";
        $props["last_seance"]                   = "bool default|0 fieldset|extra";
        $props["pec_accueil"]                   = "dateTime fieldset|admission";
        $props["pec_service"]                   = "dateTime fieldset|admission";
        $props["pec_ambu"]                      = "enum list|NR|non|oui default|NR fieldset|admission";
        $props["rques_pec_ambu"]                = "text helped fieldset|admission";
        $props["presence_confidentielle"]       = "bool default|0 fieldset|extra";
        $props["RRAC"]                          = "bool default|0 fieldset|extra";
        $props["nuit_convenance"]               = "bool default|0 fieldset|placement";
        $props["date_accident"]                 = "date fieldset|extra";
        $props["nature_accident"]               = "enum list|P|T|D|S|J|C|L|B|U fieldset|extra";
        $props["dmi_prevu"]                     = "bool default| fieldset|extra";
        $props["volume_perf_alert"]             = "float default|0 fieldset|extra";
        $props["circuit_ambu"]                  = "enum list|court|moyen|long fieldset|default";
        $props["medecin_traitant_id"]           = "ref class|CMedecin back|patients_traites_list fieldset|extra";

        $props["reception_sortie"]  = "dateTime fieldset|sortie";
        $props["completion_sortie"] = "dateTime fieldset|sortie";
        $props["sans_dmh"]          = "bool default|0 fieldset|extra";

        // Clôture des actes
        $props["cloture_activite_1"] = "bool default|0 fieldset|cotation";
        $props["cloture_activite_4"] = "bool default|0 fieldset|cotation";

        $props['directives_anticipees']        = "text helped fieldset|extra";
        $props['directives_anticipees_status'] = "enum list|1|0|unknown default|unknown fieldset|extra";

        $props["technique_reanimation"]        = "text helped fieldset|extra";
        $props["technique_reanimation_status"] = "enum list|1|0|unknown default|unknown fieldset|extra";

        $props["aide_organisee"]         = "enum list|repas|entretien|soins|mouvoir| fieldset|placement";
        $props['frais_sejour']           = 'currency min|0 show|0 fieldset|cotation';
        $props['reglement_frais_sejour'] = 'enum list|non_regle|cb|cheque|espece|virement default|non_regle show|0 fieldset|cotation';
        $props["code_EDS"]               = "enum list|1|2|3";

        $props["_assurance_maladie"]       = "ref class|CCorrespondantPatient";
        $props["_rques_assurance_maladie"] = "text helped";
        $props["_type_sejour"]             = "enum list|maladie|accident|esthetique default|maladie";
        $props["_dialyse"]                 = "bool default|0";
        $props["_cession_creance"]         = "bool default|0";
        $props["_statut_pro"]              = "enum list|chomeur|etudiant|non_travailleur|independant|" .
            "invalide|militaire|retraite|salarie_fr|salarie_sw|sans_emploi";

        $props["_time_entree_prevue"] = "time";
        $props["_time_sortie_prevue"] = "time";

        $props["_date_entree"]         = "date";
        $props["_date_sortie"]         = "date";
        $props["_date_min"]            = "dateTime";
        $props["_date_max"]            = "dateTime moreEquals|_date_min";
        $props["_filter_date_min"]     = "date notNull";
        $props["_filter_date_max"]     = "date notNull moreEquals|_filter_date_max";
        $props["_horodatage"]          = "enum list|entree_prevue|entree_reelle|sortie_prevue|sortie_reelle";
        $props['_libelle']             = 'str fieldset|default';
        $props["_admission"]           = "text";
        $props["_service"]             = "text";
        $props["_type_admission"]      = "enum notNull list|ambucomp|ambucompssr|comp|ambu|exte|seances|ssr|psy|urg|consult default|ambu";
        $props["_specialite"]          = "text";
        $props["_date_min_stat"]       = "date";
        $props["_date_max_stat"]       = "date moreEquals|_date_min_stat";
        $props["_filter_type"]         = "enum list|comp|ambu|exte|seances|ssr|psy|urg|consult";
        $props["_NDA"]                 = "str show|1";
        $props["_ccam_libelle"]        = "bool default|0";
        $props["_coordonnees"]         = "bool default|0";
        $props['_notes']               = 'bool default|0';
        $props['_by_date']             = 'bool default|0';
        $props["_etat"]                = "enum list|preadmission|encours|cloture";
        $props["_latest_chung_score"]  = "num min|0 show|1";
        $props["_libelles_interv"]     = "text show|1";
        $props["_bmr_filter"]          = "bool default|0";
        $props["_bhre_filter"]         = "bool default|0";
        $props["_bhre_contact_filter"] = "bool default|0";
        $props['_codage_ngap']         = 'str';
        $props["_with_sortie_reelle"]  = "bool";
        $props["_with_bebes"]          = "bool";
        $props["_ald_pat"]             = "bool" . (CAppUI::gconf("dPplanningOp CSejour ald_mandatory") ? " notNull" : "");
        $props["_c2s_pat"]             = "bool";
        $props["_acs_pat"]             = "bool";
        $props["_code_EDS"]            = "enum list|1|2|3";

        $props["_duree_prevue"]                     = "num min|0";
        $props["_duree_prevue_heure"]               = "num";
        $props["_duree_reelle"]                     = "num";
        $props["_duree"]                            = "num fieldset|default";
        $props["_date_entree_prevue"]               = "date";
        $props["_date_sortie_prevue"]               = "date moreEquals|_date_entree_prevue";
        $props["_protocole_prescription_anesth_id"] = "str";
        $props["_protocole_prescription_chir_id"]   = "str";
        $props["_motif_complet"]                    = "str";
        $props["_unique_lit_id"]                    = "ref class|CLit";
        $props["_date_deces"]                       = "dateTime";
        $props["_isolement_date"]                   = "dateTime";
        $props["_statut_pec"]                       = "enum list|attente|en_cours|termine";
        $props["_sa"]                               = "num";

        $props["_obs_entree_motif"]            = "text show|1";
        $props["_obs_entree_histoire_maladie"] = "text show|1";
        $props["_obs_entree_examen"]           = "text show|1";
        $props["_obs_entree_rques"]            = "text show|1";
        $props["_obs_entree_conclusion"]       = "text show|1";

        // Tamm-SIH
        $props["_ext_cabinet_id"]        = "text";
        $props["_ext_patient_id"]        = "text";
        $props["_ext_patient_nom"]       = "str";
        $props["_ext_patient_prenom"]    = "str";
        $props["_ext_patient_naissance"] = "date";

        return $props;
    }

    /**
     * @param string $class
     *
     * @return CRPU|null
     * @see parent::getRelatedObjectOfClass()
     *
     */
    function getRelatedObjectOfClass($class)
    {
        switch ($class) {
            case "CRPU":
                $rpu = $this->loadRefRPU();
                if ($rpu->_id) {
                    return $rpu;
                }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function loadRelPatient()
    {
        return $this->loadRefPatient();
    }

    /**
     * @inheritdoc
     */
    function loadRelGroup(): CGroups
    {
        return $this->loadRefEtablissement();
    }

    /**
     * @see parent::check()
     */
    public function check(): ?string
    {
        // Has to be done first to check and repair fields before further checking
        if ($msg = parent::check()) {
            return $msg;
        }

        if (
            !$this->_id && $this->_check_bounds
            && !in_array($this->type, self::getTypesSejoursUrgence($this->praticien_id))
        ) {
            $dhe_date_min = CAppUI::gconf('dPplanningOp CSejour dhe_date_min');
            $dhe_date_max = CAppUI::gconf('dPplanningOp CSejour dhe_date_max');
            if ($dhe_date_min && $this->entree_prevue < $dhe_date_min . ' 00:00:00') {
                return CAppUI::tr('CSejour-error-dhe_date_min', CMbDT::format($dhe_date_min, CAppUI::conf("date")));
            } elseif ($dhe_date_max && $this->entree_prevue > $dhe_date_max . ' 23:59:59') {
                return CAppUI::tr(
                    'CSejour-error-dhe_date_max-entree',
                    CMbDT::format($dhe_date_max, CAppUI::conf("date"))
                );
            }
        }

        $pathos = new CDiscipline();

        // Test de la pathologies
        if ($this->pathologie != null && (!in_array($this->pathologie, $pathos->_specs["categorie"]->_list))) {
            return "Pathologie non disponible";
        }

        // Test de coherence de date avec les interventions
        if ($this->_check_bounds) {
            $this->completeField("entree_prevue");
            $this->completeField("sortie_prevue");
            $entree = $this->entree_prevue;
            $sortie = $this->sortie_prevue;

            if ($entree !== null && $sortie !== null && !$this->_skip_date_consistencies) {
                $entree = CMbDT::date($entree);
                $sortie = CMbDT::date($sortie);
                $this->makeDatesOperations();
                if (!$this->entree_reelle) {
                    foreach ($this->_dates_operations as $operation_id => $date_operation) {
                        if ($this->_curr_op_id == $operation_id) {
                            $date_operation = $this->_curr_op_date;
                        }

                        if (!CMbRange::in($date_operation, $entree, $sortie)) {
                            return "Intervention du '$date_operation' en dehors des nouvelles dates du séjour du"
                                . " '$entree' au '$sortie'";
                        }
                    }
                }


                if (!$this->entree_reelle && $this->type == "consult") {
                    $this->makeDatesConsultations();
                    foreach ($this->_dates_consultations as $date_consultation) {
                        if (!CMbRange::in($date_consultation, $entree, $sortie)) {
                            return "Consultations en dehors des nouvelles dates du séjour.";
                        }
                    }
                }
            }

            $this->completeField("entree_reelle", "annule");
            if ($this->fieldModified("annule", "1") && $this->type !== "seances") {
                $max_cancel_time = CAppUI::conf("dPplanningOp CSejour max_cancel_time");
                if ((CMbDT::dateTime("+ $max_cancel_time HOUR", $this->entree_reelle) < CMbDT::dateTime())) {
                    return "Impossible d'annuler un dossier ayant une entree réelle"
                        . " depuis plus de $max_cancel_time heures.<br />";
                }
            }

            if (!$this->_merging && !$this->_forwardRefMerging) {
                foreach ($this->getCollisions() as $collision) {
                    return "Collision avec le séjour du '$collision->entree' au '$collision->sortie'";
                }
            }
        }
        if (!$this->entree_reelle && $this->sortie_reelle && $this->fieldModified("sortie_reelle")) {
            return CAppUI::tr("CSejour.no_entree_relle_for_sortie");
        }

        $this->completeField("mode_sortie", "mode_entree", "destination", "provenance");
        if (
            $this->mode_sortie && (!$this->_id || $this->fieldModified("destination")
                || $this->fieldModified("mode_sortie"))
        ) {
            if (
                !in_array(
                    $this->destination,
                    self::$destinations[$this->type === "psy" ? "psy" : "MCO_SSR"][$this->mode_sortie]
                )
            ) {
                return CAppUI::tr("CSejour.destination_incoherente_mode_sortie");
            }
        }

        if (
            $this->mode_entree && (!$this->_id || $this->fieldModified("provenance")
                || $this->fieldModified("mode_entree"))
        ) {
            if (
                !in_array(
                    $this->provenance,
                    self::$provenances[$this->type === "psy" ? "psy" : "MCO_SSR"][$this->mode_entree]
                )
            ) {
                return CAppUI::tr("CSejour.provenance_incoherente_mode_entree");
            }
        }

        return null;
    }

    /**
     * Cherche les différentes collisions au séjour courant
     *
     * @return CSejour[]
     */
    function getCollisions()
    {
        $collisions = [];

        // Ne concerne pas les annulés
        $this->completeField("annule", "type", "group_id", "patient_id", "facturable");
        if ($this->annule || in_array($this->type, $this->_not_collides)) {
            return $collisions;
        }

        // Données incomplètes
        if (!$this->entree || !$this->sortie) {
            return $collisions;
        }

        // Test de colision avec un autre sejour
        $patient = new CPatient();
        if (!$patient->load($this->patient_id)) {
            return $collisions;
        }

        // Chargement des autres séjours
        $where["annule"]   = " = '0'";
        $where["group_id"] = " = '" . $this->group_id . "'";
        if (CAppUI::conf('dPplanningOp CSejour facturable_distinct_not_collides', $this->loadRefEtablissement()->_guid)) {
            $where["facturable"] = " = '" . $this->facturable . "'";
        }
        foreach ($this->_not_collides as $_type_not_collides) {
            $where[] = "type != '$_type_not_collides'";
        }

        $patient->loadRefsSejours($where);
        $sejours = $patient->_ref_sejours;

        // Collision sur chacun des autres séjours
        foreach ($sejours as $sejour) {
            if ($sejour->_id != $this->_id && $this->collides($sejour)) {
                $collisions[$sejour->_id] = $sejour;
            }
        }

        return $this->_collisions = $collisions;
    }

    /**
     * Cherche des séjours les dates d'entrée ou sortie sont proches,
     * pour le même patient dans le même établissement
     *
     * @param int  $tolerance Tolérance en heures
     * @param bool $use_type  Matche sur le type de séjour aussi
     *
     * @return CSejour[]
     */
    function getSiblings($tolerance = 1, $use_type = false)
    {
        $sejour             = new CSejour;
        $sejour->patient_id = $this->patient_id;
        $sejour->group_id   = $this->group_id;
        $sejour->annule     = "0";

        // Si on veut rechercher pour un type de séjour donné
        if ($use_type) {
            $sejour->type = $this->type;
        }

        /** @var CSejour[] $siblings */
        $siblings = $sejour->loadMatchingList();

        $this->updateFormFields();

        // Entree et sortie ne sont pas forcément stored
        $entree = $this->entree_reelle ? $this->entree_reelle : $this->entree_prevue;
        $sortie = $this->sortie_reelle ? $this->sortie_reelle : $this->sortie_prevue;

        foreach ($siblings as $_sibling) {
            if ($_sibling->_id == $this->_id) {
                unset($siblings[$_sibling->_id]);
                continue;
            }

            $entree_relative = abs(CMbDT::hoursRelative($entree, $_sibling->entree));
            $sortie_relative = abs(CMbDT::hoursRelative($sortie, $_sibling->sortie));
            if ($entree_relative > $tolerance && $sortie_relative > $tolerance) {
                unset($siblings[$_sibling->_id]);
            }
        }

        return $siblings;
    }

    /**
     * Check if the object collides another
     *
     * @param CSejour $sejour                 Sejour
     * @param bool    $collides_update_sejour Launch updateFormFields
     *
     * @return boolean
     */
    function collides(CSejour $sejour, $collides_update_sejour = true)
    {
        if ($this->_id && $sejour->_id && $this->_id == $sejour->_id) {
            return false;
        }

        if ($this->annule || $sejour->annule) {
            return false;
        }

        if (in_array($this->type, $this->_not_collides) || in_array($sejour->type, $this->_not_collides)) {
            return false;
        }

        if (CAppUI::conf('dPplanningOp CSejour ssr_not_collides', $this->loadRefEtablissement()->_guid)) {
            if ($this->type == "ssr" xor $sejour->type == "ssr") {
                return false;
            }
        }

        if ($this->group_id != $sejour->group_id) {
            return false;
        }

        if ($collides_update_sejour) {
            $this->updateFormFields();
        }

        switch (CAppUI::gconf("dPplanningOp CSejour check_collisions")) {
            case "no":
                return false;

            case "date":
                $lower1 = CMbDT::date($this->entree);
                $upper1 = CMbDT::date($this->sortie);
                $lower2 = CMbDT::date($sejour->entree);
                $upper2 = CMbDT::date($sejour->sortie);
                break;

            default:
            case "datetime":
                $lower1 = $this->entree;
                $upper1 = $this->sortie;
                $lower2 = $sejour->entree;
                $upper2 = $sejour->sortie;
                break;
        }

        return CMbRange::collides($lower1, $upper1, $lower2, $upper2, false);
    }

    /**
     * Apply a prescription protocol
     *
     * @param int $operation_id Operation ID
     *
     * @return null|string
     */
    function applyProtocolesPrescription($operation_id = null)
    {
        if (!$this->_protocole_prescription_chir_id) {
            return null;
        }

        // Application du protocole de prescription
        $prescription               = new CPrescription();
        $prescription->object_class = $this->_class;
        $prescription->object_id    = $this->_id;
        $prescription->type         = "sejour";
        if ($msg = $prescription->store()) {
            return $msg;
        }

        /*
    if ($this->_protocole_prescription_anesth_id) {
      $prescription->applyPackOrProtocole(
        $this->_protocole_prescription_anesth_id,
        $this->praticien_id,
        CMbDT::date(),
        null,
        $operation_id
      );
    }
    */
        if ($this->_protocole_prescription_chir_id) {
            if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
                HandlerManager::enableObjectHandler('CPrescriptionAlerteHandler');
            }
            $prescription->_dhe_mode = true;
            $prescription->applyPackOrProtocole(
                $this->_protocole_prescription_chir_id,
                $this->praticien_id,
                CMbDT::date(),
                null,
                $operation_id,
                null
            );
        }

        return null;
    }

    /**
     * check for a sectorisation rules to find a service_id
     * this feature will find the first sectorisation rule following his priority
     *
     * if 0 rules, no work
     * if 1 rule found, we redirect to the service_id
     *
     * @param $quiet boolean Quiet mode (no entry in system message)
     *
     * @return bool it worked =)
     */
    function getServiceFromSectorisationRules($quiet = false)
    {
        if (!CAppUI::conf("dPplanningOp CRegleSectorisation use_sectorisation") || !$this->_apply_sectorisation) {
            return false;
        }

        $this->completeField(
            "service_id",
            "type",
            "praticien_id",
            "entree",
            "entree_reelle",
            "entree_prevue",
            "sortie_prevue",
            "sortie",
            "group_id",
            "type_pec",
        );

        // Si création et service défini, pas de règle à appliquer
        if (!$this->_id && $this->service_id) {
            return false;
        }

        // Redéclencher la règle de sectorisation également pour les séjours sans entrée réelle si les champs suivants changent :
        // - durée de séjour (entrée ou sortie)
        // - type d'admission (ambu => comp)
        // - médecin responsable
        // - type de prise en charge

        $fields_modified = !$this->entree_reelle &&
            ((CMbDT::date($this->entree_prevue) != $this->_old->_date_entree_prevue)
                || (CMbDT::date($this->sortie_prevue) != $this->_old->_date_sortie_prevue)
                || ($this->fieldModified("type"))
                || ($this->fieldModified("praticien_id"))
                || ($this->fieldModified("type_pec")));

        // En modification, on check les champs précédents
        if ($this->_id && !$fields_modified) {
            return false;
        }

        // Si on est en redéclenchement de la règle de sectorisation
        if ($fields_modified) {
            $affectations = $this->loadRefsAffectations();

            // S'il y a des affectations on ne fait rien
            if (count($affectations) > 1) {
                return false;
            }

            // S'il y a une seule affectation, on vérifie si elle cible un lit
            if (count($affectations) === 1) {
                $first_aff = reset($affectations);

                if ($first_aff->lit_id) {
                    return false;
                }

                if ($msg = $first_aff->delete()) {
                    return false;
                }
            }
        }

        // make sure entree & sortie well defined
        $this->updatePlainFields();
        $group_id = CGroups::loadCurrent()->_id;

        $praticien           = $this->loadRefPraticien();
        $secondary_functions = $praticien->loadRefsSecondaryFunctions($group_id);
        $patient             = $this->loadRefPatient();

        $ds = CSQLDataSource::get('std');

        $where                   = [];
        $where["type_admission"] = $ds->prepare("= ? OR `type_admission` IS NULL", $this->type);
        $where["praticien_id"]   = $ds->prepare("= ? OR `praticien_id` IS NULL", $this->praticien_id);
        $where["function_id"]    = $ds->prepare("= ? OR `function_id` IS NULL", $praticien->function_id);
        if (count($secondary_functions)) {
            $where["function_id"] = $ds->prepare(
                "= ? OR `function_id` IS NULL OR `function_id` " . CSQLDataSource::prepareIn(array_keys($secondary_functions)),
                $praticien->function_id
            );
        }

        $where["date_min"] = $ds->prepare("<= ? OR `date_min` IS NULL", $this->entree);
        $where["date_max"] = $ds->prepare(">= ? OR `date_max` IS NULL", $this->entree);
        $where["regle_sectorisation.group_id"] = $ds->prepare("= ?", $this->group_id);

        if ($this->type_pec) {
            $where["type_pec"] = $ds->prepare("= ? OR `type_pec` IS NULL", $this->type_pec);
        }

        $duree              = CMbDT::daysRelative($this->entree, $this->sortie);
        $where["duree_min"] = $ds->prepare("<= ? OR `duree_min` IS NULL", $duree);
        $where["duree_max"] = $ds->prepare(">= ? OR `duree_max` IS NULL", $duree);

        $where['age_min'] = $ds->prepare('<= ? OR `age_min` IS NULL', $patient->evalAge($this->entree));
        $where['age_max'] = $ds->prepare('>= ? OR `age_max` IS NULL', $patient->evalAge($this->entree));

        $where_handicap    = ($this->handicap || $this->loadRefPatient()->loadRefsPatientHandicaps());
        $where['handicap'] = $ds->prepare('= ? OR `handicap` IS NULL', (int)$where_handicap);

        // Don't load the inactive service
        $ljoin = [
            "service" => "service.service_id = regle_sectorisation.service_id"
        ];
        $where["service.cancelled"] = $ds->prepare("= ?", "0");

        $regle = new CRegleSectorisation();

        /** @var CRegleSectorisation[] $regles */
        $regle->loadObject($where, "priority DESC", null, $ljoin);

        // one or more rules, lets do the work
        if ($regle->_id) {
            $regle->loadRefService();
            $this->service_id = $regle->service_id;
            if (!$quiet) {
                CAppUI::setMsg("CRegleSectorisation-rule%d-rule%s", UI_MSG_OK, 1, $regle->_ref_service->nom);
            }

            return true;
        }

        //no result, no work
        if (!$quiet) {
            CAppUI::setMsg("CRegleSectorisation-no-rules-found", UI_MSG_WARNING);
        }

        return false;
    }


    /**
     * affect a lit if unique lit id is defined
     *
     * @return string
     */
    function createAffectationLitUnique()
    {
        // Unique affectation de lit
        if (!$this->_unique_lit_id) {
            return null;
        }

        // Si la création du séjour vient de l'interop. on ne fait rien
        if ($this->_eai_sender_guid) {
            return null;
        }

        // Une affectation maximum
        if (count($this->_ref_affectations) > 1) {
            foreach ($this->_ref_affectations as $_affectation) {
                if ($msg = $_affectation->delete()) {
                    return "Impossible de supprimer une ancienne affectation: $msg";
                }
            }
        }

        // Affectation unique sur le lit
        $this->loadRefsAffectations();
        $unique            = $this->_ref_first_affectation;
        $unique->sejour_id = $this->_id;
        $unique->entree    = $this->entree;
        $unique->sortie    = $this->sortie;
        $unique->lit_id    = $this->_unique_lit_id;
        if ($msg = $unique->store()) {
            return "Impossible d'affecter un lit unique: $msg";
        }

        return null;
    }

    /**
     *
     */
    function createAffectationService()
    {
        // Si la création du séjour vient de l'interop. on ne fait rien
        if ($this->_eai_sender_guid) {
            return null;
        }

        if (!$this->countBackRefs("affectations")
            && $this->service_id
            && CAppUI::gconf("dPhospi CAffectation sejour_default_affectation")
            && $this->_create_affectations
        ) {
            $this->clearBackRefCache("affectations");

            $affectation             = new CAffectation();
            $affectation->sejour_id  = $this->_id;
            $affectation->service_id = $this->service_id;
            $affectation->entree     = $this->entree;
            $affectation->sortie     = $this->sortie;
            if ($msg = $affectation->store()) {
                return "Impossible d'affecter un couloir : $msg";
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        $this->completeField(
            "entree_reelle",
            "entree",
            "patient_id",
            "type_pec",
            "grossesse_id",
            "mode_sortie",
            "facturable",
            'codes_ccam'
        );

        if ($msg = CBillingPeriod::checkStore($this)) {
            return $msg;
        }

        if (in_array($this->facturable, ["", null])) {
            $this->facturable = 1;
        }

        /** @var CSejour $old */
        $old = $this->loadOldObject();

        // Patch pour prévenir le changement du group_id du séjour
        if ($this->_id) {
            $this->group_id = $old->group_id;
        }

        // Vérification de la validité des codes CIM
        if ($this->DP != null) {
            $dp = CCodeCIM10::get($this->DP);
            if (!$dp->exist) {
                CAppUI::setMsg("Le code CIM saisi n'est pas valide", UI_MSG_WARNING);
                $this->DP = "";
            }
        }
        if ($this->DR != null) {
            $dr = CCodeCIM10::get($this->DR);
            if (!$dr->exist) {
                CAppUI::setMsg("Le code CIM saisi n'est pas valide", UI_MSG_WARNING);
                $this->DR = "";
            }
        }

        /* Reset the cache of the used codes for the praticien */
        if (($this->_id && $this->fieldModified('DP')) || (!$this->_id && $this->DP)) {
            $this->loadRefPraticien();
            CCodeCIM10::resetUsedCodesCacheFor($this->_ref_praticien);
        }

        // Mode de sortie normal par défaut si l'autorisation de sortie est réalisée
        if ($this->conf("specified_output_mode") && $this->fieldModified("confirme")) {
            if (!$this->mode_sortie) {
                $this->mode_sortie = "normal";
            }
            if (!$this->mode_sortie_id && CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
                $mode_pec           = new CModeSortieSejour();
                $mode_pec->group_id = CGroups::loadCurrent()->_id;
                $mode_pec->actif    = '1';
                $mode_pec->mode     = 'normal';
                $mode_pec->loadMatchingObject("code");
                if ($mode_pec->_id) {
                    $this->mode_sortie_id = $mode_pec->_id;
                }
            }
        }

        // Annulation de l'établissement de transfert si le mode de sortie n'est pas transfert
        if (null !== $this->mode_sortie) {
            if (!in_array($this->mode_sortie, ['transfert', 'transfert_acte'])) {
                $this->etablissement_sortie_id = "";
            }
            if ("mutation" != $this->mode_sortie) {
                $this->service_sortie_id = "";
            }
        }

        // Mise à jour du type PEC si vide
        if (!$this->_id && !$this->type_pec) {
            $this->type_pec = ($this->grossesse_id ? "O" : "M");
        }

        // Annulation de la sortie réelle si on annule le mode de sortie
        if ($this->fieldModified("mode_sortie") && $this->mode_sortie === "") {
            $this->sortie_reelle = "";
        }

        // Annulation de l'établissement de provenance si le mode d'entrée n'est pas transfert
        if ($this->fieldModified("mode_entree")) {
            if ("7" != $this->mode_entree && "0" != $this->mode_entree) {
                $this->etablissement_entree_id = "";
            }

            if ("6" != $this->mode_entree) {
                $this->service_entree_id = "";
            }
        }

        // Passage au mode transfert si on value un établissement de provenance
        if ($this->fieldModified("etablissement_entree_id")) {
            if ($this->etablissement_entree_id != null) {
                $this->mode_entree = 7;
            }
        }

        // Passage au mode mutation si on value un service de provenance
        if ($this->fieldModified("service_entree_id")) {
            if ($this->service_entree_id != null) {
                $this->mode_entree = 6;
            }
        }

        $patient_modified = $this->fieldModified("patient_id");

        // Avoid getting unexpected results for $this->entree_prevue and $this->sortie_prevue while merging
        if ($this->_merging) {
            $this->_date_entree_prevue = null;
            $this->_date_sortie_prevue = null;
        }

        // Si le patient est modifié et qu'il y a des consultations, on cascade les consultations
        if (!$this->_forwardRefMerging && $this->sejour_id && $patient_modified) {
            /** @var CConsultation[] $consultations */
            $consultations = $this->loadBackRefs("consultations");
            foreach ($consultations as $_consult) {
                $_consult->_sync_consults_from_sejour = true;
                $_consult->patient_id                 = $this->patient_id;
                if ($msg = $_consult->store()) {
                    return $msg;
                }
            }
        }

        // Pour un séjour non annulé, mise à jour de la date de décès du patient
        // suivant le mode de sortie
        if (!$this->annule) {
            $patient = new CPatient;
            $patient->load($this->patient_id);

            if ("deces" == $this->mode_sortie) {
                $patient->deces = $this->_date_deces;
            } else {
                if ($this->_old->mode_sortie == "deces") {
                    $patient->deces = "";
                }
            }

            // On verifie que le champ a été modifié pour faire le store (sinon probleme lors de la fusion de patients)
            if ($patient->fieldModified("deces")) {
                // Ne pas faire de return $msg ici, car ce n'est pas "bloquant"
                $patient->store();
            }
        }

        if ($this->_handicap !== null) {
            $form_handicap = str_contains($this->_handicap, ',') ? explode(',', $this->_handicap) : [$this->_handicap];
            $form_handicap = array_filter($form_handicap);
            $patient       = CPatient::findOrFail($this->patient_id);

            // Get the handicaps list of the patient
            $patient_handicap             = new CPatientHandicap();
            $patient_handicap->patient_id = $patient->_id;
            $handicap_list                = CMbArray::pluck($patient_handicap->loadMatchingListEsc(), 'handicap');

            // Remove handicaps which aren't in the list anymore
            foreach (array_diff($handicap_list, $form_handicap) as $_handicap) {
                $patient_handicap             = new CPatientHandicap();
                $patient_handicap->patient_id = $patient->_id;
                $patient_handicap->handicap   = $_handicap;
                $patient_handicap->loadMatchingObjectEsc();
                $patient_handicap->delete();
            }

            // Refresh list after deletes
            $handicap_list = CMbArray::pluck($patient_handicap->loadMatchingListEsc(), 'handicap');

            // Add handicaps which are not in the list yet
            foreach (array_diff($form_handicap, $handicap_list) as $_handicap) {
                $patient_handicap             = new CPatientHandicap();
                $patient_handicap->patient_id = $patient->_id;
                $patient_handicap->handicap   = $_handicap;
                $patient_handicap->loadMatchingObjectEsc();

                if (!$patient_handicap->_id) {
                    $patient_handicap->store();
                }
            }
        }

        // Si annulation possible que par le chef de bloc
        if (
            CAppUI::conf("dPplanningOp COperation cancel_only_for_resp_bloc") &&
            $this->fieldModified("annule", 1) &&
            $this->entree_reelle &&
            !CModule::getCanDo("dPbloc")->edit
        ) {
            foreach ($this->loadRefsOperations() as $_operation) {
                if ($_operation->rank) {
                    CAppUI::setMsg(
                        "Impossible de sauvegarder : une des interventions du séjour est validée.\nContactez le responsable de bloc",
                        UI_MSG_ERROR
                    );

                    return null;
                }
            }
        }

        if ($this->fieldModified("annule", 0)) {
            $this->motif_annulation = "";
            $this->rques_annulation = "";
        }

        // On fixe la récusation si pas définie pour un nouveau séjour
        if (!$this->_id && ($this->recuse === "" || $this->recuse === null)) {
            $this->recuse = CAppUI::conf("dPplanningOp CSejour use_recuse") ? -1 : 0;
        }

        // no matter of config, if sejour is "urgence" type: recusation 0
        if (in_array($this->type, self::getTypesSejoursUrgence($this->praticien_id))) {
            $this->recuse = 0;
        }

        // Si gestion en mode expert de l'isolement
        if (CAppUI::conf("dPplanningOp CSejour systeme_isolement") == "expert") {
            $this->isolement_date =
                $this->_isolement_date !== $this->entree && $this->isolement ?
                    $this->_isolement_date : "";
            if (!$this->isolement) {
                $this->isolement_fin = "";
            }
        }

        $this->completeField("mode_entree_id");
        if ($this->mode_entree_id) {
            /** @var CModeEntreeSejour $mode */
            $mode              = $this->loadFwdRef("mode_entree_id");
            $this->mode_entree = $mode->mode;
        }

        $this->completeField("mode_sortie_id");
        if ($this->mode_sortie_id) {
            /** @var CModeSortieSejour $mode */
            $mode              = $this->loadFwdRef("mode_sortie_id");
            $this->mode_sortie = $mode->mode;
        }

        // Gestion du tarif et precodage des actes
        if ($this->_bind_tarif && $this->_id) {
            $this->getActeExecution();
            if ($msg = $this->bindTarif()) {
                return $msg;
            }
        }

        // Si on change la grossesse d'un séjour, il faut remapper les naissances éventuelles
        $change_grossesse = $this->fieldModified("grossesse_id");
        /** @var CNaissance[] $naissances */
        $naissances = [];
        if ($change_grossesse) {
            $naissances = $old->loadRefGrossesse()->loadRefsNaissances();
        }

        // Sectorisation Rules
        $this->getServiceFromSectorisationRules();

        if ($this->fieldModified("completion_sortie") && $this->completion_sortie && !$this->reception_sortie) {
            $this->reception_sortie = $this->completion_sortie;
        }

        if ($this->fieldModified("reception_sortie", "") && !$this->completion_sortie) {
            $this->completion_sortie = "";
        }

        if (!$this->_id && $code_regime = CAppUI::gconf("dPpatients CPatient default_code_regime")) {
            $patient = $this->loadRefPatient();
            if (!$patient->code_regime) {
                $patient->code_regime = $code_regime;
                $patient->store();
            }
        }

        $this->updateDureePrevue();
        if (CAppUI::gconf("dPplanningOp CSejour update_sortie_prevue")
            && $this->_id && $this->_duree_prevue && $this->fieldModified("entree_reelle") && $this->entree_reelle
        ) {
            $this->entree_prevue       = $this->entree_reelle;
            $this->sortie_prevue       = CMbDT::dateTime("+ $this->_duree_prevue DAYS", $this->entree_reelle);
            $this->_date_sortie_prevue = CMbDT::date($this->sortie_prevue);
            $this->_time_sortie_prevue = CMbDT::time($this->sortie_prevue);
        }

        $this->getUFs();

        $eai_sender_guid = $this->_eai_sender_guid;

        $fields_facture = [];
        if (CModule::getActive("dPfacturation") && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab")) {
            $fields_facture = [
                "_bill_prat_id"            => $this->_bill_prat_id,
                "_type_sejour"             => $this->_type_sejour,
                "_dialyse"                 => $this->_dialyse,
                "_statut_pro"              => $this->_statut_pro,
                "_cession_creance"         => $this->_cession_creance,
                "_assurance_maladie"       => $this->_assurance_maladie,
                "_rques_assurance_maladie" => $this->_rques_assurance_maladie,
                "_statut_pro"              => $this->_statut_pro,
            ];
        }

        $update_date_acts = false;
        if ($this->fieldModified('entree_reelle') && $this->entree_reelle) {
            $update_date_acts = true;
        }

        // Changement du mode de traitement
        if ($this->fieldModified("charge_id")) {
            $charge_price = new CChargePriceIndicator();
            $charge_price->load($this->charge_id);
            $this->type = $charge_price->type;
        }

        if (!static::$_in_transfert
            && $this->_id && $this->fieldModified("praticien_id")
            && $this->fieldModified("uf_medicale_id") && $this->countBackRefs("affectations")
        ) {
            $this->uf_medicale_id = $this->_old->uf_medicale_id;
        }

        $sortie_reelle_modified = $this->fieldModified('sortie_reelle');
        $sortie_prevue_modified = $this->fieldModified('sortie_prevue');
        $entree_prevue_modified = $this->fieldModified('entree_prevue');

        // On fait le store du séjour
        if ($msg = parent::store()) {
            return $msg;
        }

        if ($update_date_acts) {
            $this->updateDateActes();
        }

        if ($this->_protocole_prescription_chir_id) {
            $this->applyProtocolesPrescription();
            // Eviter une double application si le séjour est storé de nouveau
            $this->_protocole_prescription_chir_id = null;
        }

        $this->_eai_sender_guid = $eai_sender_guid;

        if ($change_grossesse) {
            foreach ($naissances as $_naissance) {
                $_naissance->grossesse_id = $this->grossesse_id;
                if ($msg = $_naissance->store()) {
                    return $msg;
                }
            }
        }

        // Changement des liaisons de prestations si besoin
        // Seulement par rapport à l'entrée
        if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "expert") {
            $decalage = CMbDT::daysRelative(CMbDT::date($old->entree), CMbDT::date($this->entree));

            if ($decalage != 0) {
                $liaisons = $this->loadBackRefs("items_liaisons");

                foreach ($liaisons as $_liaison) {
                    $_liaison->date = CMbDT::date("$decalage days", $_liaison->date);
                    if ($msg = $_liaison->store()) {
                        return $msg;
                    }
                }
            }
        }

        if (CModule::getActive("dPfacturation") && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab")) {
            foreach ($fields_facture as $_field => $value_field) {
                $this->$_field = $value_field;
            }
            if ($msg = CFacture::save($this)) {
                return $msg;
            }
        }

        if ($patient_modified) {
            $list_backrefs = ["contextes_constante", "deliveries", "consultations"];
            foreach ($list_backrefs as $_backname) {
                /** @var CConstantesMedicales[]|CProductDelivery[]|CConsultation[] $backobjects */
                $backobjects = $this->loadBackRefs($_backname);
                if (!$backobjects) {
                    continue;
                }
                foreach ($backobjects as $_object) {
                    if ($_object->patient_id == $this->patient_id) {
                        continue;
                    }
                    $_object->patient_id = $this->patient_id;
                    if ($_object instanceof CConsultation) {
                        $_object->_skip_count = true;
                    }
                    if ($msg = $_object->store()) {
                        CAppUI::setMsg($msg, UI_MSG_WARNING);
                    }
                }
            }
        }

        // Cas d'une annulation de séjour
        if ($this->annule) {
            // Suppression des affectations
            if ($msg = $this->delAffectations()) {
                return $msg;
            }

            // Suppression des opérations
            if ($msg = $this->cancelOperations()) {
                return $msg;
            }

            if ($msg = $this->cancelConsultations()) {
                return $msg;
            }

            // Annulation des mouvements
            if ($msg = $this->cancelMovements()) {
                return $msg;
            }

            // Suppression des actes
            if ($msg = $this->deleteActes()) {
                return $msg;
            }
        }

        // Synchronisation des affectations
        if (!$this->_no_synchro) {
            CAffectation::$skip_check_billing_period = true;

            $this->loadRefsAffectations();
            if (
                static::$delete_aff_hors_sejours
                && ($sortie_reelle_modified || $sortie_prevue_modified || $entree_prevue_modified)
            ) {
                // Suppression des affectations hors séjour lorsque l'on modifie la sortie réelle du séjour
                $reload_aff = false;

                foreach ($this->_ref_affectations as $_affectation) {
                    if ($_affectation->entree > $this->sortie || $_affectation->sortie < $this->entree) {
                        $_affectation->_synchro_sortie = false;
                        $_affectation->_no_synchro     = true;
                        $_affectation->delete();
                        $reload_aff = true;
                    }
                }

                // Si une affectation a été supprimée : retrait des affectations dans le cache des backrefs du séjour
                // et on les récupère à nouveau
                if ($reload_aff) {
                    $this->_back['affectations']  = null;
                    $this->_count['affectations'] = null;
                    $this->loadRefsAffectations();
                }
            }

            $firstAff =& $this->_ref_first_affectation;
            $lastAff  =& $this->_ref_last_affectation;

            // Cas où on a une premiere affectation différente de l'heure d'admission
            if ($firstAff->_id && ($firstAff->entree != $this->entree)) {
                $firstAff->entree          = $this->entree;
                $firstAff->_no_synchro     = 1;
                $firstAff->_no_synchro_eai = 1;
                $firstAff->store();
            }

            // Cas où on a une dernière affectation différente de l'heure de sortie
            if ($lastAff->_id && ($lastAff->sortie != $this->sortie)) {
                $lastAff->sortie          = $this->sortie;
                $lastAff->_no_synchro     = 1;
                $lastAff->_no_synchro_eai = 1;
                $lastAff->store();
            }

            //si le sejour a une sortie ==> compléter le champ effectue de la derniere affectation
            if ($lastAff->_id) {
                $this->_ref_last_affectation->effectue        = $this->sortie_reelle ? 1 : 0;
                $this->_ref_last_affectation->_no_synchro     = 1;
                $this->_ref_last_affectation->_no_synchro_eai = 1;
                $this->_ref_last_affectation->store();
            }

            CAffectation::$skip_check_billing_period = false;
        }

        // creation du nettoyage de la chambre quand la sortie réelle est renseignée
        if (CModule::getActive("hotellerie")) {
            if ($this->_ref_last_affectation
                && $this->_ref_last_affectation->_id
                && $this->sortie_reelle
                && CAppUI::gconf("hotellerie General create_object")
            ) {
                // Si il n'y a pas de demande de nettoyage pour cette chambre pour ce séjour, on la crée
                $cleanup            = new CBedCleanup();
                $cleanup->date      = CMbDT::date($this->sortie_reelle);
                $cleanup->lit_id    = $this->_ref_last_affectation->lit_id;
                $cleanup->sejour_id = $this->_id;

                $cleanups = $cleanup->loadMatchingList();
                if (!$cleanups) {
                    $cleanup->status_room = 'faire';
                    $cleanup->store();
                }
            }
        }

        // try to assign an affectation
        $this->createAffectationLitUnique();
        $this->createAffectationService();

        // Génération du NDA ?
        if ($this->_generate_NDA) {
            // On ne synchronise pas un séjour d'urgences qui est un reliquat
            $rpu = $this->loadRefRPU();
            if ($rpu && $rpu->mutation_sejour_id && ($rpu->sejour_id != $rpu->mutation_sejour_id)) {
                return null;
            }

            if ($msg = $this->generateNDA()) {
                return $msg;
            }
        }

        if (CModule::getActive('appFineClient') && CAppUI::gconf("appFineClient Sync allow_appfine_sync", $this->group_id) && !$old->_id) {
            CAppFineClientOrderPackProtocole::save($this);
        }

        if ($msg = $this->manageLastSeance()) {
            return $msg;
        }

        if ($this->_codage_ngap) {
            $this->_tokens_ngap = $this->_codage_ngap;
            $this->precodeActe('_tokens_ngap', 'CActeNGAP', $this->getExecutantId());
            $this->_tokens_ngap = $this->_codage_ngap = null;
        }

        // Made for Tamm-SIH
        if ($this->_ext_cabinet_id) {
            // If there is a cabinet id, store it as a external id
            $idex = CIdSante400::getMatch($this->_class, "cabinet_id", $this->_ext_cabinet_id, $this->_id);
            $idex->store();

            if ($this->_ext_patient_id) {
                // If there is a cabinet id, store it as a external id
                $idex = CIdSante400::getMatch("CPatient", "ext_patient_id-$this->_ext_cabinet_id", $this->_ext_patient_id, $this->patient_id);
                $idex->store();
            }
        }

        return null;
    }

    public function bindTarif(): ?string
    {
        if (!$this->exec_tarif && $this->_datetime) {
            $this->exec_tarif = $this->_datetime;
        } elseif (!$this->_datetime) {
            $this->exec_tarif = CAppUI::pref("use_acte_date_now") ? CMbDT::dateTime() : $this->_acte_execution;
        } elseif (CAppUI::pref("use_acte_date_now")) {
            $this->exec_tarif = CMbDT::dateTime();
        }

        return parent::bindTarif();
    }

    /**
     * Gestion du flag dernière séance
     *
     * @return string|null
     */
    function manageLastSeance()
    {
        if ($this->_manage_seance || !CAppUI::gconf("dPplanningOp CSejour hdj_seance") || $this->type !== "seances") {
            return null;
        }

        // On flag le passage dans cette fonction pour la séance courante
        $this->_manage_seance = true;

        if (!$this->_NDA) {
            $this->loadNDA();
        }

        if (!$this->_NDA) {
            $this->_manage_seance = false;

            return null;
        }

        /** @var CSejour[] $seances */
        $seances = $this->loadListFromNDA($this->_NDA);

        $last_seance = null;

        // Détection de la dernière séance avec prise en compte des séances annulées
        foreach ($seances as $_seance) {
            if (!$_seance->annule) {
                $last_seance = $_seance;
            }
        }

        foreach ($seances as $_seance) {
            $save_last_seance = $_seance->last_seance;

            // Par défaut, on considère que ce n'est pas la dernière séance
            $_seance->last_seance = 0;

            // S'il s'avère que c'est bien la dernière séance, on la flag comme telle
            if ($last_seance && $_seance->_id === $last_seance->_id) {
                $_seance->last_seance = 1;
            }

            // Eviter de store s'il n'y en a pas besoin (flag déjà à jour)
            if ($_seance->last_seance == $save_last_seance) {
                continue;
            }

            $_seance->_manage_seance = true;

            $msg = $_seance->store();

            if ($msg) {
                $_seance->_manage_seance = false;

                return $msg;
            }

            $_seance->_manage_seance = false;
        }

        $this->_manage_seance = false;

        return null;
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        $this->loadRefsCodagesCCAM();
        $this->loadRefItemsLiaisons();
        $this->loadRefBilanSSR();

        /** @var CSejour $_object */
        foreach ($objects as $_object) {
            /* Delete the CCodageCCAM that share the same user_id and date because of a SQL unique constraint that prevent the merge */
            $_object->loadRefsCodagesCCAM();
            foreach ($_object->_ref_codages_ccam as $user_id => $_days) {
                if (array_key_exists($user_id, $this->_ref_codages_ccam)) {
                    foreach ($_days as $_day => $_activities) {
                        if (array_key_exists($_day, $this->_ref_codages_ccam[$user_id])) {
                            foreach ($_activities as $_codage) {
                                $_codage->delete();
                            }
                        }
                    }
                }
            }

            // Suppression des prestations en doublon (même date et même prestation)
            $_object->loadRefItemsLiaisons();
            foreach ($_object->_ref_items_liaisons as $_item_liaison) {
                if (!$_item_liaison->prestation_id) {
                    continue;
                }

                foreach ($this->_ref_items_liaisons as $__item_liaison) {
                    if (!$__item_liaison->prestation_id) {
                        continue;
                    }
                    if (
                        ($_item_liaison->date === $__item_liaison->date)
                        && ($_item_liaison->prestation_id === $__item_liaison->prestation_id)
                    ) {
                        // On supprime celui qui n'est pas réalisé
                        if ($__item_liaison->item_realise_id) {
                            $_item_liaison->delete();
                        } else {
                            $__item_liaison->delete();
                        }
                    }
                }
            }

            // Supprression des bilans ssr en doublon
            if ($this->_ref_bilan_ssr && $this->_ref_bilan_ssr->_id) {
                $_object->loadRefBilanSSR()->delete();
            }

            // Check if duplicate redons
            $this->loadRefRedons(true);

            $_object->loadRefRedons(true);
            foreach ($_object->_ref_redons_by_redon as $class_redon => $_redons) {
                if (array_key_exists($class_redon, $this->_ref_redons_by_redon)) {
                    foreach ($_redons as $category => $_redon) {
                        if ($_redon->_id && array_key_exists(
                                $category,
                                $this->_ref_redons_by_redon[$class_redon]
                            ) && ($this->_ref_redons_by_redon[$class_redon][$category]->_id)) {
                            foreach ($_redon->loadRefsReleves() as $_releve) {
                                $_releve->delete();
                            }
                            $_redon->delete();
                        }
                    }
                }
            }
        }

        $days_diff = CMbDT::daysRelative($this->entree_prevue, reset($objects)->entree_prevue);
        $operation = $days_diff < 0 ? '+' . abs($days_diff) . ' DAYS' : "-$days_diff DAYS";

        parent::merge($objects, $fast, $merge_log);

        foreach ($this->loadRefItemsLiaisons() as $_item_liaison) {
            $_item_liaison->date = CMbDT::date($operation, $_item_liaison->date);
            if (CMbDT::date($this->entree_prevue) <= $_item_liaison->date && CMbDT::date($this->sortie_prevue) >= $_item_liaison->date) {
                $_item_liaison->store();
            }
        }
    }

    /**
     * Generate NDA
     *
     * @return null|string Error message if not null
     */
    function generateNDA()
    {
        if ($this->_forwardRefMerging) {
            return null;
        }

        // Pas de génération du NDA si la date d'entrée du séjour est inférieur à la date en config.
        $dhe_date_min = CAppUI::gconf('dPsante400 CIncrementer CSejour increment_NDA_date_min');
        if ($dhe_date_min && $this->entree < $dhe_date_min . ' 00:00:00') {
            return null;
        }

        $group = CGroups::get($this->group_id);
        if (!$group->isNDASupplier()) {
            return null;
        }

        $this->loadNDA($group->_id);
        if ($this->_NDA) {
            return null;
        }

        // Copie du NDA si demandé à la création du séjour (cas des séances)
        if ($this->_copy_NDA) {
            $idex        = new CIdSante400();
            $idex->id400 = $this->_copy_NDA;
            $idex->loadMatchingObject();
            $idex->_id = "";
            $idex->setObject($this);

            return $idex->store();
        }

        $group_id = $group->_id;
        // On préfère générer un identifiant d'un établissement virtuel pour les séjours non-facturables
        $group_id_pour_sejour_facturable = CAppUI::conf('dPsante400 CDomain group_id_pour_sejour_facturable', $group);
        if (!$this->facturable && $group_id_pour_sejour_facturable) {
            $group_id = $group_id_pour_sejour_facturable;
        }

        if (!$NDA = CIncrementer::generateIdex($this, self::getTagNDA($group->_id), $group_id)) {
            return CAppUI::tr("CIncrementer_undefined");
        }

        return null;
    }

    /**
     * Delete affectations
     *
     * @return null|string Store-like message
     */
    function delAffectations()
    {
        $this->loadRefsAffectations();

        $msg = null;
        // Module might not be active
        if ($this->_ref_affectations) {
            foreach ($this->_ref_affectations as $key => $value) {
                $affectation                   = $this->_ref_affectations[$key];
                $affectation->_eai_sender_guid = $this->_eai_sender_guid;

                $msg .= $affectation->deleteOne();
            }
        }

        return $msg;
    }

    /**
     * Cancel all operations
     *
     * @return null|string
     */
    function cancelOperations()
    {
        $this->loadRefsOperations();

        $msg = null;
        foreach ($this->_ref_operations as $key => $value) {
            $value->annulee = 1;
            $msg            .= $this->_ref_operations[$key]->store();
        }

        return $msg;
    }

    /**
     * Cancel all consultations
     *
     * @return null|string
     */
    function cancelConsultations()
    {
        $this->loadRefsConsultations();

        $msg = null;
        foreach ($this->_ref_consultations as $key => $value) {
            $value->_sync_sejour = false;
            $value->annule       = 1;
            $msg                 .= $this->_ref_consultations[$key]->store();
        }

        return $msg;
    }

    /**
     * Cancel all movements
     *
     * @return null|string
     */
    function cancelMovements()
    {
        $this->loadRefsMovements();

        $msg = null;
        foreach ($this->_ref_movements as $movement) {
            $movement->cancel = 1;
            $msg              .= $movement->store();
        }

        return $msg;
    }

    /**
     * @see parent::getActeExecution()
     */
    public function getActeExecution(): string
    {
        return $this->_acte_execution = CMbDT::dateTime($this->entree);
    }

    /**
     * Update estimated duration
     *
     * @return void
     */
    function updateDureePrevue()
    {
        if (CMbDT::date($this->entree_prevue) == CMbDT::date($this->sortie_prevue)) {
            $this->_duree_prevue = 0;
        } else {
            $this->_duree_prevue = CMbDT::daysRelative($this->entree_prevue, $this->sortie_prevue);
        }
    }

    /**
     * Update date isolement within sejour bounds
     *
     * @return void
     */
    function updateIsolement()
    {
        $this->_isolement_date = $this->isolement ? CValue::first($this->isolement_date, $this->entree) : "";
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        if (CAppUI::conf("dPplanningOp CSejour systeme_isolement") == "expert") {
            $this->updateIsolement();
        }

        // Durées
        $this->updateDureePrevue();

        if (!$this->_duree_prevue_heure) {
            $this->_duree_prevue_heure = CMbDT::timeRelative(CMbDT::time($this->entree_prevue), CMbDT::time($this->sortie_prevue), "%02d");
        }
        $this->_duree_reelle = CMbDT::daysRelative($this->entree_reelle, $this->sortie_reelle);
        $this->_duree        = CMbDT::daysRelative($this->entree, $this->sortie);

        // Dates
        $this->_date_entree_prevue = CMbDT::date(null, $this->entree_prevue);
        $this->_date_sortie_prevue = CMbDT::date(null, $this->sortie_prevue);

        // Horaires
        // @todo: A supprimer
        $this->_time_entree_prevue = CMbDT::format($this->entree_prevue, "%H:%M:00");
        $this->_time_sortie_prevue = CMbDT::format($this->sortie_prevue, "%H:%M:00");
        $this->_hour_entree_prevue = CMbDT::format($this->entree_prevue, "%H");
        $this->_hour_sortie_prevue = CMbDT::format($this->sortie_prevue, "%H");
        $this->_min_entree_prevue  = CMbDT::format($this->entree_prevue, "%M");
        $this->_min_sortie_prevue  = CMbDT::format($this->sortie_prevue, "%M");

        switch (CAppUI::conf("dPpmsi systeme_facturation")) {
            case "siemens" :
                $this->_guess_NDA = CMbDT::format($this->entree_prevue, "%y");
                $this->_guess_NDA .=
                    $this->type == ("exte" ? "5" : $this->type == "ambu") ? "4" : "0";
                $this->_guess_NDA .= "xxxxx";
                break;
            default:
                $this->_guess_NDA = "-";
        }
        $this->_at_midnight = ($this->_date_entree_prevue != $this->_date_sortie_prevue);

        if ($this->entree_prevue && $this->sortie_prevue) {
            $this->_view      = "Séjour du " . CMbDT::format($this->entree, CAppUI::conf("date"));
            $this->_shortview = "Du " . CMbDT::format($this->entree, CAppUI::conf("date"));
            if (CMbDT::format($this->entree, CAppUI::conf("date")) != CMbDT::format($this->sortie, CAppUI::conf("date"))) {
                $this->_view      .= " au " . CMbDT::format($this->sortie, CAppUI::conf("date"));
                $this->_shortview .= " au " . CMbDT::format($this->sortie, CAppUI::conf("date"));
            }
        }

        $this->getActeExecution();

        $this->_praticien_id = $this->praticien_id;

        // Etat d'un sejour : encours, clôturé ou preadmission
        $this->_etat = "preadmission";
        if ($this->entree_reelle) {
            $this->_etat = "encours";
        }
        if ($this->sortie_reelle) {
            $this->_etat = "cloture";
        }

        // Motif complet du séjour
        $this->_motif_complet .= $this->libelle;
        $this->_motif_complet = "";
        if ($this->recuse == -1) {
            $this->_motif_complet .= "[Att] ";
        }
        $this->_motif_complet .= $this->libelle;

        if (!$this->annule && $this->recuse == -1) {
            $this->_view = "[Att] " . $this->_view;
        }

        $this->_hdj_seance = CAppUI::conf("dPplanningOp CSejour hdj_seance", "CGroups-$this->group_id") && $this->hospit_de_jour;

        $this->updateEntreePreparee();
    }

    /**
     * Charge la facture de séjour
     *
     * @return void
     */
    function updateFieldsFacture()
    {
        if (CModule::getActive("dPfacturation") && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab")) {
            $this->loadRefFacture();
            if ($this->_ref_facture) {
                $facture                = $this->_ref_facture;
                $this->_type_sejour     = $facture->type_facture;
                $this->_statut_pro      = $facture->statut_pro;
                $this->_dialyse         = $facture->dialyse;
                $this->_cession_creance = $facture->cession_creance;

                $facture->loadRefAssurance();
                $type_assurance                 = $facture->type_facture != "accident" ? "assurance_maladie" : "assurance_accident";
                $ref_assurance                  = "_ref_$type_assurance";
                $rq_type_assurance              = "rques_$type_assurance";
                $this->_assurance_maladie       = $facture->$ref_assurance;
                $this->_rques_assurance_maladie = $facture->$rq_type_assurance;
            }
        }
    }

    function checkDaysRelative($date)
    {
        if ($this->entree && $this->sortie) {
            $this->_entree_relative = CMbDT::daysRelative($date, CMbDT::date($this->entree));
            $this->_sortie_relative = CMbDT::daysRelative($date, CMbDT::date($this->sortie));
        }
    }

    /**
     * Changement de la spec de l'entrée préparée
     */
    function updateEntreePreparee()
    {
        if (self::$_flag_entree_preparee) {
            return;
        }

        self::$_flag_entree_preparee = true;

        if ($this->entree_preparee && !$this->entree_preparee_date && $this->_id) {
            $log = $this->loadLastLogForField("entree_preparee");
            if ($log->_id) {
                $this->entree_preparee_date = $log->date;
                $this->rawStore();
            }
        }
        self::$_flag_entree_preparee = false;
    }

    /**
     * check if this need to update 'entree_prevue' and/or 'sortie_prevue' data in db for ambulatoire sejour
     *
     * @param COperation $interv
     *
     * @return bool does this need to be updated
     * @throws Exception
     */
    public function checkUpdateTimeAmbu(COperation $interv): bool
    {
        $do_store_sejour = false;

        $this->completeField('group_id');

        $interv->completeField(
            'date',
            'horaire_voulu',
            'time_operation',
            'temp_operation',
            'presence_preop',
            'presence_postop'
        );

        // check for conf and if sejour type is 'ambu'
        if (!CAppUI::gconf('dPplanningOp CSejour entree_pre_op_ambu', $this->group_id) || $this->type != 'ambu') {
            return $do_store_sejour;
        }

        // we need only one operation = ambu
        if (intval($this->countBackRefs('operations')) !== 1) {
            return $do_store_sejour;
        }

        $time_operation = ($interv->time_operation !== '00:00:00') ? $interv->time_operation : '';
        $horaire_voulu  = $interv->horaire_voulu;

        if ($interv->presence_preop && ($time_operation || $horaire_voulu)) {
            $entree_prevue = CMbDT::subDateTime(
                $interv->presence_preop,
                $interv->date . ' ' . CValue::first($time_operation, $horaire_voulu)
            );
            $heure_deb = CAppUI::conf('dPplanningOp CSejour heure_deb');
            if ($heure_deb) {
                $min_hour_sejour = $interv->date . ' ' . str_pad($heure_deb, 2, '0', STR_PAD_LEFT) . ':00:00';
                if ($entree_prevue < $min_hour_sejour) {
                    $entree_prevue = $min_hour_sejour;
                }
            }
            if ($this->entree_prevue != $entree_prevue) {
                $this->_date_entree_prevue = null;
                $this->entree_prevue       = $entree_prevue;
                $do_store_sejour           = true;

                // Si l'entrée prévue se retrouve supérieure à l'entrée prévue,
                // alors on met la sortie prévue à l'heure de sortie ambu configurée
                if ($this->entree_prevue > $this->sortie_prevue) {
                    $heure_fin = CAppUI::gconf('dPplanningOp CSejour default_hours heure_sortie_ambu', $this->group_id);
                    $this->sortie_prevue =
                        CMbDT::date($this->sortie_prevue) . ' ' . str_pad($heure_fin, 2, '0', STR_PAD_LEFT) . ':00:00';
                    $this->_date_sortie_prevue = null;
                }
            }
        }
        if (
            $interv->presence_postop && $interv->temp_operation
            && ($time_operation || $horaire_voulu)
        ) {
            $time_postop   = CMbDT::addTime($interv->temp_operation, $interv->presence_postop);
            $sortie_prevue = CMbDT::addDateTime(
                $time_postop,
                $interv->date . ' ' . CValue::first($time_operation, $horaire_voulu)
            );
            if ($this->sortie_prevue != $sortie_prevue) {
                $this->_date_sortie_prevue = null;
                $this->sortie_prevue       = $sortie_prevue;
                $do_store_sejour           = true;
            }
        }

        if ($do_store_sejour) {
            $this->updateFormFields();
        }

        return $do_store_sejour;
    }

    /**
     * @inheritdoc
     */
    public function updatePlainFields(): void
    {
        // Si la config de synchro est activée, on met à jour la sortie prévue lors de la saisie de la sortie aurotisée
        if (CAppUI::gconf("dPplanningOp CSejour synchro_autorisation_sortie_prevue")) {
            if ($this->fieldFirstModified("confirme")) {
                $this->sortie_prevue = $this->confirme;
            }
        }

        // UHCD
        $this->completeField("UHCD");
        if ($this->fieldModified("UHCD")) {
            $this->last_UHCD = "now";
        }

        // Annulation / Récusation
        $this->completeField("annule", "recuse");
        $annule = $this->annule;
        if ($this->fieldModified("recuse", "1")) {
            $annule = "1";
        }
        if ($this->fieldModified("recuse", "0")) {
            $annule = "0";
        }
        if ($this->fieldModified("recuse", "-1")) {
            $annule = "0";
        }
        $this->annule = $annule;

        if ((!$this->mode_sortie || !in_array($this->mode_sortie, ["normal", "deces"])) && ($this->destination === "0" || $this->destination === 0)) {
            $this->destination = null;
        }

        // Détail d'horaire d'entrée, ne pas comparer la date_entree_prevue à null
        // @todo Passer au TimePicker
        if ($this->_date_entree_prevue && $this->_hour_entree_prevue !== null && $this->_min_entree_prevue !== null) {
            $this->entree_prevue = "$this->_date_entree_prevue";
            $this->entree_prevue .= " " . str_pad($this->_hour_entree_prevue, 2, "0", STR_PAD_LEFT);
            $this->entree_prevue .= ":" . str_pad($this->_min_entree_prevue, 2, "0", STR_PAD_LEFT);
            $this->entree_prevue .= ":00";
        }

        // Détail d'horaire de sortie, ne pas comparer la date_sortie_prevue à null
        // @todo Passer au TimePicker
        if ($this->_date_sortie_prevue && $this->_hour_sortie_prevue !== null && $this->_min_sortie_prevue !== null) {
            $this->sortie_prevue = "$this->_date_sortie_prevue";
            $this->sortie_prevue .= " " . str_pad($this->_hour_sortie_prevue, 2, "0", STR_PAD_LEFT);
            $this->sortie_prevue .= ":" . str_pad($this->_min_sortie_prevue, 2, "0", STR_PAD_LEFT);
            $this->sortie_prevue .= ":00";
        }

        $this->completeField('entree_prevue', 'entree_preparee', 'sortie_prevue', 'entree_reelle', 'sortie_reelle', 'type');

        // Signaler l'action de validation de la sortie
        if ($this->_modifier_sortie === '1') {
            $this->sortie_reelle = CMbDT::dateTime();
        }

        if ($this->_modifier_sortie === '0') {
            $this->sortie_reelle = "";
        }

        if ($this->_modifier_entree === '0') {
            $this->entree_reelle = "";
        }

        // si modification d'entree prévue et config et que le séjour était préparé
        if ($this->fieldModified("entree_preparee")) {
            $this->entree_preparee_date = $this->entree_preparee ? CMbDT::dateTime() : null;
        }

        if (($this->fieldModified("type") || ($this->fieldModified("entree_prevue") && CMbDT::date($this->_old->entree_prevue) != CMbDT::date(
                        $this->entree_prevue
                    )))
            && CAppUI::conf("dPplanningOp CSejour entree_modifiee") && ($this->_old->entree_preparee)
        ) {
            $this->entree_preparee      = 0;
            $this->entree_preparee_date = null;
            $this->entree_modifiee      = 1;
        } // si le séjour était préparé et qu'on le dé-prépare
        elseif ($this->fieldModified("entree_preparee", "0")) {
            $this->entree_preparee_date = null;
            $this->entree_modifiee      = 0;
        }

        /* La date de sortie prévue a été modifiée et qu'elle est différente de l'ancienne, le champ sortie préparée est mis à 0 */
        if (CAppUI::gconf('dPplanningOp CSejour cancel_sortie_preparee') && $this->fieldModified('sortie_prevue')) {
            if (CMbDT::date(null, $this->_old->sortie_prevue) != CMbDT::date(null, $this->sortie_prevue)) {
                $this->sortie_preparee = 0;
            }
        }

        // Affectation de la date d'entrée prévue si on a la date d'entrée réelle
        if ($this->entree_reelle && !$this->entree_prevue) {
            $this->entree_prevue = $this->entree_reelle;
        }

        // Affectation de la date de sortie prévue si on a la date de sortie réelle
        if ($this->sortie_reelle && !$this->sortie_prevue) {
            $this->sortie_prevue = $this->sortie_reelle;
        }

        // Nouveau séjour relié à une grossesse
        // Si l'entrée prévue est à l'heure courante, alors on value également l'entrée réelle
        if (CModule::getActive("maternite") && $this->grossesse_id) {
            if (!$this->_id && CMbDT::date() == $this->_date_entree_prevue) {
                $this->entree_reelle = CMbDT::dateTime();
            }

            if (CAppUI::gconf("maternite general map_sortie")) {
                $sejours_cible = CMbObject::massLoadFwdRef($this->loadRefGrossesse()->loadRefsNaissances(), "sejour_enfant_id");

                if ($this->fieldModified("sortie_prevue")) {
                    /** @var CSejour $_sejour_cible */
                    foreach ($sejours_cible as $_sejour_cible) {
                        $_sejour_cible->sortie_prevue       = $this->sortie_prevue;
                        $_sejour_cible->_date_sortie_prevue = null;
                        $_sejour_cible->store();
                    }
                }
            }
        }

        //@TODO : mieux gérer les current et now dans l'updatePlainFields et le store
        $entree_reelle = ($this->entree_reelle === 'current' || $this->entree_reelle === 'now') ? CMbDT::dateTime() : $this->entree_reelle;
        if ($entree_reelle && ($this->sortie_prevue < $entree_reelle)) {
            $this->sortie_prevue = $this->type == "comp" ? CMbDT::dateTime("+1 DAY", $entree_reelle) : $entree_reelle;
        }

        // Has to be donne once entree / sortie - reelle / prevue is not modified
        $this->entree = $this->entree_reelle ? $this->entree_reelle : $this->entree_prevue;
        $this->sortie = $this->sortie_reelle ? $this->sortie_reelle : $this->sortie_prevue;

        if (!CAppUI::gconf("dPplanningOp CSejour sejour_type_duree_nocheck")) {
            // Synchro durée d'hospi / type d'hospi
            $this->_at_midnight = (CMbDT::date(null, $this->entree) != CMbDT::date(null, $this->sortie));
            if ($this->_at_midnight && $this->type == "ambu") {
                $this->type = "comp";
            } elseif (!$this->_at_midnight && $this->type == "comp") {
                $this->type = "ambu";
            }
        }
    }

    /**
     * Count sejours including a specific date
     *
     * @param string $date     Date to check for inclusion
     * @param array  $where    Array of additional where clauses
     * @param array  $leftjoin Array of left join clauses
     *
     * @return int Count null if module is not installed
     */
    static function countForDate($date, $where = null, $leftjoin = null)
    {
        $where[] = "sejour.entree <= '$date 23:59:59'";
        $where[] = "sejour.sortie >= '$date 00:00:00'";
        $sejour  = new CSejour;

        return $sejour->countList($where, null, $leftjoin);
    }

    /**
     * Count sejours including a specific date
     *
     * @param string $datetime Date to check for inclusion
     * @param array  $where    Array of additional where clauses
     * @param array  $leftjoin Array of left join clauses
     *
     * @return int Count null if module is not installed
     */
    static function countForDateTime($datetime, $where = null, $leftjoin = null)
    {
        $where[] = "sejour.entree <= '$datetime'";
        $where[] = "sejour.sortie >= '$datetime'";
        $sejour  = new CSejour;

        return $sejour->countList($where, null, $leftjoin);
    }

    /**
     * Load sejours including a specific date
     *
     * @param string $date  Date to check for inclusion
     * @param array  $where Array of additional where clauses
     * @param array  $order Array of order fields
     * @param string $limit MySQL limit clause
     * @param array  $group Array of group by clauses
     * @param array  $ljoin Array of left join clauses
     *
     * @return self[] List of found sejour, null if module is not installed
     */
    static function loadListForDate($date, $where = null, $order = null, $limit = null, $group = null, $ljoin = null)
    {
        $where[] = "sejour.entree <= '$date 23:59:59'";
        $where[] = "sejour.sortie >= '$date 00:00:00'";
        $sejour  = new CSejour;

        return $sejour->loadList($where, $order, $limit, $group, $ljoin);
    }

    /**
     * Load sejours including a specific datetime
     *
     * @param string $datetime Datetime to check for inclusion
     * @param array  $where    Array of additional where clauses
     * @param array  $order    Array of order fields
     * @param string $limit    MySQL limit clause
     * @param array  $group    Array of group by clauses
     * @param array  $ljoin    Array of left join clauses
     *
     * @return self[] List of found sejour, null if module is not installed
     */
    static function loadListForDateTime($datetime, $where = null, $order = null, $limit = null, $group = null, $ljoin = null)
    {
        $where[] = "sejour.entree <= '$datetime'";
        $where[] = "sejour.sortie >= '$datetime'";
        $sejour  = new CSejour;

        return $sejour->loadList($where, $order, $limit, $group, $ljoin);
    }

    /**
     * @see parent::getTemplateClasses()
     */
    function getTemplateClasses()
    {
        $this->loadRefsFwd();

        $tab = [];

        // Stockage des objects liés au séjour
        $tab['CSejour']  = $this->_id;
        $tab['CPatient'] = $this->_ref_patient->_id;

        $tab['CConsultation']  = 0;
        $tab['CConsultAnesth'] = 0;
        $tab['COperation']     = 0;

        return $tab;
    }

    /**
     * Calcul des droits C2S pour la duree totale du sejour
     *
     * @return void
     */
    function getDroitsC2S()
    {
        if ((!$this->_ref_patient->fin_amo || $this->_date_sortie_prevue <= $this->_ref_patient->fin_amo) && $this->_ref_patient->c2s) {
            $this->_couvert_c2s = 1;
        } else {
            $this->_couvert_c2s = 0;
        }
        if ((!$this->_ref_patient->fin_amo || $this->_date_sortie_prevue <= $this->_ref_patient->fin_amo) && $this->_ref_patient->ald) {
            $this->_couvert_ald = 1;
        } else {
            $this->_couvert_ald = 0;
        }
    }

    /**
     * @see parent::loadRefSejour()
     */
    public function loadRefSejour(bool $cache = true): ?CSejour
    {
        return $this->_ref_sejour =& $this;
    }

    /**
     * Load current affectation relative to a date
     *
     * @param string $datetime   Reference datetime, now if null
     * @param string $service_id Service filter
     *
     * @return CAffectation
     */
    function loadRefCurrAffectation($datetime = null, $service_id = null)
    {
        if (!$datetime) {
            $datetime = CMbDT::dateTime();
        }

        $affectation        = new CAffectation();
        $where              = [];
        $where["sejour_id"] = " = '$this->_id'";
        if ($service_id) {
            $where["service_id"] = " = '$service_id'";
        }

        if (strpos($datetime, " ") !== false) {
            $where[] = "'$datetime' BETWEEN entree AND sortie";
        } else {
            $where[] = "'$datetime' BETWEEN DATE(entree) AND DATE(sortie)";
        }
        $affectation->loadObject($where);

        return $this->_ref_curr_affectation = $affectation;
    }


    /**
     * Load surrounding affectations
     *
     * @param string $date $date Current date, now if null
     *
     * @return CAffectation[] Affectations array with curr, prev and next keys
     */
    function loadSurrAffectations($date = null)
    {
        if (!$date) {
            $date = CMbDT::dateTime();
        }

        // Current affectation
        $affectations         = [];
        $affectations["curr"] = $this->loadRefCurrAffectation($date);

        // Previous affection
        $affectation        = new CAffectation();
        $where              = [];
        $where["sortie"]    = " < '$date'";
        $where["sejour_id"] = " = '$this->_id'";
        $affectation->loadObject($where);
        $affectations["prev"] = $this->_ref_prev_affectation = $affectation;

        // Next affectation
        $affectation        = new CAffectation();
        $where              = [];
        $where["entree"]    = "> '$date'";
        $where["sejour_id"] = " = '$this->_id'";
        $affectation->loadObject($where);
        $affectations["next"] = $this->_ref_next_affectation = $affectation;

        return $affectations;
    }

    static function massLoadSurrAffectation(&$sejours = [], $date = null)
    {
        if (!count($sejours)) {
            return;
        }

        if (!$date) {
            $date = CMbDT::dateTime();
        }

        $sejour_ids = CMbArray::pluck($sejours, "_id");

        $affectation        = new CAffectation();
        $where              = [];
        $where["sortie"]    = "< '$date'";
        $where["sejour_id"] = CSQLDataSource::prepareIn($sejour_ids);
        /** @var CAffectation[] $affectations */
        $affectations = $affectation->loadList($where);
        CAffectation::massUpdateView($affectations);

        foreach ($affectations as $_affectation) {
            $sejours[$_affectation->sejour_id]->_ref_prev_affectation = $_affectation;
        }

        unset($where["sortie"]);
        $where["entree"] = "> '$date'";
        $affectations    = $affectation->loadList($where);
        CAffectation::massUpdateView($affectations);

        foreach ($affectations as $_affectation) {
            $sejours[$_affectation->sejour_id]->_ref_next_affectation = $_affectation;
        }

        foreach ($sejours as $_sejour) {
            if (!$_sejour->_ref_prev_affectation) {
                $_sejour->_ref_prev_affectation = new CAffectation();
            }
            if (!$_sejour->_ref_next_affectation) {
                $_sejour->_ref_next_affectation = new CAffectation();
            }
        }

        self::massLoadCurrAffectation($sejours, $date);
    }

    static function massLoadCurrAffectation(&$sejours = [], $date = null, $service_id = null): array
    {
        if (!count($sejours)) {
            return [];
        }

        if (!$date) {
            $date = CMbDT::dateTime();
        }

        $affectation        = new CAffectation();
        $where              = [];
        $where["sejour_id"] = CSQLDataSource::prepareIn(CMbArray::pluck($sejours, "_id"));
        if ($service_id) {
            $where["service_id"] = "= '$service_id'";
        }
        if (strpos($date, " ") !== false) {
            if (CMbDT::time(null, $date) === "00:00:00") {
                $ds              = CSQLDataSource::get("std");
                $where["entree"] = $ds->prepare("<= %", CMbDT::date(null, $date) . " 23:59:59");
                $where["sortie"] = $ds->prepare(">= %", CMbDT::date(null, $date) . " 00:00:01");
            } else {
                $where[] = "'$date' BETWEEN entree AND sortie";
            }
        } else {
            $where[] = "'$date' BETWEEN DATE(entree) AND DATE(sortie)";
        }
        $affectations = $affectation->loadList($where, "sortie DESC");

        foreach ($affectations as $_affectation) {
            if (!$sejours[$_affectation->sejour_id]->_ref_curr_affectation) {
                $sejours[$_affectation->sejour_id]->_ref_curr_affectation = $_affectation;
            }
        }

        CAffectation::massUpdateView($affectations);

        foreach ($sejours as $_sejour) {
            if (!$_sejour->_ref_curr_affectation) {
                $_sejour->_ref_curr_affectation = new CAffectation();
            }
        }

        return $affectations;
    }

    /**
     * Charge le dossier médical
     *
     * @return CDossierMedical
     */
    function loadRefDossierMedical()
    {
        return $this->_ref_dossier_medical = $this->loadUniqueBackRef("dossier_medical");
    }

    /**
     * Charge le RSS
     *
     * @return CRSS
     */
    function loadRefRSS()
    {
        return $this->_ref_rss = $this->loadUniqueBackRef("rss");
    }

    /**
     * Charge l'établissement externe de provenance
     *
     * @return CEtabExterne
     */
    function loadRefEtablissementProvenance()
    {
        return $this->_ref_etablissement_provenance = $this->loadFwdRef("etablissement_entree_id", true);
    }

    /**
     * Charge l'établissement externe de transfert
     *
     * @return CEtabExterne
     */
    function loadRefEtablissementTransfert()
    {
        return $this->_ref_etablissement_transfert = $this->loadFwdRef("etablissement_sortie_id", true);
    }

    /**
     * Charge le service de provenance
     *
     * @return CService
     */
    function loadRefServiceProvenance()
    {
        return $this->_ref_service_provenance = $this->loadFwdRef('service_entree_id', true);
    }

    /**
     * Charge le service de mutation
     *
     * @return CService
     */
    function loadRefServiceMutation()
    {
        return $this->_ref_service_mutation = $this->loadFwdRef("service_sortie_id", true);
    }

    /**
     * Charge l'indicateur de prix
     *
     * @return CChargePriceIndicator
     */
    function loadRefChargePriceIndicator()
    {
        return $this->_ref_charge_price_indicator = $this->loadFwdRef("charge_id", true);
    }

    /**
     * Charge le mode d'entrée
     *
     * @return CModeEntreeSejour
     */
    function loadRefModeEntree()
    {
        return $this->_ref_mode_entree = $this->loadFwdRef("mode_entree_id", true);
    }

    /**
     * Charge le mode de sortie
     *
     * @return CModeSortieSejour
     */
    function loadRefModeSortie()
    {
        return $this->_ref_mode_sortie = $this->loadFwdRef("mode_sortie_id", true);
    }

    /**
     * Charge le mode de destination
     *
     * @return CModeDestinationSejour
     */
    function loadRefModeDestination()
    {
        return $this->_ref_mode_destination = $this->loadFwdRef("mode_destination_id", true);
    }

    /**
     * Charge le mode PeC
     *
     * @return CModePECSejour
     */
    function loadRefModePeC()
    {
        return $this->_ref_mode_pec = $this->loadFwdRef("mode_pec_id", true);
    }

    public function loadRefRPUMutation(): ?CRPU
    {
        return $this->_ref_rpu_mutation = $this->loadUniqueBackRef("rpu_mute");
    }

    /**
     * Charge le user qui a autorisé la sortie
     *
     * @return CMediusers
     */
    function loadRefConfirmeUser()
    {
        return $this->_ref_confirme_user = $this->loadFwdRef("confirme_user_id", true);
    }

    /**
     * Charge la discipline tarifaire
     *
     * @return CDisciplineTarifaire
     */
    public function loadRefDisciplineTarifaire()
    {
        return $this->_ref_discipline_tarifaire = $this->loadFwdRef('discipline_id', true);
    }

    /**
     * Compte les observations de visite du praticien responsable
     *
     * @param string     $date A une date donnée, maintenant si null
     * @param CMediusers $user User courant
     *
     * @return int
     */
    function countNotificationVisite($date, $user)
    {
        if (!$date) {
            $date = CMbDT::date();
        }

        $observation        = new CObservationMedicale();
        $where              = [];
        $where["sejour_id"] = " = '$this->_id'";
        $where["user_id"]   = $user->getUserSQLClause();
        $where["degre"]     = " = 'info'";
        $where["date"]      = " LIKE '$date%'";

        return $observation->countList($where);
    }

    /**
     * Compte les visites du praticien responsable
     *
     * @param CSejour[]  $sejours Séjours
     * @param string     $date    A une date donnée
     * @param CMediusers $user    User connecté
     *
     * @return array
     */
    static function countVisitesUser($sejours, $date, $user)
    {
        $visites = [
            "all"           => [],
            "non_effectuee" => [],
        ];

        if (count($sejours)) {
            foreach ($sejours as $_sejour) {
                /* @var CSejour $_sejour */
                $nb_visites = $_sejour->countNotificationVisite($date, $user);
                if (!$nb_visites && $_sejour->entree_reelle) {
                    $visites["non_effectuee"][] = $_sejour->_id;
                    $visites["all"][]           = $_sejour->_id;
                } elseif ($nb_visites) {
                    $visites["all"][] = $_sejour->_id;
                }
            }
        }

        return $visites;
    }

    /**
     * Charge le patient
     *
     * @param bool $cache Utilise le cache
     *
     * @return CPatient
     */
    public function loadRefPatient(bool $cache = true): ?CPatient
    {
        $this->_ref_patient = $this->loadFwdRef("patient_id", $cache);
        $this->getDroitsC2S();

        // View
        if ($this->_ref_patient->_view && strstr($this->_view, $this->_ref_patient->_view) === false) {
            $this->_view = $this->_ref_patient->_view . " - " . $this->_view;
        }

        return $this->_ref_patient;
    }

    /**
     * Charge le praticien responsable
     *
     * @param bool $cache Utiliser le cache
     *
     * @return CMediusers
     */
    public function loadRefPraticien(bool $cache = true): ?CMediusers
    {
        /** @var CMediusers $praticien */
        $praticien            = $this->loadFwdRef("praticien_id", $cache);
        $this->_ref_executant = $praticien;
        $praticien->loadRefFunction();

        return $this->_ref_praticien = $praticien;
    }

    /**
     * Charge les diagnostics CIM principal et relié
     *
     * @return void
     */
    function loadExtDiagnostics()
    {
        $this->_ext_diagnostic_principal = $this->DP ? CCodeCIM10::get($this->DP) : null;
        $this->_ext_diagnostic_relie     = $this->DR ? CCodeCIM10::get($this->DR) : null;
    }

    /**
     * Charge les diagnostics CIM associés
     *
     * @param bool $split Notation française avec le point séparateur après trois caractères
     * @param bool $load  Chargement du code cim associé si split est false
     *
     * @return string[] Codes CIM
     */
    function loadDiagnosticsAssocies($split = true, $load = false)
    {
        $this->_diagnostics_associes = [];
        $this->loadRefDossierMedical();
        if ($this->_ref_dossier_medical->_id) {
            foreach ($this->_ref_dossier_medical->_codes_cim as $code) {
                if ($split && strlen($code) >= 4) {
                    $this->_diagnostics_associes[] = substr($code, 0, 3) . "." . substr($code, 3);
                } else {
                    if ($load) {
                        $this->_diagnostics_associes[] = CCodeCIM10::get($code);
                    } else {
                        $this->_diagnostics_associes[] = $code;
                    }
                }
            }
        }

        return $this->_diagnostics_associes;
    }

    /**
     * Charge le niveau de prestation principal
     *
     * @return CPrestation
     */
    function loadRefPrestation()
    {
        return $this->_ref_prestation = $this->loadFwdRef("prestation_id", true);
    }

    /**
     * Charge les transmissions du séjour
     *
     * @param bool $cible_importante Filtrer sur les cibles importantes
     * @param bool $important        Filtrer sur le degré important
     * @param bool $macro_cible      N'utiliser que les macrocible (uniquement pour les cibles importantes)
     * @param null $limit            Limite SQL
     * @param null $date             date limit à prendre en compte
     * @param null $degre            Degré de la transmission ou de la macrocible
     *
     * @return array|CStoredObject[]|null
     */
    function loadRefsTransmissions($cible_importante = false, $important = false, $macro_cible = false, $limit = null, $date = null, $degre = null)
    {
        $this->_ref_transmissions = [];

        // Chargement des dernieres transmissions des cibles importantes
        if ($cible_importante) {
            $transmission                   = new CTransmissionMedicale();
            $ljoin                          = [];
            $ljoin["category_prescription"] = "category_prescription.category_prescription_id = transmission_medicale.object_id";

            $where                                           = [];
            $where["object_class"]                           = " = 'CCategoryPrescription'";
            $where["sejour_id"]                              = " = '$this->_id'";
            $where["category_prescription.cible_importante"] = " = '1'";
            $where["cancellation_date"]                      = " IS NULL";
            if ($degre) {
                $where["degre"] = " = '$degre'";
            }
            if ($macro_cible) {
                $where["category_prescription.only_cible"] = " = '1'";
            }
            $order                    = "date DESC";
            $this->_ref_transmissions = $transmission->loadList($where, $order, $limit, null, $ljoin, "sejour_id");
        }

        // Chargement des transmissions de degré important
        if ($important) {
            $transmission               = new CTransmissionMedicale();
            $where                      = [];
            $where["sejour_id"]         = "= '$this->_id'";
            $order                      = "date DESC";
            $where["degre"]             = " = '" . ($degre ? $degre : 'high') . "'";
            $where["cancellation_date"] = " IS NULL";

            if ($date) {
                $where[] = "date_max >= '" . $date . "' OR date_max IS NULL";
            }

            $this->_ref_transmissions = $this->_ref_transmissions + $transmission->loadList($where, $order, $limit);
        }

        if (!$cible_importante && !$important) {
            $this->_ref_transmissions = $this->loadBackRefs("transmissions", null, null, null, null, null, "", ["cancellation_date IS NULL"]);
        }

        return $this->_ref_transmissions;
    }

    /**
     * Charge les observations du séjour
     *
     * @param bool   $important   Filtrer les observations importantes
     * @param string $type        Filtrer le type d'observations
     * @param string $etiquette   Filtrer l'étiquette d'observations
     * @param int    $function_id Filtrer la fonction de l'utilisateur
     * @param array  $functioins  Liste des fonctions
     *
     * @return CObservationMedicale[]
     * @throws Exception
     */
    function loadRefsObservations($important = false, $type = null, $etiquette = null, $function_id = null, &$functions = [])
    {
        $order        = "date DESC";
        $backname_alt = null;

        $where = [
            "cancellation_date" => "IS NULL",
        ];

        $ljoin = [];

        if ($type) {
            $where["type"] = "= '$type'";
        }

        if ($etiquette) {
            $where["etiquette"] = "= '$etiquette'";
        }

        if ($important) {
            $backname_alt   = 'obs_high';
            $where['degre'] = "= 'high'";
        }

        $this->_ref_observations = $this->loadBackRefs("observations", $order, null, null, $ljoin, null, $backname_alt, $where);

        CStoredObject::massLoadFwdRef($this->_ref_observations, 'user_id');

        foreach ($this->_ref_observations as $_observation) {
            $_observation->loadRefUser()->loadRefFunction();
            $functions[$_observation->_ref_user->function_id] = $_observation->_ref_user->_ref_function;

            if ($function_id && ($_observation->_ref_user->function_id != $function_id)) {
                unset($this->_ref_observations[$_observation->_id]);
            }
        }

        CMbArray::pluckSort($functions, SORT_ASC, '_view');

        return $this->_ref_observations;
    }

    /**
     * Chargement des demandes AppFine
     *
     * @return CAppFineClientOrderItem[]
     */
    function loadRefsOrdersItem($where = [], $ljoin = [])
    {
        return $this->_ref_orders_item = $this->loadBackRefs("appFine_order_items", null, null, null, $ljoin, null, null, $where);
    }

    /**
     * Chargement des documents AppFine non liés à une demande
     *
     * @return CAppFineClientObjectReceived[]
     */
    function loadRefsObjectsReceived()
    {
        return $this->_ref_objects_received = $this->loadBackRefs("object_received");
    }

    /**
     * Chargement des objectifs de soins du séjour
     *
     * @return CObjectifSoin[]
     */
    function loadRefsObjectifsSoins()
    {
        return $this->_ref_objectifs_soins = $this->loadBackRefs("objectifs_soins", "statut ASC, date DESC, libelle ASC");
    }

    /**
     * @see parent::loadAlertsNotHandled
     */
    function loadAlertsNotHandled($level = null, $tag = null, $perm = PERM_READ)
    {
        if ($tag == "observation") {
            $alert = new CAlert();
            $where = [
                "object_class" => "= 'CObservationMedicale'",
                "object_id"    => CSQLDataSource::prepareIn($this->loadBackIds("observations")),
                "level"        => "= 'medium'",
                "handled"      => "= '0'",
                "tag"          => "= '" . CObservationMedicale::$tag_alerte . "'",
            ];

            return $this->_refs_alerts_not_handled = $alert->loadList($where);
        }

        return parent::loadAlertsNotHandled($level, $tag, $perm);
    }

    function countAlertsNotHandled($level = null, $tag = null)
    {
        if ($tag == "observation") {
            $alert = new CAlert();
            $where = [
                "object_class" => "= 'CObservationMedicale'",
                "object_id"    => CSQLDataSource::prepareIn($this->loadBackIds("observations")),
                "level"        => "= 'medium'",
                "handled"      => "= '0'",
                "tag"          => "= '" . CObservationMedicale::$tag_alerte . "'",
            ];

            return $this->_count_alerts_not_handled = $alert->countList($where);
        }

        return parent::countAlertsNotHandled($level, $tag);
    }

    /**
     * Comptes les tâches en cours et réalisées
     *
     * @return int|null
     */
    function countTasks()
    {
        $where                      = ["realise" => "!= '1'"];
        $this->_count_pending_tasks = $this->countBackRefs("tasks", $where);

        $this->_count["tasks"] = null;

        return $this->_count_tasks = $this->countBackRefs("tasks");
    }

    /**
     * Comptes les tâches en cours et réalisées
     *
     * @return int|null
     */
    function countObjectifsSoins()
    {
        return $this->_count_objectifs_soins = $this->countBackRefs("objectifs_soins");
    }

    /**
     * Comptes le nombre d'objectif de soins non réévalués depuis 7 jours
     *
     * @return int
     */
    function countObjectifsDelayWeek()
    {
        $this->_count_objectifs_retard = 0;
        if ($this->_count_objectifs_soins) {
            $this->clearBackRefCache("objectifs_soins");
            $where             = [];
            $where["statut"]   = " = 'ouvert'";
            $objectifs_ouverts = $this->loadBackRefs("objectifs_soins", "statut ASC, date DESC, libelle ASC", null, null, null, null, "", $where);
            $now               = CMbDT::date();
            CStoredObject::massLoadBackRefs($objectifs_ouverts, "reevaluations", "date");
            foreach ($objectifs_ouverts as $_objectif) {
                /* @var CObjectifSoin $_objectif */
                $_objectif->loadRefsReevaluations();
                if (!count($_objectif->_ref_reevaluations)) {
                    if (abs(CMbDT::daysRelative($_objectif->date, $now)) >= 7) {
                        $this->_count_objectifs_retard++;
                    }
                } else {
                    if (abs(CMbDT::daysRelative(end($_objectif->_ref_reevaluations)->date, $now)) >= 7) {
                        $this->_count_objectifs_retard++;
                    }
                }
            }
        }

        return $this->_count_objectifs_retard;
    }

    /**
     * Charge les tâches d'un séjour
     *
     * @return CSejourTask[]
     */
    function loadRefsTasks(?string $date_min = null, ?string $date_max = null)
    {
        $where = [];
        if ($date_min && $date_max) {
            $where["date"] = "BETWEEN '$date_min' AND '$date_max'";
        } elseif ($date_min) {
            $where["date"] = ">= '$date_min'";
        } elseif ($date_max) {
            $where["date"] = "<= '$date_max'";
        }
        $this->_ref_tasks = $this->loadBackRefs("tasks", null, null, null, null, null, "", $where);

        return $this->_ref_tasks;
    }

    /**
     * Chargement des itemps de liaison
     *
     * @return CItemLiaison[]
     */
    function loadRefItemsLiaisons()
    {
        return $this->_ref_items_liaisons = $this->loadBackRefs("items_liaisons");
    }

    /**
     * Charge les rendez-vous externes d'un séjour
     *
     * @param array $where Optional conditions
     *
     * @return CRDVExterne[]
     */
    function loadRefsRDVExternes($where = [])
    {
        return $this->_refs_rdv_externes = $this->loadBackRefs("rdv_externe", null, null, null, null, null, "", $where);
    }

    /**
     * Comptes les rendez-vous externes
     *
     * @return int|null
     */
    function countRDVExternes()
    {
        return $this->_count_rdv_externe = $this->countBackRefs("rdv_externe");
    }

    /**
     * Charge les examens IGS
     *
     * @return CExamIgs[]
     */
    function loadRefsExamsIGS()
    {
        return $this->_ref_exams_igs = $this->loadBackRefs('exams_igs', 'date DESC');
    }

    /**
     * Charge l'examen IGS le plus récent
     *
     * @return CExamIgs
     */
    function loadLastExamIGS()
    {
        return $this->_ref_last_exam_igs = $this->loadLastBackRef('exams_igs', 'date ASC');
    }

    /**
     * Load the linked Chung score objects
     *
     * @return CChungScore[]
     */
    function loadRefsChungScore()
    {
        return $this->_ref_chung_scores = $this->loadBackRefs('chung_scores', 'datetime DESC');
    }

    /**
     * Charge le score de Chung le plus récent
     *
     * @return CMbObject
     */
    function loadLastChungScore()
    {
        return $this->_ref_last_chung_score = $this->loadLastBackRef('chung_scores', 'datetime ASC');
    }

    /**
     * Charge les examens Gir
     *
     * @return CStoredObject[]|null
     * @throws \Exception
     */
    function loadRefsExamsGir()
    {
        return $this->_ref_exams_gir = $this->loadBackRefs('exams_gir', 'date DESC');
    }

    /**
     * Charge l'examen Gir le plus récent
     *
     * @return CMbObject
     * @throws \Exception
     */
    function loadLastExamGir()
    {
        return $this->_ref_last_exam_gir = $this->loadLastBackRef('exams_gir', 'date ASC');
    }

    function getLibellesInterv($cancelled = false)
    {
        $where = [];

        if (!$cancelled) {
            $where['annulee'] = "= '0'";
        }

        $libelles = [];
        $intervs  = $this->_ref_operations;
        if ($intervs === null) {
            $intervs = $this->loadRefsOperations($where);
        }

        foreach ($intervs as $_interv) {
            if (!$cancelled || !$_interv->annulee) {
                $libelles[] = ($_interv->libelle) ?: CAppUI::tr('common-No label');
            }
        }

        return implode('; ', $libelles);
    }

    /**
     * Charge tout le suivi médical, composé d'observations, transmissions, consultations et prescriptions
     *
     * @param string $datetime_min Date de référence à partir de laquelle filtrer
     * @param null   $cible_trans
     * @param array  $cibles
     * @param array  $last_trans_cible
     * @param int    $user_id
     * @param array  $users
     * @param int    $function_id
     * @param array  $functions
     * @param int    $print
     * @param null   $datetime_max
     * @param string $dietetique
     * @param null   $etiquette
     *
     * @return array|CMbObject[]
     * @throws Exception
     */
    function loadSuiviMedical(
        $datetime_min = null,
        $cible_trans = null,
        &$cibles = [],
        &$last_trans_cible = [],
        $user_id = null,
        &$users = [],
        $function_id = null,
        &$functions = [],
        $print = 0,
        $datetime_max = null,
        $dietetique = "",
        $etiquette = null
    ) {
        if ($datetime_min || $datetime_max) {
            $trans      = new CTransmissionMedicale();
            $whereTrans = [];
            if ($datetime_min) {
                $whereTrans[] = "(degre = 'high' AND (date_max IS NULL OR date_max >= '$datetime_min')) OR (date >= '$datetime_min')";
            }
            if ($datetime_max) {
                $whereTrans[] = "(degre = 'high' AND (date_max IS NULL OR date_max <= '$datetime_max')) OR (date <= '$datetime_max')";
            }

            if ($print) {
                $whereTrans["cancellation_date"] = " IS NULL";
            }
            if ($dietetique !== "") {
                $whereTrans["dietetique"] = " = '$dietetique'";
            }
            $whereTrans["sejour_id"]      = " = '$this->_id'";
            $this->_back["transmissions"] = $trans->loadList($whereTrans, "date DESC, transmission_medicale_id DESC");

            $obs      = new CObservationMedicale();
            $whereObs = [];
            if ($datetime_min) {
                $whereObs[] = "(degre = 'high') OR (date >= '$datetime_min')";
            }
            if ($datetime_max) {
                $whereObs[] = "date <= '$datetime_max'";
            }
            if ($print) {
                $whereObs["cancellation_date"] = " IS NULL";
            }
            if ($etiquette) {
                $whereObs["etiquette"] = " = '$etiquette'";
            }
            $whereObs["sejour_id"]       = " = '$this->_id'";
            $this->_back["observations"] = $obs->loadList($whereObs);
        } else {
            $where = [];
            if ($print) {
                $where["cancellation_date"] = "IS NULL";
            }
            if ($etiquette) {
                $where["etiquette"] = " = '$etiquette'";
            }
            $this->loadBackRefs("observations", null, null, null, null, null, "", $where);
            if ($etiquette) {
                unset($where["etiquette"]);
            }
            if ($dietetique !== "") {
                $where["dietetique"] = " = '$dietetique'";
            }

            $this->loadBackRefs("transmissions", "date DESC, transmission_medicale_id DESC", null, null, null, null, "", $where);
        }

        $consultations = $this->loadRefsConsultations();

        $this->_ref_suivi_medical = [];

        if (isset($this->_back["observations"])) {
            CObservationMedicale::massLoadRefAlerte($this->_back["observations"]);

            foreach ($this->_back["observations"] as $curr_obs) {
                /** @var CObservationMedicale $curr_obs */
                $curr_obs->loadRefsFwd();
                $users[$curr_obs->user_id]                    = $curr_obs->_ref_user;
                $functions[$curr_obs->_ref_user->function_id] = $curr_obs->_ref_user->loadRefFunction();

                if (($user_id && ($curr_obs->user_id != $user_id))
                    || ($function_id && ($curr_obs->_ref_user->function_id != $function_id))
                    || ($dietetique === 1 && $curr_obs->etiquette !== 'dietetique')) {
                    continue;
                }
                $curr_obs->_ref_user->loadRefFunction();
                $curr_obs->canEdit();
                $this->_ref_suivi_medical[$curr_obs->date . $curr_obs->_id . "obs"] = $curr_obs;
            }
        }
        $group = CGroups::loadCurrent();
        if (isset($this->_back["transmissions"])) {
            $trans_compact = CAppUI::conf("soins Transmissions trans_compact", $group) && !$print;
            $list_trans    = [];

            $cibles_trans = CStoredObject::massLoadFwdRef($this->_back["transmissions"], "cible_id");

            foreach ($cibles_trans as $_cible) {
                $cibles_trans[$_cible->_id] = $_cible;
            }

            /** @var CTransmissionMedicale $curr_trans * */
            foreach ($this->_back["transmissions"] as $curr_trans) {
                $curr_trans->loadRefsFwd();
                $users[$curr_trans->user_id]                    = $curr_trans->_ref_user;
                $functions[$curr_trans->_ref_user->function_id] = $curr_trans->_ref_user->loadRefFunction();

                if (($user_id && ($curr_trans->user_id != $user_id))
                    || ($function_id && ($curr_trans->_ref_user->function_id != $function_id))
                    || ($dietetique === 1 && !$curr_trans->dietetique)) {
                    continue;
                }

                $curr_trans->calculCibles($cibles);
                if ($cible_trans && $curr_trans->_cible != $cible_trans) {
                    continue;
                }

                $list_trans[] = $curr_trans;

                $curr_trans->canEdit();
                $curr_trans->loadRefUser();
                $curr_trans->loadRefCible();
                $key_last_trans = $curr_trans->cible_id;

                if ($key_last_trans && !$curr_trans->_ref_cible->report) {
                    $key_last_trans = "$curr_trans->object_class-$curr_trans->object_id-$curr_trans->libelle_ATC";
                }

                if ($key_last_trans && !isset($last_trans_cible[$key_last_trans])) {
                    $last_trans_cible[$key_last_trans] = $curr_trans;
                }
            }

            $see_old_trans = CAppUI::conf("soins Transmissions see_old_trans", $group);
            $tmp_trans     = [];
            $tmp_old_trans = [];

            foreach ($list_trans as $_trans) {
                $sort_key_pattern = "$_trans->_class $_trans->user_id $_trans->cible_id";

                $sort_key = "$_trans->date $sort_key_pattern";

                $date_before     = CMbDT::dateTime("-1 SECOND", $_trans->date);
                $sort_key_before = "$date_before $sort_key_pattern";

                $date_after     = CMbDT::dateTime("+1 SECOND", $_trans->date);
                $sort_key_after = "$date_after $sort_key_pattern";

                $old_trans = false;

                $key_last_trans = $_trans->cible_id;

                if ($key_last_trans && !$_trans->_ref_cible->report) {
                    $key_last_trans = "$_trans->object_class-$_trans->object_id-$_trans->libelle_ATC";
                }

                if ($key_last_trans && $last_trans_cible[$key_last_trans] != $_trans) {
                    if ($last_trans_cible[$key_last_trans]->locked && !$print) {
                        continue;
                    }

                    // En mode compact, on stocke pour l'affichage des transmissions précédentes
                    if ($trans_compact
                        && !array_key_exists($sort_key, $tmp_trans)
                        && !array_key_exists($sort_key_before, $tmp_trans)
                        && !array_key_exists($sort_key_after, $tmp_trans)
                    ) {
                        $old_trans = true;
                    }
                }

                // Aggrégation à -1 sec
                if (array_key_exists($sort_key_before, $tmp_trans)) {
                    $sort_key = $sort_key_before;
                } // à +1 sec
                else {
                    if (array_key_exists($sort_key_after, $tmp_trans)) {
                        $sort_key = $sort_key_after;
                    }
                }
                if ($old_trans) {
                    $tmp_trans_update =& $tmp_old_trans;
                } else {
                    $tmp_trans_update =& $tmp_trans;
                }

                if (!isset($tmp_trans_update[$sort_key])) {
                    $tmp_trans_update[$sort_key] = ["data" => [], "action" => [], "result" => []];
                }
                if (!isset($tmp_trans_update[$sort_key][0])) {
                    $tmp_trans_update[$sort_key][0] = $_trans;
                }
                $tmp_trans_update[$sort_key][$_trans->type][] = $_trans;
            }

            // Affichage de la précédente transmission lors de l'affichage par groupe
            if ($see_old_trans) {
                $tmp_trans_keys = array_merge(array_keys($tmp_trans), array_keys($tmp_old_trans));

                foreach (array_keys($tmp_trans) as $sort_key) {
                    $sorts    = explode(" ", $sort_key);
                    $cible_id = end($sorts);

                    if (!$cible_id) {
                        continue;
                    }

                    // On n'affiche que si le flag report est valué
                    if (!$cibles_trans[$cible_id]->report) {
                        continue;
                    }

                    foreach (preg_grep("/$cible_id$/", $tmp_trans_keys) as $other_sort_key) {
                        if ($other_sort_key == $sort_key) {
                            continue;
                        }

                        foreach (["data", "action"] as $type) {
                            if (@count($tmp_trans[$sort_key][$type])) {
                                continue;
                            }
                            if ($type == "action" && @count($tmp_trans[$sort_key]["data"])) {
                                $can_add = true;

                                foreach ($tmp_trans[$sort_key]["data"] as $_trans) {
                                    if (!$_trans->_old) {
                                        $can_add = false;
                                    }
                                }

                                if (!$can_add) {
                                    continue;
                                }
                            }

                            if (isset($tmp_trans[$other_sort_key][$type])) {
                                foreach ($tmp_trans[$other_sort_key][$type] as $_trans) {
                                    $tmp_trans[$sort_key][$type][] = clone $_trans;
                                }
                            }
                            if (isset($tmp_old_trans[$other_sort_key][$type])) {
                                foreach ($tmp_old_trans[$other_sort_key][$type] as $_trans) {
                                    if (!isset($tmp_trans[$sort_key][0])) {
                                        $tmp_trans[$sort_key][0] = clone $_trans;
                                    }
                                    $tmp_trans[$sort_key][$type][] = clone $_trans;
                                }
                            }

                            // On flag en _old pour la classe compact dans l'ihm
                            foreach ($tmp_trans[$sort_key][$type] as $_trans) {
                                $_trans->_old = 1;
                            }
                        }
                    }
                    // On supprime la première clé de la liste (on parcourt par date décroissante, pas de retour vers le futur
                    // pour l'ajout des transmissions précédentes)
                    array_shift($tmp_trans_keys);
                }
            }

            if (CAppUI::conf("soins Transmissions show_priority_hight", $group)) {
                foreach ($tmp_trans as $key_suivi => $_suivi) {
                    if (is_array($_suivi) && ($_suivi[0] instanceof CTransmissionMedicale) && $_suivi[0]->degre == "high") {
                        unset($tmp_trans[$key_suivi]);
                        $this->_ref_suivi_medical["a" . $key_suivi] = $_suivi;
                    }
                }
            }
            foreach ($tmp_trans as $sort_key => $_trans_by_key) {
                $this->_ref_suivi_medical[$sort_key] = $_trans_by_key;
            }
        }

        if (!$dietetique) {
            CStoredObject::massLoadBackRefs($consultations, "consult_anesth");
            CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
            foreach ($consultations as $_consultation) {
                $_consultation->canEdit();
                $_consultation->loadRefConsultAnesth();
                $_consultation->loadRefPlageConsult();
                $_consultation->loadRefPraticien()->loadRefFunction();
                foreach ($_consultation->_refs_dossiers_anesth as $_dossier_anesth) {
                    $_dossier_anesth->loadRefOperation();
                    $_dossier_anesth->loadRefsTechniques();
                }
                $this->_ref_suivi_medical[$_consultation->_datetime . $_consultation->_guid] = $_consultation;
            }

            $consult_anesth = $this->loadRefsConsultAnesth();
            if ($consult_anesth->operation_id) {
                $consult                          = $consult_anesth->loadRefConsultation();
                $consult->_ref_consult_anesth     = $consult_anesth;
                $consult->_refs_dossiers_anesth[] = $consult_anesth;
                $consult->canEdit();
                $consult_anesth->loadRefOperation();
                $consult_anesth->loadRefsTechniques();
                $consult->loadRefPraticien()->loadRefFunction();
                $this->_ref_suivi_medical[$consult->_datetime . $consult_anesth->_guid] = $consult;
            }
        }

        if (!$dietetique &&
            CModule::getActive("dPprescription") &&
            in_array($this->type, self::getTypesSejoursUrgence($this->praticien_id)) &&
            CAppUI::conf("dPprescription CPrescription prescription_suivi_soins", $group)
        ) {
            $this->loadRefPrescriptionSejour();
            $prescription = $this->_ref_prescription_sejour;

            // Chargement des lignes de prescriptions d'elements
            $prescription->loadRefsLinesElement();
            $prescription->loadRefsLinesAllComments();

            foreach ($prescription->_ref_prescription_lines_all_comments as $_comment) {
                $_comment->canEdit();
                $_comment->countBackRefs("transmissions");
                $this->_ref_suivi_medical["$_comment->debut $_comment->time_debut $_comment->_guid"] = $_comment;
            }

            // Ajout des lignes de prescription dans la liste du suivi de soins
            foreach ($prescription->_ref_prescription_lines_element as $_line_element) {
                $_line_element->canEdit();
                $_line_element->countBackRefs("transmissions");
                $this->_ref_suivi_medical["$_line_element->debut $_line_element->time_debut $_line_element->_guid"] = $_line_element;
            }
        }

        krsort($this->_ref_suivi_medical);

        CMbArray::pluckSort($users, SORT_ASC, "_view");
        CMbArray::pluckSort($functions, SORT_ASC, "_view");

        return $this->_ref_suivi_medical;
    }

    /**
     * Chargement du médecin traitant
     *
     * @return CStoredObject|CMedecin
     */
    function loadRefMedecinTraitant()
    {
        return $this->_ref_medecin_traitant = $this->loadFwdRef("medecin_traitant_id", true);
    }

    /**
     * Charge toutes les constantes médicales et l'ajoute au suivi médical
     *
     * @param string $user_id Filtrer sur les créateur de la ligne
     *
     * @return CMbObject[]
     */
    function loadRefConstantes($user_id = null)
    {
        /** @var CConstantesMedicales[] $constantes */
        $constantes = $this->loadListConstantesMedicales();
        CStoredObject::massLoadBackRefs($constantes, "comments");
        foreach ($constantes as $_const) {
            $_const->loadRefUser();
            $_const->loadRefsComments();
            if ($_const->context_class != "CSejour" || $_const->context_id != $this->_id) {
                unset($constantes[$_const->_id]);
            }
            if ($user_id && $_const->user_id != $user_id) {
                unset($constantes[$_const->_id]);
            }
        }

        if (!$this->_ref_suivi_medical) {
            $this->_ref_suivi_medical = [];
        }

        foreach ($constantes as $_constante) {
            $this->_ref_suivi_medical[$_constante->datetime . $_constante->user_id . "constante"] = $_constante;
        }

        krsort($this->_ref_suivi_medical);
    }

    /**
     * Load associated Group
     *
     * @return CGroups
     */
    function loadRefEtablissement()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Load associated RPU
     *
     * @return CRPU
     */
    function loadRefRPU()
    {
        return $this->_ref_rpu = $this->loadUniqueBackRef("rpu");
    }

    /**
     * Load associated BilanSSR
     *
     * @return CBilanSSR
     */
    function loadRefBilanSSR()
    {
        return $this->_ref_bilan_ssr = $this->loadUniqueBackRef("bilan_ssr");
    }

    /**
     * Charge la fiche d'autonomie associé
     *
     * @return CFicheAutonomie
     */
    function loadRefFicheAutonomie()
    {
        return $this->_ref_fiche_autonomie = $this->loadUniqueBackRef("fiche_autonomie");
    }

    /**
     * Charge le praticien adressant
     *
     * @return CMedecin
     */
    function loadRefAdresseParPraticien()
    {
        return $this->_ref_adresse_par_prat = $this->loadFwdRef("adresse_par_prat_id", true);
    }

    /**
     * Charge le dossier d'anesthésie associé au séjour
     *
     * @return CConsultAnesth
     */
    function loadRefsConsultAnesth()
    {
        if ($this->_ref_consult_anesth) {
            return $this->_ref_consult_anesth;
        }

        return $this->_ref_consult_anesth = $this->loadFirstBackRef("consultations_anesths", "consultation_anesth_id ASC");
    }

    /**
     * Charge la dernière consultation préanesthésique du séjour
     *
     * @return array
     */
    function loadRefLastCPA()
    {
        $ljoin                 = [];
        $ljoin["consultation"] = "consultation.consultation_id = consultation_anesth.consultation_id";
        $ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

        $where                                  = [];
        $where['consultation.annule']           = " = '0'";
        $where['consultation_anesth.sejour_id'] = " = '$this->_id'";

        $consult_anesth  = new CConsultAnesth();
        $consults_anesth = $consult_anesth->loadList($where, null, null, null, $ljoin);

        $cpa      = [
            "date_cpa"     => null,
            "consult_guid" => null,
        ];
        $last_cpa = end($consults_anesth);

        if (!empty($last_cpa)) {
            $last_cpa->loadRefConsultation()->loadRefPlageConsult();
            $cpa = [
                "date_cpa"     => $last_cpa->_ref_consultation->_datetime,
                "consult_guid" => $last_cpa->_ref_consultation->_guid,
            ];
        }

        return $this->_ref_last_cpa = $cpa;
    }

    /**
     * Charge les consultations, en particulier l'ATU dans le cas UPATOU
     *
     * @param string $order order of the list
     * @param array  $where Where clause
     *
     * @return CConsultation[]
     */
    function loadRefsConsultations($order = "date DESC, heure DESC", $where = [])
    {
        $this->_ref_consultations = $this->loadBackRefs(
            "consultations",
            $order,
            null,
            null,
            ["plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"],
            null,
            "",
            $where
        );

        $this->_ref_consult_atu = new CConsultation();

        foreach ($this->_ref_consultations as $_consult) {
            /** @var CConsultation $_consult */
            $praticien = $_consult->loadRefPraticien();
            $praticien->loadRefFunction();
            $_consult->canDo();
            if ($praticien->isUrgentiste() && ($this->countBackRefs("rpu") > 0 || !CAppUI::conf("dPurgences create_sejour_hospit"))) {
                $this->_ref_consult_atu = $_consult;
                $this->_ref_consult_atu->countDocItems();
            }
        }

        $this->_ref_last_consult = count($this->_ref_consultations) ? reset($this->_ref_consultations) : new CConsultation();

        return $this->_ref_consultations;
    }

    /**
     * Chargement de toutes les prescriptions liées au sejour (object_class CSejour)
     *
     * @return CPrescription[]
     */
    function loadRefsPrescriptions()
    {
        $prescriptions = $this->loadBackRefs("prescriptions");
        // Si $prescriptions n'est pas un tableau, module non installé
        if (!is_array($prescriptions)) {
            $this->_ref_last_prescription = null;

            return null;
        }
        $this->_count_prescriptions                = count($prescriptions);
        $this->_ref_prescriptions["pre_admission"] = new CPrescription();
        $this->_ref_prescriptions["sejour"]        = new CPrescription();
        $this->_ref_prescriptions["sortie"]        = new CPrescription();

        // Stockage des prescriptions par type
        foreach ($prescriptions as $_prescription) {
            $this->_ref_prescriptions[$_prescription->type] = $_prescription;
        }

        return $this->_ref_prescriptions;
    }

    /**
     * Chargement de la prescription d'hospitalisation
     *
     * @return CPrescription
     */
    function loadRefPrescriptionSejour()
    {
        if (!CModule::getActive("dPprescription")) {
            return null;
        }

        $this->_ref_prescription_sejour = new CPrescription();
        if (!$this->_id) {
            return $this->_ref_prescription_sejour;
        }

        $this->_ref_prescription_sejour->object_class = "CSejour";
        $this->_ref_prescription_sejour->object_id    = $this->_id;
        $this->_ref_prescription_sejour->type         = "sejour";
        $this->_ref_prescription_sejour->loadMatchingObject();

        return $this->_ref_prescription_sejour;
    }

    /**
     * Chargement de la prescription de séjour pour une collection de séjours
     *
     * @param self[] $sejours
     *
     * @return CPrescription[]
     */
    static function massLoadRefPrescriptionSejour($sejours = [])
    {
        if (!count($sejours) || !CModule::getActive("dPprescription")) {
            return [];
        }

        $prescription = new CPrescription();

        $where = [
            "object_class" => "= 'CSejour'",
            "object_id"    => CSQLDataSource::prepareIn(CMbArray::pluck($sejours, "sejour_id")),
            "type"         => "= 'sejour'",
        ];

        $prescriptions = $prescription->loadList($where);

        foreach ($prescriptions as $_prescription) {
            $sejours[$_prescription->object_id]->_ref_prescription_sejour = $_prescription;
        }

        foreach ($sejours as $_sejour) {
            if (!$_sejour->_ref_prescription_sejour) {
                $_sejour->_ref_prescription_sejour = new CPrescription();
            }
        }

        return $prescriptions;
    }

    /**
     * Chargement de l'ensemble des prescripteurs
     *
     * @return CMediusers[]
     */
    function loadRefsPrescripteurs()
    {
        $this->_ref_prescripteurs = [];
        $this->loadRefsPrescriptions();
        foreach ($this->_ref_prescriptions as $_prescription) {
            $_prescription->getPraticiens();
            if (is_array($_prescription->_praticiens)) {
                foreach ($_prescription->_praticiens as $_praticien_id => $_praticien_view) {
                    if (!is_array($this->_ref_prescripteurs) || !array_key_exists($_praticien_id, $this->_ref_prescripteurs)) {
                        $praticien                                = new CMediusers();
                        $this->_ref_prescripteurs[$_praticien_id] = $praticien->load($_praticien_id);
                    }
                }
            }
        }

        return $this->_ref_prescripteurs;
    }

    /**
     * Chargement des remplacements pour ce séjour
     *
     * @return CReplacement[]
     */
    function loadRefReplacements()
    {
        return $this->_ref_replacements = $this->loadBackRefs("replacements");
    }

    /**
     * Chargement du remplacement
     *
     * @param int $conge_id le congé
     *
     * @return CReplacement
     */
    function loadRefReplacement($conge_id)
    {
        $this->_ref_replacement            = new CReplacement;
        $this->_ref_replacement->sejour_id = $this->_id;
        $this->_ref_replacement->conge_id  = $conge_id;
        $this->_ref_replacement->loadMatchingObject();

        return $this->_ref_replacement;
    }

    /**
     * Chargement de la grossesse associée
     *
     * @return CGrossesse
     */
    function loadRefGrossesse()
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }

    /**
     * Calcul de la date en semaines d'aménorrhée
     *
     * @return int
     */
    function getSA()
    {
        $this->loadRefGrossesse();
        $sa_comp = $this->_ref_grossesse->getAgeGestationnel($this->entree);

        return $this->_sa = $sa_comp["SA"];
    }

    /**
     * Cherche si utilisateur est remplacant pour le séjour
     *
     * @param string $replacer_id Filtre sur l'utilisateur
     *
     * @return int Nombre de remplacement
     */
    function isReplacer($replacer_id)
    {
        $replacement              = new CReplacement;
        $replacement->sejour_id   = $this->_id;
        $replacement->replacer_id = $replacer_id;

        return $replacement->countMatchingList();
    }

    /**
     * Chargement de constantes médicales
     *
     * @param array $where Clauses where
     *
     * @return CConstantesMedicales[]
     */
    function loadListConstantesMedicales($where = [])
    {
        if ($this->_list_constantes_medicales) {
            return $this->_list_constantes_medicales;
        }

        $this->loadRefsConsultations();
        $this->loadRefsConsultAnesth();
        if (!empty($this->_ref_consultations) || $this->_ref_consult_anesth) {
            $whereOr   = [];
            $whereOr[] = "(context_class = '$this->_class' AND context_id = '$this->_id')";
            foreach ($this->_ref_consultations as $_ref_consult) {
                $whereOr[] = "(context_class = '$_ref_consult->_class' AND context_id = '$_ref_consult->_id')";
            }
            if ($this->_ref_consult_anesth) {
                $consult   = $this->_ref_consult_anesth->loadRefConsultation();
                $whereOr[] = "(context_class = '$consult->_class' AND context_id = '$consult->_id')";
            }
            $where[] = implode(" OR ", $whereOr);
        } else {
            $where['context_class'] = " = '$this->_class'";
            $where['context_id']    = " = '$this->_id'";
        }
        $constantes          = new CConstantesMedicales();
        $where['patient_id'] = " = '$this->patient_id'";

        return $this->_list_constantes_medicales = $constantes->loadList($where, 'datetime ASC');
    }

    /**
     * @see parent::loadRefsFwd()
     * @see deprecated
     */
    function loadRefsFwd($cache = true)
    {
        $this->loadRefPatient($cache);
        $this->loadRefPraticien($cache);
        $this->loadRefEtablissement();
        $this->loadRefEtablissementTransfert();
        $this->loadRefServiceMutation();
        $this->loadExtCodesCCAM();
        $this->loadRefFacture();
    }

    /**
     * Charge les éléments de codage CCAM
     *
     * @param string $from The begin date for the CCodageCCAM
     * @param string $to   The end date for the CCodageCCAM
     *
     * @return CCodageCCAM[]
     */
    public function loadRefsCodagesCCAM(string $from = null, string $to = null): array
    {
        if ($this->_ref_codages_ccam && !$from && !$to) {
            return $this->_ref_codages_ccam;
        }

        /** @var CCodageCCAM[] $codages */
        $codages                 = $this->loadBackRefs('codages_ccam', 'activite_anesth desc');
        $this->_ref_codages_ccam = [];
        foreach ($codages as $_codage) {
            if (!array_key_exists($_codage->praticien_id, $this->_ref_codages_ccam)) {
                $this->_ref_codages_ccam[$_codage->praticien_id] = [];
            }
            if (
                ($from && $to && $_codage->date >= $from && $_codage->date <= $to) ||
                ($from && !$to && $_codage->date >= $from) || (!$from && $to && $_codage->date <= $to)
            ) {
                if (!array_key_exists($_codage->date, $this->_ref_codages_ccam[$_codage->praticien_id])) {
                    $this->_ref_codages_ccam[$_codage->praticien_id][$_codage->date] = [];
                }

                $this->_ref_codages_ccam[$_codage->praticien_id][$_codage->date][] = $_codage;
            }
        }


        return $this->_ref_codages_ccam;
    }

    /**
     * Relie les actes aux codages pour calculer les règles d'association
     *
     * @return void
     */
    public function guessActesAssociation(): void
    {
        $this->loadRefsActesCCAM();
        $this->loadRefsCodagesCCAM();
        foreach ($this->_ref_codages_ccam as $_codages_by_prat) {
            foreach ($_codages_by_prat as $_codage_by_day) {
                foreach ($_codage_by_day as $_codage) {
                    $_codage->_ref_actes_ccam = [];
                    foreach ($this->_ref_actes_ccam as $_acte) {
                        if (
                            $_codage->praticien_id == $_acte->executant_id
                            && (($_acte->code_activite == 4 && $_codage->activite_anesth)
                                || ($_acte->code_activite != 4 && !$_codage->activite_anesth))
                            && ($_acte->execution >= "$_codage->date 00:00:00"
                                && $_acte->execution <= "$_codage->date 23:59:59")
                        ) {
                            $_codage->_ref_actes_ccam[$_acte->_id] = $_acte;
                        }
                    }

                    $_codage->guessActesAssociation();
                }
            }
        }
    }

    /**
     * @see parent::loadComplete()
     */
    function loadComplete()
    {
        if (!$this->_id) {
            return;
        }

        parent::loadComplete();

        // Chek if operations were loaded yet (cf ExObjects)
        $operations = $this->_ref_operations;
        if ($operations === null) {
            $operations = $this->loadRefsOperations();
        }

        foreach ($operations as $operation) {
            $operation->loadRefsFwd();
            $operation->loadRefBrancardage();
            $operation->loadRefChirs();
            $operation->_ref_chir->loadRefSpecCPAM();
            $operation->_ref_chir->loadRefDiscipline();
        }

        foreach ($this->_ref_affectations as $affectation) {
            $affectation->loadRefLit();
            $affectation->_ref_lit->loadCompleteView();
        }

        if ($this->_ref_actes_ccam) {
            foreach ($this->_ref_actes_ccam as $acte_ccam) {
                $acte_ccam->loadRefsFwd();
            }
        }

        $this->loadExtDiagnostics();

        // Chargement du RPU dans le cas des urgences
        $this->loadRefRPU();
        if ($this->_ref_rpu) {
            $this->_ref_rpu->loadRefSejour();
        }

        $this->loadNDA();

        // Chargement de la consultation préanesthésique pour l'affichage de la fiche d'anesthesie
        $this->loadRefsConsultAnesth();
        $this->_ref_consult_anesth->loadRefConsultation();

        $this->loadSuiviMedical();
        $this->_ref_patient->loadRefPhotoIdentite();

        /** @var CChungScore $last_chung */
        $last_chung = $this->loadLastChungScore();
        if ($last_chung && $last_chung->_id) {
            $this->_latest_chung_score = $last_chung->total;
        }

        $this->_libelles_interv = $this->getLibellesInterv();

        $obs_entree = $this->loadRefObsEntree();

        $fields_obs_entree = [
            "motif",
            "histoire_maladie",
            "examen",
            "rques",
            "conclusion",
        ];

        foreach ($fields_obs_entree as $_field) {
            $this->{"_obs_entree_$_field"} = $obs_entree->$_field;
        }
    }

    /**
     * @see parent::loadView()
     */
    public function loadView(): void
    {
        parent::loadView();

        if (CBrisDeGlace::isBrisDeGlaceRequired()) {
            $canAccess = CAccessMedicalData::checkForSejour($this);
            if ($canAccess) {
                $this->_can->read = 1;
            }
        }

        $this->loadRefPatient()->loadRefPhotoIdentite();
        $this->_ref_patient->loadIPP();
        $this->_ref_patient->updateBMRBHReStatus($this);

        $this->loadRefRPU();
        $this->loadRefEtablissement();
        $affectations = $this->loadRefsAffectations();

        foreach ($this->loadRefsOperations() as $_operation) {
            $_operation->loadRefChir();
            $_operation->loadRefPlageOp();
        }

        if (is_array($affectations) && count($affectations)) {
            foreach ($affectations as $_affectation) {
                /** @var CAffectation $_affectation */
                if (!$_affectation->lit_id) {
                    $_affectation->_view = $_affectation->loadRefService()->_view;
                } else {
                    $_affectation->loadRefLit()->loadCompleteView();
                    $_affectation->_view = $_affectation->_ref_lit->_view;
                }

                $_affectation->loadRefParentAffectation();
            }
        }

        $this->loadNDA();

        if (CModule::getActive("dPprescription")) {
            $this->loadRefPrescriptionSejour();
        }

        if (CModule::getActive("maternite")) {
            if ($this->grossesse_id) {
                foreach ($this->loadRefGrossesse()->loadRefsNaissances() as $_naissance) {
                    $_naissance->loadRefSejourEnfant()->loadRefPatient();
                }
            } else {
                $this->loadRefNaissance()->loadRefSejourMaman()->loadRefPatient();
            }
        }

        if (CModule::getActive("printing")) {
            // Compter les imprimantes pour l'impression d'étiquettes
            $user_printers      = CMediusers::get();
            $function           = $user_printers->loadRefFunction();
            $this->_nb_printers = $function->countBackRefs("printers");
        }

        // On compte les modèles d'étiquettes pour :
        // - stream si un seul
        // - modale de choix si plusieurs
        $modele_etiquette               = new CModeleEtiquette();
        $modele_etiquette->object_class = "CSejour";
        $modele_etiquette->group_id     = $this->group_id;
        $this->_count_modeles_etiq      = $modele_etiquette->countMatchingList();

        // Iconographie de la consultation sur les systèmes tiers
        $this->loadExternalIdentifiers($this->group_id);

        // Compteur appFine des demandes
        if (CModule::getActive("appFineClient") && CAppUI::gconf("appFineClient Sync allow_appfine_sync")) {
            CAppFineClient::loadIdex($this->_ref_patient, $this->group_id);

            if ($this->_ref_patient->_ref_appFine_idex && $this->_ref_patient->_ref_appFine_idex->_id) {
                $this->_ref_patient->loadRefStatusPatientUser();
                CAppFineClient::loadIdex($this, CGroups::loadCurrent()->_id);
            }

            if ($this->_ref_patient->_ref_appFine_idex && $this->_ref_patient->_ref_appFine_idex->_id
                    && $this->_ref_appFine_idex && $this->_ref_appFine_idex->_id
            ) {
                CAppFineClient::countOrders($this);
                $this->loadRefsFoldersRelaunchByType();
            }
        }

        if (CModule::getActive("transport")) {
            $this->loadRefsTransports();
        }

        CUniteFonctionnelle::getAlertesUFs($this);
    }

    /**
     * Charge le sejour ayant les traits suivants :
     * - Meme patient
     * - Meme type de séjour
     * - Date d'entree équivalente
     *
     * @param bool $strict       Le séjour this exclu
     * @param bool $notCancel    Seulement les non annulés
     * @param bool $useSortie    Filtrer aussi sur la date de sortie
     * @param bool $usePraticien Filtrer aussi sur le praticien
     *
     * @return int|void Nombre d'occurences trouvées
     */
    function loadMatchingSejour($strict = false, $notCancel = false, $useSortie = true, $usePraticien = false)
    {
        if ($strict && $this->_id) {
            $where["sejour_id"] = " != '$this->_id'";
        }
        $where["patient_id"] = " = '$this->patient_id'";

        $this->entree = CValue::first($this->entree_reelle, $this->entree_prevue);
        if ($useSortie) {
            $this->sortie = CValue::first($this->sortie_reelle, $this->sortie_prevue);
        }

        if (!$this->entree) {
            return null;
        }

        if ($this->entree) {
            $date_entree = CMbDT::date($this->entree);
            $where[]     = "DATE(entree_prevue) = '$date_entree' OR DATE(entree_reelle) = '$date_entree'";
        }
        if ($useSortie) {
            if ($this->sortie) {
                $date_sortie = CMbDT::date($this->sortie);
                $where[]     = "DATE(sortie_prevue) = '$date_sortie' OR DATE(sortie_reelle) = '$date_sortie'";
            }
        }

        if ($usePraticien) {
            $where["praticien_id"] = " = '$this->praticien_id'";
        }

        if ($notCancel) {
            $where["annule"] = " = '0'";
        }

        if ($this->type) {
            $where["type"] = " = '$this->type'";
        }

        $this->loadObject($where);

        return $this->countList($where);
    }

    /**
     * Construit le tag NDA en fonction des variables de configuration
     *
     * @param int    $group_id Permet de charger le NDA pour un établissement donné si non null
     * @param string $type_tag Permet de spécifier le type de tag
     *
     * @return string|void
     */
    static function getTagNDA($group_id = null, $type_tag = "tag_dossier")
    {
        // Recherche de l'établissement
        $group = CGroups::get($group_id);
        if (!$group_id) {
            $group_id = $group->_id;
        }

        $cache = new Cache('CSejour.getTagNDA', [$group_id, $type_tag], Cache::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        // Gestion du tag NDA par son domaine d'identification
        if (CAppUI::conf("eai use_domain")) {
            $tag_NDA = CDomain::getMasterDomainSejour($group_id)->tag;

            if ($type_tag != "tag_dossier") {
                $tag_NDA = CAppUI::conf("dPplanningOp CSejour $type_tag") . $tag_NDA;
            }

            return $cache->put($tag_NDA, false);
        }

        $tag_NDA = CAppUI::conf("dPplanningOp CSejour tag_dossier");

        if ($type_tag != "tag_dossier") {
            $tag_NDA = CAppUI::conf("dPplanningOp CSejour $type_tag") . $tag_NDA;
        }

        // Si on est dans le cas d'un établissement gérant la numérotation
        $group->loadConfigValues();
        if ($group->_configs["smp_idex_generator"]) {
            $tag_NDA = CAppUI::conf("smp tag_nda");
        }

        // Pas de tag Num dossier
        if (null == $tag_NDA) {
            return $cache->put(null, false);
        }

        // Préférer un identifiant externe de l'établissement
        if ($tag_group_idex = CAppUI::conf("dPplanningOp CSejour tag_dossier_group_idex")) {
            $idex = new CIdSante400();
            $idex->loadLatestFor($group, $tag_group_idex);
            $group_id = $idex->id400;
        }

        return $cache->put(str_replace('$g', $group_id, $tag_NDA), false);
    }

    /**
     * Construit le tag NPA (préad) en fonction des variables de configuration
     *
     * @param int $group_id Permet de charger le NPA pour un établissement donné si non null
     *
     * @return string
     */
    static function getTagNPA($group_id = null)
    {
        return self::getTagNDA($group_id, "tag_dossier_pa");
    }

    /**
     * Construit le tag NTA (trash) en fonction des variables de configuration
     *
     * @param int $group_id Permet de charger le NTA pour un établissement donné si non null
     *
     * @return string
     */
    static function getTagNTA($group_id = null)
    {
        return self::getTagNDA($group_id, "tag_dossier_trash");
    }

    /**
     * Construit le tag NRA (rang) en fonction des variables de configuration
     *
     * @param int $group_id Permet de charger le NRA pour un établissement donné si non null
     *
     * @return string
     */
    static function getTagNRA($group_id = null)
    {
        return self::getTagNDA($group_id, "tag_dossier_rang");
    }

    /**
     * Charge le NDA du séjour pour l'établissement courant
     *
     * @param int $group_id Permet de charger le NDA pour un établissement donné si non null
     *
     * @return void|string
     */
    function loadNDA($group_id = null)
    {
        // Objet inexistant
        if (!$this->_id) {
            return "-";
        }

        // Aucune configuration de numéro de dossier
        if (null == $tag_NDA = $this->getTagNDA($group_id)) {
            $this->_NDA_view = $this->_NDA = str_pad($this->_id, 6, "0", STR_PAD_LEFT);

            return null;
        }


        // Recuperation de la valeur de l'id400
        $idex = CIdSante400::getMatchFor($this, $tag_NDA);

        // Stockage de la valeur de l'id400
        $this->_ref_NDA  = $idex;
        $this->_NDA_view = $this->_NDA = $idex->id400;

        // Cas de l'utilisation du rang
        $this->loadNRA($group_id);

        return $this->_NDA;
    }

    /**
     * Mass load mechanism for forward references of an object collection
     *
     * @param self[] $sejours  Array of objects
     * @param string $group_id Tag
     *
     * @return self[] Loaded collection, null if unavailable, with ids as keys of guids for meta references
     */
    static function massLoadNDA($sejours, $group_id = null)
    {
        // Aucune configuration de numéro de dossier
        if (null == $tag_NDA = self::getTagNDA($group_id)) {
            foreach ($sejours as $_sejour) {
                $_sejour->_NDA_view = $_sejour->_NDA = str_pad($_sejour->_id, 6, "0", STR_PAD_LEFT);
            }

            return null;
        }

        // Récupération de la valeur des idex
        $ideces = CIdSante400::massGetMatchFor($sejours, $tag_NDA);

        // Association idex-séjours
        foreach ($ideces as $_idex) {
            if (array_key_exists($_idex->object_id, $sejours)) {
                $sejour = $sejours[$_idex->object_id];

                if ($sejour->_ref_NDA) {
                    continue;
                }

                $sejour->_ref_NDA  = $_idex;
                $sejour->_NDA_view = $sejour->_NDA = $_idex->id400;
            }
        }

        foreach ($sejours as $_sejour) {
            if ($_sejour->_ref_NDA) {
                continue;
            }

            $_sejour->_ref_NDA      = new CIdSante400();
            $_sejour->_ref_NDA->tag = $tag_NDA;
        }

        return null;
    }

    /**
     * Mass load mechanism for forward references of an object collection
     *
     * @param self[] $sejours  Array of objects
     * @param string $group_id Tag
     *
     * @return self[] Loaded collection, null if unavailable, with ids as keys of guids for meta references
     */
    static function massLoadNRA($sejours, $group_id = null)
    {
        // Utilise t-on le rang pour le dossier
        if (!CAppUI::conf("dPplanningOp CSejour use_dossier_rang")) {
            return null;
        }

        // Aucune configuration du numero de rang
        if (null == $tag_NRA = self::getTagNRA($group_id)) {
            return null;
        }

        // Récupération de la valeur des idex
        $ideces = CIdSante400::massGetMatchFor($sejours, $tag_NRA);

        /** @var CPatient[] $patients */
        $patients = CMbObject::massLoadFwdRef($sejours, "patient_id");
        CPatient::massLoadIPP($patients, $group_id);

        // Association idex-séjours
        foreach ($ideces as $_idex) {
            $sejour  = $sejours[$_idex->object_id];
            $patient = $patients[$sejour->patient_id];

            if ($sejour->_ref_NRA) {
                continue;
            }

            $sejour->_ref_NRA = $_idex;

            $NRA = $_idex->_id ? $_idex->id400 : "-";

            $sejour->_NDA_view = $patient->_IPP . "/" . $NRA;
        }

        foreach ($sejours as $_sejour) {
            if ($_sejour->_ref_NRA) {
                continue;
            }

            $_sejour->_ref_NRA = new CIdSante400();
        }

        return null;
    }

    /**
     * Charge le Numéro de rang du séjour pour l'établissement courant
     *
     * @param int $group_id Permet de charger le NRA pour un établissement donné si non null
     *
     * @return void|string
     */
    function loadNRA($group_id = null)
    {
        // Utilise t-on le rang pour le dossier
        if (!CAppUI::conf("dPplanningOp CSejour use_dossier_rang")) {
            return null;
        }

        // Objet inexistant
        if (!$this->_id) {
            return "-";
        }

        // Aucune configuration du numero de rang
        if (null == $tag_NRA = $this->getTagNRA($group_id)) {
            return null;
        }

        // Recuperation de la valeur de l'id400
        $idex = CIdSante400::getMatchFor($this, $tag_NRA);

        // Stockage de la valeur de l'id400
        $this->_ref_NRA = $idex;
        $NRA            = $idex->_id ? $idex->id400 : "-";

        // Récupération de l'IPP du patient
        $this->loadRefPatient();
        $this->_ref_patient->loadIPP();

        $this->_NDA_view = $this->_ref_patient->_IPP . "/" . $NRA;

        return null;
    }

    /**
     * Charge le Numéro de pré-admission du séjour pour l'établissement courant
     *
     * @param int $group_id Permet de charger le NPA pour un établissement donné si non null
     *
     * @return void|string
     */
    function loadNPA($group_id = null)
    {
        // Objet inexistant
        if (!$this->_id) {
            return "-";
        }

        // Aucune configuration de numéro de dossier
        if (null == $tag_NPA = $this->getTagNDA($group_id, "tag_dossier_pa")) {
            $this->_NPA = str_pad($this->_id, 6, "0", STR_PAD_LEFT);

            return null;
        }

        // Recuperation de la valeur de l'id400
        $idex = CIdSante400::getMatchFor($this, $tag_NPA);

        // Stockage de la valeur de l'id400
        $this->_ref_NPA = $idex;
        $this->_NPA     = $idex->id400;

        return null;
    }

    /**
     * Mass load mechanism for forward references of an object collection
     *
     * @param self[] $sejours  Array of objects
     * @param string $group_id Tag
     *
     * @return self[] Loaded collection, null if unavailable, with ids as keys of guids for meta references
     */
    static function massLoadNPA($sejours, $group_id = null)
    {
        // Aucune configuration de numéro de dossier
        if (null == $tag_NDA = self::getTagNDA($group_id, "tag_dossier_pa")) {
            foreach ($sejours as $_sejour) {
                $_sejour->_NPA = str_pad($_sejour->_id, 6, "0", STR_PAD_LEFT);
            }

            return null;
        }

        foreach ($sejours as $_sejour) {
            $_sejour->_ref_NPA = new CIdSante400();
        }

        // Récupération de la valeur des idex
        $ideces = CIdSante400::massGetMatchFor($sejours, $tag_NDA);

        // Association idex-séjours
        foreach ($ideces as $_idex) {
            $sejour = $sejours[$_idex->object_id];

            if ($sejour->_ref_NPA) {
                continue;
            }

            $sejour->_ref_NPA = $_idex;
            $sejour->_NPA     = $_idex->id400;
        }

        return null;
    }

    /**
     * Charge le séjour depuis son NDA
     *
     * @param string $nda      NDA du séjour
     * @param int    $group_id Identifiant de l'établissement
     *
     * @return void
     */
    function loadFromNDA($nda, $group_id = null)
    {
        // Aucune configuration de numéro de dossier
        if (null == $tag_NDA = $this->getTagNDA($group_id)) {
            return;
        }

        $idDossier               = new CIdSante400();
        $idDossier->id400        = $nda;
        $idDossier->tag          = $tag_NDA;
        $idDossier->object_class = $this->_class;
        $idDossier->loadMatchingObject();

        if ($idDossier->_id) {
            $this->load($idDossier->object_id);
            $this->_NDA = $idDossier->id400;
        }
    }

    /**
     * Charge une collection de séjours à partir d'un NDA
     *
     * @param string $nda NDA du séjour
     *
     * @return self[]
     */
    function loadListFromNDA($nda)
    {
        // Aucune configuration de numéro de dossier
        if ((null == $tag_NDA = $this->getTagNDA()) || !$nda) {
            return [];
        }

        $idDossier  = new CIdSante400();
        $where      = [
            "id400"        => "= '$nda'",
            "tag"          => "= '$tag_NDA'",
            "object_class" => "= '$this->_class'",
        ];
        $sejour_ids = $idDossier->loadColumn("object_id", $where);

        return $this->loadList(["sejour_id" => CSQLDataSource::prepareIn($sejour_ids)], "entree ASC");
    }

    /**
     * Passage en trash du NDA
     *
     * @return bool
     */
    function trashNDA()
    {
        if (!$this->_ref_NDA) {
            $this->loadNDA($this->group_id);
        }

        if (!$this->_ref_NDA || !$this->_ref_NDA->_id) {
            return;
        }

        $NDA      = $this->_ref_NDA;
        $NDA->tag = $this->getTagNTA($this->group_id);
        $NDA->store();
    }

    /**
     * @see parent::getExecutantId()
     */
    public function getExecutantId(string $code_activite = null): int
    {
        $user = CMediusers::get();
        if (!(CAppUI::pref("user_executant") && $user->isProfessionnelDeSante())) {
            $user = $this->loadRefPraticien();
        }

        if ($user->loadRefRemplacant($this->_acte_execution)) {
            $user = $user->_ref_remplacant;
        }

        return $user->_id;
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        if (!$this->_ref_praticien) {
            $this->loadRefPraticien();
        }
        if (!$this->_ref_group) {
            $this->loadRefEtablissement();
        }

        return (
            $this->_ref_group->getPerm($permType) && $this->_ref_praticien->getPerm($permType) && parent::getPerm($permType)
        );
    }

    /**
     * Charge l'affectation courante
     *
     * @param string $dateTime Permet de spécifier un horaire de références, maintenant si null
     *
     * @return CAffectation
     * @todo A dédoublonner avec loadRefCurrAffectation
     */
    function getCurrAffectation($dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = CMbDT::dateTime();
        }

        $ds = $this->_spec->ds;

        $where              = [];
        $where["sejour_id"] = $ds->prepare("= %", $this->sejour_id);

        if (CMbDT::time(null, $dateTime) == "00:00:00") {
            $where["entree"] = $ds->prepare("<= %", CMbDT::date(null, $dateTime) . " 23:59:59");
            $where["sortie"] = $ds->prepare(">= %", CMbDT::date(null, $dateTime) . " 00:00:01");
        } else {
            $where["entree"] = $ds->prepare("<= %", $dateTime);
            $where["sortie"] = $ds->prepare(">= %", $dateTime);
        }

        //Cas où il y a deux affectations trouvées, on prend la dernière en date
        $order = "entree DESC";

        $curr_affectation = new CAffectation();
        $curr_affectation->loadObject($where, $order);

        return $curr_affectation;
    }

    /**
     * Chargements des affectations
     *
     * @param string $order order
     *
     * @return CAffectation[]
     */
    function loadRefsAffectations($order = "sortie DESC", array $where = [])
    {
        $affectations = $this->loadBackRefs("affectations", $order, null, null, null, null, "", $where);

        if (count($affectations) > 0) {
            $this->_ref_first_affectation = end($affectations);
            $this->_ref_last_affectation  = reset($affectations);
        } else {
            $this->_ref_first_affectation = new CAffectation();
            $this->_ref_last_affectation  = new CAffectation();
        }

        return $this->_ref_affectations = $affectations;
    }

    /**
     * Charge les mouvements du séjour
     *
     * @param array $where where
     *
     * @return CMovement[]
     */
    function loadRefsMovements($where = [])
    {
        $movements = $this->loadBackRefs("movements", null, null, null, null, null, "", $where);

        if (count($movements) > 0) {
            $this->_ref_first_movement = reset($movements);
            $this->_ref_last_movement  = end($movements);
        } else {
            $this->_ref_first_movement = new CMovement();
            $this->_ref_last_movement  = new CMovement();
        }

        return $this->_ref_movements = $movements;
    }

    /**
     * Charge la première affectation
     *
     * @return CAffectation
     */
    function loadRefFirstAffectation()
    {
        $this->loadRefsAffectations();

        return $this->_ref_first_affectation;
    }

    /**
     * Force la création d'une affectation en fonction de la tolérance(?)
     *
     * @param CAffectation $affectation Affectation concernée
     * @param bool         $no_synchro  No synchro
     *
     * @return CAffectation|null|string|void
     * @todo A détailler
     */
    function forceAffectation(CAffectation $affectation, $no_synchro = false)
    {
        $datetime   = $affectation->entree;
        $lit_id     = $affectation->lit_id;
        $service_id = $affectation->service_id;
        $tolerance  = CAppUI::gconf("dPhospi CAffectation create_affectation_tolerance");

        $splitting          = new CAffectation();
        $where["sejour_id"] = "=  '$this->_id'";
        $where["entree"]    = "<= '$datetime'";
        $where["sortie"]    = ">= '$datetime'";
        $splitting->loadObject($where);

        $create = new CAffectation();

        // On retrouve une affectation a spliter
        if ($splitting->_id) {
            //on ne splite pas et on ne créé pas d'affectation si la tolérance n'est pas atteinte
            if (CMbDT::addDateTime("00:$tolerance:00", $splitting->entree) <= $affectation->entree || $affectation->_mutation_urg) {
                // Affecte la sortie de l'affectation a créer avec l'ancienne date de sortie
                $create->sortie = $splitting->sortie;
                $create->entree = $datetime;

                // On passe à effectuer la split
                $splitting->effectue        = 1;
                $splitting->sortie          = $datetime;
                $splitting->_no_synchro     = $no_synchro;
                $splitting->_no_synchro_eai = $no_synchro;
                $splitting->_mutation_urg   = $affectation->_mutation_urg;
                if ($msg = $splitting->store()) {
                    return $msg;
                }
            } else {
                $create->affectation_id = $splitting->affectation_id;
                $create->sortie         = $splitting->sortie;
            }
        } // On créé une première affectation
        else {
            $create->entree = $datetime;
            $create->sortie = $this->sortie;
        }

        // Créé la nouvelle affectation
        $create->sejour_id       = $this->_id;
        $create->lit_id          = $lit_id;
        $create->service_id      = $service_id;
        $create->uf_medicale_id  = $affectation->uf_medicale_id;
        $create->_mutation_urg   = $affectation->_mutation_urg;
        $create->_no_synchro     = $no_synchro;
        $create->_no_synchro_eai = $no_synchro;
        $create->uhcd            = $this->UHCD;
        if ($msg = $create->store()) {
            return $msg;
        }

        return $create;
    }

    /**
     * Chargement des opérations
     *
     * @param array  $where where
     * @param string $order order of list
     *
     * @return COperation[]
     */
    function loadRefsOperations($where = [], $order = "date ASC", string $backname = null)
    {
        $this->_ref_operations = $this->loadBackRefs("operations", $order, null, null, null, null, $backname, $where);

        // Motif complet
        if (!$this->libelle) {
            $this->_motif_complet = "";
            if ($this->recuse == -1) {
                $this->_motif_complet .= "[Att] ";
            }
            $motif = [];
            foreach ($this->_ref_operations as $_op) {
                /** @var COperation $_op */
                if ($_op->libelle) {
                    $motif[] = $_op->libelle;
                } else {
                    $motif[] = implode("; ", $_op->_codes_ccam);
                }
            }
            $this->_motif_complet .= implode("; ", $motif);
        }

        // Agrégats des codes CCAM des opérations
        $this->_codes_ccam_operations = CMbArray::pluck($this->_ref_operations, "codes_ccam");
        CMbArray::removeValue("", $this->_codes_ccam_operations);
        $this->_codes_ccam_operations = implode("|", $this->_codes_ccam_operations);

        if (count($this->_ref_operations) > 0) {
            $this->_ref_last_operation = reset($this->_ref_operations);
        } else {
            $this->_ref_last_operation = new COperation();
        }

        return $this->_ref_operations;
    }

    /**
     * Charge la première opération d'un séjour
     *
     * @return COperation
     */
    function loadRefFirstOperation()
    {
        $operation            = new COperation;
        $operation->sejour_id = $this->_id;
        $operation->loadMatchingObject("date ASC");

        return $this->_ref_first_operation = $operation;
    }

    /**
     * Charge la dernière opération d'un séjour
     *
     * @param Bool $notCancel séjour non annulé
     *
     * @return COperation
     */
    function loadRefLastOperation($notCancel = false)
    {
        $operation            = new COperation;
        $operation->sejour_id = $this->_id;
        if ($notCancel) {
            $operation->annulee = "0";
        }
        $operation->loadMatchingObject("date DESC");

        return $this->_ref_last_operation = $operation;
    }

    /**
     * Charge la première internvention du jour
     *
     * @param string $date Datetime de référence
     *
     * @return COperation
     */
    function loadRefCurrOperation($date)
    {
        if (!$this->_id) {
            return $this->_ref_curr_operation = new COperation();
        }

        $operation            = new COperation;
        $operation->sejour_id = $this->_id;
        $operation->date      = CMbDT::date($date);
        $operation->loadMatchingObject();

        return $this->_ref_curr_operation = $operation;
    }

    /**
     * Charge toutes les interventions du jour
     *
     * @param string $date Datetime de référence
     *
     * @return COperation[]
     */
    function loadRefCurrOperations($date)
    {
        if (!$this->_id) {
            return $this->_ref_curr_operations = [];
        }

        $operation            = new COperation;
        $operation->sejour_id = $this->_id;
        $operation->date      = CMbDT::date($date);

        return $this->_ref_curr_operations = $operation->loadMatchingList();
    }

    /**
     * Chargement du jour operatoire
     *
     * @param string $date Date
     *
     * @return void
     */
    function loadJourOp($date = null)
    {
        if (!$date) {
            $date = CMbDT::date();
        }

        $this->loadRefsOperations();
        foreach ($this->_ref_operations as $_operation) {
            if ($_operation->annulee) {
                continue;
            }
            $_operation->loadRefPlageOp();
            $this->_jour_op[$_operation->_id]["operation_guid"]  = $_operation->_guid;
            $this->_jour_op[$_operation->_id]["jour_op"]         = CMbDT::daysRelative(CMbDT::date($_operation->_datetime), $date);
            $this->_jour_op[$_operation->_id]["heure_operation"] = $_operation->time_operation;
            $this->_jour_op[$_operation->_id]["rques"]           = $_operation->rques ?: null;
            $this->_jour_op[$_operation->_id]["operation"]       = $_operation;
        }
    }

    /**
     * @see parent::loadRefsBack()
     */
    function loadRefsBack()
    {
        $this->loadRefsFiles();
        $this->loadRefsAffectations();
        $this->loadRefsOperations();
        $this->loadRefsActesCCAM();
    }

    /**
     * Charge l'observation d'entrée du séjour
     *
     * @return CConsultation
     */
    function loadRefObsEntree()
    {
        $consult = new CConsultation();
        if ($this->_id) {
            $consult->sejour_id = $this->_id;
            $consult->type      = "entree";
            $consult->annule    = 0;
            $consult->loadMatchingObject();
        }

        return $this->_ref_obs_entree = $consult;
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template)
    {
        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $this->loadRefsOperations();

        // Admission
        $admission_section = CAppUI::tr('Admission');
        $template->addLongDateProperty("$admission_section - " . CAppUI::tr('CSejour-Long date'), $this->entree_prevue);
        $template->addDateProperty("$admission_section - " . CAppUI::tr('common-Date'), $this->entree_prevue);
        $template->addTimeProperty("$admission_section - " . CAppUI::tr('common-Hour'), $this->entree_prevue);
        $template->addProperty("$admission_section - " . CAppUI::tr('common-Type'), $this->getFormattedValue("type"));

        // Hospitalisation
        $hospi_section = CAppUI::tr('CSejour-msg-hospi');
        $template->addProperty("$hospi_section - " . CAppUI::tr('common-Duration'), $this->_duree_prevue);
        $template->addDateProperty("$hospi_section - " . CAppUI::tr('CSejour-Release date'), $this->sortie_prevue);
        $template->addProperty("$hospi_section - " . CAppUI::tr('CSejour-Long release date'), $this->getFormattedValue("sortie_prevue"));

        // Séjour
        $sejour_section = CAppUI::tr('CSejour-Stay');
        $this->loadNDA();
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-_NDA'), $this->_NDA);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Concerned by ALD'), $this->getFormattedValue("ald"));
        $template->addBarcode("$sejour_section - " . CAppUI::tr('CSejour-ID barcode'), $this->_id);
        $template->addBarcode("$sejour_section - " . CAppUI::tr('CSejour-NDOS barcode'), $this->_NDA);

        $template->addDateProperty("$sejour_section - " . CAppUI::tr('CSejour-Date entered'), $this->entree);
        $template->addLongDateProperty("$sejour_section - " . CAppUI::tr('CSejour-Date entered (long)'), $this->entree);
        $template->addTimeProperty("$sejour_section - " . CAppUI::tr('CSejour-Time of entry'), $this->entree);
        $template->addDateProperty("$sejour_section - " . CAppUI::tr('CSejour-Release date'), $this->sortie);
        $template->addLongDateProperty("$sejour_section - " . CAppUI::tr('CSejour-Release date (long)'), $this->sortie);
        $template->addTimeProperty("$sejour_section - " . CAppUI::tr('CSejour-Time out'), $this->sortie);

        $template->addDateProperty("$sejour_section - " . CAppUI::tr('CSejour-Actual entry date'), $this->entree_reelle);
        $template->addTimeProperty("$sejour_section - " . CAppUI::tr('CSejour-Actual entry time'), $this->entree_reelle);
        $template->addDateProperty("$sejour_section - " . CAppUI::tr('CSejour-Actual release date'), $this->sortie_reelle);
        $template->addTimeProperty("$sejour_section - " . CAppUI::tr('CSejour-Actual exit time'), $this->sortie_reelle);

        $template->addProperty(
            "$sejour_section - " . CAppUI::tr('CModeEntreeSejour'),
            $this->getFormattedValue($this->mode_entree_id ? "mode_entree_id" : "mode_entree")
        );
        $template->addProperty(
            "$sejour_section - " . CAppUI::tr('CSejour-mode_sortie'),
            $this->getFormattedValue($this->mode_sortie_id ? "mode_sortie_id" : "mode_sortie")
        );
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Exit service'), $this->getFormattedValue("service_sortie_id"));
        $template->addProperty(
            "$sejour_section - " . CAppUI::tr('CSejour-Establishment of exit'),
            $this->getFormattedValue("etablissement_sortie_id")
        );
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Release Comment|pl'), $this->getFormattedValue("commentaires_sortie"));

        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Label'), $this->getFormattedValue("libelle"));
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-transport'), $this->getFormattedValue("transport"));

        if (CAppUI::gconf("dPplanningOp CSejour use_charge_price_indicator") != "no") {
            //mode de traitement
            $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-charge_id'), $this->loadRefChargePriceIndicator()->_view);
        }

        //service
        $template->addProperty("$sejour_section - " . CAppUI::tr('CService'), $this->loadRefService()->_view);

        //unite de soins
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-uf_soins_id'), $this->loadRefUFSoins()->_view);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Medical unit'), $this->loadRefUFMedicale()->_view);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Accommodation unit'), $this->loadRefUFHebergement()->_view);

        $code_tr = CAppUI::tr('CUniteFonctionnelle-code');
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-uf_soins_id') . " $code_tr", $this->loadRefUFSoins()->code);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Medical unit') . " $code_tr", $this->loadRefUFMedicale()->code);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Accommodation unit') . " $code_tr", $this->loadRefUFHebergement()->code);

        /** @var CExamIgs $last_exam_igs */
        $last_exam_igs = $this->loadLastExamIGS();
        $template->addProperty("$sejour_section - " . CAppUI::tr('CExamIgs'), $last_exam_igs->_id ? $last_exam_igs->scoreIGS : "");
        $template->addProperty(
            "$sejour_section - " . CAppUI::tr('CExamIgs-simplified_igs-court'),
            $last_exam_igs->_id ? $last_exam_igs->simplified_igs : ""
        );

        /* Score de Chung */
        $last_chung_score = $this->loadLastChungScore();
        $date_score       = $last_chung_score->datetime ? $last_chung_score->getFormattedValue("datetime") : "";
        $template->addProperty("{$sejour_section} - " . CAppUI::tr('CChungScore'), $last_chung_score->total ? $last_chung_score->total : '');
        $template->addProperty("{$sejour_section} - " . CAppUI::tr("CChungScore") . " (" . CAppUI::tr("common-date") . ")", $date_score);

        /* Score de GIR (calculé à partir du formulaire) */
        $last_exam_gir = $this->loadLastExamGir();
        $output        = $last_exam_gir->score_gir ? CAppUI::tr('CExamGir') . " : " . $last_exam_gir->score_gir : '';
        $template->addProperty("{$sejour_section} - " . CAppUI::tr('CExamGir'), $output);
        $date_gir = $last_exam_gir->date ? CMbDT::format($last_exam_gir->date, "%d/%m/%Y") : "";
        $template->addProperty(
            "{$sejour_section} - " . CAppUI::tr('CExamGir') . " (" . CAppUI::tr("common-date") . ")",
            $date_gir . " - " . $output
        );

        // Détail du score de GIR en tableau
        $content = null;
        if ($last_exam_gir->score_gir) {
            $last_exam_gir->computeAllCodes();
            $smarty = new CSmartyDP('modules/dPcabinet');
            $smarty->assign("exam_gir", $last_exam_gir);
            $content = $smarty->fetch('exam_gir_readonly.tpl');

            $content = preg_replace('`([\\n\\r])`', '', $content);
            $content = preg_replace('/<br>/', '<br />', $content);
        }

        $template->addProperty("{$sejour_section} - " . CAppUI::tr('CExamGir-GIR score table'), $content, null, false);

        // Consultation anesthésique
        $consult_anesth = $this->loadRefsConsultAnesth();
        $consult        = $consult_anesth->loadRefConsultation();
        $consult->loadRefPlageConsult();

        $cpa_subItem  = CAppUI::tr('CConsultAnesth-Anesthesia consultation');
        $cpa_datetime = $consult->_id ? $consult->_datetime : "";
        $template->addDateProperty("$sejour_section - $cpa_subItem - " . CAppUI::tr('common-Date'), $cpa_datetime);
        $template->addLongDateProperty("$sejour_section - $cpa_subItem - " . CAppUI::tr('common-Date (long)'), $cpa_datetime);
        $template->addLongDateProperty("$sejour_section - $cpa_subItem - " . CAppUI::tr('common-Date (long, lowercase)'), $cpa_datetime);
        $template->addTimeProperty("$sejour_section - $cpa_subItem - " . CAppUI::tr('common-Hour'), $cpa_datetime);

        $this->loadRefsFiles();
        $list = CMbArray::pluck($this->_ref_files, "file_name");
        $template->addListProperty("$sejour_section - " . CAppUI::tr('CFile-List of file|pl'), $list);

        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-provenance'), $this->getFormattedValue("provenance"));
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-destination'), $this->getFormattedValue("destination"));

        $this->loadRefPraticien();
        $template->addProperty("$hospi_section - " . CAppUI::tr('common-Practitioner'), "Dr " . $this->_ref_praticien->_view);
        $template->addProperty(
            "$hospi_section - " . CAppUI::tr('common-Practitioner') . " - " . CAppUI::tr('CMediusers-rpps'),
            $this->_ref_praticien->rpps
        );

        // Affectations
        $this->loadRefsAffectations();
        $this->loadRefCurrAffectation();
        $curr_affectation = $this->_ref_curr_affectation;

        $curr_affectation_view = null;
        if ($curr_affectation && $curr_affectation->_id) {
            $curr_affectation_view = "$curr_affectation->_view du " . CMbDT::format(
                    $curr_affectation->entree,
                    CAppUI::conf("datetime")
                ) . " au " . CMbDT::format($curr_affectation->sortie, CAppUI::conf("datetime"));
        }
        $template->addProperty("$hospi_section - " . CAppUI::tr('CAffectation.current'), $curr_affectation_view);

        $this->_ref_last_affectation->loadView();
        $last_affectation = $this->_ref_last_affectation;
        $template->addProperty("$hospi_section - " . CAppUI::tr('CAffectation-Last assignment'), $last_affectation->_view);
        $template->addProperty(
            "$hospi_section - " . CAppUI::tr('CAffectation-Last assignment (Service)'),
            $last_affectation->loadRefService()->_view
        );
        $template->addProperty(
            "$hospi_section - " . CAppUI::tr('CAffectation-Last assignment (Sector)'),
            $last_affectation->_ref_service->loadRefSecteur()->nom
        );

        $affectations = [];
        if (count($this->_ref_affectations)) {
            foreach ($this->_ref_affectations as $_aff) {
                $affectations[] = "$_aff->_view du " . CMbDT::format($_aff->entree, CAppUI::conf("datetime")) . " au " . CMbDT::format(
                        $_aff->sortie,
                        CAppUI::conf(
                            "datetime"
                        )
                    );
            }
        }
        $template->addListProperty("$sejour_section - " . CAppUI::tr('CLit-back-affectations'), $affectations);

        // Diagnostics
        $this->loadExtDiagnostics();
        $diag = $this->DP ? "$this->DP: {$this->_ext_diagnostic_principal->libelle}" : null;
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Main Diagnosis'), $diag);
        $diag = $this->DR ? "$this->DR: {$this->_ext_diagnostic_relie->libelle}" : null;
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Related Diagnosis'), $diag);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-rques-court'), $this->rques);
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-convalescence'), $this->convalescence);

        // Chargement du suivi medical (transmissions, observations, prescriptions)
        $this->loadSuiviMedical();

        // Transmissions
        $transmissions = [];
        if (isset($this->_back["transmissions"])) {
            foreach ($this->_back["transmissions"] as $_trans) {
                if ($_trans->cancellation_date) {
                    continue;
                }
                $datetime                                      = CMbDT::format($_trans->date, CAppUI::conf('datetime'));
                $transmissions["$_trans->date $_trans->_guid"] = "$_trans->text, le $datetime, {$_trans->_ref_user->_view}";
            }
        }

        $template->addListProperty("$sejour_section - " . CAppUI::tr('CSejour-back-transmissions'), $transmissions);

        $this->loadRefsTransmissions(false, true, false);

        $transmissions_hautes = [];
        foreach ($this->_ref_transmissions as $_trans) {
            $_trans->loadRefUser();
            $datetime                                             = CMbDT::format($_trans->date, CAppUI::conf('datetime'));
            $transmissions_hautes["$_trans->date $_trans->_guid"] = "$_trans->text, le $datetime, {$_trans->_ref_user->_view}";
        }

        $trans_subItem = CAppUI::tr('CSejour-back-transmissions');
        $template->addListProperty(
            "$sejour_section - $trans_subItem - " . CAppUI::tr('CTransmissionMedicale-High importance'),
            $transmissions_hautes
        );

        $this->loadRefsTransmissions(true, false, true);
        $transmissions_macro = [];

        foreach ($this->_ref_transmissions as $_trans) {
            $_trans->loadRefUser();
            $datetime                                            = CMbDT::format($_trans->date, CAppUI::conf('datetime'));
            $transmissions_macro["$_trans->date $_trans->_guid"] = "$_trans->text, le $datetime, {$_trans->_ref_user->_view}";
        }

        $template->addListProperty("$sejour_section - $trans_subItem - " . CAppUI::tr('CTransmissionMedicale-macrocible'), $transmissions_macro);

        $this->loadRefsTransmissions();
        $transmission_reeducateur = [];
        $transmission_assistante_sociale = [];
        foreach ($this->_ref_transmissions as $_trans) {
            $_trans->loadRefUser();
            $datetime = CMbDT::format($_trans->date, CAppUI::conf('datetime'));
            if ($_trans->_ref_user->isKine()) {
                $transmission_reeducateur["$_trans->date $_trans->_guid"] =
                    "$_trans->text, le $datetime, {$_trans->_ref_user->_view}";
            }
            if ($_trans->_ref_user->isAssistanteSociale()) {
                $transmission_assistante_sociale["$_trans->date $_trans->_guid"] =
                    "$_trans->text, le $datetime, {$_trans->_ref_user->_view}";
            }
        }
        $template->addListProperty(
            "$sejour_section - $trans_subItem - " .
            CAppUI::tr('CTransmissionMedicale-reeducateur'),
            $transmission_reeducateur
        );
        $template->addListProperty(
            "$sejour_section - $trans_subItem - " .
            CAppUI::tr('CTransmissionMedicale-assistante-sociale'),
            $transmission_assistante_sociale
        );
        // Observations
        $observations = [];
        $all_obs      = [];
        if (isset($this->_back["observations"])) {
            $all_obs = $this->_back["observations"];
            foreach ($this->_back["observations"] as $_obs) {
                if ($_obs->cancellation_date) {
                    continue;
                }
                $datetime                                 = CMbDT::format($_obs->date, CAppUI::conf('datetime'));
                $observations["$_obs->date $_obs->_guid"] = "$_obs->text, le $datetime, {$_obs->_ref_user->_view}";
            }
            ksort($observations);
        }

        self::filllimitedTemplateObs(
            isset($this->_back["observations"]) ? $this->_back["observations"] : [],
            $template,
            "$sejour_section - " . CAppUI::tr('CSejour-back-observations')
        );

        // Observation par étiquette
        $observation_tag = new CObservationMedicale();
        foreach ($observation_tag->_specs["etiquette"]->_list as $_etiquette) {
            self::filllimitedTemplateObs(
                $this->loadRefsObservations(null, null, $_etiquette),
                $template,
                "$sejour_section - " . CAppUI::tr("CSejour-back-observations") . " - " . CAppUI::tr("CObservationMedicale.etiquette." . $_etiquette)
            );
        }

        // Observation par type
        $observation_tag = new CObservationMedicale();
        foreach ($observation_tag->_specs["type"]->_list as $_type) {
            self::filllimitedTemplateObs(
                $this->loadRefsObservations(null, $_type),
                $template,
                "$sejour_section - " . CAppUI::tr("CSejour-back-observations") . " - " . CAppUI::tr("CObservationMedicale.type." . $_type)
            );
        }

        // Observation par type et étiquette
        $observation_tag = new CObservationMedicale();
        foreach ($observation_tag->_specs["type"]->_list as $_type) {
            foreach ($observation_tag->_specs["etiquette"]->_list as $_etiquette) {
                self::filllimitedTemplateObs(
                    $this->loadRefsObservations(null, $_type, $_etiquette),
                    $template,
                    "$sejour_section - " . CAppUI::tr("CSejour-back-observations") . " - " . CAppUI::tr(
                        "CObservationMedicale.type." . $_type
                    ) . " - " . CAppUI::tr("CObservationMedicale.etiquette." . $_etiquette)
                );
            }
        }

        // Observations de synthèse
        self::filllimitedTemplateObs(
            $this->loadRefsObservations(null, "synthese"),
            $template,
            "$sejour_section - " . CAppUI::tr('CObservationMedicale-Synthesis observation|pl')
        );

        // Observations de communication
        self::filllimitedTemplateObs(
            $this->loadRefsObservations(null, "communication"),
            $template,
            "$sejour_section - " . CAppUI::tr('CObservationMedicale-Communication observation|pl')
        );

        // Entrée et sortie en USC
        $entree_usc = $entree_usc_formatted = null;
        $sortie_usc = $sortie_usc_formatted = null;

        foreach ($this->_ref_affectations as $_aff) {
            if ($_aff->loadRefService()->usc) {
                if (!$entree_usc) {
                    $entree_usc           = $_aff->entree;
                    $entree_usc_formatted = $_aff->getFormattedValue("entree");
                }
                $sortie_usc           = $_aff->sortie;
                $sortie_usc_formatted = $_aff->getFormattedValue("sortie");
            }
        }

        $template->addProperty("$sejour_section - " . CAppUI::tr("CSejour-Entree USC"), $entree_usc_formatted);
        $template->addProperty("$sejour_section - " . CAppUI::tr("CSejour-Sortie USC"), $sortie_usc_formatted);

        // Observations en USC
        $obs_usc = [];

        if ($entree_usc) {
            foreach ($all_obs as $_obs) {
                if ($_obs->date >= $entree_usc && (!$sortie_usc || $_obs->date <= $sortie_usc)) {
                    $obs_usc[$_obs->_id] = $_obs;
                }
            }
        }

        self::filllimitedTemplateObs(
            $obs_usc,
            $template,
            "$sejour_section - " . CAppUI::tr('CObservationMedicale-USC observation|pl')
        );

        // Observation d'entrée
        /** @var CConsultation $obs_entree */
        $obs_entree = $this->loadRefObsEntree();

        $obs_entree_subItem = CAppUI::tr('CObservationMedicale-Observation entry');
        $template->addMarkdown("$sejour_section - $obs_entree_subItem - " . CAppUI::tr('CSejour-libelle'), $obs_entree->getFormattedValue("motif"));
        $template->addMarkdown(
            "$sejour_section - $obs_entree_subItem - " . CAppUI::tr('CConsultation-examen'),
            $obs_entree->getFormattedValue("examen")
        );
        $template->addMarkdown(
            "$sejour_section - $obs_entree_subItem - " . CAppUI::tr('CSejour-rques-court'),
            $obs_entree->getFormattedValue("rques")
        );
        $template->addMarkdown(
            "$sejour_section - $obs_entree_subItem - " . CAppUI::tr('CConsultation-traitement'),
            $obs_entree->getFormattedValue("traitement")
        );
        $template->addMarkdown(
            "$sejour_section - $obs_entree_subItem - " . CAppUI::tr('CConsultAnesth-histoire_maladie'),
            $obs_entree->getFormattedValue("histoire_maladie")
        );
        $template->addMarkdown(
            "$sejour_section - $obs_entree_subItem - " . CAppUI::tr('CConsultation-conclusion'),
            $obs_entree->getFormattedValue("conclusion")
        );

        // Prescriptions
        $lines = [];
        if (CModule::getActive('dPprescription')) {
            $prescription = $this->loadRefPrescriptionSejour();
            $prescription->loadRefsLinesAllComments();
            $prescription->loadRefsLinesElement();

            if (isset($prescription->_ref_prescription_lines_all_comments)) {
                foreach ($prescription->_ref_prescription_lines_all_comments as $_comment) {
                    $datetime                                                         = CMbDT::format(
                        "$_comment->debut $_comment->time_debut",
                        CAppUI::conf('datetime')
                    );
                    $lines["$_comment->debut $_comment->time_debut $_comment->_guid"] =
                        "$_comment->_view, $datetime, {$_comment->_ref_praticien->_view}";
                }
            }

            if (isset($prescription->_ref_prescription_lines_element)) {
                foreach ($prescription->_ref_prescription_lines_element as $_line_element) {
                    $datetime = CMbDT::format("$_line_element->debut $_line_element->time_debut", CAppUI::conf('datetime'));
                    $view     = "$_line_element->_view";
                    if ($_line_element->commentaire) {
                        $view .= " ($_line_element->commentaire)";
                    }
                    $view                                                                            .= ", $datetime, " . $_line_element->_ref_praticien->_view;
                    $lines["$_line_element->debut $_line_element->time_debut $_line_element->_guid"] = $view;
                }
            }
            krsort($lines);
            $template->addListProperty("$sejour_section - " . CAppUI::tr('CPrescription-Prescription light'), $lines);
        }

        // Suivi médical: transmissions, observations, prescriptions
        $suivi_medical = $transmissions + $observations + $lines;
        krsort($suivi_medical);
        $template->addListProperty("$sejour_section - " . CAppUI::tr('CSejour-Medical follow-up'), $suivi_medical);

        // Interventions
        $operations = [];
        foreach ($this->_ref_operations as $_operation) {
            $_operation->loadRefPlageOp(true);
            $datetime     = $_operation->getFormattedValue("_datetime");
            $chir         = $_operation->loadRefChir(true);
            $operations[] = "le $datetime, par $chir->_view" . ($_operation->libelle ? " : $_operation->libelle" : "");
        }

        $interv_subItem = CAppUI::tr('COperation');
        $template->addListProperty("$sejour_section - $interv_subItem - " . CAppUI::tr('CMbFieldSpec.list'), $operations);

        // Dernière intervention
        $this->_ref_last_operation->fillLimitedTemplate($template);

        // Consultations
        $consults_by_function = [];
        foreach ($this->loadRefsConsultations() as $_consult) {
            $_consult->loadRefsDossiersAnesth();
            $conclusion = null;
            if (count($_consult->_refs_dossiers_anesth)) {
                foreach ($_consult->_refs_dossiers_anesth as $_dossier_anesth) {
                    $conclusion = $_dossier_anesth->conclusion;
                }
            } else {
                $conclusion = $_consult->conclusion;
            }
            if ($conclusion) {
                $praticien = $_consult->loadRefPraticien();
                $function  = $praticien->loadRefFunction();
                if (!isset($consults_by_function[$function->_view])) {
                    $consults_by_function[$function->_view] = [];
                }
                $consults_by_function[$function->_view][] = $conclusion;
            }
        }
        $consults = [];
        foreach ($consults_by_function as $_function_view => $_consults) {
            $consults[] = $_function_view . " : ";
            foreach ($_consults as $_consult) {
                $consults[] = $_consult;
            }
        }

        $template->addListProperty("$sejour_section - " . CAppUI::tr('CConsult-Conclusion of the consultation|pl'), $consults);

        if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "expert") {
            $items_liaisons = $this->loadBackRefs("items_liaisons", "date");
            CStoredObject::massLoadFwdRef($items_liaisons, "item_souhait_id");
            CStoredObject::massLoadFwdRef($items_liaisons, "sous_item_id");

            $souhaits    = [];
            $ponctuelles = [];

            foreach ($items_liaisons as $_liaison) {
                $item_souhait = $_liaison->loadRefItem();
                if ($item_souhait->object_class == "CPrestationPonctuelle") {
                    $ponctuelles[] = $item_souhait->nom;
                    continue;
                }
                $sous_item = $_liaison->loadRefSousItem();
                $nom       = $item_souhait;
                if ($sous_item->_id) {
                    $nom = $sous_item->nom;
                }
                $souhaits[] = $nom;
            }
            $template->addListProperty("$sejour_section - " . CAppUI::tr('CPrestation-Desired service|pl'), $souhaits);
            $template->addListProperty("$sejour_section - " . CAppUI::tr('CPrestation-One-off service|pl'), $ponctuelles);
        }

        // Régime
        $regimes = [];

        if ($this->hormone_croissance) {
            $regimes[] = CAppUI::tr("CSejour-hormone_croissance");
        }

        if ($this->repas_sans_sel) {
            $regimes[] = CAppUI::tr("CSejour-repas_sans_sel");
        }

        if ($this->repas_sans_porc) {
            $regimes[] = CAppUI::tr("CSejour-repas_sans_porc");
        }

        if ($this->repas_diabete) {
            $regimes[] = CAppUI::tr("CSejour-repas_diabete");
        }

        if ($this->repas_sans_residu) {
            $regimes[] = CAppUI::tr("CSejour-repas_sans_residu");
        }

        if (!count($regimes)) {
            $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Regime'), CAppUI::tr("CSejour-no_diet_specified"));
        } else {
            $template->addListProperty("$sejour_section - " . CAppUI::tr('CSejour-Regime'), $regimes);
        }

        if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "expert") {
            $liaisons_j = $dates = $list_souhaits = $liaisons_by_id = [];

            self::getIntervallesPrestations($liaisons_j, $dates, $liaisons_by_id);
            $template->addProperty(
                "$sejour_section - " . CAppUI::tr('CSejour-Prestations'),
                $this->printPrestations($liaisons_by_id, $dates),
                '',
                false
            );
            foreach ($liaisons_j as $prestation_id => $_liaisons) {
                foreach ($_liaisons as $date => $_liaison) {
                    if (!$_liaison->item_souhait_id) {
                        continue;
                    }
                    $_item_souhait   = $_liaison->loadRefItem();
                    $_sous_item      = $_liaison->loadRefSousItem();
                    $dates_liaison   = $dates[$_liaison->_id];
                    $list_souhaits[] = "Du " . CMbDT::dateToLocale($dates_liaison["debut"]) . " au " . CMbDT::dateToLocale(
                            $dates_liaison["fin"]
                        ) . " : "
                        . ($_sous_item->_id ? $_sous_item->nom : $_item_souhait->nom);
                }
            }

            $template->addListProperty("$sejour_section - " . CAppUI::tr('CPrestation-Desired service|pl'), $list_souhaits);
        }

        if (CModule::getActive("forms")) {
            CExObject::addFormsToTemplate($template, $this, "Sejour");
        }

        if (CModule::getActive("dPfacturation") && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab")) {
            $this->updateFieldsFacture();
            $this->_ref_facture->fillLimitedTemplate($template);
            $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-_type_sejour'), $this->getFormattedValue("_type_sejour"));
            $template->addProperty(
                "$sejour_section - " . CAppUI::tr('CSejour-_rques_assurance_maladie'),
                $this->getFormattedValue("_rques_assurance_maladie")
            );
        }
        $this->loadNRA();
        $template->addProperty(
            "$sejour_section - " . CAppUI::tr('CSejour-Case number'),
            $this->_ref_NRA && $this->_ref_NRA->_id ? $this->_ref_NRA->id400 : "-"
        );

        // Grossesse
        if (CModule::getActive("maternite")) {
            $grossesse         = $this->loadRefGrossesse();
            $naissance         = new CNaissance();
            $enfant            = new CPatient();
            $constantes_enfant = new CConstantesMedicales();
            $sa_reste_jour     = null;

            if ($this->grossesse_id && count($grossesse->loadRefsNaissances())) {
                /** @var CNaissance $naissance */
                $naissance         = reset($grossesse->_ref_naissances);
                $enfant            = $naissance->loadRefSejourEnfant()->loadRefPatient();
                $constantes        = $enfant->loadRefLatestConstantes(null, ["poids"]);
                $constantes_enfant = reset($constantes);
            } else {
                $grossesse = $this->loadRefNaissance()->loadRefGrossesse();
            }

            if ($grossesse && $grossesse->_id) {
                $sa_reste_jour = $grossesse->_semaine_grossesse . " " . CAppUI::tr(
                        'CDepistageGrossesse-_sa'
                    ) . " + " . $grossesse->_reste_semaine_grossesse . " J";
            }

            $accouchement_subItem = CAppUI::tr('CDossierPerinat-accouchement');
            $template->addProperty(
                "$sejour_section - $accouchement_subItem - " . CAppUI::tr('CNaissance-_heure-desc'),
                $naissance->getFormattedValue("_heure")
            );
            $date_naiss       = $naissance->date_time;
            $date_naiss_word  = CMbDT::format($date_naiss, "%A") . " " . CMbString::toWords(CMbDT::format($date_naiss, "%d")) . " " . CMbDT::format(
                    $date_naiss,
                    "%B"
                ) . " " . CMbString::toWords(CMbDT::format($date_naiss, "%Y"));
            $heure_naiss_word = CMbString::toWords(CMbDT::format($date_naiss, "%H")) . " heures " . CMbString::toWords(
                    CMbDT::format($date_naiss, "%M")
                ) . " minutes";
            $template->addProperty("$sejour_section - $accouchement_subItem - " . CAppUI::tr('CNaissance-Date of birth (letter)'), $date_naiss_word);
            $template->addProperty("$sejour_section - $accouchement_subItem - " . CAppUI::tr('CNaissance-Birth time (letter)'), $heure_naiss_word);

            $template->addProperty(
                "$sejour_section - $accouchement_subItem - " . CAppUI::tr('CNaissance-Child sex'),
                $enfant->getFormattedValue("sexe")
            );
            $template->addProperty(
                "$sejour_section - $accouchement_subItem - " . CAppUI::tr('CExamenNouveauNe-Weight (kg)'),
                $constantes_enfant->poids . " kg"
            );
            $template->addProperty(
                "$sejour_section - $accouchement_subItem - " . CAppUI::tr('CExamenNouveauNe-Weight (g)'),
                $constantes_enfant->_poids_g . " g"
            );
            $template->addProperty("$sejour_section - $accouchement_subItem - " . CAppUI::tr('CPatient-First name of the child'), $enfant->prenom);
            $template->addProperty("$sejour_section - $accouchement_subItem - " . CAppUI::tr('CPatient-Name of the child'), $enfant->nom);
            $template->addProperty("$sejour_section - $accouchement_subItem - " . CAppUI::tr('CGrossesse-_semaine_grossesse'), $sa_reste_jour);

            $this->loadRefNaissance()->fillLiteLimitedTemplate($template, CAppUI::tr('CNaissance'));
        }

        if (CModule::getActive("ssr")) {
            $ssr_subItem = CAppUI::tr('CBilanSSR');
            $bilan_ssr   = $this->loadRefBilanSSR();
            $template->addProperty("$sejour_section - $ssr_subItem - " . CAppUI::tr('CBilanSSR-entree'), $bilan_ssr->getFormattedValue("entree"));
            $template->addProperty("$sejour_section - $ssr_subItem - " . CAppUI::tr('CBilanSSR-sortie'), $bilan_ssr->getFormattedValue("sortie"));
        }

        /* Constantes */
        CConstantesMedicales::fillLiteLimitedTemplate($this, $template);
        $constantes       = CConstantesMedicales::getFirstFor($this->_ref_patient, $this->sortie, null, $this, false, $this->entree);
        $first_constantes = reset($constantes);
        CConstantesMedicales::fillLiteLimitedTemplate2($first_constantes, $template, true);

        $constantes_last   = CConstantesMedicales::getLatestFor($this->_ref_patient, $this->sortie, null, $this, false, $this->entree);
        $latest_constantes = reset($constantes_last);
        CConstantesMedicales::fillLiteLimitedTemplate2($latest_constantes, $template, false);

        /* Eléments significatifs */

        // Séparateur pour les groupes de valeurs
        $default            = CAppUI::pref("listDefault");
        $separator          = CAppUI::pref("listInlineSeparator");
        $separators         = [
            "ulli"   => "",
            "br"     => "<br />",
            "inline" => " $separator ",
        ];
        $separator          = $separators[$default];
        $elts_significatifs = "$sejour_section - " . CAppUI::tr('common-Significant element|pl') . " - ";

        $elements        = [];
        $dossier_medical = $this->loadRefDossierMedical();

        // Antécédents significatifs
        $antecedents = [];
        $dossier_medical->loadRefsAntecedents(true);
        $dossier_medical->countAntecedents();

        if ($dossier_medical->_ref_antecedents_by_type && $dossier_medical->_count_antecedents) {
            foreach ($dossier_medical->_ref_antecedents_by_type as $type) {
                foreach ($type as $_ant) {
                    $atcd_type     = ($_ant->type) ? '<strong>' . CAppUI::tr("CAntecedent.type.$_ant->type") . '</strong> ' : '';
                    $atcd_appareil = ($_ant->appareil) ? '<strong>' . CAppUI::tr("CAntecedent.appareil.$_ant->appareil") . '</strong> ' : '';
                    $atcd_date     = ($_ant->date) ? "[" . $_ant->date . "]: " : '';
                    $atcd_rques    = ($_ant->rques) ? $_ant->rques : '';

                    $antecedents[] = $atcd_type . $atcd_appareil . $atcd_date . $atcd_rques;
                }
            }

            $elements[] = $atcds_significatifs = $template->makeList($antecedents, false, 1);
            $template->addProperty("$elts_significatifs" . CAppUI::tr("CAntecedent-significatif|pl"), $atcds_significatifs, null, false);
        }

        // Traitements significatifs
        $traitements = [];
        $dossier_medical->loadRefsTraitements(true);
        $dossier_medical->countTraitements();

        if ($dossier_medical->_ref_traitements && $dossier_medical->_count_traitements) {
            foreach ($dossier_medical->_ref_traitements as $curr_trmt) {
                if ($curr_trmt->fin) {
                    $trmt_date = CAppUI::tr("common-Since") . " " . $curr_trmt->debut . " " . CAppUI::tr(
                            "CPrescriptionLine-until-court"
                        ) . " " . $curr_trmt->fin;
                } elseif ($curr_trmt->debut) {
                    $trmt_date = CAppUI::tr("common-Since") . $curr_trmt->debut;
                } else {
                    $trmt_date = '';
                }

                $trmt_traitement = ($curr_trmt->traitement) ? nl2br($curr_trmt->traitement) : '';

                $traitements[] = $trmt_date . $trmt_traitement;
            }
            $elements[] = $ttts_significatifs = $template->makeList($traitements, false, 1);
            $template->addProperty("$elts_significatifs" . CAppUI::tr("CTraitement-significatif|pl"), $ttts_significatifs, null, false);
        }

        // Chargement de la prescription de sejour
        $traitements_perso = [];
        $count             = 0;
        $prescription      = $this->loadRefPrescriptionSejour();

        // Chargement des lignes de tp de la prescription
        if ($prescription && $prescription->_id && CPrescription::isMPMActive()) {
            $line_tp                       = new CPrescriptionLineMedicament();
            $line_tp->prescription_id      = $prescription->_id;
            $line_tp->traitement_personnel = 1;
            $lines_tp                      = $line_tp->loadMatchingList();

            if (count($lines_tp)) {
                foreach ($lines_tp as $_line_tp) {
                    $_line_tp->loadRefsPrises();

                    $tp_name = $_line_tp->_ucd_view;
                    $tp_com  = '<span>' . $_line_tp->commentaire . '</span>';

                    if ($total = count($_line_tp->_ref_prises)) {
                        $tp_com .= '<br />(';

                        foreach ($_line_tp->_ref_prises as $_prise) {
                            $count++;
                            $tp_com .= $_prise;
                            $tp_com .= ($count != $total) ? "," : "";
                        }
                        $tp_com .= ')';
                        $count  = 0;
                    }

                    $traitements_perso[] = $tp_name . "  " . $tp_com;
                }
                $elements[] = $tp_significatifs = $template->makeList($traitements_perso, false, 1);
                $template->addProperty("$elts_significatifs" . CAppUI::tr("CPrescriptionLineMedicament-tp|pl"), $tp_significatifs, null, false);
            }
        }

        // Diagnostics significatifs de l'intervention
        $diagnostics = [];

        if ($dossier_medical->_ext_codes_cim) {
            foreach ($dossier_medical->_ext_codes_cim as $_code) {
                $diagnostics[] = $_code->code . ': ' . $_code->libelle;
            }

            $elements[] = $diags_significatifs = $template->makeList($diagnostics, false, 1);
            $template->addProperty("$elts_significatifs" . CAppUI::tr("CDossierMedical-diag_significatif|pl"), $diags_significatifs, null, false);
        }

        $template->addProperty("$elts_significatifs" . CAppUI::tr("All"), implode($separator, $elements), null, false);

        /* autorisation de sortie */
        $this->loadRefConfirmeUser();

        $autorisation_subItem = CAppUI::tr('CSejour-Medical discharge authorization');
        $template->addProperty(
            "$sejour_section - $autorisation_subItem - " . CAppUI::tr('common-Practitioner name'),
            $this->_ref_confirme_user->_view
        );
        $template->addDateProperty("$sejour_section - $autorisation_subItem - " . CAppUI::tr('common-noun-Date'), $this->confirme);
        $template->addTimeProperty("$sejour_section - $autorisation_subItem - " . CAppUI::tr('common-Hour'), $this->confirme);
        $template->addProperty(
            "$sejour_section - $autorisation_subItem - " . CAppUI::tr('CSejour-mode_sortie'),
            $this->getFormattedValue("mode_sortie")
        );

        // Facteurs de risque (consultation d'anesthésie)
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Eligible stay for a pec ambulatory'), $this->getFormattedValue("pec_ambu"));
        $template->addProperty("$sejour_section - " . CAppUI::tr('CSejour-Notes on pec in ambulatory'), $this->getFormattedValue("rques_pec_ambu"));

        //Dossier d'addictologie
        if (CModule::getActive("addictologie")) {
            $dossier_addictologie = $this->loadRefDossierAddictologie();
            $dossier_addictologie->loadRefReferentUser();

            $addictologie_subItem = CAppUI::tr('CDossierAddictologie');
            $template->addProperty(
                "$sejour_section - $addictologie_subItem - " . CAppUI::tr('CDossierAddictologie-referent_user_id'),
                $dossier_addictologie->_ref_referent_user->_view
            );
            $template->addProperty(
                "$sejour_section - $addictologie_subItem - " . CAppUI::tr('CDossierAddictologie-convention'),
                $dossier_addictologie->getFormattedValue("convention")
            );
            $template->addProperty(
                "$sejour_section - $addictologie_subItem - " . CAppUI::tr('CDossierAddictologie-cas_particulier'),
                $dossier_addictologie->getFormattedValue("cas_particulier")
            );
            $template->addProperty(
                "$sejour_section - $addictologie_subItem - " . CAppUI::tr('CDossierAddictologie-suivi_social'),
                $dossier_addictologie->getFormattedValue("suivi_social")
            );
            $template->addProperty(
                "$sejour_section - $addictologie_subItem - " . CAppUI::tr('CDossierAddictologie-dispensation'),
                $dossier_addictologie->getFormattedValue("dispensation")
            );
        }

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }

    /**
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template)
    {
        // Chargement du fillTemplate du praticien
        $this->loadRefPraticien()->fillTemplate($template);

        // Ajout d'un fillTemplate du patient
        $this->loadRefPatient()->fillTemplate($template);

        // Ajout du fillTemplate de l'établissement du séjour
        $this->loadRefEtablissement()->fillLimitedTemplate($template, "Sejour");

        $this->fillLimitedTemplate($template);

        // Dossier médical
        $this->loadRefDossierMedical()->fillTemplate($template, "Sejour");

        // Prescription
        if (CModule::getActive('dPprescription')) {
            $prescriptions      = $this->loadRefsPrescriptions();
            $prescription       = isset($prescriptions["pre_admission"]) ? $prescriptions["pre_admission"] : new CPrescription();
            $prescription->type = "pre_admission";
            $prescription->fillLimitedTemplate($template);
            $prescription       = isset($prescriptions["sejour"]) ? $prescriptions["sejour"] : new CPrescription();
            $prescription->type = "sejour";
            $prescription->fillLimitedTemplate($template);
            $prescription       = isset($prescriptions["sortie"]) ? $prescriptions["sortie"] : new CPrescription();
            $prescription->type = "sortie";
            $prescription->fillLimitedTemplate($template);
        }

        // RPU
        $this->loadRefRPU();
        if ($this->_ref_rpu) {
            $this->_ref_rpu->fillLimitedTemplate($template);
        }

        if (CModule::getActive("maternite")) {
            $grossesse = $this->loadRefGrossesse();
            if ($grossesse->_id) {
                $grossesse->fillLimitedTemplate($template);
            } else {
                $this->loadRefNaissance()->fillLimitedTemplate($template);
            }
            /** @var CNaissance $_naissance */
            $naissances = $this->loadRefsNaissances();

            while (count($naissances) < 4) {
                $naissances[] = new CNaissance();
            }

            $i = 1;
            foreach ($naissances as $_naissance) {
                $_naissance->fillLiteLimitedTemplate($template, "Sejour - Bébé $i");
                $i++;
            }
        }
    }

    /**
     * Formattage des observations dans les champs de modèle
     *
     * @param CObservationMedicale[] $obs      Liste des observations
     * @param CTemplateManager       $template Le template manager
     * @param string                 $field    Le nom du champ
     *
     * @return void
     */
    protected static function filllimitedTemplateObs($obs, CTemplateManager &$template, $field)
    {
        $observations = [];

        foreach ($obs as $_obs) {
            if ($_obs->cancellation_date) {
                unset($obs[$_obs->_id]);
                continue;
            }
            $_obs->loadRefUser();
            $datetime                                 = CMbDT::format($_obs->date, CAppUI::conf('datetime'));
            $observations["$_obs->date $_obs->_guid"] = "$_obs->text, le $datetime, {$_obs->_ref_user->_view}";
        }
        ksort($observations);

        $template->addListProperty($field, $observations, true, true);

        // En tableau
        $smarty = new CSmartyDP("modules/dPhospi");
        $smarty->assign("obs", $obs);
        $content = $smarty->fetch("print_observations.tpl");

        $content = preg_replace('`([\\n\\r])`', '', $content);
        $content = preg_replace('/<br>/', '<br />', $content);

        $template->addProperty("$field (" . CAppUI::tr("Array") . ")", $content, "", false);
    }

    /**
     * Build an array containing surgery dates
     *
     * @return string[]
     */
    function makeDatesOperations()
    {
        $this->_dates_operations = [];

        // On s'assure d'avoir les opérations
        if (!$this->_ref_operations) {
            $this->loadRefsOperations();
        }

        foreach ($this->_ref_operations as $operation) {
            if ($operation->annulee) {
                continue;
            }

            // On s'assure d'avoir les plages op
            if (!$operation->_ref_plageop) {
                $operation->loadRefPlageOp();
            }

            $this->_dates_operations[$operation->_id] = CMbDT::date($operation->_datetime);
        }

        return $this->_dates_operations;
    }

    /**
     * Builds an array containing consults dates
     *
     * @return string[]
     */
    function makeDatesConsultations()
    {
        $this->_dates_consultations = [];

        // On s'assure d'avoir les opérations
        if (!$this->_ref_consultations) {
            $this->loadRefsConsultations();
        }

        foreach ($this->_ref_consultations as &$consultation) {
            if ($consultation->annule) {
                continue;
            }

            // On s'assure d'avoir les plages op
            if (!$consultation->_ref_plageconsult) {
                $consultation->loadRefPlageConsult();
            }

            $this->_dates_consultations[$consultation->_id] = CMbDT::date($consultation->_datetime);
        }

        return $this->_dates_consultations;
    }

    /**
     * Builds an array containing cancel alerts for the sejour
     *
     * @param int $excluded_id Exclude given operation
     *
     * @return void Valuate $this->_cancel_alert
     */
    function makeCancelAlerts($excluded_id = null)
    {
        $this->_cancel_alerts = [
            "operations"    => [
                "all"   => [],
                "acted" => [],
            ],
            "consultations" => [
                "all"   => [],
                "acted" => [],
            ],
        ];

        // On s'assure d'avoir les opérations
        if (!$this->_ref_operations) {
            $this->loadRefsOperations();
        }

        if ($this->_ref_operations) {
            foreach ($this->_ref_operations as $_operation) {
                // Needed for correct view
                $_operation->loadRefPraticien();
                $_operation->loadRefPlageOp();

                // Exclude one
                if ($_operation->_id == $excluded_id) {
                    continue;
                }

                if ($_operation->annulee == 0) {
                    $operation_view = " le "
                        . CMbDT::dateToLocale(CMbDT::date($_operation->_datetime))
                        . " par le Dr "
                        . $_operation->_ref_chir->_view;
                    $_operation->countActes();
                    if ($_operation->_count_actes && $_operation->date <= CMbDT::date()) {
                        $this->_cancel_alerts["operations"]["acted"][$_operation->_id] = $operation_view;
                    }

                    $this->_cancel_alerts["operations"]["all"][$_operation->_id] = $operation_view;
                }
            }
        }

        if (!$this->_ref_consultations) {
            $this->loadRefsConsultations();
        }

        if ($this->_ref_consultations) {
            CStoredObject::massLoadFwdRef($this->_ref_consultations, "plageconsult_id");
            foreach ($this->_ref_consultations as $_consult) {
                if ($_consult->annule) {
                    continue;
                }

                $_consult->loadRefPlageConsult();

                $consult_view = "le " . CMbDT::dateToLocale($_consult->_date) . " avec le Dr " . $_consult->_ref_chir->_view;

                if ($_consult->chrono == CConsultation::TERMINE) {
                    $this->_cancel_alerts["consultations"]["acted"][$_consult->_id] = $consult_view;
                }

                $this->_cancel_alerts["consultations"]["all"][$_consult->_id] = $consult_view;
            }
        }
    }

    /**
     * Count evenement SSR for a given date
     *
     * @param string $date Date
     *
     * @return void|int
     */
    function countEvenementsSSR($date)
    {
        if (!$this->_id) {
            return null;
        }

        $evenement                    = new CEvenementSSR;
        $ljoin                        = [];
        $ljoin[]                      = "evenement_ssr AS evt_seance ON (evt_seance.seance_collective_id = evenement_ssr.evenement_ssr_id)";
        $where[]                      = "(evenement_ssr.sejour_id = '$this->_id') OR (evenement_ssr.sejour_id IS NULL AND evt_seance.sejour_id = '$this->_id')";
        $where["evenement_ssr.debut"] = "BETWEEN '$date 00:00:00' AND '$date 23:59:59'";

        return $this->_count_evenements_ssr = $evenement->countList($where, null, $ljoin);
    }

    /**
     * Count evenement SSR for a given week and a given kine
     *
     * @param string $kine_id  Filtrer sur le kine
     * @param string $date_min Date minimale
     * @param string $date_max Date maximale
     *
     * @return void|int
     */
    function countEvenementsSSRWeek($kine_id, $date_min, $date_max)
    {
        if (!$this->_id) {
            return null;
        }

        $evenement                        = new CEvenementSSR();
        $where["evenement_ssr.sejour_id"] = " = '$this->_id'";
        $where[]                          = "'$kine_id' IN(evenement_ssr.therapeute_id, evenement_ssr.therapeute2_id, evenement_ssr.therapeute3_id)";
        $this->_count_evenements_ssr      = $evenement->countList($where);

        $where["evenement_ssr.debut"] = "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'";

        return $this->_count_evenements_ssr_week = $evenement->countList($where);
    }

    /**
     * Détermine le nombre de jours du planning pour la semaine
     *
     * @param string $date Date de référence
     *
     * @return int 5, 6 ou 7 jours
     */
    function getNbJourPlanning($date)
    {
        $sunday   = CMbDT::date("next sunday", CMbDT::date("- 1 DAY", $date));
        $saturday = CMbDT::date("-1 DAY", $sunday);

        $_evt                         = new CEvenementSSR();
        $where                        = [];
        $where["evenement_ssr.debut"] = "BETWEEN '$sunday 00:00:00' AND '$sunday 23:59:59'";
        $where[]                      = "evenement_ssr.sejour_id = '$this->_id' AND evenement_ssr.seance_collective_id IS NULL";
        $count_event_sunday           = $_evt->countList($where);

        $ljoin              = [];
        $ljoin[]            = "evenement_ssr AS evt_seance ON (evt_seance.seance_collective_id = evenement_ssr.evenement_ssr_id)";
        $where[0]           = "evenement_ssr.sejour_id IS NULL AND evt_seance.sejour_id = '$this->_id' AND evt_seance.seance_collective_id IS NOT NULL";
        $count_event_sunday += $_evt->countList($where, null, $ljoin);

        $nb_days = 7;
        // Si aucun evenement le dimanche
        if (!$count_event_sunday) {
            $nb_days                      = 6;
            $where["evenement_ssr.debut"] = "BETWEEN '$saturday 00:00:00' AND '$saturday 23:59:59'";
            $count_event_saturday         = $_evt->countList($where, null, $ljoin);

            $where[0]             = "evenement_ssr.sejour_id = '$this->_id' AND evenement_ssr.seance_collective_id IS NULL";
            $count_event_saturday += $_evt->countList($where);
            // Aucun evenement le samedi et aucun le dimanche
            if (!$count_event_saturday) {
                $nb_days = 5;
            }
        }

        return $nb_days;
    }

    /**
     * @see parent::completeLabelFields()
     */
    function completeLabelFields(&$fields, $params)
    {
        if (!isset($this->_from_op)) {
            $this->loadRefLastOperation()->_from_sejour = 1;
            $this->_ref_last_operation->completeLabelFields($fields, $params);
        }

        $this->loadRefEtablissement();
        $this->_ref_group->completeLabelFields($fields, $params);
        $this->loadRefPatient()->completeLabelFields($fields, $params);
        $this->loadRefPraticien();
        $this->loadNDA();
        $this->loadNRA();
        $now         = CMbDT::dateTime();
        $affectation = $this->getCurrAffectation($this->entree > $now ? $this->entree : null);
        $affectation->loadView();
        $affectation->loadRefService();

        $souhaits = [];
        if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "expert") {
            /** @var CItemLiaison[] $items_liaisons */
            $items_liaisons = $this->loadBackRefs("items_liaisons", "date");
            CStoredObject::massLoadFwdRef($items_liaisons, "item_souhait_id");
            CStoredObject::massLoadFwdRef($items_liaisons, "sous_item_id");

            foreach ($items_liaisons as $_liaison) {
                $item_souhait = $_liaison->loadRefItem();
                if ($item_souhait->object_class == "CPrestationPonctuelle") {
                    continue;
                }
                $sous_item = $_liaison->loadRefSousItem();
                $nom       = $item_souhait;
                if ($sous_item->_id) {
                    $nom = $sous_item->nom;
                }
                $souhaits[] = $nom;
            }
        }

        $meds_dispenses = [];

        if (isset($params["debut_dispensation"]) && isset($params["fin_dispensation"])) {
            $from = $params["debut_dispensation"];
            $to   = $params["fin_dispensation"];

            $delivery = new CProductDelivery();

            $where = [
                "date_dispensation" => "BETWEEN '$from' AND '$to'",
                "sejour_id"         => "= '$this->_id'",
            ];

            /** @var CProductDelivery[] $deliveries */
            $deliveries = $delivery->loadList($where);
            $stocks     = CStoredObject::massLoadFwdRef($deliveries, "stock_id");
            CStoredObject::massLoadFwdRef($stocks, "product_id");
            $total_quantities = [];

            foreach ($deliveries as $_delivery) {
                $product          = $_delivery->loadRefStock()->loadRefProduct();
                $article          = CMedicamentArticle::get($product->code);

                if (isset($total_quantities[$product->_id])) {
                    $total_quantities[$product->_id] += $_delivery->quantity;
                } else {
                    $total_quantities[$product->_id] = $_delivery->quantity;
                }
                $meds_dispenses[$product->_id] =
                    "(" . $total_quantities[$product->_id] . ")" . $product->code . " " . CMedicamentArticle::getLibelleEtiquette($article);
            }
        }

        $nom_maman = $prenom_maman = "";

        if (CModule::getActive("maternite")) {
            $patiente     = $this->loadRefNaissance()->loadRefSejourMaman()->loadRefPatient();
            $nom_maman    = $patiente->nom;
            $prenom_maman = $patiente->prenom;
        }

        $fields_sejour = [
            "DATE ENT"                => CMbDT::dateToLocale(CMbDT::date($this->entree)),
            "HEURE ENT"               => CMbDT::transform($this->entree, null, "%H:%M"),
            "DATE SORTIE"             => CMbDT::dateToLocale(CMbDT::date($this->sortie)),
            "HEURE SORTIE"            => CMbDT::transform($this->sortie, null, "%H:%M"),
            "TYPE HOSPITALISATION"    => CAppUI::tr("CSejour.type.$this->type.short"),
            "PRAT RESPONSABLE"        => $this->_ref_praticien->_view,
            "PRENOM PRAT RESPONSABLE" => $this->_ref_praticien->_user_first_name,
            "NOM PRAT RESPONSABLE"    => $this->_ref_praticien->_user_last_name,
            "NDOS"                    => $this->_NDA,
            "NRA"                     => $this->_ref_NRA ? $this->_ref_NRA->id400 : "",
            "CODE BARRE NDOS"         => "@BARCODE_" . $this->_NDA . "@",
            "CHAMBRE COURANTE"        => $affectation->_view,
            "SERVICE COURANT"         => $affectation->_ref_service->_view,
            "MEDICAMENTS DISPENSES"   => implode("<br />", $meds_dispenses),
            "NOM MAMAN"               => $nom_maman,
            "PRENOM MAMAN"            => $prenom_maman,
        ];

        if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "expert") {
            $fields_sejour["PRESTATIONS SOUHAITEES"] = implode(" - ", $souhaits);
        }

        $fields = array_merge($fields, $fields_sejour);
        if (CModule::getActive("barcodeDoc") && CAppUI::gconf("barcodeDoc general module_actif")) {
            $fields = array_merge(
                $fields,
                [
                    "CODE BARRE NDOS AVEC PREFIXE" => "@BARCODE_" . CAppUI::gconf("barcodeDoc general prefix_NDA") . $this->_NDA . "@",
                ]
            );
        }
    }

    /**
     * @inheritDoc
     * @throws CanNotMerge
     * @throws Exception
     */
    public function checkMerge(array $objects = []): void
    {
        if (!static::getAllowMerge($this->group_id)) {
            throw CanNotMergeSejour::sejourNotAllowed();
        }

        parent::checkMerge($objects);

        // Cas des affectations
        // Do not use CSejour::massLoadBackRefs because $sejours keys should be sejour_id
        $affectation = new CAffectation();

        $where = [
            'sejour_id' => CSQLDataSource::prepareIn(CMbArray::pluck($objects, '_id')),
        ];

        $affectations = $affectation->loadList($where);
        $sejour_ids   = CMbArray::pluck($affectations, "sejour_id");

        if (count(array_unique($sejour_ids)) > 1) {
            throw CanNotMergeSejour::multipleAffectations();
        }

        foreach ($affectations as $_affectation_1) {
            foreach ($affectations as $_affectation_2) {
                if ($_affectation_1->collide($_affectation_2)) {
                    throw CanNotMergeSejour::affectationsConflict($_affectation_1, $_affectation_2);
                }
            }
        }
    }

    /**
     * Détermine les UFs d'hébergement, de soins et médicaux pour une date donnée
     * et éventuellement une affectation donnée
     *
     * @param null $date           Date de référence
     * @param null $affectation_id Affectation spécifique
     *
     * @return array|CUniteFonctionnelle[]
     */
    function getUFs($date = null, $affectation_id = null)
    {
        if (!$date) {
            $date = CMbDT::dateTime();
        }

        // Les modifications dans la DHE des UFs de séjour sont répercutées sur la première affectation
        if (!CSejour::$_cutting_affectation && CAppUI::conf("dPplanningOp CSejour update_ufs_first_aff", "CGroups-$this->group_id")
            && ($this->fieldModified("uf_hebergement_id") || $this->fieldModified("uf_soins_id") || $this->fieldModified("uf_medicale_id"))
        ) {
            $first_affectation = $this->loadRefFirstAffectation();
            if ($this->fieldModified("uf_hebergement_id")) {
                $first_affectation->uf_hebergement_id = $this->uf_hebergement_id;
            }
            if ($this->fieldModified("uf_soins_id")) {
                $first_affectation->uf_soins_id = $this->uf_soins_id;
            }
            if ($this->fieldModified("uf_medicale_id")) {
                $first_affectation->uf_medicale_id = $this->uf_medicale_id;
            }
            $first_affectation->_no_synchro_eai = true;
            $first_affectation->_no_synchro     = true;
            if ($first_affectation->_id) {
                if ($msg = $first_affectation->store()) {
                    return $msg;
                }
            }
        }

        if ($affectation_id) {
            $affectation = new CAffectation();
            $affectation->load($affectation_id);
        } else {
            // Chargement de l'affectation courante
            $affectation = $this->getCurrAffectation($date);

            // Si on n'a pas d'affectation on va essayer de chercher la première
            if (!$affectation->_id) {
                $this->loadRefsAffectations();
                $affectation = $this->_ref_first_affectation;
            }
        }

        if ($affectation->_id) {
            $this->completeField("uf_hebergement_id", "uf_soins_id", "uf_medicale_id");
            $ufs = $affectation->getUFs();
            if ((!$this->uf_hebergement_id && !$this->fieldModified("uf_hebergement_id")) || $this->fieldModified("service_id")) {
                $this->uf_hebergement_id = $affectation->uf_hebergement_id;
            }
            if ((!$this->uf_soins_id && !$this->fieldModified("uf_soins_id")) || $this->fieldModified("service_id")) {
                $this->uf_soins_id = $affectation->uf_soins_id;
            }
            if (!$this->uf_medicale_id && !$this->fieldModified("uf_medicale_id")) {
                $this->uf_medicale_id = $affectation->uf_medicale_id;
            }

            return $ufs;
        } else {
            $this->makeUF();
        }

        return [
            "hebergement" => $this->loadRefUFHebergement(),
            "medicale"    => $this->loadRefUFMedicale(),
            "soins"       => $this->loadRefUFSoins(),
        ];
    }

    function makeUF()
    {
        $this->completeField("uf_hebergement_id", "uf_soins_id", "uf_medicale_id", "entree_prevue", "sortie_prevue");

        $ljoin   = ["uf" => "uf.uf_id = affectation_uf.uf_id"];
        $where   = [];
        $where[] = "uf.type_sejour IS NULL OR uf.type_sejour = '$this->type'";
        $where[] = "uf.date_debut IS NULL OR uf.date_debut < '" . CMbDT::date($this->sortie) . "'";
        $where[] = "uf.date_fin IS NULL OR uf.date_fin > '" . CMbDT::date($this->entree) . "'";

        if ((!$this->uf_hebergement_id && !$this->fieldModified("uf_hebergement_id")) || $this->fieldModified("service_id")) {
            $affectation_uf   = new CAffectationUniteFonctionnelle();
            $where["uf.type"] = "= 'hebergement'";

            if (!$affectation_uf->uf_id) {
                $where["object_id"]    = "= '$this->service_id'";
                $where["object_class"] = "= 'CService'";
                $affectation_uf->loadObject($where, null, null, $ljoin);
            }

            $this->uf_hebergement_id = $affectation_uf->uf_id == null ? "" : $affectation_uf->uf_id;
        }

        if ((!$this->uf_soins_id && !$this->fieldModified("uf_soins_id")) || $this->fieldModified("service_id")) {
            $affectation_uf   = new CAffectationUniteFonctionnelle();
            $where["uf.type"] = "= 'soins'";

            if (!$affectation_uf->uf_id) {
                $where["object_id"]    = "= '$this->service_id'";
                $where["object_class"] = "= 'CService'";
                $affectation_uf->loadObject($where, null, null, $ljoin);
            }

            $this->uf_soins_id = $affectation_uf->uf_id == null ? "" : $affectation_uf->uf_id;
        }

        if (!$this->uf_medicale_id && !$this->fieldModified("uf_medicale_id")) {
            $affectation_uf   = new CAffectationUniteFonctionnelle();
            $where["uf.type"] = "= 'medicale'";

            if (!$affectation_uf->uf_id) {
                $praticien = $this->loadRefPraticien();
                $praticien->loadRefFunction();

                $where["object_id"]    = "= '$praticien->_id'";
                $where["object_class"] = "= 'CMediusers'";
                $affectation_uf->loadObject($where, null, null, $ljoin);

                if (!$affectation_uf->_id) {
                    $function              = $praticien->_ref_function;
                    $where["object_id"]    = "= '$function->_id'";
                    $where["object_class"] = "= 'CFunctions'";
                    $affectation_uf->loadObject($where, null, null, $ljoin);
                }
            }

            $this->uf_medicale_id = $affectation_uf->uf_id == null ? "" : $affectation_uf->uf_id;
        }
    }

    /**
     * Détermine les incréments
     *
     * @return array
     */
    function getIncrementVars()
    {
        $group_guid = $this->group_id ? "CGroups-$this->group_id" : CGroups::loadCurrent()->_guid;

        $typeHospi = $this->type ? CAppUI::conf("dPsante400 CIncrementer type_sejour $this->type", $group_guid) : null;

        return [
            "typeHospi" => $typeHospi,
            "SSSS"      => CMbDT::format($this->entree, "%Y"),
            "SS"        => CMbDT::format($this->entree, "%y"),
        ];
    }

    /**
     * Détermine les types de mouvements en fonction du code de message
     *
     * @param null $code Code du message
     *
     * @return null|string
     */
    function getMovementType($code = null)
    {
        // Cas d'une pré-admission
        if ($this->_etat == "preadmission") {
            return "PADM";
        }

        if ($this->_etat == "encours" && ($this->service_entree_id || $code == "A02")) {
            return "MUTA";
        }

        // Cas d'une absence provisoire
        if ($code == "A21") {
            return "AABS";
        }

        // Cas d'un retour d'absence provisoire
        if ($code == "A22") {
            return "RABS";
        }

        // Cas d'une entrée autorisée
        if ($code == "A14") {
            return "EATT";
        }

        // Cas d'un transfert autorisé
        if ($code == "A15") {
            return "TATT";
        }

        // Cas d'une sortie autorisée
        if ($code == "A16") {
            return "SATT";
        }

        // Cas d'une admission
        if ($this->_etat == "encours") {
            return "ADMI";
        }

        // Cas d'une sortie
        if ($this->_etat == "cloture") {
            return "SORT";
        }

        return null;
    }

    function getIntervallesPrestations(&$liaisons, &$dates, &$liaisons_by_id, $only_souhait = false)
    {
        $where = [];

        if ($only_souhait) {
            $where["item_souhait_id"] = "IS NOT NULL";
        }

        /** @var CItemLiaison[] $items_liaisons */
        $items_liaisons = $this->loadBackRefs("items_liaisons", "date", null, null, null, null, null, $where);

        CStoredObject::massLoadFwdRef($items_liaisons, "item_souhait_id");
        CStoredObject::massLoadFwdRef($items_liaisons, "item_realise_id");

        foreach ($items_liaisons as $_item_liaison) {
            $_item_souhait = $_item_liaison->loadRefItem();

            if ($_item_souhait->object_class == "CPrestationPonctuelle") {
                $_item_souhait->loadRefObject();
                // Ponctuelles au forfait : quantité de 1 au début du séjour
                if ($_item_souhait->_ref_object->forfait) {
                    $this->_ref_prestations[CMbDT::date($this->entree)][] = [
                        "quantite"          => 1,
                        "item"              => $_item_souhait,
                        "liaison"           => $_item_liaison,
                        "sous_item_facture" => "",
                    ];
                } else {
                    $this->_ref_prestations[$_item_liaison->date][] = [
                        "quantite"          => $_item_liaison->quantite,
                        "item"              => $_item_souhait,
                        "liaison"           => $_item_liaison,
                        "sous_item_facture" => "",
                    ];
                }
            } else {
                $liaisons[$_item_liaison->prestation_id][$_item_liaison->date] = $_item_liaison;
                $liaisons_by_id[$_item_liaison->_id]                           = $_item_liaison;
            }
        }

        // Calcul des dates de début et fin par liaison
        foreach ($liaisons as $prestation_id => $_liaisons) {
            $last_liaison = end($_liaisons);

            unset($prev_liaison_id);

            foreach ($_liaisons as $date => $_liaison) {
                $_sous_item = $_liaison->loadRefSousItem();
                if (isset($prev_liaison_id) || $_liaison->_id == $last_liaison->_id) {
                    // Utilisation du début de la liaison courante pour indiquer la fin de la liaison précédente
                    if (isset($prev_liaison_id)) {
                        $dates[$prev_liaison_id]["fin"] = CMbDT::date("-1 day", $date);
                    }
                    if ($_liaison->_id == $last_liaison->_id) {
                        $dates[$_liaison->_id]["debut"] = CMbDT::date($date);
                        $dates[$_liaison->_id]["fin"]   = CMbDT::date($_sous_item->niveau == "nuit" ? "-1 day" : "", $this->sortie);
                        continue;
                    }
                }

                $prev_liaison_id                = $_liaison->_id;
                $dates[$_liaison->_id]["debut"] = CMbDT::date($date);
            }
        }

        // Dans le cas de liaisons identiques qui se suivent, on les fusionne
        // Résoud le cas des liaisons avec sous-item de niveau nuit
        foreach ($liaisons as $prestation_id => $_liaisons) {
            foreach ($_liaisons as $date => $_liaison) {
                if (isset($_save_liaison)) {
                    if (($_save_liaison->item_souhait_id == $_liaison->item_souhait_id) &&
                        ($_save_liaison->item_realise_id == $_liaison->item_realise_id) &&
                        ($_save_liaison->sous_item_id == $_liaison->sous_item_id)
                    ) {
                        $old_fin   = $dates[$_save_liaison->_id]["fin"];
                        $new_debut = $dates[$_liaison->_id]["debut"];
                        if (CMbDT::daysRelative($old_fin, $new_debut) == 1 || CMbDT::daysRelative($old_fin, $new_debut) == 0) {
                            $dates[$_save_liaison->_id]["fin"] = $dates[$_liaison->_id]["fin"];
                            unset($dates[$_liaison->_id]);
                            unset($liaisons[$prestation_id][$date]);
                            continue;
                        }
                    }
                }
                $_save_liaison = $_liaison;
            }
        }

        foreach ($dates as $_liaison_id => $_date) {
            $dates[$_liaison_id]["qte"] = CMbDT::daysRelative($_date["debut"], $_date["fin"]);
        }

        CMbArray::pluckSort($dates, SORT_ASC, "debut");
    }

    /**
     * Charge les items de prestations souhaités et réalisés
     *
     * @return CItemPrestation[]
     */
    function getPrestations()
    {
        $this->_ref_prestations = [];

        $liaisons_j = $dates = $liaisons_by_id = [];

        self::getIntervallesPrestations($liaisons_j, $dates, $liaisons_by_id);

        $facture_outclass = CAppUI::gconf("dPhospi prestations facture_outclass");

        // Calcul du niveau de réalisation (_quantite)
        foreach ($liaisons_j as $prestation_id => $_liaisons) {
            foreach ($_liaisons as $date => $_liaison) {
                $_item_souhait = $_liaison->loadRefItem();
                $_item_realise = $_liaison->loadRefItemRealise();
                $sous_item     = $_liaison->loadRefSousItem();

                if (!$_item_souhait->_id) {
                    continue;
                }

                if (!$_item_realise->_id) {
                    continue;
                }

                $item_facture = $_item_realise;

                // On ne facture pas si sous-classé suivant la configuration
                if (!$facture_outclass && $_item_souhait->rank < $_item_realise->rank) {
                    continue;
                }

                // Si ce qui est réalisé est supérieur au demandé (rank inférieur), c'est le souhait qui est facturé
                if ($_item_realise->rank < $_item_souhait->rank) {
                    $item_facture = $_item_souhait;
                }

                if (!$item_facture->facturable) {
                    continue;
                }

                $dates_liaison = $dates[$_liaison->_id];

                $quantite = CMbDT::daysRelative($dates_liaison["debut"], $dates_liaison["fin"]);

                // On incrémente la quantité si ce n'est pas la dernière liaison ou que le sous-item est de type jour
                if ($dates_liaison["fin"] != CMbDT::date($this->sortie) || (!$sous_item->_id || $sous_item->niveau == "jour")) {
                    $quantite += 1;
                }

                if (!$quantite || $quantite < 0) {
                    continue;
                }

                $this->_ref_prestations[$date][] = [
                    "quantite"          => $quantite,
                    "item"              => $item_facture,
                    "liaison"           => $_liaison,
                    "date_fin"          => $dates_liaison["fin"],
                    // On prend le nom du sous-item et son id400 si présent et s'il fait partie des sous-items de l'item facturé.
                    "sous_item_facture" => $sous_item->item_prestation_id == $item_facture->_id ? $sous_item : "",
                ];
            }
        }

        return $this->_ref_prestations;
    }

    /**
     * Chrage la première liaison de prestation journalière pour une prestation
     *
     * @param string $prestation_id Prestation concernée
     *
     * @return void
     */
    function loadRefFirstLiaisonForPrestation($prestation_id)
    {
        $this->_first_liaison_for_prestation = new CItemLiaison();

        if (!$prestation_id || $prestation_id === "all") {
            return;
        }

        $this->_first_liaison_for_prestation->sejour_id     = $this->_id;
        $this->_first_liaison_for_prestation->prestation_id = $prestation_id;
        $this->_first_liaison_for_prestation->loadMatchingObject();
    }

    /**
     * Chargement de la première liaison d'une prestation pour une collection de séjours
     *
     * @param self[]                 $sejours       Liste des séjours
     * @param CPrestationJournaliere $prestation_id Prestation
     *
     * @return void
     */
    static function massLoadRefFirstLiaisonForPrestation($sejours, $prestation_id)
    {
        if (!$prestation_id || $prestation_id === "all") {
            foreach ($sejours as $_sejour) {
                $_sejour->_first_liaison_for_prestation = new CItemLiaison();
            }

            return;
        }

        $first_liaison = new CItemLiaison();

        $where = [
            "sejour_id"     => CSQLDataSource::prepareIn(array_keys($sejours)),
            "prestation_id" => "= '$prestation_id'",
        ];

        $first_liaisons = $first_liaison->loadList($where);

        foreach ($first_liaisons as $_first_liaison) {
            $sejours[$_first_liaison->sejour_id]->_first_liaison_for_prestation = $_first_liaison;
        }

        foreach ($sejours as $_sejour) {
            if (!$_sejour->_first_liaison_for_prestation) {
                $_sejour->_first_liaison_for_prestation = new CItemLiaison();
            }
        }
    }

    /**
     * load the last liaisons for the given date
     *
     * @param string                 $date          Date
     * @param CPrestationJournaliere $prestation_id Prestation
     *
     * @return string[]
     */
    function loadAllLiaisonsForDay($date = null, $prestation_id = null)
    {
        $ds  = $this->getDS();
        $sql = "SELECT object_id, item_prestation.nom, item_prestation.color, date
      FROM item_liaison, item_prestation, prestation_journaliere
      WHERE (item_liaison.item_souhait_id = item_prestation.item_prestation_id OR item_liaison.item_realise_id = item_prestation.item_prestation_id)
      AND prestation_journaliere.prestation_journaliere_id = object_id
      AND sejour_id = '$this->_id'
      AND date <= '$date'
      AND object_class = 'CPrestationJournaliere'";
        if ($prestation_id && $prestation_id != "all") {
            $sql .= "AND prestation_journaliere.prestation_journaliere_id = '$prestation_id'";
        }
        $sql     .= "ORDER BY date asc, prestation_journaliere.nom ASC";
        $results = $ds->loadList($sql);

        $prestas = [];
        foreach ($results as $_result) {
            $prestas[$_result["object_id"]] = ["color" => $_result["color"], "nom" => $_result["nom"]];
        }

        return $prestas;
    }

    /**
     * Charge les liaisons de prestations pour une prestation entre deux date
     *
     * @param string $prestation_id Prestation de référence
     * @param null   $date_min      Date minimale
     * @param null   $date_max      Date maximale
     *
     * @return CStoredObject[]
     */
    function loadLiaisonsForPrestation($prestation_id, $date_min = null, $date_max = null)
    {
        $this->_liaisons_for_prestation = [];

        if ($prestation_id == "all") {
            $presta        = new CPrestationJournaliere();
            $prestation_id = $presta->loadIds();
        } else {
            $prestation_id = [$prestation_id];
        }

        if (!$date_max) {
            $date_max = $date_min;
        }

        $where = [
            "sejour_id" => "= '$this->_id'",
        ];

        foreach ($prestation_id as $_presta_id) {
            $item_liaison = new CItemLiaison();
            if ($date_min && $date_max) {
                $where["date"] = "BETWEEN '$date_min' AND '$date_max'";
            }
            $where["prestation_id"] = "= '$_presta_id'";

            $liaisons = $item_liaison->loadList($where, null, null, "item_liaison_id");

            // S'il n'y a pas de liaison (ou que la première liaison est après la date de début)
            // et qu'une période est donnée, on cherche la dernière liaison disponible
            // avant la date de début
            $first_liaison = reset($liaisons);
            if ($date_min && $date_max && (!count($liaisons) || $first_liaison->date > $date_min)) {
                $where["date"] = "< '$date_min'";
                $item_liaison->loadObject($where, "date DESC");
                $liaisons = array_merge($liaisons, [$item_liaison]);
            }

            foreach ($liaisons as $_liaison) {
                $this->_liaisons_for_prestation[$_liaison->_id] = $_liaison;
            }
        }

        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation, "item_souhait_id");
        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation, "item_realise_id");
        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation, "sous_item_id");

        /** @var CItemLiaison $_liaison */
        foreach ($this->_liaisons_for_prestation as $_liaison) {
            $_liaison->loadRefItem();
            $_liaison->loadRefSousItem();
            $_liaison->loadRefItemRealise();
        }

        $order_date = CMbArray::pluck($this->_liaisons_for_prestation, "date");
        array_multisort($order_date, SORT_ASC, $this->_liaisons_for_prestation);

        return $this->_liaisons_for_prestation;
    }

    /**
     * Charge les liaisons de prestations pour une collection de séjours
     *
     * @param self[]                 $sejours
     * @param CPrestationJournaliere $prestation_id Prestation
     * @param string                 $date_min
     * @param string                 $date_max
     *
     * @return void
     */
    static function massLoadLiaisonsForPrestation($sejours, $prestation_id, $date_min = null, $date_max = null)
    {
        if (!$prestation_id) {
            return;
        }

        if ($prestation_id === "all") {
            $presta        = new CPrestationJournaliere();
            $prestation_id = $presta->loadIds();

            if (!count($prestation_id)) {
                return;
            }
        } else {
            $prestation_id = [$prestation_id];
        }

        if (!$date_max) {
            $date_max = $date_min;
        }

        if ($date_min && $date_max) {
            $date_min = CMbDT::date($date_min);
            $date_max = CMbDT::date($date_max);
        }

        foreach ($sejours as $_sejour) {
            $_sejour->_liaisons_for_prestation = [];
        }

        $item_liaison = new CItemLiaison();

        $temp_liaisons_by_sejour = [];

        foreach ($prestation_id as $_prestation_id) {
            $where = [
                "sejour_id"     => CSQLDataSource::prepareIn(array_keys($sejours)),
                "prestation_id" => "= '$_prestation_id'",
            ];

            if ($date_min && $date_max) {
                $where["date"] = "BETWEEN '$date_min' AND '$date_max'";
            }

            $liaisons = $item_liaison->loadList($where, null, null, "item_liaison_id");

            CStoredObject::massLoadFwdRef($liaisons, "item_souhait_id");
            CStoredObject::massLoadFwdRef($liaisons, "item_realise_id");
            CStoredObject::massLoadFwdRef($liaisons, "sous_item_id");

            foreach ($liaisons as $_liaison) {
                $temp_liaisons_by_sejour[$_liaison->sejour_id][$_liaison->prestation_id][$_liaison->_id] = $_liaison;
            }

            foreach ($sejours as $_sejour) {
                // S'il n'y a pas de liaison (ou que la première liaison est après la date de début)
                // et qu'une période est donnée, on cherche la dernière liaison disponible
                // avant la date de début
                if (!isset($temp_liaisons_by_sejour[$_sejour->_id])) {
                    $temp_liaisons_by_sejour[$_sejour->_id] = [];
                }
                if (!isset($temp_liaisons_by_sejour[$_sejour->_id][$_prestation_id])) {
                    $temp_liaisons_by_sejour[$_sejour->_id][$_prestation_id] = [];
                }
                $first_liaison = count($temp_liaisons_by_sejour[$_sejour->_id][$_prestation_id]) ?
                    reset($temp_liaisons_by_sejour[$_sejour->_id][$_prestation_id]) : new CItemLiaison();
                if ($date_min && $date_max && (!$first_liaison->_id || $first_liaison->date > $date_min)) {
                    $where["sejour_id"] = "= '$_sejour->_id'";
                    $where["date"]      = "< '$date_min'";
                    $temp_liaison       = new CItemLiaison();
                    if ($temp_liaison->loadObject($where, "date DESC")) {
                        $temp_liaisons_by_sejour[$_sejour->_id][$_prestation_id] = array_merge(
                            $temp_liaisons_by_sejour[$_sejour->_id][$_prestation_id],
                            [$temp_liaison]
                        );
                    }
                }
            }
        }

        foreach ($temp_liaisons_by_sejour as $_sejour_id => $_liaisons_by_prestation) {
            foreach ($_liaisons_by_prestation as $_liaisons) {
                foreach ($_liaisons as $_liaison) {
                    $sejours[$_sejour_id]->_liaisons_for_prestation[$_liaison->_id] = $_liaison;
                }
            }
        }

        foreach ($sejours as $_sejour) {
            /** @var CItemLiaison $_liaison */
            foreach ($_sejour->_liaisons_for_prestation as $_liaison) {
                $_liaison->loadRefItem();
                $_liaison->loadRefSousItem();
                $_liaison->loadRefItemRealise();
            }

            CMbArray::pluckSort($_sejour->_liaisons_for_prestation, SORT_ASC, "date");
        }
    }

    /**
     * get prestations for a particular day
     * check for previous prestation to keep only "active" liaisons
     *
     * @param int    $prestation_id prestation
     * @param string $date          date
     *
     * @return CStoredObject[]
     */
    function loadLiaisonsForDay($prestation_id, $date)
    {
        $maxs               = [];
        $item_liaison       = new CItemLiaison();
        $ds                 = $item_liaison->getDS();
        $where              = [];
        $groupby            = "item_liaison_id";
        $order              = "item_liaison_id DESC";
        $where["sejour_id"] = "= '$this->_id'";

        if ($prestation_id == "all") {
            $prestation_id = null;
        }

        $where["item_liaison.prestation_id"] = $prestation_id ? $ds->prepare('= ?', $prestation_id) : 'IS NOT NULL';

        $ljoin["prestation_journaliere"] = "item_liaison.prestation_id = prestation_journaliere.prestation_journaliere_id";

        $where["date"] = "<= '$date'";

        $this->_liaisons_for_prestation = $item_liaison->loadList($where, $order, null, $groupby, $ljoin);

        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation, "item_souhait_id");
        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation, "item_realise_id");

        /** @var CItemLiaison $_liaison */
        foreach ($this->_liaisons_for_prestation as $_liaison) {
            $_liaison->loadRefItem();
            $_liaison->loadRefItemRealise();
            $_liaison->loadRefSousItem();

            //@todo : find a better way to cleanup old prestas
            $maxs[$_liaison->date][$_liaison->prestation_id] = $_liaison->_id;
            foreach ($maxs as $date => $data) {
                if ($date > $_liaison->date) {
                    foreach ($data as $cat => $id) {
                        if ($cat == $_liaison->prestation_id) {
                            unset($this->_liaisons_for_prestation[$_liaison->_id]);
                        }
                    }
                }
            }
        }

        return $this->_liaisons_for_prestation;
    }

    /**
     * @param $date
     *
     * @return array
     * @throws Exception
     */
    public function loadLiaisonsPonctualPrestationsForDay(string $date): array
    {
        $item_liaison = new CItemLiaison();
        $ds           = $item_liaison->getDS();
        $order        = "item_liaison_id DESC";
        $where        = [
            "sejour_id"     => $ds->prepare("= '$this->_id'"),
            "date"          => $ds->prepare("= '$date'"),
            "prestation_id" => "IS NULL",
        ];

        $this->_liaisons_for_prestation_ponct = $item_liaison->loadList($where, $order);

        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation_ponct, "item_souhait_id");
        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation_ponct, "item_realise_id");
        CStoredObject::massLoadFwdRef($this->_liaisons_for_prestation_ponct, "sous_item_id");

        /** @var CItemLiaison $_liaison */
        foreach ($this->_liaisons_for_prestation_ponct as $_liaison) {
            $_liaison->loadRefItem();
            $_liaison->loadRefItemRealise();
            $_liaison->loadRefSousItem();
        }

        return $this->_liaisons_for_prestation_ponct;
    }

    /**
     * Compte les prestations souhaitées
     *
     * @return int
     */
    function countPrestationsSouhaitees()
    {
        $where["item_souhait_id"] = "IS NOT NULL";

        return $this->countBackRefs("items_liaisons", $where);
    }

    /**
     * Comptage de masse des prestations souhaitées pour une collection de séjours
     *
     * @param CSejour[] $sejours Collection
     *
     * @return void
     */
    static function massCountPrestationSouhaitees($sejours)
    {
        $where["item_souhait_id"] = "IS NOT NULL";
        CStoredObject::massCountBackRefs($sejours, "items_liaisons", $where);
    }

    static function massLoadPrestationSouhaitees($sejours)
    {
        $where["item_souhait_id"] = "IS NOT NULL";
        $items_liaisons           = CStoredObject::massLoadBackRefs($sejours, "items_liaisons", null, $where);
        $items                    = CStoredObject::massLoadFwdRef($items_liaisons, "item_souhait_id");
        CStoredObject::massLoadFwdRef($items, "object_id");
        CStoredObject::massLoadFwdRef($items_liaisons, "sous_item_id");

        /** @var CItemLiaison $_item_liaison */
        if (is_array($items_liaisons)) {
            foreach ($items_liaisons as $_item_liaison) {
                $_item_liaison->loadRefItem()->loadRefObject();
                $_item_liaison->loadRefSousItem();
            }

            // Retrait des doublons (qui identifient le même item de prestation)
            foreach ($sejours as $_sejour) {
                $temp_ids_items = [];
                foreach ($_sejour->_back["items_liaisons"] as $_liaison) {
                    if ($_liaison->prestation_id && in_array($_liaison->item_souhait_id, $temp_ids_items)) {
                        unset($_sejour->_back["items_liaisons"][$_liaison->_id]);
                        continue;
                    }
                    $temp_ids_items[] = $_liaison->item_realise_id;
                }
            }
        }
    }

    static function massLoadPrestationRealisees($sejours)
    {
        $where          = [
            "item_realise_id IS NOT NULL OR prestation_id IS NULL",
        ];
        $items_liaisons = CStoredObject::massLoadBackRefs($sejours, "items_liaisons", null, $where);
        $items          = CStoredObject::massLoadFwdRef($items_liaisons, "item_souhait_id");
        $items_realises = CStoredObject::massLoadFwdRef($items_liaisons, "item_realise_id");

        CStoredObject::massLoadFwdRef($items, "object_id");
        CStoredObject::massLoadFwdRef($items_realises, "object_id", "CPrestationJournaliere");
        CStoredObject::massLoadFwdRef($items_liaisons, "sous_item_id");

        /** @var CItemLiaison $_item_liaison */
        if (is_array($items_liaisons)) {
            foreach ($items_liaisons as $_item_liaison) {
                $_item_liaison->loadRefItem()->loadRefObject();
                if ($_item_liaison->item_realise_id) {
                    $_item_liaison->loadRefItemRealise()->loadRefObject();
                }
                $_item_liaison->loadRefSousItem();
            }
        }

        // Retrait des doublons (qui identifient le même item de prestation)
        foreach ($sejours as $_sejour) {
            $temp_ids_items = [];
            foreach ($_sejour->_back["items_liaisons"] as $_liaison) {
                if (in_array($_liaison->item_realise_id, $temp_ids_items)) {
                    unset($_sejour->_back["items_liaisons"][$_liaison->_id]);
                    continue;
                }
                $temp_ids_items[] = $_liaison->item_realise_id;
            }
        }
    }

    /**
     * Chargement de naissances
     *
     * @return CNaissance[]
     */
    function loadRefsNaissances()
    {
        return $this->_ref_naissances = $this->loadBackRefs("naissances");
    }

    /**
     * Chargement de la naissance pour le séjour du bébé
     *
     * @return CNaissance
     */
    function loadRefNaissance()
    {
        return $this->_ref_naissance = $this->loadUniqueBackRef("naissance");
    }

    /**
     * Chargement de l'ensemble des UFs du séjour
     *
     * @return void
     */
    function loadRefUfs()
    {
        $this->loadRefUFHebergement();
        $this->loadRefUFMedicale();
        $this->loadRefUFSoins();
    }

    /**
     * Chargement de l'UF d'hébergement
     *
     * @return CUniteFonctionnelle
     */
    function loadRefUFHebergement()
    {
        return $this->_ref_uf_hebergement = $this->loadFwdRef("uf_hebergement_id", true);
    }

    /**
     * Chargement de l'UF médicale
     *
     * @return CUniteFonctionnelle
     */
    function loadRefUFMedicale()
    {
        return $this->_ref_uf_medicale = $this->loadFwdRef("uf_medicale_id", true);
    }

    /**
     * Chargement de l'UF de soins
     *
     * @return CUniteFonctionnelle
     */
    function loadRefUFSoins()
    {
        return $this->_ref_uf_soins = $this->loadFwdRef("uf_soins_id", true);
    }

    /**
     * Return idex type if it's special (e.g. NDA/...)
     *
     * @param CIdSante400 $idex Idex
     *
     * @return string|null
     */
    function getSpecialIdex(CIdSante400 $idex)
    {
        // L'identifiant externe est le NDA
        if ($idex->tag == self::getTagNDA()) {
            return "NDA";
        }

        // L'identifiant externe est le NDA
        if ($idex->tag == CSmp::getTagVisitNumber($this->group_id)) {
            return "VN";
        }

        if (CModule::getActive("appFineClient")) {
            if ($idex_type = CAppFineClient::getSpecialIdex($idex)) {
                return $idex_type;
            }
        }

        return null;
    }

    /**
     * Return the service
     *
     * @return CService
     */
    function loadRefService()
    {
        return $this->_ref_service = $this->loadFwdRef("service_id", true);
    }

    /**
     * Return users for sejour
     *
     * @param CMediusers $userCourant utilisateur courant
     * @param string     $date        date de référence
     * @param string     $mode        vue journée ou instantannée
     * @param bool       $with_old    Affiche les affectations de + de 24 heures
     *
     * @return CUserSejour[]
     */
    function loadRefsUserSejour($userCourant = null, $date = null, $mode = null, $with_old = true)
    {
        $group            = CGroups::loadCurrent();
        $type_affectation = CAppUI::conf("soins UserSejour type_affectation", $group);
        $where            = [];
        if ($date && $type_affectation == "segment") {
            if ($date == CMbDT::date() && $mode == "instant") {
                $date_time = CMbDT::dateTime();
                $where[]   = "'$date_time' BETWEEN debut AND fin";
            } else {
                $where[] = "'$date' BETWEEN DATE(debut) AND DATE(fin)";
            }
        }
        if ($with_old == false) {
            $where[] = "fin >= '" . CMbDT::dateTime("-24 hours") . "' OR fin IS NULL";
        }
        $this->clearBackRefCache("user_sejour");
        $this->_ref_users_sejour = $this->loadBackRefs("user_sejour", "debut", null, null, null, null, "", $where);
        foreach ($this->_ref_users_sejour as $_user_sejour) {
            $_user_sejour->loadRefUser();
        }
        $this->_count_users_sejour = count($this->_ref_users_sejour);

        if ($mode == "affectations" && $type_affectation == "segment") {
            $users = [];
            foreach ($this->_ref_users_sejour as $key_user => $user_sejour) {
                if ((!$user_sejour->debut && !$user_sejour->fin) || (CMbDT::date($user_sejour->debut) != $date && $date != CMbDT::date(
                            $user_sejour->fin
                        )) || isset($users[$user_sejour->user_id])) {
                    if (!((!$user_sejour->debut && !$user_sejour->fin) || (CMbDT::date($user_sejour->debut) != $date && $date != CMbDT::date(
                                $user_sejour->fin
                            )))) {
                        $this->_ref_users_sejour[$users[$user_sejour->user_id]]->_affectations[$user_sejour->_id] = $user_sejour;
                    }
                    unset($this->_ref_users_sejour[$key_user]);
                } else {
                    $users[$user_sejour->user_id]                                         = $key_user;
                    $this->_ref_users_sejour[$key_user]->_affectations[$user_sejour->_id] = $user_sejour;
                }
            }
        }

        if (CAppUI::conf("soins UserSejour see_global_users", $group) && $mode != 1 && $mode != 0) {
            return $this->_ref_users_sejour;
        }

        $this->_ref_users_by_type = $this->_ref_group_users_by_type = [
            "infirmiere" => [],
            "AS"         => [],
            "SF"         => [],
            "kine"       => [],
            "prat"       => [],
            "other"      => [],
        ];

        $delete_other = true;
        foreach ($this->_ref_users_sejour as $_user_sejour) {
            $_user = $_user_sejour->_ref_user;
            $name  = "other";
            if ($_user->isInfirmiere()) {
                $name = "infirmiere";
            } elseif ($_user->isAideSoignant()) {
                $name = "AS";
            } elseif ($_user->isSageFemme()) {
                $name = "SF";
            } elseif ($_user->isKine()) {
                $name = "kine";
            } elseif ($_user->isPraticien()) {
                $name = "prat";
            } else {
                $delete_other = false;
            }

            $key                                                = $type_affectation == "segment" ? count(
                $this->_ref_users_by_type[$name]
            ) : $_user->_id;
            $this->_ref_users_by_type[$name][$key]              = $_user_sejour;
            $this->_ref_group_users_by_type[$name][$_user->_id] = $_user_sejour;
        }
        if ($delete_other) {
            unset($this->_ref_users_by_type["other"]);
            unset($this->_ref_group_users_by_type["other"]);
        }

        if ($userCourant && ($userCourant->isInfirmiere() || $userCourant->isAideSoignant() || $userCourant->isSageFemme() || $userCourant->isKine(
                ) || $userCourant->isPraticien())) {
            $serached                = $this->_ref_users_sejour;
            $this->_ref_users_sejour = [];

            foreach ($serached as $user_sejour) {
                $user = $user_sejour->loadRefUser();
                if ($user->_id == $userCourant->_id && $type_affectation == "complet") {
                    $this->_ref_users_sejour[$user_sejour->_id] = $user_sejour;
                } elseif ($user->_id == $userCourant->_id && $type_affectation == "segment") {
                    if ($mode == "instant" || $mode == 0) {
                        $datetime = $date == CMbDT::date() ? CMbDT::dateTime() : $date . " " . CMbDT::time();
                        if ((!$user_sejour->debut && !$user_sejour->fin) || ($user_sejour->debut <= $datetime && $datetime <= $user_sejour->fin)) {
                            $this->_ref_users_sejour[$user_sejour->sejour_id] = $user_sejour;
                        }
                    } else {
                        if ((!$user_sejour->debut && !$user_sejour->fin) || (CMbDT::date($user_sejour->debut) <= $date && $date <= CMbDT::date(
                                    $user_sejour->fin
                                ))) {
                            $this->_ref_users_sejour[$user_sejour->sejour_id] = $user_sejour;
                        }
                    }
                }
            }
        }

        return $this->_ref_users_sejour;
    }

    /**
     * Make a PDF document archive of the sejour (based on soins/print_dossier_soins)
     *
     * @param string $title        File title
     * @param bool   $replace      Replace existing file
     * @param bool   $zip          Zip the file
     * @param bool   $current_date Use current date
     *
     * @return bool
     * @throws CMbException
     */
    function makePDFarchive($title = "Dossier complet", $replace = false, $zip = false, $current_date = true)
    {
        if (!CModule::getActive("soins")) {
            return false;
        }

        $ds = $this->getDS();

        $file_ext  = ($zip) ? '.zip' : '.pdf';
        $file_type = ($zip) ? "application/zip" : "application/pdf";

        $where = [
            'object_class' => $ds->prepare('= ?', $this->_class),
            'object_id'    => $ds->prepare('= ?', $this->_id),
            'file_name'    => $ds->prepareLike("$title%$file_ext"),
            'file_type'    => $ds->prepare('= ?', $file_type),
        ];

        try {
            $file_category_id          = $this->getCategoryArchive();
            $where['file_category_id'] = $ds->prepare('= ?', $file_category_id);
        } catch (CMbException $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
        }

        $file  = new CFile();
        $files = $file->loadList($where, 'file_date DESC');

        if ($files && is_countable($files) && count($files)) {
            $file = reset($files);
        }

        if (!$file->_id || $replace) {
            $file->setObject($this);
            $file->file_name        = $title . $file_ext;
            $file->file_type        = $file_type;
            $file->file_date        = ($current_date) ? CMbDT::dateTime() : $this->entree;
            $file->file_category_id = $this->getCategoryArchive();
        }

        $file->fillFields();
        $file->updateFormFields();
        $file->forceDir();
        $file->author_id = CAppUI::$user->_id;

        $pdf = $this->getPrintDossierSoins(1);

        if ($pdf) {
            if ($zip) {
                // Create ZIP Archive
                $_tmp = tempnam(sys_get_temp_dir(), 'mb_zip_');
                file_put_contents($_tmp, $pdf);
                $_zip_name = "{$_tmp}.zip";

                // Open or overwrite archive
                $zip = new ZipArchive();

                if ($zip->open($_zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    unlink($_tmp);

                    CAppUI::setMsg('Erreur pendant la création de l\'archive', UI_MSG_WARNING);

                    return false;
                }

                // Add (HTML) document to archive
                if ($zip->addFile($_tmp, "$title.pdf") !== true) {
                    $zip->close();

                    unlink($_tmp);
                    unlink($_zip_name);

                    CAppUI::setMsg('Erreur pendant l\'ajout du fichier à l\'archive', UI_MSG_WARNING);

                    return false;
                }

                if ($zip->close() !== true) {
                    unlink($_tmp);
                    unlink($_zip_name);

                    CAppUI::setMsg('Erreur pendant la fermeture de l\'archive', UI_MSG_WARNING);

                    return false;
                }

                $pdf = file_get_contents($_zip_name);
                unlink($_tmp);
                unlink($_zip_name);
            }

            $file->setContent($pdf);

            if ($msg = $file->store()) {
                throw new CMbException($msg);
            }

            return true;
        }

        return false;
    }

    /**
     * Get the category to use
     *
     * @return int
     * @throws CMbException
     *
     */
    protected function getCategoryArchive()
    {
        $category           = new CFilesCategory();
        $category->nom      = "Dossier complet";
        $category->group_id = CGroups::loadCurrent()->_id;
        $category->loadMatchingObjectEsc();

        if (!$category->_id) {
            $category->eligible_file_view = false;
            if ($msg = $category->store()) {
                throw new CMbException($msg);
            }
        }

        return $category->_id;
    }

    function getPrintDossierSoins($show_forms = 0, $date_min = null, $date_max = null, $checkbox_selected = "")
    {
        $query = [
            "m"                   => "soins",
            "a"                   => "print_dossier_soins",
            "sejour_id"           => $this->_id,
            "show_forms"          => $show_forms,
            "dialog"              => 1,
            "offline"             => 1,
            "forms_limit"         => 10000,
            "entree"              => $date_min,
            "sortie"              => $date_max,
            "checkbox_selected"   => $checkbox_selected,
            "_aio"                => 1,
            "_aio_ignore_scripts" => 1,
        ];

        try {
            $content = CApp::fetchQuery($query);
        } catch (Throwable $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
        }

        $content = str_replace("<!DOCTYPE html>", "", $content);
        // Pour cause de texte vertical non fonctionnel dans les constantes (Wkhtmltopdf)
        $content = str_replace('class="ua-msie ua-msie', 'class="ua-webkit ua-webkit', $content);

        // Ajout du class text sur la div qui contient les formulaires
        // Des lignes peuvent être trop longue et wkhtmltopdf met à l'échelle (donc diminution globale de la taille de la police)
        $content = preg_replace("/div id=\"ex-objects-([0-9]+)\"/", "div id=\"ex-objects-$1\" class=\"text\"", $content);

        CHtmlToPDFConverter::init("CWkHtmlToPDFConverter");

        //CHtmlToPDFConverter::init("CPrinceXMLConverter");

        return CHtmlToPDFConverter::convert($content, "a4", "portrait");
    }

    /** Charge le traitement du dossier pmsi (statut)
     *
     * @return CTraitementDossier
     */
    function loadRefTraitementDossier()
    {
        return $this->_ref_traitement_dossier = $this->loadUniqueBackRef("traitement_dossier");
    }

    /**
     * @inheritdoc
     */
    function loadAllDocs($params = [])
    {
        $this->mapDocs($this, $params);
    }

    /**
     * Chargement des appels du séjour
     *
     * @param string $type type de l'appel
     * @param bool   $all  Ne charge pas que le dernier appel
     *
     * @return CAppelSejour|CAppelSejour[]
     */
    function loadRefsAppel($type = null, $all = false)
    {
        if ($type) {
            $where         = [];
            $where["type"] = " = '$type'";
            $backname = "appels_{$type}";
            if (!$all) {
                $resultats = $this->loadBackRefs("appels", "appel_id DESC, datetime DESC", null, null, null, null, $backname, $where);
                if (!count($resultats)) {
                    $backSpec                         = $this->_backSpecs["appels"];
                    $this->_ref_appels_by_type[$type] = new $backSpec->class;
                } else {
                    $this->_ref_appels_by_type[$type] = reset($resultats);
                }
            } else {
                $this->_ref_appels_by_type[$type] = $this->loadBackRefs("appels", "appel_id DESC, datetime DESC", null, null, null, null, $backname, $where);
            }
        } else {
            $this->_ref_appels = $this->loadBackRefs("appels", "appel_id DESC, datetime DESC");
        }
    }

    /**
     * Chargement des appels du séjour d'admission et de sortie
     *
     * @return void
     */
    function loadAppelsInAndOut()
    {
        $this->loadRefsAppel("admission");
        $this->loadRefsAppel("sortie");
    }

    /**
     * Chargement du brancardage courant
     *
     * @return CBrancardage
     */
    function loadCurrBrancardage()
    {
        if (!CModule::getActive("brancardage") || !CAppUI::gconf("brancardage General use_brancardage")) {
            return null;
        }
/*        $ljoin = [];
        $ljoin["brancardage_etape"] = "brancardage_etape.brancardage_id = brancardage.brancardage_id";

        $curr_date = CMbDT::date();
        $where = [];
        $where[] = CBrancardageConditionMakerUtility::makeIncludeOrExcludeByStep("demandeBrancardage") .
            " OR " . CBrancardageConditionMakerUtility::makeIncludeOrExcludeByStep("patientPret");
        $where["brancardage_etape.date"] = " LIKE '$curr_date%'";
        $where[] = CBrancardageConditionMakerUtility::makeIncludeOrExcludeByStep("priseEnCharge", true);

        $brancardages                              = $this->loadBackRefs(
            "context_ref_brancardages",
            "brancardage.brancardage_id DESC",
            "1",
            null,
            $ljoin,
            null,
            "",
            $where
        );*/

        $brancardages = CBrancardageGetUtility::getCurrBrancardagesWithSejour($this);

        if (!count($brancardages)) {
            $brancardages = [new CBrancardage()];
        }

        return $this->_ref_curr_brancardage = reset($brancardages);
    }

    /**
     * @param CPatient[] $patients
     */
    static function checkIncomingSejours($patients, $encours = false)
    {
        $ids    = CMbArray::pluck($patients, "_id");
        $result = array_fill_keys($ids, null);

        $sejour = new CSejour();
        $ds     = $sejour->getDS();

        $where = [
            "patient_id" => $ds->prepareIn($ids),
            "annule"     => "= '0'",
        ];

        $now = CMbDT::dateTime();
        if ($encours) {
            $where["entree_reelle"] = $ds->prepare("<= ?", $now);
            $where["sortie"]        = $ds->prepare("> ?", $now);
        } else {
            $where [] = $ds->prepare("entree_prevue >= ?", $now);
        }

        $request = new CRequest();
        $request->addWhere($where);
        $request->addSelect(["patient_id", "sejour_id"]);

        $results = $ds->loadHashList($request->makeSelect($sejour)) + $result;
        if (!$encours) {
            foreach ($results as $_patient_id => $_sejour_id) {
                $patient = CPatient::findOrFail($_patient_id);
                $sejours = $patient->loadRefsSejours(["annule = 0"]);
                /** @var CSejour */
                $last_sejour = null;
                $type_ssr    = false;
                foreach ($sejours as $_sejour) {
                    if ($_sejour->type == "ssr") {
                        $type_ssr = true;
                    } elseif (!$last_sejour || ($_sejour->entree_prevue > $last_sejour->entree_prevue)) {
                        $last_sejour = $_sejour;
                    }
                }
                if ($last_sejour && $_sejour_id != $last_sejour->_id && !$type_ssr) {
                    $results[$_patient_id] = null;
                }
            }
        }

        return $results;
    }

    /**
     * Gets icon for current patient event
     *
     * @return array
     */
    function getEventIcon()
    {
        $icon = [
            'icon'  => 'far fa-hospital me-event-icon',
            'color' => 'steelblue',
            'title' => CAppUI::tr($this->_class),
        ];

        if (in_array($this->type, self::getTypesSejoursUrgence($this->praticien_id))) {
            $icon['icon']  = 'fa fa-ambulance me-event-icon';
            $icon['title'] = CAppUI::tr("{$this->_class}.type.urg");
        }

        if ($this->grossesse_id) {
            $icon['color'] = 'palevioletred';
            $icon['title'] = in_array($this->type, self::getTypesSejoursUrgence($this->praticien_id)) ? CAppUI::tr(
                'CSejour-title-Emergency with pregnancy'
            ) : CAppUI::tr('CSejour-title-Stay with pregnancy');
        }

        return $icon;
    }

    function loadRefRelance()
    {
        return $this->_ref_relance = $this->loadUniqueBackRef("relance");
    }

    /*
   * Chargement de l'ensemble des anesthésistes des interventions du séjour
   *
   * return CMediusers[]
   */
    function loadListAnesth()
    {
        $this->_ref_list_anesth = [];
        foreach ($this->_ref_operations as $_interv) {
            if (!isset($this->_ref_list_anesth[$_interv->anesth_id])) {
                $_interv->loadRefAnesth()->loadRefFunction();
                $this->_ref_list_anesth[$_interv->anesth_id] = $_interv->loadRefAnesth();
            }
        }

        return $this->_ref_list_anesth;
    }

    /*
   * Chargement des macrocibles du séjoru
   *
   * @param string $type_load type de chargement
   *
   * @return CTransmissionMedicale[]|int
   */
    function loadRefsMacrocible($type_load = "all")
    {
        $ljoin                                            = [];
        $ljoin["category_prescription"]                   = "category_prescription.category_prescription_id = transmission_medicale.object_id AND transmission_medicale.object_class = 'CCategoryPrescription'";
        $where                                            = [];
        $where["transmission_medicale.sejour_id"]         = " = '$this->_id'";
        $where["transmission_medicale.cancellation_date"] = " IS NULL";
        $transmission                                     = new CTransmissionMedicale();
        if ($type_load != "all") {
            return $this->_ref_macrocibles = $transmission->loadIds(
                $where,
                "date DESC, transmission_medicale_id DESC",
                null,
                "transmission_medicale_id",
                $ljoin
            );
        }
        $this->_ref_macrocibles = $transmission->loadList(
            $where,
            "date DESC, transmission_medicale_id DESC",
            null,
            "transmission_medicale_id",
            $ljoin
        );
        foreach ($this->_ref_macrocibles as $_macrocible) {
            $_macrocible->loadTargetObject();
            $_macrocible->loadRefUser();
            $_macrocible->loadRefCible();
        }

        return $this->_ref_macrocibles;
    }

    /**
     * Charge les stocks du séjour
     *
     * @return CStockSejour[]
     */
    function loadRefsStocksSejour()
    {
        return $this->_ref_stock_sejour = $this->loadBackRefs('stock_sejour');
    }

    /**
     * Charge les événements SSR du séjour
     *
     * @return CEvenementSSR[]
     */
    function loadRefsEvtsSSRSejour($where = [], $ljoin = [])
    {
        return $this->_ref_evts_ssr_sejour = $this->loadBackRefs('evenements_ssr', "debut", null, null, $ljoin, null, "", $where);
    }

    /**
     * Recupérer les ids du type de séjour dans les préférences utilisateurs
     *
     * @param array $type_sejours_ids array
     *
     * @return array
     */
    static function getTypeSejourIdsPref($type_sejours_ids = [])
    {
        // Détection du changement d'établissement
        $group_id = CView::get("g", "str");

        if (!$type_sejours_ids || $group_id) {
            $group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;

            $pref_type_sejours_ids = json_decode(CAppUI::pref("sejours_ids_admissions"));

            // Si la préférence existe, alors on la charge
            if (isset($pref_type_sejours_ids->{"g$group_id"})) {
                $type_sejours_ids = $pref_type_sejours_ids->{"g$group_id"};
                $type_sejours_ids = explode("|", $type_sejours_ids);
                CMbArray::removeValue("", $type_sejours_ids);
            }
        }

        if (is_array($type_sejours_ids)) {
            CMbArray::removeValue("", $type_sejours_ids);
        }

        CView::setSession("sejours_ids", $type_sejours_ids);
        CView::checkin();

        return $type_sejours_ids;
    }

    /**
     * Calcul de la couleur suivant les constantes médicales
     *
     * @param string $date     date
     * @param string $hour_min time min
     * @param string $hour_max time max
     *
     * @return string
     */
    function calculConstantesMedicales($date, $hour_min = "00:00:00", $hour_max = "23:59:59")
    {
        $constante = new CConstantesMedicales();
        $where     = [];

        if ($hour_min > $hour_max) {
            [$hour_min, $hour_max] = [$hour_max, $hour_min];
        }

        $where["context_class"] = " = '$this->_class'";
        $where["context_id"]    = " = $this->_id";
        $where["datetime"]      = "BETWEEN '$date $hour_min' AND '$date $hour_max'";

        return $constante->countList($where) ? "green" : "grey";
    }

    /**
     * Get the color prestations
     *
     * @return void
     */
    public function getColorPrestations()
    {
        $liaisons                = $this->loadRefItemsLiaisons();
        $this->_color_prestation = "grey";
        $this->_title_prestation = CAppUI::tr("CSejour-Prestations");

        /** @var CItemPrestation $items */
        $items          = CStoredObject::massLoadFwdRef($liaisons, "item_souhait_id");
        /** @var CItemPrestation $items_realises */
        $items_realises = CStoredObject::massLoadFwdRef($liaisons, "item_realise_id");

        foreach ($liaisons as $_liaison) {
            $item         = $_liaison->loadRefItem();
            $item_realize = $_liaison->loadRefItemRealise();

            $is_item_ponctuelle         = $item->object_class == CItemPrestation::OBJECT_CLASS_PRESTATION_PONCTUELLE;
            $is_item_realize_ponctuelle = $item_realize->object_class == CItemPrestation::OBJECT_CLASS_PRESTATION_PONCTUELLE;

            if ($is_item_ponctuelle || $is_item_realize_ponctuelle) {
                continue;
            }

            if (!$_liaison->item_souhait_id && !$_liaison->item_realise_id) {
                $this->_color_prestation = "grey";
                $this->_title_prestation = CAppUI::tr("CSejour-msg-No service desired or performed");
            } elseif ($_liaison->item_souhait_id && !$_liaison->item_realise_id) {
                $this->_color_prestation = "darkorange";
                $this->_title_prestation = CAppUI::tr('CSejour-msg-Wish % s - Not fulfilled', $item->nom);
            } else {
                $this->_color_prestation = ($item->_id == $item_realize->_id) ? "green" : "red";
                $this->_title_prestation = CAppUI::tr(
                    'CSejour-msg-Wish % s - Fulfilled %s',
                    $item->nom,
                    $item_realize->nom
                );
            }
        }
    }

    /**
     * Get the color medicine
     *
     * @return void
     */
    public function getColorMedicineByStep()
    {
        $prescription = $this->_ref_prescription_sejour;

        $paires_heures = [
            ["admission", "bloc"],
            ["bloc", "bloc_fin"],
            ["sspi", "sspi_fin"],
            ["sspi_fin", "sortie"],
        ];

        foreach ($paires_heures as $_paire_heure) {
            if ($this->_ambu_time_phase) {
                $hour_min = $this->_ambu_time_phase[$_paire_heure[0]];
                $hour_max = $this->_ambu_time_phase[$_paire_heure[1]];

                $color = "grey";
                if ($prescription->_id) {
                    $color = $prescription->calculPlanife($hour_min, $hour_max);
                }

                $this->_color_prescription[$_paire_heure[0]] = $color;
            }
        }
    }

    /**
     * Charge la chambre nettoyée
     *
     * @return CBedCleanup[]
     */
    public function loadRefsBedCleanup()
    {
        return $this->_refs_bed_cleanup = $this->loadBackRefs("cleanup_sejour");
    }

    /**
     * Charge les transports du séjour
     *
     * @param array where
     *
     * @return CTransport[]
     */
    public function loadRefsTransports($where = [])
    {
        $order   = "transport_id DESC, datetime DESC";
        $where[] = "transport.statut <> 'prescribed'";

        return $this->_refs_transports = $this->loadBackRefs("transports", $order, null, null, null, null, "", $where);
    }

    /**
     * Charge les dossiers d'addictologie
     *
     * @return CDossierAddictologie
     */
    public function loadRefDossierAddictologie()
    {
        return $this->_ref_dossier_addictologie = $this->loadUniqueBackRef("dossiers_addictologie");
    }

    public function loadRefsDossiersAnesth(): array
    {
        return $this->_refs_dossiers_anesth = $this->loadBackRefs('consultations_anesths');
    }

    /**
     * Charge le dossier complete de la consultation pour AppFineClient
     *
     * @param string $type type
     *
     * @return CAppFineClientFolderLiaison
     */
    // TODO pour l'instant on prend tout, mais que le type "pread" est fonctionnel
    function loadRefFolderLiaison($type = "pread")
    {
        $folders_liaison = $this->loadBackRefs("folder_liaison", null, null, null, null, null, "folder_liaison_$type", ["type" => " = '$type' "]);

        if (count($folders_liaison) == 0) {
            $folders_liaison = [new CAppFineClientFolderLiaison()];
        }

        return $this->_ref_appfine_client_folder = reset($folders_liaison);
    }

    /**
     * Charge les relances des dossiers AppFine
     *
     * @param string $type type
     *
     * @return CAppFineClientFolderLiaison
     */
    function loadRefsFoldersRelaunch()
    {
        return $this->_refs_appfine_client_folders_relaunch = $this->loadBackRefs("folder_relaunch");
    }

    /**
     * Charge les relances des dossiers AppFine par type de dossier
     *
     * @param string $type type
     *
     * @return CAppFineClientFolderLiaison
     */
    function loadRefsFoldersRelaunchByType()
    {
        $pread = $this->loadBackRefs(
            "folder_relaunch",
            'relaunch_date ASC',
            null,
            null,
            null,
            null,
            'folder_relaunch_pread',
            ['type' => " = 'pread'"]
        );

        $preop = $this->loadBackRefs(
            "folder_relaunch",
            'relaunch_date ASC',
            null,
            null,
            null,
            null,
            'folder_relaunch_preop',
            ['type' => " = 'preop'"]
        );

        $postop = $this->loadBackRefs(
            "folder_relaunch",
            'relaunch_date ASC',
            null,
            null,
            null,
            null,
            'folder_relaunch_postop',
            ['type' => " = 'postop'"]
        );

        $consult = $this->loadBackRefs(
            "folder_relaunch",
            'relaunch_date ASC',
            null,
            null,
            null,
            null,
            'folder_relaunch_consult',
            ['type' => " = 'consult'"]
        );

        return $this->addToStore(
            "count_appFine_folders_relaunch",
            [
                'pread'   => $pread,
                'preop'   => $preop,
                'postop'  => $postop,
                'consult' => $consult,
            ]
        );
    }

    /**
     * Charge le dernier RHS du séjour
     *
     * @return CRHS
     */
    function loadRefLastRhs()
    {
        $rhss = $this->loadBackRefs("rhss", "date_monday ASC");

        return $this->_ref_last_rhs = end($rhss);
    }

    /**
     * Récupérer la couleur de complétude du dernier formulaire du séjour
     *
     * @param string $event_name event name
     *
     * @return string|null
     */
    function getColorCompletenessLastForm($event_name): ?string
    {
        $color        = null;
        $completeness = CExClass::getLatestCompletenessByEvent($this, $event_name);

        foreach ($completeness as $_completeness) {
            if ($_completeness["completeness"] == 'all') {
                $color = "green";
            } elseif ($_completeness["completeness"] == 'some') {
                $color = "orange";
            } elseif ($_completeness["completeness"] == 'none') {
                $color = "red";
            }
        }

        return $this->_completeness_color_form = $color;
    }

    /**
     * @inheritdoc
     */
    function getRGPDContext()
    {
        return $this->loadRefPatient();
    }

    /**
     * @inheritdoc
     */
    function checkTrigger($first_store = false)
    {
        return ($first_store || ($this->fieldModified('entree') || $this->fieldModified('sortie') || $this->fieldModified('patient_id')));
    }

    /**
     * @inheritDoc
     */
    public function getGroupID()
    {
        $group = $this->loadRefEtablissement();

        if ($group && $group->_id) {
            return $group->_id;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function triggerEvent()
    {
        $context = $this->getRGPDContext();

        if (!$context || !$context->_id) {
            throw new CMbException('CRGPDConsent-error-Unable to find context');
        }

        $manager = new CRGPDManager($this->getGroupID());
        $manager->askConsentFor($context);
    }

    /**
     * Chargement du consentement esatis
     *
     * @return CEsatisConsent
     */
    function loadRefEsatisConsent()
    {
        return $this->_ref_esatis_consent = $this->loadUniqueBackRef("esatis");
    }

    public function loadPatientBanner()
    {
        $this->loadRefCurrAffectation();
        $this->getCovidDiag();

        $dossier_medical_sejour = $this->loadRefDossierMedical();
        if ($dossier_medical_sejour->_id) {
            $dossier_medical_sejour->loadRefsAllergies();
            $dossier_medical_sejour->loadRefsAntecedents();
            $dossier_medical_sejour->countAntecedents(false);
            $dossier_medical_sejour->countAllergies();
        }

        $patient = $this->_ref_patient;

        $patient->loadRefPhotoIdentite();
        $patient->updateBMRBHReStatus($this);
        $dossier_medical_patient = $patient->_ref_dossier_medical ?: $patient->loadRefDossierMedical();
        $patient->loadRefLatestConstantes(null, ["poids", "taille", "clair_creatinine"]);
        if ($dossier_medical_patient->_id) {
            $dossier_medical_patient->loadRefsAllergies();
            $dossier_medical_patient->loadRefsAntecedents();
            $dossier_medical_patient->countAntecedents(false);
            $dossier_medical_patient->countAllergies();
        }

        /* Suppression des antecedents du dossier medical du patients présent dans le dossier medical du sejour */
        if ($dossier_medical_patient->_id && $dossier_medical_sejour->_id) {
            CDossierMedical::cleanAntecedentsSignificatifs($dossier_medical_sejour, $dossier_medical_patient);
        }
    }

    /**
     * @inheritdoc
     */
    function loadRefsFiles($where = [], bool $with_cancelled = true)
    {
        parent::loadRefsFiles($where, $with_cancelled);

        // Récupérer les bons de transport
        if (CModule::getActive("transport")) {
            foreach ($this->loadRefsTransports() as $_transport) {
                $_transport->loadRefsFiles($where, $with_cancelled);
                $this->_ref_files = CMbArray::mergeKeys($this->_ref_files, $_transport->_ref_files);
            }
        }

        return count($this->_ref_files);
    }

    /**
     * Charge les arrêts de travail du séjour
     *
     * @param array where
     *
     * @return CAvisArretTravail[]
     */
    public function loadRefsAvisArretsTravail($where = [])
    {
        $order = "debut DESC";

        return $this->_refs_avis_arrets_travail = $this->loadBackRefs("arret_travail", $order, null, null, null, null, "", $where);
    }

    /**
     * Charge l'accident de travail du séjour
     *
     * @return CAccidentTravail
     */
    function loadRefAccidentTravail()
    {
        return $this->_ref_accident_travail = $this->loadUniqueBackRef("accident_travail");
    }

    /**
     * Vérifie si un séjour est considéré
     * comme terminé concernant le codage des actes
     *
     * @return bool
     */
    public function isCoded(): bool
    {
        $this->_coded = false;

        $config      = CAppUI::gconf('dPsalleOp COperation modif_actes');
        $worked_days = CAppUI::gconf('dPsalleOp COperation modif_actes_worked_days') == '1' ? true : false;
        if (
            ($config == "oneday" && $this->sortie_reelle
                && CMbDT::daysRelative($this->sortie_reelle, CMbDT::date() . ' 00:00:00', $worked_days) >= 1)
            || ($config == '48h' && $this->sortie_reelle
                && CMbDT::daysRelative(
                    CMbDT::dateTime('+48 hours', $this->sortie_reelle),
                    CMbDT::dateTime(),
                    $worked_days
                ) >= 2)
        ) {
            $this->_coded         = true;
            $this->_coded_message = "config-dPsalleOp-COperation-modif_actes.$config";
        } elseif (strpos($config, 'sortie_sejour') !== false && $this->sortie_reelle) {
            $days      = CMbDT::daysRelative($this->sortie_reelle, CMbDT::dateTime(), $worked_days);
            $threshold = null;
            $config    = explode('|', $config);
            if (array_key_exists(1, $config) && $this->type == 'ambu') {
                $threshold = $config[1];
            } elseif (array_key_exists(2, $config) && $this->type == 'comp') {
                $threshold = $config[2];
            }

            if ($days > $threshold) {
                $this->_coded         = true;
                $this->_coded_message = 'config-dPsalleOp-COperation-modif_actes.sortie_sejour';
            }
        } elseif ($config == 'facturation_web100T' && CModule::getActive('web100T') && $this->sortie_reelle) {
            $this->_coded = CWeb100TSejour::isSejourBilled($this);
            if ($this->_coded) {
                $this->_coded_message = 'config-dPsalleOp-COperation-modif_actes.facturation_web100T';
            }
        }

        return $this->_coded;
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        // Check if prat is in the export list
        $prats = (!$prat_ids || in_array($this->praticien_id, $prat_ids));

        // Check if the dates are ok for export
        $dates = false;
        if (!$date_min && !$date_max) {
            $dates = true;
        } elseif ($date_min && $date_max) {
            $dates = (bool)($date_min <= $this->entree && $date_max >= $this->entree);
        } elseif ($date_max) {
            $dates = (bool)($date_max >= $this->entree);
        } elseif ($date_min) {
            $dates = (bool)($date_min <= $this->entree);
        }

        return $prats && $dates;
    }

    /**
     * Set the execution date of the acts to the entree_reelle
     *
     * @return string|null
     */
    protected function updateDateActes()
    {
        $this->loadRefsActes();

        foreach ($this->_ref_actes as $act) {
            if ($this->entree_reelle > $act->execution) {
                $act->execution = $this->entree_reelle;
                if ($msg = $act->store()) {
                    return $msg;
                }
            }
        }
    }

    /**
     * Get the patient status in ambu
     *
     * @return array
     */
    public function getPatientStatus()
    {
        $now = CMBDT::dateTime();

        // état du patient
        $patient_statut = [
            "etat"       => "patient-ambu-attente",
            "etatTexte"  => "module-ambu-Waiting for admission",
            "presence"   => "patient-ambu-absent",
            "non_sortie" => "patient-ambu-non-sorti",
            "background" => "grey",
        ];

        if ($this->entree_reelle && !$this->sortie_reelle) {
            $patient_statut["presence"] = "patient-ambu-present";
        }

        if ($this->entree_reelle && !$this->pec_service) {
            $patient_statut["etat"]       = "patient-ambu-preop";
            $patient_statut["background"] = "blueviolet";
            $patient_statut["etatTexte"]  = "mod-ambu-Admitted";
        }

        if ($this->entree_reelle && $this->pec_service) {
            $patient_statut["etat"]       = "patient-ambu-preop";
            $patient_statut["background"] = "blueviolet";
            $patient_statut["etatTexte"]  = "mod-ambu-In preparation preop";
        }

        if ($this->sortie_reelle && $this->sortie_reelle < $now) {
            $patient_statut["non_sortie"] = "";
            $patient_statut["etat"]       = "patient-ambu-sorti";
            $patient_statut["background"] = "steelblue";
            $patient_statut["etatTexte"]  = "mod-ambu-Patient out";
        }

        return $this->_patient_status_ambu = $patient_statut;
    }

    /**
     * Obtenir les differents statut de l'étape Admission du module Ambu
     *
     * @return array
     */
    public function getStatusAdmission()
    {
        $colors = COperation::getColorsStepAndSubStep();

        //*********** admission ***********
        // - etape
        if ($this->entree_reelle && $this->pec_service) {
            $statut_admission = $colors['step']['blue'];
        } elseif ($this->entree_reelle && !$this->pec_service) {
            $statut_admission = $colors['step']['purple'];
        } else {
            $statut_admission = $colors['step']['grey'];
        }
        // -- sous-etapes
        // --- entrée séjour
        if ($this->entree_reelle) {
            $statut_entree = $colors['substep']['green'];
        } else {
            $statut_entree = $colors['step']['grey'];
        }

        // --- Dossier entrée préparée
        if ($this->entree_preparee) {
            $statut_entree_preparee = $colors['substep']['green'];
        } elseif ($this->entree_reelle) {
            $statut_entree_preparee = $colors['substep']['orange'];
        } else {
            $statut_entree_preparee = $colors['step']['grey'];
        }

        // --- Pec accueil
        if ($this->pec_accueil) {
            $statut_pec_accueil = $colors['substep']['green'];
        } else {
            $statut_pec_accueil = $colors['step']['grey'];
        }

        $this->getColorPrestations();

        $sous_etapes_admission = [
            "entree"           => $statut_entree,
            "formulaire"       => "",
            "prestations"      => $this->_color_prestation,
            "prestation_title" => $this->_title_prestation,
            "entree_preparee"  => $statut_entree_preparee,
            "pec_accueil"      => $statut_pec_accueil,
        ];
        //********************************

        $admission = [
            'step'    => [
                "statut" => $statut_admission,
            ],
            'substep' => $sous_etapes_admission,
        ];

        return $admission;
    }

    /**
     * Obtenir les differents statut de l'étape Preop
     *
     * @return array
     */
    public function getStatusPreop()
    {
        $colors = COperation::getColorsStepAndSubStep();

        //*********** preop ***********
        // - etape
        if ($this->pec_service) {
            $statut_preop = $colors['step']['purple'];
        } else {
            $statut_preop = $colors['step']['grey'];
        }

        // -- sous-etapes
        // --- Pec service
        if ($this->pec_service) {
            $statut_pec_service = $colors['substep']['green'];
        } else {
            $statut_pec_service = $colors['step']['grey'];
        }

        $sous_etapes_preop = [
            "pec_service" => $statut_pec_service,
            "formulaire"  => "",
        ];
        //********************************

        $preop = [
            'step'    => [
                "statut" => $statut_preop,
            ],
            'substep' => $sous_etapes_preop,
        ];

        return $preop;
    }

    /**
     * Obtenir les differents statut de l'étape Postop
     *
     * @return array
     */
    public function getStatusPostop()
    {
        $colors = COperation::getColorsStepAndSubStep();

        //*********** postop ***********
        // - etape
        if ($this->confirme_user_id) {
            $statut_postop = $colors['step']['blue'];
        } else {
            $statut_postop = $colors['step']['grey'];
        }

        // -- sous-etapes
        // --- sortie autorisation
        if ($this->confirme_user_id) {
            $statut_sortie_autorisation = $colors['substep']['green'];
        } elseif (!$this->confirme_user_id && $this->sortie_reelle) {
            $statut_sortie_autorisation = $colors['substep']['orange'];
        } else {
            $statut_sortie_autorisation = $colors['step']['grey'];
        }

        $sous_etapes_postop = [
            "retour_service"      => "",
            "formulaire"          => "",
            "medicaments"         => "",
            "constantes"          => "",
            "sortie_autorisation" => $statut_sortie_autorisation,
        ];
        //********************************

        $postop = [
            'step'    => [
                "statut" => $statut_postop,
            ],
            'substep' => $sous_etapes_postop,
        ];

        return $postop;
    }

    /**
     * Obtenir les differents statut de l'étape Sortie
     *
     * @return array
     */
    public function getStatusSortie()
    {
        $colors = COperation::getColorsStepAndSubStep();

        //*********** sortie ***********
        // - etape
        if ($this->sortie_reelle) {
            $statut_sortie = $colors['step']['blue'];
        } elseif ($this->confirme_user_id && !$this->sortie_reelle) {
            $statut_sortie = $colors['step']['purple'];
        } else {
            $statut_sortie = $colors['step']['grey'];
        }

        // -- sous-etapes
        // --- Dossier entrée préparée
        if ($this->sortie_preparee) {
            $statut_sortie_preparee = $colors['substep']['green'];
        } else {
            $statut_sortie_preparee = $colors['step']['grey'];
        }

        // --- Sortie séjour
        if ($this->sortie_reelle) {
            $statut_sortie_reelle = $colors['substep']['green'];
        } else {
            $statut_sortie_reelle = $colors['step']['grey'];
        }

        $sous_etapes_sortie = [
            "formulaire"      => "",
            "sortie_preparee" => $statut_sortie_preparee,
            "sortie"          => $statut_sortie_reelle,
        ];
        //********************************

        $sortie = [
            'step'    => [
                "statut" => $statut_sortie,
            ],
            'substep' => $sous_etapes_sortie,
        ];

        return $sortie;
    }

    /**
     * Obtenir le statut et les retards des différentes étapes
     *
     * @return array
     */
    public function getStatusPhase()
    {
        //Prestations
        $this->getColorPrestations();

        // Médicaments
        $this->getColorMedicineByStep();

        // Tableau des statuts de chaque étape et sous-étapes
        $admission = $this->getStatusAdmission();
        $preop     = $this->getStatusPreop();
        $postop    = $this->getStatusPostop();
        $sortie    = $this->getStatusSortie();

        $delay_status = [
            "admission"   => [
                "statut"      => $admission['step']['statut'],
                "sous_etapes" => $admission['substep'],
            ],
            "preop"       => [
                "statut"      => $preop['step']['statut'],
                "sous_etapes" => $preop['substep'],
            ],
            "branc_first" => [
                "statut"      => "",
                "delay"       => "",
                "sous_etapes" => "",
            ],
            "branc_last"  => [
                "statut"      => "",
                "delay"       => "",
                "sous_etapes" => "",
            ],
            "postop"      => [
                "statut"      => $postop['step']['statut'],
                "sous_etapes" => $postop['substep'],
            ],
            "sortie"      => [
                "statut"      => $sortie['step']['statut'],
                "sous_etapes" => $sortie['substep'],
            ],
        ];

        return $this->_ambu_statut_phase = $delay_status;
    }

    public function printPrestations($liaisons_by_id, $dates)
    {
        foreach ($liaisons_by_id as $_liaison) {
            $_liaison->loadRefItemRealise();
            $_liaison->loadRefPrestation();
        }

        $smarty = new CSmartyDP('modules/dPhospi');
        $smarty->assign('liaisons', $liaisons_by_id);
        $smarty->assign('dates', $dates);
        $smarty->assign('only_souhait', 0);

        return preg_replace('`([\\n\\r])`', '', $smarty->fetch('print_prestations_table.tpl'));
    }

    /**
     * @param int $group_id Group ID to get conf from
     *
     * @return mixed
     */
    public static function getAllowMerge($group_id = null)
    {
        return CAppUI::gconf("dPplanningOp CSejour allow_fusion_sejour", $group_id);
    }

    /**
     * Charge les gestions de passage
     *
     * @return CPassageGestion[]
     */
    public function loadRefsPassagesGestion()
    {
        return $this->_ref_passages_gestion = $this->loadBackRefs("passages_gestion", "date_debut");
    }

    /**
     * Charge les passages
     *
     * @return CPassage[]
     */
    public function loadRefsPassages()
    {
        return $this->_ref_passages = $this->loadBackRefs("passages", "date");
    }

    /**
     * Charge les absences de patient
     *
     * @return CAbsencePatient[]
     */
    public function loadRefsAbsencesPatient()
    {
        return $this->_ref_absences_patient = $this->loadBackRefs("absences_patient", "debut");
    }

    /**
     * Charge les autorisations de sortie du patient
     *
     * @return CAutorisationPermission[]
     */
    public function loadRefsAutorisationsPermission()
    {
        return $this->_ref_autorisations_permission = $this->loadBackRefs("autorisations_permission", "debut DESC");
    }

    /**
     * Charge la dernière autorisation de sortie du patient
     *
     * @return CAutorisationPermission[]
     */
    public function loadLastAutorisationPermission()
    {
        $autorisations = $this->loadRefsAutorisationsPermission();

        return $this->_ref_last_autorisation_permission = reset($autorisations);
    }

    /**
     * Vérifie si le patient est en permission (service externe)
     *
     * @return boolean
     */
    public function isInPermission()
    {
        return $this->_in_permission = $this->loadRefCurrAffectation()->loadRefService()->externe === "1";
    }

    /**
     * @inheritDoc
     */
    function loadExternalIdentifiers($group_id = null)
    {
        // Iconographie de AppFine
        if (CModule::getActive("appFineClient")) {
            CAppFineClient::loadIdexConsult($this, $group_id);
        }

        // Iconographie de SIH Cabinet
        if (CModule::getActive("oxSIHCabinet")) {
            CSIHCabinet::loadIdex($this);
        }
    }

    function getCovidDiag()
    {
        $pattern = "/U07\.?[0-9]/";

        if (preg_match($pattern, $this->DP)) {
            $this->_covid_diag = CCodeCIM10::get($this->DP);
        } elseif (preg_match($pattern, $this->DR ?? '')) {
            $this->_covid_diag = CCodeCIM10::get($this->DR);
        } else {
            foreach ($this->loadDiagnosticsAssocies() as $_diag) {
                if (preg_match($pattern, $_diag)) {
                    $this->_covid_diag = CCodeCIM10::get(str_replace(".", "", $_diag));
                }
            }
        }

        if ($this->_covid_diag) {
            if (!preg_match("/Covid-19/", $this->_covid_diag->libelle)) {
                $this->_covid_diag->libelle = "Covid-19 : " . $this->_covid_diag->libelle;
            }
        }
    }

    function loadRefRedons($with_inactive = false)
    {
        $redons = $this->loadBackRefs("redons");

        if (!$with_inactive) {
            foreach ($redons as $_redon) {
                if (!$_redon->actif) {
                    unset($redons[$_redon->_id]);
                }
            }
        }

        $redons_cste = CMbArray::pluck($redons, "constante_medicale");

        $ranks_cstes = CConstantesMedicales::getRanksFor(null, $this);

        foreach (CRedon::$list as $_redon_class => $_redons) {
            foreach ($_redons as $_cste) {
                if ($ranks_cstes[$_cste] < 0) {
                    continue;
                }

                if (!isset($this->_ref_redons_by_redon[$_redon_class])) {
                    $this->_ref_redons_by_redon[$_redon_class] = [];
                }

                if ($key_cste = array_search($_cste, $redons_cste)) {
                    $_redon                          = $redons[$key_cste];
                    $this->_ref_redons[$_redon->_id] = $_redon;
                } else {
                    $_redon                     = new CRedon();
                    $_redon->sejour_id          = $this->_id;
                    $_redon->constante_medicale = $_cste;
                    $_redon->updateFormFields();
                }
                $this->_ref_redons_by_redon[$_redon_class][$_cste] = $_redon;
            }
        }

        return $this->_ref_redons;
    }

    public static function getTypesSejoursUrgence(int $user_id = null): array
    {
        $cache = LayeredCache::getCache(LayeredCache::INNER);

        $key = 'types_sejours_urg' . $user_id;

        if ($types_sejours = $cache->get($key)) {
            return $types_sejours;
        }

        $is_urgentiste = null;

        if ($user_id) {
            $user          = CMediusers::get($user_id);
            $group         = CGroups::loadCurrent();
            $function_ids  = array_merge(
                [$user->function_id],
                CMbArray::pluck($user->loadRefsSecondaryFunctions($group->_id), '_id')
            );
            $is_urgentiste = in_array($group->service_urgences_id, $function_ids);
        }

        $types_sejours = ((!$user_id || $is_urgentiste) && (CAppUI::gconf("dPurgences CRPU type_sejour") === "urg_consult")) ?
            ["urg", "consult"] : ["urg"];

        $cache->set($key, $types_sejours);

        return $types_sejours;
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchSejour($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * Récupération de la ressource Service
     *
     * @return Item
     * @throws ApiException
     */
    public function getResourceService(): ?Item
    {
        $service = $this->loadRefService();
        if (!$service->_id) {
            return null;
        }

        return new Item($service);
    }

    /**
     * Récupération de la ressource Patient
     *
     * @return Item
     * @throws ApiException
     */
    public function getResourcePatient(): ?Item
    {
        if (!$patient = $this->loadRefPatient()) {
            return null;
        }

        return new Item($patient);
    }

    /**
     * Récupération de la ressource Praticien
     *
     * @return Item
     * @throws ApiException
     */
    public function getResourcePraticien(): ?Item
    {
        if (!$praticien = $this->loadRefPraticien()) {
            return null;
        }
        $res = new Item($praticien);
        $res->setType(CMediusers::RESOURCE_TYPE_PRATICIEN);

        return $res;
    }

    /**
     * Récupération des ressources Actes CCAM
     *
     * @return Collection|null
     * @throws \Exception
     */
    public function getResourceActesCcam(): ?Collection
    {
        if (!$actes_ccam = $this->loadRefsActesCCAM()) {
            return null;
        }

        return new Collection($actes_ccam);
    }

    /**
     * Getter to fields_etiq variale
     *
     * @return array
     * @throws Exception
     */
    public static function getFieldsEtiq()
    {
        $fields_etiq = self::$fields_etiq;

        if (CModule::getActive("barcodeDoc") && CAppUI::gconf("barcodeDoc general module_actif")) {
            $fields_etiq[] = "CODE BARRE NDOS AVEC PREFIXE";
        }

        return $fields_etiq;
    }

    /**
     * @return string|null
     */
    public function loadEpisodeSoin(): ?string
    {
        $idex               = new CIdSante400();
        $idex->object_class = $this->_class;
        $idex->object_id    = $this->_id;
        $idex->tag          = "CODE_EDS";
        $idex->loadMatchingObjectEsc();
        if ($idex->_id) {
            return $this->_code_EDS = $idex->id400;
        }

        return $this->_code_EDS = null;
    }

    /**
     * @throws Exception
     */
    public function manageEdsIdex(): void
    {
        $idex               = new CIdSante400();
        $idex->object_class = $this->_class;
        $idex->object_id    = $this->_id;
        $idex->tag          = "CODE_EDS";
        $idex->loadMatchingObjectEsc();
        if ($this->_code_EDS) {
            $idex->id400 = $this->_code_EDS;
            $idex->store();
        } elseif ($idex->_id) {
            $idex->delete();
        }
    }

    public static function getLibelles($object, $parent = null)
    {
        $libelle        = $object->_view . ($object->libelle ? (" - " . $object->libelle) : "");
        $libelle_parent = "";
        if ($object instanceof COperation) {
            $libelle .= " - " . $object->loadRefChir()->_view;
            if ($object->salle_id) {
                $libelle .= " - " . $object->loadRefSalle()->_view;
            }
            $libelle        .= " - " . CMbDT::transform(null, $object->temp_operation, "%Hh%M");

            if ($parent) {
                $libelle_parent = $parent->_view . ($parent->libelle ? " - " . $parent->libelle : "");
                $libelle_parent .= " - " . $parent->loadRefPraticien()->_view;
            }
        } else {
            $libelle .= " - " . $object->loadRefPraticien()->_view;
        }

        return [$libelle, $libelle_parent];
    }

    /**
     * Compute the datetime for the first intervention
     *
     * @param string $backname Name of collection to check the operations
     *
     * @return string
     */
    public function getPassageBloc(string $backname = 'operations'): ?string
    {
        if (isset($this->_back[$backname])) {
            if (count($this->_ref_operations)) {
                $cancelled_status = CMbArray::pluck($this->_ref_operations, 'annulee');
                $datetimes = CMbArray::pluck($this->_ref_operations, '_datetime');
                asort($datetimes);

                foreach ($datetimes as $_operation_id => $_datetime) {
                    if (!$cancelled_status[$_operation_id]) {
                        $this->_ref_first_operation = $this->_ref_operations[$_operation_id];
                        $this->_passage_bloc = $_datetime;
                        break;
                    }
                }
            }
            return $this->_passage_bloc;
        }

        $this->loadRefFirstOperation();
        if ($this->_ref_first_operation->_id && !$this->_ref_first_operation->annulee) {
            $this->_passage_bloc = $this->_ref_first_operation->_datetime;
        }

        return $this->_passage_bloc;
    }

    /**
     * @return array|CItemPrestation[]
     */
    public function getPrestationsForStats(): array
    {
        $this->_ref_prestations = [];
        $liaisons_j             = $dates = $liaisons_by_id = [];
        self::getIntervallesPrestations($liaisons_j, $dates, $liaisons_by_id);

        // Calcul du niveau de réalisation (_quantite)
        foreach ($liaisons_j as $prestation_id => $_liaisons) {
            foreach ($_liaisons as $date => $_liaison) {
                $_item_souhait = $_liaison->loadRefItem();
                $_item_realise = $_liaison->loadRefItemRealise();
                $sous_item     = $_liaison->loadRefSousItem();

                $item_facture  = $_item_realise;
                $dates_liaison = $dates[$_liaison->_id];
                $quantite      = 1 + CMbDT::daysRelative($dates_liaison["debut"], $dates_liaison["fin"]);

                $this->_ref_prestations[$date][] = [
                    "quantite"          => $quantite,
                    "item"              => $item_facture,
                    "liaison"           => $_liaison,
                    "date_fin"          => $dates_liaison["fin"],
                    "souhait"           => $_item_souhait,
                    "realise"           => $_item_realise,
                    "sous_item_facture" => $sous_item->item_prestation_id == $item_facture->_id ? $sous_item : "",
                ];
            }
        }

        return $this->_ref_prestations;
    }

    public static function getSampleObject($class = null, bool $only_notNull = true): CModelObject
    {
        /** @var CSejour $sejour */
        $sejour = parent::getSampleObject($class, $only_notNull);

        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime('+1 HOURS');
        $sejour->libelle       = uniqid();

        return $sejour;
    }
}
