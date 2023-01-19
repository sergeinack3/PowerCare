<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$filter = new CSejour();

$filter->_date_min = CValue::get("_date_min", CMbDT::date("-1 YEAR"));
$rectif            = CMbDT::transform("+0 DAY", $filter->_date_min, "%d") - 1;
$filter->_date_min = CMbDT::date("-$rectif DAYS", $filter->_date_min);

$filter->_date_max = CValue::get("_date_max", CMbDT::date());
$rectif            = CMbDT::transform("+0 DAY", $filter->_date_max, "%d") - 1;
$filter->_date_max = CMbDT::date("-$rectif DAYS", $filter->_date_max);
$filter->_date_max = CMbDT::date("+ 1 MONTH", $filter->_date_max);
$filter->_date_max = CMbDT::date("-1 DAY", $filter->_date_max);

$filter->_service     = CValue::get("service_id", 0);
$filter->type         = CValue::get("type", 1);
$filter->praticien_id = CValue::get("prat_id", 0);
$filter->_specialite  = CValue::get("discipline_id", 0);
$filter->septique     = CValue::get("septique", 0);

$type_data = CValue::get("type_data", "prevue");

$user      = new CMediusers();
$listPrats = $user->loadPraticiens(PERM_READ);

$service            = new CService();
$where              = array();
$where["cancelled"] = "= '0'";
$listServices       = $service->loadGroupList($where);

$listDisciplines = new CDiscipline();
$listDisciplines = $listDisciplines->loadUsedDisciplines();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("type_data", $type_data);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("listServices", $listServices);
$smarty->assign("listDisciplines", $listDisciplines);

$smarty->display("vw_hospitalisation.tpl");
