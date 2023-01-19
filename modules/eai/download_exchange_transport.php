<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * Download exchange transport
 */
CCanDo::checkRead();

$exchange_guid = CView::get("exchange_guid", "guid class|CExchangeTransportLayer", true);
CView::checkin();

/** @var CExchangeTransportLayer $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);
if (!$exchange) {
  return;
}

$content = "";
$content .= CAppUI::tr("{$exchange->_class}-date_echange") . " : {$exchange->date_echange}\n \n";
$content .= CAppUI::tr("{$exchange->_class}-response_time") . " : {$exchange->response_time} ms \n \n";
$content .= $exchange->fillDownloadExchange();

header("Content-Disposition: attachment; filename={$exchange->function_name}-{$exchange->_id}.txt");
header("Content-Type: text/plain;");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Content-Length: " . strlen($content));
echo $content;
