<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;

CCanDo::checkRead();

$begin         = CView::get('date_debut', 'date');
$end           = CView::get('date_fin', 'date');
$ccam_code     = CView::get('CCAM', 'str');
$hospi_type    = CView::get('type', 'str');
$discipline_id = CView::get('discipline_id', 'ref class|CDiscipline');
$bloc_id       = CView::get('bloc_id', 'ref class|CBlocOperatoire');
$salle_id      = CView::get('salle_id', 'ref class|CSalle');
$hors_plage    = CView::get('hors_plage', 'bool default|0');
$user_id       = CView::get('prat_id', 'ref class|CMediusers');
$function_id   = CView::get('func_id', 'ref class|CFunctions');

CView::checkin();

CView::enforceSlave();

if (!$begin) {
  $begin = CMbDT::date('-7 DAYS');
}
if (!$end) {
  $end = CMbDT::date();
}

$salle = new CSalle();
$salle->load($salle_id);

$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);

$discipline = new CDiscipline;
$discipline->load($discipline_id);

$salles = CSalle::getSallesStats($salle_id, $bloc_id);

$group = CGroups::loadCurrent();

/* Loading the users */
if ($user_id) {
  $users = array(
    $user_id => CMediusers::get($user_id)
  );
}
elseif ($function_id) {
  $function = new CFunctions();
  $function->load($function_id);

  $users = $function->loadRefsUsers();

  if ($discipline->_id) {
    foreach ($users as $key => $user) {
      if ($user->discipline_id != $discipline->_id) {
        unset($listPrats[$key]);
      }
    }
  }
}
else {
  /* Filter on user type */
  $utypes_flip = array_flip(CUser::$types);
  $user_types  = array('Chirurgien', 'Anesthésiste', 'Médecin', 'Dentiste');
  foreach ($user_types as &$_type) {
    $_type = $utypes_flip[$_type];
  }

  $user  = new CMediusers();
  $where = array(
    'users_mediboard.actif' => " = '1'",
    'users.user_type'       => CSQLDataSource::prepareIn($user_types)
  );

  if ($discipline->_id) {
    $where['users_mediboard.discipline_id'] = "= '{$discipline->_id}'";
  }

  $ljoin = array(
    'users' => 'users.user_id = users_mediboard.user_id'
  );
  $order = 'users_mediboard.function_id, users_mediboard.discipline_id, users.user_last_name, users.user_first_name';

  $users = $user->loadList($where, $order, null, null, $ljoin);
}

$where = array(
  'acte_ccam.coding_datetime' => 'IS NOT NULL',
  'acte_ccam.object_class'    => "= 'COperation'",
  'acte_ccam.executant_id'    => CSQLDataSource::prepareIn(array_keys($users)),
  'operations.date'           => "BETWEEN '{$begin}' AND '{$end}'",
  'sejour.group_id'           => "= {$group->_id}",
  'operations.salle_id'       => CSQLDataSource::prepareIn(array_keys($salles))
);

$ljoin = array(
  'operations' => 'acte_ccam.object_id = operations.operation_id',
  'sejour'     => 'operations.sejour_id = sejour.sejour_id'
);

if ($hospi_type) {
  $where['sejour.type'] = " = '{$hospi_type}'";
}

if (!$hors_plage) {
  $where['operations.plageop_id'] = 'IS NOT NULL';
}

$order = 'acte_ccam.executant_id, acte_ccam.execution';
$group = 'acte_ccam.executant_id, acte_ccam.object_id';

$act = new CActeCCAM();
/** @var CActeCCAM[] $acts */
$acts = $act->loadList($where, $order, null, $group, $ljoin);

$_users = CMbObject::massLoadFwdRef($acts, 'executant_id');
CMbObject::massLoadFwdRef($_users, 'function_id');

$results = array(
  'functions' => array(),
  'total'     => 0,
  'j0'        => 0,
  'j1'        => 0,
  'j2'        => 0,
  'j3'        => 0,
  'j4'        => 0,
  'j5'        => 0
);
/** @var CActeCCAM $act */
foreach ($acts as $act) {
  $user     = $act->loadRefExecutant();
  $function = $user->loadRefFunction();

  if (!array_key_exists($function->_id, $results['functions'])) {
    $results['functions'][$function->_id] = array(
      'function' => $function,
      'users'    => array(),
      'total'    => 0,
      'j0'       => 0,
      'j1'       => 0,
      'j2'       => 0,
      'j3'       => 0,
      'j4'       => 0,
      'j5'       => 0,
    );
  }

  if (!array_key_exists($user->_id, $results['functions'][$function->_id]['users'])) {
    $results['functions'][$function->_id]['users'][$user->_id] = array(
      'user' => $user, 'total' => 0,
      'j0'   => 0, 'count_j0' => 0,
      'j1'   => 0, 'count_j1' => 0,
      'j2'   => 0, 'count_j2' => 0,
      'j3'   => 0, 'count_j3' => 0,
      'j4'   => 0, 'count_j4' => 0,
      'j5'   => 0, 'count_j5' => 0,
    );
  }

  $results['functions'][$function->_id]['users'][$user->_id]['total']++;

  $delta = CMbDT::daysRelative(CMbDT::date($act->execution), CMbDT::date($act->coding_datetime));

  switch ($delta) {
    case 0:
      $results['functions'][$function->_id]['users'][$user->_id]['count_j0']++;
      break;
    case 1:
      $results['functions'][$function->_id]['users'][$user->_id]['count_j1']++;
      break;
    case 2:
      $results['functions'][$function->_id]['users'][$user->_id]['count_j2']++;
      break;
    case 3:
      $results['functions'][$function->_id]['users'][$user->_id]['count_j3']++;
      break;
    case 4:
      $results['functions'][$function->_id]['users'][$user->_id]['count_j4']++;
      break;
    default:
      $results['functions'][$function->_id]['users'][$user->_id]['count_j5']++;
  }
}

$totals = array('j0' => 0, 'j1' => 0, 'j2' => 0, 'j3' => 0, 'j4' => 0, 'j5' => 0);
foreach ($results['functions'] as $function_id => $function) {
  $totals_function = array('j0' => 0, 'j1' => 0, 'j2' => 0, 'j3' => 0, 'j4' => 0, 'j5' => 0);

  foreach ($function['users'] as $user_id => $user) {
    for ($i = 0; $i <= 5; $i++) {
      if ($user["count_j$i"]) {
        $results['functions'][$function_id]['users'][$user_id]["j$i"] = round($user["count_j$i"] / $user['total'] * 100, 2);
        $totals_function["j$i"]                                       += $results['functions'][$function_id]['users'][$user_id]["j$i"];
      }
    }

    $results['functions'][$function_id]['total'] += $user['total'];
  }

  for ($i = 0; $i <= 5; $i++) {
    $results['functions'][$function_id]["j$i"] = round($totals_function["j$i"] / count($function['users']), 2);
    $totals["j$i"]                             += $results['functions'][$function_id]["j$i"];
  }

  $results['total'] += $results['functions'][$function_id]['total'];
}

for ($i = 0; $i <= 5; $i++) {
  $results["j$i"] = round($totals["j$i"] / count($results['functions']), 2);
}

$smarty = new CSmartyDP();
$smarty->assign('results', $results);
$smarty->display('inc_codage_quality.tpl');
