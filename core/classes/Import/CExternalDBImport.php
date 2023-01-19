<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Import;

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\COracleDataSource;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CBirthDateSpec;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Core\FieldSpecs\CNumcharSpec;
use Ox\Core\FieldSpecs\CNumSpec;
use Ox\Core\FieldSpecs\CPhoneSpec;
use Ox\Core\FieldSpecs\CTimeSpec;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Utility class, used for external software data import
 */
class CExternalDBImport {
  /** @var CPDOMySQLDataSource|COracleDataSource|CSQLDataSource */
  protected $_ds;

  /** @var string Mediboard class name */
  protected $_class;

  /** @var string External table name */
  protected $_table;

  /** @var string Primary key name */
  protected $_key;

  /** @var bool Tells if the key is numeric */
  protected $_key_is_numeric = false;

  /** @var string SQL restriction */
  protected $_sql_restriction;

  /** @var array List of fields to select */
  protected $_select = array();

  /** @var array Field mapping between External DB => MB field */
  protected $_map = array();

  /** @var string Order by key name */
  protected $_order_by;

  /** @var string Group by key name */
  protected $_group_by;

  /** @var string Patient field name, used to import only the patient */
  protected $_patient_field;

  /** @var string Patient ID to import, will only import patient related data */
  protected $_patient_id;

  /** @var string User class, CMediusers, or CUser */
  protected $_user_class = "CMediusers";

  public $_correct_file;

  /** @var CMbObject */
  public $_mb_object;

  /** @var int[] Statistics about each table's total count */
  public $_stats_total;

  /** @var int[] Statistics about each table's imported count */
  public $_stats_imported;

  public $_default_limit = false;

  protected static $_module;

  /** @var int Stored object count */
  static $_count_stored = 0;

  /** @var array Base user information, for the mapping page */
  static $_base_user = array(
    "count"     => null,
    "ID"        => null,
    "firstname" => null,
    "lastname"  => null,
    "username"  => null,
    "type"      => null,
    "specialty" => null,
  );

  /** @var string Import tag configuration name */
  protected $_import_tag_conf;

  /** @var string Import function name */
  protected $_import_function_name_conf;

  /** @var string Import datasource name */
  protected $_import_dsn;

  /** @var string External patient class name to import */
  protected $_patient_class;

  /** @var integer CFunctions ID (in case of "single practitioner") */
  public $_function_id;

  /** @var string Prefix to apply to external ID (stored in id_sante400) */
  public $_id_prefix;

  /** @var bool Debug mode */
  static $_debug;

  /** @var array Import sequence (order of the different tables) */
  static $import_sequence = array();

  /**
   * Include a view from the factory
   *
   * @param string $file   File to include
   * @param string $subdir Subdirectory
   *
   * @return void
   */
  static function includeView($file, $subdir = null) {
    $_GET["import_class"] = get_called_class();

    $file = substr(basename($file), 0, -4);

    if ($subdir) {
      CAppUI::requireModuleFile("importTools", "$subdir/factory/$file");
    }
    else {
      CAppUI::requireModuleFile("importTools", "factory/$file");
    }
  }

  /**
   * Set debug mode
   *
   * @param bool $debug True or false
   *
   * @return void
   */
  static function setDebug($debug) {
    self::$_debug = !!$debug;
  }

  /**
   * CExternalDBImport constructor.
   *
   * @param bool $init_ds Should the DS be initialized or not
   */
  function __construct($init_ds = true) {
    if ($init_ds) {
      $this->getDS();
    }
  }

  /**
   * Get external software import tag
   *
   * @return string
   */
  function getImportTag() {
    return CAppUI::conf($this->_import_tag_conf);
  }

  /**
   * Get datasource
   *
   * @return CSQLDataSource
   */
  function getDS() {
    if ($this->_ds) {
      return $this->_ds;
    }

    return $this->_ds = CSQLDataSource::get($this->_import_dsn);
  }

