<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Core\CMbException;

CCanDo::checkAdmin();

$new_directory = CView::get("new_directory", "str");
$source_guid   = CView::get("source_guid", "str");
CView::checkin();

/** @var CSourceFTP|CSourceSFTP $source */
$source = CMbObject::loadFromGuid($source_guid);

try {
    $current_directory = $source->getClient()->getCurrentDirectory($new_directory);
    $directory         = $source->getClient()->getListDirectory($current_directory);
    $root              = $source->getRootDirectory($current_directory);
}catch(CMbException $e){
    CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
}

$smarty = new CSmartyDP();

$smarty->assign("current_directory", $current_directory);
$smarty->assign("root"             , $root);
$smarty->assign("directory"        , $directory);
$smarty->assign("source_guid"      , $source_guid);

$smarty->display("inc_manage_directory.tpl");
