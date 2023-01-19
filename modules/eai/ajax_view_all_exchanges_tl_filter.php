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
CView::checkin();

$exchange_tl            = new CExchangeTransportLayer();
$exchange_tl->_date_min = $date_min;
$exchange_tl->_date_max = $date_max;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange_tl", $exchange_tl);
$smarty->display("inc_vw_all_exchanges_tl_filter.tpl");


