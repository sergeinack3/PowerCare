<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$date = CView::get("date", "date default|now", true);

CView::checkin();

$group = CGroups::loadCurrent();

$date_min = CMbDT::date("-" . CAppUI::conf("maternite CGrossesse min_check_terme", $group) . " DAYS", $date);
$date_max = CMbDT::date("+" . CAppUI::conf("maternite CGrossesse max_check_terme", $group) . " DAYS", $date);

$where = array(
  "grossesse.terme_prevu" => "BETWEEN '$date_min' AND '$date_max'",
  "grossesse.group_id"    => "= '$group->_id'",
  "grossesse.active"      => "= '1'",
  "sejour.annule"         => "= '0'",
  "sejour.entree_reelle"  => "IS NOT NULL",
);

// Il peut y avoir des séjours antérieurs liés à la grossesse.
// Nécessité de cibler un séjour dont l'entrée est dans le même intervalle que celui de la grossesse
$where[] = "DATE(sejour.entree_prevue) BETWEEN '$date_min' AND '$date_max'";

$ljoin = array(
  "grossesse" => "sejour.grossesse_id = grossesse.grossesse_id"
);

$sejour = new CSejour();

// Patientes présentes
$sejours = $sejour->loadList($where, "DATE(sejour.entree_reelle), grossesse.terme_prevu", null, "sejour.sejour_id", $ljoin, null, null, false);

// Puis par terme prévu
$where["sejour.entree_reelle"] = "IS NULL";

/** @var CSejour[] $sejours */
$sejours = $sejours + $sejour->loadList($where, "grossesse.terme_prevu", null, "sejour.sejour_id", $ljoin);

CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massCountBackRefs($sejours, "operations");

$grossesses         = CStoredObject::massLoadFwdRef($sejours, "grossesse_id");
$sejours_grossesses = CStoredObject::massLoadBackRefs($grossesses, "sejours", "entree_prevue DESC");
CStoredObject::massLoadFwdRef($sejours_grossesses, "praticien_id");

$consultations_grossesses = CStoredObject::massLoadBackRefs($grossesses, "consultations", "date DESC, heure DESC", null,
  array("plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"));
$plages                   = CStoredObject::massLoadFwdRef($consultations_grossesses, "plageconsult_id");
CStoredObject::massLoadFwdRef($plages, "chir_id");

$naissances     = CStoredObject::massLoadBackRefs($grossesses, "naissances");
$sejours_enfant = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
CStoredObject::massLoadFwdRef($sejours_enfant, "patient_id");

foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefsOperations();

  $grossesse = $_sejour->loadRefGrossesse();

  $grossesse->_praticiens = array();

  foreach ($grossesse->loadRefsSejours() as $_sejour) {
    $grossesse->_praticiens[] = $_sejour->loadRefPraticien();
  }

  foreach ($grossesse->loadRefsConsultations() as $_consult) {
    $grossesse->_praticiens[] = CMediusers::get($_consult->loadRefPlageConsult()->chir_id);
  }

  foreach ($grossesse->loadRefsNaissances() as $_naissance) {
    $_naissance->loadRefSejourEnfant()->loadRefPatient();
  }

  foreach ($grossesse->_praticiens as $_praticien) {
    $_praticien->loadRefFunction();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours", $sejours);
$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);

$smarty->display("inc_tdb_termes_prevus.tpl");
