<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkAdmin();

$manager = new CRGPDManager(CGroups::loadCurrent()->_id);

$source_smtp = $manager->getRGPDSource();

if (!$source_smtp || !$source_smtp->_id) {
  $source_smtp       = new CSourceSMTP();
  $source_smtp->name = $manager->getRGPDSourceName();
}

$rgpd_user = new CMediusers();

if ($rgpd_user_id = $manager->getRGPDUserID()) {
  $rgpd_user->load($rgpd_user_id);
}

$classes         = $manager::getCompliantClasses();
$total_by_status = CRGPDConsent::getCountByStatus();

$smarty = new CSmartyDP();
$smarty->assign('manager', $manager);
$smarty->assign('source_smtp', $source_smtp);
$smarty->assign('rgpd_user', $rgpd_user);
$smarty->assign('classes', $classes);
$smarty->assign('total_by_status', $total_by_status);
$smarty->display('vw_rgpd');