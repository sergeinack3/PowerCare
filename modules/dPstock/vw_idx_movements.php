<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductMovement;

CCanDo::checkEdit();

$datetime_min = CView::get('_datetime_min', 'dateTime', true);
$datetime_max = CView::get('_datetime_max', 'dateTime', true);
$account      = CView::get('account', 'str', true);
$origin_class = CView::get('origin_class', 'str', true);

CView::checkin();

$movement                = new CProductMovement();
$movement->_datetime_min = $datetime_min;
$movement->_datetime_max = $datetime_max;
$movement->account       = $account;
$movement->origin_class  = $origin_class;

$smarty = new CSmartyDP();
$smarty->assign("movement", $movement);
$smarty->display('vw_idx_movements.tpl');

