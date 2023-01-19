<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExList;

CCanDo::check();

$field       = CValue::get('field');
$view_field  = CValue::get('view_field', $field);
$input_field = CValue::get('input_field', $view_field);
$show_view   = CValue::get('show_view', 'false') == 'true';
$keywords    = CValue::get($input_field);
$limit       = CValue::get('limit', 30);
$group_id    = CView::getRefCheckRead('group_id', 'ref class|CGroups');

CView::checkin();
CView::enableSlave();

$list = new CExList();
$ds      = $list->getDS();

if ($keywords == "") {
    $keywords = "%";
}

$where    = [];
$ljoin    = [];
$order    = null;
$group_by = null;

if (CExClass::inHermeticMode(false)) {
    if ($group_id) {
        $where["group_id"] = $ds->prepare("= ?", $group_id);
    } else {
        $where["group_id"] = 'IS NULL';
    }
}

$matches  = $list->getAutocompleteList($keywords, $where, $limit, $ljoin, $order, $group_by);
$template = $list->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP('modules/system');
$smarty->assign('matches', $matches);
$smarty->assign('field', $field);
$smarty->assign('view_field', $view_field);
$smarty->assign('show_view', true);
$smarty->assign('template', $template);
$smarty->assign('keywords', $keywords);
$smarty->assign('nodebug', true);
$smarty->assign("input", "");
$smarty->display('inc_field_autocomplete.tpl');
