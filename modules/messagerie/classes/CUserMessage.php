<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * The CUserMessage class
 */
class CUserMessage extends CMbObject
{
    // DB Fields
    public $usermessage_id;
    public $creator_id;
    public $subject;
    public $content;
    public $in_reply_to;
    /** @var bool Allow the sender to hide the others recipients when one view the message */
    public $hidden_recipients;

    // Form Fields
    public $_abstract;
    public $_can_edit;
    public $_mode;

    // References
    /** @var CMediusers */
    public $_ref_user_creator;
    /** @var CUserMessageDest[] */
    public $_ref_destinataires;
    /** @var CUserMessageDest */
    public $_ref_dest_user;
    /** @var CUserMessage */
    public $_ref_reply_to;

    /** @var CUserMessageAttachment[] */
    public $_ref_attachments;

    /**
     * Get specs
     *
     * @return CMbObjectSpec $spec
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "usermessage";
        $spec->key   = "usermessage_id";

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["subject"]           = "str notNull";
        $props["content"]           = "html";
        $props["creator_id"]        = "ref class|CMediusers notNull back|usermessage_created";
        $props["in_reply_to"]       = "ref class|CUserMessage back|usermessage_in_reply";
        $props['hidden_recipients'] = 'bool default|0';

        /* Form fields */
        $props['_abstract'] = 'text';

        return $props;
    }

    /**
     * @see parent::uodateFormFields()
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_abstract = str_replace(["\n", "\t", "\r"],
                                       ' ',
                                       substr(CMbString::htmlToText($this->content), 0, 50)) . '...';
        $this->_view     = ($this->subject ? "$this->subject  - " : "") . $this->_abstract;
    }

    public function updatePlainFields(): void
    {
        parent::updatePlainFields();
        $this->content = CMbString::purifyHTML($this->content);
    }

    /**
     * @inheritDoc
     */
    public function getPerm($permType)
    {
        return $this->_id ? $this->loadRefDestUser()->getPerm($permType) : parent::getPerm($permType);
    }

    /**
     * Load the list of destinataires
     *
     * @return CUserMessageDest[]
     */
    function loadRefDests()
    {
        $this->_ref_destinataires = $this->loadBackRefs("usermessage_destinataires");

        if ($this->hidden_recipients) {
            foreach ($this->_ref_destinataires as $key => $dest) {
                if ($dest->to_user_id !== CMediusers::get()->_id && $this->creator_id !== CMediusers::get()->_id) {
                    unset($this->_ref_destinataires[$key]);
                }
            }
        }

        return $this->_ref_destinataires;
    }

    /**
     * Load the user_connected destinataire of a message
     *
     * @return CUserMessageDest
     */
    function loadRefDestUser()
    {
        $user = CMediusers::get();
        $dest = new CUserMessageDest();
        if ($this->_id) {
            $where                    = [];
            $where["user_message_id"] = " = '$this->_id'";
            $where["to_user_id"]      = " = '$user->_id'";
            $where["datetime_sent"]   = " IS NOT NULL";
            $dest->loadObject($where);
            if ($dest->_id) {
                $dest->_ref_user_to = $user;
                $dest->loadRefFrom();
            }
        }

        return $this->_ref_dest_user = $dest;
    }

    /**
     * Load the creator
     *
     * @return CMediusers|null
     */
    function loadRefCreator()
    {
        return $this->_ref_user_creator = $this->loadFwdRef("creator_id");
    }

    /**
     * Load the message for which the current message is a reply
     *
     * @param bool $cache True if the cache is used
     *
     * @return CUserMessage
     */
    public function loadRefReplyTo($cache = true)
    {
        if (!$this->_ref_reply_to) {
            $this->_ref_reply_to = $this->loadFwdRef('in_reply_to', $cache);
        }

        return $this->_ref_reply_to;
    }

    /**
     * Load the message's attachments
     *
     * @return CUserMessageAttachment[]
     */
    public function loadRefsAttachments()
    {
        $this->_ref_attachments = $this->loadBackRefs('attachments');

        foreach ($this->_ref_attachments as $_attachment) {
            $_attachment->loadRefFile();
        }

        return $this->_ref_attachments;
    }

    public function sanitizeContent(): void
    {
        $this->content = CMbString::purifyHTML($this->content);

        if (strpos($this->content, '<br />') === false) {
        $this->content = nl2br($this->content);
        }
    }
}
