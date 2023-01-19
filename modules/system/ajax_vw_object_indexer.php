<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();
$index_name = CView::get('index_name', 'str notNull');
CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign('index_name', $index_name);
$smarty->display("inc_vw_object_indexer");