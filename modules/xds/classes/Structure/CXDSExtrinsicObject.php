<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Correspond au documentEntry(fiche) dans XDS
 * Une fiche appartient à un registre et représente le document stocké dans l'entrepôt. Elle
 * contient les métadonnées décrivant les caractéristiques principales d'un document stocké
 * dans l'entrepôt dont l'index (uniqueId) pour pointer vers ce document.
 */
class CXDSExtrinsicObject extends CXDSExtrinsicPackage {
  /** @var  CXDSSlot */
  public $hash;
  /** @var  CXDSSlot */
  public $size;

  public $mimeType;
  public $status;
  /** @var  CXDSSlot */
  public $creationTime;
  /** @var  CXDSSlot */
  public $languageCode;
  /** @var  CXDSSlot */
  public $legalAuthenticator;
  /** @var  CXDSSlot */
  public $serviceStartTime;
  /** @var  CXDSSlot */
  public $serviceStopTime;
  /** @var  CXDSSlot */
  public $sourcePatientId;
  /** @var  CXDSSlot */
  public $sourcePatientInfo;
  /** @var  CXDSSlot */
  public $URI;
  /** @var  CXDSLocalizedString */
  public $title;
  /** @var  CXDSSlot */
  public $documentAvailability;
  /** @var  CXDSClass */
  public $class;
  /** @var  CXDSConfidentiality[] */
  public $confidentiality = array();
  /** @var  CXDSEventCodeList[] */
  public $eventCodeList = array();
  /** @var  CXDSFormat */
  public $format;
  /** @var  CXDSHealthcareFacilityType */
  public $healthcareFacilityType;
  /** @var  CXDSPracticeSetting */
  public $practiceSetting;
  /** @var  CXDSType */
  public $type;
  /** @var  CXDSSlot */
  public $repositoryUniqueId;

  public $lid;

  /**
   * Construction de l'instance
   *
   * @param String $id       String
   * @param String $mimeType String
   * @param String $lid      String
   */
  function __construct($id, $mimeType, $status = null, $lid = null) {
    parent::__construct($id);
    $this->mimeType   = $mimeType;
    $this->objectType = "urn:uuid:7edca82f-054d-47f2-a032-9b2a5b5186c1";
    $this->status     = $status;
    $this->lid        = $lid;
  }

  /**
   * Setter PatientId
   *
   * @param String $id             String
   * @param String $registryObject String
   * @param String $value          String
   *
   * @return void
   */
  function setPatientId($id, $registryObject, $value) {
    $this->patientId = new CXDSPatientID($id, $registryObject, $value);
  }

  /**
   * Setter UniqueId
   *
   * @param String $id             String
   * @param String $registryObject String
   * @param String $value          String
   *
   * @return void
   */
  function setUniqueId($id, $registryObject, $value) {
    $this->uniqueId = new CXDSUniqueId($id, $registryObject, $value);
  }

  /**
   * Setter Class
   *
   * @param CXDSClass $class CXDSClass
   *
   * @return void
   */
  function setClass($class) {
    $this->class = $class;
  }

  /**
   * Setter Format
   *
   * @param CXDSFormat $format CXDSFormat
   *
   * @return void
   */
  function setFormat($format) {
    $this->format = $format;
  }

  /**
   * Setter HealthcareFacilityType
   *
   * @param CXDSHealthcareFacilityType $health CXDSHealthcareFacilityType
   *
   * @return void
   */
  function setHealthcareFacilityType($health) {
    $this->healthcareFacilityType = $health;
  }

  /**
   * Setter PracticeSetting
   *
   * @param CXDSPracticeSetting $practice CXDSPracticeSetting
   *
   * @return void
   */
  function setPracticeSetting($practice) {
    $this->practiceSetting = $practice;
  }

  /**
   * Setter Type
   *
   * @param CXDSType $type CXDSType
   *
   * @return void
   */
  function setType($type) {
    $this->type = $type;
  }

  /**
   * Setter Confidentiality
   *
   * @param CXDSConfidentiality $confidentiality CXDSConfidentiality
   *
   * @return void
   */
  function appendConfidentiality($confidentiality) {
    array_push($this->confidentiality, $confidentiality);
  }

  /**
   * Setter EventCodeList
   *
   * @param CXDSEventCodeList $event CXDSEventCodeList
   *
   * @return void
   */
  function appendEventCodeList($event) {
    array_push($this->eventCodeList, $event);
  }

  /**
   * Setter title
   *
   * @param String $title String
   *
   * @return void
   */
  public function setTitle($title) {
    $this->title = new CXDSName($title);
  }

  /**
   * @inheritdoc
   */
  function toXML($registry = true) {
    return parent::toXML(false);
  }
}