<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();

$object_class = CView::get('object_class', 'str default|CSejour notNull');
$date         = CView::get('date', 'date');
$service_id   = CView::get('service_id', 'str');

CView::checkin();

CView::enableSlave();

$date     = ($date) ?: CMbDT::date();
$group_id = CGroups::loadCurrent()->_id;

/** @var CMbObject $object */
$object = new $object_class();

$ds = CSQLDataSource::get('std');

switch ($object) {
  case $object instanceof CSejour:
    $where = array(
      'sejour.group_id' => "= '$group_id'",
    );
    break;

  default:
    CAppUI::commonError();
}

$where["sejour.entree"] = " <= '$date 23:59:59'";
$where["sejour.sortie"] = " >= '$date 00:00:00'";
$where["sejour.annule"] = " = '0'";

$ljoin = array();

if ($service_id) {
  $ljoin = array(
    'affectation' => 'sejour.sejour_id = affectation.sejour_id',
  );

  if ($service_id == 'NP') {
    $where['affectation.sejour_id'] = 'IS NULL';
  }
  else {
    $where['sejour.service_id'] = $ds->prepare('= ?', $service_id) . ' OR affectation.service_id ' . $ds->prepare('= ?', $service_id);
  }
}

$objects = $object->loadList($where, 'sejour.entree ASC', null, null, $ljoin);

$ex_events = CExClassEvent::massLoadMandatoryEvents($objects);

$filtered_objects = array();

foreach ($objects as $_object) {
  if (!$ex_events[$_object->_guid]) {
    continue;
  }

  $_object->loadRelPatient();

  $filtered_objects[$_object->_id] = $_object;
}

$smarty = new CSmartyDP();
$smarty->assign('objects', $filtered_objects);
$smarty->assign('ex_events', $ex_events);
$smarty->display('inc_vw_mandatory_forms.tpl');