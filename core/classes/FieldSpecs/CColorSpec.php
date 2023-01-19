<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * Colors spec (6 hex chars)
 */
class CColorSpec extends CMbFieldSpec {

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "color";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "VARCHAR (6)";   // hex value (FF0000)
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className){
    $field        = CMbString::htmlSpecialChars($this->fieldName);
    $form         = CMbArray::extract($params, "form");
    $name         = CMbArray::extract($params, 'name');
    $extra        = CMbArray::makeXmlAttributes($params);
    $readonly     = CMbArray::extract($params, "readonly");
    $spec         = $object->_specs[$field];

    $value = (!$value && ($this->notNull || $this->default)) ? $this->default : $value;

    $name = $name ?: $field;

    $sHtml = "<input type='hidden' name='$name' value='$value' class='$spec' $extra />";

    if ($form && !$readonly) {
      $js_params = "";
      if (!$this->notNull) {
        $js_params = "allowEmpty: true,";
      }

      $sHtml .= "<script>
        Main.add(function(){
          var e = getForm('".$form."').elements['".$name."'];
          e.colorPicker({
            $js_params
            change: function(color) {
              \$V(this, color ? color.toHex() : '');
            }.bind(e)
          });
        });
      </script>";
    }
    return $sHtml;
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true){
    parent::sample($object, $consistent);

    $object->{$this->fieldName} = sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255));
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $field = $this->fieldName;

    if ($object->$field) {
      return "<div style=\"background-color: #{$object->$field}; min-width:30px; height:1em; border: solid 1px #afafaf;\"></div>";
    }

    return CAppUI::tr("Undefined");
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string {
    return "Code couleur hexadécimal #xxxxxx . ". parent::getLitteralDescription();
  }

  /**
   * Return a font color following the hexa color given (background)
   *
   * @param string $hex_value Hex color value
   *
   * @return float
   */
  static function get_text_color($hex_value) {
    static $cache = array();

    if (!$hex_value) {
      return 0;
    }

    if (isset($cache[$hex_value])) {
      return $cache[$hex_value];
    }

    $hex = ltrim($hex_value, '#');
    $c_r = hexdec(substr($hex, 0, 2));
    $c_g = hexdec(substr($hex, 2, 2));
    $c_b = hexdec(substr($hex, 4, 2));

    return $cache[$hex_value] = (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;
  }

}
