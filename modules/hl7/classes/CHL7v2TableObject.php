<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

/**
 * Class CHL7v2TableObject 
 * HL7 Table
 */
class CHL7v2TableObject extends CStoredObject {
  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->dsn         = 'hl7v2';
    $spec->incremented = 0;
    return $spec;
  }
}