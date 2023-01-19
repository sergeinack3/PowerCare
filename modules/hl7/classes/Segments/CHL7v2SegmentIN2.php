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
 * Class CHL7v2SegmentIN2
 * IN2 - Represents an HL7 IN2 message segment (Additional Information)
 */

class CHL7v2SegmentIN2 extends CHL7v2Segment {
  /** @var string */
  public $name    = "IN2";

  /** @var null */
  public $set_id;


  /** @var CPatient */
  public $patient;

  /**
   * Build IN2 segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $message  = $event->message;
    $receiver = $event->_receiver;

    /** @var CPatient $patient */
    $patient  = $this->patient;
    
    $data = array();
    
    // IN2-2: Set ID - IN2 (SI)
    $data[] = null;
    
    // IN2-2: Insurance Plan ID (CE) 
    $data[] = null;
    
    // IN2-3: Insurance Company ID (CX) (repeating) 
    $data[] = null;
    
    // IN2-4: Insurance Company Name (XON) (optional repeating) 
    $data[] = null;
    
    // IN2-5: Insurance Company Address (XAD) (optional repeating) 
    $data[] = null;
    
    // IN2-6: Insurance Co Contact Person (XPN) (optional repeating) 
    $data[] = null;
    
    // IN2-7: Insurance Co Phone Number (XTN) (optional repeating) 
    $data[] = null;
    
    // IN2-8: Group Number (ST) (optional) 
    $data[] = null;
    
    // IN2-9: Group Name (XON) (optional repeating) 
    $data[] = null;
    
    // IN2-10: Insured's Group Emp ID (CX) (optional repeating) 
    $data[] = null;
    
    // IN2-11: Insured's Group Emp Name (XON) (optional repeating) 
    $data[] = null;
    
    // IN2-12: Plan Effective Date (DT) (optional) 
    $data[] = null;

    // IN2-13: Plan Expiration Date (DT) (optional) 
    $data[] = null;
    
    // IN2-14: Authorization Information (AUI) (optional) 
    $data[] = null;
    
    // IN2-15: Plan Type (IS) (optional) 
    $data[] = null;
    
    // IN2-16: Name Of Insured (XPN) (optional repeating)
    $data[] = null;
    
    // IN2-17: Insured's Relationship To Patient (CE) (optional) 
    $data[] = null;
    
    // IN2-18: Insured's Date Of Birth (TS) (optional) 
    $data[] = null;
    
    // IN2-19: Insured's Address (XAD) (optional repeating)
    $data[] = null;
    
    // IN2-20: Assignment Of Benefits (IS) (optional) 
    $data[] = null;
    
    // IN2-21: Coordination Of Benefits (IS) (optional) 
    $data[] = null;
    
    // IN2-22: Coord Of Ben. Priority (ST) (optional) 
    $data[] = null;
    
    // IN2-23: Notice Of Admission Flag (ID) (optional) 
    $data[] = null;
    
    // IN2-24: Notice Of Admission Date (DT) (optional) 
    $data[] = null;
    
    // IN2-25: Report Of Eligibility Flag (ID) (optional) 
    $data[] = null;
    
    // IN2-26: Report Of Eligibility Date (DT) (optional) 
    $data[] = null;
    
    // IN2-27: Release Information Code (IS) (optional) 
    $data[] = null;
    
    // IN2-28: Pre-Admit Cert (PAC) (ST) (optional) 
    $data[] = null;
    
    // IN2-29: Verification Date/Time (TS) (optional) 
    $data[] = null;
    
    // IN2-30: Verification By (XCN) (optional repeating) 
    $data[] = null;
    
    // IN2-31: Type Of Agreement Code (IS) (optional) 
    $data[] = null;
    
    // IN2-32: Billing Status (IS) (optional) 
    $data[] = null;
    
    // IN2-33: Lifetime Reserve Days (NM) (optional) 
    $data[] = null;
    
    // IN2-34: Delay Before L.R. Day (NM) (optional) 
    $data[] = null;
    
    // IN2-35: Company Plan Code (IS) (optional) 
    $data[] = null;
    
    // IN2-36: Policy Number (ST) (optional) 
    $data[] = null;
    
    // IN2-37: Policy Deductible (CP) (optional) 
    $data[] = null;
    
    // IN2-38: Policy Limit - Amount (CP) (optional) 
    $data[] = null;
    
    // IN2-39: Policy Limit - Days (NM) (optional) 
    $data[] = null;
    
    // IN2-40: Room Rate - Semi-Private (CP) (optional) 
    $data[] = null;
    
    // IN2-41: Room Rate - Private (CP) (optional) 
    $data[] = null;
    
    // IN2-42: Insured's Employment Status (CE) (optional) 
    $data[] = null;
    
    // IN2-43: Insured's Administrative Sex (IS) (optional) 
    $data[] = null;
    
    // IN2-44: Insured's Employer's Address (XAD) (optional repeating) 
    $data[] = null;
    
    // IN2-45: Verification Status (ST) (optional) 
    $data[] = null;
    
    // IN2-46: Prior Insurance Plan ID (IS) (optional) 
    $data[] = null;
    
    // IN2-47: Coverage Type (IS) (optional) 
    $data[] = null;
    
    // IN2-48: Handicap (IS) (optional) 
    $data[] = null;
    
    // IN2-49: Insured's ID Number (CX) (optional repeating) 
    $data[] = null;
    
    // IN2-50: Signature Code (IS) (optional) 
    $data[] = null;
    
    // IN2-51: Signature Code Date (DT) (optional) 
    $data[] = null;
    
    // IN2-52: Insured_s Birth Place (ST) (optional) 
    $data[] = null;
    
    // IN2-53: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-54: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-55: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-56: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-57: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-58: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-59: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-60: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-61: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-62: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-63: Insured's Phone Number - Home (optional)
    $phones = array();
    if ($patient->assure_tel) {
      $phones[] = $this->getXTN($receiver, $patient->assure_tel, "PRN", "PH");
    }
    if ($patient->assure_tel2) {
      // Pour le portable on met soit PRN ou ORN
      $phones[] = $this->getXTN($receiver, $patient->assure_tel2, $receiver->_configs["build_cellular_phone"], "CP");
    }
    $data[] =  $phones;

    // IN2-64: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-65: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-66: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-67: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-68: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-69: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-70: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-71: VIP Indicator (IS) (optional)
    $data[] = null;

    // IN2-72: VIP Indicator (IS) (optional)
    $data[] = null;
    
    $this->fill($data);
  }
}