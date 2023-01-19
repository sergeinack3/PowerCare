<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CRefCheckTable;

CCanDo::checkAdmin();

$class = CView::get('class', 'str');
$order = CView::get('order', 'enum list|name|duration|state|errors default|name');

CView::checkin();

CView::enforceSlave();

$ref_check_table = new CRefCheckTable();

$all_class_check = $ref_check_table->loadList(null, 'class ASC');

$parsed_lines = 0;
$max_lines    = 0;
$start        = null;
$end          = null;
$all_ended    = true;
foreach ($all_class_check as $_ref_check) {
  $_ref_check->loadState();
  $parsed_lines += $_ref_check->_total_lines;
  $max_lines    += $_ref_check->_max_lines;
  $start        = ($start == null || ($_ref_check->start_date && $_ref_check->start_date < $start)) ? $_ref_check->start_date : $start;
  $end          = ($end == null || $_ref_check->end_date > $end) ? $_ref_check->end_date : $end;
  $all_ended    = ($all_ended && !$_ref_check->end_date) ? false : $all_ended;
  $_ref_check->prepareRefFields();
}

$end = ($all_ended) ? CMbDT::relativeDuration($start, $end) : null;

$progression = ($max_lines == 0) ? 0 : ($parsed_lines / $max_lines) * 100;

switch ($order) {
//  case 'duration':
//    CMbArray::pluckSort($all_class_check, SORT_DESC, '_duration');
//    break;
  case 'state':
    CMbArray::pluckSort($all_class_check, SORT_DESC, '_state');
    break;
  case 'errors':
    CMbArray::pluckSort($all_class_check, SORT_DESC, '_error_count');
    break;
  default:
    // Do nothing
}

$smarty = new CSmartyDP();
$smarty->assign('class', $class);
$smarty->assign('progression', $progression);
$smarty->assign('max_lines', $max_lines);
$smarty->assign('parsed_lines', $parsed_lines);
$smarty->assign('start', $start);
$smarty->assign('end', $end ? $end['locale'] : '');
$smarty->assign('classes', $all_class_check);
$smarty->assign('order', $order);
$smarty->display('inc_vw_references');
