<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$rank = CView::get("rank", "num");

CView::checkin();

$smarty = new CSmartyDP();

$smarty->assign("rank"    , $rank);
$smarty->assign("multiple", 1);

$smarty->display("inc_form_admission_patient");