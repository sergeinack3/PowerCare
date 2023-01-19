<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CMedecin;

CCanDo::checkRead();

$medecin_id = CValue::get("medecin_id");

$medecin = new CMedecin();
$medecin->load($medecin_id);

if (!$medecin || !$medecin->_id) {
  CAppUI::stepAjax('common-error-Invalid object', UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("medecin", $medecin);
$smarty->assign("date", CMbDT::date());
$smarty->display("print_medecin.tpl");
