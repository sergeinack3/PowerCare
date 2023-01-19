<?php 
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\CLogModificationExchange;

$exchange_guid = CView::get("exchange_guid", "str");
CView::checkin();

/** @var CExchangeDataFormat $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);

$log_modification = new CLogModificationExchange();
$log_modification->content_class = "CContentTabular";
$log_modification->content_id = $exchange->message_content_id;
$logs_modification = $log_modification->loadMatchingList("datetime_update desc", 20);

/** @var CLogModificationExchange $_log_modification */
foreach ($logs_modification as $_log_modification) {
  $_log_modification->loadRefUser();
  $_log_modification->_data_update = json_decode($_log_modification->data_update);
}

$smarty = new CSmartyDP();
$smarty->assign("logs_modification", $logs_modification);
$smarty->display("inc_show_logs_modification.tpl");