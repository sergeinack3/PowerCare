<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CExchangeDataFormat;

/**
 * Define master idex missing
 */
CCanDo::checkAdmin();

$exchange_guid = CValue::post("exchange_guid");
$IPP           = CValue::post("IPP");
$NDA           = CValue::post("NDA");

/** @var CExchangeDataFormat $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);

if ($IPP) {
  $exchange->_message = str_replace("===IPP_MISSING===", $IPP, $exchange->_message);
  CAppUI::stepAjax("CExchangeDataFormat-confirm-IPP replaced");
}

if ($NDA) {
  $exchange->_message = str_replace("===NDA_MISSING===", $NDA, $exchange->_message);
  CAppUI::stepAjax("CExchangeDataFormat-confirm-NDA replaced");
}

if (strpos($exchange->_message, "===NDA_MISSING===") !== false || strpos($exchange->_message, "===IPP_MISSING===") !== false) {
  $exchange->master_idex_missing = true;
}
else {
  $exchange->master_idex_missing = false;
}

if ($msg = $exchange->store()) {
  CAppUI::stepAjax(CAppUI::tr("$exchange->_class-msg-store-failed") . $msg, UI_MSG_ERROR);
}