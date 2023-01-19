<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use DOMElement;
use DOMNode;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;
use Ox\Interop\InteropResources\CInteropResources;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\Xds\CXDSXmlDocument;

/**
 * Classe représente la racine de l?échange des métadonnées XDS
 */
class CXDSRegistryObjectList implements IShortNameAutoloadable {
  /** @var CXDSAssociationOld[] */
  public $association = array();
  /** @var CXDSRegistryPackage[] */
  public $registryPackage = array();
  /** @var CXDSExtrinsicObject[] */
  public $extrinsicObject = array();

  /**
   * Setter Association
   *
   * @param CXDSAssociationOld $association CXDSAssociation
   *
   * @return void
   */
  function appendAssociation($association) {
    array_push($this->association, $association);
  }

  /**
   * Setter RegistryPackage
   *
   * @param CXDSRegistryPackage $registry CXDSRegistryPackage
   *
   * @return void
   */
  function appendRegistryPackage($registry) {
    array_push($this->registryPackage, $registry);
  }

  /**
   * Setter ExtrinsicObject
   *
   * @param CXDSExtrinsicObject $extrinsic CXDSExtrinsicObject
   *
   * @return void
   */
  function appendExtrinsicObject($extrinsic) {
    array_push($this->extrinsicObject, $extrinsic);
  }

  /**
   * Génération du xml
   *
   * @param array  $metadata     metadata
   * @param String $id_extrinsic id_extrinsic
   * @param String $uuid         uuid
   * @param String $masquage     masquage
   *
   * @return CXDSXmlDocument
   */
  function toXML($metadata = null, $id_extrinsic = null, $uuid = null, $masquage = null) {
    $xml = new CXDSXmlDocument();
    $xml->createRegistryObjectListRoot();
    $base_xml = $xml->documentElement;
    foreach ($this->registryPackage as $_registryPackage) {
      $xml->importDOMDocument($base_xml, $_registryPackage->toXML());
    }

    foreach ($this->extrinsicObject as $_extrinsicObject) {
      $xml->importDOMDocument($base_xml, $_extrinsicObject->toXML());
    }

    foreach ($this->association as $_association) {
      $xml->importDOMDocument($base_xml, $_association->toXML());
    }

    // Cas ou on injecte l'extrisicObject que l'on a récupéré des métadonnées
    if ($metadata) {
      /** @var DOMElement $extrinsicNode */
      $extrinsicNode = CMbArray::get($metadata, "extrinsicNode");

      // On remplace les attributs par les bonnes valeurs
      $extrinsicNode->setAttribute("id", $id_extrinsic);
      $extrinsicNode->setAttribute("lid", $uuid);

      // Dans le cas d'un masquage, on supprime tous les masques s'il y en a (sauf celui avec la valeur "N")
      $this->deleteMasquageNode($extrinsicNode);

      // Ajout du masquage sur l'extrinsicObject
      if ($masquage != "empty") {
        $this->createMasquageNode($masquage, $extrinsicNode, $xml);
      }

      $extrinsicNode = $xml->importNode(CMbArray::get($metadata, "extrinsicNode"), true);
      $xml->documentElement->appendChild($extrinsicNode);
    }

    $xml->createSubmitObjectsRequestRoot();

    return $xml;
  }

  /**
   * Delete masquage node
   *
   * @param $extrinsicNode
   *
   * @return void
   */
  function deleteMasquageNode($extrinsicNode) {
    $nodes_to_remove = array();
    /** @var DOMNode $_child_node */
    foreach ($extrinsicNode->childNodes as $_child_node) {
      if ($_child_node->nodeName != "ns4:Classification") {
        continue;
      }

      foreach ($_child_node->attributes as $_attribute) {
        if ($_attribute->name != "classificationScheme") {
          continue;
        }

        if ($_attribute->value != "urn:uuid:f4f85eac-e6cb-4883-b524-f2705394840f") {
          continue;
        }

        // Ici, on sait qu'on est sur un noeud de masquage
        // Avant de supprimer ce noeud, on vérifie la valeur de l'attribut "nodeRepresentation"
        foreach ($_child_node->attributes as $_attribute_node_representation) {
          if ($_attribute_node_representation->name != "nodeRepresentation") {
            continue;
          }

          // Ce noeud est à supprimer (on met le noeud dans une liste parce qu'on ne peut pas supprimer un noeud dans un foreach comme ça
          if ($_attribute_node_representation->value != "N") {
            $nodes_to_remove[] = $_child_node;
          }
        }
      }
    }

    // Suppression des noeuds de masquage
    foreach ($nodes_to_remove as $_node_to_remove) {
      $_node_to_remove->parentNode->removeChild($_node_to_remove);
    }
  }

