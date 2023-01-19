<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Tableau de bord de la maternité
 */
CCanDo::checkRead();

$date_tdb = CView::get("date_tdb", "date default|now", true);

CView::checkin();

$smarty = new CSmartyDP();

$smarty->assign("date_tdb", $date_tdb);
$smarty->assign("prec", CMbDT::date("-1 day", $date_tdb));
$smarty->assign("suiv", CMbDT::date("+1 day", $date_tdb));
$smarty->assign("sejour", new CSejour());

$smarty->display("vw_tdb_maternite.tpl");