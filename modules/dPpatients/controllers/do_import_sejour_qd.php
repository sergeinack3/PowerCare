<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CCSVImportSejours;

CCanDo::checkAdmin();
ini_set("auto_detect_line_endings", true);

$start    = CView::post("start", 'num default|0');
$count    = CView::post("count", 'num default|100');
$maj      = CView::post("maj", 'str default|0');
$by_NDA   = CView::post("by_NDA", 'str default|0');
$callback = CView::post("callback", 'str');

CView::checkin();

CApp::setTimeLimit(600);
CApp::setMemoryLimit("1024");
CApp::disableCacheAndHandlers();
CAppUI::stepAjax("Désactivation du gestionnaire", UI_MSG_OK);

CMbObject::$useObjectCache = false;

$import_sejours = new CCSVImportSejours($start, $count);
$import_sejours->setOptions($maj, $by_NDA);

$ret = $import_sejours->import();

$start += $count;

file_put_contents(CAppUI::conf("root_dir") . "/tmp/import_cegi_sejour.txt", "$start;$count");

echo CAppUI::getMsg();

if ($callback && $ret) {
  CAppUI::js("$callback($start,$count)");
}

CMbObject::$useObjectCache = true;
CApp::rip();