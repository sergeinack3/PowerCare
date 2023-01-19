<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Throwable;

/**
 * XML Reader
 */
class CImportXmlReader {
  /** @var DOMXPath */
  private $xpath;
  private $dom;
  private $file_path;

  /**
   * CImportXmlReader constructor.
   *
   * @param string $file_path Path to the file to read
   */
  public function __construct(string $file_path) {
    $this->file_path = $file_path;
  }

  /**
   * Load a file as xml
   *
   * @return bool
   */
  public function loadFileAsXml() : bool {
    if (!$this->file_path || !file_exists($this->file_path)) {
      CAppUI::setMsg('No patient file to import', UI_MSG_WARNING);

      return false;
    }

    $this->dom = new DOMDocument(null, 'UTF-8');

    $xml = file_get_contents($this->file_path);

    // Suppression des caractères invalides pour DOMDocument
    $xml = CMbString::convertHTMLToXMLEntities($xml);

    $this->dom->loadXML($xml);

    $this->xpath = new DOMXPath($this->dom);

    return true;
  }

  /**
   * Make a xpath query and return a DOMNodeList
   *
   * @param string  $pattern Query to make
   * @param DOMNode $ctx     Root node to use
   *
   * @return DOMNodeList|null
   */
  private function query(string $pattern, ?DOMNode $ctx = null) : ?DOMNodeList {
    return $this->xpath->query($pattern, $ctx);
  }

  /**
   * Get a information from the xml file using an ossiciative array
   *
   * @param array        $mapping
   * @param bool         $decode
   * @param DOMNode|null $ctx
   *
   * @return array
   */
  public function getLineFromMapping(array $mapping, bool $decode = false, ?DOMNode $ctx = null) : array {
    $query_result = [];
    foreach ($mapping as $_root => $_fields) {
      $_root_list = $this->query($_root, $ctx);
      if (!$_root_list->length) {
        continue;
      }

      for ($i = 0; $i < $_root_list->length; $i++) {
        $node_list = [];
        $_root_node = $_root_list->item($i);
        foreach ($_fields as $_xml => $_mb) {
          $_field_list = $this->query($_xml, $_root_node);

          if (!$_field_list->length) {
            $node_list[$_mb] = null;
          }
          else {
            $content = $_field_list->item(0)->nodeValue;
            // Keep 0 for ATCD
            $node_list[$_mb] = ($content !== null) ? trim($content) : null;

            if ($decode && $node_list[$_mb]) {
              $node_list[$_mb] = iconv('UTF-8', 'windows-1252//TRANSLIT', $node_list[$_mb]);
            }
          }
        }

        $query_result[] = [
          'root_node' => $_root_node,
          'node_list' => $node_list
        ];
      }
    }

    return $query_result;
  }
}