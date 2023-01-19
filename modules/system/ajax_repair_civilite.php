<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$check = CView::get('check', 'num default|0');
$step  = CView::get('step', 'num default|100');
$start = CView::get('start', 'num default|0');

CView::checkin();
CView::enforceSlave();

$patient = new CPatient();
$ds      = $patient->getDS();

$query = new CRequest();
$query->addSelect(array('patient_id', 'nom', 'prenom', 'naissance', 'sexe', 'civilite'));
$query->addTable('patients');
$query->addWhere(
  array(
    "(sexe = 'm' AND civilite IN ('mme', 'mlle')) OR (sexe = 'f' AND civilite = 'm')"
  )
);

$query_count = clone $query;

$query->setLimit("$start,$step");

$patients = $ds->loadList($query->makeSelect());
$count    = $ds->loadResult($query_count->makeSelectCount());

$smarty = new CSmartyDP();
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->assign('patients', $patients);
$smarty->assign('count', $count);
$smarty->display('inc_repair_civilite.tpl');