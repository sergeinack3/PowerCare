<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Ex class category
 */
class CExClassCategory extends CMbObject implements FormComponentInterface {
  public $ex_class_category_id;

  public $group_id;
  public $title;
  public $description;
  public $color;

  /** @var CExClass[] */
  public $_ref_ex_classes;

  /** @var self[] */
  static $_list_cache = array();

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "ex_class_category";
    $spec->key   = "ex_class_category_id";
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|ex_class_categories";
    $props["title"]       = "str notNull";
    $props["color"]       = "color";
    $props["description"] = "text";
    return $props;
  }

  /**
   * Load ex classes
   *
   * @return CExClass[]
   */
  function loadRefsExClasses() {
    return $this->_ref_ex_classes = $this->loadBackRefs("ex_classes", "name");
  }

  /**
   * @inheritdoc
   */
  function updateFormFields(){
    parent::updateFormFields();

    $this->_view = $this->title;
  }

  /**
   * @inheritdoc
   */
  function store(){
    if (!$this->_id) {
      $this->group_id = CGroups::loadCurrent()->_id;
    }

    return parent::store();
  }
}
