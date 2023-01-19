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
 * Liaison entre les intervention et les libellés
 */
class CLiaisonLibelleInterv extends CMbObject {

  // DB Table key
  public $liaison_libelle_id;

  // DB Fields
  public $libelleop_id;
  public $operation_id;
  public $numero;

  // Object References
  /** @var  COperation $_ref_operation*/
  public $_ref_operation;
  /** @var  CLibelleOp $_ref_libelle*/
  public $_ref_libelle;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'liaison_libelle';
    $spec->key   = 'liaison_libelle_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["libelleop_id"]  = "ref notNull class|CLibelleOp autocomplete|nom dependsOn|group_id back|liaison_interv";
    $props["operation_id"]  = "ref notNull class|COperation back|liaison_libelle";
    $props["numero"]        = "num min|1 default|1";
    return $props;
  }

  /**
   * Chargement de l'intervention
   *
   * @return COperation
   */
  function loadRefOperation() {
    return $this->_ref_operation = $this->loadFwdRef("operation_id", true);
  }

  /**
   * Chargement du libellé
   *
   * @return CLibelleOp
   */
  function loadRefLibelle() {
    return $this->_ref_libelle = $this->loadFwdRef("libelleop_id", true);
  }
}