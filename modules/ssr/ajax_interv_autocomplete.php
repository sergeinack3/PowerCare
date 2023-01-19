<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

$keywords             = CValue::post("keywords_nom", "%%");
$exclude_without_code = CValue::post("exclude_without_code");

CView::enableSlave();

$mediuser = new CMediusers;

$ds = $mediuser->getDS();

$ljoin                        = array();
$ljoin["users"]               = "users.user_id = users_mediboard.user_id";
$ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

$where                          = array();
$where[]                        = "user_last_name " . $ds->prepareLike("$keywords%") .
  "OR user_first_name " . $ds->prepareLike("$keywords%");
$where["users_mediboard.actif"] = "= '1'";

if ($exclude_without_code == "true") {
  $where["code_intervenant_cdarr"] = "IS NOT NULL";
}

$order = "users.user_last_name ASC, users.user_first_name ASC";

$mediusers = $mediuser->loadlist($where, $order, null, null, $ljoin);

$smarty = new CSmartyDP;

$smarty->assign("mediusers", $mediusers);
$smarty->assign("nodebug", true);
$smarty->assign("keywords", $keywords);

$smarty->display("inc_interv_autocomplete");
