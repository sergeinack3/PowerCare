<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CValue;

CCanDo::checkAdmin();

// Size in KB
$size = CValue::get("size", 100);
$size = min($size, 10*1024); // Cap it to 10MB MAX

$big_file = CAppUI::getTmpPath("bandwidth_test/big.bin");
CMbPath::forceDir(dirname($big_file));
file_put_contents($big_file, str_pad("", 1024*$size, "a")); // Must be a "normal" char so that it's not url encoded

$empty_file = CAppUI::getTmpPath("bandwidth_test/empty.bin");
file_put_contents($empty_file, "");
