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
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFileAddEdit;

CCanDo::checkAdmin();

$object_guid = CValue::post("object_guid");
$object = CMbObject::loadFromGuid($object_guid);

// Chargement de la ligne à rendre active
foreach ($object->loadBackRefs("files") as $_file) {
  $_POST["file_id"] = $_file->_id;
  $_POST["del"] = "1";
  $do = new CFileAddEdit();
  $do->redirect = null;
  $do->doIt();
}

echo CAppUI::getMsg();
CApp::rip();

