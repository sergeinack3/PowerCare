<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbString;
use Ox\Interop\Hl7\CHL7v2Message;

/**
 * ER7 string (HL7v2 message)
 */
class CER7Spec extends CTextSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "er7";
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
  function getHtmlValue($object, $params = array()) {
    return $this->getValue($object, $params);
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $value = $object->{$this->fieldName};
    
    if (isset($params["advanced"]) && $params["advanced"]) {
      $message = new CHL7v2Message();
      $message->parse($value);
      return $message->flatten(true);
    }
    
    return CMbString::highlightCode("er7", $value);
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true){
    $object->{$this->fieldName} = 
      "MSH|^~\&|MYSENDER|MYRECEIVER|MYAPPLICATION||200612211200||QRY^A19|1234|P|2.5\n".
      "QRD|200612211200|R|I|GetPatient|||1^RD|0101701234|DEM||\n";
  }
}
