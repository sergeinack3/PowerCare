<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Mediboard\System\CSourceFileSystem;

CCanDo::checkAdmin();

$current_directory = CView::get("current_directory", "str");
$delete            = CView::get("delete", "str", false);
$rename            = CView::get("rename", "str", false);
$new_name          = CView::get("new_name", "str");
$file              = CView::get("file", "str");
$source_guid       = CView::get("source_guid", "str");
CView::checkin();

/** @var CSourceFTP|CSourceSFTP|CSourceFileSystem $source */
$exchange_source = CMbObject::loadFromGuid($source_guid);

try {
    if ($delete && $file) {
        $exchange_source->getClient()->delFile($file, $current_directory);
    }

    if ($rename && $new_name) {
        $exchange_source->getClient()->renameFile($file, $new_name);
    }

    $current_directory = $exchange_source->getClient()->getCurrentDirectory($current_directory);
    $files             = $exchange_source->getClient()->getListFilesDetails($current_directory);
} catch (CMbException $e) {
    CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
}
$smarty = new CSmartyDP();
$smarty->assign("files", $files);
$smarty->assign("current_directory", $current_directory);
$smarty->assign("source_guid", $source_guid);
$smarty->display("inc_manage_file.tpl");
