<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Math utilities
 */
class CMbMath {
  /**
   * Round numbers using significant digits
   *
   * @param float $number The number to round
   * @param int   $n      The significant digits to keep
   *
   * @return float
   */
  static function roundSig($number, $n = 4) {
    if ($number == 0) {
      return 0;
    }

    $d     = ceil(log10(abs($number)));
    $power = $n - $d;

    return round($number, $power);
  }

  /**
   * Get the min value from arguments or the first argument if it's an array, ignoring null and false
   *
   * @param mixed $values
   *
   * @return mixed
   */
  static function min($values) {
    $args = func_get_args();
    if (!is_array($values) || func_num_args() > 1) {
      $values = $args;
    }

    $values = array_filter(
      $values,
      function ($v) {
        return $v !== null && $v !== false;
      }
    );

    if (count($values) === 0) {
      return null;
    }

    return min($values);
  }

  /**
   * Get the max value from arguments or the first argument if it's an array, ignoring null and false
   *
   * @param mixed $values
   *
   * @return mixed
   */
  static function max($values) {
    $args = func_get_args();
    if (!is_array($values) || func_num_args() > 1) {
      $values = $args;
    }

    $values = array_filter(
      $values,
      function ($v) {
        return $v !== null && $v !== false;
      }
    );

    if (count($values) === 0) {
      return null;
    }

    return max($values);
  }

  /**
   * Evaluates mathematical expressions, like "a+b", or "round(1 / c)", etc
   *
   * @param string $expression Expression to evaluate
   * @param array  $variables  Expression variables
   *
   * @return number
   */
  static function evaluate($expression, $variables = array()) {
    $customOpsVars = array(
      "Min" => CMbDT::SECS_PER_MINUTE,
      "H"   => CMbDT::SECS_PER_HOUR,
      "J"   => CMbDT::SECS_PER_DAY,
      "Sem" => CMbDT::SECS_PER_WEEK,
      "M"   => CMbDT::SECS_PER_MONTH,
      "A"   => CMbDT::SECS_PER_YEAR,
    );

    $customOps = CMbArray::get(self::getCustomOps(), 0);

    $executor = new \NXP\MathExecutor();

    // Time ops
    foreach ($customOpsVars as $_key => $_seconds) {
      $divisor = $_seconds;

      $executor->addFunction(
        $_key,
        function ($ms) use ($divisor) {
          $timestampLimit = 50000;
          $divisor        *= 1000;

          // Absolute timestamp
          if (abs($ms) > $timestampLimit) {
            return ceil($ms / $divisor);
          }

          // Relative timestamp
          return ceil($ms * $divisor);
        }
      );
    }

    // Math ops
    foreach ($customOps as $_func) {
      $executor->addFunction(
        $_func,
        function ($arg) use ($_func) {
          return $_func($arg);
        }
      );
    }

    // add Math pow
    $executor->addFunction("pow", "pow", 2);

    $executor->setVars($variables);

    return $executor->execute($expression);
  }

  /**
   * Get name of custom functions managed by executor
   * Index of key array is for number of parameters
   *
   * @return array
   */
  static function getCustomOps() {
    return array(
      array("sqrt", "log", "exp", "abs", "ceil", "floor", "round"),
      array("pow")
    );
  }

  /**
   * @from https://gist.github.com/will83/5920606
   *
   * @param float $x Lambert93 X coordinate
   * @param float $y Lambert93 Y coordinate
   *
   * @return array
   */
  function lambert93ToWgs84($x, $y) {
    $x         = number_format(floatval($x), 10, '.', '');
    $y         = number_format(floatval($y), 10, '.', '');
    $b6        = 6378137.0000; // Unused
    $b7        = 298.257222101;
    $b8        = 1 / $b7;
    $b9        = 2 * $b8 - $b8 * $b8;
    $b10       = sqrt($b9);
    $b13       = 3.000000000;
    $b14       = 700000.0000;
    $b15       = 12655612.0499;
    $b16       = 0.7256077650532670;
    $b17       = 11754255.426096;
    $delx      = $x - $b14;
    $dely      = $y - $b15;
    $gamma     = atan(-($delx) / $dely);
    $r         = sqrt(($delx * $delx) + ($dely * $dely));
    $latiso    = log($b17 / $r) / $b16;
    $sinphiit0 = tanh($latiso + $b10 * atanh($b10 * sin(1)));
    $sinphiit1 = tanh($latiso + $b10 * atanh($b10 * $sinphiit0));
    $sinphiit2 = tanh($latiso + $b10 * atanh($b10 * $sinphiit1));
    $sinphiit3 = tanh($latiso + $b10 * atanh($b10 * $sinphiit2));
    $sinphiit4 = tanh($latiso + $b10 * atanh($b10 * $sinphiit3));
    $sinphiit5 = tanh($latiso + $b10 * atanh($b10 * $sinphiit4));
    $sinphiit6 = tanh($latiso + $b10 * atanh($b10 * $sinphiit5));
    $longrad   = $gamma / $b16 + $b13 / 180 * pi();
    $latrad    = asin($sinphiit6);
    $long      = ($longrad / pi() * 180);
    $lat       = ($latrad / pi() * 180);

    return array(
      'lambert93' => array(
        'x' => $x,
        'y' => $y
      ),
      'wgs84'     => array(
        'lat'  => $lat,
        'long' => $long
      )
    );
  }

  /**
   * @param int $base
   * @param int $nbr
   *
   * @return bool
   */
  public static function isValidExponential(int $base, int $nbr):bool{
    $result = $nbr/$base;
    if($result === 1){
      return true;
    }

    if (is_int($result)){
      return static::isValidExponential($base, $result);
    }

    return false;
  }
}
