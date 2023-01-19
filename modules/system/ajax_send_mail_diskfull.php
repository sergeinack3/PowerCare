<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

/**
 * Send a mail concerning the diskfull problem while backuping
 */
/** @var CSourceSMTP $source */
$source = CExchangeSource::get("system-message", CSourceSMTP::TYPE, true, null, true);

try {
  // Source init
  $source->init();
  $source->addTo(CAppUI::conf("system CMessage default_email_to"));
  $source->addBcc(CAppUI::conf("system CMessage default_email_from"));
  $source->addRe(CAppUI::conf("system CMessage default_email_from"));
  
  // Email subject
  $page_title = CAppUI::conf("page_title");
  $message_subject = CAppUI::tr("system-msg-backup-diskfull");
  $source->setSubject("$page_title - $message_subject");
      
  // Email body
  $message_body = CAppUI::tr("system-msg-backup-diskfull-desc");
  $body = "<strong>$page_title</strong>";
  $body.= "<p>$message_body</p>";
  $source->setBody($body);
      
  // Do send
  $source->send();
}
catch (CMbException $e) {
  $e->stepAjax();
}

CAppUI::stepAjax("system-msg-email_sent");
