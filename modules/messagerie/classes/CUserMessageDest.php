<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CUserAuthentication;

/**
 * Description
 */
class CUserMessageDest extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $usermessage_dest_id;

  public $user_message_id;
  public $to_user_id;            //destinataire
  public $from_user_id;

  public $datetime_read;
  public $datetime_sent;     // if !sent => draft
  public $archived;
  public $starred;
  public $deleted;

  public $_ref_message;
  public $_ref_user_to;
  public $_ref_user_from;

  public $_datetime_sent;
  public $_datetime_read;
  public $_is_received;
  public $_is_sent;
  public $_is_draft;


  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "usermessage_dest";
    $spec->key   = "usermessage_dest_id";

    return $spec;
  }


  /**
   * Load unread messages
   *
   * @param null $user_id user to load, null = current
   *
   * @return CUserMessageDest[]
   */
  static function loadNewMessages($user_id = null) {
    $dests = array();

    if (!CModule::getActive('messagerie')) {
      return $dests;
    }

    $user_id = ($user_id) ?: CMediusers::get()->_id;

    $dest = new static();
    $ds   = $dest->getDS();

    if ($dest->_ref_module->mod_version < 0.30) {
      CAppUI::stepAjax('CModule%s-msg-pls_update_module', UI_MSG_WARNING, $dest->_ref_module->mod_name);

      return $dests;
    }

    $where = array(
      'to_user_id'    => $ds->prepare('= ?', $user_id),
      'datetime_sent' => 'IS NOT NULL',
      'datetime_read' => 'IS NULL',
      'deleted'       => "= '0'",
    );

    /** @var CUserMessageDest[] $dests */
    $dests = $dest->loadList($where);

    static::massLoadFwdRef($dests, 'user_message_id');
    $from_users = static::massLoadFwdRef($dests, 'from_user_id');
    $to_users   = static::massLoadFwdRef($dests, 'to_user_id');

    CStoredObject::massLoadFwdRef($from_users, 'function_id');
    CStoredObject::massLoadFwdRef($to_users, 'function_id');

    foreach ($dests as $_dest) {
      $_dest->loadRefFwd();
    }

    return $dests;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["user_message_id"] = "ref class|CUserMessage notNull cascade back|usermessage_destinataires";
    $props["to_user_id"]      = "ref class|CMediusers notNull back|usermessage_dest_to";
    $props["from_user_id"]    = "ref class|CMediusers notNull back|usermessage_dest_from";
    $props["datetime_read"]   = "dateTime";
    $props["datetime_sent"]   = "dateTime";
    $props["archived"]        = "bool default|0";
    $props["starred"]         = "bool default|0";
    $props['deleted']         = 'bool default|0';

    return $props;
  }

  /** @see parent::updateFormFields() */
  function updateFormFields() {
    parent::updateFormFields();
    if ($this->_ref_message) {
      $this->_view = $this->_ref_message->subject;
    }

    if ($this->datetime_sent) {
      $this->_datetime_sent = CMbDT::date(null, $this->datetime_sent);
      if ($this->_datetime_sent == CMbDT::date()) {
        $this->_datetime_sent = CMbDT::format($this->datetime_sent, '%H:%M');
      }
      else {
        if (CMbDT::format($this->datetime_sent, '%Y') == CMbDT::format(CMbDT::date(), '%Y')) {
          $this->_datetime_sent = CMbDT::format($this->datetime_sent, '%d %B');
        }
      }
    }

    if ($this->datetime_read) {
      $this->_datetime_read = CMbDT::date(null, $this->datetime_read);
      if ($this->_datetime_read == CMbDT::date()) {
        $this->_datetime_read = CMbDT::format($this->datetime_read, '%H:%M');
      }
      else {
        if (CMbDT::format($this->datetime_read, '%Y') == CMbDT::format(CMbDT::date(), '%Y')) {
          $this->_datetime_read = CMbDT::format($this->datetime_read, '%d %B');
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    if (!$this->_id || $this->fieldModified('datetime_read')) {
      self::getUnreadMessages(true);
    }

    return null;
  }

  /**
   * @param int $user_id
   */
  function loadStatusFor($user_id = null) {
    $user = CMediusers::get($user_id);

    if ($this->to_user_id == $user->_id) {
      $this->_is_received = true;
    }

    if ($this->from_user_id == $user->_id) {
      $this->_is_sent = true;
    }

    if (!$this->datetime_sent) {
      $this->_is_draft = true;
    }
  }

  /**
   * load the message
   *
   * @param bool $cache use cache
   *
   * @return CUserMessage
   */
  function loadRefMessage($cache = true) {
    return $this->_ref_message = $this->loadFwdRef("user_message_id", $cache);
  }

  /**
   * load the user TO
   *
   * @return CMediusers|null
   */
  function loadRefTo() {
    return $this->_ref_user_to = $this->loadFwdRef("to_user_id", true);
  }

  /**
   * load the user FROM
   *
   * @return CMediusers|null
   */
  function loadRefFrom() {
    return $this->_ref_user_from = $this->loadFwdRef("from_user_id", true);
  }


  /**
   * load the main refs
   *
   * @return null
   */
  function loadRefFwd() {
    $this->loadRefMessage();
    $this->loadRefFrom()->loadRefFunction();
    $this->loadRefTo()->loadRefFunction();
  }

  /**
   * Count the messages sent by the given user
   *
   * @param CMediusers $user The user
   *
   * @return int The number of sent messages
   */
  public static function countSentFor($user) {
    $query = "SELECT COUNT(DISTINCT `user_message_id`) FROM `usermessage_dest`
            WHERE `from_user_id` = '$user->_id' AND `datetime_sent` IS NOT NULL;";

    return CSQLDataSource::get('std')->loadResult($query);
  }

  /**
   * Count the messages unread by the given user
   *
   * @param CMediusers $user The user
   *
   * @return int The number of unread messages
   */
  public static function countUnreadFor($user) {
    $user_message = new self;
    $where        = array(
      'to_user_id'    => "= '$user->_id'",
      'datetime_sent' => "IS NOT NULL",
      'archived'      => "!= '1'",
      'datetime_read' => "IS NULL",
      'deleted'       => "= '0'",
    );

    return $user_message->countList($where);
  }

  /**
   * Count the messages unread by the given user
   *
   * @param CMediusers $user The user
   *
   * @return int The number of unread messages
   */
  public static function countInboxFor($user) {
    $user_message = new self;
    $where        = array(
      'to_user_id'    => "= '$user->_id'",
      'datetime_sent' => "IS NOT NULL",
      'archived'      => "!= '1'",
      'deleted'       => "= '0'",
    );

    return $user_message->countList($where);
  }

  /**
   * Count the messages archived by the given user
   *
   * @param CMediusers $user The user
   *
   * @return int The number of archived messages
   */
  public static function countArchivedFor($user) {
    $user_message = new self;
    $where        = array(
      'to_user_id'    => "= '$user->_id'",
      'datetime_sent' => "IS NOT NULL",
      'archived'      => "= '1'",
      'deleted'       => "= '0'",
    );

    return $user_message->countList($where);
  }

  /**
   * Count the messages drafted by the given user
   *
   * @param CMediusers $user The user
   *
   * @return int The number of drafted messages
   */
  public static function countDraftedFor($user) {
    $usermessage = new CUserMessage();
    $where       = array('creator_id' => "= '$user->_id'");
    /** @var CUserMessage[] */
    $listDrafted = $usermessage->loadList($where);
    foreach ($listDrafted as $key => $_mail) {
      $dests = $_mail->loadRefDests();
      foreach ($dests as $_dest) {
        if ($_dest->datetime_sent) {
          unset($listDrafted[$key]);
          continue 2;
        }
      }
    }

    return count($listDrafted);
  }

  /**
   * Return the TTL duration for the unread messages and connected users cache
   *
   * @return int
   */
  public static function getCacheLifetime() {
    $refresh_frequency = CAppUI::gconf('messagerie access internal_mail_refresh_frequency');

    return ($refresh_frequency > 0) ? intval($refresh_frequency * 1.5) : 30;
  }

  /**
   * Get the number of unread messages for the connected user from cache
   *
   * @param bool $reset If true, remove the cache entry
   *
   * @return int
   */
  public static function getUnreadMessages($reset = false) {
    $user = CMediusers::get();
    if (!$user->_id) {
      return 0;
    }

    $cache_unread_mails = new Cache('CUserMessageDest.getUnreadMessages', "messagerie-unread-{$user->_id}", Cache::OUTER, static::getCacheLifetime());

    if ($reset) {
      $cache_unread_mails->rem();
    }

    if ($cache_unread_mails->exists()) {
      $unread_mails = $cache_unread_mails->get();
    }
    else {
      $unread_mails = static::countUnreadFor($user);
      $cache_unread_mails->put($unread_mails);
    }

    return $unread_mails;
  }

  /**
   * Return the list of connected users from the cache
   *
   * @return array
   */
  public static function getConnectedUsers() {
    $group = CGroups::get();
    $user  = CMediusers::get();

    // Do not use CAppUI::isCabinet() function, but Messagerie configuration below to determine caching strategy
    $is_cabinet   = (CAppUI::gconf('messagerie messagerie_interne resctriction_level_messages') === 'function');
    $context_guid = ($is_cabinet) ? "CFunctions-{$user->function_id}" : $group->_guid;
    $context_id   = ($is_cabinet) ? $user->function_id : $group->_id;

    $cache_connected_users = new Cache(
      'CUserMessageDest.getConnectedUsers',
      "messagerie-connected-users-{$context_guid}",
      Cache::OUTER, static::getCacheLifetime()
    );

    if ($cache_connected_users->exists()) {
      $connected_users = $cache_connected_users->get();
    }
    else {
      $connected_users = ($is_cabinet) ?
        CUserAuthentication::getConnectedUsersForFunction($context_id) :
        CUserAuthentication::getConnectedUsersForGroup($context_id);

      $cache_connected_users->put($connected_users);
    }

    return $connected_users;
  }
}
