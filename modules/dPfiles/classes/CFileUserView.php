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

/**
 * Description
 */
class CFileUserView extends CStoredObject {
  /**
   * @var integer Primary key
   */
  public $view_id;

  public $user_id;
  public $file_id;
  public $read_datetime;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "files_user_view";
    $spec->key    = "view_id";
    return $spec;  
  }

  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["user_id"] = "ref class|CUser notNull back|files_user_view";
    $props["file_id"] = "ref class|CFile notNull back|file_read_status";
    $props["read_datetime"] = "dateTime notNull";
    return $props;
  }
}
