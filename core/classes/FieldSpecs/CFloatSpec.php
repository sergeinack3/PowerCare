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
use Ox\Core\CModelObject;

/**
 * Float value
 */
class CFloatSpec extends CMbFieldSpec {
  public $min;
  public $max;
  public $pos;
  public $decimals;
  public $trimZeros;

  /**
   * Tells if the float values are equal
   *
   * @param float        $value1 Value 1
   * @param float        $value2 Value 2
   * @param CMbFieldSpec $spec   Spec
   *
   * @return bool
   */
  static function equals($value1, $value2, $spec) {
    $v1_null = "$value1" === "";
    $v2_null = "$value2" === "";

    $value1 = floatval($value1);
    $value2 = floatval($value2);

    if ($v1_null xor $v2_null) {
      return false;
    }
    if ($spec instanceof CCurrencySpec) {
      $precision = isset($spec->precise) ? 5 : 3;

      return round($value1, $precision) == round($value2, $precision);
    }

    return round($value1, 2) == round($value2, 2);
  }

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "float";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return 'FLOAT' . ($this->pos || ($this->min !== null && $this->min >= 0) ? ' UNSIGNED' : '');
  }

  /**
   * @inheritdoc
   */
  public function getPHPSpec(): string {
    return parent::PHP_TYPE_FLOAT;
  }

  /**
   * @inheritdoc
   */
  function getOptions() {
    return array(
        'min'      => 'num',
        'max'      => 'num',
        'pos'      => 'bool',
        'decimals' => 'num',
      ) + parent::getOptions();
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $propValue = $object->{$this->fieldName};

    if ($propValue !== null) {
      $decimals  = CMbArray::extract($params, "decimals", $this->decimals);
      $trimZeros = CMbArray::extract($params, "trimZeros", $this->trimZeros);

      if ($decimals != null && $propValue != null) {
        $value = number_format($propValue, $decimals, ',', ' ');

        if ($trimZeros != null) {
          if (preg_match('/^\d+(?P<decimals>(,|\.)(0+))$/', $value, $match)) {
            $value = str_replace($match['decimals'], '', $value);
          }
        }

        return $value;
      }
    }

    if ($propValue && $this->mask) {
      $propValue = self::formattedToMasked($propValue, $this->mask, $this->format);
    }

    return CMbString::htmlSpecialChars($propValue);
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object) {
    $propValue = CMbFieldSpec::checkNumeric($object->{$this->fieldName}, false);
    if ($propValue === null) {
      return "N'est pas une valeur décimale";
    }

    // pos
    if ($this->pos && $propValue <= 0) {
      return "Doit avoir une valeur positive";
    }

    // min
    if ($this->min) {
      if (!$min = CMbFieldSpec::checkNumeric($this->min, false)) {
        CModelObject::warning("Specification-de-minimum-numerique-invalide-min=min%d", $this->min);

        return "Erreur système";
      }

      if ($propValue < $min) {
        return "Doit avoir une valeur minimale de $min";
      }
    }

    // max
    if ($this->max) {
      $max = CMbFieldSpec::checkNumeric($this->max, false);
      if ($max === null) {
        CModelObject::warning("Specification-de-maximum-numerique-invalide-max=max%d", $this->max);

        return "Erreur système";
      }

      if ($propValue > $max) {
        return "Doit avoir une valeur maximale de $max";
      }
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);
    $object->{$this->fieldName} = self::randomString(CMbFieldSpec::$nums, 2) . "." . self::randomString(CMbFieldSpec::$nums, 2);
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className) {
    $form         = CMbArray::extract($params, "form");
    $increment    = CMbArray::extract($params, "increment");
    $showPlus     = CMbArray::extract($params, "showPlus");
    $fraction     = CMbArray::extract($params, "fraction");
    $showFraction = CMbArray::extract($params, "showFraction");
    $deferEvent   = CMbArray::extract($params, "deferEvent");
    $bigButtons   = CMbArray::extract($params, "bigButtons");
    $readonly     = CMbArray::get($params, "readonly");
    $name         = CMbArray::get($params, 'name');

    $field = CMbString::htmlSpecialChars($this->fieldName);

    $name = $name ?: $field;

    $min = CMbArray::extract($params, "min");
    if ($min === null) {
      $min = CMbFieldSpec::checkNumeric($this->min, false);
    }

    $max = CMbArray::extract($params, "max");
    if ($max === null) {
      $max = CMbFieldSpec::checkNumeric($this->max, false);
    }

    $new_value = CMbArray::extract($params, "value");
    if ($new_value !== null) {
      $value = $new_value;
    }

    $decimals = CMbArray::extract($params, "decimals", $this->decimals);
    if ($decimals == null) {
      $decimals = isset($this->precise) ? 4 : 2;
    }

    $step = CMbArray::extract($params, "step");
    $step = CMbFieldSpec::checkNumeric($step, false);

    CMbArray::defaultValue($params, "size", 4);

    if ($form && $increment && !$readonly) {
      $sHtml = $this->getFormElementText($object, $params, (($value >= 0 && $showPlus) ? '+' : '') . (($value == 0 && $showPlus) ? '0' : $value), $className, $showPlus ? "text" : "number");
      $sHtml .= '
    <script type="text/javascript">
      Main.add(function(){
        var element = $(document.forms["' . $form . '"]["' . $name . '"]);
        
        if ($(element.form).isReadonly()) return;
        
        element.addSpinner({';

      if ($step) {
        $sHtml .= "step: $step,";
      }
      if ($decimals) {
        $sHtml .= "decimals: $decimals,";
      }
      if ($this->pos) {
        $sHtml .= "min: 0,";
      }
      elseif (isset($min)) {
        $sHtml .= "min: $min,";
      }
      if (isset($max)) {
        $sHtml .= "max: $max,";
      }
      if ($deferEvent) {
        $sHtml .= "deferEvent: true,";
      }
      if ($bigButtons) {
        $sHtml .= "bigButtons: true,";
      }
      if ($showPlus) {
        $sHtml .= "showPlus: true,";
      }
      if ($fraction) {
        $sHtml .= "fraction: true,";
      }
      if ($showFraction) {
        $sHtml .= "showFraction: true,";
      }

      $sHtml .= '_:0 // IE rules
        });
      });
    </script>';
    }
    else {
      $sHtml = $this->getFormElementText($object, $params, $value, $className, "number");
    }

    return $sHtml;
  }
}
