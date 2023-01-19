<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;

/**
 * Impression de compte-rendu
 */

// Récupération du compte-rendu
$compte_rendu_id = CView::get("compte_rendu_id", "ref class|CCompteRendu");

CView::checkin();

$compte_rendu = new CCompteRendu();
$compte_rendu->load($compte_rendu_id);
$compte_rendu->loadContent();

// Utilisation des headers/footers
if ($compte_rendu->header_id || $compte_rendu->footer_id) {
  $compte_rendu->loadComponents();
  
  $header = $compte_rendu->_ref_header;
  $header->loadContent();
  
  $footer = $compte_rendu->_ref_footer;
  $footer->loadContent();
  
  $header->height = isset($header->height) ? $header->height : 20;
  $footer->height = isset($footer->height) ? $footer->height : 20;
  
  $style = "
<style type=\"text/css\">
  #header {
    height: {$header->height}px;
  }

  #footer {
    height: {$footer->height}px;
  }";
  
  if ($header->_id) {
    $header->_source = "<div id=\"header\">$header->_source</div>";
    $header->height += 20;
    $compte_rendu->header_id = null;
  }
  
  if ($footer->_id) {
    $footer->_source = "<div id=\"footer\">$footer->_source</div>";
    $footer->height += 20;
    $compte_rendu->footer_id = null;
  }
  
  $style.= "
  @media print {
    #body { 
      padding-top: {$header->height}px; 
    }

    hr.pagebreak, hr.pageBreak { 
      padding-top: {$header->height}px; 
    }
  }
</style>";

  $compte_rendu->_source = "<div id=\"body\">$compte_rendu->_source</div>";
  $compte_rendu->_source = $style . $header->_source . $footer->_source . $compte_rendu->_source;
}

// Initialisation de CKEditor
$templateManager = new CTemplateManager();
$templateManager->printMode = true;
$templateManager->initHTMLArea();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("_source", $compte_rendu->_source);
$smarty->display("print_cr");
