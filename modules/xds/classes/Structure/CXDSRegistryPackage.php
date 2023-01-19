<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Les classes XDS SubmissionSet et Folder ont pour équivalent le même élément
 * rim:RegistryPackage. La distinction entre les deux classes est effectuée par l?ajout de
 * rim:Classification à rim:RegistryPackage.
 */
class CXDSRegistryPackage extends CXDSExtrinsicPackage {
  /** @var  CXDSSlot */
  public $submissionTime;
  /** @var  CXDSLocalizedString */
  public $title;
  /** @var  CXDSContentType */
  public $contentType;
  /** @var  CXDSFolder|CXDSSubmissionSet */
  public $submissionSet;

  /**
   * Construction de l'instance
   *
   * @param String $id String
   */
  function __construct($id) {
    parent::__construct($id);
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
   * Setter SubmissionTime
   *
   * @param String[] $value String[]
   *
   * @return void
   */
  function setSubmissionTime($value) {
    $this->submissionTime = new CXDSSlot("submissionTime", $value);
  }

  /**
   * Setter SourceId
   *
   * @param String $id             String
   * @param String $registryObject String
   * @param String $value          String
   *
   * @return void
   */
  function setSourceId($id, $registryObject, $value) {
    $this->sourceId = new CXDSSourceId($id, $registryObject, $value);
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
    $this->uniqueId = new CXDSUniqueId($id, $registryObject, $value, true);
  }

  /**
   * Setter ContentType
   *
   * @param CXDSContentType $contentType CXDSContentType
   *
   * @return void
   */
  function setContentType($contentType) {
    $this->contentType = $contentType;
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
    $this->patientId = new CXDSPatientID($id, $registryObject, $value, true);
  }

  /**
   * Setter SubmissionSet
   *
   * @param String $id                 id
   * @param String $classifiedObject   classified Object
   * @param bool   $classificationNode Folder ou submissionset
   *
   * @return void
   */
  function setSubmissionSet($id, $classifiedObject, $classificationNode) {
    if ($classificationNode) {
      $this->submissionSet = new CXDSFolder($id, $classifiedObject);
      return;
    }
    $this->submissionSet = new CXDSSubmissionSet($id, $classifiedObject);
  }

  /**
   * @inheritdoc
   */
  function toXML($registry = true) {
    return parent::toXML(true);
  }
}