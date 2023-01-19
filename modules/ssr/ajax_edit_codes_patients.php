<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CActeCdARR;
use Ox\Mediboard\Ssr\CActeCsARR;
use Ox\Mediboard\Ssr\CActePrestationSSR;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$evenement_id = CView::get("evenement_id", "ref class|CEvenementSSR");
CView::checkin();

/** @var CEvenementSSR[] $evenements */
$evenement = CEvenementSSR::findOrFail($evenement_id);
$evenement->loadRefSejour()->loadRefPatient();
$evenement->loadRefsActes();
$evenements = $evenement->loadRefSeanceCollective()->loadRefsEvenementsSeance();
unset($evenements[$evenement_id]);

foreach ($evenements as $_evenement) {
  if ($_evenement->realise || $_evenement->annule) {
    unset($evenements[$_evenement->_id]);
  }
}

foreach (array("actes_cdarr", "actes_csarr", "prestas_ssr") as $_type_code) {
  CStoredObject::massLoadBackRefs($evenements, $_type_code);
}
$sejours = CStoredObject::massLoadFwdRef($evenements, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");

$codes_by_evt_and_type = array();
foreach ($evenements as $_evenement) {
  $_evenement->loadRefsActes();
  $_evenement->loadRefSejour()->loadRefPatient();

  $codes_by_evt_and_type[$_evenement->_id] = array();
  foreach ($_evenement->_ref_actes as $_type => $_actes) {
    $codes_by_evt_and_type[$_evenement->_id][$_type] = array();
    foreach ($_actes as $_acte) {
      if (!isset($codes_by_evt_and_type[$_evenement->_id][$_type][$_acte->code])) {
        $codes_by_evt_and_type[$_evenement->_id][$_type][$_acte->code] = array();
      }
      $codes_by_evt_and_type[$_evenement->_id][$_type][$_acte->code][$_acte->_id] = $_acte;
    }
  }
}

//Utilisation uniquement des types d'actes paramétrés
switch (CAppUI::gconf("ssr general use_acte_presta")) {
  case "presta":
    $evenement_codes_by_type = array("prestas" => array());
    break;
  case "csarr":
    $evenement_codes_by_type = array("csarr" => array(), "cdarr" => array());
    break;
  default:
    $evenement_codes_by_type = array();
    break;
}

$keys_types = array_keys($evenement_codes_by_type);
foreach ($evenement->_ref_actes as $_type => $_actes) {
  if (!in_array($_type, $keys_types)) {
    unset($evenement->_ref_actes[$_type]);
    continue;
  }
  foreach ($_actes as $_acte) {
    if (!isset($evenement_codes_by_type[$_type])) {
      $evenement_codes_by_type[$_type] = array();
    }
    if (!isset($evenement_codes_by_type[$_type][$_acte->code])) {
      $evenement_codes_by_type[$_type][$_acte->code] = array();
    }
    $evenement_codes_by_type[$_type][$_acte->code][$_acte->_id] = $_acte;
  }
}

//Tableau contenant les différents types d'actes sous forme d'objets(pour le nom des champs de formulaire)
$types_acte = array();
foreach ($evenement->_ref_actes as $_type_acte => $_actes) {
  if ($_type_acte === "prestas") {
    $object = new CActePrestationSSR();
    $object->type = "presta_ssr";
  }
  elseif ($_type_acte === "csarr") {
    $object = new CActeCsARR();
  }
  elseif ($_type_acte === "cdarr") {
    $object = new CActeCdARR();
  }
  $types_acte[$_type_acte] = $object;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement", $evenement);
$smarty->assign("evenement_codes_by_type", $evenement_codes_by_type);
$smarty->assign("evenements", $evenements);
$smarty->assign("types_acte", $types_acte);
$smarty->assign("codes_by_evt_and_type", $codes_by_evt_and_type);
$smarty->display("inc_edit_codes_patients");