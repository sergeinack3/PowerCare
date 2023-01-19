<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CView;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;

CCanDo::checkAdmin();

$min_generation_datetime = CView::post('_min_generation_datetime', 'dateTime');
$max_generation_datetime = CView::post('_max_generation_datetime', 'dateTime');
$start                   = CView::post('start', 'num default|0');
$auto                    = CView::post('auto', 'str');
$dry_run                 = CView::post('dry_run', 'str');

CView::checkin();

$auto    = ($auto === 'on');
$dry_run = ($dry_run === 'on');
$start   = (is_int($start)) ? $start : 0;

$consent = new CRGPDConsent();
$ds      = $consent->getDS();

$where = array(
  $consent->getSpec()->key => $ds->prepare('> ?', $start),
);

if ($min_generation_datetime) {
  $where[] = $ds->prepare('`generation_datetime` >= ?', $min_generation_datetime);
}

if ($max_generation_datetime) {
  $where[] = $ds->prepare('`generation_datetime` <= ?', $max_generation_datetime);
}

$order = "{$consent->getSpec()->key} ASC";
$limit = "50";

$total = $consent->countList($where);

CAppUI::stepAjax(sprintf("Nombre de consentements à purger au total : %d", $total), UI_MSG_OK);

if ($dry_run) {
  return;
}

$request = new CRequest();
$request->addTable($consent->getSpec()->table);
$request->addWhere($where);
$request->addOrder($order);
$request->setLimit($limit);

$count = $ds->loadResult($request->makeSelectCount());
$count = min($count, $limit);

CAppUI::stepAjax(sprintf("Nombre de consentements à purger durant cette passe : %d", $count), UI_MSG_OK);

$consents = $consent->loadList($where, $order, $limit);

if (!$consents) {
  CAppUI::stepAjax("Purge terminée", UI_MSG_OK);

  return;
}

$last_id = CMbArray::pluck($consents, '_id');
$last_id = max($last_id);

/** @var CRGPDConsent $_consent */
foreach ($consents as $_consent) {
  if ($_msg = $_consent->purge()) {
    CAppUI::stepAjax($_msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::stepAjax(sprintf("Consentement purgé"), UI_MSG_OK);
  }
}

CAppUI::js("\$V(getForm('purge-rgpd-consents').elements.start, '$last_id')");

if ($auto) {
  CAppUI::js("getForm('purge-rgpd-consents').onsubmit()");
}
