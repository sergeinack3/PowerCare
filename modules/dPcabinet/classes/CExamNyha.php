<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;

class CExamNyha extends CMbObject {
  // DB Table key
  public $examnyha_id;

  // DB References
  public $consultation_id;

  // DB fields
  public $q1;
  public $q2a;
  public $q2b;
  public $q3a;
  public $q3b;
  public $hesitation;

  /** @var CConsultation */
  public $_ref_consult;

  // Form fields
  public $_classeNyha;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'examnyha';
    $spec->key   = 'examnyha_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["consultation_id"] = "ref notNull class|CConsultation back|examnyha";
    $props["q1"]              = "bool default|none";
    $props["q2a"]             = "bool";
    $props["q2b"]             = "bool";
    $props["q3a"]             = "bool";
    $props["q3b"]             = "bool";
    $props["hesitation"]      = "bool notNull";

    // Derives fields
    $props["_classeNyha"] = "";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_classeNyha = "";

    if ($this->q1 == 1) {
      if ($this->q2a !== null && $this->q2a == 0) {
        $this->_classeNyha = CAppUI::tr('CExamNyha-Class III');
      }
      if ($this->q2a == 1 && $this->q2b !== null && $this->q2b == 1) {
        $this->_classeNyha = CAppUI::tr('CExamNyha-Class I');
      }
      if ($this->q2a == 1 && $this->q2b !== null && $this->q2b == 0) {
        $this->_classeNyha = CAppUI::tr('CExamNyha-Class II');
      }
    }
    elseif ($this->q1 == 0) {
      if ($this->q3a !== null && $this->q3a == 0) {
        $this->_classeNyha = CAppUI::tr('CExamNyha-Class III');
      }
      if ($this->q3a == 1 && $this->q3b !== null && $this->q3b == 1) {
        $this->_classeNyha = CAppUI::tr('CExamNyha-Class III');
      }
      if ($this->q3a == 1 && $this->q3b !== null && $this->q3b == 0) {
        $this->_classeNyha = CAppUI::tr('CExamNyha-Class IV');
      }
    }

    $this->_view = CAppUI::tr('CExamNyha-long')." : $this->_classeNyha";
  }

  /**
   * Charge la consultation associée
   *
   * @return CConsultation
   */
  function loadRefConsult() {
    return $this->_ref_consult = $this->loadFwdRef("consultation_id", true);
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    return $this->loadRefConsult()->getPerm($permType);
  }
}
