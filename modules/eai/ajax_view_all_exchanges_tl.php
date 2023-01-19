<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * View all exchanges transport layer
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
CView::checkin();

$total_exchanges = 0;
$exchanges_tl    = array();

$where                 = array();
$where["date_echange"] = " BETWEEN '$date_min' AND '$date_max'";

$forceindex[] = "date_echange";
$order        = "date_echange DESC";

foreach (CExchangeTransportLayer::getAll() as $key => $_exchange_class) {
  /** @var CExchangeTransportLayer $exchange */
  $exchange  = new $_exchange_class;
  $exchanges = $exchange->loadList($where, $order, "0, 10", null, null, $forceindex);
  foreach ($exchanges as $_exchange) {
    $_exchange->loadRefsNotes();
    $_exchange->loadRefSource();
  }

  $exchanges_tl[$_exchange_class] = $exchanges;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchanges_tl", $exchanges_tl);
$smarty->display("inc_vw_all_exchanges_tl.tpl");