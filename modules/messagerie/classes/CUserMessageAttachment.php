<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbPath;
use Ox\Mediboard\Files\CFile;

/**
 * Represents a file attachment to a user message
 */
class CUserMessageAttachment extends CMbObject {
  /** @var integer Primary key */
  public $user_message_attachment_id;

  /** @var int The id of the linked user message */
  public $user_message_id;

  /** @var int The id of the linked CFile */
  public $file_id;

  /** @var CUserMessage The CUserMessage object */
  public $_ref_message;

  /** @var CFile The linked file */
  public $_ref_file;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "user_message_attachments";
    $spec->key   = "user_message_attachment_id";

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['user_message_id'] = 'ref class|CUserMessage notNull cascade back|attachments';
    $props['file_id']         = 'ref class|CFile back|user_message_attachements';

    return $props;
  }

  /**
   * @see parent::delete()
   */
  public function delete() {
    $this->completeField('file_id');
    $this->loadRefFile();

    if ($msg = $this->_ref_file->delete()) {
      return $msg;
    }

    return parent::delete();
  }

  /**
   * Load the CUserMessage object and returns it
   *
   * @return CUserMessage
   */
  public function loadRefMessage() {
    return $this->_ref_message = $this->loadFwdRef('user_message_id');
  }

  /**
   * Load the CFile object and returns it
   *
   * @return CFile
   */
  public function loadRefFile() {
    return $this->_ref_file = $this->loadFwdRef('file_id');
  }

  /**
   * Create the CFile from an uploaded file
   *
   * @param array $path   The path of the file (The name of the tmp file if the file was uploaded)
   * @param bool  $upload Whether or not the given file is an uploaded file
   * @param bool  $copy   Whether or not you need to copy the file
   *
   * @return null|string
   */
  public function setFile($path, $upload = false, $copy = false) {
    $file = new CFile();

    $file->setObject($this);
    $file->author_id = CAppUI::$user->_id;
    $file->file_name = $path['name'];
    $file->file_date = CMbDT::dateTime();
    $file->fillFields();
    $file->updateFormFields();
    $file->doc_size = $path['size'];
    $file->file_type = CMbPath::guessMimeType($file->file_name);

    $file->setMoveFrom($path['tmp_name'], $upload, $copy);

    $msg = $file->store();

    $this->file_id = $file->_id;
    return $msg;
  }
}
