<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

$date = CValue::getOrSession("date");

// Chargement des praticiens
$med      = new CMediusers();
$listPrat = $med->loadPraticiens(PERM_READ);

$dateEntree = CMbDT::dateTime("23:59:00", $date);
$dateSortie = CMbDT::dateTime("00:01:00", $date);

$hierEntree = CMbDT::date("- 1 day", $dateEntree);
$hierEntree = CMbDT::dateTime("23:59:00", $hierEntree);

// Chargement des services
$service                    = new CService();
$whereServices              = array();
$whereServices["group_id"]  = "= '" . CGroups::loadCurrent()->_id . "'";
$whereServices["cancelled"] = "= '0'";
$services                   = $service->loadListWithPerms(PERM_READ, $whereServices, "nom");

// Initialisations
$totalHospi       = 0;
$totalAmbulatoire = 0;
$totalMedecin     = 0;
$total_prat       = array();
foreach ($listPrat as $key => $prat) {
  $totalPrat[$prat->_id]["prat"]  = $prat;
  $totalPrat[$prat->_id]["hospi"] = 0;
  $totalPrat[$prat->_id]["ambu"]  = 0;
  $totalPrat[$prat->_id]["total"] = 0;
}

$sejour                              = new CSejour;
$whereSejour                         = array();
$whereSejour["sejour.group_id"]      = "= '" . CGroups::loadCurrent()->_id . "'";
$whereSejour["sejour.entree_reelle"] = "IS NOT NULL";
$whereSejour[]                       = "`sejour`.`entree_reelle` <= '$dateEntree'";
$whereSejour["sejour.sortie"]        = ">= '$dateSortie'";
$whereSejour["sejour.annule"]        = "= '0'";

/** @var CSejour[] $listSejours */
$listSejours = $sejour->loadList($whereSejour);

// Stockage des informations liées au praticiens
foreach ($listSejours as $_sejour) {
  $_sejour->loadRefPraticien(1);

  foreach ($listPrat as $key => $_prat) {
    // Cas d'un sejour de type Ambulatoire
    if ($_prat->_id == $_sejour->_ref_praticien->_id && $_sejour->type == "ambu") {
      $totalPrat[$_prat->_id]["ambu"]++;
      $totalAmbulatoire++;
    }

    // Autres cas
    if ($_prat->_id == $_sejour->_ref_praticien->_id && $_sejour->type == "comp") {
      $totalPrat[$_prat->_id]["hospi"]++;
      $totalHospi++;
    }

    // Total des hospitalisations (Ambu + autres)
    if ($_prat->_id == $_sejour->_ref_praticien->_id) {
      $totalPrat[$_prat->_id]["total"] = $totalPrat[$_prat->_id]["ambu"] + $totalPrat[$_prat->_id]["hospi"];

      if ($totalPrat[$_prat->_id]["total"]) {
          $totalMedecin++;
      }
    }
  }
}

// Calcul des patients par service

// Calcul du nombre d'affectations a la date $date
$affectation = new CAffectation();
$whereAffect = array();
$ljoin       = array();

$whereAffect["affectation.entree"]    = "<= '$dateEntree'";
$whereAffect["affectation.sortie"]    = ">= '$dateSortie'";
$whereAffect["affectation.sejour_id"] = "!= '0'";
$whereAffect["sejour.group_id"]       = "= '" . CGroups::loadCurrent()->_id . "'";

$ljoin["sejour"] = "sejour.sejour_id = affectation.sejour_id";

$groupAffect = "affectation.sejour_id";

$list_affectations = $affectation->loadList($whereAffect, null, null, $groupAffect, $ljoin);
$total_service     = array();

foreach ($services as $_service) {
  $total_service[$_service->_id]["service"] = $_service;
  $total_service[$_service->_id]["total"]   = 0;
}

CStoredObject::massLoadFwdRef($list_affectations, 'sejour_id');

foreach ($list_affectations as $key => $_affectation) {
  // Chargement des références nécessaire pour parcourir les affectations
  $_affectation->loadRefSejour();

  // Stockage des informations liées aux services
  foreach ($services as $_service) {
    if ($_service->_id == $_affectation->service_id && !$_affectation->_ref_sejour->annule) {
      $total_service[$_service->_id]["total"]++;
    }
  }
}

$date_debut = CMbDT::dateTime("00:01:00", $date);
$date_fin   = CMbDT::dateTime("23:59:00", $date);

// present du jour
$sejourJour                 = new CSejour();
$whereJour                  = array();
$whereJour["sejour.entree"] = "<= '$date_fin'";
$whereJour["sejour.sortie"] = ">= '$date_debut'";
$whereJour["annule"]        = "= '0'";
$whereJour["type"]          = "= 'comp'";
$countPresentJour           = $sejourJour->countList($whereJour);

// present de la veille
$sejourVeille                 = new CSejour();
$whereVeille                  = array();
$whereVeille["sejour.entree"] = "<= '$hierEntree'";
$whereVeille["sejour.sortie"] = ">= '$dateSortie'";
$whereVeille["annule"]        = "= '0'";
$whereVeille["type"]          = "= 'comp'";
$countPresentVeille           = $sejourVeille->countList($whereVeille);

// entree du jour
$sejourEntreeJour             = new CSejour();
$whereEntree                  = array();
$whereEntree["sejour.entree"] = "BETWEEN '$date_debut' AND '$date_fin'";
$whereEntree["annule"]        = "= '0'";
$whereEntree["type"]          = "= 'comp'";
$countEntreeJour              = $sejourEntreeJour->countList($whereEntree);

// sorties du jour
$sejourSortieJour             = new CSejour();
$whereSortie                  = array();
$whereSortie["sejour.sortie"] = "BETWEEN '$date_debut' AND '$date_fin'";
$whereSortie["annule"]        = "= '0'";
$whereSortie["type"]          = "= 'comp'";
$countSortieJour              = $sejourSortieJour->countList($whereSortie);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("totalHospi", $totalHospi);
$smarty->assign("totalMedecin", $totalMedecin);
$smarty->assign("totalAmbulatoire", $totalAmbulatoire);
$smarty->assign("services", $services);
$smarty->assign("list_affectations", $list_affectations);
$smarty->assign("total_service", $total_service);
$smarty->assign("countPresentVeille", $countPresentVeille);
$smarty->assign("countSortieJour", $countSortieJour);
$smarty->assign("countEntreeJour", $countEntreeJour);
$smarty->assign("countPresentJour", $countPresentJour);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("totalPrat", $totalPrat);

$smarty->display("vw_rapport.tpl");

