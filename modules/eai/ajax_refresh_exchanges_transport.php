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
 * View exchanges transport
 */
CCanDo::checkRead();

$page = CView::get('page', "num default|0");

$exchange_class  = CView::get("exchange_class", "str", true);
$purge           = CView::get('purge', "bool default|0", true);
$keywords_input  = CView::get("keywords_input", "str", true);
$keywords_output = CView::get("keywords_output", "str", true);

$_date_min = CView::get('_date_min', array("dateTime", "default" => CMbDT::dateTime("-7 day")), true);
$_date_max = CView::get('_date_max', array("dateTime", "default" => CMbDT::dateTime("+1 day")), true);

CView::checkin();
CView::enforceSlave();

// Types filtres qu'on peut prendre en compte
$types        = array(
  'ok' => array('emetteur', 'destinataire')
);
$filter_types = array();
foreach ($types as $status_type => $_type) {
  foreach ($_type as $type) {
    $filter_types[$status_type][$type] = !isset($t) || in_array($type, $t);
  }
}

/** @var CExchangeTransportLayer $exchange */
$exchange            = new $exchange_class;
$exchange->_date_min = $_date_min;
$exchange->_date_max = $_date_max;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange", $exchange);
$smarty->assign("purge", $purge);
$smarty->assign("page", $page);
$smarty->assign("filter_types", $filter_types);
$smarty->assign("keywords_input", $keywords_input);
$smarty->assign("keywords_output", $keywords_output);
$smarty->display("inc_filters_exchanges_transport.tpl");
