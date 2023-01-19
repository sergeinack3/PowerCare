<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CProtocoleGestePerop;

CCanDo::checkEdit();
$operation_id = CView::get("operation_id", "ref class|COperation");
$type         = CView::get("type", "str default|perop");
CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$where = array();

$protocole_geste_perop = new CProtocoleGestePerop();
$order                 = "libelle ASC";

$user     = CMediusers::get();
$function = $user->loadRefFunction();
$group    = CGroups::loadCurrent();

$where[]        = "user_id = '$user->_id' OR function_id = '$function->_id' OR group_id = '$group->_id'";
$where["actif"] = " = '1'";

$protocoles_geste_perop = $protocole_geste_perop->loadList($where, $order);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("protocoles_geste_perop", $protocoles_geste_perop);
$smarty->assign("operation_id", $operation_id);
$smarty->assign("type", $type);
$smarty->display("inc_select_protocoles_gestes_perop");
