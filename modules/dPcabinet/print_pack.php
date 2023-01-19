<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\CompteRendu\CPack;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

// !! Attention, régression importante si ajout de type de paiement

// Récupération des paramètres
$operation_id = CValue::get("operation_id", null);
$op = new COperation;
$op->load($operation_id);

CAccessMedicalData::logAccess($op);

$op->loadRefsFwd();
$op->_ref_sejour->loadRefsFwd();
$patient =& $op->_ref_sejour->_ref_patient;

$pack_id = CValue::get("pack_id", null);

$pack = new CPack;
$pack->load($pack_id);

// Creation des template manager
$listCr = array();
foreach ($pack->_modeles as $key => $value) {
  $listCr[$key] = new CTemplateManager;
  $listCr[$key]->valueMode = true;
  $op->fillTemplate($listCr[$key]);
  $patient->fillTemplate($listCr[$key]);
  $listCr[$key]->applyTemplate($value);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listCr", $listCr);

$smarty->display("print_pack.tpl");
