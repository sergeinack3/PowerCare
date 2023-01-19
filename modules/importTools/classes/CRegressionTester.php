<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;

/**
 * Compare two databases with their id_sante400
 */
class CRegressionTester {
  /** @var CSQLDataSource|null First DS */
  public $ds1;

  /** @var CSQLDataSource|null Second DS */
  public $ds2;

  /** @var string Import tag used for the import */
  public $import_tag;

  /** @var array Resulting array */
  public $result_regression;

  /** @var array The classes existing in the two databases */
  public $classes_to_test;

  /** @var int Number of objects of each class to test */
  public $nb_tests;

  /** @var array Result counts */
  public $counts;

  /** @var array The différents results possible for the comparaison */
  public $states = array('same', 'diff', 'miss');

  /**
   * CRegressionTester constructor.
   *
   * @param int $nb_tests Number of element of each class to test
   */
  function __construct($nb_tests = 1000) {
    $this->ds1 = CSQLDataSource::get('regression_first');
    $this->ds2 = CSQLDataSource::get('regression_second');

    $this->nb_tests = $nb_tests;

    $this->result_regression = array_fill_keys($this->states, array());
    $this->counts            = array_fill_keys($this->states, array());
  }

  /**
   * Set the import tag
   *
   * @param string $import_tag Import tag
   *
   * @return void
   */
  function setImportTag($import_tag) {
    $this->import_tag = $import_tag;
  }


  /**
   * Main function to get differences of two imports
   *
   * @return void
   */
  function getRegression() {
    if (!$this->ds1 || !$this->ds2) {
      return null;
    }

    // Récupération de l'ensemble des classes présentes parmis les id400 pour chaque DS
    $imported_classes1 = $this->getImportedClasses($this->ds1);
    $imported_classes2 = $this->getImportedClasses($this->ds2);

    // Calcul des classes non présentes parmis les id400 des DS
    $this->result_regression['missing_in_1'] = array_diff($imported_classes2, $imported_classes1);
    $this->result_regression['missing_in_2'] = array_diff($imported_classes1, $imported_classes2);

    $this->classes_to_test = array_intersect($imported_classes1, $imported_classes2);

    foreach ($this->classes_to_test as $_class_name) {
      // Préparation du tableau de résultat
      foreach ($this->states as $_state) {
        $this->initiateArrays($_state, $_class_name);
      }
    }
    $ids400        = $this->getListId400FromClasses();
    $objects_ids_1 = $this->massGetIdFromId400($ids400, $this->ds1);
    $objects_ids_2 = $this->massGetIdFromId400($ids400, $this->ds2);

    $objects = array();
    $this->massLoadFromDS($this->ds1, $objects_ids_1, $objects);
    $this->massLoadFromDS($this->ds2, $objects_ids_2, $objects);

    $this->getObjectsMissingAndCompare($objects);

  }

  /**
   * @param array $objects An array containing the objects to compare
   *
   * @return void
   */
  function getObjectsMissingAndCompare($objects) {
    foreach ($objects as $_class => $_db) {
      /** @var CStoredObject $object */
      $object = new $_class();
      $spec = $object->getSpec();

      foreach ($_db['DB1'] as $_id400 => $_obj) {

        if (!$_obj || !$_obj[$spec->key]) {
          $this->result_regression['miss'][$_class]['DB1'][$_id400] = null;
          $this->result_regression['miss'][$_class]['DB2'][$_id400] = $this->removeFieldsNotShow($_db['DB2'][$_id400], $_class);

          continue;
        }

        if (!array_key_exists($_id400, $_db['DB2']) || !$_db['DB2'][$_id400] || !$_db['DB2'][$_id400][$spec->key]) {
          $this->result_regression['miss'][$_class]['DB2'][$_id400] = null;
          $this->result_regression['miss'][$_class]['DB1'][$_id400] = $this->removeFieldsNotShow($_obj, $_class);

          continue;
        }

        $this->compareObjects($_obj, $_db['DB2'][$_id400], $_id400, $_class);
      }
    }
  }


  /**
   * Get the list of imported classes
   *
   * @param CSQLDataSource $ds The datasource to use
   *
   * @return array|false
   */
  function getImportedClasses($ds) {
    $query = new CRequest();
    $query->addSelect('DISTINCT object_class');
    $query->addTable('id_sante400');
    $where = array(
      'tag' => $ds->prepareLike("$this->import_tag%")
    );
    $query->addWhere($where);

    return $ds->loadColumn($query->makeSelect());
  }


