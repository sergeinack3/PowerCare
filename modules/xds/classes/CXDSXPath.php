<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use DOMNode;
use DOMNodeList;
use Ox\Core\CMbXPath;

/**
 * Xpath class for XDS
 */
class CXDSXPath extends CMbXPath {

  /**
   * @see parent::__construct()
   */
  function __construct($xml) {
    parent::__construct($xml);
    $this->registerNamespace("rim", "urn:oasis:names:tc:ebxml-regrep:xsd:rim:3.0");
    $this->registerNamespace("xds", "urn:ihe:iti:xds-b:2007");
  }

  /**
   * Return the list of extrinsic objects
   *
   * @return DOMNodeList
   */
  function getExtrinsicObjects() {
    return $this->query("//rim:ExtrinsicObject");
  }

  /**
   * Return the value of unique ID
   *
   * @param DOMNode $node                 Node
   * @param String  $identificationScheme Specify the external Identifier
   *
   * @return string
   */
  public function getExternalIdentifier(DOMNode $node, string $identificationScheme) {
    return $this->queryAttributNode("rim:ExternalIdentifier[@identificationScheme='$identificationScheme']", $node, "value");
  }

  /**
   * Return the value of classification
   *
   * @param DOMNode $node                 Node
   * @param String  $classificationScheme Specify the classification
   *
   * @return string|null
   */
  public function getClassification(DOMNode $node, string $classificationScheme) {
    return $this->queryAttributNode("rim:Classification[@classificationScheme='$classificationScheme']", $node, "nodeRepresentation");
  }

    /**
     * Return the value of classification
     *
     * @param DOMNode $node                 Node
     * @param String  $classificationScheme Specify the classification
     *
     * @return string|null
     * @throws \Exception
     */
    public function getClassificationEntries(DOMNode $node, ?string $classificationScheme): array
    {
        if ($classificationScheme) {
            $classification_node =  $this->queryUniqueNode("rim:Classification[@classificationScheme='$classificationScheme']", $node);
        } else {
            $classification_node = $node;
        }
        $code = $this->queryAttributNode('.', $classification_node, "nodeRepresentation");
        $display_name = $this->getName($classification_node);
        $code_system = $this->getSlot($classification_node, 'codingScheme');

        return [
            'code' => $code,
            'codeSystem' => $code_system,
            'displayName' => $display_name
        ];
    }

  /**
   * Return the value of the slot
   *
   * @param DOMNode $node     Node
   * @param String  $name     Name of the slot
   * @param bool    $multiple Multiple value in the slot
   *
   * @return array|string|null
   */
  public function getSlot($node, $name, $multiple = false) {
    if ($multiple) {
      return $this->getMultipleTextNodes("rim:Slot[@name='$name']/rim:ValueList/rim:Value", $node);
    }
    return $this->queryTextNode("rim:Slot[@name='$name']/rim:ValueList/rim:Value", $node);
  }

  /**
   * Return the document
   *
   * @param DOMNode $extrinsicObject Node of the extrinsic object
   *
   * @return String
   */
  function getDocument($extrinsicObject) {
    $identifiant = $this->queryAttributNode(".", $extrinsicObject, "id");
    return $this->queryTextNode("//xds:Document[@id='$identifiant']");
  }

  /**
   * Return the target object on the association
   *
   * @param String $associationType association type
   * @param String $sourceObject    source object
   *
   * @return string
   */
  function getAssociationTargetObject($associationType, $sourceObject) {
    return $this->queryAttributNode("//rim:Association[@associationType='$associationType' and @sourceObject='$sourceObject']", null, "targetObject");
  }

    /**
     * @param DOMNode $node
     *
     * @return string|null
     * @throws \Exception
     */
    public function getDescription(DOMNode $node): ?string
    {
        return $this->queryAttributNode("rim:Description/rim:LocalizedString", $node, 'value');
    }

    /**
     * @param DOMNode $node
     *
     * @return string|null
     * @throws \Exception
     */
    public function getName(DOMNode $node): ?string
    {
        return $this->queryAttributNode("rim:Name/rim:LocalizedString", $node, 'value');
    }

    /**
     * @param DOMNode $node
     *
     * @return string|null
     * @throws \Exception
     */
    public function getVersionInfo(DOMNode $node): ?string
    {
        return $this->queryAttributNode("rim:VersionInfo", $node, 'versionName');
    }
}
