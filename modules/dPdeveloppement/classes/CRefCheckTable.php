<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Atih\CCMA;
use Ox\Mediboard\Pmsi\CCIM10;
use Ox\Mediboard\System\Forms\CExObject;
use Throwable;

/**
 * Description
 */
class CRefCheckTable extends CMbObject {
  /** @var integer Primary key */
  public $ref_check_table_id;

  public $class;
  public $start_date;
  public $end_date;
  public $max_id;
  public $count_rows;

  /** @var CStoredObject */
  protected $_obj;

  /** @var CRefCheckField[] */
  protected $_refs_check_field;

  public $_total_lines;
  public $_state;
  public $_max_lines;
  public $_duration;
  public $_error_count;

  /** @var CRefCheckField */
  public $_current_ref_check_field;

  public static $_chunk_size = array(
    1000    => '',
    10000   => '',
    100000  => '',
    1000000 => '',
  );

  public static $_ignore_classes = array(
    'CRefCheckTable',
    'CRefCheckField',
    'CRefError',
    'CExObject',
    'CCIM10',
    'CCMA',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "ref_check_table";
    $spec->key      = "ref_check_table_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['class']      = 'str notNull';
    $props['start_date'] = 'dateTime';
    $props['end_date']   = 'dateTime';
    $props['max_id']     = 'num default|0';
    $props['count_rows'] = 'num default|0';

    return $props;
  }

  /**
   * Set the class attribute and instanciate an objets of class
   *
   * @param string $class Name of the class to set
   *
   * @return void
   */
  public function setClass($class) {
    if (class_exists($class)) {
      $this->class = $class;
      $obj         = CModelObject::getInstance($class);
      $this->_obj  = $obj;
    }
  }

