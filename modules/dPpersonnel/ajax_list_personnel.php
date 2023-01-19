<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Personnel\CPersonnel;

CCanDo::checkRead();

$emplacement      = CView::get("emplacement", "str", true);
$_user_last_name  = CView::get("_user_last_name", "str", true);
$_user_first_name = CView::get("_user_first_name", "str", true);
$personnel_id     = CView::get("personnel_id", "num", true);

CView::checkin();

// Chargement de la liste des affectations pour le filtre
$where   = [];
$ljoin   = [
    "users" => "users.user_id = personnel.user_id",
];
$order   = "users.user_last_name";

if ($emplacement) {
    $where["emplacement"] = "= '$emplacement'";
}
if ($_user_last_name) {
    $where["user_last_name"] = "LIKE '%$_user_last_name%'";
}
if ($_user_first_name) {
    $where["user_first_name"] = "LIKE '%$_user_first_name%'";
}

$personnels = (new CPersonnel())->loadGroupList($where, $order, null, null, $ljoin);

CStoredObject::massLoadFwdRef($personnels, "user_id");
CStoredObject::massCountBackRefs($personnels, "affectations");

/** @var CPersonnel $_personnel */
foreach ($personnels as $_personnel) {
    $_personnel->loadRefUser();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("personnels", $personnels);
$smarty->assign("personnel_id", $personnel_id);

$smarty->display("inc_list_personnel.tpl");
