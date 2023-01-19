<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\Mutex\CMbFileMutex;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchIndexing;

CCanDo::checkAdmin();

$continue = CView::get('continue', 'str default|0');

CView::checkin();

$lock = new CMbFileMutex("search_indexing");

if (!$lock->lock(600)) {
  echo "Script locked search_indexing\n";
  return;
}

CApp::setTimeLimit(600);
CApp::setMemoryLimit("1024M");

$t     = time();
$count = null;


// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}


// Bulk
try {
  // Passage à l'indexation en tps réel pour améliorer la performance du bulk indexing
  //  $search->updateIndexSettings(null, array("refresh_interval" => "-1", "number_of_replicas" => 0));

  // Recupere les datas à indexer (+update)
  CView::enforceSlave();
  $data       = CSearchIndexing::getDataToIndex(CSearch::getIndexingStep());
  $count_data = 0;
  CView::disableSlave();

  // Index docs
  if (isset($data['index'])) {
    $count_data += count($data['index']);
    $search->bulkIndexing($data['index'], true);
  }

  // Delete docs
  if (isset($data['delete'])) {
    $count_data += count($data['delete']);
    $search->deleteDocs($data['delete']);
  }

  // Display
  CAppUI::displayAjaxMsg("L'indexation s'est correctement déroulée ", UI_MSG_OK);

  // on remet le paramètre à défaut
  //$search->updateIndexSettings(null, array("refresh_interval" => "1s"));

  // optimise le pas
  CSearch::adaptStep($count_data, time() - $t);

  // libère le lock
  $lock->release();

  // Trace
  dump($data);
}
catch (Exception $e) {
  CAppUI::displayAjaxMsg("L'indexation a rencontré un problème %s", UI_MSG_WARNING, $e->getMessage());
  $lock->release();
}

if ($continue) {
  CAppUI::js("Search.indexData();");
}

CApp::rip();
