<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$source_guid       = CView::get("source_guid"      , "str");
$current_directory = CView::get("current_directory", "str");
CView::checkin();

$max_size = ini_get("upload_max_filesize");
$source = CMbObject::loadFromGuid($source_guid);

//template
$smarty = new CSmartyDP();
$smarty->assign("source_guid"      , $source_guid);
$smarty->assign("current_directory", $current_directory);
$smarty->assign("max_size"         , $max_size);

$smarty->display("inc_add_file.tpl");