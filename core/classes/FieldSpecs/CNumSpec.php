<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * Integer value
 */
class CNumSpec extends CMbFieldSpec
{
    private const MIN_UNSIGNED_INT = 0;

    private const MIN_SIGNED_INT = -(2 ** 31);
    private const MAX_SIGNED_INT = (2 ** 31) - 1;
    private const MAX_UNSIGNED_INT = (2 ** 32) - 1;

    private const MIN_SIGNED_BIGINT = -(2 ** 63);
    private const MAX_SIGNED_BIGINT = (2 ** 63) - 1;
    private const MAX_UNSIGNED_BIGINT = (2 ** 64) - 1;

    public $min;
    public $max;
    public $pos;
    public $length;
    public $minLength;
    public $maxLength;
    public $byteUnit;

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "num";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        $type_sql = 'INT(11)';

        $pos = $this->isStrictlyPositive();

        // No predefined limit
        if (!$pos && (($this->max === null || $this->min === null))) {
            return $type_sql;
        }

        // Integer is strictly positive
        if ($pos) {
            if ($this->max !== null) {
                $type_sql = 'TINYINT(4)';

                switch (true) {
                    case ($this->max >= pow(2, 32)):
                        $type_sql = 'BIGINT(20)';
                        break;

                    case ($this->max >= pow(2, 16)):
                        $type_sql = 'INT(11)';
                        break;

                    case ($this->max >= pow(2, 8)):
                        $type_sql = 'MEDIUMINT(9)';
                        break;

                    default:
                }
            }

            return "{$type_sql} UNSIGNED";
        }

        // Integer is not strictly positive and we have a limit somewhere
        if ($this->min === null) {
            $range = abs($this->max) * 2;
        } elseif ($this->max === null) {
            $range = abs($this->min) * 2;
        } else {
            $range = max(abs($this->min) * 2, abs($this->max) * 2);
        }

        $type_sql = 'TINYINT(4)';

        switch (true) {
            case ($range >= (pow(2, 32))):
                return 'BIGINT(20)';

            case ($range >= (pow(2, 16))):
                return 'INT(11)';

            case ($range >= (pow(2, 8))):
                return 'MEDIUMINT(9)';

            default:
        }

