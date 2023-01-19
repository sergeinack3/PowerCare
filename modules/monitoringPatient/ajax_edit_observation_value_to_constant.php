<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\ObservationResult\CObservationValueToConstant;

CCanDo::checkAdmin();

$observation_value_to_constant_id = CView::get('observation_value_to_constant_id', 'ref class|CObservationValueToConstant');

CView::checkin();

$group = CGroups::loadCurrent();

$conversion = new CObservationValueToConstant();
if ($conversion->load($observation_value_to_constant_id)) {
  $conversion->loadRefValueType();
  $conversion->loadRefValueUnit();
}
else {
  $conversion->conversion_ratio = 1;
}

$smarty = new CSmartyDP();
$smarty->assign('conversion', $conversion);
$smarty->display('inc_edit_observation_value_to_constant');
