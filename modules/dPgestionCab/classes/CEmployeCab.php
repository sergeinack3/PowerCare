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
 * Employé cabinet
 */
class CEmployeCab extends CMbObject {
  // DB Table key
  public $employecab_id;

  // DB References
  public $function_id;

  // DB Fields
  public $nom;
  public $prenom;
  public $function;
  public $adresse;
  public $cp;
  public $ville;

  /** @var CFunctions */
  public $_ref_function;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'employecab';
    $spec->key   = 'employecab_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["function_id"] = "ref notNull class|CFunctions back|employes";
    $props["nom"]         = "str notNull seekable|begin";
    $props["prenom"]      = "str notNull seekable|begin";
    $props["function"]    = "str notNull";
    $props["adresse"]     = "text confidential";
    $props["ville"]       = "str";
    $props["cp"]          = "str length|5 confidential";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "$this->nom $this->prenom";
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
