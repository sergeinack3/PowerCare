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
use Ox\Core\CClassMap;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\CView;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

$import_class = CView::get("import_class", "str"); // Get this field from <module>/ajax_import

$import       = CView::get("import", "str");
$import       = stripslashes($import);
$count        = CView::get("count", "num default|20");
$chir_id      = CView::get("chir_id", "num");
$last_id      = CView::get("last_id", "str");
$order        = CView::get("order_by", "str");
$import_id    = CView::get("import_id", "str");
$continue     = CView::get("continue", "str default|0");
$date_min     = CView::get("date_min", "str");
$date_max     = CView::get("date_max", "str");
$debug        = CView::get("debug", "str default|0");
$patient_id   = CView::get("patient_id", "str");
$limit        = CView::get("limit", "str");
$reimport     = CView::get("reimport", "str default|0");
$correct_file = CView::get("correct_file", "str default|0");
$handlers     = CView::get("handlers", "str default|0");
$interval     = CView::get("interval", "num");

CView::setSession("chir_id", $chir_id);
CView::setSession("date_min", $date_min);
CView::setSession("date_max", $date_max);

CView::disableSlave();

CView::checkin();

CFile::$migration_enabled = false;
// Lock
$lock = new CMbMutex($import);
if (!$lock->lock(600)) {
  CAppUI::stepAjax("Verrou présent ($import)");

  return;
}

// Set system limits
CApp::setTimeLimit(600);
CApp::setMemoryLimit("1024M");

if (!$handlers) {
  CApp::disableCacheAndHandlers();
}

CExternalDBImport::setDebug($debug);

if ($import_id) {
  /** @var CExternalDBImport $object */
  $object = new $import;
  $object->importObject($import_id, true, $correct_file);
}
else {
  $start = 0;

  /** @var CExternalDBImport $import_class */
  $last_id = $import_class::importByClass(
    $import, $start, $count, $reimport, $chir_id, $order, $date_min, $date_max, $last_id, $limit, $patient_id, $correct_file
  );

  $import = CClassMap::getSN($import);

  if ($last_id) {
    CAppUI::js("\$V(getForm('import-$import').elements.last_id, '$last_id')");
  }

  if ($import_class::$_count_stored) {
    if ($interval) {
      $interval *= 1000;
      CAppUI::js("setTimeout('next$import()', $interval)");
    }
    else {
      CAppUI::js("next$import()");
    }
  }

  CAppUI::setMsg("Import terminé");
}

echo CAppUI::getMsg();

$lock->release();
