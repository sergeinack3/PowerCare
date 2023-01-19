<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;
use Ox\Core\CStoredObject;

/**
 * The CSecondaryFunction Class
 */
class CSecondaryFunction extends CStoredObject {
  // DB Table key
  public $secondary_function_id;

  // DB References
  public $function_id;
  public $user_id;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CMediusers */
  public $_ref_user;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'secondary_function';
    $spec->key   = 'secondary_function_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["function_id"] = "ref notNull class|CFunctions back|secondary_functions";
    $specs["user_id"]     = "ref notNull class|CMediusers cascade back|secondary_functions";
    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefFunction();
    $this->loadRefUser();
    $this->_view = $this->_ref_user->_view." - ".$this->_ref_function->_view;
    $this->_shortview = $this->_ref_user->_shortview." - ".$this->_ref_function->_shortview;
  }

  /**
   * @see parent::loadRefsFwd()
   * @deprecated
   */
  function loadRefsFwd() {
    $this->loadRefFunction();
    $this->loadRefUser();
  }

  /**
   * Load function
   *
   * @return CFunctions
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

  /**
   * Load mediuser
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }
}
