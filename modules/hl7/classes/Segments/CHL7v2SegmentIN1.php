<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CMbArray;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;

/**
 * Class CHL7v2SegmentIN1 
 * IN1 - Represents an HL7 IN1 message segment (Insurance)
 */

class CHL7v2SegmentIN1 extends CHL7v2Segment {
  /** @var string */
  public $name    = "IN1";

  /** @var null */
  public $set_id;


  /** @var CPatient */
  public $patient;

  /**
   * Build IN1 segement
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
    $group    = $receiver->loadRefGroup();

    /** @var CPatient $patient */
    $patient  = $this->patient;
    
    $data = array();
    
    // IN1-1: Insured's Employee ID
    $data[] = $this->set_id;
    
    // IN1-2: Insured's Social Security Number
    $data[] = $this->getIN12($patient);
    
    // IN1-3: Insurance Company ID (CX) (repeating) 
    $data[] = array(
      $patient->code_regime.$patient->caisse_gest.$patient->centre_gest
    );
    
    // IN1-4: Insurance Company Name (XON) (optional repeating) 
    $data[] = null;
    
    // IN1-5: Insurance Company Address (XAD) (optional repeating) 
    $data[] = null;
    
    // IN1-6: Insurance Co Contact Person (XPN) (optional repeating) 
    $data[] = null;
    
    // IN1-7: Insurance Co Phone Number (XTN) (optional repeating) 
    $data[] = null;
    
    // IN1-8: Group Number (ST) (optional) 
    $data[] = null;
    
    // IN1-9: Group Name (XON) (optional repeating) 
    $data[] = null;
    
    // IN1-10: Insured's Group Emp ID (CX) (optional repeating) 
    $data[] = null;
    
    // IN1-11: Insured's Group Emp Name (XON) (optional repeating) 
    $data[] = null;
    
    // IN1-12: Plan Effective Date (DT) (optional) 
    $data[] = $patient->deb_amo;
    
    // IN1-13: Plan Expiration Date (DT) (optional) 
    $data[] = $patient->fin_amo;
    
    // IN1-14: Authorization Information (AUI) (optional) 
    $data[] = null;
    
    // IN1-15: Plan Type (IS) (optional) 
    $data[] = null;
    
    // IN1-16: Name Of Insured (XPN) (optional repeating)
    $names = array();

    $mode_identito_vigilance = "light";
    if ($receiver) {
      $mode_identito_vigilance = $receiver->_configs["mode_identito_vigilance"];
    }

    $anonyme = is_numeric($patient->assure_nom);

    $nom    = CPatient::applyModeIdentitoVigilance($patient->assure_nom, false, $mode_identito_vigilance);

    $prenom   = CPatient::applyModeIdentitoVigilance($patient->assure_prenom  , true, $mode_identito_vigilance, $anonyme);
    $prenom_2 = CPatient::applyModeIdentitoVigilance($patient->_assure_prenom_2, true, $mode_identito_vigilance, $anonyme);
    $prenom_3 = CPatient::applyModeIdentitoVigilance($patient->_assure_prenom_3, true, $mode_identito_vigilance, $anonyme);
    $prenom_4 = CPatient::applyModeIdentitoVigilance($patient->_assure_prenom_4, true, $mode_identito_vigilance, $anonyme);

    $prenoms = array($prenom_2, $prenom_3, $prenom_4);
    CMbArray::removeValue("", $prenoms);

    // Nom usuel
    $assure_usualname = array(
      $nom,
      $prenom,
      implode(",", $prenoms),
      null,
      $patient->assure_civilite,
      null,
      // Table 0200
      // A - Alias Name
      // B - Name at Birth
      // C - Adopted Name
      // D - Display Name
      // I - Licensing Name
      // L - Legal Name
      // M - Maiden Name
      // N - Nickname /_Call me_ Name/Street Name
      // P - Name of Partner/Spouse (retained for backward compatibility only)
      // R - Registered Name (animals only)
      // S - Coded Pseudo-Name to ensure anonymity
      // T - Indigenous/Tribal/Community Name
      // U - Unspecified
      (is_numeric($nom)) ? "S" : "L",
      // Table 465
      // A - Alphabetic (i.e., Default or some single-byte)
      // I - Ideographic (i.e., Kanji)
      // P - Phonetic (i.e., ASCII, Katakana, Hiragana, etc.)
      "A"
    );

    $assure_birthname = array();
    // Cas nom de naissance
    if ($patient->assure_nom_jeune_fille) {
      $nom_jeune_fille = CPatient::applyModeIdentitoVigilance(
        $patient->assure_nom_jeune_fille, true, $mode_identito_vigilance, $anonyme
      );

      $assure_birthname    = $assure_usualname;
      $assure_birthname[0] = $nom_jeune_fille;
      // Legal Name devient Display Name
      $assure_usualname[6] = "D";
    }
    $names[] = $assure_usualname;

