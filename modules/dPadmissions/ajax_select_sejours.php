<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejours_ids         = CView::get("sejours_ids", "str", true);
$view                = CView::get("view", "str");
CView::checkin();

$group_id = CGroups::loadCurrent()->_id;

$sejour                  = new CSejour();
$sejour->group_id        = $group_id;

// Liste des types d'admission possibles
$list_type_admission = $sejour->_specs["_type_admission"]->_list;

$sejours_ids_admissions = CAppUI::pref("sejours_ids_admissions");
$sejours_ids_admissions = ($sejours_ids_admissions) ?: '{}';

$smarty = new CSmartyDP();
$smarty->assign("view"                   , $view);
$smarty->assign("sejours_ids_admissions" , $sejours_ids_admissions);
$smarty->assign("list_type_admission"    , $list_type_admission);
$smarty->assign("sejours_ids"            , $sejours_ids);
$smarty->assign("group_id"               , $group_id);
$smarty->display("inc_select_sejours.tpl");
