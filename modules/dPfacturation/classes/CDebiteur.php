<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CMbObject;

/**
 * Le compte débiteur des règlements
 */
class CDebiteur extends CMbObject {
  // DB Table key
  public $debiteur_id;

  // DB Fields
  public $numero;
  public $nom;
  public $description;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'debiteur';
    $spec->key   = 'debiteur_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["numero"]      = "num notNull";
    $props["nom"]         = "str notNull maxLength|50";
    $props["description"] = "text";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->numero." - ".$this->nom;
  }
}
