<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Sessions\CSessionHandler;

CCanDo::checkRead();

$sleep = 5;

$i        = CValue::get("i");
$duration = CValue::get("duration", 10);

$colors = array(
  "#f00",
  "#0f0",
  "#09f",
  "#ff0",
  "#f0f",
  "#0ff",
);

// Remove session lock
CSessionHandler::writeClose();

$mutex = new CMbMutex("test", isset($colors[$i]) ? $colors[$i] : null);
$time = $mutex->acquire($duration);

sleep($sleep);

$mutex->release();

$data = array(
  "driver" => get_class($mutex->getDriver()),
  "i"      => $i,
  "time"   => $time,
);

ob_clean();
echo json_encode($data);
CApp::rip();