  /**
   * Get X ($this->nb_tests) id400 from the id_sante400 table for each class.
   *
   * @return array
   */
  function getListId400FromClasses() {
    $ids400_1 = $this->getIds400ForClasses($this->ds1);
    $ids400_2 = $this->getIds400ForClasses($this->ds2);

    $ids400 = array();
    foreach ($ids400_1 as $_class => $_ids) {
      $ids400_merged = array_merge($_ids, $ids400_2[$_class]);
      $ids400_merged = array_flip($ids400_merged);
      $ids400_unique = array_flip($ids400_merged);

      $ids400[$_class] = array_slice($ids400_unique, 0, $this->nb_tests);
    }

    return $ids400;
  }

  /**
   * Get all the id400 from id_sante400 table for a class.
   * If $first_key is set, get the X ($this->nb_tests) id400 starting at $first_key
   *
   * @param CSQLDataSource $ds       The datasource to use
   * @param bool           $no_limit Should the query use a limit or not
   *
   * @return array
   */
  function getIds400ForClasses($ds, $no_limit = false) {
    $ids400 = array();
    $dsn = ($ds->dsn == 'regression_first') ? 'DB1' : 'DB2';
    foreach ($this->classes_to_test as $_class) {

      $max = $this->getNbIds400ForClass($ds, $_class);
      $this->counts[$_class][$dsn] = $max;
      if ($max <= $this->nb_tests) {
        $max = $this->nb_tests;
      }
      $rand = rand(0, $max - $this->nb_tests);

      $query = new CRequest();
      $query->addSelect('object_class, id400');
      $query->addTable('id_sante400');
      $where = array(
        'tag'          => $ds->prepareLike("$this->import_tag%"),
        'object_class' => $ds->prepare('= ?', $_class)
      );
      $query->addWhere($where);
      $limit_max = $rand + $this->nb_tests;
      if (!$no_limit) {
        $query->setLimit("$rand, $limit_max");
      }

      $res    = $ds->exec($query->makeSelect());
      if (!array_key_exists($_class, $ids400)) {
        $ids400[$_class] = array();
      }
      while ($row = $ds->fetchAssoc($res)) {
        $ids400[$_class][] = $row['id400'];
      }
    }


    return $ids400;
  }

  /**
   * @param CSQLDataSource $ds    DS to use for the request
   * @param string         $class Class name of the objects
   *
   * @return int
   */
  function getNbIds400ForClass($ds, $class) {
    $query = new CRequest();
    $query->addTable('id_sante400');
    $where = array(
      'tag'          => $ds->prepareLike("$this->import_tag%"),
      'object_class' => $ds->prepare('= ?', $class)
    );
    $query->addWhere($where);

    return $ds->loadResult($query->makeSelectCount());
  }

  /**
   * @param array          $ids400 An array containing the id400 to load for each class
   * @param CSQLDataSource $ds     The DS to use for requests
   *
   * @return array
   */
  function massGetIdFromId400($ids400, $ds) {
    $real_ids = array();
    foreach ($ids400 as $_class => $_ids) {
      $query = new CRequest();
      $query->addSelect('id400, object_id');
      $query->addTable('id_sante400');
      $where = array(
        'object_class' => $ds->prepare('= ?', $_class),
        'tag'          => $ds->prepareLike("$this->import_tag%"),
        'id400'        => $ds->prepareIn($_ids)
      );
      $query->addWhere($where);
      $result            = $ds->loadHashList($query->makeSelect());
      $real_ids[$_class] = $result;
    }

    return $real_ids;
  }

  /**
   * Load objects from their id and store them into an other array
   *
   * @param CSQLDataSource $ds          The DS to use for request
   * @param array          $objects_ids An array containing all the objects id with their id400
   * @param array          $objects     An array to store the objects loaded
   *
   * @return void
   */
  function massLoadFromDS($ds, $objects_ids, &$objects) {
    $db = ($ds->dsn == 'regression_first') ? 'DB1' : 'DB2';
    foreach ($objects_ids as $_class => $_values) {
      foreach ($this->states as $_state) {
        $this->initiateArrays($_state, $_class, $db);
      }
      /** @var CStoredObject $obj */
      $obj  = new $_class();
      $spec = $obj->getSpec();

      $query = new CRequest();
      $query->addSelect('*');
      $query->addTable($spec->table);
      $where = array(
        "$spec->key" => $ds->prepareIn($_values)
      );
      $query->addWhere($where);

      $hashs = $ds->loadHashAssoc($query->makeSelect());

      foreach ($hashs as $_hash) {
        $obj_id400 = array_search($_hash[$spec->key], $_values);
        $objects[$_class][$db][$obj_id400] = $_hash;
      }
    }
  }

