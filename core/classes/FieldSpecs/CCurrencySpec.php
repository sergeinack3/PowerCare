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
use Ox\Core\CMbString;

/**
 * Currency value
 */
class CCurrencySpec extends CFloatSpec {
  public $precise;

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "currency";
  }

  /**
   * @inheritdoc
   */
  function getOptions() {
    return array(
        'precise' => 'bool',
      ) + parent::getOptions();
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $value    = $object->{$this->fieldName};
    $decimals = CMbArray::extract($params, "decimals", $this->decimals);
    $empty    = CMbArray::extract($params, "empty");

    return CMbString::currency($value, $decimals, $this->precise, $empty);
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className) {
    CMbArray::defaultValue($params, "size", 6);

    return parent::getFormHtmlElement($object, $params, $value, $className) . CAppUI::conf("currency_symbol");
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    $size = $this->precise ? "12, 5" : "10, 3";

    return "DECIMAL ($size)" . ($this->pos ? " UNSIGNED" : "");
  }

  /**
   * @inheritdoc
   */
  function getPHPSpec(): string {
    return parent::PHP_TYPE_FLOAT;
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string
  {
    return "Booléen au format : '0, 1'" . parent::getLitteralDescription();
  }
}
