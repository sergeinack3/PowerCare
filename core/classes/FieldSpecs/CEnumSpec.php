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
use Ox\Core\CModelObject;

/**
 * Enum value (list of values)
 */
class CEnumSpec extends CMbFieldSpec
{
    public $list;
    public $typeEnum;
    public $vertical;
    public $columns;

    public $_list;
    public $_locales;

    /**
     * @inheritdoc
     */
    function __construct($className, $field, $prop = null, $aProperties = [])
    {
        parent::__construct($className, $field, $prop, $aProperties);

        $this->_list    = $this->getListValues($this->list);
        $this->_locales = [];

        if (!is_subclass_of($className, CModelObject::class) && strpos($className, 'CExObject') === false) {
            return;
        }

        // Locales not ready
        if (!CAppUI::$locale_info) {
            return;
        }

        foreach ($this->_list as $value) {
            $this->_locales[$value] = CAppUI::tr("$className.$field.$value");
        }
    }

    /**
     * Get the values of the list
     *
     * @param string $string The string to get the values of
     *
     * @return array
     */
    protected function getListValues($string)
    {
        $list = [];

        if ($string !== "" && $string !== null) {
            $list = explode('|', $string);
        }

        return $list;
    }

    public function getLocalesValues(): array
    {
        if (!$this->_locales) {
            foreach ($this->_list as $value) {
                $this->_locales[$value] = CAppUI::tr("{$this->className}.{$this->fieldName}.$value");
            }
        }

        return $this->_locales;
    }

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "enum";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "ENUM('" . str_replace('|', "','", $this->list) . "')";
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'list'     => 'list',
                'typeEnum' => ['radio', 'select'],
                'vertical' => 'bool',
                'columns'  => 'num',
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        $fieldName = $this->fieldName;
        $propValue = $object->$fieldName;

        return CMbString::htmlSpecialChars(CAppUI::tr("$object->_class.$fieldName.$propValue"), ENT_COMPAT, false);
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $value         = $object->{$this->fieldName};
        $specFragments = $this->getListValues($this->list);

