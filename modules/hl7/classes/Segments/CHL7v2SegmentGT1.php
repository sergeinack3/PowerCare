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
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentGT1
 * GT1 - Represents an HL7 ACC message segment (Guarantor)
 */

class CHL7v2SegmentGT1 extends CHL7v2Segment {

  /** @var string */
  public $name   = "GT1";

  /** @var null */
  public $set_id;
  

  /** @var CPatient */
  public $patient;
  

  /** @var CSejour */
  public $sejour;

  /**
   * Build GT1 segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    $data = array();

    parent::build($event);

    $data[] = null;
    
    // GT1-1: Set ID - GT1 (SI)
    $data[] = $this->set_id;
        
    // GT1-2: Guarantor Number (CX) (optional repeating)
    $data[] = null;
    
    // GT1-3: Guarantor Name (XPN)  (repeating)
    $data[] = null;
    
    // GT1-4: Guarantor Spouse Name (XPN) (optional repeating)
    $data[] = null;
    
    // GT1-5: Guarantor Address (XAD) (optional repeating)
    $data[] = null;
    
    // GT1-6: Guarantor Ph Num - Home (XTN) (optional repeating)
    $data[] = null;
    
    // GT1-7: Guarantor Ph Num - Business (XTN) (optional repeating)
    $data[] = null;
    
    // GT1-8: Guarantor Date/Time Of Birth (TS)  (optional)
    $data[] = null;
    
    // GT1-9: Guarantor Administrative Sex (IS)  (optional)
    $data[] = null;
    
    // GT1-10: Guarantor Type (IS)  (optional)
    $data[] = null;
    
    // GT1-11: Guarantor Relationship (CE)  (optional)
    $data[] = null;
    
    // GT1-12: Guarantor SSN (ST)  (optional)
    $data[] = null;
    
    // GT1-13: Guarantor Date - Begin (DT)  (optional)
    $data[] = null;
    
    // GT1-14: Guarantor Date - End (DT)  (optional)
    $data[] = null;
    
    // GT1-15: Guarantor Priority (NM)  (optional)
    $data[] = null;
    
    // GT1-16: Guarantor Employer Name (XPN) (optional repeating)
    $data[] = null;
    
    // GT1-17: Guarantor Employer Address (XAD) (optional repeating)
    $data[] = null;
    
    // GT1-18: Guarantor Employer Phone Number (XTN) (optional repeating)
    $data[] = null;
    
    // GT1-19: Guarantor Employee ID Number (CX) (optional repeating)
    $data[] = null;
    
    // GT1-20: Guarantor Employment Status (IS)  (optional)
    $data[] = null;
    
    // GT1-21: Guarantor Organization Name (XON) (optional repeating)
    $data[] = null;
    
    // GT1-22: Guarantor Billing Hold Flag (ID)  (optional)
    $data[] = null;
    
    // GT1-23: Guarantor Credit Rating Code (CE)  (optional)
    $data[] = null;
    
    // GT1-24: Guarantor Death Date And Time (TS)  (optional)
    $data[] = null;
    
    // GT1-25: Guarantor Death Flag (ID)  (optional)
    $data[] = null;
    
    // GT1-26: Guarantor Charge Adjustment Code (CE)  (optional)
    $data[] = null;
    
    // GT1-27: Guarantor Household Annual Income (CP)  (optional)
    $data[] = null;
    
    // GT1-28: Guarantor Household Size (NM)  (optional)
    $data[] = null;
    
    // GT1-29: Guarantor Employer ID Number (CX) (optional repeating)
    $data[] = null;
    
    // GT1-30: Guarantor Marital Status Code (CE)  (optional)
    $data[] = null;
    
    // GT1-31: Guarantor Hire Effective Date (DT)  (optional)
    $data[] = null;
    
    // GT1-32: Employment Stop Date (DT)  (optional)
    $data[] = null;
    
    // GT1-33: Living Dependency (IS)  (optional)
    $data[] = null;
    
    // GT1-34: Ambulatory Status (IS) (optional repeating)
    $data[] = null;
    
    // GT1-35: Citizenship (CE) (optional repeating)
    $data[] = null;
    
    // GT1-36: Primary Language (CE)  (optional)
    $data[] = null;
    
    // GT1-37: Living Arrangement (IS)  (optional)
    $data[] = null;
    
    // GT1-38: Publicity Code (CE)  (optional)
    $data[] = null;
    
    // GT1-39: Protection Indicator (ID)  (optional)
    $data[] = null;
    
    // GT1-40: Student Indicator (IS)  (optional)
    $data[] = null;
    
    // GT1-41: Religion (CE)  (optional)
    $data[] = null;
    
    // GT1-42: Mother's Maiden Name (XPN) (optional repeating)
    $data[] = null;
    
    // GT1-43: Nationality (CE)  (optional)
    $data[] = null;
    
    // GT1-44: Ethnic Group (CE) (optional repeating)
    $data[] = null;
    
    // GT1-45: Contact Person's Name (XPN) (optional repeating)
    $data[] = null;
    
    // GT1-46: Contact Person's Telephone Number (XTN) (optional repeating)
    $data[] = null;
    
    // GT1-47: Contact Reason (CE)  (optional)
    $data[] = null;
    
    // GT1-48: Contact Relationship (IS)  (optional)
    $data[] = null;
    
    // GT1-49: Job Title (ST)  (optional)
    $data[] = null;
    
    // GT1-50: Job Code/Class (JCC)  (optional)
    $data[] = null;
    
    // GT1-51: Guarantor Employer's Organization Name (XON) (optional repeating)
    $data[] = null;
    
    // GT1-52: Handicap (IS)  (optional)
    $data[] = null;
    
    // GT1-53: Job Status (IS)  (optional)
    $data[] = null;
    
    // GT1-54: Guarantor Financial Class (FC)  (optional)
    $data[] = null;
    
    // GT1-55: Guarantor Race (CE) (optional repeating)
    $data[] = null;
    
    // GT1-56: Guarantor Birth Place (ST)  (optional)
    $data[] = null;
    
    // GT1-57: VIP Indicator (IS)  (optional)
    $data[] = null;
    
    $this->fill($data);
  }
}