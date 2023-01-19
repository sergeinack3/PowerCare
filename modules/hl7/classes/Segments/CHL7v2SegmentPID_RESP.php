<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2SegmentPID 
 * PID - Represents an HL7 PID message segment (Patient Identification)
 */

class CHL7v2SegmentPID_RESP extends CHL7v2Segment {

  /** @var string */
  public $name    = "PID";

  /** @var null */
  public $set_id;
  

  /** @var CPatient */
  public $patient;
  

  /** @var CSejour */
  public $sejour;

  /** @var array() */
  public $domains_returned;

  /**
   * Build PID segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $message          = $event->message;
    $sender           = $event->_sender;
    $group            = $sender->loadRefGroup();
    $domains_returned = $this->domains_returned;

    $patient  = $this->patient;
    
    $data = array();
    // PID-1: Set ID - PID (SI) (optional)
    $data[] = $this->set_id;
    
    // PID-2: Patient ID (CX) (optional)
    $data[] = null;

    $identifiers = array();
    if (empty($domains_returned)) {
      $idex = new CIdSante400();
      $idex->object_id    = $patient->_id;
      $idex->object_class = "CPatient";

      $ljoin   = array();
      $ljoin[] = "group_domain AS g1 ON g1.domain_id = domain.domain_id AND g1.object_class = 'CPatient'";

      foreach ($idex->loadMatchingList() as $_idex) {
        $domain = new CDomain();
        $where["tag"] = " = '$_idex->tag'";
        $domain->loadObject($where, null, null, $ljoin);

        if (!$domain->_id) {
          continue;
        }

        $identifiers[] = array(
          $_idex->id400,
          null,
          null,
          // PID-3-4 Autorité d'affectation
          $this->getAssigningAuthority("domain", null, null, $domain),
          "MR"
        );
      }
    }
    else {
      foreach ($domains_returned as $_domain_returned) {
        $assigning_authority = $this->getAssigningAuthority("domain", null, null, $_domain_returned);

        $identifiers[] = array(
          CIdSante400::getValueFor($patient, $_domain_returned->tag),
          null,
          null,
          // PID-3-4 Autorité d'affectation
          $assigning_authority,
          "MR"
        );
      }
    }

    // PID-3: Patient Identifier List (CX) (repeating)
    $data[] = $identifiers;
    
    // PID-4: Alternate Patient ID - PID (CX) (optional repeating)
    $data[] = null;
    
    // PID-5: Patient Name (XPN) (repeating)
    $data[] = $this->getXPN($patient);

    // PID-6: Mother's Maiden Name (XPN) (optional repeating)
    $data[] = null;
    
    // PID-7: Date/Time of Birth (TS) (optional)
    $data[] = CMbDT::isLunarDate($patient->naissance) ? null : $patient->naissance;
    
    // PID-8: Administrative Sex (IS) (optional)
    // Table - 0001
    // F - Female
    // M - Male
    // O - Other
    // U - Unknown
    // A - Ambiguous  
    // N - Not applicable
    $data[] = CHL7v2TableEntry::mapTo("1", $patient->sexe);
    
    // PID-9: Patient Alias (XPN) (optional repeating)
    $data[] = null;
    
    // PID-10: Race (CE) (optional repeating)
    $data[] = null;
    
    // PID-11: Patient Address (XAD) (optional repeating)
    $address = array();
    
    if ($patient->adresse || $patient->ville || $patient->cp) {
      $linesAdress = explode("\n", $patient->adresse, 2);
      $address[] = array(
        CValue::read($linesAdress, 0),
        str_replace("\n", $message->componentSeparator, CValue::read($linesAdress, 1)),
        $patient->ville,
        $patient->province,
        $patient->cp,
        // Pays INSEE, récupération de l'alpha 3
        CPaysInsee::getAlpha3($patient->pays_insee),
        // Table - 0190
        // B   - Firm/Business 
        // BA  - Bad address 
        // BDL - Birth delivery location (address where birth occurred)  
        // BR  - Residence at birth (home address at time of birth)  
        // C   - Current Or Temporary
        // F   - Country Of Origin 
        // H   - Home 
        // L   - Legal Address 
        // M   - Mailing
        // N   - Birth (nee) (birth address, not otherwise specified)  
        // O   - Office
        // P   - Permanent 
        // RH  - Registry home
        "H",
      );
    }
    
    if ($patient->lieu_naissance || $patient->cp_naissance || $patient->pays_naissance_insee) {
      $address[] = array(
        null,
        null,
        $patient->lieu_naissance,
        null,
        $patient->cp_naissance,
        // Pays INSEE, récupération de l'alpha 3
        CPaysInsee::getAlpha3($patient->pays_naissance_insee),
        // Table - 0190
        // B   - Firm/Business 
        // BA  - Bad address 
        // BDL - Birth delivery location (address where birth occurred)  
        // BR  - Residence at birth (home address at time of birth)  
        // C   - Current Or Temporary
        // F   - Country Of Origin 
        // H   - Home 
        // L   - Legal Address 
        // M   - Mailing
        // N   - Birth (nee) (birth address, not otherwise specified)  
        // O   - Office
        // P   - Permanent 
        // RH  - Registry home
        "BDL",
      );
    }
    $data[] = $address;
    
    // PID-12: County Code (IS) (optional)
    $data[] = null;
    
    // PID-13: Phone Number - Home (XTN) (optional repeating)
    // Table - 0201
    // ASN - Answering Service Number
    // BPN - Beeper Number 
    // EMR - Emergency Number  
    // NET - Network (email) Address
    // ORN - Other Residence Number 
    // PRN - Primary Residence Number 
    // VHN - Vacation Home Number  
    // WPN - Work Number
    
    // Table - 0202
    // BP       - Beeper  
    // CP       - Cellular Phone  
    // FX       - Fax 
    // Internet - Internet Address: Use Only If Telecommunication Use Code Is NET 
    // MD       - Modem 
    // PH       - Telephone  
    // TDD      - Telecommunications Device for the Deaf  
    // TTY      - Teletypewriter
    $phones = array();
    if ($patient->tel) {
      $area_city_code = null;
      $local_number   = null;

      if ($sender->_configs["send_area_local_number"]) {
        $area_city_code = substr($patient->tel, 0, 3);
        $local_number   = substr($patient->tel, 3);
      }

      $phones[] = array(
        null,
        // Table - 0201
        "PRN",
        // Table - 0202
        "PH",
        null,
        null,
        $area_city_code,
        $local_number,
        null,
        null,
        null,
        null,
        ($sender->_configs["send_area_local_number"]) ? null : $patient->tel,
      );
    }

    $data[] =  $phones;
    
    // PID-14: Phone Number - Business (XTN) (optional repeating)
    $data[] = null;
    
    // PID-15: Primary Language (CE) (optional)
    $data[] = null;
    
    // PID-16: Marital Status (CE) (optional)
    $data[] = null;
    
    // PID-17: Religion (CE) (optional)
    $data[] = null;
    
    // PID-18: Patient Account Number (CX) (optional)
    if ($this->sejour) {
      $sejour = $this->sejour;
      $sejour->loadNDA($group->_id);

      $domain = new CDomain();
      $domain->tag = $sejour->getTagNDA();
      $domain->loadMatchingObject();

      $data[] = $sejour->_NDA ? array(
        array(
          $sejour->_NDA,
          null,
          null,
          // PID-3-4 Autorité d'affectation
          $this->getAssigningAuthority("domain", null, null, $domain),
        )
      ) : null;
    }
    else {
      $data[] = null;
    }

    // PID-19: SSN Number - Patient (ST) (optional)
    $data[] = $patient->matricule;

    // PID-20: Driver's License Number - Patient (DLN) (optional)
    $data[] = null;
    
    // PID-21: Mother's Identifier (CX) (optional repeating)
    $data[] = null;

    // PID-22: Ethnic Group (CE) (optional repeating)
    $data[] = null;
    
    // PID-23: Birth Place (ST) (optional)
    $data[] = null;
    
    // PID-24: Multiple Birth Indicator (ID) (optional)
    $data[] = null;
    
    // PID-25: Birth Order (NM) (optional)
    $data[] = $patient->rang_naissance;
    
    // PID-26: Citizenship (CE) (optional repeating)
    $data[] = null;
    
    // PID-27: Veterans Military Status (CE) (optional)
    $data[] = null;
    
    // PID-28: Nationality (CE) (optional)
    $data[] = null;
    
    // PID-29: Patient Death Date and Time (TS) (optional)
    $data[] = ($patient->deces) ? $patient->deces : null;
    
    // PID-30: Patient Death Indicator (ID) (optional)
    $data[] = ($patient->deces) ? "Y" : "N";
    
    // PID-31: Identity Unknown Indicator (ID) (optional)
    $data[] = null;
    
    // PID-32: Identity Reliability Code (IS) (optional repeating)
    $data[] = null;

    // PID-33: Last Update Date/Time (TS) (optional)
    $data[] =  null;
    
    // PID-34: Last Update Facility (HD) (optional)
    $data[] = null;
    
    // PID-35: Species Code (CE) (optional)
    $data[] = null;
    
    // PID-36: Breed Code (CE) (optional)
    $data[] = null;
    
    // PID-37: Strain (ST) (optional)
    $data[] = null;
    
    // PID-38: Production Class Code (CE) (optional)
    $data[] = null;
    
    // PID-39: Tribal Citizenship (CWE) (optional repeating)
    $data[] = null;
          
    $this->fill($data);
  }

  /**
   * Fill other identifiers
   *
   * @param array         &$identifiers Identifiers
   * @param CPatient      $patient      Person
   * @param CInteropActor $actor        Interop actor
   *
   * @return null
   */
  function fillOtherIdentifiers(&$identifiers, CPatient $patient, CInteropActor $actor = null) {
    if (CValue::read($actor->_configs, "send_own_identifier")) {
      $identifiers[] = array(
        $patient->_id,
        null,
        null,
        // PID-3-4 Autorité d'affectation
        $this->getAssigningAuthority("mediboard", null, null, null, $actor->group_id),
        "RI"
      );
    }

    if (!CValue::read($actor->_configs, "send_self_identifier")) {
      return;
    }

    if (!$idex_actor = $actor->getIdex($patient)->id400) {
      return;
    }

    $identifiers[] = array(
      $idex_actor,
      null,
      null,
      // PID-3-4 Autorité d'affectation
      $this->getAssigningAuthority("actor", null, $actor),
    );
  }
}