<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$operation_id = CView::get("operation_id", 'ref class|COperation');

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$user = CMediusers::get();
$tarifs = array();

if ($operation->_id) {
  $operation->loadRefPraticien();
  $operation->loadRefsActes();
  $operation->updateFormFields();
  $operation->bindTarif();
  $tarifs = CTarif::loadTarifsUser($operation->_ref_praticien);
}

if ($user->isProfessionnelDeSante()) {
  $tarifs = CTarif::loadTarifsUser($user);
}

$smarty = new CSmartyDP();
$smarty->assign("operation", $operation);
$smarty->assign("tarifs", $tarifs);
$smarty->display("inc_tarifs_operation.tpl");
