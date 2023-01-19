<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

class CCommandeMaterielOp extends CMbObject {
  // DB Table key
  public $commande_materiel_id;

  // DB Fields
  public $operation_id;
  public $etat;
  public $date;
  public $commentaire;
  public $type;

  /** @var COperation */
  public $_ref_operation;
  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = 'operation_commande';
    $spec->key    = 'commande_materiel_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["operation_id"]= "ref notNull class|COperation seekable back|commande_op";
    $props["date"]        = "date";
    $props["etat"]        = "enum notNull list|a_commander|modify|commandee|recue|a_annuler|annulee default|a_commander";
    $props["commentaire"] = "text";
    $props["type"]        = "enum notNull list|bloc|pharmacie default|bloc";
    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("operation_id");

    if ($this->_id && $this->etat != "a_commander") {
      if (CMediusers::get()->_id == $this->loadRefOperation()->chir_id) {
        $this->etat = "modify";
      }
    }

    // Standard storage
    if ($msg = parent::store()) {
      return $msg;
    }
    $this->loadRefOperation();
    $commande_mat = $this->type == "bloc" ? "commande_mat" : "commande_mat_pharma";
    if ($this->_ref_operation->$commande_mat && in_array($this->etat, array("a_commander", "a_annuler", "annulee"))) {
      $this->_ref_operation->$commande_mat = 0;
      if ($msg = $this->_ref_operation->store(false)) {
        return $msg;
      }
    }
    elseif (!$this->_ref_operation->$commande_mat && !in_array($this->etat, array("a_commander", "a_annuler", "annulee"))) {
      $this->_ref_operation->$commande_mat = 1;
      if ($msg = $this->_ref_operation->store(false)) {
        return $msg;
      }
    }
    return null;
  }

  /**
   * @see parent::updateFormFields()
   */
  function loadView() {
    parent::loadView();
    $this->_view = "Commande du ". CMbDT::format($this->date, CAppUI::conf("date"));
    $this->_view .= " pour ". $this->loadRefOperation()->loadRefPatient();
  }


  /**
   * Changement de l'état de la commande en : à annuler
   *
   * @return void|string
   */
  function cancelledOp() {
    $this->etat = 'a_annuler';
    if ($msg = $this->store()) {
      return $msg;
    }
  }

  /**
   * Changement de l'état de la commande en : à annuler
   *
   * @param string $materiel Matériel de l'intervention
   *
   * @return void|string
   */
  function modifiedOp($materiel) {
    $this->etat = $materiel ? 'modify' : 'annulee';
    if ($msg = $this->store()) {
      return $msg;
    }
  }

  /**
   * Chargement de l'intervention
   *
   * @param bool $cache Utilisation du cache
   *
   * @return COperation
   */
  function loadRefOperation($cache = true) {
    return $this->_ref_operation = $this->loadFwdRef("operation_id", $cache);
  }

}
