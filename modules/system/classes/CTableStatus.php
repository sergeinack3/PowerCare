<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Mutex\CMbMutex;

/**
 * Table status, similar to SHOW TABLE STATUS, but uses query cache
 */
class CTableStatus extends CMbObject {
  /** @var integer Primary key */
  public $table_status_id;
  
  public $name;
  public $create_time;
  public $update_time;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "table_status";
    $spec->key    = "table_status_id";
    $spec->loggable = false;
    $spec->uniques["name"] = array("name");
    
    return $spec;
  }
  
  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["name"] = "str notNull";
    $props["create_time"] = "dateTime notNull index|0";
    $props["update_time"] = "dateTime notNull index|0";
    
    return $props;
  }

  /**
   * Get info about a table (creation time, update time)
   * 
   * @param string $table  Table name
   * @param string $module Name of the module to get info for
   *
   * @return CStoredObject|CTableStatus|mixed
   */
  static function getInfo($table, $module) {
    $status = self::getLastChange($table, $module);

    return array(
      "name"        => $status->name,
      "create_time" => $status->create_time,
      "update_time" => $status->update_time,
    );
  }

  /**
   * Set status info in $this, from DB meta data
   * 
   * @param string $table  Table name
   * @param bool   $update Update info
   *                       
   * @return void
   */
  protected function setStatusInfo($table, $update) {
    $db_status = $this->getDS()->loadHash("SHOW TABLE STATUS LIKE '$table'");
    
    $this->name = $table;
    $this->create_time = $db_status["Create_time"];
    $this->update_time = ($update ? CMbDT::dateTime() : $db_status["Update_time"]);
  } 

  /**
   * Update table info and return current status object
   * 
   * @param string $table  Table name
   * @param bool   $update Update infor
   *
   * @return self
   */
  static function change($table, $update = true) {
    // Take a mutex
    $mutex = new CMbMutex("table_status_change-$table");
    $mutex->acquire(10);
    
    $self = new self();
    $self->name = $table;
    if (!$self->isInstalled()) {
      $self->setStatusInfo($table, $update);
      $mutex->release();
      return $self;
    }
    
    $statuses = $self->loadMatchingListEsc();
    
    if (count($statuses) === 0) {
      $self->setStatusInfo($table, $update);
      $self->rawStore();

      $mutex->release();
      
      return $self;
    }
    
    if (count($statuses) > 1) {
      trigger_error("Table status for '$table' is multiple, removing one", E_USER_WARNING);
      
      $last = end($statuses);
      $last->delete();
    }

    $status = reset($statuses);
    
    if (!$update) {
      $mutex->release();
      
      return $status;
    }

    $status->update_time = CMbDT::dateTime();
    $status->rawStore();

    $mutex->release();
    
    return $status;
  }

  /**
   * Get the last status change for the configuration for $module
   *
   * @param string $table  Table name to check update
   * @param string $module Module name to check update
   *
   * @return CStoredObject|CTableStatus|mixed
   */
  static function getLastChange($table, $module) {
    // Take a mutex
    $mutex = new CMbMutex("table_status_change-$table");
    $mutex->acquire(10);

    $self = new self();
    $self->name = $table .($module ? "-$module" : "");
    if (!$self->isInstalled()) {
      $self->setStatusInfo($table, false);
      $mutex->release();
      return $self;
    }

    $statuses = $self->loadMatchingListEsc();

    if (count($statuses) === 0) {
      $self->create_time = CMbDT::dateTime();
      $self->update_time = CMbDT::dateTime();
      $self->rawStore();

      $mutex->release();

      return $self;
    }

    if (count($statuses) > 1) {
      trigger_error("Table status for '$table' is multiple, removing one", E_USER_WARNING);

      $last = end($statuses);
      $last->delete();
    }

    $status = reset($statuses);

    $mutex->release();

    return $status;
  }
}
