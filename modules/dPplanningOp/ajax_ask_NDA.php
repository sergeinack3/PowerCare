<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$patient_id     = CView::get("patient_id", "ref class|CPatient");
$json           = CView::get("json", "bool");

CView::checkin();

$sejour = new CSejour();
$sejour->patient_id = $patient_id;
$sejour->hospit_de_jour = 1;

$sejours = $sejour->loadMatchingList("entree DESC");

if ($json) {
  echo count($sejours);
  return;
}

CSejour::massLoadNDA($sejours);
CStoredObject::massLoadFwdRef($sejours, "praticien_id");

$sejours_by_NDA = array();
$locked_nda = array();

/** @var CSejour $_sejour */
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPraticien();

  if (!isset($sejours_by_NDA[$_sejour->_NDA])) {
    $sejours_by_NDA[$_sejour->_NDA] = array();
  }
  $sejours_by_NDA[$_sejour->_NDA][] = $_sejour;
  if ($_sejour->last_seance) {
    $locked_nda[$_sejour->_NDA] = $_sejour->entree;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours_by_NDA", $sejours_by_NDA);
$smarty->assign("locked_nda", $locked_nda);
$smarty->display("inc_ask_NDA");