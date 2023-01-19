<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Forms\CExClassImport;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExClass;

CCanDo::checkEdit();

$uid      = CValue::post("file_uid");
$from_db  = CValue::post("fromdb");
$options  = CValue::post("options");
$group_id = CView::postRefCheckRead('group_id', 'ref class|CGroups');

$options = CMbArray::mapRecursive("stripslashes", $options);

CView::checkin();

$in_hermetic_mode = CExClass::inHermeticMode(false);
if ($in_hermetic_mode && !CMediusers::get()->isAdmin()) {
    $group_id = CGroups::loadCurrent()->_id;
}

set_time_limit(600);

$options["ignore_disabled_fields"] = isset($options["ignore_disabled_fields"]);

$uid  = preg_replace('/[^\d]/', '', $uid);
$temp = CAppUI::getTmpPath("ex_class_import");
$file = "$temp/$uid";

try {
    $import = new CExClassImport($file);

    if ($in_hermetic_mode) {
        $import->setGroupId($group_id);
    }

    $import->import($from_db, $options);
} catch (Exception $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
} 
