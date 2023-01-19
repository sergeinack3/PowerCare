<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;

CCanDo::checkRead();

$consultation_id = CView::getRefCheckRead("consultation_id", "ref class|CConsultation");
$nbDoc           = CView::get("nbDoc", "str");

CView::checkin();

/** @var CCompteRendu[] $documents */
$documents       = array();

// Consultation courante
$consult = CConsultation::findOrFail($consultation_id);

CAccessMedicalData::logAccess($consult);

$consult->needsEdit();

$headerFound = $footerFound = false;

$consult->loadRefsDocs();

foreach ($nbDoc as $compte_rendu_id => $nb_print) {
  if (($nb_print > 0) && isset($consult->_ref_documents[$compte_rendu_id])) {
    for ($i = 1; $i <= $nb_print; $i++) {
      $documents[] = $consult->_ref_documents[$compte_rendu_id];
    }
  }
}

if (count($documents) == 0) {
  echo '<div class="small-info">Il n\'y a aucun document pour cette consultation</div>';
  CApp::rip();
}

$_source = "";
foreach ($documents as $doc) {
  $doc->loadContent();

  // Suppression des headers et footers en trop (tous sauf le premier)
  $xml = new DOMDocument("1.0", "iso-8859-1");
  $source = "<div>" . CMbString::convertHTMLToXMLEntities($doc->_source) . "</div>";
  $source = preg_replace("/&\w+;/i", "", $source);

  @$xml->loadXML(utf8_encode($source));
  $xpath = new DOMXPath($xml);

  $nodeList = $xpath->query("//*[@id='header']");
  if ($nodeList->length) {
    if ($headerFound) {
      $header = $nodeList->item(0);
      $header->parentNode->removeChild($header);
    }
    $headerFound = true;
  }

  $nodeList = $xpath->query("//*[@id='footer']");
  if ($nodeList->length) {
    if ($footerFound) {
      $footer = $nodeList->item(0);
      $footer->parentNode->removeChild($footer);
    }
    $footerFound = true;
  }

  $_source .= $xml->saveHTML() . '<br style="page-break-after: always;" />';
}

// Initialisation de CKEditor
$templateManager = new CTemplateManager();
$templateManager->printMode = true;
$templateManager->initHTMLArea();

// Création du template
$smarty = new CSmartyDP("modules/dPcompteRendu");
$smarty->assign("_source", $_source);
$smarty->display("print_cr");
