<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CRemplacement;

CCanDo::checkRead();
$user_id  = CView::get("user_id", "ref class|CMediusers", true);
$hide_old = CView::get("hide_old", "bool default|1", true);
CView::checkin();

$user = CMediusers::get($user_id);
$user->loadRefFunction();

$where   = array();
$where[] = "remplace_id = '$user->_id' OR remplacant_id = '$user->_id'";
if ($hide_old) {
  $where["fin"] = " >= '" . CMbDT::dateTime() . "'";
}
$remplacement = new CRemplacement();
/* @var CRemplacement[] $remplacements */
$remplacements = $remplacement->loadList($where, "debut DESC, fin DESC", null, "remplacement_id");

CStoredObject::massLoadFwdRef($remplacements, "remplace_id");
CStoredObject::massLoadFwdRef($remplacements, "remplacant_id");
foreach ($remplacements as $_remplacement) {
  $_remplacement->loadRefRemplacant();
  $_remplacement->loadRefRemplace();
}

$smarty = new CSmartyDP();

$smarty->assign("user", $user);
$smarty->assign("remplacements", $remplacements);
$smarty->assign("hide_old", $hide_old);

$smarty->display("vw_remplacement_user.tpl");
