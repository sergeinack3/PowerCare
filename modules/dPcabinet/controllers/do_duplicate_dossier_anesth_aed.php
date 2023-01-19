<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Moebius\CMoebiusAPI;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Duplication de dossier d'anesthésie
 */
$dossier_anesth_id = CValue::post("_consult_anesth_id");//Consultation d'anesthésie à dupliquer
$_dest_consult_anesth_id   = CValue::post("_dest_consult_anesth_id"); //Id du nouveau dossier d'anesthésie s'il existe déjà
$sejour_id         = CValue::post("sejour_id");
$operation_id      = CValue::post("operation_id");
$redirect          = CValue::post("redirect", 1);

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($dossier_anesth_id);

//Seul les personnes du cabinet d'anesthésie peuvent dupliquer un dossier d'anesthésie (conf)
$functions_ids = CMbArray::pluck(CMediusers::loadCurrentFunctions(), "function_id");
$prat = $consult_anesth->loadRefConsultation()->loadRefPraticien();
$function = $prat->loadRefFunction();
if (!(in_array($prat->function_id, $functions_ids) || $function->canDo()->edit)
    && CAppUI::gconf("dPcabinet CConsultation csa_duplicate_by_cabinet")
) {
  CAppUI::setMsg("Vous n'appartenez pas au cabinet d'anesthésie: ".$function, UI_MSG_ERROR);
  if ($redirect) {
    CAppUI::redirect(
      "m=cabinet&tab=edit_consultation&selConsult=".
      $consult_anesth->consultation_id."&dossier_anesth_id=".$consult_anesth->_id."&represcription=$represcription"
    );
  }
  CApp::rip();
}

$consult_anesth->_docitems_from_consult = true;
$consult_anesth->loadRefsFiles();
$consult_anesth->loadRefsDocs();
$risques = $consult_anesth->loadRefsRisques();
$fichiers = $consult_anesth->_ref_files;
$documents = $consult_anesth->_ref_documents;

//Nous délions le séjour et l'intervention de l'ancienne consultation d'anesthésie
if ($_dest_consult_anesth_id) {
  $consult_anesth->operation_id = $consult_anesth->sejour_id = "";
  if ($msg = $consult_anesth->store()) {
    CAppUI::setMsg($msg);
  }
}
$consult_anesth->_id = $consult_anesth->operation_id = $consult_anesth->sejour_id = "";

if ($sejour_id) {
  $consult_anesth->sejour_id = $sejour_id;
}

if ($operation_id) {
  $consult_anesth->operation_id = $operation_id;
}

//Nous associons l'ancienne consultation d'anesthésie
if ($_dest_consult_anesth_id) {
  $_dest_consult_anesth = CConsultAnesth::findOrFail($_dest_consult_anesth_id);
  $consult_anesth->consultation_id = $_dest_consult_anesth->consultation_id;
  $consult_anesth->_id = $consult_anesth->consultation_anesth_id = $_dest_consult_anesth->_id;
}

$msg = $consult_anesth->store();

$represcription = 0;
if ($msg) {
  CAppUI::setMsg($msg);
}
else {
  CAppUI::setMsg(CAppUI::tr("CConsultAnesth-msg-duplicate"));

  // Duplication des fichiers
  foreach ($fichiers as $_fichier) {
    $_fichier->_id = "";
    $_fichier->object_id = $consult_anesth->_id;
    $_fichier->file_real_filename = "";
    $_fichier->fillFields();
    $_fichier->setContent(file_get_contents($_fichier->_file_path));
    $msg = $_fichier->store();
    CAppUI::displayMsg($msg, "CFile-msg-create");
  }
  // Duplication des documents
  foreach ($documents as $_document) {
    $_document->_id = "";
    $_document->object_id = $consult_anesth->_id;
    $_document->loadContent();
    $_document->_ref_content->_id = "";
    $_document->content_id = "";
    $msg = $_document->store();
    CAppUI::displayMsg($msg, "CCompteRendu-msg-create");
  }

  // Duplication des risques
  if (count($risques)) {
    unset($risques["risque_allergique"]);
    unset($risques["risque_apnee"]);
    unset($risques["risque_nvpo"]);
    unset($risques["risque_vas"]);
    unset($risques["risque_dentaire"]);
    unset($risques["risque_jeun"]);
  }
  foreach ($risques as $_risque) {
    $_risque->_id = "";
    $_risque->consultation_anesth_id = $consult_anesth->_id;
    $msg = $_risque->store();
    CAppUI::displayMsg($msg, "$_risque->_class-msg-create");
  }
  //Appel à l'api Moebius si besoin
  if (CModule::getActive("moebius") && CAppUI::pref("ViewConsultMoebius")) {
    $consult_anesth->loadRefOperation();
    if ($consult_anesth->_ref_operation->_id && $consult_anesth->_ref_operation->type_anesth) {
      try {
        CMoebiusAPI::exportEltsConsult($consult_anesth->_id);
      }
      catch (Exception $e) {
        CAppUI::setMsg('CMoebiusAPI-error', UI_MSG_ERROR, CMoebiusAPI::getErrorMessage());
      }
    }
  }

  //Création de la prescription de séjour selon pref user
  if ($consult_anesth->sejour_id && CAppUI::pref("show_replication_duplicate")) {
    $prescription = new CPrescription();
    $prescription->object_class = 'CSejour';
    $prescription->object_id = $consult_anesth->sejour_id;
    $prescription->type = 'sejour';
    if (!$prescription->loadMatchingObject()) {
      if ($msg = $prescription->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
      }
    }
    $_sejour = new CSejour();
    $_sejour->load($consult_anesth->sejour_id);
    $where = array();
    $where["entree"] = " <= '$_sejour->entree'";
    $sejours = $_sejour->loadRefPatient()->loadRefsSejours($where);
    unset($sejours[$sejour_id]);
    $prescriptions = 0;
    if (count($sejours)) {
      $order = "entree_prevue DESC, entree DESC";
      $ljoin = array();
      $ljoin["sejour"] = "sejour.sejour_id = prescription.object_id";
      $where = array();
      $where["prescription.type"] = "= 'sejour'";
      $where["object_class"]      = "= 'CSejour'";
      $where["object_id"]         = CSQLDataSource::prepareIn(array_keys($sejours));
      $prescription = new CPrescription();
      $prescriptions = $prescription->countList($where, "prescription_id", $ljoin);
    }
    if (count($sejours) && $prescriptions) {
      $represcription = 1;
    }
  }
}

echo CAppUI::getMsg();

if ($redirect) {
  CAppUI::redirect(
    "m=cabinet&tab=edit_consultation&selConsult=".
    $consult_anesth->consultation_id."&dossier_anesth_id=".$consult_anesth->_id."&represcription=$represcription"
  );
}

CApp::rip();
