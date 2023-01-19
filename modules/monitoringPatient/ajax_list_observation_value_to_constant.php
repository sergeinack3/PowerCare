<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\ObservationResult\CObservationValueToConstant;

CCanDo::checkAdmin();

$start = (int)CView::get('start', 'num min|0 default|0');

CView::checkin();

$group = CGroups::loadCurrent();

$conversion = new CObservationValueToConstant();
$ljoin      = array(
  'observation_value_type' => 'observation_value_type.observation_value_type_id = observation_values_to_constant.value_type_id'
);
$where      = array(
  "observation_value_type.group_id = $group->_id OR observation_value_type.group_id IS NULL"
);

$total       = $conversion->countList($where, 'observation_value_to_constant_id', $ljoin);
$conversions = $conversion->loadList($where, null, "$start, 30", 'observation_value_to_constant_id', $ljoin);

$smarty = new CSmartyDP();
$smarty->assign('conversions', $conversions);
$smarty->assign('total'      , $total);
$smarty->assign('start'      , $start);
$smarty->display('inc_list_observation_value_to_constant');
