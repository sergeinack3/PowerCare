<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;


use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;


/**
 * Susceptible de gérer les dates de naissance non grégorienne
 * au format pseudo ISO : YYYY-MM-DD mais avec potentiellement :
 *  MM > 12
 *  DD > 31
 */
class CBirthDateSpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "birthDate";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return "CHAR(10)";
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = []) {
    $propValue = $object->{$this->fieldName};

    if (!$propValue || $propValue === "0000-00-00") {
      return "";
    }

    return parent::getValue($object, $params);
  }

  /**
   * @inheritdoc
   */
  public function checkProperty($object) {
    $value = $object->{$this->fieldName};

    if (!preg_match("/^([0-9]{4})-((?!00)[0-9]{1,2})-((?!00)[0-9]{1,2})$/", $value ?? '', $match)) {
      return "Format de date invalide";
    }

    if ($match[1] < 1850) {
      return "Année inférieure à 1850";
    }

    if (!CMbDT::isLunarDate($value) && !CMbDT::isDateValid($value)) {
      return "La date '{$value}' est invalide";
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function getPropSuffix() {
    return "mask|99/99/9999 format|$3-$2-$1";
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);

    $object->{$this->fieldName} = CMbDT::getRandomDate('1970-01-01', CMbDT::date(), 'Y-m-d');
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className) {
    $maxLength = 10;
    CMbArray::defaultValue($params, "size", $maxLength);
    CMbArray::defaultValue($params, "maxlength", $maxLength);

    return $this->getFormElementText($object, $params, $value, $className);
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string {
    return "Date de naissance au format : 'YYYY-MM-DD', accepte les mois lunaires." . parent::getLitteralDescription();
  }
}
