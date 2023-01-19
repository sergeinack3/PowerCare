<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

/**
 * Operation daily miner
 */
class COperationMiner extends CStoredObject {
  static $mine_delay     = "+4 weeks";
  static $remine_delay   = "+0 weeks";
  static $postmine_delay = "-4 weeks";

  // Table key
  public $miner_id;

  // Plain fields
  public $operation_id;
  public $date;
  public $remined;
  public $postmined;

  // Count fields
  public $_count_unmined;
  public $_count_unremined;
  public $_count_unpostmined;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->key = "miner_id";
    $spec->loggable = false;
    $spec->uniques["operations"] = array("operation_id");
    return $spec;
  }

  /**
   * @see parent::getSpec()
   */
  function getProps() {
    $props = parent::getProps();
    $props["operation_id"] = "ref class|COperation notNull cascade";
    $props["date"]         = "date notNull";
    $props["remined"]      = "bool notNull default|0";
    $props["postmined"]    = "bool notNull default|0";
    return $props;
  }

  /**
   * Count all operations before a given date
   *
   * @param string $before If null, count all operations ever
   *
   * @return int
   */
  static function countOperations($before = null) {
    $operation = new COperation;
    $where = null;
    $ljoin = null;
    if ($before) {
      $where[] = "operations.date < '$before'";
    }

    return $operation->countList($where, null, $ljoin);
  }

  /**
   * Make all operation counts
   *
   * @return int[] Keys being: overall, tobemined, toberemined
   */
  static function makeOperationCounts() {
    return array(
      "overall"       => self::countOperations(),
      "tobemined"     => self::countOperations(CMbDT::date(self::$mine_delay)),
      "toberemined"   => self::countOperations(CMbDT::date(self::$remine_delay)),
      "tobepostmined" => self::countOperations(CMbDT::date(self::$postmine_delay)),
    );
  }

  function makeMineCounts() {
    return array(
      "unmined"     => $this->countUnmined(),
      "unremined"   => $this->countUnremined(),
      "unpostmined" => $this->countUnpostmined(),
    );
  }

  /**
   * Count operations that have not been mined yet
   *
   * @return int
   */
  function countUnmined() {
    $date = CMbDT::date(self::$mine_delay);
    $operation = new COperation;
    $table = $this->_spec->table;
    $ljoin[$table] = "$table.operation_id = operations.operation_id";
    $where["$table.operation_id"] = "IS NULL";
    $where[] = "operations.date < '$date'";
    return $this->_count_unmined = $operation->countList($where, null, $ljoin);

  }

  /**
   * Count mining that have not been remined yet
   *
   * @return int
   */
  function countUnremined() {
    $date             = CMbDT::date(self::$remine_delay);
    $where["date"]    = "< '$date'";
    $where["remined"] = "= '0'";
    return $this->_count_unremined = $this->countList($where);
  }

  /**
   * Count mining that have not been remined yet
   *
   * @return int
   */
  function countUnpostmined() {
    $date               = CMbDT::date(self::$postmine_delay);
    $where["date"]      = "< '$date'";
    $where["postmined"] = "= '0'";
    return $this->_count_unpostmined = $this->countList($where);
  }

  /**
   * Mine or remine the first available operations
   *
   * @param int    $limit
   * @param string $phase
   *
   * @return array Success/failure counts report
   */
  function mineSome($limit = 100, $phase = "mine") {
    $report = array(
      "success" => 0,
      "failure" => 0,
    );

    if (!$limit) {
      return $report;
    }

    $operation = new COperation;
    /** @var COperation[] $operations */
    $operations = array();

    CView::enforceSlave();

    if ($phase == "remine") {
      $date             = CMbDT::date(self::$remine_delay);
      $where["date"]    = "< '$date'";
      $where["remined"] = "= '0'";
      $mined = $this->loadList($where, null, $limit);
      $operation_ids = CMbArray::pluck($mined, "operation_id");
      $operations = $operation->loadAll($operation_ids);
    }

    if ($phase == "postmine") {
      $date               = CMbDT::date(self::$postmine_delay);
      $where["date"]      = "< '$date'";
      $where["postmined"] = "= '0'";
      $mined = $this->loadList($where, null, $limit);
      $operation_ids = CMbArray::pluck($mined, "operation_id");
      $operations = $operation->loadAll($operation_ids);
    }

    if ($phase == "mine") {
      $date          = CMbDT::date(self::$mine_delay);
      $table         = $this->_spec->table;
      $ljoin[$table] = "$table.operation_id = operations.operation_id AND $table.miner_id IS NULL";
      $where[] = "operations.date < '$date'";
      $operations = $operation->loadList($where, null, $limit, null, $ljoin);
    }

    $plages = CStoredObject::massLoadFwdRef($operations, "plageop_id");
    $salles = CStoredObject::massLoadFwdRef($plages, "salle_id");
    CStoredObject::massLoadFwdRef($salles, "bloc_id");

    CView::disableSlave();

    foreach ($operations as $_operation) {
      $_operation->loadRefPlageOp();
      $this->mine($_operation);
      if ($msg = $this->store()) {
        trigger_error($msg, UI_MSG_ERROR);
        $report["failure"]++;
        continue;
      }

      $report["success"]++;
    }

    return $report;
  }

  /**
   * Mine the operation
   *
   * @param COperation $operation Operation
   *
   * @return null
   */
  function mine(COperation $operation) {
    $this->nullifyProperties();
    $this->operation_id = $operation->_id;
    $this->loadMatchingObject();
    $this->date = CMbDT::date($operation->_datetime);

    if ($this->date < CMbDT::date(self::$remine_delay)) {
      $this->remined = 1;
    };

    if ($this->date < CMbDT::date(self::$postmine_delay)) {
      $this->postmined = 1;
    };
  }

  function warnUsage() {
    /** @var self $that */
    $that = new static();
    $that->loadObject(['1'], "date DESC");
    $warn = $that->date >= CMbDT::date("-1 DAY") ? 1 : 0;
    CAppUI::stepMessage(
      $warn ? UI_MSG_OK : UI_MSG_WARNING,
      "COperationMiner-warnusage-$warn",
      CAppUI::tr("$that->_class"),
      $that->date
    );
  }
}
