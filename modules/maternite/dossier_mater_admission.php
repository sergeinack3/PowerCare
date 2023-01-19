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
$patient = $grossesse->loadRefParturiente();
$grossesse->loadRefsSejours();
foreach ($grossesse->_ref_sejours as $sejour) {
  $sejour->loadNDA();
  $sejour->loadRefPraticien();
}

$dossier = $grossesse->loadRefDossierPerinat();
$dossier->loadRefSejourAccouchement();
$dossier->_ref_sejour_accouchement->loadRefPatient();

$user       = CMediusers::get();
$constantes = $dossier->loadRefConstantesMaternelsAdmission();
if (!$constantes->_id) {
  $constantes                = new CConstantesMedicales();
  $constantes->patient_id    = $patient->_id;
  $constantes->context_class = 'CPatient';
  $constantes->context_id    = $patient->_id;
  $constantes->datetime      = CMbDT::dateTime();
  $constantes->user_id       = $user->_id;
}
$constantes->updateFormFields();
$dossier->_ref_adm_mater_constantes = $constantes;

// Liste des sage femmes
$mediuser      = new CMediusers();
$listSageFemme = $mediuser->loadListFromType(array("Sage Femme"), PERM_EDIT);

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("listSageFemme", $listSageFemme);
$smarty->assign("print", $print);

$smarty->display("dossier_mater_admission.tpl");

