<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * Refresh exchange transport
 */
CCanDo::checkRead();

$page = CView::get('page', "num default|0");

$exchange_class  = CView::get("exchange_class", "str", true);
$purge           = CView::get('purge', "bool default|0", true);
$keywords_input  = CView::get("keywords_input", "str", true);
$keywords_output = CView::get("keywords_output", "str", true);
$filter_types    = CView::get('types', "str");
$order_col       = CView::get("order_col", "str");
$order_way       = CView::get("order_way", "str");

$spec      = array(
  "dateTime",
  "default" => CMbDT::dateTime("-7 day")
);
$_date_min = CView::get('_date_min', $spec, true);
$spec      = array(
  "dateTime",
  "default" => CMbDT::dateTime("+1 day")
);
$_date_max = CView::get('_date_max', $spec, true);
CView::checkin();

CView::enforceSlave();

// Récupération de la liste des echanges
/** @var CExchangeTransportLayer $exchange */
$exchange = new $exchange_class;

$where = array();
if (isset($filter_types["emetteur"])) {
  $where[] = "emetteur IS NULL OR emetteur = '" . CAppUI::conf("mb_id") . "'";
}
if (isset($filter_types["destinataire"])) {
  $where[] = "destinataire IS NULL OR destinataire = '" . CAppUI::conf("mb_id") . "'";
}
if ($_date_min && $_date_max) {
  $where['date_echange'] = " BETWEEN '" . $_date_min . "' AND '" . $_date_max . "' ";
}
if ($keywords_input) {
  $where["input"] = " LIKE '%$keywords_input%'";
}
if ($keywords_output) {
  $where["output"] = " LIKE '%$keywords_output%'";
}

$forceindex[]    = "date_echange";
$total_exchanges = $exchange->countList($where, null, null, $forceindex);
$order           = "$order_col $order_way, {$exchange->_spec->key} DESC";

/** @var CExchangeTransportLayer[] $exchanges */
$exchanges = $exchange->loadList($where, $order, "$page, 25", null, null, $forceindex);
foreach ($exchanges as $_exchange) {
  $_exchange->loadRefsNotes();
  $_exchange->loadRefSource();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("exchange", $exchange);
$smarty->assign("exchanges", $exchanges);
$smarty->assign("total_exchanges", $total_exchanges);
$smarty->assign("page", $page);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);

$smarty->display("inc_exchanges_transport.tpl");


