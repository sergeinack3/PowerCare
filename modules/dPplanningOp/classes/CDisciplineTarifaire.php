<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;

/**
 * Table discipline médico-tarifaire
 */
class CDisciplineTarifaire extends CMbObject { 
   // DB Table key
  public $nodess;
  public $description;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->dsn   = 'sae';
    $spec->table = "discipline_tarifaire";
    $spec->key   = "nodess";
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["nodess"]      = "num notNull maxLength|3";
    $props["description"] = "str";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    
    $this->_view      = $this->description;
    $this->_shortview = $this->nodess;
  }
}
