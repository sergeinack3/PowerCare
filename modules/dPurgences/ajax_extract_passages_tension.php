<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Urgences\CExtractPassages;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\CRPUPassage;

CCanDo::checkAdmin();

CApp::setMemoryLimit("512M");

$debut_selection = CView::get("debut_selection", "dateTime");
$fin_selection   = CView::get("fin_selection", "dateTime");

CView::checkin();

$now    = $debut_selection ? $debut_selection : CMbDT::dateTime();
$before = CMbDT::dateTime("-2 DAY", $now);

$extractPassages                  = new CExtractPassages();
$extractPassages->date_extract    = CMbDT::dateTime();
$extractPassages->type            = "tension";
$extractPassages->debut_selection = $before;
$extractPassages->fin_selection   = $now;
$extractPassages->group_id        = CGroups::loadCurrent()->_id;
$extractPassages->store();

$doc_valid = null;

$where = array();
/** Indispensable pour le cas des hospit sans relicat */
//$where['sejour.type'] = " = 'urg'";
$where['sejour.annule']   = " = '0'";
$where['sejour.entree']   = " BETWEEN '$before' AND '$now' ";
$where['sejour.group_id'] = " = '" . CGroups::loadCurrent()->_id . "'";

$leftjoin           = array();
$leftjoin['sejour'] = 'sejour.sejour_id = rpu.sejour_id';

$order = "entree ASC";

$rpu = new CRPU();

/** @var CRPU[] $rpus */
$rpus = $rpu->loadList($where, $order, null, null, $leftjoin);

if (count($rpus) == 0) {
  CAppUI::stepAjax("Aucun RPU à extraire.", UI_MSG_ERROR);
}

CStoredObject::massLoadFwdRef($rpus, 'sejour_id');
foreach ($rpus as $_rpu) {
  $sejour = $_rpu->loadRefSejour();
  $sejour->loadExtDiagnostics();
  $sejour->loadDiagnosticsAssocies(false);
  $sejour->loadRefsConsultations();
}
try {
  $rpuSender = CRORFactory::getSender();
  $extractPassages = $rpuSender->extractTension($extractPassages, $rpus);
}
catch (CRORException $exception) {
  CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}

CAppUI::stepAjax("Extraction de " . count($rpus) . " RPUs du " . CMbDT::dateToLocale($before) . " au " . CMbDT::dateToLocale($now) . " terminée.", UI_MSG_OK);
if (!$extractPassages->message_valide) {
  CAppUI::stepAjax("Le document produit n'est pas valide.", UI_MSG_WARNING);
}
else {
  CAppUI::stepAjax("Le document produit est valide.", UI_MSG_OK);
}

foreach ($rpus as $_rpu) {
  $rpu_passage                      = new CRPUPassage();
  $rpu_passage->rpu_id              = $_rpu->_id;
  $rpu_passage->extract_passages_id = $extractPassages->_id;
  $rpu_passage->store();
}

echo "<script>RPU_Sender.extract_passages_id = $extractPassages->_id;</script>";

