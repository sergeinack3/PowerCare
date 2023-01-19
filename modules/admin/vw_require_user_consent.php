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
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::check();

$rgpd_user = CUser::get();

try {
  $manager = new CRGPDManager(CGroups::loadCurrent()->_id);
  $consent = $manager->getOrInitConsent($rgpd_user);

  if (!$consent->isAccepted() && !$consent->isRefused()) {
    $consent->markAsRead();
  }
}
catch (Exception $e) {
  CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
}

$smarty = new CSmartyDP('modules/admin');
$smarty->assign('user', $rgpd_user);
$smarty->assign('consent', $consent);
$smarty->display('vw_require_user_consent');
