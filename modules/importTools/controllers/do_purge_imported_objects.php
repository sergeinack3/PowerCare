<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Import\ImportTools\CPurgeImportedObjects;

CCanDo::checkAdmin();

$purge_class = CView::post("purge_classes", "enum list|" . implode('|', CPurgeImportedObjects::$purge_classes) . " notNull");
$start       = CView::post("start", 'num default|0');
$step        = CView::post("step", 'num default|100');
$audit       = CView::post("audit", "num");
$continue    = CView::post("continue", "num");
$import_tag  = CView::post("import_tag", "str notNull");

CView::checkin();

if (!$purge_class || !$import_tag || !class_exists($purge_class)) {
  CApp::rip();
}

$start = $start ?: 0;
$step  = $step ?: 100;

$purge_objects = new CPurgeImportedObjects($purge_class, $import_tag, $start, $step, $audit);
$objects = $purge_objects->purge();

dump($objects);

echo CAppUI::getMsg();

if ($objects) {
  if ($audit) {
    $start += $step;
    CAppUI::js("nextAudit('$start');");
  }
  elseif ($continue) {
    CAppUI::js("getForm('purge-imported-objects').onsubmit();");
  }
}
