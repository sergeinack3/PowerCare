<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Eai\CExchangeAny;
use Ox\Interop\Eai\CExchangeDataFormat;

/**
 * View exchange data format EAI
 */
CCanDo::checkRead();

$exchanges_classes = array();
foreach (CExchangeDataFormat::getAll(CExchangeDataFormat::class, false) as $key => $_exchange_class) {
  foreach (CApp::getChildClasses($_exchange_class, true, true) as $under_key => $_under_class) {
    $class = new $_under_class;
    $class->countExchangesDF();
    $exchanges_classes[$_exchange_class][] = $class;
  }
  if ($_exchange_class == "CExchangeAny") {
    $class = new CExchangeAny();
    $class->countExchangesDF();
    $exchanges_classes["CExchangeAny"][] = $class;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchanges_classes", $exchanges_classes);
$smarty->display("vw_idx_exchange_data_format.tpl");