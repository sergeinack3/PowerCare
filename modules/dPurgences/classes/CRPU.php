<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * The CRPU class
 * Résumé de Passage aux Urgences
 */
class CRPU extends CMbObject
{
    // DB Table key
    public $rpu_id;

    static $orientation_value = [
        "HDT",
        "HO",
        "SC",
        "SI",
        "REA",
        "UHCD",
        "MED",
        "CHIR",
        "OBST",
        "FUGUE",
        "SCAM",
        "PSA",
        "REO",
        "NA",
    ];
    static $default_degre_cte = 4;

    // DB Fields
    public $sejour_id;
    public $protocole_id;
    public $motif_entree;
    public $diag_infirmier;
    public $pec_transport;
    public $pec_douleur;
    public $motif;
    public $motif_sfmu;
    public $ccmu;
    public $gemsa;
    public $orientation;
    public $cimu;
    public $french_triage;

    public $mutation_sejour_id;
    public $box_id;
    public $sortie_autorisee;
    public $date_sortie_aut;
    public $date_at;
    public $circonstance;
    public $regule_par;
    public $ide_responsable_id;
    public $pec_inf;
    public $ioa_id;
    public $pec_ioa;
    public $color;
    public $commentaire;
    public $decision_uhcd;
    public $diag_incertain_pec;
    public $caractere_instable;
    public $surv_hosp_specifique;
    public $exam_comp;

    public $type_pathologie; // Should be $urtype

    // Form fields
    public $_libelle_circonstance;

    // Distant Fields
    public $_attente;
    public $_presence;
    public $_can_leave;
    public $_can_leave_since;
    public $_can_leave_about;
    public $_can_leave_level;

    // Patient
    public $_patient_id;
    public $_cp;
    public $_ville;
    public $_naissance;
    public $_sexe;

    // Sejour
    public $_responsable_id;
    public $_annule;
    public $_entree;
    public $_type;
    public $_DP;
    public $_ref_actes_ccam;
    public $_service_id;
    public $_mode_entree_id;
    public $_UHCD;
    public $_entree_preparee;
    public $_etablissement_sortie_id;
    public $_etablissement_entree_id;
    public $_service_entree_id;
    public $_service_sortie_id;
    public $_grossesse_id;
    public $_uf_soins_id;
    public $_charge_id;
    public $_uf_medicale_id;

    /** @var CSejour */
    public $_ref_sejour;

    /** @var CConsultation */
    public $_ref_consult;

    /** @var CSejour */
    public $_ref_sejour_mutation;

    /** @var CMotifSFMU */
    public $_ref_motif_sfmu;

    /** @var CLit */
    public $_ref_box;

    /** @var CCirconstance */
    public $_ref_circonstance;

    /** @var CMediusers */
    public $_ref_ide_responsable;

    /** @var CRPUReservationBox */
    public $_ref_reservation;

    /** @var CMediusers */
    public $_ref_ioa;

    /** @var CRPUAttente[] */
    public $_ref_attentes;
    public $_ref_attentes_by_type;
    public $_ref_first_attentes;
    public $_ref_last_attentes;

    /** @var CRPUAttente */
    public $_ref_attente_empty;

    /** @var [] */
    public $_ref_constantes_by_degre;

    /** @var CRPULinkCat[] */
    public $_ref_rpu_categories = [];

    /** @var CRPUReevalPEC[] */
    public $_ref_rpu_reevaluations_pec;
    /** @var CRPUReevalPEC */
    public $_ref_rpu_last_reevaluation_pec;

    /** @var CExtractPassages[] */
    public $_ref_extract_passages;

    /** @var CExtractPassages */
    public $_first_extract_passages;
    /** @var CExtractPassages */
    public $_last_extract_passages;
    /** @var int */
    public $_count_extract_passages;

    /** @var CRPUPassage[] */
    public $_ref_passages;

    // Behaviour fields
    public $_bind_sejour;
    public $_sortie;
    public $_mode_entree;
    public $_mode_sortie;
    public $_date_at;
    public $_provenance;
    public $_destination;
    public $_transport;
    public $_old_service_id;
    public $_validation;
    public $_ref_cts_degre;
    public $_ref_latest_constantes;
    public $_estimation_ccmu;
    public $_class_sfmu;
    public $_color_cimu;
    public $_transfert_rpu;
    public $_store_affectation = true;

    //Timings for stats
    public $_create_rpu;
    public $_pec_iao;
    public $_salle_attente;
    public $_pec_chir;
    public $_best_pec_inf_salle;
    public $_fin_pec;
    public $_sortie_rpu;
    public $_possible_update_ccmu;
    public $_count_rpu_reevaluations_pec = 0;

    // Classe CSS de la catégorie du motif SFMU
    static $class_sfmu = [
        "Cardio-vasculaire"                         => "icon-i-cardiology",
        "Gastro-enterologie"                        => "icon-i-internal-medicine",
        "Ophthalmologie"                            => "icon-i-ophthalmology",
        "Orl"                                       => "icon-i-ear-nose-throat",
        "Pediatrie < 2 ans (pathologie spécifique)" => "icon-i-pediatrics",
        "Psychiatrie"                               => "icon-i-mental-health",
        "Respiratoire"                              => "icon-i-respiratory",
        "Neurologie"                                => "icon-i-neurology",
        "Gynecologie"                               => "icon-i-womens-health",
        "Obstetrique"                               => "icon-i-labor-delivery",
        "Rhumatologie"                              => "icon-i-physical-therapy",
        "Traumatologie"                             => "icon-i-outpatient",
        "General & divers"                          => "icon-i-health-services",
        "Genito-urinaire"                           => "fa fa-venus-mars",
        "Intoxication"                              => "icon-i-ambulance",
        "Environnemental"                           => "far fa-sun",
        "Peau"                                      => "icon-i-dermatology",
    ];

    // Critères de passage en UHCD
    static $criteres_uhcd = [
        "decision_uhcd",
        "diag_incertain_pec",
        "caractere_instable",
        "surv_hosp_specifique",
        "exam_comp",
    ];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->table       = 'rpu';
        $spec->key         = 'rpu_id';
        $spec->measureable = true;

