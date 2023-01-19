<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;

$correspondant_id = CValue::get("correspondant_id");

$correspondant = new CCorrespondantPatient();
$correspondant->load($correspondant_id);

if ($correspondant->_id) {
  if (CAppUI::isCabinet()) {
    $current_user = CMediusers::get();
    $is_admin = $current_user->isAdmin();
    $same_function = $current_user->function_id == $correspondant->function_id;
    if (!$is_admin && !$same_function) {
      CAppUI::accessDenied();
    }
  }
  elseif (CAppUI::isGroup()) {
    $current_user = CMediusers::get();
    $is_admin = $current_user->isAdmin();
    $same_group = $current_user->loadRefFunction()->group_id == $correspondant->group_id;
    if (!$is_admin && !$same_group) {
      CAppUI::accessDenied();
    }
  }
}

//smarty
$smarty = new CSmartyDP();

$smarty->assign("correspondant", $correspondant);
$smarty->assign("mode_modele", 1);

$smarty->display("inc_form_correspondant.tpl");
