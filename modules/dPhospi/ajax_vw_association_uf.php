<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

// Récupération des paramètres
$curr_affectation_id = CView::get("curr_affectation_id", "ref class|CAffectation");
$lit_id              = CView::get("lit_id", "ref class|CLit");
$see_validate        = CView::get("see_validate", "bool default|1");

CView::checkin();

$mediuser = CMediusers::get();
$mediuser->loadRefFunction();

$lit = new CLit();
$lit->load($lit_id);

$chambre = $lit->loadRefChambre();
$service = $chambre->loadRefService();

$affectation = new CAffectation();
$affectation->load($curr_affectation_id);
$affectation->loadRefUfs();
$sejour         = $affectation->loadRefSejour();
$praticien      = $sejour->loadRefPraticien();
$prat_placement = $affectation->loadRefPraticien();
$function       = $praticien->loadRefFunction();

if (!$service->_id) {
  $affectation->loadRefService();
  $lit = new CLit();
  $lit->loadRefChambre();
  $lit->_ref_chambre->_ref_service = $affectation->_ref_service;
  $service                         = $lit->_ref_chambre->_ref_service;
}

$ufs_medicale                = array();
$ufs_soins                   = array();
$ufs_hebergement             = array();
$uf_sejour_hebergement       = array();
$uf_sejour_medicale          = array();
$uf_sejour_soins             = array();
$ufs_praticien_sejour_second = array();
$ufs_function_second         = array();
$ufs_prat_placement          = array();

$auf = new CAffectationUniteFonctionnelle();

// UFs de séjour
$ufs_sejour = array();

$uf = $sejour->loadRefUFHebergement();
if ($uf->_id) {
  $uf_sejour_hebergement[$uf->_id] = $uf;
  $ufs_hebergement[$uf->_id]       = $uf;
}

$uf = $sejour->loadRefUFMedicale();
if ($uf->_id) {
  $uf_sejour_medicale[$uf->_id] = $uf;
  $ufs_medicale[$uf->_id]       = $uf;
}

$uf = $sejour->loadRefUFSoins();
if ($uf->_id) {
  $uf_sejour_soins[$uf->_id] = $uf;
  $ufs_soins[$uf->_id]       = $uf;
}

/* @var CAffectationUniteFonctionnelle $_auf */
/* @var CAffectationUfSecondaire $_aff_uf_secondaire */

$date_entree = CMbDT::date($affectation->entree);
$date_sortie = CMbDT::date($affectation->sortie);

// UFs de services
$ufs_service = array("hebergement" => array(), "soins" => array());
if ($service->_id) {
  foreach ($auf->loadListFor($service) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ($uf->type_sejour && $sejour->type != $uf->type_sejour) {
      continue;
    }
    if ((!$uf->date_debut || $uf->date_debut <= $date_entree) && (!$uf->date_fin || $uf->date_fin >= $date_sortie)) {
      $ufs_service[$uf->type][$uf->_id] = $uf;
      if ($uf->type == "soins") {
        $ufs_soins      [$uf->_id] = $uf;
      }
      else {
        $ufs_hebergement[$uf->_id] = $uf;
      }
    }
  }
}

// UFs de chambre
$ufs_chambre = array("hebergement" => array(), "soins" => array());
if ($chambre->_id) {
  foreach ($auf->loadListFor($chambre) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ($uf->type_sejour && $sejour->type != $uf->type_sejour) {
      continue;
    }
    if ((!$uf->date_debut || $uf->date_debut <= $date_entree) && (!$uf->date_fin || $uf->date_fin >= $date_sortie)) {
      $ufs_chambre[$uf->type][$uf->_id] = $uf;
      if ($uf->type == "soins") {
        $ufs_soins      [$uf->_id] = $uf;
      }
      else {
        $ufs_hebergement[$uf->_id] = $uf;
      }
    }
  }
}

// UFs de lit
$ufs_lit = array("hebergement" => array(), "soins" => array());
if ($lit->_id) {
  foreach ($auf->loadListFor($lit) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ($uf->type_sejour && $sejour->type != $uf->type_sejour) {
      continue;
    }
    if ((!$uf->date_debut || $uf->date_debut <= $date_entree) && (!$uf->date_fin || $uf->date_fin >= $date_sortie)) {
      $ufs_lit[$uf->type][$uf->_id] = $uf;
      if ($uf->type == "soins") {
        $ufs_soins      [$uf->_id] = $uf;
      }
      else {
        $ufs_hebergement[$uf->_id] = $uf;
      }
    }
  }
}

// UFs de fonction
$ufs_function = array();
foreach ($auf->loadListFor($function) as $_auf) {
  $uf = $_auf->loadRefUniteFonctionnelle();
  if ((!$uf->type_sejour || $uf->type_sejour == $sejour->type)
    && (!$uf->date_debut || $uf->date_debut <= $date_entree)
    && (!$uf->date_fin || $uf->date_fin >= $date_sortie)
  ) {
    $ufs_function   [$uf->_id] = $uf;
    $ufs_medicale   [$uf->_id] = $uf;
  }
  $affs_uf_secondaire = $function->loadBackRefs("ufs_secondaires");
  foreach ($affs_uf_secondaire as $_aff_uf_secondaire) {
    $_uf_secondaire = $_aff_uf_secondaire->loadRefUniteFonctionnelle();
    if ($_uf_secondaire->type_sejour && $sejour->type != $_uf_secondaire->type_sejour) {
      continue;
    }
    $ufs_function_second [$_uf_secondaire->_id] = $_uf_secondaire;
    $ufs_medicale [$_uf_secondaire->_id]        = $_uf_secondaire;
  }
}

