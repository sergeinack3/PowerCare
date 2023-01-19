<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * Email value
 */
class CEmailSpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "email";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "VARCHAR(50)";
  }

  /**
   * @inheritdoc
   */
  function getHtmlValue($object, $params = array()) {
    $propValue = $object->{$this->fieldName};
    
    return ($propValue !== null && $propValue !== "") ? 
      "<a class=\"email\" href=\"mailto:$propValue\">$propValue</a>" : 
      "";
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    if (!CMbString::checkEmailFormat($object->{$this->fieldName})) {
      return "Le format de l'email n'est pas valide";
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className){
    $field = CMbString::htmlSpecialChars($this->fieldName);
    $value = CMbString::htmlSpecialChars($value);
    $class = CMbString::htmlSpecialChars("$className $this->prop");
    $name  = CMbArray::extract($params, 'name');
    
    $form  = CMbArray::extract($params, "form");
    $extra = CMbArray::makeXmlAttributes($params);
    $name  = $name ?: $field;
    
    return "<input type=\"email\" name=\"$name\" value=\"$value\" class=\"$class styled-element\" $extra />";
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);
    $object->{$this->fieldName} = "noone@nowhere.com";
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string
  {
    return "Courriel au format : 'XXXXX@XXXXXX.XXXX'. " . parent::getLitteralDescription();
  }
}
