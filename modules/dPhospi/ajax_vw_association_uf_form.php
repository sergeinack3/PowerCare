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
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

// Récupération des paramètres
$object_guid       = CView::get("object_guid", "str");
$prat_id           = CView::get("prat_id", "ref class|CMediusers");
$function_id       = CView::get("function_id", "ref class|CFunctions");
$service_id        = CView::get("service_id", "ref class|CService");
$entree_prevue     = CView::get("entree", "date");
$sortie_prevue     = CView::get("sortie", "date");
$uf_hebergement_id = CView::get("uf_hebergement_id", "ref class|CUniteFonctionnelle");
$uf_soins_id       = CView::get("uf_soins_id", "ref class|CUniteFonctionnelle");
$uf_medicale_id    = CView::get("uf_medicale_id", "ref class|CUniteFonctionnelle");
$type              = CView::get("type", "enum list|" . implode("|", CSejour::$types));
CView::checkin();

/* @var CProtocole|CSejour $object */
$object = CMbObject::loadFromGuid($object_guid);
$object->loadRefUfs();

if ($object instanceof CSejour) {
  $object->loadRefsAffectations();
}

//Récupération du service
$service = new CService();
$service->load($service_id);

//Récupréation du praticien et de la fonction
$praticien = new CMediusers();
$function  = new CFunctions();
if ($function_id) {
  $function->load($function_id);
}
else {
  $praticien->load($prat_id);
}

//Filtrage sur les dates limites
$entree = $sortie = null;
if ($object instanceof CSejour) {
  if ($entree_prevue) {
    $entree = $entree_prevue . " 00:00:00";
    $sortie = $sortie_prevue . " 23:59:00";
  }
}

$ufs_medicale                = array();
$ufs_soins                   = array();
$ufs_hebergement             = array();
$uf_sejour_hebergement       = array();
$uf_sejour_medicale          = array();
$uf_sejour_soins             = array();
$ufs_praticien_sejour_second = array();
$ufs_function_second         = array();

$auf = new CAffectationUniteFonctionnelle();

// UFs de séjour
$ufs_sejour = array();

$uf = $object->loadRefUFHebergement();
if ($uf->_id) {
  $uf_sejour_hebergement[$uf->_id] = $uf;
  $ufs_hebergement[$uf->_id]       = $uf;
}

$uf = $object->loadRefUFMedicale();
if ($uf->_id) {
  $uf_sejour_medicale[$uf->_id] = $uf;
  $ufs_medicale[$uf->_id]       = $uf;
}

$uf = $object->loadRefUFSoins();
if ($uf->_id) {
  $uf_sejour_soins[$uf->_id] = $uf;
  $ufs_soins[$uf->_id]       = $uf;
}

/* @var CAffectationUniteFonctionnelle $_auf */
/* @var CAffectationUfSecondaire $_aff_uf_secondaire */

// UFs de services
$ufs_service = array("hebergement" => array(), "soins" => array());
if ($service->_id) {
  foreach ($auf->loadListFor($service) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ($uf->type_sejour && $type && $uf->type_sejour != $type) {
      continue;
    }
    if ((!$uf->date_debut || !$sortie || $uf->date_debut < $sortie) && (!$uf->date_fin || !$entree || $uf->date_fin > $entree)) {
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

// UFs de fonction
$ufs_function = array();
if ($function->_id) {
  foreach ($auf->loadListFor($function) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ((!$uf->type_sejour || !$type || $uf->type_sejour == $type)
      && (!$uf->date_debut || !$sortie || $uf->date_debut < $sortie)
      && (!$uf->date_fin || !$entree || $uf->date_fin > $entree)
    ) {
      $ufs_function   [$uf->_id] = $uf;
      $ufs_medicale   [$uf->_id] = $uf;
    }
    $affs_uf_secondaire = $function->loadBackRefs("ufs_secondaires");
    foreach ($affs_uf_secondaire as $_aff_uf_secondaire) {
      $_uf_secondaire = $_aff_uf_secondaire->loadRefUniteFonctionnelle();
      if ($_uf_secondaire->type_sejour && $type && $_uf_secondaire->type_sejour != $type) {
        continue;
      }
      $ufs_function_second [$_uf_secondaire->_id] = $_uf_secondaire;
      $ufs_medicale [$_uf_secondaire->_id]        = $_uf_secondaire;
    }
  }
}

// UFs de praticien
$ufs_praticien_sejour = array();
if ($praticien->_id) {
  foreach ($auf->loadListFor($praticien) as $_auf) {
    $uf = $_auf->loadRefUniteFonctionnelle();
    if ((!$uf->type_sejour || !$type || $uf->type_sejour == $type)
      && (!$uf->date_debut || !$sortie || $uf->date_debut < $sortie)
      && (!$uf->date_fin || !$entree || $uf->date_fin > $entree)
    ) {
      $ufs_praticien_sejour [$uf->_id] = $uf;
      $ufs_medicale  [$uf->_id]        = $uf;
    }
    $affs_uf_secondaire = $praticien->loadBackRefs("ufs_secondaires");
    foreach ($affs_uf_secondaire as $_aff_uf_secondaire) {
      $_uf_secondaire = $_aff_uf_secondaire->loadRefUniteFonctionnelle();
      if ($_uf_secondaire->type_sejour && $type && $_uf_secondaire->type_sejour != $type) {
        continue;
      }
      $ufs_praticien_sejour_second [$_uf_secondaire->_id] = $_uf_secondaire;
      $ufs_medicale [$_uf_secondaire->_id]                = $_uf_secondaire;
    }
  }
}

$ufs_medicale    = array_reverse($ufs_medicale);
$ufs_soins       = array_reverse($ufs_soins);
$ufs_hebergement = array_reverse($ufs_hebergement);

$object->uf_hebergement_id = $uf_hebergement_id;
$object->uf_soins_id       = $uf_soins_id;
$object->uf_medicale_id    = $uf_medicale_id;

CUniteFonctionnelle::getAlertesUFs($object);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("service", $service);
$smarty->assign("function", $function);
$smarty->assign("praticien", $praticien);

$smarty->assign("uf_sejour_hebergement", $uf_sejour_hebergement);
$smarty->assign("uf_sejour_soins", $uf_sejour_soins);
$smarty->assign("uf_sejour_medicale", $uf_sejour_medicale);
$smarty->assign("ufs_service", $ufs_service);
$smarty->assign("ufs_function", $ufs_function);
$smarty->assign("ufs_praticien_sejour", $ufs_praticien_sejour);
$smarty->assign("ufs_medicale", $ufs_medicale);
$smarty->assign("ufs_soins", $ufs_soins);
$smarty->assign("ufs_hebergement", $ufs_hebergement);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($object));

$smarty->assign("ufs_praticien_sejour_second", $ufs_praticien_sejour_second);
$smarty->assign("ufs_function_second", $ufs_function_second);

$smarty->display("inc_vw_affectation_uf_form");
