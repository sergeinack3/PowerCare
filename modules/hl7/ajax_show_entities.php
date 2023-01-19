<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Hl7\Events\MFN\CHL7v2EventMFN;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->assign("entity_types", CHL7v2EventMFN::$entities);
$smarty->display("inc_vw_mfn.tpl");