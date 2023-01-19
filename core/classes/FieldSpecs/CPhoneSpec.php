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
 * Phone number
 */
class CPhoneSpec extends CMbFieldSpec {
  public $callable;

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "phone";
  }

  /**
   * @see parent;;getOptions()
   */
  function getOptions() {
    return array(
      'callable' => 'bool',
    ) + parent::getOptions();
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return "VARCHAR (20)";
  }

  /**
   * @inheritdoc
   */
  function getHtmlValue($object, $params = array()) {
    // Only works with +{country_code} formats
    if ($this->callable) {
      $value = $object->{$this->fieldName};

      return ($value !== null && $value !== "") ? "<a class='tel' href='tel:{$value}' target='_blank'>{$value}</a>" : '';
    }

    return $this->getValue($object, $params);
  }

  /**
   * Get the mask corresponding to the phone number format
   *
   * @return string
   */
  protected function getMask() {
    static $phone_number_mask = null;

    if ($phone_number_mask === null) {
      $phone_number_format = str_replace(' ', 'S', CAppUI::conf("system phone_number_format"));

      $phone_number_mask = "";

      if ($phone_number_format != "") {
        $phone_number_mask = " mask|$phone_number_format";
      }
    }

    return $phone_number_mask;
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);

    $phone_min_length = CAppUI::conf("system phone_min_length");

    $object->{$this->fieldName} = self::randomString(range(0, 9), $phone_min_length);
  }

  /**
   * @inheritdoc
   */
  function getPropSuffix() {
    $phone_min_length = CAppUI::conf("system phone_min_length");
    return "pattern|\d{".$phone_min_length.",}" . $this->getMask();
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className) {
    $field = CMbString::htmlSpecialChars($this->fieldName);
    $value = CMbString::htmlSpecialChars($value);
    $class = CMbString::htmlSpecialChars("$className $this->prop");
    $name  = CMbArray::extract($params, 'name');

    $form  = CMbArray::extract($params, "form");
    $extra = CMbArray::makeXmlAttributes($params);
    $name  = $name ?: $field;

    return "<input type=\"tel\" name=\"$name\" value=\"$value\" class=\"$class styled-element\" $extra />";
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string
  {
    return "Numéro de téléphone (chiffres seulement, pas d'espaces). " . parent::getLitteralDescription();
  }
}