        $spec->events = [
            "pec"                   => [
                "reference1" => ["CSejour", "sejour_id"],
                "reference2" => ["CPatient", "sejour_id.patient_id"],
            ],
            'tab_dossier_infirmier' => [
                'tab'        => true,
                'reference1' => ['CSejour', 'sejour_id'],
                'reference2' => ['CPatient', 'sejour_id.patient_id'],
            ],
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $impose_degre_urgence  = CAppUI::gconf("dPurgences CRPU impose_degre_urgence") == 1;
        $impose_diag_infirmier = CAppUI::gconf("dPurgences CRPU impose_diag_infirmier") == 1;
        $impose_motif          = CAppUI::gconf("dPurgences CRPU impose_motif") == 1;
        $impose_ide_ref        = CAppUI::gconf("dPurgences CRPU impose_ide_referent") == 1;

        $props                    = parent::getProps();
        $props["sejour_id"]       = "ref notNull class|CSejour cascade back|rpu";
        $props["protocole_id"]    = "ref class|CProtocoleRPU show|0 back|rpus";
        $props["motif_entree"]    = "text helped";
        $props["diag_infirmier"]  = "text " . ($impose_diag_infirmier ? 'notNull ' : '') . "helped";
        $props["pec_douleur"]     = "text helped";
        $props["pec_transport"]   = "enum list|med|paramed|aucun";
        $props["motif"]           = "text " . ($impose_motif ? 'notNull ' : '') . "helped";
        $props["motif_sfmu"]      = "ref class|CMotifSFMU autocomplete|libelle back|RPU";
        $props["ccmu"]            = "enum " . ($impose_degre_urgence ? 'notNull ' : '') . "list|1|P|2|3|4|5|D";
        $props["gemsa"]           = "enum list|1|2|3|4|5|6";
        $props["type_pathologie"] = "enum list|C|E|M|P|T";
        $props["orientation"]     = "enum list|" . implode("|", self::$orientation_value);
        $props["cimu"]            = "enum list|5|4|3|2|1";
        $props["french_triage"]   = "enum list|1|2|3A|3B|4|5";

        $props["mutation_sejour_id"]   = "ref class|CSejour back|rpu_mute";
        $props["box_id"]               = "ref class|CLit back|affectations_rpu";
        $props["sortie_autorisee"]     = "bool";
        $props["date_sortie_aut"]      = "dateTime";
        $props["date_at"]              = "date";
        $props["circonstance"]         = "ref class|CCirconstance autocomplete|libelle dependsOn|actif back|RPU";
        $props["regule_par"]           = "enum list|centre_15|medecin";
        $props["ide_responsable_id"]   = "ref " . ($impose_ide_ref ? 'notNull ' : '') . "class|CMediusers back|ide_responsable";
        $props["pec_inf"]              = "dateTime";
        $props["ioa_id"]               = "ref class|CMediusers back|rpu_ioa";
        $props["pec_ioa"]              = "dateTime";
        $props["color"]                = "color show|0";
        $props["commentaire"]          = "text helped";
        $props["decision_uhcd"]        = "bool default|0";
        $props["diag_incertain_pec"]   = "bool default|0";
        $props["caractere_instable"]   = "bool default|0";
        $props["surv_hosp_specifique"] = "bool default|0";
        $props["exam_comp"]            = "bool default|0";

        $props["_DP"]                      = "code cim10 show|1";
        $props["_provenance"]              = "enum list|1|2|3|4|5|6|7|8";
        $props["_destination"]             = "enum list|0|" . implode("|", CSejour::$destination_values);
        $props["_transport"]               = "enum list|perso|perso_taxi|ambu|ambu_vsl|vsab|smur|heli|fo notNull";
        $props["_mode_entree"]             = "enum list|6|7|8 notNull";
        $props["_mode_entree_id"]          = "ref class|CModeEntreeSejour autocomplete|libelle|true dependsOn|group_id|actif"
            . " notNull";
        $props["_mode_sortie"]             = "enum list|6|7|8|9 default|8";
        $props["_sortie"]                  = "dateTime";
        $props["_patient_id"]              = "ref notNull class|CPatient";
        $props["_responsable_id"]          = "ref notNull class|CMediusers";
        $props["_service_id"]              = "ref" . (CAppUI::conf(
                "dPplanningOp CSejour service_id_notNull"
            ) == 1 ? ' notNull' : '') . " class|CService";
        $props["_UHCD"]                    = "bool";
        $props["_entree"]                  = "dateTime";
        $props["_etablissement_sortie_id"] = "ref class|CEtabExterne autocomplete|nom";
        $props["_etablissement_entree_id"] = "ref class|CEtabExterne autocomplete|nom";
        $props["_service_entree_id"]       = "ref class|CService autocomplete|nom dependsOn|group_id|cancelled";
        $props["_service_sortie_id"]       = "ref class|CService autocomplete|nom dependsOn|group_id|cancelled";
        $props["_grossesse_id"]            = "ref class|CGrossesse";
        $props["_uf_soins_id"]             = "ref class|CUniteFonctionnelle seekable";
        $props["_attente"]                 = "time";
        $props["_presence"]                = "time";
        $props["_can_leave"]               = "time";
        $props["_can_leave_about"]         = "bool";
        $props["_can_leave_since"]         = "bool";
        $props["_can_leave_level"]         = "enum list|ok|warning|error";
        $props["_charge_id"]               = "ref class|CChargePriceIndicator";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadRefSejour();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    function loadView()
    {
        parent::loadView();

        $this->loadRefSejour();
        $this->_ref_sejour->loadView();

        $this->_refs_docitems = $this->_ref_sejour->loadRefsDocItems(false);
        $this->_refs_docitems = array_merge($this->_refs_docitems, $this->loadRefConsult()->loadRefsDocItems(false));

        $this->_nb_files_docs = $this->_ref_sejour->_nb_files_docs + $this->_ref_consult->_nb_files_docs;

        foreach ($this->_refs_docitems as $_docitem) {
            if ($_docitem instanceof CFile && strpos($_docitem->file_type, "pdf") === false) {
                unset($this->_refs_docitems[$_docitem->_guid]);
                $this->_nb_files_docs--;
            }
        }

        $this->loadFirstAndLastPassages();
    }

    /**
     * Chargement du séjour
     *
     * @return CSejour
     */
    public function loadRefSejour(bool $map_fields = true): CSejour
    {
        /** @var CSejour $sejour */
        $sejour = $this->loadFwdRef("sejour_id", true);
        $sejour->loadRefsFwd();

        // Calcul des temps d'attente et présence
        $entree          = CMbDT::time($sejour->entree);
        $this->_presence = CMbDT::subTime($entree, CMbDT::time());

        if ($sejour->sortie_reelle) {
            $this->_presence = CMbDT::subTime($entree, CMbDT::time($sejour->sortie_reelle));
        }

        if ($map_fields) {
            $this->_responsable_id = $sejour->praticien_id;
            $this->_entree         = $sejour->entree;
            $this->_type           = $sejour->type;
            $this->_DP             = $sejour->DP;
            $this->_annule         = $sejour->annule;
            $this->_UHCD           = $sejour->UHCD;

            if (CAppUI::gconf("dPurgences CRPU prat_affectation")) {
                $curr_aff = $sejour->_ref_curr_affectation ?? $sejour->loadRefCurrAffectation();
                $sejour->loadRefsAffectations();
                if ($curr_aff->praticien_id || $sejour->_ref_last_affectation->praticien_id) {
                    $this->_responsable_id = $curr_aff->praticien_id ?: $sejour->_ref_last_affectation->praticien_id;
                }
            }

            $patient =& $sejour->_ref_patient;

            $this->_patient_id = $patient->_id;
            $this->_cp         = $patient->cp;
            $this->_ville      = $patient->ville;
            $this->_naissance  = $patient->naissance;
            $this->_sexe       = $patient->sexe;
            $this->_view       = "RPU du " . CMbDT::dateToLocale(CMbDT::date($this->_entree)) . " pour $patient->_view";

            // Calcul des valeurs de _mode_sortie
            if ($sejour->mode_sortie == "mutation") {
                $this->_mode_sortie = 6;
            }

            if ($sejour->mode_sortie == "transfert") {
                $this->_mode_sortie = 7;
            }

            if ($sejour->mode_sortie == "normal") {
                $this->_mode_sortie = 8;
            }

            if ($sejour->mode_sortie == "deces") {
                $this->_mode_sortie = 9;
            }

            $this->_service_id              = $sejour->service_id;
            $this->_mode_entree             = $sejour->mode_entree;
            $this->_mode_entree_id          = $sejour->mode_entree_id;
            $this->_sortie                  = $sejour->sortie_reelle;
            $this->_provenance              = $sejour->provenance;
            $this->_transport               = $sejour->transport;
            $this->_destination             = $sejour->destination;
            $this->_etablissement_sortie_id = $sejour->etablissement_sortie_id;
            $this->_etablissement_entree_id = $sejour->etablissement_entree_id;
            $this->_service_entree_id       = $sejour->service_entree_id;
            $this->_service_sortie_id       = $sejour->service_sortie_id;
            $this->_uf_soins_id             = $sejour->uf_soins_id;
            $this->_charge_id               = $sejour->charge_id;
        }

        return $this->_ref_sejour = $sejour;
    }

    /**
     * Load ref consult
     *
     * @return CConsultation
     */
    function loadRefConsult()
    {
        // Chargement de la consultation ATU
        if (!$this->_ref_sejour) {
            $this->loadRefSejour();
        }

        $sejour =& $this->_ref_sejour;
        $sejour->loadRefsConsultations();

        if (!CAppUI::conf("dPurgences create_sejour_hospit") && $this->mutation_sejour_id) {
            $this->loadRefSejourMutation()->loadRefsConsultations();
            $this->_ref_consult = $this->_ref_sejour_mutation->_ref_consult_atu;
        } else {
            $this->_ref_consult = $this->_ref_sejour->_ref_consult_atu;
        }

        // Calcul du l'attente
        $this->_attente = $this->_presence;
        if ($this->_ref_consult->_id) {
            $entree         = CMbDT::time($this->_ref_sejour->entree);
            $this->_attente = CMbDT::subTime(
                CMbDT::transform($entree, null, "%H:%M:00"),
                CMbDT::transform(CMbDT::time($this->_ref_consult->heure), null, "%H:%M:00")
            );
        }

        $this->_can_leave_level = $sejour->sortie_reelle ? "" : "ok";
        if (!$sejour->sortie_reelle) {
            if (!$this->_ref_consult->_id) {
                $this->_can_leave_level = "warning";
            }

            // En consultation
            if ($this->_ref_consult->chrono != 64) {
                $this->_can_leave       = -1;
                $this->_can_leave_level = "warning";
            } else {
                if (CMbDT::time($sejour->sortie_prevue) > CMbDT::time()) {
                    $this->_can_leave_since = true;
                    $this->_can_leave       = CMbDT::timeRelative(CMbDT::time(), CMbDT::time($sejour->sortie_prevue));
                } else {
                    $this->_can_leave_about = true;
                    $this->_can_leave       = CMbDT::timeRelative(CMbDT::time($sejour->sortie_prevue), CMbDT::time());
                }

                if (CAppUI::conf("dPurgences rpu_alert_time") > $this->_can_leave) {
                    $this->_can_leave_level = "error";
                } elseif (CAppUI::conf("dPurgences rpu_warning_time") > $this->_can_leave) {
                    $this->_can_leave_level = "warning";
                }
            }
            if (!$this->sortie_autorisee) {
                $this->_can_leave_level = "error";
            } elseif (!$this->_can_leave_level) {
                $this->_can_leave_level = "ok";
            }
        }

        return $this->_ref_consult;
    }

    /**
     * Load ref mutation
     *
     * @return CSejour
     */
    function loadRefSejourMutation()
    {
        /** @var CSejour $sejour */
        $sejour = $this->loadFwdRef("mutation_sejour_id", true);
        $sejour->loadNDA();

        return $this->_ref_sejour_mutation = $sejour;
    }

    /**
     * Bind sejour
     *
     * @return null|string
     */
    function bindSejour()
    {
        if (!$this->_bind_sejour) {
            return null;
        }

        $this->completeField("sejour_id", "_mode_entree_id");

        $this->_bind_sejour = false;

        $this->loadRefSejour(false);
        $sejour = $this->_ref_sejour;

        // Dans le cas du store avec @class, l'updateformfields n'est pas appelé donc $this->_entree n'est pas valué et le calcul
        // de la sortie prévue du séjour est erroné
        if (!$this->_entree) {
            $this->_entree = $sejour->entree;
        }

        $sejour->patient_id    = $this->_patient_id;
        $sejour->group_id      = CGroups::loadCurrent()->_id;
        $sejour->type          = $sejour->_id ? $this->_type : (CAppUI::gconf(
            "dPurgences CRPU type_sejour"
        ) === "urg_consult" ? "consult" : "urg");
        $sejour->recuse        = CAppUI::conf("dPplanningOp CSejour use_recuse") ? -1 : 0;
        $sejour->entree_prevue = $this->_entree;
        $sejour->entree_reelle = $this->_entree;

        $curr_aff = $sejour->loadRefCurrAffectation();

        if (CAppUI::gconf("dPurgences CRPU prat_affectation") && $curr_aff->_id) {
            $curr_aff->praticien_id = $this->_responsable_id;
            $curr_aff->store();
        } else {
            $sejour->praticien_id = $this->_responsable_id;
        }

        if (!$sejour->sortie_prevue) {
            $init_sortie_prevue = CAppUI::gconf("dPurgences CRPU initialiser_sortie_prevue");
            if ($init_sortie_prevue === "sameday") {
                $sejour->sortie_prevue = CMbDT::date(null, $this->_entree) . " 23:59:59";
            } else {
                $sejour->sortie_prevue = CMbDT::dateTime("+" . substr($init_sortie_prevue, 1) . "HOUR", $this->_entree);
            }
        }

        $sejour->annule                  = $this->_annule;
        $sejour->service_id              = $this->_service_id;
        $sejour->etablissement_entree_id = $this->_etablissement_entree_id;
        $sejour->service_entree_id       = $this->_service_entree_id;
        $sejour->mode_entree             = $this->_mode_entree;
        $sejour->mode_entree_id          = $this->_mode_entree_id;
        $sejour->provenance              = $this->_provenance;
        $sejour->destination             = $this->_destination;
        $sejour->transport               = $this->_transport;
        $sejour->UHCD                    = $this->_UHCD;
        $sejour->entree_preparee         = $this->_entree_preparee;
        $sejour->grossesse_id            = $this->_grossesse_id;
        $sejour->uf_soins_id             = $this->_uf_soins_id;
        $sejour->charge_id               = $this->_charge_id;
        // Le patient est souvent chargé à vide ce qui pose problème
        // dans le onAfterStore(). Ne pas supprimer.
        $sejour->_ref_patient = null;

        // Ne pas créer d'affectation via le store du séjour même si la configuration du module hospi est activée.
        // Cela est géré par la classe CRPU.
        $sejour->_create_affectations = false;

        // on garde une trace du service du séjour
        $sejour->loadOldObject();
        $this->_old_service_id = $sejour->_old->service_id;

        /* TODO Supprimer ceci après l'ajout des times picker */
        $sejour->_hour_entree_prevue = null;
        $sejour->_min_entree_prevue  = null;
        $sejour->_hour_sortie_prevue = null;
        $sejour->_min_sortie_prevue  = null;

        if ($msg = $sejour->store()) {
            return $msg;
        }

        // Affectation du sejour_id au RPU
        $this->sejour_id = $sejour->_id;

        return null;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        $this->completeField("box_id");

        // Pour le changement de box du patient, on pose un mutex pour éviter
        // n déplacements simultanés (peut créer des incohérences sur les affectations du patient)
        $mutex = null;
        if ($this->_id && $this->fieldModified("box_id")) {
            try {
                $mutex = new CMbMutex("CRPU-$this->_id");
                if (!$mutex->acquire(30)) {
                    return CAppUI::tr('CRPU-Cannot acquire mutex');
                }
            } catch (Exception $e) {
                return CAppUI::tr('CRPU-Cannot acquire mutex');
            }
        }

        // Création du RPU en l'associant à un séjour existant
        if (!$this->_id && $this->sejour_id) {
            $sejour = $this->loadRefSejour(false);
            // Si y'a un RPU déjà existant on alerte d'une erreur
            if ($sejour->countBackRefs("rpu")) {
                return CAppUI::tr("CRPU-already-exists");
            }
        }

        // Création du RPU ET du séjour associé
        if (!$this->_id && !$this->sejour_id) {
            // Retrait des secondes non gérées dans l'updateplainfields de CSejour
            $this->_entree         = CMbDT::transform(null, $this->_entree, "%Y-%m-%d %H:%M:00");
            $sejour                = new CSejour();
            $sejour->patient_id    = $this->_patient_id;
            $sejour->type          = CAppUI::gconf("dPurgences CRPU type_sejour") === "urg_consult" ? "consult" : "urg";
            $sejour->entree_reelle = $this->_entree;
            $sejour->group_id      = CGroups::loadCurrent()->_id;
            if ($this->_sortie) {
                $sejour->sortie_prevue = $this->_sortie;
            } else {
                $init_sortie_prevue = CAppUI::gconf("dPurgences CRPU initialiser_sortie_prevue");
                if ($init_sortie_prevue === "sameday") {
                    $sejour->sortie_prevue = CMbDT::date(null, $this->_entree) . " 23:59:00";
                } else {
                    $sejour->sortie_prevue = CMbDT::dateTime(
                        "+" . substr($init_sortie_prevue, 1) . "HOUR",
                        $this->_entree
                    );
                }
            }

            // En cas de ressemblance à quelques heures près (cas des urgences), on a affaire au même séjour
            $siblings = $sejour->getSiblings(CAppUI::conf("dPurgences sibling_hours"), $sejour->type);
            if (count($siblings)) {
                $sibling         = reset($siblings);
                $this->sejour_id = $sibling->_id;
                $sejour          = $this->loadRefSejour(false);

                // Si y'a un RPU déjà existant on alerte d'une erreur
                if ($sejour->countBackRefs("rpu")) {
                    return CAppUI::tr("CRPU-already-exists");
                }

                $sejour->service_id              = $this->_service_id;
                $sejour->etablissement_entree_id = $this->_etablissement_entree_id;
                $sejour->service_entree_id       = $this->_service_entree_id;
                $sejour->mode_entree             = $this->_mode_entree;
                $sejour->mode_entree_id          = $this->_mode_entree_id;
                $sejour->provenance              = $this->_provenance;
                $sejour->destination             = $this->_destination;
                $sejour->transport               = $this->_transport;
                $sejour->UHCD                    = $this->_UHCD;
                $sejour->uf_soins_id             = $this->_uf_soins_id;
                $sejour->charge_id               = $this->_charge_id;
            }
        }

        // Renseigner la date et l'heure courante de l'heure de PEC par l'IOA si absente
        // et si diagnistic infirmier saisi et utilisateur connecté de type infirmier
        $curr_user = CMediusers::get();
        if (!$this->pec_ioa && $this->fieldModified("diag_infirmier") && $curr_user->isInfirmiere()) {
            $this->pec_ioa = CMbDT::dateTime();
            $this->ioa_id  = $curr_user->_id;
        }

        // Changement suivant le mode d'entrée
        switch ($this->_mode_entree) {
            case 6:
                $this->_etablissement_entree_id = "";
                break;
            case 7:
                $this->_service_entree_id = "";
                break;
            case 8:
                $this->_service_entree_id       = "";
                $this->_etablissement_entree_id = "";
                break;
            default:
        }

        // Bind Sejour
        if ($msg = $this->bindSejour()) {
            if ($mutex) {
                $mutex->release();
            }

            return $msg;
        }

        // Mode de sortie normal (ou premier normal personnalisé) par défaut si l'autorisation de sortie est réalisée
        $this->completeField("sejour_id");
        if (
            $this->_id && $this->sejour_id && CAppUI::conf("dPplanningOp CSejour specified_output_mode")
            && $this->fieldModified("sortie_autorisee", 1)
        ) {
            $sejour                  = $this->loadRefSejour();
            $sejour->_generate_NDA   = false;
            $sejour->_no_synchro     = true;
            $sejour->_no_synchro_eai = true;
            if (!$sejour->mode_sortie) {
                $sejour->mode_sortie = "normal";
            }
            $change_mode_sortie_id = !$sejour->mode_sortie_id && CAppUI::conf(
                "dPplanningOp CSejour use_custom_mode_sortie"
            );
            if ($change_mode_sortie_id) {
                $mode_pec           = new CModeSortieSejour();
                $mode_pec->group_id = CGroups::loadCurrent()->_id;
                $mode_pec->actif    = '1';
                $mode_pec->mode     = 'normal';
                $mode_pec->loadMatchingObject("code");
                if ($mode_pec->_id) {
                    $sejour->mode_sortie_id = $mode_pec->_id;
                }
            }
            if (!$sejour->mode_sortie || $change_mode_sortie_id) {
                if ($msg = $sejour->store()) {
                    if ($mutex) {
                        $mutex->release();
                    }

                    return $msg;
                }
            }
        }

        // Synchronisation AT
        $this->loadRefConsult();

        if ($this->_ref_consult->_id) {
            //Evite les check dans le cas des fusions lors du store de la consult
            $this->_ref_consult->_forwardRefMerging = $this->_forwardRefMerging;
            $this->_ref_consult->_transfert_rpu     = $this->_transfert_rpu;
            if ($this->_validation && CAppUI::conf("dPurgences valid_cotation_sortie_reelle")) {
                $this->_ref_consult->valide = "1";
            }

            if ($this->fieldModified("date_at") && !$this->_date_at) {
                $this->_date_at              = true;
                $this->_ref_consult->date_at = $this->date_at;
            }

            if ($msg = $this->_ref_consult->store()) {
                if ($mutex) {
                    $mutex->release();
                }

                return $msg;
            }
        }

        // Bind affectation
        if (CAppUI::conf("dPurgences create_affectation")) {
            if ($msg = $this->storeAffectation()) {
                if ($mutex) {
                    $mutex->release();
                }

                return $msg;
            }
        }

        if ($this->fieldModified("ccmu") && (($this->box_id && !$this->loadPossibleUpdateCcmu()))) {
            $this->ccmu  = $this->_old->ccmu;
            $change_ccmu = false;
        }

        // Standard Store
        if ($msg = parent::store()) {
            if ($mutex) {
                $mutex->release();
            }

            return $msg;
        }

        // Déclenchement pour avoir les données RPU
        // Pas de sycnhro dans certains cas
        $this->_ref_sejour->_no_synchro     = true;
        $this->_ref_sejour->_no_synchro_eai = true;
        $this->_ref_sejour->notify(ObjectHandlerEvent::AFTER_STORE());

        $this->_ref_sejour->_docitems_guid = $this->_docitems_guid;

        if ($msg = $this->_ref_sejour->storeDocItems()) {
            if ($mutex) {
                $mutex->release();
            }

            return $msg;
        }

        if ($mutex) {
            $mutex->release();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function loadComplete()
    {
        parent::loadComplete();

        $this->loadRefSejour()->loadComplete();
    }

    /**
     * Load the circonstance
     *
     * @return CCirconstance
     */
    function loadRefCirconstance()
    {
        $circonstance = new CCirconstance();
        $circonstance->load($this->circonstance);

        return $this->_ref_circonstance = $circonstance;
    }

    /**
     * @inheritdoc
     */
    function fillLimitedTemplate(&$template)
    {
        $this->loadRefsLastAttentes();
        $consult = $this->loadRefConsult() ?: new CConsultation();

        $rpu_section = CAppUI::tr('CRPU-rpu_id');
        $rpu_subItem = CAppUI::tr('CConsultation');

        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $consult->loadRefPraticien();
        // Duplication des champs de la consultation
        $template->addProperty(
            "$rpu_section - $rpu_subItem - " . CAppUI::tr('CRPU-Practitioner name'),
            $consult->_ref_praticien->_user_first_name
        );
        $template->addProperty(
            "$rpu_section - $rpu_subItem - " . CAppUI::tr('CRPU-Practitioner first name'),
            $consult->_ref_praticien->_user_last_name
        );
        $template->addProperty(
            "$rpu_section - $rpu_subItem - " . CAppUI::tr('CRPU-motif-court'),
            $consult->motif
        );
        $template->addProperty(
            "$rpu_section - $rpu_subItem - " . CAppUI::tr('CPatient-rques'),
            $consult->rques
        );
        $template->addProperty(
            "$rpu_section - $rpu_subItem - " . CAppUI::tr('COperation-examen_operation_id'),
            $consult->examen
        );
        $template->addProperty(
            "$rpu_section - $rpu_subItem - " . CAppUI::tr('CTraitement'),
            $consult->traitement
        );

        foreach ($consult->getExamFields() as $field) {
            $loc_field = CAppUI::tr("CConsultation-{$field}");

            if ($consult->_specs[$field]->markdown) {
                $template->addMarkdown(
                    "$rpu_section - $rpu_subItem - $loc_field",
                    $loc_field . ' : ' . $consult->$field
                );
            } else {
                $template->addProperty(
                    "$rpu_section - $rpu_subItem - $loc_field",
                    $loc_field . ' : ' . $consult->$field
                );
            }
        }

        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-diag_infirmier'), $this->diag_infirmier);
        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-Supports pain'), $this->pec_douleur);
        $template->addProperty(
            "$rpu_section - " . CAppUI::tr('CRPU-_pec_transport'),
            $this->getFormattedValue("pec_transport")
        );
        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-motif-court'), $this->motif);
        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-ccmu-court'), $this->getFormattedValue("ccmu"));

        if (CAppUI::gconf("dPurgences Display display_cimu")) {
            $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-cimu-court'), $this->getFormattedValue("cimu"));
        }

        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-gemsa'), $this->getFormattedValue("gemsa"));
        $attente_radio = $this->_ref_last_attentes["radio"];
        $template->addProperty(
            "$rpu_section - " . CAppUI::tr('CRPUAttente-type_radio'),
            CAppUI::tr("CRPUAttente.type_radio." . $attente_radio->type_radio)
        );
        $template->addDateTimeProperty("$rpu_section - " . CAppUI::tr('CRPU-Radio Departure'), $attente_radio->depart);
        $template->addDateTimeProperty("$rpu_section - " . CAppUI::tr('CRPU-Return Radio'), $attente_radio->retour);
        $attente_bio = $this->_ref_last_attentes["bio"];
        $template->addDateTimeProperty("$rpu_section - " . CAppUI::tr('CRPUAttente-bio-depart'), $attente_bio->depart);
        $template->addProperty(
            "$rpu_section - " . CAppUI::tr('CRPU-Biology Repository (user)'),
            $attente_bio->loadRefUser()->_view
        );
        $template->addDateTimeProperty("$rpu_section - " . CAppUI::tr('CRPUAttente-bio-retour'), $attente_bio->retour);
        $attente_specialiste = $this->_ref_last_attentes["specialiste"];
        $template->addDateTimeProperty(
            "$rpu_section - " . CAppUI::tr('CRPUAttente-specialiste-depart'),
            $attente_specialiste->depart
        );
        $template->addDateTimeProperty(
            "$rpu_section - " . CAppUI::tr('CRPUAttente-specialiste-retour'),
            $attente_specialiste->retour
        );
        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU.urprov.AT'), $this->getFormattedValue("date_at"));
        $libelle_at = $this->date_at ? CAppUI::tr('CRPU-Accident at work') . " " . $this->getFormattedValue(
            "date_at"
        ) : "";
        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-Work accident label'), $libelle_at);
        $template->addProperty(
            "$rpu_section - " . CAppUI::tr('CRPU-sortie_assuree.1'),
            $this->getFormattedValue("sortie_autorisee")
        );

        $lit = new CLit();
        if ($this->box_id) {
            $lit->load($this->box_id);
        }
        $template->addProperty("$rpu_section - " . CAppUI::tr('CRPU-box_id'), $lit->_view);

        $template->addProperty(
            "$rpu_section - " . CAppUI::tr('CRPU-orientation'),
            $this->getFormattedValue("orientation")
        );

        $criteres_uhcd = "";
        foreach (self::$criteres_uhcd as $_critere) {
            if ($this->$_critere === '0') {
                continue;
            }
            $criteres_uhcd .= CAppUI::tr('CRPU-' . $_critere) . '<br/>';
        }
        $template->addProperty(
            "$rpu_section - " . CAppUI::tr('CRPU-Criteres validation UHCD'),
            $criteres_uhcd,
            null,
            false
        );


        if (CModule::getActive("forms")) {
            CExObject::addFormsToTemplate($template, $this, "$rpu_section");
        }

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }

    /**
     * @inheritdoc
     */
    function completeLabelFields(&$fields, $params)
    {
        $sejour = $this->loadRefSejour();
        $sejour->completeLabelFields($fields, $params);

        $patient = $sejour->loadRefPatient();
        $patient->completeLabelFields($fields, $params);
    }

    /**
     * Return the SFMU Motif
     *
     * @return CMotifSFMU
     */
    function loadRefMotifSFMU()
    {
        $this->_ref_motif_sfmu = $this->loadFwdRef("motif_sfmu", true);

        if (isset(self::$class_sfmu[$this->_ref_motif_sfmu->categorie])) {
            $this->_class_sfmu = self::$class_sfmu[$this->_ref_motif_sfmu->categorie];
        }

        return $this->_ref_motif_sfmu;
    }

    /**
     * Load box
     *
     * @param bool $cache Use object cache
     *
     * @return CLit
     */
    function loadRefBox($cache = true)
    {
        return $this->_ref_box = $this->loadFwdRef("box_id", $cache);
    }

    /**
     * Chargement de l'IDE responsable
     *
     * @return CMediusers|null
     */
    function loadRefIDEResponsable()
    {
        return $this->_ref_ide_responsable = $this->loadFwdRef("ide_responsable_id");
    }

    /**
     * Store affectation
     *
     * @return null|string
     */
    function storeAffectation()
    {
        $this->completeField("box_id", "sejour_id", "mutation_sejour_id");
        $sejour = $this->loadRefSejour();

        $sejour->completeField("service_id");

        if (!$this->_id && !$sejour->service_id) {
            return null;
        }

        if ($this->_bind_sejour !== false) {
            return null;
        }

        if ($this->_id && (!$this->fieldModified("box_id") && $sejour->service_id == $this->_old_service_id)) {
            return null;
        }

        if (!$this->_store_affectation) {
            return null;
        }

        $uf_medicale_id = $this->_uf_medicale_id;

        if ($this->mutation_sejour_id && $this->mutation_sejour_id != $this->sejour_id) {
            $sejour         = $this->loadRefSejourMutation();
            $uf_medicale_id = $sejour->uf_medicale_id;
        }

        $affectations = $sejour->loadRefsAffectations();

        $affectation                 = new CAffectation();
        $affectation->entree         = (count($affectations) == 0) ? $sejour->entree : CMbDT::dateTime();
        $affectation->lit_id         = $this->box_id;
        $affectation->service_id     = $this->_service_id;
        $affectation->uf_medicale_id = $uf_medicale_id;

        if (!$this->box_id && !$this->_service_id) {
            $affectation->service_id = $sejour->service_id;
        }

        $msg = $sejour->forceAffectation($affectation);

        if ($msg instanceof CAffectation) {
            return null;
        }

        return $msg;
    }

    /**
     * Vérification de la possibilité de valider ou annuler l'échelle de tri
     */
    function loadCanValideRPU()
    {
        $reponses_ok      = 0;
        $reponses_notnull = 0;
        foreach ($this->_ref_reponses as $_reponse) {
            if ($_reponse->result == "1" || $_reponse->result == "0") {
                $reponses_notnull++;
                if ($_reponse->result == "1") {
                    $reponses_ok++;
                }
            }
        }
    }

    /**
     * Ordonnancement par degré des constantes notées
     *
     * @param bool $check_ccmu modification du ccmu
     *
     * @return void
     */
    function orderCtes($check_ccmu = true)
    {
        if (!$this->_id) {
            return null;
        }
        $this->_ref_cts_degre         = [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
        ];
        $this->_estimation_ccmu       = 4;
        $this->_ref_latest_constantes = CConstantesMedicales::getLatestFor(
            $this->_patient_id,
            null,
            [],
            $this->_ref_sejour,
            false
        );

        $where                  = [];
        $where["patient_id"]    = " = '" . $this->_patient_id . "'";
        $where["context_class"] = " = '" . $this->_ref_sejour->_class . "'";
        $where["context_id"]    = " = '" . $this->_ref_sejour->_id . "'";
        $where["comment"]       = " IS NOT NULL";
        $constante              = new CConstantesMedicales();
        $constante->loadObject($where, "datetime ASC");
        if ($constante->_id) {
            $this->_ref_latest_constantes[0]->comment = $constante->comment;
        }

        $latest_constantes = $this->_ref_latest_constantes;
        $grossesse         = $this->_ref_sejour->loadRefGrossesse();
        $sa_grossesse      = (CModule::getActive(
            "maternite"
        ) && $grossesse->terme_prevu) ? $grossesse->_semaine_grossesse : 0;
        $enceinte          = $this->_ref_sejour->_ref_grossesse ? 1 : 0;

        if ($glasgow = $latest_constantes[0]->glasgow) {
            $degre = $glasgow <= 8 ? 1 : 4;
            if ($glasgow >= 9 && $glasgow <= 13) {
                $degre = 2;
            } elseif ($glasgow == 14 || $glasgow == 15) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'glasgow';
        }
        if ($pouls = $latest_constantes[0]->pouls) {
            $degre = $pouls < 40 || $pouls > 150 ? 1 : 4;
            if (($pouls >= 40 && $pouls <= 50) || ($pouls >= 130 && $pouls <= 150)) {
                $degre = 2;
            } elseif ($pouls >= 51 && $pouls <= 129) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'pouls';
        }

        //Tensions
        if ($latest_constantes[0]->ta_gauche) {
            $this->orderTA(
                "ta_gauche",
                $latest_constantes[0]->_ta_gauche_systole,
                $latest_constantes[0]->_ta_gauche_diastole
            );
        }
        if ($latest_constantes[0]->ta_droit) {
            $this->orderTA(
                "ta_droit",
                $latest_constantes[0]->_ta_droit_systole,
                $latest_constantes[0]->_ta_droit_diastole
            );
        }
        if ($latest_constantes[0]->ta) {
            $this->orderTA("ta", $latest_constantes[0]->_ta_systole, $latest_constantes[0]->_ta_diastole);
        }

        if ($frequence = $latest_constantes[0]->frequence_respiratoire) {
            $degre = $frequence > 35 || $frequence <= 8 ? 1 : 4;
            if (($frequence >= 25 && $frequence <= 35) || ($frequence >= 9 && $frequence <= 12)) {
                $degre = 2;
            } elseif ($frequence >= 13 && $frequence <= 24) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'frequence_respiratoire';
        }
        if ($spo2 = $latest_constantes[0]->spo2) {
            $degre = $spo2 < 90 ? 1 : 4;
            if ($spo2 >= 90 && $spo2 <= 93) {
                $degre = 2;
            } elseif ($spo2 >= 94 && $spo2 <= 100) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'spo2';
        }
        if ($temp = $latest_constantes[0]->temperature) {
            $degre = $temp < 32 ? 1 : 4;
            if (($temp >= 32 && $temp <= 35) || $temp > 40) {
                $degre = 2;
            } elseif ($temp > 35 && $temp <= 40) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'temperature';
        }

        if ($glycemie = $latest_constantes[0]->_glycemie) {
            if ($glycemie < 4 || $glycemie >= 25) {
                $degre = 2;
            } elseif ($glycemie >= 4 && $glycemie < 25) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'glycemie';
        }

        if ($cetonemie = $latest_constantes[0]->_cetonemie) {
            if ($cetonemie >= 0.6) {
                $degre = 2;
            } elseif ($cetonemie < 0.6) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'cetonemie';
        }

        $patient = $this->_ref_sejour->_ref_patient;
        if ($latest_constantes[0]->peak_flow && $latest_constantes[0]->taille && $patient->_annees && $patient->sexe) {
            $taux                                                            = round(
                ($latest_constantes[0]->peak_flow / $latest_constantes[0]->_peak_flow) * 100,
                2
            );
            $degre                                                           = $taux > 50 ? self::$default_degre_cte : 2;
            $this->_ref_cts_degre[$degre][$latest_constantes[0]->_peak_flow] = 'peak_flow';
        }

        if ($contraction_uterine = $latest_constantes[0]->contraction_uterine) {
            $degre = $contraction_uterine >= 3 ? 1 : 4;
            if ($contraction_uterine > 1 && $contraction_uterine < 3) {
                $degre = 2;
            } elseif ($contraction_uterine <= 1) {
                $degre = self::$default_degre_cte;
            }
            $this->_ref_cts_degre[$degre][] = 'contraction_uterine';
        }
        if ($latest_constantes[0]->bruit_foetal && $sa_grossesse >= 20) {
            $bruit_foetal = $latest_constantes[0]->bruit_foetal;
            $degre        = 4;
            if ($sa_grossesse > 24) {
                if (($bruit_foetal >= 40 && $bruit_foetal <= 100) || $bruit_foetal >= 180) {
                    $degre = 1;
                } elseif ($bruit_foetal == 0 || ($bruit_foetal >= 101 && $bruit_foetal <= 119) || ($bruit_foetal >= 160 && $bruit_foetal <= 179)) {
                    $degre = 2;
                } elseif ($bruit_foetal >= 120 && $bruit_foetal <= 159) {
                    $degre = self::$default_degre_cte;
                }
            } else {
                $degre = $bruit_foetal > 0 ? self::$default_degre_cte : 2;
            }
            $this->_ref_cts_degre[$degre][] = 'bruit_foetal';
        }

        unset($this->_ref_cts_degre[self::$default_degre_cte == 3 ? 4 : 3]);
        if (count($this->_ref_cts_degre[1])) {
            $this->_estimation_ccmu = 1;
        } elseif (count($this->_ref_cts_degre[2])) {
            $this->_estimation_ccmu = 2;
        } elseif (count($this->_ref_cts_degre[self::$default_degre_cte])) {
            $this->_estimation_ccmu = self::$default_degre_cte;
        }
        ksort($this->_ref_cts_degre);
    }

    function orderTA($cte, $tas, $tad)
    {
        $latest_constantes = $this->_ref_latest_constantes;
        $grossesse         = $this->_ref_sejour->_ref_grossesse;
        $sa_grossesse      = (CModule::getActive(
            "maternite"
        ) && $grossesse->terme_prevu) ? $grossesse->_semaine_grossesse : 0;
        $enceinte          = $this->_ref_sejour->_ref_grossesse ? 1 : 0;

        $degre = 4;
        //Si la femme est enceinte et >= 20 SA et 1 mois PP
        if ($sa_grossesse >= 20) {
            if ($tas >= 180 || $tas <= 70 || $tad >= 115) {
                $degre = 1;
            } elseif (($tas >= 160 && $tas < 180) || ($tas > 70 && $tas <= 80) || ($tad >= 105 && $tad < 115)) {
                $degre = 2;
            } elseif (($tas > 80 && $tas <= 159) || ($tad < 105)) {
                $degre = self::$default_degre_cte;
            }
        } else {
            if ($tas >= 230 || $tas <= 70 || $tad >= 130) {
                $degre = 1;
            } elseif (($tas > 180 && $tas < 230) || ($tas > 70 && $tas <= 90) || ($tad >= 115 && $tad < 130)) {
                $degre = 2;
            } elseif (($tas > 90 && $tas <= 180) || ($tad < 115)) {
                $degre = self::$default_degre_cte;
            }
        }
        $this->_ref_cts_degre[$degre][] = $cte;

        //Index de choc
        if ($pouls = $latest_constantes[0]->pouls) {
            if (in_array('index_de_choc', $this->_ref_cts_degre[$degre])) {
                return;
            }
            $degre                          = $pouls > $tas ? 2 : self::$default_degre_cte;
            $this->_ref_cts_degre[$degre][] = 'index_de_choc';
            if (
                isset($this->_ref_cts_degre[self::$default_degre_cte]) && isset($this->_ref_cts_degre[2]) && in_array(
                    "index_de_choc",
                    $this->_ref_cts_degre[2]
                ) && in_array("index_de_choc", $this->_ref_cts_degre[self::$default_degre_cte])
            ) {
                foreach ($this->_ref_cts_degre[self::$default_degre_cte] as $num => $nom) {
                    if ($nom == "index_de_choc") {
                        unset($this->_ref_cts_degre[self::$default_degre_cte][$num]);
                    }
                }
            }
        }
    }

    /**
     * On regarde s'il est possible de modifier le degré d'urgence
     *
     * @return bool
     */
    function loadPossibleUpdateCcmu()
    {
        $this->_possible_update_ccmu = true;
        if (!CAppUI::gconf("dPurgences CRPU lock_change_ccmu_in_box") || !$this->box_id) {
            return $this->_possible_update_ccmu;
        } else {
            $affectation = $this->_ref_sejour->loadRefCurrAffectation();
            $chambre     = $affectation->loadRefLit()->loadRefChambre();
            if (!$chambre->is_waiting_room) {
                $this->_possible_update_ccmu = false;
            }
        }

        return $this->_possible_update_ccmu;
    }

    /**
     * Charge la réservation du RPU
     *
     * @return CRPUReservationBox
     */
    function loadRefReservation()
    {
        return $this->_ref_reservation = $this->loadUniqueBackRef("reservation_rpu");
    }

    /**
     * Charge l'IOA associé au RPU
     *
     * @return CMediusers
     */
    function loadRefIOA()
    {
        return $this->_ref_ioa = $this->loadFwdRef("ioa_id", true);
    }

    /**
     * Charge des attentes de RPU (imagerie, biologie ou spécialiste)
     *
     * @return CRPUAttente[]
     */
    function loadRefsAttentes()
    {
        $this->_ref_attente_empty = new CRPUAttente();
        $this->_ref_attentes      = $this->loadBackRefs("attentes_rpu");

        $this->_ref_attentes_by_type = [
            "radio"       => [],
            "bio"         => [],
            "specialiste" => [],
        ];

        foreach ($this->_ref_attentes as $_attente) {
            $this->_ref_attentes_by_type[$_attente->type_attente][$_attente->_id] = $_attente;
        }


        return $this->_ref_attentes;
    }

    /**
     * Récupération des premières attentes de RPU (imagerie, biologie ou spécialiste)
     *
     * @param array $types Types d'attente à charger
     *
     * @return array
     */
    public function loadRefsFirstAttentes(array $types = ["bio", "radio", "specialiste"]): array
    {
        $attente = new CRPUAttente();
        $where   = [];
        if (in_array("bio", $types)) {
            //Biologie
            $where["type_attente"]            = " = 'bio'";
            $bios                             = $this->loadBackRefs(
                "attentes_rpu",
                "attente_id ASC",
                1,
                null,
                null,
                null,
                "",
                $where
            );
            $this->_ref_first_attentes["bio"] = ($bios && count($bios)) ? reset($bios) : $attente;
        }
        if (in_array("radio", $types)) {
            //Radio
            $where["type_attente"]              = " = 'radio'";
            $radios                             = $this->loadBackRefs(
                "attentes_rpu",
                "attente_id ASC",
                1,
                null,
                null,
                null,
                "",
                $where
            );
            $this->_ref_first_attentes["radio"] = ($radios && count($radios)) ? reset($radios) : $attente;
        }
        if (in_array("specialiste", $types)) {
            //Spécialiste
            $where["type_attente"]                    = " = 'specialiste'";
            $specialistes                             = $this->loadBackRefs(
                "attentes_rpu",
                "attente_id ASC",
                1,
                null,
                null,
                null,
                "",
                $where
            );
            $this->_ref_first_attentes["specialiste"] = ($specialistes && count($specialistes)) ? reset(
                $specialistes
            ) : $attente;
        }

        return $this->_ref_first_attentes;
    }

    /**
     * Récupération des dernières attentes de RPU (imagerie, biologie ou spécialiste)
     *
     * @param array $types Types d'attente à charger
     *
     * @return []
     */
    function loadRefsLastAttentes($types = ["bio", "radio", "specialiste"])
    {
        $attente = new CRPUAttente();
        $where   = [];
        if (in_array("bio", $types)) {
            //Biologie
            $where["type_attente"]           = " = 'bio'";
            $bios                            = $this->loadBackRefs(
                "attentes_rpu",
                "attente_id DESC",
                1,
                null,
                null,
                null,
                "attentes_rpu_bio",
                $where
            );
            $this->_ref_last_attentes["bio"] = ($bios && count($bios)) ? reset($bios) : $attente;
        }
        if (in_array("radio", $types)) {
            //Radio
            $where["type_attente"]             = " = 'radio'";
            $radios                            = $this->loadBackRefs(
                "attentes_rpu",
                "attente_id DESC",
                1,
                null,
                null,
                null,
                "attentes_rpu_radio",
                $where
            );
            $this->_ref_last_attentes["radio"] = ($radios && count($radios)) ? reset($radios) : $attente;
        }
        if (in_array("specialiste", $types)) {
            //Spécialiste
            $where["type_attente"]                   = " = 'specialiste'";
            $specialistes                            = $this->loadBackRefs(
                "attentes_rpu",
                "attente_id DESC",
                1,
                null,
                null,
                null,
                "attentes_rpu_specialiste",
                $where
            );
            $this->_ref_last_attentes["specialiste"] = ($specialistes && count($specialistes)) ? reset(
                $specialistes
            ) : $attente;
        }

        return $this->_ref_last_attentes;
    }

    public static function massLoadRefsAttentes(array $rpus, array $types = ["bio", "radio", "specialiste"]): array
    {
        $attentes = [];

        foreach ($types as $type) {
            if (in_array($type, ["bio", "radio", "specialiste"])) {
                $results = CStoredObject::massLoadBackRefs($rpus, "attentes_rpu", "attente_id DESC", [
                    "type_attente" => " = '$type'",
                ], null, "attentes_rpu_$type");

                if (is_array($results)) {
                    $attentes = array_replace($attentes, $results);
                }
            }
        }

        return $attentes;
    }

    /**
     * Charge les liens de catégorie
     *
     * @return CRPULinkCat[]
     */
    function loadRefCategories()
    {
        return $this->_ref_rpu_categories = $this->loadBackRefs("categories_rpu");
    }

    /**
     * Load the reevaluation PEC
     *
     * @return CRPUReevalPEC[]
     * @throws Exception
     */
    function loadRefReevaluationsPec()
    {
        $this->_count_rpu_reevaluations_pec = $this->countBackRefs("reevaluations_pec_rpu");

        return $this->_ref_rpu_reevaluations_pec = $this->loadBackRefs("reevaluations_pec_rpu", "datetime DESC");
    }

    /**
     * Load the last reevaluation PEC
     *
     * @return CRPUReevalPEC
     * @throws Exception
     */
    function loadRefLastReevaluationPec()
    {
        $reevaluation_pec = $this->loadRefReevaluationsPec();

        return $this->_ref_rpu_last_reevaluation_pec = reset($reevaluation_pec);
    }

    /**
     * Charge les catégories et les fichiers pour un RPU
     *
     * @param CRPU[] $rpus
     */
    static function massLoadCategories($rpus = [])
    {
        if (!count($rpus)) {
            return;
        }

        CStoredObject::massLoadBackRefs($rpus, "categories_rpu");

        $links_cat = [];
        foreach ($rpus as $_rpu) {
            foreach ($_rpu->loadRefCategories() as $_cat) {
                $links_cat[$_cat->_id] = $_cat;
            }
        }

        CStoredObject::massLoadFwdRef($links_cat, "rpu_categorie_id");

        foreach ($links_cat as $_link_cat) {
            $_link_cat->loadRefCategorie()->loadRefIcone();
        }

        foreach ($rpus as $_rpu) {
            if (count($_rpu->_ref_rpu_categories)) {
                CMbArray::pluckSort($_rpu->_ref_rpu_categories, SORT_ASC, "_ref_cat", "motif");
            }
        }
    }

    static function loadServices($sejour)
    {
        $group = CGroups::get();

        $services_type = [
            "Urgences" => CService::loadServicesUrgence(),
            "UHCD"     => CService::loadServicesUHCD(),
        ];

        if (CAppUI::conf("dPurgences view_rpu_uhcd")) {
            // Affichage des services UHCD et d'urgence
            $services = CService::loadServicesUHCDRPU();
        } elseif (in_array($sejour->type, ["comp", "ambu"]) && $sejour->UHCD) {
            // UHCD pour un séjour "comp" et en UHCD
            $services = $services_type["UHCD"];
            unset($services_type["Urgences"]);
        } else {
            // Urgences pour un séjour "urg"
            $services = $services_type["Urgences"];
            unset($services_type["UHCD"]);
        }

        if (CAppUI::conf("dPurgences CRPU imagerie_etendue", $group)) {
            $service_imagerie          = CService::loadServicesImagerie();
            $services_type["Imagerie"] = $service_imagerie;
            $services                  = array_merge($services, $services_type["Imagerie"]);
        }

        return [$services, $services_type];
    }

    static function getBlocagesLits()
    {
        $where                = [];
        $where["entree"]      = "<= '" . CMbDT::dateTime() . "'";
        $where["sortie"]      = ">= '" . CMbDT::dateTime() . "'";
        $where["function_id"] = "IS NOT NULL";

        $affectation = new CAffectation();
        /** @var CAffectation[] $blocages_lit */
        $blocages_lit = $affectation->loadList($where);

        $where["function_id"] = "IS NULL";

        foreach ($blocages_lit as $blocage) {
            $blocage->loadRefLit()->loadRefChambre()->loadRefService();
            $where["lit_id"] = "= '$blocage->lit_id'";

            if ($affectation->loadObject($where)) {
                $_sejour_aff              = $affectation->loadRefSejour();
                $_patient                 = $_sejour_aff->loadRefPatient();
                $blocage->_ref_lit->_view .= " indisponible jusqu'à " . CMbDT::transform(
                    $affectation->sortie,
                    null,
                    "%Hh%Mmin %d-%m-%Y"
                );
                $blocage->_ref_lit->_view .= " (" . $_patient->_view . " (" . strtoupper($_patient->sexe) . ") ";
                $blocage->_ref_lit->_view .= CAppUI::conf(
                    "dPurgences age_patient_rpu_view"
                ) ? $_patient->_age . ")" : ")";
            }
        }

        return $blocages_lit;
    }

    /**
     * Return the CIMU color
     *
     * @return string
     */
    function getColorCIMU()
    {
        return $this->_color_cimu = "#" . CAppUI::gconf("dPurgences Display color_cimu_" . $this->cimu);
    }

    /**
     * Main courante Export CSV
     *
     * @param array $listSejours List stay
     * @param array $boxes       List boxes
     * @param Date  $date        Date
     *
     * @return void
     * @throws Exception
     */
    static function exportMainCourante($listSejours, $boxes, $date)
    {
        $csv         = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);
        $ccmu_header = CAppUI::gconf("dPurgences Display display_order") == "ccmu" || !CAppUI::gconf(
            "dPurgences Display display_cimu"
        );

        $header = [
            $ccmu_header ? CAppUI::tr("CRPU-ccmu") : CAppUI::tr("CRPU-cimu"),
            CAppUI::tr("CProtocoleRPU-box_id"),
            CAppUI::tr("CPatient"),
            CAppUI::tr("CPatient-_IPP"),
            CAppUI::tr("CRPU-_entree"),
            CAppUI::tr("CIncrementer._object_class.CSejour"),
            CAppUI::tr("CRPU-_responsable_id"),
            CAppUI::tr("CRPU-_attente-court"),
            CAppUI::tr("CRPU-_presence-court"),
            CAppUI::tr("CConsultation"),
            CAppUI::tr("CRPU-_sortie"),
            CAppUI::tr("CRPU-diag_infirmier-court"),
            CAppUI::tr("CRPU-Supported by the IOA"),
            CAppUI::tr("CRPU-Supported by the nurse"),
            CAppUI::tr("CRPU.pec"),
        ];

        $csv->setColumnNames($header);
        $csv->writeLine($header);

        foreach ($listSejours as $_sejour) {
            $rpu             = $_sejour->_ref_rpu;
            $patient         = $_sejour->_ref_patient;
            $consult         = $rpu->_ref_consult;
            $last_reeval_pec = $rpu->_ref_rpu_last_reevaluation_pec;

            $ccmu = $rpu->ccmu ? CAppUI::tr("CRPU.ccmu.$rpu->ccmu") : null;
            $cimu = $rpu->cimu ? CAppUI::tr("CRPU.cimu.$rpu->cimu") : null;

            if ($last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->ccmu) {
                $ccmu = CAppUI::tr("CRPU.ccmu.$last_reeval_pec->ccmu");
            }

            if ($last_reeval_pec && $last_reeval_pec->_id && $last_reeval_pec->cimu) {
                $cimu = CAppUI::tr("CRPU.cimu.$last_reeval_pec->cimu");
            }

            $box = null;

            if ($rpu->box_id && array_key_exists($rpu->box_id, $boxes)) {
                $box = $boxes[$rpu->box_id]->_view;
            }

            $responsable = $_sejour->_ref_praticien->_view;

            if ($_sejour->_ref_praticien->_ref_remplacant) {
                $responsable = $_sejour->_ref_praticien->_ref_remplacant . " (remplaçant de $responsable)";
            }

            $consultation = "Consult. " . CMbDT::format($consult->heure, CAppUI::conf('time'));

            if ($consult->_ref_plageconsult && ($date != $consult->_ref_plageconsult->date)) {
                $consultation .= " le " . CMbDT::format($consult->_ref_plageconsult->date, CAppUI::conf('date'));
            }

            $sejour_sortie = null;

            if ($_sejour->sortie_reelle) {
                if ($_sejour->mode_sortie != "normal") {
                    $sejour_sortie = CAppUI::tr("CSejour.mode_sortie.$_sejour->mode_sortie");
                } else {
                    $sejour_sortie = "Sortie";
                }

                $sejour_sortie .= " à " . CMbDT::format($_sejour->sortie_reelle, CAppUI::conf('time'));
            }


            $line = [
                $ccmu_header ? $ccmu : $cimu,
                $box,
                $patient->_view,
                $patient->_IPP,
                CMbDT::format($rpu->_entree, CAppUI::conf('datetime')),
                $_sejour->_NDA_view,
                $responsable,
                $rpu->_attente ? CMbDT::format($rpu->_attente, CAppUI::conf('time')) : null,
                $rpu->_presence ? CMbDT::format($rpu->_presence, CAppUI::conf('time')) : null,
                $consultation,
                $sejour_sortie,
                $rpu->diag_infirmier ?: $rpu->motif_entree,
                $rpu->pec_ioa ? CMbDT::format($rpu->pec_ioa, CAppUI::conf('datetime')) : null,
                $rpu->pec_inf ? CMbDT::format($rpu->pec_inf, CAppUI::conf('datetime')) : null,
                $consult && $consult->_ref_praticien ? $consult->_ref_praticien->_view : null,
            ];

            $csv->writeLine($line);
        }

        $date = CMbDT::format($date, "%Y%m%d");

        $csv->stream("export_main_courante_" . $date);
    }

    /**
     * @return CExtractPassages[]
     * @throws Exception
     */
    public function loadRefsExtractPassages(): array
    {
        /** @var CRPUPassage[] $passages_liaison */
        $passages_liaison = $this->loadRefsPassages();

        return $this->_ref_extract_passages = CStoredObject::massLoadFwdRef($passages_liaison, 'extract_passages_id');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function loadRefsPassages(): array
    {
        return $this->_ref_passages = $this->loadBackRefs('passages');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function loadFirstAndLastPassages(): array
    {
        if (!$this->_ref_extract_passages) {
            $this->loadRefsExtractPassages();
        }

        return [
            'first' => $this->_first_extract_passages = reset($this->_ref_extract_passages),
            'last'  => $this->_last_extract_passages = end($this->_ref_extract_passages),
        ];
    }
}
