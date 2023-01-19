<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Core\FieldSpecs\CDateTimeSpec;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CPasswordSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\FieldSpecs\CStrSpec;
use Ox\Core\FieldSpecs\CTextSpec;

/**
 * Performs class definition checks
 */
class CClassDefinitionChecker implements IShortNameAutoloadable {

  const UNIQUE_FULLTEXT_INDEX_LABEL = 'seeker';

  // Le module sélectionné
  public $module;
  // Les classes du module installées
  public $selected_classes;
  // Liste des classes analysées
  public $list_classes;
  // La liste des modules installés
  public $installed_modules;
  // Les types sélectionnés
  public $types = array();

  public $meta_fields = array();

  // Types d'erreurs qu'on peut prendre en compte
  public static $error_types = array('type', 'params', 'unsigned', 'zerofill', 'null', 'default', 'index', 'extra');

  /**
   * CClassDefinitionChecker constructor.
   *
   * @param string $name Nom du module ou de la classe selectionné
   */
  public function __construct($name = null) {
    foreach (CModule::getInstalled() as $_mod) {
      $this->installed_modules[$_mod->mod_name] = $_mod->mod_name;
    }
    asort($this->installed_modules);

    if (class_exists($name)) {
      $this->module = $name;
      $this->selected_classes[] = $name;
    }
    else {
      if (isset($this->installed_modules[$name])) {
        $this->module = $name;
        foreach (CModule::getClassesFor($this->module) as $_class_name) {
          $this->selected_classes[] = $_class_name;
        }
      }
    }

    $this->list_classes = array();
  }

  /**
   * Build an array of duplicates values for key-value array collection.
   *
   * @param mixed[][] $array Key-value array collection (array of arrays)
   * @param string    $key   The key to inspect
   *
   * @return mixed[][]
   */
  function array_duplicates($array, $key) {
    $ret   = array();
    $count = count($array);
    for ($i = 0; $i < $count; $i++) {
      for ($j = 0; $j < $count; $j++) {
        if ($i != $j && $array[$i][$key] == $array[$j][$key]) {
          $ret[$i][] = $array[$i];
        }
      }
    }

    return $ret;
  }

