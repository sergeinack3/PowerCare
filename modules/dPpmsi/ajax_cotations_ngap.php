<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CFilterCotation;

$sort_columns = [
  'patient_id', 'executant_id', 'prescripteur_id', 'execution', 'code', 'quantite',
  'coefficient', 'complement', 'montant_base', 'montant_depassement', '_tarif'
];

CCanDo::checkRead();

$chir_id        = CView::get('chir_id', 'ref class|CMediusers', true);
$function_id    = CView::get('function_id', 'ref class|CFunctions', true);
$patient_id     = CView::get('patient_id', 'ref class|CPatient');
$nda            = CView::get('nda', 'str');
$end_date       = CView::get('end_date', ['date', 'default' => CMbDT::date()], true);
$begin_date     = CView::get('begin_date', ['date', 'default' => CMbDT::date('-1 week', $end_date)], true);
$begin_sejour   = CView::get('begin_sejour', 'date', true);
$end_sejour     = CView::get('end_sejour', 'date', true);
$object_classes = CView::get('object_classes', 'str default|CSejour', true);
$start          = CView::get('start', 'num');
$number         = CView::get('number', 'num');
$sort_column    = CView::get('sort_column', ['enum',  'list' => implode('|', $sort_columns),  'default' => 'executant_id'], true);
$sort_way       = CView::get('sort_way', 'enum list|ASC|DESC default|DESC', true);
$action         = CView::get('action', 'enum list|export|print');

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
  'patient_id'      => $patient_id,
  'nda'             => $nda,
  'begin_date'      => $begin_date,
  'end_date'        => $end_date,
  'begin_sejour'    => $begin_sejour,
  'end_sejour'      => $end_sejour,
  'object_classes'  => $object_classes
);

$filter = new CFilterCotation($filters);
$results = $filter->getCotationsNGAP($start, $number, $sort_column, $sort_way, $action);

if ($action === 'export') {
  $file = $filter->exportCotationsNGAP($results);
  $file->stream(str_replace(' ', '_', CAppUI::tr('mod-dPpmsi-tab-vw_cotations_ngap')) . "_{$begin_date}_{$end_date}");
  CApp::rip();
}
else {
  $smarty = new CSmartyDP();
  $smarty->assign('results'    , $results);
  $smarty->assign('action'     , $action);
  $smarty->assign('sort_column', $sort_column);
  $smarty->assign('sort_way'   , $sort_way);
  $smarty->display('cotations/inc_cotations_ngap');
}