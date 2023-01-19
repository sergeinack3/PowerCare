<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$pat_id = CValue::getOrSession("patSel");

// Liste des Praticiens
$listPrat = CConsultation::loadPraticiens(PERM_READ);

$patient = new CPatient();
$patient->load($pat_id);

// Chargement des références du patient
if ($pat_id) {
  // Infos patient complètes (tableau de droite)
  $patient->loadDossierComplet();
  $patient->countINS();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("canPatients"  , CModule::getCanDo("dPpatients"));
$smarty->assign("canAdmissions", CModule::getCanDo("dPadmissions"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("canCabinet"   , CModule::getCanDo("dPcabinet"));

$smarty->assign("nb_consults_annulees", 0);
$smarty->assign("nb_sejours_annules"  , 0);
$smarty->assign("nb_ops_annulees"     , 0);
$smarty->assign("vw_cancelled"        , 1);

$smarty->assign("patient"    , $patient);
$smarty->assign("listPrat"   , $listPrat);

$smarty->display("vw_dossier.tpl");
