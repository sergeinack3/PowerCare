<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\MFN;

use Ox\Core\CMbObject;

/**
 * Class CHL7v2EventMFNM05
 * Transporte des éléments de structure liés à la localisation du patient - HL7
 */
class CHL7v2EventMFNM05 extends CHL7v2EventMFN implements CHL7EventMFNM05 {

  /** @var string */
  public $code = "M05";

  /** @var string */
  public $struct_code = "M05";

  /**
   * Build M05 event
   *
   * @param CMbObject $object object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    parent::build($object);
  }
}