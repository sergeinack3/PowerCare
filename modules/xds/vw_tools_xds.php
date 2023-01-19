<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Xds\CXDSTools;

CCanDo::checkAdmin();

$action = CValue::get("action", "null");
$resultat = false;
switch ($action) {
  case "createXml":
    $resultat = CXDSTools::generateXMLToJv();
    break;
}

$smarty = new CSmartyDP();
$smarty->assign("action"  , $action);
$smarty->assign("result", $resultat);
$smarty->display("vw_tools_xds.tpl");