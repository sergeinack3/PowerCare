<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$needle = CValue::post("_executant", "aaa");

CView::enableSlave();

$intervenant = new CMediusers();

$ljoin                        = array();
$ljoin["users"]               = "users.user_id = users_mediboard.user_id";
$ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

$where                                 = array();
$where["code_intervenant_cdarr"]       = "IS NOT NULL";
$where["users_mediboard.actif"]        = "= '1'";
$where["functions_mediboard.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";

$order = "users.user_last_name ASC, users.user_first_name ASC";

/** @var CMediusers[] $intervenants */
$intervenants = $intervenant->seek($needle, $where, 100, false, $ljoin, $order);
foreach ($intervenants as &$_intervenant) {
  $_intervenant->loadRefFunction();
  $_intervenant->loadRefIntervenantCdARR();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("intervenants", $intervenants);
$smarty->assign("needle", $needle);
$smarty->assign("nodebug", true);

$smarty->display("inc_do_intervenant_autocomplete");
