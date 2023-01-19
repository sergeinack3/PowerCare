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
use Ox\Mediboard\Hospi\CAffectationUserService;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();
$date       = CView::get("date", "date", true);
$service_id = CView::get("service_id", "ref class|CService");
CView::checkin();

$responsable = CAffectationUserService::loadResponsableJour($service_id, $date);

$service = new CService();
$service->load($service_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("responsable", $responsable);
$smarty->assign("service", $service);
$smarty->assign("date", $date);

$smarty->display("vw_edit_responsable_jour.tpl");
