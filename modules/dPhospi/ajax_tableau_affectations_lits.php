<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$g = CGroups::loadCurrent()->_id;

// A passer en variable de configuration
$heureLimit = "16:00:00";

$date            = CValue::getOrSession("date", CMbDT::date());
$mode            = CValue::getOrSession("mode", 0);
$services_ids    = CValue::getOrSession("services_ids");
$triAdm          = CValue::getOrSession("triAdm", "praticien");
$_type_admission = CValue::getOrSession("_type_admission", "ambucomp");
$filterFunction  = CValue::getOrSession("filterFunction");
$prestation_id   = CValue::getOrSession("prestation_id");

$emptySejour                  = new CSejour();
$emptySejour->_type_admission = $_type_admission;

$totalLits = 0;

$services_ids = CService::getServicesIdsPref($services_ids);

// Récupération des chambres/services
$where              = array();
$where["group_id"]  = "= '$g'";
$where["cancelled"] = "= '0'";
$services           = new CService();
$order              = "externe, nom";
$services           = $services->loadListWithPerms(PERM_READ, $where, $order);

$where_service = "";
if (reset($services_ids)) {
  $where_service = "sejour.service_id " . CSQLDataSource::prepareIn($services_ids) . " OR sejour.service_id IS NULL";
}

$count_services = 0;

// Chargement des services
foreach ($services as &$service) {
  if (!in_array($service->_id, $services_ids)) {
    continue;
  }

  loadServiceComplet($service, $date, $mode, "", "", $prestation_id);
  CApp::$chrono->stop("Load Service Complet : '$service->_view'");
  CApp::$chrono->start();
  $totalLits += $service->_nb_lits_dispo;

  if (count($service->_ref_chambres)) {
    $count_services++;
  }
}

$hotellerie_active = CModule::getActive("hotellerie");

foreach ($services as $key => $_service) {
  if (!count($_service->_ref_chambres)) {
    unset($services[$key]);
    continue;
  }

  if ($hotellerie_active) {
    foreach ($_service->_ref_chambres as $_chambre) {
      foreach ($_chambre->_ref_lits as $_lit) {
        $cleanup = $_lit->loadLastCleanup($date);
        $cleanup->getColorStatusCleanup();
      }
    }
  }
}

// Nombre de patients à placer pour la semaine qui vient (alerte)
$today   = CMbDT::date() . " 01:00:00";
$endWeek = CMbDT::dateTime("+7 days", $today);

$where                    = array();
$where["annule"]          = "= '0'";
$where["sejour.entree"]   = "BETWEEN '$today' AND '$endWeek'";
$where["sejour.group_id"] = "= '$g'";
if ($_type_admission == "ambucomp") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp'";
}
elseif ($_type_admission == "ambucompssr") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr'";
}
elseif ($_type_admission) {
  $where["sejour.type"] = " = '$_type_admission'";
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence());
}
if ($_type_admission != "seances") {
  $where[] = "affectation.affectation_id IS NULL";
}
if ($where_service) {
  $where[] = $where_service;
}
$leftjoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";

// Filtre sur les fonctions
if ($filterFunction) {
  $leftjoin["users_mediboard"]          = "sejour.praticien_id = users_mediboard.user_id";
  $where["users_mediboard.function_id"] = " = '$filterFunction'";
}

$sejour = new CSejour();
$alerte = $sejour->countList($where, null, $leftjoin);
CApp::$chrono->stop("Patient à placer dans la semaine");
CApp::$chrono->start();

$affectation         = new CAffectation();
$affectation->entree = CMbDT::addDateTime("08:00:00", $date);
$affectation->sortie = CMbDT::addDateTime("23:00:00", $date);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("services_ids", $services_ids);
$smarty->assign("affectation", $affectation);
$smarty->assign("date", $date);
$smarty->assign("demain", CMbDT::date("+ 1 day", $date));
$smarty->assign("heureLimit", $heureLimit);
$smarty->assign("mode", $mode);
$smarty->assign("emptySejour", $emptySejour);
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign("totalLits", $totalLits);
$smarty->assign("services", $services);
$smarty->assign("alerte", $alerte);
$smarty->assign("prestations", CPrestation::loadCurrentList());
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("count_services", $count_services);

$smarty->display("inc_tableau_affectations_lits.tpl");

if (CAppUI::pref("INFOSYSTEM")) {
    CApp::log("Rapport uniquement visible avec les informations système", CMbArray::pluck(CApp::$chrono->report, "total"));
}
