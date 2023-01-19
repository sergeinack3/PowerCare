<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

/**
 * View functions
 */
CCanDo::checkRead();

$page    = CView::get('page', 'num default|0');
$inactif = CView::get("inactif", "str");
$type    = CView::get("type", "str");
CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("inactif", $inactif);
$smarty->assign("page"   , $page);
$smarty->assign("type"   , $type );

$smarty->display("vw_idx_functions.tpl");
