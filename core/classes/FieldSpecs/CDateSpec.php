<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbRange;
use Ox\Core\CValue;

/**
 * Date type : DD-MM-YYYY
 */
class CDateSpec extends CMbFieldSpec {
  public $progressive;

  public $keywords = ['current', 'now'];

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "date";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return "DATE";
  }

  /**
   * @inheritdoc
   */
  function getOptions() {
    return [
        'progressive' => 'bool',
      ] + parent::getOptions();
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = []) {
    $propValue = $object->{$this->fieldName};
    $format    = CValue::first(@$params["format"], CAppUI::conf("date"));

    if (!$propValue || $propValue === "0000-00-00") {
      return "";
    }

    return ($this->progressive ? $this->progressiveFormat($propValue) : CMbDT::format($propValue, $format));
  }

  /**
   * Format a progressive date
   *
   * @param string $value The date
   *
   * @return string
   */
  function progressiveFormat($value) {
    $parts = explode('-', $value);

    return (intval($parts[2]) ? $parts[2] . '/' : '') . (intval($parts[1]) ? $parts[1] . '/' : '') . $parts[0];
  }

  /**
   * @inheritdoc
   */
  function checkParams($object) {
    $propValue = &$object->{$this->fieldName};
    if (in_array($propValue, $this->keywords)) {
      $propValue = CMbDT::date();
    }

    return parent::checkParams($object);
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object) {
    $propValue = &$object->{$this->fieldName};

    // Vérification du format
    $matches = [];
    if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $propValue, $matches)) {
      return "Format de date invalide : '$propValue'";
    }

    // Mois grégorien
    $mois = intval($matches[2]);

    // Possibilité de mettre des mois vides ()
    if (!CMbRange::in($mois, $this->progressive ? 0 : 1, 12)) {
      return "Mois '$mois' non compris entre 1 et 12 ('$propValue')";
    }

    // Jour grégorien
    $jour = intval($matches[3]);
    if (!CMbRange::in($jour, $this->progressive ? 0 : 1, 31)) {
      return "Jour '$jour' non compris entre 1 et 31 ('$propValue')";
    }

    if (!$this->progressive && !CMbDT::isLunarDate($propValue) && !CMbDT::isDateValid($propValue)) {
      return "La date '{$propValue}' est invalide";
    }

    return null;
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
    return $this->getFormElementDateTime($object, $params, $value, $className, CAppUI::conf("date"));
  }

  /**
   * @inheritdoc
   */
  function getLitteralDescription(): string
  {
    return "Date au format : 'YYYY-MM-DD'. " . parent::getLitteralDescription();
  }
}
