<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Interop\Phast\CCodeSnomedCTDossierMedical;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\Snomed\CSnomed;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\Forms\CExObject;
use Throwable;

/**
 * Dossier Médical liés aux notions d'antécédents, traitements et diagnostics
 */
class CDossierMedical extends CMbObject implements IIndexableObject, ImportableInterface, IGroupRelated
{
    /** @var string */
    const RESOURCE_TYPE = 'dossierMedical';

    /** @var string */
    public const FIELDSET_PATIENT = "patient";

    /** @var string */
    public const FIELDSET_SEJOUR = "sejour";

    // DB Table key
    public $dossier_medical_id;

    // Medecin traitant interne
    public $medecin_traitant_id;

    // DB Fields
    public $codes_cim;

    // Dossier medical Patient
    public $risque_thrombo_patient;
    public $risque_MCJ_patient;
    public $risque_viral;
    public $risque_viral_rq;
    public $facteurs_risque;
    public $absence_traitement;
    public $absence_antecedent;
    public $absence_allergie;
    public $derniere_mapa;
    public $examen_pieds;
    public $examen_fond_oeil;
    public $cancer_colorectal;
    public $frottis;
    public $derniere_score_framingham;
    public $form_reperage_tabac;
    public $form_reperage_alcool;
    public $conduites_addictives;
    public $groupe_sanguin;
    public $rhesus;
    public $groupe_ok;
    public $phenotype;
    public $coloscopie;
    public $fibroscopie;

    /** @var string */
    public $occupational_risk_factor;

    /** @var string */
    public $points_attention;

    // Dossier medical Sejour
    public $risque_thrombo_chirurgie;
    public $risque_antibioprophylaxie;
    public $risque_prophylaxie;
    public $risque_MCJ_chirurgie;

    public $object_class;
    public $object_id;

    // Form Fields
    public $_added_code_cim;
    public $_deleted_code_cim;
    public $_codes_cim     = [];
    public $_ext_codes_cim = [];
    public $_ext_codes_snomed_cim;
    public $_del_code_cim_snomed; // To delete Idex Code Snomed

    // Back references
    /** @var  CAntecedent[] */
    public $_all_antecedents                          = [];
    public $_ref_antecedents_by_type                  = [];
    public $_ref_antecedents_by_appareil              = [];
    public $_ref_antecedents_by_type_appareil         = [];
    public $_ref_antecedents_by_type_appareil_absence = [];
    /** @var  CTraitement[] */
    public $_ref_traitements = [];
    /** @var  CPathologie[] */
    public $_ref_pathologies = [];
    /** @var  CEvenementPatient[] */
    public $_ref_evenements_patient = [];
    public $_ref_etats_dents        = [];
    /** @var  CPrescription */
    public $_ref_prescription;
    public $_ref_allergies = [];
    public $_ref_atcd_sans_allergie;
    public $_ref_atcd_sans_allergie_absence;
    public $_ref_deficiences;

    /** @var CSejour|CPatient */
    public $_ref_object;

    /** @var CCodeSnomedCTDossierMedical */
    public $_ref_code_snomed;

    // Derived back references
    public $_count_antecedents;
    public $_count_antecedents_by_type;
    public $_count_traitements;
    public $_count_cancelled_antecedents;
    public $_count_cancelled_traitements;
    /** @var int */
    public $_count_traitements_in_progress;
    /** @var array */
    public $_traitements_in_progress;

    public $_count_allergies;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'dossier_medical';
        $spec->key   = 'dossier_medical_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|dossier_medical fieldset|extra";
        $props["object_class"] = "enum list|CPatient|CSejour fieldset|extra";

        // Médecin traitant interne
        $props["medecin_traitant_id"] = "ref class|CMediusers back|dossiers_medicaux fieldset|default";

        $props["codes_cim"] = "text fieldset|default";

        // Dossier medical Patient
        $props["risque_thrombo_patient"]    = "enum list|NR|faible|modere|eleve|majeur default|NR fieldset|patient";
        $props["risque_MCJ_patient"]        = "enum list|NR|aucun|possible default|NR fieldset|patient";
        $props["risque_viral"]              = "enum list|NR|aucun|possible default|NR fieldset|patient";
        $props["risque_viral_rq"]           = "text fieldset|patient";
        $props["facteurs_risque"]           = "text helped fieldset|patient";
        $props["absence_traitement"]        = "bool fieldset|patient";
        $props["absence_antecedent"]        = "bool default|0 fieldset|patient";
        $props["absence_allergie"]          = "bool default|0 fieldset|patient";
        $props["derniere_mapa"]             = "date fieldset|patient";
        $props["examen_pieds"]              = "date fieldset|patient";
        $props["examen_fond_oeil"]          = "date fieldset|patient";
        $props["cancer_colorectal"]         = "date fieldset|patient";
        $props["coloscopie"]                = "date fieldset|patient";
        $props["fibroscopie"]               = "date fieldset|patient";
        $props["frottis"]                   = "date fieldset|patient";
        $props["derniere_score_framingham"] = "date fieldset|patient";
        $props["form_reperage_tabac"]       = "date fieldset|patient";
        $props["form_reperage_alcool"]      = "date fieldset|patient";
        $props["conduites_addictives"]      = "bool default|0 fieldset|patient";
        $props["groupe_sanguin"]            = "enum list|?|O|A|B|AB default|? show|0 fieldset|patient";
        $props["rhesus"]                    = "enum list|?|NEG|POS default|? show|0 fieldset|patient";
        $props["groupe_ok"]                 = "bool default|0 show|0 fieldset|patient";
        $props["phenotype"]                 = "str fieldset|patient";
        $props["occupational_risk_factor"]  = "text fieldset|default";
        $props["points_attention"]          = "text fieldset|default";

