<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CSecteur;
use Ox\Mediboard\Hospi\CService;

/**
 * Choix d'un service destinataire pour placer le patient
 */
CCAnDo::checkRead();

$action = CValue::get("action", "mapService");
$lit_id = CValue::get("lit_id");

$group_id = CGroups::loadCurrent()->_id;

$service             = new CService();
$where               = array();
$where["group_id"]   = "= '$group_id'";
$where["cancelled"]  = "= '0'";
$where["secteur_id"] = "IS NULL";
$order               = "externe, nom";
$all_services        = $service->loadList($where, $order);

unset($where["secteur_id"]);
$services_allowed = $service->loadListWithPerms(PERM_READ, $where, $order);

$where             = array();
$where["group_id"] = "= '$group_id'";
$secteur           = new CSecteur();
$secteurs          = $secteur->loadList($where, "nom");

CMbObject::massLoadBackRefs($secteurs, "services");

foreach ($secteurs as $_secteur) {
  $_secteur->loadRefsServices();
}

$smarty = new CSmartyDP();

$smarty->assign("all_services", $all_services);
$smarty->assign("services_allowed", $services_allowed);
$smarty->assign("secteurs", $secteurs);
$smarty->assign("action", $action);
$smarty->assign("lit_id", $lit_id);

$smarty->display("inc_select_service.tpl");