<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CElementPrescriptionToCsarr;
use Ox\Mediboard\Ssr\CExtensionDocumentaireCsARR;

$code_selected = CView::get("code_selected", "str");
CView::checkin();

$acte_csarr               = new CElementPrescriptionToCsarr();
$acte_csarr->code         = $code_selected;
$activite                 = $acte_csarr->loadRefActiviteCsARR();
$acte_csarr->_fabrication = strpos($activite->hierarchie, "09.02.02.") === 0;

$extensions_doc = CExtensionDocumentaireCsARR::getList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_acte", $acte_csarr);
$smarty->assign("extensions_doc", $extensions_doc);
$smarty->assign("in_modal_administration", 1);

$results = $smarty->fetch("inc_edit_line_csarr.tpl");

CApp::json($results);
