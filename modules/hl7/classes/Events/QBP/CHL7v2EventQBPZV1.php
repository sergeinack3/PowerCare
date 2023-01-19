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
 * Class CHL7v2EventQBPZV1
 * ZV1 - Find Admit Candidates
 */
class CHL7v2EventQBPZV1 extends CHL7v2EventQBP implements CHL7EventQBPZV1 {

  /** @var string */
  public $code        = "ZV1";


  /** @var string */
  public $struct_code = "Q21";

  /**
   * Construct
   *
   * @return CHL7v2EventQBPZV1
   */
  function __construct() {
    parent::__construct();

    $this->profil = "PDQ";
  }

  /**
   * Build ZV1 event
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