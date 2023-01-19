<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$date_min_relance = CView::get("date_min_relance", "date default|" . CMbDT::date("-1 month"), true);
$date_max_relance = CView::get("date_max_relance", "date default|" . CMbDT::date(), true);
$date_min_sejour  = CView::get("date_min_sejour", "date", true);
$date_max_sejour  = CView::get("date_max_sejour", "date", true);
$status           = CView::get("status", "str", true);
$urgence          = CView::get("urgence", "str", true);
$type_doc         = CView::get("type_doc", "str", true);
$commentaire_med  = CView::get("commentaire_med", "bool", true);
$chir_id          = CView::get("chir_id", "ref class|CMediusers", true);
CView::checkin();

$chir = new CMediusers();

if ($chir_id) {
  $chir = CMediusers::get($chir_id);
}

$sejour = new CSejour();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date_min_relance", $date_min_relance);
$smarty->assign("date_max_relance", $date_max_relance);
$smarty->assign("date_min_sejour" , $date_min_sejour);
$smarty->assign("date_max_sejour" , $date_max_sejour);
$smarty->assign("status"          , $status);
$smarty->assign("urgence"         , $urgence);
$smarty->assign("type_doc"        , $type_doc);
$smarty->assign("commentaire_med" , $commentaire_med);
$smarty->assign("chir"            , $chir);
$smarty->assign("sejour"            , $sejour);
$smarty->display("vw_relances");
