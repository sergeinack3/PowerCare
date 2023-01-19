<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Module\CModule;
use Ox\Interop\Fhir\Resources\R4\ConceptMap\CFHIRResourceConceptMap;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Snomed\CSnomed;

/**
 * Remplacement d'un mot-clé par une plus longue chaîne de caractères
 * S'associe sur toute propriété d'une classe dont la spec contient helped
 */
class CAideSaisie extends CMbObject {

    /** @var string  */
    public const RESOURCE_TYPE = "input_help";

    /** @var string  */
    public const RELATION_USER = 'user';
    /** @var string  */
    public const RELATION_FUNCTION = 'function';
    /** @var string  */
    public const RELATION_GROUP = 'group';

    /** @var string */
    public const FIELDSET_AUTHOR = 'author';
    /** @var string */
    public const FIELDSET_TARGET = 'target';

  // DB Table key
  public $aide_id;

  // DB References
  public $user_id;
  public $function_id;
  public $group_id;

  // DB fields
  public $class;
  public $field;
  public $name;
  public $text;
  public $depend_value_1;
  public $depend_value_2;

  // Form Fields
  public $_depend_field_1;
  public $_depend_field_2;
  public $_owner;
  public $_vw_depend_field_1;
  public $_vw_depend_field_2;
  public $_is_ref_dp_1;
  public $_is_ref_dp_2;
  public $_class_dp_1;
  public $_class_dp_2;
  public $_applied;
  public $_ref_object_dp_1;
  public $_ref_object_dp_2;
  public $_owner_view;
  public $_is_for_instance;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CGroups */
  public $_ref_group;

  /** @var CMediusers|CFunctions|CGroups */
  public $_ref_owner;

  static $_load_lite = false;

  /** @var CLoinc[] */
  public $_ref_codes_loinc;
  /** @var CSnomed[] */
  public $_ref_codes_snomed;

  public $_ref_concept_map;
  public $_ref_codes_cim10_concept_map;
  public $_ref_codes_snomed_concept_map;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'aide_saisie';
    $spec->key   = 'aide_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["user_id"]      = "ref class|CMediusers back|aides_saisie fieldset|author";
    $props["function_id"]  = "ref class|CFunctions back|aides fieldset|author";
    $props["group_id"]     = "ref class|CGroups back|aides_saisie fieldset|author";

    $props["class"]        = "str notNull fieldset|target";
    $props["field"]        = "str notNull fieldset|target";
    $props["name"]         = "str notNull seekable fieldset|default";
    $props["text"]         = "text notNull seekable fieldset|default";
    $props["depend_value_1"] = "str";
    $props["depend_value_2"] = "str";

