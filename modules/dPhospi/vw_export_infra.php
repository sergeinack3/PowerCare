<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$only_actifs = CView::get("only_actifs", "bool");

CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("only_actifs", $only_actifs);
$smarty->display('vw_export_infra.tpl');