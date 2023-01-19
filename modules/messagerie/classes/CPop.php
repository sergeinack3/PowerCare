<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Mediboard\System\CSourcePOP;
use stdClass;

/**
 * Description
 */
class CPop implements IShortNameAutoloadable {

  public $source;

  public $_mailbox; //ressource id
  public $_server;
  public $_mailbox_info;
  public $_mailbox_overview = [];
  public $_mailbox_filtered = [];
  public $_mailbox_notfound = [];
  public $_mailbox_archived = [];

  public $_is_open = false;

  public $_parts = array();

  public $content = array (
    "text" => array(
      "plain"       => null,
      "html"        => null,
      "is_apicrypt" => null
    ),
    "attachments" => array()
  );


  /**
   * constructor
   *
   * @param CSourcePOP $source Source POP
   */
  function __construct($source) {
    //stock the source
    $this->source = $source;

    if (!function_exists("imap_open")) {
      CModelObject::error("FE-IMAP-support-not-available");
    }

    //initialise open TIMEOUT
    imap_timeout(1, $this->source->timeout);


    //lets create the string for stream
    $type = ($this->source->type == "pop3")?"/".$this->source->type:"";
    $ssl  = ($this->source->auth_ssl == "SSL/TLS")?"/ssl/novalidate-cert":"/notls";
    $port = ($this->source->port) ? ":".$this->source->port: "";
    $extension = ($this->source->extension) ? $this->source->extension : "" ;
    return $this->_server = "{".$this->source->host.$port.$type.$ssl."}".$extension;
  }

  /**
   * cleanup temporary elements
   *
   * @return void
   */
  function cleanTemp() {
    $this->content = array (
      "text" => array(
        "plain"       => null,
        "html"        => null,
        "is_apicrypt" => null
      ),
      "attachments" => array()
    );
  }

  /**
   * Open the remote mailbox
   *
   * @param string|null $extension Extension of the serveur to open (/Inbox, ...)
   *
   * @return int|bool
   */
  function open($extension = null) {
    $port = ($this->source->port) ? ":".$this->source->port : "";
    $protocole = ($this->source->auth_ssl) ? "https://" : "http://" ;
    $url = $protocole.$this->source->host.$port;

    if (!isset($this->_server)) {
      CAppUI::stepAjax("CPop-error-notInitiated", UI_MSG_ERROR);
      return false;
    }

    $password = $this->source->getPassword();
    $server = $this->_server.$extension;
    $this->_mailbox = @imap_open($server, $this->source->user, $password, 0, 0);
    //avoid errors reporting
    imap_errors();
    imap_alerts();
    if ($this->_mailbox === false ) {
      //CModelObject::warning("IMAP-warning-configuration-unreashable", $this->source->object_class, $this->source->object_id);
      return false;
    }

    $this->_is_open = true;

    return $this->_mailbox;
  }

  /**
   * @return array
   */
  function getOverview(): array {
    if (empty($this->_mailbox_info) || !property_exists($this->_mailbox_info, 'Nmsgs') || $this->_mailbox_info->Nmsgs === 0) {
      return [];
    }
    return $this->_mailbox_overview = imap_fetch_overview(
      $this->_mailbox,
      "1:{$this->_mailbox_info->Nmsgs}",
      0
    );
  }

  /**
   * return the mailbox check : new msgs
   *
   * @return object
   */
  function check() {
    $this->_mailbox_info = imap_check($this->_mailbox);

    $this->getOverview();
    return $this->_mailbox_info;
  }

  /**
   * search for a mail using specific string
   *
   * @param string $string  search string
   * @param bool   $uid     use uid of mails
   * @param bool   $reverse reverse order
   *
   * @return array
   */
  function search($string,$uid=true, $reverse=true) {
    if (!is_string($string)) {
      CAppUI::stepAjax("CPop-error-search-notString", UI_MSG_ERROR);
    }
    if ($uid) {
      $results = imap_search($this->_mailbox, $string, SE_UID);
    }
    else {
      $results = imap_search($this->_mailbox, $string);
    }

    return $results;
  }

