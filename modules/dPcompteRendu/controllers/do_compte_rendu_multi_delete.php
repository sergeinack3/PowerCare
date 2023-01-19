<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbObject;
use Ox\Core\CView;

/**
 * Multiple docs delete aed
 */
$object_guid = CView::post("object_guid", "str");
$object = CMbObject::loadFromGuid($object_guid);

// Chargement de la ligne à rendre active
foreach ($object->loadBackRefs("documents") as $_doc) {
  $_POST["compte_rendu_id"] = $_doc->_id;
  $_POST["del"] = "1";
  $do = new CDoObjectAddEdit("CCompteRendu");
  $do->redirect = $do->redirectDelete = null;
  $do->doIt();
}

echo CAppUI::getMsg();
CApp::rip();