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

set_time_limit(600);

CCanDo::checkRead();

$chir_id                  = CView::get('chir_id', 'num default|0', true);
$end_date                 = CView::get('end_date', array('date', 'default' => CMbDT::date()), true);
$begin_date               = CView::get('begin_date', array('date', 'default' => CMbDT::date('-1 week', $end_date)), true);
$only_show_missing_codes  = CView::get('only_show_missing_codes', 'bool default|0', true);
$sejour_type              = CView::get('sejour_type', 'str default|all', true);
$start                    = CView::get('start', 'num default|0');
$limit                    = CView::get('limit', 'num default|20');
$export                   = CView::get('export', 'num default|0');

CView::checkin();
CView::enforceSlave();

$filters = array(
  'chir_id' => $chir_id,
  'begin_date' => $begin_date,
  'end_date' => $end_date,
  'sejour_type' => $sejour_type,
  'object_classes' => array('COperation'),
  'show_unexported_acts' => 1,
  'objects_without_codes' => 1,
  'only_show_missing_codes' => $only_show_missing_codes
);

$filter = new CFilterCotation($filters);
$periods = $filter->getPeriods();
$results = $filter->getStatsByPeriod($start, $limit);

if ($export) {
  $file = $filter->exportStatsByPeriod($periods, $results);
  $file->stream(str_replace(' ', '_', CAppUI::tr('mod-dPpmsi-tab-vw_cotations')) . "_{$begin_date}_{$end_date}");
  CApp::rip();
}
else {
  $smarty = new CSmartyDP();
  $smarty->assign('periods', $periods);
  $smarty->assign('results', $results);
  $smarty->display('cotations/inc_statistics');
}