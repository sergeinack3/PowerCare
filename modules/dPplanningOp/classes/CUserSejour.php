<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Affectation de mediuser pour un séjour
 */
class CUserSejour extends CMbObject {

  // DB Table key
  public $sejour_affectation_id;

  // DB Fields
  public $sejour_id;
  public $user_id;
  public $debut;
  public $fin;

  // Object References
  /** @var  CSejour $_ref_sejour*/
  public $_ref_sejour;
  /** @var  CMediusers $_ref_user*/
  public $_ref_user;

  public $_debut;
  public $_fin;
  /** @var CUserSejour[] $_affectations*/
  public $_affectations = [];
  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'sejour_affectation';
    $spec->key   = 'sejour_affectation_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["sejour_id"] = "ref notNull class|CSejour back|user_sejour";
    $props["user_id"]   = "ref notNull class|CMediusers back|user_sejour";
    $props["debut"]     = "dateTime";
    $props["fin"]       = "dateTime";
    $props["_debut"]    = "date";
    $props["_fin"]      = "date";
    return $props;
  }

  /**
   * Chargement de l'intervention
   *
   * @return CSejour
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Chargement du libellé
   *
   * @return CMediusers
   */
  function loadRefUser() {
    $this->_ref_user = $this->loadFwdRef("user_id", true);
    $this->_ref_user->loadRefFunction();
    return $this->_ref_user;
  }
} 