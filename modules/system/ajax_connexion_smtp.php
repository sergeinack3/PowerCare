<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\Module\CModule;
use Ox\Core\CView;
use Ox\Mediboard\Apicrypt\CApicrypt;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

$exchange_source_name = CView::get("exchange_source_name", "str");
$type_action          = CView::get("type_action", "str");
CView::checkin();

// Check params
if (null == $exchange_source_name) {
  CAppUI::stepAjax("CExchangeSource-error-noSourceName", UI_MSG_ERROR);
}
if (null == $type_action) {
  CAppUI::stepAjax("CExchangeSource-error-noTestDefined", UI_MSG_ERROR);
}

/** @var CSourceSMTP $exchange_source */
$exchange_source = CExchangeSource::get($exchange_source_name, CSourceSMTP::TYPE, true, null, false);

if (!$exchange_source->_id) {
  CAppUI::stepAjax("CExchangeSource-error-unsavedParameters", UI_MSG_ERROR);
}
$exchange_source->init();

if ($type_action == "connexion") {
  try {
    $result = $exchange_source->_mail->SmtpConnect();
    if ($result) {
      CAppUI::stepAjax("CSourceSMTP-info-connection-established", UI_MSG_OK, $exchange_source->host, $exchange_source->port);
    }
    else {
      CAppUI::stepAjax('CSourceSMTP-msg-connection_failed', UI_MSG_ERROR, $exchange_source->host, $exchange_source->port);
    }
  } catch (phpmailerException $e) {
    CAppUI::stepAjax($e->errorMessage(), UI_MSG_ERROR);
  } catch (CMbException $e) {
    $e->stepAjax(UI_MSG_ERROR);
  }
  catch (Exception $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
  }
}
elseif ($type_action == "envoi") {
  try {
    $exchange_source->setRecipient($exchange_source->email, $exchange_source->email);
    $exchange_source->setSubject("Test d'envoi de mail par Mediboard");
    $body = "<h2>Mail de test</h2>
      <p>Ceci est un mail de test envoyé par Mediboard afin de vérifier le fonctionnement de votre serveur SMTP</p>";

    /* In the case of Apicrypt source, we encrypt the message */
    if (CModule::getActive('apicrypt') && strpos($exchange_source->name, 'apicrypt') !== false
        && strpos($exchange_source->name, 'mediuser') !== false
    ) {
      $user_id = explode('-', $exchange_source->name)[1];
      $receiver = explode('@', $exchange_source->email);
      $body = CApicrypt::encryptBody($user_id, $receiver[0], $body);
    }
    else {
      $exchange_source->addAttachment("./images/pictures/logo.png");
    }

    $exchange_source->setBody($body);
    $exchange_source->send();
    CAppUI::stepAjax("CSourceSMTP-info-message-sent", UI_MSG_OK, $exchange_source->host, $exchange_source->port);
  } catch(CMbException $e) {
    $e->stepAjax();
  }
}
else {
  CAppUI::stepAjax("CExchange-unknown-test", UI_MSG_ERROR, $type_action);
}
