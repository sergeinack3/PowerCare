<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CValue;

CCanDo::checkAdmin(); // for now

$data = CValue::post("data");
$data = stripslashes($data);

CApp::log("Log from do_multirequest", json_decode($data, true));
