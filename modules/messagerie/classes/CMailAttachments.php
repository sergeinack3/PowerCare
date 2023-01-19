<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Files\CFile;

/**
 * Description : Attachment of an email
 **/
class CMailAttachments extends CMbObject{

  public $user_mail_attachment_id;

  public $mail_id;

  public $type;
  public $encoding;
  public $subtype;
  public $id;
  public $bytes;
  public $disposition;
  public $part;
  public $file_id;        //Cfile id if linked

  public $name;
  public $extension;


  /** @var CFile|null $_file */
  public $_file;
  public $_content;     // temp for content of file
  public $_ref_mail;    // for mail ref
  /** @var string The readable size of the file */
  public $_size;

  /** @var CMailPartToFile[] */
  public $_ref_linked_files;


  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'user_mail_attachment';
    $spec->key   = 'user_mail_attachment_id';
    $spec->loggable = false;
    return $spec;
  }

  /**
   *  @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["mail_id"]       = "ref notNull class|CUserMail back|mail_attachments cascade";
    $props["type"]          = "num notNull";
    $props["encoding"]      = "num";
    $props["subtype"]       = "str";
    $props["id"]            = "str";
    $props["bytes"]         = "num";
    $props["disposition"]   = "str";
    $props["part"]          = "str notNull";
    $props["file_id"]       = "ref class|CFile unlink back|mail_attachment";

    $props["name"]          = "str notNull";
    $props["extension"]     = "str notNull";

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function updateFormFields() {
    $this->_size = self::getReadableSize($this->bytes);
  }

  /**
   * @see parent::delete()
   */
  public function delete() {

    $this->loadRefLinkedFiles();
    $this->loadRefsFwd();

    foreach ($this->_ref_linked_files as $_link) {
      if ($msg = $_link->delete()) {
        return $msg;
      }
    }

    $this->loadFiles();
    if ($this->_file->_id && $this->_file->object_class === $this->_class && $this->_file->object_id === $this->_id) {
      $this->file_id = '';
      $this->store();

      if ($msg = $this->_file->delete()) {
        return $msg;
      }
    }

    return parent::delete();
  }

  /**
   * Load MailAttachment from POP Header
   *
   * @param object $header header from source POP
   *
   * @return boolean|CMailAttachments
   */
  function loadFromHeader($header) {
    $this->type = $header->type;
    $this->encoding = $header->encoding;
    if ($header->ifsubtype) {
      $this->subtype = $header->subtype;
    }
    if ($header->ifid) {
      $this->id = $header->id;
    }
    $this->bytes = $header->bytes;
    $this->disposition = 'ATTACHMENT';
    if ($header->ifdisposition) {
      $this->disposition = $header->disposition;
    }
    if ($header->ifdparameters) {
      foreach ($header->dparameters as $_param) {
        if (strtolower($_param->attribute) == "filename") {
          $this->name = CPop::decodeString($_param->value);
          continue;
        }
      }
    }
    if ($header->ifparameters && !$this->name) {
      foreach ($header->parameters as $_param) {
        if (strtolower($_param->attribute) == "name") {
          $this->name = CPop::decodeString($_param->value);
          continue;
        }
      }
    }

    if (!$this->name) {
      return false;
    }

    //extension
    if ($ext = substr(strrchr($this->name, '.'), 1)) {
      $this->extension = $ext;
    }
    $display_lab_results = CAppUI::pref('tamm_display_lab_results');
    if ($this->extension == 'hpm'
        && (!CModule::getActive('oxCabinet') || (CModule::getActive('oxCabinet') && $display_lab_results))
    ) {
      $this->subtype = 'x-hprim-med';
      $this->type = 3;
    }
    elseif (in_array($this->extension, array('hps', 'hpr'))
        && (!CModule::getActive('oxCabinet') || (CModule::getActive('oxCabinet') && $display_lab_results))
    ) {
      $this->subtype = 'x-hprim-sante';
      $this->type = 3;
    }
    /* If the module oxCabinet is active and the user don't have subscribed to the lab results,
     *  we set the attachment type to text/plain */
    elseif (in_array($this->extension, array('hps', 'hpr', 'hpm'))
        && CModule::getActive('oxCabinet') && !$display_lab_results
    ) {
      $this->subtype = 'plain';
      $this->type = 0;
    }
    $this->name = addslashes($this->name);

    return $this;
  }

  /**
   * LoadContent from pop content
   *
   * @param object $content content from pop
   *
   * @return bool
   */
  function loadContentFromPop($content) {
    switch ($this->subtype) {
      case 'SVG+XML':
        return $this->_content = CPop::decodeMail($this->encoding, $content);
        break;

      default:
        return $this->_content = base64_encode(CPop::decodeMail($this->encoding, $content));
        break;
    }
  }

  /**
   * Get the string $this->part++
   *
   * @return string $ret
   */
  function getpartDL() {
    $ret = "";
    $parts = explode(".", $this->part);
    if (count($parts)>1) {
      foreach ($parts as $key=>$_part) {
        $ret.=$_part+1;
        if ($key+1 != count($parts)) {
          $ret.= '.';
        }
      }
    }
    else {
      $ret = $this->part+1;
    }
    return $ret;
  }

  /**
   * Load the forward refs
   *
   * @return void
   */
  function loadRefsFwd() {
    $this->loadFiles();
  }

  /**
   * Load the mail where this attachment is attached
   *
   * @return CUserMail
   */
  function loadMail() {
    return $this->_ref_mail = $this->loadFwdRef("mail_id");
  }

  /**
   * Load files linked to the present attachment
   *
   * @return CFile
   */
  function loadFiles() {
    //a file is already linked and we have the id
    $file = new CFile();
    if ($this->file_id) {
      $file->load($this->file_id);
      $file->loadRefsFwd();         //TODO : fix this
    }
    //so there is a file linked
    else {
      $file->setObject($this);
      $file->loadMatchingObject();
    }
    $file->updateFormFields();


    return $this->_file = $file;
  }

  /**
   * Get attachment mime type
   *
   * @param int    $type      type from 0 to 6
   * @param string $extension extension (png, jpg ...)
   *
   * @return string
   */
  function getType($type,$extension) {
    switch ($type) {
      case 0:
        $string ='text';
        break;

      case 1:
        $string ='multipart';
        break;

      case 2:
        $string = 'message';
        break;

      case 3:
        $string = 'application';
        break;

      case 4:
        $string = 'audio';
        break;

      case 5:
        $string = 'image';
        break;

      case 6:
        $string = 'video';
        break;

      default:
        $string = 'other';
        break;
    }
    return strtolower($string.'/'.$extension);
  }

  /**
   * Get attachment mime type
   *
   * @param string $type File type (style text/plain or image/jpg)
   *
   * @return int
   */
  function getTypeInt($type) {
    switch ($type) {
      case 'text':
        $int = 0;
        break;

      case 'multipart':
        $int = 1;
        break;

      case 'message':
        $int = 2;
        break;

      case 'application':
        $int = 3;
        break;

      case 'audio':
        $int = 4;
        break;

      case 'image':
        $int = 5;
        break;

      case 'video':
        $int = 6;
        break;

      default:
        $int = -1;
        break;
    }
    return $int;
  }

  /**
   * Load the linked files
   *
   * @return CMailPartToFile[]
   */
  public function loadRefLinkedFiles() {
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
   * Return a human readable file size
   *
   * @param int $bytes The size
   * @param int $dec   The number of decimals displayed
   *
   * @return string The human readable file size
   */
  public static function getReadableSize($bytes, $dec = 2) {
    $size   = array('o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo');
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
  }
}
