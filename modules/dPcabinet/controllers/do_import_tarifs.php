<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$chir_id      = CView::post('chir_id', 'ref class|CMediusers');
$function_id  = CView::post('function_id', 'ref class|CFunctions');
$group_id     = CView::post('group_id', 'ref class|CGroups');
$file         = CValue::files('formfile');

CView::checkin();

$object = CGroups::get();
if ($chir_id) {
  $object = CMediusers::get($chir_id);
}
elseif ($function_id) {
  $object = CFunctions::loadFromGuid("CFunctions-$function_id");
}
elseif ($group_id) {
  $object = CGroups::get($group_id);
}

$file = new CCSVFile($file['tmp_name'][0]);

$result = CTarif::importTarifsFor($object, $file);

if ($result['errors']) {
  CAppUI::stepAjax('CTarif-error-import', UI_MSG_ERROR, $result['errors']);
}
if ($result['success']) {
  CAppUI::stepAjax('CTarif-msg-import', UI_MSG_OK, $result['success']);
}
if ($result['founds']) {
  CAppUI::stepAjax('CTarif-msg-import-found', UI_MSG_OK, $result['founds']);
}