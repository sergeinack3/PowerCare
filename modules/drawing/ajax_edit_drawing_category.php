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

CCanDo::checkAdmin();

$id      = CValue::get("id");
$mode_id = CValue::get("mode_id");
$mode    = CValue::get("mode");

$cat = new CDrawingCategory();
$cat->load($id);
if (!$cat->_id) {
  $cat->$mode = $mode_id;
}
$nb_files = $cat->loadRefsFiles();

//smarty
$smarty = new CSmartyDP();
$smarty->assign("cat", $cat);
$smarty->display("inc_edit_category");