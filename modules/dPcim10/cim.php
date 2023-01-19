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

$code = CView::get('code', 'str');

CView::checkin();

$chapters = CCodeCIM10::getChapters(CCodeCIM10::FULL);

$code_cim = null;
if ($code) {
  $code_cim = CCodeCIM10::get($code, CCodeCIM10::FULL);
}

$smarty = new CSmartyDP();
$smarty->assign('code', $code_cim);
$smarty->assign('chapters', $chapters);
$smarty->assign('version', CCodeCIM10::getVersion());
$smarty->display('inc_cim.tpl');