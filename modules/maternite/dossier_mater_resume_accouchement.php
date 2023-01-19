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
use Ox\Core\CStoredObject;
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

$naissances      = $grossesse->loadRefsNaissances();
$sejours_enfants = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
CStoredObject::massLoadFwdRef($sejours_enfants, "patient_id");
foreach ($naissances as $_naissance) {
  $_naissance->loadRefSejourEnfant()->loadRefPatient();
}
$patient = $grossesse->loadRefParturiente();

$dossier = $grossesse->loadRefDossierPerinat();
$dossier->loadRefsAccouchement();

$user       = CMediusers::get();
$constantes = $dossier->loadRefConstantesFievreTravail();
if (!$constantes->_id) {
  $constantes                = new CConstantesMedicales();
  $constantes->patient_id    = $patient->_id;
  $constantes->context_class = 'CPatient';
  $constantes->context_id    = $patient->_id;
  $constantes->datetime      = CMbDT::dateTime();
  $constantes->user_id       = $user->_id;
}
$constantes->updateFormFields();
$dossier->_ref_fievre_travail_constantes = $constantes;

// Liste des praticiens
$user       = new CMediusers();
$sagefemmes = $user->loadListFromType(array("Sage Femme"));
$praticiens = $user->loadPraticiens();
$anesth     = $user->loadAnesthesistes();


$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("sagefemmes", $sagefemmes);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("anesth", $anesth);
$smarty->assign("print", $print);

$smarty->display("dossier_mater_resume_accouchement.tpl");

