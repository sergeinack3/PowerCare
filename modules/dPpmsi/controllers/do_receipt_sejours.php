<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::read();

$sejour_guids = explode('|', CValue::post('sejour_guids'));

$hour_receipt = CMbDT::dateTime();

foreach ($sejour_guids as $_guid) {
  /** @var CSejour $sejour */
  $sejour = CMbObject::loadFromGuid($_guid);
  $sejour->reception_sortie = $hour_receipt;
  if ($msg = $sejour->store()) {
    break;
  }
}

if (!$msg) {
  CAppUI::stepAjax('pmsi-action-receipt_multiple_sejour-success', UI_MSG_OK);
}
else {
  CAppUI::stepAjax('pmsi-action-receipt_multiple_sejour-error', UI_MSG_ERROR);
}