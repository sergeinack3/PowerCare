<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use DateTime;
use Exception;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CBesoinRessource;
use Ox\Mediboard\Bloc\CBlocage;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CPosteSSPI;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\Brancardage\CBrancardage;
use Ox\Mediboard\Brancardage\Utilities\CBrancardageConditionMakerUtility;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\CompteRendu\CWkhtmlToPDF;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraph;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\Prescription\CAdministrationDM;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CDailyCheckList;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\System\CAlert;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Mediboard\Web100T\CWeb100TSejour;

/**
 * Opération
 */
class COperation extends CCodable implements IPatientRelated, IIndexableObject, ImportableInterface, IGroupRelated
{
    /** @var string */
    public const RESOURCE_TYPE = 'intervention';

    /** @var string */
    public const FIELDSET_TIMING = 'timing';
    /** @var string */
    public const FIELDSET_EXAMEN = 'examen';
    /** @var string */
    public const FIELDSET_TARIF = 'tarif';

    /** @var string */
    public const RELATION_PATIENT = 'patient';
    /** @var string */
    public const RELATION_ALLERGIES = 'allergies';
    /** @var string */
    public const RELATION_PRATICIEN = 'praticien';
    /** @var string */
    public const RELATION_SEJOUR = 'sejour';
    /** @var string */
    public const RELATION_ANESTH = 'anesth';

    // static lists
    /**
     * @var array
     */
    private static $fields_etiq = ["ANESTH", "LIBELLE", "DATE", "COTE"];

    /**
     * Liste des timings, ATTENTION : à mettre à jour si un timing est ajouté
     *
     * @var array
     */
    public static $timings = [
        "debut_prepa_preop",
        "fin_prepa_preop",
        "entree_bloc",
        "entree_salle",
        "sortie_salle",
        "preparation_op",
        "pose_garrot",
        "retrait_garrot",
        "prep_cutanee",
        "debut_op",
        "fin_op",
        "remise_chir",
        "tto",
        "entree_reveil",
        "sortie_reveil_possible",
        "sortie_reveil_reel",
        "sortie_sans_sspi",
        "induction_debut",
        "induction_fin",
        "suture_fin",
        "incision",
        "debut_alr",
        "fin_alr",
        "debut_ag",
        "fin_ag",
        "remise_anesth",
        "pec_anesth",
        "fin_pec_anesth",
        "patient_stable",
    ];

    // DB Table key
    public $operation_id;

    // Clôture des actes
    public $cloture_activite_1;
    public $cloture_activite_4;

    // DB References
    public $sejour_id;
    public $chir_id;
    public $chir_2_id;
    public $chir_3_id;
    public $chir_4_id;
    public $anesth_id;
    public $sortie_locker_id;
    public $plageop_id;
    public $salle_id;
    public $sspi_id;
    public $poste_sspi_id;
    public $poste_preop_id;
    public $examen_operation_id;
    public $graph_pack_id;
    public $graph_pack_locked_user_id;
    public $datetime_lock_graph_perop;
    public $graph_pack_sspi_id;
    public $graph_pack_sspi_locked_user_id;
    public $datetime_lock_graph_sspi;
    public $graph_pack_preop_id;
    public $graph_pack_preop_locked_user_id;
    public $datetime_lock_graph_preop;
    public $protocole_id;

    // DB Fields S@nté.com communication
    public $code_uf;
    public $libelle_uf;

    // DB Fields
    public $date;
    public $libelle;
    public $cote;
    public $temp_operation;
    public $pause;
    public $time_operation;
    public $exam_extempo;
    public $examen;
    public $materiel;
    public $materiel_pharma;
    public $exam_per_op;
    public $commande_mat;
    public $commande_mat_pharma;
    public $info;
    public $type_anesth;
    public $rques;
    public $rques_personnel;
    public $rank;
    public $rank_voulu;
    public $anapath;
    public $flacons_anapath;
    public $labo_anapath_id;
    public $description_anapath;
    public $labo;
    public $flacons_bacterio;
    public $labo_bacterio_id;
    public $description_bacterio;
    public $rayons_x;
    public $ampli_id;
    public $temps_rayons_x;
    public $dose_rayons_x;
    public $dose_recue_scopie;
    public $unite_rayons_x;
    public $dose_recue_graphie;
    public $pds;
    public $unite_pds;
    public $kerma;
    public $nombre_graphie;
    public $description_rayons_x;
    public $prothese;
    public $ASA;
    public $position_id;
    public $visitors;

    public $depassement;
    public $conventionne;
    public $forfait;
    public $fournitures;
    public $depassement_anesth;
    public $commentaire_depassement_anesth;
    public $reglement_dh_chir;
    public $reglement_dh_anesth;

    public $annulee;

    public $horaire_voulu;
    public $_horaire_voulu;
    public $duree_uscpo;
    public $passage_uscpo;
    public $duree_preop;
    public $presence_preop;
    public $presence_postop;
    public $envoi_mail;
    public $duree_bio_nettoyage;
    public $duree_postop;
    public $numero_panier;

    // Timings enregistrés
    public $debut_prepa_preop;
    public $fin_prepa_preop;
    public $entree_bloc;
    public $entree_salle;
    public $preparation_op;
    public $pose_garrot;
    public $prep_cutanee;
    public $debut_op;
    public $fin_op;
    public $retrait_garrot;
    public $sortie_salle;
    public $remise_chir;
    public $tto;
    public $entree_reveil;
    public $sortie_reveil_possible;
    public $sortie_reveil_reel;
    public $sortie_sans_sspi;
    public $induction_debut;
    public $induction_fin;
    public $suture_fin;
    public $urgence;
    public $incision;
    public $validation_timing;
    public $materiel_sterilise;
    public $debut_alr;
    public $fin_alr;
    public $debut_ag;
    public $fin_ag;
    public $pec_anesth;
    public $fin_pec_anesth;
    public $remise_anesth;
    public $patient_stable;
    public $consommation_user_id;
    public $consommation_datetime;

    /** @var string Cleaning start time */
    public $cleaning_start;

    /** @var string Cleaning ed time */
    public $cleaning_end;

    /** @var string Installation start time */
    public $installation_start;

    /** @var string Installation end time */
    public $installation_end;

    // Vérification du côté
    public $cote_admission;
    public $cote_consult_anesth;
    public $cote_hospi;
    public $cote_bloc;

    // Visite préanesthésique
    public $date_visite_anesth;
    public $time_visite_anesth;
    public $prat_visite_anesth_id;
    public $rques_visite_anesth;
    public $autorisation_anesth;

    // Form fields
    public $_time_op;
    public $_time_urgence;
    public $_lu_type_anesth;
    public $_codes_ccam   = [];
    public $_fin_prevue;
    public $_duree_interv;
    public $_duree_garrot;
    public $_duree_induction;
    public $_presence_salle;
    public $_duree_sspi;
    public $_deplacee;
    public $_compteur_jour;
    public $_protocole_prescription_anesth_id;
    public $_protocole_prescription_chir_id;
    public $_move;
    public $_reorder_rank_voulu;
    public $_password_visite_anesth;
    public $_patient_id;
    public $_dmi_alert;
    public $_offset_uscpo = [];
    public $_width_uscpo  = [];
    public $_width        = [];
    public $_debut_offset = [];
    public $_fin_offset   = [];
    public $_place_after_interv_id;
    public $_heure_us;
    public $_types_ressources_ids;
    public $_is_urgence;
    public $_status;
    public $_count_lines_post_op;
    public $_pat_next;
    public $_completeness_color_form;
    public $_alert_created;
    public $_protocoles_op_ids;

    /** @var string The full CCAM codes for applying the codage of the chir from a protocole */
    public $_codage_ccam_chir;

    /** @var string The full CCAM codes for applying the codage of the anesth from a protocole */
    public $_codage_ccam_anesth;

    /** @var string Cleaning time */
    public $_cleaning_time;

    /** @var string Installation time */
    public $_installation_time;

    // Behaviour fields
    public $_no_synchro_eai = false;
    /** @var bool */
    public $_sync_ecap = false;
    public $_modif_operation;

    public $_active_session;

    // Distant fields
    public $_datetime;
    public $_datetime_reel;
    public $_datetime_reel_fin;
    public $_datetime_best;

    /** @var CAffectation */
    public $_ref_affectation;
    /** @var CBesoinRessource[] */
    public $_ref_besoins = [];
    /** @var CMediusers */
    public $_ref_chir;
    /** @var CMediusers */
    public $_ref_chir_2;
    /** @var CMediusers */
    public $_ref_chir_3;
    /** @var CMediusers */
    public $_ref_chir_4;
    /** @var CPosteSSPI */
    public $_ref_poste;
    /** @var CPosteSSPI */
    public $_ref_poste_preop;
    /** @var CPlageOp */
    public $_ref_plageop;
    /** @var CSalle */
    public $_ref_salle;
    /** @var CMediusers */
    public $_ref_anesth;
    /** @var CTypeAnesth */
    public $_ref_type_anesth;
    /** @var CConsultAnesth */
    public $_ref_consult_anesth;
    /** @var CMediusers */
    public $_ref_anesth_visite;
    /** @var CConsultation */
    public $_ref_consult_chir;
    /** @var CActeCCAM[] */
    public $_ref_actes_ccam = [];
    /** @var CEchangeHprim */
    public $_ref_echange_hprim;
    /** @var CAnesthPerop[] */
    public $_ref_anesth_perops;
    /** @var CNaissance[] */
    public $_ref_naissances;
    /** @var CPoseDispositifVasculaire[] */
    public $_ref_poses_disp_vasc = [];
    /** @var  CBloodSalvage */
    public $_ref_blood_salvage;
    /** @var CBrancardage */
    public $_ref_brancardage;
    /** @var CBrancardage */
    public $_ref_last_brancardage;
    /** @var CBrancardage */
    public $_ref_current_brancardage;
    /** @var CBrancardage[] */
    public $_ref_brancardages;
    /** @var CMediusers */
    public $_ref_sortie_locker;
    /** @var CSupervisionGraphPack */
    public $_ref_graph_pack;
    /** @var CSupervisionGraphPack */
    public $_ref_graph_pack_sspi;
    /** @var CSupervisionGraphPack */
    public $_ref_graph_pack_preop;
    /** @var COperationWorkflow */
    public $_ref_workflow;
    /** @var CLiaisonLibelleInterv[] */
    public $_ref_liaison_libelles = [];
    /** @var CCommandeMaterielOp[] */
    public $_ref_commande_mat;
    /** @var self */
    public $_ref_prev_op;
    /** @var self */
    public $_ref_next_op;
    /** @var CSSPI */
    public $_ref_sspi;
    /** @var CPosition */
    public $_ref_position;
    /** @var CMaterielOperatoire[] */
    public $_refs_materiels_operatoires = [];
    /** @var CMaterielOperatoire[] */
    public $_refs_materiels_operatoires_dm = [];
    /** @var CMaterielOperatoire[] */
    public $_refs_materiels_operatoires_dm_sterilisables = [];
    /** @var CMaterielOperatoire[] */
    public $_refs_materiels_operatoires_produit = [];
    /** @var CProtocoleOperatoire[] */
    public $_ref_protocoles_operatoires = [];
    /** @var CLaboratoireAnapath */
    public $_ref_labo_anapath;
    /** @var CLaboratoireBacterio */
    public $_ref_labo_bacterio;
    /** @var CAmpli */
    public $_ref_ampli;
    /** @var CProtocole */
    public $_ref_protocole;

    // Filter Fields
    public $_date_min;
    public $_date_max;
    public $_plage;
    public $_datetime_min;
    public $_datetime_max;
    public $_service;
    public $_ranking;
    public $_cotation;
    public $_specialite;
    public $_scodes_ccam;
    public $_prat_id;
    public $_func_id;
    public $_bloc_id;
    public $_salle_id;
    public $_ccam_libelle;
    public $_planning_perso;
    public $_libelle_interv;
    public $_libelle_sejour;
    public $_entree_sejour;
    public $_rques_interv;
    public $_ref_chirs           = [];
    public $_current_move;
    public $_count_anesth_perops = 0;
    public $_prepa_dt_min;
    public $_prepa_dt_max;
    public $_prepa_chir_id;
    public $_prepa_spec_id;
    public $_prepa_bloc_id;
    public $_prepa_salle_id;
    public $_prepa_urgence;
    public $_prepa_libelle;
    public $_prepa_libelle_prot;
    public $_prepa_order_col;
    public $_prepa_order_way;
    public $_prepa_type_intervention;
    public $_prepa_period;
    public $_status_panier;
    public $_color_panier;
    public $_legend_panier;
    public $_filter_panier;
    public $_ref_prepa_chir;
    public $_ref_prepa_spec;
    public $_libelle_comp;

    /** @var COperationGarrot[] Garrots posés */
    public $_ref_garrots;

    public $_ext_cabinet_id;
    public $_ext_patient_id;

