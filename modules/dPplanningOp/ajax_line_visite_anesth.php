<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation_guid = CValue::get("operation_guid");

/* @var COperation $operation*/
$operation = CMbObject::loadFromGuid($operation_guid);

CAccessMedicalData::logAccess($operation);

$operation->loadRefAffectation();
$operation->loadRefChir()->loadRefFunction();
$operation->loadRefPatient()->loadRefLatestConstantes(null, array("poids", "taille"));
$operation->loadRefVisiteAnesth()->loadRefFunction();
$operation->loadRefsConsultAnesth()->loadRefConsultation()->loadRefPraticien()->loadRefFunction();
$operation->countLinesPostOp();
$operation->loadRefTypeAnesth();
$operation->_ref_anesth->loadRefFunction();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_operation", $operation);

$smarty->display("inc_list_visite_anesth");
