<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;

class CDataMinerWorker implements IShortNameAutoloadable {

  static function mine($parent_class) {
    $classes = CApp::getChildClasses($parent_class);
    $limit   = CAppUI::conf("dataminer_limit");

    foreach ($classes as $_class) {
      $miner  = new $_class;
      $report = $miner->mineSome($limit, "mine");

      $dt = CMbDT::dateTime();
      echo "<$dt> Miner: $_class. Success mining count is '" . $report["success"] . "'\n";
      if (!$report["failure"]) {
        echo "<$dt> Miner: $_class. Failure mining counts is '" . $report["failure"] . "'\n";
      }

      $miner  = new $_class;
      $report = $miner->mineSome($limit, "remine");

      $dt = CMbDT::dateTime();
      echo "<$dt> Reminer: $_class. Success remining count is '" . $report["success"] . "'\n";
      if (!$report["failure"]) {
        echo "<$dt> Reminer: $_class. Failure remining counts is '" . $report["failure"] . "'\n";
      }

      $miner  = new $_class;
      $report = $miner->mineSome($limit, "postmine");

      $dt = CMbDT::dateTime();
      echo "<$dt> Postminer: $_class. Success postmining count is '" . $report["success"] . "'\n";
      if (!$report["failure"]) {
        echo "<$dt> Postminer: $_class. Failure postmining counts is '" . $report["failure"] . "'\n";
      }
    }

  }

}