<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CExtensionDocumentaireCsARR;
use Ox\Mediboard\Ssr\CLigneActivitesRHS;
use Ox\Mediboard\Ssr\CRHS;
use Ox\Mediboard\Ssr\DependancesRHSBilan;

CCanDo::checkEdit();
$rhs_id      = CView::get("rhs_id", "ref class|CRHS");
$recalculate = CView::get("recalculate", "str");
$light_view  = CView::get("light_view", "bool default|0");
CView::checkin();

// Utilisateur courant
$user = CMediusers::get();

// RHS concernés
$rhs = new CRHS();
$rhs->load($rhs_id);
if (!$rhs->_id) {
  CAppUI::stepAjax("RHS inexistant", UI_MSG_ERROR);
}
$rhs->loadRefsNotes();

// Actes de prestations SSR

if (CAppUI::gconf("ssr general use_acte_presta") == 'presta') {
  $rhs->loadRefActesPrestationSSR();
}

// Recalcul
if ($recalculate) {
  $rhs->recalculate();
}

// Liste des catégories d'activité
if ($rhs->_id) {
  $sejour = $rhs->loadRefSejour();
  $sejour->loadRefsEvtsSSRSejour();

  $rhs->loadRefDiagnostics();
  $dependances = $rhs->loadRefDependances();

  $dependances->loadRefBilanRHS();
  if (!$dependances->_id) {
    $dependances->store();
  }
  $rhs->loadDependancesChronology();
  $rhs->buildTotaux();
}

// Ligne vide d'activité
$rhs_line = new CLigneActivitesRHS();
if ($user->code_intervenant_cdarr) {
  $rhs_line->_executant             = $user->_view;
  $rhs_line->executant_id           = $user->user_id;
  $rhs_line->code_intervenant_cdarr = $user->code_intervenant_cdarr;
}

$extensions_doc = CExtensionDocumentaireCsARR::getList();

// Diagnostics DAS et DAD
$code_das = array();
$code_dad = array();

$code_das_rhs = explode("|", $rhs->DAS);
$code_dad_rhs = explode("|", $rhs->DAD);

foreach ($code_das_rhs as $_code_das) {
  $code_das[] = CCodeCIM10::get($_code_das);
}

foreach ($code_dad_rhs as $_code_dad) {
  $code_dad[] = CCodeCIM10::get($_code_dad);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("rhs_line", $rhs_line);
$smarty->assign("rhs", $rhs);
$smarty->assign("extensions_doc", $extensions_doc);
$smarty->assign("code_das", $code_das);
$smarty->assign("code_dad", $code_dad);
$smarty->assign("light_view", $light_view);
$smarty->display("inc_edit_rhs");