  /**
   * Initiate the result array if it's not.
   *
   * @param string $cat        diff or same or miss
   * @param string $class_name Class name of the object
   * @param string $db         DB1 or DB2
   *
   * @return void
   */
  function initiateArrays($cat, $class_name, $db = null) {
    if (!$db) {
      if (!array_key_exists($class_name, $this->result_regression)) {
        $this->result_regression[$cat][$class_name] = array();
      }
    }
    else {
      if (!array_key_exists($db, $this->result_regression[$cat][$class_name])) {
        $this->result_regression[$cat][$class_name][$db] = array();
      }
    }
  }

  /**
   * Compare two objects and sort them depending on the matches
   *
   * @param CStoredObject $object1    First object to compare
   * @param CStoredObject $object2    Second object to compare
   * @param string        $id400      External id of the objects
   * @param string        $class_name Class name of the objects
   *
   * @return void
   */
  function compareObjects($object1, $object2, $id400, $class_name) {
    // Comparaison des objets
    /** @var CStoredObject $object */
    $object = new $class_name();
    $specs = $object->getSpecs();
    $spec  = $object->getSpec();

    $object_fields1 = $this->removeFieldsNotShow($object1, $class_name);
    $object_fields2 = $this->removeFieldsNotShow($object2, $class_name);

    // Permet de savoir si tous les champs comparés sont les mêmes ou non
    $all_same = true;

    foreach ($object_fields1 as $_field_name => $_field_value) {
      // On ne considère pas les champs de type ref et les champs 'show|0'
      if (!($specs[$_field_name] instanceof CRefSpec) && !($class_name == 'CFile' && $_field_name == 'file_real_filename')
          && $_field_name != $spec->key && $_field_value != $object_fields2[$_field_name]
      ) {
        $all_same = false;
      }
    }
    // Les deux objets sont identiques
    if ($all_same) {
      if (!array_key_exists($id400, $this->result_regression['same'][$class_name]['DB1'])) {
        $this->result_regression['same'][$class_name]['DB1'][$id400] = $object1[$spec->key];
      }
      if (!array_key_exists($id400, $this->result_regression['same'][$class_name]['DB2'])) {
        $this->result_regression['same'][$class_name]['DB2'][$id400] = $object2[$spec->key];
      }
    }
    // Les deux objets ont des différences
    else {
      if (!array_key_exists($id400, $this->result_regression['diff'][$class_name]['DB1'])) {
        $this->result_regression['diff'][$class_name]['DB1'][$id400] = $object_fields1;
      }
      if (!array_key_exists($id400, $this->result_regression['diff'][$class_name]['DB2'])) {
        $this->result_regression['diff'][$class_name]['DB2'][$id400] = $object_fields2;
      }
    }
  }

  /**
   * Remove fields with show|0 prop if it's not the primary key
   *
   * @param CStoredObject $object     The object
   * @param string        $class_name Name of the class
   *
   * @return array
   */
  function removeFieldsNotShow($object, $class_name) {
    /** @var CStoredObject $obj */
    $obj = new $class_name();
    $props          = $obj->getProps();
    $spec           = $obj->getSpec();
    $fields_to_show = array();

    foreach ($object as $_name => $_value) {
      if (array_key_exists($_name, $props) && (strpos($props[$_name], 'show|0') === false || $spec->key == $_name)) {
        $fields_to_show[$_name] = $_value;
      }
    }

    return $fields_to_show;
  }

  /**
   * Count all the objects of each category
   *
   * @return void
   */
  function countEverything() {
    foreach ($this->states as $_state) {
      $this->countCat($_state);
    }
  }

  /**
   * Count the objects for one category
   *
   * @param string $cat The category to count
   *
   * @return void
   */
  function countCat($cat) {
    $this->counts[$cat]['all'] = 0;
    foreach ($this->result_regression[$cat] as $_class => $_values) {
      // Initialisation du tableau s'il n'existe pas
      (!array_key_exists($_class, $this->counts[$cat])) ? $this->counts[$cat][$_class] = 0 : null;

      $this->counts[$cat][$_class] = (array_key_exists('DB1', $_values)) ? count($_values['DB1']) : 0;
      $this->counts[$cat]['all']   += (array_key_exists('DB1', $_values)) ? count($_values['DB1']) : 0;
    }
  }

  /**
   * @param string $class_name Class of the object to compare
   *
   * @return array
   */
  function compareObjectsFromClass($class_name) {
    $this->classes_to_test = array($class_name);
    $ids400_missing        = array(
      'Base1' => array(),
      'Base2' => array()
    );

    $ids400_1              = $this->getIds400ForClasses($this->ds1, true);
    $ids400_2              = $this->getIds400ForClasses($this->ds2, true);

    $ids400_missing['Base2'] = array_diff($ids400_1[$class_name], $ids400_2[$class_name]);
    $ids400_missing['Base1'] = array_diff($ids400_2[$class_name], $ids400_1[$class_name]);

    return $ids400_missing;
  }
}
