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
use Ox\Mediboard\Files\CFile;

/**
 * Represent the link between an CMailAttachment and a CFile (who is linked to another object, like a CSejour or a CPatient)
 */
class CMailPartToFile extends CMbObject {
  /** @var integer Primary key */
  public $attachment_to_file_id;

  /** @var integer The part's id */
  public $part_id;

  /** @var  string The part's class (CUserMail or CMailAttachment) */
  public $part_class;

  /** @var integer The CFile's id */
  public $file_id;

  /** @var CMailAttachments|CUserMail The part */
  public $_ref_part;

  /** @var CFile The CFile */
  public $_ref_file;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "mail_part_to_file";
    $spec->key    = "mail_part_to_file_id";
    return $spec;
  }


  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['part_id']     = 'ref meta|part_class notNull back|mail_parts cascade';
    $props['part_class']  = 'enum list|CUserMail|CMailAttachments';
    $props['file_id']     = 'ref class|CFile notNull unlink back|mail_parts_links';

    return $props;
  }

  /**
   * @inheritDoc
   */
  public function delete() {
    $this->loadRefFile();

    if ($this->_ref_file->_id && $this->_ref_file->object_class === $this->_class && $this->_ref_file->object_id === $this->_id) {
      if ($msg = $this->_ref_file->delete()) {
        return $msg;
      }
    }

    return parent::delete();
  }

  /**
   * Load the part
   *
   * @param bool $cache Use the cache or not
   *
   * @return CMailAttachments|CuserMail
   */
  public function loadRefPart($cache = true) {
    if (!$this->_ref_part) {
      $this->_ref_part = $this->loadFwdRef('part_id', $cache);
    }

    return $this->_ref_part;
  }

  /**
   * Load the file
   *
   * @param bool $cache Use the cache or not
   *
   * @return CFile
   */
  public function loadRefFile($cache = true) {
    if (!$this->_ref_file) {
      $this->_ref_file = $this->loadFwdRef('file_id', $cache);
    }

    return $this->_ref_file;
  }

  /**
   * Load the links to the CFile for the given part
   *
   * @param CMailAttachments|CUserMail $part The part
   *
   * @return CMailPartToFile[]
   */
  public static function loadFor($part) {
    $link = new self;

    $link->part_id = $part->_id;
    $link->part_class = $part->_class;

    return $link->loadMatchingList(null, null, 'file_id');
  }
}
