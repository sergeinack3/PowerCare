<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

CView::checkin();

$slave_exists = CAppUI::conf("db slave dbhost");

$smarty = new CSmartyDP();
$smarty->assign('slave_exists', $slave_exists);
$smarty->display('vw_db_std.tpl');