  /**
   * @param string $subject
   * @param bool   $uid
   * @param string $from
   * @param string $to
   *
   * @description Function that filters emails from a mailbox depending on entry parameters
   *
   * @return array|bool
   */
  public function filter($subject = '', $uid = true, $from = '', $to = '') {
    if (empty($to)) {
      $to = $this->source->user;
    }

    $request = '';
    if (!empty($subject) && is_string($subject)) {
      $request .= ' SUBJECT "'.$subject.'"';
    }
    if (!empty($from) && filter_var($from, FILTER_VALIDATE_EMAIL)) {
      $request .= ' FROM "'.$from.'"';
    }
    if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
      $request .= ' TO "'.$to.'"';
    }

    if (!empty($request)) {
      $filtered_ids = imap_search(
        $this->_mailbox,
        $request,
        $uid ? SE_UID : false
      );

      $filtered_emails = [];
      $archived_emails = [];

      if (!empty($filtered_ids)) {
        foreach ($this->_mailbox_overview as $email) {
          if (in_array($email->uid, $filtered_ids)) {
            $filtered_emails[] = $email;
          } else {
            $archived_emails[] = $email;
          }
        }
      }

      $this->_mailbox_archived = $archived_emails;

      return $this->_mailbox_filtered = $filtered_emails;
    }
    return false;
  }

  /**
   * @param string $pattern
   *
   * @return array
   */
  function getMailboxesList(string $pattern = '*') {
    return imap_list($this->_mailbox, $this->_server, $pattern);
  }

  /**
   * @param string $name
   * @param string $extension
   * @param bool   $server
   *
   * @return string
   */
  function getMailboxName(string $name, string $extension = 'INBOX', bool $server = true) {
    return ($server ? $this->_server : '').$extension.'.'.$name;
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  function createMailbox(string $name) {
    if (@imap_createmailbox($this->_mailbox, imap_utf7_encode($this->getMailboxName($name)))) {
      return true;
    } else {
      echo("Impossible de créer une nouvelle boîte aux lettres : " . implode("<br />\n", imap_errors()) . "<br />\n");
      return false;
    }
  }

  /**
   * Return the content type of the given mail
   *
   * @param int $id The id of the mail
   *
   * @return string
   */
  public function checkSMIME(int $id) {
    $structure = $this->structure($id);

    return $structure->ifsubtype && isset($structure->subtype) && $structure->subtype == 'PKCS7-MIME';
  }

  /**
   * Get the raw content of the mail
   *
   * @param integer $id The mail id
   *
   * @return string
   */
  public function getEncryptedMail($id) {
    $mime = imap_fetchheader($this->_mailbox, $id, FT_UID);
    $body = imap_body($this->_mailbox, $id, FT_UID);

    return $mime . "\r\n" . $body;
  }

  /**
   * Return the MIME headers of the given mail
   *
   * @param integer $id The mail id
   *
   * @return string
   */
  public function mimeHeaders($id) {
    $mimeheaders = imap_fetchmime($this->_mailbox, $id, 0, FT_UID);

    return $mimeheaders;
  }

  /**
   * get the header of the mail
   *
   * @param int $id mail id
   *
   * @return array|stdClass
   */
  function header($id) {
    $header = imap_headerinfo($this->_mailbox, imap_msgno($this->_mailbox, $id));
    $header->uid = $id;

    return $header;
  }

  /**
   * get informations of an email (UID)
   *
   * @param int $id message number (msgno ! not uid)
   *
   * @return object
   */
  function infos($id) {
    return imap_headerinfo($this->_mailbox, $id, FT_UID);
  }

  /**
   * get the structure of the mail (parts)
   *
   * @param int $id uid of the mail
   *
   * @return object
   */
  function structure($id) {
    return imap_fetchstructure($this->_mailbox, $id, FT_UID);
  }

  /**
   * send a flag to the server
   *
   * @param int    $id   test
   * @param string $flag test
   *
   * @return bool
   */
  function setFlag($id, $flag) {
    return imap_setflag_full($this->_mailbox, $id, $flag, ST_UID);
  }


  /**
   * Open a part of an email
   *
   * @param integer $msgId  test
   * @param string  $partId test
   * @param boolean $uid    test
   *
   * @return object $object
   */
  function openPart($msgId,$partId,$uid=true) {
    if ($uid) {
      return imap_fetchbody($this->_mailbox, $msgId, $partId, FT_UID | FT_PEEK);
    }
    return imap_fetchbody($this->_mailbox, $msgId, $partId, FT_PEEK);
  }

  /**
   * Return the raw body content
   *
   * @param integer $mail_id The mail id
   *
   * @return string
   */
  function body($mail_id) {
    return imap_body($this->_mailbox, $mail_id, FT_UID);
  }

  /**
   * Get the body of the mail : HTML & plain text if available!
   *
   * @param int  $mail_id     test
   * @param bool $structure   test
   * @param bool $part_number test
   * @param bool $only_text   test
   *
   * @return array
   */
  function getFullBody($mail_id, $structure = false, $part_number = false, $only_text = false) {

    if (!$structure) {
      $structure = $this->structure($mail_id);
    }

    if ($structure) {
      if (!isset($structure->parts) && !$part_number) {  //plain text only, no recursive
        $part_number = "1";
      }
      if (!$part_number) {
        $part_number = "0";
      }

      switch ($structure->type) {
        case 0: //text or html
          /* Checks if the part or subpart has a filename to detect if it is an attachment or not */
          if ($structure->subtype == "PLAIN") {
              $is_attachment = false;
              if (isset($structure->parameters) && is_array($structure->parameters)) {
                  foreach ($structure->parameters as $parameter) {
                      if (in_array(strtolower($parameter->attribute), ['name', 'filename'])) {
                          $is_attachment = true;
                      }
                  }
              }

              if (!$is_attachment) {
                  $this->content["text"]["plain"] = self::decodeMail(
                      $structure->encoding,
                      self::openPart($mail_id, $part_number),
                      $structure
                  );
                  if (
                      stripos($this->content["text"]["plain"], '$APICRYPT') !== false
                      || stripos($this->content["text"]["plain"], '****FINFICHIER****') !== false
                  ) {
                      $this->content["text"]["is_apicrypt"] = "plain";
                  }
              }
          }

          if ($structure->subtype == "HTML") {
            $this->content["text"]["html"] = self::decodeMail($structure->encoding, self::openPart($mail_id, $part_number), $structure);
            if (
              stripos($this->content["text"]["plain"], '$APICRYPT') !== false
              || stripos($this->content["text"]["plain"], '****FINFICHIER****') !== false
            ) {
              $this->content["text"]["is_apicrypt"] = "html";
            }
          }

          break;
        case 1: //multipart, alternatived
          foreach ($structure->parts as $index => $sub_structure) {
            if ($part_number) {
              $prefix = $part_number.'.';
            }
            else {
              $prefix = null;
            }
            self::getFullBody($mail_id, $sub_structure, $prefix . ($index + 1));
          }
          break;

        case 2:     //message
        case 3:     //application
        case 4:     //audio
        case 5:     //images
        case 6:     //video
        default:    //other
          if ($only_text) {
            $attach  = new CMailAttachments();
            $attach->loadFromHeader($structure);
            if ($attach->name) {
              $attach->loadContentFromPop($this->openPart($mail_id, $part_number));

              //inline attachments
              if ($attach->id && $attach->subtype != "SVG+XML") {
                $id= 'cid:'.str_replace(array("<",">"), array("",""), $attach->id);
                $url = "data:image/$attach->subtype|strtolower;base64,".$attach->_content;
                $this->content["text"]["html"] = str_replace($id, $url, $this->content["text"]["html"]);
              }
              else {  //attachments below
                $this->content["attachments"][] = $attach;
              }
            }
          }
      }
    }
    return $this->content;
  }

  /**
   * Get the attachments of a mail_id
   *
   * @param int  $mail_id     id of the mail (warning, UID and not ID)
   * @param bool $structure   structure
   * @param bool $part_number part number
   * @param bool $part_temp   part for get the part later
   *
   * @return CMailAttachments[]
   */
  function getListAttachments($mail_id, $structure = false, $part_number = false, $part_temp=false) {

    if (!$structure) {
      $structure = $this->structure($mail_id);
    }

    if ($structure) {
      if (!isset($structure->parts) && !$part_number) {  //plain text only, no recursive
        $part_number = "1";
      }
      if (!$part_number) {
        $part_number = "0";
      }

      switch ($structure->type) {
        //multipart, alternatived
        case 1:
          foreach ($structure->parts as $index => $sub_structure) {
            if ($part_number !== false) {
              $prefix = $part_number.'.';
            }
            else {
              $prefix = null;
            }

            if ($part_temp !== false) {
              $prefix_temp = $part_temp.'.';
            }
            else {
              $prefix_temp = null;
            }

            self::getListAttachments($mail_id, $sub_structure, $prefix . ($index + 1), $prefix_temp.$index);
          }
          break;

        // text
        case 0:
        case 2:     //message
        case 3:     //application
        case 4:     //audio
        case 5:     //images
        case 6:     //video
          $attach = new CMailAttachments();
          $attach->loadFromHeader($structure);
          if ($attach->name) {
            $attach->part = $part_temp;
            //inline attachments
            $this->content["attachments"][] = $attach;
          }
          break;
        default:    //other
      }
    }
    return $this->content["attachments"];
  }

  /** TOOLS **/

  /**
   * get the right decoding string from mail structure
   *
   * @param int         $encoding  encoding number (from structure)
   * @param string      $text      the text to decode
   * @param object|null $structure an mail structure for additional decoding
   *
   * @return string
   */
  static function decodeMail($encoding, $text, $structure=null) {
    $retour = null;
    switch ($encoding) {
      /* 0 : 7 bit / 1 : 8 bit / 2 ; binary / 5 : other  => default  */
      case(3):  //base64
        $retour = imap_base64($text);
        break;

      case(4):
        $retour = imap_qprint($text);
        break;

      default:
        $retour = $text;
        break;
    }

    //Hack for bad defined encoding
    if (!empty($structure->parameters) && is_array($structure->parameters)) {
      for ($k = 0, $l = count($structure->parameters); $k < $l; $k++) {
        $attribute = $structure->parameters[$k];
        if ($attribute->attribute == 'CHARSET' && strtoupper($attribute->value) == 'UTF-8') {
          return utf8_decode($retour);
        }
      }
    }
    return $retour;
  }

  /**
   * Decode an encoded string (in base64 or quoted printable)
   *
   * @param string $string The string to decode
   *
   * @return string
   */
  public static function decodeString($string) {
    $str = '';

    if (strpos($string, '?=')) {
      foreach (explode('?= =?', $string) as $_part) {
        $_part = str_replace(array('=?', '?='), '', $_part);//substr($_part, strpos($_part, '=?') + 2);

        $_parts = explode('?', $_part);

        if (in_array($_parts[0], mb_list_encodings())) {
          $charset  = $_parts[0];
          $encoding = $_parts[1];

          switch ($encoding) {
            case 'B':
              $_str = base64_decode($_parts[2]);
              break;
            case 'Q':
              $_str = quoted_printable_decode($_parts[2]);
              break;
            default:
              $_str = $_parts[2];
          }

          $str .= iconv($charset, 'ISO-8859-1//TRANSLIT', CMbString::normalizeUtf8($_str));
        }
      }
    }
    else {
      $str = $string;
    }

    return $str;
  }

  /**
   * Mark the mail with the given uid to be deleted from the current mailbox
   *
   * @param int $uid
   *
   * @return bool
   */
  public function deleteMail(int $uid): bool {
    return imap_delete($this->_mailbox, $uid, FT_UID);
  }

  /**
   * Deletes all the messages marked for deletion
   *
   * @return bool
   */
  public function expunge(): bool {
    return imap_expunge($this->_mailbox);
  }

  /**
   * check if imap_lib exist
   *
   * @return null
   */
  static function checkImapLib() {
    if (!function_exists("imap_open")) {
      CMbObject::error("no-imap-lib");
      CAppUI::stepAjax("no-imap-lib", UI_MSG_ERROR);
      return false;
    }
    return true;
  }

  /**
   * close the stream
   *
   * @return bool
   */
  function close() {
    if ($this->_is_open) {
      return imap_close($this->_mailbox);
    }
    return false;
  }
}
