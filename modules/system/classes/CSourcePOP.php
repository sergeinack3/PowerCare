<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Messagerie\CUserMessageDest;

/**
 * Class CSourcePOP
 */
class CSourcePOP extends CExchangeSource
{
    // Source type
    public const TYPE = 'pop';

    /* KEY */
    public $source_pop_id;

    // DB Fields
    public $port;
    public $auth_ssl;
    public $timeout; //seconds
    public $type;
    public $is_private;

    public $last_update;
    public $object_class;
    public $object_id;

    public $extension;
    public $cron_update;

    public $_mailbox; //ressource id
    public $_server; //string of server for imap
    public $_mailbox_info;

    /** @var  CMediusers */
    public $_ref_mediuser;
    /** @var  CMbObject */
    public $_ref_metaobject;

    public $_nb_ref_mails;
    public $_unread_messages = 0;

    /**
     * @param CMediusers $user  The user
     * @param bool       $owner If true, only the accounts that belong to the user are returned, if false, only the
     *                          available accounts will be returned
     *
     * @return CSourcePOP[]
     */
    public static function getAccountsFor($user, $owner = true)
    {
        $account = new self;

        $ljoin = [];
        $where = [
            'object_class' => " = 'CMediusers'",
        ];

        if ($owner) {
            $where['object_id'] = " = '{$user->_id}'";
            $where['name']      = " NOT LIKE '%apicrypt'";
        } else {
            $where["source_pop.is_private"]       = "= '0'";
            $where["users_mediboard.function_id"] = "= '$user->function_id'";
            $ljoin['users_mediboard']             = 'source_pop.object_id = users_mediboard.user_id';
        }

        return $account->loadList($where, null, null, null, $ljoin);
    }

    /**
     * Return the Apicrypt account for the given user
     *
     * @param CMediusers $user    The user
     * @param bool       $visible If true, only the accounts that are public will be returned
     *
     * @return CSourcePOP
     */
    public static function getApicryptAccountFor($user, $visible = false)
    {
        $account = new CSourcePOP();
        $where   = [
            'object_class' => "= 'CMediusers'",
            'object_id'    => "= '{$user->_id}'",
            'name'         => "= 'SourcePOP-{$user->_id}-apicrypt'",
        ];

        if ($visible) {
            $where['is_private'] = "= '0'";
        }

        $account->loadObject($where);

        return $account;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_pop';
        $spec->key   = 'source_pop_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["host"]        = "text autocomplete";
        $props["port"]        = "num default|25";
        $props["auth_ssl"]    = "enum list|None|SSL/TLS|STARTTLS";
        $props["password"]    = "password show|0 loggable|0";
        $props["timeout"]     = "num default|5 max|30";
        $props["type"]        = "enum notNull list|pop3|imap";
        $props["extension"]   = "str";
        $props["cron_update"] = "bool default|1";
        $props["is_private"]  = "bool default|0";
        $props["libelle"]     = "str notNull";

        $props["last_update"]  = "dateTime loggable|0";
        $props["object_id"]    = "ref notNull class|CMbObject meta|object_class back|sources_pop cascade";
        $props["object_class"] = "str notNull class show|0";
        $props["_server"]      = "str maxLength|255";

        return $props;
    }

    /**
     * Load object
     *
     * @return CMbObject|CMediusers
     * @throws Exception
     */
    function loadRefMetaObject()
    {
        $this->_ref_metaobject = CMbObject::loadFromGuid("$this->object_class-$this->object_id");
        if ($this->object_class == "CMediusers") {
            $this->_ref_mediuser = $this->_ref_metaobject;
            $this->_ref_mediuser->loadRefFunction();
        }

        return $this->_ref_metaobject;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        /* Reset the user accounts cache if a new source is created */
        if (!$this->_id) {
            CAppUI::resetMessagerieAccountsCache();
        }

        return parent::store();
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        /* Reset the user acocunts cache if a new account is created */
        CAppUI::getMessagerieAccounts(true);

        return parent::delete();
    }

    /**
     * return the number of mails linked to the present account
     *
     * @return int|null
     */
    function countRefMails()
    {
        return $this->_nb_ref_mails = $this->countBackRefs("user_mail_account");
    }

    /**
     * @inheritdoc
     */
    function isReachableSource()
    {
        return true;
    }

    function isAuthentificate()
    {
        $pop = new CPop($this);
        $this->startCallTrace();
        if (!$pop->open()) {
            $this->stopCallTrace();

            return false;
        }
        $pop->close();
        $this->stopCallTrace();

        return true;
    }

    /**
     * Get the number of unread message from the cache
     *
     * @param bool $reset Reset the cache or not
     *
     * @return int|mixed
     */
    public function getUnreadMessages($reset = false)
    {
        $cache = new Cache('CSourcePOP.getUnreadMessages', "unread-mail-{$this->_id}", Cache::OUTER, CUserMessageDest::getCacheLifetime());

        if ($reset) {
            $cache->rem();
        }

        if ($cache->exists()) {
            $this->_unread_messages = $cache->get();
        } else {
            $this->_unread_messages = CUserMail::countUnread($this->_id, true);
            $cache->put($this->_unread_messages);
        }

        return $this->_unread_messages;
    }
}
