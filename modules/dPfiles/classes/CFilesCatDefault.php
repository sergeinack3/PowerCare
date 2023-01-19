<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CFilesCatDefault extends CStoredObject {
  /**
   * @var integer Primary key
   */
  public $files_cat_default_id;

  // DB Fields
  public $file_category_id;
  public $object_class;
  public $owner_class;
  public $owner_id;

  // References
  /** @var CMediusers|CFunctions */
  public $_ref_owner;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "files_cat_default";
    $spec->key    = "files_cat_default_id";
    $spec->uniques["unique"] = array("owner_class", "owner_id", "object_class");
    return $spec;  
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["file_category_id"] = "ref class|CFilesCategory back|default_cats";
    $props["object_class"]     = "str";
    $props["owner_class"]      = "enum list|CMediusers|CFunctions";
    $props["owner_id"]         = "ref class|CMbObject meta|owner_class back|default_files_cat";
    return $props;
  }

  /**
   * @return CMediusers|CFunctions
   */
  function loadRefOwner() {
    return $this->_ref_owner = $this->loadFwdRef("owner_id", true);
  }
}