        // Dossier mesical Sejour
        $props["risque_thrombo_chirurgie"]  = "enum list|NR|faible|modere|eleve default|NR fieldset|sejour";
        $props["risque_antibioprophylaxie"] = "enum list|NR|non|oui default|NR fieldset|sejour";
        $props["risque_prophylaxie"]        = "enum list|NR|non|oui default|NR fieldset|sejour";
        $props["risque_MCJ_chirurgie"]      = "enum list|NR|sans|avec default|NR fieldset|sejour";

        // Form fields
        $props["_del_code_cim_snomed"] = "str";

        return $props;
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        $basePerm = CModule::getCanDo('soins')->edit ||
            CModule::getCanDo('dPurgences')->edit ||
            CModule::getCanDo('dPcabinet')->edit ||
            CModule::getCanDo('dPbloc')->edit ||
            CModule::getCanDo('dPplanningOp')->edit;

        return $basePerm
            && (!CAppUI::isCabinet() || ($this->object_id && $this->loadTargetObject()->getPerm($permType)))
            && parent::getPerm($permType);
    }

    /**
     * @see parent::loadRefsBack()
     */
    function loadRefsBack()
    {
        parent::loadRefsBack();
        $this->loadRefsAntecedents();
        $this->loadRefsTraitements();
    }

    /**
     * Chargement de la prescription du dossier médical
     *
     * @return CPrescription
     */
    function loadRefPrescription()
    {
        /** @var CPrescription $prescription */
        $prescription = $this->loadUniqueBackRef("prescriptions");

        if ($prescription && $prescription->_id) {
            $prescription->loadRefsLinesMed();
            $prescription->loadRefsLinesElement();
        }

        return $this->_ref_prescription = $prescription;
    }

    /**
     * Chargement de l'objet lié au dossier médical
     *
     * @return CPatient|CSejour|null
     */
    function loadRefObject()
    {
        if (!$this->object_class) {
            return;
        }

        $this->_ref_object = $this->loadFwdRef('object_id');

        return $this->_ref_object;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        // Tokens CIM
        $this->codes_cim  = $this->codes_cim !== null ? strtoupper($this->codes_cim) : "";
        $this->_codes_cim = $this->codes_cim !== null ? explode("|", $this->codes_cim) : [];

        // Objets CIM
        $this->_ext_codes_cim = [];
        foreach ($this->_codes_cim as $code_cim) {
            $this->_ext_codes_cim[$code_cim] = CCodeCIM10::get($code_cim);
        }

        if (CModule::getActive('snomed')) {
            CSnomed::bindSnomedCodeAndCimCode($this);
        }
    }

    /**
     * @see parent::mergePlainFields()
     */
    function mergePlainFields($objects /*array(<CMbObject>)*/, $getFirstValue = false)
    {
        $codes_cim_array   = CMbArray::pluck($objects, 'codes_cim');
        $codes_cim_array[] = $this->codes_cim;
        $codes_cim         = implode('|', $codes_cim_array);
        $codes_cim_array   = array_unique(explode('|', $codes_cim));
        CMbArray::removeValue('', $codes_cim_array);

        foreach ($objects as $objet) {
            if ($this->risque_thrombo_patient == 'NR') {
                $this->risque_thrombo_patient = $objet->risque_thrombo_patient;
            }
            if ($this->risque_MCJ_patient == 'NR') {
                $this->risque_MCJ_patient = $objet->risque_MCJ_patient;
            }
            if ($this->risque_thrombo_chirurgie == 'NR') {
                $this->risque_thrombo_chirurgie = $objet->risque_thrombo_chirurgie;
            }
            if ($this->risque_antibioprophylaxie == 'NR') {
                $this->risque_antibioprophylaxie = $objet->risque_antibioprophylaxie;
            }
            if ($this->risque_prophylaxie == 'NR') {
                $this->risque_prophylaxie = $objet->risque_prophylaxie;
            }
            if ($this->risque_MCJ_chirurgie == 'NR') {
                $this->risque_MCJ_chirurgie = $objet->risque_MCJ_chirurgie;
            }
            if (!$this->facteurs_risque) {
                $this->facteurs_risque = $objet->facteurs_risque;
            }
            if (!$this->absence_traitement) {
                $this->absence_traitement = $objet->absence_traitement;
            }
            if ($this->groupe_sanguin == '?') {
                $this->groupe_sanguin = $objet->groupe_sanguin;
            }
            if ($this->rhesus == '?') {
                $this->rhesus = $objet->rhesus;
            }
            if (!$this->groupe_ok) {
                $this->groupe_ok = $objet->groupe_ok;
            }
        }
        parent::mergePlainFields($objects);

        $this->codes_cim = implode('|', $codes_cim_array);
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        $prescriptions = [];

        if ($this->loadRefPrescription()->_id) {
            $prescriptions[$this->_ref_prescription->_id] = $this->_ref_prescription;
        }

        $prescriptions = array_merge($prescriptions, CStoredObject::massLoadBackRefs($objects, "prescriptions"));

        try {
            parent::merge($objects, $fast, $merge_log);
        } catch (CouldNotMerge $e) {
            // Legacy behavior: keep on script execution...
        } catch (Throwable $t) {
            throw $t;
        }

        $this->store();

        if (count($prescriptions) > 1) {
            $keys_prescript = array_keys($prescriptions);
            $first_key      = reset($keys_prescript);

            $prescription = $prescriptions[$first_key];
            unset($prescriptions[$first_key]);

            $prescription_merge_log = CMergeLog::logStart(CUser::get()->_id, $prescription, $prescriptions, $fast);

            try {
                $prescription->merge($prescriptions, $fast, $prescription_merge_log);
                $prescription_merge_log->logEnd();
            } catch (Throwable $t) {
                $prescription_merge_log->logFromThrowable($t);

                throw $t;
            }
        }
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadComplete();
    }

    /**
     * @see parent::loadComplete()
     */
    function loadComplete()
    {
        parent::loadComplete();

        $this->loadRefsTraitements();
        $this->loadRefsAntecedents();
        $prescription = $this->loadRefPrescription();

        if ($prescription && is_array($prescription->_ref_prescription_lines)) {
            foreach ($prescription->_ref_prescription_lines as $_line) {
                $_line->loadRefsPrises();
            }
        }
    }

    /**
     * Chargement des antécédents du dossier
     *
     * @param bool $cancelled   Prise en compte des annulés
     * @param bool $bydate      Sort by date the list
     * @param bool $ignore_ras  Ignore the RAS types antecedents
     * @param bool $see_nvp     Recherche des mots clés NVP
     * @param int  $see_absence Prise en compte des absences
     * @param bool $same_func   Prise en compte de la fonction de l'utilisateur
     *
     * @return CStoredObject[]|CAntecedent[]|null
     */
    function loadRefsAntecedents(
        $cancelled = false,
        $bydate = false,
        $ignore_ras = false,
        $see_nvp = false,
        $see_absence = 0,
        $same_func = false
    ) {
        // Initialisation du classement
        $order = $bydate ? "date DESC, CAST(type AS CHAR), CAST(appareil AS CHAR), rques ASC" : "CAST(type AS CHAR), CAST(appareil AS CHAR), rques ASC";
        $where = [];
        $ljoin = [];

        if ($ignore_ras) {
            $where["rques"] = 'NOT IN ("' . str_replace('|', '","', CAppUI::gconf("soins Other ignore_allergies")) . '")';
        }
        if ($see_nvp) {
            $where["rques"] = 'IN ("' . str_replace('|', '","', CAppUI::gconf("dPcabinet CConsultAnesth text_atcd_nvp")) . '")';
        }

        if ($see_absence == 1) {
            $where["absence"] = " = '1'";
        } elseif ($see_absence == 0) {
            $where["absence"] = " = '0'";
        }

        if ($same_func) {
            $ljoin = [
                "users_mediboard" => "users_mediboard.user_id = antecedent.owner_id",
            ];

            $curr_user = CMediusers::get();

            $where["users_mediboard.function_id"] = "= '$curr_user->function_id'";
        }

        if (null === $this->_all_antecedents = $this->loadBackRefs("antecedents", $order, null, null, $ljoin, null, null, $where, false)) {
            return null;
        }

        // Filtrage sur les annulés
        foreach ($this->_all_antecedents as $_atcd) {
            /** @var $_atcd CAntecedent */
            if ($_atcd->annule && !$cancelled) {
                unset($this->_all_antecedents[$_atcd->_id]);
            }
        }

        $atcd = new CAntecedent();

        // Classement par type
        $this->_ref_antecedents_by_type = array_fill_keys($atcd->_specs["type"]->_list, []);
        ksort($this->_ref_antecedents_by_type);
        foreach ($this->_all_antecedents as $_atcd) {
            $this->_ref_antecedents_by_type[$_atcd->type][$_atcd->_id] = $_atcd;
            if ($_atcd->type != "alle") {
                if ($_atcd->absence == 0) {
                    $this->_ref_atcd_sans_allergie[$_atcd->_id] = $_atcd;
                } else {
                    $this->_ref_atcd_sans_allergie_absence[$_atcd->_id] = $_atcd;
                }
            }
        }

        $this->_ref_allergies = [];
        if (array_key_exists('alle', $this->_ref_antecedents_by_type)) {
            $this->_ref_allergies = $this->_ref_antecedents_by_type["alle"];
        }

        // Classement par appareil
        $this->_ref_antecedents_by_appareil = array_fill_keys($atcd->_specs["appareil"]->_list, []);
        foreach ($this->_all_antecedents as $_atcd) {
            $this->_ref_antecedents_by_appareil[$_atcd->appareil ?: "aucun"][$_atcd->_id] = $_atcd;
        }

        // Classement par type puis appareil
        $this->_ref_antecedents_by_type_appareil = array_fill_keys(
            $atcd->_specs["type"]->_list,
            array_fill_keys($atcd->_specs["appareil"]->_list, [])
        );
        foreach ($this->_all_antecedents as $_atcd) {
            if ($_atcd->absence == 0) {
                $this->_ref_antecedents_by_type_appareil[$_atcd->type][$_atcd->appareil ?: "aucun"][$_atcd->_id] = $_atcd;
            } else {
                $this->_ref_antecedents_by_type_appareil_absence[$_atcd->type][$_atcd->appareil ?: "aucun"][$_atcd->_id] = $_atcd;
            }
        }

        return $this->_all_antecedents;
    }

    /**
     * Chargement des pathologies du dossier
     *
     * @param bool $display_resolve_cancel TRUE si toutes les pathologies doivent être affichées
     *
     * @return CPathologie[]|null
     */
    function loadRefsPathologies($display_resolve_cancel = false)
    {
        $order = "debut DESC";
        $where = [];
        if (!$display_resolve_cancel) {
            $where = [
                "annule" => "= '0'",
                "resolu" => "= '0'",
            ];
        }

        return $this->_ref_pathologies = $this->loadBackRefs("pathologies", $order, null, null, null, null, null, $where);
    }

    function loadRefsCodeSnomed()
    {
        $this->_ref_code_snomed = $this->loadBackRefs('codes_snomed_ct');

        return $this->_ref_code_snomed;
    }

    /**
     * Chargement des évènements du dossier
     *
     * @return CEvenementPatient[]|null
     */
    function loadRefsEvenementsPatient($where = [], $all_event = false)
    {
        $order = "date DESC";

        $curr_user = CMediusers::get();
        $extra_where = "";

        $ljoin = [];
        if (!$curr_user->isAdmin()) {
            $ljoin = [
                "users_mediboard"     => "evenement_patient.praticien_id = users_mediboard.user_id",
                "functions_mediboard" => "users_mediboard.function_id = functions_mediboard.function_id",
            ];

            if ($all_event) {
                $extra_where = " || evenement_patient.praticien_id IS NULL";
            }

            $where[] = "functions_mediboard.consults_events_partagees = '1' || functions_mediboard.function_id = '$curr_user->function_id'$extra_where";
        }

        return $this->_ref_evenements_patient = $this->loadBackRefs("evenements_patient", $order, null, null, $ljoin, null, "", $where);
    }


    /**
     * Chargement de l'état des dents
     *
     * @return CEtatDent[]
     */
    function loadRefsEtatsDents()
    {
        return $this->_ref_etats_dents = $this->loadBackRefs("etats_dent");
    }

    /**
     * Compte les antécédents annulés et non-annulés
     *
     * @param boolean $count_allergies Permet de préciser si les allergies sont prises en compte ou non
     * @param boolean $ignore_ras      Permet d'ignorer les chaines "Rien à signaler"
     *
     * @return void
     */
    function countAntecedents($count_allergies = true, $ignore_ras = false)
    {
        if (!$this->_id) {
            $this->_count_antecedents = $this->_count_cancelled_antecedents = 0;

            return;
        }

        $antedecent                  = new CAntecedent();
        $where                       = [];
        $where["dossier_medical_id"] = " = '$this->_id'";
        $where["annule"]             = " != '1'";
        if (!$count_allergies) {
            $where["type"] = " != 'alle' OR `type` IS NULL";
        }
        if ($ignore_ras) {
            $where["rques"] = 'NOT IN ("' . str_replace('|', '","', CAppUI::gconf("soins Other ignore_allergies")) . '")';
        }
        $this->_count_antecedents = $antedecent->countList($where);

        $where["annule"]                    = " = '1'";
        $this->_count_cancelled_antecedents = $antedecent->countList($where);
    }

    /**
     * MassCount des antecedents
     *
     * @param array   $dossiers        Dossier médicaux
     * @param boolean $count_allergies Permet de préciser si les allergies sont prises en compte ou non
     *
     * @return array
     */
    static function massCountAntecedents($dossiers = [], $count_allergies = true)
    {
        $antecedent      = new CAntecedent();
        $where           = [];
        $where["annule"] = " != '1'";
        if (!$count_allergies) {
            $where[] = "type IS NULL OR type != 'alle'";
        }
        $where["dossier_medical_id"] = CSQLDataSource::prepareIn($dossiers);
        $where["rques"]              = 'NOT IN ("' . str_replace('|', '","', CAppUI::gconf("soins Other ignore_allergies")) . '")';

        $request = new CRequest();
        $request->addTable("antecedent");
        $request->addColumn("dossier_medical_id");
        $request->addColumn("count(*)", "c");
        $request->addWhere($where);
        $request->addGroup("dossier_medical_id");

        return $antecedent->getDS()->loadHashList($request->makeSelect());
    }

    public function loadTraitementsInProgress(): void
    {
        if (!$this->_id) {
            $this->_traitements_in_progress = ["traitement" => [], "medicament" => [], "element" => []];

            return;
        }

        $ds                                           = $this->getDS();
        $traitement                                   = new CTraitement();
        $where                                        = [];
        $where["dossier_medical_id"]                  = $ds->prepare(" = ?", $this->_id);
        $where[]                                      = $ds->prepare("fin IS NULL OR fin >= ?", CMbDT::date());
        $where["annule"]                              = $ds->prepare(" != '1'");
        $this->_traitements_in_progress["traitement"] = $traitement->loadList($where);

        $this->loadRefPrescription();
        $where                    = [];
        $where["prescription_id"] = $ds->prepare(" = ?", $this->_ref_prescription->_id);
        $where[]                  = $ds->prepare("fin IS NULL OR fin >= ?", CMbDT::date());

        $line_med                                     = new CPrescriptionLineMedicament();
        $this->_traitements_in_progress["medicament"] = $line_med->loadList($where);

        $line_element                              = new CPrescriptionLineElement();
        $this->_traitements_in_progress["element"] = $line_element->loadList($where);
    }

    public function countTraitementsInProgress(): void
    {
        if (!$this->_id) {
            $this->_count_traitements_in_progress = 0;

            return;
        }
        $ds                                   = $this->getDS();
        $traitement                           = new CTraitement();
        $where                                = [];
        $where["dossier_medical_id"]          = $ds->prepare(" = ?", $this->_id);
        $where[]                              = $ds->prepare("fin IS NULL OR fin >= ?", CMbDT::date());
        $where["annule"]                      = $ds->prepare(" != '1'");
        $this->_count_traitements_in_progress = $traitement->countList($where);

        $this->loadRefPrescription();
        $where                    = [];
        $where["prescription_id"] = $ds->prepare(" = ?", $this->_ref_prescription->_id);
        $where[]                  = $ds->prepare("fin IS NULL OR fin >= ?", CMbDT::date());

        $line_med                             = new CPrescriptionLineMedicament();
        $this->_count_traitements_in_progress += $line_med->countList($where);

        $line_element                         = new CPrescriptionLineElement();
        $this->_count_traitements_in_progress += $line_element->countList($where);
    }

    /**
     * Compte les antécédents annulés et non-annulés
     *
     * @return void
     */
    function countTraitements()
    {
        if (!$this->_id) {
            $this->_count_traitements = $this->_count_cancelled_traitements = 0;

            return;
        }

        $traitement                  = new CTraitement();
        $where                       = [];
        $where["dossier_medical_id"] = " = '$this->_id'";

        $where["annule"]          = " != '1'";
        $this->_count_traitements = $traitement->countList($where);

        $where["annule"]                    = " = '1'";
        $this->_count_cancelled_traitements = $traitement->countList($where);
    }

    /**
     * Compte les antecedents de type allergies
     * tout en tenant compte de la config pour ignorer certaines allergies
     *
     * @return int
     */
    function countAllergies()
    {
        if (!$this->_id) {
            return $this->_count_allergies = 0;
        }

        $antecedent                  = new CAntecedent();
        $where["type"]               = "= 'alle'";
        $where["annule"]             = " ='0'";
        $where["absence"]            = " ='0'";
        $where["dossier_medical_id"] = " = '$this->_id'";
        $where["rques"]              = 'NOT IN ("' . str_replace('|', '","', CAppUI::gconf("soins Other ignore_allergies")) . '")';

        return $this->_count_allergies = $antecedent->countList($where);
    }


    /**
     * MassCount des allergies
     *
     * @param array $dossiers Dossier médicaux
     *
     * @return array
     */
    static function massCountAllergies($dossiers = [])
    {
        $antecedent                  = new CAntecedent();
        $where["type"]               = "= 'alle'";
        $where["annule"]             = " ='0'";
        $where["dossier_medical_id"] = CSQLDataSource::prepareIn($dossiers);
        $where["rques"]              = 'NOT IN ("' . str_replace('|', '","', CAppUI::gconf("soins Other ignore_allergies")) . '")';

        $request = new CRequest();
        $request->addColumn("dossier_medical_id");
        $request->addColumn("count(*)", "c");
        $request->addWhere($where);
        $request->addGroup("dossier_medical_id");
        $request->addTable("antecedent");

        return $antecedent->getDS()->loadHashList($request->makeSelect());
    }


    /**
     * Chargement des antécédents par type
     *
     * @param string $type        Type des antécédents
     * @param bool   $see_absence Affiche les absences d'allergies
     *
     * @return array|CStoredObject[]
     */
    function loadRefsAntecedentsOfType($type, $see_absence = false)
    {
        if (!$this->_id) {
            return $this->_ref_antecedents_by_type[$type] = [];
        }

        $antecedent       = new CAntecedent();
        $antecedent->type = $type;

        if ($see_absence && $type = 'alle') {
            $antecedent->absence = "1";
        } else {
            $antecedent->absence = "0";
        }

        $antecedent->annule             = "0";
        $antecedent->dossier_medical_id = $this->_id;

        return $this->_ref_antecedents_by_type[$type] = $antecedent->loadMatchingList();
    }

    /**
     * Chargement des allergies
     *
     * @param bool $see_absence Affiche les absences d'allergies
     *
     * @return CAntecedent[]
     */
    function loadRefsAllergies($see_absence = false)
    {
        return $this->_ref_allergies = $this->loadRefsAntecedentsOfType("alle", $see_absence);
    }

    /**
     * Load the allergies not matching "ignore_allergies" (config)
     *
     * @return CAntecedent[] allergie list
     */
    function loadRefsActiveAllergies()
    {
        self::loadRefsAllergies();
        $allergies = [];
        $ignores   = array_map('trim', explode("|", CAppUI::gconf("soins Other ignore_allergies")));
        foreach ($this->_ref_allergies as $_allergie) {
            if (!in_array(trim($_allergie->rques), $ignores)) {
                $allergies[] = $_allergie;
            }
        }

        return $this->_ref_allergies = $allergies;
    }

    /**
     * Chargement des déficiences
     *
     * @return CStoredObject[]
     */
    function loadRefsDeficiences()
    {
        return $this->_ref_deficiences = $this->loadRefsAntecedentsOfType("deficience");
    }

    /**
     * Comptage des antécédents par type
     *
     * @param self[] $dossiers liste des dossiers
     * @param string $type     Type des antécédents
     *
     * @return void
     */
    static function massCountAntecedentsByType($dossiers, $type = "")
    {
        if ($type && !preg_match("/$type/", CAppUI::conf("patients CAntecedent types"))) {
            return;
        }
        $where = [];
        if ($type) {
            $where["type"] = "= '$type'";
        }

        CMbObject::massCountBackRefs($dossiers, "antecedents", $where);

        foreach ($dossiers as $_dossier) {
            if ($type) {
                $_dossier->_count_antecedents_by_type[$type] = $_dossier->_count["antecedents"];
            } else {
                $_dossier->_count_antecedents = $_dossier->_count["antecedents"];
            }
        }
    }

    /**
     * Chargement des traitements personnels
     *
     * @param bool $cancelled Prise en compte des annulés
     *
     * @return CTraitement[]
     */
    function loadRefsTraitements($cancelled = false)
    {
        $order = "fin DESC, debut DESC";

        $this->_ref_traitements = $this->loadBackRefs("traitements", $order);

        // Filtrage sur les annulés
        foreach ($this->_ref_traitements as $_traitement) {
            /** @var $_traitement CTraitement */
            if ($_traitement->annule && !$cancelled) {
                unset($this->_ref_traitements[$_traitement->_id]);
            }
        }

        return $this->_ref_traitements;
    }

    /**
     * Identifiant de dossier médical lié à l'objet fourni.
     * Crée le dossier médical si nécessaire
     *
     * @param integer $object_id    Identifiant de l'objet
     * @param string  $object_class Classe de l'objet
     *
     * @return integer Id du dossier médical
     */
    static function dossierMedicalId($object_id, $object_class)
    {
        $dossier               = new CDossierMedical();
        $dossier->object_id    = $object_id;
        $dossier->object_class = $object_class;
        $dossier->loadMatchingObject();
        if (!$dossier->_id) {
            $dossier->store();
        }

        return $dossier->_id;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->completeField("codes_cim");
        $this->_codes_cim = $this->codes_cim ? explode("|", $this->codes_cim) : [];

        if ($this->_added_code_cim) {
            $da = CCodeCIM10::get($this->_added_code_cim);
            if (!$da->exist) {
                CAppUI::setMsg("Le code CIM saisi n'est pas valide", UI_MSG_WARNING);

                return null;
            }
            $this->_codes_cim[] = $this->_added_code_cim;
        }

        if ($this->_deleted_code_cim) {
            CMbArray::removeValue($this->_deleted_code_cim, $this->_codes_cim);
        }

        $this->codes_cim = implode("|", array_unique($this->_codes_cim));

    $this->completeField("object_id", "object_class");
    if ($this->object_class == "CPatient" && $this->fieldModified("codes_cim")) {
        Cache::deleteKeys(Cache::DISTR, "alertes-CPatient-" . $this->object_id . '-');
    }

        if (CModule::getActive('snomed') && $this->_id && $this->_del_code_cim_snomed) {
            CSnomed::deleteIdexSnomed($this, $this->_del_code_cim_snomed);
        }

        return parent::store();
    }

    /**
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template, $champ = "Patient")
    {
        // Antécédents
        $this->getValuesListForAntecedents($champ, $template);

        // Antécédents non présent
        $this->getValuesListForAntecedents($champ, $template, true);

        // Antécédents de la même fonction
        $this->getValuesListForAntecedents($champ, $template, false, true);

        $traitements_subItem = CAppUI::tr('CTraitement.more');
        // Traitements
        $this->loadRefsTraitements();
        if (is_array($this->_ref_traitements)) {
            $list = [];
            /** @var $_traitement CTraitement */
            foreach ($this->_ref_traitements as $_traitement) {
                if ($_traitement->fin && $_traitement->fin <= CMbDT::date()) {
                    continue;
                }
                $debut  = $_traitement->debut ? " " . CAppUI::tr('common-since') . " " . $_traitement->getFormattedValue("debut") : "";
                $fin    = $_traitement->fin ? " " . CAppUI::tr('CPatientState-_date_max') . " " . $_traitement->getFormattedValue("fin") : "";
                $colon  = $debut || $fin ? ": " : "";
                $list[] = $debut . $fin . $colon . $_traitement->traitement;
            }

            // Ajout des traitements notés a l'aide de la BCB
            // On force la récup de la première prescription sans passer par le loadUniqueBackRef pour éviter le trigger_error dans l'API dans le cas où on en a plusieurs
            $prescription = CModule::getActive('appFine') ? CAppFineServer::loadRefUniquePrescription($this) : $this->loadRefPrescription();
            if ($prescription && $prescription->_id) {
                $prescription->loadRefsLinesMed();
                foreach ($prescription->_ref_prescription_lines as $_line) {
                    if ($_line->fin && $_line->fin <= CMbDT::date()) {
                        continue;
                    }
                    $view      = $_line->_ucd_view;
                    $prises    = $_line->loadRefsPrises();
                    $posologie = implode(" - ", CMbArray::pluck($prises, "_view"));
                    $posologie = $posologie ? " ($posologie)" : "";
                    $duree     = "";
                    if ($_line->debut && (!$_line->fin || $_line->fin >= CMbDT::date())) {
                        if ($_line->fin) {
                            $duree = " (" . CAppUI::tr(
                                    'common-From %s to %s',
                                    $_line->getFormattedValue("debut"),
                                    $_line->getFormattedValue("fin")
                                ) . ")";
                        } else {
                            $duree = " (" . CAppUI::tr('common-Since the %s', $_line->getFormattedValue("debut")) . ")";
                        }
                    } elseif ($_line->fin && $_line->fin >= CMbDT::date()) {
                        $duree = " (" . CAppUI::tr('common-Until the %s', $_line->getFormattedValue("fin")) . ")";
                    }

                    $list[] = $view . $posologie . $duree . ($_line->commentaire ? "\n$_line->commentaire" : "");
                }
            }
            $traitements_property = $list;
            // Si aucun traitement, on affiche "Aucun traitement"
            if (!count($list)) {
                $traitements_property = [CAppUI::tr("CTraitement-none-filled")];
                // Si aucun traitement et case absence traitement cochée, on affiche "Absence traitement"
                if ($this->absence_traitement) {
                    $traitements_property = [CAppUI::tr('CDossierMedical-absence_traitement_personnel')];
                }
            }
            $template->addListProperty("$champ - $traitements_subItem", $traitements_property);
        } else {
            $template->addProperty(
                "$champ - $traitements_subItem",
                $this->absence_traitement ? $this->getLocale("absence_traitement_personnel") : ""
            );
        }
        $template->addProperty(
            "$champ - $traitements_subItem - " . CAppUI::tr('CDossierMedical-absence_traitement_personnel'),
            $this->absence_traitement ? $this->getLocale("absence_traitement_personnel") : ""
        );

        // Etat dentaire
        $etats = [];
        foreach ($this->loadRefsEtatsDents() as $etat) {
            if ($etat->etat) {
                switch ($etat->dent) {
                    case 10:
                    case 50:
                        $position = 'Central haut';
                        break;
                    case 30:
                    case 70:
                        $position = 'Central bas';
                        break;
                    default:
                        $position = $etat->dent;
                }
                if (!isset($etats[$etat->etat])) {
                    $etats[$etat->etat] = [];
                }
                $etats[$etat->etat][] = $position;
            }
        }

        // Production des listes par état
        $list = [];
        foreach ($etats as $etat => $positions) {
            sort($positions);
            $positions = implode(', ', $positions);
            $etat      = CAppUI::tr("CEtatDent.etat.$etat");
            $list[]    = "$etat: $positions";
        }

        $template->addListProperty("$champ - " . CAppUI::tr('CEtatDent'), $list);

        // Codes CIM10
        $list = [];
        if ($this->_ext_codes_cim) {
            foreach ($this->_ext_codes_cim as $_code) {
                $list[] = "$_code->code: $_code->libelle";
            }
        }

        // Facteurs de risque
        $anesth_section        = CAppUI::tr('CConsultation._type.anesth');
        $embolique_subItem     = CAppUI::tr('CDossierMedical-Embolic thromboembolism');
        $mcj_subItem           = CAppUI::tr('CDossierMedical-MCJ');
        $risque_anesth_subItem = CAppUI::tr('CDossierMedical-Anesthetic Risk');

        switch ($champ) {
            case "Sejour":
                $template->addProperty(
                    "$anesth_section - $embolique_subItem - " . CAppUI::tr('CDossierMedical-Surgery'),
                    $this->getFormattedValue("risque_thrombo_chirurgie")
                );
                $template->addProperty(
                    "$anesth_section - $mcj_subItem - " . CAppUI::tr('CDossierMedical-Surgery'),
                    $this->getFormattedValue("risque_MCJ_chirurgie")
                );
                $template->addProperty(
                    "$anesth_section - $risque_anesth_subItem - " . CAppUI::tr('CDossierMedical-Antibiotic prophylaxis'),
                    $this->getFormattedValue("risque_antibioprophylaxie")
                );
                $template->addProperty(
                    "$anesth_section - $risque_anesth_subItem - " . CAppUI::tr('CDossierMedical-Thrombo prophylaxis'),
                    $this->getFormattedValue("risque_prophylaxie")
                );
                break;
            case "Patient":
                $template->addProperty(
                    "$anesth_section - $embolique_subItem - " . CAppUI::tr('CPatient'),
                    $this->getFormattedValue("risque_thrombo_patient")
                );
                $template->addProperty(
                    "$anesth_section - $mcj_subItem - " . CAppUI::tr('CPatient'),
                    CAppUI::tr("CDossierMedical.risque_MCJ_patient.{$this->risque_MCJ_patient}")
                );
                $template->addProperty(
                    "$anesth_section - " . CAppUI::tr('CDossierMedical-Risk factor|pl'),
                    $this->getFormattedValue("facteurs_risque")
                );
            default:
                // On ne fait rien
        }

        $template->addListProperty("$champ - " . CAppUI::tr('CDossierMedical-codes_cim'), $list);

        // Pathologies
        $this->loadRefsPathologies();
        $list = [];
        foreach ($this->_ref_pathologies as $_pathologie) {
            $_pathologie_view = "";
            if ($_pathologie->debut) {
                if ($_pathologie->fin) {
                    if ($_pathologie->debut != $_pathologie->fin) {
                        $_pathologie_view .= CAppUI::tr('common-From %s to %s', $_pathologie->debut, $_pathologie->fin);
                    } else {
                        $_pathologie_view .= "$_pathologie->debut :";
                    }
                } else {
                    $_pathologie_view .= CAppUI::tr('common-Since %s', $_pathologie->debut);
                }
            } elseif ($_pathologie->fin) {
                $_pathologie_view .= CAppUI::tr('common-Until %s', $_pathologie->fin);
            }
            if ($_pathologie->indication_id || $_pathologie->indication_group_id) {
                $_pathologie->loadRefIndication();
                $_pathologie_view .= "[Vidal : " . $_pathologie->_ref_indication->name . "] ";
            }
            if ($_pathologie->pathologie) {
                $_pathologie_view .= $_pathologie->pathologie;
            }
            $list[] = $_pathologie_view;
        }
        $template->addListProperty("$champ - " . CAppUI::tr('CDossierMedical-back-pathologies'), $list);
    }

    /**
     * Supprime du dossier médical les antécedents présents dans le dossier du séjour et dans le dossier du patient
     *
     * @param CDossierMedical $dossier_sejour  Le dossier medical du sejours
     * @param CDossierMedical $dossier_patient Le dossier medical du patient
     *
     * @return void
     */
    public static function cleanAntecedentsSignificatifs(&$dossier_sejour, &$dossier_patient)
    {
        $del_ante = 0;
        foreach ($dossier_sejour->_ref_antecedents_by_type as $_cat_name => $_cat_ante) {
            if ($_cat_name != 'alle') {
                foreach ($_cat_ante as $_key => $_ante) {
                    if (isset($dossier_patient->_ref_antecedents_by_type[$_cat_name])
                        && is_array($dossier_patient->_ref_antecedents_by_type[$_cat_name])
                    ) {
                        foreach ($dossier_patient->_ref_antecedents_by_type[$_cat_name] as $_pat_key => $_pat_ante) {
                            if ($_ante->type == $_pat_ante->type && $_ante->appareil == $_pat_ante->appareil && $_ante->date == $_pat_ante->date
                                && $_ante->rques == $_pat_ante->rques && $_ante->annule == $_pat_ante->annule
                            ) {
                                $del_ante++;
                                unset($dossier_patient->_ref_antecedents_by_type[$_cat_name][$_pat_key]);
                            }
                        }
                    }
                }
            }
        }
        $dossier_patient->_count_antecedents = $dossier_patient->_count_antecedents - $del_ante;
    }

    /**
     * Obtenir la listes des valeurs des antécédents ou des antécédents non présent
     *
     * @param string           $champ       Field name
     * @param CTemplateManager $template    Template manager
     * @param bool             $see_absence Get antecedents not present
     * @param bool             $same_func   Get antecedents from same function only
     *
     * @return void
     */
    public function getValuesListForAntecedents($champ, &$template, $see_absence = false, $same_func = false)
    {
        // Séparateur pour les groupes de valeurs
        $default    = CAppUI::pref("listDefault");
        $separator  = CAppUI::pref("listInlineSeparator");
        $separators = [
            "ulli"   => "",
            "br"     => "<br />",
            "inline" => " $separator ",
        ];
        $separator  = $separators[$default];

        $suffixe = "";
        if ($see_absence) {
            $suffixe = "-No antecedent";
        } elseif ($same_func) {
            $suffixe = "-Same function";
        }

        $atcd_section = CAppUI::tr("CAntecedent$suffixe|pl");

        // Antécédents non présent
        $this->clearBackRefCache("antecedents");
        $this->loadRefsAntecedents(false, false, false, false, $see_absence, $same_func);
        $atcd = new CAntecedent();

        // Construction des listes de valeurs
        $lists_par_type          = [];
        $lists_par_appareil      = [];
        $lists_par_type_appareil = [];
        foreach ($this->_all_antecedents as $_antecedent) {
            if ($_antecedent->important) {
                $rques = CMbString::htmlEntities($_antecedent->rques . " (" . CAppUI::tr("CAntecedent-important")) . ")";
            } elseif ($_antecedent->majeur) {
                $rques = CMbString::htmlEntities($_antecedent->rques . " (" . CAppUI::tr("CAntecedent-majeur")) . ")";
            } else {
                $rques = CMbString::htmlEntities($_antecedent->rques);
            }

            $type                                         = $_antecedent->type ? $_antecedent->getFormattedValue("type") . ": " : "";
            $appareil                                     = $_antecedent->appareil ? $_antecedent->getFormattedValue("appareil") . ": " : "";
            $date                                         = $_antecedent->date ? "[" . $_antecedent->getFormattedValue("date") . "] " : "";
            $lists_par_type    [$_antecedent->type][]     = $appareil . $date . $rques;
            $lists_par_appareil[$_antecedent->appareil][] = $type . $date . $rques;
            if (!array_key_exists($_antecedent->type, $lists_par_type_appareil)) {
                $lists_par_type_appareil[$_antecedent->type] = [];
            }
            if (!array_key_exists($_antecedent->appareil, $lists_par_type_appareil[$_antecedent->type])) {
                $lists_par_type_appareil[$_antecedent->type][$_antecedent->appareil] = [];
            }
            $lists_par_type_appareil[$_antecedent->type][$_antecedent->appareil][] = $date . $rques;

            // Avoir les antécédents sans les codes CIM
            if (strpos($_antecedent->rques, ':') !== false) {
                $withoutCim = explode(":", $rques);

                //regex: Vérifie que ce soit un code CIM
                if (preg_match("/\([A-Z]\d{2}[-][A-Z]\d{2}\)|[A-Z]\d{2,3}/", $withoutCim[0])) {
                    $rques = $withoutCim[1];
                }
            }

            $lists_par_type_sans_CIM    [$_antecedent->type][] = $appareil . $date . $rques;
        }

        // Création des listes par type et appareil
        $parts = [];
        foreach ($lists_par_type_appareil as $_type => $_by_appareils) {
            $list = [];
            foreach ($_by_appareils as $_appareil => $_list) {
                $list[] = CAppUI::tr("CAntecedent.appareil.$_appareil") . ':' . $template->makeList($_list, false, 2);
            }

            $parts[] = '<strong>' . CAppUI::tr("CAntecedent.type.$_type") . '</strong>:' . $template->makeList($list, false, 1);
        }

        $template->addProperty("$champ - $atcd_section - " . CAppUI::tr('all'), implode($separator, $parts), null, false);

        // Création des listes par type
        $parts   = [];
        $types   = $atcd->_specs["type"]->_list;
        $types[] = "";
        foreach ($types as $type) {
            $sType = CAppUI::tr("CAntecedent.type.$type");
            $list  = @$lists_par_type[$type];
            if ($type) {
                $template->addListProperty("$champ - $atcd_section - $sType", $list, false);
            } else {
                $template->addListProperty("$champ - $atcd_section - " . CAppUI::tr('CAntecedent-Other (type)|pl'), $list, false);
            }
            if ($list) {
                $parts[] = "<strong>$sType</strong>: " . $template->makeList($list, false, 1);
            }
        }

        $template->addProperty("$champ - $atcd_section - " . CAppUI::tr('CAntecedent-all by type'), implode($separator, $parts), null, false);

        // Création des listes par type sans codes CIM
        $parts   = [];
        $types   = $atcd->_specs["type"]->_list;
        $types[] = "";

        foreach ($types as $type) {
            $sType = CAppUI::tr("CAntecedent.type.$type");
            $list  = @$lists_par_type_sans_CIM[$type];
            if ($type == "med") {
                $template->addListProperty("$champ - $atcd_section - $sType " . CAppUI::tr('CAntecedent-without CIM code|pl'), $list, false);
            }
            if ($list) {
                $parts[] = "<strong>$sType</strong>: " . $template->makeList($list, false, 1);
            }
        }

        $template->addProperty(
            "$champ - $atcd_section - " . CAppUI::tr('CAntecedent-all (without CIM code)|pl'),
            implode($separator, $parts),
            null,
            false
        );

        // Création des listes par appareil
        $parts       = [];
        $appareils   = $atcd->_specs["appareil"]->_list;
        $appareils[] = "";
        foreach ($appareils as $appareil) {
            $sAppareil = CAppUI::tr("CAntecedent.appareil.$appareil");
            $list      = @$lists_par_appareil[$appareil];
            if ($appareil) {
                $template->addListProperty("$champ - $atcd_section - $sAppareil", $list, false);
            } else {
                $template->addListProperty("$champ - $atcd_section - " . CAppUI::tr('CAntecedent-Other (device)|pl'), $list, false);
            }
            if ($list) {
                $parts[] = "<strong>$sAppareil</strong>: " . $template->makeList($list, false, 1);
            }
        }
        $template->addProperty("$champ - $atcd_section - " . CAppUI::tr('CAntecedent-all by device'), implode($separator, $parts), null, false);
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }

    /**
     * @inheritDoc
     */
    public function getIndexablePatient(): CPatient
    {
        $object = $this->loadRefObject();
        if ($object instanceof CPatient) {
            return $object;
        } elseif ($object instanceof CSejour) {
            return $object->loadRefPatient();
        }
    }

    /**
     * @inheritDoc
     */
    public function getIndexablePraticien(): ?CMediusers
    {
        if ($this->loadRefObject() instanceof CSejour) {
            return $this->_ref_object->loadRefPraticien();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getIndexableData(): array
    {
        $practitioner = $this->getIndexablePraticien();

        return [
            "id"               => $this->_id,
            "prat_id"          => ($practitioner) ? $practitioner->_id : "",
            "author_id"        => ($practitioner) ? $practitioner->_id : "",
            "date"             => str_replace("-", "/", CMbDT::dateTime()),
            "title"            => CAppUI::tr(CClassMap::getSN($this->loadRefObject())),
            "body"             => $this->getIndexableBody(""),
            "function_id"      => ($practitioner) ? $practitioner->function_id : "",
            "group_id"         => ($practitioner) ? $practitioner->loadRefFunction()->group_id : "",
            "patient_id"       => $this->getIndexablePatient()->_id,
            "object_ref_id"    => $this->_id,
            "object_ref_class" => CClassMap::getSN($this),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getIndexableBody($content): string
    {
        return str_replace('|', ' ', $this->codes_cim);
    }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchDossierMedical($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return CGroups|null
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        $target = $this->loadRefObject();
        if ($target instanceof IGroupRelated) {
            return $target->loadRelGroup();
        }

        return null;
    }
}
