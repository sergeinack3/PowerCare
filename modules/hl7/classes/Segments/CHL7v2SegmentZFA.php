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
 * Class CHL7v2SegmentZFA
 * ZFD - Represents an HL7 ZFA message segment (DMP)
 */

class CHL7v2SegmentZFA extends CHL7v2Segment {
  /** @var string */
  public $name   = "ZFA";
  

  /** @var CPatient */
  public $patient;

  /**
   * Build ZFD segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $patient = $this->patient;
    
    // ZFA-1: Statut du DMP du patient (ID)
    $data[] = null;

    // ZFA-2: Date de recueil du statut du DMP (TS)
    $data[] = null;

    // ZFA-3: Date de fermeture du DMP du patient (TS)
    $data[] = null;

    // ZFA-4: Autorisation d?accès valide au DMP du patient pour l?établissement (ID)
    $data[] = null;

    // ZFA-5: Date de recueil de l?état de l?autorisation d?accès au DMP du patient pour l?établissement (TS)
    $data[] = null;

    // ZFA-6: Opposition du patient à l?accès en mode bris de glace (ID)
    $data[] = null;

    // ZFA-7: Opposition du patient à l?accès en mode centre de régulation (ID)
    $data[] = null;

    // ZFA-8: Date de recueil de l?état des oppositions du patient (TS)
    $data[] = null;
    
    $this->fill($data);
  }
}