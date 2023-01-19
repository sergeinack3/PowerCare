<?php

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

class CDailySalleMiner extends CStoredObject {
  static $mine_delay     = "+4 weeks";
  static $remine_delay   = "+0 weeks";
  static $postmine_delay = "-4 weeks";

  // Table key
  public $miner_id;

  // Plain fields
  public $salle_id;
  public $date;
  public $status;

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
    $spec->uniques["salles_day"] = array("salle_id", "date");
    return $spec;
  }

  /**
   * @see parent::getSpec()
   */
  function getProps() {
    $props = parent::getProps();
    $props["salle_id"]     = "ref class|CSalle notNull";
    $props["date"]         = "date notNull";
    $props["status"]       = "enum list|mined|remined|postmined";
    return $props;
  }

  /**
   * Count all salles before a given date
   *
   * @param string $before If null, count all operations ever
   *
   * @return int
   */
  static function countSallesDaily($before = null) {
    $salle = new CSalle();
    $nb_salles = $salle->countList();

    $first_date = self::getMinDate();
    $day_relative = CMbDT::daysRelative($first_date, CMbDT::date($before));
    return $nb_salles*$day_relative;
  }

  /**
   * @return mixed
   */
  static function getMinDate() {
    $op = new COperation();
    $op->loadObject(array('date' => ' IS NOT NULL'), "date ASC");
    return max($op->date, "2000-01-01");
  }

  /**
   * Make all salles counts
   *
   * @return int[] Keys being: overall, tobemined, toberemined
   */
  static function makeSalleDailyCounts() {
    return array(
      "overall"       => self::countSallesDaily(),
      "tobemined"     => self::countSallesDaily(CMbDT::date(self::$mine_delay)),
      "toberemined"   => self::countSallesDaily(CMbDT::date(self::$remine_delay)),
      "tobepostmined" => self::countSallesDaily(CMbDT::date(self::$postmine_delay)),
    );
  }

  /**
   * Count operations that have not been mined yet
   *
   * @return int
   */
  function countUnmined() {
    return $this->_count_unmined = (self::countSallesDaily(CMbDT::date(self::$mine_delay))) - $this->countList();
  }

  /**
   * Count mining that have not been remined yet
   *
   * @return int
   */
  function countUnremined() {
    $date             = CMbDT::date(self::$remine_delay);
    $where["date"]    = "< '$date'";
    $where["status"] = "= 'mined'";
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
    $where["status"] = " IN ('remined', 'postmined')";
    return $this->_count_unpostmined = $this->countList($where);
  }

  /**
   * Mine or remine the first availables salles
   *
   * @param int    $limit
   * @param string $phase
   *
   * @return array Success/failure counts report
   */
  function mineSome($limit = 100, $phase = "mine") {
    $date = CMbDT::date();
    $report = array(
      "success" => 0,
      "failure" => 0,
    );

    if (!$limit) {
        return $report;
    }

    $salle = new CSalle();
    $ds = $salle->getDS();

    $min_date = self::getMinDate();

    $phases = array(
      "mine"      => array("mined", "remined", "postmined"),
      "remine"    => array("remined", "postmined"),
      "postmine"  => array("postmined"),
    );

    $ref_dates = array(
      "mine"      => CMbDT::date(self::$mine_delay),
      "remine"    => CMbDT::date(self::$remine_delay),
      "postmine"  => CMbDT::date(self::$postmine_delay),
    );

    $sql = "SELECT sallesbloc.salle_id, MAX(date) as date FROM sallesbloc
      LEFT JOIN salle_daily_occupation ON salle_daily_occupation.salle_id = sallesbloc.salle_id
      WHERE (salle_daily_occupation.status ".$ds->prepareIn($phases[$phase])." OR salle_daily_occupation.status IS NULL)
      GROUP BY salle_id
      ";
    if (!$result = $ds->loadList($sql)) {
      return;
    }

    // cleanup
    foreach ($result as &$_result) {
      if (!$_result["date"]) {
        $_result["date"] = $min_date;
      }
    }

    // iteration
    $i = $limit;
    while($i--) {
      // sort
        $oredered_results = CMbArray::pluck($result, "date");
      array_multisort($oredered_results, SORT_ASC, $result);

      // first
      $result[0]["date"] = CMbDT::date("+1 DAY", $result[0]["date"]);
      if ($result[0]["date"] > $ref_dates[$phase]) {
        break;
      }

      $this->mine($result[0]["salle_id"], $result[0]["date"]);
      if ($msg = $this->store()) {
        $report["failure"]++;
      }
      else {
        $report["success"]++;
      }
    }

    return $report;
  }

  /**
   * Operation sur les ranges
   *
   * @param int    $salle_id Salle id
   * @param string $date     date to mine
   *
   * @return null
   */
  function mine($salle_id, $date) {
    $this->nullifyProperties();
    $this->salle_id = $salle_id;
    $this->date = $date;
    $this->loadMatchingObject();

    if ($this->date <= CMbDT::date(self::$postmine_delay)) {
      $this->status = "postmined";
    }
    elseif ($this->date <= CMbDT::date(self::$remine_delay)) {
      $this->status = "remined";
    }
    else {
      $this->status = "mined";
    }
  }
}
