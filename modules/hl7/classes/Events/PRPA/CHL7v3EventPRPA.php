<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\PRPA;


use DateTime;
use DateTimeZone;
use DOMElement;
use DOMNode;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Interop\Dmp\CDMP;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CMbOID;
use Ox\Interop\Hl7\CHL7v3MessageXML;
use Ox\Interop\Hl7\Events\CHL7v3Event;
use Ox\Interop\InteropResources\CInteropResources;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v3EventPRPA
 * Patient Administration HL7v3
 */
class CHL7v3EventPRPA extends CHL7v3Event implements CHL7EventPRPA {
  public $_event_name;

  /**
   * Construct
   *
   * @return CHL7v3EventPRPA
   */
  function __construct() {
    parent::__construct();

    $this->event_type = "PRPA";
  }

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    parent::build($object);

    // Header
    $this->addHeader();

    // Receiver
    $this->addReceiver();

    // Sender
    $this->addSender();
  }

  /**
   * Get interaction
   *
   * @return string|void
   */
  function getInteractionID() {
  }

  /**
   * Add header
   *
   * @return void
   */
  function addHeader() {
    $dom            = $this->dom;

    $root = $dom->addElement($dom, $this->getInteractionID());
    $dom->addNameSpaces();

    // id
    $id = $dom->addElement($root, "id");
    $this->setII($id, CMbSecurity::generateUUID(), CMbOID::getOIDFromClass($this->_exchange_hl7v3, $this->_receiver));

    // creationTime
    $creationTime = $dom->addElement($root, "creationTime");
    $dom->addAttribute($creationTime, "value", CHL7v3MessageXML::dateTime());

    // interactionId
    $interactionId = $dom->addElement($root, "interactionId");
    $this->setII($interactionId, $this->getInteractionID(), "2.16.840.1.113883.1.6");

    // processingCode
    $processingCode = $dom->addElement($root, "processingCode");
    $instance_role  = CAppUI::conf("instance_role") == "prod" ? "P" : "D";
    $dom->addAttribute($processingCode, "code", $instance_role);

    // processingModeCode
    $processingModeCode = $dom->addElement($root, "processingModeCode");
    $dom->addAttribute($processingModeCode, "code", "T");

    // acceptAckCode
    $acceptAckCode = $dom->addElement($root, "acceptAckCode");
    $dom->addAttribute($acceptAckCode, "code", "AL");
  }

  /**
   * Add receiver
   *
   * @return void
   */
  function addReceiver() {
    $dom  = $this->dom;
    $root = $dom->documentElement;

    $receiver = $dom->addElement($root, "receiver");
    $this->setTypeCode($receiver, "RCV");

    $this->addDevice($receiver, $this->_receiver);
  }

  /**
   * Add sender
   *
   * @return void
   */
  function addSender() {
    $dom  = $this->dom;
    $root = $dom->documentElement;

    $sender = $dom->addElement($root, "sender");
    $this->setTypeCode($sender, "SND");

    $this->addDevice($sender);
  }

  /**
   * Add device
   *
   * @param DOMNode       $elParent Parent element
   * @param CInteropActor $actor    Actor
   *
   * @return void
   */
  function addDevice(DOMNode $elParent, CInteropActor $actor = null) {
    $dom = $this->dom;

    // device
    $device = $dom->addElement($elParent, "device");
    $dom->addAttribute($device, "classCode", "DEV");
    $dom->addAttribute($device, "determinerCode", "INSTANCE");

    // id
    $id = $dom->addElement($device, "id");
    $dom->addAttribute($id, "root", $actor ? $actor->OID : CAppUI::conf("dmp LPS_ID"));

    // softwareName
    $dom->addElement($device, "softwareName", $actor ? "DMP" : CAppUI::conf("dmp LPS_Nom"));
  }

  /**
   * Add control act process
   *
   * @param CPatient $patient Patient
   *
   * @return DOMElement
   */
  function addControlActProcess(CPatient $patient) {
    $dom  = $this->dom;
    $root = $dom->documentElement;

    $controlActProcess = $dom->addElement($root, "controlActProcess");
    $dom->addAttribute($controlActProcess, "classCode", "CACT");
    $dom->addAttribute($controlActProcess, "moodCode", "EVN");

    return $controlActProcess;
  }

  /**
   * Add subject of
   *
   * @param DOMNode $elParent      Parent element
   * @param string  $code          Code
   * @param string  $codeSystem    Code system
   * @param string  $displayName   Display name
   * @param string  $value         Value
   * @param bool    $effectiveTime Effective time
   *
   * @return void
   */
  function addSubjectOf(DOMNode $elParent, $code, $codeSystem, $displayName, $value, $effectiveTime = false) {
    $dom = $this->dom;

    $subjectOf = $dom->addElement($elParent, "subjectOf");
    $this->setTypeCode($subjectOf, "SBJ");

    $administrativeObservation = $dom->addElement($subjectOf, "administrativeObservation");
    $this->setClassMoodCode($administrativeObservation, "OBS", "EVN");

    $code_elt = $dom->addElement($administrativeObservation, "code");
    $this->setCode($code_elt, $code, $codeSystem, $displayName);

    if ($effectiveTime) {
      $date = $this->getDateToFormatCDA(CMbDT::date());
      $effectiveTime = $dom->addElement($administrativeObservation, "effectiveTime");
      $dom->addAttribute($effectiveTime, "value", $date);
    }

    $value_elt = $dom->addElement($administrativeObservation, "value");
    $value_elt->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $value_elt->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:type', 'BL');
    $dom->addAttribute($value_elt, "value", $value);
  }

  /**
   * Transforme une chaine date au format time CDA
   *
   * @param String $dateTime String
   *
   * @return string
   */
  function setUtcToTime($dateTime) {
    $timezone_local = new DateTimeZone(CAppUI::conf("timezone"));
    $timezone_utc = new DateTimeZone("UTC");

    $date = new DateTime($dateTime, $timezone_utc);
    $date->setTimezone($timezone_local);

    return $date->format("d-m-Y H:i");
  }

  /**
   * Transforme une chaine date au format date CDA
   *
   * @param String  $date                 Date
   * @param Boolean $transform_lunar_date Apply the algo for change lunar date to date
   *
   * @return string
   */
  function getDateToFormatCDA($date, $transform_lunar_date = false) {
    if (!$date) {
      return null;
    }

    [$year, $month, $day] = explode("-", $date);

    if ($transform_lunar_date && !checkdate($month, $day, $year)) {
      if ($month > 12) {
        $month = 12;
      }
      $last_day = date("t", strtotime("$year-$month-01"));
      if ($day > $last_day) {
        $day = $last_day;
      }
    }

    return $year.$month.$day;
  }

  /**
   * Set class code
   *
   * @param DOMNode $elParent  Parent element
   * @param string  $classCode Class code
   *
   * @return void
   */
  function setClassCode(DOMNode $elParent, $classCode) {
    $dom = $this->dom;

    $dom->addAttribute($elParent, "classCode", $classCode);
  }

  /**
   * Set class code
   *
   * @param DOMNode $elParent Parent element
   * @param string  $typeCode Type code
   *
   * @return void
   */
  function setTypeCode(DOMNode $elParent, $typeCode) {
    $dom = $this->dom;

    $dom->addAttribute($elParent, "typeCode", $typeCode);
  }

  /**
   * Set class and determiner code
   *
   * @param DOMNode $elParent       Parent element
   * @param string  $classCode      Class code
   * @param string  $determinerCode Determiner code
   *
   * @return void
   */
  function setClassDeterminerCode(DOMNode $elParent, $classCode, $determinerCode) {
    $dom = $this->dom;

    $this->setClassCode($elParent, $classCode);
    $dom->addAttribute($elParent, "determinerCode", $determinerCode);
  }

  /**
   * Set II
   *
   * @param DOMNode $elParent  Parent element
   * @param string  $extension Extension
   * @param string  $root      Root
   *
   * @return void
   */
  function setII(DOMNode $elParent, $extension, $root) {
    $dom = $this->dom;

    $dom->addAttribute($elParent, "extension", $extension);
    $dom->addAttribute($elParent, "root", $root);
  }

  /**
   * Set code
   *
   * @param DOMNode $elParent    Parent element
   * @param string  $code        Code
   * @param string  $codeSystem  Code system
   * @param string  $displayName Display name
   *
   * @return void
   */
  function setCode(DOMNode $elParent, $code, $codeSystem, $displayName = null) {
    $dom = $this->dom;

    $dom->addAttribute($elParent, "code", $code);
    $dom->addAttribute($elParent, "codeSystem", $codeSystem);

    if ($displayName) {
      $dom->addAttribute($elParent, "displayName", $displayName);
    }
  }

  /**
   * Set class and mood code
   *
   * @param DOMNode $elParent  Parent element
   * @param string  $classCode Class code
   * @param string  $moodCode  Mood code
   *
   * @return void
   */
  function setClassMoodCode(DOMNode $elParent, $classCode, $moodCode) {
    $dom = $this->dom;

    $dom->addAttribute($elParent, "classCode", $classCode);
    $dom->addAttribute($elParent, "moodCode", $moodCode);
  }

  /**
   * Set qualifier
   *
   * @param DOMNode $elParent  Parent element
   * @param string  $qualifier Qualifier
   *
   * @return void
   */
  function setQualifier(DOMNode $elParent, $qualifier) {
    $dom = $this->dom;

    $dom->addAttribute($elParent, "qualifier", $qualifier);
  }

  /**
   * Add value
   *
   * @param DOMNode $elParent Parent element
   * @param string  $value    Value
   * @param string  $use      Use value
   *
   * @return void
   */
  function addValue(DOMNode $elParent, $value = null, $use = null) {
    $dom = $this->dom;

    if (!$value) {
      //return;
    }

    if ($use) {
      $dom->addAttribute($elParent, "use", $use);
    }

    $dom->addAttribute($elParent, "value", $value);
  }

  /**
   * Add name
   *
   * @param DOMNode  $elParent Parent element
   * @param CPatient $patient  Patient
   *
   * @return void
   */
  function addName(DOMNode $elParent, CPatient $patient) {
    $dom  = $this->dom;

    $name = $dom->addElement($elParent, "name");

    $civilite = null;
    if ($patient->civilite == "m" || $patient->civilite == "mme" || $patient->civilite == "mlle") {
      $civilite = ucfirst($patient->civilite);
    }
    else {
      $civilite = $patient->sexe == "m" ? "M" : "Mme";
    }
    $dom->addElement($name, "prefix", $civilite);

    if ($patient->_p_maiden_name) {
      $family = $dom->addElement($name, "family", $patient->_p_last_name);
      $this->setQualifier($family, "SP");

      $family = $dom->addElement($name, "family", $patient->_p_maiden_name);
      $this->setQualifier($family, "BR");
    }
    else {
      $family = $dom->addElement($name, "family", $patient->_p_last_name);
      $this->setQualifier($family, "SP");
    }

    $dom->addElement($name, "given", $patient->_p_first_name);

    if ($patient->_prenom_2) {
      $dom->addElement($name, "given", $patient->_prenom_2);
    }
    if ($patient->_prenom_3) {
      $dom->addElement($name, "given", $patient->_prenom_3);
    }
    if ($patient->_prenom_4) {
      $dom->addElement($name, "given", $patient->_prenom_4);
    }
  }

  /**
   * Add Telecom
   *
   * @param DOMNode  $elParent Parent element
   * @param CPatient $patient  Patient
   *
   * @return void
   */
  function addTelecom(DOMNode $elParent, CPatient $patient) {
    $dom = $this->dom;

    $patientPhoneNumber       = $patient->_p_phone_number;
    $patientMobilePhoneNumber = $patient->_p_mobile_phone_number;
    $patientEmail             = $patient->_p_email;

    $telecom = $dom->addElement($elParent, "telecom");
    $this->addValue($telecom, ($patientPhoneNumber ? "tel:$patientPhoneNumber" : null), "HP");

    $telecom = $dom->addElement($elParent, "telecom");
    $this->addValue($telecom, ($patientMobilePhoneNumber ? "tel:$patientMobilePhoneNumber" : null), "MC");

    $telecom = $dom->addElement($elParent, "telecom");
    $this->addValue($telecom, ($patientEmail ? "mailto:$patientEmail" : null));
  }

  /**
   * Add Adress
   *
   * @param DOMNode  $elParent Parent element
   * @param CPatient $patient  Patient
   *
   * @return void
   */
  function addAdress(DOMNode $elParent, CPatient $patient) {
    $dom = $this->dom;

    $addr = $dom->addElement($elParent, "addr");
    $addresses = preg_split("#[\t\n\v\f\r]+#", $patient->_p_street_address, -1, PREG_SPLIT_NO_EMPTY);
    if ($addresses) {
      foreach ($addresses as $_addr) {
        $dom->addElement($addr, "streetAddressLine", $_addr);
      }
    }
    else {
      $dom->addElement($addr, "streetAddressLine", "");
    }

    $dom->addElement($addr, "postalCode", $patient->_p_postal_code);
    $dom->addElement($addr, "city", $patient->_p_city);
    $dom->addElement($addr, "country", $patient->_p_country);
  }

  /**
   * Add Personal Relationship (représentant légal)
   *
   * @param DOMNode  $elParent Parent element
   * @param CPatient $patient  Patient
   *
   * @throws
   *
   * @return void
   */
  function addPersonalRelationship(DOMNode $elParent, CPatient $patient) {
    $dom = $this->dom;

    $personalRelationShip = $dom->addElement($elParent, "personalRelationship");
    $dom->addAttribute($personalRelationShip, "classCode", "PRS");

    $correspondant_patient = new CCorrespondantPatient();
    $correspondant_patient->patient_id = $patient->_id;
    if ($patient->tutelle == "tutelle") {
      $correspondant_patient->parente = "tuteur";
    }
    elseif ($patient->tutelle == "curatelle") {
      $correspondant_patient->parente = "curateur";
    }
    $correspondant_patient->loadMatchingObject();

    if (!$correspondant_patient->_id) {
      if ($correspondant_patient->parente) {
        throw new CMbException("DMP-msg-None correspondant parente", $correspondant_patient->parente);
      }
      else {
        throw new CMbException("DMP-msg-None correspondant");
      }
    }

    // Ajout du code
    $code = $dom->addElement($personalRelationShip, "code");
    $values = CANSValueSet::loadEntries(
        "qualiteRepresentantLegal",
      CMbArray::get(CDMP::$qualite_representant_legal, $correspondant_patient->parente)
    );

    if (!CMbArray::get($values, "code")) {
      CAppUI::stepAjax("Test", UI_MSG_ERROR, $correspondant_patient->parente);
    }

    $dom->addAttribute($code, "code", CMbArray::get($values, "code"));
    $dom->addAttribute($code, "codeSystem", CMbArray::get($values, "codeSystem"));

    // Ajout de l'adresse
    $addr = $dom->addElement($personalRelationShip, "addr");
    $dom->addElement($addr, "streetAddressLine", $correspondant_patient->adresse);
    $dom->addElement($addr, "postalCode"       , $correspondant_patient->cp);
    $dom->addElement($addr, "city"             , $correspondant_patient->ville);

    // Ajout des téléphones et adresse mail
    $correspondantPatientPhoneNumber       = $correspondant_patient->tel;
    $correspondantPatientMobilePhoneNumber = $correspondant_patient->mob;
    $correspondantPatientEmail             = $correspondant_patient->email;

    $telecom = $dom->addElement($personalRelationShip, "telecom");
    $this->addValue($telecom, ($correspondantPatientPhoneNumber ? "tel:$correspondantPatientPhoneNumber" : null), "HP");

    $telecom = $dom->addElement($personalRelationShip, "telecom");
    $this->addValue($telecom, ($correspondantPatientMobilePhoneNumber ? "tel:$correspondantPatientMobilePhoneNumber" : null), "MC");

    $telecom = $dom->addElement($personalRelationShip, "telecom");
    $this->addValue($telecom, ($correspondantPatientEmail ? "mailto:$correspondantPatientEmail" : null));

    // Ajout relationshipHolder1
    $relationshipHolder1 = $dom->addElement($personalRelationShip, "relationshipHolder1");
    $name                = $dom->addElement($relationshipHolder1, "name");

    $family_BR           = $dom->addElement($name, "family", $correspondant_patient->nom);
    $dom->addAttribute($family_BR, "qualifier", "BR");

    $family_SP  = $dom->addElement($name, "family", $correspondant_patient->nom);
    $dom->addAttribute($family_SP, "qualifier", "SP");

    $dom->addElement($name, "given", $correspondant_patient->prenom);
  }

  /**
   * Add birthplace
   *
   * @param DOMNode  $elParent Parent element
   * @param CPatient $patient  Patient
   *
   * @return void
   */
  function addBirthPlace(DOMNode $elParent, CPatient $patient) {
    if (!$patient->cp_naissance && !$patient->_pays_naissance_insee && !$patient->lieu_naissance) {
      //return;
    }

    $dom = $this->dom;

    $birthplace = $dom->addElement($elParent, "birthPlace");
    $dom->addAttribute($birthplace, "classCode", "BIRTHPL");

    $patient->updateNomPaysInsee();

    $addr = $dom->addElement($birthplace, "addr");
    $dom->addElement($addr, "postalCode", $patient->cp_naissance);
    $dom->addElement($addr, "city", $patient->lieu_naissance);
    $dom->addElement($addr, "country", $patient->_pays_naissance_insee);
  }

  /**
   * Add represented organization
   *
   * @param DOMNode $elParent Parent element
   * @param CGroups $group    Group
   *
   * @return void
   */
  function addRepresentedOrganizationGroup(DOMNode $elParent, CGroups $group) {
    $identifiant = null;

    if (CAppUI::gconf('xds general use_siret_finess_ans', $group->_id) == 'siret' && $group->siret) {
      $identifiant = "3$group->siret";
    }
    elseif (CAppUI::gconf('xds general use_siret_finess_ans', $group->_id) == 'finess' && $group->finess) {
      $identifiant = "1$group->finess";
    }

    if (!$identifiant) {
      CAppUI::stepAjax('CDMP-msg-none identifer', UI_MSG_ERROR);
    }

    $this->addRepresentedOrganization($elParent, $identifiant, $group->text);
  }

  /**
   * Add represented organization
   *
   * @param DOMNode $elParent    Parent element
   * @param String  $identifiant Identifiant
   * @param String  $name        Name
   *
   * @return void
   */
  function addRepresentedOrganization(DOMNode $elParent, $identifiant, $name) {
    $dom = $this->dom;

    $representedOrganization = $dom->addElement($elParent, "representedOrganization");
    $this->setClassDeterminerCode($representedOrganization, "ORG", "INSTANCE");

    $id = $dom->addElement($representedOrganization, "id");
    $this->setII($id, $identifiant, "1.2.250.1.71.4.2.2");

    $dom->addElement($representedOrganization, "name", $name);

    $contactParty = $dom->addElement($representedOrganization, "contactParty");
    $this->setClassCode($contactParty, "CON");
  }

  /**
   * Add a represented contact party
   *
   * @param DOMNode  $elParent Parent element
   * @param CPatient $patient  Patient
   *
   * @return void
   */
  function addContactParty(DOMNode $elParent, CPatient $patient) {
    $dom = $this->dom;
    $contactParty = $dom->addElement($elParent, "contactParty");
    $this->setClassCode($contactParty, "CON");

    $code = $dom->addElement($contactParty, "code");
    $this->setCode($code, "CARTE_SESAM_VITALE", "1.2.250.1.213.4.1.2.5");

    $contactPerson = $dom->addElement($contactParty, "contactPerson");
    $name = $dom->addElement($contactPerson, "name");

    $family = $dom->addElement($name, "family", $patient->_vitale_lastname);
    $this->setQualifier($family, "SP");

    $family = $dom->addElement($name, "family", $patient->_vitale_birthname);
    $this->setQualifier($family, "BR");

    $dom->addElement($name, "given", $patient->_vitale_firstname);

    $birthTime = $dom->addElement($contactPerson, "birthTime");
    $date = $patient->_vitale_birthdate;
    if (strlen($date) > 6) {
      [$day, $month, $year, $year2] = str_split($date, 2);
      $date = $year2.$month.$day;
    }
    $dom->addAttribute($birthTime, "value", $date);

  }
}
