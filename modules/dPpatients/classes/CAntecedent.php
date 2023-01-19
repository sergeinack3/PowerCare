<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\AppFine\Server\CAppFineDashboardTaskHealth;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CHyperTextLink;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Snomed\CSnomed;

/**
 * Antecedent
 */
class CAntecedent extends CMbObject implements ImportableInterface, IGroupRelated
{
    /** @var string */
    public const RESOURCE_TYPE = 'antecedent';

    /** @var string */
    public const RELATION_DOSSIER_MEDICAL = 'medicalRecord';

    public static $mappingFamilyLink = [
        'membre_famille' => 'FAMMEMB',
        'mere' => 'MTH',
        'pere' => 'FTH',
        'grand_mere' => 'GRMTH',
        'grand_pere' => 'GRFTH',
        'arriere_grand_mere' => 'GGRMTH',
        'arriere_grand_pere' => 'GGRFTH',
        'frere' => 'BRO',
        'soeur' => 'SIS',
        'demi_frere' => 'HBRO',
        'demi_soeur' => 'HSIS',
        'cousin' => 'COUSN',
        'cousin_paternel' => 'PCOUSN',
        'cousin_maternel' => 'MCOUSN',
        'tante' => 'AUNT',
        'oncle' => 'UNCLE',
        'niece' => 'NIECE',
        'enfant' => 'CHILD',
        'fille' => 'DAU',
        'fils' => 'SON',
        'petite_fille' => 'GRNDDAU',
        'petit_fils' => 'GRNDSON',
        'mari' => 'HUSB',
        'femme' => 'WIFE',
    ];

  // DB Table key
  public $antecedent_id;

  // DB fields
  public $type;
  public $appareil;
  public $date;
  public $date_fin;
  public $rques;
  public $dossier_medical_id;
  public $dossier_tiers_id;
  public $annule;
  public $majeur;
  public $important;
  public $doctor;
  public $comment;
  public $verified;
  public $origin;
  public $origin_autre;
  public $absence;
  public $degree_certainty;
  public $reaction_indesirable;
  public $family_link;
  public $owner_id;
  public $creation_date;

  public $_ref_doctor;

  // Form Fields
  public $_search;
  public $_aides_all_depends;
  public $_idex_code;
  public $_idex_tag;
  public $_hypertext_links_ids;
  public $_is_exist_dm;
  public $_no_synchro_eai = false;

  // Distant fields
  public $_count_rques_aides;
  public $_count_rques_aides_appareil;

  /** @var CMediusers */
  public $_ref_owner;

  /** @var CDossierMedical */
  public $_ref_dossier_medical;

  /** @var CDossierTiers */
  public $_ref_dossier_tiers;

  /** @var CDatedCodeCCAM */
  public $_ref_code_ccam;

  /** @var CAppFineDashboardTaskHealth */
  public $_ref_dashboard_task_health;

  /** @var CAntecedent */
  public $_antecedent_sejour;
  /** @var CDossierMedical */
  public $_dossier_medical_sejour;
  public $_codes_cim10_sejour = array();
  public $_codes_cim10        = array();
  public $_codes_cim10_detail = array();
  public $_codes_ccam         = array();
  public $_codes_ccam_detail  = array();


  /** @var CLoinc[] */
  public $_ref_codes_loinc;
  /** @var CSnomed[] */
  public $_ref_codes_snomed;

  // Types
  static $types = array(
    'med', 'alle', 'trans', 'obst', 'deficience', 'chir', 'fam', 'anesth', 'gyn',
    'cardio', 'pulm', 'stomato', 'plast', 'ophtalmo', 'digestif', 'gastro',
    'stomie', 'uro', 'ortho', 'traumato', 'amput', 'neurochir', 'greffe', 'thrombo',
    'cutane', 'hemato', 'rhumato', 'neuropsy', 'infect', 'endocrino', 'carcino',
    'orl', 'addiction', 'habitus', 'coag', 'dentaire'
  );

