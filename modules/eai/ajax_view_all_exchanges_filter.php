<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * View all exchanges
 */
CCanDo::checkRead();

$_date_min  = CView::get('_date_min' , array("dateTime", "default" => CMbDT::dateTime("-7 day")), true);
$_date_max  = CView::get('_date_max' , array("dateTime", "default" => CMbDT::dateTime("+1 day")), true);
$group_id   = CView::get("group_id"  , "ref class|CGroups default|".CGroups::loadCurrent()->_id);
$actor_guid = CView::get("actor_guid", "guid class|CInteropActor");
$modal      = CView::get("modal"     , "bool default|0");
CView::checkin();

$exchange_df               = new CExchangeDataFormat();
$exchange_df->_date_min    = $_date_min;
$exchange_df->_date_max    = $_date_max;
$exchange_df->group_id     = $group_id;

if ($actor_guid) {
  /** @var CInteropActor $actor */
  $actor = CMbObject::loadFromGuid($actor_guid);
  if ($actor instanceof CInteropReceiver) {
    $smarty->assign("actor", $actor);
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange_df", $exchange_df);
$smarty->assign("actor_guid" , $actor_guid);
$smarty->assign("modal"      , $modal);
$smarty->display("inc_vw_all_exchanges_filter.tpl");


