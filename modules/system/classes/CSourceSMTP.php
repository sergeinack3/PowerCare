<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Files\CLinkDestDispatch;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CElectronicDelivery;
use Ox\Mediboard\Messagerie\CMailAttachments;
use Ox\Mediboard\Messagerie\CUserMail;
use PHPMailer;
use phpmailerException;

class CSourceSMTP extends CExchangeSource
{
    // Source type
    public const TYPE = 'smtp';

    // DB Table key
    public $source_smtp_id;

    // DB Fields
    public $port;
    public $email;
    public $email_reply_to;
    public $auth;
    public $secure;
    public $timeout;
    public $debug;
    public $asynchronous;
    public $address_type;

    /** @var PHPMailer */
    public $_mail;
    public $_to = [];
    /** @var string The sender name */
    public $_sender_name = '';

    /** @var CSMTPBuffer SMTP buffer */
    public $_buffer;
    public $_skip_buffer;

    /**
     * Loads system-message SMTP source
     *
     * @return null|self
     */
    static function getSystemSource()
    {
        $exchange_source       = new self();
        $exchange_source->name = 'system-message';

        if ($exchange_source->loadMatchingObject()) {
            return $exchange_source;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_smtp';
        $spec->key   = 'source_smtp_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                   = parent::getProps();
        $props["port"]           = "num default|25";
        $props["email"]          = "email";
        $props["email_reply_to"] = "email";
        $props["auth"]           = "bool default|1";
        $props["secure"]         = "enum list|none|ssl|tls default|none";
        $props["timeout"]        = "num default|5";
        $props["debug"]          = "bool default|0";
        $props['asynchronous']   = 'bool default|0';
        $props['address_type']   = 'enum list|mail|apicrypt|mssante default|mail';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = ($this->email ? $this->email : $this->libelle) ? $this->libelle : $this->host;
    }

    /**
     * Mailer initialisation
     *
     * @return void
     */
    function init()
    {
        $this->_to   = [];
        $this->_mail = new PHPMailer(true);
        $this->_mail->IsSMTP();
        $this->_mail->SMTPAutoTLS = false;

        // Sets the prefix to the server
        if ($this->secure == 'ssl') {
            $this->_mail->SMTPSecure = "ssl";
        } elseif ($this->secure == 'tls') {
            $this->_mail->SMTPSecure = "tls";
        }

        $this->_mail->Host      = $this->host;
        $this->_mail->SMTPAuth  = $this->auth;
        $this->_mail->Port      = $this->port;
        $this->_mail->Username  = $this->user;
        $this->_mail->Password  = $this->getPassword();
        $this->_mail->SMTPDebug = $this->debug ? 2 : 0;
        $this->_mail->Timeout   = $this->timeout;

        $this->_buffer = new CSMTPBuffer();
        $this->_buffer->init();

        $this->setFrom($this->email, $this->_sender_name, 0);

        if ($this->email_reply_to) {
            $this->addRe($this->email_reply_to, $this->_sender_name);
        }
    }

    /**
     * Set FROM field
     *
     * @param string  $address Email address
     * @param string  $name    Displayed name
     * @param integer $auto    Sets REPLY TO
     *
     * @return mixed
     */
    function setFrom($address, $name = '', $auto = 1)
    {
        $this->_buffer->_input['from'] = $address;

        $this->startCallTrace();
        $res = $this->_mail->SetFrom($address, $name, $auto);
        $this->stopCallTrace();

        return $res;
    }

    /**
     * Set the sender name from the user
     *
     * @param CMediusers $user         The user
     * @param boolean    $use_function Use the function name if it's a cabinet function
     *
     * @return void
     */
    function setSenderNameFromUser($user, $use_function = false)
    {
        $user->loadRefFunction();
        if ($user->_ref_function->type == 'cabinet' && $use_function) {
            $this->_sender_name = $user->_ref_function->text;
            if (CAppUI::pref('lang') == 'fr' && strpos(strtolower($user->_ref_function->text), 'cabinet') === false) {
                $this->_sender_name = 'Cabinet ' . $this->_sender_name;
            }
        } else {
            $this->_sender_name = "$user->_user_last_name $user->_user_first_name";
        }
    }

    /**
     * Set a supposably unique to-address
     *
     * @param string $address E-mail address
     * @param string $name    Display name
     *
     * @return bool Job done
     */
    function setRecipient($address, $name = '')
    {
        return $this->addTo($address, $name);
    }

    /**
     * Add a to-address
     *
     * @param string $address E-mail address
     * @param string $name    Display name
     *
     * @return bool  Job done
     */
    function addTo($address, $name = '')
    {
        $this->_buffer->_input['to'][] = $address;

        $this->_to[] = ['address' => $address, 'name' => $name];

        return $this->_mail->AddAddress($address, $name);
    }

    /**
     * Add a cc-address
     *
     * @param string $address E-mail address
     * @param string $name    Display name
     *
     * @return bool
     * Job done
     */
    function addCc($address, $name = '')
    {
        $this->_buffer->_input['cc'][] = $address;

        return $this->_mail->AddCC($address, $name);
    }

    /**
     * Add a bcc-address
     *
     * @param string $address E-mail address
     * @param string $name    Display name
     *
     * @return boolean Job done
     */
    function addBcc($address, $name = '')
    {
        $this->_buffer->_input['bcc'][] = $address;

        return $this->_mail->AddBCC($address, $name);
    }

    /**
     * Add a replyto-address
     *
     * @param string $address E-mail address
     * @param string $name    Display name
     *
     * @return bool Job done
     */
    function addRe($address, $name = '')
    {
        $this->_buffer->_input['re'][] = $address;

        return $this->_mail->AddReplyTo($address, $name);
    }

    function setSubject($subject)
    {
        $subject                          = str_replace(["\'", '\"'], ["'", '"'], $subject);
        $this->_buffer->_input['subject'] = $subject;

        $this->_mail->Subject = $subject;
    }

    function setBody($body, bool $html = true)
    {
        $body                          = str_replace(["\'", '\"'], ["'", '"'], $body);
        //$this->_buffer->_input['body'] = CMbString::purifyHTML($body);
        $this->_buffer->_input['body'] = $body;

        if ($html) {
            $this->_mail->MsgHTML($body);
        } else {
            $this->_mail->isHTML(false);
            $this->_mail->Body = $body;
        }
    }

    function addAttachment($file_path, $name = '')
    {
        $type = mime_content_type($file_path);

        if ($name) {
            $path_infos = pathinfo($name);

            $name = CMbString::removeBanCharacter($path_infos['filename'], true) . '.' . $path_infos['extension'];
        }

        $this->_buffer->_input['attachments'][] = [
            'name'     => $name,
            'path'     => $file_path,
            'encoding' => 'base64',
            'type'     => $type,
        ];

        $this->_mail->AddAttachment($file_path, $name, 'base64', $type);
    }

    function addEmbeddedImage($file_path, $cid)
    {
        $this->_buffer->_input['embedded_images'][] = [
            'cid'      => $cid,
            'path'     => $file_path,
            'encoding' => 'base64',
            'type'     => 'application/octet-stream',
        ];

        $this->_mail->AddEmbeddedImage($file_path, $cid);
    }

    /**
     * @inheritdoc
     */
    function send($evenement_name = null)
    {
        if ($this->asynchronous && !$this->_skip_buffer) {
            return $this->bufferize();
        }

        try {
            $this->startCallTrace();
            $res = $this->_mail->send();
            $this->stopCallTrace();

            return $res;
        } catch (phpmailerException $e) {
            $this->stopCallTrace();
            throw $e;
        }
    }

    /**
     * Store mail in buffer
     *
     * @return null|string
     */
    function bufferize()
    {
        $buffer                = $this->_buffer;
        $buffer->source_id     = $this->_id;
        $buffer->creation_date = CMbDT::dateTime();
        $buffer->user_id       = CUser::get()->_id;

        return $buffer->store();
    }

    /**
     * @param integer $user_id
     * @param array   $objects
     * @param bool    $apicrypt
     * @param array   $destinataires_item_ids
     *
     * @return null|CUserMail
     */
    function createUserMail($user_id, $objects = null, $apicrypt = false, $destinataires_item_ids = null)
    {
        $mail = null;

        if (CModule::getActive('messagerie')) {
            $mail                = new CUserMail();
            $mail->account_id    = $this->_id;
            $mail->account_class = $this->_class;

            $mail->subject    = $this->_mail->Subject;
            $mail->from       = $this->_mail->From;
            $mail->to         = implode(',', CMbArray::pluck($this->_to, 'address'));
            $mail->date_inbox = CMbDT::dateTime();
            $mail->draft   = '1';

            if ($this->_mail->ContentType == 'text/html') {
                $mail->_text_html = $this->_mail->Body;
                if ($apicrypt) {
                    $mail->is_apicrypt = '1';
                }
                $mail->getHtmlText($user_id);
            } else {
                $mail->_text_plain = $this->_mail->Body;
                if ($apicrypt) {
                    $mail->is_apicrypt = '1';
                }
                $mail->getPlainText($user_id);
            }

            if ($this->asynchronous) {
                $mail->to_send = '1';
            }

            $mail->store();

            // Création des liens entre le mail et les destinataires
            if (is_countable($destinataires_item_ids) && count($destinataires_item_ids)) {
                foreach ($destinataires_item_ids as $_destinataire_item_id) {
                    $link                       = new CLinkDestDispatch();
                    $link->destinataire_item_id = $_destinataire_item_id;
                    $link->dispatch_class       = "CUserMail";
                    $link->dispatch_id          = $mail->_id;
                    $link->store();
                }
            }

            if (!is_array($objects)) {
                $objects = [$objects];
            }

            CMbArray::removeValue(null, $objects);

            if (count($objects)) {
                foreach ($objects as $_object) {
                    $file = null;
                    switch ($_object->_class) {
                        case "CCompteRendu":
                            $file = $_object->_ref_file;
                            break;
                        case "CFile":
                            $file = $_object;
                            break;
                        default:
                    }

                    /* Create the delivery, for keep tracks of the deliveries of the document */
                    $delivery                 = new CElectronicDelivery();
                    $delivery->document_class = $_object->_class;
                    $delivery->document_id    = $_object->_id;
                    $delivery->message_class  = $mail->_class;
                    $delivery->message_id     = $mail->_id;
                    $delivery->store();

                    if ($file && $file->_id) {
                        $attachment          = new CMailAttachments();
                        $attachment->mail_id = $mail->_id;
                        [$type, $subtype] = explode('/', $file->file_type);
                        $attachment->type    = $attachment->getTypeInt($type);
                        $attachment->part    = 1;
                        $attachment->subtype = $subtype;
                        $attachment->bytes   = $file->doc_size;
                        [$file_name, $extension] = explode('.', $file->file_name);
                        $attachment->name      = $file_name;
                        $attachment->extension = $extension;
                        $attachment->file_id   = $file->_id;
                        $attachment->store();
                    }
                }
            }
        }

        return $mail;
    }

    /**
     * Returns the main SMTP source of the user
     *
     * @param CMediusers $user
     *
     * @return CSourceSMTP|null
     */
    public static function getSourceForUser(CMediusers $user): ?CSourceSMTP
    {
        try {
            $source = self::get("mediuser-{$user->_id}", self::TYPE);
        } catch (Exception $e) {
            $source = new self();
        }

        if (!$source->_id || !$source instanceof CSourceSMTP) {
            $source = null;
        }

        return $source;
    }
}
