<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CReplacement;
use Ox\Mediboard\Ssr\CTechnicien;

CCanDo::checkRead();

// Plateaux disponibles
$show_cancelled_services = CValue::getOrSession("show_cancelled_services");
$technicien_id           = CValue::get("technicien_id");
$service_id              = CValue::getOrSession("service_id");
$date                    = CValue::getOrSession("date", CMbDT::date());

$technicien = new CTechnicien();
$technicien->load($technicien_id);
$technicien->loadRefKine();
$kine_id = $technicien->_ref_kine->_id;

$sejours  = CBilanSSR::loadSejoursSSRfor($technicien_id, $date, $show_cancelled_services);
$services = array();

$all_sejours = array();

$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

foreach ($sejours as $_sejour) {
  // Filtre sur service
  $service                 = $_sejour->loadFwdRef("service_id");
  $services[$service->_id] = $service;
  if (!$technicien_id && $service_id && $_sejour->service_id != $service_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  $all_sejours[] = $_sejour;
  $_sejour->checkDaysRelative($date);
  $_sejour->loadRefPatient(1)->updateBMRBHReStatus($_sejour);
  $_sejour->loadRefBilanSSR()->getDateEnCours($date);
}

// Blows id keys
CMbArray::pluckSort($sejours, SORT_ASC, "_ref_patient", "nom");

// Ajustements services
$service = new CService;
$service->load($service_id);
$services[$service->_id] = $service;
unset($services[""]);

// Remplacements
$replacement  = new CReplacement;
$replacements = $replacement->loadListFor($kine_id, $date);

foreach ($replacements as $_replacement) {
  // Détails des séjours remplacés
  $_replacement->loadRefSejour();
  $sejour =& $_replacement->_ref_sejour;
  if ($sejour->sortie < $date) {
    unset($replacements[$_replacement->_id]);
    continue;
  }

  $all_sejours[] = $sejour;
  $sejour->checkDaysRelative($date);
  $sejour->loadRefPatient(1);
  $sejour->loadRefBilanSSR();

  // Détail sur le congé
  $_replacement->loadRefConge()->loadRefUser()->loadRefFunction();
}

// Chargement du séjour potentiellement remplacé
$technicien->loadRefCongeDate($date);
$conge = $technicien->_ref_conge_date;
if ($conge->_id) {
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefReplacement($conge->_id);
  }
}

// Nombre de séjours
$sejours_count = count($sejours) + count($replacements);

// Couleurs
$colors = CColorLibelleSejour::loadAllFor(CMbArray::pluck($all_sejours, "libelle"));

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("show_cancelled_services", $show_cancelled_services);
$smarty->assign("technicien_id", $technicien_id);
$smarty->assign("service_id", $service_id);
$smarty->assign("colors", $colors);
$smarty->assign("sejours", $sejours);
$smarty->assign("sejours_count", $sejours_count);
$smarty->assign("services", $services);
$smarty->assign("replacements", $replacements);
$smarty->display("inc_sejours_technicien");
