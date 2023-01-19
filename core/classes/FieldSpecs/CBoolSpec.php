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
 * Boolean value (0 or 1)
 */
class CBoolSpec extends CMbFieldSpec
{
    public $_list;
    public $_locales;
    public $typeEnum;
    public $iconography;

    static $_default_no = true; // todo : faire en sorte de se passer de ça

    /**
     * @inheritdoc
     */
    function __construct($className, $field, $prop = null, $aProperties = [])
    {
        parent::__construct($className, $field, $prop, $aProperties);

        // Locales not ready
        if (!CAppUI::$locale_info) {
            return;
        }

        foreach ($this->_list = [0, 1] as $value) {
            $this->_locales[$value] = CAppUI::tr("bool.$value");
        }
    }

    public function getLocalesValues(): array
    {
        if (!$this->_locales) {
            foreach ($this->_list = [0, 1] as $value) {
                $this->_locales[$value] = CAppUI::tr("bool.$value");
            }
        }

        return $this->_locales;
    }

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "bool";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "ENUM('0','1')";
    }

    /**
     * @inheritdoc
     */
    public function getPHPSpec(): string
    {
        return parent::PHP_TYPE_BOOL;
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'default'  => 'bool',
                'typeEnum' => ['radio', 'select', 'checkbox'],
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        $value = CAppUI::tr("bool." . $object->{$this->fieldName});

        if (CMbArray::extract($params, "iconography")) {
            if ($object->{$this->fieldName}) {
                return "<i class=\"fa-lg fas fa-check\" style=\"color: forestgreen;\" title=\"$value\"></i>";
            } else {
                return "<i class=\"fa-lg fas fa-times\" style=\"color: firebrick;\" title=\"$value\"></i>";
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    function checkOptions()
    {
        parent::checkOptions();

        if ($this->default === null) {
            $this->default = (self::$_default_no ? 0 : "");
        }
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $value = $object->{$this->fieldName};

        // Has to be numeric
        $value = CMbFieldSpec::checkNumeric($value, true);
        if ($value === null) {
            return "N'est pas une chaîne numérique";
        }

        // Only two options
        if ($value !== 0 && $value != 1) {
            return "Ne peut être différent de 0 ou 1";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $sHtml     = "";
        $field     = CMbString::htmlSpecialChars($this->fieldName);
        $typeEnum  = CMbArray::extract($params, "typeEnum", $this->typeEnum ? $this->typeEnum : "radio");
        $separator = CMbArray::extract($params, "separator");
        $disabled  = CMbArray::extract($params, "disabled");
        $readonly  = CMbArray::extract($params, "readonly");
        $default   = CMbArray::extract($params, "default", $this->default);

        // needs to be extracted to avoid adding it to classes.
        $form = CMbArray::extract($params, "form");

        $className = CMbString::htmlSpecialChars(trim("$className $this->prop"));
        $name      = CMbArray::extract($params, 'name');
        $extra     = CMbArray::makeXmlAttributes($params);

        // Empty label
        if ($emptyLabel = CMbArray::extract($params, "emptyLabel")) {
            $emptyLabel = "&mdash; " . CAppUI::tr($emptyLabel);
        }

        $name = $name ?: $field;

        switch ($typeEnum) {
            default:
            case "radio":
                // Attributes for all inputs
                $attributes = [
                    "type" => "radio",
                    "name" => $name,
                ];

                if (null === $value) {
                    $value = "$default";
                }

                for ($i = 1; $i >= 0; $i--) {
                    $attributes["value"]    = "$i";
                    $attributes["checked"]  = $value === "$i" ? "checked" : null;
                    $attributes["disabled"] = $disabled === "$i" || $readonly ? "disabled" : null;
                    $attributes["class"]    = $className;

                    $xmlAttributes = CMbArray::makeXmlAttributes($attributes);
                    $sHtml         .= "\n<input $xmlAttributes $extra />";

                    $sTr   = CAppUI::tr("bool.$i");
                    $sHtml .= "\n<label for=\"{$name}_$i\">$sTr</label> ";

                    if ($separator && $i != 0) {
                        $sHtml .= "\n$separator";
                    }
                }

                return $sHtml;

            case "checkbox":
                $disabled = $readonly ? "disabled=\"1\"" : $disabled;

                if (null === $value) {
                    $value = "$default";
                }

                if ($value !== null && $value == 1) {
                    $checked = " checked=\"checked\"";
                } else {
                    $checked = "";
                    $value   = "0";
                }

                $sHtml = '<input type="checkbox" name="__' . $name . '" 
          onclick="$V(this.form.' . $name . ', $V(this)?1:0);" ' . $checked . ' ' . $disabled . ' />';

                $sHtml .= '<input type="hidden" name="' . $name . '" ' . $extra . ' value="' . $value . '" />';

                return $sHtml;

            case "select":
                $disabled = $readonly ? "disabled=\"1\"" : $disabled;
                $sHtml    = "<select name=\"$name\" class=\"$className\" $disabled $extra>";

                if ($emptyLabel) {
                    if ($value === null) {
                        $sHtml .= "\n<option value=\"\" selected=\"selected\">$emptyLabel</option>";
                    } else {
                        $sHtml .= "\n<option value=\"\">$emptyLabel</option>";
                    }
                }

                foreach ($this->getLocalesValues() as $key => $item) {
                    if (($value !== null && $value === "$key") || ($value === null && "$key" === "$this->default" && !$emptyLabel)) {
                        $selected = " selected=\"selected\"";
                    } else {
                        $selected = "";
                    }
                    $sHtml .= "\n<option value=\"$key\" $selected>$item</option>";
                }
                $sHtml .= "\n</select>";

                return $sHtml;
        }
    }

    /**
     * @inheritdoc
     */
    function getLabelForAttribute($object, &$params)
    {
        $typeEnum = CMbArray::extract($params, "typeEnum", "radio");

        switch ($typeEnum) {
            //case "radio":    return "{$this->fieldName}_1";
            case "checkbox":
                return "__$this->fieldName";

            default:
            case "radio":
            case "select":
                return $this->fieldName;
        }
    }

    /**
     * @inheritdoc
     */
    function sample($object, $consistent = true)
    {
        parent::sample($object, $consistent);
        $object->{$this->fieldName} = rand(0, 1);
    }

    /**
     * @inheritdoc
     */
    function regressionSamples()
    {
        return ["0", "1"];
    }

    /**
     * @inheritdoc
     */
    public function getLitteralDescription(): string
    {
        return "Booléen au format : '0, 1'. " . parent::getLitteralDescription();
    }
}
