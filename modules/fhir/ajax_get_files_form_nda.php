<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$nda         = CView::get("nda", "str");
$search_type = CView::get("search_type", "str");
CView::checkin();

if (!$nda) {
  CAppUI::stepAjax("fhir-msg-None NDA", UI_MSG_ERROR);
}

$sejour = new CSejour();
$sejour->loadFromNDA($nda);

if (!$sejour || !$sejour->_id) {
  CAppUI::stepAjax("fhir-msg-None admit found", UI_MSG_ERROR);
}

$sejour->loadRefsDocItems(true, array('type_doc' => 'IS NOT NULL', "annule" => " = '0' " ));

foreach ($sejour->_refs_docitems as $_doc_item) {
  CFHIR::loadIdex($_doc_item, $sejour->group_id);
}

$smarty = new CSmartyDP();
$smarty->assign("sejour"     , $sejour);
$smarty->assign("search_type", $search_type);
$smarty->display("inc_list_files_from_nda.tpl");
