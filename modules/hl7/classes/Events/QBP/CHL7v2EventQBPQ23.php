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
 * Class CHL7v2EventQBPQ23
 * Q23 - Get corresponding identifiers
 */
class CHL7v2EventQBPQ23 extends CHL7v2EventQBP implements CHL7EventQBPQ23 {

  /** @var string */
  public $code        = "Q23";


  /** @var string */
  public $struct_code = "Q21";

  /**
   * Construct
   *
   * @return CHL7v2EventQBPQ23
   */
  function __construct() {
    parent::__construct();

    $this->profil = "PIX";
  }

  /**
   * Build Q23 event
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
  }
}