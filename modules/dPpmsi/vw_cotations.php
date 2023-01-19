<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

set_time_limit(600);

CCanDo::checkRead();

$chir_id                  = CView::get('chir_id', 'num default|0', true);
$end_date                 = CView::get('end_date', array('date', 'default' => CMbDT::date()), true);
$begin_date               = CView::get('begin_date', array('date', 'default' => CMbDT::date('-1 week', $end_date)), true);
$only_show_missing_codes  = CView::get('only_show_missing_codes', 'bool default|0', true);
$sejour_type              = CView::get('sejour_type', 'str default|all', true);

CView::checkin();
CView::enforceSlave();

$user = CMediusers::get();
$chir_list = $user->loadPraticiens(PERM_READ);

$sejour_types = CSejour::$types;

$filters = array(
  'chir_id' => $chir_id,
  'begin_date' => $begin_date,
  'end_date' => $end_date,
  'sejour_type' => $sejour_type,
  'object_classes' => array('COperation'),
  'unexported_acts' => 1,
  'objects_without_codes' => 1,
  'only_show_missing_codes' => $only_show_missing_codes
);

$filter = new CFilterCotation($filters);
$periods = $filter->getPeriods();
$results = $filter->getStatsByPeriod();

$smarty = new CSmartyDP();
$smarty->assign('chir_id'                 , $chir_id);
$smarty->assign('end_date'                , $end_date);
$smarty->assign('begin_date'              , $begin_date);
$smarty->assign('sejour_type'             , $sejour_type);
$smarty->assign('only_show_missing_codes' , $only_show_missing_codes);
$smarty->assign('sejour_types'            , $sejour_types);
$smarty->assign('chir_list'               , $chir_list);
$smarty->assign('periods'                 , $periods);
$smarty->assign('results'                 , $results);
$smarty->display('vw_cotations');