  /**
   * Get import function
   *
   * @return CFunctions
   */
  function getImportFunction() {
    static $function;

    if ($function) {
      return $function;
    }

    $function_name  = CAppUI::conf($this->_import_function_name_conf);
    $function       = new CFunctions;
    $function->text = $function_name;
    $function->loadMatchingObjectEsc();

    if (!$function->_id) {
      $function->group_id        = CGroups::loadCurrent()->_id;
      $function->type            = "cabinet";
      $function->compta_partagee = 0;
      $function->color           = "#CCCCCC";

      if ($msg = $function->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
    }

    return $function;
  }

  /**
   * Get basic stats (total rows, total imported)
   *
   * @return bool
   */
  function getStats() {
    $ds     = $this->getDS();
    $ds_std = CSQLDataSource::get("std");

    if (!$ds->hasTable($this->_table)) {
      $this->_stats_total = 0;
      $this->_stats_imported = 0;
      CApp::log("Table missing : {$this->_table}");
      return false;
    }

    $this->_stats_total = $ds->loadResult("SELECT COUNT(*) FROM $this->_table " . $this->getWhereClause());

    if ($this->_stats_total == '0') {
        CApp::log("Table empty : {$this->_table}");
      return false;
    }

    $query = $ds_std->prepare(
      "SELECT COUNT(*) FROM id_sante400 WHERE object_class = ?1 AND tag = ?2",
      $this->_class,
      $this->getImportTag()
    );

    if ($this->_id_prefix) {
      $query .= ' AND id400 ' . $ds_std->prepareLike("{$this->_id_prefix}%");
    }

    $this->_stats_imported = $ds_std->loadResult($query);

    return true;
  }

  /**
   * @see parent::getSelectFields()
   */
  function getSelectFields() {
    return $this->_select;
  }

  /**
   * Get ORDER BY clause
   *
   * @return string|null
   */
  function getOrderBy() {
    $order_by = $this->_order_by;

    if (!$order_by) {
      return null;
    }

    return "DATE($order_by)";
  }

  /**
   * Get GROUP BY clause
   *
   * @return string|null
   */
  function getGroupBy() {
    return $this->_group_by;
  }

  /**
   * Set patient ID to import
   *
   * @param int $patient_id Patient ID to import
   *
   * @return void
   */
  function setPatientId($patient_id) {
    $this->_patient_id = $patient_id;
  }

  /**
   * Get patient field name in the table to import
   *
   * @return string
   */
  function getPatientField() {
    return $this->_patient_field;
  }

  /**
   * Get SQL restriction
   *
   * @return string
   */
  function getSqlRestriction() {
    if ($this->_patient_id && $this->_patient_field) {
      $sql = $this->getDS()->prepare("$this->_patient_field = ?", $this->_patient_id);

      if (!$this->_sql_restriction) {
        return $sql;
      }

      return "($this->_sql_restriction) AND $sql";
    }

    if (!$this->_sql_restriction) {
      return "";
    }

    return "($this->_sql_restriction)";
  }

  /**
   * Get SELECT query
   *
   * @param string $where WHERE statement
   *
   * @return string
   */
  function getSelectQuery($where = null) {
    if (count($this->getSelectFields()) == 0) {
      $select = "$this->_table.*";
    }
    else {
      $items  = array();
      $fields = $this->getSelectFields();
      foreach ($fields as $col) {
        $items[] = "$this->_table.$col";
      }

      $select = implode(", ", $items);
    }

    $query = "SELECT $select";
    $query .= " FROM $this->_table";

    if ($where_clause = $this->getWhereClause($where)) {
      $query .= $where_clause;
    }

    return $query;
  }

  /**
   * Get WHERE clause
   *
   * @param string $where WHERE statement
   *
   * @return string
   */
  function getWhereClause($where = null) {
    $where_clause = '';
    if ($this->getSqlRestriction() || $where) {
      $where_clause .= " WHERE " . $this->getSqlRestriction();

      if ($this->getSqlRestriction() && $where) {
        $where_clause .= " AND ";
      }

      $where_clause .= $where;
    }

    return $where_clause;
  }

  /**
   * Import an external table in function a class
   *
   * @param string $class        Class to import (must inherit from CExternalDBImport)
   * @param int    $start        Start offset
   * @param int    $count        Number of objects to import
   * @param bool   $reimport     Reimport objects already imported
   * @param int    $chir_id      Owner
   * @param string $order        Order of the import
   * @param string $date_min     Min date
   * @param string $date_max     Max date
   * @param int    $id           Id of the object to import
   * @param bool   $limit        Limit of the SELECT made in the table
   * @param int    $patient_id   Patient ID to import data of
   * @param bool   $correct_file Check for files date
   *
   * @return null
   */
  static function importByClass(
      $class,
      $start = null,
      $count = null,
      $reimport = false,
      $chir_id = null,
      $order = null,
      $date_min = null,
      $date_max = null,
      $id = null,
      $limit = false,
      $patient_id = null,
      $correct_file = null
  ) {
    if (!is_subclass_of($class, CExternalDBImport::class)) {
      CAppUI::stepAjax("Classe invalide", UI_MSG_ERROR);
    }

    /** @var self $object */
    $object = new $class;

    if ($patient_id) {
      $object->setPatientId($patient_id);
    }

    $ds = $object->getDS();

    $key_name  = $object->getKey();
    $key_multi = strpos($key_name, "|") !== false;

    if ($key_multi) {
      $key_multi = explode("|", $key_name);
    }

    $query = static::forgeQuery($object, $date_min, $date_max, $id, $order, $limit, $count);

    if (!$reimport) {
      $ids = array_flip($object->getDbIds($id));
    }

    $oracle = $ds instanceof COracleDataSource;

    $res = $ds->exec($query);

    $last_id = null;
    while ($count && ($hash = $ds->fetchAssoc($res))) {
      /** @var self $import_object */
      $import_object = new $class;
      $import_object->_correct_file = $correct_file;

      $hash = $import_object->fixHashEncoding($hash);

      if ($key_multi) {
        $_values = array();
        foreach ($key_multi as $_col) {
          $_values[] = trim($hash[$_col]);
        }
        $hash[$key_name] = implode("|", $_values);
      }

      if (!$reimport && isset($ids[$hash[$key_name]])) {
        continue;
      }

      if ($oracle) {
        /** @var COracleDataSource $ds */
        $hash = $ds->readLOB($hash);
      }

      // If reimport => force import
      $import_object->storeMbObject($hash, $reimport);

      if (!$import_object->_mb_object || isset($import_object->_mb_object->_failed)) {
        continue;
      }

      if ($count-- == 1) {
        $last_id = $import_object->getId($hash);
      }
    }

    $ds->freeResult($res);

    if ($last_id) {
      return $last_id;
    }

    return null;
  }

  /**
   * Applies treatment on data external hash
   *
   * @param array $hash The imported hash to convert
   *
   * @return mixed
   */
  function convertHash($hash) {
    return $hash;
  }

  /**
   * Forge a query
   *
   * @param CExternalDBImport $object   The object for which the query is created
   * @param string            $date_min The minimum date for the query
   * @param string            $date_max The maximum date for the query
   * @param string            $id       The ID to import
   * @param string            $order    The order part of the SQL statement
   * @param bool              $limit    Should the statement should use LIMIT
   * @param int               $count    The number of elements to return
   *
   * @return string
   */
  static function forgeQuery(CExternalDBImport $object, $date_min, $date_max, $id, $order, $limit, $count) {
    $query    = $object->getSelectQuery();
    $order_by = $object->getOrderBy();
    $group_by = $object->getGroupBy();

    $key_name  = $object->_key;
    $key_multi = strpos($key_name, "|") !== false;

    if ($key_multi) {
      $key_multi = explode("|", $key_name);
    }

    if ($date_min || $date_max || $id) {
      if ($object->getSqlRestriction()) {
        $query .= " AND ";
      }
      else {
        $query .= " WHERE ";
      }

      // Si date_max plus petite que date_min on inverse les variables
      if ($date_min && $date_max && CMbDT::date($date_min) > CMbDT::date($date_max)) {
        $tmp      = $date_min;
        $date_min = $date_max;
        $date_max = $tmp;
      }

      if ($date_min && $date_max) {
        $query .= " $order_by BETWEEN '$date_min' AND '$date_max'";
      }
      elseif ($date_min) {
        $query .= " $order_by > '$date_min'";
      }
      elseif ($date_max) {
        $query .= " $order_by < '$date_max'";
      }

      if ($id) {
        $_id = ($object->_id_prefix) ? str_replace("{$object->_id_prefix}_", '', $id) : $id;

        if ($date_min || $date_max) {
          $query .= " AND ";
        }

        if ($object->_key_is_numeric) {
          $query .= " $object->_key > '$_id'";
        }
        else {
          $query .=
            ($key_multi) ? " CONCAT(TRIM(" . implode("),'|',TRIM(", $key_multi) . ")) > '$_id'" : " $object->_key > TRIM('$_id')";
        }
      }
    }

    if ($group_by) {
      $query .= " GROUP BY {$group_by}";
    }

    if ($key_multi) {
      $query .= " ORDER BY CONCAT(TRIM(" . implode("),'|',TRIM(", $key_multi) . ")) ASC";
    }
    else {
      $query .= " ORDER BY $object->_key ASC";
    }

    $lim = 10000;
    if ($limit) {
      $lim = ($count > 0) ? $count * 10 : 1000;
    }

    $query .= " LIMIT $lim";

    return $query;
  }

  /**
   * @param array $hash The hash that have to be fixed
   *
   * @return mixed
   */
  function fixHashEncoding($hash) {
    return $hash;
  }

  /**
   * Load list from the data source
   *
   * @param string $query SQL query
   *
   * @return array
   */
  function loadList($query) {
    $ds   = self::getDS();
    $res  = $ds->exec($query);
    $list = array();

    while ($hash = $ds->fetchAssoc($res)) {
      $list[] = $this->fixHashEncoding($hash);
    }

    $ds->freeResult($res);

    return $list;
  }

  /**
   * Load the data from the first columns of a SQL query
   *
   * @param string $query SQL query
   *
   * @return array
   */
  function loadColumn($query) {
    return self::getDS()->loadColumn($query);
  }

  /**
   * Load the first data from the first result of a SQL query
   *
   * @param string $query SQL query
   *
   * @return mixed
   */
  function loadResult($query) {
    return self::getDS()->loadResult($query);
  }

  /**
   * Returns an MBObject by its class and its Import ID
   *
   * @param string $class The Mediboard class name
   * @param string $db_id The Import ID
   * @param string $tag   Tag to use
   *
   * @return CStoredObject The MB Object
   */
  function getMbObjectByClass($class, $db_id, $tag = null) {
    static $objects = array();

    if (isset($objects[$class][$db_id])) {
      return $objects[$class][$db_id];
    }

    $tag = ($tag) ?: $this->getImportTag();
    $idex   = CIdSante400::getMatch($class, $tag, $db_id);
    $target = $idex->loadTargetObject();

    if ($idex->_id) {
      $objects[$class][$db_id] = $target;
    }

    return $target;
  }

  /**
   * Get external DB IDs already imported
   *
   * @param int $min_id Min ID
   *
   * @return array
   */
  function getDbIds($min_id = null) {
    $ds = CSQLDataSource::get("std");

    $request = new CRequest();
    $request->addColumn("DISTINCT id400");
    $request->addTable("id_sante400");
    $tag = $this->getImportTag();

    $where = array(
      "object_class" => "= '$this->_class'",
      "tag"          => "= '$tag'",
    );

    if ($min_id) {
      $where["id400"] = $ds->prepare("> ?", $min_id);
    }

    $request->addWhere($where);

    return $ds->loadColumn($request->makeSelect());
  }

  /**
   * Returns the CMbObject corresponding to the given $db_id
   *
   * @param string $db_id The Import ID
   *
   * @return CMbObject|CStoredObject The CMbObject
   */
  function getMbObject($db_id) {
    return $this->getMbObjectByClass($this->_class, $db_id);
  }

  /**
   * Store the external ID of the given object
   *
   * @param CMbObject $object The MB to store the external ID of
   * @param string    $db_id  The Import ID to store on the MB Object
   *
   * @return string The external ID store error message
   */
  function storeIdExt(CMbObject $object, $db_id) {
    $id_ext = new CIdSante400;
    $id_ext->setObject($object);
    $id_ext->tag   = $this->getImportTag();
    $id_ext->id400 = $db_id;
    $id_ext->escapeValues();
    $id_ext->loadMatchingObject();

    $id_ext->unescapeValues();

    return $id_ext->store();
  }

  /**
   * @param string  $class Object class
   * @param integer $id    Object ID
   * @param string  $tag   Import tag to use
   *
   * @return bool|CStoredObject The object
   */
  static function getOrImportObject($class, $id, $tag = null) {
    /** @var self $import_object */
    $import_object = new $class;
    $object        = $import_object->getMbObjectByClass($import_object->_class, $id, ($tag) ?: $import_object->getImportTag());

    if (!$object->_id) {
      $import_object->importObject($id);

      if (!$import_object->_mb_object || !$import_object->_mb_object->_id) {
        CAppUI::setMsg(CAppUI::tr($import_object->_class) . " non retrouvé et non importé : " . $id, UI_MSG_WARNING);

        return false;
      }

      $object = $import_object->_mb_object;
    }

    return $object;
  }

  /**
   * Get user class, may be CUser or CMediusers
   *
   * @return string
   */
  function getUserClass() {
    return $this->_user_class;
  }

  /**
   * @param string $patient_id   Import patient ID
   * @param string $prat         Import praticien ID
   * @param string $date         Date
   * @param string $idex         External ID
   * @param string $annule       $sejour->annule
   * @param int    $group_id     Group id to use
   * @param bool   $current_user Use current user if prat is not found
   *
   * @return bool|CSejour|CStoredObject Finds a sejour from a patient, praticien and date
   */
  function findSejour($patient_id, $prat, $date, $idex = null, $annule = '0', $group_id = null, $current_user = false) {
    if ($idex) {
      $object = $this->getMbObjectByClass("CSejour", $idex);

      if ($object->_id) {
        return $object;
      }
    }

    // Trouver ou importer le patient
    $patient = $this->getOrImportObject($this->_patient_class, $patient_id);
    if (!$patient || !$patient->_id) {
      CAppUI::setMsg("Patient non retrouvé et non importé : $patient_id", UI_MSG_WARNING);

      return false;
    }

    // Trouver le praticien du sejour
    $user = null;
    if ($prat) {
      $user = $this->getMbObjectByClass($this->_user_class, $prat);
      if (!$user->_id) {
        if ($current_user) {
          $user = forward_static_call(array($this->_user_class, "get"));
        }
        else {
          CAppUI::setMsg("Praticien du séjour non retrouvé : $prat", UI_MSG_WARNING);

          return false;
        }
      }
    }

    // Recherche d'un séjour dont le debut peut
    // commencer 1 jour apres la date ou finir 2 jours avant
    $date = CMbDT::date($date);

    $sejour = new CSejour;
    $where  = array(
      "patient_id"   => "= '$patient->_id'",
      "annule"       => "= '$annule'",
      "DATE_SUB(`sejour`.`entree`, INTERVAL 1 DAY) < '$date'",
      "DATE_ADD(`sejour`.`sortie`, INTERVAL 2 DAY) > '$date'",
    );

    if ($user) {
      $where['praticien_id'] = "= '$user->_id'";
    }

    if ($group_id) {
      $where['group_id'] = "= '$group_id'";
    }

    $sejour->loadObject($where);

    if ($sejour->_id && $idex) {
      $this->storeIdExt($sejour, $idex);
    }

    if (!$sejour->_id) {
      CAppUI::setMsg("Séjour non trouvé : $patient_id / $prat / $date", UI_MSG_WARNING);

      return false;
    }

    return $sejour;
  }

  /**
   * Find a consultation
   *
   * @param integer|CPatient         $patient External patient ID or CPatient object
   * @param integer|CMediusers|CUser $prat    External practitioner ID or CMediusers object
   * @param string                   $date    Consultation date
   * @param bool|true                $store   Do we need to store the found consultation?
   * @param string|null                $time    Consultation time
   * @param string                   $freq    Freq of the consult if stored
   *
   * @return bool|CConsultation|null|string
   */
  function findConsult($patient, $prat, $date, $store = true, $time = null, $freq = "00:30:00") {
    if (!$patient instanceof CPatient) {
      // Trouver ou importer le patient
      $patient = $this->getOrImportObject($this->_patient_class, $patient);

      if (!$patient || !$patient->_id) {
        CAppUI::setMsg("Patient non retrouvé et non importé : $patient", UI_MSG_WARNING);

        return false;
      }
    }

    // Trouver le praticien de la consult
    if ($prat instanceof $this->_user_class) {
      $mediuser = $prat;
    }
    else {
      $mediuser = $this->getMbObjectByClass($this->_user_class, $prat);
    }

    if (!$mediuser->_id) {
      CAppUI::setMsg("Praticien de la consult non retrouvé : $prat", UI_MSG_WARNING);

      return false;
    }

    // Recherche d'une consult qui se passe entre 2 jours avant ou 1 jour apres
    $date_min = CMbDT::date("-2 DAYS", $date);
    $date_max = CMbDT::date("+1 DAYS", $date);

    $consult = new CConsultation();

    $ljoin = array(
      "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",
    );

    $where = array(
      "consultation.patient_id" => "= '$patient->_id'",
      "consultation.annule"     => "= '0'",
      "plageconsult.chir_id"    => "= '$mediuser->_id'",
      "plageconsult.date"       => "BETWEEN '$date_min' AND '$date_max'",
    );

    $consult->loadObject($where, null, null, $ljoin);
    if (!$consult->_id) {
      $consult = $this->makeConsult($patient->_id, $mediuser->_id, $date, $store, $time, $freq);
    }

    return $consult;
  }

  /**
   * Find a pause
   *
   * @param integer|CMediusers|CUser $prat  External practitioner ID or CMediusers object
   * @param string                   $date  Consultation date
   * @param string                   $motif Consultation reason
   * @param bool|true                $store Do we need to store the found consultation?
   * @param string|null                $time  Consultation time
   * @param string                   $freq  Freq of the consult if stored
   *
   * @return bool|CConsultation|null|string
   */
  function findPauseConsult($prat, $date, $motif, $store = true, $time = null, $freq = "00:30:00") {
    if (!$motif) {
      CAppUI::setMsg("Une pause doit avoir un motif", UI_MSG_WARNING);

      return false;
    }

    // Trouver le praticien de la consult
    if ($prat instanceof $this->_user_class) {
      $mediuser = $prat;
    }
    else {
      $mediuser = $this->getMbObjectByClass($this->_user_class, $prat);
    }

    if (!$mediuser->_id) {
      CAppUI::setMsg("Praticien de la pause non retrouvé : $prat", UI_MSG_WARNING);

      return false;
    }

    $consult = new CConsultation();
    $ds      = $consult->getDS();

    $ljoin = array(
      "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",
    );

    $where = array(
      "consultation.patient_id" => "IS NULL",
      "consultation.motif"      => $ds->prepare("=?", $motif),

      "plageconsult.chir_id" => $ds->prepare("=?", $mediuser->_id),
      "plageconsult.date"    => $ds->prepare("=?", $date),
    );

    $consult->loadObject($where, null, null, $ljoin);
    if (!$consult->_id) {
      $consult = $this->makeConsult(null, $mediuser->_id, $date, $store, $time, $freq);
    }

    return $consult;
  }

  /**
   * Make a consult
   *
   * @param integer   $patient_id Patient ID
   * @param integer   $chir_id    Chir ID
   * @param string    $date       Consultation date
   * @param bool|true $store      Do we need to store the found consultation?
   * @param string|null $time       Consultation time
   * @param string    $freq       Freq of the consult if stored
   *
   * @return bool|CConsultation|null|string
   */
  static function makeConsult($patient_id, $chir_id, $date, $store = true, $time = null, $freq = "00:30:00") {
    $consult = new CConsultation;
    $date    = CMbDT::date($date);

    $plage = new CPlageconsult();
    $where = array(
      "plageconsult.chir_id" => "= '$chir_id'",
      "plageconsult.date"    => "= '$date'",
    );

    $plage->loadObject($where);

    if (!$plage->_id) {
      $plage->date    = $date;
      $plage->chir_id = $chir_id;
      $plage->debut   = "09:00:00";
      $plage->fin     = "19:00:00";
      $plage->freq    = $freq;
      $plage->libelle = "Importation";

      if ($msg = $plage->store()) {
        CAppUI::setMsg($msg);

        return false;
      }
    }

    $consult->patient_id      = $patient_id;
    $consult->plageconsult_id = $plage->_id;
    $consult->heure           = ($time) ? $time : "09:00:00";
    $consult->chrono          = ($date < CMbDT::date() ? CConsultation::TERMINE : CConsultation::PLANIFIE);

    if ($store) {
      if ($msg = $consult->store()) {
        CAppUI::setMsg($msg);

        return false;
      }

      if (!$consult->_id) {
        CAppUI::setMsg("Consultation non trouvée et non importée : $patient_id / $chir_id / $date", UI_MSG_WARNING);

        return false;
      }

      CAppUI::setMsg("{$consult->_class}-msg-create");
    }

    return $consult;
  }

  /**
   * Convert an external DB value to MB value
   *
   * @param array     $hash   The associative array containing all the data
   * @param string    $from   External DB field name
   * @param CMbObject $object The MB Object to get its specs
   *
   * @return string The value
   */
  function convertValue($hash, $from, $object) {
    $to  = $this->_map[$from];
    $src = $this->convertEncoding($hash[$from]);

    if (is_array($to)) {
      return CValue::read($to[1], $src, CValue::read($to, 2));
    }
    else {
      $v    = $src;
      $spec = $object->_specs[$to];

      switch (true) {
        case $spec instanceof CDateSpec:
        case $spec instanceof CBirthDateSpec:
          $srcs = explode(" ", $v);
          return reset($srcs);

        case $spec instanceof CTimeSpec:
          $srcs = explode(" ", $v);
          return end($srcs);

        case $spec instanceof CNumSpec:
        case $spec instanceof CNumcharSpec:
        case $spec instanceof CPhoneSpec:
          return preg_replace("/[^0-9]/", "", $v);
        default:
          // Do nothing
      }

      return $v;
    }
  }

  /**
   * Convert a string between encodings
   *
   * @param string $string String to convert
   *
   * @return string Converted string
   */
  function convertEncoding($string) {
    return $string;
  }

  /**
   * Bind a hash to $object
   *
   * @param array     $hash   The hash to bind to the CMbObject
   * @param CMbObject $object The CMbObject
   *
   * @return void
   */
  function bindObject($hash, CMbObject $object) {
    foreach ($this->_map as $from => $to) {
      if (is_array($to)) {
        $to = reset($to);
      }

      $value         = $this->convertValue($hash, $from, $object);
      $object->{$to} = $value;
    }
  }

  /**
   * Get a hash from the primary key
   *
   * @param string $id The primary key value
   *
   * @return array The hash
   */
  protected function getHash($id) {
    $id = $this->getDS()->escape($id);

    $sep          = "|";
    $key          = $this->_key;
    $key_multi    = strpos($key, $sep) !== false;
    $values_multi = "";

    if (!$key_multi) {
      $where = "$key = '$id'";
    }
    else {
      $cols         = array_combine(explode($sep, $key), explode($sep, $id));
      $values_multi = implode("|", $cols);
      $where        = array();

      foreach ($cols as $_col => $_value) {
        $where[] = "$_col = '$_value'";
      }
      $where = implode(" AND ", $where);
    }

    $query = $this->getSelectQuery($where);
    $hash  = $this->_ds->loadHash($query);

    if ($hash && $key_multi) {
      $hash[$key] = $values_multi;
    }

    if ($hash == false) {
      return $hash;
    }

    return $hash;
  }

  /**
   * Bind a DB entry to a CMbObject from the primary key
   *
   * @param string $id The Import ID
   *
   * @return CMbObject The CMbObject
   */
  function mapIdToMbObject($id) {
    return $this->mapHashToMbObject($this->getHash($id));
  }

  /**
   * Bind a hash to a new CMbObject
   *
   * @param array     $hash   The associative array
   * @param CMbObject $object The object or the class name
   *
   * @return CMbObject The CMbObject
   */
  function mapHashToMbObject($hash, $object = null) {
    if ($object) {
      $this->_mb_object = $object;
    }
    else {
      // Do not replace new $this->_class with new static !!!!
      $this->_mb_object = new $this->_class;
    }

    $this->bindObject($hash, $this->_mb_object);

    return $this->_mb_object;
  }

  /**
   * Called before storing object
   *
   * @param CMbObject $object Treatment before store
   *
   * @return void
   */
  function beforeStore(CMbObject $object) {
    $object->repair();
  }

  /**
   * Stores a CMbObject from a hash
   *
   * @param array   $hash  The associative array
   * @param boolean $force Force the object re-importation
   *
   * @return string|null The store message
   */
  function storeMbObject($hash, $force = false) {
    $db_id  = $this->getId($hash);
    $object = $this->getMbObject($db_id);

    // If object was already imported
    if (!$force && $object->_id) {
      return null;
    }

    $this->mapHashToMbObject($hash, $object);

    if (self::$_debug) {
      CApp::log('HASH', $hash);
      CApp::log('MB OBJECT', ($this->_mb_object instanceof CMbObject) ? $this->_mb_object->getPlainFields() : $this->_mb_object);
    }

    if (isset($this->_mb_object->_failed)) {
      return null;
    }

    $this->beforeStore($this->_mb_object);
    $found = ($this->_mb_object && $this->_mb_object->_id);

    if ($msg = $this->_mb_object->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);

      return $msg;
    }
    else {
      $msg = ($found) ? "{$this->_mb_object->_class}-msg-Object found" : "{$this->_mb_object->_class}-msg-create";
      CAppUI::setMsg($msg, UI_MSG_OK);
    }
    $this->afterStore($this->_mb_object);
    static::$_count_stored++;

    return self::storeIdExt($this->_mb_object, $this->getId($hash));
  }

