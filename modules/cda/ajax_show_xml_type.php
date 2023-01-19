<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Interop\Cda\CCdaTools;

$name = CValue::get("name");

echo CMbString::purifyHTML("<h1>$name</h1>");

echo CMbString::highlightCode("xml", CCdaTools::showNodeXSD($name, "modules/cda/resources/datatypes-base_original.xsd"));