  // Types that should not be types, mostly appareils
  static $non_types = array(
    'obst', 'gyn', 'cardio', 'stomato', 'digestif', 'gastro', 'stomie', 'neuropsy',
    'endocrino', 'orl', 'uro', 'ortho', 'pulm',
  );

  // Appareils
  static $appareils = array(
    'cardiovasculaire', 'digestif', 'endocrinien', 'neuro_psychiatrique',
    'pulmonaire', 'uro_nephrologique', 'orl', 'gyneco_obstetrique', 'orthopedique',
    'ophtalmologique', 'locomoteur', 'terrain', 'neuro', 'divers', 'cancero', 'maxillo_faciale'
  );

  // Couleurs degrées de certitude
  static $degree_certainty_colors = array(
    'undefined'  => 'grey',
    'unprobable' => 'd6aa2c',
    'probable'   => 'cfe218',
    'proven'     => '0cff00',
    'excluded'   => 'fe3231',
    'inexact'    => 'fd6430',
    'duplicate'  => '1a85f2'
  );

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'antecedent';
    $spec->key   = 'antecedent_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                         = parent::getProps();
    $props["type"]                 = "enum list|" . CAppUI::conf("patients CAntecedent types") . " fieldset|default";
    $props["appareil"]             = "enum list|" . CAppUI::conf("patients CAntecedent appareils") . " fieldset|default";
    $props["date"]                 = "date progressive fieldset|default";
    $props["date_fin"]             = "date progressive fieldset|default";
    $props["rques"]                = "text helped|type|appareil fieldset|default";
    $props["doctor"]               = "str fieldset|extra";
    $props["comment"]              = "text fieldset|extra";
    $props["dossier_medical_id"]   = "ref class|CDossierMedical show|0 back|antecedents cascade fieldset|extra";
    $props["dossier_tiers_id"]     = "ref class|CDossierTiers show|0 back|antecedents_tiers cascade fieldset|extra";
    $props["annule"]               = "bool show|0 fieldset|default";
    $props["majeur"]               = "bool fieldset|default";
    $props["important"]            = "bool fieldset|default";
    $props["owner_id"]             = "ref class|CMediusers back|antecedents fieldset|extra";
    $props["creation_date"]        = "dateTime fieldset|extra";
    $props["_search"]              = "str";
    $props["verified"]             = "enum list|0|1|2 default|0 show|0 fieldset|extra";
    $props["origin"]               = "enum list|autre|patient|labo|crmedical default|patient fieldset|extra";
    $props["origin_autre"]         = "str fieldset|extra";
    $props["absence"]              = "bool fieldset|default";
    $props["degree_certainty"]     = "enum list|undefined|unprobable|probable|proven|excluded|inexact|duplicate default|undefined show|0 fieldset|extra";
    $props["reaction_indesirable"] = "text fieldset|extra";
    $props["family_link"]          = "enum list|membre_famille|mere|pere|grand_mere|grand_pere|arriere_grand_mere|arriere_grand_pere|frere|soeur|demi_frere|demi_soeur|cousin|cousin_paternel|cousin_maternel|tante|oncle|niece|enfant|fille|fils|petite_fille|petit_fils|mari|femme fieldset|default";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = CMbString::truncate($this->rques, 40);

