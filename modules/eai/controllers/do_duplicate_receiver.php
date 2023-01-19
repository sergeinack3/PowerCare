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
use Ox\Interop\Eai\CInteropReceiver;

/**
 * Duplicate receiver
 */
CCanDo::checkAdmin();

$receiver_guid = CValue::get("receiver_guid");

if (!$receiver_guid) {
  return;
}

/** @var CInteropReceiver $receiver */
$receiver = CMbObject::loadFromGuid($receiver_guid);

$backrefs = array(
  "messages_supported",
  "object_configs",
  "actor_transformations"
);

if ($msg = $receiver->duplicateObject("libelle", $backrefs, "object_id")) {
  CAppUI::stepAjax("CInteropReceiver-action-Duplicate error '%s'", UI_MSG_ERROR, $msg);
}
else {
  CAppUI::stepAjax("CInteropReceiver-action-Duplicate");
}