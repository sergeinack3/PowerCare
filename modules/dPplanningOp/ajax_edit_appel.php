<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CAppelSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$appel_id  = CView::get("appel_id", "ref class|CAppelSejour", true);
$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
$interv_id = CView::get("interv_id", "ref class|COperation", true);
$type      = CView::get("type", "str", true);
CView::checkin();

//Chargement de l'appel
$appel = new CAppelSejour();
$appel->load($appel_id);
if (!$appel_id) {
  $appel->type      = $type;
  $appel->sejour_id = $sejour_id;
  $appel->user_id   = CMediusers::get()->_id;
  $appel->datetime  = CMbDT::dateTime();

  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);
}
else {
  $sejour = $appel->loadFwdRef('sejour_id');
  $type = $appel->type;
}

$sejour->loadRefPatient();
$sejour->updateFormFields();
$sejour->loadRefsAppel($type);
$first_appel = $sejour->_ref_appels_by_type[$type];

if (!$appel_id || ($first_appel->_id && $first_appel == "realise")) {
  if ($first_appel->_id && $first_appel == "realise") {
    $appel = $sejour->_ref_appels_by_type[$type];
  }

  $sejour->loadRefsAppel($type, true);
  foreach ($sejour->_ref_appels_by_type as $type => $_appels) {
    foreach ($_appels as $_appel) {
      /* @var CAppelSejour $_appel*/
      $_appel->loadRefuser()->loadRefFunction();
      $_appel->loadRefsForms();

      foreach ($_appel->_ref_forms as $_form) {
        $ex_class = $_form->loadRefExClass();
      }

    }
  }
}

$operation = COperation::findOrNew($interv_id);

CAccessMedicalData::logAccess($operation);

// Creation du template
$smarty = new CSmartyDP();
$smarty->assign("appel"    , $appel);
$smarty->assign("sejour"   , $sejour);
$smarty->assign("type"     , $type);
$smarty->assign("appel_id" , $appel_id);
$smarty->assign("operation", $operation);
$smarty->display("vw_edit_appel");
