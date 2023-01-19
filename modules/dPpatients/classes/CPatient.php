<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTime;
use DateTimeImmutable;
use Exception;
use Ox\Api\CPatientUserAPI;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientObjectReceived;
use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\AppFine\Client\CAppFineClientStatusPatientUser;
use Ox\AppFine\Server\CAppFineOrderItemResponse;
use Ox\AppFine\Server\CAppFineServer;
use Ox\AppFine\Server\CPatientUser;
use Ox\AppFine\Server\CPaymentTrace;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CPerson;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSoundex2;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Exceptions\CanNotMerge;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Core\FileUtil\CMbvCardExport;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Interop\Dmp\CDmpState;
use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Addictologie\CDossierAddictologie;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDException;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPatientReunion;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Doctolib\CDoctolib;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\InstanceContexte\CInstanceContexte;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Maternite\CAllaitement;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\OpenData\CCommuneFrance;
use Ox\Mediboard\OxPyxvital\CPyxvitalCV;
use Ox\Mediboard\Patients\Exceptions\CanNotMergePatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use Ox\Mediboard\Provenance\CProvenancePatient;
use Ox\Mediboard\PyxVital\CPvCV;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CIncrementer;
use Ox\Mediboard\System\CGeoLocalisation;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\CUserLog;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Mediboard\System\IGeocodable;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Vivalto\CVivalto;
use Symfony\Component\Routing\RouterInterface;
use Throwable;

/**
 * The CPatient Class
 */
class CPatient extends CPerson implements IGeocodable, ImportableInterface, IGroupRelated
{
    /** @var string */
    public const RESOURCE_TYPE = 'patient';

    /** @var string */
    public const FIELDSET_CONTACT = "contact";

    /** @var string */
    public const FIELDSET_ACTIVITE = "activite";

    /** @var string */
    public const FIELDSET_ASSURANCE = "assurance";

    /** @var string */
    public const FIELDSET_ASSURE = "assure";

    /** @var string */
    public const FIELDSET_OWNER = "owner";

    /** @var string  */
    public const FIELDSET_STATUS = "status";

    /** @var string */
    public const RELATION_PATIENT_USERS = "patientUsers";

    /** @var string */
    public const RELATION_RGPDS = "rgpd";

    /** @var string */
    public const RELATION_CORRESPONDANTS_PATIENT = "correspondantsPatient";

    /** @var string */
    public const RELATION_DOCTOR = 'doctor';

    /** @var string */
    public const RELATION_DOSSIER_MEDICAL = "medicalRecord";

    /** @var string */
    public const RELATION_AVATAR = "avatar";

    /** @var string */
    public const RELATION_ALLERGIES = 'allergies';

    public const TAG_AUTHORIZE_APPOINTMENT = 'authorize_appointment';

    public const TAG_MES = 'tag_mes';

    public const MSSANTE_MAIL_DOMAIN = 'patient.mssante.fr';

    static $dossier_cabinet_prefix = [
        "dPcabinet"  => "?m=dPcabinet&tab=vw_dossier&patSel=",
        "dPpatients" => "?m=dPpatients&tab=vw_full_patients&patient_id=",
        "oxCabinet"  => "?m=oxCabinet&tab=vw_full_patients&patient_id=",
    ];

    // http://www.msa47.fr/front/id/msa47/S1153385038825/S1153385272497/S1153385352251
    static $libelle_exo_guess = [
        "code_exo"          => [
            //0 => null,
            4 => [
                "affection",
                "ald",
                "hors liste",
            ],
            /*3 => array(
        "stérilité",
        "prématuré",
        "HIV"
      ),*/
            5 => [
                "rente AT",
                "pension d'invalidité",
                "pension militaire",
                "enceinte",
                "maternité",
            ],
            9 => [
                "FSV",
                "FNS",
                "vieillesse",
            ],
        ],
        "_art115"           => [
            true => [
                "pension militaire",
            ],
        ],
        "_type_exoneration" => [
            "aldHorsListe"          => ["hors liste"],
            "aldListe"              => ["ald liste"],
            "aldMultiple"           => ["ald multiple"],
            "alsaceMoselle"         => ["alsace", "moselle"],
            "article115"            => ["pension militaire"],
            "fns"                   => ["fns", "fsv", "vieillesse"],
            "autreCas"              => [],
            "autreCasAlsaceMoselle" => [],
        ],
    ];

    static $rangToQualBenef = [
        "01" => "00",
        "31" => "01",
        "02" => "02",
        "11" => "06",
    ];

    /** @var string[] */
    public static $field_matching = ['sexe', 'email', 'tel2', 'cp_naissance'];

    private static $fields_etiq = [
        "DATE NAISS",
        "AGE",
        "IPP",
        "LIEU NAISSANCE",
        "PAYS_NAISSANCE",
        "CODE POSTAL NAISSANCE",
        "NOM UTILISE",
        "NOM NAISSANCE",
        "FORMULE NOM NAISSANCE",
        "PREMIER PRENOM NAISSANCE",
        "PRENOM UTILISE",
        "PRENOMS",
        "SEXE",
        "CIVILITE",
        "CIVILITE LONGUE",
        "ACCORD GENRE",
        "CODE BARRE IPP",
        "ADRESSE",
        "ADRESSE SEULE",
        "CODE POSTAL",
        "VILLE",
        "MED. TRAITANT",
        "TEL",
        "TEL PORTABLE",
        "TEL ETRANGER",
        "PAYS",
        "PREVENIR - NOM",
        "PREVENIR - PRENOM",
        "PREVENIR - ADRESSE",
        "PREVENIR - TEL",
        "PREVENIR - PORTABLE",
        "PREVENIR - CP VILLE",
        "CODE BARRE ID",
        "MATRICULE INS",
        "CODE INSEE NAISSANCE",
    ];

    // DB Table key
    public $patient_id;

    // Owner
    public $function_id;
    public $group_id;

    // Author
    public $creator_id;
    public $creation_date;

    // DB Fields
    public $source_identite_id;
    public $nom;
    public $nom_jeune_fille;
    public $prenom;
    public $prenoms;
    public $prenom_usuel;
    public $nom_soundex2;
    public $nomjf_soundex2;
    public $prenom_soundex2;
    public $naissance;
    public $deces;
    public $sexe;
    public $civilite;
    public $adresse;
    public $province;
    public $ville;
    public $cp;
    public $phone_area_code;
    public $tel;
    public $tel2;
    public $tel_autre;
    public $tel_autre_mobile;
    public $tel_pro;
    public $tel_refus;
    public $email;
    public $email_refus;
    public $vip;
    public $tutelle;
    public $don_organes;
    public $directives_anticipees;
    public $situation_famille;
    public $mdv_familiale;
    public $condition_hebergement;
    public $is_smg; //titulaire de soins médicaux gratuits
    public $_handicap;

    public $medecin_traitant_declare;
    public $medecin_traitant;
    public $medecin_traitant_exercice_place_id;
    public $pharmacie_id;
    public $incapable_majeur;
    public $ATNC;
    public $matricule;
    public $avs;

    public $code_regime;
    public $caisse_gest;
    public $centre_gest;
    public $code_gestion;
    public $centre_carte;
    public $regime_sante;
    public $rques;
    public $c2s;

    /** @var boolean "Aide médicale de l'État" */
    public $ame;
    public $ald;
    public $acs;
    public $acs_type;
    public $code_exo;
    public $libelle_exo;
    public $notes_amo;
    public $notes_amc;
    public $deb_amo;
    public $fin_amo;
    public $code_sit;
    public $regime_am;
    public $mutuelle_types_contrat;
    public $assurance_invalidite;
    public $decision_assurance_invalidite;
    public $niveau_prise_en_charge;

    public $rang_beneficiaire;
    public $qual_beneficiaire; // VitaleVision
    public $rang_naissance;
    public $fin_validite_vitale;

    public $pays;
    public $pays_insee;           // warning, this is not the insee code ! iso code here.
    public $lieu_naissance;
    public $cp_naissance;
    public $commune_naissance_insee;
    public $pays_naissance_insee;
    public $niveau_etudes;
    public $activite_pro;
    public $activite_pro_date;
    public $activite_pro_rques;
    public $profession;
    public $csp; // Catégorie socioprofessionnelle
    public $fatigue_travail;
    public $travail_hebdo;
    public $transport_jour;
    public $ressources_financieres;
    public $hebergement_precaire;
    public $patient_link_id; // Patient link
    public $status;
    //appfine
    public $validated;

    // Assuré
    public $assure_nom;
    public $assure_nom_jeune_fille;
    public $assure_prenom;
    public $assure_prenoms;
    public $assure_naissance;
    public $assure_naissance_amo;
    public $assure_sexe;
    public $assure_civilite;
    public $assure_adresse;
    public $assure_ville;
    public $assure_cp;
    public $assure_tel;
    public $assure_tel2;
    public $assure_pays;
    public $assure_pays_insee;
    public $assure_cp_naissance;
    public $assure_lieu_naissance;
    public $assure_pays_naissance_insee;
    public $assure_profession;
    public $assure_rques;
    public $assure_matricule;
    public $assure_rang_naissance;

    // Other fields
    public $date_lecture_vitale;
    public $allow_sms_notification;
    public $allow_sisra_send;
    public $_pays_naissance;
    public $_pays_naissance_insee;
    public $_assure_pays_naissance_insee;

    public $allow_email;
    public $allow_pers_prevenir;
    public $allow_pers_confiance;

    // Behaviour fields
    public $_anonyme;
    public $_generate_IPP   = true;
    public $_allow_matching = false;

    // Form fields
    public $_vip;
    public $_prenom_2;
    public $_prenom_3;
    public $_prenom_4;
    public $_annees;
    public $_mois;
    public $_jours;
    public $_age;
    public $_age_assure;
    public $_civilite;
    public $_civilite_long;
    public $_assure_civilite;
    public $_assure_civilite_long;
    public $_longview;
    public $_art115;
    public $_type_exoneration;
    public $_exoneration;
    public $_can_see_photo;
    public $_csp_view;
    public $_nb_enfants;
    public $_overweight;
    public $_age_min;
    public $_age_max;
    public $_taille;
    public $_poids;
    public $_age_epoque;
    public $_naissance;
    public $_naissance_id;
    public $_sejour_maman_id;
    public $_important_files_docs;
    public $_antecedents_guid;
    public $_bmr_bhre_status;
    public $_nb_printers;
    public $_count_modeles_etiq;
    public $_matricule;
    public $_avs;
    public $_consent_terresante;
    public $_consent_dmp;
    public $_consent_mssante_patient;
    public $_consent_mssante_pro;
    public $_assure_prenom_2;
    public $_assure_prenom_3;
    public $_assure_prenom_4;
    public $_homonyme;
    public $_douteux;
    public $_douteux_stored;
    public $_fictif;
    public $_fictif_stored;
    public $_mode_obtention = CSourceIdentite::MODE_OBTENTION_MANUEL;
    public $_identity_proof_type_id;
    /** @var bool */
    public $_search_free = false;
    /** @var string */
    public $_mssante_email;
    /** @var string */
    public $_mssante_email_alias;

    /** @var string */
    public $_code_insee;

    // Vitale behaviour
    public $_bind_vitale;
    public $_update_vitale;
    public $_id_vitale;
    public $_vitale_lastname;
    public $_vitale_birthname;
    public $_vitale_firstname;
    public $_vitale_birthdate;
    public $_vitale_nir_certifie;
    public $_vitale_birthrank;
    public $_vitale_quality;
    public $_vitale_nir;
    public $_vitale_code_regime;
    public $_vitale_code_gestion;
    public $_vitale_code_caisse;
    public $_vitale_code_centre;

    // AppFine
    public $_responsable_compte;
    public $_correspond_responsable;

    // Navigation Fields
    public $_dossier_cabinet_url;

    public $_prenoms; // multiple
    public $_nom_naissance; // +/- = nom_jeune_fille
    public $_adresse_ligne2;
    public $_adresse_ligne3;
    public $_pays;
    public $_IPP;

    // Interop Fields
    public $_no_synchro_eai = false;
    public $_fusion; // fusion
    public $_fusion_doctolib; // fusion doctolib
    //hl7 field
    public $_status_no_guess = false; // Status du patient renseigné directement via une interface
    /** @var CCorrespondant */
    public $_current_correspondant;
    /** @var CCorrespondant */
    public $_delete_correspondant;
    public $_disable_insi_identity_source;
    /** @var bool INS temporaire pour les identités inconnues */
    public $_ins_temporaire = false;
    // DMP
    public $_dmp_create;
    /** @var CDmpState $_ref_state_dmp */
    public $_ref_state_dmp;
    // Accès urgence
    public $_dmp_urgence_15;
    // Accès bris de glace
    public $_dmp_urgence_PS;
    public $_dmp_medecin_traitant;

    /** @var  CMediusers */
    public $_dmp_mediuser;
    public $_dmp_reactivation_dmp;
    public $_dmp_reason_close;

    public $_reason_state;
    public $_doubloon_ids = [];

    /** @var CPatient */
    public $_patient_elimine; // fusion

    public $_nb_docs;
    public $_total_docs;

    public $_dashboard_task_id;
    public $_evenement_medical_id;

    /** @var CSejour[] */
    public $_ref_sejours = [];

    /** @var COperation[] */
    public $_ref_operations = [];

    /** @var CConsultation[] */
    public $_ref_consultations = [];

    /** @var CPrescription[] */
    public $_ref_prescriptions = [];

    /** @var CGrossesse[] */
    public $_ref_grossesses = [];

    /** @var CGrossesse */
    public $_ref_next_grossesse;

    /** @var CGrossesse */
    public $_ref_last_grossesse;

    /** @var CAllaitement[] */
    public $_ref_allaitements = [];

    /** @var CAllaitement */
    public $_ref_last_allaitement;

    /** @var CNaissance */
    public $_ref_naissance;

    /** @var CConstantesMedicales */
    public $_ref_first_constantes;

    /** @var CConstantesMedicales */
    public $_ref_last_constantes;

    /** @var CConstantesMedicales[] */
    public $_list_constantes_medicales = [];

    /** @var CPatient[] */
    public $_ref_patient_links = [];

    /** @var CFile */
    public $_ref_photo_identite;

    /** @var CAffectation */
    public $_ref_curr_affectation;

    /** @var CAffectation */
    public $_ref_next_affectation;

    /** @var CMedecin */
    public $_ref_medecin_traitant;

    /** @var CMedecinExercicePlace */
    public $_ref_medecin_traitant_exercice_place;

    /** @var CMedecin */
    public $_ref_pharmacie;

    /** @var CCorrespondant[] */
    public $_ref_medecins_correspondants = [];

    /** @var CCorrespondantPatient[] */
    public $_ref_correspondants_patient = [];
    /** @var CCorrespondantPatient */
    public $_ref_assurance_patient;
    public $_ref_cp_by_relation;
    public $_ref_tuteur;

    /** @var CFunctions */
    public $_ref_function;
    /** @var CGroups */
    public $_ref_group;
    /** @var CDossierMedical */
    public $_ref_dossier_medical;
    /** @var CDossierTiers[] */
    public $_ref_dossiers_tiers = [];
    /** @var CIdSante400 */
    public $_ref_IPP;
    /** @var CIdSante400 */
    public $_ref_vitale_idsante400;
    /** @var CConstantesMedicales */
    public $_ref_constantes_medicales;
    /** @var array */
    public $_latest_constantes_dates;
    /** @var CConstantesMedicales[] */
    public $_refs_all_contantes_medicales = [];
    /** @var CINSPatient[] */
    public $_refs_ins = [];
    /** @var CPatientINSNIR */
    public $_ref_patient_ins_nir;
    /** @var CINSPatient */
    public $_ref_last_ins;
    /** @var CInstanceContexte */
    public $_ref_instance_contextes;
    /** @var CPatientUserAPI */
    public $_ref_patient_user_api;

    /** @var CPatientUser[] */
    public $_ref_patient_users;
    /** @var CPatientUser */
    public $_ref_last_patient_user;
    /** @var CUser */
    public $_ref_user;
    public $_status_patient_user;
    public $_ref_status_patient_user;
    /** @var CAppFineOrderItemResponse[] */
    public $_orders = [];
    /** @var  integer */
    public $_count_order_item_responses;
    public $_folder_complete = 0;
    /** @var  integer */
    public $_count_todo;
    /** @var float[] */
    public $_count_degrees = [];
    /** @var CFile[] */
    public $_ref_external_files = [];
    /** @var CFile[] */
    public $_ref_external_files_read = [];
    /** @var CFile[] */
    public $_ref_external_files_not_read = [];
    /** @var CAppFineClientOrderItem[] */
    public $_ref_orders_item = [];
    /** @var CAppFineClientObjectReceived[] */
    public $_ref_objects_received = [];
    /** @var CInjection[] */
    public $_ref_vaccins = [];
    /** @var CPyxvitalCV[]|CPvCV[] */
    public $_refs_cvs;
    /** @var CProvenancePatient */
    public $_ref_provenance_patient;

    /** @var CSourceIdentite */
    public $_ref_source_identite;

    /** @var CSourceIdentite[] */
    public $_ref_sources_identite;

    /**
     * `CPaymentTrace` reference objects.
     *
     * @public
     * @var CPaymentTrace[]
     * @see CPaymentTrace
     */
    public $_ref_payment_traces;

    // Variables provenance
    public $_provenance_id;
    public $_commentaire_prov;

    public $_all_docs;

    public $_count_ins;
    public $_count_consult_prat;
    public $_count_all_sejours = 0;

    // Distant fields
    public $_ref_praticiens; // Praticiens ayant participé à la pec du patient

    /** @var  CPatientState[] */
    public $_ref_patient_states = [];

    /** @var CPatientGroup[] Links between the patient and a group */
    public $_ref_patient_groups = [];

    /** @var array Sharing groups ordered by status (allowed, denied, not asked) */
    public $_sharing_groups;

    /** @var CInclusionProgramme[] */
    public $_refs_inclusions_programme = [];

    /** @var CDirectiveAnticipee[] */
    public $_refs_directives_anticipees;
    public $_ref_last_directive_anticipee;

    /** @var CVerrouDossierPatient */
    public $_ref_last_verrou_dossier;

    /** @var CBMRBHRe */
    public $_ref_bmr_bhre;

    /** @var CPatientFamilyLink */
    public $_ref_family_patient;

    /** @var CGeoLocalisation */
    public $_ref_geolocalisation;

    /** @var CDossierAddictologie */
    public $_ref_last_dossier_addictologie;

    /** @var CPatientReunion[] */
    public $_refs_patient_reunion = [];

    /** @var CPatientUser */
    public $_ref_first_patient_user;

    /** @var CPatientHandicap[] */
    public $_refs_patient_handicaps = [];

    /** @var CEvenementPatient */
    public $_ref_evenement_alerte;

    /** @var string */
    public $_source_nom;

    /** @var string */
    public $_source_nom_jeune_fille;

    /** @var string */
    public $_source_prenom;

    /** @var string */
    public $_source_prenom_usuel;

    /** @var string */
    public $_source_prenoms;

    /** @var string */
    public $_source_sexe;

    /** @var string */
    public $_source_civilite;

    /** @var string */
    public $_source_naissance;

    /** @var string */
    public $_source_naissance_corrigee;

    /** @var string */
    public $_source_lieu_naissance;

    /** @var string */
    public $_source_commune_naissance_insee;

    /** @var string */
    public $_source_cp_naissance;

    /** @var string */
    public $_source_pays_naissance_insee;

    /** @var string */
    public $_source__pays_naissance_insee;

    /** @var string */
    public $_source__code_insee;

    /** @var string */
    public $_source__date_fin_validite;

    /** @var int */
    public $_source__validate_identity;

    /** @var string */
    public $_date_fin_validite;

    /** @var string */
    public $_oid;

    /** @var string */
    public $_ins_type;

    /** @var string */
    public $_ins;

    /** @var bool */
    public $_is_link_ens;

    /** @var string */
    public $_previous_ins;

    /** @var int */
    public $_map_source_form_fields;

    /** @var int */
    public $_force_manual_source;

    /** @var bool */
    public $_force_new_manual_source;

    /** @var bool */
    public $_force_new_insi_source = true;

    /** @var int */
    public $_copy_file_id;

    /** @var int */
    public $_source__complete_traits_stricts;

    static function massLoadRefsAffectations($patients, $date = null)
    {
        if (!count($patients)) {
            return;
        }

        if (!$date) {
            $date = CMbDT::dateTime();
        }

        $patients_ids = CMbArray::pluck($patients, "_id");
        CMbArray::removeValue("", $patients_ids);
        $patients_ids = array_unique($patients_ids);

        $ds          = CSQLDataSource::get("std");
        $group       = CGroups::loadCurrent();
        $affectation = new CAffectation();

        // Récupération des séjours
        $request = new CRequest();
        $request->addSelect("patient_id, GROUP_CONCAT(sejour_id)");
        $request->addTable("sejour");
        $request->addWhere(
            [
                "group_id"   => "= '$group->_id'",
                "patient_id" => CSQLDataSource::prepareIn($patients_ids),
            ]
        );
        $request->addGroup("patient_id");

        $sejours_ids_by_patient = $ds->loadHashList($request->makeSelect());

        $sejours_ids_by_patients_ids = [];
        $sejours_ids                 = [];

        foreach ($sejours_ids_by_patient as $patient_id => $_sejours_ids) {
            $_sejours_ids                             = explode(",", $_sejours_ids);
            $sejours_ids_by_patients_ids[$patient_id] = $_sejours_ids;
            $sejours_ids                              = array_merge($sejours_ids, $_sejours_ids);
        }

        // Affectation courante
        $where = [
            "sejour_id"          => CSQLDataSource::prepareIn($sejours_ids),
            "affectation.entree" => "< '$date'",
            "affectation.sortie" => ">= '$date'",
        ];

        $affectations = $affectation->loadList($where);

        CAffectation::massUpdateView($affectations);

        foreach ($patients as $_patient) {
            $_patient->_ref_curr_affectation = new CAffectation();
            foreach ($affectations as $_affectation) {
                if (!in_array($_affectation->sejour_id, $sejours_ids_by_patients_ids[$_patient->_id])) {
                    continue;
                }
                $_patient->_ref_curr_affectation = $_affectation;
            }
        }

        // Prochaine affectation
        $where = [
            "sejour_id"          => CSQLDataSource::prepareIn($sejours_ids),
            "affectation.entree" => "> '$date'",
            "affectation.sortie" => ">= '$date'",
        ];

        $affectations = $affectation->loadList($where);

        CAffectation::massUpdateView($affectations);

        foreach ($patients as $_patient) {
            $_patient->_ref_next_affectation = new CAffectation();
            foreach ($affectations as $_affectation) {
                if (!in_array($_affectation->sejour_id, $sejours_ids_by_patients_ids[$_patient->_id])) {
                    continue;
                }
                $_patient->_ref_next_affectation = $_affectation;
            }
        }
    }

    /**
     * Calcul du nombre de photos d'identité présentes
     *
     * @param CPatient $patients le patient souhaité
     *
     * @return void
     */
    static function massCountPhotoIdentite($patients)
    {
        CFile::massCountNamed($patients, "identite.jpg");
    }

    /**
     * Get the importants files and doc of a patient
     *
     * @param CPatient $patient The patient
     *
     * @return void
     */
    static function getImportantFilesDocs($patient)
    {
        $important_categories = CFilesCategory::getImportantCategories();
        $patient->mapImportantFilesDocs($patient, $important_categories);

        foreach ($patient->_ref_consultations as $_consult) {
            $patient->mapImportantFilesDocs($_consult, $important_categories);
        }

        foreach ($patient->_ref_sejours as $_sejour) {
            $patient->mapImportantFilesDocs($_sejour, $important_categories);

            foreach ($_sejour->_ref_operations as $_op) {
                $patient->mapImportantFilesDocs($_op, $important_categories);
            }
        }

        if (is_countable($patient->_important_files_docs) && count($patient->_important_files_docs)) {
            ksort($patient->_important_files_docs);
            foreach ($patient->_important_files_docs as $key => $_files_docs_by_cat) {
                krsort($patient->_important_files_docs[$key]);
            }
        }
    }

    /**
     * Map the docs items of important categories to an object
     *
     * @param CMbObject $object               Reference object
     * @param array     $important_categories List of important categories ids
     *
     * @return void
     */
    function mapImportantFilesDocs($object, $important_categories)
    {
        foreach ($object->_ref_files as $_file) {
            if (array_key_exists($_file->file_category_id, $important_categories)) {
                $_file->_ref_object                                     = $object;
                $category_name                                          = $important_categories[$_file->file_category_id]->nom;
                $file_key                                               = $_file->file_date . "-" . $_file->_guid;
                $this->_important_files_docs[$category_name][$file_key] = $_file;
            }
        }

        foreach ($object->_ref_documents as $_doc) {
            if (array_key_exists($_doc->file_category_id, $important_categories)) {
                $_doc->_ref_object                                     = $object;
                $category_name                                         = $important_categories[$_doc->file_category_id]->nom;
                $doc_key                                               = $_doc->creation_date . "-" . $_doc->_guid;
                $this->_important_files_docs[$category_name][$doc_key] = $_doc;
            }
        }
    }

