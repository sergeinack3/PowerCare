<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CExchangeDataFormatConfig;

/**
 * Export CExchangeDataFormatConfig
 */
CCanDo::checkRead();

$object_guid = CValue::get("object_guid");

/** @var CExchangeDataFormatConfig $object */
$object = CMbObject::loadFromGuid($object_guid);

$name = $object->loadRefObject()->_view;

ob_clean();

header("Content-Type: text/xml");
header("Content-Disposition: attachment; filename=\"config-$name.xml\"");
echo $object->exportXMLConfigValues()->saveXML();
  
CApp::rip();