<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Core\CView;

CCanDo::checkEdit();

$show_cancelled       = CView::get("show_cancelled", "bool default|0", true);
$is_tamm_consultation = CView::get("is_tamm_consultation", "bool default|0");

CView::checkin();

$libelles = CPlageconsult::getLibellesPref();

$smarty = new CSmartyDP();
$smarty->assign("libelles", $libelles);
$smarty->assign("show_cancelled", $show_cancelled);
$smarty->assign("is_tamm_consultation", $is_tamm_consultation);
$smarty->display("vw_edit_pref_libelles_plage.tpl");
