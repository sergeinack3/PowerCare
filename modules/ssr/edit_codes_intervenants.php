<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CIntervenantCdARR;

CCanDo::checkRead();

$current              = CView::get("current", "num default|0");
$step                 = CView::get("step", "num default|20");
$user_id              = CView::get("interv", "ref class|CMediusers");
$exclude_without_code = CView::get("exclude_without_code", "str");
$list_mode            = CView::get("list_mode", "bool default|0");
CView::checkin();

$intervenant  = new CIntervenantCdARR();
$intervenants = $intervenant->loadList(null, "code");

$mediuser = new CMediusers();

$ljoin                        = array();
$ljoin["users"]               = "users.user_id = users_mediboard.user_id";
$ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

$where                                 = array();
$where["users_mediboard.actif"]        = "= '1'";
$where["functions_mediboard.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";

if ($exclude_without_code == "1") {
  $where["users_mediboard.code_intervenant_cdarr"] = "IS NOT NULL";
}
if ($user_id) {
  $where["users.user_id"] = " = '$user_id' ";
}

$limit = "$current, $step";
$order = "users.user_last_name ASC, users.user_first_name ASC";
$total = $mediuser->countList($where, null, $ljoin);

/** @var CMediusers[] $mediusers */
$mediusers = $mediuser->loadList($where, $order, $limit, null, $ljoin);
foreach ($mediusers as $_mediuser) {
  $_mediuser->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("mediuser", $mediuser);
$smarty->assign("intervenants", $intervenants);
$smarty->assign("interv", $user_id);
$smarty->assign("mediusers", $mediusers);
$smarty->assign("current", $current);
$smarty->assign("step", $step);
$smarty->assign("total", $total);
$smarty->assign("exclude_without_code", $exclude_without_code);

if ($list_mode) {
  $smarty->display("edit_codes_intervenants_list");
}
else {
  $smarty->display("edit_codes_intervenants");
}
