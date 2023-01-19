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
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$date            = CValue::getOrSession("date", CMbDT::date());
$type_hospi      = CView::get("type_hospi", "str", true);
$vue             = CView::get("vue", "bool default|0", true);
$group_id        = CView::get("g", "ref class|CGroups");
$mode            = CView::get("mode", "bool default|0", true);
$hour_instantane = CView::get("hour_instantane", "num default|" . CMbDT::format(CMbDT::time(), "%H"), true);
$prestation_id   = CView::get("prestation_id", "str default|" . CAppUI::pref("prestation_id_hospi"), true);
$services_ids    = CView::get("services_ids", "str", true);
$by_secteur      = CView::get("by_secteur", "bool default|0", true);
$function_id     = CView::get("function_id", "ref class|CFunctions", true);
$praticien_id    = CView::get("praticien_id", "ref class|CMediusers", true);

$services_ids = CService::getServicesIdsPref($services_ids);

// Si c'est la préférence utilisateur, il faut la mettre en session
CView::setSession("prestation_id", $prestation_id);

CView::checkin();

// Si la date en session vient de la vue temporelle (datetime), on retransforme en date
if (strpos($date, " ") !== false) {
  $date = CMbDT::date($date);
}

$mouvements = array("comp"    => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)),
                    "ambu"    => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)),
                    "urg"     => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)),
                    "ssr"     => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)),
                    "psy"     => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)),
                    "exte"    => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)),
                    "seances" => array("entrees" => array("place" => 0, "non_place" => 0),
                                       "sorties" => array("place" => 0, "non_place" => 0)));
$group      = CGroups::loadCurrent();

// Récupération de la liste des services et du service selectionné
$where = array(
  "externe"  => "= '0'",
  "sejour.group_id" => "= '$group->_id'"
);
$order = "nom";

// Récupération de la liste des praticiens et du praticien selectionné
$praticien  = new CMediusers();
$praticiens = $praticien->loadPraticiens(PERM_READ);

$function  = new CFunctions();
$functions = $function->loadSpecialites(PERM_READ);

$limit1 = $date . " 00:00:00";
$limit2 = $date . " 23:59:59";

// Patients placés
$affectation                 = new CAffectation();
$ljoin                       = array();
$ljoin["sejour"]             = "sejour.sejour_id = affectation.sejour_id";
$ljoin["patients"]           = "sejour.patient_id = patients.patient_id";
$ljoin["users"]              = "sejour.praticien_id = users.user_id";
$ljoin["service"]            = "service.service_id = affectation.service_id";
$where                       = array();
$where["service.group_id"]   = "= '$group->_id'";
$where["service.service_id"] = CSQLDataSource::prepareIn($services_ids);
$where["sejour.type"]        = CSQLDataSource::prepareIn(array_keys($mouvements), $type_hospi);
$where["sejour.annule"]      = "= '0'";
$where["affectation.lit_id"] = "IS NOT NULL";

if ($vue) {
  $where["sejour.confirme"] = " IS NULL";
}
if ($praticien_id) {
  $where["sejour.praticien_id"] = "= '$praticien_id'";
}
if ($function_id) {
  $ljoin["users_mediboard"]             = "users_mediboard.user_id = users.user_id";
  $where["users_mediboard.function_id"] = "= '$function_id'";
}
// Patients non placés
$sejour                     = new CSejour();
$ljoinNP                    = array();
$ljoinNP["affectation"]     = "sejour.sejour_id = affectation.sejour_id";
$whereNP                    = array();
$whereNP["sejour.group_id"] = "= '$group->_id'";
$whereNP["sejour.annule"]   = "= '0'";
$whereNP["sejour.type"]     = CSQLDataSource::prepareIn(array_keys($mouvements), $type_hospi);
if (count($services_ids)) {
  $whereNP[] = "((sejour.service_id " . CSQLDataSource::prepareIn($services_ids) . " OR sejour.service_id IS NULL) AND affectation.affectation_id IS NULL) OR "
    . "(affectation.lit_id IS NULL AND affectation.service_id " . CSQLDataSource::prepareIn($services_ids) . ")";
}

if ($praticien_id) {
  $whereNP["sejour.praticien_id"] = "= '$praticien_id'";
}
if ($function_id) {
  $ljoinNP["users_mediboard"]             = "users_mediboard.user_id = sejour.praticien_id";
  $whereNP["users_mediboard.function_id"] = "= '$function_id'";
}