        return $type_sql;
    }

    /**
     * Tells if a numeric spec is strictly positive
     *
     * @return bool
     */
    function isStrictlyPositive()
    {
        return ($this->pos || ($this->min !== null && $this->min >= 0));
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'min'       => 'num',
                'max'       => 'num',
                'pos'       => 'bool',
                'length'    => 'num',
                'minLength' => 'num',
                'maxLength' => 'num',
                'byteUnit'  => 'str',
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        if (!isset($this->byteUnit)) {
            return parent::getValue($object, $params);
        }

        $propValue = $object->{$this->fieldName};
        $ratio     = 1;

        switch ($this->byteUnit) {
            case "GB":
                $ratio *= 1024;
            case "MB":
                $ratio *= 1024;
            case "KB":
                $ratio *= 1024;
            case "B":
            default:
        }

        return CMbString::toDecaBinary($propValue * $ratio);
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $propValue = CMbFieldSpec::checkNumeric($object->{$this->fieldName});

        if ($propValue === null) {
            return "N'est pas une chaîne numérique";
        }

        // pos
        if ($this->pos && $propValue <= 0) {
            return "Doit avoir une valeur positive";
        }

        // min
        if ($this->min) {
            if (!$min = CMbFieldSpec::checkNumeric($this->min)) {
                trigger_error("Spécification de minimum numérique invalide (min = $this->min)", E_USER_WARNING);

                return "Erreur système";
            }
            if ($propValue < $min) {
                return "Doit avoir une valeur minimale de $min";
            }
        }

        // max
        if ($this->max) {
            $max = CMbFieldSpec::checkNumeric($this->max);
            if ($max === null) {
                trigger_error("Spécification de maximum numérique invalide (max = $this->max)", E_USER_WARNING);

                return "Erreur système";
            }
            if ($propValue > $max) {
                return "Doit avoir une valeur maximale de $max";
            }
        }

        // length
        if ($this->length) {
            if (!$length = $this->checkLengthValue($this->length)) {
                trigger_error("Spécification de longueur invalide (longueur = $this->length)", E_USER_WARNING);

                return "Erreur système";
            }
            if (strlen($propValue) != $length) {
                return "N'a pas la bonne longueur (longueur souhaité : $length)'";
            }
        }

        // minLength
        if ($this->minLength) {
            if (!$length = $this->checkLengthValue($this->minLength)) {
                trigger_error(
                    "Spécification de longueur minimale invalide (longueur = $this->minLength)",
                    E_USER_WARNING
                );

                return "Erreur système";
            }
            if (strlen($propValue) < $length) {
                return "N'a pas la bonne longueur (longueur minimale souhaitée : $length)'";
            }
        }

        // maxLength
        if ($this->maxLength) {
            if (!$length = $this->checkLengthValue($this->maxLength)) {
                trigger_error(
                    "Spécification de longueur maximale invalide (longueur = $this->maxLength)",
                    E_USER_WARNING
                );

                return "Erreur système";
            }
            if (strlen($propValue) > $length) {
                return "N'a pas la bonne longueur (longueur maximale souhaitée : $length)'";
            }
        }

        //        try {
        //            $this->checkValueLimit($propValue);
        //        } catch (CMbException $e) {
        //            return $e->getMessage();
        //        }

        return null;
    }

    /**
     * Todo: Check cases when DB spec is hard-coded.
     * Raise an Exception if given value is lower or greater than DB limit.
     *
     * @param mixed $value
     *
     * @throws CMbException
     */
    public function checkValueLimit($value): void
    {
        // NULL and "" cases
        if (!$value) {
            return;
        }

        $strictly_positive = $this->isStrictlyPositive();
        $big_int           = $this->isBigInteger();

        $min = self::MIN_SIGNED_INT;
        $max = self::MAX_SIGNED_INT;

        if ($big_int) {
            $min = self::MIN_SIGNED_BIGINT;
            $max = self::MAX_SIGNED_BIGINT;
        }

        if ($strictly_positive) {
            $min = self::MIN_UNSIGNED_INT;
            $max = self::MAX_UNSIGNED_INT;

            if ($big_int) {
                $max = self::MAX_UNSIGNED_BIGINT;
            }
        }

        if ($value < $min) {
            throw new CMbException(
                'CNumSpec-error-Value cannot be lower than: %s',
                number_format($min, 0, '.', ' ')
            );
        }

        if ($value > $max) {
            throw new CMbException(
                'CNumSpec-error-Value cannot exceed: %s',
                number_format($max, 0, '.', ' ')
            );
        }
    }

    private function isBigInteger(): bool
    {
        $big_int = ($this->max && ($this->max > self::MAX_SIGNED_INT))
            || ($this->min && ($this->min < self::MIN_SIGNED_INT));

        if ($this->isStrictlyPositive()) {
            $big_int = ($this->max && ($this->max > self::MAX_UNSIGNED_INT))
                || ($this->min && ($this->min < self::MIN_UNSIGNED_INT));
        }

        return $big_int;
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);
        $propValue =& $object->{$this->fieldName};

        if ($this->length) {
            $propValue = self::randomString(CMbFieldSpec::$nums, $this->length);
        } elseif ($this->minLength) {
            $propValue = self::randomString(CMbFieldSpec::$nums, max($this->minLength, $this->_defaultLength));
        } elseif ($this->maxLength) {
            $propValue = self::randomString(CMbFieldSpec::$nums, min($this->maxLength, $this->_defaultLength));
        } elseif ($this->max || $this->min) {
            $min       = $this->min !== null ? $this->min : 0;
            $max       = $this->max !== null ? $this->max : 999999;
            $propValue = rand($min, $max);
        } else {
            $propValue = self::randomString(CMbFieldSpec::$nums, $this->_defaultLength);
        }
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $form       = CMbArray::extract($params, "form");
        $increment  = CMbArray::extract($params, "increment");
        $showPlus   = CMbArray::extract($params, "showPlus");
        $deferEvent = CMbArray::extract($params, "deferEvent");
        $bigButtons = CMbArray::extract($params, "bigButtons");
        $readonly   = CMbArray::get($params, "readonly");
        $name       = CMbArray::get($params, 'name');

        $field = CMbString::htmlSpecialChars($this->fieldName);

        $name = $name ?: $field;

        $min = CMbArray::extract($params, "min");
        if ($min === null) {
            $min = CMbFieldSpec::checkNumeric($this->min);
        }

        $max = CMbArray::extract($params, "max");
        if ($max === null) {
            $max = CMbFieldSpec::checkNumeric($this->max);
        }

        $new_value = CMbArray::extract($params, "value");
        if ($new_value !== null) {
            $value = $new_value;
        }

        $step = CMbArray::extract($params, "step");
        $step = CMbFieldSpec::checkNumeric($step);

        CMbArray::defaultValue($params, "size", 4);

        if ($form && $increment && !$readonly) {
            $sHtml = $this->getFormElementText(
                $object,
                $params,
                (($value >= 0 && $showPlus) ? '+' : '') . (($value == 0 && $showPlus) ? '0' : $value),
                $className,
                $showPlus ? "text" : "number"
            );
            $sHtml .= '
    <script type="text/javascript">
      Main.add(function(){
        var element = $(document.forms["' . $form . '"]["' . $name . '"]);
        
        if ($(element.form).isReadonly()) {
          return;
        }
        
        element.addSpinner({';

            if ($step) {
                $sHtml .= "step: $step,";
            }
            if ($this->pos) {
                $sHtml .= "min: 0,";
            } elseif (isset($min)) {
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

            $sHtml .= '_:0 // IE rules
        });
      });
    </script>';
        } else {
            $sHtml = $this->getFormElementText($object, $params, $value, $className, $this->mask ? 'text' : 'number');
        }

        return $sHtml;
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        $literral = "Nombre entier";

        if ($this->length) {
            $length   = $this->length;
            $literral .= " de $length chiffres";
        }

        if ($this->max || $this->min) {
            $literral .= "(";
            if ($this->max !== null) {
                $max      = $this->max;
                $literral .= " < $max";
            }

            if ($this->min !== null) {
                $min      = $this->min;
                $literral .= ", > $min";
            }
            $literral .= ")";
        }

        return "$literral. " . parent::getLitteralDescription();
    }
}
