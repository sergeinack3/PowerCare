<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Personnel\CPersonnel;

CCanDo::checkRead();

$emplacement      = CView::get("emplacement", "str", true);
$_user_last_name  = CView::get("_user_last_name", "str", true);
$_user_first_name = CView::get("_user_first_name", "str", true);

CView::checkin();

$filter = new CPersonnel();

$filter->emplacement      = $emplacement;
$filter->_user_last_name  = $_user_last_name;
$filter->_user_first_name = $_user_first_name;

$filter->nullifyEmptyFields();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);

$smarty->display("vw_edit_personnel.tpl");