// UFs de praticien
$ufs_praticien_sejour = array();
$ufs_prat_placement   = array();
foreach ($auf->loadListFor($praticien) as $_auf) {
  $uf = $_auf->loadRefUniteFonctionnelle();
  if ((!$uf->type_sejour || $uf->type_sejour == $sejour->type)
    && (!$uf->date_debut || $uf->date_debut <= $date_entree)
    && (!$uf->date_fin || $uf->date_fin >= $date_sortie)
  ) {
    $ufs_praticien_sejour [$uf->_id] = $uf;
    $ufs_medicale  [$uf->_id]        = $uf;
  }
  $affs_uf_secondaire = $praticien->loadBackRefs("ufs_secondaires");
  foreach ($affs_uf_secondaire as $_aff_uf_secondaire) {
    $_uf_secondaire = $_aff_uf_secondaire->loadRefUniteFonctionnelle();
    if ($_uf_secondaire->type_sejour && $sejour->type != $_uf_secondaire->type_sejour) {
      continue;
    }
    $ufs_praticien_sejour_second [$_uf_secondaire->_id] = $_uf_secondaire;
    $ufs_medicale [$_uf_secondaire->_id]                = $_uf_secondaire;
  }
}

if ($prat_placement->_id) {
  foreach ($auf->loadListFor($prat_placement) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ((!$uf->type_sejour || $uf->type_sejour == $sejour->type)
      && (!$uf->date_debut || $uf->date_debut <= $date_entree)
      && (!$uf->date_fin || $uf->date_fin >= $date_sortie)
    ) {
      $ufs_prat_placement [$uf->_id] = $uf;
      $ufs_medicale  [$uf->_id]      = $uf;
    }
    $affs_uf_secondaire = $prat_placement->loadBackRefs("ufs_secondaires");
    foreach ($affs_uf_secondaire as $_aff_uf_secondaire) {
      $_uf_secondaire = $_aff_uf_secondaire->loadRefUniteFonctionnelle();
      if ($_uf_secondaire->type_sejour && $sejour->type != $_uf_secondaire->type_sejour) {
        continue;
      }
      $ufs_prat_placement_second [$_uf_secondaire->_id] = $_uf_secondaire;
      $ufs_medicale  [$_uf_secondaire->_id]             = $_uf_secondaire;
    }
  }
}
else {
  $prat_placement = $praticien;
}

$user       = new CMediusers();
$praticiens = array();
if ($affectation->_ref_uf_medicale->_id) {
  $praticiens = CAffectation::loadPraticiensUfMedicale($affectation->_ref_uf_medicale->_id);
}
else {
  $praticiens = $user->loadPraticiens(PERM_EDIT, $function->_id);
}

foreach ($praticiens as $prat) {
  $prat->loadRefFunction();
  $prat->loadRefUfMedicale();
  foreach ($auf->loadListFor($prat) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ($uf->type_sejour && $sejour->type != $uf->type_sejour) {
      continue;
    }
    $ufs_medicale[$uf->_id] = $uf;
  }
}

$ufs_medicale    = array_reverse($ufs_medicale);
$ufs_soins       = array_reverse($ufs_soins);
$ufs_hebergement = array_reverse($ufs_hebergement);

CUniteFonctionnelle::getAlertesUFs($affectation);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("affectation", $affectation);
$smarty->assign("sejour", $sejour);
$smarty->assign("service", $service);
$smarty->assign("chambre", $chambre);
$smarty->assign("lit", $lit);
$smarty->assign("function", $function);
$smarty->assign("praticien", $praticien);
$smarty->assign("prat_placement", $prat_placement);
$smarty->assign("praticiens", $praticiens);

$smarty->assign("uf_sejour_hebergement", $uf_sejour_hebergement);
$smarty->assign("uf_sejour_soins", $uf_sejour_soins);
$smarty->assign("uf_sejour_medicale", $uf_sejour_medicale);
$smarty->assign("ufs_service", $ufs_service);
$smarty->assign("ufs_chambre", $ufs_chambre);
$smarty->assign("ufs_lit", $ufs_lit);
$smarty->assign("ufs_function", $ufs_function);
$smarty->assign("ufs_praticien_sejour", $ufs_praticien_sejour);
$smarty->assign("ufs_prat_placement", $ufs_prat_placement);
$smarty->assign("ufs_medicale", $ufs_medicale);
$smarty->assign("ufs_soins", $ufs_soins);
$smarty->assign("ufs_hebergement", $ufs_hebergement);

$smarty->assign("ufs_praticien_sejour_second", $ufs_praticien_sejour_second);
$smarty->assign("ufs_function_second", $ufs_function_second);
$smarty->assign("ufs_prat_placement", $ufs_prat_placement);

$smarty->assign("see_validate", $see_validate);

$smarty->display("inc_vw_affectation_uf.tpl");
