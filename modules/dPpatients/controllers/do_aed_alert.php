<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantAlert;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

$alert_id       = CView::get("alert_id", "ref class|CConstantAlert");
$spec_id        = CView::get("spec_id", "num");
$seuil_bas_1    = CView::get("seuil_bas_1", "str");
$seuil_bas_2    = CView::get("seuil_bas_2", "str");
$seuil_bas_3    = CView::get("seuil_bas_3", "str");
$seuil_haut_1   = CView::get("seuil_haut_1", "str");
$seuil_haut_2   = CView::get("seuil_haut_2", "str");
$seuil_haut_3   = CView::get("seuil_haut_3", "str");
$comment_haut_1 = CView::get("comment_haut_1", "text");
$comment_haut_2 = CView::get("comment_haut_2", "text");
$comment_haut_3 = CView::get("comment_haut_3", "text");
$comment_bas_1  = CView::get("comment_bas_1", "text");
$comment_bas_2  = CView::get("comment_bas_2", "text");
$comment_bas_3  = CView::get("comment_bas_3", "text");
CView::checkin();

$spec = CConstantSpec::getSpecById($spec_id);

$alert = new CConstantAlert();
$alert->load($alert_id);
$msg_ok = ($alert->_id ?  "CConstantAlert-msg-modify" : "CConstantAlert-msg-create");

$alert->spec_id        = $spec_id;
$count_level           = 0;
$alert->comment_bas_1  = $comment_bas_1;
$alert->comment_haut_1 = $comment_haut_1;
$alert->seuil_bas_1    = $seuil_bas_1;
$alert->seuil_haut_1   = $seuil_haut_1;
$alert->comment_bas_2  = $comment_bas_2;
$alert->comment_haut_2 = $comment_haut_2;
$alert->seuil_bas_2    = $seuil_bas_2;
$alert->seuil_haut_2   = $seuil_haut_2;
$alert->comment_bas_3  = $comment_bas_3;
$alert->comment_haut_3 = $comment_haut_3;
$alert->seuil_bas_3    = $seuil_bas_3;
$alert->seuil_haut_3   = $seuil_haut_3;
try {
  if ($msg = $alert->store()) {
    throw new CConstantException(CConstantException::INVALID_STORE_ALERT, $msg);
  }

  CAppUI::displayAjaxMsg($msg_ok, UI_MSG_OK);
  CConstantSpec::resetListConstants();

  if ($spec->_is_constant_base) {
    $spec->alert_id = $alert->_id;
    if ($msg = $spec->store()) {
      throw new CConstantException(CConstantException::INVALID_STORE_SPEC, $msg);
    }
  }

} catch (CConstantException $constantException) {
  CAppUI::displayAjaxMsg($constantException->getMessage(), UI_MSG_ERROR);
}
