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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CRegleSectorisation;

CCanDo::checkAdmin();

$show_inactive = CView::get("inactive", "bool default|0", true);
$refresh_mode  = CView::get("refresh_mode", "bool default|0");

CView::checkin();

$regleSector = new CRegleSectorisation();

$where = array();

if (!$show_inactive) {
  $where["date_max"] = "> '".CMbDT::dateTime()."' OR date_max IS NULL";
  $where["date_min"] = "< '".CMbDT::dateTime()."' OR date_min IS NULL";
}

$regles = $regleSector->loadGroupList($where, "priority DESC, praticien_id, function_id");

CStoredObject::massLoadFwdRef($regles, "praticien_id");
CStoredObject::massLoadFwdRef($regles, "service_id");
CStoredObject::massLoadFwdRef($regles, "function_id");

$max_prio = 0;
/**
 * @var CRegleSectorisation $_regle
 */
foreach ($regles as $_regle) {
  $max_prio = ($_regle->priority > $max_prio) ? $_regle->priority : $max_prio;
  $_regle->loadRefPraticien()->loadRefFunction();
  $_regle->loadRefService();
  $_regle->loadRefFunction();
  $_regle->checkOlder();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("regles"       , $regles);
$smarty->assign("max_prio"     , $max_prio);
$smarty->assign("show_inactive", $show_inactive);
$smarty->assign("refresh_mode" , $refresh_mode);

$smarty->display("vw_sectorisations");