<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CExchangeSourceAdvanced;

CCanDo::checkAdmin();

CView::checkin();

$all_name_sources = CExchangeSource::getAll();
$all_sources      = [];

$source_exchange = [
    "CSourceSFTP" => "CExchangeFTP",
    "CSourceFTP"  => "CExchangeFTP",
    "CSourceSOAP" => "CEchangeSOAP",
];

$count_exchange = [];
foreach ($all_name_sources as $_name_source) {
    $class                         = new $_name_source();
    $all_sources[$_name_source]    = [];
    $count_exchange[$_name_source] = $class->countList();
}

//utilisé pour la création des filtres
$ExchangeSource = new CExchangeSourceAdvanced();

$smarty = new CSmartyDP();
$smarty->assign("all_sources", $all_sources);
$smarty->assign("count_exchange", $count_exchange);
$smarty->assign("exchange_source", $ExchangeSource);
$smarty->display("vw_sources.tpl");