  /**
   * Construit la requete correspondant à la spec d'une classe
   * avec prise en compte des erreurs que l'on souhaite mettre en évidence
   *
   * @param array $class Données d'analyse de la classe
   *
   * @return string|null
   */
  function getQueryForClass($class) {
    $change     = array();
    $add_index  = array();
    $drop_index = array();
    $ret        = '';

    if (!isset($class['fields']) || count($class['fields']) === 0) {
      return null;
    }

    // On compte les champs de BDD de la classe, si c'est null, on crée la classe
    $add_table = $class['no_table'];

    // Gestion des ALTER TABLE
    foreach ($class['fields'] as $name => $field) {
      $spec_obj = $field['object']['db_spec'];
      $spec_db  = $field['db'];

      $add_field = isset($spec_db['no_column']);

      // creation des lignes de specification des champs
      if ($add_field || $add_table
          || $this->types['type'] && ($spec_obj['type'] != $spec_db['type'])
          || $this->types['params'] && ($spec_obj['params'] != $spec_db['params'])
          || $this->types['unsigned'] && ($spec_obj['unsigned'] != $spec_db['unsigned'])
          || $this->types['zerofill'] && ($spec_obj['zerofill'] != $spec_db['zerofill'])
          || $this->types['default'] && ($spec_obj['default'] != $spec_db['default'])
          || $this->types['extra'] && ($spec_obj['extra'] != $spec_db['extra'])
          || $this->types['null'] && ($spec_obj['null'] != $spec_db['null'])
      ) {

        if ($add_field && !$add_table) {
          $change[$name] = "ADD `$name` ";
        }
        else {
          if ($add_table) {
            $change[$name] = "`$name` ";
          }
          else {
            $change[$name] = "CHANGE `$name` `$name` ";
          }
        }
        $change[$name] .= strtoupper($spec_obj['type']);

        if (is_array($spec_obj['params']) && count($spec_obj['params']) > 0) {
          $change[$name] .= ' (' . implode(',', $spec_obj['params']) . ')';
        }

        $change[$name] .=
          ($spec_obj['unsigned'] ? ' UNSIGNED' : '') .
          ($spec_obj['zerofill'] ? ' ZEROFILL' : '') .
          ($spec_obj['null'] ? '' : ' NOT NULL') .
          ($spec_obj['default'] !== null ? " DEFAULT '{$spec_obj['default']}'" : '') .
          ($spec_obj['extra'] ? " {$spec_obj['extra']}" : '') .
          (($name === $class['key']) ? ' PRIMARY KEY' : '');
      }

      $check_drop_index = false;
      // creation des lignes d'ajout suppression des index
      if ($this->types['index'] && $spec_obj['index'] && !$spec_db['index'] && $class['key'] !== $name) {
        if (strpos($spec_obj['index'], ', ') !== false) {
          $add_index[$name] = $this->addMultiIndex($name, $spec_obj['index']);
        }
        else {
          $add_index[$name] = "ADD INDEX (`$name`)";
        }
      }
      elseif (strpos($spec_obj['index'], ', ') !== false && $spec_db['index'] != $spec_obj['index']) {
        $add_index[$name] = $this->addMultiIndex($name, $spec_obj['index']);
        $check_drop_index = true;
      }

      if ($check_drop_index || ($this->types['index'] && !$spec_obj['index'] && $spec_db['index'])) {
        $drop_index[$spec_db['index']] = "# DROP INDEX (`{$spec_db['index']}`)";
      }
    }

    $glue = ",\n                ";

    // creation / modification de la table
    if (count($change) > 0) {
      if ($add_table) {
        $ret = "CREATE TABLE `{$class['table']}` (\n                "
          . implode($glue, $change) . "\n              )/*! ENGINE=MyISAM */;";
      }
      else {
        $ret = "ALTER TABLE `{$class['table']}` \n                "
          . implode($glue, $change) . ";";
      }
    }

    $add_index = array_unique($add_index);
    // ajout / suppression des index
    if (count($add_index) > 0 || count($drop_index) > 0) {
      $q = array();
      if (count($add_index) > 0) {
        $q[] = implode($glue, $add_index);
      }
      if (count($drop_index) > 0) {
        $q[] = implode($glue, $drop_index);
      }
      $ret .= "\nALTER TABLE `{$class['table']}` \n                "
        . implode($glue, $q) . ";";
    }

    if (count($class['duplicates']) > 0) {
      $ret .= "\n# Il y a probablement des index en double sur cette table";
    }

    // Gestion des INDEX FULLTEXT (ADD/DROP)
    if (count($class['fulltext_indexes']) > 0) {
      foreach ($class['fulltext_indexes'] as $ft_index) {
        $drop_index = false;
        $add_index  = false;
        switch ($ft_index['status']) {
          case 'incomplete';
            $drop_index = true;
            $add_index  = true;
            break;
          case 'missing':
            $add_index  = true;
            break;
          default:
            /* Do nothing */
            break;
        }
        if ($drop_index) {
          $ret .= "\nALTER TABLE `{$class['table']}` DROP INDEX `" .$ft_index['name']. "`;";
        }
        if ($add_index) {
          $ret .= "\nALTER TABLE `{$class['table']}` ADD FULLTEXT INDEX `" .$ft_index['name']
            . "` (".implode(',', $ft_index['required_fields']).");";
        }
      }
    }

    return $ret;
  }

  /**
   * Crée la requête pour ajouter un index multiple
   *
   * @param string $name        Le nom à donner à l'index
   * @param string $index_value Les champs de l'index séparés par une virgule
   *
   * @return string
   */
  public function addMultiIndex($name, $index_value) {
    $prefix = explode('_', $name);

    return 'ADD INDEX ' . $prefix[0] . ' (' . $index_value . ')';
  }

