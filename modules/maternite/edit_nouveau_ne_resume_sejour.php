<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;

/**
 * Suivi du nouveau né en salle de naissance
 */
$naissance_id = CValue::get("naissance_id");

$naissance = new CNaissance();
$naissance->load($naissance_id);
$enfant = $naissance->loadRefSejourEnfant()->loadRefPatient();
$naissance->_ref_sejour_enfant->loadRefPraticien()->loadRefFunction();
$grossesse = $naissance->loadRefGrossesse();
$grossesse->countBackRefs("naissances");
$dossier = $grossesse->loadRefDossierPerinat();
$mere    = $grossesse->loadRefParturiente();

// Liste des consultants
$user       = new CMediusers();
$anesths    = $user->loadAnesthesistes();
$praticiens = $user->loadPraticiens();
$profssante = $user->loadProfessionnelDeSante();

// Constantes du nouveau-né
$constantes = $naissance->loadRefConstantesNouveauNe();
if (!$constantes->_id) {
  $constantes                = new CConstantesMedicales();
  $constantes->patient_id    = $enfant->_id;
  $constantes->context_class = 'CPatient';
  $constantes->context_id    = $enfant->_id;
  $constantes->datetime      = CMbDT::dateTime();
  $constantes->user_id       = $user->_id;
}
$constantes->updateFormFields();
$naissance->_ref_nouveau_ne_constantes = $constantes;

$smarty = new CSmartyDP();
$smarty->assign("naissance", $naissance);
$smarty->assign("grossesse", $grossesse);
$smarty->assign("anesths", $anesths);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("profssante", $profssante);

$smarty->display("edit_nouveau_ne_resume_sejour.tpl");
