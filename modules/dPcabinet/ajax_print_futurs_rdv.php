<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id  = CView::getRefCheckRead("patient_id", "ref class|CPatient");
$prat_id     = CView::get("chir_id", "ref class|CMediusers");
$function_id = CView::getRefCheckRead("function_id", "ref class|CFunctions");
$nb_consults = CView::get("nombre_consultations", "num");


CView::checkin();
CView::enableSlave();

if (!$prat_id && !$function_id) {
  CApp::rip();
}

$contexte = CMediusers::get($prat_id);

if ($function_id) {
  $contexte = new CFunctions();
  $contexte->load($function_id);
}

$header = CCompteRendu::getSpecialModel($contexte, "CConsultation", "[ENTETE RDV FUTURS]");
$footer = CCompteRendu::getSpecialModel($contexte, "CConsultation", "[PIED DE PAGE RDV FUTURS]");

$header_content = $footer_content = "";

$header_height = 200;
$footer_height = 0;

if ($header->_id || $footer->_id) {
  $template = new CTemplateManager();

  if ($header->_id) {
    $header->loadContent();
    $template->renderDocument($header->_source);
    $header_content = $template->document;
    $header_height = $header->height;
  }

  if ($footer->_id) {
    $footer->loadContent();
    $template->renderDocument($footer->_source);
    $footer_content = $template->document;
    $footer_height = $footer->height;
  }
}

$patient = new CPatient();
$patient->load($patient_id);
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

// Liste des RDV futurs
$consult = new CConsultation();
$now = CMbDT::date();

$ljoin = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
);

$where = array(
  "consultation.patient_id" => "= '$patient_id'",
  "plageconsult.date"       => "> '$now'",
  "consultation.annule"     => "= '0'"
);

$limit = "0, $nb_consults";

switch ($contexte->_class) {
  case "CMediusers":
    $where["plageconsult.chir_id"] = "= '$contexte->_id'";
    break;
  case "CFunctions":
    $ljoin["users_mediboard"] = "users_mediboard.user_id = plageconsult.chir_id";
    $where["users_mediboard.function_id"] = "= '$contexte->_id'";
    break;
  default:
}

$consults = $consult->loadList($where, "plageconsult.date, consultation.heure", $limit, null, $ljoin);

CStoredObject::massLoadFwdRef($consults, "plageconsult_id");

/** @var CConsultation $_consult */
foreach ($consults as $_consult) {
  $_consult->loadRefPlageConsult();
}

$code_finess = "";
$etablissement = CGroups::loadCurrent();
if ($etablissement->finess) {
  $options = array(
    "width"  => 220,
    "height" => 60,
    "class"  => "barcode",
    "title"  => CAppUI::tr("CGroups-finess"));

  $code_finess = CTemplateManager::getBarcodeDataUri($etablissement->finess, $options);
}

// Génération du content
$smarty = new CSmartyDP();

$smarty->assign("patient"       , $patient);
$smarty->assign("consults"      , $consults);
$smarty->assign("header_content", $header_content);
$smarty->assign("footer_content", $footer_content);
$smarty->assign("header"        , $header_height);
$smarty->assign("footer"        , $footer_height);
$smarty->assign("contexte"      , $contexte);
$smarty->assign("etablissement" , $etablissement);
$smarty->assign("code_finess"   , $code_finess);

$content = $smarty->display("inc_print_futurs_rdv.tpl");
