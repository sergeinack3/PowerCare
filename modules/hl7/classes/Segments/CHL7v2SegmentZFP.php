<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2SegmentZFP
 * ZFP - Represents an HL7 ZFP message segment (Situation professionnelle)
 */

class CHL7v2SegmentZFP extends CHL7v2Segment {

  /** @var string */
  public $name   = "ZFP";
  

  /** @var CPatient */
  public $patient;

  /**
   * Build ZFP segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $patient = $this->patient;
    
    // ZFP-1: Activité socio-professionnelle (nomemclature INSEE)
    $data[] = $patient->csp ? substr($patient->csp, 0, 1) : null;
    
    // ZFP-2: Catégorie socio-professionnelle (nomemclature INSEE)
    $data[] = $patient->csp;
    
    $this->fill($data);
  }
}