<?php
/**
 * @package Mediboard\Eai
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

$config_guid = CValue::get("config_guid");

/**
 * @var CExchangeDataFormatConfig
 */
$config = CMbObject::loadFromGuid($config_guid);

$name = $config->loadRefSender()->_view;

ob_clean();

header("Content-Type: text/xml");
header("Content-Disposition: attachment; filename=\"config-$name.xml\"");
echo $config->exportXML()->saveXML();

CApp::rip();