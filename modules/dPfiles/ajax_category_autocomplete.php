<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;

$object_class = CView::get("object_class", "str");
$keywords     = CView::post("keywords_category", "str");

CView::checkin();
CView::enableSlave();

$order      = "nom";
$categories = array();
$where      = array();
$instance   = new CFilesCategory();

if (!empty($object_class)) {
  $where[] = $instance->_spec->ds->prepare("`class` IS NULL OR `class` = %", $object_class);
}

$where[] = $instance->_spec->ds->prepare("`group_id` IS NULL OR `group_id` = %", CGroups::loadCurrent()->_id);

$categories = array_merge($categories, $instance->seek($keywords, $where, null, null, null, $order));

$smarty = new CSmartyDP();
$smarty->assign("categories", $categories);
$smarty->assign("nodebug"   , true);
$smarty->assign("keywords"  , $keywords);
$smarty->display("inc_category_autocomplete.tpl");
