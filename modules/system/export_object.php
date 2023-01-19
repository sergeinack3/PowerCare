<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\CView;

CCanDo::check(); // Permission check is done in CMbObjectExport
$object_guid  = CView::get("object_guid", "guid class|CMbObject");
$remove_empty_values = CView::get("remove_empty_values", "bool");
CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);
$object->needsRead();

if (!$object || !$object->_id) {
  CApp::rip();
}

try {
  $export = new CMbObjectExport($object);
}
catch (CMbException $e) {
  $e->stepAjax(UI_MSG_ERROR);
}

$export->empty_values = !$remove_empty_values;
$export->streamXML();
