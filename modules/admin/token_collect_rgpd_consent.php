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
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;

CCanDo::check();

$object_id = CView::get('object_id', 'ref class|CRGPDConsent');
$i_consent = CView::get('consent', 'bool');

CView::checkin();

if (!$object_id || (($i_consent === null) || ($i_consent === ''))) {
  CAppUI::commonError();
}

$consent = new CRGPDConsent();
$consent->load($object_id);

if (!$consent || !$consent->_id) {
  CAppUI::commonError();
}

try {
  ($i_consent) ? $consent->markAsAccepted() : $consent->markAsRefused();
}
catch (Exception $e) {
  CAppUI::stepAjax($e->getMessage());
}

$smarty = new CSmartyDP();
$smarty->assign('consent', $consent);
$smarty->display('vw_token_rgpd_consent');
