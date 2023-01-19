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
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CAnesthPerop;

CCanDo::checkRead();
$operation_id = CView::get("operation_id", 'ref class|COperation');
$type         = CView::get("type", "str default|perop");
CView::checkin();

$interv = new COperation;
$interv->load($operation_id);

CAccessMedicalData::logAccess($interv);

$interv->loadComplete();

$user_id = $interv->anesth_id ?: CMediusers::get()->_id;

$evenement = new CAnesthPerop();
$evenement->loadAides($user_id);

// Lock add new or edit event
$limit_date_min = null;

if ($interv->entree_reveil && ($type == 'sspi')) {
  $limit_date_min = $interv->entree_reveil;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement"     , $evenement);
$smarty->assign("operation"     , $interv);
$smarty->assign("limit_date_min", $limit_date_min);
$smarty->display("inc_quick_evenement_perop");