  /**
   * Create masquage DomNode for extrinsicObject
   *
   * @param String     $masquage      masquage
   * @param DOMElement $extrinsicNode extrinsicNode
   * @param CXDSXmlDocument $xml extrinsicNode
   *
   * @return void
   */
  function createMasquageNode($masquage, $extrinsicNode, $xml) {
    foreach ($extrinsicNode->childNodes as $_child_node) {
      if ($_child_node->nodeName != "ns4:Classification") {
        continue;
      }

      foreach ($_child_node->attributes as $_attribute) {
        if ($_attribute->name != "classificationScheme") {
          continue;
        }

        if ($_attribute->value != "urn:uuid:f4f85eac-e6cb-4883-b524-f2705394840f") {
          continue;
        }

        // Ici, on sait qu'on est sur un noeud de masquage
        // Avant de supprimer ce noeud, on vérifie la valeur de l'attribut "nodeRepresentation"
        foreach ($_child_node->attributes as $_attribute_node_representation) {
          if ($_attribute_node_representation->name != "nodeRepresentation") {
            continue;
          }

          // On dupplique ce noeud
          if ($_attribute_node_representation->value == "N") {
            /** @var DOMNode $_child_node */
            $node_masquage = $_child_node->cloneNode(true);

            $code_masquage = null;
            switch ($masquage) {
              case "0":
                $code_masquage = "MASQUE_PS";
                break;
              case "1":
                $code_masquage = "INVISIBLE_PATIENT";
                break;
              case "2":
                $code_masquage = "INVISIBLE_REPRESENTANTS_LEGAUX";
                break;
              default:
            }
            $infos_masquage = $this->getMasquage($code_masquage);

            // Changement des informations concernant le masque
            foreach ($node_masquage->attributes as $_attribute) {
              if ($_attribute->name != "nodeRepresentation") {
                continue;
              }
              $_attribute->value = CMbArray::get($infos_masquage, "code");
            }

            foreach ($node_masquage->childNodes as $_child_node) {
              if ($_child_node->nodeName == "ns4:Slot") {
                foreach ($_child_node->childNodes as $_child_node_slot) {
                  if ($_child_node_slot->nodeName != "ns4:ValueList") {
                    continue;
                  }
                  foreach ($_child_node_slot->childNodes as $_child_node_value_list) {
                    if ($_child_node_value_list->nodeName != "ns4:Value") {
                      continue;
                    }
                    $_child_node_value_list->nodeValue = CMbArray::get($infos_masquage, "codeSystem");
                  }
                }
              }
              elseif ($_child_node->nodeName == "ns4:Name") {
                foreach ($_child_node->childNodes as $_child_node_localize_string) {
                  if ($_child_node_localize_string->nodeName != "ns4:LocalizedString") {
                    continue;
                  }

                  foreach ($_child_node_localize_string->attributes as $_attribute) {
                    if ($_attribute->name != "value") {
                      continue;
                    }
                    $_attribute->value = utf8_encode(CMbArray::get($infos_masquage, "displayName"));
                  }
                }
              }
            }
            $extrinsicNode->insertBefore($node_masquage);
          }
        }
      }
    }
  }

  /**
   * Retourne les informations du masquage
   *
   * @param String $code Code
   *
   * @throws
   * @return array
   */
  function getMasquage($code) {
    return CANSValueSet::loadEntries("confidentialityCode", $code);
  }
}
