<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

CView::checkin();

$manager = new CRGPDManager(CGroups::loadCurrent()->_id);

$smarty = new CSmartyDP();
$smarty->assign('manager', $manager);
$smarty->assign('classes', CRGPDManager::getCompliantClasses());
$smarty->display('vw_compare_rgpd_configurations');