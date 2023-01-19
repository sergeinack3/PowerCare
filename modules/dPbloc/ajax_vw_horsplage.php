<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CIntervHorsPlage;

CCanDo::checkRead();

$date_start = CValue::get("date", CMbDT::date());
$date_end   = CValue::get("end", $date_start);
$chir_id    = CValue::get("chir_id");

$chir = new CMediusers();
$chirs = array();
if ($chir_id) {
  $chir->load($chir_id);
  $chir->loadRefFunction();
  $chirs[] = $chir_id;
}

$listHorsPlage = CIntervHorsPlage::getForDates($date_start, $date_end, $chirs);

foreach ($listHorsPlage as $_operation) {
  $_operation->loadRefPraticien()->loadRefFunction();
  $_operation->loadRefPatient()->loadRefPhotoIdentite();
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("chir", $chir);
$smarty->assign("date", $date_start);
$smarty->assign("objects", $listHorsPlage);
$smarty->display("inc_vw_horsplage.tpl");