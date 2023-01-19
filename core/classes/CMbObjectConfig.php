<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Object config
 */
class CMbObjectConfig extends CMbObject {
  /**
   * Load object config
   *
   * @return CMbObjectConfig
   */
  function loadRefObject() {
    return $this->loadFwdRef("object_id");
  }
  
  /**
   * Export object config
   *
   * @return CMbXMLDocument
   */
  function exportXMLConfigValues(){
    $doc = new CMbXMLDocument();
    $root = $doc->addElement($doc, $this->_class);
    
    foreach ($this->getConfigValues() as $key => $value) {
      $node = $doc->addElement($root, "entry");
      $node->setAttribute("config", $key);
      $node->setAttribute("value", $value);
    }
    
    return $doc;
  }
  
  /**
   * Import object config
   *
   * @return void
   */
  function importXMLConfigValues(){
  }
}
  