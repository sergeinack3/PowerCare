<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Stock\CProductEndowment;

CCanDo::checkEdit();

$endowment_id = CValue::getOrSession('endowment_id');
$start        = intval(CValue::getOrSession('start'));
$keywords     = CValue::getOrSession('keywords');
$letter       = CValue::getOrSession('letter', "%");

$group_id = CGroups::loadCurrent()->_id;

$where = array(
  "product_endowment.name" => ($letter === "#" ? "RLIKE '^[^A-Z]'" : "LIKE '$letter%'"),
  "service.group_id"       => "= '$group_id'",
);

$ljoin = array(
  "service" => "service.service_id = product_endowment.service_id",
);

$endowment = new CProductEndowment();
$endowment->load($endowment_id);

$list  = $endowment->seek($keywords, $where, "$start,25", true, $ljoin, "service.nom, product_endowment.name");
$total = $endowment->_totalSeek;

foreach ($list as $_item) {
  //$_item->loadRefs();
  $_item->countBackRefs("endowment_items");
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('list', $list);
$smarty->assign('total', $total);
$smarty->assign('start', $start);
$smarty->assign('endowment', $endowment);
$smarty->assign('letter', $letter);

$smarty->display('inc_endowments_list.tpl');
