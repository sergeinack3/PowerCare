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
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$affectation_id = CView::get("affectation_id", "ref class|CAffectation");
$lit_id         = CView::get("lit_id", "ref class|CLit");
$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
$service_id     = CView::get("service_id", "ref class|CService");
$lit_id_origine = CView::get("lit_id_origine", "ref class|CLit");
$force          = CView::get("force", "bool default|0");
$use_tolerance  = CView::get("use_tolerance", "bool");

CView::checkin();

$affectation = new CAffectation();
$sejour      = new CSejour();

if ($affectation_id) {
  $affectation->load($affectation_id);

  // On déplace l'affectation parente si nécessaire
  if ($affectation->parent_affectation_id) {
    $affectation_id = $affectation->parent_affectation_id;
    $affectation    = new CAffectation();
    $affectation->load($affectation_id);
  }
}
else {
  $affectation->sejour_id = $sejour_id;
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $affectation->entree = $sejour->entree;
  $affectation->sortie = $sejour->sortie;
}

if (!$force) {
  $result = CAffectation::alertePlacement($lit_id, $affectation);

  if (array_sum($result)) {
    CAppUI::callbackAjax("forceMoveAffectation", json_encode($result), $affectation->_id, $lit_id, $sejour_id, $lit_id_origine);
    CApp::rip();
  }
}

$save_lit_id = $affectation->lit_id;

// Couloir
if ($service_id) {
  $affectation->service_id = $service_id;
}
// Changement de lit
else {
  $affectation->lit_id = $lit_id;
}

// Si l'affectation est un blocage, il faut vider le champ sejour_id
if ($affectation->sejour_id == 0) {
  $affectation->sejour_id = "";
}

switch ($use_tolerance) {
  case "1":
    $minutes_tolerance = intval(CAppUI::gconf("dPhospi CAffectation create_affectation_tolerance"));

    $minutes_diff = CMbDT::minutesRelative($affectation->entree, CMbDT::dateTime());

    if ($minutes_tolerance && ($minutes_diff === 0 || ($minutes_diff > 0 && $minutes_diff <= $minutes_tolerance))) {
      if ($msg = $affectation->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
      }
      break;
    }

    CSejour::$_cutting_affectation = true;

    $save_sortie = $affectation->sortie;

    $affectation->lit_id = $save_lit_id;
    $affectation->sortie = CMbDT::dateTime();

    if ($msg = $affectation->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }

    $_affectation                    = new CAffectation();
    $_affectation->entree            = CMbDT::dateTime();
    $_affectation->lit_id            = $lit_id;
    $_affectation->sejour_id         = $affectation->sejour_id;
    $_affectation->service_id        = $affectation->service_id;
    $_affectation->sortie            = $save_sortie;
    $_affectation->uf_hebergement_id = $affectation->uf_hebergement_id;
    $_affectation->uf_medicale_id    = $affectation->uf_medicale_id;
    $_affectation->uf_soins_id       = $affectation->uf_soins_id;

    if ($msg = $_affectation->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }

    CSejour::$_cutting_affectation = false;

    $affectation_id = $_affectation->_id;

    break;
  default:
    if ($msg = $affectation->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
}

echo CAppUI::getMsg();

$ask_etab_externe = CAppUI::gconf("dPhospi placement ask_etab_externe");

CAppUI::callbackAjax(
  "callbackMoveAffectation",
  $affectation_id ? $affectation->_id : null,
  $lit_id,
  $sejour_id,
  $lit_id_origine,
  "undefined",
  $ask_etab_externe && ($affectation->loadRefSejour()->patient_id || $sejour->patient_id) && $affectation->loadRefService()->externe
    ? 1 : 0
);