$datetime_check = "$date $hour_instantane:00:00";

// Comptage des patients présents
$wherePresents = $where;
if ($mode) {
  $wherePresents[] = "'$date' BETWEEN DATE(affectation.entree) AND DATE(affectation.sortie)";
}
else {
  $wherePresents[] = "('$datetime_check' BETWEEN affectation.entree AND affectation.sortie) " .
    ($date == CMbDT::date() ? "AND affectation.effectue = '0'" : "");
}
$presents = $affectation->countList($wherePresents, null, $ljoin);

$wherePresentsNP = $whereNP;
if ($mode) {
  $wherePresentsNP[] = "'$date' BETWEEN DATE(sejour.entree) AND DATE(sejour.sortie)";
}
else {
  $wherePresentsNP[] = "'$datetime_check' BETWEEN sejour.entree AND sejour.sortie";
}

$presentsNP = $sejour->countList($wherePresentsNP, null, $ljoinNP);

// Comptage des déplacements
if ($vue) {
  unset($where["sejour.confirme"]);
  $where["effectue"] = "= '0'";
}
$whereEntrants                       = $whereSortants = $where;
$whereSortants["affectation.sortie"] = "BETWEEN '$limit1' AND '$limit2'";
$whereEntrants["affectation.entree"] = "BETWEEN '$limit1' AND '$limit2'";
$whereEntrants["sejour.entree"]      = "!= affectation.entree";
$whereSortants["sejour.sortie"]      = "!= affectation.sortie";
$dep_entrants                        = $affectation->countList($whereEntrants, null, $ljoin);
$dep_sortants                        = $affectation->countList($whereSortants, null, $ljoin);

// Comptage des entrées/sorties
foreach ($mouvements as $type => &$_mouvement) {
  if (($type_hospi && $type_hospi != $type) || ($type_hospi == "ambu")) {
    continue;
  }
  $where["sejour.type"] = $whereNP["sejour.type"] = " = '$type'";
  foreach ($_mouvement as $type_mouvement => &$_liste) {
    if ($type == "ambu" && $type_mouvement == "sorties") {
      $_liste["place"]     = 0;
      $_liste["non_place"] = 0;
      continue;
    }
    if ($type_mouvement == "entrees") {
      unset($where["affectation.sortie"]);
      $where["affectation.entree"] = "BETWEEN '$limit1' AND '$limit2'";
      if (isset($where["sejour.sortie"])) {
        unset($where["sejour.sortie"]);
      }
      if (isset($whereNP["sejour.sortie"])) {
        unset($whereNP["sejour.sortie"]);
      }
      $where["sejour.entree"]   = "= affectation.entree";
      $whereNP["sejour.entree"] = "BETWEEN '$limit1' AND '$limit2'";
    }
    else {
      unset($where["affectation.entree"]);
      $where["affectation.sortie"] = "BETWEEN '$limit1' AND '$limit2'";
      if (isset($where["sejour.entree"])) {
        unset($where["sejour.entree"]);
      }
      if (isset($whereNP["sejour.entree"])) {
        unset($whereNP["sejour.entree"]);
      }
      $where["sejour.sortie"]   = "= affectation.sortie";
      $whereNP["sejour.sortie"] = "BETWEEN '$limit1' AND '$limit2'";
    }

    $_liste["place"]     = $affectation->countList($where, null, $ljoin);
    $_liste["non_place"] = $sejour->countList($whereNP, null, $ljoinNP);
  }
}

$smarty = new CSmartyDP();

$smarty->assign("presents", $presents);
$smarty->assign("presentsNP", $presentsNP);
$smarty->assign("mouvements", $mouvements);
$smarty->assign("dep_entrants", $dep_entrants);
$smarty->assign("dep_sortants", $dep_sortants);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("functions", $functions);
$smarty->assign("function_id", $function_id);
$smarty->assign("type_hospi", $type_hospi);
$smarty->assign("vue", $vue);
$smarty->assign("date", $date);
$smarty->assign("mode", $mode);
$smarty->assign("hour_instantane", $hour_instantane);
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("prestations_journalieres", CPrestationJournaliere::loadCurrentList());
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("by_secteur", $by_secteur);

$smarty->display("edit_sorties");
