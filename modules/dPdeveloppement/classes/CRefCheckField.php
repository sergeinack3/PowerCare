<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;

/**
 * Description
 */
class CRefCheckField extends CMbObject {
  /** @var integer Primary key */
  public $ref_check_field_id;

  public $ref_check_table_id;
  public $main_ref_check_field_id;
  public $field;
  public $target_class;
  public $start_date;
  public $end_date;
  public $count_nulls;
  public $last_id;
  public $count_rows;

  public $_duration;
  public $_count_errors;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "ref_check_field";
    $spec->key      = "ref_check_field_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['ref_check_table_id']      = 'ref class|CRefCheckTable notNull back|ref_fields cascade';
    $props['main_ref_check_field_id'] = 'ref class|CRefCheckField cascade back|target_classes';
    $props['field']                   = 'str notNull';
    $props['target_class']            = 'str';
    $props['start_date']              = 'dateTime';
    $props['end_date']                = 'dateTime';
    $props['count_nulls']             = 'num default|0';
    $props['last_id']                 = 'num';
    $props['count_rows']              = 'num default|0';

    return $props;
  }

  /**
   * Create the errors
   *
   * @param array $ids Ids in error
   *
   * @return void
   */
  public function createErrors($ids) {
    if (!$ids || !is_array($ids)) {
      return;
    }

    $ds    = $this->getDS();
    $where = array(
      'missing_id' => $ds->prepareIn($ids),
    );

    $existing_errors = $this->loadBackRefs('errors', null, null, null, null, null, null, $where);

    foreach ($ids as $_id) {
      if ($_id) {
        if (array_key_exists($_id, $existing_errors)) {
          /** @var CRefError $ref_error */
          $ref_error = $existing_errors[$_id];
          $ref_error->count_use++;

          $msg_ok = 'CRefError-msg-modify';
        }
        else {
          $ref_error                     = new CRefError();
          $ref_error->ref_check_field_id = $this->_id;
          $ref_error->missing_id         = $_id;
          $ref_error->count_use          = 1;

          $msg_ok = 'CRefError-msg-create';
        }

        if ($msg = $ref_error->store()) {
          CAppUI::setMsg($msg, UI_MSG_WARNING);
        }
        else {
          CAppUI::setMsg($msg_ok, UI_MSG_OK);
        }
      }
    }
  }

  /**
   * Retrieve the non existing ids from a list of ids
   *
   * @param array $ids_to_check List of ids to check
   *
   * @return array
   */
  public function getMissingIds($ids_to_check) {
    /** @var CStoredObject $target_obj */
    $target_obj = CModelObject::getInstance($this->target_class);

    if (!$target_obj) {
      return array();
    }

    $ds = $target_obj->getDS();

    if (!$ds || !$ids_to_check) {
      return array();
    }

    $where = array(
      $target_obj->_spec->key => $ds->prepareIn($ids_to_check)
    );

    $existing_ids = $target_obj->loadColumn($target_obj->_spec->key, $where);

    if (!$existing_ids) {
      return $ids_to_check;
    }

    return array_diff($ids_to_check, $existing_ids);
  }

  /**
   * Count the number of null values for a reference
   *
   * @param string $class Class to check for null values
   *
   * @return int
   */
  public function countNulls($class) {
    /** @var CStoredObject $obj */
    $obj   = CModelObject::getInstance($class);
    $where = array(
      $this->field => 'IS NULL'
    );

    return $obj->countList($where);
  }

  /**
   * Set the _duration field
   *
   * @return void
   */
  public function setDuration() {
    if ($this->end_date) {
      $duration        = CMbDT::relativeDuration($this->start_date, $this->end_date);
      $this->_duration = $duration['locale'];
    }
  }

  /**
   * Count the errors for a field
   *
   * @return void
   */
  public function countErrors() {
    $counts = $this->countErrorsByClass();
    foreach ($counts as $_count) {
      $this->_count_errors += $_count['count_errors'];
    }
  }

  /**
   * Count the error by class for a field
   *
   * @return array
   */
  public function countErrorsByClass() {
    $counts = array();
    if (!$this->target_class) {
      /** @var CRefCheckField $back_fields */
      $back_fields = $this->loadBackRefs('target_classes');
      foreach ($back_fields as $_field) {
        $counts[$_field->target_class] = array(
          'field'        => $_field,
          'count_errors' => CRefError::countErrorsByField($_field->_id)
        );
      }
    }
    else {
      $counts[$this->target_class] = array(
        'field' => $this,
        'count_errors' => CRefError::countErrorsByField($this->_id)
      ) ;
    }

    return $counts;
  }
}
