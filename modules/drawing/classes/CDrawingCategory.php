<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Drawing;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CDrawingCategory extends CMbObject {
  public $drawing_category_id;

  public $group_id;
  public $function_id;
  public $user_id;

  public $name;
  public $description;
  public $creation_datetime;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec               = parent::getSpec();
    $spec->table        = "drawing_category";
    $spec->key          = "drawing_category_id";
    $spec->xor["owner"] = array("user_id", "function_id", "group_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["user_id"]     = "ref class|CMediusers purgeable show|1 back|drawing_category_user";
    $props["function_id"] = "ref class|CFunctions purgeable back|drawing_category_function";
    $props["group_id"]    = "ref class|CGroups purgeable back|drawing_category_group";

    $props["name"]              = "str notNull";
    $props["description"]       = "text";
    $props["creation_datetime"] = "dateTime notNull";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->creation_datetime) {
      $this->creation_datetime = CMbDT::dateTime();
    }

    return parent::store();
  }
}
