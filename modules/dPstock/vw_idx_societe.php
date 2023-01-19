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

CCanDo::checkEdit();

$societe_id    = CView::get("societe_id", "ref class|CSociete", true);
$suppliers     = CView::get("suppliers", "bool default|1", true);
$manufacturers = CView::get('manufacturers', "bool default|1", true);
$inactive      = CView::get('inactive', "bool default|1", true);

CView::checkin();

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('suppliers', $suppliers);
$smarty->assign('manufacturers', $manufacturers);
$smarty->assign('inactive', $inactive);

$smarty->display('vw_idx_societe.tpl');

