<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::check();

$i_consent = (bool)CView::post('consent', 'bool');

CView::checkin();

$current_user = CUser::get();

try {
  $manager = new CRGPDManager(CGroups::loadCurrent()->_id);
  $consent = $manager->getConsentForObject($current_user);
  $current_user->setRGPDConsent($consent);

  if (!$consent || !$consent->_id) {
    CAppUI::setMsg('CRGPDConsent-error-Cannot find consent');
  }

  ($i_consent) ? $consent->markAsAccepted() : $consent->markAsRefused();
}
catch (Exception $e) {
  CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
}

//CAppUI::js('document.location.reload()');
CAppUI::js("location.href = 'index.php'");

echo CAppUI::getMsg();

CApp::rip();