<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$patient_id = CView::get('patient_id', 'ref class|CPatient');
$dossier_id = CView::get('dossier_id', 'num');
$nda        = CView::get('nda', 'str');

CView::checkin();

$patient = new CPatient();
if ($patient_id) {
  $patient->load($patient_id);
}
elseif ($nda) {
  $sejour = new CSejour();
  $sejour->loadFromNDA($nda);
  $patient = $sejour->loadRefPatient(true);
}

$where = array(
  "group_id" => "= '".CGroups::loadCurrent()->_id."'",
  "annule"   => "= '0'"
);

//sejours & opé
foreach ($patient->loadRefsSejours($where) as $_sejour) {
  foreach ($_sejour->loadRefsConsultations() as $_consult) {
    $_consult->getType();
    $_consult->loadRefPlageConsult();
    $_consult->loadRefPraticien()->loadRefFunction();
  }

  foreach ($_sejour->loadRefsOperations(array("annulee" => "= '0'")) as $_operation) {
    $_operation->loadRefsFwd();
  }
}

//consultations
foreach ($patient->loadRefsConsultations(array("annule" => "= '0'")) as $_consult) {
  if ($_consult->sejour_id) {
    unset($patient->_ref_consultations[$_consult->_id]);
    continue;
  }

  $function = $_consult->loadRefPraticien()->loadRefFunction();
  if ($function->group_id != CGroups::loadCurrent()->_id) {
    unset($patient->_ref_consultations[$_consult->_id]);
    continue;
  }

  $_consult->getType();
  $_consult->loadRefPlageConsult();

  // Facture de consultation
  $facture = $_consult->loadRefFacture();
  if ($facture->_id) {
    $facture->loadRefsNotes();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("dossier_id", $dossier_id);
$smarty->display("inc_radio_last_refs.tpl");