  /**
   * Action à effectuer après le store d'un objet
   *
   * @param CMbObject $object objet stocké
   *
   * @return void
   */
  function afterStore(CMbObject $object) {
  }

  /**
   * Import object from its ID
   *
   * @param int  $db_id        External DB id
   * @param bool $force        Force reimport
   * @param bool $correct_file Should files date be corrected
   *
   * @return CStoredObject
   */
  function importObject($db_id, $force = false, $correct_file = false) {
    $hash = $this->getHash($db_id);

    if (empty($hash)) {
      CAppUI::setMsg("ID <strong>$db_id</strong> inconnu", UI_MSG_ALERT);

      return null;
    }

    $this->_correct_file = $correct_file;
    $this->storeMbObject($hash, $force);

    return $this->_mb_object;
  }

  /**
   * Get the ID from the hash
   *
   * @param array $hash Hash to get the ID from
   *
   * @return string
   */
  function getId($hash) {
    $id = ($this->_id_prefix) ? "{$this->_id_prefix}_{$hash[$this->_key]}" : $hash[$this->_key];

    return $id;
  }

  /**
   * Get imported object MAX ID
   *
   * @return null|string
   */
  function getImportedMaxID() {
    $ds = CSQLDataSource::get('std');

    $request = new CRequest();
    $request->addTable('id_sante400');
    $request->addSelect('id400');

    $where = array(
      'tag'          => $ds->prepare('= ?', $this->getImportTag()),
      'object_class' => $ds->prepare('= ?', $this->_class),
    );

    if ($this->_id_prefix) {
      $where['id400'] = $ds->prepareLike("{$this->_id_prefix}%");
    }

    $request->addWhere($where);

    $request->addOrder('id400 DESC');
    $request->setLimit('0,1');

    return $db_id = $ds->loadResult($request->makeSelect());
  }

