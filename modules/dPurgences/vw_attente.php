<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$date = CView::get("date", "date default|now", true);
CView::checkin();

$group = CGroups::loadCurrent();

$groupby = "sejour_id";

// Attente radio
$sejour_radio = new CSejour();
$where        = array();
$ljoin        = array();
$ljoin["rpu"] = "sejour.sejour_id = rpu.sejour_id";

$where["sejour.entree"]   = "BETWEEN '$date 00:00:00' AND '$date 23:59:59'";
$where["sejour.type"]     = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
$where["sejour.group_id"] = "= '$group->_id'";
$ljoin["rpu_attente"]     = "rpu_attente.rpu_id = rpu.rpu_id";
if (CAppUI::gconf("dPurgences CRPU imagerie_etendue")) {
  $ljoin["patients"]    = "sejour.patient_id = patients.patient_id";
  $ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";
  $ljoin["service"]     = "affectation.service_id = service.service_id";
  $where[]              = "service.radiologie = '1'";
}
else {
  $where[] = "(rpu_attente.type_attente = 'radio') AND (rpu_attente.depart IS NOT NULL AND rpu_attente.retour IS NULL)";
}

$sejours_ids_radio = $sejour_radio->loadIds($where, null, null, $groupby, $ljoin);

// Attente biologie
$sejour_bio   = new CSejour();
$where        = array();
$ljoin        = array();
$ljoin["rpu"] = "sejour.sejour_id = rpu.sejour_id";

$where["sejour.entree"]   = "BETWEEN '$date 00:00:00' AND '$date 23:59:59'";
$where["sejour.type"]     = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
$where["sejour.group_id"] = "= '$group->_id'";
$ljoin["rpu_attente"]     = "rpu_attente.rpu_id = rpu.rpu_id";
if (CAppUI::gconf("dPurgences CRPU imagerie_etendue")) {
  $ljoin["patients"]    = "sejour.patient_id = patients.patient_id";
  $ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";
  $ljoin["service"]     = "affectation.service_id = service.service_id";
  $where[]              = "(rpu_attente.type_attente = 'bio') AND (rpu_attente.depart IS NOT NULL AND rpu_attente.retour IS NULL)";
}
else {
  $where[] = "(rpu_attente.type_attente = 'bio') AND (rpu_attente.depart IS NOT NULL AND rpu_attente.retour IS NULL)";
}

$sejours_ids_bio = $sejour_radio->loadIds($where, null, null, $groupby, $ljoin);

// Attente specialiste
$sejour_specialiste = new CSejour();
$where              = array();
$ljoin              = array();
$ljoin["rpu"]       = "sejour.sejour_id = rpu.sejour_id";

$where["sejour.entree"]   = "BETWEEN '$date 00:00:00' AND '$date 23:59:59'";
$where["sejour.type"]     = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
$where["sejour.group_id"] = "= '$group->_id'";
$ljoin["rpu_attente"]     = "rpu_attente.rpu_id = rpu.rpu_id";
if (CAppUI::gconf("dPurgences CRPU imagerie_etendue")) {
  $ljoin["patients"]    = "sejour.patient_id = patients.patient_id";
  $ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";
  $ljoin["service"]     = "affectation.service_id = service.service_id";
  $where[]              = "(rpu_attente.type_attente = 'specialiste') AND (rpu_attente.depart IS NOT NULL AND rpu_attente.retour IS NULL)";
}
else {
  $where[] = "(rpu_attente.type_attente = 'specialiste') AND (rpu_attente.depart IS NOT NULL AND rpu_attente.retour IS NULL)";
}

$sejours_ids_specialiste = $sejour_radio->loadIds($where, null, null, $groupby, $ljoin);

// Chargement des urgences prises en charge
$sejour                = new CSejour();
$where                 = array();
$sejours_ids_radio_bio = array_merge($sejours_ids_radio, $sejours_ids_bio);
$sejours_ids           = array_merge($sejours_ids_specialiste, $sejours_ids_radio_bio);

$where["sejour_id"] = CSQLDataSource::prepareIn($sejours_ids);
/** @var CSejour[] $listSejours */
$listSejours = $sejour->loadList($where);
CSejour::massLoadNDA($listSejours);

$patients = CStoredObject::massLoadFwdRef($listSejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CPatient::massLoadIPP($patients);

CStoredObject::massLoadFwdRef($listSejours, "praticien_id");

foreach ($listSejours as &$_sejour) {
  $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
  $_sejour->loadRefPraticien();
  $_sejour->loadRefRPU();
  $_sejour->_ref_rpu->loadRefSejourMutation();
  $_sejour->_ref_rpu->loadRefsLastAttentes();

  CMbObject::massLoadFwdRef($_sejour->loadRefsAffectations("sortie ASC"), "service_id");
  foreach ($_sejour->_ref_affectations as $_affectation) {
    $_affectation->loadRefService();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listSejours", $listSejours);
$smarty->assign("date", $date);
$smarty->assign("today", CMbDT::date());
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));

$smarty->display("vw_attente");
