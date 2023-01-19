<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\Atih\CCIM10CategoryATIH;
use Ox\Mediboard\Cim10\CCodeCIM10;

CCanDo::checkRead();
$words = CView::get("words", "str", true);
$modal = CView::get("modal", "str");
CView::checkin();

$chapters = CCIM10CategoryATIH::getChapters(CCodeCIM10::FULL);
$categories_cim = array();

foreach ($chapters as $_chapter) {
  foreach ($_chapter->_categories as $_cat) {
    $categories_cim[$_cat->id] = $_cat;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("words"   , $words);
$smarty->assign("modal"   , $modal);
$smarty->assign("categories_cim", $categories_cim);
$smarty->display("nomenclature_cim/vw_cim_explorer");