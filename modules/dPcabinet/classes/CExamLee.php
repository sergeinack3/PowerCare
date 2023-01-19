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
 * Examen Score Lee
 */
class CExamLee extends CMbObject {
  public $examlee_id;

  // DB References
  public $consultation_anesth_id;

  // DB fields
  public $chirurgie_risque;
  public $coronaropathie ;
  public $insuffisance_cardiaque;
  public $antecedent_avc;
  public $diabete;                // all type
  public $clairance_creatinine;   // < 60 ml/min

  // Form Fields
  public $_score_lee = 0;

  /** @var CConsultAnesth */
  public $_ref_consultation_anesth;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'examlee';
    $spec->key   = 'examlee_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["consultation_anesth_id"] = "ref notNull class|CConsultAnesth back|score_lee cascade";
    $props["chirurgie_risque"]       = "bool show|0";
    $props["coronaropathie"]         = "bool show|0";
    $props["insuffisance_cardiaque"] = "bool show|0";
    $props["antecedent_avc"]         = "bool show|0";
    $props["diabete"]                = "bool show|0";
    $props["clairance_creatinine"]   = "bool show|0";

    $props["_score_lee"] = "num show|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_score_lee = $this->chirurgie_risque + $this->coronaropathie + $this->insuffisance_cardiaque +
                        $this->antecedent_avc + $this->diabete + $this->clairance_creatinine;
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
