<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CBesoinRessource;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$type       = CValue::get("type");
$object_id  = CValue::get("object_id");
$usage      = CValue::get("usage", 0);
$usage_bloc = CValue::get("usage_bloc", 0);

$besoin        = new CBesoinRessource();
$besoin->$type = $object_id;
/** @var CBesoinRessource[] $besoins */
$besoins = $besoin->loadMatchingList();
CMbObject::massLoadFwdRef($besoins, "type_ressource_id");

$operation = new COperation;
$operation->load($object_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefPlageOp();
$deb_op = $operation->_datetime;
$fin_op = CMbDT::addDateTime($operation->temp_operation, $deb_op);

foreach ($besoins as $_besoin) {
  $_besoin->loadRefTypeRessource();
  $_besoin->loadRefUsage();
  // Côté protocole, rien à vérifier
  if ($type != "operation_id") {
    $_besoin->_color = "";
    continue;
  }

  $_besoin->isAvailable();
}

$smarty = new CSmartyDP;

$smarty->assign("besoins", $besoins);
$smarty->assign("object_id", $object_id);
$smarty->assign("type", $type);
$smarty->assign("usage", $usage);
$smarty->assign("usage_bloc", $usage_bloc);

$smarty->display("inc_edit_besoins.tpl");
