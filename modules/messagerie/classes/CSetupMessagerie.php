<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\CMbSecurity;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;

/**
 * @codeCoverageIgnore
 */
class CSetupMessagerie extends CSetup {
  /**
   * Update user messages destinataires
   *
   * @return bool
   */
  protected function updateMessages() {
    $ds = CSQLDataSource::get("std");
    if (!$ds) {
      return false;
    }
    $messages = $ds->loadList("SELECT * FROM usermessage");
    if (count($messages)) {
      $query = "INSERT INTO `usermessage_dest` (`user_message_id`, `from_user_id`, `to_user_id`, `datetime_read`, `datetime_sent`, `archived`, `starred`)
         VALUES";
      $values = array();
      foreach ($messages as $_message) {
        $umid   = $_message['usermessage_id'];
        $from   = $_message['from'];
        $to     = $_message['to'];
        $dtr    = $_message['date_read'] ? '\''.$_message['date_read'].'\'' : 'NULL';
        $dts    = $_message['date_sent'] ? '\''.$_message['date_sent'].'\'' : 'NULL';
        $arc    = $_message['archived'];
        $star   = $_message['starred'];
        $values[] = " ('$umid', '$from', '$to', $dtr, $dts, '$arc', '$star')";
      }
      $ds->query($query.implode(',', $values));
      if ($msg = $ds->error()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
        return false;
      }
    }
    return true;
  }

  /**
   * Set the hash for the user mails
   *
   * @return bool
   */
  protected function setUserMailHash() {
    $ds = CSQLDataSource::get("std");

    $mails = $ds->loadList("SELECT m.user_mail_id, m.account_class, m.account_id, m.from, m.to, m.subject, c.content FROM user_mail as m, content_html as c WHERE m.account_class IS NOT NULL AND m.account_id IS NOT NULL AND m.text_html_id = c.content_id ORDER BY m.user_mail_id DESC;");
    if (count($mails)) {
      $values = array();

      foreach ($mails as $_mail) {
        $data = "==FROM==\n" . $_mail['from'] .
          "\n==TO==\n" . $_mail['to'] .
          "\n==SUBJECT==\n" . $_mail['subject'] .
          "\n==CONTENT==\n" . $_mail['content'];

        $hash     = CMbSecurity::hash(CMbSecurity::SHA256, $data);
        $values[] = '(' . $_mail['user_mail_id'] . ', ' . $_mail['account_id'] . ', \'' . $_mail['account_class'] . "', '$hash')";
      }

      $mails = $ds->loadList("SELECT m.user_mail_id, m.account_class, m.account_id, m.from, m.to, m.subject, c.content FROM user_mail AS m, content_any AS c WHERE m.account_class IS NOT NULL AND m.account_id IS NOT NULL AND m.text_html_id IS NULL AND m.text_plain_id = c.content_id ORDER BY m.user_mail_id DESC;");

      foreach ($mails as $_mail) {
        $data = "==FROM==\n" . $_mail['from'] .
          "\n==TO==\n" . $_mail['to'] .
          "\n==SUBJECT==\n" . $_mail['subject'] .
          "\n==CONTENT==\n" . $_mail['content'];

        $hash     = CMbSecurity::hash(CMbSecurity::SHA256, $data);
        $values[] = '(' . $_mail['user_mail_id'] . ', ' . $_mail['account_id'] . ', \'' . $_mail['account_class'] . "', '$hash')";
      }

      $query = "INSERT INTO `user_mail` (`user_mail_id`, `account_id`, `account_class`, `hash`) VALUES " .
        implode(', ', $values) . " ON DUPLICATE KEY UPDATE `hash` = VALUES(`hash`);";

      $ds->query($query);
      if ($msg = $ds->error()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);

        return false;
      }
    }

