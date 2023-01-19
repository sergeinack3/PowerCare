<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2EventADTA31_FRA
 * A31 - Add person information
 */
class CHL7v2EventADTA31_FRA extends CHL7v2EventADTA31 {
  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return CHL7v2EventADTA31_FRA
   */
  function __construct($i18n = "FRA") {
    parent::__construct($i18n);
  }

  /**
   * Build i18n segements
   *
   * @param CPatient $patient Person
   *
   * @see parent::buildI18nSegments()
   *
   * @return void
   */
  function buildI18nSegments($patient) {
    // Complément démographique
    $this->addZFD($patient);

    if ($this->_receiver->_configs["send_insurance"]) {
      // Insurance
      $this->addIN1($patient);

      // Insurance (Additional Information)
      $this->addIN2($patient);
    }
  }
}