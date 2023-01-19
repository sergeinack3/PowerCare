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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;

CCanDo::checkRead();

$consult_id = CView::get("consult_id", "ref class|CConsultation");

CView::checkin();

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$patient = $consult->loadRefPatient();
$sejour  = $consult->loadRefSejour();

$sejour->loadRefPraticien();

if ($sejour->_ref_praticien->isSageFemme()) {
  $sejour->praticien_id = "";
  $sejour->_ref_praticien = new CMediusers();
}

$use_custom_mode_entree = CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree");

$modes_entree = CModeEntreeSejour::listModeEntree($sejour->group_id);

if ($use_custom_mode_entree && count($modes_entree)) {
  foreach ($modes_entree as $_mode_entree) {
    if ($_mode_entree->code == "8") {
      $sejour->mode_entree_id = $_mode_entree->_id;
      break;
    }
  }
}
else {
  $sejour->mode_entree = "8";
}

$sejours = $patient->loadRefsSejours(array("sejour.annule" => "= '0'"));

if ($sejour->_id) {
  unset($sejours[$sejour->_id]);
}

// On ne garde que les séjours futurs ou actuels
$now = CMbDT::dateTime();
foreach ($sejours as $_sejour) {
  if ($_sejour->sortie < $now) {
    unset($sejours[$_sejour->_id]);
  }
}

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPraticien();

  if (!$sejour->praticien_id) {
    $sejour->praticien_id = $_sejour->praticien_id;
    $sejour->_ref_praticien = $_sejour->_ref_praticien;
    $sejour->uf_soins_id = $_sejour->uf_soins_id;
    $sejour->ATNC = $_sejour->ATNC;
  }
}

if (!$sejour->uf_soins_id) {
  $sejour->uf_soins_id = CAppUI::gconf("maternite placement uf_soins_id_dhe");
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult", $consult);
$smarty->assign("sejour", $sejour);
$smarty->assign("sejours", $sejours);
$smarty->assign("modes_entree", $modes_entree);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($sejour));

$smarty->display("inc_declenchement_accouchement");