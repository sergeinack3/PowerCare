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
 * Class CHL7v2EventQBPK23
 * K23 - Query Response
 */
class CHL7v2EventQBPK23 extends CHL7v2EventQBP implements CHL7EventQBPK23 {

  /** @var string */
  public $code = "K23";

  /**
   * Construct
   *
   * @return CHL7v2EventQBPK23
   */
  function __construct() {
    parent::__construct();

    $this->profil = "PIX";
  }

  /**
   * Build K22 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($patient) {
  }
}