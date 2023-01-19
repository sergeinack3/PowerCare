<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeAny;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * View interop receiver EAI
 */
CCanDo::checkRead();

$exchange_class = CView::get("exchange_class", "str");
CView::checkin();
CView::enforceSlave();

// Création du template
$smarty = new CSmartyDP();
if ($exchange_class == "CExchangeTransportLayer") {
  $exchanges_transport_layer_classes = array();

  foreach (CExchangeTransportLayer::getAll() as $key => $_exchange_class) {
    /** @var CExchangeTransportLayer $class */
    $class = new $_exchange_class;
    $class->countExchangesTL();
    $class->getMysqlInfos();
    $exchanges_transport_layer_classes[$_exchange_class] = $class;
  }

  $smarty->assign("exchanges_transport_layer_classes", $exchanges_transport_layer_classes);
  $smarty->display("inc_list_exchange_transport_layer.tpl");
}
else {
  $exchanges_data_format_classes = array();

  foreach (CExchangeDataFormat::getAll(CExchangeDataFormat::class, false) as $key => $_exchange_class) {
    foreach (CApp::getChildClasses($_exchange_class, true, true) as $under_key => $_under_class) {
      /** @var CExchangeDataFormat $class */
      $class = new $_under_class;
      $class->countExchangesDF();
      $class->getMysqlInfos();
      $exchanges_data_format_classes[CClassMap::getSN($_exchange_class)][] = $class;
    }
    if ($_exchange_class == CExchangeAny::class) {
      $class = new CExchangeAny();
      $class->countExchangesDF();
      $class->getMysqlInfos();
      $exchanges_data_format_classes["CExchangeAny"][] = $class;
    }
  }

  $smarty->assign("exchanges_data_format_classes", $exchanges_data_format_classes);
  $smarty->display("inc_list_exchange_data_format.tpl");
}