        // TODO : Use strict (third argument) for in_array to avoid getting unexpected results
        // If the $specFragments has a string full of 0 ("00", "000", "0000") and $value is 0 or '0' or "0"
        // in_array will return true because the comparison is not strict (third arg for in_array)
        if (!in_array($value, $specFragments)) {
            return "N'a pas une valeur possible";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);
        $specFragments              = $this->getListValues($this->list);
        $object->{$this->fieldName} = self::randomString($specFragments, 1);
    }

    /**
     * @inheritdoc
     */
    function regressionSamples()
    {
        return $this->getListValues($this->list);
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $field     = CMbString::htmlSpecialChars($this->fieldName, ENT_COMPAT, false);
        $typeEnum  = CMbArray::extract($params, "typeEnum", $this->typeEnum ? $this->typeEnum : "select");
        $columns   = CMbArray::extract($params, "columns", $this->columns ? $this->columns : 1);
        $separator = CMbArray::extract($params, "separator");
        $cycle     = CMbArray::extract($params, "cycle", 1);
        $alphabet  = CMbArray::extract($params, "alphabet", false);
        $form      = CMbArray::extract($params, "form"); // needs to be extracted
        $prefix    = CMbArray::extract($params, "prefix");
        $name      = CMbArray::extract($params, 'name');
        // Empty label
        if ($emptyLabel = CMbArray::extract($params, "emptyLabel")) {
            $emptyLabel = CAppUI::tr($emptyLabel);
        }

        // Prefix
        if ($prefix) {
            $field = "{$prefix}_{$field}";
        }

        $name = $name ?: $field;

        // Extra info por HTML generation
        $extra     = CMbArray::makeXmlAttributes($params);
        $locales   = $this->getLocalesValues();
        $className = CMbString::htmlSpecialChars(trim("$className $this->prop"), ENT_COMPAT, false);
        $html      = "";

        // Alpha sorting
        if ($alphabet) {
            asort($locales);
        }

        // Turn readonly to disabled
        $readonly = CMbArray::extract($params, "readonly");
        $disabled = $readonly ? "disabled=\"1\"" : "";

        switch ($typeEnum) {
            default:
            case "select":

                $html .= "<select name=\"$name\" class=\"$className\" $disabled $extra>";

                // Empty option label
                if ($emptyLabel) {
                    $emptyLabel = "&mdash; $emptyLabel";

                    if ($value === null) {
                        $html .= "\n<option value=\"\" selected=\"selected\">$emptyLabel</option>";
                    } else {
                        $html .= "\n<option value=\"\">$emptyLabel</option>";
                    }
                }

                // All other options
                foreach ($locales as $key => $item) {
                    $selected = "";
                    if (($value !== null && $value === "$key") || ($value === null && "$key" === "$this->default" && !$emptyLabel)) {
                        $selected = " selected=\"selected\"";
                    }

                    $html .= "\n<option value=\"" . CMbString::htmlSpecialChars(
                            $key,
                            ENT_COMPAT,
                            false
                        ) . "\" $selected>" . CMbString::htmlSpecialChars($item, ENT_COMPAT, false) . "</option>";
                }

                $html .= "\n</select>";

                return $html;

            case "radio":
                $compteur   = 0;
                $nb_locales = count($locales);

                // Empty radio label
                if ($emptyLabel) {
                    $nb_locales++;
                    if ($value === null) {
                        $html .= "\n<input type=\"radio\" name=\"$name\" value=\"\" checked=\"checked\" $extra/>";
                    } else {
                        $html .= "\n<input type=\"radio\" name=\"$name\" value=\"\" $extra/>";
                    }
                    $html .= " <label for=\"{$name}_\">$emptyLabel</label> ";
                    $compteur++;

                    $modulo = $compteur % $cycle;
                    if ($separator != null && $modulo == 0 && $compteur < $nb_locales) {
                        $html .= $separator;
                    }

                    if ($this->vertical) {
                        $html .= "<br />\n";
                    }
                }

                // All other radios
                foreach ($locales as $key => $item) {
                    $selected = "";
                    if (($value !== null && $value === "$key") || ($value === null && "$key" === "$this->default")) {
                        $selected = " checked=\"checked\"";
                    }

                    $html .= "\n<input type=\"radio\" name=\"$name\" value=\"" . CMbString::htmlSpecialChars(
                            $key,
                            ENT_COMPAT,
                            false
                        ) . "\" $selected class=\"$className\" $disabled $extra />
                       <label for=\"{$name}_" . CMbString::htmlSpecialChars(
                            $key,
                            ENT_COMPAT,
                            false
                        ) . "\">" . CMbString::htmlSpecialChars($item, ENT_COMPAT, false) . "</label> ";
                    $compteur++;

                    $modulo = $compteur % $cycle;
                    if ($separator != null && $modulo == 0 && $compteur < $nb_locales) {
                        $html .= $separator;
                    }

                    if ($this->vertical) {
                        $html .= "<br />\n";
                    }
                }

                return $html;
        }
    }

    /**
     * @inheritdoc
     */
    function getLabelForAttribute($object, &$params)
    {
        // to extract the XHTML invalid attribute "typeEnum"
        $typeEnum = CMbArray::extract($params, "typeEnum");

        return parent::getLabelForAttribute($object, $params);
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        $litterals = [];
        foreach ($this->_list as $_list) {
            $litterals[] = "'$_list' (" . CAppUI::tr($this->className . "." . $this->fieldName . "." . $_list) . ")";
        }

        return "Chaîne de caractère dont les valeurs possibles sont : " . implode(", ", $litterals) . ". " .
            parent::getLitteralDescription();
    }

    /**
     * @return array
     */
    public function transform(): array
    {
        $datas                 = parent::transform();
        $datas['values']       = $this->_list;
        $datas['translations'] = $this->_locales;

        return $datas;
    }
}
