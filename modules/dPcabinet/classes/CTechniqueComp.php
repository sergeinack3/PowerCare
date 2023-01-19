<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;

/**
 * Les techniques complémentaires permettent de préciser les gestes d'anesthésie
 */
class CTechniqueComp extends CMbObject {
  // DB Table key
  public $technique_id;

  // DB References
  public $consultation_anesth_id;

  // DB fields
  public $technique;

  // References
  /** @var CConsultAnesth */
  public $_ref_consult_anesth;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'techniques_anesth';
    $spec->key   = 'technique_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["consultation_anesth_id"] = "ref notNull class|CConsultAnesth cascade back|techniques";
    $props["technique"]              = "text helped";
    return $props;
  }

  /**
   * Charge la consultation préanesthésique associée
   *
   * @return CConsultAnesth
   */
  function loadRefConsultAnesth() {
    return $this->loadFwdRef("consultation_anesth_id", true);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    return $this->loadRefConsultAnesth()->getPerm($permType);
  }
}
