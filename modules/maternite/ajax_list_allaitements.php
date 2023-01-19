<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Maternite\CAllaitement;

CCanDo::checkRead();

$patient_id  = CValue::getOrSession("patient_id");
$object_guid = CValue::get("object_guid");

$allaitement             = new CAllaitement();
$allaitement->patient_id = $patient_id;

$allaitements = $allaitement->loadMatchingList("date_debut DESC, date_fin DESC");

$smarty = new CSmartyDP();

$smarty->assign("allaitements", $allaitements);
$smarty->assign("patient_id", $patient_id);
$smarty->assign("object_guid", $object_guid);

$smarty->display("inc_list_allaitements.tpl");