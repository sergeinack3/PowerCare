<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Soins\CObjectifSoin;
use Ox\Mediboard\Soins\CObjectifSoinCible;

CModule::getCanDo("soins")->needsEdit();

$transmission_id  = CView::get("transmission_id", "ref class|CTransmissionMedicale");
$data_id          = CView::get("data_id", "ref class|CTransmissionMedicale");
$action_id        = CView::get("action_id", "ref class|CTransmissionMedicale");
$result_id        = CView::get("result_id", "ref class|CTransmissionMedicale");
$sejour_id        = CView::get("sejour_id", "ref class|CSejour");
$object_id        = CView::get("object_id", "num");
$object_class     = CView::get("object_class", "str");
$libelle_ATC      = CView::get("libelle_ATC", "str");
$cible_id         = CView::get("cible_id", "ref class|CCible");
$refreshTrans     = CView::get("refreshTrans", "bool default|0");
$update_plan_soin = CView::get("update_plan_soin", "bool default|0");
$focus_area       = CView::get("focus_area", "enum list|data|action|result");
$select_diet      = CView::get("select_diet", "bool default|0");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$user_id = CMediusers::get()->_id;

$cibles = array();

$transmission = new CTransmissionMedicale();
if ($transmission_id) {
  $transmission->load($transmission_id);
}
else {
  if ($data_id || $action_id || $result_id) {
    $transmission->sejour_id = $sejour_id;

    // Multi-transmissions
    if ($data_id) {
      $trans = new CTransmissionMedicale();
      $trans->load($data_id);
      $trans->canEdit();
      $transmission->_text_data = $trans->text;
      $transmission->user_id    = $trans->user_id;
      $transmission->date       = $trans->date;
      $transmission->degre      = $trans->degre;
      $transmission->cible_id   = $trans->cible_id;
      if ($trans->object_id && $trans->object_class) {
        $transmission->object_id    = $trans->object_id;
        $transmission->object_class = $trans->object_class;
      }
      else {
        if ($trans->libelle_ATC) {
          $transmission->libelle_ATC = stripslashes($trans->libelle_ATC);
        }
      }
    }
    if ($action_id) {
      $trans = new CTransmissionMedicale();
      $trans->load($action_id);
      $trans->canEdit();
      $transmission->_text_action = $trans->text;
      $transmission->user_id      = $trans->user_id;
      $transmission->date         = $trans->date;
      $transmission->degre        = $trans->degre;
      $transmission->cible_id     = $trans->cible_id;
      if ($trans->object_id && $trans->object_class) {
        $transmission->object_id    = $trans->object_id;
        $transmission->object_class = $trans->object_class;
      }
      else {
        if ($trans->libelle_ATC) {
          $transmission->libelle_ATC = stripslashes($trans->libelle_ATC);
        }
      }
    }
    if ($result_id) {
      $trans = new CTransmissionMedicale();
      $trans->load($result_id);
      $trans->canEdit();
      $transmission->_text_result = $trans->text;
      $transmission->user_id      = $trans->user_id;
      $transmission->date         = $trans->date;
      $transmission->degre        = $trans->degre;
      $transmission->cible_id     = $trans->cible_id;
      if ($trans->object_id && $trans->object_class) {
        $transmission->object_id    = $trans->object_id;
        $transmission->object_class = $trans->object_class;
      }
      else {
        if ($trans->libelle_ATC) {
          $transmission->libelle_ATC = stripslashes($trans->libelle_ATC);
        }
      }
    }
  }
  else {
    $transmission->sejour_id = $sejour_id;
    $transmission->user_id   = $user_id;
    $transmission->cible_id  = $cible_id;
    if ($object_id && $object_class) {
      $transmission->object_id    = $object_id;
      $transmission->object_class = $object_class;
    }
    else {
      if ($libelle_ATC) {
        $transmission->libelle_ATC = stripslashes($libelle_ATC);
      }
      elseif ($transmission->cible_id) {
        $cible = $transmission->loadRefCible();
        if ($cible->object_id && $cible->object_class) {
          $transmission->object_id    = $cible->object_id;
          $transmission->object_class = $cible->object_class;
        }
        else {
          $transmission->libelle_ATC = stripslashes($cible->libelle_ATC);
        }
      }
    }
  }
}

$transmission->loadTargetObject();
$transmission->loadRefSejour()->loadRefPatient();

if ($transmission->object_class == "CAdministration") {
  $transmission->_ref_object->loadRefsFwd();
}
if ($select_diet && !$transmission->_id) {
  $transmission->dietetique = 1;
}
$smarty = new CSmartyDP();

$smarty->assign("transmission", $transmission);
$smarty->assign("patient", $transmission->_ref_sejour->_ref_patient);
$smarty->assign("refreshTrans", $refreshTrans);
$smarty->assign("update_plan_soin", $update_plan_soin);
$smarty->assign("data_id", $data_id);
$smarty->assign("action_id", $action_id);
$smarty->assign("result_id", $result_id);
$smarty->assign("date", CMbDT::date());
$smarty->assign("hour", intval(CMbDT::format(CMbDT::time(), "%H")));
$smarty->assign("objectif_soin_cible", new CObjectifSoinCible());
$smarty->assign("objectif_soin", new CObjectifSoin());
$smarty->assign("focus_area", $focus_area);

$smarty->display("inc_transmission.tpl");
