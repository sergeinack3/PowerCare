<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;

CCanDo::checkEdit();

$_GET["object_class"] = "CExConcept";

//$_GET["col"] = array("name");

$object_guid  = CValue::get("object_guid");

CAppUI::requireModuleFile("system", "vw_object_tree_explorer");
