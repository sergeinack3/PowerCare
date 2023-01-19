<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectationUserService;
use Ox\Mediboard\Hospi\CService;

$service_id = CView::get("service_id", "ref class|CService");
CView::checkin();

$service = new CService();
$service->load($service_id);

$affectations_users = CAffectationUserService::listUsersService($service_id);

$smarty = new CSmartyDP();

$smarty->assign("affectations_users", $affectations_users);
$smarty->assign("affectation_user", new CAffectationUserService());
$smarty->assign("service", $service);

$smarty->display("vw_list_user_service.tpl");
