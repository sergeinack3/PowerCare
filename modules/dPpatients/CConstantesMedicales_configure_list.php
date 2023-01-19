<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::checkAdmin();

$constants     = CView::post('constants', 'str');
$context_guids = explode('|', CView::post('context_guids', 'str'));

CView::checkin();

$context = null;
if (count($context_guids)) {
  $guid = $context_guids[0];
  if ($guid != 'global') {
    $context = CMbObject::loadFromGuid($context_guids[0]);
  }
}

$smarty = new CSmartyDP();
$smarty->assign('constants', CConstantesMedicales::getConstantsByRank('form', false, $context, true));
$smarty->assign('checked', explode('|', $constants));
$smarty->display('constantes_configs/inc_constants_list');