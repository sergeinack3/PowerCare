<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CPack;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Autocomplete des packs de modèles
 */
$user_id      = CValue::get("user_id");
$function_id  = CValue::get("function_id");
$object_class = CValue::get("object_class");
$keywords     = CValue::post("keywords_pack");

CView::enableSlave();

$group_id = CGroups::loadCurrent()->_id;

switch ($object_class) {
  case "CPrescription":
  case "CElementPrescription":
    $object_class = array("CSejour", "COperation");
    break;
  default:
    $object_class = array($object_class);
}

$curr_user = CMediusers::get();

$where = array();
$where["object_class"] = CSQLDataSource::prepareIn($object_class);
$where[] = "(
  pack.user_id " . CSQLDataSource::prepareIn([$user_id, $curr_user->_id]) . " OR
  pack.function_id " . CSQLDataSource::prepareIn([$function_id, $curr_user->function_id]) . " OR
  pack.group_id = '$group_id'
) OR (pack.user_id IS NULL AND pack.function_id IS NULL AND pack.group_id IS NULL)";
$where[] = "pack.pack_id IN ( SELECT pack_id FROM modele_to_pack)";

$order = "nom";

$pack = new CPack();
$packs = $pack->seek($keywords, $where, null, null, null, $order);

/** @var $_pack CPack */
foreach ($packs as $_pack) {
  $_pack->getModelesIds();
}

$smarty = new CSmartyDP();

$smarty->assign("packs", $packs);
$smarty->assign("nodebug", true);
$smarty->assign("keywords", $keywords);

$smarty->display("inc_pack_autocomplete");
