<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CINSPatient;

$birthDate = CValue::get("birthDate", "");
$firstName = CValue::get("firstName", "");
$nir       = CValue::get("nir", "");
$nirKey    = CValue::get("nirKey", "");
$insc      = "";

if ($nir && $nirKey) {
  $firstName = CINSPatient::formatString($firstName);
  $insc      = CINSPatient::calculInsc($nir, $nirKey, $firstName, $birthDate);
}

$smarty = new CSmartyDP();

$smarty->assign("birthDate", $birthDate);
$smarty->assign("firstName", $firstName);
$smarty->assign("nir", $nir);
$smarty->assign("nirKey", $nirKey);
$smarty->assign("insc", $insc);

$smarty->display("ins/inc_test_insc_saisi.tpl");