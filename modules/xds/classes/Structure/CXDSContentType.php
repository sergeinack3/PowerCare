<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe classification représentant la variable ContentType de RegistryPackage
 */
class CXDSContentType extends CXDSClassification {
  /** @var CXDSSlot */
  public $codingScheme;

  /** @var CXDSName */
  public $contentTypeCodeDisplayName;

  /**
   * Génération de l'instance
   *
   * @param String $id               String
   * @param String $classifiedObject String
   * @param String $contentType      String
   */
  function __construct($id, $classifiedObject, $contentType) {
    parent::__construct($id);
    $this->classificationScheme = "urn:uuid:aa543740-bdda-424e-8c96-df4873be8500";
    $this->classifiedObject = $classifiedObject;
    $this->nodeRepresentation = $contentType;
  }

  /**
   * Setter ContentTypeCodeDisplayName
   *
   * @param String $value String
   *
   * @return void
   */
  function setContentTypeCodeDisplayName($value) {
    $this->contentTypeCodeDisplayName = new CXDSName($value);
  }

  /**
   * Setter CodingScheme
   *
   * @param String[] $value String[]
   *
   * @return void
   */
  function setCodingScheme($value) {
    $this->codingScheme = new CXDSSlot("codingScheme", $value);
  }

  /**
   * @inheritdoc
   */
  function toXML($submissionSet = false) {
    $xml = parent::toXML($submissionSet);

    if ($this->codingScheme) {
      $xml->importDOMDocument($xml->documentElement, $this->codingScheme->toXML());
    }

    if ($this->contentTypeCodeDisplayName) {
      $xml->importDOMDocument($xml->documentElement, $this->contentTypeCodeDisplayName->toXML());
    }


    return $xml;
  }

}
