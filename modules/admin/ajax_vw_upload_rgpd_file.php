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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Admin\Rgpd\IRGPDCompliant;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::check();

$consent_id   = CView::get('consent_id', 'ref class|CRGPDConsent');
$object_class = CView::get('object_class', 'str');
$object_id    = CView::get('object_id', 'ref meta|object_class');

CView::checkin();

if (!$consent_id && (!$object_class || !$object_id)) {
  CAppUI::commonError();
}

if ($consent_id) {
  $consent = new CRGPDConsent();
  $consent->load($consent_id);
}
else {
  /** @var IRGPDCompliant $object */
  $object = CStoredObject::loadFromGuid("{$object_class}-{$object_id}");

  if (!$object || !$object->_id) {
    CAppUI::commonError();
  }

  $manager = new CRGPDManager(CGroups::loadCurrent()->_id);
  $consent = $manager->getOrInitConsent($object);
}

if (!$consent || !$consent->_id) {
  CAppUI::commonError();
}

$consent->loadTargetObject();

$smarty = new CSmartyDP();
$smarty->assign('consent', $consent);
$smarty->display('vw_upload_rgpd_file');