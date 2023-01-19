<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();
// Filtres d'affichage
$date          = CView::get("date", "date default|now", true);
$type_externe  = CView::get("type_externe", "str default|depart", true);
CView::checkin();

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");
$hier          = CMbDT::date("- 1 day", $date);
$demain        = CMbDT::date("+ 1 day", $date);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date_demain"  , $date_demain);
$smarty->assign("date_actuelle", $date_actuelle);
$smarty->assign("date"         , $date);
$smarty->assign("hier"         , $hier);
$smarty->assign("demain"       , $demain);
$smarty->assign("type_externe" , $type_externe);

$smarty->display("vw_idx_permissions.tpl");