  /**
   * Vérifie si des classes contiennent des erreurs
   *
   * @return array
   */
  public function checkErrors() {
    // Tableau indiquant si chaque champ contient une erreur
    $list_errors = array();
    foreach ($this->list_classes as $_class => &$class_details) {
      $list_errors[$_class] = array();
      $show = false;
      foreach ($class_details['fields'] as $curr_field_name => &$curr_field) {
        $list_errors[$_class][$curr_field_name] = false;

        if (!isset($curr_field['db'])) {
          $curr_field['db']              = array();
          $curr_field['db']['no_column'] = true;
        }

        if (!isset($curr_field['object'])) {
          $curr_field['object'] = array();
        }

        if (!isset($curr_field['object']['db_spec'])) {
          $curr_field['object']['db_spec'] = array();
        }

        foreach (self::$error_types as $err) {
          if (!isset($curr_field['db'][$err])) {
            $curr_field['db'][$err] = null;
          }

          if (!isset($curr_field['object']['db_spec'][$err])) {
            $curr_field['object']['db_spec'][$err] = null;
          }

          if ($this->types[$err] && $curr_field['db'][$err] != $curr_field['object']['db_spec'][$err]) {
            $list_errors[$_class][$curr_field_name] = true;
            $show                                   = true;
          }
        }
      }
      if (!$show) {
        $list_errors[$_class] = null;
      }
    }

    return $list_errors;
  }

  /**
   * Extrait les détails d'une classe et les ajoute dans la liste des classes
   *
   * @param string        $_class Le nom de la classe à traiter
   * @param CStoredObject $object L'objet à traiter
   *
   * @return void
   * @throws Exception
   */
  public function getDetailsSelectedClasses($_class, $object) {
    $this->list_classes[$_class] = array();
    $details                     = &$this->list_classes[$_class];

    // Clé de la table
    $details['table']            = $object->_spec->table;
    $details['key']              = $object->_spec->key;
    $details['db_key']           = null;
    $details['fields']           = array();
    $details['fulltext_indexes'] = array();

    $this->extractClassFields($object, $details);

    $this->extractClassProperties($object, $details);

    $this->extractDBFields($object, $details);
    $details['suggestion'] = null;
  }

