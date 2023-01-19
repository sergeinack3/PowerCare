<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$user = CAppUI::$user;
if ($user->isPraticien()) {
  $listPrat = CConsultation::loadPraticiensCompta($user->user_id);
}
else {
  $listPrat = CConsultation::loadPraticiensCompta();
}

$functions_id = CMbArray::pluck($listPrat, "function_id");
$where = array();
$where["function_id"] = CSQLDataSource::prepareIn(array_values($functions_id));
$function = new CFunctions();
$functions = $function->loadList($where, "text");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("functions", $functions);
$smarty->display("vw_category_facturation");
