<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;

CCanDo::checkAdmin();

$charge_id = CValue::get("charge_id");

$charge = new CChargePriceIndicator;
$charge->load($charge_id);
$charge->loadRefsNotes();
if (!$charge->_id) {
  $charge->group_id = CGroups::loadCurrent()->_id;
}

$smarty = new CSmartyDP();
$smarty->assign("charge", $charge);
$smarty->display("inc_edit_charge_price_indicator");
