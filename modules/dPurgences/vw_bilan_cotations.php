<?php

/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$chir_id        = CView::get('chir_id', 'ref class|CMediusers', true);
$speciality     = CView::get('speciality', 'num', true);
$function_id    = CView::get('function_id', 'ref class|CFunctions', true);
$object_classes = CView::get('object_classes', 'str', true);
$end_date       = CView::get('end_date', array('date', 'default' => CMbDT::date()), true);
$begin_date     = CView::get('begin_date', array('date', 'default' => CMbDT::date('-1 week', $end_date)), true);
$nda            = CView::get('nda', 'str', true);
$begin_sejour   = CView::get('begin_sejour', 'date', true);
$end_sejour     = CView::get('end_sejour', 'date', true);
$sort_column    = CView::get('sort_column', 'enum list|patient_id|executant_id|execution default|patient_id', true);
$sort_way       = CView::get('sort_way', 'enum list|ASC|DESC default|DESC', true);

CView::checkin();
CView::enforceSlave();

if ($object_classes !== '' && !is_null($object_classes)) {
  $object_classes = explode('|', $object_classes);
}
else {
  $object_classes = ['CSejour', 'CConsultation', 'COperation'];
}

$filters = array(
  'chir_id'        => $chir_id,
  'speciality'     => $speciality,
  'function_id'    => $function_id,
  'nda'            => $nda,
  'begin_date'     => $begin_date,
  'end_date'       => $end_date,
  'begin_sejour'   => $begin_sejour,
  'end_sejour'     => $end_sejour,
  'object_classes' => $object_classes,
  'sejour_type'    => CSejour::getTypesSejoursUrgence()
);

$filter  = new CFilterCotation($filters);
$results = $filter->getBilanCotation(0, 30, $sort_column, $sort_way);

$smarty = new CSmartyDP();
$smarty->assign('chir'          , CMediusers::findOrNew($chir_id));
$smarty->assign('function'      , CFunctions::findOrNew($function_id));
$smarty->assign('speciality'    , $speciality);
$smarty->assign('specialities'  , CSpecCPAM::getList());
$smarty->assign('end_date'      , $end_date);
$smarty->assign('begin_date'    , $begin_date);
$smarty->assign('begin_sejour'  , $begin_sejour);
$smarty->assign('end_sejour'    , $end_sejour);
$smarty->assign('object_classes', $object_classes);
$smarty->assign('nda'           , $nda);
$smarty->assign('sort_column'   , $sort_column);
$smarty->assign('sort_way'      , $sort_way);
$smarty->assign('results'       , $results);
$smarty->display('vw_bilan_cotation.tpl');