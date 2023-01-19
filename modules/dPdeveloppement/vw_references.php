<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CRefCheckTable;

CCanDo::checkAdmin();

// TODO handle the choice of a class
$class = CView::get('class', 'str');

CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign('chunks', array_keys(CRefCheckTable::$_chunk_size));
$smarty->display('vw_references');