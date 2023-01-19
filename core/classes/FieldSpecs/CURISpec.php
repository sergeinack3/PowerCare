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
 * URI
 */
class CURISpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "uri";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "VARCHAR(255)";
  }

  /**
   * @inheritdoc
   */
  function getHtmlValue($object, $params = array()) {
    $propValue = $object->{$this->fieldName};
    
    return ($propValue !== null && $propValue !== "") ? 
      "<a class=\"inline-url\" target=\"_blank\" href=\"$propValue\">$propValue</a>" :
      "";
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    $regex = "@^(\w+):///?(\w+:{0,1}\w*\@)?(\S+)(:[0-9]+)?(/|/([\w#!:.?+=&%\@!-/]))?$@i";
    if (!preg_match($regex, $object->{$this->fieldName})) {
      return "Le format de l'URI n'est pas valide";
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

    return "<input type=\"url\" name=\"$name\" value=\"$value\" class=\"$class styled-element\" $extra />";
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);
    $object->{$this->fieldName} = "telnet://mediboard.org";
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string
  {
    return "Chaine de caract�re de type uri'. " . parent::getLitteralDescription();
  }
}
