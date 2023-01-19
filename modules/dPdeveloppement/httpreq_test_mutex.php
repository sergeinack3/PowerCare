<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Sessions\CSessionHandler;

CCanDo::checkRead();

$action = CValue::get("action");

$duration = 10;

// Remove session lock
CSessionHandler::writeClose();

CAppUI::stepAjax("test_mutex-try", UI_MSG_OK, $action);

$mutex = new CMbMutex("test");

switch ($action) {
  case "stall":
    CAppUI::stepAjax("test_mutex-acquired", UI_MSG_OK, $mutex->acquire($duration));
    sleep(5);
    $mutex->release();
    break;

  case "die": 
    CAppUI::stepAjax("test_mutex-acquired", UI_MSG_OK, $mutex->acquire($duration));
    sleep(5);
    CApp::rip();
    break;

  case "run":
    CAppUI::stepAjax("test_mutex-acquired", UI_MSG_OK, $mutex->acquire($duration));
    $mutex->release();
    break;

  case "lock":
    $locked_aquired = $mutex->lock($duration);
    if ($locked_aquired) {
      CAppUI::stepAjax("test_mutex-lock_aquired", UI_MSG_OK);

      sleep(5);

      $mutex->release();
    }
    else {
      CAppUI::stepAjax("test_mutex-already_locked", UI_MSG_WARNING);
    }
    break;

  case "dummy":
    // Nothing to do
    CAppUI::stepAjax("test_mutex-dummy", UI_MSG_OK);
    break;
  
  default:
    CAppUI::stepAjax("test_mutex-fail", UI_MSG_WARNING, $action);
    return;
}
