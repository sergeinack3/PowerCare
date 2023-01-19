<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CAlert;

CCanDo::checkRead();

$date_min     = CView::get("date_min", "date default|now", true);
$date_max     = CView::get("date_max", "date default|now", true);
$service_id   = CView::get("service_id", "ref class|CService", true);
$services_ids = CView::get("services_ids", "str", true);
$praticien_id = CView::get("praticien_id", "ref class|CMediusers", true);

$services = array();
if (CAppUI::gconf("soins Sejour select_services_ids")) {
  $services_ids = CService::getServicesIdsPref($services_ids);
}
else {
  $service  = new CService();
  $services = $service->loadListWithPerms();
}

CView::checkin();

$prat       = new CMediusers();
$praticiens = $prat->loadPraticiens(PERM_READ);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_min);
$smarty->assign("service_id", $service_id);
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("services", $services);
$smarty->assign("alerte", new CAlert());

$smarty->display("vw_sejours_reeducation");