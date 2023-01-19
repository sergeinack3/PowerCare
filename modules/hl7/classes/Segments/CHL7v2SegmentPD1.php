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
 * Class CHL7v2SegmentPD1 
 * PD1 - Represents an HL7 PD1 message segment (Patient Additional Demographic)
 */

class CHL7v2SegmentPD1 extends CHL7v2Segment {

  /** @var string */
  public $name    = "PD1";
  

  /** @var CPatient */
  public $patient;

  /**
   * Build PD1 segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
        
    $patient  = $this->patient;
    
    $data = array();
    
    // PD1-1: Living Dependency (IS) (optional repeating)
    $data[] = null;
    
    // PD1-2: Living Arrangement (IS) (optional)
    // Table 0220
    // A - Alone - Seul
    // F - Family
    // I - Institution
    // R - Relative
    // S - Spouse Only
    // U - Unknown
    // H - Homeless - Sans domicile fixe
    $data[] = "U";
    
    // PD1-3: Patient Primary Facility (XON) (optional repeating)
    $data[] = null;
    
    // PD1-4: Patient Primary Care Provider Name & ID No. (XCN) (optional repeating)
    $data[] = null;
    
    // PD1-5: Student Indicator (IS) (optional)
    $data[] = null;
    
    // PD1-6: Handicap (IS) (optional)
    $data[] = null;
    
    // PD1-7: Living Will Code (IS) (optional)
    $data[] = null;
    
    // PD1-8: Organ Donor Code (IS) (optional)
    $data[] = null;
    
    // PD1-9: Separate Bill (ID) (optional)
    $data[] = null;
    
    // PD1-10: Duplicate Patient (CX) (optional repeating)
    $data[] = null;
    
    // PD1-11: Publicity Code (CE) (optional)
    $data[] = null;
    
    // PD1-12: Protection Indicator (ID) (optional)
    // Table - 0136
    // Y - Oui - Accès protégé à l'information du patient
    // N - Non - Accès normal à l'information du patient
    $data[] = ($patient->vip) ? "Y" : "N";
    
    // PD1-13: Protection Indicator Effective Date (DT) (optional)
    $data[] = null;
    
    // PD1-14: Place of Worship (XON) (optional repeating)
    $data[] = null;
    
    // PD1-15: Advance Directive Code (CE) (optional repeating)
    $data[] = null;
    
    // PD1-16: Immunization Registry Status (IS) (optional)
    $data[] = null;
    
    // PD1-17: Immunization Registry Status Effective Date (DT) (optional)
    $data[] = null;
    
    // PD1-18: Publicity Code Effective Date (DT) (optional)
    $data[] = null;
    
    // PD1-19: Military Branch (IS) (optional)
    $data[] = null;
    
    // PD1-20: Military Rank/Grade (IS) (optional)
    $data[] = null;
    
    // PD1-21: Military Status (IS) (optional)
    $data[] = null;
    
    $this->fill($data);
  }
}