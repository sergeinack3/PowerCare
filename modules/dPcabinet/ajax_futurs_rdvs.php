<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$patient_id = CView::getRefCheckEdit("patient_id", "ref class|CPatient");

CView::checkin();
CView::enableSlave();

$curr_user = CMediusers::get();
$group     = CGroups::loadCurrent();

$prats    = $curr_user->loadProfessionnelDeSante();
$cabinets = $group->loadFunctions();

$smarty = new CSmartyDP();

$smarty->assign("patient_id", $patient_id);
$smarty->assign("prats"     , $prats);
$smarty->assign("cabinets"  , $cabinets);
$smarty->assign("curr_user" , $curr_user);

$smarty->display("inc_futurs_rdvs.tpl");
