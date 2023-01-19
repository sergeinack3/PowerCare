<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClass;

if (CExClass::inHermeticMode(false)) {
    CCanDo::checkAdmin();
} else {
    CCanDo::checkRead();
}

// Paramétrage
$param_min_count = CValue::getOrSession("param_min_count");

// Saisie
$ex_object_date_min        = CValue::getOrSession('ex_object_date_min', CMbDT::date('first day of last month'));
$ex_object_date_max        = CValue::getOrSession('ex_object_date_max', CMbDT::date('first day of this month'));
$ex_object_grouping        = CValue::getOrSession('ex_object_grouping', 'day');
$ex_object_limit_threshold = CValue::getOrSession('ex_object_limit_threshold', 3);

// Remplissage
$ex_object_filling_date_max      = CValue::getOrSession('ex_object_filling_date_max', CMbDT::date());
$ex_object_filling_date_min      = CValue::getOrSession('ex_object_filling_date_min', CMbDT::date('last monday', $ex_object_filling_date_max));
$ex_object_filling_grouping      = CValue::getOrSession('ex_object_filling_grouping', 'day');
$ex_object_filling_min_threshold = CValue::getOrSession('ex_object_filling_min_threshold', 100);
$ex_object_filling_max_threshold = CValue::getOrSession('ex_object_filling_max_threshold');

$smarty = new CSmartyDP();
$smarty->assign("param_min_count", $param_min_count);
$smarty->assign('ex_object_date_min', $ex_object_date_min);
$smarty->assign('ex_object_date_max', $ex_object_date_max);
$smarty->assign('ex_object_grouping', $ex_object_grouping);
$smarty->assign('ex_object_limit_threshold', $ex_object_limit_threshold);
$smarty->assign('ex_object_filling_date_min', $ex_object_filling_date_min);
$smarty->assign('ex_object_filling_date_max', $ex_object_filling_date_max);
$smarty->assign('ex_object_filling_grouping', $ex_object_filling_grouping);
$smarty->assign('ex_object_filling_min_threshold', $ex_object_filling_min_threshold);
$smarty->assign('ex_object_filling_max_threshold', $ex_object_filling_max_threshold);
$smarty->display("view_stats.tpl");
