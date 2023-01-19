<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbObject;

/**
 * Examen Score Met (echelle de Dukes)
 */
class CExamMet extends CMbObject {
  public $exammet_id;

  // DB References
  public $consultation_anesth_id;

  // DB fields
  public $aptitude_physique;

  // Form Fields
  public $_score_met = 0;

  /** @var CConsultAnesth */
  public $_ref_consultation_anesth;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'exammet';
    $spec->key   = 'exammet_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["consultation_anesth_id"] = "ref notNull class|CConsultAnesth back|score_met cascade";
    $props["aptitude_physique"]      = "enum list|0|1|4|7|10 show|0";

    $props["_score_met"] = "num show|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_score_met = $this->aptitude_physique;
  }

  /**
   * Load the anesthesia consultation
   *
   * @return CConsultAnesth
   * @throws Exception
   */
  function loadRefConsultAnesth() {
    return $this->_ref_consultation_anesth = $this->loadFwdRef("consultation_anesth_id", true);
  }
}
