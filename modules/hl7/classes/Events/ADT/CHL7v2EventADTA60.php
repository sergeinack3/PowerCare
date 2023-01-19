<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Mediboard\Patients\CAntecedent;

/**
 * Class CHL7v2EventADTA60
 * A60 - Update allergy information
 */
class CHL7v2EventADTA60 extends CHL7v2EventADT implements CHL7EventADTA60 {
  /** @var string */
  public $code        = "A60";

  /** @var string */
  public $struct_code = "A60";

  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return void
   */
  function __construct($i18n = null) {
    $this->profil = "ADT";

    parent::__construct($i18n);

    $this->transaction = null;
  }

  /**
   * Build A60 event
   *
   * @param CAntecedent $antecedent Antecedent
   *
   * @see parent::build()
   *
   * @return void
   * @throws CHL7v2Exception
   */
  function build($antecedent){
    $patient = $antecedent->_ref_dossier_medical->_ref_object;
    parent::build($patient);

    // Patient Identification
    $this->addPID($patient);

    // Admit
    $this->addPV1();

    // Patient Adverse Reaction Information
    $this->addIAMs($patient, $antecedent);
  }
}