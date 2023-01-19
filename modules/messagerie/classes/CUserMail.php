<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Apicrypt\CApicrypt;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CContentAny;
use Ox\Mediboard\System\CContentHTML;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CSourceSMTP;
use stdClass;

/**
 * Used for external e-mail from the CsourcePOP
 */
class CUserMail extends CMbObject {

  public $user_mail_id;  //key

  public $account_id; //Source id
  public $account_class;// Source class

  //headers
  public $subject;      //subject of the mail
  public $from;         //who sent it
  public $_from;        //who sent it, readable
  public $to;           //complete recipient
  public $_to;          //recipient readable
  public $cc;
  public $_cc;
  public $bcc;
  public $_bcc;
  public $date_inbox;   //sent date
  public $date_read;    //date of the first read of the mail
  public $_msgno;       //message sequence number in the mailbox
  public $uid;
  public $answered;     //this message is flagged as answered
  public $hash;         //hash of the content of the mail, the subject, the addresses
  public $folder_id;
  public $send_fail;

  //status
  public $favorite;     // favorite, important email
  public $archived;     // is the mail archived, (hidden)
  public $sent;         // mail has been sent
  public $draft;        // mail has been drafted
  public $to_send;      // The mail is marked to be sent by the cron
  public $retry_count;  // Count the numbe rof retries when the mails are send in asynchronous mode

  public $in_reply_to_id; //is a reply to this message id
  public $text_file_id;
  public $_ref_file_linked;

  /* Patient link */
  /** @var integer The linked patient */
  public $linked_patient_id;
  /** @var CPatient The patient guessed from the HPRIM header */
  public $_guessed_patient;
  /** @var CPatient[] A list of patients guessed from the HPRIM header */
  public $_guessed_patients;

  //body
  public $text_plain_id; //plain text (no html) = CContentAny_id
  public $_text_plain;
  public $_ref_account_;
  public $is_apicrypt;
  public $is_hprimnet;
  public $_is_hprim;
  public $_content;
  public $_hprim_content;

  public $text_html_id; //html text = CContentHTML_id
  public $_text_html;

  /** @var CMailAttachments[] $_attachments */
  public $_attachments           = array();

  public $_parts;

  public $_size; //size in bytes
  public $_date_inbox;
  public $_date_read;

  /** @var CMailPartToFile[] The links of the CContent */
  public $_ref_linked_files;

  /** @var CUserMailFolder The folder */
  public $_ref_folder;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'user_mail';
    $spec->key   = 'user_mail_id';
    $spec->loggable = false;
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["subject"]       = "str";
    $props["account_id"]    = "ref notNull class|CExchangeSource meta|account_class back|user_mail_account cascade";
    $props["account_class"] = "enum list|CSourcePOP|CSourceSMTP notNull";
    $props["from"]          = "str";
    $props["_from"]         = "str";
    $props["to"]            = "str";
    $props["_to"]           = "str";
    $props['cc']            = 'str';
    $props['_cc']           = 'str';
    $props['bcc']           = 'str';
    $props['_bcc']          = 'str';
    $props["date_inbox"]    = "dateTime";
    $props["date_read"]     = "dateTime";
    $props["_msgno"]        = "num";
    $props["uid"]           = "num";
    $props["answered"]      = "bool default|0";
    $props["favorite"]      = "bool default|0";
    $props["archived"]      = "bool default|0";
    $props["sent"]          = "bool default|0";
    $props['draft']         = 'bool default|0';
    $props['to_send']       = 'bool default|0';
    $props['retry_count']   = 'num default|0';
    $props['is_apicrypt']   = 'bool default|0';
    $props['is_hprimnet']   = 'bool default|0';
    $props['hash']          = "text";
    $props['folder_id']     = 'ref class|CUserMailFolder unlink back|mails';
    $props['send_fail']     = 'bool default|0';
    $props['_content']      = 'html';
    //$props["msg_references"]= "str";
    $props["in_reply_to_id"] = "ref class|CUserMail back|reply_of";
    $props["text_file_id"]  = "ref class|CFile back|mail_content_id";
    $props['linked_patient_id'] = 'ref class|CPatient back|mails';

