<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\GestionCab;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Mode de paiement
 */
class CModePaiement extends CMbObject {
  // DB Table key
  public $mode_paiement_id;

  // DB Fields
  public $function_id;
  public $nom;

  /** @var CFunctions */
  public $_ref_function;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'mode_paiement';
    $spec->key   = 'mode_paiement_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["function_id"] = "ref class|CFunctions back|paiements";
    $props["nom"]         = "str notNull seekable";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "Mode de paiement '".$this->nom."'";
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->_ref_function = new CFunctions();
    $this->_ref_function->load($this->function_id);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if (!$this->_ref_function) {
      $this->loadRefsFwd();
    }
    return $this->_ref_function->getPerm($permType);
  }
}
