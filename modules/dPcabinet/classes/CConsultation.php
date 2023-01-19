<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use DateTime;
use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientFolderLiaison;
use Ox\AppFine\Client\CAppFineClientObjectReceived;
use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\AppFine\Client\CAppFineClientRelaunchFolder;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Admin\Rgpd\IRGPDEvent;
use Ox\Mediboard\Ameli\CAvisArretTravail;
use Ox\Mediboard\Brancardage\CBrancardage;
use Ox\Mediboard\Brancardage\Utilities\CBrancardageConditionMakerUtility;
use Ox\Mediboard\Cabinet\Utilities\ConsultationRestrictionUtility;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Doctolib\CDoctolib;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacturable;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Fse\CFSE;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CSuiviGrossesse;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Notifications\CNotification;
use Ox\Mediboard\Notifications\CNotificationEvent;
use Ox\Mediboard\OxPyxvital\CPyxvitalCPS;
use Ox\Mediboard\OxPyxvital\CPyxvitalCV;
use Ox\Mediboard\OxPyxvital\CPyxvitalFSE;
use Ox\Mediboard\OxPyxvital\CSesamVitaleRuleSet;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\Patients\MedecinExercicePlaceService;
use Ox\Mediboard\Patients\MedecinFieldService;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\Soins\CSejourTask;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Mediboard\Teleconsultation\CRoom;
use Ox\Mediboard\Transport\CTransport;
use Ox\Mediboard\Web100T\CWeb100TSejour;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Consultation d'un patient par un praticien, éventuellement pendant un séjour
 * Un des évenements fondamentaux du dossier patient avec l'intervention
 */
