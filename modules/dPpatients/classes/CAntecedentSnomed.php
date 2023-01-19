<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;
use Ox\Mediboard\Snomed\CSnomed;

/**
 * SNOMED code related to a antecedent
 */
class CAntecedentSnomed extends CMbObject {
  /** @var integer Primary key */
  public $antecedent_snomed_id;

  // DB Fields
  public $antecedent_id;
  public $code;

  /** @var CAntecedent */
  public $_ref_antecedent;
  /** @var CSnomed */
  public $_ref_snomed;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "antecedent_snomed";
    $spec->key   = "antecedent_snomed_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props["antecedent_id"] = "ref class|CAntecedent back|atcd_snomed";
    $props["code"]          = "str notNull";

    return $props;
  }

  /**
   * Load antecedent
   *
   * @return CAntecedent
   */
  function loadRefAntecedent() {
    return $this->_ref_antecedent = $this->loadFwdRef("antecedent_id", true);
  }

  /**
   * Load code Snomed for antecedent
   *
   * @return CSnomed
   */
  function loadRefCodeSnomed() {
    $snomed       = new CSnomed();
    $snomed->code = $this->code;
    $snomed->loadMatchingObject();

    return $this->_ref_snomed = $snomed;
  }
}
