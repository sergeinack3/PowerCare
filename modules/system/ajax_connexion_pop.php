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
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;

if (null == $exchange_source_name = CValue::get("exchange_source_name")) {
  CAppUI::stepAjax("CExchangeSource-error-noSourceName", UI_MSG_ERROR);
}
if (null == $type_action = CValue::get("type_action")) {
  CAppUI::stepAjax("CExchangeSource-error-noTestDefined", UI_MSG_ERROR);
}

/** @var CSourcePOP $exchange_source */
$exchange_source = CExchangeSource::get($exchange_source_name, "pop", true, null, false);

if (!$exchange_source->_id) {
  CAppUI::stepAjax("CExchangeSource-error-unsavedParameters", UI_MSG_ERROR);
}
$pop = new CPop($exchange_source);

switch ($type_action) {
  case 'connexion':
    try {
      if ($pop->open()) {
        CAppUI::stepAjax("CSourcePOP-info-connection-established@%s:%s", UI_MSG_OK, $exchange_source->host, $exchange_source->port);
      }
      else {
        CAppUI::stepAjax("CSourcePOP-info-connection-failed@%s:%s", UI_MSG_ERROR, $exchange_source->host, $exchange_source->port);
      }
    } catch (CMbException $e) {
      $e->stepAjax(UI_MSG_ERROR);
    }
    catch (Exception $e) {
      CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
    }
    break;

  case 'listBox':
    try {
      if ($pop->open()) {
        $boxes = imap_list($pop->_mailbox, $pop->_server, "*");
        CAppUI::stepAjax("OK, %d comptes", UI_MSG_OK, count($boxes));
        foreach ($boxes as $_box) {
          echo $_box.'<br/>';
        }
      }
    } catch(CMbException $e) {
      $e->stepAjax(UI_MSG_WARNING);
    }
    break;

  default:
    CAppUI::stepAjax("CExchange-unknown-test", UI_MSG_ERROR, $type_action);
    break;
}
