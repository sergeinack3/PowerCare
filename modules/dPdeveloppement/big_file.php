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

CCanDo::checkRead();

$size = CValue::get("size", 1024 * 1024);

// max = 10MB
$size = min($size, 1024 * 1024 * 10);

$lorem = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy ";
$lorem.= "nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. ";

$lorem_size = strlen($lorem);

$string = str_repeat($lorem, $size / $lorem_size);

ob_clean();

echo $string;

CApp::rip();