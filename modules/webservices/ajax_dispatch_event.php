<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\CEAISoapHandler;

/**
 * Dispatch event
 */
CCanDo::checkAdmin();

$sender_soap_id = CValue::get("sender_soap_id");
$message        = CValue::get("message");

$message = utf8_encode(stripcslashes($message));

$soap_handler = new CEAISoapHandler();
// Dispatch EAI
if (!$ack = $soap_handler->event($message, $sender_soap_id)) {
    CAppUI::stepAjax("Le fichier n'a pu être dispatché correctement", UI_MSG_ERROR);

    CApp::log($ack);
}
