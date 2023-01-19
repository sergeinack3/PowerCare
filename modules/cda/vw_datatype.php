<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Interop\Cda\CCdaTools;

$listtypes = CCdaTools::returnType("modules/cda/resources/datatypes-base_original.xsd");

//template
$smarty = new CSmartyDP();

$smarty->assign("listTypes", $listtypes);

$smarty->display("vw_datatype.tpl");