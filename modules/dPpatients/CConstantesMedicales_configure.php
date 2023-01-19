<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::checkAdmin();
CView::checkin();

$schemas = array(
  'constantes / CService CGroups.group_id',
  'constantes / CFunctions CGroups.group_id',
  'constantes / CBlocOperatoire CGroups.group_id'
);

$smarty = new CSmartyDP();
$smarty->assign('schemas', $schemas);
$smarty->assign('groups', CGroups::loadGroups());
$smarty->assign('constants', CConstantesMedicales::getConstantsByRank('form', false, CGroups::loadCurrent(), true));
$smarty->display('CConstantesMedicales_configure.tpl');