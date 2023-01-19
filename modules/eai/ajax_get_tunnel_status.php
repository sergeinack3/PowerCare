<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Interop\Eai\CHTTPTunnelObject;

CCanDo::checkRead();

// Déverrouiller la session pour rendre possible les requêtes concurrentes.
CSessionHandler::writeClose();

$source_guid = CValue::get("source_guid");

$status = null;

/** @var CHTTPTunnelObject $tunnel */
$tunnel = CMbObject::loadFromGuid($source_guid);

$reachable = $tunnel->checkStatus();

$status = array(
  "reachable" => $reachable,
  "message"   => $tunnel->_message_status
);

echo json_encode($status);