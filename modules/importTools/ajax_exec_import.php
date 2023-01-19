<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Chronometer;
use Ox\Core\CView;
use Ox\Core\Mutex\CMbMutex;
use Ox\Import\ImportTools\CImportCronLogs;
use Ox\Mediboard\Files\CFile;


CCanDo::checkAdmin();

// Variables pour l'import
$module           = CView::get('import_module', 'str notNull');
$import_class     = CView::get('import_class', 'str notNull');
$limit            = CView::get('limit', 'bool default|1');
$count            = CView::get('count', 'num default|200');
$reimport         = CView::get('reimport', 'bool default|0');
$excluded_classes = CView::get('exclude', 'str');
// Variable pour la gestion du temps d'import
$adapt_step    = CView::get('adapt_step', 'bool default|1');
$max_exec_time = CView::get('max_exec_time', 'num default|290');
$max_memory    = CView::get('max_memory', 'num default|200');
$handlers      = CView::get('handlers', 'str default|0');
$one_class     = CView::get('one_class', 'str');

CView::checkin();

if (!$module || !$import_class) {
  CApp::rip();
}

$excluded = array();
if ($excluded_classes) {
  $excluded = explode('|', $excluded_classes);
}
$excluded[] = 'count';

CFile::$migration_enabled = false;

// Lock
$lock = new CMbMutex($import_class);
if (!$lock->lock(120)) {
  CAppUI::stepAjax("Verrou présent ($import_class)");
  CImportCronLogs::createLogs($module, $import_class, 'error', "Verrou présent ($import_class)");

  return;
}

// Set system limits
CApp::setTimeLimit(600);
CApp::setMemoryLimit("1024M");

if (!$handlers) {
  CApp::disableCacheAndHandlers();
}

$cache = new Cache('import_progress', $module, Cache::OUTER | Cache::DISTR);

$progression_import = $cache->get();
if (!$progression_import) {
  $progression_import = array();
  foreach ($import_class::$import_sequence as $_class_import) {
    $progression_import[$_class_import] = array(
      'is_finished' => false,
      'last_id'     => 0
    );
  }
}

$last_memory = 0;
$chrono      = new Chronometer();
$chrono->start();

foreach ($progression_import as $_class => $_infos) {
  if (in_array($_class, $excluded) || ($one_class && $one_class != $_class)) {
    continue;
  }

  if ($progression_import[$_class]['is_finished']) {
    continue;
  }

  $last_id = CImportCronLogs::importByClass(
    $module, $_class, $count, $reimport, null, null, null, $progression_import[$_class]['last_id'], $limit
  );

  $msg = CAppUI::getMsg();
  CImportCronLogs::parseMsg($msg, $module, $_class);

  CApp::log($msg);

  if ($last_id === $progression_import[$_class]['last_id'] || !$last_id) {
    $progression_import[$_class]['is_finished'] = true;
    CImportCronLogs::createLogs($module, $_class, 'info', "Fin de l'import pour $_class");
  }
  else {
    $progression_import[$_class]['last_id'] = $last_id;
  }

  $chrono->step($_class);

  // S'il ne reste plus assez de temps pour faire un tour d'import on break plutôt que d'annuler le prochain cron
  if ($chrono->total > $max_exec_time || ($max_exec_time - $chrono->total) <= $chrono->latestStep) {
    break;
  }

  if ($adapt_step) {
    $memory_used = memory_get_usage(true) / (1024 * 1024);

    if (($memory_used > $max_memory) || ($memory_used < $max_memory && $chrono->latestStep >= $max_exec_time / 2)) {
      // Le nombre d'objets commence à descendre donc la mémoire n'augmente plus mais elle stagne.
      if ($last_memory == $memory_used) {
        continue;
      }
      $count = round($count / 2);
    }
    elseif (($memory_used < $max_memory / 2)) {
      $count = $count * 2;
    }
    $last_memory = $memory_used;
  }

  break;
}

$lock->release();

$cache->put($progression_import);