<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * View stats EAI
 */
CCanDo::checkAdmin();

$count             = CValue::getOrSession("count", 30);
$date_production   = CValue::getOrSession("date_production", CMbDT::date());
$group_id          = CValue::getOrSession("group_id", CGroups::loadCurrent()->_id);

$filter = new CExchangeDataFormat();
$filter->date_production = $date_production;
$filter->group_id = $group_id;

$exchanges_classes = array();
foreach (CExchangeDataFormat::getAll(CExchangeDataFormat::class, false) as $key => $_exchange_class) {
  foreach (CApp::getChildClasses($_exchange_class, true, true) as $_child_key => $_child_class) {
    $exchanges_classes[$_exchange_class][] = $_child_class;
  }
  if ($_exchange_class == "CExchangeAny") {
    $exchanges_classes[$_exchange_class][] = $_exchange_class;
  }
}

$criteres = array(
  'no_date_echange',
  'emetteur',
  'destinataire',
  'message_invalide',
  'acquittement_invalide',
);

$smarty = new CSmartyDP();

$smarty->assign("count", $count);
$smarty->assign("date_production", $date_production);
$smarty->assign("filter", $filter);
$smarty->assign("exchanges_classes", $exchanges_classes);
$smarty->assign("criteres", $criteres);

$smarty->display("vw_stats.tpl");
