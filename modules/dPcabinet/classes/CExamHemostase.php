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
 * Class CExamHemostase - Risque Hémorragique
 *
 * @package Ox\Mediboard\Cabinet
 */
class CExamHemostase extends CMbObject {

  // Primary Key
  public $exam_hemostase_id;

  // DB References
  public $consultation_anesth_id;

  /** @var boolean */
  public $coupure_minime;
  public $soin_dentaire;
  public $apres_chirurgie;
  public $hematomes_spontanes;
  public $hemostase_famille;
  public $apres_accouchement;
  public $menometrorragie;

  /** @var int  */
  public $_score_hemostase = 0;

  /** @var CConsultAnesth */
  public $_ref_consultation_anesth;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'exam_hemostase';
    $spec->key   = 'exam_hemostase_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["consultation_anesth_id"] = "ref notNull class|CConsultAnesth back|score_hemostase cascade";
    $props["coupure_minime"]         = "bool show|0";
    $props["soin_dentaire"]          = "bool show|0";
    $props["apres_chirurgie"]        = "bool show|0";
    $props["hematomes_spontanes"]    = "bool show|0";
    $props["hemostase_famille"]      = "bool show|0";
    $props["apres_accouchement"]     = "bool show|0";
    $props["menometrorragie"]        = "bool show|0";

    $props["_score_hemostase"] = "num show|0";

    return $props;
  }

  /**
   * Update form fields and compute score
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_score_hemostase = $this->coupure_minime + $this->soin_dentaire + $this->apres_chirurgie +
      $this->hematomes_spontanes + $this->hemostase_famille + $this->apres_accouchement + $this->menometrorragie;
  }

  /**
   * Charge la consultation d'anesthésie
   *
   * @return \Ox\Core\CStoredObject
   * @throws \Exception
   */
  function loadRefConsultAnesth() {
    return $this->_ref_consultation_anesth = $this->loadFwdRef("consultation_anesth_id", true);
  }
}