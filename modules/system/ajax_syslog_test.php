<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Check params
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSyslogSource;

if (null == $exchange_source_name = CValue::get("exchange_source_name")) {
  CAppUI::stepAjax("CExchangeSource-error-noSourceName", UI_MSG_ERROR);
}
if (null == $type_action = CValue::get("type_action")) {
  CAppUI::stepAjax("CExchangeSource-error-noTestDefined", UI_MSG_ERROR);
}

/** @var CSyslogSource $exchange_source */
$exchange_source = CExchangeSource::get($exchange_source_name, "syslog", true, null, false);

if (!$exchange_source->_id) {
  CAppUI::stepAjax("CExchangeSource-error-unsavedParameters", UI_MSG_ERROR);
}

switch ($type_action) {
  case 'connection':
    if ($exchange_source->protocol == 'UDP') {
      try {
        $exchange_source->testUDPConnection();
        CAppUI::setMsg("common-msg-Successful connection", UI_MSG_OK);
      }
      catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
      }
    }
    else {
      try {
        $exchange_source->connect();
        CAppUI::setMsg("common-msg-Successful connection", UI_MSG_OK);
      }
      catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
      }
    }

    break;

  case 'send':
    if ($exchange_source->protocol == 'UDP') {
      try {
        $exchange_source->testUDPConnection();
        CAppUI::setMsg("common-msg-Successful connection", UI_MSG_OK);
      }
      catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
      }
    }
    else {
      try {
        $exchange_source->sendTestMessage();
        CAppUI::setMsg("common-msg-Message sent", UI_MSG_OK);
      }
      catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
      }
    }
    break;

  default:
    CAppUI::setMsg("CExchange-unknown-test", UI_MSG_ERROR, $type_action);
}

echo CAppUI::getMsg();