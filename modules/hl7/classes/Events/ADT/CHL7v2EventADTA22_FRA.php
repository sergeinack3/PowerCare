<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA22_FRA
 * A22 - Patient returns from a _leave of absence_
 */
class CHL7v2EventADTA22_FRA extends CHL7v2EventADTA22 {
  /**
   * Construct
   *
   * @param string $i18n i18n
   *
   * @return CHL7v2EventADTA22_FRA
   */
  function __construct($i18n = "FRA") {
    parent::__construct($i18n);
  }

  /**
   * Build i18n segements
   *
   * @param CSejour $sejour Admit
   *
   * @see parent::buildI18nSegments()
   *
   * @return void
   */
  function buildI18nSegments($sejour) {
    
    // Movement segment
    $this->addZBE($sejour);
    
    // Mouvement PMSI
    $this->addZFM($sejour);
  }
}