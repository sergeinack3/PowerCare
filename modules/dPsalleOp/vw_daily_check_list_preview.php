<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\SalleOp\CDailyCheckList;

CCanDo::checkEdit();

$object_class = CView::get("object_class", 'str');
$object_id    = CView::get("object_id", 'ref meta|object_class');
$type         = CView::get("type", "str default|ouverture_salle");

CView::checkin();

$object = CMbObject::loadFromGuid("$object_class-$object_id");

// Vérification de la check list journalière
$daily_check_lists = array();
$daily_check_list_types = array();

list($check_list_not_validated, $daily_check_list_types, $daily_check_lists) = CDailyCheckList::getCheckLists($object, "1970-01-01", $type);

$validateur = new CPersonnel();
$validateur->_ref_user = new CMediusers();
$validateur->_ref_user->_view = "Validateur test";

$listValidateurs = array(
  $validateur
);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("daily_check_lists" , $daily_check_lists);
$smarty->assign("listValidateurs"   , $listValidateurs);
$smarty->display("vw_daily_check_list_preview.tpl");