    $props["text_plain_id"]    = "ref class|CContentAny cascade back|usermail_plain";
    $props["text_html_id"]     = "ref class|CContentHTML cascade back|usermail_html";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }

    //a message flag as sent cannot be archived
    if ($this->sent && $this->archived) {
      return "CUserMail-msg-AMessageSentCannotBeArchived";
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function delete() {
    $files = $this->loadLinkedFiles();
    $content_html = $this->loadContentHTML();
    $content_text = $this->loadContentPlain();

    if ($msg = parent::delete()) {
      return $msg;
    }

    // Remove html content
    if ($content_html->_id) {
      if ($msg = $content_html->delete()) {
        return $msg;
      }
    }

    // Remove plain content
    if ($content_text->_id) {
      if ($msg = $content_text->delete()) {
        return $msg;
      }
    }

    foreach ($files as $_link) {
      if ($msg = $_link->delete()) {
        return $msg;
      }
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  public function updateFormFields() {
    parent::updateFormFields();

    $this->_date_inbox = CMbDT::date(null, $this->date_inbox);
    if ($this->_date_inbox == CMbDT::date()) {
      $this->_date_inbox = CMbDT::format($this->date_inbox, '%H:%M');
    }
    else if (CMbDT::format($this->date_inbox, '%Y') == CMbDT::format(CMbDT::date(), '%Y')) {
      $this->_date_inbox = CMbDT::format($this->date_inbox, '%d %B');
    }

    $this->_date_read = CMbDT::date(null, $this->date_read);
    if ($this->_date_read == CMbDT::date()) {
      $this->_date_read = CMbDT::format($this->date_read, '%H:%M');
    }
    else if (CMbDT::format($this->date_read, '%Y') == CMbDT::format(CMbDT::date(), '%Y')) {
      $this->_date_read = CMbDT::format($this->date_read, '%d %B');
    }
  }

  /**
   * @inheritdoc
   */
  public function store() {
    /* Reset the unread messages cache */
    if ((!$this->_id || $this->fieldModified('date_read')) && $this->account_id && $this->account_class == 'CSourcePOP') {
      $account = CSourcePOP::loadFromGuid("{$this->account_class}-{$this->account_id}");
      $account->getUnreadMessages(true);
    }

    return parent::store();
  }

  /**
   * Return the list of uid for an account_id
   *
   * @param int $account_id account id = source_pop_id
   *
   * @return array
   */
  static function getListMailInMb($account_id) {
    $mail = new self;
    $ds = $mail->getDS();
    $query = "SELECT `uid` FROM `user_mail` WHERE `account_id` = '$account_id' AND `account_class` = 'CSourcePOP'";
    return $ds->loadColumn($query);
  }

  /**
   * Get the last uid mail from mb
   *
   * @param int $account_id account_id = source pop
   *
   * @return string|null
   */
  static function getLastMailUid($account_id) {
    $mail = new self;
    $ds = $mail->getDS();
    $query = "SELECT MAX(`uid`) FROM `user_mail` WHERE `account_id` = '$account_id' AND `account_class` = 'CSourcePOP'";
    return $ds->loadResult($query);
  }

  /**
   * Get the date of the last mail received
   *
   * @param integer $account_id The account id
   *
   * @return string
   */
  static function getLastMailDate($account_id) {
    $mail = new self;
    $ds = $mail->getDS();
    $query = "SELECT MAX(`date_inbox`) FROM `user_mail` 
                WHERE `account_id` = '$account_id' AND `account_class` = 'CSourcePOP' AND sent = '0';";
    $date = $ds->loadResult($query);
    $date = ($date) ? $date : CMbDT::dateTime();
    return $date;
  }

  /**
   * Get the date of the first mail received
   *
   * @param integer $account_id The account id
   *
   * @return string
   */
  static function getFirstMailDate($account_id) {
    $mail = new self;
    $ds = $mail->getDS();
    $query = "SELECT MIN(`date_inbox`) FROM `user_mail`
                WHERE `account_id` = '$account_id' AND `account_class` = 'CSourcePOP' AND sent = '0';";
    $date = $ds->loadResult($query);
    $date = ($date) ? $date : CMbDT::dateTime();
    return $date;
  }


  /**
   * Used to load the mail from SourcePOP
   *
   * @param string $hash The hash of the mail
   *
   * @return bool|int|null
   */
  function loadMatchingFromHash($hash) {
    $this->hash = $hash;
    $this->loadMatchingObject();

    return $this->_id;
  }

  /**
   * Get the data from the header
   *
   * @param mixed $source The source
   *
   * @return void
   */
  public function setHeaderFromSource($source) {
    //assignment
    $this->uid          = $source->uid;
    $this->loadMatchingObject();

    $this->subject      = (isset($source->subject)) ? self::flatMimeDecode($source->subject) : null;
    $this->from         = (isset($source->fromaddress)) ? self::flatMimeDecode($source->fromaddress) : null;
    $this->to           = (isset($source->toaddress)) ? self::flatMimeDecode($source->toaddress) : null;
    $this->cc           = (isset($source->ccaddress)) ? self::flatMimeDecode($source->ccaddress) : null;
    $this->bcc          = (isset($source->bccaddress)) ? self::flatMimeDecode($source->bccaddress) : null;
    $this->date_inbox   = (isset($source->date)) ? CMbDT::dateTime($source->date) : CMbDT::dateTime();

    //cleanup
    if (empty($source->Unseen)) {
      $this->date_read = $this->date_inbox;
    }

    $this->unescapeValues();
  }


  /**
   * Get the plain text from the mail structure
   *
   * @param int $source_object_id the user id
   *
   * @return mixed
   */
  function getPlainText($source_object_id) {
    if ($this->_text_plain) {
      $textP = new CContentAny();
      //apicrypt
      if (CModule::getActive("apicrypt") && $this->is_apicrypt && CApicrypt::getEncryptionAgent($source_object_id) === 'mb') {
        $textP->content = CApicrypt::uncryptBody($source_object_id, $this->_text_plain)."\n[apicrypt]";
      }
      else {
        $textP->content = $this->_text_plain;
      }

      if (!$msg = $textP->store()) {
        $this->text_plain_id = $textP->_id;
      }
    }

    return $this->text_plain_id;
  }

  /**
   * Get the html text from the mail structure
   *
   * @param int $source_object_id the user id
   *
   * @return mixed
   */
  function getHtmlText($source_object_id) {
    if ($this->_text_html) {
      $textH = new CContentHTML();

      //apicrypt
      if (CModule::getActive("apicrypt") && $this->is_apicrypt && CApicrypt::getEncryptionAgent($source_object_id) === 'mb') {
        $this->_text_html = CApicrypt::uncryptBody($source_object_id, $this->_text_html);
      }

      $textH->content = CUserMail::purifyHTML($this->_text_html); //cleanup

      if (!$msg = $textH->store()) {
        $this->text_html_id = $textH->_id;
      }
    }

    return $this->text_html_id;
  }


  /**
   * Create the CFiles attached to the mail
   *
   * @param CMailAttachments[] $attachList  The list of CMailAttachment
   * @param CPop               $popClient   the CPop client
   * @param bool               $retrieve    If true, the files will be retrieved automatically
   * @param bool               $display_msg If true, confirmation of the file creation will be displayed
   *
   * @return void
   */
  function attachFiles($attachList, $popClient, $retrieve = false, $display_msg = false) {
    //size limit
    $size_required = CAppUI::pref("getAttachmentOnUpdate");
    if ($size_required == "") {
      $size_required = 0;
    }

    foreach ($attachList as $_attch) {
      $_attch->mail_id = $this->_id;
      $_attch->loadMatchingObject();
      if (!$_attch->_id) {
        $_attch->store();
      }
      //si preference taille ok OU que la piece jointe est incluse au texte => CFile
      if ($retrieve || ($_attch->bytes <= $size_required ) || $_attch->disposition == "INLINE") {

        $file = new CFile();
        $file->setObject($_attch);
        $file->author_id  = CAppUI::$user->_id;

        if (!$file->loadMatchingObject()) {
          $file_pop = $popClient->decodeMail($_attch->encoding, $popClient->openPart($this->uid, $_attch->getpartDL()));
          $file->file_name  = $_attch->name;

          //apicrypt attachment
          if (strpos($_attch->name, ".apz") !== false) {
            $file_pop = CApicrypt::uncryptAttachment($popClient->source->object_id, $file_pop);

            if (count($file_pop)) {
                $_attch->name = CMbPath::getBasename($file_pop['file_name']);
                $file->file_name  = $_attch->name;
                $file_pop = $file_pop['content'];
            }
          }

          $mime = $this->extensionDetection($file_pop);

          if ($file_pop === null || $file_pop === '') {
            CAppUI::setMsg("CMailAttachments-msg-unable_to_get_attachment", UI_MSG_ERROR);
            return;
          }

          //file name
          $infos = pathinfo($_attch->name);
          $extension = $infos['extension'];
          $exts = explode("/", $mime);
          $mime_extension = strtolower(end($exts));
          if (strtolower($extension) != $mime_extension) {
            $file->file_name  = $infos['filename'].".".$mime_extension;
          }

          $file->file_type  = $mime ? $mime : $_attch->getType($_attch->type, $_attch->subtype);
          $file->fillFields();
          $file->updateFormFields();
          $file->setContent($file_pop);
          $msg = $file->store();

          if ($msg && $display_msg) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
          }
          elseif ($display_msg) {
            CAppUI::setMsg("CMailAttachments-msg-attachment_saved", UI_MSG_OK);
          } else {
              $_attch->subtype = $file->file_type;
              $_attch->file_id = $file->_id;
              $_attch->store();
          }
        }
      }
    }
  }

  /**
   * Detect the mime type
   *
   * @param string $file_contents The content
   *
   * @return string
   */
  function extensionDetection($file_contents) {
    $dir = dirname(dirname(dirname(__DIR__))) . "/tmp/attachment";
    file_put_contents($dir, $file_contents);
    $mime = mime_content_type($dir);
    unset($dir);
    return $mime;
  }


  /**
   * Load the visual fields
   *
   * @return null
   */
  function loadReadableHeader() {
    $this->_from = $this->adressToUser($this->from);
    $this->_to   = $this->adressToUser($this->to);
    return;
  }

  /**
   * Load mail content from CSoursePOP source
   *
   * @param array $contentsource test
   *
   * @return null
   */
  function setContentFromSource($contentsource) {
    $this->_text_plain   = $contentsource["text"]["plain"];
    $this->is_apicrypt   = $contentsource["text"]["is_apicrypt"] ? '1' : '0';
    $this->_text_html    = $contentsource["text"]["html"];
    $this->_attachments  = $contentsource["attachments"];
    return;
  }

  /**
   * Make the hash for the given headers and mail content
   *
   * @param stdClass $header  The headers, returned by the POP source
   * @param array    $content The content, returned by the POP source
   *
   * @return bool|string
   */
  public function makeHash($header, $content) {
    if (!isset($header->fromaddress) || !isset($header->toaddress)) {
      return null;
    }

    $data = "==FROM==\n" . self::flatMimeDecode($header->fromaddress) . "\n==TO==\n" . self::flatMimeDecode($header->toaddress);

    if (isset($header->subject)) {
      $data .= "\n==SUBJECT==\n" . self::flatMimeDecode($header->subject);
    }

    $text = '';
    if (!empty($content['text']['html'])) {
      $text = $content['text']['html'];
    }
    elseif (!empty($content['text']['plain'])) {
      $text = $content['text']['plain'];
    }

    $data .= "\n==CONTENT==\n$text";
    return CMbSecurity::hash(CMbSecurity::SHA256, $data);
  }

  /**
   * Used for decoding a multi mime string into one line
   *
   * @param string $string decode mime string
   *
   * @return string
   */
  private function flatMimeDecode($string) {
    $parts = imap_mime_header_decode($string);
    $str = implode("", CMbArray::pluck($parts, "text"));
    if (strpos($string, 'UTF-8') !== false) {
      $str = utf8_decode($str);
    }

    return addslashes($str);
  }

  /**
   * Check if html content has image inline and return true if an image has been found.
   *
   * @return bool
   */
  function checkInlineAttachments() {
    if (!count($this->_attachments) || !$this->_text_html->content) {
      return false;
    }

    foreach ($this->_attachments as $_attachment) {
      $_attachment->loadFiles();
      if (!isset($_attachment->_id) || $_attachment->disposition != "INLINE") {
        continue;
      }

      $_attachment->id = preg_replace("/(<|>)/", "", $_attachment->id);
      if (preg_match("/$_attachment->id/", $this->_text_html->content)) {
        if (isset($_attachment->_file->_id)) {
          $url = "?m=files&raw=thumbnail&document_guid=`$_attachment->_file->_class`-`$_attachment->_file->_id`";
          $this->_text_html->content = str_replace("cid:$_attachment->id", $url , $this->_text_html->content);
        }
      }
    }
    return true;
  }

  /**
   * Return the cleaned string
   *
   * @param string $string an address string example: <foo@bar.com>"Mr Foo"
   *
   * @return mixed
   */
  private function adressToUser($string) {
    $email_complex = '/^(.+)(<[A-Za-z0-9._%-@ +]+>)$/';
    if (preg_match($email_complex, $string, $out)) {
      if (count($out)>1) {
        $out = str_replace('"', "", $out);
        return $out[1];
      }
    }
    return $string;
  }

  /**
   * Load the text_plain ref
   *
   * @return CContentAny
   */
  function loadContentPlain() {
    /** @var CContentAny _text_plain */
    $this->_text_plain = $this->loadFwdRef("text_plain_id");

    if ($this->is_apicrypt && $this->sent === '1' && strpos($this->_text_plain->content, '$APICRYPT') !== false) {
        $this->loadAccount();
        if ($this->account_class == 'CSourceSMTP') {
            $user_id = explode('-', $this->_ref_source_account->name)[1];
        }
        else {
            $user_id = $this->_ref_source_account->object_id;
        }
        $this->_text_plain->content = CApicrypt::uncryptBody($user_id, $this->_text_plain->content);
        $this->_text_plain->store();
    }

    return $this->_text_plain;
  }

  /**
   * Load the text_html ref
   *
   * @return CContentHTML
   */
  function loadContentHTML() {
    /** @var CContentHTML _text_html */
    return $this->_text_html = $this->loadFwdRef("text_html_id");
  }

  /**
   * Load accoun user
   *
   * @return CMbObject
   */
  function loadAccount() {
    return $this->_ref_source_account = CMbObject::loadFromGuid("$this->account_class-$this->account_id", true);
  }

  /**
   * Load attachments of the present mail
   *
   * @return CStoredObject[]
   */
  function loadAttachments() {
    return  $this->_attachments = $this->loadBackRefs("mail_attachments", 'part ASC');
  }

  /**
   * Load files linked
   *
   * @return CMbObject
   */
  function loadFileLinked() {
    $file = $this->loadFwdRef("text_file_id");
    $file->loadRefsFwd(); //@TODO Fix this !
    return $this->_ref_file_linked = $file;
  }

  /**
   * Load the CFiles that represent the links between the content of the mail and an object
   *
   * @return CMailPartToFile[]
   */
  public function loadLinkedFiles() {
    if (!$this->_ref_linked_files) {
      $this->_ref_linked_files = CMailPartToFile::loadFor($this);

      foreach ($this->_ref_linked_files as $_link) {
        $_link->_ref_attachment = $this;
        $_link->loadRefFile();
        $_link->_ref_file->loadTargetObject();
      }
    }

    return $this->_ref_linked_files;
  }

  /**
   * Load the parent folder
   *
   * @param bool $cache Use of the object cache
   *
   * @return CUserMailFolder
   */
  public function loadFolder($cache = true) {
    if (!$this->_ref_folder) {
      $this->_ref_folder = $this->loadFwdRef('folder_id');
    }

    return $this->_ref_folder;
  }

  /**
   * Check if there is hprim headers
   *
   * @return int|null
   */
  function checkHprim() {
    if ($this->_text_plain->content == "") {
      return false;
    }
    $this->loadAttachments();

    foreach ($this->_attachments as $_attachment) {
      if (strpos($_attachment->subtype, 'hprim') !== false) {
        $_attachment->loadFiles();
        $_file = file_get_contents($_attachment->_file->_file_path);
        $this->_hprim_content = preg_split("/(\r\n|\n)/", $_file, 13);
        break;
      }
    }

    if (!$this->_hprim_content) {
      $this->_hprim_content = preg_split("/(\r\n|\n)/", $this->_text_plain->content, 13);
    }

    if (count($this->_hprim_content) >= 13) {
      $date_regex = "^([0-3][0-9])[/](0[1-9]|1[0-2])[/]([0-9]{4})$^";
      if (preg_match($date_regex, $this->_hprim_content[6]) && preg_match($date_regex, $this->_hprim_content[9])) {
        $this->_is_hprim = 1;
      }
    }
    return $this->_is_hprim;
  }

  /**
   * Check if the content plain is from apicrypt
   *
   * @return bool|null
   */
  function checkApicrypt() {

    if ($this->_text_plain->content == "") {
      return false;
    }

    return $this->is_apicrypt;
  }

  /**
   * Load complete email
   *
   * @return int|void
   */
  function loadRefsFwd() {
    $this->loadContentHTML();
    $this->loadContentPlain();
    $this->loadAttachments();
    $this->loadAccount();
    $this->loadFileLinked();
    $this->loadLinkedFiles();
    $this->loadFolder();
    return;
  }

  /**
   * Count the unread mails for an account
   *
   * @param int  $account_id The account id
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return int
   */
  public static function countUnread($account_id, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['archived'] = "= '0'";
    $where['sent'] = "= '0'";
    $where['date_read'] = 'IS NULL';
    $where['draft'] = "= '0'";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $mail = new CUserMail();
    return $mail->countList($where);
  }

  /**
   * Count the mails in the inbox for an account
   *
   * @param int  $account_id The account id
   *
   * @return bool
   */
  public static function hasReceivedMails($account_id) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['sent'] = "= '0'";
    $where['draft'] = "= '0'";

    $mail = new CUserMail();
    return $mail->countList($where) > 0;
  }

  /**
   * Count the mails in the inbox for an account
   *
   * @param int  $account_id The account id
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return int
   */
  public static function countInbox($account_id, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['archived'] = "= '0'";
    $where['sent'] = "= '0'";
    $where['draft'] = "= '0'";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $mail = new CUserMail();
    return $mail->countList($where);
  }

  /**
   * Load the mails in the inbox for an account
   *
   * @param int  $account_id The account id
   * @param int  $start      The start
   * @param int  $limit      The number of mails to load
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return CUserMail[]
   */
  public static function loadInbox($account_id, $start, $limit, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['archived'] = "= '0'";
    $where['sent'] = "= '0'";
    $where['draft'] = "= '0'";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $order = "date_inbox DESC";
    $limit= "$start, $limit";
    $mail = new CUserMail();
    return $mail->loadList($where, $order, $limit);
  }

  /**
   * Count the archived mails for an account
   *
   * @param int  $account_id The account id
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return int
   */
  public static function countArchived($account_id, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['archived'] = "= '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $mail = new CUserMail();
    return $mail->countList($where);
  }

  /**
   * Load the archived mails for an account
   *
   * @param int  $account_id The account id
   * @param int  $start      The start
   * @param int  $limit      The number of mails to load
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return CUserMail[]
   */
  public static function loadArchived($account_id, $start, $limit, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['archived'] = "= '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $order = "date_inbox DESC";
    $limit= "$start, $limit";
    $mail = new CUserMail();
    return $mail->loadList($where, $order, $limit);
  }

  /**
   * Count the favoured mails for an account
   *
   * @param int  $account_id The account id
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return int
   */
  public static function countFavorites($account_id, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['favorite'] = "= '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $mail = new CUserMail();
    return $mail->countList($where);
  }

  /**
   * Load the favoured mails for an account
   *
   * @param int  $account_id The account id
   * @param int  $start      The start
   * @param int  $limit      The number of mails to load
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return CUserMail[]
   */
  public static function loadFavorites($account_id, $start, $limit, $subfolders = false) {
    $where['account_id'] = "= '$account_id'";
    $where['account_class'] = "= 'CSourcePOP'";
    $where['favorite'] = "= '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $order = "date_inbox DESC";
    $limit= "$start, $limit";
    $mail = new CUserMail();
    return $mail->loadList($where, $order, $limit);
  }

  /**
   * Count the number of sent mails for an account
   *
   * @param int  $account_id The account id
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return int
   */
  public static function countSent($account_id, $subfolders = false) {
    $source_smtp = self::getSMTPSourceFor($account_id);
    if ($source_smtp->_id) {
      $where[] = "(account_id = '$account_id' AND account_class = 'CSourcePOP')
        OR (account_id = '$source_smtp->_id' AND account_class = 'CSourceSMTP')";
    }
    else {
      $where['account_id'] = "= '$account_id'";
      $where['account_class'] = "= 'CSourcePOP'";
    }
    $where['sent'] = " = '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $mail = new CUserMail();
    return $mail->countList($where);
  }

  /**
   * Load the sent mails for an account
   *
   * @param int  $account_id The account id
   * @param int  $start      The start
   * @param int  $limit      The number of mails to load
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return CUserMail[]
   */
  public static function loadSent($account_id, $start, $limit, $subfolders = false) {
    $source_smtp = self::getSMTPSourceFor($account_id);
    if ($source_smtp->_id) {
      $where[] = "(account_id = '$account_id' AND account_class = 'CSourcePOP')
        OR (account_id = '$source_smtp->_id' AND account_class = 'CSourceSMTP')";
    }
    else {
      $where['account_id'] = "= '$account_id'";
      $where['account_class'] = "= 'CSourcePOP'";
    }
    $where['sent'] = " = '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $order = "date_inbox DESC";
    $limit= "$start, $limit";
    $mail = new CUserMail();
    return $mail->loadList($where, $order, $limit);
  }

  /**
   * Count the number of drafted mails for an account
   *
   * @param int  $account_id The account id
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return int
   */
  public static function countDrafted($account_id, $subfolders = false) {
    $source_smtp = self::getSMTPSourceFor($account_id);
    if ($source_smtp->_id) {
      $where[] = "(account_id = '$account_id' AND account_class = 'CSourcePOP')
        OR (account_id = '$source_smtp->_id' AND account_class = 'CSourceSMTP')";
    }
    else {
      $where['account_id'] = "= '$account_id'";
      $where['account_class'] = "= 'CSourcePOP'";
    }
    $where['draft'] = "= '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $mail = new CUserMail();
    return $mail->countList($where);
  }

  /**
   * Load the drafted mails for an account
   *
   * @param int  $account_id The account id
   * @param int  $start      The start
   * @param int  $limit      The number of mails to load
   * @param bool $subfolders If true, the messages in subfolders will be included
   *
   * @return CUserMail[]
   */
  public static function loadDrafted($account_id, $start, $limit, $subfolders = false) {
    $source_smtp = self::getSMTPSourceFor($account_id);
    if ($source_smtp->_id) {
      $where[] = "(account_id = '$account_id' AND account_class = 'CSourcePOP')
        OR (account_id = '$source_smtp->_id' AND account_class = 'CSourceSMTP')";
    }
    else {
      $where['account_id'] = "= '$account_id'";
      $where['account_class'] = "= 'CSourcePOP'";
    }
    $where['draft'] = "= '1' ";

    if (!$subfolders) {
      $where['folder_id'] = ' IS NULL';
    }

    $order = "date_inbox DESC";
    $limit= "$start, $limit";
    $mail = new CUserMail();
    return $mail->loadList($where, $order, $limit);
  }

  /**
   * Search the mails of the given account, with the given query and options
   *
   * @param integer $account_id The account id
   * @param string  $folder     The folder (either a CUserMailFolder's id or a main folder name, such as inbox)
   * @param string  $query      The keywords to search
   * @param array   $options    An array of the search options (fields and range)
   * @param string  $limit      The limit clause
   *
   * @return array An array containing two data: count (the number of result), and mails (the actual results)
   */
  public static function search($account_id, $folder, $query = '', $options = array('range' => 'actual'), $limit = null) {
    $where = array();

    $source_smtp = self::getSMTPSourceFor($account_id);
    if ($source_smtp->_id) {
      $where[] = "(user_mail.account_id = '$account_id' AND user_mail.account_class = 'CSourcePOP')
        OR (user_mail.account_id = '$source_smtp->_id' AND user_mail.account_class = 'CSourceSMTP')";
    }
    else {
      $where['user_mail.account_id'] = "= '$account_id'";
      $where['user_mail.account_class'] = "= 'CSourcePOP'";
    }

    /* Handle the range of the query */
    if (array_key_exists('range', $options) && $options['range'] != 'all') {
      if (in_array($folder, CUserMailFolder::$types)) {
        switch ($folder) {
          case 'archived':
            $where['user_mail.archived'] = "= '1' ";
            break;
          case 'favorites':
            $where['user_mail.favorite'] = "= '1' ";
            break;
          case 'sentbox':
            $where['user_mail.sent'] = " = '1' ";
            break;
          case 'drafts':
            $where['user_mail.draft'] = "= '1' ";
            break;
          case 'inbox':
          default:
            $where['user_mail.archived'] = "= '0'";
            $where['user_mail.sent'] = "= '0'";
            $where['user_mail.draft'] = "= '0'";
        }

        if ($options['range'] == 'selected') {
          $where['user_mail.folder_id'] = ' IS NULL';
        }
      }
      elseif ($folder instanceof CUserMailFolder) {
        $where['user_mail.folder_id'] = $options['range'] == 'selected' ? " = $folder->_id"
          : CSQLDataSource::prepareIn($folder->getDescendantsId());
      }
    }

    $keywords = explode(' ', $query);
    $whereOr = array();
    $ljoin = array();
    if (array_key_exists('subject', $options) && $options['subject']) {
      foreach ($keywords as $keyword) {
        if ($keyword != '') {
          $whereOr[] = "user_mail.subject LIKE '%$keyword%'";
        }
      }
    }
    if (array_key_exists('from', $options) && $options['from']) {
      foreach ($keywords as $keyword) {
        if ($keyword != '') {
          $whereOr[] = "user_mail.from LIKE '%$keyword%'";
        }
      }
    }
    if (array_key_exists('to', $options) && $options['to']) {
      foreach ($keywords as $keyword) {
        if ($keyword != '') {
          $whereOr[] = "user_mail.to LIKE '%$keyword%'";
          $whereOr[] = "user_mail.cc LIKE '%$keyword%'";
          $whereOr[] = "user_mail.bcc LIKE '%$keyword%'";
        }
      }
    }
    if (array_key_exists('body', $options) && $options['body']) {
      $ljoin['content_any'] = 'content_any.content_id = user_mail.text_plain_id';
      foreach ($keywords as $keyword) {
        if ($keyword != '') {
          $whereOr[] = "content_any.content LIKE '%$keyword%'";
        }
      }
    }

    if (count($whereOr)) {
      $where[] = implode(' OR ', $whereOr);
    }

    $order = "date_inbox DESC";

    $mail = new self;
    $count = $mail->countList($where, null, $ljoin);
    $mails = $mail->loadList($where, $order, $limit, null, $ljoin);

    return array('count' => $count, 'mails' => $mails);
  }

  /**
   * Purify a HTML string without deleting the embedded image
   *
   * @param string $html The HTML code to purify
   *
   * @return string
   */
  public static function purifyHTML($html) {
    $matches = array();
    $embedded_images = array();

    /* Correction of the br tags for passing the validation */
    $html = str_replace('<br>', '<br/>', $html);
    /* We replace the img tags by div tags,
     * because HTMLPurifier remove the img tag of the embedded images
     */
    if (preg_match_all('#<img[^>]*>#i', $html, $matches)) {
      foreach ($matches[0] as $_key => $_img) {
        $embedded_images[$_key] = $_img;
        /* We close the unclosed img tags */
        if (strpos($_img, '/>') === false) {
          $embedded_images[$_key] = str_replace('>', '/>', $_img);
        }
        $html = str_replace($_img, "<div class=\"image-$_key\"></div>", $html);
      }
    }
    $html = CMbString::purifyHTML($html);

    $search = array();
    /* The div tags are  replaced by the img tags*/
    foreach ($embedded_images as $index => $img) {
      $search[$index] = "<div class=\"image-$index\"></div>";
    }
    return str_replace($search, $embedded_images, $html);
  }

  /**
   * Return the CSourceSMTP linked to the user of the given account
   *
   * @param int $account_id The id of the account (CSourcePOP)
   *
   * @return CSourceSMTP
   */
  public static function getSMTPSourceFor($account_id) {
    $account = CSourcePOP::loadFromGuid("CSourcePOP-{$account_id}");
    if ($account->object_class == 'CMediusers') {
        $name = "mediuser-{$account->object_id}";
    }
    else {
      $name = 'mediuser-' . CMediusers::get()->_id;
    }

    if (strpos($account->name, 'apicrypt') !== false) {
      $name .= '-apicrypt';
    }

    return CExchangeSource::get($name, CSourceSMTP::TYPE);
  }
}
