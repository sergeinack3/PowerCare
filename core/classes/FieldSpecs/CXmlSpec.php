<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * XML code
 */
class CXmlSpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "xml";
  }  

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return "MEDIUMTEXT";
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className){
    return $this->getFormElementTextarea($object, $params, $value, $className);
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    return CMbString::highlightCode("xml", $object->{$this->fieldName});
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true){
    $object->{$this->fieldName} = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<note>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
  <body>Don't forget me this weekend!</body>
</note>
XML;
  }

  /**
   * Get the litteral description of the spec
   *
   * @return string
   */
  public function getLitteralDescription(): string
  {
    return "texte formaté en xml'. " . parent::getLitteralDescription();
  }
}
