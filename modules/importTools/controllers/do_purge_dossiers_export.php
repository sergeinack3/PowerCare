<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

$author_id = CView::post("author_id", "ref class|CUser notNull");
$continue  = CView::post("continue", "bool default|0");
$dry_run   = CView::post("dry_run", "bool default|0");
$reset     = CView::post('reset', 'bool default|0');
$start     = CView::post("start", "num default|0");
$step      = CView::post("step", "num default|10");

CView::checkin();

CView::enforceSlave();

$ds = CSQLDataSource::get("std");

$where = array(
  "file_name" => $ds->prepareLike("Dossier complet%"),
  "author_id" => $ds->prepare("= ?", $author_id),
);

$file  = new CFile();
$files = $file->loadList($where, null, "$start,$step");

$count = (is_array($files)) ? count($files) : 0;

$cache = new Cache('ExportDossierComplet', $author_id, Cache::INNER_DISTR);
if ($reset && $cache->exists()) {
  $cache->rem();
}

$list_files = $cache->get() ?: [];

foreach ($files as $_file) {
  $list_files[] = [
    $_file->file_id, $_file->file_name, $_file->file_real_filename, $_file->_file_path
  ];
}

$cache->put($list_files);

if (!$dry_run) {
  CView::disableSlave();

  foreach ($files as $_file) {
    if ($msg = $_file->purge()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
  }
}
else {
  CAppUI::setMsg($count . " fichiers traités");
  CAppUI::setMsg(count($list_files) . " fichiers traités au total");
}

echo CAppUI::getMsg();

if ($continue && $count > 0) {
  if ($dry_run) {
    $next_start = $start + $step;
    CAppUI::js("nextPurge('$author_id', '$next_start');");
  }
  else {
    CAppUI::js("nextPurge('$author_id');");
  }
}
