<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCategory;

CCanDo::checkEdit();
global $g;
$category_id = CView::get("category_id", "ref class|CFactureCategory");
$function_id = CView::get("function_id", "ref class|CFunctions");
CView::checkin();

/* @var CFacture $facture*/
$category = new CFactureCategory();
$category->load($category_id);
if (!$category->_id) {
  $category->function_id = $function_id;
  $category->group_id = $g;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("category", $category);
$smarty->display("vw_edit_category_facturation");
