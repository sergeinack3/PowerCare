<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$blocs_ids  = CView::get("blocs_ids", "str", true);
$salle_ids  = CView::get("salle_ids", "str");
$date_suivi = CView::get("date", "date default|now");
CView::checkin();

if (CAppUI::pref("suivisalleAutonome")) {
    $date_suivi = CMbDT::date();
}

$smarty = new CSmartyDP();

$smarty->assign("blocs_ids", $blocs_ids);
$smarty->assign("salle_ids", $salle_ids);
$smarty->assign("date", $date_suivi);

$smarty->display("vw_suivi_salles_presentation.tpl");
