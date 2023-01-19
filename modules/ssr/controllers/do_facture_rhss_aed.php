<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CRHS;

$sejour_ids  = CValue::post("sejour_ids");
$date_monday = CValue::post("date_monday");
$all_rhs     = CValue::post("all_rhs");

$where["sejour_id"]   = CSQLDataSource::prepareIn($sejour_ids);
$where["date_monday"] = $all_rhs ? ">= '$date_monday'" : "= '$date_monday'";

$order = "sejour_id, date_monday";

$rhs = new CRHS;
/** @var CRHS[] $rhss */
$rhss = $rhs->loadList($where, $order);
if (count($rhss)) {
  foreach ($rhss as $_rhs) {
    $_rhs->facture = CValue::post("facture");
    $msg           = $_rhs->store();
    CAppUI::displayMsg($msg, "CRHS-msg-modify");
  }
}
else {
  CAppUI::setMsg("CRHS.none", UI_MSG_WARNING);
}

echo CAppUI::getMsg();

CApp::rip();