    return true;
  }

  /**
   * @return bool
   * @throws \Exception
   */
  protected function upgradePartsLinks() {
    $ds = CSQLDataSource::get("std");

    $query = "SELECT a.* FROM `user_mail_attachment` AS a 
      LEFT JOIN `files_mediboard` AS f ON a.`file_id` = f.`file_id`
      WHERE a.`file_id` IS NOT NULL AND f.`object_class` != 'CUserMailAttachments';";
    $attachments = $ds->loadList($query);

    $query = "SELECT m.* FROM `user_mail` AS m 
      LEFT JOIN `files_mediboard` AS f ON m.`text_file_id` = f.`file_id`
      WHERE m.`text_file_id` IS NOT NULL AND f.`object_class` != 'CUserMail';";
    $mails = $ds->loadList($query);

    $i = 1;
    $inserts = '';
    foreach ($attachments as $_attachment) {
      $inserts .= '(' . $_attachment['user_mail_attachment_id'] . ", 'CMailAttachments', " . $_attachment['file_id'] . ')';

      if (count($mails) || ($i != count($attachments) && !count($mails))) {
        $inserts .= ", \n";
      }
      $i++;
    }

    $i = 1;
    foreach ($mails as $_mail) {
      $inserts .= '(' . $_mail['user_mail_id'] . ", 'CUserMail', " . $_mail['text_file_id'] . ')';

      if ($i != count($mails)) {
        $inserts .= ", \n";
      }
      $i++;
    }

    $query = "INSERT INTO `mail_part_to_file` (`part_id`, `part_class`, `file_id`) VALUES \n$inserts;";

    if (strlen($inserts) != 0) {
      $ds->query($query);
      if ($msg = $ds->error()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);

        return false;
      }
    }

    return true;
  }

  function __construct() {
    parent::__construct();

    $this->mod_name = "messagerie";
    $this->makeRevision("0.0");

    $query = "CREATE TABLE `usermessage` (
              `mbmail_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `from` INT (11) UNSIGNED NOT NULL,
              `to` INT (11) UNSIGNED NOT NULL,
              `subject` VARCHAR (255) NOT NULL,
              `source` MEDIUMTEXT,
              `date_sent` DATETIME,
              `date_read` DATETIME,
              `archived` ENUM ('0','1') NOT NULL DEFAULT '0',
              `starred` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `usermessage`
              ADD INDEX (`from`),
              ADD INDEX (`to`),
              ADD INDEX (`date_sent`),
              ADD INDEX (`date_read`);";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `usermessage` CHANGE `mbmail_id` `usermessage_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;";
    $this->addQuery($query);
    $this->addPrefQuery("ViewMailAsHtml", 1);

    $this->makeRevision("0.13");
    $this->addDependency("dPfiles", "0.1");
    $query = "CREATE TABLE `user_mail` (
              `user_mail_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `user_id` INT (11) UNSIGNED NOT NULL,
              `subject` VARCHAR (255),
              `from` VARCHAR (255),
              `to` VARCHAR (255),
              `date_inbox` DATETIME,
              `date_read` DATETIME,
              `uid` INT (11),
              `answered` ENUM ('0','1') DEFAULT '0',
              `in_reply_to_id` INT (11) UNSIGNED,
              `text_plain_id` INT (11) UNSIGNED,
              `text_html_id` INT (11) UNSIGNED
) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `user_mail` 
              ADD INDEX (`date_inbox`);";
    $this->addQuery($query);


    $this->makeRevision("0.14");
    $query = "CREATE TABLE `user_mail_attachment` (
              `user_mail_attachment_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `mail_id` INT (11) UNSIGNED NOT NULL,
              `type` INT (11) NOT NULL,
              `encoding` INT (11),
              `subtype` VARCHAR (255),
              `id` VARCHAR (255),
              `bytes` INT (11),
              `disposition` VARCHAR (255),
              `part` VARCHAR (255) NOT NULL,
              `name` VARCHAR (255) NOT NULL,
              `extension` VARCHAR (255) NOT NULL
) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `user_mail_attachment`
              ADD INDEX (`mail_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $this->addPrefQuery("getAttachmentOnUpdate", 0);

    $this->makeRevision("0.16");
    $query = "ALTER TABLE `user_mail_attachment`
              ADD `linked` VARCHAR (255) AFTER `part`;";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $this->addPrefQuery("LinkAttachment", 1);

    $this->makeRevision("0.18");
    $this->addPrefQuery("showImgInMail", 1);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `user_mail`
              ADD `account_id` INT (11) NOT NULL AFTER `user_mail_id`,
              DROP `user_id`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `user_mail` ADD INDEX (`account_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `user_mail_attachment` CHANGE `linked` `file_id` INT (11) UNSIGNED ;";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $this->addPrefQuery("nbMailList", 20);

    $this->makeRevision("0.22");
    $query = "ALTER TABLE `user_mail`
    ADD `text_file_id` INT (11) UNSIGNED AFTER `in_reply_to_id`;";
    $this->addQuery($query);

    $this->makeRevision("0.23");
    $query = "ALTER TABLE `user_mail`
              ADD `favorite` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `user_mail`
              ADD `archived` ENUM ('0','1') DEFAULT '0' AFTER `favorite`;";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $this->addPrefQuery("markMailOnServerAsRead", 1);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `user_mail`
                CHANGE `account_id` `account_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                ADD `sent` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $this->addPrefQuery("mailReadOnServerGoToArchived", 1);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `usermessage`
                ADD `grouped` INT (11);";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $query = "ALTER TABLE `usermessage`
                ADD `in_reply_to` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `usermessage`
                ADD INDEX (`in_reply_to`);";
    $this->addQuery($query);

    $this->makeRevision("0.30");
    $query = "CREATE TABLE `usermessage_dest` (
                `usermessage_dest_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_message_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `from_user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `to_user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `datetime_read` DATETIME,
                `datetime_sent` DATETIME,
                `archived` ENUM ('0','1') DEFAULT '0',
                `starred` ENUM ('0','1') DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.31");
    $query = "ALTER TABLE `usermessage_dest`
                ADD INDEX (`user_message_id`),
                ADD INDEX (`from_user_id`),
                ADD INDEX (`to_user_id`),
                ADD INDEX (`datetime_read`),
                ADD INDEX (`datetime_sent`);";
    $this->addQuery($query);

    $this->makeRevision("0.32");
    $this->addMethod("updateMessages");

    $this->makeRevision("0.33");
    $query = "ALTER TABLE `usermessage`
      CHANGE `source` `content` MEDIUMTEXT,
      CHANGE `from` `creator_id` INT (11) UNSIGNED NOT NULL,
      DROP `to`,
      DROP `date_read`,
      DROP `date_sent`,
      DROP `archived`,
      DROP `starred`,
      DROP `grouped`;";
    $this->addQuery($query);

    $this->makeRevision('0.34');

    $query = "ALTER TABLE `user_mail`
                ADD `account_class` VARCHAR (50) NOT NULL AFTER `account_id`";
    $this->addQuery($query);

    $query = "UPDATE `user_mail` SET `account_class` = 'CSourcePOP';";
    $this->addQuery($query);

    $this->makeRevision('0.35');

    $query = "ALTER TABLE `usermessage_dest`
                ADD `deleted` ENUM('0', '1') DEFAULT '0';";
    $this->addQuery($query);

    $this->addPrefQuery('inputMode', 'html');

    $this->makeRevision('0.36');

    $query = "ALTER TABLE `user_mail`
                ADD `cc` VARCHAR(255) AFTER `to`,
                ADD `bcc` VARCHAR(255) AFTER `cc`,
                ADD `draft` ENUM('0', '1') DEFAULT '0',
                ADD `hash` CHAR(64);";
    $this->addQuery($query);

    $query = "ALTER TABLE `user_mail` ADD INDEX (`hash`);";
    $this->addQuery($query);

    $this->addMethod('setUserMailHash');

    $this->makeRevision('0.37');
    $query = "ALTER TABLE `user_mail`
      ADD `destinataire_item_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision('0.38');
    $query = "ALTER TABLE `user_mail`
      DROP `destinataire_item_id`";
    $this->addQuery($query);

    $this->makeRevision('0.39');

    $query = "ALTER TABLE `user_mail`
      ADD `to_send` ENUM('0', '1') DEFAULT '0',
      ADD `is_apicrypt` ENUM('0', '1') DEFAULT '0',
      ADD `retry_count` INT(2) DEFAULT 0;";
    $this->addQuery($query);

    $this->makeRevision('0.40');

    $query = "ALTER TABLE `user_mail`
      ADD `linked_patient_id` INT(11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision('0.41');

    $query = "CREATE TABLE `s_mime_key` (
        `s_mime_key_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `source_id` INT(11) UNSIGNED NOT NULL,
        `cert_path` VARCHAR(255) NOT NULL,
        `passphrase` VARCHAR(255),
        `iv` VARCHAR(255)
      )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `user_mail`
      ADD `is_hprimnet` ENUM('0', '1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision('0.42');

    $query = "CREATE TABLE `mail_part_to_file` (
        `mail_part_to_file_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `part_id` INT(11) UNSIGNED NOT NULL,
        `part_class` ENUM('CUserMail', 'CMailAttachments') NOT NULL,
        `file_id` INT(11) UNSIGNED NOT NULL
      )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->addDependency('dPfiles', '0.17');
    $this->addMethod('upgradePartsLinks');

    $this->makeRevision('0.43');

    $query = "CREATE TABLE `user_mail_folders` (
        `user_mail_folder_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `account_id` INT(11) UNSIGNED NOT NULL,
        `name` VARCHAR (50) NOT NULL,
        `description` VARCHAR (255),
        `parent_id` INT(11) UNSIGNED,
        `type` ENUM ('inbox', 'archived', 'favorites', 'sentbox', 'drafts') NOT NULL
      )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE user_mail ADD folder_id INT(11) UNSIGNED AFTER `account_class`;";
    $this->addQuery($query);

    $this->makeRevision('0.44');

    $query = "CREATE TABLE `electronic_deliveries` (
        `electronic_delivery_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `message_class` ENUM('CUserMail', 'CMSSanteMail') NOT NULL,
        `message_id` INT(11) UNSIGNED NOT NULL,
        `document_class` ENUM('CFile', 'CCompteRendu') NOT NULL,
        `document_id` INT(11) UNSIGNED NOT NULL
      )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision('0.45');

    $this->addPrefQuery("chooseEmailAccount", "mssante");

    $this->makeRevision('0.46');

    $query = "CREATE TABLE `user_message_attachments` (
        `user_message_attachment_id` INT(11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `user_message_id` INT(11) UNSIGNED NOT NULL,
        `file_id` INT(11) UNSIGNED
      )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.47");

    $this->addDefaultConfig("messagerie messagerie_interne resctriction_level_messages", "messagerie resctriction_level_messages");
    $this->addDefaultConfig("messagerie messagerie_externe limit_external_mail", "messagerie limit_external_mail");
    $this->addDefaultConfig("messagerie messagerie_externe retry_number", "messagerie retry_number");

    $this->makeRevision("0.48");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->makeRevision('0.49');

    $query = 'CREATE TABLE `user_message_dest_groups` (
        `user_message_dest_group_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `name` VARCHAR (255) NOT NULL,
        `color` VARCHAR (6),
        `group_id` INT (11) UNSIGNED NOT NULL,
        INDEX (`group_id`)
      )/*! ENGINE=MyISAM */;';
    $this->addQuery($query);

    $query = "CREATE TABLE `user_message_dest_group_users` (
        `user_message_dest_group_user_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
        `group_id` INT (11) UNSIGNED NOT NULL,
        `user_id` INT (11) UNSIGNED NOT NULL,
        INDEX (`group_id`),
        INDEX (`user_id`)
      )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `usermessage` ADD `hidden_recipients` ENUM('0', '1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision('0.50');
    $query = "ALTER TABLE electronic_deliveries ADD INDEX (document_id, document_class),
                ADD INDEX (message_id, message_class),
                ADD INDEX (message_class);";
    $this->addQuery($query);

    $this->makeRevision("0.51");
    $this->addPrefQuery("cciReceivers", 0);

    $this->makeRevision("0.52");
    $this->addPrefQuery("oneMailPerRecipient", 0);

    $this->makeRevision('0.53');
    $this->addQuery("ALTER TABLE `user_mail` 
                ADD `send_fail` ENUM ('0','1') DEFAULT '0';");

    $this->mod_version = '0.54';
  }
}
