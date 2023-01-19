<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CProtocole;

CCanDo::checkRead();
$ds = CSQLDataSource::get("std");

$chir         = CView::get("chir", "ref class|CMediusers");
$date         = CView::get("date_plagesel", "date default|now", true);
$group_id     = CView::get("group_id", "ref class|CGroups default|" . CGroups::loadCurrent()->_id, true);
$curr_op_time = CView::get("curr_op_time", "time default|25:00");
$multiple     = CView::get("multiple", "bool default|0");
$protocole_id = CView::get("protocole_id", "ref class|CProtocole");
$new_dhe      = CView::get("new_dhe", "num default|0");

CView::checkin();

// Liste des mois selectionnables
$date = CMbDT::format($date, "%Y-%m-01");
$listMonthes = array();
for ($i = -6; $i <= 12; $i++) {
  $curr_key   = CMbDT::transform("$i month", $date, "%Y-%m-%d");
  $curr_month = CMbDT::transform("$i month", $date, "%B %Y");
  $listMonthes[$i]["date"] = $curr_key;
  $listMonthes[$i]["month"] = $curr_month;
}

$protocole = new CProtocole();
$protocole->load($protocole_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date"        , $date);
$smarty->assign("listMonthes" , $listMonthes);
$smarty->assign("chir"        , $chir);
$smarty->assign("group_id"    , $group_id);
$smarty->assign("curr_op_time", $curr_op_time);
$smarty->assign("multiple"    , $multiple);
$smarty->assign("new_dhe"     , $new_dhe);
$smarty->assign("protocole"   , $protocole);

$smarty->display("plage_selector");
