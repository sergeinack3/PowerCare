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

$class = CView::get('class', 'str notNull');

CView::checkin();

CView::enforceSlave();

$ref_check = new CRefCheckTable();
$ref_check->class = $class;
$ref_check->loadMatchingObjectEsc();

$ref_check->prepareRefFields();

$smarty = new CSmartyDP();
$smarty->assign('ref_check_table', $ref_check);
$smarty->assign('class', $class);
$smarty->display('vw_class_integrity');