    /**
     * Sort backprops for a patient. Return an array with all the backprops which have been merged.
     * Return an array with the id of the object for key and new for value if the object have already changed his
     * patient_field value
     *
     * @param string   $back_name Name of the backprop
     * @param CPatient $patient   Old patient
     *
     * @return array
     */
    static function checkBackRefsOwner($back_name, $patient)
    {
        $return = [];

        $ds = CSQLDataSource::get('std');
        if (array_key_exists('slave', CSQLDataSource::$dataSources)) {
            $ds = CSQLDataSource::get('slave');
        }

        // Load the last id_sante400 whith 'merged' for the patient
        $id_sante400 = new CIdSante400();
        $where       = [
            'object_class' => "= 'CPatient'",
            'object_id'    => $ds->prepare('= ?', $patient->_id),
            'tag'          => "= 'merged'",
        ];

        $merged_id = $id_sante400->loadList($where, 'last_update DESC', 1);

        // Get the patient_field for the backprop's class
        $back_spec     = $patient->makeBackSpec($back_name);
        $patient_field = $back_spec->field;

        // Load all the logs of the last merge for the patient and mark them as new
        $last_merged = reset($merged_id);
        $log         = new CUserLog();
        $date        = $last_merged->last_update;
        $where       = [
            'object_class' => $ds->prepare('= ?', $back_spec->class),
            'date'         => $ds->prepare(
                'BETWEEN ?1 AND ?2',
                CMbDT::dateTime("-10 MIN", $date),
                CMbDT::dateTime("+10 MIN", $date)
            ),
            'fields'       => $ds->prepareLike("%$patient_field%"),
            'extra'        => $ds->prepareLike("%\"$patient_field\":\"{$last_merged->id400}\"%"),
        ];
        $logs        = $log->loadList($where);

        foreach ($logs as $_id => $_log) {
            $return[$_log->object_id] = 'new';
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    static function isGeocodable()
    {
        return false;
    }

    /**
     * Vérifie le droit de fusion pour l'utilisateur connecté
     *
     * @return bool
     */
    static function canMerge()
    {
        global $can;

        return (!CAppUI::gconf("dPpatients identitovigilance merge_only_admin") || $can->admin) && $can->edit;
    }

    /**
     * Transforms an array of ids of relatives to patient objects
     *
     * @param array $relatives_array - array of relatives
     *
     * @return CPatient[] - an array of CPatient objects
     * @throws CMbModelNotFoundException
     */
    public static function transformRelativesPatient($relatives_array)
    {
        $patients = ["bros" => null, "children" => null, "parent_1" => null, "parent_2" => null];

        if (isset($relatives_array["bros"])) {
            foreach ($relatives_array["bros"] as $_bro) {
                $patients["bros"][] = [CPatient::findOrFail($_bro[0]), $_bro[1]];
            }
        }
        if (isset($relatives_array["children"])) {
            foreach ($relatives_array["children"] as $_child) {
                $patients["children"][] = [CPatient::findOrFail($_child[0]), $_child[1]];
            }
        }
        if (isset($relatives_array["parent_1"]) && $relatives_array["parent_1"][0]) {
            $patients["parent_1"] = [
                CPatient::findOrFail($relatives_array["parent_1"][0]),
                $relatives_array["parent_1"][1],
            ];
        }
        if (isset($relatives_array["parent_2"]) && $relatives_array["parent_2"][0]) {
            $patients["parent_2"] = [
                CPatient::findOrFail($relatives_array["parent_2"][0]),
                $relatives_array["parent_2"][1],
            ];
        }

        return $patients;
    }

    /**
     * Counts the relative using an array
     *
     * @param array $array - relatives ids
     *
     * @return int - the amount of relatives
     */
    public static function countBloodRelatives($array)
    {
        if (!is_array($array)) {
            throw new CMbException("Can't count anything ...");
        }

        $total = 0;
        $total += (isset($array["bros"]) && $array["bros"]) ? sizeof($array["bros"]) : 0;
        $total += (isset($array["children"]) && $array["children"]) ? sizeof($array["children"]) : 0;
        $total += (isset($array["parent_1"]) && isset($array["parent_1"][0])) ? 1 : 0;
        $total += (isset($array["parent_2"]) && isset($array["parent_2"][0])) ? 1 : 0;

        return $total;
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->table       = 'patients';
        $spec->key         = 'patient_id';
        $spec->measureable = true;
        $spec->merge_type  = 'check';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('dossierpatient_patients', ["patient_id" => $this->_id]);
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["function_id"]              = "ref class|CFunctions back|patients_function fieldset|owner";
        $props["group_id"]                 = "ref class|CGroups back|patients fieldset|owner";
        $props["creator_id"]               = "ref class|CMediusers back|patients";
        $props["creation_date"]            = "dateTime";
        $props['source_identite_id']       = 'ref class|CSourceIdentite back|patients unlink';
        $props["nom"]                      = "str confidential seekable|begin index fieldset|default";
        $props["prenom"]                   = "str notNull seekable|begin index fieldset|default";
        $props['prenoms']                  = "str fieldset|default";
        $props["prenom_usuel"]             = "str fieldset|default";
        $props["nom_jeune_fille"]          = "str confidential seekable|begin index fieldset|default notNull";
        $props["nom_soundex2"]             = "str index";
        $props["prenom_soundex2"]          = "str index";
        $props["nomjf_soundex2"]           = "str index";
        $props["medecin_traitant_declare"] = "enum list|1|0| default| fieldset|extra";
        $props["medecin_traitant"]         = "ref class|CMedecin back|patients_traites fieldset|extra";
        $props['medecin_traitant_exercice_place_id']
                                           = 'ref class|CMedecinExercicePlace back|patients_traites fieldset|extra';
        $props["pharmacie_id"]             = "ref class|CMedecin back|patients_pharmacie";
        $conf_insee                        = CAppUI::gconf("dPpatients CPatient check_code_insee");
        $props["matricule"]                = ($conf_insee ? "code insee confidential mask|9S99S99S9xS999S999S99" : "str maxLength|15") . " fieldset|extra";
        $props["code_regime"]              = "numchar length|2 fieldset|extra";
        $props["caisse_gest"]              = "numchar length|3 fieldset|extra";
        $props["centre_gest"]              = "numchar length|4 fieldset|extra";
        $props["code_gestion"]             = "str length|2 fieldset|extra";
        $props["centre_carte"]             = "numchar length|4 fieldset|extra";
        $props["regime_sante"]             = "str fieldset|extra";
        $props["sexe"]                     = "enum list|i|m|f fieldset|default";
        $props["civilite"]                 = "enum list|m|mme|mlle|enf|dr|pr|me|vve fieldset|default";
        $addr_mandatory                    = CAppUI::gconf(
            "dPpatients CPatient addr_patient_mandatory"
        ) ? " notNull" : "";
        $props["adresse"]                  = "text$addr_mandatory confidential fieldset|contact";
        $props["province"]                 = "str maxLength|40 fieldset|contact";
        $props["is_smg"]                   = "bool default|0 fieldset|extra";
        $props["ville"]                    = "str$addr_mandatory confidential seekable|begin fieldset|contact";
        $cp_mandatory                      = CAppUI::gconf(
            "dPpatients CPatient cp_patient_mandatory"
        ) ? " notNull" : "";
        [$min_cp, $max_cp] = CPatient::getLimitCharCP();
        $props["cp"]                    = "str$cp_mandatory minLength|$min_cp maxLength|$max_cp confidential fieldset|contact";
        $props['phone_area_code']       = 'numchar length|2 default|' . CAppUI::conf(
                'system phone_area_code'
            ) . ' fieldset|contact';
        $tel_mandatory                  = CAppUI::gconf("dPpatients CPatient tel_patient_mandatory") ? " notNull" : "";
        $props["tel"]                   = "phone$tel_mandatory confidential fieldset|contact";
        $props["tel2"]                  = "phone confidential fieldset|contact";
        $props["tel_autre"]             = "str maxLength|20 fieldset|contact";
        $props["tel_autre_mobile"]      = "str maxLength|20 fieldset|contact";
        $props["tel_pro"]               = "phone confidential fieldset|contact";
        $props["tel_refus"]             = "bool default|0 fieldset|contact";
        $props["email"]                 = "email confidential fieldset|contact";
        $props["email_refus"]           = "bool default|0 fieldset|contact";
        $props["vip"]                   = "bool default|0 fieldset|default";
        $props["situation_famille"]     = "enum list|S|M|G|P|D|W|A fieldset|extra";
        $props["mdv_familiale"]         = "enum list|S|C|A fieldset|extra";
        $props["condition_hebergement"] = "enum list|locataire|proprietaire|precaire fieldset|extra";
        $tutelle_mandatory              = CAppUI::gconf("dPpatients CPatient tutelle_mandatory");
        $props["tutelle"]               = "enum list|aucune|tutelle|curatelle " . ($tutelle_mandatory ? "notNull" : "default|aucune") . ' fieldset|extra';
        $props["don_organes"]           = "enum list|non_renseigne|accord|pas_accord default|non_renseigne fieldset|extra";
        $props["directives_anticipees"] = "enum list|1|0|unknown default|unknown fieldset|extra";
        $props["incapable_majeur"]      = "bool fieldset|extra";
        $props["ATNC"]                  = "bool fieldset|extra";
        $props["avs"]                   = "str maxLength|16 fieldset|extra";// mask|999.99.999.999";
        $props["_handicap"]             = "str";

        $props["naissance"] = "birthDate notNull fieldset|default";

        $props["deces"]                         = "dateTime fieldset|default";
        $props["rques"]                         = "text fieldset|default";
        $props["c2s"]                           = "bool fieldset|assurance";
        $props["ame"]                           = "bool fieldset|assurance";
        $props["ald"]                           = "bool fieldset|assurance";
        $props['acs']                           = 'bool fieldset|assurance';
        $props['acs_type']                      = 'enum list|none|a|b|c default|none fieldset|assurance';
        $props["code_exo"]                      = "enum list|0|4|5|9 default|0 fieldset|assurance";
        $props["libelle_exo"]                   = "text fieldset|assurance";
        $props["deb_amo"]                       = "date fieldset|assurance";
        $props["fin_amo"]                       = "date fieldset|assurance";
        $props["notes_amo"]                     = "text fieldset|assurance";
        $props["notes_amc"]                     = "text fieldset|assurance";
        $props["rang_beneficiaire"]             = "enum list|01|02|09|11|12|13|14|15|16|31 fieldset|assurance";
        $props["qual_beneficiaire"]             = "enum list|00|01|02|03|04|05|06|07|08|09 fieldset|assurance";
        $props["rang_naissance"]                = "enum list|1|2|3|4|5|6 default|1 fieldset|assurance";
        $props["fin_validite_vitale"]           = "date fieldset|assurance";
        $props["code_sit"]                      = "numchar length|4 fieldset|assurance";
        $props["regime_am"]                     = "bool default|0 fieldset|assurance";
        $props["mutuelle_types_contrat"]        = "text fieldset|assurance";
        $props["assurance_invalidite"]          = "enum list|oui|non|en_cours fieldset|assurance";
        $props["decision_assurance_invalidite"] = "enum list|partielle|totale fieldset|assurance";
        $props["niveau_prise_en_charge"]        = "enum list|leger|moyen|intensif fieldset|assurance";

        $props["pays"]                    = "str$addr_mandatory fieldset|contact";
        $props["pays_insee"]              = "numchar length|3 fieldset|extra";
        $props["lieu_naissance"]          = "str fieldset|extra";
        $props["cp_naissance"]            = "str minLength|$min_cp maxLength|$max_cp confidential fieldset|extra";
        $props["commune_naissance_insee"] = "str length|5 fieldset|extra";
        $props["pays_naissance_insee"]    = "numchar length|3 fieldset|extra";
        $props["niveau_etudes"]           = "enum list|ns|p|c|l|es fieldset|activite";
        $props["activite_pro"]            = "enum list|a|c|f|cp|e|i|r fieldset|activite";
        $props["activite_pro_date"]       = "date fieldset|activite";
        $props["activite_pro_rques"]      = "text fieldset|activite";
        $props["profession"]              = "str autocomplete fieldset|activite";
        $props["csp"]                     = "numchar length|2 fieldset|activite";
        $props["fatigue_travail"]         = "bool fieldset|activite";
        $props["travail_hebdo"]           = "num pos fieldset|activite";
        $props["transport_jour"]          = "num pos fieldset|activite";
        $props["ressources_financieres"]  = "enum list|tra|cho|rsa|api|non fieldset|activite";
        $props["hebergement_precaire"]    = "bool fieldset|activite";
        $props["patient_link_id"]         = "ref class|CPatient back|patient_links";
        $props["status"]                  = "enum list|VIDE|PROV|VALI|RECUP|QUAL fieldset|default";
        $props["validated"]               = "bool default|0 fieldset|default";

        $props["assure_nom"]                  = "str confidential fieldset|assure";
        $props["assure_prenom"]               = "str fieldset|assure";
        $props["assure_prenoms"]              = "str fieldset|assure";
        $props["assure_nom_jeune_fille"]      = "str confidential fieldset|assure";
        $props["assure_sexe"]                 = "enum list|m|f fieldset|assure";
        $props["assure_civilite"]             = "enum list|m|mme|mlle|enf|dr|pr|me|vve fieldset|assure";
        $props["assure_naissance"]            = "birthDate confidential mask|99/99/9999 format|$3-$2-$1 fieldset|assure";
        $props["assure_naissance_amo"]        = "birthDate confidential mask|99/99/9999 format|$3-$2-$1 fieldset|assure";
        $props["assure_adresse"]              = "text confidential fieldset|assure";
        $props["assure_ville"]                = "str confidential fieldset|assure";
        $props["assure_cp"]                   = "str minLength|$min_cp maxLength|$max_cp confidential fieldset|assure";
        $props["assure_tel"]                  = "phone confidential fieldset|assure";
        $props["assure_tel2"]                 = "phone confidential fieldset|assure";
        $props["assure_pays"]                 = "str fieldset|assure";
        $props["assure_pays_insee"]           = "numchar length|3 fieldset|assure";
        $props["assure_lieu_naissance"]       = "str fieldset|assure";
        $props["assure_cp_naissance"]         = "str minLength|$min_cp maxLength|$max_cp confidential fieldset|assure";
        $props["assure_pays_naissance_insee"] = "numchar length|3 fieldset|assure";
        $props["assure_profession"]           = "str autocomplete fieldset|assure";
        $props["assure_rques"]                = "text fieldset|assure";
        $props["assure_matricule"]            = $conf_insee ? "code insee confidential mask|9S99S99S9xS999S999S99" : "str maxLength|15 fieldset|assure";
        $props['assure_rang_naissance']       = 'enum list|1|2|3|4|5|6 default|1 fieldset|assure';

        $props["date_lecture_vitale"]          = "dateTime";
        $props["allow_sms_notification"]       = "bool default|0 fieldset|contact";
        $props["allow_sisra_send"]             = "bool default|1";
        $props["_id_vitale"]                   = "num";
        $props["_pays_naissance_insee"]        = "str fieldset|default";
        $props["_assure_pays_naissance_insee"] = "str";
        $props["_art115"]                      = "bool";
        $props['_code_insee']                  = 'str length|5 fieldset|extra notNull';

        $allow_email = CAppUI::gconf("dPpatients CPatient allow_email_not_defined") ? " default|2" : " default|1";

        $props["allow_email"]          = "enum list|0|1|2$allow_email fieldset|contact";
        $props["allow_pers_prevenir"]  = "bool default|0";
        $props["allow_pers_confiance"] = "bool default|0";

        $types_exo = [
            "aldHorsListe",
            "aldListe",
            "aldMultiple",
            "alsaceMoselle",
            "article115",
            "autreCas",
            "autreCasAlsaceMoselle",
            "fns",
        ];

        $props["_type_exoneration"]        = "enum list|" . implode("|", $types_exo);
        $props["_annees"]                  = "num show|1";
        $props["_age"]                     = "str";
        $props["_vip"]                     = "bool";
        $props["_prenom_2"]                = "str fieldset|extra show|1";
        $props["_prenom_3"]                = "str fieldset|extra show|1";
        $props["_prenom_4"]                = "str fieldset|extra show|1";
        $props["_age_assure"]              = "num";
        $props["_poids"]                   = "float show|1";
        $props["_taille"]                  = "float show|1";
        $props["_consent_terresante"]      = "bool";
        $props["_consent_dmp"]             = "bool";
        $props["_consent_mssante_patient"] = "bool";
        $props["_consent_mssante_pro"]     = "bool";
        $props["_assure_prenom_2"]         = "str fieldset|assure show|1";
        $props["_assure_prenom_3"]         = "str fieldset|assure show|1";
        $props["_assure_prenom_4"]         = "str fieldset|assure show|1";
        $props['_mode_obtention']          = 'str default|manuel';
        $props['_identity_proof_type_id']  = 'ref class|CIdentityProofType';

        $props["_age_min"] = "num min|0";
        $props["_age_max"] = "num min|0";

        $props["_bmr_bhre_status"] = "str fieldset|default";

        $props["_IPP"] = "str show|1";

        // DMP
        $props["_dmp_create"]           = "bool";
        $props["_dmp_medecin_traitant"] = "bool";
        $props["_dmp_urgence_15"]       = "bool";
        $props["_dmp_urgence_PS"]       = "bool";
        $props["_dmp_reactivation_dmp"] = "str";
        $props["_dmp_reason_close"]     = "text";

        //données provenant de la carte vitale
        $props["_vitale_lastname"]     = "str";
        $props["_vitale_birthname"]    = "str";
        $props["_vitale_firstname"]    = "str";
        $props["_vitale_birthdate"]    = "str confidential";
        $props["_vitale_nir_certifie"] = "str confidential";
        $props["_vitale_nir"]          = "str";
        $props["_vitale_quality"]      = "str";
        $props["_vitale_birthrank"]    = "num";
        $props["_vitale_code_regime"]  = "str";
        $props["_vitale_code_caisse"]  = "str";
        $props["_vitale_code_centre"]  = "str";
        $props["_vitale_code_gestion"] = "str";

        //Form fields utilisés pour la recherche partielle (Numéro Séc.Sociale/AVS)
        $props["_matricule"] = "str maxLength|15";
        $props["_avs"]       = "str maxLength|16";

        $props["_reason_state"] = "text";

        $props["_provenance_id"]    = "num";
        $props["_commentaire_prov"] = "text";

        $props['_date_fin_validite']               = 'date';
        $props['_source_nom']                      = 'str confidential fieldset|source_identite';
        $props['_source_prenom']                   = 'str confidential fieldset|source_identite';
        $props['_source_prenom_usuel']             = 'str fieldset|source_identite';
        $props['_source_prenoms']                  = 'str fieldset|source_identite';
        $props['_source_nom_jeune_fille']          = 'str confidential fieldset|source_identite';
        $props['_source_sexe']                     = 'enum list|i|m|f fieldset|source_identite';
        $props['_source_civilite']                 = 'enum list|m|mme|mlle|enf|dr|pr|me|vve fieldset|source_identite';
        $props['_source_naissance']                = 'birthDate fieldset|source_identite';
        $props['_source_naissance_corrigee']       = 'bool fieldset|source_identite';
        $props['_source_lieu_naissance']           = 'str fieldset|source_identite';
        $props['_source_commune_naissance_insee']  = 'str length|5 fieldset|source_identite';
        $props['_source_cp_naissance']             = "str minLength|$min_cp maxLength|$max_cp confidential fieldset|source_identite";
        $props['_source_pays_naissance_insee']     = 'str fieldset|source_identite';
        $props['_source__pays_naissance_insee']    = 'str fieldset|source_identite';
        $props['_source__code_insee']              = 'str fieldset|source_identite';
        $props['_source__date_fin_validite']       = "birthDate fieldset|source_identite";
        $props['_source__validate_identity']       = 'bool';
        $props['_map_source_form_fields']          = 'bool fieldset|source_identite';
        $props['_force_manual_source']             = 'bool fieldset|source_identite';
        $props['_force_new_manual_source']         = 'bool fieldset|source_identite';
        $props['_force_new_insi_source']           = 'bool fieldset|source_identite';
        $props['_copy_file_id']                    = 'ref class|CFile';
        $props['_source__complete_traits_stricts'] = 'bool';

        // INSi
        $props['_oid']          = 'str';
        $props['_ins']          = 'str';
        $props['_ins_type']     = 'str';
        $props['_previous_ins'] = 'str';
        $props['_douteux']      = 'bool fieldset|status';
        $props['_fictif']       = 'bool fieldset|status';

        return $props;
    }

    /**
     * Récupère la limite du nombre de chiffre à mettre en place selon les configurations
     *
     * @return array
     */
    static function getLimitCharCP()
    {
        $cps = [];

        if (CAppUI::conf("dPpatients INSEE suisse") || CAppUI::conf("dPpatients INSEE belgique")) {
            $cps[] = 4;
        }

        if (CAppUI::conf("dPpatients INSEE france") || CAppUI::conf("dPpatients INSEE allemagne")
            || CAppUI::conf("dPpatients INSEE espagne")
        ) {
            $cps[] = 5;
        }

        if (CAppUI::conf("dPpatients INSEE portugal")) {
            $cps[] = 8;
        }

        if (CAppUI::conf("dPpatients INSEE gb")) {
            $cps[] = 2;
            $cps[] = 4;
        }

        if (!$cps) {
            $cps[] = 5;
        }

        return [min($cps), max($cps)];
    }

    /**
     * @inheritDoc
     * @throws CanNotMerge
     * @throws Exception
     */
    public function checkMerge(array $objects = []): void
    {
        parent::checkMerge($objects);

        $sejour = new CSejour();

        $where = [
            'patient_id' => CSQLDataSource::prepareIn(CMbArray::pluck($objects, '_id')),
        ];

        /** @var CSejour[] $sejours */
        $sejours = $sejour->loadList($where);

        foreach ($sejours as $_sejour1) {
            foreach ($sejours as $_sejour2) {
                if ($_sejour1->collides($_sejour2)) {
                    $_sejour1->loadRefPatient(1);
                    $_sejour2->loadRefPatient(1);

                    throw CanNotMergePatient::patientVenueConflict($_sejour1, $_sejour2);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        $must_demote = !in_array($this->status, ['VIDE', 'PROV']);

        // Load the matching CDossierMedical objects
        if ($this->_id) {
            $merged_objects = array_merge($objects, [$this]);
        } else {
            $merged_objects = $objects;
        }

        $where = [
            'object_class' => "= '$this->_class'",
            'object_id'    => CSQLDataSource::prepareIn(CMbArray::pluck($merged_objects, 'patient_id')),
        ];

        $dossier_medical = new CDossierMedical();
        $list            = $dossier_medical->loadList($where);

        $ins_nir_presence = false;
        $patient_ins_nir  = $this->loadRefPatientINSNIR();
        if ($patient_ins_nir && $patient_ins_nir->_id) {
            $ins_nir_presence = true;
        }

        foreach ($objects as $object) {
            if (!$must_demote) {
                $must_demote = !in_array($object->status, ['VIDE', 'PROV']);
            }

            // Désactivation des sources d'identité
            foreach ($object->loadRefsSourcesIdentite() as $_source) {
                $_source->active = 0;
                $_source->store();
            }

            // Suppression des consentements
            foreach ($this->loadBackRefs('patient_consents') as $_patient_consent) {
                $_patient_consent->delete();
            }

            $object->loadIPP();

            // Suppression de l'INS-NIR pour les autres patients si le patient que l'on garde en a déjà un
            if (!$ins_nir_presence) {
                continue;
            }

            $ins_nir_object = $object->loadRefPatientINSNIR();
            if (!$ins_nir_object || !$ins_nir_object->_id) {
                continue;
            }

            $ins_nir_object->delete();
        }

        if (CModule::getActive('appFineClient')) {
            $status_patient_user  = new CAppFineClientStatusPatientUser();
            $status_patient_users = $status_patient_user->loadList(
                ['patient_id' => CSQLDataSource::prepareIn(CMbArray::pluck($merged_objects, 'patient_id'))]
            );

            $delete_status_patient_user = false;

            // On a plusieurs status_patient_user et on va devoir en garder qu'un après la fusion
            if (count($status_patient_users) > 1) {
                $delete_status_patient_user = true;
            }
        }

        $traits_strict_modified = $must_demote ? $this->getTraitsStrictsModified() : [];

        try {
            parent::merge($objects, $fast, $merge_log);
        } catch (CouldNotMerge $e) {
            // Legacy behavior: Keep on script execution...
        } catch (Throwable $t) {
            throw $t;
        }

        $signatures_ids = $this->loadBackIds('signatures');
        (new CPatientSignature())->deleteAll($signatures_ids);
        CPatientSignature::addPatientSignature($this->_id);

        CPatientLink::deleteDoubloon();

        if (CModule::getActive('appFineClient') && $delete_status_patient_user) {
            CAppFineClient::deleteDoubloonStatusPatientUser($status_patient_users, $this->_id);
        }

        $this->store();

        // Merge them
        if (count($list) > 1) {
            // Si le dossier médical du patient gardé comme base avait un identifiant plus récent que celui du patient non conservé
            // Le merge ne pouvait pas se faire car le dossier médical conservé comme base était celui du patient supprimé
            foreach ($list as $_dossier) {
                if ($_dossier->object_id == $this->_id) {
                    $dossier_medical = $list[$_dossier->_id];
                    unset($list[$_dossier->_id]);
                    break;
                }
            }

            $dossier_merge_log = CMergeLog::logStart(CUser::get()->_id, $dossier_medical, $list, $fast);

            try {
                $dossier_medical->merge($list, $fast, $dossier_merge_log);
                $dossier_merge_log->logEnd();
            } catch (Throwable $t) {
                $dossier_merge_log->logFromThrowable($t);

                throw $t;
            }
        }

        // Le patient revient en statut provisoire si un trait strict a changé
        if ($must_demote && count($traits_strict_modified)) {
            $this->loadRefSourceIdentite();
            $this->loadRefsSourcesIdentite();
            PatientStatus::demotePatientStatus($this, $traits_strict_modified);
        }
    }

    /**
     * Charge l'IPP du patient pour l'établissement courant
     *
     * @param int $group_id Permet de charger l'IPP pour un établissement donné si non null
     */
    public function loadIPP($group_id = null): ?string
    {
        if (!$this->_id) {
            return null;
        }

        // Prevent loading twice
        if ($this->_IPP) {
            return $this->_IPP;
        }

        // Pas de tag IPP => pas d'affichage d'IPP
        if (null == $tag_ipp = $this->getTagIPP($group_id)) {
            $this->_IPP = str_pad($this->_id, 6, "0", STR_PAD_LEFT);

            return null;
        }

        // Recuperation de la valeur de l'id400
        $idex = CIdSante400::getMatchFor($this, $tag_ipp);

        // Stockage de la valeur de l'id400
        $this->_ref_IPP = $idex;

        return $this->_IPP = $idex->id400;
    }

    /**
     * Construit le tag IPP en fonction des variables de configuration
     *
     * @param int $group_id Permet de charger l'IPP pour un établissement donné si non null
     *
     * @return string|null
     */
    static function getTagIPP($group_id = null)
    {
        // Recherche de l'établissement
        $group = CGroups::get($group_id);
        if (!$group_id) {
            $group_id = $group->_id;
        }

        $cache = new Cache('CPatient.getTagIPP', [$group_id], Cache::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        // Gestion du tag IPP par son domaine d'identification
        if (CAppUI::conf("eai use_domain")) {
            return $cache->put(CDomain::getMasterDomainPatient($group_id)->tag, false);
        }

        // Pas de tag IPP => pas d'affichage d'IPP
        if (null == $tag_ipp = CAppUI::conf("dPpatients CPatient tag_ipp")) {
            return $cache->put(null, false);
        }

        // Si on est dans le cas d'un établissement gérant la numérotation
        $group->loadConfigValues();
        if ($group->_configs["sip_idex_generator"]) {
            $tag_ipp = CAppUI::conf("sip tag_ipp");
        }

        // Préférer un identifiant externe de l'établissement
        if ($tag_group_idex = CAppUI::conf("dPpatients CPatient tag_ipp_group_idex")) {
            $idex = new CIdSante400();
            $idex->loadLatestFor($group, $tag_group_idex);
            $group_id = $idex->id400;
        }

        return $cache->put(str_replace('$g', $group_id, $tag_ipp), false);
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->loadOldObject();

        $this->completeField(
            "patient_link_id",
            "lieu_naissance",
            "assure_lieu_naissance",
            'nom',
            'nom_jeune_fille',
            'lieu_naissance',
            'cp',
            'commune_naissance_insee'
        );

        $allow_modify_strict_traits = CAppUI::pref('allow_modify_strict_traits');
        $ref_pays                   = CAppUI::conf('ref_pays');

        $mode_obtention = $this->_mode_obtention;

        $medecin_traitant_modified = $this->fieldModified('medecin_traitant');

        if ($ref_pays === '1') {
            foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_trait) {
                $this->completeField($_trait);

                // Ne pas autoriser la modification des traits stricts si la permission fonctionnelle ne le permet pas
                if (
                    $this->_id
                    && !$this->_merging
                    && !$this->_source_nom && !$allow_modify_strict_traits
                    && in_array(
                        $this->status,
                        [CPatientState::STATE_VALI, CPatientState::STATE_QUAL, CPatientState::STATE_RECUP]
                    )
                ) {
                    $this->{$_trait} = $this->_old->{$_trait};
                }
            }
        }

        // Si aucun trait strict est modifié, pas besoin de créer une nouvelle source manuelle
        // en mode déverrouillage pour la modification de traits stricts
        if ($this->_force_new_manual_source) {
            if (count($this->getTraitsStrictsModified())) {
                // Si QUAL, on repasse en VALI (source manuelle avec justif)
                if ($this->status === 'QUAL') {
                    foreach ($this->loadRefsSourcesIdentite() as $_source_identite) {
                        if ($_source_identite->identity_proof_type_id) {
                            $this->source_identite_id = $_source_identite->_id;
                            break;
                        }
                    }
                } else {
                    // Sinon provisoire (nouvelle source manuelle)
                    $this->source_identite_id = '';
                }
            } else {
                $this->_force_new_manual_source = false;
            }
        }

        $create = false;
        if (!$this->_id) {
            $create = true;
        } else {
            $old_fields = [];

            foreach (CPatientSignature::$fields as $_field) {
                $old_fields[$_field] = $this->_old->$_field;
            }
        }

        if ($create) {
            $mediuser            = CMediusers::get();
            $this->creator_id    = $mediuser ? $mediuser->_id : null;
            $this->creation_date = CMbDT::dateTime();
        }

        if ($this->_id && $this->_id == $this->patient_link_id) {
            $this->patient_link_id = "";
        }

        // Création d'un patient en mode cabinets distincts
        if (CAppUI::isCabinet() && !$this->_id && !$this->function_id) {
            $this->function_id = CFunctions::getCurrent()->_id;
        } elseif (CAppUI::isGroup() && !$this->_id && !$this->group_id) {
            $this->group_id = CGroups::loadCurrent()->_id;
        }

        if ($this->fieldModified("naissance") || $this->fieldModified("sexe")) {
            // _guid is not valued yet !!
            Cache::deleteKeys(Cache::DISTR, "alertes-CPatient-" . $this->_id . '-');
        }

        // Si changement de sexe on essaie de retrouver la civilité
        if (($this->fieldModified("sexe") || $this->fieldModified("naissance")) && !$this->fieldModified("civilite")) {
            $this->civilite = "guess";
        }

        // Storing a qual_beneficiaire with '0' or 0 will store "" instead
        if ($this->qual_beneficiaire === '0' || $this->qual_beneficiaire === 0) {
            $this->qual_beneficiaire = '00';
        }

        $this->_anonyme = $this->checkAnonymous();

        // Changement des traits stricts lors d'un changement de source, hormis dans les cas suivants :
        // - fusion
        // - déverrouillage du changement des traits stricts depuis l'ihm
        if (
            $this->fieldModified('source_identite_id')
            && $this->source_identite_id
            && !$this->_merging
            && !$this->_force_new_manual_source
        ) {
            $source = $this->loadRefSourceIdentite();
            $source->mapFields($this);
        }

        if ($ref_pays === '1') {
            if (($this->fieldModified('prenom') || $this->fieldModified(
                        'prenoms'
                    )) && $this->prenoms && !$this->_merging) {
                $prenoms = preg_split("/[' \-]/", strtoupper($this->prenoms));
                $prenom  = preg_split("/[' \-]/", strtoupper($this->prenom));
                if (count($prenom) > count($prenoms) || array_slice($prenoms, 0, count($prenom)) !== $prenom) {
                    return CAppUI::tr('CPatient.first_birth_name_warning');
                }
            }
        }

        if ($this->nom_jeune_fille && !$this->nom) {
            $this->nom = $this->nom_jeune_fille;
        }

        (new MedecinExercicePlaceService($this, 'medecin_traitant', 'medecin_traitant_exercice_place_id'))
            ->applyFirstExercicePlace();

        $generate_ipp   = $this->_generate_IPP;
        $no_synchro_eai = $this->_no_synchro_eai;

        // Gestion de la saisie du lieu de naissance sans code postal
        if ($this->lieu_naissance && $this->commune_naissance_insee) {
            if (
                ($create && !$this->cp_naissance)
                || ($this->fieldModified('lieu_naissance') || $this->fieldModified('commune_naissance_insee'))
            ) {
                foreach ((new CCommuneFrance())->loadListByInsee($this->commune_naissance_insee) as $_commune) {
                    if (CMbString::lower(CMbString::removeAccents($_commune->commune)) ===
                        CMbString::lower(CMbString::removeAccents($this->lieu_naissance))) {
                        $this->cp_naissance = $_commune->code_postal;
                        break;
                    }
                }
            }
        }

        // Gestion du code INSEE & code Postal de naissance à 99999
        if ($this->_code_insee == "99999" && $this->cp_naissance == "99999") {
            $this->pays_naissance_insee = "";
        }

        // En création, si le pays est absent, on complète le pays si la commune est en france
        if ($create && $this->ville && $this->cp && !$this->pays) {
            $commune = (new CCommuneFrance())->loadFirstByCP($this->cp);

            if ($commune->_id) {
                $this->pays = 'France';
            }
        }

        // Standard store
        if ($msg = parent::store()) {
            return $msg;
        }

        $this->_generate_IPP   = $generate_ipp;
        $this->_no_synchro_eai = $no_synchro_eai;

        if ($create) {
            CPatientSignature::addPatientSignature($this->_id);
        } elseif ($old_fields) {
            CPatientSignature::updatePatientSignature($this, $old_fields);
        }

        if ($this->_anonyme && $this->nom !== $this->_id) {
            $this->nom = $this->_id;
            if ($msg = parent::store()) {
                return $msg;
            }
        }

        $this->_generate_IPP   = $generate_ipp;
        $this->_no_synchro_eai = $no_synchro_eai;

        CPatientState::storeStates($this);

        foreach (CRGPDConsent::TAGS_PATIENT as $_tag) {
            if ($msg = CConsentPatient::storeConsent($this, $_tag)) {
                return $msg;
            }
        }

        $this->_generate_IPP   = $generate_ipp;
        $this->_no_synchro_eai = $no_synchro_eai;

        if ($msg = CSourceIdentite::manageFictifDouteux($this)) {
            return $msg;
        }

        $this->_generate_IPP   = $generate_ipp;
        $this->_no_synchro_eai = $no_synchro_eai;

        if ($msg = CSourceIdentite::manageSource($this, $mode_obtention)) {
            return $msg;
        }

        $this->_generate_IPP   = $generate_ipp;
        $this->_no_synchro_eai = $no_synchro_eai;

        if ($msg = (new PatientStatus($this))->updateStatus()) {
            return $msg;
        }

        // Handicap
        $handicap_is_split = $this->_handicap !== null ? str_contains($this->_handicap, ',') : false;
        $form_handicap = $handicap_is_split ? explode(',', $this->_handicap) : [$this->_handicap];

        if ($this->_handicap !== null) {
            $form_handicap = array_filter($form_handicap);

            // Get the handicaps list of the patient
            $patient_handicap             = new CPatientHandicap();
            $patient_handicap->patient_id = $this->_id;
            $handicap_list                = CMbArray::pluck($patient_handicap->loadMatchingListEsc(), 'handicap');

            // Remove handicaps which aren't in the list anymore
            foreach (array_diff($handicap_list, $form_handicap) as $_handicap) {
                $patient_handicap             = new CPatientHandicap();
                $patient_handicap->patient_id = $this->_id;
                $patient_handicap->handicap   = $_handicap;
                $patient_handicap->loadMatchingObjectEsc();
                $patient_handicap->delete();
            }

            // Refresh list after deletes
            $handicap_list = CMbArray::pluck($patient_handicap->loadMatchingListEsc(), 'handicap');

            // Add handicaps which are not in the list yet
            foreach (array_diff($form_handicap, $handicap_list) as $_handicap) {
                $patient_handicap             = new CPatientHandicap();
                $patient_handicap->patient_id = $this->_id;
                $patient_handicap->handicap   = $_handicap;
                $patient_handicap->loadMatchingObjectEsc();

                if (!$patient_handicap->_id) {
                    $patient_handicap->store();
                }
            }
        }

        // Vitale
        if (CModule::getActive("fse")) {
            $cv = CFseFactory::createCV();
            if ($cv) {
                if ($msg = $cv->bindVitale($this)) {
                    return $msg;
                }
            }
        }

        // Génération de l'IPP ?
        if ($this->_generate_IPP) {
            if ($msg = $this->generateIPP()) {
                return $msg;
            }
        }

        if ($this->_vitale_nir_certifie) {
            if ($msg = CINSPatient::createINSC($this)) {
                return $msg;
            }
        }

        // Mise à jour du médecin traitant sur tous séjours en cours ou futur
        if ($medecin_traitant_modified) {
            $where = [
                $this->getDS()->prepare('sejour.entree >= ? OR sejour.sortie >= ?', CMbDT::dateTime())
            ];

            foreach ($this->loadRefsSejours($where) as $sejour) {
                $sejour->medecin_traitant_id = $this->medecin_traitant;
                if ($msg = $sejour->store()) {
                    return $msg;
                }
            }
        }

        // Check BMR BHRE Status adn end date
        if ($this->_id) {
            CApp::doProbably(100, [$this, 'checkBmrBhreStatus']);
        }

        return null;
    }

    public function delete(): ?string
    {
        // Suppression de la source d'identité reliée directement au patient
        if ($this->source_identite_id) {
            $source = $this->loadRefSourceIdentite();

            CSourceIdentite::$update_patient_status = false;
            if ($source->_id && ($msg = $source->delete())) {
                CSourceIdentite::$update_patient_status = false;

                return $msg;
            }
            CSourceIdentite::$update_patient_status = true;
        }

        return parent::delete();
    }

    /**
     * Vérification de l'aspect anonyme du patient
     *
     * @return bool
     */
    function checkAnonymous()
    {
        return (CMbString::upper($this->nom) === "ANONYME" || is_numeric($this->nom)) && CMbString::upper(
                $this->prenom
            ) === "ANONYME";
    }

    /**
     * Génération de l'IPP du patient
     *
     * @param string $group_id Group
     *
     * @return null|string
     */
    function generateIPP($group_id = null)
    {
        if ($this->_forwardRefMerging) {
            return null;
        }

        $group = CGroups::loadCurrent();
        if ($group_id && $group_id != $group->_id) {
            return;
        }

        if (!$group->isIPPSupplier()) {
            return null;
        }

        $this->loadIPP($group->_id);
        if ($this->_IPP) {
            return null;
        }

        if (!$IPP = CIncrementer::generateIdex($this, self::getTagIPP($group->_id), $group->_id)) {
            return CAppUI::tr("CIncrementer_undefined");
        }

        $this->_IPP = $IPP->id400;

        return null;
    }

    /**
     * @see parent::check()
     */
    function check()
    {
        // Standard check
        if ($msg = parent::check()) {
            return $msg;
        }

        // Check birthdate is correct
        if ($this->naissance) {
            $annee_naissance = CMbDT::format($this->naissance, "%Y");
            $current_year    = CMbDT::format(CMbDT::date(), "%Y");

            if ($annee_naissance > $current_year) {
                return "CPatient-msg-You cannot enter a date of birth greater than the current year";
            }
        }

        return null;
    }

    /**
     * Charge le patient ayant les traits suivants :
     * - Même nom à la casse et aux séparateurs près
     * - Même prénom à la casse et aux séparateurs près
     * - Strictement la même date de naissance
     *
     * @param bool  $other                  Vérifier qu'on n'inclut pas $this
     * @param bool  $loadObject             Permet de ne pas charger le patient, seulement renvoyer le nombre de matches
     * @param array $additionnal_fields     Champs supplémentaires à vérifier
     * @param bool  $trim                   Trim the name and surname in database for comparison
     * @param bool  $group_id_fields_config Add fields in config to do matching
     *
     * @return int Nombre d'occurences trouvées
     */
    function loadMatchingPatient(
        $other = false,
        $loadObject = true,
        $additionnal_fields = [],
        $trim = false,
        $group_id = null
    ) {
        $where = $this->getWhereDoubloon($this, $other, $additionnal_fields, $trim, $group_id);

        if (!$where) {
            return null;
        }

        if ($loadObject) {
            $this->loadObject($where);
        }

        return $this->countList($where);
    }

    /**
     * Créé la clause pour la recherche de doublon sctrict
     *
     * @param CPatient $patient            Patient
     * @param bool     $other              Inclusion du patient en cours
     * @param array    $additionnal_fields Additionnals fields to check
     * @param bool     $trim               Trim the name and surname from database for comparison
     * @param bool     $group_id           Group to add fields in config
     *
     * @return null|String[]
     */
    public function getWhereDoubloon(
        $patient,
        $other = false,
        $additionnal_fields = [],
        $trim = false,
        $group_id = null
    ) {
        $ds    = $patient->_spec->ds;
        $where = [];

        if (CAppUI::isCabinet()) {
            $function_id          = CFunctions::getCurrent()->_id;
            $where["function_id"] = "= '$function_id'";
        } elseif (CAppUI::isGroup()) {
            $group_id          = $group_id ?: CMediusers::get()->loadRefFunction()->group_id;
            $where["group_id"] = "= '$group_id'";
        }

        if ($other && $patient->_id) {
            $where["patient_id"] = " != '$patient->_id'";
        }

        // if no birthdate, sql request too strong
        if (!$patient->naissance) {
            return null;
        }

        $nom             = ($trim) ? 'trim(`nom`)' : '`nom`';
        $nom_jeune_fille = ($trim) ? 'trim(`nom_jeune_fille`)' : '`nom_jeune_fille`';

        $whereOr[] = "$nom " . $ds->prepareLikeName($patient->nom);
        $whereOr[] = "$nom_jeune_fille " . $ds->prepareLikeName($patient->nom);

        if ($patient->nom_jeune_fille) {
            $whereOr[] = "$nom " . $ds->prepareLikeName($patient->nom_jeune_fille);
            $whereOr[] = "$nom_jeune_fille " . $ds->prepareLikeName($patient->nom_jeune_fille);
        }

        $where[] = implode(" OR ", $whereOr);

        // Cannot use trim(prenom) for key in $where because the key is escaped
        $prenom  = ($trim) ? 'trim(`prenom`)' : '`prenom`';
        $prenoms = ($trim) ? 'trim(`prenoms`)' : '`prenoms`';

        $where[] = "$prenom " . $ds->prepareLikeName($patient->prenom);

        if ($patient->_prenom_2) {
            $where[] = "$prenoms " . $ds->prepareLikeName($patient->_prenom_2);
        }
        if ($patient->_prenom_3) {
            $where[] = "$prenoms " . $ds->prepareLikeName($patient->_prenom_3);
        }
        if ($patient->_prenom_4) {
            $where[] = "$prenoms " . $ds->prepareLikeName($patient->_prenom_4);
        }

        $where["naissance"] = $ds->prepare("= %", $patient->naissance);

        if ($fields_matching = CAppUI::gconf('dPpatients CPatient custom_matching', $group_id)) {
            $fields             = explode('|', $fields_matching);
            $additionnal_fields = array_unique(array_merge($fields, $additionnal_fields));
        }

        foreach ($additionnal_fields as $_field) {
            if (property_exists('CPatient', $_field)) {
                if ($patient->$_field === null || $patient->$_field == '') {
                    continue;
                }
                $where[$_field] = $ds->prepare('= ?', $patient->$_field);
            }
        }

        return $where;
    }

    /**
     * @inheritDoc
     */
    function getPerm($permType)
    {
        if (CAppUI::isCabinet() && $this->function_id) {
            return $this->loadRefFunction()->getPerm($permType) && parent::getPerm($permType);
        }

        if (CAppUI::isGroup() && $this->group_id) {
            return $this->loadRefGroup()->getPerm($permType) && parent::getPerm($permType);
        }

        return parent::getPerm($permType);
    }

    /**
     * Chargement de la fonction reliée
     *
     * @return CFunctions
     */
    function loadRefFunction()
    {
        return $this->_ref_function = $this->loadFwdRef("function_id", true);
    }

    /**
     * Chargement de la fonction reliée
     *
     * @return CGroups
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Chargement du dernier PatientUser
     *
     * @return CPatientUser
     * @throws Exception
     */
    function loadRefFirstPatientUser()
    {
        $patient_user             = new CPatientUser();
        $patient_user->patient_id = $this->_id;
        $patient_user->loadMatchingObjectEsc("create_datetime ASC");

        return $this->_ref_first_patient_user = $patient_user;
    }

    /**
     * @inheritDoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        // Noms
        $anonyme               = is_numeric($this->nom);
        $this->nom             = self::applyModeIdentitoVigilance($this->nom, false, null, $anonyme);
        $this->nom_jeune_fille = self::applyModeIdentitoVigilance($this->nom_jeune_fille, false, null, $anonyme);
        $this->prenom          = self::applyModeIdentitoVigilance($this->prenom, true, null, $anonyme);

        $this->_nom_naissance = $this->nom_jeune_fille ? $this->nom_jeune_fille : $this->nom;

        if ($this->prenoms) {
            foreach (explode(' ', $this->prenoms) as $_i => $_prenom) {
                if ($_i === 0) {
                    continue;
                }
                if ($_i === 1) {
                    $this->_prenom_2 = $_prenom;
                }
                if ($_i === 2) {
                    $this->_prenom_3 = $_prenom;
                }
                if ($_i === 3) {
                    $this->_prenom_4 = $_prenom;
                }
            }
        }

        if ($this->assure_prenoms) {
            foreach (explode(' ', $this->assure_prenoms) as $_i => $_assure_prenom) {
                $this->{"_assure_prenom_$_i"} = $_assure_prenom;
            }
        }

        $this->_prenoms = $this->prenoms ?
            explode(' ', $this->prenoms) : [$this->prenom, $this->_prenom_2, $this->_prenom_3, $this->_prenom_4];
        CMbArray::removeValue(null, $this->_prenoms);

        if ($this->libelle_exo) {
            $this->_art115 = preg_match("/pension militaire/i", $this->libelle_exo);
        }

        $duration   = CMbDT::achievedDurations($this->naissance, $this->deces);
        $this->_age = CMbArray::get($duration, "locale");

        $this->evalAge();
        $this->checkVIP();
        $this->getCivilityView();

        if ($this->prenom_usuel && strtoupper($this->prenom) != strtoupper($this->prenom_usuel)) {
            $dit             = CAppUI::tr('CPatient-alias');
            $this->_view     .= " $dit $this->prenom_usuel";
            $this->_longview .= " $dit $this->prenom_usuel";
        }
        if (CAppUI::pref("see_statut_patient")) {
            $status          = $this->status === "VIDE" ? "NQUAL" : $this->status;
            $this->_view     .= $this->status ? " [$status.]" : "";
            $this->_longview .= $this->status ? " [$status.]" : "";
        }
        $this->_view     .= $this->vip ? " [Conf.]" : "";
        $this->_view     .= $this->deces ? " [Décès.]" : "";
        $this->_longview .= $this->vip ? " [Conf.]" : "";
        $this->_longview .= $this->deces ? " [Décès.]" : "";
        if (CAppUI::isCabinet() && $this->function_id && Cfunctions::getCurrent()->_id != $this->function_id) {
            $this->_view .= " [" . $this->loadRefFunction()->_view . "]";
        } elseif (CAppUI::isGroup() && $this->group_id && CGroups::loadCurrent()->_id != $this->group_id) {
            $this->_view .= " [" . $this->loadRefGroup()->_view . "]";
        }

        $last_name_last_particule   = explode(' ', $this->nom);
        $last_name_last_particule   = ($last_name_last_particule) ? end($last_name_last_particule) : $this->nom;
        $first_name_first_particule = ($this->prenom) ? explode(' ', $this->prenom) : null;
        $first_name_first_particule = ($first_name_first_particule) ? reset(
            $first_name_first_particule
        ) : $this->prenom;
        $this->_shortview           = CMbString::makeInitials(
            "{$first_name_first_particule} {$last_name_last_particule}"
        );

        // Navigation fields
        //$this->_dossier_cabinet_url = self::$dossier_cabinet_prefix[CAppUI::pref("DossierCabinet")] . $this->_id;
        $module                     = CModule::getActive("oxCabinet") ? "oxCabinet" : "dPpatients";
        $this->_dossier_cabinet_url = self::$dossier_cabinet_prefix[$module] . $this->_id;

        if ($this->pays_insee) {
            $this->pays = CPaysInsee::getNomFR($this->pays_insee);
        }

        if ($this->csp) {
            $this->_csp_view = $this->getCSPName();
        }

        if (!$this->phone_area_code) {
            $this->phone_area_code = CAppUI::conf('system phone_area_code');
        }

        $this->mapPerson();
    }

    /**
     * Get the view of civility
     *
     * @return void
     * @throws Exception
     */
    public function getCivilityView(): void
    {
        $this->_civilite = CAppUI::tr("CPatient.civilite.$this->civilite");
        if ($this->civilite === "enf") {
            $this->_civilite_long = CAppUI::tr("CPatient.civilite." . ($this->sexe === "m" ? "le_jeune" : "la_jeune"));
        } else {
            $this->_civilite_long = CAppUI::tr("CPatient.civilite.$this->civilite-long");
        }

        $this->_assure_civilite = CAppUI::tr("CPatient.civilite.$this->assure_civilite");
        if ($this->assure_civilite === "enf") {
            $this->_assure_civilite_long = CAppUI::tr(
                "CPatient.civilite." . ($this->assure_sexe === "m" ? "le_jeune" : "la_jeune")
            );
        } else {
            $this->_assure_civilite_long = CAppUI::tr("CPatient.civilite.$this->assure_civilite-long");
        }

        $group           = CGroups::loadCurrent();
        $nom_naissance   = $this->nom_jeune_fille && ($this->nom_jeune_fille != $this->nom || CAppUI::conf(
                "dPpatients CPatient nom_jeune_fille_always_present",
                $group
            )) ? " ($this->nom_jeune_fille)" : "";
        $this->_view     = "$this->_civilite $this->nom$nom_naissance $this->prenom";
        $this->_longview = "$this->_civilite_long $this->nom$nom_naissance $this->prenom";
    }

    /**
     * Apply the mode of identito vigilance
     *
     * @param String $string    String
     * @param Bool   $firstname Apply the lower and the capitalize
     * @param bool   $anonyme   Is anonyme
     *
     * @return string
     */
    static function applyModeIdentitoVigilance(
        $string,
        $firstname = false,
        $mode_identito_vigilance = null,
        $anonyme = false
    ) {
        $mode_identito_vigilance = $mode_identito_vigilance ?: "strict";

        switch ($mode_identito_vigilance) {
            case "medium":
                $result = CMbString::removeBanCharacter($string, true);
                $result = $firstname ? CMbString::capitalize(CMbString::lower($result)) : CMbString::upper($result);
                break;
            case "strict":
                $result = CMbString::upper(CMbString::removeBanCharacter($string, $anonyme, true));
                $result = preg_replace('/-[-]+/', '-', $result);
                break;
            default:
                $result = $firstname ? CMbString::capitalize(CMbString::lower($string)) : CMbString::upper($string);
        }

        return $result;
    }

    /**
     * Calcul l'âge du patient en années
     *
     * @param string $date Date de référence pour le calcul, maintenant si null
     *
     * @return int l'age du patient en années
     */
    function evalAge($date = null)
    {
        if ($date == "now") {
            $date = CMbDT::date();
        }

        $achieved = CMbDT::achievedDurations($this->naissance, $date);

        return $this->_annees = $achieved["year"];
    }

    /**
     * Calcul l'aspect confidentiel du patient
     *
     * @return bool on a accès ou pas
     * @throws Exception
     */
    function checkVIP()
    {
        // Checking _ref_last_log presence because of loadLogs() erasing initial creation user log in CStoredObject::store()
        if ($this->_vip !== null || $this->_ref_last_log) {
            return;
        }

        $this->_vip = false;
        $user       = CMediusers::get();

        if ($this->vip && !CModule::getCanDo("dPpatients")->admin) {
            // Test si le praticien est présent dans son dossier

            $praticiens        = $this->loadRefsPraticiens();
            $user_in_list_prat = array_key_exists($user->_id, $praticiens);

            if ($this->creator_id) {
                $user_in_logs = $user->_id === $this->creator_id;
            } else {
                // Test si un l'utilisateur est présent dans les logs
                $this->loadLogs();
                $user_in_logs = in_array($user->_id, CMbArray::pluck($this->_ref_logs, "user_id"));

                foreach ($this->_ref_logs as $_log) {
                    if ($_log->type === "create") {
                        $this->creator_id    = $_log->user_id;
                        $this->creation_date = $_log->date;
                        break;
                    }
                }

                if ($this->creator_id && $this->creation_date) {
                    $this->rawStore();
                }
            }

            $this->_vip = !$user_in_list_prat && !$user_in_logs;
        }
        if ($this->_vip) {
            CValue::setSession("patient_id", 0);
        }
    }

    /**
     * Chargement des praticien ayant travaillé sur le patient
     *
     * @return CMediusers[]
     */
    function loadRefsPraticiens()
    {
        $ds = self::getDS();

        // Consultations
        $request = new CRequest();
        $request->addSelect("DISTINCT chir_id");
        $request->addTable("consultation");
        $request->addLJoin("plageconsult ON plageconsult.plageconsult_id = consultation.plageconsult_id");
        $request->addWhere("consultation.patient_id = '$this->_id'");

        $praticiens_ids = $ds->loadColumn($request->makeSelect());

        // Séjours
        $request = new CRequest();
        $request->addSelect("DISTINCT praticien_id");
        $request->addTable("sejour");
        $request->addWhere("sejour.patient_id = '$this->_id'");

        $praticiens_ids = array_merge($praticiens_ids, $ds->loadColumn($request->makeSelect()));

        // Opérations
        $request = new CRequest();
        $request->addSelect("DISTINCT chir_id");
        $request->addTable("operations");
        $request->addLJoin("sejour ON sejour.sejour_id = operations.sejour_id");
        $request->addWhere("sejour.patient_id = '$this->_id'");

        $praticiens_ids = array_merge($praticiens_ids, $ds->loadColumn($request->makeSelect()));

        array_unique($praticiens_ids);

        // Liste des praticiens
        $prat                  = new CMediusers();
        $this->_ref_praticiens = $prat->loadList(["user_id" => CSQLDataSource::prepareIn($praticiens_ids)]);

        return $this->_ref_praticiens;
    }

    /**
     * Récupération du nom de la categorie socio-professionnelle
     *
     * @return null|string
     */
    function getCSPName()
    {
        // Query
        $select = "SELECT LIBELLE FROM categorie_socioprofessionnelle";
        $where  = "WHERE CODE = '$this->csp'";
        $query  = "$select $where";

        $ds = CSQLDataSource::get("INSEE");

        return $ds->loadResult($query);
    }

    /**
     * Map the class variable with CPerson variable
     *
     * @return void
     */
    function mapPerson()
    {
        $this->_p_city                       = $this->ville;
        $this->_p_postal_code                = $this->cp;
        $this->_p_street_address             = $this->adresse;
        $this->_p_country                    = $this->pays;
        $this->_p_phone_number               = $this->tel;
        $this->_p_mobile_phone_number        = $this->tel2;
        $this->_p_email                      = $this->email;
        $this->_p_first_name                 = $this->prenom;
        $this->_p_last_name                  = $this->nom;
        $this->_p_birth_date                 = $this->naissance;
        $this->_p_maiden_name                = $this->nom_jeune_fille;
        $this->_p_international_phone        = $this->tel_autre;
        $this->_p_international_mobile_phone = $this->tel_autre_mobile;
        $this->_p_phone_area_code            = $this->phone_area_code;
    }

    /**
     * Computes the rests of months, weeks and days once the preceding value is displayed
     * e.g. 1year and 2months and 10 days will return 2months, 1week and 3 days
     *
     * @param DateTimeImmutable|null $to
     *
     * @return array
     * @throws Exception
     */
    function getRestAge(DateTimeImmutable $to = null)
    {
        $to       = ($to) ?: new DateTimeImmutable();
        try {
            $achieved = CMbDT::achievedDurationsDT($this->naissance, $to->format("Y-m-d"));
        } catch (Exception $e) {
            $achieved = ['year' => 0, 'month' => 0, 'week' => 0];
        }

        $rest_months = null;
        $rest_weeks  = null;
        $rest_days   = null;

        if ($this->naissance) {
            $naissance = $this->naissance;

            if (CMbDT::isLunarDate($naissance)) {
                $naissance = CMbDT::lunarToGregorian($naissance);
            }

            $naissance = new DateTime($naissance);
            $diff      = $naissance->diff($to);

            $rest_months = ($achieved["year"] >= 1) ? $diff->m : $achieved["month"];
            // Don't transform days to week until the 3rd week
            $rest_weeks = floor($diff->d / 7);
            $rest_days  = $diff->d - $rest_weeks * 7;
        }

        $locale = "";
        if ($achieved["year"] >= 1) {
            $locale = $achieved["year"] . " " . CAppUI::tr("years");
        }

        if ($achieved["year"] < 15) {
            if ($rest_months > 0) {
                $locale .= ($locale) ? ", " : "";
                $locale .= $rest_months . " ";
                $locale .= ($rest_months > 1) ? CAppUI::tr("months") : CAppUI::tr("month");
            }
            if ($achieved["month"] < 1 && $rest_weeks > 0) {
                $locale .= ($locale) ? ", " : "";
                $locale .= $rest_weeks . " ";
                $locale .= ($rest_weeks > 1) ? CAppUI::tr("weeks") : CAppUI::tr("week");
            }
            if ($achieved["month"] == 0 && $achieved["week"] < 2 && $rest_days > 0) {
                $locale .= $rest_days . " ";
                $locale .= ($rest_days > 1) ? CAppUI::tr("days") : CAppUI::tr("day");
            }
        }

        return [
            "rest_months" => (int)$rest_months,
            "rest_weeks"  => (int)$rest_weeks,
            "rest_days"   => (int)$rest_days,
            "locale"      => $locale,
        ];
    }

    /**
     * Calcul l'âge du patient en semaines
     *
     * @param string $date Date de référence pour le calcul, maintenant si null
     *
     * @return int l'age du patient en semaines
     */
    function evalAgeSemaines($date = null)
    {
        $jours = $this->evalAgeJours($date);

        return intval($jours / 7);
    }

    /**
     * Calcul l'âge du patient en jours
     *
     * @param string $date Date de référence pour le calcul, maintenant si null
     *
     * @return int l'age du patient en jours
     */
    function evalAgeJours($date = null)
    {
        $date = $date ? $date : CMbDT::date();
        if (!$this->naissance || $this->naissance === "0000-00-00") {
            return 0;
        }

        return CMbDT::daysRelative($this->naissance, $date);
    }

    /**
     * Calcul l'âge du patient en mois
     *
     * @param string $date Date de référence pour le calcul, maintenant si null
     *
     * @return int l'age du patient en mois
     */
    function evalAgeMois($date = null)
    {
        $achieved = CMbDT::achievedDurations($this->naissance, $date);

        return $this->_mois = $achieved["month"];
    }

    /**
     * Calcul l'âge du patient en jours
     *
     * @param string $date Date de référence pour le calcul, maintenant si null
     *
     * @return int l'age du patient en jours
     */
    function evalAgeDays($date = null)
    {
        $achieved = CMbDT::achievedDurations($this->naissance, $date);

        return $this->_jours = $achieved["day"];
    }

    /**
     * @see parent::updatePlainFields()
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        // Si réception des données patient depuis un flux sans nom usuel
        if ($this->nom_jeune_fille && !$this->nom) {
            $this->nom = $this->nom_jeune_fille;
        } elseif (!$this->nom_jeune_fille && $this->nom) {
            $this->nom_jeune_fille = $this->nom;
        }

        $soundex2 = new CSoundex2();
        $anonyme  = is_numeric($this->nom);
        if ($this->nom) {
            $this->nom          = self::applyModeIdentitoVigilance($this->nom, false, null, $anonyme);
            $this->nom_soundex2 = $soundex2->build($this->nom);
        }

        if ($this->nom_jeune_fille) {
            $this->nom_jeune_fille = self::applyModeIdentitoVigilance($this->nom_jeune_fille, false, null, $anonyme);
            $this->nomjf_soundex2  = $soundex2->build($this->nom_jeune_fille);
        }

        if ($this->prenom) {
            $this->prenom          = self::applyModeIdentitoVigilance($this->prenom, true, null, $anonyme);
            $this->prenom_soundex2 = $soundex2->build($this->prenom);
        }

        if ($this->prenom_usuel) {
            $this->prenom_usuel = self::applyModeIdentitoVigilance($this->prenom_usuel, true, null, $anonyme);
        }

        if ($this->prenoms) {
            $prenoms = explode(' ', $this->prenoms);

            foreach ($prenoms as $_key => $_prenom) {
                // Cas de la liste des prénoms séparés par plusieurs espaces
                $_prenom = trim($_prenom);
                if (!$_prenom) {
                    unset($prenoms[$_key]);
                    continue;
                }
                $prenoms[$_key] = self::applyModeIdentitoVigilance(trim($_prenom), true, null, $anonyme);
            }

            $this->prenoms = implode(' ', $prenoms);
        }

        if ($this->cp === "00000") {
            $this->cp = "";
        }

        if ($this->assure_nom) {
            $this->assure_nom = self::applyModeIdentitoVigilance($this->assure_nom);
        }

        if ($this->assure_nom_jeune_fille) {
            $this->assure_nom_jeune_fille = self::applyModeIdentitoVigilance($this->assure_nom_jeune_fille);
        }

        if ($this->assure_prenom) {
            $this->assure_prenom = self::applyModeIdentitoVigilance($this->assure_prenom, true);
        }

        if ($this->assure_cp === "00000") {
            $this->assure_cp = "";
        }

        if ($this->_pays_naissance_insee) {
            $this->pays_naissance_insee = CPaysInsee::getPaysNumByNomFR($this->_pays_naissance_insee);
        }

        if ($this->pays) {
            $this->pays_insee = CPaysInsee::getPaysNumByNomFR($this->pays);
        }
        if (!$this->pays && $this->pays_insee) {
            $this->pays = CPaysInsee::getPaysByNumerique($this->pays_insee)->nom_fr;
        }

        if ($this->_assure_pays_naissance_insee) {
            $this->assure_pays_naissance_insee = CPaysInsee::getPaysNumByNomFR($this->_assure_pays_naissance_insee);
        }

        if ($this->assure_pays) {
            $this->assure_pays_insee = CPaysInsee::getPaysNumByNomFR($this->assure_pays);
        }
        if (!$this->assure_pays && $this->assure_pays_insee) {
            $this->assure_pays = CPaysInsee::getPaysByNumerique($this->assure_pays_insee)->nom_fr;
        }

        // Détermine la civilité du patient automatiquement (utile en cas d'import)
        // Ne pas vérifier que la civilité est null (utilisation de updatePlainField dans le loadmatching)
        $this->completeField("civilite");
        if ($this->civilite === "guess") {
            $this->naissance = CMbDT::dateFromLocale($this->naissance);
            $this->evalAge();
            $this->civilite = ($this->_annees < CAppUI::gconf("dPpatients CPatient adult_age")) ?
                "enf" : (($this->sexe === "m") ? "m" : "mme");
        }

        // Détermine la civilité de l'assure automatiquement (utile en cas d'import)
        $this->completeField("assure_civilite");
        if ($this->assure_civilite === "guess") {
            $this->assure_naissance = CMbDT::dateFromLocale($this->assure_naissance);
            $this->evalAgeAssure();
            $sexe                  = $this->assure_sexe ? $this->assure_sexe : $this->sexe;
            $this->assure_civilite = ($this->_age_assure < CAppUI::gconf("dPpatients CPatient adult_age")) ?
                "enf" : (($sexe === "m") ? "m" : "mme");
        }
    }

    /**
     * Calcul l'âge de l'assuré en années
     *
     * @param string $date Date de référence pour le calcul, maintenant si null
     *
     * @return int l'age de l'assuré en années
     */
    function evalAgeAssure($date = null)
    {
        $achieved = CMbDT::achievedDurations($this->assure_naissance, $date);

        return $this->_age_assure = $achieved["year"];
    }

    /**
     * Chargement du statut du patient sur le DMP
     *
     * @return CDmpState|null
     */
    function loadStateDMP()
    {
        if (!CModule::getActive("dmp")) {
            return null;
        }
        //Solution temporaire avant le long ref
        if ($this->_ref_state_dmp) {
            return $this->_ref_state_dmp;
        }

        return $this->_ref_state_dmp = $this->loadUniqueBackRef("state_dmp");
    }

    /**
     * Chargement des demandes AppFine
     *
     * @return CAppFineClientOrderItem[]
     */
    function loadRefsOrdersItem()
    {
        return $this->_ref_orders_item = $this->loadBackRefs("appFine_order_items");
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
     * Chargement du séjour courant du patient
     *
     * @param string $dateTime     Date de référence, maintenant si null
     * @param int    $group_id     group_id, groupe courant si null
     * @param int    $praticien_id mediuser_id si connu
     *
     * @return CSejour[]
     */
    function getCurrSejour($dateTime = null, $group_id = null, $praticien_id = null)
    {
        if (!$dateTime) {
            $dateTime = CMbDT::dateTime();
        }
        if (!$group_id) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        if ($praticien_id) {
            $where["praticien_id"] = "= '$praticien_id'";
        }

        $where["annule"]   = " = '0'";
        $where["group_id"] = "= '$group_id'";
        $where[]           = "'$dateTime' BETWEEN entree AND sortie";

        return $this->loadRefsSejours($where);
    }

    /**
     * Charge les séjours du patient
     *
     * @param array $where SQL where clauses
     * @param int   $limit The limit required
     *
     * @return CSejour[]n loadRef
     */
    function loadRefsSejours($where = [], $limit = null)
    {
        if (!$this->_id) {
            return $this->_ref_sejours = [];
        }

        $sejour   = new CSejour();
        $group_id = CGroups::loadCurrent()->_id;

        $where["patient_id"] = "= '$this->_id'";
        if (CAppUI::gconf("dPpatients sharing multi_group") == "hidden") {
            $where["sejour.group_id"] = "= '$group_id'";
        }

        $order = "entree DESC";

        $sejours = $sejour->loadList($where, $order, $limit);

        $this->_count_all_sejours = $limit ? $sejour->countList($where) : count($sejours);

        return $this->_ref_sejours = $sejours;
    }

    /**
     * Get patient links
     *
     * @return CPatientLink[]
     */
    function loadPatientLinks()
    {
        /** @var CPatientLink[] $links1 */
        $links1 = $this->loadBackRefs("patient_link1");
        /** @var CPatientLink[] $links2 */
        $links2 = $this->loadBackRefs("patient_link2");
        /** @var CPatient[] $patient_link1 */
        $patient_link1 = CPatientLink::massLoadFwdRef($links1, "patient_id2");
        $patient_link2 = CPatientLink::massLoadFwdRef($links2, "patient_id1");
        $patient_link  = $patient_link1 + $patient_link2;
        self::massLoadIPP($patient_link);

        foreach ($links1 as $_link1) {
            $_link1->_ref_patient_doubloon = $patient_link[$_link1->patient_id2];
        }

        foreach ($links2 as $_link2) {
            $_link2->_ref_patient_doubloon = $patient_link[$_link2->patient_id1];
        }

        return $this->_ref_patient_links = $links1 + $links2;
    }

    /**
     * Mass load mechanism for forward references of an object collection
     *
     * @param self[] $patients Array of objects
     * @param string $group_id Tag
     *
     * @return self[] Loaded collection, null if unavailable, with ids as keys of guids for meta references
     */
    static function massLoadIPP($patients, $group_id = null)
    {
        // Aucune configuration de numéro de dossier
        if (null == $tag_ipp = self::getTagIPP($group_id)) {
            foreach ($patients as $_patient) {
                $_patient->_IPP = str_pad($_patient->_id, 6, "0", STR_PAD_LEFT);
            }

            return null;
        }

        // Récupération de la valeur des idex
        $ideces = CIdSante400::massGetMatchFor($patients, $tag_ipp);

        // Association idex-séjours
        foreach ($ideces as $_idex) {
            $patient = $patients[$_idex->object_id];

            if ($patient->_ref_IPP) {
                continue;
            }

            $patient->_ref_IPP = $_idex;
            $patient->_IPP     = $_idex->id400;
        }

        foreach ($patients as $_patient) {
            if ($_patient->_ref_IPP) {
                continue;
            }

            $_patient->_ref_IPP      = new CIdSante400();
            $_patient->_ref_IPP->tag = $tag_ipp;
        }

        return null;
    }

    /**
     * Count the patient link
     *
     * @return int
     */
    function countPatientLinks()
    {
        return $this->countBackRefs("patient_link1") + $this->countBackRefs("patient_link2");
    }

    /**
     * Load external identifiers
     *
     * @param int $group_id Group ID
     *
     * @return void
     */
    function loadExternalIdentifiers($group_id = null)
    {
        // Iconographie de AppFine
        if (CModule::getActive("appFineClient")) {
            CAppFineClient::loadIdex($this, $group_id);
            $this->loadRefStatusPatientUser();
        }

        // Iconographie du portail patient Vivalto
        if (CModule::getActive("vivalto")) {
            CVivalto::loadIdex($this, $group_id);
        }

        // Iconographie du portail patient Doctolib
        if (CModule::getActive("doctolib")) {
            CDoctolib::loadIdex($this, $group_id);
        }
    }

    /**
     * Get the next sejour from today or from a given date
     *
     * @param string $date                Date de début de recherche
     * @param bool   $withOperation       Avec rechreche des interventions
     * @param int    $consult_id          Identifiant de la consultation de référence
     * @param bool   $with_consult_anesth Retourne l'intervention même si elle a déjà une consult. anesth.
     *
     * @return array;
     */
    function getNextSejourAndOperation(
        $date = null,
        $withOperation = true,
        $consult_id = null,
        $with_consult_anesth = true
    ) {
        $sejour = new CSejour();
        $op     = new COperation();
        if (!$date) {
            $date = CMbDT::date();
        }
        if (!$this->_ref_sejours) {
            $this->loadRefsSejours();
        }
        foreach ($this->_ref_sejours as $_sejour) {
            // Conditions d'exlusion du séjour
            if (!in_array($_sejour->type, ["ambu", "comp", "exte"]
                ) || $_sejour->annule || $_sejour->entree_prevue < $date) {
                continue;
            }
            if (!$sejour->_id) {
                $sejour = $_sejour;
            } elseif ($_sejour->entree_prevue < $sejour->entree_prevue) {
                $sejour = $_sejour;
            }

            if (!$withOperation) {
                continue;
            }
            if (!$_sejour->_ref_operations) {
                $_sejour->loadRefsOperations(["annulee" => "= '0'"]);
            }
            foreach ($_sejour->_ref_operations as $_op) {
                $consult_anesth = $_op->loadRefsConsultAnesth();
                if (!$with_consult_anesth && $consult_anesth->_id) {
                    continue;
                }

                if ($consult_id && $consult_anesth->consultation_id == $consult_id) {
                    continue;
                }

                $_op->loadRefPlageOp();
                if (!$op->_id) {
                    $op = $_op;
                } elseif ($_op->_datetime < $op->_datetime) {
                    $op = $_op;
                }
            }
        }

        $sejour->loadRefPraticien()->loadRefFunction();
        $op->loadRefPraticien()->loadRefFunction();

        return ["CSejour" => $sejour, "COperation" => $op];
    }

    /**
     * Get an associative array of uncancelled sejours and their dates
     *
     * @return array Sejour ID => array("entree_prevue" => DATE, "sortie_prevue" => DATE, "datetime_entree" =>
     *               DATETIME, "datetime_sortie" => DATETIME)
     */
    function getSejoursCollisions()
    {
        $sejours_collision = [];
        $group_id          = CGroups::loadCurrent()->_id;

        if ($this->_ref_sejours) {
            foreach ($this->_ref_sejours as $_sejour) {
                if (!$_sejour->annule && $_sejour->group_id == $group_id && !in_array(
                        $_sejour->type,
                        ["urg", "seances", "consult"]
                    )) {
                    $sejours_collision[$_sejour->_id] = [
                        "date_entree"     => CMbDT::date($_sejour->entree),
                        "date_sortie"     => CMbDT::date($_sejour->sortie),
                        "datetime_entree" => $_sejour->entree,
                        "datetime_sortie" => $_sejour->sortie,
                    ];
                }
            }
        }

        return $sejours_collision;
    }

    /**
     * Return the Id of the doubloons
     *
     * @return integer[]|array
     */
    function getDoubloonIds()
    {
        $where = $this->getWhereDoubloon($this, true);

        if (!$where) {
            return [];
        }

        return $this->loadIds($where);
    }

    /**
     * Finds patient siblings with at least two exact matching traits out of
     * nom, prenom, naissance
     * Optimized version with split queries for index usage forcing
     *
     * @return CPatient[] Array of siblings
     */
    function getSiblings()
    {
        $ds =& $this->_spec->ds;

        $where = [
            "nom"        => $ds->prepareLikeName($this->nom),
            "prenom"     => $ds->prepareLikeName($this->prenom),
            "patient_id" => "!= '$this->_id'",
        ];

        if (CAppUI::isCabinet()) {
            $function_id          = CFunctions::getCurrent()->_id;
            $where["function_id"] = "= '$function_id'";
        } elseif (CAppUI::isGroup()) {
            $group_id          = CMediusers::get()->loadRefFunction()->group_id;
            $where["group_id"] = "= '$group_id'";
        }

        $siblings = $this->loadList($where);

        if ($this->naissance !== "0000-00-00") {
            $where = [
                "nom"        => $ds->prepareLikeName($this->nom),
                "naissance"  => $ds->prepare(" = %", $this->naissance),
                "patient_id" => "!= '$this->_id'",
            ];
            if (CAppUI::isCabinet()) {
                $function_id          = CFunctions::getCurrent()->_id;
                $where["function_id"] = "= '$function_id'";
            } elseif (CAppUI::isGroup()) {
                $group_id          = CMediusers::get()->loadRefFunction()->group_id;
                $where["group_id"] = "= '$group_id'";
            }

            $siblings = CMbArray::mergeKeys($siblings, $this->loadList($where, null, null, "patients.patient_id"));

            $where = [
                "prenom"     => $ds->prepareLikeName($this->prenom),
                "naissance"  => $ds->prepare(" = %", $this->naissance),
                "patient_id" => "!= '$this->_id'",
            ];
            if (CAppUI::isCabinet()) {
                $function_id          = CFunctions::getCurrent()->_id;
                $where["function_id"] = "= '$function_id'";
            } elseif (CAppUI::isGroup()) {
                $group_id          = CMediusers::get()->loadRefFunction()->group_id;
                $where["group_id"] = "= '$group_id'";
            }
            $siblings = CMbArray::mergeKeys($siblings, $this->loadList($where, null, null, "patients.patient_id"));
        }

        return $siblings;
    }

    /**
     * Find patient phoning similar
     *
     * @param null $date restrict to a venue collide date
     *
     * @return CPatient[] Array of phoning patients
     * @throws Exception
     */
    public function getPhoning($date = null): array
    {
        $ds       = $this->getDS();
        $whereNom = [
            "nom_soundex2 " . $ds->prepareLike("$this->nom_soundex2%"),
            "nomjf_soundex2 " . $ds->prepareLike("$this->nom_soundex2%"),
        ];
        if ($this->nomjf_soundex2) {
            $whereNom[] = "nom_soundex2 " . $ds->prepareLike("$this->nomjf_soundex2%");
            $whereNom[] = "nomjf_soundex2 " . $ds->prepareLike("$this->nomjf_soundex2%");
        }
        $where   = [
            "prenom_soundex2"     => $ds->prepareLike("$this->prenom_soundex2%"),
            "patients.patient_id" => $ds->prepare("!= ?", $this->_id),
        ];
        $where[] = implode(" OR ", $whereNom);

        $join = null;
        if ($date) {
            $join["sejour"] = "sejour.patient_id = patients.patient_id";
            // Ne pas utiliser de OR entree_prevue / entree_reelle ici: problèmes de performance
            $where["sejour.entree"] = $ds->prepare("<= ?", CMbDT::dateTime("$date 23:59:59"));
            $where["sejour.sortie"] = $ds->prepare(">= ?", CMbDT::dateTime("$date 00:00:00"));
        }

        if (CAppUI::isCabinet()) {
            $function_id          = CFunctions::getCurrent()->_id;
            $where["function_id"] = $ds->prepare("= ?", $function_id);
        } elseif (CAppUI::isGroup()) {
            $group_id                   = CMediusers::get()->loadRefFunction()->group_id;
            $where["patients.group_id"] = $ds->prepare("= ?", $group_id);
        }

        return $this->loadList($where, null, null, "patients.patient_id", $join);
    }

    /**
     * Vérification de la similarité du patient avec un autre nom / prénom
     *
     * @param string $nom    Nom à tester
     * @param string $prenom Prénom à taster
     * @param bool   $strict Test strict
     *
     * @return bool
     */
    function checkSimilar($nom, $prenom, $strict = true)
    {
        $soundex2 = new CSoundex2;

        $testNom    = CMbString::lower($this->nom_soundex2) == CMbString::lower($soundex2->build($nom));
        $testPrenom = CMbString::lower($this->prenom_soundex2) == CMbString::lower($soundex2->build($prenom));

        if ($strict) {
            return ($testNom && $testPrenom);
        } else {
            return ($testNom || $testPrenom);
        }
    }

    /**
     * Chargement du dossier tiers
     *
     * @param array  $where       where
     * @param string $backNameAlt backNameAlt
     *
     * @return CDossierTiers[]
     */
    function loadRefsDossierTiers($where = [], $backNameAlt = null)
    {
        return $this->_ref_dossiers_tiers = $this->loadBackRefs(
            "dossiers_tiers",
            null,
            null,
            null,
            null,
            null,
            $backNameAlt,
            $where
        );
    }

    /**
     * Load the patient state
     *
     * @return CPatientState[]|null
     */
    function loadRefPatientState()
    {
        $this->_ref_patient_states = $this->loadBackRefs("patient_state");

        foreach ($this->_ref_patient_states as $_patient_state) {
            if ($_patient_state->state === 'HOMD') {
                $this->_homonyme = true;
            } elseif ($_patient_state->state === 'DOUT') {
                $this->_douteux = true;
            } elseif ($_patient_state->state === 'FICTI') {
                $this->_fictif = true;
            }
        }

        return $this->_ref_patient_states;
    }

    /**
     * Load patient user API of a patient
     *
     * @return CPatientUserAPI
     */
    function loadRefPatientUserAPI($where = [])
    {
        return $this->_ref_patient_user_api = $this->loadUniqueBackRef(
            "patient_user_api",
            null,
            null,
            null,
            null,
            null,
            $where
        );
    }

    /**
     * Chargement des periodes d'allaitement
     *
     * @param string $order ordre de récupération
     *
     * @return CAllaitement[]|null
     */
    function loadRefsAllaitements($order = "date_fin DESC")
    {
        return $this->_ref_allaitements = $this->loadBackRefs("allaitements", $order);
    }

    /**
     * @see parent::loadRefsBack()
     */
    function loadRefsBack()
    {
        parent::loadRefsBack();
        $this->loadRefsFiles();
        $this->loadRefsDocs();
        $this->loadRefsConsultations();
        $this->loadRefsCorrespondants();
        $this->loadRefsAffectations();
        $this->loadRefsPrescriptions();
        $this->loadRefsGrossesses();
    }

    /**
     * @inheritdoc
     */
    function loadRefsDocs($where = [], bool $with_canelled = true)
    {
        $docs_valid = parent::loadRefsDocs($where, $with_canelled);
        if ($docs_valid) {
            $this->_nb_docs .= "$docs_valid";
        }

        return $docs_valid;
    }

    /**
     * Charge les consultations du patient
     *
     * @param array|null $where [optional] Clauses SQL
     * @param int|null   $limit [optional] Limit of consultations loaded
     *
     * @return CConsultation[]
     * @throws Exception
     */
    function loadRefsConsultations($where = null, int $limit = null)
    {
        if (!$this->_id) {
            return $this->_ref_consultations = [];
        }

        [$where, $ljoin, $order] = self::getConstraintsForConsultations($where, $limit);

        $this->_ref_consultations = $this->loadBackRefs(
            'consultations',
            $order,
            $limit,
            null,
            $ljoin,
            null,
            null,
            $where
        );

        CStoredObject::massLoadFwdRef($this->_ref_consultations, "plageconsult_id");

        foreach ($this->_ref_consultations as $_consult) {
            $_consult->loadRefPlageConsult();
        }

        CMbArray::pluckSort($this->_ref_consultations, SORT_DESC, "_datetime");

        return $this->_ref_consultations;
    }

    public static function getConstraintsForConsultations($where = null, $limit = null): array
    {
        $group_id     = CGroups::loadCurrent()->_id;
        $curr_user    = CMediusers::get();
        $multi_group  = CAppUI::gconf("dPpatients sharing multi_group");
        $order        = null;
        $ljoin        = null;

        if ($where === null) {
            $where = [];
        }

        if (!$curr_user->isAdmin() || $multi_group == "hidden" || count($where) || $limit) {
            $order = "plageconsult.date DESC, consultation.heure DESC";

            $ljoin = [
                "plageconsult"        => "consultation.plageconsult_id = plageconsult.plageconsult_id",
                "users_mediboard"     => "plageconsult.chir_id = users_mediboard.user_id",
                "functions_mediboard" => "users_mediboard.function_id = functions_mediboard.function_id",
            ];

            if (!$curr_user->isAdmin()) {
                $where[] = "functions_mediboard.consults_events_partagees = '1' || functions_mediboard.function_id = '$curr_user->function_id'";
            }

            if ($multi_group == "hidden") {
                $where["functions_mediboard.group_id"] = "= '$group_id'";
            }
        }

        return [$where, $ljoin, $order];
    }

    /**
     * Chargement des correspondants médicaux
     *
     * @return CCorrespondant[]|null
     */
    public function loadRefsCorrespondants(): array
    {
        // Médecin traitant
        $this->loadRefMedecinTraitant();

        // Pharmacie de ville
        $this->loadRefPharmacie();

        // Autres correspondant
        $this->_ref_medecins_correspondants = $this->loadBackRefs("correspondants");
        foreach ($this->_ref_medecins_correspondants as $corresp) {
            $corresp->loadRefsFwd();
        }

        return $this->_ref_medecins_correspondants;
    }

    /**
     * Chargement du médecin traitant
     *
     * @return CMedecin
     */
    public function loadRefMedecinTraitant(): CMedecin
    {
        return $this->_ref_medecin_traitant = $this->loadFwdRef("medecin_traitant", true);
    }

    public function loadRefMedecinTraitantExercicePlace(): CMedecinExercicePlace
    {
        return $this->_ref_medecin_traitant_exercice_place =
            $this->loadFwdRef('medecin_traitant_exercice_place_id', true);
    }

    /**
     * Chargement de la pharmacie
     *
     * @return CMedecin
     */
    function loadRefPharmacie()
    {
        return $this->_ref_pharmacie = $this->loadFwdRef("pharmacie_id", true);
    }

    /**
     * Chargement des affectations courantes et à venir du patient
     *
     * @param string $date Date de référence
     *
     * @return void
     */
    function loadRefsAffectations($date = null)
    {
        $affectation = new CAffectation();

        // Affectations inactives
        if (!$affectation->_ref_module) {
            $this->_ref_curr_affectation = null;
            $this->_ref_next_affectation = null;
        }

        if (!$date) {
            $date = CMbDT::dateTime();
        }

        $group   = CGroups::loadCurrent();
        $sejours = $this->loadBackIds(
            "sejours",
            null,
            null,
            null,
            null,
            ["group_id" => "= '$group->_id'", "annule" => " = '0'"]
        );

        $where = [
            "affectation.sejour_id" => CSQLDataSource::prepareIn($sejours),
        ];

        $order = "affectation.entree";

        // Affection courante
        $this->_ref_curr_affectation = new CAffectation();
        $where["affectation.entree"] = "<  '$date'";
        $where["affectation.sortie"] = ">= '$date'";
        $this->_ref_curr_affectation->loadObject($where, $order, null, null, 'sejour_id');
        $this->_ref_curr_affectation->updateView();

        // Prochaine affectations
        $this->_ref_next_affectation = new CAffectation();
        $where["affectation.entree"] = "> '$date'";
        $this->_ref_next_affectation->loadObject($where, $order, null, null, 'sejour_id');
        $this->_ref_next_affectation->updateView();
    }

    /**
     * Chargement des prescriptions de labo (module dPlabo)
     *
     * @param int $perm niveau de permission
     *
     * @return void
     */
    function loadRefsPrescriptions($perm = null)
    {
        if (CModule::getInstalled("dPlabo")) {
            $prescription             = new CPrescriptionLabo();
            $where                    = ["patient_id" => "= '$this->_id'"];
            $order                    = "date DESC";
            $this->_ref_prescriptions = $prescription->loadListWithPerms($perm, $where, $order);
        }
    }

    /**
     * Chargement des grossesses de la patients
     *
     * @param string $order ordre de récupération
     *
     * @return CGrossesse[]|null
     */
    function loadRefsGrossesses($order = "terme_prevu DESC")
    {
        return $this->_ref_grossesses = $this->loadBackRefs("grossesses", $order);
    }

    function loadListConstantesMedicales($where = [])
    {
        if ($this->_list_constantes_medicales) {
            return $this->_list_constantes_medicales;
        }

        return $this->_list_constantes_medicales = $this->loadRefsConstantesMedicales("datetime ASC");
    }

    /**
     * Load all the backrefs constantes of a patient
     *
     * @param string $order ordre de récupération
     *
     * @return CConstantesMedicales[]
     */
    function loadRefsConstantesMedicales($order = null)
    {
        return $this->_refs_all_contantes_medicales = $this->loadBackRefs("constantes", $order);
    }

    /**
     * @throws Exception
     * @see parent::loadComplete()
     */
    function loadComplete()
    {
        parent::loadComplete();
        $this->loadIPP();
        $this->loadRefPhotoIdentite();
        $this->loadRefsCorrespondantsPatient(null, true);
        $this->loadRefDossierMedical();
        $this->loadRefPatientINSNIR();
        $this->updateBMRBHReStatus();
        $this->_ref_dossier_medical->canDo();
        $this->_ref_dossier_medical->loadRefsAntecedents();
        $this->_ref_dossier_medical->loadRefsTraitements();
        $prescription = $this->_ref_dossier_medical->loadRefPrescription();

        if ($prescription && is_array($prescription->_ref_prescription_lines)) {
            foreach ($prescription->_ref_prescription_lines as $_line) {
                $_line->loadRefsPrises();
            }
        }

        $this->loadRefLatestConstantes(null, ["poids", "taille"]);
        $const_med = $this->_ref_constantes_medicales;

        if ($const_med) {
            $this->_poids  = $const_med->poids;
            $this->_taille = $const_med->taille;
        }

        if ($this->_id && CAppUI::gconf("dPpatients sharing patient_data_sharing")) {
            $this->_sharing_groups = $this->getSharingGroupsByStatus();
        }
        $this->updateNomPaysInsee();
    }

    /**
     * Chargement de la photo d'identité
     *
     * @return CFile
     */
    function loadRefPhotoIdentite()
    {
        $file                 = CFile::loadNamed($this, "identite.jpg");
        $this->_can_see_photo = 1;
        if ($file->_id) {
            $author = $file->loadRefAuthor();
            global $can;
            $this->_can_see_photo = ($can && $can->admin) || CAppUI::$user->function_id == $author->function_id;
        }

        return $this->_ref_photo_identite = $file;
    }

    /**
     * Chargement des correspondants non médicaux du patient
     *
     * @param string $order Classement des correspondants
     *
     * @return CCorrespondantPatient[]|null
     */
    function loadRefsCorrespondantsPatient($order = null, $only_valide = false)
    {
        $this->_ref_correspondants_patient = $this->loadBackRefs("correspondants_patient", $order);

        if ($only_valide) {
            $now = CMbDT::date();
            foreach ($this->_ref_correspondants_patient as $_correspondant_patient) {
                if (($_correspondant_patient->date_debut && $_correspondant_patient->date_debut > $now) ||
                    ($_correspondant_patient->date_fin && $_correspondant_patient->date_fin < $now)) {
                    unset($this->_ref_correspondants_patient[$_correspondant_patient->_id]);
                }
            }
        }
        $correspondant             = new CCorrespondantPatient();
        $this->_ref_cp_by_relation = [];
        foreach (explode("|", $correspondant->_specs["relation"]->list) as $_relation) {
            $this->_ref_cp_by_relation[$_relation] = [];
        }

        foreach ($this->_ref_correspondants_patient as $_correspondant) {
            $this->_ref_cp_by_relation[$_correspondant->relation][$_correspondant->_id] = $_correspondant;
        }

        return $this->_ref_correspondants_patient;
    }

    /**
     * Chargement du dossier médical
     *
     * @param bool $load_refs_back Avec chargement des backrefs
     *
     * @return CDossierMedical
     */
    function loadRefDossierMedical($load_refs_back = true)
    {
        $this->_ref_dossier_medical = $this->loadUniqueBackRef("dossier_medical");

        if ($load_refs_back) {
            $this->_ref_dossier_medical->loadRefsBack();
        }

        return $this->_ref_dossier_medical;
    }

    public function updateBMRBHReStatus(CStoredObject $context = null): void
    {
        $bmr_bhre   = $this->loadRefBMRBHRe();
        if (!$bmr_bhre->_id) {
            return;
        }
        $date_debut = null;
        $date_fin   = null;
        if (!$context) {
            if (
                $bmr_bhre->bmr === "1" ||
                $bmr_bhre->bhre === "1"
            ) {
                $this->_bmr_bhre_status[$bmr_bhre->bmr ? "BMR+" : "BHReP"] = "red";
            }

            if (
                $bmr_bhre->hospi_etranger === "1" ||
                $bmr_bhre->rapatriement_sanitaire === "1" ||
                $bmr_bhre->ancien_bhre === "1"
            ) {
                $this->_bmr_bhre_status["BHReR"] = "hotpink";
            }

            if ($bmr_bhre->bhre_contact === "1") {
                $this->_bmr_bhre_status["BHReC"] = "orange";
            }

            return;
        }
        switch (get_class($context)) {
            case CSejour::class:
                /** @var CSejour $context */
                $date_debut = CMbDT::date($context->entree);
                $date_fin   = CMbDT::date($context->sortie);
                break;
            case CConsultation::class:
                /** @var CConsultation $context */
                $date_debut = $context->_ref_plageconsult->date;
                $date_fin   = $date_debut;
                break;
            case COperation::class:
            default:
                /** @var COperation $context */
                $date_debut = $context->date;
                $date_fin   = $date_debut;
        }

        $this->_bmr_bhre_status = [];
        if (
            (
                (
                    $bmr_bhre->bmr_debut &&
                    $bmr_bhre->bmr_debut <= $date_fin
                ) &&
                (
                    $bmr_bhre->bmr_fin &&
                    $date_debut <= $bmr_bhre->bmr_fin
                )
            )
            ||
            ((!$bmr_bhre->bmr_fin && $bmr_bhre->bmr_debut) && ($bmr_bhre->bmr_debut <= $date_debut))
        ) {
            $this->_bmr_bhre_status["BMR+"] = "red";
        }

        if (
            (
                (
                    $bmr_bhre->bhre_debut &&
                    $bmr_bhre->bhre_debut <= $date_fin
                ) &&
                (
                    $bmr_bhre->bhre_fin &&
                    $date_debut <= $bmr_bhre->bhre_fin
                )
            )
            ||
            (
                (!$bmr_bhre->bhre_fin && $bmr_bhre->bhre_debut) &&
                ($bmr_bhre->bhre_debut <= $date_debut)
            )
        ) {
            $this->_bmr_bhre_status["BHReP"] = "red";
        }


        if (
            (
                (
                    $bmr_bhre->hospi_etranger_debut &&
                    $bmr_bhre->hospi_etranger_debut <= $date_fin
                ) &&
                (
                    $bmr_bhre->hospi_etranger_fin &&
                    $date_debut <= $bmr_bhre->hospi_etranger_fin
                )
            ) ||
            (
                (!$bmr_bhre->hospi_etranger_fin && $bmr_bhre->hospi_etranger_debut) &&
                ($bmr_bhre->hospi_etranger_debut <= $date_debut)
            )
        ) {
            $this->_bmr_bhre_status["BHReR"] = "hotpink";
        }

        if (
            (
                (
                    $bmr_bhre->bhre_contact_debut &&
                    $bmr_bhre->bhre_contact_debut <= $date_fin
                ) &&
                (
                    $bmr_bhre->bhre_contact_fin &&
                    $date_debut <= $bmr_bhre->bhre_contact_fin
                )
            )
            ||
            (
                (!$bmr_bhre->bhre_contact_fin && $bmr_bhre->bhre_contact_debut) &&
                ($bmr_bhre->bhre_contact_debut <= $date_debut)
            )
        ) {
            $this->_bmr_bhre_status["BHReC"] = "orange";
        }
    }

    /** Charge le BMR BHRe du patient
     *
     * @return CBMRBHRe
     */
    function loadRefBMRBHRe()
    {
        return $this->_ref_bmr_bhre = $this->loadUniqueBackRef("bmr_bhre");
    }

    /**
     * Load the latest constants of the patient
     *
     * @param string    $datetime  The reference datetime
     * @param array     $selection A selection of constantes to load
     * @param CMbObject $context   A particular context
     * @param boolean   $use_cache Force the function to return the latest_values is already set
     *
     * @return array
     */
    function loadRefLatestConstantes($datetime = null, $selection = [], $context = null, $use_cache = true)
    {
        $latest = CConstantesMedicales::getLatestFor($this, $datetime, $selection, $context, $use_cache);

        [$this->_ref_constantes_medicales, $this->_latest_constantes_dates] = $latest;
        $this->_ref_constantes_medicales->updateFormFields();

        return $latest;
    }

    /**
     * Gets sharing groups, ordered by status (allowed, denied, not asked)
     *
     * @return array
     */
    function getSharingGroupsByStatus()
    {
        $groups         = CGroups::get()->loadList();
        $known_groups   = [];
        $patient_groups = [
            'allowed' => [],
            'denied'  => [],
            'unknown' => [],
        ];

        /** @var CPatientGroup[] $sharing_groups */
        $sharing_groups = $this->loadSharingGroups();

        foreach ($sharing_groups as $_sharing_group) {
            $_sharing_group->loadRefUser();
            $_sharing_group->loadRefGroup();

            if ($_sharing_group->share) {
                $patient_groups['allowed'][] = $_sharing_group;
                $known_groups[]              = $_sharing_group->group_id;
            } else {
                $patient_groups['denied'][] = $_sharing_group;
                $known_groups[]             = $_sharing_group->group_id;
            }
        }

        $groups_ids     = CMbArray::pluck($groups, '_id');
        $unknown_groups = array_diff($groups_ids, $known_groups);

        foreach ($unknown_groups as $_group_id) {
            $patient_groups['unknown'][] = $groups[$_group_id];
        }

        return $this->_sharing_groups = $patient_groups;
    }

    /**
     * Load links between this patient and all groups
     *
     * @return CStoredObject[]|array
     */
    function loadSharingGroups()
    {
        if (!$this->_id) {
            return [];
        }

        $patient_group             = new CPatientGroup();
        $patient_group->patient_id = $this->_id;

        return $this->_ref_patient_groups = $patient_group->loadMatchingList();
    }

    /**
     * Chargement du dossier complet de consultation
     *
     * @param null $permType            Niveau de permission
     * @param bool $hide_consult_sejour Cache les consultations de séjour
     * @param int  $limit               The limit required
     *
     * @return void
     */
    function loadDossierComplet($permType = null, $hide_consult_sejour = true, $limit = null)
    {
        $this->_total_docs = 0;

        if (!$this->_id) {
            return;
        }

        // Patient permission
        $this->canDo();

        // Doc items
        $this->loadRefsFiles();
        $this->loadRefsDocs();
        $this->_total_docs += $this->countDocItems($permType);

        // Photos et Notes
        $this->loadRefPhotoIdentite();
        $this->loadRefsNotes();

        // Correspondants
        $this->loadRefsCorrespondants();
        $this->loadRefsCorrespondantsPatient(null, true);

        // Affectations courantes
        $this->loadRefsAffectations();
        $affectation = $this->_ref_curr_affectation;
        if ($affectation && $affectation->_id) {
            $affectation->loadRefsFwd();
            $affectation->_ref_lit->loadCompleteView();
        }

        $affectation = $this->_ref_next_affectation;
        if ($affectation && $affectation->affectation_id) {
            $affectation->loadRefsFwd();
            $affectation->_ref_lit->loadCompleteView();
        }

        $maternite_active = CModule::getActive("maternite");
        if ($maternite_active) {
            $this->loadRefsGrossesses();
        }

        // Consultations
        $this->loadRefsConsultations();
        CStoredObject::massLoadFwdRef($this->_ref_consultations, 'plageconsult_id');
        CStoredObject::massLoadBackRefs($this->_ref_consultations, "examaudio");
        CStoredObject::massLoadBackRefs($this->_ref_consultations, "examnyha");
        CStoredObject::massLoadBackRefs($this->_ref_consultations, "exampossum");
        CStoredObject::massLoadBackRefs($this->_ref_consultations, 'examcomp');
        CMbObject::massCountDocItems($this->_ref_consultations);

        if (CModule::getActive("transport")) {
            CStoredObject::massLoadBackRefs(
                $this->_ref_consultations,
                'transports',
                'transport_id DESC, datetime DESC',
                ['transport.statut' => "<> 'prescribed'"]
            );
        }

        $consult_anesths = CStoredObject::massLoadBackRefs($this->_ref_consultations, 'consult_anesth');
        CMbObject::massCountDocItems($consult_anesths);

        foreach ($this->_ref_consultations as $consult) {
            if ($consult->sejour_id && $hide_consult_sejour) {
                unset($this->_ref_consultations[$consult->_id]);
                continue;
            }

            $consult->loadRefConsultAnesth();
            $consult->loadRefsFichesExamen();
            $consult->loadRefsExamsComp();
            if (!count($consult->_refs_dossiers_anesth)) {
                $this->_total_docs += $consult->countDocItems($permType);
            }

            // Praticien
            $consult->getType();
            $praticien = $consult->_ref_praticien;

            $praticien->loadRefFunction()->loadRefGroup();

            foreach ($consult->_refs_dossiers_anesth as $_dossier_anesth) {
                $_dossier_anesth->_ref_consultation = $consult;
                $this->_total_docs                  += $_dossier_anesth->countDocItems($permType);
            }

            // Grossesse
            if ($maternite_active && $consult->grossesse_id && isset($this->_ref_grossesses[$consult->grossesse_id])) {
                $result                                                                           = $this->_ref_grossesses[$consult->grossesse_id]->getAgeGestationnel(
                    $consult->_date
                );
                $consult->_semaine_grossesse                                                      = $result["SA"];
                $this->_ref_grossesses[$consult->grossesse_id]->_ref_consultations[$consult->_id] = $consult;
            }

            // Permission
            $consult->canDo();
        }

        // Sejours
        $this->loadRefsSejours([], $limit);
        CStoredObject::massLoadFwdRef($this->_ref_sejours, "praticien_id");
        CStoredObject::massLoadBackRefs($this->_ref_sejours, "affectations", "sortie DESC");
        CStoredObject::massLoadBackRefs($this->_ref_sejours, 'rpu');

        $operations = CStoredObject::massLoadBackRefs($this->_ref_sejours, 'operations', 'date DESC');
        CStoredObject::massLoadFwdRef($operations, 'plageop_id');
        $this->_total_docs += CMbObject::massCountDocItems($this->_ref_sejours);
        $this->_total_docs += CMbObject::massCountDocItems($operations);

        $consultations = CStoredObject::massLoadBackRefs(
            $this->_ref_sejours,
            'consultations',
            'date DESC, heure DESC',
            null,
            ['plageconsult' => 'plageconsult.plageconsult_id = consultation.plageconsult_id']
        );
        CStoredObject::massLoadFwdRef($consultations, 'plageconsult_id');
        CStoredObject::massLoadBackRefs($consultations, "examaudio");
        CStoredObject::massLoadBackRefs($consultations, "examnyha");
        CStoredObject::massLoadBackRefs($consultations, "exampossum");
        CStoredObject::massLoadBackRefs($consultations, 'examcomp');
        $consult_anesths = CStoredObject::massLoadBackRefs($consultations, 'consult_anesth');
        CMbObject::massCountDocItems($consultations);
        CMbObject::massCountDocItems($consult_anesths);
        CSejour::massLoadNDA($this->_ref_sejours);
        $affectations = CStoredObject::massLoadBackRefs($this->_ref_sejours, 'affectations', 'sortie DESC');
        CAffectation::massUpdateView($affectations);

        foreach ($this->_ref_sejours as $_sejour) {
            // Permission
            $_sejour->canDo();

            // Praticien
            $_sejour->loadRefPraticien(1);

            $_sejour->countDocItems($permType);
            if ($maternite_active && $_sejour->grossesse_id) {
                $this->_ref_grossesses[$_sejour->grossesse_id]->_ref_sejours[$_sejour->_id] = $_sejour;
            }

            $_sejour->loadRefsOperations([], "date DESC");
            foreach ($_sejour->_ref_operations as $_operation) {
                $_operation->canDo();

                // Praticien
                $praticien = $_operation->loadRefPraticien(1);
                $praticien->loadRefFunction();

                // Autres
                $_operation->loadRefPlageOp(1);
                $_operation->countDocItems($permType);

                // Consultation préanesthésique
                $consult_anesth = $_operation->loadRefsConsultAnesth();
                $consult_anesth->countDocItems($permType);

                $consultation = $consult_anesth->loadRefConsultation();
                $consultation->canRead();
                $consultation->canEdit();
            }

            // RPU
            $rpu = $_sejour->loadRefRPU();
            if ($rpu && $rpu->_id) {
                $this->_total_docs += $rpu->countDocItems($permType);
            }

            $_sejour->loadRefsConsultations();

            foreach ($_sejour->_ref_consultations as $_consult) {
                $_consult->loadRefConsultAnesth();
                $_consult->loadRefsFichesExamen();
                $_consult->loadRefsExamsComp();
                if (!count($_consult->_refs_dossiers_anesth)) {
                    $_consult->countDocItems($permType);
                }
                $_consult->loadRefsFwd(1);
                $_consult->_ref_sejour = $_sejour;
                $_consult->getType();
                $_consult->_ref_chir->loadRefFunction();
                $_consult->_ref_chir->_ref_function->loadRefGroup();
                $_consult->canDo();

                foreach ($_consult->_refs_dossiers_anesth as $_dossier_anesth) {
                    $_dossier_anesth->_ref_consultation = $_consult;
                    $_dossier_anesth->countDocItems($permType);
                }
            }
        }
    }

    /**
     * Load the INS of the patient
     *
     * @return CINSPatient[]|CStoredObject[]
     * @throws Exception
     */
    function loadRefsINS()
    {
        return $this->_refs_ins = $this->loadBackRefs("ins_patient", "date DESC");
    }

    /**
     * Load the patient INS-NIR of the patient
     *
     * @return CPatientINSNIR|CStoredObject
     * @throws Exception
     */
    function loadRefPatientINSNIR()
    {
        return $this->_ref_patient_ins_nir = $this->loadUniqueBackRef(
            "patient_ins_nir", null, null, null,
            [
                'patients' => 'patients.source_identite_id = patient_ins_nir.source_identite_id',
            ],
            'patient_ins_nir',
            [
                'patients.patient_id' => '= patient_ins_nir.patient_id'
            ]
        );
    }

    /**
     * Update link ENS
     *
     * @param string $link_ens
     *
     * @return CIdSante400
     * @throws Exception
     */
    function updateLinkENS($link_ens): CIdSante400
    {
        $idex_ens        = CIdSante400::getMatchFor($this, CPatient::TAG_MES);
        $idex_ens->id400 = $link_ens;
        $idex_ens->store();

        return $idex_ens;
    }

    /**
     * Check if patient link with ENS
     *
     * @return bool
     */
    function isLinkENS(): bool
    {
        $idex_ens = CIdSante400::getMatchFor($this, CPatient::TAG_MES);
        if (!$idex_ens || !$idex_ens->_id) {
            $this->_is_link_ens = 0;

            return false;
        }

        return $this->_is_link_ens = $idex_ens->id400;
    }

    /**
     * Get INS-NIR
     *
     * @return string
     * @throws Exception
     */
    function getINSNIR()
    {
        return $this->loadRefPatientINSNIR()->ins_nir;
    }

    /**
     * Chargement du PatientUser
     *
     * @param array $where where
     *
     * @return CPatientUser[]|CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefsPatientUsers(array $where = []): array
    {
        return $this->_ref_patient_users = $this->loadBackRefs(
            "patient_user",
            null,
            null,
            null,
            null,
            null,
            "",
            $where
        );
    }

    /**
     * Chargement du dernier PatientUser
     *
     * @param int $group_id group id
     *
     * @return CPatientUser
     * @throws Exception
     */
    function loadRefLastPatientUser($group_id = null)
    {
        $patient_user             = new CPatientUser();
        $patient_user->patient_id = $this->_id;
        $patient_user->group_id   = $group_id;
        $patient_user->loadMatchingObjectEsc("create_datetime DESC");

        return $this->_ref_last_patient_user = $patient_user;
    }

    /**
     * Chargement de l'utilisateur
     *
     * @param bool $last last or first
     *
     * @return CUser
     * @throws Exception
     */
    function loadRefUser($last = true)
    {
        $patient_user             = new CPatientUser();
        $patient_user->patient_id = $this->_id;
        $patient_user->loadMatchingObjectEsc($last ? "create_datetime DESC" : "create_datetime ASC");

        if (!$patient_user->_id) {
            return $this->_ref_user = null;
        }

        return $this->_ref_user = $patient_user->loadRefUser();
    }

    /**
     * Trash IPP
     *
     * @param CIdSante400 $IPP IPP
     *
     * @return string
     */
    function trashIPP(CIdSante400 $IPP)
    {
        if (!$IPP->_id) {
            return null;
        }

        $IPP->tag = CAppUI::conf("dPpatients CPatient tag_ipp_trash") . $IPP->tag;

        return $IPP->store();
    }

    function getTemplateClasses()
    {
        $tab               = [];
        $tab['CPatient']   = $this->_id;
        $tab['CSejour']    = 0;
        $tab['COperation'] = 0;

        return $tab;
    }

    /**
     * Get first constantes
     *
     * @return CConstantesMedicales
     */
    function getFirstConstantes()
    {
        return $this->_ref_first_constantes = $this->loadFirstBackRef("constantes", "datetime ASC");
    }

    function getLastConstantes()
    {
        return $this->_ref_last_constantes = $this->loadLastBackRef("constantes", "datetime ASC");
    }

    /**
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template)
    {
        $this->fillLimitedTemplate($template);
        $this->loadRefBMRBHRe()->fillTemplate($template);

        // Dossier médical
        $this->loadRefDossierMedical();
        $this->_ref_dossier_medical->fillTemplate($template);

        $this->loadRefPatientINSNIR();
        $this->_ref_patient_ins_nir->fillTemplate($template);
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template, $champ = "Patient", $with_corresp = true)
    {
        if ($champ == "Patient") {
            $champ = CAppUI::tr('CPatient');
        }

        $this->loadRefsFwd();
        $this->loadRefLatestConstantes(null, [], null, false);
        $this->loadIPP();
        $this->loadLastINS();
        $this->loadRefPatientINSNIR();
        $this->loadCodeInseeNaissance();

        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        // Situation
        $situation = CAppUI::tr('CPatient-Situation');
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-situation_famille'),
            $this->getFormattedValue("situation_famille")
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-mdv_familiale'),
            $this->getFormattedValue("mdv_familiale")
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-condition_hebergement'),
            $this->getFormattedValue("condition_hebergement")
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-niveau_etudes'),
            $this->getFormattedValue("niveau_etudes")
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-activite_pro'),
            $this->getFormattedValue("activite_pro")
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-activite_pro_date-doc'),
            $this->getFormattedValue("activite_pro_date")
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-activite_pro_rques-desc'),
            $this->activite_pro_rques
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-csp'),
            $this->csp ? $this->getCSPName() : ""
        );
        $template->addProperty(
            "$champ - $situation - " . CAppUI::tr('CPatient-fatigue_travail'),
            $this->getFormattedValue("fatigue_travail")
        );
        $template->addProperty("$champ - $situation - " . CAppUI::tr('CPatient-travail_hebdo'), $this->travail_hebdo);
        $template->addProperty("$champ - $situation - " . CAppUI::tr('CPatient-transport_jour'), $this->transport_jour);

        $template->addProperty("$champ - " . CAppUI::tr('CPatient-article'), $this->_civilite);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-long article'), ucfirst($this->_civilite_long));
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-long article (lowercase)'),
            strtolower($this->_civilite_long)
        );
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-_source_nom'), $this->nom);// Nom utilisé
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-_source_nom_jeune_fille'),
            $this->nom_jeune_fille
        );// Nom de naissance
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-_source_prenom'),
            $this->prenom
        );// Premier prénom de naissance
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-prenoms'), $this->prenoms);// Prénoms
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-_source_prenom_usuel'),
            $this->prenom_usuel
        );// Prénom usuel
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-address'), $this->adresse);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-city'), $this->ville);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-ZIP code-court'), $this->cp);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-years'), $this->_annees);

        $diff = date_diff(new DateTimeImmutable($this->naissance), new DateTimeImmutable(CMbDT::date()));

        if ($diff->y === 0 && $diff->m < 1) {
            $days = $diff->days . ' ' . CAppUi::tr('day' . ($diff->days > 1 ? 's' : ''));
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-age'), $days);
        } elseif ($diff->y === 0 && $diff->m < 6) {
            $weeks = intval($diff->d / 7);
            if ($weeks >= 4) {
                $diff->m++;
                $weeks = 0;
            } elseif ($diff->d % 7 > 3) {
                $weeks++;
            }
            $months    = $diff->m ? $diff->m . ' ' . CAppUi::tr('month') : '';
            $separator = $diff->m && $weeks ? ' + ' : '';
            $weeks     = $weeks ? $weeks . ' ' . CAppUi::tr('week' . ($weeks > 1 ? 's' : '')) : '';
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-age'), $months . $separator . $weeks);
        } else {
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-age'), $this->_age);
        }

        $template->addProperty("$champ - " . CAppUI::tr('CPatient-birth date'), $this->getFormattedValue("naissance"));
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-place of birth'), $this->lieu_naissance);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-code-insee_birth'), $this->commune_naissance_insee);
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-pays_naissance_insee'),
            (!$this->pays_naissance_insee || $this->pays_naissance_insee == CPaysInsee::NUMERIC_FRANCE)
                ? ""
                : (CPaysInsee::getPaysByNumerique($this->pays_naissance_insee))->code_insee
        );
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-_code_insee'), $this->_code_insee);
        $template->addDateProperty("$champ - " . CAppUI::tr('CPatient-date of death'), $this->deces);
        $template->addTimeProperty("$champ - " . CAppUI::tr('CPatient-death time'), $this->deces);
        $this->loadRefsPatientHandicaps();

        $handicap = [];
        foreach ($this->_refs_patient_handicaps as $_handicap){
            $handicap[] = CAppUI::tr("CPatientHandicap.handicap.$_handicap->handicap");
        }

        $template->addListProperty("$champ - " . CAppUI::tr('CPatientHandicap-handicap'), $handicap);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-sex'), strtolower($this->getFormattedValue("sexe")));
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-short sex'),
            substr(strtolower($this->getFormattedValue("sexe")), 0, 1)
        );
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-insured number'),
            $this->getFormattedValue("matricule")
        );
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-phone'), $this->getFormattedValue("tel"));
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-professional phone'),
            $this->getFormattedValue("tel_pro")
        );
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-mobile phone'), $this->getFormattedValue("tel2"));
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-phone other'), $this->tel_autre);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-e-mail'), $this->getFormattedValue("email"));
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-profession'), $this->profession);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-_IPP'), $this->_IPP);
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-qual_beneficiaire'), $this->qual_beneficiaire);
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-status-desc'),
            CAppUI::tr("CPatient.status.$this->status")
        );
        $template->addProperty(
            "$champ - " . CAppUI::tr('CINSPatient'),
            $this->status == "QUAL" ? $this->getINSNIR() : ""
        );
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatientINSNIR-oid'),
            $this->status == "QUAL" ? $this->loadRefPatientINSNIR()->oid : ""
        );
        $qualite_subItem = CAppUI::tr('CPatient-qual_beneficiaire');
        $this->guessExoneration();
        $template->addProperty("$champ - $qualite_subItem - " . CAppUI::tr('common-Label'), $this->libelle_exo);
        $template->addProperty(
            "$champ - " . CAppUI::tr('CPatient-Social Security number'),
            $this->getFormattedValue("matricule")
        );
        $template->addBarcode("$champ - " . CAppUI::tr('CPatient-ID barcode'), $this->_id);
        $template->addBarcode("$champ - " . CAppUI::tr('CPatient-IPP barcode'), $this->_IPP);
        $ins = $this->_ref_last_ins ? $this->_ref_last_ins->ins : "";
        if (!$ins) {
            $ins = $this->_ref_patient_ins_nir ? $this->_ref_patient_ins_nir->ins_nir : "";
        }
        $template->addBarcode(
            "$champ - " . CAppUI::tr('CPatient-INS barcode'),
            $ins
        );

        if ($this->sexe === "m") {
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-he/she'), "il");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-He/She (uppercase)'), "Il");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-the'), "le");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-The (uppercase)'), "Le");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-of the'), "du");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-at the'), "au");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-gender agreement'), "");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-dear'), "cher");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-Dear (uppercase)'), "Cher");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-him/her'), "lui");
        } else {
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-he/she'), "elle");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-He/She (uppercase)'), "Elle");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-the'), "la");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-The (uppercase)'), "La");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-of the'), "de la");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-at the'), "à la");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-gender agreement'), "e");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-dear'), "chère");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-Dear (uppercase)'), "Chère");
            $template->addProperty("$champ - " . CAppUI::tr('CPatient-him/her'), "elle");
        }

        $med_traitant_subItem = CAppUI::tr('CPatient-doctor');
        $medecin              = $this->loadRefMedecinTraitant();
        $medecin_service      = new MedecinFieldService(
            $medecin,
            $this->loadRefMedecinTraitantExercicePlace()
        );
        $medecin_nom_prenom   = "$medecin->nom $medecin->prenom";

        if ($this->medecin_traitant_declare === "0") {
            $medecin_nom_prenom = CAppUI::tr("CPatient-Patient doesnt have a GP");
        }

        $medecin->mapMedecinExercicesPaces();

        $template->addProperty("$champ - $med_traitant_subItem", $medecin_nom_prenom);
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-Last name First name'),
            $medecin_nom_prenom
        );
        $template->addProperty("$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-name'), $medecin->nom);
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-first name'),
            $medecin->prenom
        );

        $completed_adresse = "{$medecin_service->getAdresse()}\n{$medecin_service->getCP()} {$medecin_service->getVille()}";

        if (!$completed_adresse) {
            $completed_adresse = "{$medecin->_mep_adresse}\n{$medecin->_mep_cp} {$medecin->_mep_ville}";
        }

        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-address'),
            $completed_adresse
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-way'),
            $medecin_service->getAdresse() ?: $medecin->_mep_adresse
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-ZIP code-court'),
            $medecin_service->getCP() ?: $medecin->_mep_cp
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-city'),
            $medecin_service->getVille() ?: $medecin->_mep_ville
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-brotherhood'),
            $medecin->_confraternite
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-phone-court'),
            $medecin_service->getTel() ?: $medecin->_mep_tel
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-fax'),
            $medecin_service->getFax() ?: $medecin->_mep_fax
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-email'),
            $medecin_service->getEmail() ?: $medecin->_mep_email
        );
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-email_apicrypt'),
            $medecin->email_apicrypt
        );
        CMbArray::removeValue("", $medecin->_mep_mssante_emails);
        $template->addProperty(
            "$champ - $med_traitant_subItem - " . CAppUI::tr('CMedecin-mssante_address'),
            $medecin_service->getMssanteAddress() ?: reset($medecin->_mep_mssante_emails)
        );

        if ($medecin->sexe == "f") {
            $template->addProperty("$champ - $med_traitant_subItem - " . CAppUI::tr('CPatient-gender agreement'), "e");
            $template->addProperty(
                "$champ - $med_traitant_subItem - " . CAppUI::tr('CPatient-long article'),
                "Mme le docteur"
            );
        } elseif ($medecin->sexe == "m") {
            $template->addProperty("$champ - $med_traitant_subItem - " . CAppUI::tr('CPatient-gender agreement'), "");
            $template->addProperty(
                "$champ - $med_traitant_subItem - " . CAppUI::tr('CPatient-long article'),
                "Mr le docteur"
            );
        } else {
            $template->addProperty("$champ - $med_traitant_subItem - " . CAppUI::tr('CPatient-gender agreement'), "");
            $template->addProperty(
                "$champ - $med_traitant_subItem - " . CAppUI::tr('CPatient-long article'),
                "le docteur"
            );
        }
        //Pharmacie
        $pharmacien_subItem = CAppUI::tr('CPatient-pharmacie_id');
        $pharmacie              = $this->loadRefPharmacie();
        $pharmacie_service      = new MedecinFieldService(
            $pharmacie,
            $this->loadRefMedecinTraitantExercicePlace()
        );
        $pharmacie_nom_prenom   = "$pharmacie->nom $pharmacie->prenom";

        if ($this->medecin_traitant_declare === "0") {
            $pharmacie_nom_prenom = CAppUI::tr("CPatient-Patient doesnt have a GP");
        }

        $pharmacie->mapMedecinExercicesPaces();

        $template->addProperty("$champ - $pharmacien_subItem", $pharmacie_nom_prenom);
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-Last name First name'),
            $pharmacie_nom_prenom
        );
        $template->addProperty("$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-name'), $pharmacie->nom);
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-first name'),
            $pharmacie->prenom
        );

        $completed_adresse = "{$pharmacie_service->getAdresse()}\n{$pharmacie_service->getCP()} {$pharmacie_service->getVille()}";

        if (!$completed_adresse) {
            $completed_adresse = "{$pharmacie->_mep_adresse}\n{$pharmacie->_mep_cp} {$pharmacie->_mep_ville}";
        }

        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-address'),
            $completed_adresse
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-way'),
            $pharmacie_service->getAdresse() ?: $pharmacie->_mep_adresse
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-ZIP code-court'),
            $pharmacie_service->getCP() ?: $pharmacie->_mep_cp
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-city'),
            $pharmacie_service->getVille() ?: $pharmacie->_mep_ville
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-brotherhood'),
            $pharmacie->_confraternite
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-phone-court'),
            $pharmacie_service->getTel() ?: $pharmacie->_mep_tel
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-fax'),
            $pharmacie_service->getFax() ?: $pharmacie->_mep_fax
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-email'),
            $pharmacie_service->getEmail() ?: $pharmacie->_mep_email
        );
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-email_apicrypt'),
            $pharmacie->email_apicrypt
        );
        CMbArray::removeValue("", $pharmacie->_mep_mssante_emails);
        $template->addProperty(
            "$champ - $pharmacien_subItem - " . CAppUI::tr('CMedecin-mssante_address'),
            $pharmacie_service->getMssanteAddress() ?: reset($pharmacie->_mep_mssante_emails)
        );

        if ($pharmacie->sexe == "f") {
            $template->addProperty("$champ - $pharmacien_subItem - " . CAppUI::tr('CPatient-gender agreement'), "e");

        } elseif ($pharmacie->sexe == "m") {
            $template->addProperty("$champ - $pharmacien_subItem - " . CAppUI::tr('CPatient-gender agreement'), "");

        } else {
            $template->addProperty("$champ - $pharmacien_subItem - " . CAppUI::tr('CPatient-gender agreement'), "");

        }
        //End pharmacie

        $events      = $this->loadRefDossierMedical()->loadRefsEvenementsPatient();
        $temp_events = [];
        foreach ($events as $event) {
            if ($event->date >= CMbDT::date()) {
                $temp_events[] = CEvenementPatient::viewTemplate($event);
            }
        }

        $template->addListProperty("$champ - " . CAppUI::tr('CSejourTimeline-title-all'), $temp_events, false);

        // Employeur
        $this->loadRefsCorrespondantsPatient(null, true);
        $correspondants = $this->_ref_cp_by_relation;

        $employeur_subItem = CAppUI::tr('CCorrespondantPatient-employer');

        foreach ($correspondants as $relation => $_correspondants) {
            $_correspondant = @reset($_correspondants);

            // Dans le cas d'un modèle, création d'un correspondant pour chaque type de relation
            if (!count($_correspondants)) {
                $_correspondant = new CCorrespondantPatient();
            }

            switch ($relation) {
                case "employeur":
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('common-name'),
                        $_correspondant->nom
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-address'),
                        $_correspondant->adresse
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-ZIP code-court'),
                        $_correspondant->cp
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-city'),
                        $_correspondant->ville
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-phone-court'),
                        $_correspondant->getFormattedValue("tel")
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-mobile phone'),
                        $_correspondant->getFormattedValue("mob")
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
                        $_correspondant->getFormattedValue("tel_autre")
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-urssaf number'),
                        $_correspondant->urssaf
                    );
                    $template->addProperty(
                        "$champ - $employeur_subItem - " . CAppUI::tr('CCorrespondantPatient-e-mail'),
                        $_correspondant->email
                    );
                    break;
                case "prevenir":
                case "assurance":
                    $sections = [
                        "prevenir"  => "prévenir",
                        "assurance" => "assurance",
                    ];

                    $section = $sections[$relation];
                    if ($section === "prévenir" && CModule::getActive("appFine")) {
                        CAppFineServer::fillTemplateCorrespPatient($template, $this, $champ, "prévenir");
                        break;
                    }

                    $correspondants_ids = array_keys($_correspondants);

                    for ($i = 1; $i < 4; $i++) {
                        $key = " $i ";
                        if ($key == 1) {
                            $key = " ";
                        }
                        $_correspondant = new CCorrespondantPatient();
                        if (isset($correspondants_ids[$i - 1])) {
                            $_correspondant = $_correspondants[$correspondants_ids[$i - 1]];
                        }

                        if ($section === "prévenir") {
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('common-name'),
                                $_correspondant->nom
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CPatient-first name'),
                                $_correspondant->prenom
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-address'),
                                $_correspondant->adresse
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-ZIP code-court'),
                                $_correspondant->cp
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-city'),
                                $_correspondant->ville
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-phone-court'),
                                $_correspondant->getFormattedValue("tel")
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-mobile phone'),
                                $_correspondant->getFormattedValue("mob")
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
                                $_correspondant->getFormattedValue("tel_autre")
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-relationship'),
                                $_correspondant->parente
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-e-mail'),
                                $_correspondant->email
                            );
                        }

                        if ($section == "assurance") {
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-begin'),
                                $_correspondant->getFormattedValue("date_debut")
                            );
                            $template->addProperty(
                                "$champ - $section$key- " . CAppUI::tr('CCorrespondantPatient-end'),
                                $_correspondant->getFormattedValue("date_fin")
                            );
                        }
                    }
                    break;
                case "confiance":
                case "representant_th":
                    if ($relation === "confiance" && CModule::getActive("appFine")) {
                        CAppFineServer::fillTemplateCorrespPatient($template, $this, $champ, "confiance");
                        break;
                    }

                    $name_relation = $relation == "confiance" ? $relation : CAppUI::tr(
                        'CCorrespondantPatient.relation.representant_th'
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('common-name'),
                        $_correspondant->nom
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-maiden name'),
                        $_correspondant->nom_jeune_fille
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CPatient-first name'),
                        $_correspondant->prenom
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-birth date'),
                        $_correspondant->getFormattedValue("naissance")
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-address'),
                        $_correspondant->adresse
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-ZIP code-court'),
                        $_correspondant->cp
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-city'),
                        $_correspondant->ville
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-phone-court'),
                        $_correspondant->getFormattedValue("tel")
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-mobile phone'),
                        $_correspondant->getFormattedValue("mob")
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
                        $_correspondant->getFormattedValue("tel_autre")
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-relationship'),
                        $_correspondant->parente
                    );
                    $template->addProperty(
                        "$champ - $name_relation - " . CAppUI::tr('CCorrespondantPatient-e-mail'),
                        $_correspondant->email
                    );
            }
        }

        // Vider les anciens holders
        $med_corresp_subItem = CAppUI::tr('CMedecin-corresponding doctor');

        $fields = [
            CAppUI::tr('CMedecin-Last name First name'),
            CAppUI::tr('CMedecin-address'),
            CAppUI::tr('CMedecin-way'),
            CAppUI::tr('CMedecin-ZIP code-court'),
            CAppUI::tr('CMedecin-city'),
            CAppUI::tr('CMedecin-brotherhood'),
            CAppUI::tr('CMedecin-speciality'),
            CAppUI::tr('CMedecin-phone-court'),
            CAppUI::tr('CMedecin-fax'),
            CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
            CAppUI::tr('CCorrespondantPatient-e-mail'),
            CAppUI::tr('CMedecin-type praticioner-court'),
            CAppUI::tr('CPatient-dear'),
            CAppUI::tr('CPatient-Dear (uppercase)'),
            CAppUI::tr('CMedecin-email_apicrypt'),
            CAppUI::tr('CMedecin-mssante_address'),
        ];

        for ($i = 1; $i < 4; $i++) {
            $template->addProperty("$champ - $med_corresp_subItem $i");
            foreach ($fields as $_field) {
                $template->addProperty("$champ - $med_corresp_subItem $i - $_field");
            }
        }

        if ($with_corresp) {
            $medCorresp_subItem = CAppUI::tr('CMedecin-Corresponding doctor');
            // Récupération des spécialités CPAM
            $specialites_cpam = CSpecCPAM::getList();

            $fields = [
                CAppUI::tr('CMedecin-Last name First name'),
                CAppUI::tr('CMedecin-address'),
                CAppUI::tr('CMedecin-way'),
                CAppUI::tr('CMedecin-ZIP code-court'),
                CAppUI::tr('CMedecin-city'),
                CAppUI::tr('CMedecin-brotherhood'),
                CAppUI::tr('CMedecin-speciality'),
                CAppUI::tr('CMedecin-phone-court'),
                CAppUI::tr('CMedecin-fax'),
                CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
                CAppUI::tr('CCorrespondantPatient-e-mail'),
                CAppUI::tr('CMedecin-type praticioner-court'),
                CAppUI::tr('CPatient-dear'),
                CAppUI::tr('CPatient-Dear (uppercase)'),
                CAppUI::tr('CMedecin-email_apicrypt'),
                CAppUI::tr('CMedecin-mssante_address'),
            ];

            foreach ($specialites_cpam as $_specialite_cpam) {
                $specialite_cpam_name = str_replace("-", " ", $_specialite_cpam->text);

                //$template->addProperty("$medCorresp_subItem - $specialite_cpam_name");

                foreach ($fields as $_field) {
                    $template->addProperty("$medCorresp_subItem - $specialite_cpam_name - $_field");
                }
            }
        }

        $this->loadRefsCorrespondants();
        $i              = 0;
        $noms           = [];
        $noms_addresses = [];

        foreach ($this->_ref_medecins_correspondants as $corresp) {
            $i++;
            $corresp->loadRefsFwd();
            $medecin = $corresp->_ref_medecin;
            $medecin->loadRefSpecCPAM();

            $medecin_service = new MedecinFieldService(
                $medecin,
                $corresp->loadRefMedecinExercicePlace()
            );

            $nom              = "{$medecin->nom} {$medecin->prenom}";
            $noms[]           = $nom;
            $noms_addresses[] = "$nom<br />$medecin->adresse<br />$medecin->cp $medecin->ville";

            if ($medecin->sexe == "m" || $medecin->sexe == "u") {
                $template->addProperty("$champ - $med_corresp_subItem $i - " . CAppUI::tr('CPatient-dear'), "cher");
                $template->addProperty(
                    "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CPatient-Dear (uppercase)'),
                    "Cher"
                );
            } elseif ($medecin->sexe == "f") {
                $template->addProperty("$champ - $med_corresp_subItem $i - " . CAppUI::tr('CPatient-dear'), "chère");
                $template->addProperty(
                    "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CPatient-Dear (uppercase)'),
                    "Chère"
                );
            }

            $template->addProperty("$champ - $med_corresp_subItem $i", $nom);
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-Last name First name'),
                "$medecin->nom $medecin->prenom"
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-address'),
                "{$medecin_service->getAdresse()}\n{$medecin_service->getCP()} {$medecin_service->getVille()}"
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-way'),
                $medecin_service->getAdresse()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-ZIP code-court'),
                $medecin_service->getCP()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-city'),
                $medecin_service->getVille()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-brotherhood'),
                $medecin->_confraternite
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-speciality'),
                $medecin_service->getDisciplines()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-phone-court'),
                $medecin_service->getTel()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-fax'),
                $medecin_service->getFax()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
                $medecin->getFormattedValue("tel_autre")
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CCorrespondantPatient-e-mail'),
                $medecin_service->getEmail()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-email_apicrypt'),
                $medecin->email_apicrypt
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-mssante_address'),
                $medecin_service->getMssanteAddress()
            );
            $template->addProperty(
                "$champ - $med_corresp_subItem $i - " . CAppUI::tr('CMedecin-type praticioner-court'),
                $medecin->getFormattedValue("type")
            );

            if ($with_corresp && $medecin->_ref_spec_cpam->_id) {
                $specialite = str_replace("-", " ", $medecin->_ref_spec_cpam->text);
                $specialite = preg_replace('/\s+/', ' ', $specialite);

                if ($medecin->sexe === "m" || $medecin->sexe == "u") {
                    $template->addProperty(
                        "$medCorresp_subItem - $specialite - " . CAppUI::tr('CPatient-dear'),
                        "cher"
                    );
                    $template->addProperty(
                        "$medCorresp_subItem - $specialite - " . CAppUI::tr('CPatient-Dear (uppercase)'),
                        "Cher"
                    );
                } elseif ($medecin->sexe == "f") {
                    $template->addProperty(
                        "$medCorresp_subItem - $specialite - " . CAppUI::tr('CPatient-dear'),
                        "chère"
                    );
                    $template->addProperty(
                        "$medCorresp_subItem - $specialite - " . CAppUI::tr('CPatient-Dear (uppercase)'),
                        "Chère"
                    );
                }

                $template->addProperty("$medCorresp_subItem - $specialite", $nom);
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-Last name First name'),
                    "$medecin->nom $medecin->prenom"
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-address'),
                    "{$medecin->adresse}\n{$medecin->cp} {$medecin->ville}"
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-way'),
                    $medecin_service->getAdresse()
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-ZIP code-court'),
                    $medecin_service->getCP()
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-city'),
                    $medecin_service->getVille()
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-brotherhood'),
                    $medecin->_confraternite
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-speciality'),
                    $medecin_service->getDisciplines()
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-phone-court'),
                    $medecin->getFormattedValue("tel")
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-fax'),
                    $medecin->getFormattedValue("fax")
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CCorrespondantPatient-foreign phone-court'),
                    $medecin->getFormattedValue("tel_autre")
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CCorrespondantPatient-e-mail'),
                    $medecin_service->getEmail()
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-email_apicrypt'),
                    $medecin->email_apicrypt
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-mssante_address'),
                    $medecin_service->getMssanteAddress()
                );
                $template->addProperty(
                    "$medCorresp_subItem - $specialite - " . CAppUI::tr('CMedecin-type praticioner-court'),
                    $medecin->getFormattedValue("type")
                );
            }
        }

        $template->addProperty("$champ - " . CAppUI::tr('CMedecin-corresponding doctor|pl'), implode(" - ", $noms));

        $template->addListProperty(
            "$champ - " . CAppUI::tr('CMedecin-corresponding doctor (with address)|pl'),
            $noms_addresses,
            false
        );


        //Liste des séjours du patient
        $this->loadRefsSejours();

        if (is_array($this->_ref_sejours)) {
            foreach ($this->_ref_sejours as $_sejour) {
                $_sejour->loadRefPraticien();
            }
            $smarty = new CSmartyDP("modules/dPpatients");
            $smarty->assign("sejours", $this->_ref_sejours);
            $sejours = $smarty->fetch("print_closed_sejours.tpl", '', '', 0);
            $sejours = preg_replace('`([\\n\\r])`', '', $sejours);
        } else {
            $sejours = CAppUI::tr("CSejour.none");
        }
        $template->addProperty("$champ - " . CAppUI::tr('CPatient-list of stay|pl'), $sejours, '', false);

        // Constantes médicales
        $const_med   = $this->_ref_constantes_medicales;
        $const_dates = $this->_latest_constantes_dates;

        $grid_complet = CConstantesMedicales::buildGridLatest($const_med, $const_dates, true);
        $grid_minimal = CConstantesMedicales::buildGridLatest($const_med, $const_dates, false);
        $grid_valued  = CConstantesMedicales::buildGridLatest($const_med, $const_dates, false, true);

        CConstantesMedicales::addConstantesTemplate($template, $grid_complet, $grid_minimal, $grid_valued, $champ);

        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-weight'), "$const_med->poids kg");
        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-size'), "$const_med->taille cm");
        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-pouls'), $const_med->pouls);
        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-_imc'), $const_med->_imc);
        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-_vst'), $const_med->_vst);
        $template->addProperty(
            "$champ - " . CAppUI::tr('CConstantesMedicales-constant temperature'),
            $const_med->temperature . "°"
        );
        $template->addProperty(
            "$champ - " . CAppUI::tr('CConstantesMedicales-ta-court'),
            ($const_med->ta ? "$const_med->_ta_systole / $const_med->_ta_diastole" : "")
        );
        $template->addProperty("$champ - " . CAppUI::tr('CConstantesMedicales-Saturation (spo2)'), $const_med->spo2);

        $last_cste = new CConstantesMedicales();

        if ($this->_id) {
            // Ne pas utiliser le getLastConstantes qui va charger l'intégralité des constantes du patient... (dépassement mémoire)
            $last_cste->loadObject(['patient_id' => " = {$this->_id}"], "datetime DESC");
        }

        $consts       = [$last_cste];
        $grid_complet = CConstantesMedicales::buildGrid($consts, true);
        $grid_minimal = CConstantesMedicales::buildGrid($consts, false);
        $grid_valued  = CConstantesMedicales::buildGrid($consts, false, true);

        CConstantesMedicales::addConstantesTemplate(
            $template,
            $grid_complet,
            $grid_minimal,
            $grid_valued,
            $champ,
            CAppUI::tr('CConstantesMedicales-Constant (last statement)|pl')
        );

        CConstantesMedicales::fillLiteLimitedTemplate($this, $template, $champ);

        $constantes       = CConstantesMedicales::getFirstFor($this, null, null, null, false);
        $first_constantes = reset($constantes);
        CConstantesMedicales::fillLiteLimitedTemplate2($first_constantes, $template, true, $champ);

        $constantes_last   = CConstantesMedicales::getLatestFor($this, null, null, null, false);
        $latest_constantes = reset($constantes_last);
        CConstantesMedicales::fillLiteLimitedTemplate2($latest_constantes, $template, false, $champ);

        $latest_constantes = [];

        $constantes_last     = CConstantesMedicales::getNthLatestFor($this, null, null, null, false);
        $latest_constantes[] = reset($constantes_last);

        $constantes_last     = CConstantesMedicales::getNthLatestFor($this, null, null, null, false, null, 1);
        $latest_constantes[] = reset($constantes_last);

        $constantes_last     = CConstantesMedicales::getNthLatestFor($this, null, null, null, false, null, 2);
        $latest_constantes[] = reset($constantes_last);
        CConstantesMedicales::fillLiteLimitedTemplate2($latest_constantes, $template, false, $champ, true);

        // Liste des fichiers
        $this->loadRefsFiles();
        $list = CMbArray::pluck($this->_ref_files, "file_name");
        $template->addListProperty("$champ - " . CAppUI::tr('CFile-List of file|pl'), $list);

        // Identité
        $identite = $this->loadNamedFile("identite.jpg");
        $template->addImageProperty(
            "$champ - " . CAppUI::tr('CMediusers-ID photo'),
            $identite->_id,
            ["title" => "$champ - " . CAppUI::tr('CMediusers-ID photo')]
        );

        // Assuré social
        $ass_social_subItem = CAppUI::tr('CPatient-part-assure-social');
        $template->addProperty("$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-last name'), $this->assure_nom);
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CCorrespondantPatient-maiden name-court'),
            $this->assure_nom_jeune_fille
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-first name'),
            $this->assure_prenom
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-birth date'),
            $this->getFormattedValue("assure_naissance")
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-article'),
            $this->_assure_civilite
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-long article'),
            $this->_assure_civilite_long
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-address'),
            $this->assure_adresse
        );
        $template->addProperty("$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-city'), $this->assure_ville);
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-ZIP code-court'),
            $this->assure_cp
        );
        $template->addProperty("$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-country'), $this->assure_pays);
        $template->addProperty("$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-phone'), $this->assure_tel);
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-mobile phone'),
            $this->assure_tel2
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-Birth ZIP code-court'),
            $this->assure_cp_naissance
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-place of birth'),
            $this->assure_lieu_naissance
        );
        $template->addProperty(
            "$champ - $ass_social_subItem - " . CAppUI::tr('CPatient-profession'),
            $this->assure_profession
        );

        // Bénéficiaire de soins
        $benef_soin_subItem = CAppUI::tr('CPatient-Beneficiary of care');
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-code regime'),
            $this->code_regime
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-manager office-court'),
            $this->caisse_gest
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-manager center-court'),
            $this->centre_gest
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-manager code-court'),
            $this->code_gestion
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-health diet'),
            $this->regime_sante
        );
        $template->addDateProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-early period'),
            $this->deb_amo
        );
        $template->addDateProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-end of period'),
            $this->fin_amo
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-am diet'),
            $this->getFormattedValue("regime_am")
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-long term affection-court'),
            $this->getFormattedValue("ald")
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-incapable adult'),
            $this->getFormattedValue("incapable_majeur")
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-universal health coverage-court'),
            $this->getFormattedValue("c2s")
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-ATNC'),
            $this->getFormattedValue("ATNC")
        );
        $template->addDateProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-vitale validity'),
            $this->fin_validite_vitale
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-declared treating physician'),
            $this->getFormattedValue("medecin_traitant_declare")
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-mutual contract type|pl'),
            addslashes($this->mutuelle_types_contrat)
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-amo note|pl'),
            addslashes($this->notes_amo)
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-exo label'),
            addslashes($this->libelle_exo)
        );
        $template->addProperty(
            "$champ - $benef_soin_subItem - " . CAppUI::tr('CPatient-amc note|pl'),
            addslashes($this->notes_amc)
        );

        if (CModule::getActive("maternite")) {
            $allaitement_subItem = CAppUI::tr('CAllaitement');
            $allaitement         = $this->loadLastAllaitement();
            $template->addDateProperty(
                "$champ - $allaitement_subItem - " . CAppUI::tr('common-Start'),
                $allaitement->date_debut
            );
            $template->addDateProperty("$champ - $allaitement_subItem - " . CAppUI::tr('end'), $allaitement->date_fin);

            $grossesse_subItem = CAppUI::tr('CGrossesse');
            $grossesse         = $this->loadLastGrossesse();
            $sa_reste_jour     = null;

            $naissance = $this->loadRefNaissance();

            if ($this->civilite == "enf" && $naissance && $naissance->_id) {
                $grossesse = $naissance->loadRefGrossesse();
            }

            if ($grossesse && $grossesse->_id) {
                $sa_reste_jour = $grossesse->_semaine_grossesse . " " . CAppUI::tr(
                        'CDepistageGrossesse-_sa'
                    ) . " + " . $grossesse->_reste_semaine_grossesse . " J";
            }

            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-terme_prevu'),
                $grossesse->terme_prevu
            );
            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-Date of the last rule|pl'),
                $grossesse->date_dernieres_regles
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-active'),
                $grossesse->getFormattedValue("active")
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-multiple'),
                $grossesse->getFormattedValue("multiple")
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-allaitement_maternel'),
                $grossesse->getFormattedValue("allaitement_maternel")
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-num_semaines'),
                $grossesse->getFormattedValue('num_semaines'),
                null,
                false
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-lieu_accouchement'),
                $grossesse->getFormattedValue("lieu_accouchement")
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-rques'),
                $grossesse->rques
            );
            $template->addProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-_semaine_grossesse'),
                $sa_reste_jour
            );
            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-date_debut_grossesse'),
                $grossesse->date_debut_grossesse
            );
            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-estimate_first_ultrasound_date'),
                $grossesse->estimate_first_ultrasound_date
            );
            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-estimate_second_ultrasound_date'),
                $grossesse->estimate_second_ultrasound_date
            );
            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-estimate_third_ultrasound_date'),
                $grossesse->estimate_third_ultrasound_date
            );
            $template->addDateProperty(
                "$champ - $grossesse_subItem - " . CAppUI::tr('CGrossesse-estimate_sick_leave_date'),
                $grossesse->estimate_sick_leave_date
            );

            $dossier_perinatal = $grossesse->loadRefDossierPerinat();
            $dossier_perinatal->fillLimitedTemplate($template, $champ);
        }

        if (CAppUI::conf("dPpatients CPatient function_distinct") == 1) {
            $this->loadRefFunction()->fillTemplate($template);
        }

        if (CModule::getActive("forms")) {
            CExObject::addFormsToTemplate($template, $this, $champ);
        }

        $manager = new CRGPDManager(CGroups::loadCurrent()->_id);

        if ($manager->canNotify($this->_class)) {
            $smarty = new CSmartyDP('modules/admin');
            $smarty->assign('manager', $manager);
            $smarty->assign('object_class', $this->_class);
            $smarty->assign('stylized', '0');

            if ($this->_id && $manager->canNotifyWithActions($this->_class)) {
                $consent = $manager->getConsentForObject($this);
                $this->setRGPDConsent($consent);
                $smarty->assign('token', $consent->getResponseToken());
            }

            $content = $smarty->fetch('inc_vw_rgpd_document.tpl');
            $template->addProperty(
                CAppUI::tr('CRGPDConsent') . ' - ' . CAppUI::tr(
                    "CRGPDConsent.object_class.{$this->_class}"
                ) . ' Document',
                $content,
                null,
                false
            );
        }
        // Ajout des codes barres de catégories de documents de type "catégorie de documents"
        $files_category_doc = CFilesCategory::listCatClass("CFilesCategory", true);
        foreach ($files_category_doc as $_file_category) {
            $template->addBarcode(
                CAppUI::tr('CFilesCategory-barcode') . " - " . $_file_category,
                $_file_category->nom_court
            );
        }

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadRefFunction();
        $this->loadIdVitale();
    }

    /**
     * Chargement de l'identifiant de carte vitale
     *
     * @return void
     */
    function loadIdVitale()
    {
        if (CModule::getActive("fse")) {
            $cv = CFseFactory::createCV();
            if ($cv) {
                $cv->loadIdVitale($this);
            }
        }
    }

    /**
     * Load the last INS of the patient
     *
     * @return CINSPatient|null
     */
    function loadLastINS()
    {
        $ins   = null;
        $array = $this->loadBackRefs("ins_patient", "date DESC", 1);
        if ($array) {
            $ins = current($array);
        }

        return $this->_ref_last_ins = $ins;
    }

    /**
     * Evaluation des libellés d'exonération
     *
     * @return void
     */
    function guessExoneration()
    {
        $this->completeField("libelle_exo");

        if (!$this->libelle_exo) {
            return;
        }

        foreach (self::$libelle_exo_guess as $field => $values) {
            if ($this->$field !== null) {
                continue;
            }

            foreach ($values as $value => $rules) {
                foreach ($rules as $rule) {
                    if (preg_match("/$rule/i", $this->libelle_exo)) {
                        $this->$field = $value;
                        break;
                    }
                }
            }
        }
    }

    /**
     * Chargement du dernier allaitement
     *
     * @return CAllaitement|null
     */
    function loadLastAllaitement()
    {
        if (!CModule::getActive("maternite")) {
            return null;
        }
        $allaitement         = new CAllaitement();
        $where               = [];
        $where["patient_id"] = "= '$this->_id'";
        $where[]             = "date_fin IS NULL OR date_fin >= '" . CMbDT::dateTime() . "'";
        $allaitement->loadObject($where, "date_fin DESC");

        return $this->_ref_last_allaitement = $allaitement;
    }

    /**
     * Chargement de la dernière grossesse active
     *
     * @return CGrossesse|null
     */
    function loadLastGrossesse()
    {
        if (!CModule::getActive("maternite")) {
            return null;
        }
        $grossesse = new CGrossesse();

        if ($this->_id) {
            $grossesse->parturiente_id = $this->_id;
            $grossesse->active = 1;
            $grossesse->loadMatchingObject("terme_prevu DESC");
        }

        return $this->_ref_last_grossesse = $grossesse;
    }

    /**
     * Récupération de la table de correspondance label / champs
     *
     * @return array
     */
    function getLabelTable()
    {
        return [
            "[NOM]"        => $this->nom,
            "[PRENOM]"     => $this->prenom,
            "[SEXE]"       => $this->sexe,
            "[NOM JF]"     => $this->nom_jeune_fille,
            "[DATE NAISS]" => $this->naissance,
            "[NUM SECU]"   => $this->matricule,
        ];
    }

    /**
     * Chargement du nom FR du pays de naissance
     *
     * @return void
     */
    function updateNomPaysInsee()
    {
        $pays = new CPaysInsee();
        if ($this->pays_naissance_insee) {
            $where = [
                "numerique" => $pays->_spec->ds->prepare("= %", $this->pays_naissance_insee),
            ];
            $pays->loadObject($where);
            $this->_pays_naissance_insee = $pays->nom_fr;
        }
        if ($this->assure_pays_naissance_insee) {
            $where = [
                "numerique" => $pays->_spec->ds->prepare("= %", $this->assure_pays_naissance_insee),
            ];
            $pays->loadObject($where);
            $this->_assure_pays_naissance_insee = $pays->nom_fr;
        }
    }

    /**
     * Création de la VCard du patient
     *
     * @param CMbvCardExport $vcard Vcard à remplir
     *
     * @return void
     */
    function toVcard(CMbvCardExport $vcard)
    {
        $vcard->addName($this->prenom, $this->nom, ucfirst($this->civilite));
        $vcard->addBirthDate($this->naissance);
        $vcard->addPhoneNumber($this->tel, 'HOME');
        $vcard->addPhoneNumber($this->tel2, 'CELL');
        $vcard->addPhoneNumber($this->tel_autre, 'WORK');
        $vcard->addEmail($this->email);
        $vcard->addAddress($this->adresse, $this->ville, $this->cp, $this->pays, 'HOME');
        $vcard->addTitle(ucfirst($this->profession));

        $this->loadRefPhotoIdentite();
        if ($this->_ref_photo_identite->_id) {
            $vcard->addPicture($this->_ref_photo_identite);
        }
    }

    function isIPPConflict($ipp)
    {
        // Pas de tag IPP => pas d'affichage d'IPP
        if (null == $tag_ipp = CAppUI::conf("dPpatients CPatient tag_ipp")) {
            return null;
        }

        $idex               = new CIdSante400();
        $idex->object_class = 'CPatient';
        $idex->tag          = $tag_ipp;
        $idex->id400        = $ipp;
        $idex->loadMatchingObject();

        return $idex->_id;
    }

    function countMatchingPatients()
    {
        $ds = CSQLDataSource::get("std");

        $res = $ds->query(
            "SELECT COUNT(*) AS total,
      CONVERT( GROUP_CONCAT(`patient_id` SEPARATOR '|') USING latin1 ) AS ids ,
      LOWER( CONCAT_WS( '-',
        REPLACE( REPLACE( REPLACE( REPLACE( `nom` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) ,
        REPLACE( REPLACE( REPLACE( REPLACE( `prenom` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) ,
        `naissance`
        , QUOTE( REPLACE( REPLACE( REPLACE( REPLACE( `nom_jeune_fille` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) )
        , QUOTE( REPLACE( REPLACE( REPLACE( REPLACE( `prenoms` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) )
      )) AS hash
      FROM `patients`
      GROUP BY hash
      HAVING total > 1"
        );

        return intval($ds->numRows($res));
    }

    /**
     * @throws Exception
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadIPP();
        $this->loadRefPhotoIdentite();
        $this->loadRefsCorrespondants();
        $this->loadRefsCorrespondantsPatient();
        $this->countINS();
        $this->updateBMRBHReStatus();
        $this->loadRefPatientINSNIR();
        $this->loadCodeInseeNaissance();
        $this->loadRefMedecinTraitantExercicePlace();

        foreach ($this->_ref_medecins_correspondants as $correspondant) {
            $correspondant->loadRefMedecinExercicePlace();
        }

        // Iconographie du patient sur les systèmes tiers
        $group_id = CGroups::loadCurrent()->_id;
        $this->loadExternalIdentifiers($group_id);

        if (CModule::getActive("instanceContexte")) {
            $this->_ref_instance_contextes = CInstanceContexte::loadByPatient($this->_id);
        }

        $this->loadRefLastVerrouDossier();
        $this->loadLastDossierAddictologie();

        // On compte les modèles d'étiquettes pour :
        // - stream si un seul
        // - modale de choix si plusieurs
        $modele_etiquette               = new CModeleEtiquette();
        $modele_etiquette->object_class = "CPatient";
        $modele_etiquette->group_id     = CGroups::get()->_id;
        $this->_count_modeles_etiq      = $modele_etiquette->countMatchingList();

        if (CModule::getActive("printing")) {
            // Compter les imprimantes pour l'impression d'étiquettes
            $user_printers      = CMediusers::get();
            $function           = $user_printers->loadRefFunction();
            $this->_nb_printers = $function->countBackRefs("printers");
        }
    }

    /**
     * Chargement de l'assurance valide et actuelle du patient
     *
     * @return CCorrespondantPatient
     */
    function loadRefAssuranceCurrent()
    {
        $now               = CMbDT::date();
        $where             = [];
        $where["relation"] = " = 'assurance'";
        $where[]           = "date_debut IS NULL OR date_debut <= '$now'";
        $where[]           = "date_fin IS NULL OR date_fin >= '$now'";
        $assurances        = $this->loadBackRefs(
            "correspondants_patient",
            "date_debut ASC",
            1,
            null,
            null,
            null,
            "",
            $where
        );
        if (count($assurances)) {
            $this->_ref_assurance_patient = reset($assurances);
        }

        return $this->_ref_assurance_patient;
    }

    /**
     * Count the number of INS
     *
     * @return int|null
     */
    function countINS()
    {
        return $this->_count_ins = $this->countBackRefs("ins_patient");
    }

    /**
     * Chargement du statut du patient AppFine
     *
     * @return CAppFineClientStatusPatientUser|CMbObject
     */
    function loadRefStatusPatientUser()
    {
        $this->_ref_status_patient_user = $this->loadUniqueBackRef("status_patient_user");

        if (!$this->_ref_status_patient_user) {
            $this->_ref_status_patient_user = new CAppFineClientStatusPatientUser();
        }

        $this->_ref_status_patient_user->updateFormFields();

        return $this->_ref_status_patient_user;
    }

    /**
     * Chargement du dernier verrouillage du dossier patient
     *
     * @return CVerrouDossierPatient|null
     */
    function loadRefLastVerrouDossier()
    {
        $this->_ref_last_verrou_dossier = $this->loadLastBackRef("verrou_dossier_patient", "date ASC");

        return $this->_ref_last_verrou_dossier;
    }

    /**
     * Chargement du dernier dossier d'addictologie
     *
     * @return CDossierAddictologie|null
     */
    function loadLastDossierAddictologie()
    {
        if (!CModule::getActive("addictologie")) {
            return null;
        }

        $ljoin             = [];
        $ljoin["sejour"]   = "dossier_addictologie.sejour_id = sejour.sejour_id";
        $ljoin["patients"] = "sejour.patient_id = patients.patient_id";

        $where                        = [];
        $where["patients.patient_id"] = " = '$this->_id'";

        $dossier_addictologie  = new CDossierAddictologie();
        $dossiers_addictologie = $dossier_addictologie->loadList(
            $where,
            "dossier_addictologie_id DESC",
            null,
            null,
            $ljoin
        );

        $referents = CStoredObject::massLoadFwdRef($dossiers_addictologie, "referent_user_id");
        CStoredObject::massLoadFwdRef($referents, "function_id");
        $pathologies = CStoredObject::massLoadBackRefs($dossiers_addictologie, "pathologies_addictologie");
        CStoredObject::massLoadFwdRef($pathologies, "type_pathologie_id");
        CStoredObject::massLoadFwdRef($pathologies, "motif_fin_pathlogie_id");

        $suivis = CStoredObject::massLoadBackRefs($dossiers_addictologie, "suivis_addictologie");
        CStoredObject::massLoadFwdRef($suivis, "type_suivi_addiction_id");

        foreach ($dossiers_addictologie as $_dossier) {
            $_dossier->loadRefReferentUser()->loadRefFunction();
            $pathologies = $_dossier->loadRefsPathologiesAddictologie();
            $suivis      = $_dossier->loadRefsSuivisAddictologie();

            foreach ($pathologies as $_pathologie) {
                $_pathologie->loadRefTypePathologie();
                $_pathologie->loadRefMotifFinPathologie();
            }

            foreach ($suivis as $_suivi) {
                $_suivi->loadRefTypeSuiviAddiction();
            }
        }

        return $this->_ref_last_dossier_addictologie = reset($dossiers_addictologie);
    }

    /**
     * Calcul du nombre d'enfants
     *
     * @return int
     */
    function countNbEnfants()
    {
        $this->_nb_enfants = 0;
        foreach ($this->loadRefsGrossesses() as $_grossesse) {
            $this->_nb_enfants += $_grossesse->countBackRefs("naissances");
        }

        return $this->_nb_enfants;
    }

    function completeLabelFields(&$fields, $params)
    {
        $this->loadIPP();
        $medecin_traitant = new CMedecin();
        $medecin_traitant->load($this->medecin_traitant);
        $this->updateNomPaysInsee();
        $this->loadRefsCorrespondantsPatient();
        $prevenir = new CCorrespondantPatient();
        $this->loadRefPatientINSNIR();
        $matricule_ins = $this->status === 'QUAL' ?
            ($this->_ref_patient_ins_nir->ins_nir . "(" . $this->_ref_patient_ins_nir->_ins_type . ")") : '';

        if (count($this->_ref_cp_by_relation["prevenir"])) {
            $prevenir = reset($this->_ref_cp_by_relation["prevenir"]);
        }
        $this->loadCodeInseeNaissance();
        $fields = array_merge(
            $fields,
            [
                "DATE NAISS"               => CMbDT::dateToLocale($this->naissance),
                "IPP"                      => $this->_IPP,
                "AGE"                      => $this->_age,
                "LIEU NAISSANCE"           => $this->lieu_naissance,
                "PAYS_NAISSANCE"           => $this->_pays_naissance_insee,
                "CODE POSTAL NAISSANCE"    => $this->cp_naissance,
                "NOM UTILISE"              => $this->nom,
                "NOM NAISSANCE"            => $this->nom_jeune_fille,
                "FORMULE NOM NAISSANCE"    => $this->nom_jeune_fille ? ($this->sexe == "f" ? "née $this->nom_jeune_fille" : "né $this->nom_jeune_fille") : '',
                "PREMIER PRENOM NAISSANCE" => $this->prenom,
                "PRENOM UTILISE"           => $this->prenom_usuel,
                "PRENOMS"                  => $this->prenoms,
                "SEXE"                     => strtoupper($this->sexe),
                "CIVILITE"                 => $this->civilite,
                "CIVILITE LONGUE"          => $this->_civilite_long,
                "ACCORD GENRE"             => $this->sexe == "f" ? "e" : "",
                "CODE BARRE IPP"           => "@BARCODE_" . $this->_IPP . "@",
                "ADRESSE"                  => preg_replace(
                        "/\n/",
                        "<br />",
                        $this->adresse
                    ) . " \n$this->cp $this->ville",
                "ADRESSE SEULE"            => preg_replace("/\n/", "<br />", $this->adresse),
                "CODE POSTAL"              => $this->cp,
                "VILLE"                    => $this->ville,
                "MED. TRAITANT"            => "Dr $medecin_traitant->nom $medecin_traitant->prenom",
                "TEL"                      => $this->getFormattedValue("tel"),
                "TEL PORTABLE"             => $this->getFormattedValue("tel2"),
                "TEL ETRANGER"             => $this->getFormattedValue("tel_autre"),
                "PAYS"                     => $this->getFormattedValue("pays"),
                "PREVENIR - NOM"           => $prevenir->nom,
                "PREVENIR - PRENOM"        => $prevenir->prenom,
                "PREVENIR - ADRESSE"       => $prevenir->adresse,
                "PREVENIR - TEL"           => $prevenir->getFormattedValue("tel"),
                "PREVENIR - PORTABLE"      => $prevenir->getFormattedValue("mob"),
                "PREVENIR - CP VILLE"      => "$prevenir->cp $prevenir->ville",
                "CODE BARRE ID"            => "@BARCODE_$this->_id@",
                "MATRICULE INS"            => $matricule_ins,
                "CODE INSEE NAISSANCE"     => $this->getCodeInseePrintableValue(),
            ]
        );
        switch (CAppUI::conf("ref_pays")) {
            case 1:
                $fields["NUM SECU"] = $this->matricule;
                break;
        }

        if (CModule::getActive("barcodeDoc") && CAppUI::gconf("barcodeDoc general module_actif")) {
            $fields = array_merge(
                $fields,
                [
                    "CODE BARRE IPP AVEC PREFIXE" => "@BARCODE_" . CAppUI::gconf(
                            "barcodeDoc general prefix_IPP"
                        ) . $this->_IPP . "@",
                ]
            );
        }
    }

    /**
     * @see parent::getIncrementVars()
     */
    function getIncrementVars()
    {
        return [];
    }

    /**
     * Return idex type if it's special (e.g. IPP/...)
     *
     * @param CIdSante400 $idex Idex
     *
     * @return string|null
     */
    function getSpecialIdex(CIdSante400 $idex)
    {
        // L'identifiant externe est l'IPP
        if ($idex->tag == self::getTagIPP()) {
            return "IPP";
        }

        if (CModule::getActive("appFineClient")) {
            if ($idex_type = CAppFineClient::getSpecialIdex($idex)) {
                return $idex_type;
            }
        }

        if (CModule::getActive("vivalto")) {
            if ($idex_type = CVivalto::getSpecialIdex($idex)) {
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
     * @inheritdoc
     */
    function loadAllDocs($params = [])
    {
        $type_doc = isset($params["type_doc"]) ? $params["type_doc"] : null;

        // Les ordonnances sont attachées aux prescriptions externes des consultations uniquement
        // Aucun autre document est attaché à ces prescriptions
        foreach ($this->loadRefsConsultations() as $_consult) {
            $_consult->loadRefsPrescriptions();
            if (isset($_consult->_ref_prescriptions["externe"])) {
                $this->mapDocs($_consult->_ref_prescriptions["externe"], $params);
            }
        }

        if ($type_doc == "ordonnances") {
            return;
        }

        $this->mapDocs($this, $params);

        foreach ($this->loadRefsSejours() as $_sejour) {
            $this->mapDocs($_sejour, $params);
            foreach ($_sejour->loadRefsOperations() as $_op) {
                $this->mapDocs($_op, $params);
            }

            foreach ($_sejour->loadRefsConsultations() as $_consult) {
                $this->mapDocs($_consult, $params);
            }

            if (CAppUI::pref("vue_globale_display_all_forms")) {
                $_sejour->loadAppelsInAndOut();
                foreach ($_sejour->_ref_appels_by_type as $_appel) {
                    $this->mapDocs($_appel, $params);
                }

                if (CModule::getActive('ssr')) {
                    $this->mapDocs($_sejour->loadRefBilanSSR(), $params);
                }

                if (CModule::getActive('maternite')) {
                    $this->mapDocs($_sejour->loadRefGrossesse(), $params);
                }

                if (CModule::getActive('urgences')) {
                    $this->mapDocs($_sejour->loadRefRPU(), $params);
                }

                CPrescriptionLine::$_load_extra_lite = true;
                foreach ($_sejour->loadRefsPrescriptions() as $_prescription) {
                    foreach ($_prescription->loadRefsLinesElement() as $_element) {
                        $this->mapDocs($_element, $params);
                    }

                    if (CModule::getActive('mpm')) {
                        foreach ($_prescription->loadRefsLinesMed() as $_medicament) {
                            $this->mapDocs($_medicament, $params);
                            foreach ($_medicament->loadRefsAdministrations() as $_administration) {
                                $this->mapDocs($_administration, $params);
                            }
                        }

                        foreach ($_prescription->loadRefsPrescriptionLineMixes() as $_mix) {
                            $this->mapDocs($_mix, $params);
                        }
                    }
                }
            }
        }

        foreach ($this->loadRefsConsultations(["sejour_id" => "IS NULL"]) as $_consult) {
            $this->mapDocs($_consult, $params);
        }

        foreach ($this->loadRefDossierMedical()->loadRefsEvenementsPatient() as $_evenement) {
            $this->mapDocs($_evenement, $params);
        }

        foreach ($this->loadRefsPatientReunion() as $reunion) {
            $this->mapDocs($reunion, $params);
        }
    }

    /**
     * Check if a CPatient have been merged
     *
     * @return bool
     */
    function hasBeenMerged()
    {
        $id_sante400 = new CIdSante400();
        $ds          = $this->getDS();
        $where       = [
            'object_id'    => $ds->prepare('= ?', $this->_id),
            'object_class' => $ds->prepare('= ?', $this->_class),
            'tag'          => "= 'merged'",
        ];

        $ids = $id_sante400->loadList($where);
        if (!$ids || count($ids) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Checks if current patient allowed data sharing between groups
     *
     * @param string $group_id Group reference
     *
     * @return bool|null
     */
    function checkSharingGroup($group_id = null)
    {
        if (!$this->_id) {
            return null;
        }

        if (!$group_id) {
            $group_id = CGroups::get()->_id;
        }

        $patient_group             = new CPatientGroup();
        $patient_group->group_id   = $group_id;
        $patient_group->patient_id = $this->_id;

        $patient_group->loadMatchingObject();

        if (!$patient_group || !$patient_group->_id) {
            return false;
        }

        return $patient_group->share;
    }

    /**
     * Gets full list of groups with patient link
     *
     * @return array
     */
    function getSharingList()
    {
        if (!$this->_id) {
            return [];
        }

        $groups = CGroups::get()->loadList();

        CStoredObject::massLoadFwdRef($this->_ref_patient_groups, 'user_id');
        $this->loadSharingGroups();

        $patient_groups_here = [];
        if ($this->_ref_patient_groups) {
            foreach ($this->_ref_patient_groups as $_patient_group) {
                $_patient_group->loadRefUser();
                $patient_groups_here[$_patient_group->group_id] = $_patient_group;
            }
        }

        $patient_groups = [];
        foreach ($groups as $_group) {
            $patient_groups[$_group->_id] = [
                'label' => $_group->_view,
                'share' => (isset($patient_groups_here[$_group->_id])) ? $patient_groups_here[$_group->_id] : null,
            ];
        }

        return $patient_groups;
    }

    /**
     * Return the next grossesse to come
     *
     * @param string $date Reference date
     *
     * @return CGrossesse
     */
    function getNextGrossesse($date = null)
    {
        $this->_ref_next_grossesse = new CGrossesse();

        if (!$this->_id) {
            return $this->_ref_next_grossesse;
        }

        if (!$date) {
            $date = CMbDT::date();
        }

        $where = [
            "grossesse.parturiente_id" => "= '$this->_id'",
            "grossesse.terme_prevu"    => ">= '$date'",
        ];

        $this->_ref_next_grossesse->loadObject($where);

        return $this->_ref_next_grossesse;
    }

    /**
     * Constructs events tree (séjours, consultations, opérations, etc.)
     *
     * @return array
     */
    function getTimeline()
    {
        $objects_history = [];
        $events_by_date  = [];

        foreach ($this->_ref_sejours as $_sejour) {
            $_date = CMbDT::format($_sejour->entree, '%Y');

            if (!isset($events_by_date[$_date])) {
                $events_by_date[$_date] = [];
            }

            if (!isset($events_by_date[$_date][$_sejour->entree])) {
                $events_by_date[$_date][$_sejour->entree] = [];
            }

            $_sejour_related = [];
            foreach ($_sejour->_ref_operations as $_operation) {
                if (!isset($_sejour_related[$_operation->_datetime_best])) {
                    $_sejour_related[$_operation->_datetime_best] = [];
                }

                $_sejour_related[$_operation->_datetime_best][] = [
                    'event'   => $_operation,
                    'related' => [],
                    'icon'    => null,
                ];

                // Stockage du GUID de la consultation car celle-ci est présente dans une intervention
                if ($_operation->_ref_consult_anesth && $_operation->_ref_consult_anesth->_id) {
                    $objects_history[] = $_operation->_ref_consult_anesth->_ref_consultation->_guid;
                }
            }

            foreach ($_sejour->_ref_consultations as $_consult) {
                // On ne traite pas les objets déjà référencés dans les interventions
                if (in_array($_consult->_guid, $objects_history)) {
                    continue;
                }

                if (!isset($_sejour_related[$_consult->_datetime])) {
                    $_sejour_related[$_consult->_datetime] = [];
                }

                $_sejour_related[$_consult->_datetime][] = [
                    'event'   => $_consult,
                    'related' => [],
                    'icon'    => null,
                ];

                // Stockage du GUID de la consultation car celle-ci est présente dans une consultation de séjour
                $objects_history[] = $_consult->_guid;
            }

            $events_by_date[$_date][$_sejour->entree][] = [
                'event'   => $_sejour,
                'related' => $_sejour_related,
                'icon'    => null,
            ];
        }

        foreach ($this->_ref_consultations as $_consult) {
            // On ne traite pas les objets déjà référencés dans les séjours et interventions
            if (in_array($_consult->_guid, $objects_history)) {
                continue;
            }

            $_date = CMbDT::format($_consult->_datetime, '%Y');

            if (!isset($events_by_date[$_date])) {
                $events_by_date[$_date] = [];
            }

            if (!isset($events_by_date[$_date][$_consult->_datetime])) {
                $events_by_date[$_date][$_consult->_datetime] = [];
            }

            $_icon = null;
            if ($_consult->_ref_consult_anesth && $_consult->_ref_consult_anesth->_id) {
                $_icon = $_consult->_ref_consult_anesth->getEventIcon();
            }

            $events_by_date[$_date][$_consult->_datetime][] = [
                'event'   => $_consult,
                'related' => [],
                'icon'    => $_icon,
            ];
        }

        krsort($events_by_date);
        foreach ($events_by_date as $_year => $_dates) {
            krsort($events_by_date[$_year]);

            foreach ($_dates as $_key => $_events) {
                foreach ($_events as $_key2 => $_event) {
                    ksort($events_by_date[$_year][$_key][$_key2]['related']);
                }
            }
        }

        return $events_by_date;
    }

    /**
     * Recherche du tuteur
     *
     * @return CCorrespondantPatient
     */
    function loadRefTuteur()
    {
        foreach ($this->loadRefsCorrespondantsPatient() as $_correspondant) {
            if ($_correspondant->parente == "tuteur") {
                $this->_ref_tuteur = $_correspondant;
            }
        }

        return $this->_ref_tuteur;
    }

    /**
     * Charge les dernières valeurs des constantes poids et taille selon un référentiel de date donné
     *
     * @param dateTime $datetime Date et heure des constantes
     *
     * @return array
     */
    function getFastPoidsTaille($datetime = null)
    {
        $constantes = [
            'poids',
            'taille',
        ];

        if (!$this->_id) {
            return array_fill_keys($constantes, null);
        }

        $datetime = ($datetime) ?: CMbDT::dateTime();

        return CConstantesMedicales::getFastConstantes($this->_id, $constantes, $datetime);
    }

    /**
     * @see parent::getSexFieldName()
     */
    function getSexFieldName()
    {
        return "sexe";
    }

    /**
     * @øee parent::getPrenomFieldName()
     */
    function getPrenomFieldName()
    {
        return "prenom";
    }

    /**
     * Compte le nombre de consultations avec le praticien pour ce patient
     *
     * @param int $prat_id Consultation de praticien
     *
     * @return int
     */
    function countConsultationPrat($prat_id)
    {
        $ljoin                            = [];
        $ljoin["plageconsult"]            = "plageconsult.plageconsult_id = consultation.plageconsult_id";
        $where                            = [];
        $where["consultation.patient_id"] = " = '$this->_id'";
        $where["consultation.annule"]     = " = '0'";
        $where["plageconsult.chir_id"]    = " = '$prat_id'";
        $consultation                     = new CConsultation();

        return $this->_count_consult_prat = $consultation->countList($where, "consultation.consultation_id", $ljoin);
    }

    function getSurpoids()
    {
        $surpoids = CAppUI::gconf("dPpatients CPatient overweight");
        $this->loadRefLatestConstantes(null, ["poids"]);
        $this->_overweight = $this->_ref_constantes_medicales->poids > $surpoids ? $this->_ref_constantes_medicales->poids : false;
    }

    /**
     * Return the mobile phone number, with the phone area code if set
     *
     * @return string
     */
    public function getMobilePhoneNumber()
    {
        $phone = $this->tel2;

        if ($this->phone_area_code) {
            $phone = "+$this->phone_area_code" . substr($this->tel2, 1);
        }

        return $phone;
    }

    /**
     * Count order item response for medical event
     *
     * @return int|null
     */
    function countOrderItemResponses()
    {
        return $this->_count_order_item_responses = $this->countBackRefs("orders_item_response");
    }

    /**
     * Chargement des inclusions du programme du patient
     *
     * @return CInclusionProgramme[]
     */
    function loadRefsInclusionsProgramme()
    {
        return $this->_refs_inclusions_programme = $this->loadBackRefs("inclusions_programme");
    }

    /**
     * Chargement les directives anticipées du patient
     *
     * @return CDirectiveAnticipee[]
     */
    function loadRefsDirectivesAnticipees()
    {
        return $this->_refs_directives_anticipees = $this->loadBackRefs("directives_patient");
    }

    /**
     * @return CStoredObject[]|CPatientReunion[]|null
     * @throws Exception
     */
    function loadRefsPatientReunion()
    {
        return $this->_refs_patient_reunion = $this->loadBackRefs("patient_reunion");
    }

    /**
     * @throws Exception
     */
    public function loadRefsPatientHandicaps(): ?array
    {
        return $this->_refs_patient_handicaps = $this->loadBackRefs("patient_handicaps");
    }

    /**
     * Chargement de la dernière directive anticipée valide
     *
     * @return CDirectiveAnticipee
     */
    function loadRefLastDirectiveAnticipee()
    {
        $directive_patient = new CDirectiveAnticipee();

        $where                  = [];
        $where["patient_id"]    = " = '$this->_id'";
        $where["date_validite"] = " IS NOT NULL";
        $where[]                = "date_validite > '" . CMbDT::date() . "'";

        $order = "date_validite ASC";

        $directives_patient = $directive_patient->loadList($where, $order);

        return $this->_ref_last_directive_anticipee = $directives_patient ? end(
            $directives_patient
        ) : $directive_patient;
    }

    function getByIPPNDA($patient_ipp, $patient_nda)
    {
        // Recherche par IPP
        if ($patient_ipp) {
            $this->_IPP = $patient_ipp;
            // Aucune configuration de IPP
            if (!$this->getTagIPP()) {
                $this->load($patient_ipp);
            } else {
                $this->loadFromIPP();
            }
        } // Recherche par NDA
        elseif ($patient_nda) {
            $sejour = new CSejour();
            // Aucune configuration de NDA
            if (!$sejour->getTagNDA()) {
                $sejour->load($patient_nda);
            } else {
                $sejour->loadFromNDA($patient_nda);
            }

            if ($sejour->_id) {
                $this->load($sejour->patient_id);
            }
        }
    }

    function loadFromIPP($group_id = null)
    {
        if (!$this->_IPP) {
            return;
        }

        // Pas de tag IPP => pas d'affichage d'IPP
        if (null == $tag_ipp = $this->getTagIPP($group_id)) {
            return;
        }

        // Recuperation de la valeur de l'id400
        $idex = CIdSante400::getMatch('CPatient', $tag_ipp, $this->_IPP);

        $this->load($idex->object_id);
    }

    /**
     * Recherche d'un patient par numéro de sécurité sociale
     *
     * @param string $num_ss Numéro de sécurité sociale du patient
     *
     * @return void
     */
    function getByNumSS($num_ss)
    {
        $field_card = "matricule";

        $this->$field_card = $num_ss;

        $this->loadMatchingObject();
    }

    /**
     * @inheritdoc
     */
    function getGeocodeFields()
    {
        return [
            'adresse',
            'cp',
            'ville',
            'pays',
        ];
    }

    function getFullAddress()
    {
        return $this->getAddress() . ' ' . $this->getZipCode() . ' ' . $this->getCity() . ' ' . $this->getCountry();
    }

    function getAddress()
    {
        return $this->adresse;
    }

    function getZipCode()
    {
        return $this->cp;
    }

    function getCity()
    {
        return $this->ville;
    }

    function getCountry()
    {
        return $this->pays;
    }

    /**
     * @inheritdoc
     */
    function createGeolocalisationObject()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            $geo = new CGeoLocalisation();
            $geo->setObject($this);
            $geo->processed = '0';
            $geo->store();

            return $geo;
        } else {
            return $this->_ref_geolocalisation;
        }
    }

    /**
     * @inheritdoc
     */
    function loadRefGeolocalisation()
    {
        return $this->_ref_geolocalisation = $this->loadUniqueBackRef('geolocalisation');
    }

    /**
     * @inheritdoc
     */
    function getLatLng()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    function setLatLng($latlng)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    function getCommuneInsee()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        return $this->_ref_geolocalisation->commune_insee;
    }

    /**
     * @inheritdoc
     */
    function setCommuneInsee($commune_insee)
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->commune_insee = $commune_insee;

        return $this->_ref_geolocalisation->store();
    }

    /**
     * @inheritdoc
     */
    function resetProcessed()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->processed = "0";

        return $this->_ref_geolocalisation->store();
    }

    function setProcessed(CGeoLocalisation $object = null)
    {
        if (!$object || !$object->_id) {
            $object = $this->loadRefGeolocalisation();
        }

        if (!$object || !$object->_id) {
            return null;
        }

        $object->processed = "1";

        return $object->store();
    }

    /**
     * Gets blood relatives (father, mother, son ...)
     *
     * @return array - relatives ids
     */
    public function getBloodRelatives()
    {
        $this->loadRefFamilyPatient();

        $parent_id_1 = $this->_ref_family_patient->parent_id_1;
        $parent_id_2 = $this->_ref_family_patient->parent_id_2;

        $where[]             = "parent_id_1 = '$parent_id_1' OR parent_id_2 = '$parent_id_1' OR " .
            "parent_id_1 = '$parent_id_2' OR parent_id_2 = '$parent_id_2'";
        $where['patient_id'] = "!= '$this->_id'";

        $family_link  = new CPatientFamilyLink();
        $bro_sis_list = $family_link->loadList($where);

        $this_id     = $this->_id;
        $where       = "parent_id_1 = '$this_id' OR parent_id_2 = '$this_id'";
        $family_link = new CPatientFamilyLink();
        $children    = $family_link->loadList($where);

        $total             = [];
        $total["bros"]     = [];
        $total["children"] = [];

        $total["bros"]     = array_merge(
            array_map(
                function ($obj) {
                    return [$obj->patient_id, $obj->type];
                },
                $bro_sis_list
            ),
            $total["bros"]
        );
        $total["children"] = array_merge(
            array_map(
                function ($obj) {
                    return [$obj->patient_id, $obj->type];
                },
                $children
            ),
            $total["children"]
        );

        $total["parent_1"] = [$this->_ref_family_patient->parent_id_1, $this->_ref_family_patient->type];
        $total["parent_2"] = [$this->_ref_family_patient->parent_id_2, $this->_ref_family_patient->type];

        $total = array_filter($total);

        return $total;
    }

    /**
     * Charge les parents du patient
     *
     * @return CPatientFamilyLink
     */
    function loadRefFamilyPatient()
    {
        return $this->_ref_family_patient = $this->loadUniqueBackRef("family_patient");
    }

    /**
     * @return CInjection[]|null
     * @throws Exception
     */
    public function loadRefInjections()
    {
        return $this->_ref_injections = $this->loadBackRefs("injections", "injection_date DESC");
    }

    /**
     * Charge la provenance du patient pour l'établissement
     *
     * @return CStoredObject[]
     * @throws Exception
     */
    public function loadRefProvenancePatient()
    {
        if ($module = CModule::getInstalled('provenance') && CAppUI::isGroup()) {
            return [
                $this->_ref_provenance_patient = $this->loadUniqueBackRef("provenance_patient"),
                $this->_provenance = $this->_ref_provenance_patient !== null ? $this->_ref_provenance_patient->_view : "",
            ];
        }

        return [];
    }

    public function loadRefSourceIdentite(bool $cache = true): CSourceIdentite
    {
        $this->_ref_source_identite = $this->loadFwdRef('source_identite_id', $cache);
        $this->_ref_source_identite->updateLieuNaissance();
        $this->_ref_source_identite->loadRefPatientINSNIR();

        $this->_mode_obtention = $this->_ref_source_identite->_id ?
            $this->_ref_source_identite->mode_obtention : CSourceIdentite::MODE_OBTENTION_MANUEL;

        return $this->_ref_source_identite;
    }

    public function loadRefsSourcesIdentite(bool $only_actives = true, bool $update_fields = true): array
    {
        $where = [];

        if ($only_actives) {
            $where['active'] = "= '1'";
        }

        $this->_ref_sources_identite = $this->loadBackRefs(
            'sources_identite',
            'active DESC',
            null,
            null,
            null,
            null,
            null,
            $where
        );

        if ($update_fields) {
            foreach ($this->_ref_sources_identite as $_source_identite) {
                $_source_identite->updateLieuNaissance();
                $_source_identite->loadRefPatientINSNIR();
            }
        }

        // On positionne la source sélectionnée en premier dans la liste
        if ($this->source_identite_id && isset($this->_ref_sources_identite[$this->source_identite_id])) {
            $selected_source = $this->_ref_sources_identite[$this->source_identite_id];
            unset($this->_ref_sources_identite[$this->source_identite_id]);
            $this->_ref_sources_identite = [$selected_source->_id => $selected_source] + $this->_ref_sources_identite;
        }

        return $this->_ref_sources_identite;
    }

    /**
     * Get the sejours list with perms
     *
     * @param int $perms Perm required
     *
     * @return CSejour[]
     */
    public function getSejoursWithPerms($perms = PERM_EDIT)
    {
        $sejours = $this->loadRefsSejours();
        foreach ($sejours as $_sejour_key => $_sejour) {
            if (!$_sejour->getPerm(PERM_EDIT)) {
                unset($sejours[$_sejour_key]);
            }
        }

        return $sejours;
    }

    /**
     * Check BMR BHRE status
     *
     * @return void
     */
    function checkBmrBhreStatus()
    {
        $current_date      = CMbDT::date();
        $ljoin             = [];
        $ljoin["bmr_bhre"] = "bmr_bhre.patient_id = patients.patient_id";

        $where                          = [];
        $where["bmr_bhre.bhre_contact"] = " = '1'";
        $where[]                        = "bmr_bhre.bhre_contact_fin IS NOT NULL AND bmr_bhre.bhre_contact_fin < '$current_date'";

        $patient  = new self();
        $patients = $patient->loadList($where, null, null, null, $ljoin);

        foreach ($patients as $_patient) {
            $bmr_bhre = $_patient->loadRefBMRBHRe();

            if ($bmr_bhre->bhre_contact) {
                $bmr_bhre->bhre_contact = 0;
                $bmr_bhre->store();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchPatient($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * Charge les CV associés au patient
     *
     * @return CPyxvitalCV[]|CPvCV[]|null
     * @throws Exception
     */
    public function loadRefsCVS()
    {
        $back_name_cv = null;
        if (CModule::getActive('oxPyxvital') && CAppUI::pref('LogicielFSE') == 'oxPyxvital') {
            $back_name_cv = "cv_pyxvital";
        } elseif (CModule::getActive('pyxVital') && CAppUI::pref('LogicielFSE') == 'pv') {
            $back_name_cv = "cv_pyxvital";
        }

        if (!$back_name_cv) {
            return null;
        }

        return $this->_refs_cvs = $this->loadBackRefs($back_name_cv);
    }

    public function isConsentTerreSante(): bool
    {
        return $this->_consent_terresante = $this->isConsent(CRGPDConsent::TAG_TERRESANTE);
    }

    public function isConsentDMP(): bool
    {
        $consent = $this->getConsent(CRGPDConsent::TAG_DMP);

        // Sans objet consentement créé, le consentement est par défaut à oui pour le dmp
        return $this->_consent_dmp = $consent->_id ? $this->isConsent(CRGPDConsent::TAG_DMP) : 1;
    }

    public function isConsentMSSantePro(): bool
    {
        $consent = $this->getConsent(CRGPDConsent::TAG_MSSANTE_PRO);

        // Sans objet consentement créé, le consentement est par défaut à oui pour mssanté pro
        return $this->_consent_mssante_pro = $consent->_id ? $this->isConsent(CRGPDConsent::TAG_MSSANTE_PRO) : 1;
    }

    public function isConsentMSSantePatient(): bool
    {
        $consent = $this->getConsent(CRGPDConsent::TAG_MSSANTE_PATIENT);

        // Sans objet consentement créé, le consentement est par défaut à oui pour mssanté patient
        return $this->_consent_mssante_patient = $consent->_id ? $this->isConsent(
            CRGPDConsent::TAG_MSSANTE_PATIENT
        ) : 1;
    }

    public function isConsent(int $tag): bool
    {
        return intval($this->getConsent($tag)->status) === CRGPDConsent::STATUS_ACCEPTED ? 1 : 0;
    }

    public function getConsent(int $tag): CConsentPatient
    {
        return $this->loadUniqueBackRef(
            'patient_consents',
            null,
            null,
            null,
            null,
            'consent_' . $tag,
            [
                'tag'      => $this->getDS()->prepare('= ?', $tag),
                'group_id' => $this->getDS()->prepare('= ?', CGroups::loadCurrent()->_id),
            ]
        );
    }

    /** Load the CNaissance object
     *
     * @return CNaissance
     * @throws Exception
     */
    function loadRefNaissance()
    {
        $sejours_ids = (new CSejour())->loadIds(['patient_id' => "= '{$this->_id}'"]);

        $this->_ref_naissance = new CNaissance();
        $this->_ref_naissance->loadObject(['sejour_enfant_id' => CSQLDataSource::prepareIn($sejours_ids)]);

        return $this->_ref_naissance;
    }

    /**
     * Loads `CPaymentTrace` objects related to `patient_id` input. If already referenced, returns then by cache.
     *
     * @param int        $patient_id `CPatient` identifier to be used.
     * @param array|null $where      WHERE clause array to be used for filtering request.
     *
     * @return CPaymentTrace[]
     * @throws Exception
     */
    public function loadRefsPaymentTraces(int $patient_id, array $where = null): array
    {
        if ($this->_ref_payment_traces) {
            return $this->_ref_payment_traces;
        }

        $payment_trace = new CPaymentTrace();

        // Building SQL request
        $request = new CRequest();
        $request->addLJoinClause(
            'evenement_medical',
            'evenement_medical.evenement_id = appfine_payment_trace.context_id'
        );
        $request->addLJoinClause('patients', 'patients.patient_id = evenement_medical.patient_id');
        $request->addWhereClause('patients.patient_id', $payment_trace->getDS()->prepare(' = ?', $patient_id));

        if ($where) {
            foreach ($where as $_clause => $_predicate) {
                $request->addWhereClause($_clause, $_predicate);
            }
        }

        return $this->_ref_payment_traces = $payment_trace->loadListByReq($request);
    }

    /**
     * @return Collection|null
     * @throws \Ox\Core\Api\Exceptions\ApiException
     */
    function getResourceSejours(): ?Collection
    {
        if (!$sejours = $this->loadRefsSejours()) {
            return null;
        }

        return new Collection($sejours);
    }

    public function getResourceAvatar(): ?Item
    {
        if (!$fichier = $this->loadRefPhotoIdentite()) {
            return null;
        }

        return new Item($fichier);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourcePatientUsers(): ?Collection
    {
        $patient_users = $this->loadRefsPatientUsers();
        if (!$patient_users) {
            return null;
        }

        return new Collection($patient_users);
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceDoctor(): ?Item
    {
        $medecin = $this->loadRefMedecinTraitant();
        if (!$medecin || !$medecin->_id) {
            return null;
        }

        return new Item($medecin);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceCorrespondantsPatient(): ?Collection
    {
        $correspondants = $this->loadRefsCorrespondantsPatient();
        if (!$correspondants) {
            return null;
        }

        return new Collection($correspondants);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceMedicalRecord(): ?Item
    {
        $dossier = $this->loadRefDossierMedical();
        if (!$dossier || !$dossier->_id) {
            return null;
        }

        return new Item($dossier);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CRGPDException
     * @throws Exception
     */
    public function getResourceRgpd(): ?Collection
    {
        $patient_users = $this->loadRefsPatientUsers();
        if (!$patient_users) {
            return null;
        }

        $consents = [];
        foreach ($patient_users as $patient_user) {
            $rgpd_manager_patient = new CRGPDManager($patient_user->group_id);
            if (!$rgpd_manager_patient->isEnabledFor($this) || !$rgpd_manager_patient->shouldAskConsentFor($this)) {
                continue;
            }

            if (!$patient_consent = $rgpd_manager_patient->getOrInitConsent($this, CRGPDConsent::TAG_APPFINE)) {
                continue;
            }
            $consents[] = $patient_consent;
        }

        if (!$consents) {
            return null;
        }

        return new Collection($consents);
    }

    /**
     * @return Collection|null
     * @throws \Ox\Core\Api\Exceptions\ApiException
     */
    public function getResourceAllergies(): ?Collection
    {
        if (!$dossier_medical = $this->loadRefDossierMedical()) {
            return null;
        }

        if (!$allergies = $dossier_medical->loadRefsAllergies()) {
            return null;
        }

        return new Collection($allergies);
    }

    /**
     * Fetches `CFile` resources from datasource as a sub-resource of an `API` call.
     *
     * This function is used in `AppFine` context, for fetching patient-owned files.
     *
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceFiles(): Collection
    {
        $ds    = (new CFile)->getDS();
        $where = [
            'nature_file_id' => $ds->prepare(' IS NOT NULL'),
        ];
        $files = $this->loadBackRefs('files', null, null, null, null, null, null, $where);

        return new Collection($files ?? []);
    }

    /**
     * Getter to fields_etiq variale
     *
     * @return array
     * @throws Exception
     */
    public static function getFieldsEtiq()
    {
        $fields_etiq   = self::$fields_etiq;
        $fields_etiq[] = CAppUI::conf("ref_pays") == 1 ? "NUM SECU" : "AVS";

        if (CModule::getActive("barcodeDoc") && CAppUI::gconf("barcodeDoc general module_actif")) {
            $fields_etiq[] = "CODE BARRE IPP AVEC PREFIXE";
        }

        return $fields_etiq;
    }

    /**
     * Return the patient NIR without the key
     *
     * @param string $attribute
     *
     * @return string
     */
    public function getNirWithoutKey(string $attribute = "matricule"): string
    {
        return str_split($this->$attribute ?? '', 13)[0];
    }

    /**
     * Return the INS attribute without the key
     */
    public function getINSWithoutKey(): string
    {
        return str_split($this->getINSNIR() ?? '', 13)[0];
    }

    public function getMSSanteMail(): ?string
    {
        if ($this->_mssante_email) {
            return $this->_mssante_email;
        }

        if ($this->matricule) {
            $this->_mssante_email = $this->matricule . '@' . static::MSSANTE_MAIL_DOMAIN;
        }

        return $this->_mssante_email;
    }

    public function getMSSanteMailAlias(): ?string
    {
        if ($this->_mssante_email_alias) {
            return $this->_mssante_email_alias;
        }

        $patient_identite = $this->nom . ' ' . ($this->prenom_usuel ?: $this->prenom);
        $patient_email    = $this->getMSSanteMail();

        // Alias format
        return $this->_mssante_email_alias = $patient_email ? "{$patient_identite} <{$patient_email}>" : null;
    }

    public function getTraitsStrictsModified(): array
    {
        $traits_stricts_modified = [];

        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_trait_strict) {
            if ($_trait_strict[0] === '_') {
                continue;
            }

            switch ($_trait_strict) {
                case 'naissance':
                    if ($this->_old->naissance !== CMbDT::dateFromLocale($this->naissance)) {
                        $traits_stricts_modified[] = $_trait_strict;
                    }

                    break;
                default:
                    if ($this->fieldModified($_trait_strict)) {
                        $traits_stricts_modified[] = $_trait_strict;
                    }
            }
        }

        return $traits_stricts_modified;
    }

    /**
     * @return string|null
     */
    public function getFirstCodeCIM10(): ?string
    {
        $dossier_medical = $this->loadRefDossierMedical();

        if (!$dossier_medical || !$dossier_medical->_id) {
            return null;
        }

        return CMbArray::get($dossier_medical->_codes_cim, 0);
    }

    /**
     * @return CGroups|null
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        if ($this->group_id) {
            return CGroups::get($this->group_id);
        } elseif ($this->function_id) {
            return $this->loadRefFunction()->loadRefGroup();
        }

        return null;
    }

    /**
     * @return CEvenementPatient
     * @throws Exception
     */
    public function loadRefEvenementAlerte(): CEvenementPatient
    {
        $this->_ref_evenement_alerte = new CEvenementPatient();

        if (!$this->loadRefDossierMedical()->_id) {
            return $this->_ref_evenement_alerte;
        }

        $where = [
            'evenement_patient.dossier_medical_id' => "= '{$this->_ref_dossier_medical->_id}'",
            'evenement_patient.alerter'            => "= '1'",
            'regle_alerte_patient.type_alerte'     => "= 'open'",
        ];

        $ljoin = [
            'regle_alerte_patient' => 'regle_alerte_patient.regle_id = evenement_patient.regle_id',
        ];

        $this->_ref_evenement_alerte->loadObject($where, null, null, $ljoin);

        return $this->_ref_evenement_alerte;
    }

    /**
     * Retourne le lieu de naissance formatté pour l'appel au téléservice INSI
     * Aucun code alphanumérique en entrée du service
     */
    public function getLieuNaissanceINSI(): ?string
    {
        $lieu_naissance =
            $this->commune_naissance_insee
            ?: (($this->pays_naissance_insee)
                ? CPaysInsee::getPaysByNumerique($this->pays_naissance_insee)->code_insee
                : null);

        if ($lieu_naissance === '000' || $lieu_naissance === '00000') {
            $lieu_naissance = $this->lieu_naissance;
        }

        return preg_match('/[A-Z]/', $lieu_naissance) ? null : $lieu_naissance;
    }

    /**
     * Load the INSEE code of birth according to the country of birth
     *
     * @return void
     */
    public function loadCodeInseeNaissance(): void
    {
        if ($this->_id && $this->pays_naissance_insee) {
            $this->_code_insee = ($this->pays_naissance_insee == CPaysInsee::NUMERIC_FRANCE)
                ? $this->commune_naissance_insee
                : (CPaysInsee::getPaysByNumerique($this->pays_naissance_insee))->code_insee;
        } elseif ($this->_id && $this->cp_naissance == "99999") {
            $this->_code_insee = "99999";
        }
    }

    /**
     * Get printable value for code insee
     *
     * @return string
     */
    private function getCodeInseePrintableValue(): string
    {
        $value = CAppui::tr("CPatient-_code_insee-court") . " : ";

        $value .= ($this->_code_insee && $this->_code_insee !== "99999") ? $this->_code_insee : CAppui::tr("Unknown");

        return $value;
    }
}
