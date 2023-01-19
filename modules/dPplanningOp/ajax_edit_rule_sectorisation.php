<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CRegleSectorisation;

CCanDo::checkAdmin();

$rule_id = CView::get("rule_id", "num");
$clone   = CView::get("clone", "bool default|0");

CView::checkin();

$rule = new CRegleSectorisation();
$rule->load($rule_id);

if ($clone) {
  $rule->_id = null;
}

// Utilisateurs
$user = CMediusers::get();
$users = $user->loadPraticiens(PERM_EDIT);

// Fonctions
$function = new CFunctions();
$functions = $function->loadGroupList(null, "text");

// Services
$service = new CService();
$services = $service->loadGroupList(["cancelled" => CSQLDataSource::get("std")->prepare("= ?", "0")]);

// Etablissements
$group = new CGroups();
$groups = $group->loadList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rule"     , $rule);
$smarty->assign("clone"    , $clone);
$smarty->assign("user"     , $user);
$smarty->assign("users"    , $users);
$smarty->assign("functions", $functions);
$smarty->assign("services" , $services);
$smarty->assign("groups"   , $groups);

$smarty->display("vw_edit_rule_sectorisation");
