<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\COracleDataSource;
use Ox\Core\CRequest;

/**
 * Classe de journalisation de l'import via le cron
 */
class CImportCronLogs extends CMbObject {
  /** @var integer Primary key */
  public $import_cron_logs_id;
  /** @var string Import module name */
  public $import_mod_name;
  /** @var  string Main import class name for the module */
  public $import_class_name;
  /** @var string Log date */
  public $date_log;
  /** @var  string Type of log (error|warning|info) */
  public $type;
  /** @var  string Content of the log */
  public $text;

  /** @var  string */
  public $_date_log_min;

  /** @var  string */
  public $_date_log_max;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "import_cron_logs";
    $spec->key      = "import_cron_logs_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                      = parent::getProps();
    $props['import_mod_name']   = 'str notNull';
    $props['import_class_name'] = 'str notNull';
    $props['date_log']          = 'dateTime notNull';
    $props['text']              = 'str';
    $props['type']              = 'enum list|warning|info|error';

    $props['_date_log_max'] = 'str';
    $props['_date_log_min'] = 'str';

    return $props;
  }

  /**
   * Récupère les journaux du cron d'import pour le type donné.
   *
   * @param string      $type              Le type de log à récuéprer (info|warning|error)
   * @param string|null $import_mod_name   Le nom du module d'importation
   * @param string|null $import_class_name Le nom de la classe d'importation
   * @param string|null $date_log_min      La date minimum des logs
   * @param string|null $date_log_max      La date maximum des logs
   * @param string      $limit             La limite à utiliser
   *
   * @return array|false
   */
  function getLogsByType($type, $import_mod_name = null, $import_class_name = null, $date_log_min = null, $date_log_max = null,
      $limit = '0, 50'
  ) {
    $ds   = $this->getDS();
    $spec = $this->getSpec();

    $query = new CRequest();
    $query->addSelect('*');
    $query->addTable($spec->table);

    $where = array(
      'type' => $ds->prepare('= ?', $type)
    );
    if ($import_mod_name) {
      $where['import_mod_name'] = $ds->prepareLike("$import_mod_name%");
    }
    if ($import_class_name) {
      $where['import_class_name'] = $ds->prepareLike("$import_class_name%");
    }
    if ($date_log_min && $date_log_max) {
      $where['date_log'] = $ds->prepare('BETWEEN ?1 AND ?2', $date_log_min, $date_log_max);
    }
    elseif ($date_log_min) {
      $where['date_log'] = $ds->prepare('> ?', $date_log_min);
    }
    elseif ($date_log_max) {
      $where['date_log'] = $ds->prepare('< ?', $date_log_max);
    }

    $query->addWhere($where);

    $query_count = clone $query;

    $query->addOrder('date_log DESC');
    $query->setLimit($limit);

    try {
      $return = array(
        'data'  => $ds->loadHashAssoc($query->makeSelect()),
        'count' => $ds->loadResult($query_count->makeSelectCount())
      );
    }
    catch (Exception $e) {
      $return = array(
        "data"  => null,
        "count" => null,
      );
    }


    return $return;
  }

  /**
   * Parse les messages du cron d'import et les sauvegarde en BDD
   *
   * @param string $msg               Le message à parser
   * @param string $module_name       Le nom du module d'import
   * @param string $import_class_name Le nom de la classe d'import
   *
   * @return void
   */
  static function parseMsg($msg, $module_name, $import_class_name) {
    $regexp = "@<div class=\"(info|warning|error)\">@";
    $msg    = str_replace('</div>', '', $msg);

    $infos = preg_split($regexp, $msg, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    for ($i = 0; $i < count($infos) / 2; $i += 2) {
      self::createLogs($module_name, $import_class_name, $infos[$i], $infos[$i + 1]);
    }
  }

  /**
   * Sauvegarde les messages du cron d'import
   *
   * @param string $module_name       Le nom du module d'import
   * @param string $import_class_name Le nom de la classe d'import
   * @param string $type              Le type de log
   * @param string $text              Le contenu du log
   *
   * @return void
   */
  static function createLogs($module_name, $import_class_name, $type, $text) {
    $regexp_info_warn = "@ID <strong>.+<\/strong> inconnu@";

    $logs                    = new CImportCronLogs();
    $logs->import_mod_name   = $module_name;
    $logs->import_class_name = $import_class_name;
    $logs->date_log          = CMbDT::dateTime();
    $logs->type              = $type;
    if (preg_match($regexp_info_warn, $text)) {
      $logs->type = 'warning';
    }
    $logs->text = $text;

    if ($msg = $logs->store()) {
      CApp::log($msg);
    }
  }

  /**
   * Import an external table in function of a class
   *
   * @param string $module_name Import mod name
   * @param string $class       Class to import (must inherit from CExternalDBImport)
   * @param int    $count       Number of objects to import
   * @param bool   $reimport    Reimport objects already imported
   * @param string $order       Order of the import
   * @param string $date_min    Min date
   * @param string $date_max    Max date
   * @param int    $id          Id of the object to import
   * @param bool   $limit       Limit of the SELECT made in the table
   * @param int    $patient_id  Patient ID to import data of
   *
   * @return string
   */
  static function importByClass(
      $module_name, $class, $count = null, $reimport = false, $order = null, $date_min = null, $date_max = null, $id = null,
      $limit = false, $patient_id = null
  ) {
    if (!is_subclass_of($class, CExternalDBImport::class)) {
      CAppUI::stepAjax("Classe invalide", UI_MSG_ERROR);
      self::createLogs($module_name, $class, 'error', 'Classe invalide : ' . $class);
    }

    /** @var CExternalDBImport $object */
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

    $query = CExternalDBImport::forgeQuery($object, $date_min, $date_max, $id, $order, $limit, $count);

    if (!$reimport) {
      $ids = array_flip($object->getDbIds($id));
    }

    $oracle = $ds instanceof COracleDataSource;

    $res = $ds->exec($query);

    $last_id = null;
    while ($count && ($hash = $ds->fetchAssoc($res))) {
      /** @var CExternalDBImport $import_object */
      $import_object = new $class;

      $hash = $import_object->fixHashEncoding($hash);

      if ($key_multi) {
        $_values = array();
        foreach ($key_multi as $_col) {
          $_values[] = trim($hash[$_col]);
        }
        $hash[$key_name] = implode("|", $_values);
      }

      if (!$reimport && isset($ids[$hash[$key_name]])) {
        $last_id = $hash[$key_name];
        continue;
      }

      if ($oracle) {
        /** @var COracleDataSource $ds */
        $hash = $ds->readLOB($hash);
      }

      // If reimport => force import
      $import_object->storeMbObject($hash, $reimport);

      if (!$hash[$key_name]) {
        self::createLogs($module_name, $class, 'error', 'La requête n\'a renvoyé aucun résultat.');
      }
      if (!$import_object->_mb_object || isset($import_object->_mb_object->_failed)) {
        $last_id = $hash[$key_name];
        // Echec de l'import de l'objet
        continue;
      }

      $count--;
      $last_id = $import_object->getId($hash);
    }

    $ds->freeResult($res);

    return $last_id;
  }
}
