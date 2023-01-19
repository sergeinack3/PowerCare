<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * View exchange transport details
 */
CCanDo::checkRead();

$exchange_guid = CView::get("exchange_guid", "guid class|CExchangeTransportLayer", true);
CView::checkin();

/** @var CExchangeTransportLayer $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);
$exchange->unserialize();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange", $exchange);
$smarty->display("inc_exchange_transport_details.tpl");

