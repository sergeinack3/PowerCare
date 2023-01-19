<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$session_guid = CValue::get("session_guid");

$session = CMbObject::loadFromGuid($session_guid);

$session->loadRefActor();
$session->loadRefGroups();
$session->loadRefDicomExchange();
$session->updateFormFields();
$session->loadMessages();

$smarty = new CSmartyDP();
$smarty->assign("session", $session);
$smarty->display("inc_session_details.tpl");
