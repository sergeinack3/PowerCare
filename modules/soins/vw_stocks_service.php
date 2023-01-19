<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

global $m;

CCanDo::checkEdit();

$service_id   = CView::get("service_id", "ref class|CService", true);
$endowment_id = CView::get("endowment_id", "ref class|CProductEndowment", true);
$date_min     = CView::get("date_min", "date default|" . CMbDT::date("-1 DAY"), true);
$date_max     = CView::get("date_max", "date default|now", true);

CView::checkin();

$current_g = CGroups::get();

// Services list
$service = new CService();
$where   = array(
  "group_id"  => "= '$current_g->_id'",
  "cancelled" => "= '0'"
);

$list_services = $service->loadListWithPerms(PERM_READ, $where);

if ($m === "dPurgences") {
  foreach ($list_services as $_service) {
    if (!$_service->urgence) {
      unset($list_services[$_service->_id]);
    }
  }
}

if (count($list_services) == 0) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Vous n'avez accès à aucun service pour effectuer des commandes");

  return;
}

// Service
if (!$service_id) {
  $default_services_id = CAppUI::pref("default_services_id", "{}");

  $default_services_id = json_decode($default_services_id);
  if (isset($default_services_id->{"g$current_g->_id"})) {
    $default_service_id = explode("|", $default_services_id->{"g$current_g->_id"});
    $service_id         = reset($default_service_id);
  }
}

if (!$service_id) {
    $first_service = reset($list_services);
    $service_id    = $first_service->service_id;
}

if (!$endowment_id) {
    $service = new CService();
    $service->load($service_id);
    $service->loadBackRefs("endowments");

    if (CAppUI::gconf("soins Commandes select_first_endowment") && ($endowment_id === null) && count($service->_back["endowments"])) {
        //On ne garde que les dotation actives
        foreach ($service->_back["endowments"] as $key => $_service_endowment) {
            if (!$_service_endowment->actif) {
                unset($service->_back["endowments"][$key]);
            }
        }

        $first        = reset($service->_back["endowments"]);
        $endowment_id = $first->_id;
    }
}

$filter            = new CSejour();
$filter->_filter_date_min = $date_min;
$filter->_filter_date_max = $date_max;
$filter->_date_min = CMbDT::date()." 00:00:00";
$filter->_date_max = CMbDT::date()." 23:59:59";

// Création du template
$smarty = new CSmartyDP("modules/soins");

$smarty->assign("service_id", $service_id);
$smarty->assign("list_services", $list_services);
$smarty->assign("endowment_id", $endowment_id);
$smarty->assign("filter", $filter);

$smarty->display("vw_stocks_service");
