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
use Ox\Core\CView;
use Ox\Mediboard\Stats\CTempsOp;

CCanDo::checkRead();
$chir_id    = CView::get("chir_id", "str default|0");
$codes      = CView::get("codes", "str");
$javascript = CView::get("javascript", "bool default|1");
CView::checkin();

$codes = explode("|", $codes);
$result = CTempsOp::getTime($chir_id, $codes);
$temps = $result ? CMbDT::strftime("%Hh%M", $result) : $temps = "-";

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("temps", $temps);
$smarty->assign("javascript", $javascript);

$smarty->display("inc_get_time");
