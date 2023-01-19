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
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$chir_id      = CValue::get('chir_id');
$end_date     = CValue::get('end_date');
$begin_date   = CValue::get('begin_date');
$sejour_type  = CValue::get('sejour_type', 'all');
$period       = CValue::get('period');

CView::enforceSlave();

$chir = CMediusers::get($chir_id);

$filters = array(
  'chir_id' => $chir_id,
  'begin_date' => $begin_date,
  'end_date' => $end_date,
  'sejour_type' => $sejour_type,
  'object_classes' => array('COperation'),
  'show_unexported_acts' => 1,
  'objects_without_codes' => 1
);

$filter = new CFilterCotation($filters);
$objects = $filter->getCotationDetails($period);

$smarty = new CSmartyDP();
$smarty->assign('chir', $chir);
$smarty->assign('operations', $objects['COperation']);
$smarty->assign('period', $period);
$smarty->assign('begin_date', CMbDT::dateToLocale($begin_date));
$smarty->assign('end_date', CMbDT::dateToLocale($end_date));
$smarty->display('cotations/inc_details_operations');