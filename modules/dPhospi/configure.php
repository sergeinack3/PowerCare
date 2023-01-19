<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$service            = new CService();
$service->group_id  = CGroups::loadCurrent()->_id;
$service->cancelled = 0;
$order              = "nom ASC";
$services           = $service->loadMatchingList($order);

$sejour          = new CSejour();
$types_admission = $sejour->_specs["type"]->_locales;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("types_admission", $types_admission);
$smarty->assign("services", $services);
$smarty->display("configure.tpl");

