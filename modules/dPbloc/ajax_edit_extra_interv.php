<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Ajout de personnel et changement de salle
 */
$op_id = CValue::get("op_id");

$operation = new COperation();
$operation->load($op_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefPlageOp();
$operation->loadAffectationsPersonnel();

$blocs = CGroups::loadCurrent()->loadBlocs(PERM_READ);

$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("blocs"    , $blocs);

$smarty->display("inc_edit_extra_interv.tpl");