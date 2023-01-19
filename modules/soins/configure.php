<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkAdmin();

$service            = new CService();
$where              = array();
$where["group_id"]  = "= '" . CGroups::loadCurrent()->_id . "'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $service->loadListWithPerms(PERM_READ, $where, $order);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("services", $services);

$smarty->display("configure");