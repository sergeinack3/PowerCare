<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHL7v2Transformation;

CCanDo::checkAdmin();

$actor_guid    = CValue::get("actor_guid");
$profil        = CValue::get("profil", "PAM");
$message_class = CValue::get("message_class", "CHL7EventADTA01");

$temp       = explode("_", $message_class);

$event_name = CMbArray::get($temp, 0);
$version    = CAppUI::conf("hl7 default_version");
$extension = null;
if (CMbArray::get($temp, 1)) {
  $extension    = CAppUI::conf("hl7 default_fr_version");
}

$message = str_replace("CHL7Event", "", $event_name);

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);
$where = array(
  "message"     => " = '$message'",
  "profil"      => " = '$profil'"
);

if ($extension) {
  $where["extension"] = " = '$extension'";
}

$event_class_name = "CHL7v2Event". $message;
$event_class = new $event_class_name();
$messageNameXpath = '';
if ($event_class) {
    $messageNameXpath = $event_class->event_type."_".$event_class->code;
}

$trans = new CHL7v2Transformation($version, $extension, $message, $messageNameXpath);
$tree = $trans->getSegments($actor);

$smarty = new CSmartyDP();
$smarty->assign("profil"    , $profil);
$smarty->assign("version"   , $version);
$smarty->assign("extension" , $extension);
$smarty->assign("message"   , $message);
$smarty->assign("tree"      , $tree);
$smarty->assign("actor_guid", $actor_guid);
$smarty->assign("actor"     , $actor);

$smarty->display("inc_transformation_hl7.tpl");
