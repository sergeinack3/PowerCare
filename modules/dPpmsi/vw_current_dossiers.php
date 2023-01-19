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
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$date_max = CView::get("date_max", "date default|now", true);
$date_min = CView::get("date_min", "date default|" . CMbDT::date("-1 day"), true);
$types    = CView::get("types", "str", true);
CView::checkin();

$sejour_filter = new CSejour();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('date_min'     , $date_min);
$smarty->assign('date_max'     , $date_max);
$smarty->assign('sejour_filter', $sejour_filter);
$smarty->assign('types'        , $types);
$smarty->display("current_dossiers/vw_current_dossiers");
