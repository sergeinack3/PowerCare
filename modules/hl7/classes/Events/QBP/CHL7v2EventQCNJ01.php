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
   * J01 - PDQ Cancel Query
   */
class CHL7v2EventQCNJ01 extends CHL7v2EventQCN implements CHL7EventQCNJ01 {

  /** @var string */
  public $code        = "J01";


  /** @var string */
  public $struct_code = "J01";

  /**
   * Build J01 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($patient) {
    parent::build($patient);

    // QID
    $this->addQID($patient);
  }
}