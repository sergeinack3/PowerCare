<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Webservices\CSourceSOAP;

/**
 * Execute method
 */
CCanDo::checkAdmin();

$method               = CValue::post("func");
$exchange_source_guid = CValue::post("exchange_source_guid");
$parameters           = CValue::post("parameters");

/** @var $exchange_source CSourceSOAP */
$exchange_source = CMbObject::loadFromGuid($exchange_source_guid);
$exchange_source->setData($parameters);
$exchange_source->getClient()->send($method);

echo $exchange_source->getACQ();

CApp::rip();
