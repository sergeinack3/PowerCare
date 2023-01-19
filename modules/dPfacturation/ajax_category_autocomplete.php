<?php
/**
 * @package Mediboard\OxCabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*/

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFactureCategory;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::check();

$prat_id = CView::get("prat_id", "ref class|CMediusers");
$keywords = CView::get("category_view", "str");
CView::checkin();
CView::enableSlave();

if (!$prat_id) {
  $prat = CMediusers::get();
}
else {
  $prat = New CMediusers();
  $prat->load($prat_id);
}

$where = array();
$where["function_id"] = "= '$prat->function_id'";
if ($keywords) {
  $where["libelle"] = "LIKE '%$keywords%'";
}

$category = new CFactureCategory();
$categorys = $category->loadGroupList($where, "libelle");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("categorys", $categorys);
$smarty->assign("keywords" , $keywords);
$smarty->display("vw_category_autocomplete");