    if ($patient->assure_nom_jeune_fille && $receiver &&  $receiver->_configs["build_PID_6"] == "none") {
      $names[] = $assure_birthname;
    }

    $data[] = $names;
    
    // IN1-17: Insured's Relationship To Patient (CE) (optional) 
    $data[] = $patient->qual_beneficiaire;
    
    // IN1-18: Insured's Date Of Birth (TS) (optional) 
    $data[] = $patient->assure_naissance;
    
    // IN1-19: Insured's Address (XAD) (optional repeating)
    $address = array();

    $linesAdress = explode("\n", $patient->assure_adresse, 2);
    $address[] = array(
      CValue::read($linesAdress, 0),
      str_replace("\n", $message->componentSeparator, CValue::read($linesAdress, 1)),
      $patient->assure_ville,
      null,
      $patient->assure_cp,
      // Pays INSEE, récupération de l'alpha 3
      CPaysInsee::getAlpha3($patient->assure_pays_insee),
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

    $data[] = $address;
    
    // IN1-20: Assignment Of Benefits (IS) (optional) 
    $data[] = null;
    
    // IN1-21: Coordination Of Benefits (IS) (optional) 
    $data[] = null;
    
    // IN1-22: Coord Of Ben. Priority (ST) (optional) 
    $data[] = null;
    
    // IN1-23: Notice Of Admission Flag (ID) (optional) 
    $data[] = null;
    
    // IN1-24: Notice Of Admission Date (DT) (optional) 
    $data[] = null;
    
    // IN1-25: Report Of Eligibility Flag (ID) (optional) 
    $data[] = null;
    
    // IN1-26: Report Of Eligibility Date (DT) (optional) 
    $data[] = null;
    
    // IN1-27: Release Information Code (IS) (optional) 
    $data[] = null;
    
    // IN1-28: Pre-Admit Cert (PAC) (ST) (optional) 
    $data[] = null;
    
    // IN1-29: Verification Date/Time (TS) (optional) 
    $data[] = null;
    
    // IN1-30: Verification By (XCN) (optional repeating) 
    $data[] = null;
    
    // IN1-31: Type Of Agreement Code (IS) (optional) 
    $data[] = null;
    
    // IN1-32: Billing Status (IS) (optional) 
    $data[] = null;
    
    // IN1-33: Lifetime Reserve Days (NM) (optional) 
    $data[] = null;
    
    // IN1-34: Delay Before L.R. Day (NM) (optional) 
    $data[] = null;
    
    // IN1-35: Company Plan Code (IS) (optional) 
    $data[] = $patient->code_gestion;
    
    // IN1-36: Policy Number (ST) (optional) 
    $data[] = null;
    
    // IN1-37: Policy Deductible (CP) (optional) 
    $data[] = null;
    
    // IN1-38: Policy Limit - Amount (CP) (optional) 
    $data[] = null;
    
    // IN1-39: Policy Limit - Days (NM) (optional) 
    $data[] = null;
    
    // IN1-40: Room Rate - Semi-Private (CP) (optional) 
    $data[] = null;
    
    // IN1-41: Room Rate - Private (CP) (optional) 
    $data[] = null;
    
    // IN1-42: Insured's Employment Status (CE) (optional) 
    $data[] = null;
    
    // IN1-43: Insured's Administrative Sex (IS) (optional)
    $sexe   = CHL7v2TableEntry::mapTo("1", $patient->assure_sexe);
    $data[] = $sexe ? : "U";

    // IN1-44: Insured's Employer's Address (XAD) (optional repeating) 
    $data[] = null;
    
    // IN1-45: Verification Status (ST) (optional) 
    $data[] = null;
    
    // IN1-46: Prior Insurance Plan ID (IS) (optional) 
    $data[] = null;
    
    // IN1-47: Coverage Type (IS) (optional) 
    $data[] = null;
    
    // IN1-48: Handicap (IS) (optional) 
    $data[] = null;
    
    // IN1-49: Insured's ID Number (CX) (optional repeating) 
    $data[] = $patient->matricule;
    
    // IN1-50: Signature Code (IS) (optional) 
    $data[] = null;
    
    // IN1-51: Signature Code Date (DT) (optional) 
    $data[] = null;
    
    // IN1-52: Insured_s Birth Place (ST) (optional) 
    $data[] = null;
    
    // IN1-53: VIP Indicator (IS) (optional)
    $data[] = null;
    
    $this->fill($data);
  }
}