    /**
     * COperation constructor. Initializes $this->_locked
     *
     * @inheritdoc
     */
    function __construct()
    {
        parent::__construct();

        static $locked = null;
        if ($locked === null) {
            $locked = CAppUI::conf("planningOp COperation locked");
        }
        $this->_locked = $locked;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->table       = 'operations';
        $spec->key         = 'operation_id';
        $spec->measureable = true;

        $references = [
            'reference1' => ['CSejour', 'sejour_id'],
            'reference2' => ['CPatient', 'sejour_id.patient_id'],
        ];

        $references_w_auto         = $references;
        $references_w_auto['auto'] = true;

        $spec->events = [
            "dhe"                    => $references,
            "checklist"              => $references,
            "preop"                  => $references,
            "perop"                  => $references,
            "liaison"                => $references,
            "entree_salle"           => $references,
            "entree_reveil"          => $references,
            "sortie_reveil"          => $references,
            "debut_intervention"     => $references,
            "fin_intervention"       => $references_w_auto,
            "sortie_sans_sspi_auto"  => $references_w_auto,
            "timed_data"             => $references,
            "ambu_checklist_pre_op"  => $references,
            "ambu_checklist_bloc"    => $references,
            "ambu_checklist_sspi"    => $references,
            "ambu_checklist_post_op" => $references,
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $hideDate = CAppUI::gconf('dPsalleOp COperation hide_timing_date') ? ' hideDate' : '';

        $protocole                                = new CProtocole();
        $props                                    = parent::getProps();
        $props["sejour_id"]                       = "ref notNull class|CSejour back|operations fieldset|default";
        $props["chir_id"]                         = "ref notNull class|CMediusers seekable back|operations_chir fieldset|default";
        $props["chir_2_id"]                       = "ref class|CMediusers seekable back|operations_chir2";
        $props["chir_3_id"]                       = "ref class|CMediusers seekable back|operations_chir3";
        $props["chir_4_id"]                       = "ref class|CMediusers seekable back|operations_chir4";
        $props["anesth_id"]                       = "ref class|CMediusers back|operations_anesth fieldset|extra";
        $props["sortie_locker_id"]                = "ref class|CMediusers back|ops_sortie_validee";
        $props["plageop_id"]                      = "ref class|CPlageOp seekable show|0 back|operations";
        $props["pause"]                           = "time show|0";
        $props["salle_id"]                        = "ref class|CSalle back|operations";
        $props["sspi_id"]                         = "ref class|CSSPI back|operations";
        $props["poste_sspi_id"]                   = "ref class|CPosteSSPI back|operations";
        $props["poste_preop_id"]                  = "ref class|CPosteSSPI back|operations_preop";
        $props["examen_operation_id"]             = "ref class|CExamenOperation back|operation";
        $props["graph_pack_id"]                   = "ref class|CSupervisionGraphPack back|operations";
        $props["graph_pack_locked_user_id"]       = "ref class|CMediusers back|graph_packs_locked";
        $props["datetime_lock_graph_perop"]       = "dateTime";
        $props["graph_pack_sspi_id"]              = "ref class|CSupervisionGraphPack back|operations_sspi";
        $props["graph_pack_sspi_locked_user_id"]  = "ref class|CMediusers back|graph_packs_sspi_locked";
        $props["datetime_lock_graph_sspi"]        = "dateTime";
        $props["graph_pack_preop_id"]             = "ref class|CSupervisionGraphPack back|operations_preop";
        $props["graph_pack_preop_locked_user_id"] = "ref class|CMediusers back|graph_pack_preop_locked";
        $props["datetime_lock_graph_preop"]       = "dateTime";
        $props["consult_related_id"]              = "ref class|CConsultation show|0 back|intervs_liees";
        $props["protocole_id"]                    = "ref class|CProtocole nullify back|operations fieldset|default";
        $props["date"]                            = "date fieldset|default";
        $props["time_operation"]                  = "time show|0 fieldset|default";
        $props["temp_operation"]                  = "time show|0 fieldset|default";
        $props["code_uf"]                         = "str length|3";
        $props["libelle_uf"]                      = "str maxLength|35";
        $props["libelle"]                         = "str seekable autocomplete dependsOn|chir_id fieldset|default";
        $props["cote"]                            = $protocole->_props["cote"] . " notNull default|inconnu fieldset|default";
        $props["examen"]                          = "text helped fieldset|examen";
        $props["exam_extempo"]                    = "bool fieldset|examen";
        $props["materiel"]                        = "text helped seekable show|0 fieldset|default";
        $props["materiel_pharma"]                 = "text helped seekable show|0";
        $props["exam_per_op"]                     = "text helped seekable show|0 fieldset|examen";
        $props["commande_mat"]                    = "bool show|0";
        $props["commande_mat_pharma"]             = "bool show|0";
        $props["info"]                            = "bool";
        $props["type_anesth"]                     = "ref class|CTypeAnesth back|operations fieldset|extra";
        $props["rques"]                           = "text helped fieldset|default";
        $props["rques_personnel"]                 = "text";
        $props["rank"]                            = "num max|255 show|0";
        $props["rank_voulu"]                      = "num max|255 show|0";
        $props["depassement"]                     = "currency min|0 confidential show|0 default|0 fieldset|tarif";
        $props["conventionne"]                    = "bool default|1 fieldset|tarif";
        $props["forfait"]                         = "currency min|0 confidential show|0 fieldset|tarif";
        $props["fournitures"]                     = "currency min|0 confidential show|0 fieldset|tarif";
        $props["depassement_anesth"]              = "currency min|0 confidential show|0 default|0 fieldset|tarif";
        $props["commentaire_depassement_anesth"]  = "text fieldset|tarif";
        $props["annulee"]                         = "bool show|0";
        $props['reglement_dh_chir']               = 'enum list|non_regle|cb|cheque|espece|virement default|non_regle show|0 fieldset|tarif';
        $props['reglement_dh_anesth']             = 'enum list|non_regle|cb|cheque|espece|virement default|non_regle show|0 fieldset|tarif';

        $props["anapath"]               = "enum list|1|0|? default|? show|0";
        $props["flacons_anapath"]       = "num max|255 show|0";
        $props["labo_anapath_id"]       = "ref class|CLaboratoireAnapath back|operations";
        $props["description_anapath"]   = "text helped";
        $props["labo"]                  = "enum list|1|0|? default|? show|0";
        $props["flacons_bacterio"]      = "num max|255 show|0";
        $props["labo_bacterio_id"]      = "ref class|CLaboratoireBacterio back|operations";
        $props["description_bacterio"]  = "text helped";
        $props["rayons_x"]              = "enum list|1|0|? default|? show|0";
        $props["ampli_id"]              = "ref class|CAmpli back|operations";
        $props["temps_rayons_x"]        = "time";
        $props["dose_rayons_x"]         = "float";
        $props['dose_recue_scopie']     = 'float';
        $props["unite_rayons_x"]        = "enum list|mA|mGy|cGy.cm_carre|mGy.m_carre|mGy.cm_carre|Gy.cm_carre default|mA";
        $props['pds']                   = 'float';
        $props["unite_pds"]             = "enum list|uGycm_carre|mGycm_carre|Gycm_carre|cGycm_carre default|mGycm_carre";
        $props['kerma']                 = 'float';
        $props['dose_recue_graphie']    = 'float';
        $props['nombre_graphie']        = 'num min|0';
        $props["description_rayons_x"]  = "text helped";
        $props["prothese"]              = "enum list|1|0|? default|? show|0";
        $props['position_id']           = "ref class|CPosition autocomplete|libelle back|positions_operation";
        $props["ASA"]                   = "enum list|1|2|3|4|5|6";
        $props["horaire_voulu"]         = "time show|0 fieldset|timing";
        $props["presence_preop"]        = "time show|0 fieldset|timing";
        $props["presence_postop"]       = "time show|0 fieldset|timing";
        $props["envoi_mail"]            = "dateTime show|0";
        $props['urgence']               = 'bool default|0 fieldset|default';
        $props["numero_panier"]         = "str";
        $props["consommation_user_id"]  = "ref class|CMediusers back|operations_consommation_user";
        $props["consommation_datetime"] = "dateTime";

        // Horodatage
        $props["debut_prepa_preop"]      = "dateTime show|0 refDate|date$hideDate";
        $props["fin_prepa_preop"]        = "dateTime show|0 refDate|date$hideDate";
        $props["entree_salle"]           = "dateTime show|0 refDate|date$hideDate";
        $props["preparation_op"]         = "dateTime show|0 refDate|date$hideDate";
        $props["sortie_salle"]           = "dateTime show|0 refDate|date$hideDate";
        $props["remise_chir"]            = "dateTime show|0 refDate|date$hideDate";
        $props["tto"]                    = "dateTime show|0 refDate|date$hideDate";
        $props["pose_garrot"]            = "dateTime show|0 refDate|date$hideDate";
        $props["prep_cutanee"]           = "dateTime show|0 refDate|date$hideDate";
        $props["debut_op"]               = "dateTime show|0 refDate|date$hideDate";
        $props["fin_op"]                 = "dateTime show|0 refDate|date$hideDate";
        $props["retrait_garrot"]         = "dateTime show|0 refDate|date$hideDate";
        $props["entree_reveil"]          = "dateTime show|0 refDate|date$hideDate";
        $props["sortie_reveil_possible"] = "dateTime show|0 refDate|date$hideDate";
        $props["sortie_reveil_reel"]     = "dateTime show|0 refDate|date$hideDate";
        $props["sortie_sans_sspi"]       = "dateTime show|0 refDate|date$hideDate";
        $props["induction_debut"]        = "dateTime show|0 refDate|date$hideDate";
        $props["induction_fin"]          = "dateTime show|0 refDate|date$hideDate";
        $props["suture_fin"]             = "dateTime show|0 refDate|date$hideDate";
        $props["entree_bloc"]            = "dateTime show|0 refDate|date$hideDate";
        $props["cleaning_start"]         = "dateTime show|0 refDate|date$hideDate";
        $props["cleaning_end"]           = "dateTime show|0 refDate|date$hideDate";
        $props["installation_start"]     = "dateTime show|0 refDate|date$hideDate";
        $props["installation_end"]       = "dateTime show|0 refDate|date$hideDate";
        $props["incision"]               = "dateTime show|0 refDate|date$hideDate";
        $props["validation_timing"]      = "dateTime show|0 refDate|date$hideDate";
        $props["debut_alr"]              = "dateTime show|0 refDate|date$hideDate";
        $props["fin_alr"]                = "dateTime show|0 refDate|date$hideDate";
        $props["debut_ag"]               = "dateTime show|0 refDate|date$hideDate";
        $props["fin_ag"]                 = "dateTime show|0 refDate|date$hideDate";
        $props["pec_anesth"]             = "dateTime show|0 refDate|date$hideDate";
        $props["fin_pec_anesth"]         = "dateTime show|0 refDate|date$hideDate";
        $props["remise_anesth"]          = "dateTime show|0 refDate|date$hideDate";
        $props["patient_stable"]         = "dateTime show|0 refDate|date$hideDate";
        $props["duree_bio_nettoyage"]    = "time show|0 fieldset|timing";
        $props["duree_postop"]           = "time show|0";
        $props["materiel_sterilise"]     = "bool default|0";
        $props["visitors"]               = "text helped";

    // Clôture des actes
    $props["cloture_activite_1"] = "bool default|0";
    $props["cloture_activite_4"] = "bool default|0";

    $props["cote_admission"]      = $protocole->_props["cote"] . " show|0";
    $props["cote_consult_anesth"] = $protocole->_props["cote"] . " show|0";
    $props["cote_hospi"]          = $protocole->_props["cote"] . " show|0";
    $props["cote_bloc"]           = $protocole->_props["cote"] . " show|0";

    // Visite préanesthésique
    $props["date_visite_anesth"]    = "date";
    $props["time_visite_anesth"]    = "time";
    $props["prat_visite_anesth_id"] = "ref class|CMediusers back|visites_anesth";
    $props["rques_visite_anesth"]   = "text helped show|0";
    $props["autorisation_anesth"]   = "bool default|0";

    $props["facture"] = "bool default|0";

    // Max USCPO accélère les chargements, ne pas supprimer, au pire augmenter
    $props["duree_uscpo"] = "num min|0 max|10 default|0 fieldset|timing";

    if (CAppUI::conf("dPplanningOp COperation show_duree_uscpo") == 2) {
      $props["passage_uscpo"] = "bool notNull";
    }
    else {
      $props["passage_uscpo"] = "bool";
    }

    $props["duree_preop"]    = "time";
    $props["_horaire_voulu"] = "time";

    $props["_duree_interv"]      = "time duration";
    $props["_duree_garrot"]      = "time duration";
    $props["_duree_induction"]   = "time duration";
    $props["_presence_salle"]    = "time duration";
    $props["_duree_sspi"]        = "time duration";
    $props["_cleaning_time"]     = 'time duration';
    $props["_installation_time"] = 'time duration';

    $props["_pat_next"] = "dateTime show|0 refDate|date";

    $props["_date_min"] = "date";
    $props["_date_max"] = "date moreEquals|_date_min";
    $props["_plage"]    = "bool";

    $props["_datetime_min"] = "dateTime";
    $props["_datetime_max"] = "dateTime moreEquals|_datetime_min";

    $props["_ranking"]  = "enum list|ok|ko";
    $props["_cotation"] = "enum list|ok|ko";

    $props["_prat_id"]                = "ref class|CMediusers";
    $props["_func_id"]                = "ref class|CFunctions";
    $props["_patient_id"]             = "ref class|CPatient show|1";
    $props["_bloc_id"]                = "ref class|CBlocOperatoire";
    $props["_salle_id"]               = "ref class|CSalle";
    $props["_specialite"]             = "text";
    $props["_ccam_libelle"]           = "bool default|1";
    $props["_time_op"]                = "time";
    $props["_datetime"]               = "dateTime show";
    $props["_datetime_reel"]          = "dateTime";
    $props["_datetime_reel_fin"]      = "dateTime";
    $props["_fin_prevue"]             = "time";
    $props["_datetime_best"]          = "dateTime";
    $props["_move"]                   = "str";
    $props["_password_visite_anesth"] = "password notNull";
    $props["_heure_us"]               = "time";
    $props['_codage_ccam_chir']       = 'str';
    $props['_codage_ccam_anesth']     = 'str';
    $props['_status']                 = 'enum list|planned|ongoing|sspi|ended';
    $props["_filter_panier"]          = "enum list||ok|missing";
    $props['_codes_ccam']             = "str fieldset|default";
    $props["_libelle_interv"]         = "str fieldset|default";
    $props["_libelle_sejour"]         = "str fieldset|default";
    $props["_entree_sejour"]          = "dateTime fieldset|default";

    // Préparation des salles
    $props["_prepa_dt_min"]            = "dateTime";
    $props["_prepa_dt_max"]            = "dateTime";
    $props["_prepa_period"]            = "enum list|all_day|morning|afternoon";
    $props["_prepa_chir_id"]           = "ref class|CMediusers";
    $props["_prepa_spec_id"]           = "ref class|CFunctions";
    $props["_prepa_bloc_id"]           = "ref class|CBlocOperatoire";
    $props["_prepa_salle_id"]          = "ref class|CSalle";
    $props["_prepa_urgence"]           = "bool default|0";
    $props["_prepa_libelle"]           = "str";
    $props["_prepa_libelle_prot"]      = "str";
    $props["_prepa_order_col"]         = "str";
    $props["_prepa_order_way"]         = "str";
    $props["_prepa_type_intervention"] = "enum list|hors_plage|avec_plage|tous default|tous";

    // Tamm-SIH
    $props['_ext_cabinet_id'] = 'text';
    $props['_ext_patient_id'] = 'text';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function loadRelPatient() {
    return $this->loadRefPatient();
  }

  /**
   * @inheritdoc
   */
  public function getExecutantId(string $code_activite = null): ?int {
    if (is_null($code_activite)) {
        $code_activite = '1';
    }
    $this->loadRefChir();
    $this->loadRefPlageOp();

    $executant_id = ($code_activite == 4 ? $this->_ref_anesth->_id : $this->chir_id);

    $user = CMediusers::get();
    if (!($user->isProfessionnelDeSante() && CAppUI::pref('user_executant')) && $code_activite == 1) {
      $user = $this->_ref_chir;
    }
    elseif (!(CAppUI::pref('user_executant') && $user->isAnesth()) && $code_activite == 4) {
      $user = $this->_ref_anesth;
    }

    if ($user->loadRefRemplacant($this->getActeExecution())) {
      $user = $user->_ref_remplacant;
    }

    return $user->_id;
  }

  /**
   * @inheritdoc
   */
  public function getExtensionDocumentaire(?int $executant_id = 1): ?int {
    $extension_documentaire = null;

    if ($executant_id) {
        $codage_ccam = CCodageCCAM::get($this, $executant_id, 4, $this->date, false);
        $actes       = $codage_ccam->loadActesCCAM();

        foreach ($actes as $_acte) {
            if ($_acte->extension_documentaire) {
                $extension_documentaire = $_acte->extension_documentaire;
                break;
            }
        }
    }

    if (!$extension_documentaire) {
      /** @var CTypeAnesth $type_anesth */
      $type_anesth            = $this->loadRefTypeAnesth();
      $extension_documentaire = $type_anesth->ext_doc;
    }

    return $extension_documentaire;
  }

  /**
   * @inheritdoc
   */
  function getTemplateClasses() {
    $this->loadRefsFwd();

    $tab = array();

    // Stockage des objects liés à l'opération
    $tab['COperation'] = $this->_id;
    $tab['CSejour']    = $this->_ref_sejour->_id;
    $tab['CPatient']   = $this->_ref_sejour->_ref_patient->_id;

    $tab['CConsultation']  = 0;
    $tab['CConsultAnesth'] = 0;

    return $tab;
  }

  /**
   * @inheritdoc
   */
  public function check(): ?string {
    $msg = null;
    $this->completeField("chir_id", "plageop_id", "sejour_id");
    if (!$this->_id && !$this->chir_id) {
      $msg .= "Praticien non valide ";
    }

    // Bornes du séjour
    $sejour = $this->loadRefSejour();
    $this->loadRefPlageOp();

    if ($this->_check_bounds && !$this->_forwardRefMerging) {
      if ($this->plageop_id !== null && !$sejour->entree_reelle) {
        if ($msg_borns = $this->warningBounds()) {
          $msg .= $msg_borns;
        }
      }
    }

    // Vérification de la signature de l'anesthésiste pour la visite préanesthésique
    if ($this->fieldModified("prat_visite_anesth_id")
      && $this->prat_visite_anesth_id !== null
      && $this->prat_visite_anesth_id != CAppUI::$user->_id
    ) {
      $anesth = new CUser();
      $anesth->load($this->prat_visite_anesth_id);

      if (!CUser::checkPassword($anesth->user_username, $this->_password_visite_anesth)) {
        $msg .= "Mot de passe incorrect";
      }
    }

    //Ne pas permettre la saisie de l'entrée en salle si un patient s'y trouve déjà
    if ($this->plageop_id
      && $this->entree_salle
      && !$this->_old->entree_salle
      && CAppUI::gconf("dPsalleOp COperation no_entree_fermeture_salle_in_plage")
    ) {
      $this->completeField("salle_id");

      $where                            = array();
      $where["operations.operation_id"] = " <> '$this->_id'";
      $where["operations.plageop_id"]   = " = '$this->plageop_id'";
      $where["operations.salle_id"]     = " = '$this->salle_id'";
      $where["operations.annulee"]      = " = '0'";
      $where[]                          = "operations.entree_salle IS NOT NULL";
      $where[]                          = "operations.sortie_salle IS NULL";
      $operation                        = new COperation();
      $result                           = $operation->countList($where, "operations.operation_id");
      if ($result) {
        $msg .= "COperation.no_entree_salle_in_plage";
      }
    }

    //Ne pas permettre la saisie de la sortie de salle si les 3 checklists de l'intervention n'ont pas été validées
      if ($this->_id && $this->sortie_salle && !$this->_old->sortie_salle
          && CAppUI::gconf("dPsalleOp CDailyCheckList presence_for_sortie_salle")
          && (CDailyCheckList::countNumberCheckListForType($this->getCheckListType()) === 0
              || CDailyCheckList::getCountChecklistInterv($this) <
              CDailyCheckList::countNumberCheckListForType($this->getCheckListType()))
      ) {
          $msg .= "COperation.checklist_no_presence_for_sortie_salle";
      }

    return $msg . parent::check();
  }

  /**
   * Vérifie si l'intervention est dans les bornes du séjour
   *
   * @return string|null
   * @throws Exception
   */
  function warningBounds() {
    $date   = $this->date;
    $entree = CMbDT::date($this->_ref_sejour->entree_prevue);
    $sortie = CMbDT::date($this->_ref_sejour->sortie_prevue);

    if (!CMbRange::in($date, $entree, $sortie)) {
      return CAppUI::tr(
        "COperation-Out of borns",
        CMbDT::transform(null, $date, CAppUI::conf('date')),
        CMbDT::transform(null, $entree, CAppUI::conf("datetime")),
        CMbDT::transform(null, $sortie, CAppUI::conf("datetime"))
      );
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function delete() {
    $msg = parent::delete();
    $this->loadRefPlageOp();
    $this->_ref_plageop->reorderOp();

    return $msg;
  }

    /**
     * @inheritdoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        $miners = $this->loadBackRefs("workflow");
        $this->loadRefsCodagesCCAM();

        /** @var COperation $_object */
        foreach ($objects as $_object) {
            /* Removes the operation miners as they prevent merging operations */
            $miners = array_merge($miners, $_object->loadBackRefs("workflow"));

            /* Delete the CCodageCCAM that share the same user_id because of a SQL unique constraint that prevent the merge */
            $_object->loadRefsCodagesCCAM();
            foreach ($_object->_ref_codages_ccam as $user_id => $_activities) {
                if (array_key_exists($user_id, $this->_ref_codages_ccam)) {
                    foreach ($_activities as $_codage) {
                        $_codage->delete();
                    }
                }
            }
        }

        foreach ($miners as $_miner) {
            $_miner->delete();
        }

        parent::merge($objects, $fast, $merge_log);
    }

  /**
   * @inheritdoc
   */
  public function updateFormFields(): void {
    parent::updateFormFields();
    $this->_time_op      = $this->temp_operation;
    $this->_time_urgence = $this->time_operation;

    $this->_fin_prevue = CMbDT::addTime($this->time_operation, $this->temp_operation);

    if ($this->debut_op && $this->fin_op && $this->fin_op > $this->debut_op) {
      $this->_duree_interv = CMbDT::durationTime($this->debut_op, $this->fin_op);
    }
    if ($this->pose_garrot && $this->retrait_garrot && $this->retrait_garrot > $this->pose_garrot) {
      $this->_duree_garrot = CMbDT::durationTime($this->pose_garrot, $this->retrait_garrot);
    }
    if ($this->induction_debut && $this->induction_fin && $this->induction_fin > $this->induction_debut) {
      $this->_duree_induction = CMbDT::durationTime($this->induction_debut, $this->induction_fin);
    }
    if ($this->entree_salle && $this->sortie_salle && $this->sortie_salle > $this->entree_salle) {
      $this->_presence_salle = CMbDT::durationTime($this->entree_salle, $this->sortie_salle);
    }
    if ($this->entree_reveil && $this->sortie_reveil_reel && $this->sortie_reveil_reel > $this->entree_reveil) {
      $this->_duree_sspi = CMbDT::durationTime($this->entree_reveil, $this->sortie_reveil_reel);
    }

    if ($this->cleaning_start && $this->cleaning_end && $this->cleaning_end > $this->cleaning_start) {
      $this->_cleaning_time = CMbDT::durationTime($this->cleaning_start, $this->cleaning_end);
    }

    if ($this->installation_start && $this->installation_end && $this->installation_end > $this->installation_start) {
      $this->_installation_time = CMbDT::durationTime($this->installation_start, $this->installation_end);
    }

    if (!$this->debut_op) {
      $this->_status = 'planned';
    }
    elseif ($this->debut_op && !$this->fin_op) {
      $this->_status = 'ongoing';
    }
    elseif ($this->entree_reveil && !$this->sortie_reveil_reel) {
      $this->_status = 'sspi';
    }
    else {
      $this->_status = 'ended';
    }

    $this->_acte_depassement        = $this->depassement;
    $this->_acte_depassement_anesth = $this->depassement_anesth;

    $this->updateView();
    $this->updateDatetimes();
  }

  /**
   * @inheritdoc
   */
  public function updatePlainFields(): void {
    if (is_array($this->_codes_ccam) && count($this->_codes_ccam)) {
      $this->updateCCAMPlainField();
    }

    if ($this->codes_ccam) {
      $this->codes_ccam = strtoupper($this->codes_ccam);
      $codes_ccam       = explode("|", $this->codes_ccam);
      $XPosition        = true;
      // @TODO: change it to use removeValue
      while ($XPosition !== false) {
        $XPosition = array_search("-", $codes_ccam);
        if ($XPosition !== false) {
          array_splice($codes_ccam, $XPosition, 1);
        }
      }
      $this->codes_ccam = implode("|", $codes_ccam);
    }
    if ($this->_time_op !== null) {
      $this->temp_operation = $this->_time_op;
    }
    if ($this->_time_urgence !== null) {
      $this->time_operation = $this->_time_urgence;
    }
    elseif ($this->_horaire_voulu) {
      $this->horaire_voulu = $this->_horaire_voulu;
    }

    $this->completeField("rank", "plageop_id");

    if ($this->_move || (!$this->_current_move && $this->fieldModified("annulee"))) {
      $this->_current_move = true;

      $seconde_plage = new CPlageOp();
      if (CAppUI::gconf("dPplanningOp COperation multi_salle_op") && $this->_ref_plageop->chir_id) {
        $seconde_plage = CPlageOp::findSecondePlageChir($this->_ref_plageop, $new_time);
      }

      // On simule un retrait de l'intervention si elle est annulée
      if ($seconde_plage->_id && $this->fieldModified("annulee") && $this->annulee && $this->rank) {
        $this->_move = "out";
      }

      if ($seconde_plage->_id && in_array($this->_move, array("before", "after", "out", "last"))) {
        $this->completeField("time_operation", "temp_operation");
        switch ($this->_move) {
          case "before":
            $prev_op       = CPlageOp::findPrevOp($this, $this->_ref_plageop, $seconde_plage, $this->time_operation);
            $op_rank       = $this->rank;
            $op_plageop_id = $this->plageop_id;

            $this->plageop_id     = $prev_op->plageop_id;
            $this->time_operation = $this->_time_urgence = $prev_op->time_operation;
            $this->rank           = $prev_op->rank;
            $this->_move          = null; // Vidage obligatoire du form field sinon l'appel au store fera repasser dans ce if
            $this->store(false);

            $prev_op->plageop_id     = $op_plageop_id;
            $prev_op->rank           = $op_rank;
            $prev_op->time_operation = $prev_op->_time_urgence = CMbDT::addTime($this->temp_operation, $this->time_operation);
            $prev_op->store(false);
            break;
          case "after":
            $next_op = CPlageOp::findNextOp($this, $this->_ref_plageop, $seconde_plage, $this->time_operation);

            $next_op_rank       = $next_op->rank;
            $next_op_plageop_id = $next_op->plageop_id;

            $next_op->plageop_id     = $this->plageop_id;
            $next_op->time_operation = $next_op->_time_urgence = $this->time_operation;
            $next_op->rank           = $this->rank;
            $next_op->store(false);

            $this->plageop_id     = $next_op_plageop_id;
            $this->rank           = $next_op_rank;
            $this->time_operation = $this->_time_urgence = CMbDT::addTime($next_op->temp_operation, $next_op->time_operation);

            // Pas d'appel au store, il sera fait dans la suite du traitement
            break;
          case "out":
            // Sauvegarde des informations de l'intervention qui est retirée de l'ordonnancement
            $temp_op             = $this;
            $temp_time_operation = $this->time_operation;

            $temp_temp_operation = "00:00:00";
            $temp_rank           = $this->rank;
            $temp_plageop_id     = $this->plageop_id;

            $this->rank                = 0;
            $this->time_operation      = '00:00:00';
            $this->pause               = '00:00:00';
            $this->duree_bio_nettoyage = '00:00:00';
            $this->duree_postop        = '00:00:00';
            $this->_move               = null;
            $this->store(false);

            // Parcours des opérations suivantes pour décaler le rang et l'heure
            while ($next_op = CPlageOp::findNextOp($temp_op, $temp_op->_ref_plageop, $seconde_plage, $temp_time_operation)) {
              if (!$next_op->_id) {
                break;
              }

              $_rank       = $next_op->rank;
              $_plageop_id = $next_op->plageop_id;

              $next_op->time_operation = CMbDT::addTime($temp_temp_operation, $temp_time_operation);
              $next_op->_time_urgence  = $next_op->time_operation;
              $next_op->rank           = $temp_rank;
              $next_op->plageop_id     = $temp_plageop_id;
              $next_op->store(false);

              $temp_op = $next_op;

              $temp_time_operation = $next_op->time_operation;
              $temp_temp_operation = $next_op->temp_operation;
              $temp_rank           = $_rank;
              $temp_plageop_id     = $_plageop_id;

              $seconde_plage = CPlageOp::findSecondePlageChir($next_op->loadRefPlageOp(), $new_time);
            }
            break;
          case "last":
            $this->_ref_plageop->loadRefsOperations(true, "rank, time_operation, rank_voulu, horaire_voulu", true, true);

            // Si changement de plage, le rang doit être initialisé à 1
            $this->rank = 1;

            $prev_op              = CPlageOp::findPrevOp($this, $this->_ref_plageop, $seconde_plage, $new_time, false);
            $this->time_operation = $this->_time_urgence = $prev_op->_id ? CMbDT::addTime($prev_op->temp_operation, $prev_op->time_operation) : $this->_ref_plageop->debut;

            $op             = new COperation();
            $op->plageop_id = $this->plageop_id;
            if ($op->loadMatchingObject('rank DESC')) {
              $this->rank = $op->rank + 1;
            }

            $this->_ref_plageop->_ref_operations[$this->_id] = $this;

            CMbArray::pluckSort($this->_ref_plageop->_ref_operations, SORT_ASC, 'rank');

            break;
          default:
        }
      }
      else {
        $op             = new COperation();
        $op->plageop_id = $this->plageop_id;

        switch ($this->_move) {
          case "before":
            $op->rank = $this->rank - 1;
            if ($op->loadMatchingObject()) {
              $op->rank = $this->rank;
              $op->store(false);
              $this->rank--;
            }
            break;

          case "after":
            $op->rank = $this->rank + 1;
            if ($op->loadMatchingObject()) {
              $op->rank = $this->rank;
              $op->store(false);
              $this->rank++;
            }
            break;

          case "out":
            $this->rank                = 0;
            $this->time_operation      = '00:00:00';
            $this->pause               = '00:00:00';
            $this->duree_bio_nettoyage = '00:00:00';
            $this->duree_postop        = '00:00:00';
            break;

          case "last":
            // Si changement de plage, le rang doit être initialisé à 1
            $this->rank = 1;
            if ($op->loadMatchingObject('rank DESC')) {
              $this->rank = $op->rank + 1;
            }
            break;

          case "toggle":
            /** @var COperation $old */
            $old = $this->loadOldObject();

            $plage    = $this->loadRefPlageOp();
            $new_time = $plage->debut;

            $time_operation = null;

            $operations = $this->_ref_plageop->loadRefsOperations(true, "rank, time_operation, rank_voulu, horaire_voulu", true, true);
            unset($operations[$this->_id]);

            foreach ($operations as $_op) {
              // Rang supérieur (le rang des interventions qui suivent l'ancien rang est décrémenté)
              if ($this->rank > $old->rank) {
                // Si c'est la première intervention de la liste (rang 1), alors il n'y a rien à faire
                if ($_op->rank == 1) {
                  continue;
                }
                // Si le rang décrémenté est identique au rang de l'intervention que l'on déplace, alors il n'y a rien à faire
                if (($_op->rank - 1) == $this->rank) {
                  continue;
                }
                $_op->rank--;
              }
              // Rang inférieur (le rang des interventions qui précèdent l'ancien rang est incrémenté)
              else {
                // Si le rang incrémenté est identique au rang de l'intervention que l'on déplace, alors il n'y a rien à faire
                if (($_op->rank + 1) == $this->rank) {
                  continue;
                }
                $_op->rank++;
              }

              $_op->time_operation = $new_time;
              $_op->_time_urgence  = $_op->time_operation;
              $_op->store(false);

              $new_time = CMbDT::addTime($_op->temp_operation, $new_time);
              $new_time = CMbDT::addTime($plage->temps_inter_op, $new_time);
              $new_time = CMbDT::addTime($_op->pause, $new_time);
              $new_time = CMbDT::addTime($_op->duree_bio_nettoyage, $new_time);
              $new_time = CMbDT::addTime($_op->duree_postop, $new_time);

              // Si l'intervention courante a l'ancien rang de l'intervention qui est déplacée
              if ($_op->rank == $old->rank) {
                // Sauvegarde du début
                $time_operation = $new_time;
              }
            }

            $this->time_operation = $time_operation;
            $this->_time_urgence  = $this->time_operation;

            // On retrie par rang
            $operations[$this->_id] = $this;

            $keys = array_keys($operations);
            $order_rank = CMbArray::pluck($operations, "rank");
            array_multisort($order_rank, SORT_ASC, $operations, $keys);

            $operations = array_combine($keys, $operations);

            $this->_ref_plageop->_ref_operations = $operations;

            break;
          default:
        }
      }

      $this->_reorder_rank_voulu = true;
      $this->_move               = null;
    }
  }

  /**
   * Prepare the alert before storage
   *
   * @return string Alert comments if necessary, null if no alert
   */
  function prepareAlert() {
    // Création d'un alerte sur l'intervention
    $comments = null;
    /** @var self $old */
    $old = $this->_old;
    if ($old->rank || ($this->materiel && $this->commande_mat) || ($this->materiel_pharma && $this->commande_mat_pharma)) {
      $this->loadRefPlageOp();
      $old->loadRefPlageOp();

      if ($this->fieldModified("annulee", "1")) {
        // Alerte sur l'annulation d'une intervention
        $comments .= "L'intervention a été annulée pour le " . CMbDT::format($this->_datetime, CAppUI::conf("datetime")) . ".";
      }
      elseif (CMbDT::date(null, $this->_datetime) != CMbDT::date(null, $old->_datetime)) {
        // Alerte sur le déplacement d'une intervention
        $comments .= "L'intervention a été déplacée du " . CMbDT::format($old->_datetime, CAppUI::conf("date")) .
          " au " . CMbDT::format($this->_datetime, CAppUI::conf("date")) . ".";
      }
      elseif ($this->fieldModified("materiel") && $this->commande_mat) {
        // Alerte sur la commande de matériel
        $comments .= "Le materiel a été modifié \n - Ancienne valeur : " . $old->materiel .
          " \n - Nouvelle valeur : " . $this->materiel;
      }
      elseif ($this->fieldModified("materiel_pharma") && $this->commande_mat_pharma) {
        // Alerte sur la commande de matériel pour la pharmacie
        $comments .= "Le materiel pharmacie a été modifié \n - Ancienne valeur : " . $old->materiel .
          " \n - Nouvelle valeur : " . $this->materiel;
      }
      else {
        // Aucune alerte
        return null;
      }

      // Complément d'alerte
      if ($old->rank) {
        $comments .= "\nL'intervention avait été validée.";
      }
      if ($this->materiel && $this->commande_mat) {
        $comments .= "\nLe materiel avait été commandé.";
      }
      if ($this->materiel_pharma && $this->commande_mat_pharma) {
        $comments .= "\nLe materiel pharmacie avait été commandé.";
      }
    }

    return $comments;
  }

  /**
   * Create an alert if comments is not empty
   *
   * @param string  $comments Comments of the alert
   * @param boolean $update   Search an existing alert for updating
   * @param string  $tag      Tag of the alert
   *
   * @return string Store-like message
   */
  function createAlert($comments, $update = false, $tag = "mouvement_intervention") {
    if (!$comments || $this->_alert_created) {
      return null;
    }

    $this->_alert_created = true;

    $alerte = new CAlert();
    $alerte->setObject($this);
    $alerte->tag     = $tag;
    $alerte->handled = "0";
    $alerte->level   = "medium";
    if ($update) {
      $alerte->loadMatchingObject();
    }
    $alerte->comments = $comments;

    return $alerte->store();
  }

  /**
   * @inheritdoc
   */
  function store($reorder = true) {
    /** @var self $old */
    $old = $this->loadOldObject();

    $group = CGroups::loadCurrent();

    $this->completeField(
      "annulee",
      "rank",
      "codes_ccam",
      "sejour_id",
      "plageop_id",
      "chir_id",
      "materiel",
      "commande_mat",
      "commande_mat_pharma",
      "date",
      "entree_reveil",
      "sortie_reveil_possible",
      "sortie_reveil_reel",
      "duree_uscpo",
      "duree_preop",
      "presence_preop",
      "debut_prepa_preop",
      "duree_bio_nettoyage",
      "sortie_sans_sspi"
    );

    $sejour_id_modified = $this->fieldModified("sejour_id");

    if (!$this->entree_reveil && $this->sortie_reveil_possible && $this->fieldModified("sortie_reveil_possible")) {
      return "La sortie SSPI ne peut être saisie car l'entrée SSPI n'a pas été renseignée";
    }

    if ($this->fieldModified("temp_operation") && CAppUI::conf("dPplanningOp COperation only_admin_can_change_time_op", $group)
      && !($this->_ref_module->_can->admin || CMediusers::get()->isAdmin())) {
      return CAppUI::tr("config-dPplanningOp-COperation-only_admin_can_change_time_op");
    }

    // Pas de modification possible de l'entrée réveil si la sortie réveil est renseignée
    if ($this->sortie_reveil_reel && !$this->fieldModified("sortie_reveil_reel") && $this->fieldModified("entree_reveil")) {
      return "La sortie SSPI a déjà été renseignée, vous ne pouvez plus modifier l'entrée SSPI";
    }

    if ((!$this->_id && $this->duree_uscpo) || $this->fieldModified("duree_uscpo")) {
      $this->passage_uscpo = $this->duree_uscpo ? 1 : 0;
    }

    // Si on a une plage, la date est celle de la plage
    if ($this->plageop_id) {
      $plage      = $this->loadRefPlageOp();
      $this->date = $plage->date;
    }

    // Si on choisit une plage, on copie la salle
    if ($this->fieldValued("plageop_id")) {
      $plage          = $this->loadRefPlageOp();
      $this->salle_id = $plage->salle_id;
    }

    // Si on change pour une plage verrouillée (ou via un changement de salle qui est la même que la plage verrouillée) alors on refuse
    if (isset($plage) && $plage->verrouillage === "oui"
      && ($this->fieldModified("plageop_id") || ($this->fieldModified("salle_id") && $plage->salle_id === $this->salle_id))
    ) {
      return CAppUI::tr("COperation-alert_plage_verouillee");
    }

    // On empêche également le déplacement vers une salle bloquée
    if ($this->salle_id && $this->fieldModified('salle_id') && $this->_datetime) {
      $blocage = new CBlocage();
      $where   = array(
        "salle_id" => "= '$this->salle_id'"
      );

      $where[] = "'$this->_datetime' BETWEEN deb AND fin";

      if ($blocage->loadObject($where)) {
        return CAppUI::tr(
          "COperation-alert_salle_bloquee",
          CMbDT::transform($blocage->deb, null, CAppUI::conf("datetime")),
          CMbDT::transform($blocage->fin, null, CAppUI::conf("datetime"))
        );
      }
    }

    // Cas d'une plage que l'on quitte
    /** @var CPlageOp $old_plage */
    $old_plage = null;
    if ($this->fieldAltered("plageop_id") && $old->rank) {
      $old_plage = $old->loadRefPlageOp();
    }

    // Lors d'un changement de plage, on vide l'horaire prévu
    if ($this->fieldModified("plageop_id") && $this->plageop_id && !$this->_sync_ecap) {
      $this->time_operation = "";
    }

    $this->completeField("entree_salle", "sortie_salle", "sortie_sans_sspi", "time_operation", "libelle", "sejour_id");
    if ($this->sortie_salle && $this->fieldModified("sortie_salle")) {
      if (CAppUI::conf("dPplanningOp COperation adjust_debut_op", $group)) {
        $sortie_salle = $this->sortie_salle === "current" ? CMbDT::time() : CMbDT::time($this->sortie_salle);
        $time_start   = $this->entree_salle ? CMbDT::time($this->entree_salle) : $this->time_operation;

        if ($time_start < $sortie_salle) {
          $this->temp_operation = CMbDT::timeRelative($time_start, $sortie_salle);
        }
      }
    }

    if (($this->sortie_salle && (!$this->_id || $this->fieldModified("sortie_salle")))
        || ($this->sortie_sans_sspi && (!$this->_id || $this->fieldModified("sortie_sans_sspi")))) {
      // Génération d'un antécédent dans le dossier médical avec le motif de l'intervention
      if ($this->libelle) {
        $dossier_medical_id = CDossierMedical::dossierMedicalId($this->loadRefPatient()->_id, "CPatient");

        $antecedent                     = new CAntecedent();
        $antecedent->dossier_medical_id = $dossier_medical_id;
        $antecedent->owner_id           = $this->chir_id;
        $antecedent->date               = $this->date;
        $antecedent->type               = "chir";
        $antecedent->origin             = "autre";
        $antecedent->rques              = $this->loadRefChir()->_view . " - " . $this->libelle;

        if ($msg = $antecedent->store()) {
          return $msg;
        }
      }
    }

    if (!$this->sortie_salle && $this->sortie_sans_sspi) {
      $this->sortie_salle = $this->sortie_sans_sspi;
    }

    $comments                     = $this->prepareAlert();
    $place_after_interv_id        = $this->_place_after_interv_id;
    $this->_place_after_interv_id = null;

    // Liaison automatique entre le libellé de l'intervention et les protocoles DHE (si config activée)
    if ((!$this->_id || !$this->_old->libelle) && CAppUI::gconf('dPplanningOp CProtocole link_auto_label_protocole_dhe', $this->loadRefSejour()->group_id) && $this->libelle && !$this->protocole_id) {
      $libelle = $this->libelle;

      // Récupère le dernier mot de la chaine de caractère
      $last_word = substr($this->libelle, strrpos($this->libelle, ' ') + 1);

      // Supprime le dernier mot si le libelle contient une latéralité (GAUCHE, DROIT, DROITE, BILAT.) dans le cas D'ecap
      if (in_array($last_word, array("GAUCHE", "DROIT", "DROITE", "BILAT."))) {
        $pattern = $last_word == "BILAT." ? '/\W\w+\s*(\W*)\.$/' : '/\W\w+\s*(\W*)$/';

        $libelle = preg_replace($pattern, '$1', $this->libelle);
      }

      $where                      = array();
      $where["libelle"]           = " = '$libelle'";
      $where["protocole.actif"]   = " = '1'";
      $where["protocole.chir_id"] = " = '$this->chir_id'";
      $where["for_sejour"]        = " = '0'";

      $protocole  = new CProtocole();
      $protocoles = $protocole->loadList($where, null, 1);

      $same_protocole = null;

      if (count($protocoles)) {
        $same_protocole = reset($protocoles)->_id;
      }
      else {
        $chir = new CMediusers();
        $chir->load($this->chir_id);

        unset($where["protocole.chir_id"]);
        $where["protocole.function_id"] = " = '$chir->function_id'";

        $protocoles = $protocole->loadList($where, null, 1);

        if (count($protocoles)) {
          $same_protocole = reset($protocoles)->_id;
        }
      }

      $this->protocole_id = $same_protocole;
    }


    // Pré-remplissage de la durée préop et présence préop si c'est une nouvelle intervention
    if (!$this->_id && (!$this->duree_preop || !$this->presence_preop)) {
      $sejour = $this->loadRefSejour();

      $salle_id = $this->plageop_id ? $this->loadRefPlageOp()->loadRefSalle()->salle_id : $this->salle_id;

      $salle = new CSalle();
      $salle->load($salle_id);

      $bloc = $salle->loadRefBloc();

      if (!$this->duree_preop) {
        $patient = $sejour->loadRefPatient();

        $patient_majorite = $patient->_annees >= 18 ? "adulte" : "enfant";

        $this->duree_preop = "00:" . CAppUI::conf("dPplanningOp COperation duree_preop_$patient_majorite") . ":00";

        if ($sejour->type === "ambu" && $bloc->duree_preop_ambu) {
          $this->duree_preop = $bloc->duree_preop_ambu;
        }
      }
      if (!$this->presence_preop && $sejour->type === "ambu" && $bloc->presence_preop_ambu) {
        $this->presence_preop = $bloc->presence_preop_ambu;
      }
    }

    // On recopie la sortie réveil possible sur le réel si pas utilisée en config
    if (!CAppUI::conf("dPsalleOp COperation use_sortie_reveil_reel", $group) && !$this->sortie_sans_sspi) {
      $this->sortie_reveil_reel = $this->sortie_reveil_possible;
    }

    if (CAppUI::gconf("dPsalleOp timings use_exit_without_sspi") && $this->fieldModified("sortie_sans_sspi")) {
      $this->entree_reveil = $this->sortie_reveil_reel = $this->sortie_reveil_possible = $this->sortie_sans_sspi;
    }

    if ($this->_id
      && !$this->debut_prepa_preop
      && !$this->_old->poste_preop_id
      && $this->fieldModified("poste_preop_id")
      && CAppUI::conf("dPsalleOp COperation set_debutprepapreop_on_postepreop_choice", $group)) {
      $this->debut_prepa_preop = CMbDT::dateTime();
    }

    // Création d'une alerte si modification du libellé et/ou du côté
    if ($this->_id && ($this->fieldModified("libelle") || $this->fieldModified("cote"))) {
      $alerte = "";
      $date   = CMbDT::dateToLocale(CMbDT::date());

      if ($this->fieldModified("libelle")) {
        $alerte = "Le libellé a été modifié le $date\n" .
          "Ancienne valeur : " . $old->getFormattedValue("libelle") .
          "\nNouvelle valeur : " . $this->getFormattedValue("libelle");
      }
      $this->createAlert($alerte, true, "libelle");
      $alerte = "";
      if ($this->fieldModified("cote")) {
        $alerte = "Le côté a été modifié le $date : \n" .
          "Ancienne valeur : " . $old->getFormattedValue("cote") .
          "\nNouvelle valeur : " . $this->getFormattedValue("cote");
      }
      $this->createAlert($alerte, true, "cote");
    }

    if (!$this->_id && (!$this->duree_bio_nettoyage || $this->duree_bio_nettoyage == "00:00:00")) {
      $temps_op                         = CMbDT::format($this->_time_op, "%H") * 60 + CMbDT::format($this->_time_op, "%M");
      $duree_bio_nettoyage_inf_or_eq_30 = Cappui::gconf("dPplanningOp COperation duree_bio_nettoyage_inf_or_eq_30");
      $duree_bio_nettoyage_sup_30       = Cappui::gconf("dPplanningOp COperation duree_bio_nettoyage_sup_30");
      if ($temps_op <= 30 && $duree_bio_nettoyage_inf_or_eq_30) {
        $this->duree_bio_nettoyage = CMbDT::time("+ $duree_bio_nettoyage_inf_or_eq_30 minutes", "00:00:00");
      }
      elseif ($temps_op > 30 && $duree_bio_nettoyage_sup_30) {
        $this->duree_bio_nettoyage = CMbDT::time("+ $duree_bio_nettoyage_sup_30 minutes", "00:00:00");
      }
    }

    $sejour          = $this->loadRefSejour();
    $do_store_sejour = false; // Flag pour storer le séjour une seule fois
    $do_update_time  = false;

    // Synchronisation des heures d'admission
    if ($this->fieldModified('horaire_voulu')
      || $this->fieldModified('temp_operation')
      || $this->fieldModified('duree_postop')
      || $this->fieldModified('presence_preop')
      || $this->fieldModified('presence_postop')
      || $this->fieldModified('date')
      || $this->fieldModified('time_operation')
      || $this->fieldModified('duree_bio_nettoyage')
    ) {
      $do_update_time = true;
    }

    if ($this->loadRefCommande("bloc")->_id && $this->_ref_commande_mat["bloc"]->etat !== "annulee") {
      if ($this->fieldModified("annulee", "1")) {
        // Souci de cache sur l'intervention qui va être chargée dans le store de la commande (annule sera à 0)
        $this->_ref_commande_mat["bloc"]->_fwd['operation_id'] = $this;
        $this->_ref_commande_mat["bloc"]->cancelledOp();
      }
      if ($this->fieldModified("materiel") || $this->fieldModified("date")) {
        $this->_ref_commande_mat["bloc"]->modifiedOp($this->materiel);
      }
    }
    if ($this->loadRefCommande("pharmacie")->_id && $this->_ref_commande_mat["pharmacie"]->etat !== "annulee") {
      if ($this->fieldModified("annulee", "1")) {
        $this->_ref_commande_mat["pharmacie"]->cancelledOp();
      }
      if ($this->fieldModified("materiel_pharma") || $this->fieldModified("date")) {
        $this->_ref_commande_mat["pharmacie"]->modifiedOp($this->materiel);
      }
    }

    $change_date_codage = $this->fieldModified("date");
    if ($change_date_codage) {
      $this->loadRefBrancardage($this->_old->date);
    }

    // Si l'entrée réveil est retirée, on retire aussi la sspi
    if ($this->fieldModified("entree_reveil") && !$this->entree_reveil) {
      $this->sspi_id = "";
    }

    /* Suppression des actes dans le cas d'une annulation */
    if ($this->fieldModified("annulee") && $this->annulee) {
      $this->deleteActes();
    }

    if ($this->_libelle_comp) {
        $this->libelle .= ' - ' . $this->_libelle_comp;
    }

    // Standard storage
    if ($msg = parent::store()) {
      return $msg;
    }

    /* Si la date de l'intervention est modifiée, on modifie aussi la date des codage CCAM
    pour éviter d'avoir plusieurs codage liés à l'intervention */
    if ($change_date_codage) {
      $this->loadRefsCodagesCCAM();
      $this->loadRefsActes();

      foreach ($this->_ref_codages_ccam as $_codages_by_prat) {
        foreach ($_codages_by_prat as $_codage) {
          $_codage->date = $this->date;
          $_msg          = $_codage->store();
        }
      }

      /* Modification des dates d'exécution des actes */
      foreach ($this->_ref_actes as $act) {
        if (property_exists($act, 'execution') && CMbDT::date($act->execution) != $this->date) {
          $act->execution   = "$this->date " . CMbDT::time($act->execution);
          $act->_permissive = true;
          $msg              = $act->store();
        }
      }

      //De même pour la date du brancardage s'il a été crée
      if ($this->_ref_brancardage && $this->_ref_brancardage->_id) {
        $this->_ref_brancardage->prevu = $this->date;
        $this->_ref_brancardage->store();
      }
    }

    if ($do_update_time) {
      $do_store_sejour = $sejour->checkUpdateTimeAmbu($this);
    }

    // Création des besoins d'après le protocole sélectionné
    // Ne le faire que pour une nouvelle intervention
    // Pour une intervention existante, l'application du protocole
    // store les besoins
    if ($this->_types_ressources_ids && !$old->_id && CAppUI::gconf("dPbloc CPlageOp systeme_materiel") === "expert") {
      $types_ressources_ids = explode(",", $this->_types_ressources_ids);

      foreach ($types_ressources_ids as $_type_ressource_id) {
        $besoin                    = new CBesoinRessource;
        $besoin->type_ressource_id = $_type_ressource_id;
        $besoin->operation_id      = $this->_id;
        if ($msg = $besoin->store()) {
          return $msg;
        }
      }
    }

    // Création des matériels d'après les protocoles sélectionnés
    $this->createMateriels();

    $this->createAlert($comments);

    // Mise à jour du type de PeC du séjour en Chirurgical si pas déja obstétrique
    $sejour->completeField("type_pec");
    if (!$this->_id && $sejour->type_pec !== "O") {
      $sejour->type_pec = "C";
      $do_store_sejour  = true;
    }

    // Cas d'une annulation
    if (!$this->annulee) {
      // Si pas une annulation on recupére le sejour
      // et on regarde s'il n'est pas annulé
      if ($sejour->annule) {
        $sejour->annule  = 0;
        $do_store_sejour = true;
      }

      // Application des protocoles de prescription en fonction de l'operation->_id
      if ($this->_protocole_prescription_chir_id || $this->_protocole_prescription_anesth_id) {
        $sejour->_protocole_prescription_chir_id   = $this->_protocole_prescription_chir_id;
        $sejour->_protocole_prescription_anesth_id = $this->_protocole_prescription_anesth_id;
        $sejour->applyProtocolesPrescription($this->_id);

        // On les nullify pour eviter de les appliquer 2 fois
        $this->_protocole_prescription_anesth_id   = null;
        $this->_protocole_prescription_chir_id     = null;
        $sejour->_protocole_prescription_chir_id   = null;
        $sejour->_protocole_prescription_anesth_id = null;
      }
    }
    elseif ($this->rank != 0 && !CAppUI::conf("dPplanningOp COperation save_rank_annulee_validee")) {
      $this->rank           = 0;
      $this->time_operation = "00:00:00";
    }

    // Store du séjour (une seule fois)
    if ($do_store_sejour) {
      if ($msg = $sejour->store()) {
          return $msg;
      }
    }

    // Vérification qu'on a pas des actes CCAM codés obsolètes
    if ($this->codes_ccam) {
      $this->loadRefsActesCCAM();
      foreach ($this->_ref_actes_ccam as $keyActe => $acte) {
        if (stripos($this->codes_ccam, $acte->code_acte) === false) {
          $this->_ref_actes_ccam[$keyActe]->delete();
        }
      }
    }

    $reorder_rank_voulu        = $this->_reorder_rank_voulu;
    $this->_reorder_rank_voulu = null;

    if ($this->plageop_id) {
      $plage = $this->loadRefPlageOp();
      // Cas de la création dans une plage de spécialité
      if ($plage->spec_id && $plage->unique_chir) {
        $plage->chir_id = $this->chir_id;
        $plage->spec_id = "";
        $plage->store();
      }

      // Placement de l'interv selon la preference (placement souhaité)
      if ($place_after_interv_id) {
        $plage->loadRefsOperations(false, "rank, rank_voulu, horaire_voulu", true);

        unset($plage->_ref_operations[$this->_id]);

        if ($place_after_interv_id == -1) {
          $reorder                = true;
          $reorder_rank_voulu     = true;
          $plage->_ref_operations = CMbArray::mergeKeys(
            array($this->_id => $this), $plage->_ref_operations
          ); // To preserve keys (array_unshift does not)
        }
        elseif (isset($plage->_ref_operations[$place_after_interv_id])) {
          $reorder            = true;
          $reorder_rank_voulu = true;
          CMbArray::insertAfterKey($plage->_ref_operations, $place_after_interv_id, $this->_id, $this);
        }

        if ($reorder_rank_voulu) {
          $plage->_reorder_up_to_interv_id = $this->_id;
        }
      }
    }

    // Gestion du tarif et precodage des actes
    if ($this->_bind_tarif && $this->_id) {
      if ($msg = $this->bindTarif()) {
        return $msg;
      }
    }

    /* Création des actes ccam du chir dans le cas de l'application d'un tarif */
    if ($this->_codage_ccam_chir) {
      $this->codes_ccam = $this->_codage_ccam_chir;
      $this->precodeCCAM($this->chir_id);
    }

    /* Création des actes ccam de l'anesth dans le cas de l'application d'un tarif */
    if ($this->_codage_ccam_anesth && ($this->anesth_id || ($this->plageop_id && $this->_ref_plageop->anesth_id))) {
      $anesth_id        = $this->anesth_id ? $this->anesth_id : $this->_ref_plageop->anesth_id;
      $this->codes_ccam = $this->_codage_ccam_anesth;
      $this->precodeCCAM($anesth_id);
    }

    if ($this->_codage_ccam_chir
      && ($this->_codage_ccam_anesth && ($this->anesth_id || ($this->plageop_id && $this->_ref_plageop->anesth_id)))
    ) {
      $this->facture = 1;
    }

    // Vérouille le graphique Pré-opératoire, perop et SSPI quand le patient est sortie de salle
    $this->lockSurveillancePerop();

    // Standard storage bis
    if ($msg = parent::store()) {
      return $msg;
    }

    // Réordonnancement post-store
    if ($reorder) {
      // Réordonner la plage que l'on quitte
      if ($old_plage) {
        $old_plage->reorderOp();
      }

      $this->_ref_plageop->reorderOp($reorder_rank_voulu ? CPlageOp::RANK_REORDER : null);
    }

    // Made for Tamm-SIH
    if ($this->_ext_cabinet_id) {
      // If there is a cabinet id, store it as a external id
      $idex = new CIdSante400();
      $idex->setObject($this);
      $idex->id400 = $this->_ext_cabinet_id;
      $idex->tag   = "cabinet_id";
      $idex->store();
    }

    // Made for Tamm-SIH
    if ($this->_ext_cabinet_id) {
      // If there is a cabinet id, store it as a external id
      $idex = CIdSante400::getMatch($this->_class, "cabinet_id", $this->_ext_cabinet_id, $this->_id);
      $idex->store();

      if ($this->_ext_patient_id) {
        // If there is a cabinet id, store it as a external id
        if (!$this->_ref_patient) {
          $this->loadRefPatient();
        }
        $idex = CIdSante400::getMatch("CPatient", "ext_patient_id-$this->_ext_cabinet_id", $this->_ext_patient_id, $this->_ref_patient->_id);
        $idex->store();
      }
    }


    if ($sejour_id_modified) {
      $consult_anesth = $this->loadRefsConsultAnesth();

      if ($consult_anesth->_id) {
        $consult_anesth->sejour_id = $this->sejour_id;
        $consult_anesth->store();
      }
    }

    return null;
  }

  /**
   * Génération et enregistrement de la surveillance perop en fichier PDF
   *
   * @param string $type The type of surveillance to generate the document for
   *
   * @return void
   */
  public function generateSurveillanceDocument(string $type) {
    if (CModule::getActive("monitoringBloc") && !CAppUI::gconf("monitoringBloc general active_graph_supervision") ||
        CModule::getActive("monitoringMaternite") && !CAppUI::gconf("monitoringMaternite general active_graph_supervision") ||
      !in_array($type, ['preop', 'perop', 'sspi', 'partogramme'])) {
      return;
    }

    $this->completeField("sejour_id", "libelle");

    $url = [
      [
        "m"            => "salleOp",
        "dialog"       => "print_feuille_bloc",
        "operation_id" => $this->_id,
        "see_unit"     => 0,
        'surveillance' => $type
      ],
    ];

      $this->loadRefSejour();

      if ($this->_ref_sejour && $this->_ref_sejour->grossesse_id) {
          if ($type != "sspi") {
              $type = 'partogramme';
          }
          else {
              $type = 'post-partum';
          }
      }

    $file_name = CAppUI::tr("CSupervisionGraph-type-{$type}")
      . " - " . $this->loadRefPatient()->_view
      . " - " . CMbDT::dateToLocale($this->date);

    CWkhtmlToPDF::makePDF($this, $file_name, $url, "A4", "Landscape", "print");
  }

  /**
   * Verrouille le graphique Pré-opératoire et perop s'il y a une sortie de salle en fonction de la configuration
   *
   * @return void
   */
  public function lockSurveillancePerop() {
    if ((CModule::getActive("monitoringBloc") && !CAppUI::gconf("monitoringBloc general active_graph_supervision") && !$this->sortie_salle) ||
      (CModule::getActive("monitoringBloc") && !CAppUI::gconf("monitoringBloc general active_graph_supervision") && $this->sortie_salle && !$this->sortie_reveil_reel)) {
      return;
    }

    $time_locked_graph = CAppUI::gconf("dPsalleOp supervision_graph lock_supervision_graph");
    $current_user      = CMediusers::get();

    // pre op
    if (!$this->graph_pack_preop_locked_user_id && $this->sortie_salle) {
      $this->graph_pack_preop_locked_user_id = $current_user->_id;
      $this->datetime_lock_graph_preop       = CMbDT::dateTime("+{$time_locked_graph} hours", $this->sortie_salle);
    }

    // perop
    if (!$this->graph_pack_locked_user_id && $this->sortie_salle) {
      $this->graph_pack_locked_user_id = $current_user->_id;
      $this->datetime_lock_graph_perop = CMbDT::dateTime("+{$time_locked_graph} hours", $this->sortie_salle);
    }

    // sspi
    if (!$this->graph_pack_sspi_locked_user_id && $this->sortie_reveil_reel) {
      $this->graph_pack_sspi_locked_user_id = $current_user->_id;
      $this->datetime_lock_graph_sspi       = CMbDT::dateTime("+{$time_locked_graph} hours", $this->sortie_reveil_reel);
    }
  }

  /**
   * @inheritdoc
   */
  function loadGroupList($where = array(), $order = null, $limit = null, $groupby = null, $ljoin = array()) {
    $ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
    // Filtre sur l'établissement
    $g                        = CGroups::loadCurrent();
    $where["sejour.group_id"] = "= '$g->_id'";

    return $this->loadList($where, $order, $limit, $groupby, $ljoin);
  }

  /**
   * @inheritdoc
   */
  public function loadView(): void {
    parent::loadView();

    $this->loadRefSejour();
    if (CBrisDeGlace::isBrisDeGlaceRequired()) {
      $canAccess = CAccessMedicalData::checkForSejour($this->_ref_sejour);
      if ($canAccess) {
        $this->_can->read = 1;
      }
    }

    $this->loadRefPraticien()->loadRefFunction();
    $this->loadRefChirs();
    $this->loadRefAnesth()->loadRefFunction();
    $this->loadRefPlageOp();
    $this->loadRefPatient();
    $this->_ref_sejour->_ref_patient->loadRefPhotoIdentite();
  }

  /**
   * @inheritdoc
   */
  function loadComplete() {
    parent::loadComplete();
    $this->loadRefPatient();
    $this->loadRefTypeAnesth();
    foreach ($this->_ref_actes_ccam as &$acte_ccam) {
      $acte_ccam->loadRefsFwd();
    }
  }

  /**
   * Chargmeent du chirurgien
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefChir($cache = true) {
    $this->_ref_chir     = $this->loadFwdRef("chir_id", $cache);
    $this->_praticien_id = $this->_ref_chir->_id;

    return $this->_ref_chir;
  }

  /**
   * Chargement du deuxième chirurgien optionnel
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefChir2($cache = true) {
    return $this->_ref_chir_2 = $this->loadFwdRef("chir_2_id", $cache);
  }

  /**
   * Chargement du troisième chirurgien optionnel
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefChir3($cache = true) {
    return $this->_ref_chir_3 = $this->loadFwdRef("chir_3_id", $cache);
  }

  /**
   * Chargement du quatrième chirurgien optionnel
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefChir4($cache = true) {
    return $this->_ref_chir_4 = $this->loadFwdRef("chir_4_id", $cache);
  }

  /**
   * Chargement de tous les chirurgiens
   *
   * @param bool $cache Utilisation du cache
   *
   * @return void
   */
  function loadRefChirs($cache = true) {
    if ($this->loadRefChir($cache)->_id) {
      $this->_ref_chir->loadRefFunction();
      $this->_ref_chirs["chir_id"] = $this->_ref_chir;
    }
    if ($this->loadRefChir2($cache)->_id) {
      $this->_ref_chir_2->loadRefFunction();
      $this->_ref_chirs["chir_2_id"] = $this->_ref_chir_2;
    }
    if ($this->loadRefChir3($cache)->_id) {
      $this->_ref_chir_3->loadRefFunction();
      $this->_ref_chirs["chir_3_id"] = $this->_ref_chir_3;
    }
    if ($this->loadRefChir4($cache)->_id) {
      $this->_ref_chir_4->loadRefFunction();
      $this->_ref_chirs["chir_4_id"] = $this->_ref_chir_4;
    }
  }

  /**
   * Chargement du praticien responsable
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  public function loadRefPraticien(bool $cache = true): ?CMediusers {
    $this->_ref_praticien = $this->loadRefChir($cache);
    $this->_ref_executant = $this->_ref_praticien;

    return $this->_ref_praticien;
  }

  /**
   * @inheritdoc
   */
  public function getActeExecution(): ?string {
    $this->loadRefPlageOp();

    // Heure standard d'exécution des actes
    $this->loadRefSejour();
    if ($this->debut_op) {
        $this->_acte_execution = $this->debut_op;
    }
    elseif ($this->time_operation != "00:00:00") {
        $this->_acte_execution = CMbDT::addDateTime($this->temp_operation, $this->_datetime);
    }
    else {
        $this->_acte_execution = $this->_datetime;
    }

    if ($this->_acte_execution < $this->_ref_sejour->entree) {
        $this->_acte_execution = $this->_ref_sejour->entree;
    }
    elseif ($this->_acte_execution > $this->_ref_sejour->sortie) {
        $this->_acte_execution = $this->_ref_sejour->sortie;
    }

    return $this->_acte_execution;
  }

  /**
   * Charge l'affectation par rapport à la date de l'intervention
   *
   * @param bool $load_view Chargement complet des références de l'affectation
   *
   * @return CAffectation
   */
  function loadRefAffectation($load_view = true) {
    $this->loadRefPlageOp();

    $sejour                 = $this->loadRefSejour();
    $this->_ref_affectation = $sejour->getCurrAffectation($this->_datetime);
    if (!$this->_ref_affectation->_id) {
      $this->_ref_affectation = $sejour->loadRefFirstAffectation();
    }
    if ($load_view) {
      $this->_ref_affectation->loadView();
    }

    return $this->_ref_affectation;
  }

  /**
   * Load naissances
   *
   * @return CNaissance[]|null
   */
  function loadRefsNaissances() {
    return $this->_ref_naissances = $this->loadBackRefs("naissances");
  }


  /**
   * Charge le poste sspi
   *
   * @return CPosteSSPI
   */
  function loadRefPoste() {
    return $this->_ref_poste = $this->loadFwdRef("poste_sspi_id", true);
  }

  /**
   * Charge le poste préop
   *
   * @return CPosteSSPI
   */
  function loadRefPostePreop() {
    return $this->_ref_poste_preop = $this->loadFwdRef("poste_preop_id", true);
  }

  /**
   * Chargement de la SSPI
   *
   * @return CSSPI
   */
  function loadRefSSPI() {
    return $this->_ref_sspi = $this->loadFwdRef("sspi_id", true);
  }

  /**
   * Met à jour les information sur la salle
   * Nécessiste d'avoir chargé la plage opératoire au préalable
   *
   * @return CSalle
   */
  function updateSalle() {
    if ($this->plageop_id && $this->salle_id) {
      $this->_deplacee = $this->_ref_plageop->salle_id != $this->salle_id;
    }

    // Evite de recharger la salle quand ce n'est pas nécessaire
    if ($this->plageop_id && !$this->_deplacee) {
      return $this->_ref_salle =& $this->_ref_plageop->_ref_salle;
    }
    else {
      return $this->_ref_salle = $this->loadFwdRef("salle_id", true);
    }
  }

  /**
   * Chargement de l'anesthesiste, sur l'opération si disponible, sinon sur la plage
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefAnesth($cache = true) {
    // Already loaded
    if ($this->_ref_anesth) {
      return $this->_ref_anesth;
    }

    // Direct reference
    if ($this->anesth_id) {
      return $this->_ref_anesth = $this->loadFwdRef("anesth_id", $cache);
    }

    // Distant refereence
    if ($this->plageop_id) {
      $plage = $this->_ref_plageop ?
        $this->_ref_plageop :
        $this->loadFwdRef("plageop_id", $cache);

      return $this->_ref_anesth = $plage->loadFwdRef("anesth_id", $cache);
    }

    // Otherwise blank user
    return $this->_ref_anesth = new CMediusers();
  }

  /**
   * Chargement de la consultation préanesthésique pour l'opération courante
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefVisiteAnesth($cache = true) {
    return $this->_ref_anesth_visite = $this->loadFwdRef("prat_visite_anesth_id", $cache);
  }

  /**
   * Chargement de la plage opératoire
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CPlageOp
   */
  function loadRefPlageOp($cache = true) {

    if (!$this->_ref_plageop) {
      $this->_ref_plageop = $this->loadFwdRef("plageop_id", $cache);
    }

    $this->loadRefVisiteAnesth();

    /** @var CPlageOp $plage */
    $plage = $this->_ref_plageop;

    if ($plage->_id) {
      // Avec plage d'opération
      $plage->loadRefsFwd($cache);

      if ($this->anesth_id) {
        $this->loadRefAnesth();
      }
      else {
        $this->_ref_anesth = $plage->_ref_anesth;
      }
    }
    else {
      // Hors plage
      $this->loadRefAnesth();
    }

    // Champs dérivés
    $this->updateSalle();
    $this->updateDatetimes();
    $this->updateView();

    return $plage;
  }

  /**
   * Charge les éléments de codage CCAM
   *
   * @return CCodageCCAM[]
   */
  public function loadRefsCodagesCCAM(): array {
    if ($this->_ref_codages_ccam) {
      return $this->_ref_codages_ccam;
    }

    $codages                 = $this->loadBackRefs("codages_ccam");
    $this->_ref_codages_ccam = array();
    foreach ($codages as $_codage) {
      if (!array_key_exists($_codage->praticien_id, $this->_ref_codages_ccam)) {
        $this->_ref_codages_ccam[$_codage->praticien_id] = array();
      }

      $this->_ref_codages_ccam[$_codage->praticien_id][] = $_codage;
    }

    /* Si les objets codages de l'anesthésiste et du chirurgien n'existent pas, ils sont créés automatiquement
     * pour éviter que l'intervention ne soit facturé si tous les codages n'ont pas été créés */
    $chir = $this->loadRefPraticien();

    $this->getActeExecution();

    if ($chir->loadRefRemplacant($this->_acte_execution)) {
      $chir = $chir->_ref_remplacant;
    }
    if (!array_key_exists($chir->_id, $this->_ref_codages_ccam)) {
      $_codage                             = CCodageCCAM::get($this, $chir->_id, 1);
      $this->_ref_codages_ccam[$chir->_id] = array($_codage);
    }

    $anesth = $this->loadRefAnesth();

    if ($anesth->loadRefRemplacant($this->_acte_execution)) {
      $anesth = $anesth->_ref_remplacant;
    }

    $has_activity_anesth = false;
    $this->loadExtCodesCCAM();
    foreach ($this->_ext_codes_ccam as $_code) {
      if (array_key_exists(4, $_code->activites)) {
        $has_activity_anesth = true;
        break;
      }
    }

    if ($anesth->_id && !array_key_exists($anesth->_id, $this->_ref_codages_ccam) && $has_activity_anesth) {
      $_codage_anesth                        = CCodageCCAM::get($this, $anesth->_id, 4);
      $_codage                               = CCodageCCAM::get($this, $anesth->_id, 1);
      $this->_ref_codages_ccam[$anesth->_id] = array($_codage_anesth, $_codage);
    }

    return $this->_ref_codages_ccam;
  }

  function updateView() {
    $this->_view = "Intervention";

    if (!$this->plageop_id) {
      $this->_view .= " [HP]";
    }

    $this->_view .= " le " . CMbDT::format($this->date, CAppUI::conf("date"));

    if ($this->_ref_patient) {
      $this->_view .= " de " . $this->_ref_patient->_view;
    }

    if ($this->_ref_chir) {
      $this->_view .= " par le Dr " . $this->_ref_chir->_view;
    }
  }

  /**
   * Calculs sur les champs d'horodatage dérivés, notamment en fonction de la plage
   *
   * @return void
   */
  function updateDatetimes() {
    $plage = $this->_ref_plageop;
    $date  = $this->date;

    // Calcul du nombre de jour entre la date actuelle et le jour de l'operation
    $this->_compteur_jour = CMbDT::daysRelative($date, CMbDT::date());

    // Horaire global
    if ($this->time_operation && $this->time_operation != "00:00:00") {
      $this->_datetime = "$date $this->time_operation";
    }
    elseif ($this->horaire_voulu && $this->horaire_voulu != "00:00:00") {
      $this->_datetime = "$date $this->horaire_voulu";
    }
    elseif ($plage && $plage->_id) {
      $this->_datetime = "$date " . $plage->debut;
    }
    else {
      $this->_datetime = "$date 00:00:00";
    }

    $this->_datetime_best = $this->_datetime;
    $this->_datetime_reel = $this->debut_op;
    if ($this->debut_op) {
      $this->_datetime_best = $this->_datetime_reel;
    }
    $this->_datetime_reel_fin = $this->fin_op;

    $today = CMbDT::date();
    $yesterday = CMbDT::date('-1 day');

    foreach (static::$timings as $_timing) {
        if ($this->{$_timing} && in_array(CMbDT::date($this->{$_timing}), [$yesterday, $today])) {
            $this->_modif_operation = true;
        }
    }
  }

  /**
   * @inheritdoc
   */
  public function preparePossibleActes(): void {
    $this->loadRefPlageOp();
  }

  /**
   * Chargement du dossier d'anesthésie
   *
   * @return CConsultAnesth
   */
  function loadRefsConsultAnesth() {
    if ($this->_ref_consult_anesth) {
      return $this->_ref_consult_anesth;
    }

    $order                     = "date DESC";
    $ljoin                     = array(
      "consultation" => "consultation.consultation_id = consultation_anesth.consultation_id",
      "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"
    );
    $this->_ref_consult_anesth = $this->loadFirstBackRef("dossiers_anesthesie", $order, null, $ljoin);
    $this->_ref_consult_anesth->loadRefChir();

    return $this->_ref_consult_anesth;
  }

  /**
   * Chargement de la consult de chirurgie avant l'intervention,
   * cad la dernière consultation pour le patient par le chirurgien avant l'internvention et hors du séjour
   *
   * @return CConsultation
   */
  public function loadRefConsultChir() {
    $sejour                = $this->loadRefSejour();
    $entree                = CMbDT::date($sejour->entree);
    $ljoin["plageconsult"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";
    $where["patient_id"]   = "= '$sejour->patient_id'";
    $where["chir_id"]      = "= '$this->chir_id'";
    $where["date"]         = "< '$entree'";
    $where[]               = "sejour_id IS NULL OR sejour_id != '$this->sejour_id'";
    $order                 = "date DESC";
    $consult               = new CConsultation;
    $consult->loadObject($where, $order, null, $ljoin);

    return $this->_ref_consult_chir = $consult;
  }

  /**
   * Chargement du séjour
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CSejour
   * @throws Exception
   */
    public function loadRefSejour(bool $cache = true): ?CSejour {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", $cache);
  }

  /**
   * Chargement des gestes perop
   *
   * @param array $where Where SQL statement
   *
   * @return CAnesthPerop[]
   * @throws Exception
   */
  function loadRefsAnesthPerops($where = array()) {
    $this->_count_anesth_perops = $this->countBackRefs("anesth_perops", $where);

    return $this->_ref_anesth_perops = $this->loadBackRefs("anesth_perops", "datetime", null, null, null, null, "", $where);
  }

  /**
   * Chargement des poses de dispositif vasculaire
   *
   * @param bool $count_check_lists Calcul du nombre de checklist remplies
   *
   * @return CPoseDispositifVasculaire[]
   */
  function loadRefsPosesDispVasc($count_check_lists = false) {
    $this->_ref_poses_disp_vasc = $this->loadBackRefs("poses_disp_vasc", "date");

    if ($count_check_lists) {
      foreach ($this->_ref_poses_disp_vasc as $_pose) {
        /** @var CPoseDispositifVasculaire $_pose */
        $_pose->countSignedCheckLists();
      }
    }

    return $this->_ref_poses_disp_vasc;
  }

  /**
   * Chargement du patient concerné
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CPatient
   */
  public function loadRefPatient(bool $cache = true): ?CPatient {
    $sejour             = $this->loadRefSejour($cache);
    $patient            = $sejour->loadRefPatient($cache);
    $this->_ref_patient = $patient;
    $this->_patient_id  = $patient->_id;
    $this->loadFwdRef("_patient_id", $cache);

    return $patient;
  }

  /**
   * Chargement de la salle concernée
   *
   * @return CSalle
   */
  function loadRefSalle() {
    $this->_ref_salle = $this->loadFwdRef("salle_id", true);
    $this->_bloc_id   = $this->_ref_salle->bloc_id;

    return $this->_ref_salle;
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd($cache = true) {
    $consult_anesth = $this->loadRefsConsultAnesth();
    $consult_anesth->countDocItems();

    $consultation = $consult_anesth->loadRefConsultation();
    $consultation->countDocItems();
    $consultation->canRead();
    $consultation->canEdit();

    $this->loadRefPlageOp($cache);
    $this->loadExtCodesCCAM();

    $this->loadRefChir($cache)->loadRefFunction();
    $this->loadRefPatient($cache);
    $this->updateView();
  }

  /**
   * Chargement du bloodsalvage associé
   *
   * @return CBloodSalvage
   */
  function loadRefBloodSalvage() {
    return $this->_ref_blood_salvage = $this->loadUniqueBackRef("blood_salvages");
  }

  /**
   * Chargement des besoins en ressources materielles
   *
   * @return CBesoinRessource[]
   */
  function loadRefsBesoins() {
    return $this->_ref_besoins = $this->loadBackRefs("besoins_ressources");
  }

  /**
   * Charge le pack de graphiques
   *
   * @return CSupervisionGraphPack
   * @throws Exception
   */
  function loadRefGraphPack() {
    return $this->_ref_graph_pack = $this->loadFwdRef("graph_pack_id", true);
  }

  /**
   * Charge le pack de graphiques pour la SSPI
   *
   * @return CSupervisionGraphPack
   * @throws Exception
   */
  function loadRefGraphPackSSPI() {
    return $this->_ref_graph_pack_sspi = $this->loadFwdRef("graph_pack_sspi_id");
  }

  /**
   * Charge le pack de graphiques pour le preop
   *
   * @return CSupervisionGraphPack
   * @throws Exception
   */
  function loadRefGraphPackPreop() {
    return $this->_ref_graph_pack_preop = $this->loadFwdRef("graph_pack_preop_id");
  }

  /**
   * Charge le validateur de la sortie
   *
   * @return CMediusers
   */
  function loadRefSortieLocker() {
    return $this->_ref_sortie_locker = $this->loadFwdRef("sortie_locker_id", true);
  }

  /**
   * Charge le workflow d'opération
   *
   * @return COperationWorkflow
   */
  function loadRefWorkflow() {
    return $this->_ref_workflow = $this->loadUniqueBackRef("workflow");
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->loadRefsFiles();
    $this->loadRefsActes();
    $this->loadRefsDocs();
  }

  /**
   * Vérifie si une intervention est considérée
   * comme terminée concernant le codage des actes
   *
   * @return bool
   */
  public function isCoded(): ?bool {
    $this->loadRefPlageOp();
    $this->loadRefSejour();
    $this->_ref_sejour->loadRefsBillingPeriods();
    $this->_coded = false;
    $worked_days  = CAppUI::gconf('dPsalleOp COperation modif_actes_worked_days') == '1' ? true : false;

    $config = CAppUI::gconf("dPsalleOp COperation modif_actes");

    if (($config == "oneday"
        && CMbDT::daysRelative("$this->date 00:00:00", CMbDT::date() . ' 00:00:00', $worked_days) >= 1)
      || ($config == "button" && $this->_ref_plageop->actes_locked)
      || ($config == "facturation" && $this->facture)
      || ($config == '48h'
        && CMbDT::daysRelative(CMbDT::dateTime('+48 hours', $this->_datetime), CMbDT::dateTime(), $worked_days) >= 2)
    ) {
      $this->_coded         = true;
      $this->_coded_message = "config-dPsalleOp-COperation-modif_actes.$config";
    }
    elseif (strpos($config, 'sortie_sejour') !== false
      && $this->_ref_sejour->sortie_reelle
    ) {
      $days      = CMbDT::daysRelative($this->_ref_sejour->sortie_reelle, CMbDT::dateTime(), $worked_days);
      $threshold = null;
      $config    = explode('|', $config);
      if (array_key_exists(1, $config) && $this->_ref_sejour->type == 'ambu') {
        $threshold = $config[1];
      }
      elseif (array_key_exists(2, $config) && $this->_ref_sejour->type == 'comp') {
        $threshold = $config[2];
      }

      if ($days > $threshold) {
        $this->_coded         = true;
        $this->_coded_message = 'config-dPsalleOp-COperation-modif_actes.sortie_sejour';
      }
    }
    elseif ($config == 'facturation_web100T' && CModule::getActive('web100T') && $this->_ref_sejour->sortie_reelle) {
      $this->_coded = CWeb100TSejour::isSejourBilled($this->_ref_sejour);
      if ($this->_coded) {
        $this->_coded_message = "config-dPsalleOp-COperation-modif_actes.$config";
      }
    }

    $user = CMediusers::get();
    $this->loadRefsCodagesCCAM();
    $delay_auto_relock = CAppUI::gconf('dPccam codage delay_auto_relock');
    if ($this->_coded && $delay_auto_relock != '0' && !CModule::getCanDo('dPpmsi')->edit
      && array_key_exists($user->_id, $this->_ref_codages_ccam) && strpos($this->_coded_message, 'billing_period') === false
    ) {
      /** @var CCodageCCAM $codage */
      $codage = reset($this->_ref_codages_ccam[$user->_id]);

      if ($codage->_codage_derogation) {
        $this->_coded = false;
      }
    }

    return $this->_coded;
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    $chir   = $this->loadRefChir();
    $chir2  = $this->loadRefChir2();
    $chir3  = $this->loadRefChir3();
    $chir4  = $this->loadRefChir4();
    $anesth = $this->loadRefAnesth();

    // Permission sur l'un des praticien et sur le module
    return ((
        $chir->getPerm($permType)
        || ($chir2->_id && $chir2->getPerm($permType))
        || ($chir3->_id && $chir3->getPerm($permType))
        || ($chir4->_id && $chir4->getPerm($permType))
        || ($anesth->_id && $anesth->getPerm($permType))
      )
      && $this->_ref_module->getPerm($permType)
    );
  }

  /**
   * @inheritdoc
   */
  function fillTemplate(&$template) {
    $this->loadRefsFwd();
    $this->_ref_sejour->loadRefsFwd();

    // Chargement du fillTemplate du sejour
    $this->_ref_sejour->fillTemplate($template);

    $consult_anesth = $this->loadRefsConsultAnesth();
    $consult_anesth->loadRefOperation();
    $consult_anesth->fillLimitedTemplate($template);

    // Chargement du fillTemplate de l'opération
    $this->fillLimitedTemplate($template);
  }

  /**
   * @inheritdoc
   */
  function fillLimitedTemplate(&$template) {
    $this->loadRefsFwd(1);
    $this->loadRefPraticien();
    $this->loadRefChir2();
    $this->loadRefChir3();
    $this->loadRefChir4();
    $this->loadRefAnesth();
    $this->loadRefsFiles();
    $this->loadAffectationsPersonnel();
    $this->loadRefPosition();
    $this->loadRefTypeAnesth();

    $plageop = $this->_ref_plageop;
    $plageop->loadAffectationsPersonnel();

    foreach ($this->_ext_codes_ccam as $_code) {
      $_code->getRemarques();
      $_code->getActivites();
      $_code->getPrice($this->_ref_praticien, $this->_ref_patient, $this->date);
    }

    $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

    for ($i = 1; $i < 5; $i++) {
      $prop      = "_ref_chir" . ($i == 1 ? "" : "_$i");
      $praticien = $this->$prop;
      $praticien->loadRefFunction();
      $praticien->loadRefDiscipline();
      $praticien->loadRefSpecCPAM();

      $number = $i == 1 ? "" : " $i";

      $operation_section = CAppUI::tr('COperation-Operation');
      $chir_subItem      = CAppUI::tr('COperation-chir_id');

      $template->addProperty("$operation_section - $chir_subItem$number", $praticien->_id ? CAppUI::tr('CMedecin.titre.dr') . " " . $praticien->_view : '');
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-_p_last_name'), $praticien->_user_last_name);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-_p_first_name'), $praticien->_user_first_name);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-tel-court'), $praticien->_user_phone);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-initials-court'), $praticien->_shortview);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-discipline_id'), $praticien->_ref_discipline->_view);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CDiscipline'), $praticien->_ref_spec_cpam->_view);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-cab'), $praticien->cab);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-conv'), $praticien->conv);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-zisd'), $praticien->zisd);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-ik'), $praticien->ik);

      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-titres'), $praticien->titres);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-adeli'), $praticien->adeli);
      $template->addBarcode("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-ADELI bar code'), $praticien->adeli, array("barcode" => array(
        "title" => CAppUI::tr("CMediusers-adeli")
      )));
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-rpps'), $praticien->rpps);
      $template->addBarcode("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-RPPS bar code'), $praticien->rpps, array("barcode" => array(
        "title" => CAppUI::tr("CMediusers-rpps")
      )));
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMediusers-_user_email'), $praticien->_user_email);
      $template->addProperty("$operation_section - $chir_subItem$number - " . CAppUI::tr('CMedecin-email_apicrypt'), $praticien->mail_apicrypt);
    }

    $anesth_subItem = CAppUI::tr('COperation-anesth_id');
    $ccam1_subItem  = CAppUI::tr('CActe-CCAM1');
    $ccam2_subItem  = CAppUI::tr('CActe-CCAM2');
    $ccam3_subItem  = CAppUI::tr('CActe-CCAM3');
    $ccam_subItem   = CAppUI::tr('CActeCCAM-court');
    $template->addProperty("$operation_section - $anesth_subItem - " . CAppUI::tr('CMedecin-name'), @$this->_ref_anesth->_user_last_name);
    $template->addProperty("$operation_section - $anesth_subItem - " . CAppUI::tr('CPatient-first name'), @$this->_ref_anesth->_user_first_name);
    $template->addProperty("$operation_section - " . CAppUI::tr('CConsultation._type.anesth'), $this->_lu_type_anesth);
    $template->addProperty("$operation_section - " . CAppUI::tr('common-label'), $this->libelle);
    $template->addProperty("$operation_section - $ccam1_subItem - " . CAppUI::tr('common-code'), @$this->_ext_codes_ccam[0]->code);
    $template->addProperty("$operation_section - $ccam1_subItem - " . CAppUI::tr('common-description'), @$this->_ext_codes_ccam[0]->libelleLong);
    $template->addProperty("$operation_section - $ccam1_subItem - " . CAppUI::tr('CActeCCAM-amount activity 1'), @$this->_ext_codes_ccam[0]->activites[1]->phases[0]->tarif);
    $template->addProperty("$operation_section - $ccam2_subItem - " . CAppUI::tr('common-code'), @$this->_ext_codes_ccam[1]->code);
    $template->addProperty("$operation_section - $ccam2_subItem - " . CAppUI::tr('common-description'), @$this->_ext_codes_ccam[1]->libelleLong);
    $template->addProperty("$operation_section - $ccam2_subItem - " . CAppUI::tr('CActeCCAM-amount activity 1'), @$this->_ext_codes_ccam[1]->activites[1]->phases[0]->tarif);
    $template->addProperty("$operation_section - $ccam3_subItem - " . CAppUI::tr('common-code'), @$this->_ext_codes_ccam[2]->code);
    $template->addProperty("$operation_section - $ccam3_subItem - " . CAppUI::tr('common-description'), @$this->_ext_codes_ccam[2]->libelleLong);
    $template->addProperty("$operation_section - $ccam3_subItem - " . CAppUI::tr('CActeCCAM-amount activity 1'), @$this->_ext_codes_ccam[2]->activites[1]->phases[0]->tarif);
    $template->addProperty("$operation_section - $ccam_subItem - " . CAppUI::tr('common-code|pl'), implode(" - ", $this->_codes_ccam));

    /* Les anesthésistes pouvant avoir un tarif différent du chir, on récupère le tarif de l'anesthésiste */
    if ($this->_ref_anesth && $this->_ref_anesth->_id) {
      foreach ($this->_ext_codes_ccam as $_code) {
        $_code->getPrice($this->_ref_anesth, $this->_ref_patient, $this->date);
      }
    }
    $template->addProperty("$operation_section - $ccam1_subItem - " . CAppUI::tr('CActeCCAM-amount activity 4'), @$this->_ext_codes_ccam[0]->activites[4]->phases[0]->tarif);
    $template->addProperty("$operation_section - $ccam2_subItem - " . CAppUI::tr('CActeCCAM-amount activity 4'), @$this->_ext_codes_ccam[1]->activites[4]->phases[0]->tarif);
    $template->addProperty("$operation_section - $ccam3_subItem - " . CAppUI::tr('CActeCCAM-amount activity 4'), @$this->_ext_codes_ccam[2]->activites[4]->phases[0]->tarif);

    $template->addProperty(
      "$operation_section - $ccam_subItem - " . CAppUI::tr('common-description|pl'), implode(" - ", CMbArray::pluck($this->_ext_codes_ccam, "libelleLong"))
    );
    $template->addProperty("$operation_section - " . CAppUI::tr('common-operating room-court'), @$this->_ref_salle->nom);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-side'), $this->cote);
    $template->addProperty("$operation_section - " . CAppUI::tr('common-position'), @$this->_ref_position->_view);
    $template->addDateProperty("$operation_section - " . CAppUI::tr('common-date'), $this->_datetime_best != " 00:00:00" ? $this->_datetime_best : "");
    $template->addLongDateProperty("$operation_section - " . CAppUI::tr('common-long date'), $this->_datetime_best != " 00:00:00" ? $this->_datetime_best : "");
    $template->addTimeProperty("$operation_section - " . CAppUI::tr('hour'), $this->time_operation);
    $template->addTimeProperty("$operation_section - " . CAppUI::tr('common-duration'), $this->temp_operation);
    $template->addDurationProperty("$operation_section - " . CAppUI::tr('common-real duration'), $this->_duree_interv);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-block entry'), $this->entree_salle);

    if (CAppUI::gconf("dPsalleOp COperation garrots_multiples")) {
      $this->loadGarrots();
      $garrots_multiple = array();

      foreach ($this->_ref_garrots as $_garrot) {
        $poseDateTime    = CMbDT::format($_garrot->datetime_pose, CAppUI::conf("datetime"));
        $retraitDateTime = CMbDT::format($_garrot->datetime_retrait, CAppUI::conf("datetime"));

        $garrots_multiple[] = "<strong>" . CAppUI::tr("COperation-cote") . "</strong> : " . $_garrot->cote
          . " - <strong>" . CAppUI::tr("COperationGarrot-pression") . "</strong> : "
          . $_garrot->pression . " " . CAppUI::tr("common-mmhg")
          . " - <strong>" . CAppUI::tr("COperationGarrot-datetime_pose") . "</strong> : " . $poseDateTime
          . " - <strong>" . CAppUI::tr("COperationGarrot-datetime_retrait") . "</strong> : " . $retraitDateTime
          . " - <strong>" . CAppUI::tr("COperationGarrot-_duree") . "</strong> : "
          . ($_garrot->_duree ?: "-") . " " . CAppUI::tr("common-minute-court");
      }

      $template->addProperty("$operation_section - " . CAppUI::tr('COperation-multiple tourniquet|pl'), implode("<br />", $garrots_multiple), null, false);
    }
    else {
      $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-pose tourniquet'), $this->pose_garrot);
      $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-tourniquet withdrawal'), $this->retrait_garrot);
    }

    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-start induction'), $this->induction_debut);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-start operation-court'), $this->debut_op);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-end operation-court'), $this->fin_op);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-end induction'), $this->induction_fin);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-start incision'), $this->incision);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-block output'), $this->sortie_salle);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-SSPI input'), $this->entree_reveil);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-SSPI output'), $this->sortie_reveil_reel);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-start ALR'), $this->debut_alr);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-end ALR'), $this->fin_alr);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-start AG'), $this->debut_ag);
    $template->addDateTimeProperty("$operation_section - " . CAppUI::tr('COperation-end AG'), $this->fin_ag);
    $template->addProperty("$operation_section - " . CAppUI::tr('CConsultAnesth-anesthetic exceeded-court'), $this->depassement_anesth);
    $template->addProperty("$operation_section - " . CAppUI::tr('CConsultAnesth-Comment anesthetic exceeded-court'), $this->commentaire_depassement_anesth);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-passage_uscpo'), "$this->duree_uscpo " . CAppUI::tr('common-night(|pl)'));

    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-exceeding'), $this->depassement);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-pre-op exam|pl'), $this->examen);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-equipment'), $this->materiel);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-pharmacy equipment'), $this->materiel_pharma);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-pre-op exam'), $this->exam_per_op);
    $template->addProperty("$operation_section - " . CAppUI::tr('CSejour-recovery'), $this->_ref_sejour->convalescence);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-rques-court'), $this->rques);
    $template->addProperty("$operation_section - " . CAppUI::tr('COperation-ASA-desc'), $this->getFormattedValue("ASA"));

    $consult_anesth_subItem = CAppUI::tr('CConsultAnesth-Anesthesia consultation');
    $consult_anesth         = $this->_ref_consult_anesth;
    $consult                = $consult_anesth->loadRefConsultation();
    $consult->loadRefPlageConsult();
    $prat = $consult->loadRefPraticien();
    $template->addDateProperty("$operation_section - $consult_anesth_subItem - " . CAppUI::tr('CConsultAnesth-date_interv'), $consult->_id ? $consult->_datetime : "");
    $template->addLongDateProperty("$operation_section - $consult_anesth_subItem - " . CAppUI::tr('common-Date (long)'), $consult->_id ? $consult->_datetime : "");
    $template->addLongDateProperty(
      "$operation_section - $consult_anesth_subItem - " . CAppUI::tr('common-Date (long, lowercase)'), $consult->_id ? $consult->_datetime : "", true
    );
    $template->addTimeProperty("$operation_section - $consult_anesth_subItem - " . CAppUI::tr('common-Hour'), $consult->_id ? $consult->_datetime : "");
    $template->addProperty("$operation_section - $consult_anesth_subItem - " . CAppUI::tr('CConsult-Practitioner - First Name'), $consult->_id ? $prat->_user_first_name : "");
    $template->addProperty("$operation_section - $consult_anesth_subItem - " . CAppUI::tr('CConsult-Practitioner - Name'), $consult->_id ? $prat->_user_last_name : "");
    $template->addProperty("$operation_section - $consult_anesth_subItem - " . CAppUI::tr('COperation-rques-court'), $consult->rques);

    /** @var CMediusers $prat_visite */
    $visit_subItem = CAppUI::tr('COperation-Pre-anesthesia visit');
    $prat_visite   = $this->loadFwdRef("prat_visite_anesth_id", true);

    $template->addDateProperty("$operation_section - $visit_subItem - " . CAppUI::tr('common-Date'), $this->date_visite_anesth);
    $template->addLongDateProperty("$operation_section - $visit_subItem - " . CAppUI::tr('common-Date (long)'), $this->date_visite_anesth);
    $template->addProperty("$operation_section - $visit_subItem - " . CAppUI::tr('CConsultation-rques-court'), $this->getFormattedValue("rques_visite_anesth"));
    $template->addProperty("$operation_section - $visit_subItem - " . CAppUI::tr('Permission'), $this->getFormattedValue("autorisation_anesth"));
    $template->addProperty("$operation_section - $visit_subItem - " . CAppUI::tr('CConsult-Practitioner - First Name'), $prat_visite->_user_first_name);
    $template->addProperty("$operation_section - $visit_subItem - " . CAppUI::tr('CConsult-Practitioner - Name'), $prat_visite->_user_last_name);

    $template->addBarcode("$operation_section - " . CAppUI::tr('CSejour-ID barcode'), $this->_id);

    $list = CMbArray::pluck($this->_ref_files, "file_name");
    $template->addListProperty("$operation_section - " . CAppUI::tr('CFile-List of file|pl'), $list);

    $perso_reel_subItem = CAppUI::tr('CPersonnel-real staff');
    foreach ($this->_ref_affectations_personnel as $emplacement => $affectations) {
      $locale   = CAppUI::tr("CPersonnel.emplacement.$emplacement");
      $property = implode(" - ", CMbArray::pluck($affectations, "_ref_personnel", "_ref_user", "_view"));
      $template->addProperty("$operation_section - $perso_reel_subItem - $locale", $property);
    }

    $perso_prevu_subItem = CAppUI::tr('CPersonnel-planned staff');
    foreach ($plageop->_ref_affectations_personnel as $emplacement => $affectations) {
      $locale   = CAppUI::tr("CPersonnel.emplacement.$emplacement");
      $property = implode(" - ", CMbArray::pluck($affectations, "_ref_personnel", "_ref_user", "_view"));
      $template->addProperty("$operation_section - $perso_prevu_subItem - $locale", $property);
    }

    $template->addProperty("$operation_section - ". CAppUI::tr('COperation-visitors'), $this->visitors);

    $evts = $incidents = array();

    foreach ($this->loadRefsAnesthPerops() as $_evt) {
      if ($_evt->incident) {
        $incidents[] = $_evt;
        continue;
      }
      $evts[] = $_evt;
    }

    $anapath_section = "$operation_section - " . CAppUI::tr("COperation-anapath");
    $labo_anapath = $this->loadRefLaboratoireAnapath();

    $template->addProperty("$anapath_section - " . CAppUI::tr('COperation-anapath'),
      CAppUI::tr("COperation.anapath.$this->anapath"));
    $template->addProperty("$anapath_section - " . CAppUI::tr('COperation-flacons_anapath'), $this->flacons_anapath);
    $template->addProperty("$anapath_section - " . CAppUI::tr('COperation-labo_anapath_id'), $labo_anapath->_view);
    $template->addProperty("$anapath_section - " . CAppUI::tr('COperation-description_anapath'), $this->description_anapath);
    $labo_anapath->fillLimitedTemplate($template);

    $baterio_section = "$operation_section - " . CAppUI::tr("COperation-labo");
    $labo_bacterio = $this->loadRefLaboratoireBacterio();

    $template->addProperty("$baterio_section - " . CAppUI::tr('COperation-labo'),
      CAppUI::tr("COperation.labo.$this->labo"));
    $template->addProperty("$baterio_section - " . CAppUI::tr('COperation-flacons_bacterio'), $this->flacons_bacterio);
    $template->addProperty("$baterio_section - " . CAppUI::tr('COperation-labo_bacterio_id'), $labo_bacterio->_view);
    $template->addProperty("$baterio_section - " . CAppUI::tr('COperation-description_bacterio'), $this->description_bacterio);
    $labo_bacterio->fillLimitedTemplate($template);

    $rayons_x_section = "$operation_section - " . CAppUI::tr("COperation-rayons_x");
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-rayons_x'),
      CAppUI::tr("COperation.rayons_x.$this->rayons_x"));
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-ampli_id'), $this->loadRefAmpli()->_view);
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-temps_rayons_x'), $this->temps_rayons_x);
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-dose_rayons_x'),
      "$this->dose_rayons_x " . CAppUI::tr("COperation.unite_rayons_x.$this->unite_rayons_x"));
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-dose_recue_graphie'),
        "$this->dose_recue_graphie " . CAppUI::tr("COperation.unite_rayons_x.$this->unite_rayons_x"));
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-dose_recue_scopie'),
        "$this->dose_recue_scopie " . CAppUI::tr("COperation.unite_rayons_x.$this->unite_rayons_x"));
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-nombre_graphie'), $this->nombre_graphie);
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-description_rayons_x'), $this->description_rayons_x);

    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-pds'), $this->pds);
    $template->addProperty("$rayons_x_section - " . CAppUI::tr('COperation-unite_pds'), CAppUI::tr("COperation.unite_pds.$this->unite_pds"));

    $template->addProperty("$operation_section - " . CAppUI::tr("COperation-prothese"), CAppUI::tr("COperation.prothese.$this->prothese"));

    $template->addListProperty("$operation_section - " . CAppUI::tr('COperation-Pre-operative event|pl'), $evts);
    $template->addListProperty("$operation_section - " . CAppUI::tr('COperation-Pre-operative incident|pl'), $incidents);

    $besoins = [];
    foreach ($this->loadRefsBesoins() as $_besoin) {
      $type_ressource = $_besoin->loadRefTypeRessource();
      $usage = $_besoin->loadRefUsage();

      $besoins[] = $type_ressource->libelle . ($usage->_id ? (" : " . $usage->loadRefRessource()->libelle) : "");
    }

    $template->addListProperty("$operation_section - " . CAppUI::tr("CRessourceMaterielle|pl"), $besoins);

    if (CModule::getActive('monitoringPatient')) {
        CSupervisionGraph::addObservationDataToTemplate($template, $this, "Opération");
    }

    if (CModule::getActive("forms")) {
      CExObject::addFormsToTemplate($template, $this, "Opération");
    }

    if (CModule::getActive("monitoringBloc") && CAppUI::gconf("monitoringBloc general active_graph_supervision")) {
      $obs_view      = "";
      $obs_sspi_view = "";

      if ($template->valueMode && $this->_id) {
        if ($this->graph_pack_id) {
          /** @var CObservationResultSet[] $list_obr */
          [$list, $grid, $graphs, $labels, $list_obr] = SupervisionGraph::getChronological($this, $this->graph_pack_id, true);

          $smarty = new CSmartyDP("modules/dPpatients");

          // Horizontal
          $smarty->assign("observation_grid", $grid);
          $smarty->assign("observation_labels", $labels);
          $smarty->assign("observation_list", $list_obr);
          $smarty->assign("in_compte_rendu", true);
          $smarty->assign("type", "perop");

          $obs_view = $smarty->fetch("inc_observation_results_grid.tpl", '', '', 0);
          $obs_view = preg_replace('`([\\n\\r])`', '', $obs_view);
        }

        if ($this->graph_pack_sspi_id) {
          /** @var CObservationResultSet[] $list_obr */
          [$list, $grid, $graphs, $labels, $list_obr] = SupervisionGraph::getChronological($this, $this->graph_pack_sspi_id, true);

          $smarty = new CSmartyDP("modules/dPpatients");

          // Horizontal
          $smarty->assign("observation_grid", $grid);
          $smarty->assign("observation_labels", $labels);
          $smarty->assign("observation_list", $list_obr);
          $smarty->assign("in_compte_rendu", true);
          $smarty->assign("type", "sspi");

          $obs_sspi_view = $smarty->fetch("inc_observation_results_grid.tpl", '', '', 0);
          $obs_sspi_view = preg_replace('`([\\n\\r])`', '', $obs_sspi_view);
        }
      }

      $template->addProperty("$operation_section - " . CAppUI::tr('COperation-Supervision board'), $obs_view, '', false);
      $template->addProperty("$operation_section - " . CAppUI::tr('COperation-SSPI supervision board'), $obs_sspi_view, '', false);
    }

    $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
  }

  function getDMIAlert() {
    if (!CModule::getActive("dmi")) {
      return null;
    }

    $this->_dmi_prescription_id = null;
    $this->_dmi_praticien_id    = null;

    /** @var CAdministrationDM[] $lines */
    $lines = $this->loadBackRefs("administrations_dm");

    if (empty($lines)) {
      return $this->_dmi_alert = "none";
    }

    $auto_validate = CAppUI::gconf("dmi CDM auto_validate");

    foreach ($lines as $_line) {
      if (!isset($this->_dmi_prescription_id)) {
        $this->_dmi_prescription_id = $_line->prescription_id;
        $this->_dmi_praticien_id    = $_line->loadRefPrescription()->praticien_id;
      }

      if (!$auto_validate && $_line->type != "purchase" && !$_line->isValidated()) {
        return $this->_dmi_alert = "warning";
      }
    }

    return $this->_dmi_alert = "ok";
  }

  function updateHeureUS() {
    $this->_heure_us = $this->duree_preop ? CMbDT::subTime($this->duree_preop, $this->time_operation) : $this->time_operation;
  }

  function getAffectation() {
    $sejour = $this->_ref_sejour;

    if (!$this->_ref_sejour) {
      $sejour = $this->loadRefSejour();
    }

    if (!$this->_datetime_best) {
      $this->loadRefPlageOp();
    }

    $affectation = new CAffectation();
    $order       = "entree";
    $where       = array();

    $where["sejour_id"] = "= '$this->sejour_id'";

    $moment = $this->_datetime_best;

    // Si l'intervention est en dehors du séjour,
    // on recadre dans le bon intervalle
    if ($moment < $sejour->entree) {
      $moment = $sejour->entree;
    }

    if ($moment > $sejour->sortie) {
      $moment = $sejour->sortie;
    }

    if (CMbDT::time(null, $moment) == "00:00:00") {
      $where["entree"] = $this->_spec->ds->prepare("<= %", CMbDT::date(null, $moment) . " 23:59:59");
      $where["sortie"] = $this->_spec->ds->prepare(">= %", CMbDT::date(null, $moment) . " 00:00:01");
    }
    else {
      $where["entree"] = $this->_spec->ds->prepare("<= %", $moment);
      $where["sortie"] = $this->_spec->ds->prepare(">= %", $moment);
    }

    $affectation->loadObject($where, $order);

    return $affectation;
  }

  /**
   * @see parent::completeLabelFields()
   */
  function completeLabelFields(&$fields, $params) {
    if (!isset($this->_from_sejour)) {
      $this->loadRefSejour()->_from_op = 1;
      $this->_ref_sejour->completeLabelFields($fields, $params);
    }
    $this->loadRefPlageOp();
    $this->loadRefAnesth();

    $new_fields = array(
      "ANESTH"  => $this->_ref_anesth->_view,
      "LIBELLE" => $this->libelle,
      "DATE"    => $this->_id ? CMbDT::dateToLocale(CMbDT::date($this->_datetime_best)) : "",
      "COTE"    => $this->getFormattedValue("cote")
    );

    $fields = array_merge($fields, $new_fields);
    if (CModule::getActive("barcodeDoc") && CAppUI::gconf("barcodeDoc general module_actif")) {
      $fields = array_merge(
        $fields,
        [
          "CODE BARRES" => "@BARCODE_" . CAppUI::gconf("barcodeDoc general prefix_INT") . $this->_id . "@",
        ]
      );
    }
  }

    /**
     * Chargement du dernier brancardage de l'intervention
     *
     * @param string $date Date du brancardage
     *
     * @return null|CBrancardage
     * @throws Exception
     */
    public function loadRefBrancardage(string $date = null): ?CBrancardage
    {
        if (!CModule::getActive("brancardage") || !CAppUI::gconf("brancardage General use_brancardage")) {
            return null;
        }

        $where = [];
        $where[] = "brancardage.context_class = '" . $this->_class . "'";

        /** @var CBrancardage[] $brancardages */
        $brancardages = $this->loadBackRefs(
            "context_ref_brancardages",
            "brancardage_id DESC",
            1,
            "brancardage_id",
            null,
            null,
            null,
            $where
        );

        if (count($brancardages) == 0) {
            $brancardage = new CBrancardage();

            $brancardage->context_id = $this->_id;
            $brancardage->context_class = $this->_class;
            $brancardage->prevu = CMbDT::dateTime();

            $brancardages = [$brancardage];
        }

        $this->_ref_brancardage = reset($brancardages);
        $this->_ref_brancardage->loadRefEtapes();
        return $this->_ref_brancardage;
    }

    /**
     * Chargement du dernier brancardage terminé.
     *
     * @return CBrancardage|null
     * @throws Exception
     */
    public function loadLastRefBrancardage(): ?CBrancardage
    {
        if (!CModule::getActive("brancardage") || !CAppUI::gconf("brancardage General use_brancardage")) {
            return null;
        }

        $ljoin = [];
        $ljoin["brancardage_etape"] = "brancardage_etape.brancardage_id = brancardage.brancardage_id";

        $where = [];
        $where[] = "brancardage.context_id = " . $this->operation_id;
        $where[] = "brancardage.context_class = '" . $this->_class . "'";
        $where[] =  CBrancardageConditionMakerUtility::makeIncludeOrExcludeByStep(CBrancardage::ARRIVEE);

        $brancardage = new CBrancardage();
        $brancardage->loadObject($where, null, null, $ljoin);
        $brancardage->loadRefEtapes();

        return $this->_ref_last_brancardage = $brancardage;
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

        $ljoin = [];
        $ljoin["brancardage_etape"] = "brancardage_etape.brancardage_id = brancardage.brancardage_id";

        $where = [];
        $where[] = "brancardage.context_class = '" . $this->_class . "'";
        $where[] =  CBrancardageConditionMakerUtility::makeIncludeOrExcludeByStep(CBrancardage::ARRIVEE, true);

        $brancardage = new CBrancardage();
        $brancardage->loadObject($where, null, null, $ljoin);
        $brancardage->loadRefEtapes();

        return $this->_ref_current_brancardage = $brancardage;
    }

    /**
     * Chargement des brancardages de l'intervention
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
     * Prior to 06/2020, stretchers were fetched using this request because object_class and object_id didn't exist.
     *
     * @return CBrancardage
     * @throws Exception
     */
    private function loadLegacyStretcher(): CBrancardage
    {
        $ds      = $this->getDS();
        $bloc_id = (!$this->_ref_salle) ? $this->loadRefSalle()->bloc_id : $this->_ref_salle->bloc_id;
        $date    = $date ?? $this->date;

        $where = [
            "brancardage.prevu"      => $ds->prepare("= ?", $date),
        ];
        if ($this->salle_id) {
            $where[] = $ds->prepare(
                "(brancardage.destination_id = ? AND brancardage.destination_class = 'CBlocOperatoire') 
                OR (brancardage.origine_id = ? AND brancardage.origine_class = 'CBlocOperatoire')",
                $bloc_id
            );
        }

        $brancardage = new CBrancardage();
        $brancardage->loadObject($where, "brancardage_id DESC", null, null);

        return $brancardage;
    }

  /**
   * Return idex type if it's special (e.g. Idex/...)
   *
   * @param CIdSante400 $idex Idex
   *
   * @return string|null
   */
  function getSpecialIdex(CIdSante400 $idex) {
    return null;
  }

  function loadPersonnelDisponible()
      {
    $listPers = [
      "iade"             => CPersonnel::loadListPers("iade"),
      "op"               => CPersonnel::loadListPers("op"),
      "op_panseuse"      => CPersonnel::loadListPers("op_panseuse"),
      "sagefemme"        => CPersonnel::loadListPers("sagefemme"),
      "manipulateur"     => CPersonnel::loadListPers("manipulateur"),
      "aux_puericulture" => CPersonnel::loadListPers("aux_puericulture"),
      "instrumentiste"   => CPersonnel::loadListPers("instrumentiste"),
      "circulante"       => CPersonnel::loadListPers("circulante"),
      "aide_soignant"    => CPersonnel::loadListPers("aide_soignant"),
      "brancardier"      => CPersonnel::loadListPers("brancardier")
    ];

    $plage = $this->_ref_plageop;

    if (!$plage) {
      $plage = $this->loadRefPlageOp();
    }

    $listPers = $plage->loadPersonnelDisponible($listPers);

    if (!$this->_ref_affectations_personnel) {
      $this->loadAffectationsPersonnel();
    }

    $affectations_personnel = $this->_ref_affectations_personnel;

    $personnel_ids = array();
    foreach ($affectations_personnel as $_aff_by_type) {
      foreach ($_aff_by_type as $_aff) {
        if ((!$_aff->debut || !$_aff->fin) && !$_aff->parent_affectation_id) {
          $personnel_ids[] = $_aff->personnel_id;
        }
      }
    }

    // Suppression de la liste des personnels déjà présents
    foreach ($listPers as $key => $persByType) {
      foreach ($persByType as $_key => $pers) {
        if (in_array($pers->_id, $personnel_ids)) {
          unset($listPers[$key][$_key]);
        }
      }
    }

    return $listPers;
  }

  /**
   * @see parent::loadAlertsNotHandled
   */
  function loadAlertsNotHandled($level = null, $tag = null, $perm = PERM_READ) {
    $alert          = new CAlert();
    $alert->handled = "0";
    $alert->setObject($this);
    $alert->level                   = $level;
    $alert->tag                     = $tag;
    $this->_refs_alerts_not_handled = $alert->loadMatchingList();

    return $this->_refs_alerts_not_handled;
  }

  /**
   * Load libelles mvsanté
   *
   * @return CLiaisonLibelleInterv[]
   */
  function loadLiaisonLibelle() {
    return $this->_ref_liaison_libelles = $this->loadBackRefs("liaison_libelle", "numero");
  }

  /**
   * Charge la commande de l'opération
   *
   * @return void
   */
  function loadRefsCommande() {
    $this->loadRefCommande("bloc");
    if (CModule::getActive("pharmacie")) {
      $this->loadRefCommande("pharmacie");
    }
  }

  /**
   * Charge la commande de l'opération
   *
   * @return CCommandeMaterielOp
   */
  function loadRefCommande($type) {
    $where         = array();
    $where["type"] = " = '$type'";
    $commandes     = $this->loadBackRefs("commande_op", null, 1, null, null, null, "", $where);

    return $this->_ref_commande_mat[$type] = count($commandes) ? reset($commandes) : new CCommandeMaterielOp();
  }

  /**
   * @inheritdoc
   */
  function loadAllDocs($params = array()) {
    $this->mapDocs($this, $params);
  }

  function loadGarrots() {
    return $this->_ref_garrots = $this->loadBackRefs('garrots', 'datetime_pose ASC');
  }

  /**
   * Get the patient of CMbobject
   *
   * @return CPatient
   */
  function getIndexablePatient() {
    return $this->loadRelPatient();
  }

  /**
   * Get the praticien_id of CMbobject
   *
   * @return CMediusers
   */
  function getIndexablePraticien() {
    return $this->loadRefChir();
  }

  /**
   * Loads the related fields for indexing datum
   *
   * @return array
   */
  function getIndexableData() {
    $this->getIndexablePraticien();
    $array["id"]          = $this->_id;
    $array["author_id"]   = $this->_ref_chir->_id;
    $array["prat_id"]     = $this->_ref_chir->_id;
    $array["title"]       = $this->libelle;
    $array["body"]        = $this->getIndexableBody("");
    $array["date"]        = str_replace("-", "/", $this->date);
    $array["function_id"] = $this->_ref_chir->function_id;
    $array["group_id"]    = $this->_ref_chir->loadRefFunction()->group_id;
    $array["patient_id"]  = $this->getIndexablePatient()->_id;
    $this->loadRefSejour();
    $array["object_ref_id"]    = $this->_ref_sejour->_id;
    $array["object_ref_class"] = $this->_ref_sejour->_class;

    return $array;
  }

  /**
   * Redesign the content of the body you will index
   *
   * @param string $content The content you want to redesign
   *
   * @return string
   */
  function getIndexableBody($content) {
    // champs textes
    $fields = $this->getTextcontent();
    foreach ($fields as $_field) {
      $content .= " " . $this->$_field;
    }

    // Actes de l'opération
    $this->loadExtCodesCCAM();
    foreach ($this->_ext_codes_ccam as $_ccam) {
      $content .= " " . $_ccam->code . " " . $_ccam->libelleCourt . " " . $_ccam->libelleLong . "\n";
    }

    return $content;
  }

  /**
   * Gets icon for current patient event
   *
   * @return array
   */
  function getEventIcon() {
    return array(
      'icon'  => 'fas fa-cut me-event-icon',
      'color' => 'steelblue',
      'title' => CAppUI::tr($this->_class),
    );
  }

  /**
   * Compte les lignes post-opératoires (non TP et non Premed)
   *
   * @return null|void
   */
  function countLinesPostOp() {
    if (!CModule::getActive("dPprescription")) {
      return null;
    }

    $this->loadRefAnesth();
    if (!$this->_ref_anesth->_id) {
      $user = CMediusers::get();
      if ($user->isAnesth()) {
        $this->_ref_anesth = $user;
      }
    }

    /* @var CPrescription $prescription */
    $prescription = $this->_ref_sejour->loadRefPrescriptionSejour();
    if ($prescription) {
      $this->_count_lines_post_op = $prescription->countLinesPostOp($this->_ref_anesth->_id);
    }
  }

  /**
   * Vérification si l'intervention est en urgence (si le module réservation est activé)
   *
   * @param dateTime $dateTime Moment pré-choisi
   *
   * @return null|bool
   */
  function isUrgence($dateTime = null) {
    if (CModule::getActive("reservation")) {
      $diff_hour_urgence = CAppUI::conf("reservation diff_hour_urgence");
      $dateTime_used     = $dateTime ?: $this->loadFirstLog()->date;
      if (abs(CMbDT::hoursRelative($this->_datetime_best, $dateTime_used)) <= $diff_hour_urgence) {
        $this->_is_urgence = true;
      }

      return $this->_is_urgence;
    }
  }

  /**
   * Get the color constant of the different steps
   *
   * @return void
   */
  public function getColorConstantsByStep() {
    // Existence de constantes preop, sspi, postop
    $sejour = $this->_ref_sejour;

    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      $this->loadRefPlageOp();
      $this->loadRefBrancardage();
      $first_brancard = $this->_ref_brancardage->loadFirstByOperation($this);
      if(isset($first_brancard))
        $first_brancard->loadRefEtapes();
      $condition      = ($this->entree_salle || isset($first_brancard->_ref_patient_pret));
    }
    else {
      $condition = $this->entree_salle;
    }

    $paires_heures = array(
      array("admission", "bloc"),
      array("sspi", "sspi_fin"),
      array("sspi_fin", "sortie")
    );

    foreach ($paires_heures as $_paire_heure) {
      $hour_min = $sejour->_ambu_time_phase[$_paire_heure[0]];
      $hour_max = $sejour->_ambu_time_phase[$_paire_heure[1]];

      $color = $sejour->calculConstantesMedicales(CMbDT::date($sejour->entree), $hour_min, $hour_max);

      $sejour->_color_constantes[$_paire_heure[0]] = $color;

      if ($condition && $_paire_heure[0] == "admission" && $color == "grey") {
        $sejour->_color_constantes["admission"] = "darkorange";
      }
      if ($this->sortie_reveil_reel && $_paire_heure[0] == "sspi" && $color == "grey") {
        $sejour->_color_constantes["sspi"] = "darkorange";
      }
      if ($sejour->sortie_reelle && $_paire_heure[0] == "sspi_fin" && $color == "grey") {
        $sejour->_color_constantes["sspi_fin"] = "darkorange";
      }
    }
  }

  /**
   * Calculate the time plus the config of the different steps
   *
   * @return array
   */
  public function calculateDelayPhase() {
    $sejour      = $this->_ref_sejour;
    $delay_phase = array();
    $time        = CMbDT::time();

    $conf_admission = CAppUI::gconf("ambu General delay_admission");
    $conf_preop     = CAppUI::gconf("ambu General delay_preop");
    $conf_bloc      = CAppUI::gconf("ambu General delay_bloc");
    $conf_sspi      = CAppUI::gconf("ambu General delay_sspi");
    $conf_postop    = CAppUI::gconf("ambu General delay_postop");
    $conf_sortie    = CAppUI::gconf("ambu General delay_sortie");

    //Calcul phase retard
    $delay_phase["admission"] = $time >= CMbDT::addTime("00:$conf_admission:00", CMbDT::time($sejour->entree_prevue)) ? 1 : 0;

    if ($this->time_operation != "00:00:00") {
      $delay_phase["preop"] = $time >= CMbDT::addTime("00:$conf_preop:00", CMbDT::time($this->_heure_us)) ? 1 : 0;
    }
    else {
      $delay_phase["preop"] = $time >= CMbDT::addTime("00:$conf_preop:00", "00:00:00") ? 1 : 0;
    }

    $delay_phase["bloc"]   = $time >= CMbDT::addTime("00:$conf_bloc:00", CMbDT::time($this->_datetime_best)) ? 1 : 0;
    $delay_phase["sspi"]   = $time >= CMbDT::addTime("00:$conf_sspi:00", CMbDT::time($this->sortie_reveil_possible)) ? 1 : 0;
    $delay_phase["postop"] = $time >= CMbDT::addTime("00:$conf_postop:00", CMbDT::time($sejour->sortie_prevue)) ? 1 : 0;
    $delay_phase["sortie"] = $time >= CMbDT::addTime("00:$conf_sortie:00", CMbDT::time($sejour->sortie_prevue)) ? 1 : 0;

    return $delay_phase;
  }

  /**
   * Obtenir la couleur des étapes et sous-étapes
   *
   * @return array
   */
  static function getColorsStepAndSubStep() {
    $colors = array(
      "step"    => array(
        "blue"   => "steelblue",
        "purple" => "blueviolet",
        "grey"   => "grey"
      ),
      "substep" => array(
        "green"  => "green",
        "orange" => "darkorange",
        "red"    => "red"
      )
    );

    return $colors;
  }

  /**
   * Obtenir les differents statut de l'étape Admission
   *
   * @param string  $class_delay Classe CSS pour retard
   * @param array   $delay_phase Temps des différentes phases de retard
   * @param CSejour $sejour      Séjour concerné
   *
   * @return array
   */
  public function getStatusAdmission($class_delay, $delay_phase, $sejour) {
    $colors = COperation::getColorsStepAndSubStep();

    //*********** admission ***********
    // - etape
    if (($sejour->entree_reelle && $sejour->pec_service) || $this->entree_salle) {
      $statut_admission = $colors['step']['blue'];
    }
    elseif ($sejour->entree_reelle && !$sejour->pec_service) {
      $statut_admission = $colors['step']['purple'];
    }
    else {
      $statut_admission = $colors['step']['grey'];
    }
    // -- sous-etapes
    // --- entrée séjour
    if ($sejour->entree_reelle) {
      $statut_entree = $colors['substep']['green'];
    }
    else {
      $statut_entree = $colors['step']['grey'];
    }

    // --- Dossier entrée préparée
    if ($sejour->entree_preparee) {
      $statut_entree_preparee = $colors['substep']['green'];
    }
    elseif ($sejour->entree_reelle) {
      $statut_entree_preparee = $colors['substep']['orange'];
    }
    else {
      $statut_entree_preparee = $colors['step']['grey'];
    }

    // --- Pec accueil
    if ($sejour->pec_accueil) {
      $statut_pec_accueil = $colors['substep']['green'];
    }
    else {
      $statut_pec_accueil = $colors['step']['grey'];
    }

    $sous_etapes_admission = array(
      "entree"           => $statut_entree,
      "formulaire"       => "",
      "prestations"      => $sejour->_color_prestation,
      "prestation_title" => $sejour->_title_prestation,
      "entree_preparee"  => $statut_entree_preparee,
      "pec_accueil"      => $statut_pec_accueil
    );
    //********************************

    $admission = array(
      'step'    => array(
        "statut" => $statut_admission,
        "delay"  => $delay_phase["admission"] && !$sejour->entree_reelle ? $class_delay : ""
      ),
      'substep' => $sous_etapes_admission
    );

    return $admission;
  }

  /**
   * Obtenir les differents statut de l'étape Preop
   *
   * @param string  $class_delay Classe CSS pour retard
   * @param array   $delay_phase Temps des différentes phases de retard
   * @param CSejour $sejour      Séjour concerné
   *
   * @return array
   */
  public function getStatusPreop($class_delay, $delay_phase, $sejour) {
    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      $this->loadRefBrancardage();
      $first_brancardage = $this->_ref_brancardage->loadFirstByOperation($this);
      if(isset($first_brancardage))
        $first_brancardage->loadRefEtapes();
      $entree_brancard   = !$this->entree_salle && !isset($first_brancardage->_ref_demande_brancardage);
    }
    else {
      $entree_brancard = !$this->entree_salle;
    }

    $colors = COperation::getColorsStepAndSubStep();

    //*********** preop ***********
    // - etape
    if ($sejour->pec_service) {
      $statut_preop = $colors['step']['purple'];
    }
    else {
      $statut_preop = $colors['step']['grey'];
    }

    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      if (isset($first_brancardage->_ref_demande_brancardage) || $this->entree_salle) {
        $statut_preop = $colors['step']['blue'];
      }
    }
    else {
      if ($this->entree_salle) {
        $statut_preop = $colors['step']['blue'];
      }
    }
    // -- sous-etapes
    // --- Pec service
    if ($sejour->pec_service) {
      $statut_pec_service = $colors['substep']['green'];
    }
    else {
      $statut_pec_service = $colors['step']['grey'];
    }

    $sous_etapes_preop = array(
      "pec_service" => $statut_pec_service,
      "formulaire"  => "",
      "medicaments" => $sejour->_color_prescription["admission"],
      "constantes"  => $sejour->_color_constantes["admission"]
    );
    //********************************

    $preop = array(
      'step'    => array(
        "statut" => $statut_preop,
        "delay"  => $delay_phase["preop"] && $sejour->entree_reelle && $this->time_operation != "00:00:00"
        && (!$sejour->pec_service || $sejour->pec_service) && $entree_brancard ? $class_delay : ""
      ),
      'substep' => $sous_etapes_preop
    );

    return $preop;
  }

  /**
   * Obtenir les differents statut de l'étape Bloc
   *
   * @param string  $class_delay Classe CSS pour retard
   * @param array   $delay_phase Temps des différentes phases de retard
   * @param CSejour $sejour      Séjour concerné
   *
   * @return array
   */
  public function getStatusBloc($class_delay, $delay_phase, $sejour) {
    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      $this->loadRefBrancardage();
      $first_brancardage = $this->_ref_brancardage->loadFirstByOperation($this);
      if(isset($first_brancardage))
        $first_brancardage->loadRefEtapes();
      $delay             = $delay_phase["bloc"] && !$this->entree_salle && $sejour->pec_service && isset($first_brancardage->_ref_arrivee);
    }
    else {
      $delay = $delay_phase["bloc"] && !$this->entree_salle && $sejour->pec_service;
    }

    $colors = COperation::getColorsStepAndSubStep();

    //*********** bloc ***********
    // - etape
    if ($this->sortie_salle) {
      $statut_bloc = $colors['step']['blue'];
    }
    elseif ($this->entree_salle) {
      $statut_bloc = $colors['step']['purple'];
    }
    else {
      $statut_bloc = $colors['step']['grey'];
    }
    // -- sous-etapes
    // --- entrée salle
    if (!$this->entree_salle && ($this->debut_op > $this->_datetime)) {
      $statut_entree_salle = $colors['substep']['orange'];
    }
    elseif ($this->entree_salle) {
      $statut_entree_salle = $colors['substep']['green'];
    }
    else {
      $statut_entree_salle = $colors['step']['grey'];
    }
    // --- intervention
    if ($this->debut_op && !$this->fin_op) {
      $statut_intervention = $colors['substep']['orange'];
    }
    elseif ($this->fin_op) {
      $statut_intervention = $colors['substep']['green'];
    }
    else {
      $statut_intervention = $colors['step']['grey'];
    }
    // --- prescription perop
    $prescription = $sejour->_ref_prescription_sejour;
    if ($prescription->_id) {
      $prescription->countLinesPeropAdministre();
    }

    if ($prescription->_id && count($prescription->_ref_prescription_lines)) {
      $count_line_perop = count($prescription->_ref_prescription_lines) + count($prescription->_ref_prescription_line_mixes);

      if ($count_line_perop == $prescription->_med_per_op_administre) {
        $statut_med_perop = $colors['substep']['green'];
      }
      else {
        $statut_med_perop = $colors['substep']['orange'];
      }
    }
    else {
      $statut_med_perop = $colors['step']['grey'];
    }

    // --- sortie salle
    if ($this->sortie_salle) {
      $statut_sortie_salle = $colors['substep']['green'];
    }
    elseif (!$this->sortie_salle && ($this->fin_op > $this->_datetime)) {
      $statut_sortie_salle = $colors['substep']['orange'];
    }
    else {
      $statut_sortie_salle = $colors['step']['grey'];
    }

    $sous_etapes_bloc = array(
      "entree_salle"      => $statut_entree_salle,
      "formulaire"        => "",
      "intervention"      => $statut_intervention,
      "medicaments"       => $sejour->_color_prescription["bloc"],
      "medicaments_perop" => $statut_med_perop,
      "sortie_salle"      => $statut_sortie_salle
    );
    //********************************

    $bloc = array(
      'step'    => array(
        "statut" => $statut_bloc,
        "delay"  => $delay ? $class_delay : ""
      ),
      'substep' => $sous_etapes_bloc
    );

    return $bloc;
  }

  /**
   * Obtenir les differents statut de l'étape SSPI
   *
   * @param string  $class_delay Classe CSS pour retard
   * @param array   $delay_phase Temps des différentes phases de retard
   * @param CSejour $sejour      Séjour concerné
   *
   * @return array
   */
  public function getStatusSspi($class_delay, $delay_phase, $sejour) {
    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      $this->loadRefBrancardage();
      $last_brancardage = $this->_ref_brancardage->loadLastByOperation($this);
      if(isset($last_brancardage))
        $last_brancardage->loadRefEtapes();
      $delay            = $delay_phase["sspi"] && !$this->entree_reveil && !$this->sortie_reveil_reel
          && !isset($last_brancardage->_ref_demande_brancardage) && $this->sortie_salle;
    }
    else {
      $delay = $delay_phase["sspi"] && !$this->entree_reveil && !$this->sortie_reveil_reel && $this->sortie_salle;
    }

    $colors = COperation::getColorsStepAndSubStep();

    //*********** sspi ***********
    // - etape
    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      $color_phase = $this->sortie_reveil_reel && isset($last_brancardage->_ref_demande_brancardage);
    }
    else {
      $color_phase = $this->sortie_reveil_reel;
    }

    if ($color_phase) {
      $statut_sspi = $colors['step']['blue'];
    }
    elseif ($this->entree_reveil) {
      $statut_sspi = $colors['step']['purple'];
    }
    else {
      $statut_sspi = $colors['step']['grey'];
    }
    // -- sous-etapes
    // --- entree salle
    if ($this->entree_reveil) {
      $statut_entree_salle = $colors['substep']['green'];
    }
    else {
      $statut_entree_salle = $colors['step']['grey'];
    }

    // --- sortie possible
    if ($this->sortie_reveil_possible) {
      $statut_sortie_possible = $colors['substep']['green'];
    }
    else {
      $statut_sortie_possible = $colors['step']['grey'];
    }

    // --- entree salle
    if ($this->sortie_reveil_reel) {
      $statut_sortie_salle = $colors['substep']['green'];
    }
    else {
      $statut_sortie_salle = $colors['step']['grey'];
    }

    $sous_etapes_sspi = array(
      "entree_salle"    => $statut_entree_salle,
      "formulaire"      => "",
      "medicaments"     => $sejour->_color_prescription["sspi"],
      "constantes"      => $sejour->_color_constantes["sspi"],
      "sortie_possible" => $statut_sortie_possible,
      "sortie_salle"    => $statut_sortie_salle
    );
    //********************************

    $sspi = array(
      'step'    => array(
        "statut" => $statut_sspi,
        "delay"  => $delay ? $class_delay : ""
      ),
      'substep' => $sous_etapes_sspi
    );

    return $sspi;
  }

  /**
   * Obtenir les differents statut de l'étape Postop
   *
   * @param string  $class_delay Classe CSS pour retard
   * @param array   $delay_phase Temps des différentes phases de retard
   * @param CSejour $sejour      Séjour concerné
   *
   * @return array
   */
  public function getStatusPostop($class_delay, $delay_phase, $sejour) {
    if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
      $this->loadRefBrancardage();
      $last_brancardage = $this->_ref_brancardage->loadLastByOperation($this);
      if(isset($last_brancardage))
        $last_brancardage->loadRefEtapes();
      $delay            = $delay_phase["postop"] && $last_brancardage->_ref_arrivee && !$sejour->confirme_user_id && !$sejour->sortie_reelle;
    }
    else {
      $delay = $delay_phase["postop"] && !$sejour->confirme_user_id && (($this->sortie_reveil_reel && !$sejour->sortie_reelle) || ($this->sortie_salle && !CAppUI::gconf('ambu SSPI show_step_sspi')));
    }

    $colors = COperation::getColorsStepAndSubStep();

    //*********** postop ***********
    // - etape
    if ($sejour->confirme_user_id) {
      $statut_postop = $colors['step']['blue'];
    }
    elseif ($this->sortie_reveil_reel || (!$this->sortie_reveil_reel && $this->sortie_sans_sspi)) {
      if (CModule::getActive("brancardage") && CAppUI::gconf("brancardage General use_brancardage")) {
        if ($last_brancardage->_ref_arrivee) {
          $statut_postop = $colors['step']['purple'];
        }
        elseif ($this->sortie_sans_sspi) {
          $statut_postop = $colors['step']['purple'];
        }
        else {
          $statut_postop = $colors['step']['grey'];
        }
      }
      else {
        $statut_postop = $colors['step']['purple'];
      }
    }
    elseif ($this->sortie_salle && !CAppUI::gconf('ambu SSPI show_step_sspi')) {
      $statut_postop = $colors['step']['purple'];
    }
    else {
      $statut_postop = $colors['step']['grey'];
    }

    // -- sous-etapes
    // --- retour service
    if ($this->sortie_reveil_reel) {
      $statut_retour_service = $colors['substep']['green'];
    }
    else {
      $statut_retour_service = $colors['step']['grey'];
    }
    // --- sortie autorisation
    if ($sejour->confirme_user_id) {
      $statut_sortie_autorisation = $colors['substep']['green'];
    }
    elseif (!$sejour->confirme_user_id && $sejour->sortie_reelle) {
      $statut_sortie_autorisation = $colors['substep']['orange'];
    }
    else {
      $statut_sortie_autorisation = $colors['step']['grey'];
    }

    $sous_etapes_postop = array(
      "retour_service"      => $statut_retour_service,
      "formulaire"          => "",
      "medicaments"         => $sejour->_color_prescription["sspi_fin"],
      "constantes"          => $sejour->_color_constantes["sspi_fin"],
      "sortie_autorisation" => $statut_sortie_autorisation
    );
    //********************************

    $postop = array(
      'step'    => array(
        "statut" => $statut_postop,
        "delay"  => $delay ? $class_delay : ""
      ),
      'substep' => $sous_etapes_postop
    );

    return $postop;
  }

  /**
   * Obtenir les differents statut de l'étape Sortie
   *
   * @param string  $class_delay Classe CSS pour retard
   * @param array   $delay_phase Temps des différentes phases de retard
   * @param CSejour $sejour      Séjour concerné
   *
   * @return array
   */
  public function getStatusSortie($class_delay, $delay_phase, $sejour) {
    $colors = COperation::getColorsStepAndSubStep();

    //*********** sortie ***********
    // - etape
    if ($sejour->sortie_reelle) {
      $statut_sortie = $colors['step']['blue'];
    }
    elseif ($sejour->confirme_user_id && !$sejour->sortie_reelle) {
      $statut_sortie = $colors['step']['purple'];
    }
    else {
      $statut_sortie = $colors['step']['grey'];
    }

    // -- sous-etapes
    // --- Dossier entrée préparée
    if ($sejour->sortie_preparee) {
      $statut_sortie_preparee = $colors['substep']['green'];
    }
    else {
      $statut_sortie_preparee = $colors['step']['grey'];
    }

    // --- Sortie séjour
    if ($sejour->sortie_reelle) {
      $statut_sortie_reelle = $colors['substep']['green'];
    }
    else {
      $statut_sortie_reelle = $colors['step']['grey'];
    }

    $sous_etapes_sortie = array(
      "formulaire"      => "",
      "sortie_preparee" => $statut_sortie_preparee,
      "sortie"          => $statut_sortie_reelle
    );
    //********************************

    $sortie = array(
      'step'    => array(
        "statut" => $statut_sortie,
        "delay"  => $delay_phase["sortie"] && !$sejour->sortie_reelle && $sejour->confirme_user_id ? $class_delay : ""
      ),
      'substep' => $sous_etapes_sortie
    );

    return $sortie;
  }

  /**
   * Obtenir le statut et les retards des différentes étapes
   *
   * @return array
   */
  public function getStatusPhase() {
    //classe pour encadré rouge des retards
    $class_delay = "delay_phase";
    $sejour      = $this->_ref_sejour;
    $delay_phase = $this->calculateDelayPhase();

    //Prestations
    $sejour->getColorPrestations();

    // Les différentes heures entre chaque étape
    $this->calculateTimingsByStep();

    // Constantes
    $this->getColorConstantsByStep();

    // Médicaments
    $sejour->getColorMedicineByStep();

    // Tableau des statuts de chaque étape et sous-étapes
    $admission = $this->getStatusAdmission($class_delay, $delay_phase, $sejour);
    $preop     = $this->getStatusPreop($class_delay, $delay_phase, $sejour);
    $bloc      = $this->getStatusBloc($class_delay, $delay_phase, $sejour);
    $sspi      = $this->getStatusSspi($class_delay, $delay_phase, $sejour);
    $postop    = $this->getStatusPostop($class_delay, $delay_phase, $sejour);
    $sortie    = $this->getStatusSortie($class_delay, $delay_phase, $sejour);

    $delay_status = array(
      "admission"   => array(
        "statut"      => $admission['step']['statut'],
        "delay"       => $admission['step']['delay'],
        "sous_etapes" => $admission['substep']
      ),
      "preop"       => array(
        "statut"      => $preop['step']['statut'],
        "delay"       => $preop['step']['delay'],
        "sous_etapes" => $preop['substep']
      ),
      "branc_first" => array(
        "statut"      => "",
        "delay"       => "",
        "sous_etapes" => ""
      ),
      "bloc"        => array(
        "statut"      => $bloc['step']['statut'],
        "delay"       => $bloc['step']['delay'],
        "sous_etapes" => $bloc['substep']
      ),
      "sspi"        => array(
        "statut"      => $sspi['step']['statut'],
        "delay"       => $sspi['step']['delay'],
        "sous_etapes" => $sspi['substep']
      ),
      "branc_last"  => array(
        "statut"      => "",
        "delay"       => "",
        "sous_etapes" => ""
      ),
      "postop"      => array(
        "statut"      => $postop['step']['statut'],
        "delay"       => $postop['step']['delay'],
        "sous_etapes" => $postop['substep']
      ),
      "sortie"      => array(
        "statut"      => $sortie['step']['statut'],
        "delay"       => $sortie['step']['delay'],
        "sous_etapes" => $sortie['substep']
      )
    );

    return $sejour->_ambu_statut_phase = $delay_status;
  }

  /**
   * Calculate the time between the different steps
   *
   * @return array
   */
  public function calculateTimingsByStep() {
    $sejour = $this->_ref_sejour;
    $phase  = array();

    // Les différentes phase entre les étapes
    $phase["admission"] = $sejour->entree_reelle ? CMbDT::time($sejour->entree_reelle) : CMbDT::time($sejour->entree_prevue);

    //bloc
    $debut_op = $this->entree_salle ? CMbDT::time($this->entree_salle) : $this->time_operation;
    $fin_op   = $this->sortie_salle ? CMbDT::time($this->sortie_salle) : CMbDT::addTime($this->temp_operation, $debut_op);

    $phase["bloc"]     = $debut_op;
    $phase["bloc_fin"] = $fin_op;

    //SSPI
    if (!$this->entree_reveil) {
      $debut_sspi = $fin_op;
    }
    else {
      $debut_sspi = CMbDT::time($this->entree_reveil);
    }

    if (!$this->sortie_reveil_possible && !$this->sortie_reveil_reel) {
      $fin_sspi = $this->sortie_sans_sspi ? CMbDT::time($this->sortie_sans_sspi) : CMbDT::time($sejour->sortie_prevue);
    }
    else {
      $fin_sspi = $this->sortie_reveil_reel ? CMbDT::time($this->sortie_reveil_reel) : CMbDT::time($this->sortie_reveil_possible);
    }

    $phase["sspi"]     = $debut_sspi;
    $phase["sspi_fin"] = $fin_sspi;

    //postop
    $phase["postop"] = CMbDT::time($sejour->sortie_prevue);
    $phase["sortie"] = $sejour->sortie_reelle ? CMbDT::time($sejour->sortie_reelle) : CMbDT::time($sejour->sortie_prevue);

    return $sejour->_ambu_time_phase = $phase;
  }

  /**
   * Get forms event of the different steps
   *
   * @return array
   */
    public function getFormsEventByStep(): array
    {
        $sejour = $this->_ref_sejour;

        $form["entree"]          = CExClass::getLatestCompletenessByEvent($sejour, "ambu_checklist_entree");
        $form["entree"]["color"] = $sejour->getColorCompletenessLastForm("ambu_checklist_entree");
        $form["preop"]           = CExClass::getLatestCompletenessByEvent($this, "ambu_checklist_pre_op");
        $form["preop"]["color"]  = $this->getColorCompletenessLastForm("ambu_checklist_pre_op") ?? $sejour->getColorCompletenessLastForm("ambu_checklist_pre_op");
        $form["bloc"]            = CExClass::getLatestCompletenessByEvent($this, "ambu_checklist_bloc");
        $form["bloc"]["color"]   = $this->getColorCompletenessLastForm("ambu_checklist_bloc") ?? $sejour->getColorCompletenessLastForm("ambu_checklist_bloc");
        $form["sspi"]            = CExClass::getLatestCompletenessByEvent($this, "ambu_checklist_sspi");
        $form["sspi"]["color"]   = $this->getColorCompletenessLastForm("ambu_checklist_sspi") ?? $sejour->getColorCompletenessLastForm("ambu_checklist_sspi");
        $form["postop"]          = CExClass::getLatestCompletenessByEvent($this, "ambu_checklist_post_op");
        $form["postop"]["color"] = $this->getColorCompletenessLastForm("ambu_checklist_post_op") ?? $sejour->getColorCompletenessLastForm("ambu_checklist_post_op");
        $form["sortie"]          = CExClass::getLatestCompletenessByEvent($sejour, "ambu_checklist_sortie");
        $form["sortie"]["color"] = $sejour->getColorCompletenessLastForm("ambu_checklist_sortie");

        return $sejour->_ambu_form = $form;
    }

    /**
     * Récupérer la couleur de complétude du dernier formulaire de l'intervention
     *
     * @param string $event_name event name
     *
     * @return string|null
     */
    public function getColorCompletenessLastForm($event_name): ?string
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
   * Get the patient status in ambu
   *
   * @return array
   */
  public function getPatientStatus() {
    $now = CMBDT::dateTime();

    $sejour = $this->_ref_sejour;

    // état du patient
    $patient_statut = array(
      "etat"       => "patient-ambu-attente",
      "etatTexte"  => "module-ambu-Waiting for admission",
      "presence"   => "patient-ambu-absent",
      "non_sortie" => "patient-ambu-non-sorti",
      "background" => "grey"
    );

    if ($sejour->entree_reelle && !$sejour->sortie_reelle) {
      $patient_statut["presence"] = "patient-ambu-present";
    }

    if ($sejour->entree_reelle && !$sejour->pec_service) {
      $patient_statut["etat"]       = "patient-ambu-preop";
      $patient_statut["background"] = "blueviolet";
      $patient_statut["etatTexte"]  = "mod-ambu-Waiting for support";
    }

    if ($sejour->entree_reelle && $sejour->pec_service) {
      $patient_statut["etat"]       = "patient-ambu-preop";
      $patient_statut["background"] = "blueviolet";
      $patient_statut["etatTexte"]  = "mod-ambu-In preparation preop";
    }

    if ($this->entree_salle) {
      $patient_statut["etat"]       = "patient-ambu-intervention";
      $patient_statut["background"] = "blueviolet";
      $patient_statut["etatTexte"]  = "mod-ambu-In intervention room";
    }

    if ($this->entree_reveil) {
      $patient_statut["etat"]       = "patient-ambu-SSPI";
      $patient_statut["background"] = "blueviolet";
      $patient_statut["etatTexte"]  = "mod-ambu-In SSPI";
    }

    if ($this->sortie_reveil_reel || (!$this->sortie_reveil_reel && $this->sortie_sans_sspi) || ($this->sortie_salle && !CAppUI::gconf('ambu SSPI show_step_sspi'))) {
      $patient_statut["etat"]       = "patient-ambu-postop";
      $patient_statut["background"] = "blueviolet";
      $patient_statut["etatTexte"]  = "mod-ambu-In postop follow-up";
    }

    if ($sejour->sortie_reelle && $sejour->sortie_reelle < $now) {
      $patient_statut["non_sortie"] = "";
      $patient_statut["etat"]       = "patient-ambu-sorti";
      $patient_statut["background"] = "steelblue";
      $patient_statut["etatTexte"]  = "mod-ambu-Patient out";
    }

    return $patient_statut;
  }

  /**
   * @inheritdoc
   */
  public function isExportable($prat_ids = array(), $date_min = null, $date_max = null, ...$additional_args) {
    $sejour = $this->loadRefSejour();
    if ($sejour && $sejour->_id) {
      return $sejour->isExportable($prat_ids, $date_min, $date_max);
    }

    $this->loadRefPlageOp();

    return (!$prat_ids || in_array($this->chir_id, $prat_ids)) && $this->_ref_plageop->isExportable($prat_ids, $date_min, $date_max);
  }

  /**
   * Chargement de la position
   *
   * @return CMediusers
   */
  function loadRefPosition() {
    return $this->_ref_position = $this->loadFwdRef("position_id", true);
  }

  /**
   * Get the last file anesthesia from intervention
   *
   * @return CFile|null
   */
  function getLastFileAnesthesia() {
    $file_category_id = CAppUI::gconf("dPsalleOp COperation category_document_pre_anesth");

    if (!$file_category_id) {
      return null;
    }

    $where                                     = array();
    $where["files_mediboard.file_category_id"] = "= '$file_category_id' ";

    $this->loadRefsFiles($where);

    CMbArray::multiSortByProps($this->_ref_files, 'file_date', SORT_ASC, 'file_id', SORT_ASC, true);

    $last_file = $this->_ref_files ? end($this->_ref_files) : null;

    return $last_file;
  }

  /**
   * Création des matériels sur l'intervention
   */
  private function createMateriels() {
    $protocoles_op_ids = explode("|", $this->_protocoles_op_ids ?? "");

    CMbArray::removeValue("", $protocoles_op_ids);

    $protocole_op = new CProtocoleOperatoire();

    $protocoles_op = $protocole_op->loadList(["protocole_operatoire_id" => CSQLDataSource::prepareIn($protocoles_op_ids)]);

    CStoredObject::massLoadBackRefs($protocoles_op, "materiels_operatoires", null, ["operation_id" => "IS NULL"]);

    foreach ($protocoles_op as $_protocole_op) {
      foreach ($_protocole_op->loadRefsMaterielsOperatoires() as $_materiel_operatoire) {
        $_materiel_operatoire->_id          = "";
        $_materiel_operatoire->operation_id = $this->_id;
        $msg                                = $_materiel_operatoire->store();
        CAppUI::setMsg($msg ?: "CMaterielOperatoire-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);
      }
    }
  }

  /**
   * Charge les matériels opératoires de l'intervention
   *
   * @param bool $with_refs    Chargement des références
   * @param bool $only_missing Seuelement les matériels manquants
   * @param bool $separate_sterilisable Séparer les matériels opératoires DM stérilisables
   *
   * @return CMaterielOperatoire[]
   */
  public function loadRefsMaterielsOperatoires($with_refs = false, $only_missing = false, $separate_sterilisable = false) {
    return $this->_refs_materiels_operatoires = CMaterielOperatoire::getList($this, $with_refs, $only_missing, $separate_sterilisable);
  }

  /**
   * Charge les protocoles opératoires appliqués via les matériels opératoires
   *
   * @return CProtocoleOperatoire[]
   */
  public function loadRefsProtocolesOperatoires() {
    $materiel_operatoire = new CMaterielOperatoire();

    $ds    = $this->getDS();
    $where = [
      "operation_id" => $ds->prepare("= ?", $this->_id)
    ];

    $protocoles_ids = $materiel_operatoire->loadColumn("protocole_operatoire_id", $where);

    CMbArray::removeValue("", $protocoles_ids);

    $protocoles_ids = array_unique($protocoles_ids);

    $protocole_op = new CProtocoleOperatoire();

    $where = [
      "protocole_operatoire_id" => CSQLDataSource::prepareIn($protocoles_ids)
    ];

    return $this->_ref_protocoles_operatoires = $protocole_op->loadList($where);
  }

    /**
     * Calcul le statut du panier :
     * - gris : non encore traité
     * - orange : panier incomplet
     * - vert : panier complet
     *
     * @throws Exception
     */
    public function computeStatusPanier(): void
    {
        $where = [];
        $ljoin = [];

        if (CModule::getActive("dmi")) {
            $where = [
                "type_usage" => "IS NULL OR type_usage != 'sterilisable'",
            ];

            $ljoin = [
                "dm" => "dm.dm_id = materiel_operatoire.dm_id",
            ];
        }

        $where["completude_panier"] = $this->getDS()->prepare(" = '1'");

        $count_materiels = count($this->loadBackIds("materiels_operatoires", null, null, null, $ljoin, $where));

        $where["status"] = "= 'ko'";

        $count_materiels_ko = count($this->loadBackIds("materiels_operatoires", null, null, null, $ljoin, $where));

        $where["status"] = "= 'ok'";

        $count_materiels_ok = count($this->loadBackIds("materiels_operatoires", null, null, null, $ljoin, $where));

        if ($count_materiels) {
            $this->_status_panier = "none";
            $this->_color_panier  = "#bbb";
        }

        if ($count_materiels_ko || ($count_materiels_ok && $count_materiels_ok < $count_materiels)) {
            $this->_status_panier = "incomplete";
            $this->_color_panier  = "orange";
        }

        if ($count_materiels && $count_materiels === $count_materiels_ok) {
            $this->_status_panier = "complete";
            $this->_color_panier  = "#080";
        }

        $this->_legend_panier = CAppUI::tr(
            "CMaterielOperatoire-Status materiel",
            $count_materiels_ok,
            $count_materiels
        );
    }

  /**
   * Charge le filtre praticien dans la préparation du matériel
   *
   * @return CMediusers
   * @throws Exception
   */
  public function loadRefPrepaChir() {
    return $this->_ref_prepa_chir = $this->loadFwdRef("_prepa_chir_id", true);
  }

  /**
   * Charge le filtre spécialité dans la préparation du matériel
   *
   * @return CFunctions
   * @throws Exception
   */
  public function loadRefPrepaSpec() {
    return $this->_ref_prepa_spec = $this->loadFwdRef("_prepa_spec_id", true);
  }


    /**
     * @return Item|null
     * @throws Exception
     */
    public function getResourceSejour(): ?Item
    {
        if (!$sejour = $this->loadRefSejour()) {
            return null;
        }

        return new Item($sejour);
    }

    /**
     * @return Item|null
     * @throws Exception
     */
    public function getResourceAnesth(): ?Item
    {
        $anesth = $this->loadRefAnesth();
        if (!$anesth->_id) {
            return null;
        }

        return new Item($anesth);
    }

  /**
   * @return Item|null
   * @throws \Ox\Core\Api\Exceptions\ApiException
   */
  public function getResourcePatient():? Item {
    if (!$patient = $this->loadRefPatient()) {
      return null;
    }

    $patient->updateBMRBHReStatus($this);

        return new Item($patient);
    }

    /**
     * @return Collection|null
     * @throws \Ox\Core\Api\Exceptions\ApiException
     */
    public function getResourceAllergies(): ?Collection
    {
        if (!$patient = $this->loadRefPatient()) {
            return null;
        }

        if (!$dossier_medical = $patient->loadRefDossierMedical()) {
            return null;
        }

        if (!$allergies = $dossier_medical->loadRefsAllergies()) {
            return null;
        }

        return new Collection($allergies);
    }

    /**
     * Récupération de la ressource Praticien
     *
     * @return Item
     * @throws Exception
     */
    public function getResourcePraticien(): ?Item
    {
        if (!$praticien = $this->loadRefChir()) {
            return null;
        }
        $res = new Item($praticien);
        $res->setType(CMediusers::RESOURCE_TYPE_PRATICIEN);

        return $res;
    }

    /**
     * Getter to fields_etiq variable
     *
     * @return array
     * @throws Exception
     */
    public static function getFieldsEtiq(): array
    {
        $fields_etiq = self::$fields_etiq;
        if (CModule::getActive("barcodeDoc") && CAppUI::gconf("barcodeDoc general module_actif")) {
            $fields_etiq[] = "CODE BARRES";
        }
        return $fields_etiq;
    }

    /**
     * Get the min and max datetime for validating a 'sortie_salle' timing and the last timing write
     *
     * @param int    $operation_id Intervention ID
     * @param string $field_timing Intervention timings field
     *
     * @return array
     * @throws Exception
     */
    public static function getValidatingTimings(int $operation_id, string $field_timing): array
    {
        $operation    = self::findOrFail($operation_id);
        $datetime_min = CMbDT::dateTime("- 6 HOURS");
        $datetime_max = CMbDT::dateTime();

        if (CModule::getActive("monitoringPatient")) {
            $time_locked_graph = CAppUI::gconf("dPsalleOp supervision_graph lock_supervision_graph");
            $hours             = ($time_locked_graph == 0) ? "2" : $time_locked_graph;

            $datetime_min = CMbDT::dateTime("- {$hours} HOURS");
            $datetime_max = CMbDT::dateTime();
        }

        $fields      = ["entree_bloc", "entree_salle", "debut_op", "fin_op", "sortie_salle", "entree_reveil"];
        $last_timing = null;

        if (in_array($field_timing, $fields)) {
            foreach ($fields as $_field) {
                if ($_field == $field_timing) {
                    break;
                }

                if ($operation->{$_field}) {
                    $last_timing = $operation->{$_field};
                }
            }
        }

        return ["min" => $datetime_min, "max" => $datetime_max, "last_timing" => $last_timing];
    }

    /**
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefLaboratoireAnapath() {
        return $this->_ref_labo_anapath = $this->loadFwdRef('labo_anapath_id', true);
    }

    /**
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefLaboratoireBacterio()
    {
        return $this->_ref_labo_bacterio = $this->loadFwdRef('labo_bacterio_id', true);
    }

    /**
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefAmpli()
    {
        return $this->_ref_ampli = $this->loadFwdRef('ampli_id', true);
    }

    /**
     * Return the checklist type
     *
     * @return string
     */
    public function getCheckListType(): ?string
    {
        $daily_check_list = new CDailyCheckList();

        $daily_check_list->object_class = $this->_class;
        $daily_check_list->object_id    = $this->_id;

        $daily_check_list->loadMatchingObjectEsc();

        return $daily_check_list->type;
    }

    public function getContextConfigMonitoring(): CMbObject
    {
        if ($this->salle_id) {
            return $this->loadRefSalle()->loadRefBloc();
        }

        return $this->_ref_sejour->loadRefEtablissement();
    }

    /**
     * @return string
     */
    public function getLibellesActesPrevus(): ?string
    {
        $this->loadRefsCodagesCCAM();
        $_op_libelle_actes = $this->libelle;
        $codes_ccam        = explode("|", $this->codes_ccam);

        if ($codes_ccam && !in_array("", $codes_ccam)) {
            $last_key          = array_key_last($codes_ccam);
            $_op_libelle_actes .= " - ";
            foreach ($codes_ccam as $key => $_code) {
                $_op_libelle_actes .= $_code;
                $_op_libelle_actes .= ($key === $last_key) ? null : " - ";
            }
        }

        return $_op_libelle_actes;
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchOperation($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return CGroups
     * @throws Exception
     */
    public function loadRelGroup(): CGroups
    {
        return $this->loadRefSejour()->loadRelGroup();
    }

    /**
     * @throws Exception
     */
    public function loadRefProtocole(): CProtocole
    {
        return $this->_ref_protocole = $this->loadFwdRef('protocole_id', true);;
    }

    public function bindTarif(): ?string
    {
        if (!$this->exec_tarif) {
            $this->exec_tarif = CAppUI::pref("use_acte_date_now") ? CMbDT::dateTime() : $this->getActeExecution();
        } elseif (CAppUI::pref("use_acte_date_now")) {
            $this->exec_tarif = CMbDT::dateTime();
        }

        return parent::bindTarif();
    }

    /**
     * @throws Exception
     */
    public function loadRefTypeAnesth(): CTypeAnesth
    {
        $this->_ref_type_anesth = $this->loadFwdRef('type_anesth', true);
        $this->_lu_type_anesth  = $this->_ref_type_anesth->name;

        return $this->_ref_type_anesth;
    }
}
