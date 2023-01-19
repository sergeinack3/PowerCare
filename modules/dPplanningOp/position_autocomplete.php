<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CPosition;

CCanDo::checkRead();

$libelle  = CView::get("position_id_view", "str");
$group_id = CView::get("group_id", "ref class|CGroups");

CView::checkin();
CView::enableSlave();

$position = new CPosition();
$ds       = $position->getDS();
$where    = $ds->prepare("actif = '1' AND (group_id = ? OR group_id IS NULL)", $group_id);

$matches  = $position->getAutocompleteList($libelle, [$where]);
$template = $position->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign('view_field', 'position_id_view');
$smarty->assign('show_view', true);
$smarty->assign("nodebug", true);
$smarty->assign('template', $template);

$smarty->display("inc_field_autocomplete");
