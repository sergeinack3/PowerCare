<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\QBP;

use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2EventPDQQ22
 * Q22 - Find Candidates
 */
class CHL7v2EventQBPQ22 extends CHL7v2EventQBP implements CHL7EventQBPQ22 {

  /** @var string */
  public $code        = "Q22";


  /** @var string */
  public $struct_code = "Q21";

  /**
   * Construct
   *
   * @return CHL7v2EventQBPQ22
   */
  function __construct() {
    parent::__construct();

    $this->profil = "PDQ";
  }

  /**
   * Build Q22 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($patient) {
    parent::build($patient);

    // QPD
    $this->addQPD($patient);

    // RCP
    $this->addRCP($patient);

    // DSC
    if (isset($patient->_pointer)) {
      $this->addDSC($patient);
    }
  }
}