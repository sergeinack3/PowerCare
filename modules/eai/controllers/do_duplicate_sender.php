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
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropSender;

/**
 * Duplicate sender
 */
CCanDo::checkAdmin();

$sender_guid = CValue::get("sender_guid");
if (!$sender_guid) {
  return;
}

/** @var CInteropSender $sender */
$sender = CMbObject::loadFromGuid($sender_guid);

$backrefs = array(
  "messages_supported",
  "object_links",

  "config_hprimxml",
  "config_hprimsante",
  "config_hl7",
);

if (CModule::getActive("phast")) {
  $backrefs = array_merge($backrefs, array("config_phast"));
}
if ($msg = $sender->duplicateObject("libelle", $backrefs)) {
  CAppUI::stepAjax("CInteropSender-action-Duplicate error '%s'", UI_MSG_ERROR, $msg);
}
else {
  CAppUI::stepAjax("CInteropSender-action-Duplicate");
}
