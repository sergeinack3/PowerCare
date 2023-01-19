<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

$plage = new CPlageOp();
$plage->bind($_GET);
$plage->updateFormFields();

$repeat = CValue::get('_repeat', 1);
$type_repeat = CValue::get('_type_repeat');
$errors = array();
$success = array();

$salle_id = $plage->salle_id;
$chir_id  = $plage->chir_id;
$spec_id  = $plage->spec_id;
$secondary_function_id = $plage->secondary_function_id;
if ($plage->_id) {
  $old_plage = new CPlageOp();
  $old_plage->load($plage->_id);
  $salle_id = $old_plage->salle_id;
  $chir_id  = $old_plage->chir_id;
  $spec_id  = $old_plage->spec_id;
  $secondary_function_id = $old_plage->secondary_function_id;
}

$create_mode = !$plage->_id;

while ($repeat > 0) {
  $_guid = $plage->_guid;
  if (!$plage->_id) {
    $_guid = 0;
  }
  if (($error = $plage->check()) || (!$create_mode && !$plage->_id)) {
    if (!$create_mode && !$plage->_id) {
      $error = CAppUI::tr("CPlageOp-Not found");
    }
    $errors[] = array('text' => $error, 'view' => $plage->_view, 'guid' => $_guid);
  }
  else {
    $success[] = array('view' => $plage->_view, 'guid' => $_guid);
  }

  $repeat -= $plage->becomeNext($salle_id, $chir_id, $spec_id, $secondary_function_id);
  $plage->loadOldObject();
}

$plage = new CPlageOp();
$plage->bind($_GET);

$smarty = new CSmartyDP();
$smarty->assign('errors', $errors);
$smarty->assign('success', $success);
$smarty->assign('plage', $plage);
$smarty->assign('repeat', CValue::get('_repeat'));
$smarty->assign('type_repeat', $type_repeat);
$smarty->display('inc_check_plage_repetitions.tpl');
