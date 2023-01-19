<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::check();

$object_class = CView::get('object_class', 'enum list|' . implode('|', CRGPDManager::getCompliantClasses()) . ' notNull');
$stylized     = CView::get('stylized', 'bool default|1');

CView::checkin();

if (!$object_class || !in_array($object_class, CRGPDManager::getCompliantClasses())) {
  CAppUI::commonError();
}

$manager = new CRGPDManager(CGroups::loadCurrent()->_id);

$smarty = new CSmartyDP('modules/admin');
$smarty->assign('manager', $manager);
$smarty->assign('object_class', $object_class);
$smarty->assign('stylized', $stylized);
$smarty->display('inc_vw_rgpd_document');