  /**
   * Extrait les informations des champs de la classe et les mets dans la liste des classes
   *
   * @param CStoredObject $object  L'objet dont on veut extraire les propriétés des champs
   * @param array         $details Tableau contenant les informations
   *
   * @return void
   */
  public function extractClassFields($object, &$details) {
    // Extraction des champs de la classe
    $fields = $object->getPlainFields();
    $object->getSpecs();

    foreach ($fields as $k => $v) {
      $details['fields'][$k] = array();

      // object fields
      $details['fields'][$k]['object'] = array(
        'spec'    => null,
        'db_spec' => null
      );

      $details['fields'][$k]['db'] = null;

      $is_key = $k === $details['key'];

      // db fields
      if ($spec = @$object->_specs[$k]) {
        $details['fields'][$k]['object']['db_spec'] = CMbFieldSpec::parseDBSpec($spec->getDBSpec());

        $db_spec = &$details['fields'][$k]['object']['db_spec'];

        $check_meta = false;
        if (array_key_exists($object->_class, $this->meta_fields)) {
          $field_name = explode('_', $k);
          if (count($field_name) === 2 && array_key_exists($field_name[0], $this->meta_fields[$object->_class])) {
            $check_meta = 1;
          }
        }

        $db_spec['index'] = (
          $check_meta || (
            $spec->index !== "0" && !$spec instanceof CTextSpec && !$spec instanceof CPasswordSpec && (
              in_array(array($k), $object->_spec->uniques) ||
              isset($spec->class) ||
              $spec instanceof CDateTimeSpec ||
              $spec instanceof CDateSpec ||
              $is_key ||
              $spec->autocomplete ||
              $spec->index === true || $spec->index === "1"
            ))
        );

        $prefix = '';
        // Récupération des champs metas
        if ($db_spec['index']
            && (($spec instanceof CRefSpec && $spec->meta) || $spec instanceof CEnumSpec || $spec instanceof CStrSpec)
        ) {
          $prefix = $this->getPrefixFromField($object, $spec, $k);
          // Si prefix et champs meta alors on attribue la valeur du champ pour l'index
          if ($prefix && isset($spec->meta)) {
            $db_spec['index'] = $db_spec['index'] = $spec->meta . ', ' . $spec->fieldName;
            // Permet de ne pas effacer l'index si le champ correspondant n'est pas encore traité
            $this->meta_fields[$object->_class][$prefix] = $spec->meta . ', ' . $spec->fieldName;
            // Fixe l'index si le champ correpondant n'est pas encore traité
            $details['fields'][$spec->meta]['object']['db_spec']['index'] = $spec->meta . ', ' . $spec->fieldName;
          }
        }

        if (array_key_exists($object->_class, $this->meta_fields)
            && array_key_exists($prefix, $this->meta_fields[$object->_class])
        ) {
          $db_spec['index'] = $this->meta_fields[$object->_class][$prefix];
        }

        $db_spec['null'] = !isset($spec->notNull) && !$is_key;

        $default = null;
        if (isset($spec->default) || $spec->notNull) {
          if ($spec->default === "NULL") {
            $default = "NULL";
          }
          elseif ($spec->default !== null) {
            $default = "{$spec->default}";
          }
        }

        $db_spec['default'] = $default;

        // Some keys from external tables are str
        if ($is_key && $spec instanceof CRefSpec) {
          $db_spec['unsigned'] = true;
        }

        $db_spec['extra'] = '';
        if ($k === $details['key'] && $object->_spec->incremented) {
          $db_spec['extra'] = 'auto_increment';
        }
      }
      $details['fields'][$k]['db'] = null;
    }
  }

  /**
   * Récupère le prefix commum des champs metas (object_id -> object_class, ...)
   *
   * @param CStoredObject $object L'objet à traiter
   * @param CMbFieldSpec  $spec   La spécification du champs que l'on traite
   * @param string        $k      Le nom du champs que l'on traite
   *
   * @return null|string
   */
  public function getPrefixFromField($object, $spec, $k) {
    $prefix = null;
    if ($spec instanceof CRefSpec) {
      $prefix = trim(CMbString::getCommonPrefix($spec->meta, $k), "_");

      if (!$prefix) {
        $prefix = "$spec->meta $k";
      }
    }
    else {
      $other_fields = $object->getPlainFields();

      foreach ($other_fields as $_field => $_other) {
        $_other_spec = $object->_specs[$_field];

        if ($_field === $k || !$_other_spec instanceof CRefSpec) {
          continue;
        }

        if ($_other_spec->meta === $k) {
          $prefix = trim(CMbString::getCommonPrefix($k, $_field), "_");

          if (!$prefix) {
            $prefix = "$k $_field";
          }
        }
      }
    }

    return $prefix;
  }

  /**
   * Récupère les propriétés de la classe et les mets dans la liste des classes
   *
   * @param CStoredObject $object  L'objet à traiter
   * @param array         $details Tableau contenant les informations
   *
   * @return void
   */
  public function extractClassProperties($object, &$details) {
    // Extraction des propriétés de la classe
    foreach ($object->_props as $k => $v) {
      if (isset($k[0]) && $k[0] !== '_') {
        $details['fields'][$k]['object']['spec'] = $v;
      }
    }
  }

