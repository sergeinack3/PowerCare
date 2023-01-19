<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * View all exchanges
 */
CCanDo::checkRead();

$spec     = array(
  "dateTime",
  "default" => CMbDT::dateTime("-7 day")
);
$date_min = CView::get('_date_min', $spec, true);
$spec     = array(
  "dateTime",
  "default" => CMbDT::dateTime("+1 day")
);
$date_max = CView::get('_date_max', $spec, true);

$group_id   = CView::get("group_id", "ref class|CGroups default|" . CGroups::loadCurrent()->_id, true);
$actor_guid = CView::get("actor_guid", "guid class|CInteropActor");
$modal      = CView::get("modal", "bool default|0");
CView::checkin();

$total_exchanges = 0;
$exchanges       = array();

$where = array();

$where["group_id"] = " = '$group_id'";

$actor = null;

if ($actor_guid) {
  /** @var CInteropActor $actor */
  $actor = CMbObject::loadFromGuid($actor_guid);
  if ($actor instanceof CInteropSender) {
    $where["sender_id"]    = " = '$actor->_id'";
    $where["sender_class"] = " = '$actor->_class'";
  }
}

$forceindex[] = "date_production";

$where["date_production"] = " BETWEEN '$date_min' AND '$date_max'";

foreach (CExchangeDataFormat::getAll(CExchangeDataFormat::class, false) as $key => $_exchange_class) {
  foreach (CApp::getChildClasses($_exchange_class, true, true) as $under_key => $_under_class) {
    if (!$_under_class) {
      continue;
    }

    $exchange = new $_under_class;

    $order = "date_production DESC";
    $exchanges[$_under_class] = $exchange->loadList($where, "$order, {$exchange->_spec->key} DESC", "0, 10", null, null, $forceindex);
    foreach ($exchanges[$_under_class] as $_exchange) {
      /** @var CExchangeDataFormat $_exchange */
      $_exchange->loadRefsBack();
      $_exchange->getObservations();
      $_exchange->loadRefsInteropActor();
    }
  }
}
    
// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchanges"  , $exchanges);
$smarty->assign("actor"      , $actor);
$smarty->assign("actor_guid" , $actor_guid);
$smarty->assign("modal"      , $modal);
$smarty->display("inc_vw_all_exchanges.tpl");


