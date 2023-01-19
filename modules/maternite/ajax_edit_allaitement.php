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
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$allaitement_id = CValue::get("allaitement_id");
$patient_id     = CValue::getOrSession("patient_id");

$allaitement = new CAllaitement();
$allaitement->load($allaitement_id);

if (!$allaitement->_id) {
  $allaitement->patient_id = $patient_id;
}

$patient = new CPatient();
$patient->load($allaitement->patient_id);

$grossesses = $patient->loadRefsGrossesses();

$smarty = new CSmartyDP();

$smarty->assign("allaitement", $allaitement);
$smarty->assign("grossesses", $grossesses);

$smarty->display("inc_edit_allaitement.tpl");