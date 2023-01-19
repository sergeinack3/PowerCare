<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkEdit();

$operation_id = CValue::get("operation_id");

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$praticien = $operation->loadRefChir();

$email = $praticien->_user_email;

if (!$email) {
  CAppUI::js("alert('" . addslashes(CAppUI::tr("alert-praticien_email")) . "')");
  CApp::rip();
}

$operation->loadRefPlageOp();

/** @var CSourceSMTP $exchange_source */
$exchange_source = CExchangeSource::get("mediuser-" . CAppUI::$user->_id, CSourceSMTP::TYPE);

$exchange_source->init();

try {
  $exchange_source->setRecipient($email);

  // Création du token
  $token               = new CViewAccessToken();
  $token->datetime_end = CMbDT::dateTime("+24 HOURS");
  $token->user_id      = $praticien->_id;
  $token->params       = "m=planningOp\na=vw_edit_urgence\noperation_id=$operation_id";

  if ($msg = $token->store()) {
    CAppUI::displayAjaxMsg($msg, UI_MSG_ERROR);
  }

  $url = $token->getUrl();

  // Lien vers la DHE
  $subject = CAppUI::conf("reservation subject_mail");
  $content = CAppUI::conf("reservation text_mail");

  $from = array(
    "[URL]",
    "[PRATICIEN - NOM]",
    "[PRATICIEN - PRENOM]",
    "[DATE INTERVENTION]",
    "[HEURE INTERVENTION]");

  $to = array(
    $url,
    $praticien->_user_last_name,
    $praticien->_user_first_name,
    CMbDT::dateToLocale(CMbDT::date($operation->_datetime_best)),
    CMbDT::transform($operation->_datetime_best, null, CAppUI::conf("time"))
  );

  $subject = str_replace($from, $to, $subject);
  $exchange_source->setSubject($subject);

  $content = str_replace($from, $to, $content);
  $content = nl2br(utf8_encode($content));
  $exchange_source->setBody($content);

  $exchange_source->send();
  $operation->envoi_mail = CMbDT::dateTime();

  if ($msg = $operation->store()) {
    CAppUI::displayAjaxMsg($msg, UI_MSG_ERROR);
  }

  CAppUI::displayAjaxMsg("Message envoyé");
}
catch (phpmailerException $e) {
  CAppUI::displayAjaxMsg($e->errorMessage(), UI_MSG_WARNING);
}
catch (CMbException $e) {
  $e->stepAjax();
}
