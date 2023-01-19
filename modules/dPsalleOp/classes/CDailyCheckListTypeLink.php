<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CMbObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;

/**
 * Link between an object and a list type
 */
class CDailyCheckListTypeLink extends CMbObject {
  public $daily_check_list_type_link_id;

  public $object_class;
  public $object_id;
  public $list_type_id;

  public $_object_guid;

  /** @var CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire|CSSPI */
  public $_ref_object;

  /** @var CDailyCheckListType */
  public $_ref_list_type;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_list_type_link';
    $spec->key   = 'daily_check_list_type_link_id';
    $spec->uniques["object"] = array("object_class", "object_id", "list_type_id");
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['object_class'] = 'enum notNull list|CSalle|CBlocOperatoire|CSSPI default|CSalle';
    $props['object_id']    = 'ref class|CMbObject meta|object_class autocomplete back|check_list_type_links';
    $props['list_type_id'] = 'ref notNull class|CDailyCheckListType back|daily_check_list_type_links';
    $props['_object_guid'] = 'str';
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->loadRefObject()->_view." - ".$this->loadRefListType()->_view;
  }

  /**
   * Load target object
   *
   * @return CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire|CSSPI
   */
  function loadRefObject(){
    return $this->_ref_object = $this->loadFwdRef("object_id", true);
  }

  /**
   * Load list type
   *
   * @return CDailyCheckListType
   */
  function loadRefListType(){
    return $this->_ref_list_type = $this->loadFwdRef("list_type_id", true);
  }
}
