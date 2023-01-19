<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFilePathCorrector;

CCanDo::checkAdmin();

$count_only = CView::post('count', 'bool default|0');
$old_path   = CView::post('old_path', 'str');
$old_size   = CView::post('old_size', 'str default|2,2,2');
$date_min   = CView::post('date_min', 'dateTime');
$date_max   = CView::post('date_max', 'dateTime');
$copy_files = CView::post('copy', 'bool default|0');
$start      = CView::post('start', 'num default|0');
$step       = CView::post('step', 'num default|100');
$continue   = CView::post('continue', 'bool default|0');

CView::checkin();

$old_size = ($old_size) ? explode(',', $old_size) : [2, 2, 2];

$corrector = new CFilePathCorrector($old_path, $old_size, $date_min, $date_max, $start, $step);

if ($count_only) {
  $count = $corrector->countFiles();
  CAppUI::setMsg("$count fichiers présents pour l'interval de date demandé.");

  $counts = $corrector->correctFiles(true);
}
else {
  $counts = $corrector->correctFiles(false, $copy_files);
}

if (!$counts) {
  CAppUI::setMsg('Correction terminée');
  echo CAppUI::getMsg();
  CApp::rip();
}

CAppUI::setMsg("{$counts['ok']} fichiers sur la configuration actuelle");
CAppUI::setMsg("{$counts['ok_old']} fichiers sur l'ancienne configuration");
CAppUI::setMsg("{$counts['nok']} fichiers présents sur aucune des deux configurations");

echo CAppUI::getMsg();

if ($continue) {
  $start += $step;
  CAppUI::js("nextCorrection('{$start}')");
}