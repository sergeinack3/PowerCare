<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CView;

$patient_id = CView::get("patient_id", "ref class|CPatient");
if (!$patient_id) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Patient not found"), UI_MSG_ERROR);
}
CView::setSession("patient_id_api", $patient_id);
CView::checkin();