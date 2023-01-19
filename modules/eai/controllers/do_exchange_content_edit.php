<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CExchangeDataFormat;

/**
 * Message supported
 */
CCanDo::checkAdmin();

$exchange_guid      = CValue::post("exchange_guid");
$_message           = CValue::post("_message");
$segment_terminator = CValue::post("segment_terminator");

$_message = str_replace("\\\\", "\\", $_message);
$_message = str_replace('\"', '"', $_message);

$map = array(
  "CR"   => "\r",
  "LF"   => "\n",
  "CRLF" => "\r\n",
);
$segment_terminator = CValue::read($map, $segment_terminator);

if ($segment_terminator) {
  $lines = preg_split("/(\r\n|\r|\n)/", $_message);
  $_message = implode($segment_terminator, $lines);
}

/**
 * @var CExchangeDataFormat
 */
$exchange = CMbObject::loadFromGuid($exchange_guid);

if ($exchange->_id) {
  $exchange->_message = $_message;
  if ($msg = $exchange->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
  else {
    CAppUI::setMsg("$exchange->_class-msg-modify");
  }
}

echo CAppUI::getMsg();
CApp::rip();