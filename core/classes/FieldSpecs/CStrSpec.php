<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Core\CValue;

/**
 * Short string value
 */
class CStrSpec extends CMbFieldSpec
{
    public $length;
    public $minLength;
    public $maxLength;
    public $protected;
    public $class;
    public $delimiter;
    public $canonical;

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "str";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        if ($this->maxLength) {
            return "VARCHAR ($this->maxLength)";
        }

        if ($this->length) {
            return "CHAR ($this->length)";
        }

        if ($this->class) {
            return "VARCHAR (80)";
        }

        return "VARCHAR (255)";
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'length'    => 'num',
                'minLength' => 'num',
                'maxLength' => 'num',
                'protected' => 'bool',
                'class'     => 'class',
                'delimiter' => 'num',
                'canonical' => 'bool',
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        if ($this->class) {
            return CMbString::htmlSpecialChars(CAppUI::tr($object->{$this->fieldName}));
        }

        return parent::getValue($object, $params);
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $propValue = $object->{$this->fieldName};

        // length
        if ($this->length) {
            if (!$length = $this->checkLengthValue($this->length)) {
                $msg = "Spécification de longueur invalide (longueur = '$this->length')";
                CApp::log($msg, null, UI_MSG_WARNING);
                return $msg;
            }
            if (strlen($propValue) != $length) {
                return "N'a pas la bonne longueur '$propValue' (longueur souhaitée : $length)";
            }
        }

        // minLength
        if ($this->minLength) {
            if (!$length = $this->checkLengthValue($this->minLength)) {
                $msg = "Spécification de longueur minimale invalide (longueur = '$this->minLength')";
                CApp::log($msg, null, UI_MSG_WARNING);
                return $msg;
            }
            if (strlen($propValue) < $length) {
                return "N'a pas la bonne longueur '$propValue' (longueur minimale souhaitée : $length)";
            }
        }

        // maxLength
        if ($this->maxLength) {
            if (!$length = $this->checkLengthValue($this->maxLength)) {
                $msg = "Spécification de longueur maximale invalide (longueur = '$this->maxLength')";
                CApp::log($msg, null, UI_MSG_WARNING);
                return $msg;

            }
            if (strlen($propValue) > $length) {
                return "N'a pas la bonne longueur '$propValue' (longueur maximale souhaitée : $length)";
            }
        }

        // delimiter
        if ($this->delimiter) {
            $delim  = chr(intval($this->delimiter));
            $values = explode($delim, $propValue);

            if (in_array("", $values, true)) {
                return "Contient des valeurs vides '$propValue'";
            }
        }

        // canonical
        if ($this->canonical) {
            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $propValue)) {
                return "Ne doit contenir que des chiffres et des lettres non-accentuées (pas d'espaces)";
            }
        }

        // class
        if ($this->class) {
            $object = @CModelObject::getInstance($propValue);
            if (!$object) {
                return "La classe '$propValue' n'existe pas";
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);
        $propValue =& $object->{$this->fieldName};

        $this->length = $this->length ?: (($this->minLength == $this->maxLength && $this->minLength) ? $this->minLength : null);

        if ($this->length) {
            $propValue = self::randomString(CMbFieldSpec::$chars, $this->length);
        } elseif ($this->minLength) {
            if ($this->_defaultLength < intval($this->minLength)) {
                $propValue = self::randomString(CMbFieldSpec::$chars, $this->minLength);
            } else {
                $propValue = self::randomString(CMbFieldSpec::$chars, $this->_defaultLength);
            }
        } elseif ($this->maxLength) {
            if ($this->_defaultLength > intval($this->maxLength)) {
                $propValue = self::randomString(CMbFieldSpec::$chars, $this->maxLength);
            } else {
                $propValue = self::randomString(CMbFieldSpec::$chars, $this->_defaultLength);
            }
        } else {
            $propValue = self::randomString(CMbFieldSpec::$chars, $this->_defaultLength);
        }
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $maxLength = CValue::first($this->length, $this->maxLength, 255);
        CMbArray::defaultValue($params, "size", min($maxLength, 25));
        CMbArray::defaultValue($params, "maxlength", $maxLength);

        return $this->getFormElementText($object, $params, $value, $className);
    }

    /**
     * @inheritdoc
     */
    function filter($value)
    {
        if (CAppUI::conf("purify_text_input")) {
            $value = CMbString::purifyHTML($value);
        }

        return parent::filter($value);
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        $litteral = "Chaîne de caractère, longueur : ";

        $properties = [];

        if ($this->minLength) {
            $properties[] = "min : $this->minLength";
        }

        if ($this->maxLength) {
            $properties[] = "max : $this->maxLength";
        }

        if (!$this->maxLength && !$this->minLength && $this->length) {
            $properties[] = "$this->length caractères";
        }

        if (!$this->maxLength && !$this->length) {
            $properties[] = "max : 255";
        }

        if (count($properties)) {
            $litteral .= "[" . implode(", ", $properties) . "]";
        }

        return "$litteral. " . parent::getLitteralDescription();
    }
}
