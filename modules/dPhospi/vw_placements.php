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
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$services_ids = CView::get("services_ids", "str", true);
$readonly     = CView::get("readonly", "bool");

$services_ids = CService::getServicesIdsPref($services_ids);

CView::checkin();

$smarty = new CSmartyDP();

$smarty->assign("readonly", $readonly);

$smarty->display("vw_placements.tpl");