  /**
   * Count a list from a SQL query
   *
   * @param string $where Where clause as string
   *
   * @return null|string
   */
  function countList($where = null) {
    $ds = $this->getDS();

    $query = "SELECT COUNT(*) AS `total`";
    $query .= " FROM {$this->_table}";

    if ($where_clause = $this->getWhereClause($where)) {
      $query .= $where_clause;
    }

    return $ds->loadResult($query);
  }

  /**
   * Extract the phone number from a string (removing any non numeric char)
   *
   * @param string $string String to get the phone number from
   *
   * @return string
   */
  protected function extractPhone($string) {
    return substr(preg_replace("/[^0-9]/", "", $string), 0, 10);
  }

  /**
   * Check the presence of the "to_skip" column, and if not present, is add it
   *
   * @return bool|resource
   */
  protected function checkToSkipPresence() {
    $ds = $this->getDS();

    $query  = "SHOW COLUMNS FROM `{$this->_table}` LIKE 'to_skip';";
    $exists = $ds->loadResult($query);

    if (!$exists) {
      $query = "ALTER TABLE `{$this->_table}` ADD COLUMN `to_skip` ENUM('0', '1') NOT NULL DEFAULT '0';";

      return $ds->exec($query);
    }

    return true;
  }

  /**
   * Analyze a table and wheck the "to_skip" column
   *
   * @return void
   */
  function analyze() {
    $this->getDS();
    $this->checkToSkipPresence();
  }

