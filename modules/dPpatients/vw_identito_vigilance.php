<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

global $can;
if (CAppUI::$user->_user_type != 1) {
  $can->denied();
}

$patient_id = CValue::getOrSession("patient_id");
$naissance  = CValue::getOrSession(
  "naissance", array(
    "day"   => 1,
    "month" => 1,
    "year"  => 1,
  )
);

// Patient à analyser
$patient = new CPatient();

$idex               = new CIdSante400();
$idex->object_class = "CPatient";
$idex->tag          = CAppUI::conf("dPpatients CPatient tag_conflict_ipp") . CAppUI::conf("dPpatients CPatient tag_ipp");
$count_conflicts    = $idex->countMatchingList();

$patient->load($patient_id);

// Liste des praticiens disponibles
$listPrat = array();
if ($patient->_id) {
  $listPrat = new CMediusers();
  $listPrat = $listPrat->loadPraticiens(PERM_READ);
  $patient->loadDossierComplet();
}

if ($patient->_id) {
  foreach ($patient->_ref_sejours as &$_sejour) {
    $_sejour->loadNDA();
  }
}

// Chargement des identifiants standards
$patient->loadIPP();
$patient->countINS();
if (CModule::getActive("fse")) {
  $cv = CFseFactory::createCV();
  if ($cv) {
    $cv->loadIdVitale($patient);
  }
}

// Liste des siblings
$listSiblings = $patient->getSiblings();
foreach ($listSiblings as &$_sibling) {
  $_sibling->loadDossierComplet();
  $_sibling->loadIPP();
  $_sibling->countINS();
  if (CModule::getActive("fse")) {
    $cv = CFseFactory::createCV();
    if ($cv) {
      $cv->loadIdVitale($_sibling);
    }
  }
  foreach ($_sibling->_ref_sejours as &$_sejour) {
    $_sejour->loadNDA();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("canPatients", CModule::getCanDo("dPpatients"));
$smarty->assign("canAdmissions", CModule::getCanDo("dPadmissions"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("canCabinet", CModule::getCanDo("dPcabinet"));

$smarty->assign("patient", $patient);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("listSiblings", $listSiblings);
$smarty->assign("naissance", $naissance);

$smarty->assign("count_conflicts", $count_conflicts);

$smarty->display("vw_identito_vigilance.tpl");
