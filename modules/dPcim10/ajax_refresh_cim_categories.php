<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;

CCanDo::checkRead();

$code = CView::get('chapter_code', 'str');

CView::checkin();

$chapter = CCodeCIM10::get($code, CCodeCIM10::FULL);

$smarty = new CSmartyDP();
$smarty->assign('chapter', $chapter);
$smarty->display('cim/inc_filter_category.tpl');