  /**
   * Reset the "to_skip" flag
   *
   * @return bool|resource
   */
  function reset() {
    $this->checkToSkipPresence();
    $ds = $this->getDS();

    return $ds->exec("UPDATE $this->_table SET `to_skip` = '0';");
  }

  /**
   * Cleans up a string, removing repreating "-" and "'"
   *
   * @param string $str String to cleanup
   *
   * @return string
   */
  function cleanString($str) {
    $str = preg_replace("/\s*-+\s*/", '-', $str);
    $str = preg_replace("/\s*'+\s*/", "'", $str);

    return $str;
  }

  /**
   * Gets user from a given SPEC code
   *
   * @param string $hash_id User external ID
   *
   * @return CMediusers|CMbObject
   */
  function getUser($hash_id) {
    $idex = CIdSante400::getMatch(
      'CMediusers',
      $this->getImportTag(),
      ($this->_id_prefix) ? "{$this->_id_prefix}_{$hash_id}" : $hash_id
    );

    return ($idex) ? $idex->loadTargetObject() : null;
  }

  /**
   * Loads a single row from a given table
   *
   * @param string $id    ID
   * @param string $table Table where to search in
   * @param string $key   Search field
   * @param string $where Additional parameters
   *
   * @return array|null
   */
  function loadUniqueRow($id, $table, $key, $where = null) {
    if (!$id || !$table || !$key) {
      return null;
    }

    $ds    = $this->getDS();
    $query = $ds->prepare("SELECT * FROM `{$table}` WHERE `{$key}` = ?", $id);

    $query .= ($where) ?: null;

    return $ds->loadHash($query);
  }

