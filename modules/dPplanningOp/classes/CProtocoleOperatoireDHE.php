<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CStoredObject;

/**
 * Lien entre un protocole opératoire et un protocole de DHE
 */
class CProtocoleOperatoireDHE extends CStoredObject {
  /** @var integer Primary key */
  public $protocole_operatoire_dhe_id;

  // DB fields
  public $protocole_operatoire_id;
  public $protocole_id;

  // Form fields
  /** @var CProtocoleOperatoire */
  public $_ref_protocole_operatoire;

  /** @var CProtocole */
  public $_ref_protocole;

  /**
   * @inheritdoc
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "protocole_operatoire_dhe";
    $spec->key   = "protocole_operatoire_dhe_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  public function getProps() {
    $props = parent::getProps();
    $props["protocole_operatoire_id"] = "ref class|CProtocoleOperatoire back|protocoles";
    $props["protocole_id"]            = "ref class|CProtocole back|links_protocoles_op";
    return $props;
  }

  /**
   * Charge le protocole opératoire
   *
   * @return CProtocoleOperatoire
   */
  public function loadRefProtocoleOperatoire(): CProtocoleOperatoire {
    return $this->_ref_protocole_operatoire = $this->loadFwdRef("protocole_operatoire_id", true);
  }

  /**
   * Charge le protocole de DHE
   *
   * @return CProtocole
   */
  public function loadRefProtocole(): CProtocole {
    return $this->_ref_protocole = $this->loadFwdRef("protocole_id", true);
  }
}
