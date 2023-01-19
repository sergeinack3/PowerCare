<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$offline      = CView::get("offline", "bool default|0");
$print        = CView::get("print", "bool default|0");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

$consultations = $grossesse->loadRefsConsultations();

CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
CStoredObject::massLoadBackRefs($consultations, "suivi_grossesse");

$prescription_installed = CModule::getActive("dPprescription");

if ($prescription_installed) {
  CPrescription::$_load_lite = 1;
  CStoredObject::massLoadBackRefs($consultations, "prescriptions");
  CPrescription::$_load_lite = 0;
}
$selection_constantes = array("temperature", "poids", "variation_poids", "ta", 'pouls');

foreach ($consultations as $consult) {
  $consult->loadRefPraticien();
  $consult->loadRefSuiviGrossesse();
  $consult->getSA();
  //charge les constantes poids et ta de la consultation
  list($consult->_list_constantes_medicales, $dates) = CConstantesMedicales::getLatestFor(
    $consult->patient_id,
    null,
    $selection_constantes,
    $consult,
    false,
    null
  );

  if ($prescription_installed) {
    $consult->loadRefsPrescriptions();
    if (isset($consult->_ref_prescriptions["externe"])) {
      $prescription = $consult->_ref_prescriptions["externe"];
      $prescription->loadRefsLinesMed();
      $prescription->loadRefsLinesElementByCat();
    }
  }
}

$smarty = new CSmartyDP();
$smarty->assign("grossesse"             , $grossesse);
$smarty->assign("prescription_installed", $prescription_installed);
$smarty->assign("liste_unites"          , CConstantesMedicales::$list_constantes);
$smarty->assign("selection_constantes"  , $selection_constantes);
$smarty->assign("offline"               , $offline);

if ($print) {
    $smarty->display("inc_liste_suivi_grossesse_print");
}
else {
    $smarty->display("inc_liste_suivi_grossesse");
}

