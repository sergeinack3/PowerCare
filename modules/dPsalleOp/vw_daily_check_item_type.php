<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CDailyCheckItemType;

CCanDo::checkAdmin();
$item_type_id     = CView::get('item_type_id', 'ref class|CDailyCheckItemType');
$item_category_id = CView::get('item_category_id', 'ref class|CDailyCheckItemCategory');
CView::checkin();

$item_type = new CDailyCheckItemType();
if ($item_type->load($item_type_id)) {
  $item_type->loadRefsNotes();
}
else {
  $item_type->index       = 1;
  $item_type->category_id = $item_category_id;
  $item_type->active      = "1";
}
$item_type->loadRefCategory()->loadRefListType();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("item_type", $item_type);
$smarty->display("vw_daily_check_item_type");
