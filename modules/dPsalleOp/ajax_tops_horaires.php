<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$operation_id = CView::get("operation_id", "ref class|COperation");

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$sejour = $operation->loadRefSejour();
$patient = $sejour->loadRefPatient();
$dossier_medical_sejour = $sejour->loadRefDossierMedical();
$dossier_medical_pat = $patient->loadRefDossierMedical();

// Chargements li�s � l'intervention
$operation->canDo();
$operation->loadRefPlageOp();
$operation->loadRefChir();
$operation->loadRefsConsultAnesth();

// Chargements li�s au s�jour
$sejour->loadRefPraticien();
$sejour->loadRefCurrAffectation()->updateView();
$dossier_medical_sejour->loadRefsAntecedents();
$dossier_medical_sejour->loadRefsTraitements();

// Chargements li�s au patient
$patient->loadRefPhotoIdentite();
$patient->loadRefBMRBHRe();
$patient->loadRefLatestConstantes(null, null, $sejour, false);
$dossier_medical_pat->loadRefsAntecedents();
$dossier_medical_pat->loadRefsTraitements();

// Recherche de la pr�c�dente op�ration pour interdire la modification de timings ult�rieurs au d�but de l'induction
$prev_op = new COperation();
if (CAppUI::gconf("dPsalleOp COperation no_entree_fermeture_salle_in_plage")) {
  $where = array(
    "salle_id"     => "= '$operation->salle_id'",
    "entree_salle" => "IS NOT NULL",
    "sortie_salle" => "IS NULL",
    "operation_id" => "!= '$operation->_id'",
    "date"         => "= '$operation->date'",
    "annulee"      => "= '0'"
  );
  $prev_op->loadObject($where);
  $prev_op->loadRefPatient();
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("operation"           , $operation);
$smarty->assign("edit_after_induction", !$prev_op->_id);
$smarty->assign("prev_op"             , $prev_op);

$smarty->display("inc_tops_horaires");
