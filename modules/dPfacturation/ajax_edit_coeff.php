<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $g;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCoeff;

CCanDo::checkEdit();
$coeff_id = CValue::get("coeff_id");
$prat_id  = CValue::get("prat_id");

$coeff = new CFactureCoeff();
$coeff->load($coeff_id);

$user = CAppUI::$user;
$listPrat = array();
$user_id = $user->isPraticien() ? $user->user_id : null;
$listPrat = CConsultation::loadPraticiensCompta($user_id);

if (!$coeff->_id) {
  $coeff->praticien_id = $prat_id;
  $coeff->group_id = $g;
}
// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("coeff",  $coeff);
$smarty->assign("listPrat",  $listPrat);

$smarty->display("vw_edit_coeff");