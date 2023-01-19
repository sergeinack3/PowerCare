<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CDHEController;

if (CAppUI::pref('create_dhe_with_read_rights')) {
  CCanDo::checkRead();
}
else {
  CCanDo::checkEdit();
}

$data   = json_decode(stripslashes(CValue::post('data')), true);
$action = CValue::post('action');

$controller = new CDHEController($data);
$controller->doIt($action);
