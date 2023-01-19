<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;

/**
 * assign a new session var for periodical updater
 */
CCanDo::checkRead();

$var = CValue::get("var");
$value = CValue::get("value");

if ($var) {
  $ok = CValue::setSession($var, $value);
  if ($ok) {
    CAppUI::setMsg("Parameter-changed");
  }
}

echo CAppUI::getMsg();