  /**
   * Extrait les champs de la base de données et les met dans la liste des classes
   *
   * @param CStoredObject $object  L'objet à traiter
   * @param array         $details Tableau contenant les informations
   *
   * @return void
   * @throws Exception
   */
  public function extractDBFields($object, &$details) {
    $ds = $object->_spec->ds;

    // Extraction des champs de la BDD
    if ($ds && $object->_spec->table && $ds->loadTable($object->_spec->table)) {
      $details['no_table'] = false;

      $sql         = "SHOW COLUMNS FROM `{$object->_spec->table}`";
      $list_fields = $ds->loadList($sql);

      foreach ($list_fields as $curr_field) {
        $details['fields'][$curr_field['Field']]['db'] = array();
        if (!isset($details['fields'][$curr_field['Field']]['object'])) {
          $details['fields'][$curr_field['Field']]['object']         = array();
          $details['fields'][$curr_field['Field']]['object']['spec'] = null;
        }
        $field =& $details['fields'][$curr_field['Field']]['db'];

        $props = CMbFieldSpec::parseDBSpec($curr_field['Type']);

        $field['type']     = $props['type'];
        $field['params']   = $props['params'];
        $field['unsigned'] = $props['unsigned'];
        $field['zerofill'] = $props['zerofill'];
        $field['null']     = ($curr_field['Null'] !== 'NO');
        $field['default']  = $curr_field['Default'];
        $field['index']    = null;
        $field['fulltext'] = null;
        $field['extra']    = $curr_field['Extra'];
      }

      // Extraction des Index
      $sql = "SHOW INDEXES FROM `{$object->_spec->table}` WHERE `index_type` != 'FULLTEXT'";

      $list_indexes = $ds->loadList($sql);

      $duplicates            = $this->array_duplicates($list_indexes, 'Column_name');
      $details['duplicates'] = $duplicates;

      foreach ($list_indexes as $curr_index) {
        $details['fields'][$curr_index['Column_name']]['db']['index'] = $curr_index['Key_name'];
        if (array_key_exists($object->_class, $this->meta_fields)
            && array_key_exists($curr_index['Key_name'], $this->meta_fields[$object->_class])
        ) {
          $details['fields'][$curr_index['Column_name']]['db']['index'] =
            $this->meta_fields[$object->_class][$curr_index['Key_name']];
        }

        if ($curr_index['Key_name'] === 'PRIMARY') {
          $details['db_key'] = $curr_index['Column_name'];
          if ($object->_spec->incremented) {
            $details['fields'][$curr_index['Column_name']]['object']['db_spec']['extra'] = 'auto_increment';
          }
        }
      }

      /* Extraction des index fulltext */
      if (isset($object->_spec->seek) && $object->_spec->seek === 'match') {
        /* Check only classes with the spec "seek" property set to 'match' where 'like' is the default value */
        $seekable_fields = $object->getSeekables();
        if (!empty($seekable_fields)) {
          $seekable_fields_names = array_keys($seekable_fields);
          $details['fulltext_indexes'][$object->_spec->table] = array(
            "required_fields" => $seekable_fields_names,
            "indexed_fields"  => array(),
            "name"            => self::UNIQUE_FULLTEXT_INDEX_LABEL,
            "status"          => 'missing'
          );
        }

        $sql = "SELECT index_name, group_concat(column_name) AS columns
          FROM information_schema.STATISTICS 
          WHERE table_schema = '{$ds->config["dbname"]}' 
          AND table_name = '{$object->_spec->table}' 
          AND index_type = 'FULLTEXT'
          GROUP BY index_name;";
        $fulltext_indexes = $ds->loadList($sql);

        foreach ($details['fulltext_indexes'] as $_required_index) {
          foreach ($fulltext_indexes as $_fulltext_index) {
            $fulltext_fields_in_index = explode(',', $_fulltext_index['columns']);
            if (count(array_diff($_required_index['required_fields'], $fulltext_fields_in_index)) === 0) {
              $details['fulltext_indexes'][$object->_spec->table]['status'] = 'valid';
            }
            else {
              $details['fulltext_indexes'][$object->_spec->table]['status'] = 'incomplete';
            }
            $details['fulltext_indexes'][$object->_spec->table]['indexed_fields'] = $fulltext_fields_in_index;
          }
        }
      }
    }
    else {
      $details['no_table']   = true;
      $details['duplicates'] = array();
    }
  }
}
