<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Core\CMbObject;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTZ99_FRA
 * Z99 - Change admit
 */
class CHL7v2EventADTZ99_FRA  extends CHL7v2EventADTZ99 {
  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return CHL7v2EventADTZ99_FRA
   */
  function __construct($i18n = "FRA") {
    parent::__construct($i18n);
  }

  /**
   * Build i18n segements
   *
   * @param CMbObject $object Object
   *
   * @see parent::buildI18nSegments()
   *
   * @return void nj
   */
  function buildI18nSegments($object) {
    if ($object instanceof CAffectation) {
      /** @var CSejour $sejour */
      $sejour                       = $object->_ref_sejour;
      $sejour->_ref_hl7_affectation = $object;
    }
    else {
      $sejour = $object;
    }

    // Movement segment
    $this->addZBE($sejour);

    if ($this->version == "FRA_2.5" || $this->version == "FRA_2.6" || $this->version == "FRA_2.7") {
      // Situation professionnelle
      $this->addZFA($sejour->_ref_patient);
    }

    // Situation professionnelle
    // Si A01, A04, A05, A14
    $this->addZFP($sejour);
    
    // Compléments sur la rencontre
    // Si A01, A02, A03, A04, A05, A14, A21
    $this->addZFV($sejour);
    
    // Mouvement PMSI
    // Si A01, A02, A03, A04, A05, A14, 
    // Z80, Z81, Z82, Z83, Z84, Z85, Z86, Z87 
    $this->addZFM($sejour);
    
    // Complément démographique
    // Si A01, A04, A05, A14
    $this->addZFD($sejour->_ref_patient);

    if ($this->_receiver->_configs["send_insurance"]) {
      // Insurance
      $this->addIN1($sejour->_ref_patient);

      // Insurance (Additional Information)
      $this->addIN2($sejour->_ref_patient);
    }
  }
}