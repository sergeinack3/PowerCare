<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;

CCanDo::checkEdit();

$_GET["object_class"] = "CExList";

//$_GET["col"] = array("name");

CAppUI::requireModuleFile("system", "vw_object_tree_explorer");
