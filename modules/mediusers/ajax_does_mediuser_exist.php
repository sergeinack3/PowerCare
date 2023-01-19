<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Show mediuser
 */
$adeli = CValue::get("adeli");

$msg = "";
if ($adeli) {
  $mediuser = CMediusers::loadFromAdeli($adeli);

  if ($mediuser->_id) {
    $msg = $mediuser->_id;
  }
}

echo json_encode($msg);