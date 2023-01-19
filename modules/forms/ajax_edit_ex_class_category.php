<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassCategory;

CCanDo::checkEdit();

$category_id = CValue::get("category_id");

$category = new CExClassCategory();
$category->load($category_id);
$category->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("category", $category);
$smarty->display("inc_edit_ex_class_category.tpl");