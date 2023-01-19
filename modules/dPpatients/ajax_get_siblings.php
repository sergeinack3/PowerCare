<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id      = CView::get("patient_id", "ref class|CPatient");
$nom             = CView::get("nom", "str");
$nom_jeune_fille = CView::get("nom_jeune_fille", "str");
$prenom          = CView::get("prenom", "str");
$prenoms         = CView::get("prenoms", "str");
$naissance       = CView::get("naissance", "str default|0000-00-00");
$submit          = CView::get("submit", "bool default|0");
$json_result     = CView::get("json_result", "bool");
CView::checkin();

/* Decode the string when the request is made by using the requestJSON method */
if ($json_result) {
  $nom             = utf8_decode($nom);
  $nom_jeune_fille = utf8_decode($nom_jeune_fille);
  $prenom          = utf8_decode($prenom);
  $prenoms         = utf8_decode($prenoms);
}

$similar = true;

$old_patient = new CPatient();
$old_patient->load($patient_id);

if ($patient_id && $nom && $prenom) {
  $similar = $old_patient->checkSimilar($nom, $prenom);
}

$patientMatch             = new CPatient();
$patientMatch->patient_id = $patient_id;

if (CAppUI::isCabinet()) {
  $function_id               = CFunctions::getCurrent()->_id;
  $patientMatch->function_id = $function_id;
}
elseif (CAppUI::isGroup()) {
  $group_id = CMediusers::get()->loadRefFunction()->group_id;
  $patientMatch->group_id = $group_id;
}

$patientMatch->nom             = $nom;
$patientMatch->nom_jeune_fille = $nom_jeune_fille;
$patientMatch->prenom          = $prenom;
$patientMatch->prenoms         = $prenoms;
$patientMatch->naissance       = $naissance;

$doubloon = implode("|", $patientMatch->getDoubloonIds());

$siblings = null;
if (!$doubloon) {
  $siblings = $patientMatch->getSiblings();
}

//Test pour l'ouverture de la modal
if ($json_result) {
  CApp::json(!$similar || $siblings || ($doubloon && $old_patient->status != "DPOT"));
}

$doubloons = array();
if ($doubloon) {
  $doubloons = $patientMatch->loadList(array("patient_id" => CSQLDataSource::prepareIn(explode("|", $doubloon))));

  CPatient::massLoadIPP($doubloons);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("similar", $similar);
$smarty->assign("old_patient", $old_patient);
$smarty->assign("doubloon", $doubloon);
$smarty->assign("doubloons", $doubloons);
$smarty->assign("siblings", $siblings);
$smarty->assign("patient_match", $patientMatch);
$smarty->assign("submit", $submit);

$smarty->display("inc_get_siblings.tpl");