  /**
   * @param int $chunk_size Size of a chunk of data
   *
   * @return void
   * @throws Exception
   */
  public function checkRefs($chunk_size) {
    CView::enforceSlave();

    $this->setStart();

    $this->count_rows = $this->getRowsCount();
    $this->max_id     = $this->getMaxId();

    $this->getFieldToCheck();

    if ($this->_current_ref_check_field && $this->_current_ref_check_field->_id) {
      $this->checkField($chunk_size);
    }

    $count_not_ended = $this->countBackRefs('ref_fields', array('end_date' => 'IS NULL', 'main_ref_check_field_id' => 'IS NULL'));

    if ($count_not_ended == 0) {
      $this->end_date = CMbDT::dateTime();
      CAppUI::setMsg('CRefCheckTable-msg-over', UI_MSG_OK, $this->class);
    }

    CView::disableSlave();

    if ($msg = $this->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
  }

  /**
   * Get the first CRefCheckField with no end date
   *
   * @return CStoredObject
   */
  public function getFieldToCheck() {
    $where = array(
      'end_date'                => 'IS NULL',
      'main_ref_check_field_id' => 'IS NULL',
    );

    $fields = $this->loadBackRefs('ref_fields', 'field ASC', 1, null, null, null, null, $where);

    return $this->_current_ref_check_field = reset($fields);
  }

  /**
   * Check the references of a field
   *
   * @param int $chunk_size Number of rows to parse
   *
   * @return bool
   */
  public function checkField($chunk_size) {
    if (!$this->_obj) {
      $this->setClass($this->class);
    }

    if (!$this->_current_ref_check_field->start_date) {
      $this->_current_ref_check_field->start_date = CMbDT::dateTime();
    }

    $lines = $this->getLinesToCheck($chunk_size);

    CView::disableSlave();

    if (isset($this->_obj->_specs[$this->_current_ref_check_field->field]->meta)) {
      $ids_by_class = array();
      $meta         = $this->_obj->_specs[$this->_current_ref_check_field->field]->meta;

      // Sorting lines by target_class
      foreach ($lines as $_line) {
        if (!array_key_exists($_line[$meta], $ids_by_class)) {
          $ids_by_class[$_line[$meta]] = array();
        }

        if ($_line[$this->_current_ref_check_field->field]) {
          $ids_by_class[$_line[$meta]][$_line[$this->_current_ref_check_field->field]] = '';
        }
      }

      foreach ($ids_by_class as $_class => $_ids) {
        $_ids = array_keys($_ids);
        /** @var CRefCheckField $ref_field */
        $ref_field                          = CModelObject::getInstance('CRefCheckField');
        $ref_field->main_ref_check_field_id = $this->_current_ref_check_field->_id;
        $ref_field->ref_check_table_id      = $this->_id;
        $ref_field->target_class            = $_class;
        $ref_field->field                   = $this->_current_ref_check_field->field;

        $ref_field->loadMatchingObjectEsc();

        if (!$ref_field->_id) {
          if ($msg = $ref_field->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
          }
        }

        $ids_missing = $ref_field->getMissingIds($_ids);
        $ref_field->createErrors($ids_missing);
      }
    }
    else {
      $ids_to_check = array();
      foreach ($lines as $_line) {
        // Better performance using the array key to get unique values
        $ids_to_check[$_line[$this->_current_ref_check_field->field]] = '';
      }
      $ids_missing = $this->_current_ref_check_field->getMissingIds(array_keys($ids_to_check));
      $this->_current_ref_check_field->createErrors($ids_missing);
    }

    $this->_current_ref_check_field->count_rows += count($lines);

    $last_line = ($lines) ? end($lines) : null;

    $this->_current_ref_check_field->last_id = ($last_line[$this->_obj->_spec->key]) ?: null;
    $ended                                   = ($this->max_id == 0 || !$this->_current_ref_check_field->last_id
      || $this->_current_ref_check_field->last_id == $this->max_id);

    if ($ended) {
      $this->_current_ref_check_field->end_date = CMbDT::dateTime();
    }

    if ($msg = $this->_current_ref_check_field->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }

    return $ended;
  }

  /**
   * Retrieve the lines to check for references
   *
   * @param int $chunk_size Number of lines to return
   *
   * @return array
   */
  public function getLinesToCheck($chunk_size) {
    $ds = $this->_obj->getDS();
    if (!$ds || !$this->_obj->_spec->table || !$this->_obj->_spec->key || !$ds->hasTable($this->_obj->_spec->table)) {
      return array();
    }

    $field_spec = $this->_obj->_specs[$this->_current_ref_check_field->field];
    $where      = array(
      $this->_obj->_spec->key => $ds->prepare('> ?', ($this->_current_ref_check_field->last_id) ?: 0)
    );

    $select = array(
      $this->_obj->_spec->key,
      $this->_current_ref_check_field->field,
    );

    if (isset($field_spec->meta)) {
      $select[] = $field_spec->meta;
      //$where[$field_spec->meta] = 'IS NOT NULL';
    }

    $query = new CRequest();
    $query->addSelect($select);
    $query->addTable($this->_obj->_spec->table);
    $query->addWhere($where);
    $query->addOrder($this->_obj->_spec->key);
    $query->setLimit($chunk_size);

    return ($ds->loadList($query->makeSelect())) ?: array();
  }

  /**
   * Set the start date
   *
   * @param bool $force Update value or not
   *
   * @return bool
   */
  public function setStart($force = false) {
    $start = false;
    if (!$this->start_date || $force) {
      $this->start_date = CMbDT::dateTime();
      $start            = true;
    }

    return $start;
  }

  /**
   * Fill the table ref_checker with data to check
   *
   * @param bool $delete_old Truncate tables before adding
   *
   * @return CRefCheckTable
   */
  public function firstFillTable($delete_old = false) {
    if ($delete_old) {
      $check = new self();
      $ids   = $check->loadColumn($check->_spec->key);
      $check->deleteAll($ids);
    }

    $classes = CApp::getChildClasses(CStoredObject::class, false, true);

    $first_ref = null;
    foreach ($classes as $_class) {
      if (!class_exists($_class) || in_array($_class, self::$_ignore_classes) || strpos($_class, 'CExObject') !== false) {
        continue;
      }

      try {
        /** @var CStoredObject $_obj */
        $_obj = CStoredObject::getInstance($_class);
      }
      catch (Throwable $e) {
        CApp::log($e->getMessage());
        continue;
      }

      $ds = $_obj->getDS();
      if (!$ds || !$ds->hasTable($_obj->_spec->table)) {
        continue;
      }

      $ref = new self();
      $ref->setClass($_class);

      $ref->loadMatchingObjectEsc();

      if ($ref && $ref->_id) {
        continue;
      }

      $ref->count_rows = $ref->getRowsCount();
      $ref->max_id     = $ref->getMaxId();

      $ref->createCheckFields();

      if (!$first_ref && $ref->_id) {
        $first_ref = $ref;
      }
    }

    return $first_ref;
  }

  /**
   * Count the rows using the INFORMATION_SCHEMA table
   *
   * @return int
   */
  public function getRowsCount() {
    if (!$this->_obj) {
      $this->_obj = CModelObject::getInstance($this->class);
    }

    if (!$this->_obj) {
      return 0;
    }

    $table = $this->_obj->_spec->table;
    $ds    = $this->_obj->getDS();

    if (!$ds) {
      return 0;
    }

    $query = "SELECT TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?1 AND TABLE_NAME = ?2";

    $db_name = CAppUI::conf("db $ds->dsn dbname");

    return $ds->loadResult($ds->prepare($query, $db_name, $table));
  }

  /**
   * Get the last id from a table
   *
   * @return int
   */
  public function getMaxId() {
    if (!$this->_obj) {
      $this->_obj = CModelObject::getInstance($this->class);
    }

    if (!$this->_obj) {
      return 0;
    }

    $ds = $this->_obj->getDS();

    if (!$ds || !$this->_obj->_spec->key || !$this->_obj->_spec->table || !$ds->hasTable($this->_obj->_spec->table)) {
      return 0;
    }

    $query = new CRequest();
    $query->addSelect($this->_obj->_spec->key);
    $query->addTable($this->_obj->_spec->table);
    $query->addOrder("{$this->_obj->_spec->key} DESC");
    $query->setLimit(1);

    return $ds->loadResult($query->makeSelect());
  }

  /**
   * Check all the fields from $this->class and create the CRefCheckField for each ref
   *
   * @return void
   */
  public function createCheckFields() {
    if (!$this->_obj) {
      $this->_obj = CModelObject::getInstance($this->class);
    }

    foreach ($this->_obj->_specs as $_field_name => $_spec) {
      // Ignore form_fields
      if (strpos($_field_name, '_') === 0 || !($_spec instanceof CRefSpec) || $this->_obj->_spec->key == $_field_name
        || isset($_spec->unlink)
      ) {
        continue;
      }

      // Store the ref_check_table here to avoid storing ref_check_table without references
      if (!$this->_id) {
        if ($msg = $this->store()) {
          CAppUI::stepAjax($msg, UI_MSG_WARNING);
        }
        else {
          CAppUI::setMsg('CRefCheckTable-msg-create', UI_MSG_OK);
        }
      }

      $ref_field                     = new CRefCheckField();
      $ref_field->ref_check_table_id = $this->_id;
      $ref_field->field              = $_field_name;

      if (!isset($_spec->meta)) {
        $ref_field->target_class = $_spec->class;
      }

      $ref_field->loadMatchingObjectEsc();

      if ($ref_field && $ref_field->_id) {
        continue;
      }

      if ($msg = $ref_field->store()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg('CRefCheckField-msg-create', UI_MSG_OK);
      }
    }
  }

  /**
   * Get the actual CRefCheck
   *
   * @return CRefCheckTable
   */
  public static function getRefToCheck() {
    CView::enforceSlave();

    $ref_check = new self();
    $where     = array(
      'end_date' => 'IS NULL',
    );

    $ref_check->loadObject($where, 'class ASC');

    if (!$ref_check || !$ref_check->_id) {
      $count = $ref_check->countList();
      if ($count == 0) {
        CView::disableSlave();
        $ref_check = $ref_check->firstFillTable();
      }
    }

    if (CView::$slavestate) {
      CView::disableSlave();
    }

    return $ref_check;
  }

  /**
   * Load the progression of the check
   *
   * @return void
   */
  public function loadState() {
    $where = array('main_ref_check_field_id' => 'IS NULL');
    if ($this->end_date) {
      $duration        = CMbDT::relativeDuration($this->start_date, $this->end_date);
      $this->_duration = $duration['locale'];
    }

    $ref_check_fields =
      $this->loadBackRefs('ref_fields', null, null, null, null, null, null, $where);

    /** @var CRefCheckField $_ref_check_field */
    foreach ($ref_check_fields as $_ref_check_field) {
      $_ref_check_field->loadBackRefs('target_classes');
      $this->_total_lines += $_ref_check_field->count_rows;
    }

    $this->_max_lines = $this->count_rows * count($ref_check_fields);
    if ($this->count_rows > 0) {
      $this->_state = ($this->_total_lines / $this->_max_lines) * 100;
    }
    elseif ($this->end_date) {
      $this->_state = 100;
    }
    else {
      $this->_state = 0;
    }
  }

  /**
   * Fill a CRefCheckField with duration and errors count
   *
   * @return void
   */
  public function prepareRefFields() {
    $this->loadBackRefs('ref_fields', 'field ASC', null, null, null, null, null, array('main_ref_check_field_id' => 'IS NULL'));
    /** @var CRefCheckField $_ref_field */
    foreach ($this->_back['ref_fields'] as $_ref_field) {
      $_ref_field->setDuration();
      $_ref_field->countErrors();
      $this->_error_count += $_ref_field->_count_errors;
    }
  }
}