class CConsultation extends CFacturable implements IPatientRelated, IIndexableObject, IGroupRelated, IRGPDEvent,
                                                   ImportableInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'consultation';

    /** @var string */
    public const RELATION_PATIENT = 'patient';
    /** @var string */
    public const RELATION_PLAGE_CONSULT = 'plageConsult';
    /** @var string */
    public const RELATION_CATEGORIE = 'categorie';
    /** @var string */
    public const RELATION_MEDIUSER = 'mediuser';
    /** @var string  */
    public const RELATION_FACTURE_CABINET = 'factureCabinet';

    /** @var string */
    public const FIELDSET_AUTHOR = 'author';
    /** @var string */
    public const FIELDSET_STATUS = 'status';
    /** @var string */
    public const FIELDSET_EXAMEN = 'examen';
    /** @var string */
    public const FIELDSET_TYPE = 'type';

    /** @var string[] */
    public const CCMU_VALUES = ["1", "P", "2", "3", "4", "5", "D"];

    /** @var string[] */
    public const CIMU_VALUES = ["5", "4", "3", "2", "1"];

    // Payment consts
    public const NO_SETTLEMENT              = "noSettlement";
    public const VOUCHER_SENT_NOT_PAYED     = "voucherSentNotPayed";
    public const VOUCHER_PAYED              = "voucherPayed";
    public const VOUCHER_PAYMENT_ERROR      = "voucherPaymentError";
    public const VOUCHER_NOT_SENT           = "voucherNotSent";
    public const SETTLEMENT_NOT_PAYED       = "settlementNotPayed";
    public const SETTLEMENT_PAYED           = "settlementPayed";
    public const SETTLEMENT_PARTIALLY_PAYED = "settlementPartiallyPayed";
    public const REFUND_NOT_RECEIVED        = "refundNotReceived";

    public const KEY_CACHE_EXAM_FIELDS = 'CConsultation.getExamFields';

    const DEMANDE        = 8;
    const PLANIFIE       = 16;
    const PATIENT_ARRIVE = 32;
    const EN_COURS       = 48;
    const TERMINE        = 64;

    // DB Table key
    public $consultation_id;

    // DB References
    public $owner_id;
    public $plageconsult_id;
    public $patient_id;
    public $sejour_id;
    public $categorie_id;
    public $grossesse_id;
    public $element_prescription_id;
    public $lit_id;

    // DB fields
    public $creation_date;
    public $type;
    public $heure;
    public $duree;
    public $secteur1;
    public $secteur2;
    public $secteur3; // Assujetti à la TVA
    public $du_tva;
    public $taux_tva;
    public $chrono;
    public $annule;
    public $motif_annulation;
    public $suspendu;

    public $motif;
    public $rques;
    public $examen;
    public $histoire_maladie;
    public $brancardage;
    public $projet_soins;
    public $conclusion;
    public $resultats;

    public $traitement;
    public $premiere;
    public $derniere;
    public $adresse; // Le patient a-t'il été adressé ?
    public $adresse_par_prat_id;
    public $adresse_par_exercice_place_id;

    public $arrivee;
    public $valide; // Cotation validée
    public $si_desistement;
    public $demande_nominativement; // Demandé nominativement le praticien par le patient

    public $total_assure;
    public $total_amc;
    public $total_amo;

    public $du_patient; // somme que le patient doit régler
    public $du_tiers;
    public $type_assurance;
    public $date_at;
    public $fin_at;
    public $pec_at;
    public $num_at;
    public $cle_at;
    public $reprise_at;
    public $at_sans_arret;
    public $org_at;
    public $feuille_at;
    public $arret_maladie;
    public $concerne_ALD;
    public $visite_domicile;
    public $docs_necessaires;
    public $groupee;
    public $no_patient;
    public $reunion_id;
    public $next_meeting; // Le patient doit être vu lors d'une prochaine réunion ?
    public $teleconsultation;
    public $soins_infirmiers;
    public $motif_sfmu_id;
    public $csnp;
    public $ccmu;
    public $cimu;
    public $sortie;

    public $forfait_peps;

    // Used when object related to external entity
    /** @var dateTime Date de création de la consultation si antérieure */
    public $date_creation_anterieure;

    /** @var string Agent extérieur associé à la consultation */
    public $agent;

    // Derived fields
    public $_etat;
    public $_hour;
    public $_min;
    public $_check_adresse;
    public $_somme;
    public $_types_examen;
    public $_precode_acte;
    public $_exam_fields;
    public $_function_secondary_id;
    public $_semaine_grossesse;
    public $_type;  // Type de la consultation
    public $_duree;
    public $_force_create_sejour;
    public $_rques_consult;
    public $_examen_consult;
    public $_line_element_id;
    public $_etat_dhe_anesth;
    public $_color_planning;
    public $_list_etat_dents;
    public $_active_grossesse;
    public $_type_suivi;
    public $_cancel_sejour;
    public $_covid_diag;
    public $_in_maternite;
    public $_codable_guid;

    // seances
    public $_consult_sejour_nb;
    public $_consult_sejour_out_of_nb;

    // References
    /** @var CMediusers */
    public $_ref_owner;
    /** @var CMediusers */
    public $_ref_chir;
    /** @var CPlageconsult */
    public $_ref_plageconsult;
    /** @var CMedecin */
    public $_ref_adresse_par_prat;
    /** @var CMedecinExercicePlace */
    public $_ref_adresse_par_exercice_place;
    /** @var CGroups */
    public $_ref_group;
    /** @var CConsultAnesth */
    public $_ref_consult_anesth;
    /** @var CExamAudio */
    public $_ref_examaudio;
    /** @var CExamNyha */
    public $_ref_examnyha;
    /** @var CExamPossum */
    public $_ref_exampossum;
    /** @var CGrossesse */
    public $_ref_grossesse;
    /** @var CPrescription */
    public $_ref_prescription;
    /** @var CConsultationCategorie */
    public $_ref_categorie;
    /** @var CSejourTask */
    public $_ref_task;
    /** @var  CSuiviGrossesse */
    public $_ref_suivi_grossesse;
    /** @var CElementPrescription */
    public $_ref_element_prescription;
    /** @var CBrancardage */
    public $_ref_brancardage;
    /** @var CBrancardage[] */
    public $_ref_brancardages;
    /** @var CBrancardage */
    public $_ref_current_brancardage;
    /** @var CReunion */
    public $_ref_reunion;
    /** @var CAccidentTravail */
    public $_ref_accident_travail;
    /** @var CRoom */
    public $_ref_room;
    /** @var CLit */
    public $_ref_lit;
    /** @var CBonAPayer */
    public $_ref_bon_a_payer;
    /** @var CSlot[] */
    public $_ref_slots;
    /** @var string */
    public $type_consultation;
    // Collections
    /** @var CConsultAnesth[] */
    public $_refs_dossiers_anesth = [];
    /** @var  CExamComp[] */
    public $_ref_examcomp = [];
    /** @var  CInfoChecklistItem[] */
    public $_refs_info_check_items = [];
    /** @var  CInfoChecklist[] */
    public $_refs_info_checklist = [];
    /** @var  CInfoChecklistItem */
    public $_ref_info_checklist_item;
    /** @var CAppFineClientFolderLiaison */
    public $_ref_appfine_client_folder;
    /** @var CTransport[] */
    public $_refs_transports = [];
    /** @var CAvisArretTravail[] */
    public $_refs_avis_arrets_travail = [];
    /** @var CAppFineClientOrderItem */
    public $_ref_orders_item;
    /** @var CAppFineClientObjectReceived[] */
    public $_ref_objects_received = [];
    /** @var CAppFineClientRelaunchFolder[] */
    public $_refs_appfine_client_folders_relaunch;
    /** @var CReservation[] */
    public $_ref_reserved_ressources = [];

    // Counts
    public $_count_fiches_examen;
    public $_count_matching_sejours;
    public $_count_prescriptions;

    // AppFine
    public $_count_order_sent;
    public $_link_appfine = false;

    // FSE
    public $_bind_fse;
    public $_ids_fse;
    public $_ext_fses;
    /** @var  CFSE */
    public $_current_fse;
    public $_fse_intermax;
    public $_category_facturation;

    // Distant fields
    public $_date;
    public $_datetime;
    public $_date_fin;
    public $_is_anesth;
    public $_is_dentiste;
    public $_forfait_se;
    public $_forfait_sd;
    public $_facturable;
    public $_uf_soins_id;
    public $_uf_medicale_id;
    public $_charge_id;
    public $_unique_lit_id;
    public $_service_id;
    public $_mode_entree;
    public $_mode_entree_id;
    public $_rappel; // For meetings
    /** @var  CConstantesMedicales[] */
    public $_list_constantes_medicales = [];
    // Semaine d'aménorrhée
    public $_sa;
    public $_ja;

    // Filter Fields
    public $_date_min;
    public $_date_max;
    public $_prat_id;
    public $_etat_reglement_patient;
    public $_etat_reglement_tiers;
    public $_etat_accident_travail;
    public $_type_affichage;
    public $_all_group_money;
    public $_all_group_compta;
    public $_function_compta;
    public $_telephone;
    public $_coordonnees;
    public $_plages_vides;
    public $_empty_places;
    public $_non_pourvues;
    public $_print_ipp;
    public $_date_souscription_optam;

    // Behaviour fields
    public $_no_synchro_eai               = false;
    public $_adjust_sejour;
    public $_operation_id;
    public $_dossier_anesth_completed_id;
    public $_docitems_from_dossier_anesth;
    public $_locks;
    public $_handler_external_booking;
    public $_list_forms                   = [];
    public $_skip_count                   = false;
    public $_sync_consults_from_sejour    = false;     // used to allow CSejour::store to avoid consultation's sejour patient check
    public $_sync_sejour                  = true;
    public $_create_sejour_activite_mixte = false;
    public $_sync_parcours_soins          = true;
    public $_transfert_rpu;

    // Payment field
    public string $_payment_status = "";

    // Field used in purgeEtablissement
    public $_check_prat_change = true;

    public $_is_importing = false;

    public $_function_id;

    /**
     * Charge les praticiens à la compta desquels l'utilisateur courant a accès
     *
     * @param string $prat_id    Si définit, retourne un tableau avec seulement ce praticien
     * @param bool   $actif_only Uniquement les utilisateurs actifs
     *
     * @return CMediusers[]
     * @todo Définir verbalement la stratégie
     */
    static function loadPraticiensCompta($prat_id = null, $actif_only = true)
    {
        // Cas du praticien unique
        if ($prat_id) {
            $prat = CMediusers::get($prat_id);
            $prat->loadRefFunction();
            $users = [$prat->_id => $prat];
            $prat->loadRefsSecondaryUsers();
            foreach ($prat->_ref_secondary_users as $_user) {
                $_user->loadRefFunction();
                $users[$_user->_id] = $_user;
            }

            return $users;
        }

        // Cas standard
        $user              = CMediusers::get();
        $is_admin          = in_array(CUser::$types[$user->_user_type], ["Administrator"]);
        $is_admin_secr_dir = $is_admin || in_array(CUser::$types[$user->_user_type], ["Secrétaire", "Directeur"]);

        // Récupération des fonctions de l'utilisateur
        $function  = $user->loadRefFunction();
        $functions = $user->loadRefsSecondaryFunctions();
        foreach ($functions as $_function) {
            if (!$_function->compta_partagee) {
                unset($functions[$_function->_id]);
            }
        }
        $functions = CMbArray::mergeKeys($functions, [$function->_id => $function]);

        $praticiens = [];
        // Liste des praticiens du cabinet
        if ($is_admin_secr_dir || $function->compta_partagee) {
            if ($is_admin && (CAppUI::gconf(
                        "dPcabinet Comptabilite show_compta_tiers"
                    ) || $user->_user_username == "admin")) {
                $functions = [new CFunctions()];
            }

            foreach ($functions as $_function) {
                $praticiens = CMbArray::mergeKeys(
                    $praticiens,
                    CConsultation::loadPraticiens(
                        PERM_EDIT,
                        $_function->_id,
                        null,
                        null,
                        $actif_only
                    )
                );
            }
            if (!$is_admin) {
                // On ajoute les praticiens qui ont délégués leurs compta
                $where   = [];
                $where[] = "users_mediboard.compta_deleguee <> '0' || users_mediboard.user_id " .
                    CSQLDataSource::prepareIn(array_keys($praticiens));
                // Filters on users values
                if ($actif_only) {
                    $where["users_mediboard.actif"] = "= '1'";
                }
                $where["functions_mediboard.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";

                $ljoin["users"]               = "users.user_id = users_mediboard.user_id";
                $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

                $order = "users.user_last_name, users.user_first_name";

                $mediuser = new CMediusers();
                /** @var CMediusers[] $mediusers */
                $mediusers = $mediuser->loadListWithPerms(PERM_EDIT, $where, $order, null, null, $ljoin);

                // Associate already loaded function
                foreach ($mediusers as $_mediuser) {
                    $_mediuser->loadRefFunction();
                }
                $praticiens = CMbArray::mergeKeys($praticiens, $mediusers);
            }
        }
        if ($user->isProfessionnelDeSante() && $user->compta_deleguee != "1") {
            $praticiens = CMbArray::mergeKeys($praticiens, [$user->_id => $user]);
        }

        return $praticiens;
    }

    /**
     * Charge les praticiens susceptibles d'être concernés par les consultation
     * en fonction de les préférences utilisateurs
     *
     * @param int    $permType    Type de permission
     * @param string $function_id Fonction spécifique
     * @param string $name        Nom spécifique
     * @param bool   $secondary   Chercher parmi les fonctions secondaires
     * @param bool   $actif       Seulement les actifs
     * @param bool   $use_group   Restreint la recherche à l'établissement courant
     *
     * @return CMediusers[]
     */
    static function loadPraticiens(
        $permType = PERM_READ,
        $function_id = null,
        $name = null,
        $secondary = false,
        $actif = true,
        $use_group = true
    ) {
        $user = new CMediusers();

        return $user->loadProfessionnelDeSanteByPref($permType, $function_id, $name, $secondary, $actif, $use_group);
    }

    /**
     * Construit le tag d'une consultation en fonction des variables de configuration
     *
     * @param string $group_id Permet de charger l'id externe d'uns consultation pour un établissement donné si non null
     *
     * @return string|null Nul si indisponible
     */
    static function getTagConsultation($group_id = null)
    {
        // Pas de tag consultation
        if (null == $tag_consultation = CAppUI::gconf("dPcabinet CConsultation tag")) {
            return null;
        }

        // Permettre des id externes en fonction de l'établissement
        $group = CGroups::loadCurrent();
        if (!$group_id) {
            $group_id = $group->_id;
        }

        return str_replace('$g', $group_id, $tag_consultation);
    }

    /**
     * count the number of consultations asking to be
     *
     * @param array()   $chir_ids list of chir ids
     * @param string $day date targeted, default = today
     *
     * @return int number of result
     */
    static function countDesistementsForDay($chir_ids, $day = null)
    {
        $date         = CMbDT::date($day);
        $consultation = new self();
        $ds           = $consultation->getDS();
        $where        = [
            "plageconsult.date"           => " > '$date'",
            "consultation.si_desistement" => "= '1'",
            "consultation.annule"         => "= '0'",
        ];
        $where[]      = "plageconsult.chir_id " . $ds->prepareIn(
                $chir_ids
            ) . " OR plageconsult.remplacant_id " . $ds->prepareIn($chir_ids);
        $ljoin        = [
            "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id",
        ];

        return $consultation->countList($where, null, $ljoin);
    }

    /**
     * @param CConsultation   $consult
     * @param CDossierMedical $dossier_medical
     * @param CConsultAnesth  $consultAnesth
     * @param CSejour         $sejour
     * @param array           $list_etat_dents
     *
     * @return array
     */
    static function makeTabsCount($consult, $dossier_medical, $consultAnesth, $sejour)
    {
        $tabs_count = [
            "AntTrait"            => 0,
            "Constantes"          => 0,
            "prescription_sejour" => 0,
            "facteursRisque"      => 0,
            "Examens"             => 0,
            "Exams"               => 0,
            "ExamsComp"           => 0,
            "Intub"               => 0,
            "InfoAnesth"          => 0,
            "dossier_suivi"       => 0,
            "Actes"               => 0,
            "fdrConsult"          => 0,
            "reglement"           => 0,
        ];

        if (CModule::getActive("dPprescription")) {
            CPrescription::$_load_lite = true;
        }
        foreach ($tabs_count as $_tab => $_count) {
            $count = 0;
            switch ($_tab) {
                case "AntTrait":
                    $prescription = $dossier_medical->loadRefPrescription();
                    $count_meds   = 0;
                    if (CModule::getActive("dPprescription") && CPrescription::isMPMActive()) {
                        $count_meds = $prescription->countBackRefs("prescription_line_medicament");
                    }
                    $count_cim = is_array($dossier_medical->_ext_codes_cim) ? count(
                        $dossier_medical->_ext_codes_cim
                    ) : 0;

                    $dossier_medical->countTraitements();
                    $dossier_medical->countAntecedents();
                    $tabs_count[$_tab] =
                        $dossier_medical->_count_antecedents
                        + $dossier_medical->_count_cancelled_traitements
                        + $dossier_medical->_count_traitements
                        + $dossier_medical->_count_cancelled_antecedents
                        + $count_meds
                        + $count_cim;
                    break;
                case "Constantes":
                    if ($sejour->_ref_rpu && $sejour->_ref_rpu->_id) {
                        $tabs_count[$_tab] = $sejour->countBackRefs("contextes_constante");
                    } else {
                        $tabs_count[$_tab] = $consult->countBackRefs("contextes_constante");
                    }
                    break;
                case "prescription_sejour":
                    $_sejour = $sejour;
                    if ($consultAnesth->_id && $consultAnesth->operation_id) {
                        $_sejour = $consultAnesth->loadRefOperation()->loadRefSejour();
                    }

                    if ($_sejour->_id) {
                        $_sejour->loadRefsPrescriptions();
                        foreach ($_sejour->_ref_prescriptions as $key => $_prescription) {
                            if (!$_prescription->_id) {
                                unset($_sejour->_ref_prescriptions[$key]);
                                continue;
                            }

                            $_sejour->_ref_prescriptions[$_prescription->_id] = $_prescription;
                            unset($_sejour->_ref_prescriptions[$key]);
                        }

                        if (count($_sejour->_ref_prescriptions)) {
                            $prescription = new CPrescription();
                            $prescription->massCountMedsElements($_sejour->_ref_prescriptions);
                            foreach ($_sejour->_ref_prescriptions as $_prescription) {
                                $count += array_sum($_prescription->_counts_by_chapitre);
                            }
                        }
                    }

                    $tabs_count[$_tab] = $count;
                    break;
                case "facteursRisque":
                    if (!$consultAnesth) {
                        break;
                    }
                    if ($dossier_medical->_id) {
                        $fields = [
                            "risque_antibioprophylaxie",
                            "risque_MCJ_chirurgie",
                            "risque_MCJ_patient",
                            "risque_prophylaxie",
                            "risque_thrombo_chirurgie",
                            "risque_thrombo_patient",
                        ];

                        foreach ($fields as $_field) {
                            if ($dossier_medical->$_field != "NR") {
                                $count++;
                            }
                        }

                        if ($dossier_medical->facteurs_risque) {
                            $count++;
                        }
                    }
                    $tabs_count[$_tab] = $count;
                    break;
                case "Examens":
                    if ($consultAnesth->_id) {
                        break;
                    }
                    $fields = ["motif", "rques", "examen", "histoire_maladie", "conclusion"];
                    foreach ($fields as $_field) {
                        if ($consult->$_field) {
                            $count++;
                        }
                    }
                    $count             += $consult->countBackRefs("examaudio");
                    $count             += $consult->countBackRefs("examnyha");
                    $count             += $consult->countBackRefs("exampossum");
                    $tabs_count[$_tab] = $count;
                    break;
                case "Exams":
                    if (!$consultAnesth->_id) {
                        break;
                    }
                    $fields = ["examenCardio", "examenPulmo", "examenDigest", "examenAutre"];
                    foreach ($fields as $_field) {
                        if ($consultAnesth->$_field) {
                            $count++;
                        }
                    }
                    if ($consult->examen != "") {
                        $count++;
                    }
                    $count             += $consult->countBackRefs("examaudio");
                    $count             += $consult->countBackRefs("examnyha");
                    $count             += $consult->countBackRefs("exampossum");
                    $tabs_count[$_tab] = $count;
                    break;
                case "ExamsComp":
                    if (!$consultAnesth->_id) {
                        break;
                    }
                    $count += $consult->countBackRefs("examcomp");
                    if ($consultAnesth->result_ecg) {
                        $count++;
                    }
                    if ($consultAnesth->result_rp) {
                        $count++;
                    }
                    $tabs_count[$_tab] = $count;
                    break;
                case "Intub":
                    if (!$consultAnesth->_id) {
                        break;
                    }
                    $fields = [
                        "mallampati",
                        "bouche",
                        "distThyro",
                        "mob_cervicale",
                        "etatBucco",
                        "conclusion",
                        "plus_de_55_ans",
                        "edentation",
                        "barbe",
                        "imc_sup_26",
                        "ronflements",
                        "piercing",
                    ];
                    foreach ($fields as $_field) {
                        if ($consultAnesth->$_field) {
                            $count++;
                        }
                    }
                    $consult->loadListEtatsDents();
                    $count             += count(array_filter($consult->_list_etat_dents));
                    $tabs_count[$_tab] = $count;
                    break;
                case "InfoAnesth":
                    if (!$consultAnesth->_id) {
                        break;
                    }
                    $op = $consultAnesth->loadRefOperation();

                    $fields_anesth = [
                        "prepa_preop",
                        "premedication",
                        "apfel_femme",
                        "apfel_non_fumeur",
                        "apfel_atcd_nvp",
                        "apfel_morphine",
                    ];
                    $fields_op     = ["passage_uscpo", "type_anesth", "ASA", "position_id"];

                    foreach ($fields_anesth as $_field) {
                        if ($consultAnesth->$_field) {
                            $count++;
                        }
                    }
                    if ($op->_id) {
                        foreach ($fields_op as $_field) {
                            if ($op->$_field) {
                                $count++;
                            }
                        }
                    }

                    if ($consult->rques) {
                        $count++;
                    }

                    $count += $consultAnesth->countBackRefs("techniques");

                    $tabs_count[$_tab] = $count;
                    break;
                case "dossier_suivi":
                    break;
                case "Actes":
                    $consult->countActes();
                    $tabs_count[$_tab] = $consult->_count_actes;

                    if ($sejour->_id) {
                        if ($sejour->DP) {
                            $tabs_count[$_tab]++;
                        }
                        if ($_sejour->DR) {
                            $tabs_count[$_tab]++;
                        }
                        $sejour->loadDiagnosticsAssocies();
                        $tabs_count[$_tab] += count($sejour->_diagnostics_associes);
                    }
                    break;
                case "fdrConsult":
                    $consult->_docitems_from_dossier_anesth = false;
                    $consult->countDocs();
                    $consult->countFiles();
                    $consult->loadRefsPrescriptions();
                    $tabs_count[$_tab] = $consult->_nb_docs + $consult->_nb_files;
                    if (isset($consult->_ref_prescriptions["externe"])) {
                        $tabs_count[$_tab]++;
                    }
                    if ($sejour->_id) {
                        $sejour->countDocs();
                        $sejour->countFiles();
                        $tabs_count[$_tab] += $sejour->_nb_docs + $sejour->_nb_files;
                    }
                    break;
                case "reglement":
                    $consult->loadRefFacture()->loadRefsReglements();
                    $tabs_count[$_tab] = count($consult->_ref_facture->_ref_reglements);
            }
        }
        if (CModule::getActive("dPprescription")) {
            CPrescription::$_load_lite = false;
        }

        return $tabs_count;
    }

    /**
     * Ordonne l'état des dents
     *
     * @return array
     */
    function loadListEtatsDents()
    {
        $list_etat_dents = [];
        $dossier_medical = $this->_ref_patient->loadRefDossierMedical();
        if ($dossier_medical->_id) {
            $etat_dents = $dossier_medical->loadRefsEtatsDents();
            foreach ($etat_dents as $etat) {
                $list_etat_dents[$etat->dent] = $etat->etat;
            }
        }

        return $this->_list_etat_dents = $list_etat_dents;
    }

    /**
     * @see parent::countDocs()
     */
    function countDocs()
    {
        $nbDocs = parent::countDocs();

        if (!$this->_docitems_from_dossier_anesth) {
            // Ajout des documents des dossiers d'anesthésie
            if (!$this->_refs_dossiers_anesth) {
                $this->loadRefConsultAnesth();
            }

            foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
                $_dossier_anesth->_docitems_from_consult = true;
                $nbDocs                                  += $_dossier_anesth->countDocs();
            }
        }

        return $this->_nb_docs = $nbDocs;
    }

    /**
     * Charge un dossier d'anesthésie classique
     *
     * @param string $dossier_anesth_id Identifiant de dossier à charger explicitement
     *
     * @return CConsultAnesth
     */
    function loadRefConsultAnesth($dossier_anesth_id = null)
    {
        $dossiers = $this->loadRefsDossiersAnesth();

        // Cas du choix initial du dossier à utiliser
        if ($dossier_anesth_id !== null && isset($dossiers[$dossier_anesth_id])) {
            return $this->_ref_consult_anesth = $dossiers[$dossier_anesth_id];
        }

        // On retourne le premier ou un dossier vide
        return $this->_ref_consult_anesth = count($dossiers) ? reset($dossiers) : new CConsultAnesth();
    }

    /**
     * Charge tous les dossiers d'anesthésie
     *
     * @return CConsultAnesth[]
     * @throws Exception
     */
    public function loadRefsDossiersAnesth(): array
    {
        if (!CAppUI::gconf('dPcabinet CConsultAnesth active')) {
            return $this->_refs_dossiers_anesth = [];
        }

        $this->_refs_dossiers_anesth = $this->loadBackRefs("consult_anesth");

        foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
            $_dossier_anesth->_ref_consultation = $this;
            $_dossier_anesth->loadRefChir();
        }

        return $this->_refs_dossiers_anesth;
    }

    /**
     * @throws Exception
     */
    public function countDossiersAnesth(): ?int
    {
        if (!CAppUI::gconf('dPcabinet CConsultAnesth active')) {
            return 0;
        }

        return $this->countBackRefs('consult_anesth');
    }

    /**
     * @see parent::countFiles()
     */
    function countFiles($where = [])
    {
        $nbFiles = parent::countFiles($where);

        if (!$this->_docitems_from_dossier_anesth) {
            // Ajout des fichiers des dossiers d'anesthésie
            if (!$this->_refs_dossiers_anesth) {
                $this->loadRefConsultAnesth();
            }

            foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
                $_dossier_anesth->_docitems_from_consult = true;
                $nbFiles                                 += $_dossier_anesth->countFiles();
            }
        }

        return $this->_nb_files = $nbFiles;
    }

    /**
     * Chargement des prescriptions liées à la consultation
     *
     * @return CPrescription[] Les prescription, classées par type, pas par identifiant
     */
    function loadRefsPrescriptions()
    {
        $prescriptions = $this->loadBackRefs("prescriptions");

        // Cas du module non installé
        if (!is_array($prescriptions)) {
            return $this->_ref_prescriptions = null;
        }

        $this->_count_prescriptions = count($prescriptions);

        foreach ($prescriptions as $_prescription) {
            $this->_ref_prescriptions[$_prescription->type] = $_prescription;
        }

        return $this->_ref_prescriptions;
    }

    public static function guessUfMedicaleMandatory(array $prats): void
    {
        if (!count($prats)) {
            return;
        }

        if (!CAppUI::gconf('dPcabinet CConsultation attach_consult_sejour')) {
            return;
        }

        if (!CAppUI::gconf('dPcabinet CConsultation create_consult_sejour')) {
            return;
        }

        if (CAppUI::gconf('dPplanningOp CSejour required_uf_med') === 'no') {
            return;
        }

        /** @var CMediusers $_prat */
        foreach ($prats as $_prat) {
            if ($_prat->loadRefFunction()->create_sejour_consult || in_array($_prat->activite, ['salarie', 'mixte'])) {
                $_prat->_uf_medicale_mandatory = true;
            }
        }
    }

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table       = 'consultation';
        $spec->key         = 'consultation_id';
        $spec->measureable = true;

        $spec->events = [
            'prise_rdv'                    => [
                'reference1' => ['CSejour', 'sejour_id'],
                'reference2' => ['CPatient', 'patient_id'],
            ],
            'prise_rdv_auto'               => [
                'auto'       => true,
                'reference1' => ['CSejour', 'sejour_id'],
                'reference2' => ['CPatient', 'patient_id'],
            ],
            'examen'                       => [
                'reference1' => ['CSejour', 'sejour_id'],
                'reference2' => ['CPatient', 'patient_id'],
            ],
            'tab_examen'                   => [
                'reference1'  => ['CSejour', 'sejour_id'],
                'reference2'  => ['CPatient', 'patient_id'],
                'tab'         => true,
                'tab_actions' => [],
            ],
            'tab_dossier_soins_obs_entree' => [
                'reference1'  => ['CSejour', 'sejour_id'],
                'reference2'  => ['CPatient', 'patient_id'],
                'tab'         => true,
                'tab_actions' => [
                    [
                        'title'    => 'CConsultation-new_obs_entree',
                        // Button title
                        'class'    => 'change',
                        // Button class
                        'callback' => 'createObsEntree',
                        // Method name, that will be called on $this, with the "formTabAction_" prefix
                    ],
                ],
            ],
        ];

        static $appFine = null;
        if ($appFine === null) {
            $appFine = CModule::getActive("appFineClient") !== null;
        }

        if ($appFine) {
            $spec->events["appFine"] = [
                "reference1" => ["CMediusers", "praticien_id"],
                "reference2" => ["CPatient", "patient_id"],
            ];
        }

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["owner_id"]                = "ref class|CMediusers show|0 back|consultations fieldset|author";
        $props["plageconsult_id"]         = "ref notNull class|CPlageconsult seekable show|1 back|consultations fieldset|default";
        $props["patient_id"]              = "ref class|CPatient purgeable seekable show|1 back|consultations fieldset|default";
        $props["sejour_id"]               = "ref class|CSejour back|consultations fieldset|default";
        $props["categorie_id"]            = "ref class|CConsultationCategorie show|1 nullify back|consultations fieldset|extra";
        $props["grossesse_id"]            = "ref class|CGrossesse show|0 unlink back|consultations";
        $props["element_prescription_id"] = "ref class|CElementPrescription back|consultations";
        $props["lit_id"]                  = "ref class|CLit back|consultations";
        $props["consult_related_id"]      = "ref class|CConsultation show|0 back|consults_liees";
        $props["creation_date"]           = "dateTime";
        $props["motif"]                   = "text helped seekable markdown|true fieldset|examen";
        $props["type"]                    = "enum list|classique|entree|chimio default|classique";
        $props["heure"]                   = "time notNull show|0 fieldset|default";
        $props["duree"]                   = "num min|1 max|255 notNull default|1 show|0 fieldset|default";
        $props["secteur1"]                = "currency min|0 show|0";
        $props["secteur2"]                = "currency show|0";
        $props["secteur3"]                = "currency show|0";
        $props["taux_tva"]                = "float";
        $props["du_tva"]                  = "currency show|0";
        $props["chrono"]                  = "enum notNull list|8|16|32|48|64 show|0 fieldset|status";
        $props["annule"]                  = "bool show|0 default|0 notNull fieldset|status";
        $props["motif_annulation"]        = "enum list|not_arrived|by_patient|other fieldset|status";
        $props["_etat"]                   = "str";
        $props["suspendu"]                = "bool show|0 default|0 notNull";
        $props["forfait_peps"]            = "bool default|0";
        $props['type_consultation']       = "enum list|consultation|suivi_patient default|consultation fieldset|" . self::FIELDSET_TYPE;

        $props["rques"]            = "text helped seekable markdown|true fieldset|examen";
        $props["examen"]           = "text helped seekable markdown|true fieldset|examen";
        $props["traitement"]       = "text helped seekable markdown|true";
        $props["histoire_maladie"] = "text helped seekable markdown|true fieldset|examen";
        $props["brancardage"]      = "text helped seekable markdown|true";
        $props["projet_soins"]     = "text helped seekable markdown|true";
        $props["conclusion"]       = "text helped seekable markdown|true fieldset|examen";
        $props["resultats"]        = "text helped seekable markdown|true";
        $props["soins_infirmiers"] = "text helped seekable markdown|true";

        $props["facture"] = "bool default|0 show|0";

        $props["premiere"]                      = "bool show|0 fieldset|status";
        $props["derniere"]                      = "bool show|0";
        $props["adresse"]                       = "bool show|0";
        $props["adresse_par_prat_id"]           = "ref class|CMedecin nullify back|consultations_adresses";
        $props['adresse_par_exercice_place_id'] = 'ref class|CMedecinExercicePlace back|consultations_adresses';
        $props["arrivee"]                       = "dateTime show|0 fieldset|examen";
        $props["concerne_ALD"]                  = "bool";
        $props["visite_domicile"]               = "bool default|0";

        $props["du_patient"] = "currency show|0";
        $props["du_tiers"]   = "currency show|0";

        $props["type_assurance"] = "enum list|classique|at|maternite|smg";
        $props["date_at"]        = "date";
        $props["fin_at"]         = "dateTime";
        $props["num_at"]         = "num length|8";
        $props["cle_at"]         = "num length|1";
        $props['feuille_at']     = 'bool default|0';
        $props['org_at']         = 'numchar length|9';

        $props["pec_at"]        = "enum list|soins|arret";
        $props["reprise_at"]    = "dateTime";
        $props["at_sans_arret"] = "bool default|0";
        $props["arret_maladie"] = "bool default|0";

        $props["total_amo"]    = "currency show|0";
        $props["total_amc"]    = "currency show|0";
        $props["total_assure"] = "currency show|0";

        $props["valide"]                 = "bool show|0 fieldset|extra";
        $props["si_desistement"]         = "bool notNull default|0";
        $props["demande_nominativement"] = "bool notNull default|0";
        $props["docs_necessaires"]       = "text helped show|0";
        $props["groupee"]                = "bool default|0";
        $props["no_patient"]             = "bool default|0";

        $props['date_creation_anterieure'] = 'dateTime';
        $props['agent']                    = 'str';

        $props["reunion_id"]       = "ref class|CReunion back|consultation cascade";
        $props["next_meeting"]     = "bool default|0";
        $props["teleconsultation"] = "bool default|0";

        // Main courante oxCabinet
        $props["motif_sfmu_id"] = "ref class|CMotifSFMU autocomplete|libelle back|consultations";
        $props["csnp"]          = "bool default|0";
        $props["ccmu"]          = "enum list|" . implode("|", self::CCMU_VALUES);
        $props["cimu"]          = "enum list|" . implode("|", self::CIMU_VALUES);
        $props["sortie"]        = "dateTime";

        $props["_etat_reglement_patient"]  = "enum list|reglee|non_reglee";
        $props["_etat_reglement_tiers"]    = "enum list|reglee|non_reglee";
        $props["_etat_accident_travail"]   = "enum list|yes|no";
        $props["_forfait_se"]              = "bool default|0";
        $props["_forfait_sd"]              = "bool default|0";
        $props["_facturable"]              = "bool default|1";
        $props["_uf_soins_id"]             = "ref class|CUniteFonctionnelle seekable";
        $props["_uf_medicale_id"]          = "ref class|CUniteFonctionnelle seekable";
        $props["_charge_id"]               = "ref class|CChargePriceIndicator seekable";
        $props['_date_souscription_optam'] = 'date';

        $props["_date"]             = "date";
        $props["_datetime"]         = "dateTime notNull show|1";
        $props["_date_min"]         = "date";
        $props["_date_max"]         = "date moreEquals|_date_min";
        $props["_type_affichage"]   = "enum list|complete|totaux";
        $props["_all_group_compta"] = "bool default|1";
        $props["_all_group_money"]  = "bool default|1";
        $props['_function_compta']  = 'bool default|0';
        $props["_telephone"]        = "bool default|0";
        $props["_coordonnees"]      = "bool default|0";
        $props["_plages_vides"]     = "bool default|1";
        $props["_non_pourvues"]     = "bool default|1";
        $props["_print_ipp"]        = "bool default|" . CAppUI::gconf("dPcabinet CConsultation show_IPP_print_consult");
        $props["_sa"]               = "num";
        $props["_ja"]               = "num";
        $props["_active_grossesse"] = "bool";

        $props["_check_adresse"] = "";
        $props["_somme"]         = "currency";
        $props["_type"]          = "enum list|urg|anesth";

        $props["_prat_id"]               = "ref class|CMediusers notNull";
        $props["_praticien_id"]          = "ref class|CMediusers show|1";
        $props["_function_secondary_id"] = "ref class|CFunctions";
        $props["_operation_id"]          = "ref class|COperation";

        $props["_rappel"]        = "bool default|0";
        $props["_cancel_sejour"] = "bool default|0";
        $props["_function_id"]   = "ref class|CFunctions";

        return $props;
    }

    /**
     * @see parent::getTemplateClasses()
     */
    function getTemplateClasses()
    {
        $this->loadRefsFwd();

        $tab = [];

        // Stockage des objects liés à l'opération
        $tab['CConsultation'] = $this->_id;
        $tab['CPatient']      = $this->_ref_patient->_id;

        $tab['CConsultAnesth'] = 0;
        $tab['COperation']     = 0;
        $tab['CSejour']        = 0;

        return $tab;
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd($cache = true)
    {
        $this->loadRefPatient($cache);
        $this->_ref_patient->loadRefLatestConstantes();
        $this->loadRefPlageConsult($cache);
        $this->_view = CAppUI::tr(
            'CConsultation-Consultation of %s - %s-court',
            $this->_ref_patient->_view,
            $this->_ref_plageconsult->_ref_chir->_view
        );
        $this->_view .= " (" . CMbDT::format($this->_ref_plageconsult->date, CAppUI::conf("date")) . ")";
        $this->loadExtCodesCCAM();
    }

    /**
     * Charge le patient
     *
     * @param bool $cache Use cache
     *
     * @return CPatient
     */
    public function loadRefPatient(bool $cache = true): ?CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id", $cache);
    }

    /**
     * Charge la plage de consultation englobante
     *
     * @param boolean $cache [optional] Use cache
     *
     * @return CPlageconsult
     */
    public function loadRefPlageConsult(bool $cache = true): CPlageconsult
    {
        $this->completeField("plageconsult_id");
        /** @var CPlageConsult $plage */
        $plage = $this->loadFwdRef("plageconsult_id", $cache);

        $this->_duree = CMbDT::minutesRelative("00:00:00", $plage->freq) * $this->duree;

        $plage->_ref_chir       = $plage->loadFwdRef("chir_id", $cache);
        $plage->_ref_remplacant = $plage->loadFwdRef("remplacant_id", $cache);

        // Distant fields
        /** @var CMediusers $chir */
        $chir = $plage->_ref_remplacant->_id ?
            $plage->_ref_remplacant :
            $plage->_ref_chir;

        $this->_date     = $plage->date;
        $this->_datetime = CMbDT::addDateTime($this->heure, $this->_date);
        $this->_date_fin = CMbDT::dateTime(
            "+" . CMbDT::minutesRelative("00:00:00", $plage->freq) * $this->duree . " MINUTES",
            $this->_datetime
        );

        if (!$this->_acte_execution) {
            $this->_acte_execution = $this->_datetime;
        }
        $this->_is_anesth    = $chir->isAnesth();
        $this->_is_dentiste  = $chir->isDentiste();
        $this->_praticien_id = $chir->_id;

        $this->_ref_chir = $chir;

        return $this->_ref_plageconsult = $plage;
    }

    /**
     * @see parent::updatePlainFields()
     */
    public function updatePlainFields(): void
    {
        if (($this->_hour !== null) && ($this->_min !== null)) {
            $this->heure = sprintf("%02d:%02d:00", $this->_hour, $this->_min);
        }

        // Liaison FSE prioritaire sur l'état
        if ($this->_bind_fse) {
            $this->valide = 0;
        }

        // Cas du paiement d'un séjour
        if ($this->sejour_id !== null && $this->sejour_id && $this->secteur1 !== null && $this->secteur2 !== null) {
            $urg              = $this->sejour_id && $this->_ref_sejour->_ref_rpu && $this->_ref_sejour->_ref_rpu->_id
                ? true : false;
            $total            = round($this->secteur1 + $this->secteur2 + $this->secteur3 + $this->du_tva, 2);
            $this->du_tiers   = $urg ? 0 : $total;
            $this->du_patient = $urg ? $total : 0;
        }
    }

    /**
     * @see parent::check()
     */
    public function check(): ?string
    {
        // Data checking
        $msg = null;
        if (!$this->_id) {
            if (!$this->plageconsult_id) {
                $msg .= CAppUI::tr('CConsultation-msg-Invalid consultation range');
            }

            return $msg . parent::check();
        }

        $this->loadOldObject();
        $this->loadRefFacture()->loadRefsReglements();

        $this->completeField("sejour_id", "plageconsult_id", "heure", "valide");

        $this->loadRefPlageConsult();
        if ($this->_check_bounds) {
            if ($this->sejour_id && !$this->_forwardRefMerging) {
                $sejour = $this->loadRefSejour();

                if (
                    !$this->fieldModified("annule", "1") && $sejour->type != "consult" &&
                    ($this->_date < CMbDT::date($sejour->entree) || CMbDT::date($this->_date) > $sejour->sortie)
                ) {
                    $msg .= CAppUI::tr('CConsultation-msg-Consultation outside of the stay');

                    return $msg . parent::check();
                }
            }
        }

        if (
            ($this->fieldModified("heure") || !$this->_id) && $this->heure
            && ($this->heure < $this->_ref_plageconsult->debut || $this->heure > $this->_ref_plageconsult->fin)
        ) {
            $msg .= CAppUI::tr('CConsultation-msg-The consultation time is outside the consultation range');
        }

        /** @var self $old */
        $old = $this->_old;
        // Dévalidation avec règlement déjà effectué
        if (!$this->_is_importing && $this->fieldModified("valide", "0")) {
            // Bien tester sur _old car valide = 0 s'accompagne systématiquement d'un facture_id = 0
            if (count($old->loadRefFacture()->loadRefsReglements())) {
                $msg .= CAppUI::tr(
                    'CConsultation-msg-You can no longer cancel the tariff, invoice payments have already been made'
                );
            }
        }

        if (
            !($this->_merging || $this->_mergeDeletion || $this->_forwardRefMerging
                || $this->_transfert_rpu || $this->_is_importing)
            && $old->valide === "1" && $this->valide === "1"
        ) {
            // Modification du tarif déjà validé
            if (
                $this->fieldModified("secteur1") ||
                $this->fieldModified("secteur2") ||
                $this->fieldModified("total_assure") ||
                $this->fieldModified("total_amc") ||
                $this->fieldModified("total_amo") ||
                $this->fieldModified("du_patient") ||
                $this->fieldModified("du_tiers")
            ) {
                $msg .= CAppUI::tr('CConsultation-msg-You can no longer modify the tariff, it is already validated');
            }
        }

        if ($this->valide && $this->sejour_id && $this->fieldModified("sejour_id") && !$old->sejour_id) {
            $msg .= CAppUI::tr('CConsultation-msg-no_associate_sejour_with_consult_valid');
        }

        return $msg . parent::check();
    }

    /**
     * Chargement du sejour et du RPU dans le cas d'une urgence
     *
     * @param bool $cache Use cache
     *
     * @return CSejour
     */
    public function loadRefSejour(bool $cache = true): ?CSejour
    {
        /** @var CSejour $sejour */
        $sejour = $this->loadFwdRef("sejour_id", $cache);
        $sejour->loadRefRPU();

        if (CAppUI::gconf("dPcabinet CConsultation attach_consult_sejour")) {
            $this->_forfait_se     = $sejour->forfait_se;
            $this->_forfait_sd     = $sejour->forfait_sd;
            $this->_facturable     = $sejour->facturable;
            $this->_uf_soins_id    = $sejour->uf_soins_id;
            $this->_uf_medicale_id = $sejour->uf_medicale_id;
            $this->_charge_id      = $sejour->charge_id;
        }

        return $this->_ref_sejour = $sejour;
    }

    /**
     * @see parent::loadView()
     */
    public function loadView(): void
    {
        parent::loadView();
        $this->loadRefPatient()->loadRefPhotoIdentite();
        $this->loadRefsFichesExamen();
        $this->loadRefsActesNGAP();
        $this->loadRefsActesLPP();
        $this->loadRefCategorie();
        $this->loadRefPlageConsult(1);
        $this->_ref_chir->loadRefFunction();
        $this->loadRefBrancardage();
        $this->loadRefSejour();
        $this->_ref_categorie->getSessionOrder($this->_ref_patient->_id);

        $group_id = $this->loadRefGroup()->_id;
        // Compteur appFine des demandes
        if (CModule::getActive("appFineClient") && CAppUI::gconf("appFineClient Sync allow_appfine_sync")) {
            CAppFineClient::loadIdex($this->_ref_patient, $group_id);

            if ($this->_ref_patient->_ref_appFine_idex && $this->_ref_patient->_ref_appFine_idex->_id) {
                $this->_ref_patient->loadRefStatusPatientUser();
                CAppFineClient::loadIdex($this, $group_id);
            }

            if ($this->_ref_patient->_ref_appFine_idex && $this->_ref_patient->_ref_appFine_idex->_id
                && $this->_ref_appFine_idex && $this->_ref_appFine_idex->_id
            ) {
                CAppFineClient::countOrders($this);
                $this->loadRefsFoldersRelaunchByType();
            }
        }

        // Iconographie de la consultation sur les systèmes tiers
        $this->loadExternalIdentifiers($group_id);

        if (CModule::getActive("transport")) {
            $this->loadRefsTransports();
        }
    }

    /**
     * Charge toutes les fiches d'examens associées
     *
     * @return int Nombre de fiche
     */
    function loadRefsFichesExamen()
    {
        $this->loadRefsExamAudio();
        $this->loadRefsExamNyha();
        $this->loadRefsExamPossum();
        $this->_count_fiches_examen = 0;
        $this->_count_fiches_examen += $this->_ref_examaudio->_id ? 1 : 0;
        $this->_count_fiches_examen += $this->_ref_examnyha->_id ? 1 : 0;
        $this->_count_fiches_examen += $this->_ref_exampossum->_id ? 1 : 0;

        return $this->_count_fiches_examen;
    }

    /**
     * Charge l'audiogramme
     *
     * @return CExamAudio
     */
    function loadRefsExamAudio()
    {
        return $this->_ref_examaudio = $this->loadUniqueBackRef("examaudio");
    }

    /**
     * Charge l'audiogramme
     *
     * @return CExamAudio
     */
    function loadRefsExamNyha()
    {
        $this->_ref_examnyha = $this->loadUniqueBackRef("examnyha");
    }

    /**
     * Charge le score possum
     *
     * @return CExamPossum
     */
    function loadRefsExamPossum()
    {
        $this->_ref_exampossum = $this->loadUniqueBackRef("exampossum");
    }

    /**
     * Charge la catégorie de la consultation
     *
     * @param bool $cache Utilise le cache
     *
     * @return CConsultationCategorie
     */
    function loadRefCategorie($cache = true)
    {
        return $this->_ref_categorie = $this->loadFwdRef("categorie_id", $cache);
    }

    /**
     * Chargement du brancardage de la consultation
     *
     * @return CBrancardage|null
     * @throws Exception
     */
    public function loadRefBrancardage(): ?CBrancardage
    {
        if (!CModule::getActive("brancardage") || !$this->sejour_id || !CAppUI::gconf(
                "brancardage General use_brancardage"
            )) {
            return null;
        }

        $where   = [];
        $where[] = "brancardage.context_class = '" . $this->_class . "'";

        /** @var CBrancardage[] $brancardages */
        $brancardages = $this->loadBackRefs(
            "context_ref_brancardages",
            "brancardage_id DESC",
            1,
            null,
            null,
            null,
            null,
            $where
        );

        if (count($brancardages) == 0) {
            $brancardage = new CBrancardage();

            $brancardage->context_id    = $this->_id;
            $brancardage->context_class = $this->_class;
            $brancardage->prevu         = CMbDT::dateTime();

            $brancardages = [$brancardage];
        }

        $key                    = array_key_last($brancardages);
        $this->_ref_brancardage = $brancardages[$key];
        $this->_ref_brancardage->loadRefEtapes();

        return $this->_ref_brancardage;
    }

    /**
     * Charge l'établissement indirectement associée à la consultation
     *
     * @return CGroups
     * @todo Prendre en compte le cas de la consultation liée à un séjour dans un établissement
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadRefPraticien()->loadRefFunction()->loadRefGroup();
    }

    /**
     * @see parent::loadRefPraticien()
     */
    public function loadRefPraticien(bool $cache = true): ?CMediusers
    {
        if ($this->_ref_praticien && $this->_ref_praticien->_id) {
            return $this->_ref_praticien;
        }
        $this->loadRefPlageConsult($cache);
        $this->_ref_executant = $this->_ref_plageconsult->_ref_chir;

        // On récupère le titulaire de la plage de consultation, car remplacement possible
        return $this->_ref_praticien = $this->_ref_plageconsult->_ref_chir;
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
     * @inheritDoc
     */
    function loadExternalIdentifiers($group_id = null)
    {
        // Iconographie de AppFine
        if (CModule::getActive("appFineClient")) {
            CAppFineClient::loadIdexConsult($this, $group_id);
        }
        // Iconographie du portail patient Doctolib
        if (CModule::getActive("doctolib")) {
            CDoctolib::loadIdex($this, $group_id);
        }
    }

    /**
     * Charge les transports de la consultation
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
     * Charge le créateur de la consultation
     *
     * @return CMediusers
     */
    function loadRefOwner()
    {
        return $this->_ref_owner = $this->loadFwdRef("owner_id", true);
    }

    /**
     * @see parent::deleteActes()
     */
    public function deleteActes(): ?string
    {
        if ($msg = parent::deleteActes()) {
            return $msg;
        }

        $this->secteur1 = "";
        $this->secteur2 = "";
        // $this->valide = 0;  Ne devrait pas être nécessaire
        $this->total_assure = 0.0;
        $this->total_amc    = 0.0;
        $this->total_amo    = 0.0;
        $this->du_patient   = 0.0;
        $this->du_tiers     = 0.0;

        return $this->store();
    }

    /**
     * @see  parent::store()
     * @todo Refactoring complet de la fonction store de la consultation
     *
     *   ANALYSE DU CODE
     *  1. Gestion du désistement
     *  2. Premier if : creation d'une consultation à laquelle on doit attacher
     *     un séjour (conf active): comportement DEPART / ARRIVEE
     *  3. Mise en cache du forfait FSE et facturable : uniquement dans le cas d'un séjour
     *  4. On load le séjour de la consultation
     *  5. On initialise le _adjust_sejour à false
     *  6. Dans le cas ou on a un séjour
     *   6.1. S'il est de type consultation, on ajuste le séjour en fonction du comportement DEPART / ARRIVEE
     *   6.2. Si la plage de consultation a été modifiée, adjust_sejour passe à true et on ajuste le séjour
     *        en fonction du comportement DEPART / ARRIVEE (en passant par l'adjustSejour() )
     *   6.3. Si on a un id (à virer) et que le chrono est modifié en PATIENT_ARRIVE,
     *        si on gère les admissions auto (conf) on met une entrée réelle au séjour
     *  7. Si le patient est modifié, qu'on est pas en train de merger et qu'on a un séjour,
     *     on empeche le store
     *  8. On appelle le parent::store()
     *  9. On passe le forfait SE et facturable au séjour
     * 10. On propage la modification du patient de la consultation au séjour
     * 11. Si on a ajusté le séjour et qu'on est dans un séjour de type conclut et que le séjour
     *     n'a plus de consultations, on essaie de le supprimer, sinon on l'annule
     * 12. Gestion du tarif et précodage des actes (bindTarif)
     * 13. Bind FSE
     * ACTIONS
     * - Faire une fonction comportement_DEPART_ARRIVEE()
     * - Merger le 2, le 6.1 et le 6.2 (et le passer en 2 si possible)
     * - Faire une fonction pour le 6.3, le 7, le 10, le 11
     * - Améliorer les fonctions 12 et 13 en incluant le test du behaviour fields
     *
     * COMPORTEMENT DEPART ARRIVEE
     * modif de la date d'une consultation ayant un séjour sur le modèle DEPART / ARRIVEE:
     * 1. Pour le DEPART :
     * -> on décroche la consultation de son ancien séjour
     * -> on ne touche pas à l'ancien séjour si :
     * - il est de type autre que consultation
     * - il a une entrée réelle
     * - il a d'autres consultations
     * -> sinon on l'annule
     *
     *   2. Pour l'ARRIVEE
     * -> si on a un séjour qui englobe : on la colle dedans
     * -> sinon on crée un séjour de consultation
     *
     *   TESTS A EFFECTUER
     *  0. Création d'un pause
     *  0.1. Déplacement d'une pause
     *  1. Création d'une consultation simple C1 (Séjour S1)
     *  2. Création d'une deuxième consultation le même jour / même patient C2 (Séjour S1)
     *  3. Création d'une troisième consultation le même jour / même patient C3 (Séjour S1)
     *  4. Déplacement de la consultation C1 un autre jour (Séjour S2)
     *  5. Changement du nom du patient C2 (pas de modification car une autre consultation)
     *  6. Déplacement de C3 au même jour (Toujours séjour S1)
     *  7. Annulation de C1 (Suppression ou annulation de S1)
     *  8. Déplacement de C2 et C3 à un autre jour (séjour S3 créé, séjour S1 supprimé ou annulé)
     *  9. Arrivée du patient pour C2 (S3 a une entrée réelle)
     * 10. Déplacement de C3 dans un autre jour (S4)
     * 11. Déplacement de C2 dans un autre jour (S5 et S3 reste tel quel)
     */
    function store()
    {
        $this->completeField(
            'owner_id',
            'creation_date',
            'sejour_id',
            'heure',
            'plageconsult_id',
            'grossesse_id',
            'si_desistement',
            'annule',
            'patient_id'
        );
        $change_slot = false;

        //Modification du créneau si la consultation est créée, si la plage de consultation à changé, si l'heure à changé ou si la consultation est rétablie
        if (
            !$this->_id
            || $this->fieldModified("plageconsult_id")
            || $this->fieldModified("heure")
            || $this->fieldModified("annule", 0)
            || $this->fieldModified("duree")
        ) {
            $change_slot = true;
        }

        //Suppression du consultation_id dans le créneau si la consultation est annulée
        if ($this->fieldModified("annule", 1)) {
            $slot_service = new SlotService();
            $slot_service->deleteConsultationOfASlot($this);
        }

        $this->loadRefPraticien()->loadRefFunction();
        $this->loadRefPatient();

        // Prévention sur la création d'une consultation pour un patient dont la personne connectée n'a pas accès
        if (!$this->_id && (CAppUI::isCabinet() || CAppUI::isGroup()) && !$this->_ref_patient->getPerm(PERM_EDIT)) {
            return 'CConsultation-You cant take appointment for this patient';
        }

        $is_new = true;

        if ($this->_id) {
            $is_new = false;
        }

        if ($is_new) {
            if ($error_msg = $this->checkPatientGroup()) {
                return $error_msg;
            }
        }

        if (!$this->_id || !$this->owner_id || !$this->creation_date) {
            if (!$this->_id) {
                $this->creation_date = "current";
                $this->owner_id      = CMediusers::get()->_id;
            } else {
                $first_log           = $this->loadFirstLog();
                $this->creation_date = $first_log->date;
                $this->owner_id      = $first_log->user_id;
            }
        }

        if (
            !$this->_id && !$this->sejour_id && !CMediusers::get()->isAdmin()
            && $this->_ref_praticien && $this->_ref_praticien->_id
        ) {
            $prefs = CPreferences::getAllPrefs($this->_ref_praticien->_id);
            if ($prefs["allowed_new_consultation"] == 0) {
                return CAppUI::tr(
                    'CConsultation-msg-The creation or modification of consultation is impossible for the practitioner %s',
                    $this->_ref_praticien
                );
            }
        }

        if ($this->si_desistement === null) {
            $this->si_desistement = 0;
        }

        $this->annule = $this->annule === null || $this->annule === '' ? 0 : $this->annule;

        // must be BEFORE loadRefSejour()
        $facturable     = $this->_facturable;
        $forfait_se     = $this->_forfait_se;
        $forfait_sd     = $this->_forfait_sd;
        $uf_soins_id    = $this->_uf_soins_id;
        $uf_medicale_id = $this->_uf_medicale_id;
        $charge_id      = $this->_charge_id;
        $unique_lit_id  = $this->_unique_lit_id;
        $service_id     = $this->_service_id;
        $mode_entree    = $this->_mode_entree;
        $mode_entree_id = $this->_mode_entree_id;

        $this->_adjust_sejour = false;

        $function = new CFunctions();

        if ($this->_function_secondary_id) {
            $function->load($this->_function_secondary_id);
        } else {
            $function = $this->_ref_chir->_ref_function;
        }

        // Consultation dans un séjour
        $sejour = $this->loadRefSejour();

        $same_year_charge_id        = CAppUI::gconf("dPcabinet CConsultation same_year_charge_id");
        $use_charge_price_indicator = CAppUI::gconf("dPplanningOp CSejour use_charge_price_indicator");

        $create_sejour_consult = false;
        if (
            $this->patient_id && !$this->sejour_id && (!$this->_id || $this->_force_create_sejour)
            && ($function->create_sejour_consult || $this->_create_sejour_activite_mixte)
        ) {
            $create_sejour_consult = true;
        }

        // On détecte également un changement du mode de traitement si config activée afin de créer un nouveau séjour
        if ($same_year_charge_id && $use_charge_price_indicator === "obl" && $sejour->_id && $charge_id && $sejour->charge_id != $charge_id) {
            $create_sejour_consult = true;
            $sejour                = new CSejour();
            $this->_ref_sejour     = $sejour;
        }

        if ($this->patient_id &&
            (!$this->_id && !$this->sejour_id && CAppUI::gconf(
                    "dPcabinet CConsultation attach_consult_sejour",
                    $function->group_id
                ))
            || $this->_force_create_sejour
            || $create_sejour_consult
        ) {
            // Recherche séjour englobant
            if (in_array($facturable, ["", null])) {
                $facturable = 1;
            }

            $datetime                      = $this->_datetime;
            $minutes_before_consult_sejour = CAppUI::gconf("dPcabinet CConsultation minutes_before_consult_sejour");
            $where                         = [];
            $where['annule']               = " = '0'";
            $where['type']                 = $this->_in_maternite ? "= 'consult'" : " != 'seances'";
            $where['patient_id']           = " = '$this->patient_id'";
            if (!CAppUI::gconf("dPcabinet CConsultation search_sejour_all_groups")) {
                $where['group_id'] = " = '$function->group_id'";
            }
            $where['facturable'] = " = '$facturable'";

            if ($same_year_charge_id && !$this->grossesse_id) {
                // Avec le même mode traitement
                if ($charge_id) {
                    $where["sejour.charge_id"] = "= '$charge_id'";
                }
                // Même année
                $where[] = "DATE_FORMAT(sejour.entree, '%Y') = '" . CMbDT::transform(
                        null,
                        $this->_datetime,
                        "%Y"
                    ) . "'";
            } else {
                $datetime_before = CMbDT::dateTime(
                    "+$minutes_before_consult_sejour minute",
                    "$this->_date $this->heure"
                );
                $where[]         = "`sejour`.`entree` <= '$datetime_before' AND `sejour`.`sortie` >= '$datetime'";
            }

            if (!$this->_force_create_sejour) {
                $sejour->loadObject($where);
            } else {
                $sejour->_id = "";
            }

            // Si pas de séjour et config (ou que le cabinet l'y autorise) alors le créer en type consultation
            if (!$sejour->_id
                && ((CAppUI::gconf("dPcabinet CConsultation create_consult_sejour")
                        && $this->_ref_praticien->activite === "salarie")
                    || $create_sejour_consult)
            ) {
                $sejour->patient_id     = $this->patient_id;
                $sejour->praticien_id   = $this->_ref_chir->_id;
                $sejour->group_id       = $function->group_id;
                $sejour->type           = "consult";
                $sejour->facturable     = $facturable;
                $sejour->uf_soins_id    = $uf_soins_id;
                $sejour->uf_medicale_id = $uf_medicale_id;
                $sejour->charge_id      = $charge_id;
                $sejour->_unique_lit_id = $unique_lit_id;
                $sejour->service_id     = $service_id;
                $sejour->mode_entree    = $mode_entree;
                $sejour->mode_entree_id = $mode_entree_id;
                $sejour->grossesse_id   = $this->grossesse_id;
                $datetime               = ($this->_date && $this->heure) ? "$this->_date $this->heure" : null;
                if ($this->chrono == self::PLANIFIE) {
                    $sejour->entree_prevue = $datetime;
                } else {
                    $sejour->entree_reelle = $datetime;
                }
                $duree_sejour          = CAppUI::gconf("dPcabinet CConsultation duree_sejour_creation_rdv");
                $sejour->sortie_prevue = ($duree_sejour) ? CMbDT::dateTime(
                    "+$duree_sejour hours",
                    $datetime
                ) : "$this->_date 23:59:59";
                if ($msg = $sejour->store()) {
                    return $msg;
                }
            }
            $this->sejour_id = $sejour->_id;
        }

        if ($this->sejour_id && $this->_sync_sejour) {
            $this->loadRefPlageConsult();

            // Si le séjour est de type consult
            if ($this->_ref_sejour->type == 'consult') {
                $this->_ref_sejour->loadRefsConsultations();
                $this->_ref_sejour->_hour_entree_prevue = null;
                $this->_ref_sejour->_min_entree_prevue  = null;
                $this->_ref_sejour->_hour_sortie_prevue = null;
                $this->_ref_sejour->_min_sortie_prevue  = null;

                $date_consult = CMbDT::date($this->_datetime);

                // On déplace l'entrée et la sortie du séjour
                $entree       = $this->_datetime;
                $duree_sejour = CAppUI::gconf("dPcabinet CConsultation duree_sejour_creation_rdv");
                $sortie       = ($duree_sejour) ? CMbDT::dateTime(
                    "+$duree_sejour hours",
                    $entree
                ) : $date_consult . " 23:59:59";

                // Si on a une entrée réelle et que la date de la consultation est avant l'entrée réelle, on sort du store
                if ($this->_ref_sejour->entree_reelle && $date_consult < CMbDT::date(
                        $this->_ref_sejour->entree_reelle
                    )) {
                    return CAppUI::tr("CConsultation-denyDayChange");
                }

                // Si on a une sortie réelle et que la date de la consultation est après la sortie réelle, on sort du store
                if ($this->_ref_sejour->sortie_reelle && $date_consult > CMbDT::date(
                        $this->_ref_sejour->sortie_reelle
                    )) {
                    return CAppUI::tr("CConsultation-denyDayChange-exit");
                }

                // S'il n'y a qu'une seule consultation dans le séjour, et que le praticien de la consultation est modifié
                // (changement de plage), alors on modifie également le praticien du séjour
                if ($this->_id && $this->fieldModified("plageconsult_id")
                    && count($this->_ref_sejour->_ref_consultations) == 1
                    && !$this->_ref_sejour->entree_reelle
                ) {
                    $this->_ref_sejour->praticien_id = $this->_ref_plageconsult->chir_id;
                }

                // S'il y a d'autres consultations dans le séjour, on étire l'entrée et la sortie
                // en parcourant la liste des consultations
                foreach ($this->_ref_sejour->_ref_consultations as $_consultation) {
                    if ($_consultation->_id != $this->_id) {
                        $_consultation->loadRefPlageConsult();
                        if ($_consultation->_datetime < $entree) {
                            $entree = $_consultation->_datetime;
                        }

                        if ($_consultation->_datetime > $sortie) {
                            $sortie = CMbDT::date($_consultation->_datetime) . " 23:59:59";
                        }
                    }
                }

                $this->_ref_sejour->entree_prevue = $entree;
                $this->_ref_sejour->sortie_prevue = $sortie;
                $this->_ref_sejour->updateFormFields();
                $this->_ref_sejour->_check_bounds = 0;
            }
            if (!$this->_ref_sejour->uf_soins_id) {
                $this->_ref_sejour->uf_soins_id = $uf_soins_id;
            }
            if (!$this->_ref_sejour->uf_medicale_id) {
                $this->_ref_sejour->uf_medicale_id = $uf_medicale_id;
            }
            if (!$this->_ref_sejour->charge_id) {
                $charge_price = new CChargePriceIndicator();
                $charge_price->load($charge_id);
                if ($charge_price->group_id == $this->_ref_sejour->group_id) {
                    $this->_ref_sejour->charge_id = $charge_id;
                }
            }
            if (in_array(
                    $this->_ref_sejour->type,
                    CSejour::getTypesSejoursUrgence($this->_ref_sejour->praticien_id)
                ) && $unique_lit_id) {
                $sejour = new CSejour();
                $sejour->load($this->sejour_id);
                $affectation                = new CAffectation();
                $affectation->sejour_id     = $this->sejour_id;
                $affectation->lit_id        = $unique_lit_id;
                $affectation->service_id    = $service_id;
                $affectation->entree        = CMbDT::dateTime();
                $affectation->_mutation_urg = true;
                $sejour->forceAffectation($affectation);
            }

            if ($this->_cancel_sejour && $this->annule && !$this->_ref_sejour->annule) {
                $this->_ref_sejour->annule = 1;
            }
            $this->_ref_sejour->store();

            // Changement de journée pour la consult
            if ($this->fieldModified("plageconsult_id")) {
                $this->_adjust_sejour = true;

                // Pas le permettre si admission est déjà faite
                $max_hours = CAppUI::gconf("dPcabinet CConsultation hours_after_changing_prat");
                if ($this->_check_prat_change && $this->_ref_sejour->entree_reelle
                    && CMbDT::dateTime("+ $max_hours HOUR", $this->_ref_sejour->entree_reelle) < CMbDT::dateTime()
                ) {
                    return CAppUI::tr("CConsultation-denyPratChange", $max_hours);
                }

                $sejour = $this->_ref_sejour;
                $this->loadRefPlageConsult();
                $dateTimePlage = $this->_datetime;
                if (!$this->sejour_id) {
                    $where               = [];
                    $where['patient_id'] = " = '$this->patient_id'";
                    $where[]             = "`sejour`.`entree` <= '$dateTimePlage' AND `sejour`.`sortie` >= '$dateTimePlage'";

                    $sejour = new CSejour();
                    $sejour->loadObject($where);
                }

                $this->adjustSejour($sejour, $dateTimePlage);
            }

            if ($this->_id && $this->fieldModified("chrono", self::PATIENT_ARRIVE)) {
                $this->completeField("plageconsult_id");
                $this->loadRefPlageConsult();
                $this->_ref_chir->loadRefFunction();
                $function = $this->_ref_chir->_ref_function;
                if ($function->admission_auto) {
                    $sejour = new CSejour();
                    $sejour->load($this->sejour_id);
                    $sejour->entree_reelle = $this->arrivee;
                    if ($msg = $sejour->store()) {
                        return $msg;
                    }
                }
            }
        }

        $patient_modified = $this->fieldModified("patient_id");

        // Si le patient est modifié et qu'il y a plus d'une consult dans le sejour, on empeche le store
        if (!$this->_forwardRefMerging && $this->sejour_id && $patient_modified && !$this->_skip_count && !$this->_sync_consults_from_sejour) {
            $this->loadRefSejour();
            $consultations = $this->_ref_sejour->countBackRefs("consultations");
            if ($consultations > 1) {
                return CAppUI::tr(
                    'CConsultation-msg-You can not change the patient from a consultation if it is contained in a stay. Dissociate the consultation or change the patient s stay.'
                );
            }
        }

        // Synchronisation AT
        $this->getType();

        if (in_array(
                $this->_type,
                CSejour::getTypesSejoursUrgence($this->_ref_sejour->praticien_id)
            ) && $this->fieldModified("date_at")) {
            $rpu = $this->_ref_sejour->_ref_rpu;
            if (!$rpu->_date_at) {
                $rpu->_date_at = true;
                $rpu->date_at  = $this->date_at;
                if ($msg = $rpu->store()) {
                    return $msg;
                }
            }
        }

        //Une consultation d'urgence ne doit pas être terminé tant qu'une inscription est présente
        $group = CGroups::loadCurrent();
        if (in_array(
                $this->_type,
                CSejour::getTypesSejoursUrgence($this->_ref_sejour->praticien_id)
            ) && $this->fieldModified("chrono", self::TERMINE)
            && !CAppUI::gconf("dPurgences CConsultation close_urg_with_inscription")) {
            $prescription = $this->_ref_sejour->loadRefPrescriptionSejour();
            $prescription->loadRefsLinesInscriptions();
            if ($prescription->_count_inscriptions) {
                return CAppUI::tr("CConsultation.no_termine.alert_inscriptions");
            }
        }

        // Update de reprise at
        // Par défaut, j+1 par rapport à fin at
        if ($this->fieldModified("fin_at") && $this->fin_at) {
            $this->reprise_at = CMbDT::dateTime("+1 DAY", $this->fin_at);
        }

        //Lors de la validation de la consultation
        // Enregistrement de la facture
        if ($this->fieldModified("valide", "1")) {
            //Si le DH est modifié, ceui ci se répercute sur le premier acte coté
            if ($this->fieldModified("secteur2") && (count($this->_tokens_ngap)
                    || count($this->_tokens_ccam)) && count($this->loadRefsActes())
            ) {
                if (count($this->_ref_actes) === 1) {
                    $acte                      = reset($this->_ref_actes);
                    $acte->_check_coded        = false;
                    $acte->montant_depassement += ($this->secteur2 - $this->_old->secteur2);
                    if ($msg = $acte->store()) {
                        return $msg;
                    }
                } /* Si il y a plus d'un acte, on vérifie le total des dépassement d'honoraires */
                else {
                    $total_dh = 0;
                    foreach ($this->_ref_actes as $_act) {
                        $total_dh += $_act->montant_depassement;
                    }

                    /* Si le secteur 2 est différent du total des dépassement des actes, on met le dépassement sur le 1er acte */
                    if ($total_dh != $this->secteur2) {
                        $_act                      = reset($this->_ref_actes);
                        $_act->_check_coded        = false;
                        $_act->montant_depassement = $this->secteur2;
                        if ($msg = $_act->store()) {
                            return $msg;
                        }

                        while ($_act = next($this->_ref_actes)) {
                            if ($_act->montant_depassement) {
                                $_act->_check_coded        = false;
                                $_act->montant_depassement = 0;
                                if ($msg = $_act->store()) {
                                    return $msg;
                                }
                            }
                        }
                    }
                }
            }

            if ($msg = CFacture::save($this)) {
                echo $msg;
            }
        }

        //Lors de dévalidation de la consultation
        if (!$this->_is_importing && $this->fieldModified("valide", "0")) {
            $reglements = $this->loadRefFacture()->loadRefsReglements();
            if (!count($reglements)) {
                /* Annulation de l'ensemble des factures de la consultation
         * Il peut y en avoir plusieurs d'actives en même temps (ex des factures n°x de frais divers) */
                foreach ($this->_ref_factures as $_facture) {
                    $_facture->cancelFacture($this);
                }
            } else {
                return CAppUI::tr('CConsultation-msg-You can not reopen a consultation with payments');
            }
        }

        if ($this->fieldModified("annule", "1")) {
            $this->loadRefConsultAnesth();
            foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
                if ($_dossier_anesth->operation_id) {
                    $_dossier_anesth->operation_id = '';
                    if ($msg = $_dossier_anesth->store()) {
                        return $msg;
                    }
                }
            }
        }

        if ($this->fieldModified("annule", "0") || ($this->annule == 0 && $this->motif_annulation)) {
            $this->motif_annulation = "";
        }

        /* Propagation of the field concern_ALD */
        if ($this->fieldModified('concerne_ALD')) {
            /* To the CSejour */
            if ($this->sejour_id) {
                $this->loadRefSejour();
                $this->_ref_sejour->ald = $this->concerne_ALD;
                $this->_ref_sejour->store();
            }

            $this->loadRefsActes();

            /* To the CActeNGAP */
            foreach ($this->_ref_actes_ngap as $_acte_ngap) {
                $_acte_ngap->_check_coded = false;
                $_acte_ngap->ald          = $this->concerne_ALD;
                $msg                      = $_acte_ngap->store();

                if ($msg) {
                    return $msg;
                }
            }

            /* To the CActeCCAM */
            foreach ($this->_ref_actes_ccam as $_acte_ccam) {
                $_acte_ccam->_check_coded = false;
                $_acte_ccam->ald          = $this->concerne_ALD;
                $msg                      = $_acte_ccam->store();

                if ($msg) {
                    return $msg;
                }
            }

            /* To the CActeLPP */
            foreach ($this->_ref_actes_lpp as $_acte_lpp) {
                $_acte_lpp->_check_coded = false;
                $_acte_lpp->concerne_ald = $this->concerne_ALD;
                $msg                     = $_acte_lpp->store();

                if ($msg) {
                    return $msg;
                }
            }
        }

        // Enregistrer un groupe séance
        if (!$this->_id) {
            $categorie = $this->loadRefCategorie();

            if ($categorie->_id && $categorie->seance) {
                $groupe_seance              = new CGroupeSeance();
                $groupe_seance->patient_id  = $this->patient_id;
                $groupe_seance->function_id = $this->_ref_praticien->_ref_function->_id;
                $groupe_seance->category_id = $this->categorie_id;

                $groupe_seance->store();
            }
        }

        /* Vidage du champ adresse_par_prat_id si le champ adresse est mis à 0 */
        if ($this->fieldModified('adresse') && $this->adresse == '0') {
            $this->completeField('adresse_par_prat_id');
            if ($this->adresse_par_prat_id) {
                $this->adresse_par_prat_id = '';
            }
        }

        /* Modifie le parcours de soins de la feuille de soins associée si celle ci existe */
        if ($this->_sync_parcours_soins && CModule::getActive('oxPyxvital')
            && ($this->fieldModified('adresse') || $this->fieldModified('adresse_par_prat_id'))
        ) {
            $fses = CPyxvitalFSE::loadForConsult($this);

            foreach ($fses as $fse) {
                if ($fse->_id && $fse->state == 'creating') {
                    $rules                          = new CSesamVitaleRuleSet(
                        new CPyxvitalCPS(),
                        new CPyxvitalCV(),
                        $fse
                    );
                    $fse->_parcours_de_soins        = $rules->isPdSneeded();
                    $fse->_synchronize_consultation = false;

                    if ($fse->_parcours_de_soins && $this->adresse && $this->adresse_par_prat_id) {
                        $patient = $this->loadRefPatient();
                        /** @var CMedecin $medecin */
                        $medecin = CMbObject::loadFromGuid("CMedecin-$this->adresse_par_prat_id");

                        if ($medecin->_id == $patient->medecin_traitant) {
                            $fse->mt_code_pds = '11';
                            $fse->medecin_id  = $medecin->_id;
                            $fse->mt_nom      = $medecin->nom;
                            $fse->mt_prenom   = $medecin->prenom;
                            $fse->mt_top_mt   = '1';
                        } else {
                            $fse->mt_code_pds = '12';
                            $fse->medecin_id  = $medecin->_id;
                            $fse->mt_nom      = $medecin->nom;
                            $fse->mt_prenom   = $medecin->prenom;
                            $fse->mt_top_mt   = $patient->medecin_traitant ? '1' : '0';
                        }
                    } elseif ($fse->_parcours_de_soins) {
                        $fse->mt_code_pds = '';
                        $fse->medecin_id  = '';
                        $fse->mt_nom      = '';
                        $fse->mt_prenom   = '';
                        $fse->mt_top_mt   = '';
                    }

                    $fse->store();
                }
            }
        }

        (new MedecinExercicePlaceService($this, 'adresse_par_prat_id', 'adresse_par_exercice_place_id'))
            ->applyFirstExercicePlace();

        if (!ConsultationRestrictionUtility::isConsultationAllowed($this)) {
            return CAppUI::tr("CConsultation-msg-Creation of city consultation not allowed");
        }

        // Standard store
        if ($msg = parent::store()) {
            return $msg;
        }

        if (($is_new || $this->_operation_id) && CAppUI::pref("create_dossier_anesth")) {
            $this->createConsultAnesth();
        }

        $this->completeField("_line_element_id");

        // Création d'une tâche si la prise de rdv est issue du plan de soin
        if ($this->_line_element_id) {
            $task                               = new CSejourTask();
            $task->consult_id                   = $this->_id;
            $task->sejour_id                    = $this->sejour_id;
            $task->prescription_line_element_id = $this->_line_element_id;
            $task->description                  = CAppUI::tr(
                'CConsultation-Consultation scheduled for %s',
                $this->_ref_plageconsult->getFormattedValue("date")
            );

            $line_element = new CPrescriptionLineElement();
            $line_element->load($this->_line_element_id);
            $this->motif = ($this->motif ? "$this->motif\n" : "") . $line_element->_view;
            $this->rques = ($this->rques ? "$this->rques\n" : "") .
                CAppUI::tr(
                    'CConsultation-Prescription of hospitalization, prescribed by Dr %s',
                    $line_element->_ref_praticien->_view
                );

            $line_element->loadRefsPrises();
            $first_prise = reset($line_element->_ref_prises);

            $key_tab  = "aucune_prise";
            $prise_id = null;

            if ($first_prise) {
                $key_tab = ($first_prise->moment_unitaire_id
                    || $first_prise->heure_prise
                    || $first_prise->condition
                    || $first_prise->datetime) ? $line_element->_chapitre : null;

                if (!$key_tab) {
                    $prise_id = $first_prise->_id;
                }
            }

            // Planification manuelle à l'heure de la consultation
            if (CPrescription::isPlanSoinsActive()) {
                $administration                      = new CAdministration();
                $administration->administrateur_id   = CAppUI::$user->_id;
                $administration->dateTime            = $this->_datetime;
                $administration->quantite            = $administration->planification = 1;
                $administration->_unite_prescription = $key_tab;
                $administration->prise_id            = $prise_id;
                $administration->setObject($line_element);

                if ($msg = $administration->store()) {
                    return $msg;
                }
            }

            $this->element_prescription_id = $line_element->element_prescription_id;

            if ($msg = $task->store()) {
                return $msg;
            }

            if ($msg = parent::store()) {
                return $msg;
            }
        }

        // On note le résultat de la tâche si la consultation est terminée
        if ($this->chrono == CConsultation::TERMINE) {
            /** @var $task CSejourTask */
            $task = $this->loadRefTask();
            if ($task->_id) {
                $task->resultat = CAppUI::tr('CConsultation-Consultation completed');
                $task->realise  = 1;
                if ($msg = $task->store()) {
                    return $msg;
                }
            }
            //Creation d'un bon à payer pour la téléconsultation
            if ($this->teleconsultation && CModule::getInstalled('teleconsultation') && CAppUI::loadPref(
                    'use_telepayment_for_teleconsultation',
                    $this->_ref_praticien->_id
                )) {
                $bon_a_payer = $this->loadRefBonAPayer();
                $facture     = $this->_ref_facture ?: $this->loadRefFacture();
                if ($facture->_id && (!$bon_a_payer->_id || $facture->du_patient != $bon_a_payer->montant)) {
                    if (!$bon_a_payer->_id) {
                        $bon_a_payer->praticien_id      = $this->loadRefPraticien()->_id;
                        $bon_a_payer->context_class     = $this->_class;
                        $bon_a_payer->context_id        = $this->_id;
                        $bon_a_payer->creation_datetime = CMbDT::dateTime();
                    }
                    $bon_a_payer->montant = $facture->du_patient;
                    if ($msg = $bon_a_payer->store()) {
                        return $msg;
                    }
                }
            }
        }
        // Forfait SE et facturable. A laisser apres le store()
        if ($this->sejour_id && CAppUI::gconf("dPcabinet CConsultation attach_consult_sejour")) {
            if ($forfait_se !== null || $facturable !== null || $forfait_sd !== null) {
                $this->_ref_sejour->forfait_se = $forfait_se;
                $this->_ref_sejour->forfait_sd = $forfait_sd;
                $this->_ref_sejour->facturable = $facturable;
                if ($msg = $this->_ref_sejour->store()) {
                    return $msg;
                }
                $this->_forfait_se     = null;
                $this->_forfait_sd     = null;
                $this->_facturable     = null;
                $this->_uf_soins_id    = null;
                $this->_uf_medicale_id = null;
                $this->_charge_id      = null;
            }
        }

        if ($this->_adjust_sejour && ($this->_ref_sejour->type === "consult") && $sejour->_id) {
            $consultations = $this->_ref_sejour->countBackRefs("consultations");
            if ($consultations < 1) {
                if ($msg = $this->_ref_sejour->delete()) {
                    $this->_ref_sejour->annule = 1;
                    if ($msg = $this->_ref_sejour->store()) {
                        return $msg;
                    }
                }
            }
        }

        // Gestion du tarif et precodage des actes
        if ($this->_bind_tarif && $this->_id) {
            if ($msg = $this->bindTarif()) {
                return $msg;
            }
        }

        // Bind FSE
        if ($this->_bind_fse && $this->_id) {
            if (CModule::getActive("fse")) {
                $fse = CFseFactory::createFSE();
                if ($fse) {
                    $fse->bindFSE($this);
                }
            }
        }

        // If it's actually a meeting, store the motive and notes in the meeting object
        if ($this->reunion_id) {
            $rappel             = $this->_rappel; // Ref Reunion Updates _rappel so we loose the form value
            $meeting            = $this->loadRefReunion();
            $meeting->remarques = $this->rques;
            $meeting->motif     = $this->motif;
            $meeting->rappel    = $rappel;
            $meeting->store($this);
        }

        if ($this->grossesse_id && $this->_type_suivi) {
            $this->getSuiviGrossesse($this->_type_suivi);
        }

        if ($change_slot) {
            $slot_service = new SlotService();
            $slot_service->addConsultToSlot($this);
        }

        return null;
    }

    /**
     * Check if the patient is in the right group (CGroup or CFunctions)
     *
     * @return string|null
     */
    public function checkPatientGroup(): ?string
    {
        if (!$this->_ref_patient) {
            $this->loadRefPatient();
        }

        if (!$this->_ref_praticien) {
            $this->loadRefPraticien();
        }

        $use_function_distinct = CAppUI::isCabinet();
        $use_group_distinct    = CAppUI::isGroup();
        $msg_error             = null;

        if ($use_function_distinct && $this->_ref_patient->function_id) {
            if ($this->_ref_praticien->function_id != $this->_ref_patient->function_id) {
                $msg_error = "CConsultation-msg-This patient is not in a regular consulting room";
            }
        } elseif ($use_group_distinct && $this->_ref_patient->group_id) {
            if ($this->_ref_praticien->loadRefFunction()->group_id != $this->_ref_patient->group_id) {
                $msg_error = "CConsultation-msg-This patient is not part of the current establishment";
            }
        }

        return $msg_error;
    }

    /**
     * Ajustement du séjour à l'enregistrement
     *
     * @param CSejour $sejour        Séjour englobant
     * @param string  $dateTimePlage Date et heure de la plage à créer
     *
     * @return string|null Store-like message
     */
    private function adjustSejour(CSejour $sejour, $dateTimePlage)
    {
        if ($sejour->_id == $this->_ref_sejour->_id) {
            return null;
        }

        // Journée dans lequel on déplace à déjà un séjour
        if ($sejour->_id) {
            // Affecte à la consultation le nouveau séjour
            $this->sejour_id = $sejour->_id;

            return null;
        }

        // Journée qui n'a pas de séjour en cible
        $count_consultations = $this->_ref_sejour->countBackRefs("consultations");

        // On déplace les dates du séjour
        if (($count_consultations == 1) && ($this->_ref_sejour->type === "consult")) {
            $this->_ref_sejour->entree_prevue       = $dateTimePlage;
            $this->_ref_sejour->sortie_prevue       = CMbDT::date($dateTimePlage) . " 23:59:59";
            $this->_ref_sejour->_hour_entree_prevue = null;
            $this->_ref_sejour->_hour_sortie_prevue = null;
            if ($msg = $this->_ref_sejour->store()) {
                return $msg;
            }

            return null;
        }

        // On créé le séjour de consultation
        $sejour->patient_id    = $this->patient_id;
        $sejour->praticien_id  = $this->_ref_chir->_id;
        $sejour->group_id      = CGroups::loadCurrent()->_id;
        $sejour->type          = "consult";
        $sejour->entree_prevue = $dateTimePlage;
        $sejour->sortie_prevue = CMbDT::date($dateTimePlage) . " 23:59:59";

        if ($msg = $sejour->store()) {
            return $msg;
        }

        $this->sejour_id = $sejour->_id;

        return null;
    }

    /**
     * Détermine le type de la consultation
     *
     * @return string Un des types possibles urg, anesth
     * @throws Exception
     */
    public function getType(): void
    {
        $praticien = $this->loadRefPraticien();
        $sejour    = $this->_ref_sejour;

        if (!$sejour) {
            $sejour = $this->loadRefSejour();
        }

        if (!$sejour->_ref_rpu) {
            $sejour->loadRefRPU();
        }

        // Consultations d'urgences
        if ($praticien->isUrgentiste() && $sejour->_ref_rpu && $sejour->_ref_rpu->_id) {
            $this->_type = (CAppUI::gconf("dPurgences CRPU type_sejour") === "urg_consult") ? "consult" : "urg";
        }

        // Consultation préanesthésique
        if ($this->countDossiersAnesth()) {
            $this->_type = "anesth";
        }
    }

    /**
     * Crée la dossier d'anesthésie associée à la consultation
     *
     * @return null|string Store-like message
     * @throws Exception
     */
    public function createConsultAnesth(): ?string
    {
        $this->loadRefPlageConsult();

        if (
            !$this->_is_anesth
            || !$this->patient_id
            || !$this->_id
            || ($this->type == "entree")
            || !CAppUI::gconf('dPcabinet CConsultAnesth active')
        ) {
            return null;
        }

        // Création de la consultation préanesthésique
        $this->_count["consult_anesth"] = null;
        $consultAnesth                  = $this->loadRefConsultAnesth();
        $operation                      = new COperation();
        if (!$consultAnesth->_id || $this->_operation_id) {
            if (!$consultAnesth->_id) {
                $consultAnesth->consultation_id = $this->_id;
                $consultAnesth->sejour_id       = $this->sejour_id;
            }
            if ($this->_operation_id) {
                // Association à l'intervention
                $consultAnesth->operation_id = $this->_operation_id;
                $operation                   = $consultAnesth->loadRefOperation();
            }
            if ($msg = $consultAnesth->store()) {
                return $msg;
            }
        }

        // Remplissage du motif préanesthésique si creation et champ motif vide
        if ($operation->_id) {
            $format_motif = CAppUI::gconf('dPcabinet CConsultAnesth format_auto_motif');
            $format_rques = CAppUI::gconf('dPcabinet CConsultAnesth format_auto_rques');

            if (($format_motif && !$this->motif) || ($format_rques && !$this->rques)) {
                $operation = $consultAnesth->_ref_operation;
                $operation->loadRefPlageOp();
                $sejour = $operation->loadRefSejour();
                $chir   = $operation->loadRefChir();
                $chir->updateFormFields();

                $items = [
                    '%N' => $chir->_user_last_name,
                    '%P' => $chir->_user_first_name,
                    '%S' => $chir->_shortview,
                    '%L' => $operation->libelle,
                    '%i' => CMbDT::format($operation->_datetime_best, CAppUI::conf('time')),
                    '%I' => CMbDT::format($operation->_datetime_best, CAppUI::conf('date')),
                    '%E' => CMbDT::format($sejour->entree_prevue, CAppUI::conf('date')),
                    '%e' => CMbDT::format($sejour->entree_prevue, CAppUI::conf('time')),
                    '%T' => strtoupper(substr($sejour->type, 0, 1)),
                ];

                if ($format_motif && !$this->motif) {
                    $this->motif = str_replace(array_keys($items), $items, $format_motif);
                }

                if ($format_rques && !$this->rques) {
                    $this->rques = str_replace(array_keys($items), $items, $format_rques);
                }

                if ($msg = parent::store()) {
                    return $msg;
                }
            }
        }

        return null;
    }

    /**
     * Charge la tâche de séjour possiblement associée
     *
     * @return CSejourTask
     */
    function loadRefTask()
    {
        return $this->_ref_task = $this->loadUniqueBackRef("task");
    }

    /**
     * Charge le bon à payer de la téléconsultation
     *
     * @return CBonAPayer
     */
    public function loadRefBonAPayer()
    {
        return $this->_ref_bon_a_payer = $this->loadUniqueBackRef('bon_a_payer');
    }

    /**
     * @see parent::bindTarif()
     */
    public function bindTarif(): ?string
    {
        $this->_bind_tarif = false;

        if (!$this->exec_tarif) {
            $this->exec_tarif = CAppUI::pref("use_acte_date_now") ? CMbDT::dateTime() : $this->_acte_execution;
        } elseif (CAppUI::pref("use_acte_date_now")) {
            $this->exec_tarif = CMbDT::dateTime();
        }

        // Chargement de l'objet CTarif ou CDevisCodage
        $codable = CMbObject::loadFromGuid($this->_codable_guid);

        if ($codable && get_class($codable) == CTarif::class) { // Via un tarif
            // Cas de la cotation normale
            $this->secteur1 += $codable->secteur1;
            $this->secteur2 += $codable->secteur2;
            $this->secteur3 += $codable->secteur3;
            $this->taux_tva = $codable->taux_tva;

            if (!$this->tarif) {
                $this->tarif = $codable->description;
            }

            // Mise à jour de codes CCAM prévus, sans information serialisée complémentaire
            foreach ($codable->_codes_ccam as $_code_ccam) {
                $this->_codes_ccam[] = substr($_code_ccam, 0, 7);
            }

            $this->codes_ccam = $this->updateCCAMPlainField();

            if ($msg = $this->store()) {
                return $msg;
            }

            $chir_id = $this->getExecutantId();

            $this->_acte_execution = $this->exec_tarif;

            $this->codes_ccam = $codable->codes_ccam;
            // Precodage des actes CCAM avec information sérialisée complète
            if ($msg = $this->precodeActeCCAM()) {
                return $msg;
            }

            // Precodage des actes NGAP avec information sérialisée complète
            $this->_tokens_ngap = $codable->codes_ngap;
            if ($msg = $this->precodeActe("_tokens_ngap", "CActeNGAP", $chir_id)) {
                return $msg;
            }

            $this->codes_ccam = $this->updateCCAMPlainField();

            if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
                /* Precodage des actes LPP avec information sérialisée complète */
                $this->_tokens_lpp = $codable->codes_lpp;
                if ($msg = $this->precodeActe('_tokens_lpp', 'CActeLPP', $this->getExecutantId())) {
                    return $msg;
                }
            }
        } elseif ($codable && get_class($codable) == CDevisCodage::class) { // Via un devis
            $this->secteur1 += $codable->base;
            $this->secteur2 += $codable->dh;
            $this->secteur3 += $codable->ht;
            $this->taux_tva = $codable->tax_rate;

            $actes             = $codable->loadRefsActes();
            $this->_codes_ccam = explode('|', $codable->codes_ccam);
            $this->codes_ccam  = $this->updateCCAMPlainField();

            if ($msg = $this->store()) {
                return $msg;
            }

            $temp_code_ccam = $temp_code_ngap = [];

            foreach ($actes as $_acte) {
                $this->_ref_actes[] = $_acte;
                switch (get_class($_acte)) {
                    case CActeCCAM::class :
                        $temp_code_ccam[] = $_acte->_full_code;
                        break;
                    case CActeNGAP::class :
                        $temp_code_ngap[] = $_acte->_full_code;
                        break;
                }
            }

            //CCAM
            $this->codes_ccam = implode('|', $temp_code_ccam);
            // Precodage des actes CCAM avec information sérialisée complète
            if ($msg = $this->precodeActeCCAM()) {
                return $msg;
            }
            $this->codes_ccam = $this->updateCCAMPlainField();

            //NGAP
            $chir_id = $this->getExecutantId();
            // Precodage des actes NGAP avec information sérialisée complète
            $this->_tokens_ngap = implode('|', $temp_code_ngap);
            if ($msg = $this->precodeActe("_tokens_ngap", "CActeNGAP", $chir_id)) {
                return $msg;
            }

            if (!$this->tarif) {
                $this->tarif = $codable->libelle;
            }

            if ($msg = $this->store()) {
                return $msg;
            }
        }

        $this->calculTVA();

        $this->loadRefsActes();

        if (is_array($this->_ref_actes) && count($this->_ref_actes)) {
            $this->doUpdateMontants();
        }

        $this->du_patient = $this->secteur1 + $this->secteur2 + $this->secteur3 + $this->du_tva;

        return null;
    }

    /**
     * @see parent::getExecutantId()
     */
    public function getExecutantId(string $code_activite = null): int
    {
        $user = CMediusers::get();
        if (!($user->isProfessionnelDeSante() && CAppUI::pref("user_executant"))) {
            $this->loadRefPlageConsult();

            $user = $this->_ref_chir;
        }

        if ($user->loadRefRemplacant($this->_acte_execution)) {
            $user = $user->_ref_remplacant;
        }

        return $user->_id;
    }

    /**
     * Précode les actes CCAM prévus de la consultation
     *
     * @return string Store-like message
     */
    public function precodeActeCCAM(): ?string
    {
        $this->loadRefPlageConsult();

        return $this->precodeCCAM($this->_ref_chir->_id);
    }

    /**
     * Calcul de la TVA assujetti au secteur 3
     *
     * @return int
     */
    function calculTVA()
    {
        return $this->du_tva = round($this->secteur3 * $this->taux_tva / 100, 2);
    }

    /**
     * @see parent::doUpdateMontants()
     */
    public function doUpdateMontants(): ?string
    {
        // Initialisation des montants
        $secteur1_CCAM_NGAP = 0;
        $secteur2_CCAM_NGAP = 0;

        $this->secteur1 = 0;
        $this->secteur2 = 0;
        $this->loadRefsActes();

        if (count($this->_ref_frais_divers)) {
            $this->secteur3 = 0;
        }

        foreach ($this->_ref_actes as $_acte) {
            switch ($_acte->_class) {
                case "CFraisDivers":
                    $this->secteur3 += $_acte->montant_base + $_acte->montant_depassement;
                    break;
                case "CActeNGAP":
                    $secteur1_CCAM_NGAP += $_acte->montant_base;
                    $secteur2_CCAM_NGAP += $_acte->montant_depassement;
                    break;
                case "CActeCCAM":
                    $secteur1_CCAM_NGAP += round($_acte->getTarif(), 2);
                    $secteur2_CCAM_NGAP += $_acte->montant_depassement;
                    break;
                case "CActeLPP":
                    $secteur1_CCAM_NGAP += round($_acte->montant_final, 2);
                    $secteur2_CCAM_NGAP += $_acte->montant_depassement;
                    break;
                default:
                    break;
            }
        }

        // Remplissage des montant de la consultation
        $this->secteur1 += $secteur1_CCAM_NGAP;
        $this->secteur2 += $secteur2_CCAM_NGAP;

        if ($secteur1_CCAM_NGAP == 0 && $secteur2_CCAM_NGAP == 0) {
            $this->du_patient = $this->secteur1 + $this->secteur2 + $this->secteur3 + $this->du_tva;
        }

        // Cotation manuelle
        $this->completeField("tarif");
        if (!$this->tarif && $this->_count_actes) {
            $this->tarif = "Codage manuel";
        } elseif (!$this->_count_actes && $this->tarif == "Codage manuel") {
            $this->tarif = "";
        }

        return $this->store();
    }

    /**
     * Charge la réunion de la consultation
     *
     * @return CReunion
     */
    function loadRefReunion()
    {
        $this->_ref_reunion = $this->loadFwdRef("reunion_id");
        $this->_rappel      = $this->_ref_reunion->rappel;

        return $this->_ref_reunion;
    }

    /**
     * Force la création d'un suivi grossesse si demande de chargement
     *
     * @param string $type_suivi Type de suivi
     *
     * @return CSuiviGrossesse
     */
    function getSuiviGrossesse($type_suivi = "surv")
    {
        $suivi = $this->loadRefSuiviGrossesse();
        if ($this->_id && !$suivi->_id) {
            $suivi                  = new CSuiviGrossesse();
            $suivi->consultation_id = $this->_id;
            $suivi->type_suivi      = $type_suivi;
            $suivi->store();
        }

        return $this->_ref_suivi_grossesse = $suivi;
    }

    /**
     * Charge le suivi de grossesse possiblement associé
     *
     * @return CSuiviGrossesse
     */
    function loadRefSuiviGrossesse()
    {
        return $this->_ref_suivi_grossesse = $this->loadUniqueBackRef("suivi_grossesse");
    }

    function loadPosition()
    {
        if (!$this->sejour_id) {
            return;
        }

        $ds   = $this->getDS();
        $sql  = "SELECT type FROM sejour WHERE sejour_id = '$this->sejour_id'";
        $type = $ds->loadResult($sql);

        // only for seances
        if ($type != "seances") {
            return;
        }

        $sql       = "SELECT consultation.plageconsult_id, date, heure, consultation_id
    FROM plageconsult, consultation
    WHERE consultation.plageconsult_id = plageconsult.plageconsult_id
      AND sejour_id = '$this->sejour_id'
      AND annule = '0'
    ORDER BY date, heure";
        $list      = $ds->loadList($sql);
        $seance_nb = 1;
        foreach ($list as $_seance) {
            if ($_seance["heure"] == $this->heure && $_seance["plageconsult_id"] == $this->plageconsult_id) {
                $this->_consult_sejour_nb = $seance_nb;
                break;
            }
            $seance_nb++;
        }
        $this->_consult_sejour_out_of_nb = count($list);
    }

    function delete()
    {
        $this->completeField("patient_id", "plageconsult_id", "groupee");

        //Suppression du consultation_id dans le créneau si la consultation est supprimée
        $slot_service = new SlotService();
        $slot_service->deleteConsultationOfASlot($this);

        if ($msg = parent::delete()) {
            return $msg;
        }

        if (!$this->groupee || !$this->patient_id) {
            return null;
        }

        $this->loadRefPlageConsult();

        $consult = new self();
        $where   = [
            "date"       => "= '$this->_date'",
            "patient_id" => "= '$this->patient_id'",
        ];
        $ljoin   = [
            "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id",
        ];

        if ($consult->countList($where, null, $ljoin)) {
            return null;
        }

        $reservation             = new CReservation();
        $reservation->date       = $this->_date;
        $reservation->patient_id = $this->patient_id;

        foreach ($reservation->loadMatchingList() as $_reservation) {
            if ($msg = $_reservation->delete()) {
                return $msg;
            }
        }

        return null;
    }

    /**
     * Charge la salle
     *
     * @return CRoom
     */
    function loadRefRoom()
    {
        return $this->_ref_room = $this->loadUniqueBackRef("rooms");
    }

    /**
     * Charge le lit pour la main courante
     *
     * @return CLit
     */
    function loadRefLit()
    {
        return $this->_ref_lit = $this->loadFwdRef("lit_id");
    }

    /**
     * Charge l'accident de travail de la consultation
     *
     * @return CAccidentTravail
     */
    function loadRefAccidentTravail()
    {
        return $this->_ref_accident_travail = $this->loadUniqueBackRef("accident_travail");
    }

    /**
     * Charge l'élément de prescription possiblement associé
     *
     * @return CElementPrescription
     */
    function loadRefElementPrescription()
    {
        return $this->_ref_element_prescription = $this->loadFwdRef("element_prescription_id", true);
    }

    /**
     * @see parent::loadComplete()
     */
    function loadComplete()
    {
        parent::loadComplete();

        if (!$this->_ref_patient) {
            $this->loadRefPatient();
        }
        $this->_ref_patient->loadRefLatestConstantes();

        if (!$this->_ref_actes_ccam) {
            $this->loadRefsActesCCAM();
        }
        foreach ($this->_ref_actes_ccam as $_acte) {
            $_acte->loadRefExecutant();
        }

        $this->loadRefConsultAnesth();
        foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
            $_dossier_anesth->loadRefOperation();
        }
    }

    /**
     * @see parent::getActeExecution()
     */
    public function getActeExecution(): string
    {
        $this->loadRefPlageConsult();

        return $this->_acte_execution;
    }

    /**
     * Charge les éléments de codage CCAM
     *
     * @return CCodageCCAM[]
     * @throws Exception
     */
    public function loadRefsCodagesCCAM(): array
    {
        parent::loadRefsCodagesCCAM();

        /* Si l'enveloppe de  codage du praticien n'existe pas, elle est créée automatiquement */
        $chir = $this->loadRefPraticien();

        if ($chir->loadRefRemplacant($this->_acte_execution)) {
            $chir = $chir->_ref_remplacant;
        }
        if (!array_key_exists($chir->_id, $this->_ref_codages_ccam)) {
            $_codage                             = CCodageCCAM::get($this, $chir->_id, 1);
            $this->_ref_codages_ccam[$chir->_id] = [$_codage];
        }

        return $this->_ref_codages_ccam;
    }

    /**
     * @see parent::preparePossibleActes()
     */
    public function preparePossibleActes(): void
    {
        $this->loadRefPlageConsult();
    }

    /**
     * @inheritdoc
     */
    function loadRefsDocs($where = [], bool $with_canelled = true)
    {
        parent::loadRefsDocs($where);

        if (!$this->_docitems_from_dossier_anesth) {
            // On ajoute les documents des dossiers d'anesthésie
            if (!$this->_refs_dossiers_anesth) {
                $this->loadRefConsultAnesth();
            }

            foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
                $_dossier_anesth->_docitems_from_consult = true;
                $_dossier_anesth->loadRefsDocs($where, $with_canelled);
                $this->_ref_documents = CMbArray::mergeKeys($this->_ref_documents, $_dossier_anesth->_ref_documents);
            }
        }

        return count($this->_ref_documents);
    }

    /**
     * @see parent::loadRefsBack()
     * @deprecated
     */
    function loadRefsBack()
    {
        // Backward references
        $this->loadRefsDocItems();
        $this->countDocItems();
        $this->loadRefConsultAnesth();

        $this->loadRefsExamsComp();

        $this->loadRefsFichesExamen();
        $this->loadRefsActesCCAM();
        $this->loadRefsActesNGAP();
        $this->loadRefFacture()->loadRefsReglements();
    }

    /**
     * @see parent::countDocItems()
     */
    function countDocItems($permType = null)
    {
        if (!$this->_nb_files_docs) {
            parent::countDocItems($permType);
        }

        if ($this->_nb_files_docs) {
            $this->getEtat();
            $this->_etat .= " ($this->_nb_files_docs)";
        }

        return $this->_nb_files_docs;
    }

    /**
     * Calcule l'état visible d'une consultation
     *
     * @return string
     */
    function getEtat()
    {
        $etat                       = [];
        $etat[self::PLANIFIE]       = CAppUI::tr('common-action-Plan-court');
        $etat[self::PATIENT_ARRIVE] = CMbDT::format($this->arrivee, "%Hh%M");
        $etat[self::EN_COURS]       = CAppUI::tr('common-In progress');
        $etat[self::TERMINE]        = CAppUI::tr('common-Completed-court');
        $etat[self::DEMANDE]        = CAppUI::tr('common-Asked');

        if ($this->chrono) {
            $this->_etat = $etat[$this->chrono];
        }

        if ($this->annule) {
            $this->_etat = CAppUI::tr('common-Canceled-court');
        }

        return $this->_etat;
    }

    /**
     * Charge les examens complémentaires à réaliser
     *
     * @return CExamComp[]
     */
    function loadRefsExamsComp()
    {
        $order = "examen";
        /** @var CExamComp $examcomps */
        $examcomps = $this->loadBackRefs("examcomp", $order);

        foreach ($examcomps as $_exam) {
            $this->_types_examen[$_exam->realisation][$_exam->_id] = $_exam;
        }

        return $this->_ref_examcomp = $examcomps;
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        $this->loadRefPlageConsult();

        return $this->_ref_chir->getPerm($permType) && parent::getPerm($permType);
    }

    /**
     * @throws Exception
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template)
    {
        $this->updateFormFields();
        $this->loadRefsFwd();
        $this->_ref_plageconsult->loadRefsFwd();
        $this->_ref_plageconsult->_ref_chir->fillTemplate($template);
        $this->_ref_patient->fillTemplate($template);
        $this->fillLimitedTemplate($template);
        if (CModule::getActive('dPprescription')) {
            // Chargement du fillTemplate de la prescription
            $this->loadRefsPrescriptions();
            $prescription       = isset($this->_ref_prescriptions["externe"]) ?
                $this->_ref_prescriptions["externe"] :
                new CPrescription();
            $prescription->type = "externe";
            $prescription->fillLimitedTemplate($template);
        }

        $sejour = $this->loadRefSejour();

        $sejour->fillLimitedTemplate($template);
        $rpu = $sejour->loadRefRPU();
        if ($rpu && $rpu->_id) {
            $rpu->fillLimitedTemplate($template);
        }

        if (!$this->countDossiersAnesth() && CModule::getActive("dPprescription")) {
            $sejour->loadRefsPrescriptions();
            $prescription       = isset($sejour->_ref_prescriptions["pre_admission"]) ?
                $sejour->_ref_prescriptions["pre_admission"] :
                new CPrescription();
            $prescription->type = "pre_admission";
            $prescription->fillLimitedTemplate($template);
            $prescription       = isset($sejour->_ref_prescriptions["sejour"]) ?
                $sejour->_ref_prescriptions["sejour"] :
                new CPrescription();
            $prescription->type = "sejour";
            $prescription->fillLimitedTemplate($template);
            $prescription       = isset($sejour->_ref_prescriptions["sortie"]) ?
                $sejour->_ref_prescriptions["sortie"] :
                new CPrescription();
            $prescription->type = "sortie";
            $prescription->fillLimitedTemplate($template);
        }

        $facture = $this->loadRefFacture();
        $facture->fillLimitedTemplate($template);
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->calculTVA();
        $this->_somme = (float)$this->secteur1 + (float)$this->secteur2 + $this->secteur3 + $this->du_tva;

        $this->du_patient = round($this->du_patient, 2);
        $this->du_tiers   = round($this->du_tiers, 2);

        $this->_hour          = intval(substr($this->heure, 0, 2));
        $this->_min           = intval(substr($this->heure, 3, 2));
        $this->_check_adresse = $this->adresse;

        $this->_view = CAppUI::tr('CConsultation-Consultation %s', $this->getEtat());

        // si _coded vaut 1 alors, impossible de modifier la cotation
        $this->_coded = $this->valide;
    }

    /**
     * Champs d'examen à afficher
     *
     * @return string[] Noms interne des champs
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    public function getExamFields(): array
    {
        $cache = Cache::getCache(Cache::INNER);

        $fields = $cache->get(self::KEY_CACHE_EXAM_FIELDS);

        if (is_array($fields) && count($fields)) {
            return $this->_exam_fields = $fields;
        }

        $fields = [
            "motif",
            "rques",
        ];

        if (CAppUI::gconf("dPcabinet CConsultation show_histoire_maladie")) {
            $fields[] = "histoire_maladie";
        }
        if (CAppUI::gconf("dPcabinet CConsultation show_examen")) {
            $fields[] = "examen";
        }
        if (CAppUI::pref("view_traitement")) {
            $fields[] = "traitement";
        }
        if (CAppUI::gconf("dPcabinet CConsultation show_projet_soins")) {
            $fields[] = "projet_soins";
        }
        if (CAppUI::gconf("dPcabinet CConsultation show_conclusion")) {
            $fields[] = "conclusion";
        }
        // Consultation d'urgence
        $praticien = $this->loadRefPraticien();
        if (CAppUI::gconf('dPurgences CRPU resultats_rpu_field_view') && $praticien->isUrgentiste()) {
            $fields[] = "resultats";
        }

        $cache->set(self::KEY_CACHE_EXAM_FIELDS, $fields);

        return $this->_exam_fields = $fields;
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template)
    {
        $this->updateFormFields();
        $this->loadRefsFwd();

        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $consultation_section = CAppUI::tr('CConsultation');      //todo: traductions sur tous les champs

        $template->addDateProperty("Consultation - date", $this->_ref_plageconsult->date);
        $template->addLongDateProperty("Consultation - date longue", $this->_ref_plageconsult->date);
        $template->addLongDateProperty(
            CAppUI::tr("CConsultation-date-day-long-lowercase"),
            $this->_ref_plageconsult->date,
            true
        );
        $template->addTimeProperty("Consultation - heure", $this->heure);
        $locExamFields = [
            "motif"            => "motif",
            "rques"            => "remarques",
            "examen"           => "examen",
            "traitement"       => "traitement",
            "histoire_maladie" => "histoire maladie",
            "projet_soins"     => "projet_soins",
            "conclusion"       => strtolower(CAppUI::tr("CConsultation-conclusion")),
            "resultats"        => "resultats",
        ];

        foreach ($this->getExamFields() as $field) {
            $loc_field = $locExamFields[$field];

            if ($this->_specs[$field]->markdown) {
                $template->addMarkdown("Consultation - $loc_field", $this->$field);
            } else {
                $template->addProperty("Consultation - $loc_field", $this->$field);
            }
        }

        if (!in_array("traitement", $this->_exam_fields)) {
            if ($this->_specs["traitement"]->markdown) {
                $template->addMarkdown("Consultation - traitement", $this->traitement);
            } else {
                $template->addProperty("Consultation - traitement", $this->traitement);
            }
        }

        $medecin         = $this->loadRefAdresseParPraticien();
        $medecin_service = new MedecinFieldService(
            $medecin,
            $this->loadRefAdresseParExercicePlace()
        );

        $nom = "{$medecin->nom} {$medecin->prenom}";
        $template->addProperty("Consultation - adressé par", $nom);
        $template->addProperty(
            "Consultation - adressé par - adresse",
            "{$medecin_service->getAdresse()}\n{$medecin_service->getCP()} {$medecin_service->getVille()}"
        );

        $template->addProperty("Consultation - Accident du travail", $this->getFormattedValue("date_at"));
        $libelle_at = $this->date_at ? "Accident du travail du " . $this->getFormattedValue("date_at") : "";
        $template->addProperty("Consultation - Libellé accident du travail", $libelle_at);

        $this->loadRefsFiles();
        $list = CMbArray::pluck($this->_ref_files, "file_name");
        $template->addListProperty("Consultation - Liste des fichiers", $list);

        // Avis arrêt de travail
        if (CModule::getActive("ameli")) {
            /** @var CAvisArretTravail $last_avis_travail */
            $this->loadRefsAvisArretsTravail();
            $last_avis_travail = new CAvisArretTravail();
            if ($this->_refs_avis_arrets_travail && is_array($this->_refs_avis_arrets_travail)) {
                $last_avis_travail = end($this->_refs_avis_arrets_travail);
            }

            $template->addProperty(
                "Consultation - Début arrêt de travail",
                CMbDT::dateToLocale($last_avis_travail->debut)
            );
            $template->addProperty(
                "Consultation - Type arrêt de travail",
                $last_avis_travail->getFormattedValue("type")
            );
            $template->addProperty("Consultation - Fin arrêt de travail", CMbDT::dateToLocale($last_avis_travail->fin));
            $template->addProperty(
                "Consultation - Accident de travail causé par un tiers",
                $last_avis_travail->getFormattedValue("accident_tiers")
            );
            $template->addProperty("Consultation - Motif arrêt maladie", $last_avis_travail->libelle_motif);
        } else {
            $template->addProperty(
                "Consultation - Fin arrêt de travail",
                CMbDT::dateToLocale(CMbDT::date($this->fin_at))
            );
            $template->addProperty(
                "Consultation - Prise en charge arrêt de travail",
                $this->getFormattedValue("pec_at")
            );
            $template->addProperty(
                "Consultation - Reprise de travail",
                CMbDT::dateToLocale(CMbDT::date($this->reprise_at))
            );
            $template->addProperty(
                "Consultation - Accident de travail sans arrêt de travail",
                $this->getFormattedValue("at_sans_arret")
            );
            $template->addProperty("Consultation - Arrêt maladie", $this->getFormattedValue("arret_maladie"));
        }


        $template->addProperty("Consultation - Documents nécessaires", nl2br($this->docs_necessaires), [], false);
        $template->addProperty("Consultation - Soins infirmiers", $this->soins_infirmiers);


        $facture = $this->loadRefFacture();
        $template->addProperty("Consultation - Numéro de facture", $facture ? $facture->_view : "");

        $this->loadRefsExamsComp();
        $exam = new CExamComp();

        foreach ($exam->_specs["realisation"]->_locales as $key => $locale) {
            $exams = isset($this->_types_examen[$key]) ? $this->_types_examen[$key] : [];
            foreach ($exams as $_exam) {
                if ($_exam->fait) {
                    $_exam->_view .= " (Fait)";
                }
            }
            $template->addListProperty("Consultation - Examens complémentaires - $locale", $exams);
        }

        if (CModule::getActive("forms")) {
            CExObject::addFormsToTemplate($template, $this, "Consultation");
        }

        if (CModule::getActive("oxCabinet")) {
            $this->loadRefsActesCCAM();
            $this->loadRefsActesNGAP();

            $actes = array_merge($this->_ref_actes_ccam, $this->_ref_actes_ngap);

            foreach ($actes as $_acte) {
                $_acte->loadRefPrescription()->loadRefsLinesElement(null, 'soin');
            }

            $smarty = new CSmartyDP("modules/dPcabinet");
            $smarty->assign("consult", $this);
            $smarty->assign("actes", $actes);

            $content_actes = $smarty->fetch("inc_actes_motifs.tpl");
            $content_actes = preg_replace("/\r\n/", "", $content_actes);
            $content_actes = preg_replace("/\n/", "", $content_actes);

            $template->addProperty("Consultation - Actes et motifs", $content_actes, null, false);
        }

        // Séjour et/ou intervention créés depuis la consultation
        $this->loadBackRefs("sejours_lies");
        $sejour_relie = reset($this->_back["sejours_lies"]);
        $this->loadBackRefs("intervs_liees");
        $interv_reliee = reset($this->_back["intervs_liees"]);

        if ($interv_reliee) {
            $sejour_relie = $interv_reliee->loadRefSejour();
        } else {
            if (!$sejour_relie) {
                $sejour_relie = new CSejour();
            }
            if (!$interv_reliee) {
                $interv_reliee = new COperation();
            }
        }

        $interv_reliee->loadRefChir();
        $interv_reliee->loadRefPlageOp();
        $interv_reliee->loadRefSalle();
        $sejour_relie->loadRefPraticien();

        // Intervention reliée
        $template->addProperty("Consultation - Opération reliée - Chirurgien", $interv_reliee->_ref_chir->_view);
        $template->addProperty("Consultation - Opération reliée - Libellé", $interv_reliee->libelle);
        $template->addProperty("Consultation - Opération reliée - Salle", $interv_reliee->_ref_salle->nom);
        $template->addDateProperty("Consultation - Opération reliée - Date", $interv_reliee->_datetime_best);

        // Séjour relié
        $template->addDateProperty("Consultation - Séjour relié - Date entrée", $sejour_relie->entree);
        $template->addLongDateProperty("Consultation - Séjour relié - Date entrée (longue)", $sejour_relie->entree);
        $template->addTimeProperty("Consultation - Séjour relié - Heure entrée", $sejour_relie->entree);
        $template->addDateProperty("Consultation - Séjour relié - Date sortie", $sejour_relie->sortie);
        $template->addLongDateProperty("Consultation - Séjour relié - Date sortie (longue)", $sejour_relie->sortie);
        $template->addTimeProperty("Consultation - Séjour relié - Heure sortie", $sejour_relie->sortie);

        $template->addDateProperty("Consultation - Séjour relié - Date entrée réelle", $sejour_relie->entree_reelle);
        $template->addTimeProperty("Consultation - Séjour relié - Heure entrée réelle", $sejour_relie->entree_reelle);
        $template->addDateProperty("Consultation - Séjour relié - Date sortie réelle", $sejour_relie->sortie_reelle);
        $template->addTimeProperty("Consultation - Séjour relié - Heure sortie réelle", $sejour_relie->sortie_reelle);
        $template->addProperty("Consultation - Séjour relié - Praticien", "Dr " . $sejour_relie->_ref_praticien->_view);
        $template->addProperty("Consultation - Séjour relié - Libelle", $sejour_relie->getFormattedValue("libelle"));

        $this->getSA();

        if ($suivi_grossesse = $this->loadRefSuiviGrossesse()) {
            $suivi_grossesse_section = CAppUI::tr("CSuiviGrossesse");

            $sa = $this->_sa . " " . CAppUI::tr('CGrossesse-_semaine_grossesse-court') . " + " . $this->_ja . " J";
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CGrossesse.SA_date_consultation"),
                $sa
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-type_suivi"),
                $suivi_grossesse->getFormattedValue("type_suivi")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-evenements_anterieurs"
                ),
                $suivi_grossesse->getFormattedValue("evenements_anterieurs")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-metrorragies"),
                $suivi_grossesse->getFormattedValue("metrorragies")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-leucorrhees"),
                $suivi_grossesse->getFormattedValue("leucorrhees")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-contractions_anormales"
                ),
                $suivi_grossesse->getFormattedValue("contractions_anormales")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-mouvements_foetaux"
                ),
                $suivi_grossesse->getFormattedValue("mouvements_foetaux")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-troubles_digestifs"
                ),
                $suivi_grossesse->getFormattedValue("troubles_digestifs")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-troubles_urinaires"
                ),
                $suivi_grossesse->getFormattedValue("troubles_urinaires")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-autres_anomalies"
                ) . " (" . CAppUI::tr("CSuiviGrossesse-functionnal_signs") . ")",
                $suivi_grossesse->getFormattedValue("autres_anomalies")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-hypertension"),
                $suivi_grossesse->getFormattedValue("hypertension")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-mouvements_actifs"),
                $suivi_grossesse->getFormattedValue("mouvements_actifs")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-auscultation_cardio_pulm"
                ),
                $suivi_grossesse->getFormattedValue("auscultation_cardio_pulm")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-examen_seins"),
                $suivi_grossesse->getFormattedValue("examen_seins")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-circulation_veineuse"
                ),
                $suivi_grossesse->getFormattedValue("circulation_veineuse")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-oedeme_membres_inf"
                ),
                $suivi_grossesse->getFormattedValue("oedeme_membres_inf")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-rques_examen_general"
                ),
                $suivi_grossesse->getFormattedValue("rques_examen_general")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-bruit_du_coeur"),
                $suivi_grossesse->getFormattedValue("bruit_du_coeur")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-col_normal"),
                $suivi_grossesse->getFormattedValue("col_normal")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-longueur_col"),
                $suivi_grossesse->getFormattedValue("longueur_col")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-position_col"),
                $suivi_grossesse->getFormattedValue("position_col")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-dilatation_col"),
                $suivi_grossesse->getFormattedValue("dilatation_col")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-dilatation_col_num"
                ),
                $suivi_grossesse->getFormattedValue("dilatation_col_num")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-consistance_col"),
                $suivi_grossesse->getFormattedValue("consistance_col")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-col_commentaire"),
                $suivi_grossesse->getFormattedValue("col_commentaire")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-presentation_position"
                ),
                $suivi_grossesse->getFormattedValue("presentation_position")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-presentation_etat"),
                $suivi_grossesse->getFormattedValue("presentation_etat")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-segment_inferieur"),
                $suivi_grossesse->getFormattedValue("segment_inferieur")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-membranes"),
                $suivi_grossesse->getFormattedValue("membranes")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-bassin"),
                $suivi_grossesse->getFormattedValue("bassin")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-examen_genital"),
                $suivi_grossesse->getFormattedValue("examen_genital")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-rques_exam_gyneco_obst"
                ),
                $suivi_grossesse->getFormattedValue("rques_exam_gyneco_obst")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-hauteur_uterine"),
                $suivi_grossesse->getFormattedValue("hauteur_uterine")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-frottis"),
                $suivi_grossesse->getFormattedValue("frottis")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-echographie"),
                $suivi_grossesse->getFormattedValue("echographie")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-prelevement_bacterio"
                ),
                $suivi_grossesse->getFormattedValue("prelevement_bacterio")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-autre_exam_comp"
                ) . " (" . CAppUI::tr("CSuiviGrossesse-exam_comp") . ")",
                $suivi_grossesse->getFormattedValue("autre_exam_comp")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-glycosurie"),
                $suivi_grossesse->getFormattedValue("glycosurie")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-leucocyturie"),
                $suivi_grossesse->getFormattedValue("leucocyturie")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-albuminurie"),
                $suivi_grossesse->getFormattedValue("albuminurie")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-nitrites"),
                $suivi_grossesse->getFormattedValue("nitrites")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr(
                    "CSuiviGrossesse-jours_arret_travail"
                ),
                $suivi_grossesse->getFormattedValue("jours_arret_travail")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CSuiviGrossesse-conclusion"),
                $suivi_grossesse->getFormattedValue("conclusion")
            );
            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CConsultation-motif"),
                $this->getFormattedValue("motif")
            );

            $template->addProperty(
                "$consultation_section - $suivi_grossesse_section - " . CAppUI::tr("CConsultation-rques"),
                $this->getFormattedValue("rques")
            );
        }

        $template->addProperty("Consultation - Identifiant de la consultation", $this->_id);

        // Constantes
        CConstantesMedicales::fillLiteLimitedTemplate($this, $template, "Consultation");

        $constantes_first = CConstantesMedicales::getFirstFor($this->_ref_patient, null, null, $this, false);
        $first_constantes = reset($constantes_first);
        CConstantesMedicales::fillLiteLimitedTemplate2($first_constantes, $template, true, "Consultation");

        $constantes_last   = CConstantesMedicales::getLatestFor($this->_ref_patient, null, null, $this, false);
        $latest_constantes = reset($constantes_last);
        CConstantesMedicales::fillLiteLimitedTemplate2($latest_constantes, $template, false, "Consultation");

        //Evènements
        $events      = $this->loadRefPatient()->loadRefDossierMedical()->loadRefsEvenementsPatient();
        $temp_events = [];
        foreach ($events as $event) {
            if ($event->date >= CMbDT::date() && CModule::getActive("oxCabinet")) {
                $temp_events[] = CEvenementPatient::viewTemplate($event);
            }
        }
        $template->addListProperty(
            "$consultation_section - " . CAppUI::tr('CSejourTimeline-title-all'),
            $temp_events,
            false
        );


        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);

        // Chargement des champs du praticien
        $this->loadRefPraticien();
        $this->_ref_praticien->fillTemplate($template);
    }

    /**
     * load "adresse par prat"
     *
     * @return CMedecin|null
     */
    public function loadRefAdresseParPraticien(): CMedecin
    {
        return $this->_ref_adresse_par_prat = $this->loadFwdRef("adresse_par_prat_id", true);
    }

    public function loadRefAdresseParExercicePlace(): CMedecinExercicePlace
    {
        return $this->_ref_adresse_par_exercice_place = $this->loadFwdRef('adresse_par_exercice_place_id', true);
    }

    /**
     * @inheritdoc
     */
    function loadRefsFiles($where = [], bool $with_cancelled = true)
    {
        parent::loadRefsFiles($where, $with_cancelled);

        if (!$this->_docitems_from_dossier_anesth) {
            // On ajoute les fichiers des dossiers d'anesthésie
            if (!$this->_refs_dossiers_anesth) {
                $this->loadRefConsultAnesth();
            }

            foreach ($this->_refs_dossiers_anesth as $_dossier_anesth) {
                $_dossier_anesth->_docitems_from_consult = true;
                $_dossier_anesth->loadRefsFiles($where, $with_cancelled);
                $this->_ref_files = CMbArray::mergeKeys($this->_ref_files, $_dossier_anesth->_ref_files);
            }
        }

        if ($this->loadRefFacture() && $this->_ref_facture->loadRefsFiles()) {
            $this->_ref_files = array_replace($this->_ref_files, $this->_ref_facture->_ref_files);
        }
        if ($this->loadRefsPrescriptions()) {
            foreach ($this->_ref_prescriptions as $_prescrition) {
                $_prescrition->loadRefsFiles();
                $this->_ref_files = array_replace($this->_ref_files, $_prescrition->_ref_files);
            }
        }

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
     * Charge les arrêts de travail de la consultation
     *
     * @param array where
     *
     * @return CAvisArretTravail[]
     */
    public function loadRefsAvisArretsTravail($where = [])
    {
        $order = "debut DESC";

        return $this->_refs_avis_arrets_travail = $this->loadBackRefs(
            "arret_travail",
            $order,
            null,
            null,
            null,
            null,
            "",
            $where
        );
    }

    /**
     * Calcul de la date en semaines d'aménorrhée
     *
     * @return int
     */
    function getSA()
    {
        $this->loadRefGrossesse();
        $this->loadRefPlageConsult();

        if ($this->_ref_grossesse) {
            $sa_comp   = $this->_ref_grossesse->getAgeGestationnel($this->_date);
            $this->_ja = $sa_comp["JA"];
            $this->_sa = $sa_comp["SA"];
        }

        return $this->_sa;
    }

    /**
     * Charge la grossesse associée au séjour
     *
     * @return CGrossesse
     */
    function loadRefGrossesse()
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }

    /**
     * @see parent::canDeleteEx()
     */
    function canDeleteEx()
    {
        if (!$this->_mergeDeletion) {
            // Date dépassée
            $this->loadRefPlageConsult();
            if ($this->_date < CMbDT::date() && !$this->_ref_module->_can->admin) {
                return CAppUI::tr('CConsultation-msg-Unable to delete past consultation');
            }
        }

        return parent::canDeleteEx();
    }

    /**
     * @see parent::completeLabelFields()
     */
    function completeLabelFields(&$fields, $params)
    {
        $this->loadRefPatient()->completeLabelFields($fields, $params);

        if ($this->sejour_id) {
            $this->loadRefSejour()->completeLabelFields($fields, $params);
        }
    }

    /**
     * @see parent::canEdit()
     */
    function canEdit()
    {
        if (!$this->sejour_id || CCanDo::admin() || !CAppUI::gconf("dPcabinet CConsultation consult_readonly")) {
            return parent::canEdit();
        }

        // Si sortie réelle, mode lecture seule
        $sejour = $this->loadRefSejour(1);
        if ($sejour->sortie_reelle) {
            return $this->_canEdit = 0;
        }

        // Modification possible seulement pour les utilisateurs de la même fonction
        $praticien = $this->loadRefPraticien();

        return $this->_canEdit = CAppUI::$user->function_id == $praticien->function_id;
    }

    /**
     * @see parent::canRead()
     */
    function canRead()
    {
        if (!$this->sejour_id || CCanDo::admin()) {
            return parent::canRead();
        }

        // Tout utilisateur peut consulter une consultation de séjour en lecture seule
        return $this->_canRead = 1;
    }

    /**
     * Crée une consultation à une horaire arbitraire et créé les plages correspondantes au besoin
     *
     * @param string $datetime            Date et heure
     * @param int    $praticien_id        Praticien
     * @param int    $patient_id          Patient
     * @param int    $duree               Durée de la consultation
     * @param int    $chrono              Etat de la consultation
     * @param int    $matching            Matching
     * @param int    $periode             Période de la plage
     * @param int    $agenda_praticien_id Identifiant de l'agenda du praticien
     * @param int    $duration            Durée de la consultation
     * @param int    $function_id         Identifiant de la fonction
     *
     * @return void
     * @throws CMbException
     */
    public function createByDatetime(
        $datetime,
        $praticien_id,
        $patient_id,
        $duree = 1,
        $chrono = self::PLANIFIE,
        $matching = 1,
        $periode = null,
        ?int $agenda_praticien_id = null,
        ?int $duration = null,
        ?int $function_id = null
    ): void {
        $day_now  = CMbDT::format($datetime, "%Y-%m-%d");
        $time_now = CMbDT::format($datetime, "%H:%M:00");

        try {
            $plage = $this->getPlageConsult($datetime, $praticien_id, $periode, $agenda_praticien_id, $function_id);
        } catch (CMbException $e) {
            throw $e;
        }

        $this->plageconsult_id = $plage->_id;
        $this->patient_id      = $patient_id;

        // Chargement de la consult avec la plageconsult && le patient
        if ($matching) {
            $this->loadMatchingObjectEsc();
        }

        if (!$this->_id) {
            $this->heure = $time_now;
            if ($chrono != self::DEMANDE && $chrono != self::PLANIFIE) {
                $this->arrivee = "$day_now $time_now";
            }
            if ($duration) {
                $duree = abs($duration / $plage->_freq);
            }
            $this->duree  = $duree;
            $this->chrono = $chrono;
        }

        if ($msg = $this->store()) {
            throw new CMbException($msg);
        }
    }

    /**
     * Get plage consult.
     *
     * @param string $datetime            Date et heure
     * @param int    $praticien_id        Praticien
     * @param int    $periode             Période de la plage
     * @param int    $agenda_praticien_id Identifiant de l'agenda du praticien
     * @param int    $function_id         Identifiant de la fonction
     *
     * @return CPlageconsult
     * @throws Exception
     */
    private function getPlageConsult(
        string $datetime,
        int $praticien_id,
        ?int $periode = null,
        ?int $agenda_praticien_id = null,
        ?int $function_id = null
    ): CPlageconsult {
        $minutes_interval = CValue::first(CAppUI::gconf("dPcabinet CPlageconsult minutes_interval"), "15");
        $periode          = ($periode) ?: ("00:" . ($minutes_interval ?: "05") . ":00");

        $day_now   = CMbDT::format($datetime, "%Y-%m-%d");
        $time_now  = CMbDT::format($datetime, "%H:%M:00");
        $hour_now  = CMbDT::format($datetime, "%H:00:00");
        $hour_next = CMbDT::time("+1 HOUR", $hour_now);

        $plage       = new CPlageconsult();
        $plageBefore = new CPlageconsult();
        $plageAfter  = new CPlageconsult();

        // Cas ou une plage correspond
        $where            = [];
        $where["chir_id"] = "= '$praticien_id'";
        $where["date"]    = "= '$day_now'";
        $where["debut"]   = "<= '$time_now'";
        $where["fin"]     = "> '$time_now'";
        if ($agenda_praticien_id) {
            $where["agenda_praticien_id"] = "= '$agenda_praticien_id'";
        }
        if ($function_id) {
            $where["function_id"] = "= '$function_id'";
        }
        $plage->loadObject($where);

        if (!$plage->plageconsult_id) {
            // Cas ou on a des plage en collision
            $where            = [];
            $where["chir_id"] = "= '$praticien_id'";
            $where["date"]    = "= '$day_now'";
            $where["debut"]   = "<= '$hour_now'";
            $where["fin"]     = ">= '$hour_now'";
            if ($agenda_praticien_id) {
                $where["agenda_praticien_id"] = "= '$agenda_praticien_id'";
            }
            if ($function_id) {
                $where["function_id"] = "= '$function_id'";
            }
            $plageBefore->loadObject($where);
            $where["debut"] = "<= '$hour_next'";
            $where["fin"]   = ">= '$hour_next'";
            $plageAfter->loadObject($where);

            if ($plageBefore->_id) {
                $plageBefore->fin = $plageAfter->_id ?
                    $plageAfter->debut :
                    max($plageBefore->fin, $hour_next);
                $plage            = $plageBefore;
            } elseif ($plageAfter->_id) {
                $plageAfter->debut = min($plageAfter->debut, $hour_now);
                $plage             = $plageAfter;
            } else {
                $plage->chir_id = $praticien_id;
                $plage->date    = $day_now;
                $plage->freq    = $periode;
                $plage->debut   = $hour_now;
                $plage->fin     = $hour_next;
                if ($this->type_consultation == "suivi_patient") {
                    $plage->libelle = CPlageconsult::LIBELLE_PLAGE_SUIVI_PATIENT;
                }
            }
            if ($agenda_praticien_id) {
                $plage->agenda_praticien_id = $agenda_praticien_id;
            }
            if ($function_id) {
                $plage->function_id = $function_id;
            }

            $plage->updateFormFields();
            if ($msg = $plage->store()) {
                throw new CMbException($msg);
            }
        }

        return $plage;
    }

    /**
     * Change le praticien de la consult
     *
     * @param int $change_prat_id      ID du nouveau chirurgien de la consultation
     * @param int $agenda_praticien_id Identifiant de l'agenda du praticien
     * @param int $function_id         Identifiant de la fonction
     *
     * @return string|void|null
     * @throws CMbException
     */
    function changePraticien(int $change_prat_id, ?int $agenda_praticien_id = null, ?int $function_id = null)
    {
        $this->loadRefPlageConsult();
        try {
            $plage = $this->getPlageConsult(
                $this->_datetime,
                $change_prat_id,
                null,
                $agenda_praticien_id,
                $function_id
            );
        } catch (CMbException $e) {
            throw $e;
        }

        $this->plageconsult_id = $plage->_id;
    }

    /**
     * Change la date de la consult
     *
     * @param string $datetime            Date et heure
     * @param int    $duree               Durée de la consultation
     * @param int    $chrono              Etat de la consultation
     * @param int    $periode             Période de la plage
     * @param int    $agenda_praticien_id Identifiant de l'agenda du praticien
     * @param int    $duration            Durée de la consultation
     * @param int    $function_id         Identifiant de la fonction
     *
     * @return null|string Store-like message
     * @throws Exception
     */
    function changeDateTime(
        string $datetime,
        int $duree = 1,
        int $chrono = self::PLANIFIE,
        ?int $periode = null,
        ?int $agenda_praticien_id = null,
        ?int $duration = null,
        ?int $function_id = null
    ) {
        $day_now  = CMbDT::format($datetime, "%Y-%m-%d");
        $time_now = CMbDT::format($datetime, "%H:%M:00");

        try {
            $plage = $this->getPlageConsult(
                $datetime,
                $this->_praticien_id,
                $periode,
                $agenda_praticien_id,
                $function_id
            );
        } catch (CMbException $e) {
            throw $e;
        }

        $this->plageconsult_id = $plage->_id;

        if ($chrono != self::DEMANDE && $chrono != self::PLANIFIE) {
            $this->arrivee = "$day_now $time_now";
        }

        if ($duration) {
            $duree = abs($duration / $plage->_freq);
        }

        $this->duree  = $duree;
        $this->chrono = $chrono;
        $this->heure  = $time_now;

        // Obligé de mettre à null pour passer le updatePlainField
        $this->_hour = null;

        $this->_datetime = "$this->_date $this->heure";
    }

    /**
     * @see parent::getDynamicTag
     */
    function getDynamicTag()
    {
        return $this->gconf("tag");
    }

    /**
     * @inheritdoc
     */
    function loadRelGroup(): CGroups
    {
        return $this->loadRefGroup();
    }

    /**
     * Charge le dossier d'anesthésie de la plage d'op la plus ancienne
     *
     * @return CConsultAnesth
     */
    function loadRefFirstDossierAnesth()
    {
        // Chargement des plages de chaques dossiers
        foreach ($this->_refs_dossiers_anesth as $_dossier) {
            $_dossier->loadRefOperation()->loadRefPlageOp();
        }
        $plages = CMbArray::pluck($this->_refs_dossiers_anesth, "_ref_operation", "_ref_plageop", "date");
        array_multisort($plages, SORT_ASC, $this->_refs_dossiers_anesth);

        return $this->_ref_consult_anesth = reset($this->_refs_dossiers_anesth);
    }

    /**
     * Loads the related fields for indexing datum (patient_id et date)
     *
     * @return array
     */
    function getIndexableData()
    {
        $this->getIndexablePraticien();
        $array["id"]          = $this->_id;
        $array["author_id"]   = $this->_praticien_id;
        $array["prat_id"]     = $this->_ref_praticien->_id;
        $array["title"]       = $this->type;
        $array["body"]        = $this->getIndexableBody("");
        $array["date"]        = str_replace("-", "/", $this->loadRefPlageConsult()->date);
        $array["function_id"] = $this->_ref_praticien->function_id;
        $array["group_id"]    = $this->_ref_praticien->loadRefFunction()->group_id;
        $array["patient_id"]  = $this->getIndexablePatient()->_id;
        $sejour               = $this->loadRefSejour();
        if ($sejour && $sejour->_id) {
            $array["object_ref_id"]    = $this->_ref_sejour->_id;
            $array["object_ref_class"] = $this->_ref_sejour->_class;
        } else {
            $array["object_ref_id"]    = $this->_id;
            $array["object_ref_class"] = $this->_class;
        }


        return $array;
    }

    /**
     * Get the praticien_id of CMbobject
     *
     * @return CMediusers
     */
    function getIndexablePraticien()
    {
        return $this->loadRefPraticien();
    }

    /**
     * Redesign the content of the body you will index
     *
     * @param string $content The content you want to redesign
     *
     * @return string
     */
    function getIndexableBody($content)
    {
        $fields = $this->getTextcontent();
        foreach ($fields as $_field) {
            $content .= " " . $this->$_field;
        }

        return $content;
    }

    /**
     * Get the patient_id of CMbobject
     *
     * @return CPatient
     */
    function getIndexablePatient()
    {
        return $this->loadRelPatient();
    }

    /**
     * @inheritdoc
     */
    function loadRelPatient()
    {
        return $this->loadRefPatient();
    }

    /**
     * Chargement des brancardages de la consultation
     *
     * @return null|CBrancardage[]
     * @throws Exception
     */
    public function loadRefsBrancardages(): ?array
    {
        if (!CModule::getActive("brancardage") || !CAppUI::gconf("brancardage General use_brancardage")) {
            return null;
        }

        $this->_ref_brancardages = $this->loadBackRefs("context_ref_brancardages", "brancardage_id ASC");
        CStoredObject::massLoadBackRefs($this->_ref_brancardages, "brancardage_ref_etapes");
        foreach ($this->_ref_brancardages as $brancardage) {
            $brancardage->loadRefEtapes();
        }

        return $this->_ref_brancardages;
    }

    /**
     * Chargement du brancardage courant.
     *
     * @return CBrancardage|null
     * @throws Exception
     */
    public function loadCurrRefBrancardage(): ?CBrancardage
    {
        if (!CModule::getActive("brancardage") || !CAppUI::gconf("brancardage General use_brancardage")) {
            return null;
        }

        $ljoin                      = [];
        $ljoin["brancardage_etape"] = "brancardage_etape.brancardage_id = brancardage.brancardage_id";

        $where   = [];
        $where[] = "brancardage.context_class = '" . $this->_class . "'";
        $where[] = CBrancardageConditionMakerUtility::makeIncludeOrExcludeByStep(CBrancardage::ARRIVEE, true);

        $brancardage = new CBrancardage();
        $brancardage->loadObject($where, null, null, $ljoin);
        $brancardage->loadRefEtapes();

        return $this->_ref_current_brancardage = $brancardage;
    }

    /**
     * @inheritdoc
     */
    function loadAllDocs($params = [])
    {
        $this->loadRefsPrescriptions();
        if (isset($this->_ref_prescriptions["externe"])) {
            $this->mapDocs($this->_ref_prescriptions["externe"], $params);
        }

        $this->mapDocs($this, $params);
    }

    /**
     * Gets icon for current patient event
     *
     * @return array
     */
    function getEventIcon()
    {
        $icon = [
            'icon'  => 'fa fa-stethoscope me-event-icon',
            'color' => 'steelblue',
            'title' => CAppUI::tr($this->_class),
        ];

        if ($this->grossesse_id) {
            $icon['color'] = 'palevioletred';
            $icon['title'] = CAppUI::tr('CConsultation-title-Consultation with pregnancy');
        }

        if (in_array($this->_type, CSejour::getTypesSejoursUrgence($this->loadRefSejour()->praticien_id))) {
            $icon['color'] = 'firebrick';
            $icon['title'] = CAppUI::tr('CConsultation-title-Emergency consultation');
        }

        return $icon;
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

        $constantes = new CConstantesMedicales();

        $where["patient_id"]    = "= '$this->patient_id'";
        $where["context_class"] = "= '$this->_class'";
        $where["context_id"]    = "= '$this->_id'";

        return $this->_list_constantes_medicales = $constantes->loadList($where, "datetime ASC");
    }

    /**
     * Analyse si la consult d'anesth contient une DHE associée, s'il en existe une ou pas
     *
     * @param string $date date de la consultation
     *
     * @return void
     */
    function checkDHE($date = null)
    {
        if (!$date) {
            $date = $this->loadRefPlageConsult()->date;
        }
        foreach ($this->loadRefsDossiersAnesth() as $_consult_anesth) {
            $_consult_anesth->_etat_dhe_anesth = null;
            $operation                         = $_consult_anesth->loadRefOperation();
            if ($operation->_id && $operation->_ref_sejour->_id) {
                $_consult_anesth->_etat_dhe_anesth = "associe";
                $this->_etat_dhe_anesth            = "associe";
            } else {
                $next = $this->_ref_patient->getNextSejourAndOperation($date);
                if ($next["CSejour"]->_id) {
                    $_consult_anesth->_etat_dhe_anesth = "dhe_exist";
                    if ($this->_etat_dhe_anesth != "associe") {
                        $this->_etat_dhe_anesth = "dhe_exist";
                    }
                } else {
                    $_consult_anesth->_etat_dhe_anesth = "non_associe";
                    if (!$this->_etat_dhe_anesth) {
                        $this->_etat_dhe_anesth = "non_associe";
                    }
                }
            }
        }
    }

    /**
     * Choix de la couleur de la consultation du nouveau planning
     *
     * @return string
     */
    function colorPlanning()
    {
        $color = CAppUI::isMediboardExtDark() ? "#f16860" : "#fee";
        if (!$this->patient_id) {
            if ($this->groupee && $this->no_patient) {
                $color = CAppUI::isMediboardExtDark() ? "#eb742f" : "#e5b774";
            } else {
                $color = CAppUI::isMediboardExtDark() ? "#726f73" : "#a7a3a3";
            }
        } elseif ($this->premiere) {
            $color = CAppUI::isMediboardExtDark() ? "#f16860" : "#faa";
        } elseif ($this->derniere) {
            $color = CAppUI::isMediboardExtDark() ? "#a88cdc" : "#faf";
        } elseif ($this->sejour_id) {
            $color = CAppUI::isMediboardExtDark() ? "#81a03e" : "#CFFFAD";
        }

        return $this->_color_planning = $color;
    }

    /**
     * Ajout du cartouche SMS s'il est necessaire dans le planning
     *
     * @return string
     */
    public function smsPlanning(): string
    {
        $title = "";
        if ($this->_ref_notification && $this->_ref_notification->_channel) {
            $notification = $this->_ref_notification;
            $title        = "<span class=\"texticon texticon-gray\"";
            if ($notification->_id) {
                $notification->loadRefMessage();
                if (in_array($notification->_message->status, ["transmitted", "delivered"])) {
                    $title = "<span class=\"texticon texticon-allergies-ok\"";
                    $title .= " title=\"" . CMbString::htmlEncode(
                            CAppUI::tr("common-$notification->_channel sent")
                        ) . "\"";
                } elseif (
                    in_array(
                        $notification->_message->status,
                        ["failed_transmission", "cancelled", "failed_delivery"]
                    )
                ) {
                    $title = "<span class=\"texticon texticon-stup texticon-stroke\"";
                    $title .= " title=\"" . CMbString::htmlEncode(
                            CAppUI::tr("common-$notification->_channel in error")
                        ) . "\"";
                }
            }
            $title .= "style=\"float:right\">" . CAppUI::tr(
                    "CNotificationEvent.type.$notification->_channel"
                ) . "</span>";
        }

        return $title;
    }

    /**
     * Affecte le bon type de transport à la notification si elle n'existe pas
     *
     * @return CNotification
     * @throws Exception
     */
    function loadRefNotification()
    {
        $notifications = parent::loadRefNotifications();
        $notification  = new CNotification();
        if (count($notifications)) {
            foreach ($notifications as $_notification) {
                $_notification->loadRefMessage();
                if ($_notification->_message && $_notification->_message->status === "delivered") {
                    $notification = $_notification;
                    break;
                } elseif (!$notification->_id || ($_notification->_message && ($_notification->_message->status === "transmitted"
                            || ($_notification->_message->status === "scheduled" && $notification->_message->status !== "transmitted")
                            || !in_array($notification->_message->status, ["transmitted", "scheduled"])))
                ) {
                    $notification = $_notification;
                }
            }
        }
        $this->_ref_notification = $notification;

        $patient = $this->_ref_patient;
        if ($notification !== null && !$notification->_id && $patient->allow_sms_notification) {
            $praticien_id = $this->_ref_praticien ? $this->_ref_praticien->_id : $this->loadRefPraticien()->_id;
            $event        = CNotificationEvent::searchNotificationUser($praticien_id);
            if ($event->_id) {
                $notification->_channel = $event->channel;
            } else {
                $this->_ref_notification = null;
            }
        } elseif ($notification->_id) {
            $notification->loadRefContext();
            $notification->loadRefMessage();
        }

        return $this->_ref_notification;
    }

    /**
     * @inheritdoc
     */
    function getSpecialIdex(CIdSante400 $idex)
    {
        if (CModule::getActive("appFineClient")) {
            if ($idex_type = CAppFineClient::getSpecialIdex($idex)) {
                return $idex_type;
            }
        }

        if (CModule::getActive("doctolib")) {
            if ($idex_type = CDoctolib::getSpecialIdex($idex)) {
                return $idex_type;
            }
        }

        return null;
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
     * Chargement des demandes AppFine
     *
     * @return CAppFineClientOrderItem[]
     */
    function loadRefsOrdersItem($where = [], $ljoin = [])
    {
        return $this->_ref_orders_item = $this->loadBackRefs(
            "appFine_order_items",
            null,
            null,
            null,
            $ljoin,
            null,
            null,
            $where
        );
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
     * Chargement de la liste complète des infos de checklist
     *
     * @return CInfoChecklist[]
     */
    function loadRefsInfoChecklist()
    {
        $this->loadRefsInfoChecklistItem();
        if (!$this->_ref_chir) {
            $this->loadRefPraticien();
        }
        $infos = CInfoChecklist::loadListWithFunction($this->_ref_chir->function_id);
        foreach ($infos as $_info) {
            foreach ($this->_refs_info_check_items as $_item) {
                if ($_item->info_checklist_id == $_info->_id) {
                    $_info->_item_id = $_item->_id;
                }
            }
        }

        $this->_ref_info_checklist_item = new CInfoChecklistItem();
        $this->_refs_info_checklist     = $infos;
    }

    /**
     * Chargement des item de checklist utilisé
     *
     * @param bool $reponse Réponse
     *
     * @return CInfoChecklistItem[]
     */
    function loadRefsInfoChecklistItem($reponse = false)
    {
        $where                       = [];
        $where["consultation_class"] = " = 'CConsultation'";
        if ($reponse) {
            $where["reponse"] = " = '1'";
        }
        $this->_refs_info_check_items = $this->loadBackRefs(
            "info_check_item",
            null,
            null,
            "info_checklist_item_id",
            null,
            null,
            "",
            $where
        );
        if ($reponse) {
            foreach ($this->_refs_info_check_items as $_item) {
                $_item->loadRefInfoChecklist();
            }
        }

        return $this->_refs_info_check_items;
    }

    /**
     * Crée une observation d'entrée, pour les formulaires en volet
     *
     * @param CMbObject $reference1 First reference
     * @param CMbObject $reference2 Second reference
     *
     * @return CConsultation|null
     */
    function formTabAction_createObsEntree(CMbObject $reference1, CMbObject $reference2 = null)
    {
        if ($reference1 instanceof CSejour) {
            $consult = $reference1->loadRefObsEntree();

            if (!$consult->_id) {
                $datetime = CMbDT::dateTime();
                $chir     = CMediusers::get();

                $day_now   = CMbDT::format($datetime, "%Y-%m-%d");
                $time_now  = CMbDT::format($datetime, "%H:%M:00");
                $hour_now  = CMbDT::format($datetime, "%H:00:00");
                $hour_next = CMbDT::time("+1 HOUR", $hour_now);

                $plage       = new CPlageconsult();
                $plageBefore = new CPlageconsult();
                $plageAfter  = new CPlageconsult();

                // Cas ou une plage correspond
                $where            = [];
                $where["chir_id"] = "= '$chir->_id'";
                $where["date"]    = "= '$day_now'";
                $where["debut"]   = "<= '$time_now'";
                $where["fin"]     = "> '$time_now'";
                $plage->loadObject($where);

                if (!$plage->_id) {
                    // Cas ou on a des plage en collision
                    $where            = [];
                    $where["chir_id"] = "= '$chir->_id'";
                    $where["date"]    = "= '$day_now'";
                    $where["debut"]   = "<= '$hour_now'";
                    $where["fin"]     = ">= '$hour_now'";
                    $plageBefore->loadObject($where);
                    $where["debut"] = "<= '$hour_next'";
                    $where["fin"]   = ">= '$time_now'";
                    $plageAfter->loadObject($where);
                    if ($plageBefore->_id) {
                        if ($plageAfter->_id) {
                            $plageBefore->fin = $plageAfter->debut;
                        } else {
                            $plageBefore->fin = max($plageBefore->fin, $hour_next);
                        }
                        $plage =& $plageBefore;
                    } elseif ($plageAfter->_id) {
                        $plageAfter->debut = min($plageAfter->debut, $hour_now);
                        $plage             =& $plageAfter;
                    } else {
                        $plage->chir_id          = $chir->_id;
                        $plage->date             = $day_now;
                        $plage->freq             = "00:" . CPlageconsult::$minutes_interval . ":00";
                        $plage->debut            = $hour_now;
                        $plage->fin              = $hour_next;
                        $plage->libelle          = "automatique";
                        $plage->_immediate_plage = 1;
                    }
                    $plage->updateFormFields();
                    if ($msg = $plage->store()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }
                }

                $consult->plageconsult_id = $plage->_id;
                $consult->patient_id      = $reference1->patient_id;
                $consult->heure           = $time_now;
                $consult->arrivee         = "$day_now $time_now";
                $consult->duree           = 1;
                $consult->chrono          = CConsultation::PATIENT_ARRIVE;
                $consult->type            = "entree";
                $consult->motif           = CAppUI::gconf('soins Other default_motif_observation');

                if ($msg = $consult->store()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }

                return $consult;
            }
        }

        return null;
    }

    /**
     * Charge le dossier complete de la consultation pour AppFineClient
     *
     * @param string $type type
     *
     * @return CAppFineClientFolderLiaison
     */
    function loadRefFolderLiaison($type = null)
    {
        $folder_liaison               = new CAppFineClientFolderLiaison();
        $folder_liaison->object_id    = $this->_id;
        $folder_liaison->object_class = $this->_class;
        if ($type) {
            $folder_liaison->type = $type;
        }
        $folder_liaison->loadMatchingObject();

        return $this->_ref_appfine_client_folder = $folder_liaison;
    }

    /**
     * @inheritdoc
     */
    function checkTrigger($first_store = false)
    {
        return ($first_store || ($this->fieldModified('heure') || $this->fieldModified(
                    'patient_id'
                ) || $this->fieldModified('plageconsult_id')));
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
     * @inheritdoc
     */
    function getRGPDContext()
    {
        return $this->loadRefPatient();
    }

    /**
     * @inheritDoc
     */
    public function getGroupID()
    {
        $group = $this->loadRefGroup();

        if ($group && $group->_id) {
            return $group->_id;
        }

        return null;
    }

    /**
     * Vérifie si une consultation est considérée
     * comme terminée concernant le codage des actes
     *
     * @return bool
     */
    public function isCoded(): bool
    {
        $this->_coded = false;

        if ($this->sejour_id && CAppUI::gconf('dPsalleOp COperation modif_actes') == 'facturation_web100T'
            && CModule::getActive('web100T') && $this->_ref_sejour->sortie_reelle
        ) {
            $this->_coded = CWeb100TSejour::isSejourBilled($this->_ref_sejour);
        }

        return $this->_coded;
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        $sejour = $this->loadRefSejour();
        if ($sejour && $sejour->_id) {
            return $sejour->isExportable($prat_ids, $date_min, $date_max);
        }

        $this->loadRefPlageConsult();

        $use_tag = ($additional_args) ? reset($additional_args) : false;

        $sync_tag = $use_tag ? $this->hasSyncTag() : false;

        return
            $this->_ref_plageconsult->isExportable($prat_ids, $date_min, $date_max)
            && (
                !$use_tag
                || !$sync_tag
                || $this->_date < CMbDT::dateTime()
            );
    }

    private function hasSyncTag(): bool
    {
        $tag = new CIdSante400();
        $ds  = $tag->getDS();

        $where = [
            'object_id'    => $ds->prepare('= ?', $this->_id),
            'object_class' => $ds->prepare('= ?', 'CConsultation'),
            'tag'          => $ds->prepareIn(['sync_object', 'sync_creation', 'sync_update']),
        ];

        return $tag->countList($where) > 0;
    }

    /**
     * @return CStoredObject[]|CReservation[]
     */
    public function loadRefReservedRessources()
    {
        $reservation             = new CReservation();
        $reservation->date       = $this->_date;
        $reservation->heure      = (new DateTime($this->_datetime ?? ''))->format('H:i:s');
        $reservation->patient_id = $this->patient_id;

        return $this->_ref_reserved_ressources = $reservation->loadMatchingList();
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchConsultation($this);
    }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourcePatient(): ?Item
    {
        $patient = $this->loadRefPatient();
        if (!$patient || !$patient->_id) {
            return null;
        }

        return new Item($patient);
    }


    /**
     * @param JsonApiItem|null $json_api_patient
     *
     * @return void
     * @throws RequestContentException
     */
    public function setResourcePatient(?JsonApiItem $json_api_patient): void
    {
        $this->patient_id = $json_api_patient === null
            ? ''
            : $json_api_patient->createModelObject(CPatient::class, false)->getModelObject()->_id;
    }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourceFactureCabinet(): ?Item
    {
        $facture = $this->loadRefFacture();
        if (!$facture || !$facture->_id) {
            return null;
        }

        return new Item($facture);
    }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourcePlageConsult(): ?Item
    {
        $plageconsult = $this->loadRefPlageConsult();
        if (!$plageconsult || !$plageconsult->_id) {
            return null;
        }

        return new Item($plageconsult);
    }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourceMediuser(): ?Item
    {
        $praticien = $this->loadRefPraticien();
        if (!$praticien || !$praticien->_id) {
            return null;
        }

        return new Item($praticien);
    }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourceCategorie(): ?Item
    {
        $categorie = $this->loadRefCategorie();
        if (!$categorie || !$categorie->_id) {
            return null;
        }

        return new Item($categorie);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * Get the Covid diagnosis
     *
     * @return void
     */
    function getCovidDiag()
    {
        $pattern = "/U07\.?[0-9]/";

        $dossier_medical = $this->_ref_patient->loadRefDossierMedical();
        if ($dossier_medical->_id) {
            foreach ($dossier_medical->_codes_cim as $_code) {
                if (preg_match($pattern, $_code)) {
                    $this->_covid_diag = CCodeCIM10::get(str_replace(".", "", $_code));
                }
            }
        }

        if ($this->_covid_diag) {
            if (!preg_match("/Covid-19/", $this->_covid_diag->libelle_court)) {
                $this->_covid_diag->libelle_court = "Covid-19 : " . $this->_covid_diag->libelle_court;
            }
        }
    }

    /**
     * Checks the state of the FSEs linked to the consultation.
     * Will return false if one the linked fses as received a negative acknowledgement,
     * or a refusal for a refund by the insurance
     *
     * @return bool
     * @throws Exception
     */
    public function isRefundReceived(): bool
    {
        $refundOk = true;

        if ($this->du_tiers > 0 || $this->_ref_facture->_du_restant_tiers > 0) {
            if (CModule::getActive('oxPyxvital')) {
                $fses = CPyxvitalFSE::loadForConsult($this);
                foreach ($fses as $_fse) {
                    if ($_fse->state == "rsp_ko" || $_fse->state == "ack_ko") {
                        $refundOk = false;
                    }
                }
            }

            if (CModule::getActive('jfse')) {
                /** @var CJfseInvoice[] */
                $invoices = $this->loadBackRefs('jfse_invoices');
                foreach ($invoices as $invoice) {
                    if ($invoice->isRejected()) {
                        $refundOk = false;
                    }
                }
            }
        }

        return $refundOk;
    }

    /**
     * @return bool
     */
    public function isIncompletePayment(): bool
    {
        return $this->_ref_facture && $this->_ref_facture->_du_restant_patient &&
            $this->_ref_facture->_du_restant_patient > 0;
    }

    /**
     * Determine the payment status of the consultation
     * @return void
     * @throws Exception
     */
    public function preparePaymentStatus(): void
    {
        $this->loadRefPraticien();
        $refund_received = $this->isRefundReceived();

        if ($refund_received && !isset($this->_ref_facture->_id)) {
            return;
        }

        $rp = floatval($this->_ref_facture->_du_restant_patient);

        $this->_payment_status = self::NO_SETTLEMENT;
        if (CModule::getInstalled('teleconsultation')
            && $this->teleconsultation
            && CAppUI::loadPref('use_telepayment_for_teleconsultation', $this->_ref_praticien->_id)) {
            $this->loadRefRoom();
            $this->loadRefBonAPayer();
            if (isset($this->_ref_bon_a_payer->_id)) {
                if ($this->_ref_bon_a_payer->paiement_datetime) {
                    $this->_payment_status = self::VOUCHER_PAYED;
                } elseif (!$this->_ref_bon_a_payer->paiement_datetime && $this->_ref_bon_a_payer->ack) {
                    $this->_payment_status = self::VOUCHER_PAYMENT_ERROR;
                } else {
                    $this->_payment_status = self::VOUCHER_SENT_NOT_PAYED;
                }
            } else {
                $this->_payment_status = self::VOUCHER_NOT_SENT;
            }
        } elseif (!$refund_received) {
            $this->_payment_status = self::REFUND_NOT_RECEIVED;
        } elseif ($rp === 0.0) {
            $this->_payment_status = self::SETTLEMENT_PAYED;
        } elseif ($rp === floatval($this->_ref_facture->du_patient)) {
            $this->_payment_status = self::SETTLEMENT_NOT_PAYED;
        } else {
            $this->_payment_status = self::SETTLEMENT_PARTIALLY_PAYED;
        }
    }

    /**
     * @return CSlot[]|null
     * @throws Exception
     */
    public function loadRefSlots($order = null)
    {
        return $this->_ref_slots = $this->loadBackRefs("slots", $order);
    }

    /**
     * Generate and return the self link.
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('cabinet_show_consultation', ['consultation_id' => $this->_id]);
    }
}
