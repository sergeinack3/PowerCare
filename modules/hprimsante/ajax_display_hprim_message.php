<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hprimsante\CHPrimSanteMessage;

CCanDo::checkRead();

$message_string = CValue::post("message");

if (!$message_string) {
  return;
}

$message_string = stripslashes($message_string);

CValue::setSession("message", $message_string);

try {
  $message = new CHPrimSanteMessage();
  $message->parse($message_string);
  
  $message->_errors_msg   = !$message->isOK(CHL7v2Error::E_ERROR);
  $message->_warnings_msg = !$message->isOK(CHL7v2Error::E_WARNING);
  $message->_xml = $message->toXML()->saveXML();
} catch (CHL7v2Exception $e) {
  CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage()." (".$e->extraData.")");
  return;
}

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("message", $message);
$smarty->assign("key"    , "input");
$smarty->display("inc_display_hprim_message.tpl");
