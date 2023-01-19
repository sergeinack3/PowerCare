<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$g            = CGroups::loadCurrent()->_id;
$date         = CValue::get("date", CMbDT::date());
$mode         = CValue::get("mode", 0);
$services_ids = CValue::getOrSession("services_ids", "");

$services_ids = CService::getServicesIdsPref($services_ids);

// Récupération des chambres/services
$where               = array();
$where["group_id"]   = "= '$g'";
$where["service_id"] = CSQLDataSource::prepareIn($services_ids);
$where["cancelled"]  = "= '0'";
$service             = new CService();
$order               = "nom";
$services            = $service->loadListWithPerms(PERM_READ, $where, $order);

// Chargement de chaque services
foreach ($services as $_service) {
  loadServiceComplet($_service, $date, $mode, null, null, null, false);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("demain", CMbDT::date("+ 1 day", $date));
$smarty->assign("services", $services);

$smarty->display("print_tableau.tpl");
