<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CSecteur;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();
$services_ids         = CView::get("services_ids", "str", true);
$services_ids_suggest = CView::get("services_ids_suggest", "str");
$show_np              = CView::get("show_np", "bool default|0");
$view                 = CView::get("view", "str");
$ajax_request         = CView::get("ajax_request", "bool default|1");
$callback             = CView::get("callback", "str");
//permet de passer une variable si besoin, de la forme view-element_id
$element_id = CView::get("element_id", "str");
CView::checkin();

if (!is_array($services_ids_suggest) && !is_null($services_ids_suggest)) {
  $services_ids = explode(",", $services_ids_suggest);
}

$group_id = CGroups::loadCurrent()->_id;

$where               = array();
$where["group_id"]   = "= '$group_id'";
$where["cancelled"]  = "= '0'";
$where["secteur_id"] = "IS NULL";
$order               = "externe, nom";

$service      = new CService();
$all_services = $service->loadList($where, $order);

unset($where["secteur_id"]);
$services_allowed = $service->loadListWithPerms(PERM_READ, $where, $order);

$where = array(
  'group_id' => "= '$group_id'"
);

$secteur  = new CSecteur();
$secteurs = $secteur->loadList($where, "nom");

foreach ($secteurs as $_secteur) {
  $_secteur->loadRefsServices();

  $keys2                  = array_keys($_secteur->_ref_services);
  $_secteur->_all_checked = (count($_secteur->_ref_services) && is_array($services_ids)) > 0 ?
    array_values(array_intersect($services_ids, $keys2)) == $keys2 : false;
}

$services_ids_hospi = CAppUI::pref("services_ids_hospi");
$services_ids_hospi = ($services_ids_hospi) ?: '{}';

$smarty = new CSmartyDP("modules/dPhospi");
$smarty->assign("view", $view);
$smarty->assign("services_ids_hospi", $services_ids_hospi);
$smarty->assign("services_ids", $services_ids);
$smarty->assign("all_services", $all_services);
$smarty->assign("services_allowed", $services_allowed);
$smarty->assign("group_id", $group_id);
$smarty->assign("secteurs", $secteurs);
$smarty->assign("ajax_request", $ajax_request);
$smarty->assign("callback", $callback);
$smarty->assign("show_np", $show_np);
$smarty->assign("element_id", $element_id);
$smarty->display("inc_select_services.tpl");
