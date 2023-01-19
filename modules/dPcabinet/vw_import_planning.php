<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$prat_id = CValue::get("prat_id");
$lite    = CValue::get("lite");

$prat = CMediusers::get($prat_id);

if (!$lite && (!$prat_id || !$prat->_id)) {
  CAppUI::stepAjax("Veillez sélectionner un praticien", UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("prat", $prat);
$smarty->assign("lite", $lite);
$smarty->display("vw_import_planning.tpl");
