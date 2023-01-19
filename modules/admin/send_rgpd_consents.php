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
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$group_id = CView::get('group_id', 'ref class|CGroups');
$limit    = CView::get('limit', 'num default|20');

CView::checkin();

$group_id = ($group_id) ?: CGroups::loadCurrent()->_id;

$manager     = new CRGPDManager($group_id);
$smtp_source = $manager->getRGPDSource();

if (!$smtp_source || !$smtp_source->_id) {
  CApp::log('CSourceSMTP.none', null, LoggerLevels::LEVEL_ERROR);

  return;
}

$compliant_classes = [];
foreach ($manager::getCompliantClasses() as $_compliant_class) {
  if ($manager->isHandlerEnabled() && $manager->canNotify($_compliant_class)) {
    $compliant_classes[] = $_compliant_class;
  }
}

$consent = new CRGPDConsent();
$ds      = $consent->getDS();
$table   = $consent->getSpec()->table;
$key     = $consent->getSpec()->key;

$where = array(
  "{$table}.group_id"   => $ds->prepare('= ?', $group_id),
  "{$table}.status"     => $ds->prepare('= ?', CRGPDConsent::STATUS_TO_SEND),
  "{$table}.last_error" => 'IS NULL',
);

$order = "{$table}.{$key} ASC";
$limit = ($limit) ?: 20;

$consents = $consent->loadList($where, $order, $limit);

$sent = 0;
foreach ($consents as $_consent) {
  try {
    $manager->send($_consent, $smtp_source);
  }
  catch (Exception $e) {
    if ($msg = $_consent->setLastError($e->getMessage())->store()) {
      CApp::log($msg, null, LoggerLevels::LEVEL_ERROR);
    }

    continue;
  }

  $sent++;
}

CApp::log(CAppUI::tr('CRGPDConsent-msg-%d sent|pl', $sent));
