<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DOMDocument;
use DOMXPath;

class CMbXMLSchema extends CMbXMLDocument {
  function addSchemaPart($filePath) {
    $schemaPart = new DOMDocument();
    $schemaPart->load($filePath);
    
    // Select all child elements of schemaPart XML
    // And pump them into main schema
    $xpath = new DOMXPath($schemaPart);
    foreach ($xpath->query('/*/*') as $node) {
      $element = $this->importNode($node, true);
      $this->documentElement->appendChild($element);
    }
  }

  function importSchemaPackage($dirPath) {
    foreach (glob("$dirPath/*.xsd") as $fileName) {
      $this->addSchemaPart($fileName);
    }
  }
  
  function purgeIncludes() {
    $xpath = new DOMXPath($this);
    foreach ($xpath->query('/*/xsd:import | /*/xsd:include') as $node) {
      $node->parentNode->removeChild($node);
    }
  }
}
