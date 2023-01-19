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
use Ox\Mediboard\Mediusers\CFunctions;

$sort_columns = [
  'patient_id', 'executant_id', 'prescripteur_id', 'execution', 'code', 'quantite',
  'coefficient', 'complement', 'montant_base', 'montant_depassement', '_tarif'
];

CCanDo::checkRead();

$chir_id        = CView::get('chir_id', 'ref class|CMediusers', true);
$function_id    = CView::get('function_id', 'ref class|CFunctions', true);
$end_date       = CView::get('end_date', ['date', 'default' => CMbDT::date()], true);
$begin_date     = CView::get('begin_date', ['date', 'default' => CMbDT::date('-1 week', $end_date)], true);
$object_classes = CView::get('object_classes', 'str default|CSejour', true);
$begin_sejour   = CView::get('begin_sejour', 'date', true);
$end_sejour     = CView::get('end_sejour', 'date', true);
$sort_column    = CView::get('sort_column', ['enum',  'list' => implode('|', $sort_columns),  'default' => 'executant_id'], true);
$sort_way       = CView::get('sort_way', 'enum list|ASC|DESC default|DESC', true);

CView::checkin();
CView::enforceSlave();

if ($object_classes != '') {
  $object_classes = explode('|', $object_classes);
}
else {
  $object_classes = array('CSejour');
}

$filters = array(
  'chir_id'         => $chir_id,
  'function_id'     => $function_id,
  'begin_date'      => $begin_date,
  'end_date'        => $end_date,
  'begin_sejour'    => $begin_sejour,
  'end_sejour'      => $end_sejour,
  'object_classes'  => $object_classes
);

$filter = new CFilterCotation($filters);
$results = $filter->getCotationsNGAP(0, 40, $sort_column, $sort_way);

$smarty = new CSmartyDP();
$smarty->assign('chir'          , CMediusers::findOrNew($chir_id));
$smarty->assign('function'      , CFunctions::findOrNew($function_id));
$smarty->assign('end_date'      , $end_date);
$smarty->assign('begin_date'    , $begin_date);
$smarty->assign('begin_sejour'  , $begin_sejour);
$smarty->assign('end_sejour'    , $end_sejour);
$smarty->assign('object_classes', $object_classes);
$smarty->assign('sort_column'   , $sort_column);
$smarty->assign('sort_way'      , $sort_way);
$smarty->assign('results'       , $results);
$smarty->display('vw_cotations_ngap');