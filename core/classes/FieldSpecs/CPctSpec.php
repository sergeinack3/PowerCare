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

/**
 * Percentage value
 */
class CPctSpec extends CFloatSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "pct";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "FLOAT";
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    $propValue = CMbFieldSpec::checkNumeric($object->{$this->fieldName}, false);
    if ($propValue === null) {
      return "N'est pas une valeur décimale";
    }
    
    if (!preg_match("/^-?([0-9]+)(\.[0-9]{0,4})?$/", $propValue)) {
      return "N'est pas un pourcentage";
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $decimals = CMbArray::extract($params, "decimals");
    return number_format($object->{$this->fieldName}, ($decimals ? $decimals : 2), ',', ' ').' %';
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className){
    CMbArray::defaultValue($params, "size", 6);
    return parent::getFormHtmlElement($object, $params, $value, $className)."%";
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);
    $object->{$this->fieldName} = rand(0, 100);
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string {
    return "Nombre réel . " . parent::getLitteralDescription();
  }
}