    $props["_depend_field_1"] = "str";
    $props["_depend_field_2"] = "str";
    $props["_vw_depend_field_1"] = "str";
    $props["_vw_depend_field_2"] = "str";
    $props["_owner"]          = "enum list|user|func|etab";
    $props["_owner_view"]     = "str";
    return $props;
  }

  /**
   * Vérifie l'unicité d'une aide à la saisie
   * 
   * @return string
   */
  function check() {
    $msg = "";

    $ds = $this->_spec->ds;

    $where = array();
    if ($this->user_id) {
      $where["user_id"] = $ds->prepare("= %", $this->user_id);
    }
    else if ($this->function_id) {
      $where["function_id"] = $ds->prepare("= %", $this->function_id);
    }
    elseif ($this->group_id) {
      $where["group_id"] = $ds->prepare("= %", $this->group_id);
    }
    else {
      $where[] = "user_id IS NULL AND function_id IS NULL AND group_id IS NULL";
    }

    $where["class"]          = $ds->prepare("= %",  $this->class);
    $where["field"]          = $ds->prepare("= %",  $this->field);
    $where["depend_value_1"] = $ds->prepare("= %",  $this->depend_value_1);
    $where["depend_value_2"] = $ds->prepare("= %",  $this->depend_value_2);
    $where["text"]           = $ds->prepare("= %",  $this->text);
    $where["aide_id"]        = $ds->prepare("!= %", $this->aide_id);

    $sql = new CRequest();
    $sql->addSelect("count(aide_id)");
    $sql->addTable("aide_saisie");
    $sql->addWhere($where);

    $nb_result = $ds->loadResult($sql->makeSelect());

    if ($nb_result) {
      $msg .= "Cette aide existe déjà<br />";
    }

    return $msg . parent::check();
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;

    $this->class = self::getSanitizedClassName($this->class);

    // Owner
    if ($this->user_id ) {
      $this->_owner = "user";
    }
    if ($this->function_id) {
      $this->_owner = "func";
    }
    if ($this->group_id) {
      $this->_owner = "etab"; 
    }
    if (!$this->user_id && !$this->function_id && !$this->group_id) {
      $this->_owner = "instance";
    }

    $this->isForInstance();

    if ($this->class) {
      switch ($this->class) {
        case "CTransmissionMedicale":
          $this->_class_dp_2 = "CCategoryPrescription";
          break;
        case "CObservationResult":
          $this->_class_dp_1 = "CObservationValueType";
          $this->_class_dp_2 = "CObservationValueUnit";
          break;
        case "CPrescriptionLineElement":
          $this->_class_dp_1 = "CElementPrescription";
      }
    }

    if (!self::$_load_lite) {
      $this->loadDependValues();
    }
  }

  /**
   * @inheritDoc
   */
  function store() {
    if ($msg = CCompteRendu::checkOwner($this)) {
      return $msg;
    }

    return parent::store();
  }

  /**
   * Charge l'utilisateur associé à l'aide
   * 
   * @param boolean $cached Charge l'utilisateur depuis le cache
   * 
   * @return CMediusers
   */
  function loadRefUser($cached = true){
    return $this->_ref_user = $this->loadFwdRef("user_id", $cached);
  }

  /**
   * Charge la fonction associée à l'aide
   * 
   * @param boolean $cached Charge la fonction depuis le cache
   * 
   * @return CFunctions
   */
  function loadRefFunction($cached = true){
    return $this->_ref_function = $this->loadFwdRef("function_id", $cached);
  }

  /**
   * Charge l'établissement associé à l'aide
   * 
   * @param boolean $cached Charge l'établissement depuis le cache
   * 
   * @return CGroups
   */
  function loadRefGroup($cached = true){
    return $this->_ref_group = $this->loadFwdRef("group_id", $cached);
  }

  /**
   * Charge le propriétaire de l'aide
   * 
   * @return CMediusers|CFunctions|CGroups|null
   */
  function loadRefOwner() {
    if ($this->_ref_owner) {
      return $this->_ref_owner;
    }
    if ($this->user_id) {
      return $this->_ref_owner = $this->loadRefUser();
    }
    if ($this->function_id) {
      return $this->_ref_owner = $this->loadRefFunction();
    }
    if ($this->group_id) {
      return $this->_ref_owner = $this->loadRefGroup();
    }

    return null;
  }

  /**
   * Permission generic check
   * 
   * @param int $permType Type of permission : PERM_READ|PERM_EDIT|PERM_DENY
   * 
   * @return boolean
   */
  function getPerm($permType) {
    return $this->loadRefOwner()->getPerm($permType);
  }

  /**
   * Traduit les depend fields
   * 
   * @param CMbObject $object L'objet sur lequel sont appliquées les valeurs de dépendances 
   * 
   * @return void
   */
  function getDependValues($object) {
    $locale = "$object->_class.$this->_depend_field_1.$this->depend_value_1";
    $this->_vw_depend_field_1 = (CAppUI::isTranslated($locale) ? CAppUI::tr($locale) : $this->depend_value_1);

    $locale = "$object->_class.$this->_depend_field_2.$this->depend_value_2";
    $this->_vw_depend_field_2 = (CAppUI::isTranslated($locale) ? CAppUI::tr($locale) : $this->depend_value_2);
  }

  /**
   * Charge les objets référencés par l'aide
   * 
   * @return void
   */
  function loadDependObjects() {
    $this->_is_ref_dp_1 = false;
    $this->_is_ref_dp_2 = false;

    $object = new $this->class;
    $field = $this->field;
    $helped = array();
    if (isset($object->_specs[$field]) && $object->_specs[$field]->helped && !is_bool($object->_specs[$field]->helped)) {
      if (!is_array($object->_specs[$field]->helped)) {
        $helped = array($object->_specs[$field]->helped);
      }
      else {
        $helped = $object->_specs[$field]->helped;
      }
    }
    foreach ($helped as $i => $depend_field) {
      $spec = $object->_specs[$depend_field];
      if ($spec instanceof CRefSpec) {
        $key = "_is_ref_dp_".($i+1);
        $this->$key = true;
        $key = "depend_value_".($i+1);
        if (is_numeric($this->$key)) {
          $key_class = "_class_dp_".($i+1);
          $object_helped = new $this->$key_class;
          $object_helped = $object_helped->getCached($this->$key);
          $key_field = "_vw_depend_field_".($i+1);
          $this->$key_field = $object_helped->_view;
          $this->{"_ref_object_dp_".($i+1)} = $object_helped;
        }
      }
    }
  }

  function loadDependValues() {
    // Depend fields
    if ($this->class) {
      $object = new $this->class;
      $helped = isset($object->_specs[$this->field]) ? $object->_specs[$this->field]->helped : array();
      $this->_depend_field_1 = isset($helped[0]) ? $helped[0] : null;
      $this->_depend_field_2 = isset($helped[1]) ? $helped[1] : null;

      $this->getDependValues($object);
    }
    $this->loadDependObjects();
  }

  static function massLoadDependObjects($aides = array()) {
    if (!count($aides)) {
      return;
    }

    $first_aide = reset($aides);

    $object = new $first_aide->class;
    $field = $first_aide->field;

    $helped = array();

    if ($object->_specs[$field]->helped && !is_bool($object->_specs[$field]->helped)) {
      if (!is_array($object->_specs[$field]->helped)) {
        $helped = array($object->_specs[$field]->helped);
      }
      else {
        $helped = $object->_specs[$field]->helped;
      }
    }
    foreach ($helped as $i => $depend_field) {
      $spec = $object->_specs[$depend_field];
      if ($spec instanceof CRefSpec) {
        $key_class = "_class_dp_".($i+1);
        $object_helped = new $first_aide->$key_class;
        $key = "depend_value_".($i+1);
        $ids = CMbArray::pluck($aides, $key);

        $object_helped->loadList(array($object_helped->_spec->key => CSQLDataSource::prepareIn($ids)));
      }
    }
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
   * Load codes cim10 from concept map
   *
   * @throws \Exception
   *
   * @return array
   */
  function loadRefsCodesCIM10ConceptMap() {
    $idex_cim_10 = new CIdSante400();
    $where = array();
    $where["object_class"] = " = '$this->_class' ";
    $where["object_id"]    = " = '$this->_id' ";
    $where["tag"]          = " = '".CFHIRResourceConceptMap::TAG_CODE_CIM10."' ";
    $idexes_cim10 = $idex_cim_10->loadList($where);

    $codes_cim10 = array();
    foreach ($idexes_cim10 as $_idex) {
      $datas_code_cim10 = explode("|", $_idex->id400);
      $codes_cim10[] = array(
        "code"        => CMbArray::get($datas_code_cim10, 0),
        "display"     => CMbArray::get($datas_code_cim10, 1),
        "equivalence" => CMbArray::get($datas_code_cim10, 2),
      );
    }

    return $this->_ref_codes_cim10_concept_map = $codes_cim10;
  }

  /**
   * Load codes snomed from concept map
   *
   * @throws \Exception
   *
   * @return array
   */
  function loadRefsCodesSnomedConceptMap() {
    $idex_snomed = new CIdSante400();
    $where = array();
    $where["object_class"] = " = '$this->_class' ";
    $where["object_id"]    = " = '$this->_id' ";
    $where["tag"]          = " = '".CFHIRResourceConceptMap::TAG_CODE_SNOMED."' ";
    $idexes_snomed = $idex_snomed->loadList($where);

    $codes_snomed = array();
    foreach ($idexes_snomed as $_idex) {
      $datas_code_snomed = explode("|", $_idex->id400);
      $codes_snomed[] = array(
        "code"        => CMbArray::get($datas_code_snomed, 0),
        "display"     => CMbArray::get($datas_code_snomed, 1),
        "equivalence" => CMbArray::get($datas_code_snomed, 2),
      );
    }

    return $this->_ref_codes_snomed_concept_map = $codes_snomed;
  }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourceUser(): ?Item
    {
        $user = $this->loadRefUser();
        if (!$user || !$user->_id) {
            return null;
        }

        return new Item($user);
    }

    public function getResourceFunction(): ?Item
    {
        $function = $this->loadRefFunction();
        if (!$function || !$function->_id) {
            return null;
        }

        return new Item($function);
    }


    public function getResourceGroup(): ?Item
    {
        $group = $this->loadRefGroup();
        if (!$group || !$group->_id) {
            return null;
        }

        return new Item($group);
    }


    /**
   * Détecte si une aide à la saisie est d'instance
   *
   * @return bool
   */
  public function isForInstance() {
    return $this->_is_for_instance = (!$this->user_id && !$this->function_id && !$this->group_id);
  }

  public static function getSanitizedClassName($class): string
  {
      if ($class === 'CCerfa') {
          return 'Cerfa';
      }

      return $class;
  }
}
