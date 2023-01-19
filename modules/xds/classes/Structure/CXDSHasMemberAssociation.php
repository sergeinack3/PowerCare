<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use Ox\Interop\Xds\CXDSXmlDocument;

/**
 * Classe Association représentant l'association HasMember
 */
class CXDSHasMemberAssociation extends CXDSAssociationOld {
  /** @var CXDSSlot */
  public $submissionSetStatus;
  /** @var CXDSSlot */
  public $previousVersion;

  /**
   * Construction de l'instance
   *
   * @param String $id           String
   * @param String $sourceObject String
   * @param String $targetObject String
   * @param bool   $sign         false
   * @param bool   $rplc         false
   */
  function __construct($id, $sourceObject, $targetObject, $sign = false, $rplc = false) {
    $associationType = null;
    if ($sign) {
      $associationType = "urn:ihe:iti:2007:AssociationType:signs";
    }
    if ($rplc) {
      $associationType = "urn:ihe:iti:2007:AssociationType:RPLC";
    }
    parent::__construct($id, $sourceObject, $targetObject, $associationType);
  }

  /**
   * Setter SubmissionsetStatus
   *
   * @param String[] $value String[]
   *
   * @return void
   */
  function setSubmissionSetStatus($value) {
    $this->submissionSetStatus = new CXDSSlot("SubmissionSetStatus", $value);
  }

  /**
   * Setter PreviousVersion
   *
   * @param String[] $value String[]
   *
   * @return void
   */
  function setPreviousVersion($value) {
    $this->previousVersion = new CXDSSlot("PreviousVersion", $value);
    $this->setSubmissionSetStatus(array("Original"));
  }

  /**
   * Génération du xml
   *
   * @return CXDSXmlDocument
   */
  function toXML() {
    $xml = parent::toXML();

    if ($this->submissionSetStatus) {
      $xml->importDOMDocument($xml->documentElement, $this->submissionSetStatus->toXML());
    }

    if ($this->previousVersion) {
      $xml->importDOMDocument($xml->documentElement, $this->previousVersion->toXML());
    }

    return $xml;
  }
}
