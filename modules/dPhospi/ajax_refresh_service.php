<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $g;

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
// Récupération des paramètres
$date       = CValue::getOrSession("date", CMbDT::dateTime());
$service_id = CValue::get("service_id");
$chambre_id = CValue::get("chambre_id");

$service = new CService();
if ($service_id) {
  $service->load($service_id);
}
elseif ($chambre_id) {
  $chambre = new CChambre();
  $chambre->load($chambre_id);
  $service = $chambre->loadRefService();
}

$ensemble_lits_charges = array();

$grille = array_fill(0, 10, array_fill(0, 10, 0));

$chambres = $service->loadRefsChambres(false);
foreach ($chambres as $ch) {
  /* @var CChambre $ch */
  $ch->loadRefEmplacement();
  if ($ch->_ref_emplacement->_id) {
    $ch->loadRefsLits();
    if (!count($ch->_ref_lits)) {
      unset($chambres[$ch->_id]);
      continue;
    }
    foreach ($ch->_ref_lits as $lit) {
      $ensemble_lits_charges[$lit->_id] = 0;
    }
    $grille[$ch->_ref_emplacement->plan_y][$ch->_ref_emplacement->plan_x] = $ch;
    $emplacement                                                          = $ch->_ref_emplacement;
    if ($emplacement->hauteur - 1) {
      for ($a = 0; $a <= $emplacement->hauteur - 1; $a++) {
        if ($emplacement->largeur - 1) {
          for ($b = 0; $b <= $emplacement->largeur - 1; $b++) {
            if ($b != 0) {
              unset($grille[$emplacement->plan_y + $a][$emplacement->plan_x + $b]);
            }
            elseif ($a != 0) {
              unset($grille[$emplacement->plan_y + $a][$emplacement->plan_x + $b]);
            }
          }
        }
        elseif ($a < $emplacement->hauteur - 1) {
          $c = $a + 1;
          unset($grille[$emplacement->plan_y + $c][$emplacement->plan_x]);
        }
      }
    }
    elseif ($emplacement->largeur - 1) {
      for ($b = 1; $b <= $emplacement->largeur - 1; $b++) {
        unset($grille[$emplacement->plan_y][$emplacement->plan_x + $b]);
      }
    }
  }
}

//Traitement des lignes vides
foreach ($grille as $j => $value) {
  $nb = 0;
  foreach ($value as $i => $valeur) {
    if ($valeur == "0") {
      if ($j == 0 || $j == 9) {
        $nb++;
      }
      else {
        if (!isset($grille[$j - 1][$i]) || $grille[$j - 1][$i] == "0" || !isset($grille[$j + 1][$i]) || $grille[$j + 1][$i] == "0") {
          $nb++;
        }
      }
    }
  }
  //suppression des lignes inutiles
  if ($nb == 10) {
    unset($grille[$j]);
  }
}

//Traitement des colonnes vides
for ($i = 0; $i < 10; $i++) {
  $nb    = 0;
  $total = 0;
  for ($j = 0; $j < 10; $j++) {
    $total++;
    if (!isset($grille[$j][$i]) || $grille[$j][$i] == "0") {
      if ($i == 0 || $i == 9) {
        $nb++;
      }
      else {
        if ((!isset($grille[$j][$i - 1]) || $grille[$j][$i - 1] == "0") || (!isset($grille[$j][$i + 1]) || $grille[$j][$i + 1] == "0")) {
          $nb++;
        }
      }
    }
  }
  //suppression des colonnes inutiles
  if ($nb == $total) {
    for ($a = 0; $a < 10; $a++) {
      unset($grille[$a][$i]);
    }
  }
}

$date_min = CMbDT::dateTime($date);
$date_max = CMbDT::dateTime("+1 day", $date_min);

$today       = CMbDT::date() === CMbDT::date($date);
$date_filter = $today ? CMbDT::dateTime() : CMbDT::date($date);

$listAff = array();

// Chargement des affectations ayant pour lit une chambre placées sur le plan
$affectation = new CAffectation();
$where       = array(
  "affectation.entree" => "<= '$date_max'",
  "affectation.sortie" => ">= '$date_min'",
  "affectation.lit_id" => CSQLDataSource::prepareIn(array_keys($ensemble_lits_charges), null)
);

if ($today) {
  $where[] = "'$date_filter' BETWEEN affectation.entree AND affectation.sortie";
}
else {
  $where[] = "'$date_filter' BETWEEN DATE(affectation.entree) AND affectation.sortie";
}

/** @var CAffectation[] $listAff */
$listAff = $affectation->loadList($where);

CAffectation::massUpdateView($listAff);
$sejours = CStoredObject::massLoadFwdRef($listAff, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
CSejour::massLoadLiaisonsForPrestation($sejours, "all", CMbDT::date($date) . " 00:00:00", CMbDT::date($date) . " 23:59:59");

foreach ($listAff as &$_aff) {
  $_aff->loadRefSejour();
  $_aff->_ref_sejour->checkDaysRelative($date);
  $_aff->_ref_sejour->loadRefPatient()->loadRefDossierMedical(false);
  $_aff->_ref_sejour->_ref_patient->loadRefsPatientHandicaps();
  $_aff->_ref_sejour->_ref_patient->updateBMRBHReStatus($_aff->_ref_sejour);
  $_aff->_ref_sejour->loadRefPrestation();
  $_aff->loadRefsAffectations();
}

$dossiers = CMbArray::pluck($listAff, "_ref_sejour", "_ref_patient", "_ref_dossier_medical");
CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("chambres_affectees", $listAff);
$smarty->assign("grille", $grille);
$smarty->assign("key", $service->_id);

$smarty->display("inc_plan_service");
