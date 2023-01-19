<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

/**
 * Classe classification représentant la variable class pour ExtrinsicObject
 * représente la classe du document (compte rendu, imagerie médicale, traitement, certificat,
 * etc.).
 */
class CXDSClass extends CXDSClassification {
  /** @var  CXDSSlot */
  public $codingScheme;

  /** @var  CXDSName */
  public $name;

  /**
   * Construction d'une instance
   *
   * @param String $id                 Identifiant
   * @param String $classifiedObject   ClassifiedObject
   * @param String $nodeRepresentation Noderepresentation
   */
  function __construct($id, $classifiedObject, $nodeRepresentation) {
    parent::__construct($id);
    $this->classifiedObject     = $classifiedObject;
    $this->classificationScheme = "urn:uuid:41a5887f-8865-4c09-adf7-e362475b143a";
    $this->nodeRepresentation   = $nodeRepresentation;
  }

  /**
   * Création du nom avec une instance de CXDSName
   *
   * @param String $name Valeur du nom
   *
   * @return void
   */
  function setName($name) {
    $this->name = new CXDSName($name);
  }

  /**
   * Création du codingScheme avec un CXDSSlot
   *
   * @param String[] $codingScheme CodingScheme
   *
   * @return void
   */
  function setCodingScheme($codingScheme) {
    $this->codingScheme = new CXDSSlot("codingScheme", $codingScheme);
  }

  /**
   * @inheritdoc
   */
  function toXML($submissionSet = false) {
    $xml      = parent::toXML($submissionSet);
    $base_xml = $xml->documentElement;

    if ($this->codingScheme) {
      $xml->importDOMDocument($base_xml, $this->codingScheme->toXML());
    }

    if ($this->name) {
      $xml->importDOMDocument($base_xml, $this->name->toXML());
    }
    return $xml;
  }
}