  /**
   * Checks XML entities
   *
   * @param string $string Data
   *
   * @return mixed
   */
  static function replaceXMLEntities($string) {
    return str_replace(
      array('&', '"', "'", '<', '>'),
      array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'),
      $string
    );
  }

  /**
   * Gets the users" list
   *
   * Each user has :
   *  - count
   *  - ID
   *  - firstname
   *  - lastname
   *  - username
   *  - type
   *  - specialty
   *  - the associated CMediusers object (if any)
   *
   * @param array  $select     array(ID => '', PRENOM => '', NOM => '', USERNAME => '', TYPE => '', SPE => '')
   * @param string $user_class Nom de la classe d'import des utilisateurs
   *
   * @return array
   */
  function getUsersList($select = null, $user_class = null) {
    $ds = $this->getDS();

    if (!class_exists($user_class)) {
      return array();
    }

    /** @var CExternalDBImport $user */
    $user  = new $user_class();
    $query = new CRequest();
    $query->addSelect($select);
    $query->addTable($user->_table);
    $query->addOrder('ID');
    $list_all = $ds->loadList($query->makeSelect());

    $users = array();
    foreach ($list_all as $_hash) {
      if (!$_hash['ID']) {
        continue;
      }

      $_user               = self::$_base_user;
      $_user['ID']         = $_hash['ID'];
      $_user['firstname']  = (isset($_hash['PRENOM'])) ? $_hash['PRENOM'] : '';
      $_user['lastname']   = (isset($_hash['NOM'])) ? $_hash['NOM'] : '';
      $_user['username']   = (isset($_hash['USERNAME'])) ? $_hash['USERNAME'] : '';
      $_user['type']       = (isset($_hash['TYPE'])) ? $_hash['TYPE'] : '';
      $_user['speciality'] = (isset($_hash['SPE'])) ? $_hash['SPE'] : '';

      $idex = CIdSante400::getMatch($this->_user_class, $user->getImportTag(), $_user['ID']);
      $_user['object'] = $idex->loadTargetObject();
      $users[] = $_user;
    }

    return $users;
  }

