<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExLink;

CCanDo::checkRead();

$service_id = CView::get('service_id', 'str');
$date       = CView::get('date', 'date');

CView::checkin();

$date     = ($date) ?: CMbDT::date();
$date_min = "{$date} 00:00:00";
$date_max = "{$date} 23:59:59";

CView::enforceSlave();

$ds = CSQLDataSource::get('std');

$sejour = new CSejour();

$where = array(
  'sejour.entree' => $ds->prepare('<= ?', $date_max),
  'sejour.sortie' => $ds->prepare('>= ?', $date_min),
);

$ljoin = array();

if ($service_id) {
  $ljoin['affectation'] = 'sejour.sejour_id = affectation.sejour_id';

  if ($service_id == 'NP') {
    $where['affectation.affectation_id'] = 'IS NULL';
    $where['sejour.group_id']            = $ds->prepare('= ?', CGroups::get()->_id);
  }
  else {
    $where['affectation.entree']     = $ds->prepare('<= ?', $date_max);
    $where['affectation.sortie']     = $ds->prepare('>= ?', $date_min);
    $where['affectation.service_id'] = $ds->prepare('= ?', $service_id);
  }
}

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, null, 'sejour.sejour_id', $ljoin);

CStoredObject::massLoadFwdRef($sejours, 'patient_id');
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
}

$ex_class = new CExClass();

$ljoin = array(
  'ex_class_field_group' => 'ex_class_field_group.ex_class_id = ex_class.ex_class_id',
  'ex_class_field'       => 'ex_class_field.ex_group_id = ex_class_field_group.ex_class_field_group_id',
);

$where = array(
  'ex_class_field.result_in_title' => "= '1'",
  'ex_class_field.disabled'        => "= '0'",
  'ex_class.group_id'              => $ds->prepare('= ? OR ex_class.group_id IS NULL', CGroups::get()->_id),
);

$ex_classes = $ex_class->loadList($where, null, null, 'ex_class.ex_class_id', $ljoin);

$ex_links = array();
if ($ex_classes) {
  $ex_link = new CExLink();

  $where = array(
    'ex_link.object_id'    => $ds->prepareIn(array_keys($sejours)),
    'ex_link.object_class' => "= 'CSejour'",
    'ex_link.ex_class_id'  => $ds->prepareIn(array_keys($ex_classes)),
  );

  $ex_links = $ex_link->loadList($where, 'ex_link.datetime_create DESC', null, 'ex_link.ex_class_id, ex_link.object_id');
}

$sejours_ids = array_keys($sejours);
$ex_link_ids = array_keys($ex_links);

$ex_links_by_class = array_fill_keys(
  $sejours_ids,
  array_fill_keys(
    array_unique(CMbArray::pluck($ex_links, 'ex_class_id')), array(
      'ex_object' => false,
      'result'    => false,
    )
  )
);

$formulae_ex_classes = array();

CExLink::massLoadExObjects($ex_links);

/** @var CExLink $_ex_link */
foreach ($ex_links as $_ex_link) {
  $_ex_class_id = $_ex_link->ex_class_id;
  $_ex_class    = $ex_classes[$_ex_class_id];

  if (!isset($formulae_ex_classes[$_ex_class_id])) {
    $formulae_ex_classes[$_ex_class_id] = $_ex_class;
  }

  $ex_links_by_class[$_ex_link->object_id][$_ex_class_id] = array(
    'ex_object' => $_ex_link->loadRefExObject(),
    'result'    => $_ex_class->getFormulaResult(
      $_ex_class->getFormulaField(),
      array(
        'ex_link.object_id'    => $ds->prepare('= ?', $_ex_link->object_id),
        'ex_link.object_class' => $ds->prepare('= ?', 'CSejour'),
      )
    ),
  );
}

$smarty = new CSmartyDP();
$smarty->assign('sejours', $sejours);
$smarty->assign('formulae_ex_classes', $formulae_ex_classes);
$smarty->assign('ex_links_by_class', $ex_links_by_class);
$smarty->display('vw_scoring_forms_service');