<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA01_FRA
 * A01 - Admit/visit notification
 */
class CHL7v2EventADTA01_FRA extends CHL7v2EventADTA01 {
  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return CHL7v2EventADTA01_FRA
   */
  function __construct($i18n = "FRA") {
    parent::__construct($i18n);
  }

  /**
   * Build i18n segements
   *
   * @param CSejour $sejour Admit
   *
   * @see parent::buildI18nSegments()
   *
   * @return void
   */
  function buildI18nSegments($sejour) {
    // Movement segment
    $this->addZBE($sejour);

    if ($this->version == "FRA_2.5" || $this->version == "FRA_2.6" || $this->version == "FRA_2.7") {
      // Situation professionnelle
      $this->addZFA($sejour->_ref_patient);
    }
    
    // Situation professionnelle
    $this->addZFP($sejour);
    
    // Compl�ments sur la rencontre
    $this->addZFV($sejour);
    
    // Mouvement PMSI
    $this->addZFM($sejour);
    
    // Compl�ment d�mographique
    $this->addZFD($sejour->_ref_patient);

    if ($this->_receiver->_configs["send_insurance"]) {
      // Insurance
      $this->addIN1($sejour->_ref_patient);

      // Insurance (Additional Information)
      $this->addIN2($sejour->_ref_patient);
    }
  }
}