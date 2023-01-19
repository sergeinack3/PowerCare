<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

global $m;
CAppUI::requireModuleFile($m, "inc_vw_affectations");

$date       = CValue::getOrSession("date", CMbDT::date());
$mode       = CValue::getOrSession("mode", 0);
$service_id = CValue::get("service_id");

// Chargement du service
$service = new CService();
$service->load($service_id);
loadServiceComplet($service, $date, $mode);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("demain", CMbDT::date("+ 1 day", $date));
$smarty->assign("curr_service", $service);

$smarty->display("inc_affectations_services.tpl");

