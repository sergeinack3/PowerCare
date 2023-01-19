<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;

CCanDo::checkEdit();

$tmp_filename   = $_FILES["import"]["tmp_name"];
$ignore_similar = CView::post('ignore_similar', 'bool default|0');

CView::checkin();

$in_hermetic_mode = CExClass::inHermeticMode(false);
if ($in_hermetic_mode) {
    $ignore_similar = '1';
}

$dom = new DOMDocument();
$dom->load($tmp_filename);

$xpath = new DOMXPath($dom);
if ($xpath->query("/mediboard-export")->length == 0) {
    CAppUI::js("window.parent.ExClass.uploadError()");
    CApp::rip();
}

$temp     = CAppUI::getTmpPath("ex_class_import");
$uid      = preg_replace('/[^\d]/', '', uniqid("", true));
$filename = "$temp/$uid";
CMbPath::forceDir($temp);

move_uploaded_file($tmp_filename, $filename);

// Cleanup old files (more than 4 hours old)
$other_files = glob("$temp/*");
$now         = time();
foreach ($other_files as $_other_file) {
    if (filemtime($_other_file) < $now - 3600 * 4) {
        unlink($_other_file);
    }
}

CAppUI::js("window.parent.ExClass.uploadSaveUID('$uid', '$ignore_similar')");
CApp::rip();
