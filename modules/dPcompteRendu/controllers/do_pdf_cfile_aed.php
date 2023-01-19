<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;

/**
 * Génération du PDF d'un compte-rendu et stream au client ou envoi vers une imprimante réseau
 */

$compte_rendu_id = CView::post("compte_rendu_id", "ref class|CCompteRendu");
$stream          = CView::post("stream", "bool");
$print_to_server = CView::post("print_to_server", "bool");
$file_id         = CView::post("file_id", "ref class|CFile");

CView::checkin();

// Si on a un file_id, on stream le pdf
if ($file_id) {
  $file = new CFile();
  $file->load($file_id);
  
  // Mise à jour de la date d'impression
  $cr = new CCompteRendu();
  $cr->load($file->object_id);
  $cr->loadContent();
  $cr->date_print = "now";
  
  if ($msg = $cr->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
  $file->streamFile();
  CApp::rip();
}

$compte_rendu = new CCompteRendu();
$compte_rendu->load($compte_rendu_id);
$compte_rendu->loadContent(1);

$content = $compte_rendu->_source;

$margins = array(
  $compte_rendu->margin_top,
  $compte_rendu->margin_right,
  $compte_rendu->margin_bottom,
  $compte_rendu->margin_left
);

$content = $compte_rendu->loadHTMLcontent($content, "", $margins, CCompteRendu::$fonts[$compte_rendu->font], $compte_rendu->size);

$file = new CFile();
$file->setObject($compte_rendu);
$file->file_name  = $compte_rendu->nom . ".pdf";
$file->file_type  = "application/pdf";
$file->fillFields();
$file->updateFormFields();
$file->file_name  = $compte_rendu->nom . ".pdf";
$file->author_id = CAppUI::$user->_id;

$htmltopdf = new CHtmlToPDF($compte_rendu->factory);
$htmltopdf->generatePDF($content, 0, $compte_rendu, $file);

$msg = $file->store();

CAppUI::displayMsg($msg, "CFile-msg-create");

// Un callback pour le stream du pdf
if ($stream) {
  CAppUI::callbackAjax("streamPDF", $file->_id);
}

// Un callback pour l'impression server side
if ($print_to_server) {
  // Mise à jour de la date d'impression
  $compte_rendu->date_print = "now";
  if ($msg = $compte_rendu->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }

  CAppUI::callbackAjax("printToServer", $file->_id);
}

echo CAppUI::getMsg();

CApp::rip();
