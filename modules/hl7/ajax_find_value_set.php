<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Ihe\CSVS;

/**
 * Find value set
 */
$value_set_type = CValue::get("value_set_type", "RetrieveValueSet");

$OID      = CValue::get("OID");
$version  = CValue::get("version");
$language = CValue::get("language");

if (!$OID) {
  return;
}

$value_set = null;
$error     = null;
try {
  $value_set = CSVS::sendRetrieveValueSet($OID, $version, $language);
}
catch (SoapFault $s) {
  $error = $s->getMessage();
}

$smarty = new CSmartyDP();
$smarty->assign("error"    , $error);
$smarty->assign("value_set", $value_set);
$smarty->display("inc_result_find_value_set.tpl");