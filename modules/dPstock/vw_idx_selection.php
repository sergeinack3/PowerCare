<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkEdit();

$keywords = CValue::getOrSession('keywords');
$start    = CValue::getOrSession('start');
$letter   = CValue::getOrSession('letter');

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('keywords', $keywords);
$smarty->assign('start', $start);
$smarty->assign('letter', $letter);

$smarty->display('vw_idx_selection.tpl');
