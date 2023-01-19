<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hl7\Events\DEC\CHL7v2EventORUR01;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$object_class     = trim(CValue::getOrSession("object_class"));
$object_id        = trim(CValue::getOrSession("object_id"));
$cn_receiver_guid = trim(CValue::getOrSessionAbs("cn_receiver_guid"));

$object = null;

if ($object_class && $object_id) {
  $object = CMbObject::loadFromGuid("$object_class-$object_id");
}

$receiver           = (new CInteropActorFactory())->receiver()->makeHL7v2();
$receiver->group_id = CGroups::loadCurrent()->_id;
$receiver->actif    = "1";
$receivers          = $receiver->loadMatchingList();

$object_classes = array("COperation", "CSejour");

$values_type_oru = CHL7v2EventORUR01::$values_type_parameters;

$smarty = new CSmartyDP();
$smarty->assign("object_class"    , $object_class);
$smarty->assign("object_classes"  , $object_classes);
$smarty->assign("object_id"       , $object_id);
$smarty->assign("object"          , $object);
$smarty->assign("receivers"       , $receivers);
$smarty->assign("cn_receiver_guid", $cn_receiver_guid);
$smarty->assign("values_type_oru", $values_type_oru);
$smarty->display("vw_test_hl7v2.tpl");
