<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["messagerie"] = array (
  "ViewMailAsHtml",
  "getAttachmentOnUpdate",
  "LinkAttachment",
  "showImgInMail",
  "nbMailList",
  "markMailOnServerAsRead",
  "mailReadOnServerGoToArchived",
  'inputMode',
  'chooseEmailAccount',
  'cciReceivers',
  'oneMailPerRecipient'
);