    $this->_codes_cim10 = $this->extractCim10Codes();
    $this->_codes_ccam  = $this->extractCCAMCodes();
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType)
  {
      return ($this->dossier_medical_id ? $this->loadRefDossierMedical()->getPerm($permType) : null) && parent::getPerm($permType);
  }

  /**
   * Load dashboard task health for an antecedent
   *
   * @param int $dashboard_task_id dashboard task id
   *
   * @return CAppFineDashboardTaskHealth|null
   */
  function loadRefDashboardTaskHealth($dashboard_task_id) {
    $dashboard_task_health                            = new CAppFineDashboardTaskHealth();
    $dashboard_task_health->object_class              = $this->_class;
    $dashboard_task_health->object_id                 = $this->_id;
    $dashboard_task_health->appfine_dashboard_task_id = $dashboard_task_id;
    $dashboard_task_health->loadMatchingObject();

    return $this->_ref_dashboard_task_health = $dashboard_task_health;
  }

  /**
   * Charge le dossier médical associé
   *
   * @return CDossierMedical
   * @throws \Exception
   */
  function loadRefDossierMedical() {
    $this->_ref_dossier_medical = new CDossierMedical();

    return $this->_ref_dossier_medical->load($this->dossier_medical_id);
  }

  /**
   * Charge le créateur de l'antécédent
   *
   * @return CMediusers
   * @throws \Exception
   */
  function loadRefOwner() {
    return $this->_ref_owner = $this->loadFwdRef("owner_id");
  }

  /**
   * Charge le dossier médical tiers associé
   *
   * @return CDossierTiers
   * @throws \Exception
   */
  function loadRefDossierTiers() {
    $this->_ref_dossier_tiers = new CDossierTiers();

    return $this->_ref_dossier_tiers->load($this->dossier_tiers_id);
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefDossierMedical();
    $this->loadRefsHyperTextLink();
    $this->loadRefsCodesLoinc();
    $this->loadRefsCodesSnomed();
  }

  /**
   * Update owner and creation date from user logs
   *
   * @return void
   * @throws \Exception
   */
  function updateOwnerAndDates() {
    if (!$this->_id || $this->owner_id && $this->creation_date) {
      return;
    }

    if (empty($this->_ref_logs)) {
      $this->loadLogs();
    }

    $first_log = $this->_ref_first_log;

    if ($first_log) {
      $this->owner_id      = $first_log->user_id;
      $this->creation_date = $first_log->date;

      $this->rawStore();
    }
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("type");
    if ($this->type == "alle") {
      $this->loadRefDossierMedical();
      $dossier_medical = $this->_ref_dossier_medical;
      if ($dossier_medical->object_class == "CPatient") {
          Cache::deleteKeys(Cache::DISTR, "alertes-CPatient-" . $dossier_medical->object_id . '-');
      }
    }
      if ($this->type !== "fam") {
          $this->family_link = "";
      }

    // Save owner and creation date
    if (!$this->_id) {
      if (!$this->creation_date) {
        $this->creation_date = CMbDT::dateTime();
      }

      if (!$this->owner_id) {
        $this->owner_id = CMediusers::get()->_id;
      }
    }

    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }

    // DossierMedical store
    $this->checkCodeCim10();

    // Sauvegarde de l'identifiant externe (code composant de la BDM pour le cas des allergies)
    if ($this->_idex_code && $this->_idex_tag) {
      $idex = new CIdSante400();
      $idex->setObject($this);
      $idex->id400 = $this->_idex_code;
      $idex->tag   = $this->_idex_tag;
      $idex->store();
    }

    // Sauvegarde des liens hypertextes éventuels
    if (is_array($this->_hypertext_links_ids) && count($this->_hypertext_links_ids)) {
      $link  = new CHyperTextLink();
      $where = array(
        "hypertext_link_id" => CSQLDataSource::prepareIn($this->_hypertext_links_ids)
      );
      /** @var CHyperTextLink $_link */
      foreach ($link->loadList($where) as $_link) {
        $_link->_id = "";
        $_link->setObject($this);
        $_link->store();
      }
    }

    // Check if it is an input help and if there are associated Loinc or Snomed codes
    if ($this->_id) {
      $this->checkInputHelp();
    }

    return null;
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    $this->completeField("type", "dossier_medical_id");

    if ($this->type == "alle") {
      $this->loadRefDossierMedical();
      $dossier_medical = $this->_ref_dossier_medical;
      if ($dossier_medical->object_class == "CPatient") {
          Cache::deleteKeys(Cache::DISTR, "alertes-CPatient-" . $dossier_medical->object_id . '-');
      }
    }

    return parent::delete();
  }

  /**
   * Extract the CIM10 codes in the
   *
   * @return array
   */
  public function extractCim10Codes() {
    $codes_cim10 = array();

    if (CModule::getActive('dPcim10') && CSQLDataSource::get('cim10', true)) {
      if (is_string($this->rques)) {
          preg_match_all('/\b[A-Z]\d{2}\.?\d{0,5}\b/i', $this->rques, $matches);
      } else {
          $matches = [];
      }

      foreach ($matches as $match_) {
        foreach ($match_ as &$match) {
          // Transformation du code CIM pour le tester
          $match = str_replace(".", "", $match);
          $match = strtoupper($match);

          // Chargement du code CIM 10
          $code_cim10 = CCodeCIM10::get($match);

          if ($code_cim10->libelle != "Code CIM inexistant") {
            $codes_cim10[]                     = $match;
            $this->_codes_cim10_detail[$match] = $code_cim10;
          }
        }
      }
    }

    return $codes_cim10;
  }

  /**
   * Extract the CCAM codes in the antecedent
   *
   * @return array
   */
  public function extractCCAMCodes() {
    if (CModule::getActive('dPccam') && CSQLDataSource::get('ccamV2', true)) {
      if (is_string($this->rques)) {
          preg_match_all('/\b[A-Z]{4}\d{3}\b/i', $this->rques, $matches);
      } else {
          $matches = [];
      }

      foreach ($matches as $match_) {
        foreach ($match_ as &$match) {
          $match = strtoupper($match);

          // Chargement du code CCAM
          $code_ccam = $code = CDatedCodeCCAM::get($match);

          if ($code_ccam->code) {
            $this->_codes_ccam_detail[$match] = $code_ccam;
          }
        }
      }
    }

    return $this->_codes_ccam_detail;
  }

  /**
   * Vérifie et extrait les codes CIM des remarques pour les sauvegarder dans le dossier médical
   *
   * @return void
   * @throws \Exception
   */
  function checkCodeCim10() {
    // Si c'est une absence d'antécédent, pas d'ajout des codes CIM
    if ($this->absence) {
      return;
    }

    $codes_cim10 = $this->extractCim10Codes();

    $dossier_medical = new CDossierMedical();
    $dossier_medical->load($this->dossier_medical_id);

    foreach ($codes_cim10 as $code_cim10) {
      /* si le code n'est pas deja present, on le rajoute */
      if (!array_key_exists($code_cim10, $dossier_medical->_ext_codes_cim)) {
        if ($dossier_medical->codes_cim != "") {
          $dossier_medical->codes_cim .= "|";
        }
        $dossier_medical->codes_cim .= $code_cim10;
      }
    }

    $dossier_medical->store();
  }

  /**
   * @see parent::check()
   */
  function check() {
    $this->completeField("dossier_medical_id", "dossier_tiers_id");
    // Si on enregistre un antécédent qui n'a pas de DM ou de Dossiers tiers
    if (!$this->dossier_medical_id && !$this->dossier_tiers_id) {
      return CAppUI::tr("CAntecent-msg-No medical file");
    }

    // Si on merge le dossier médical et que le type n'existe pas
    if (($this->_forwardRefMerging &&
        (in_array($this->type, CAntecedent::$non_types) ||
          !in_array($this->type, explode("|", CAppUI::conf("patients CAntecedent types"))))
      )
      || $this->fieldModified("annule", "1") // On ne vérifie pas le type si on annule un ATCD
    ) {
      return null;
    }

    return parent::check();
  }

  /**
   * @inheritdoc
   */
  function loadAides(
      $user_id,
      $needle = null,
      $depend_value_1 = null,
      $depend_value_2 = null,
      $object_field = null,
      $strict = "true"
  ) {
    parent::loadAides($user_id, $needle, $depend_value_1, $depend_value_2);

    $rques_aides =& $this->_aides_all_depends["rques"];
    if (!isset($rques_aides)) {
      return;
    }

    $depend_field_1  = $this->_specs["rques"]->helped[0];
    $depend_values_1 = $this->_specs[$depend_field_1]->_list;
    asort($depend_values_1);
    $depend_values_1[] = "";
    foreach ($depend_values_1 as $depend_value_1) {
      $count                  =& $this->_count_rques_aides;
      $count[$depend_value_1] = 0;
      if (isset($rques_aides[$depend_value_1])) {
        foreach ($rques_aides[$depend_value_1] as $aides_by_depend_field_2) {
          $count[$depend_value_1] += count($aides_by_depend_field_2);
        }
      }
    }
  }

  /**
   * Load le médecin de l'antécédent
   *
   * @param integer $antecedent_id The antecedent id
   *
   * @return CMedecin
   * @throws \Exception
   */
  function loadDoctor($antecedent_id) {
    $this->load($antecedent_id);

    $medecin = new CMedecin();

    if (!is_numeric($this->doctor)) {
      $medecin->_view = $this->doctor;
    }
    else {
      $where = array();
      switch (strlen($this->doctor)) {
        case 11:
          $where["rpps"] = " = '$this->doctor'";
          $medecin->loadObject($where);
          break;

        case 9:
          $where["adeli"] = " = '$this->doctor'";
          $medecin->loadObject($where);
          break;

        default:
      }
    }

    return $this->_ref_doctor = $medecin;
  }

  /**
   * Charge les éléments significatifs liés (antécédents et cim10)
   *
   * @param integer $sejour_id The id of the sejour (optional)
   *
   * @return void
   * @throws \Exception
   */
  public function loadRefLinkedElements($sejour_id = null) {
    $this->loadRefDossierMedical();

    $antecedent      = new CAntecedent();
    $dossier_medical = new CDossierMedical();
    $codes_cim10     = array();
    if ($this->_ref_dossier_medical->object_class != 'CSejour') {
      $where = array('dossier_medical.object_class' => " = 'CSejour'");

      if ($this->type) {
        $where['antecedent.type'] = " = '$this->type'";
      }
      if ($this->appareil) {
        $where['antecedent.appareil'] = " = '$this->appareil'";
      }
      if ($this->rques) {
        $ds                        = $this->getDS();
        $where['antecedent.rques'] = $ds->prepare(" = %1", $this->rques);
      }
      if ($this->date) {
        $where['antecedent.date'] = " = '$this->date'";
      }

      if ($sejour_id) {
        $where['dossier_medical.object_id'] = " = $sejour_id";
      }

      $ljoin = array('dossier_medical' => 'antecedent.dossier_medical_id = dossier_medical.dossier_medical_id');

      $antecedent->loadObject($where, null, null, $ljoin);

      if ($antecedent->_id) {
        $dossier_medical = $antecedent->loadRefDossierMedical();
        if ($dossier_medical->codes_cim) {
          foreach ($dossier_medical->_codes_cim as $_code_cim) {
            if (strpos($this->rques, $_code_cim) !== false) {
              $codes_cim10[] = $_code_cim;
            }
          }
        }
      }
    }

    $this->_antecedent_sejour      = $antecedent;
    $this->_dossier_medical_sejour = $dossier_medical;
    $this->_codes_cim10_sejour     = $codes_cim10;
  }

  /**
   * Return idex type if it's special
   *
   * @param CIdSante400 $idex Idex
   *
   * @return string|null
   */
  function getSpecialIdex(CIdSante400 $idex) {
    if (CModule::getActive('snomed') && ($idex->tag == CSnomed::getSnomedTag())) {
      return "SNOMED";
    }

    if (CModule::getActive('loinc') && ($idex->tag == CLoinc::getLoincTag())) {
      return "LOINC";
    }

    return null;
  }

  /**
   * Get all CLoinc[] backrefs
   *
   * @return CLoinc[]|null
   */
  function loadRefsCodesLoinc() {
    if (!CModule::getActive('loinc')) {
      return null;
    }

    $codes_loinc = array();

    $idex = new CIdSante400();
    $idex->setObject($this);
    $idex->tag = CLoinc::getLoincTag();
    $idexes    = $idex->loadMatchingList();

    foreach ($idexes as $_idex) {
      $loinc = new CLoinc();
      $loinc->load($_idex->id400);

      $codes_loinc[$loinc->_id] = $loinc;
    }

    return $this->_ref_codes_loinc = $codes_loinc;
  }

  /**
   * Get all CSnomed[] backrefs
   *
   * @return CSnomed[]|null
   */
  function loadRefsCodesSnomed() {
    if (!CModule::getActive('snomed')) {
      return null;
    }

    $codes_snomed = array();

    $idex = new CIdSante400();
    $idex->setObject($this);
    $idex->tag = CSnomed::getSnomedTag();
    $idexes    = $idex->loadMatchingList();

    foreach ($idexes as $_idex) {
      $snomed = new CSnomed();
      $snomed->load($_idex->id400);

      $codes_snomed[$snomed->_id] = $snomed;
    }

    return $this->_ref_codes_snomed = $codes_snomed;
  }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceMedicalRecord(): ?Item
    {
        $dossier_medical = $this->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return null;
        }

        return new Item($dossier_medical);
    }

    /**
     * @return bool
     */
    public function hasNameCodeCCAM(): bool
    {
        $explodes = explode(" ", $this->rques);
        foreach ($explodes as $_explode) {
            if (preg_match("/^[A-Z]{4}[0-9]{3}(-[0-9](-[0-9])?)?$/i", $_explode)) {
                $code_ccam = new CDatedCodeCCAM($_explode);
                $code_ccam->load();

                if ($code_ccam->code != $_explode) {
                    continue;
                }

                $this->_ref_code_ccam = $code_ccam;

                return true;
            }
        }

        return false;
    }

  /**
   * Check if it is an input help and if there are associated Loinc or Snomed codes
   *
   * @return void
   */
  function checkInputHelp() {
    if (!CModule::getActive('loinc') && !CModule::getActive('snomed')) {
      return null;
    }

    $group    = CGroups::loadCurrent();
    $user     = CMediusers::get();
    $function = $user->loadRefFunction();

    $rques = $this->rques !== null ? addslashes($this->rques) : '';

    $where          = array();
    $where[]        = "group_id = '$group->_id' OR function_id = '$function->_id' OR user_id = '$user->_id'";
    $where["text"]  = " = '$rques'";
    $where["class"] = " = '$this->_class'";

    $aide  = new CAideSaisie();
    $aides = $aide->loadList($where);

    $aide_saisie = reset($aides);

    if ($aide_saisie && $aide_saisie->_id) {
      $codes_loinc  = $aide_saisie->loadRefsCodesLoinc();
      $codes_snomed = $aide_saisie->loadRefsCodesSnomed();

      if ($codes_loinc) {
        foreach ($codes_loinc as $_loinc) {
          $idex               = new CIdSante400();
          $idex->tag          = CLoinc::getLoincTag();
          $idex->object_class = $this->_class;
          $idex->object_id    = $this->_id;
          $idex->id400        = $_loinc->_id;

          if (!$idex->loadMatchingObjectEsc()) {
            $idex->store();
          }
        }
      }

      if ($codes_snomed) {
        foreach ($codes_snomed as $_snomed) {
          $idex               = new CIdSante400();
          $idex->tag          = CSnomed::getSnomedTag();
          $idex->object_class = $this->_class;
          $idex->object_id    = $this->_id;
          $idex->id400        = $_snomed->_id;

          if (!$idex->loadMatchingObjectEsc()) {
            $idex->store();
          }
        }
      }
    }
  }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface {
        return $matcher->matchAntecedent($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface {
        return $persister->persistObject($this);
    }

    /**
     * @return CGroups
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        if ($this->dossier_medical_id) {
            return $this->loadRefDossierMedical()->loadRelGroup();
        } elseif ($this->dossier_tiers_id) {
            return $this->loadRefDossierTiers()->loadRelGroup();
        }

        return null;
    }
}
