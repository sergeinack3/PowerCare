<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Cda\CCdaTools;

CCanDo::checkAdmin();

$action = CValue::get("action", "null");
$result = "";

switch ($action) {
  case "createClass":
    $result = CCdaTools::createClass();
    break;

  case "createTest":
    $result = CCdaTools::createAllTestSchemaClasses();
    break;

  case "clearXSD":
    $result = CCdaTools::clearXSD();
    break;

  case "missClass":
    $result = CCdaTools::missclass();
    break;

  case "createClassXSD":
    $result = CCdaTools::createClassFromXSD();
    break;
}

$smarty = new CSmartyDP();

$smarty->assign("action", $action);
$smarty->assign("result", $result);

$smarty->display("vw_toolsdatatype.tpl");