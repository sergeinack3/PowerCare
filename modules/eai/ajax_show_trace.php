<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Ftp\CExchangeFTP;
use Ox\Interop\Hl7\CExchangeMLLP;
use Ox\Interop\Webservices\CEchangeSOAP;
use Ox\Mediboard\System\CExchangeFileSystem;
use Ox\Mediboard\System\CExchangeHTTP;

$source_guid = CView::get("source_guid", "guid class|CExchangeSource");
CView::checkin();

$guid  = explode("-", $source_guid);
$id    = $guid[1];
$class = $guid[0];

$exchange = null;
switch ($class) {
    case 'CSourceFTP':
        $exchange = new CExchangeFTP();
        break;

    case 'CSourceSOAP':
        $exchange = new CEchangeSOAP();
        break;

    case 'CSourceHTTP':
        $exchange = new CExchangeHTTP();
        break;

    case 'CSourceFileSystem':
        $exchange = new CExchangeFileSystem();
        break;

    case 'CSourceMLLP':
        $exchange = new CExchangeMLLP();
        break;

    default:
}

if (!$exchange) {
    return;
}

$where                 = [];
$where["source_id"]    = " = '$id' ";
$where["source_class"] = " = '$class' ";

$exchanges = $exchange->loadList($where);

foreach ($exchanges as $_exchange) {
    $_exchange->loadRefSource();
}

$smarty = new CSmartyDP();
$smarty->assign("exchange", $exchange);
$smarty->assign("exchanges", $exchanges);
$smarty->display("inc_vw_trace_source.tpl");
