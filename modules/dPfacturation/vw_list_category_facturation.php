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
use Ox\Mediboard\Facturation\CFactureCategory;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();
global $g;
$function_id = CView::getRefCheckRead("function_id", "ref class|CFunctions");
CView::checkin();

$function = new CFunctions();
$function->load($function_id);

$category = new CFactureCategory();
$category->group_id = $g;
$category->function_id = $function_id;
$categorys = $category->loadMatchingList("libelle");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("function" , $function);
$smarty->assign("category" , $category);
$smarty->assign("categorys", $categorys);
$smarty->display("vw_list_category_facturation");
