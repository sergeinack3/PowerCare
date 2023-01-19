<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::checkRead();
$constant_id = CValue::get('constant_id');

$constant = new CConstantesMedicales();
$constant->load($constant_id);
$constant->loadRefContext();
$constant->loadRefPatient();
$constant->updateFormFields();

$perms = $constant->canDo();
if (!$perms->read) {
  $perms->denied();
}
$context_guid  = $constant->_ref_context ? $constant->_ref_context->_guid : "CPatient-" . $constant->patient_id;
$host          = CConstantesMedicales::guessHost($context_guid);
$modif_timeout = intval(CAppUI::conf("dPpatients CConstantesMedicales constants_modif_timeout", $host->_guid));
list($can_edit, $disable_edit_motif, $modif_timeout) = $constant->getEditReleve($perms, $constant, $modif_timeout, $context_guid, 1);

$smarty = new CSmartyDP();
$smarty->assign('constant', $constant);
$smarty->assign('can_edit', $can_edit);
$smarty->assign('disable_edit_motif', $disable_edit_motif);
$smarty->assign('modif_timeout', $modif_timeout);
$smarty->display('inc_edit_constant.tpl');