  /**
   * Return the key
   *
   * @return string
   */
  function getKey() {
    return $this->_key;
  }

  /**
   * Check if a file_date is out of range or not
   *
   * @param string $file_date    The file date
   * @param string $key          The key to log errors
   * @param bool   $correct_file Should files be corrected or not
   * @param string $module       Module name
   *
   * @return bool
   */
  static function isBadFileDate($file_date, $key = null, $correct_file = false, $module = null) {
    if (!(self::$_module || $module) || !$correct_file || !$file_date) {
      return false;
    }

    $mod = ($module) ?: self::$_module;
    if ($file_date > self::getMinMaxDate('max', $mod) || $file_date < self::getMinMaxDate('min', $mod)) {
      CApp::log($key . ' : Conflit de date : ' . $file_date, null, LoggerLevels::LEVEL_DEBUG);
      return true;
    }

    return false;
  }

  /**
   * Get the max or min date for files
   *
   * @param string $min_max min|max
   * @param string $module  Module name
   *
   * @return string
   */
  static function getMinMaxDate($min_max, $module) {
      // Todo: Take care of LSB here
    $cache = new Cache('CExternalDBImport.getMinMaxDate', [$min_max, $module], Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }
    $conf = ($min_max == 'min') ? CAppUI::conf($module. " file_date_min") : CAppUI::conf($module . " file_date_max");

    return $cache->put($conf);
  }

  /**
   * Getter for $_table attribute
   *
   * @return string
   */
  function getTable() {
    return $this->_table;
  }
}
