<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

/**
 * Class CHL7v2EventADTA44_FRA
 * A44 - Move account information - patient account number
 */
class CHL7v2EventADTA44_FRA extends CHL7v2EventADTA44 {
  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return CHL7v2EventADTA44_FRA
   */
  function __construct($i18n = "FRA") {
    parent::__construct($i18n);
  }
}