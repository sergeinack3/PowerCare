<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

/**
 * Description
 */
class CSyslogExchange extends CEchangeXML {
  /** @var integer Primary key */
  public $syslog_exchange_id;

  static $messages = array(
    "iti"    => "CSyslogITI",
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "syslog_exchange";
    $spec->key   = "syslog_exchange_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props["group_id"]     .= " back|echanges_syslog";
    $props["object_class"]  = "str class show|0";
    $props["receiver_id"]   = "ref class|CSyslogReceiver autocomplete|nom back|echanges";
    $props["initiateur_id"] = "ref class|CSyslogExchange back|initiator";
    $props["sender_class"]  = "str show|0";
    $props["sender_id"]    .= " back|echanges_syslog";
    $props["message_content_id"]  .= " back|messages_syslog";
    $props["object_id"]    .= " back|exchanges_syslog";

    $props["acquittement_content_id"] = "ref class|CContentAny show|0 cascade back|acquittements_syslog";
    $props["_acquittement"]           = "text";

    return $props;
  }

  /**
   * @see parent::getFamily()
   */
  function getFamily() {
    return self::$messages;
  }
}
