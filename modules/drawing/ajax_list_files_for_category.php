<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Drawing\CDrawingCategory;

CCanDo::checkRead();

$cat_id = CValue::get("category_id");

$category = new CDrawingCategory();
$category->load($cat_id);
$category->loadRefsFiles();

$smarty = new CSmartyDP();
$smarty->assign("category", $category);
$smarty->display("inc_list_files_for_category");