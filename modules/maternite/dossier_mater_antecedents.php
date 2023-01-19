<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::checkEdit();

$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$print        = CView::get("print", "bool default|0");

CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadBackRefs("grossesses_ant");
$pere = $grossesse->loadRefPere();
$grossesse->_ref_pere->loadRefDossierMedical();
$dossier = $grossesse->loadRefDossierPerinat();
$patient = $grossesse->loadRefParturiente();

$user = CMediusers::get();

$constantes_mater = $dossier->loadRefConstantesAntecedentsMaternels();
if (!$constantes_mater->_id) {
  $constantes_mater                = new CConstantesMedicales();
  $constantes_mater->patient_id    = $patient->_id;
  $constantes_mater->context_class = 'CPatient';
  $constantes_mater->context_id    = $patient->_id;
  $constantes_mater->datetime      = CMbDT::dateTime();
  $constantes_mater->user_id       = $user->_id;
}
$constantes_pater = $dossier->loadRefConstantesAntecedentsPaternels();
if (!$constantes_pater->_id) {
  $constantes_pater                = new CConstantesMedicales();
  $constantes_pater->patient_id    = $pere->_id;
  $constantes_pater->context_class = 'CPatient';
  $constantes_pater->context_id    = $pere->_id;
  $constantes_pater->datetime      = CMbDT::dateTime();
  $constantes_pater->user_id       = $user->_id;
}

$dossier_medical = $patient->loadRefDossierMedical();

$dossier_medical->loadRefsAntecedentsOfType("trans");
$dossier_medical->loadRefsAntecedentsOfType("allergie");
$dossier_medical->loadRefPrescription();

if ($dossier_medical->_ref_prescription->_id) {
  foreach ($dossier_medical->_ref_prescription->_ref_prescription_lines as $_line) {
    $_line->loadRefsPrises();
  }
}

$grossesse->loadRefsGrossessesAnt();

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign('constantes_mater', $constantes_mater);
$smarty->assign('constantes_pater', $constantes_pater);
$smarty->assign("print", $print);
$smarty->display("dossier_mater_antecedents.tpl");

