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
 * Set of values
 */
class CSetSpec extends CEnumSpec
{

    public $_list_default;

    /**
     * @inheritdoc
     */
    function __construct($className, $field, $prop = null, $aProperties = [])
    {
        parent::__construct($className, $field, $prop, $aProperties);

        $this->_list_default = $this->getListValues($this->default);
    }

    /**
     * @inheritdoc
     */
    function getSpecType()
    {
        return "set";
    }

    /**
     * @inheritdoc
     */
    function getDBSpec()
    {
        return "TEXT";
    }

    /**
     * @inheritdoc
     */
    function getOptions()
    {
        return [
                'list'     => 'list',
                'typeEnum' => ['checkbox', 'select'],
            ] + parent::getOptions();
    }

    /**
     * @inheritdoc
     */
    function getValue($object, $params = [])
    {
        $fieldName = $this->fieldName;
        $propValue = $this->getListValues($object->$fieldName);

        $ret = [];
        foreach ($propValue as $_value) {
            $ret[] = CMbString::htmlSpecialChars(CAppUI::tr("$object->_class.$fieldName.$_value"));
        }

        return implode(", ", $ret);
    }

    /**
     * @inheritdoc
     */
    function checkProperty($object)
    {
        $propValue     = $this->getListValues($object->{$this->fieldName});
        $specFragments = $this->getListValues($this->list);

        $diff = array_diff($propValue, $specFragments);

        if (!empty($diff)) {
            return "Contient une valeur non valide";
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function getFormHtmlElement($object, $params, $value, $className)
    {
        $field   = CMbString::htmlSpecialChars($this->fieldName);
        $locales = $this->getLocalesValues();

        $typeEnum  = CMbArray::extract($params, "typeEnum", $this->typeEnum ? $this->typeEnum : "checkbox");
        $separator = CMbArray::extract($params, "separator", $this->vertical ? "<br />" : null);
        $cycle     = CMbArray::extract($params, "cycle", 1);
        $alphabet  = CMbArray::extract($params, "alphabet", false);
        $size      = CMbArray::extract($params, "size", 0);
        $onchange  = CMbArray::get($params, "onchange");
        $form      = CMbArray::extract($params, "form"); // needs to be extracted
        $readonly  = CMbArray::extract($params, "readonly") == 1;
        $name      = CMbArray::extract($params, 'name');

        $extra     = CMbArray::makeXmlAttributes($params);
        $className = CMbString::htmlSpecialChars(trim("$className $this->prop"));

        $prefix = CMbArray::extract($params, "prefix");

        if ($prefix) {
            $field = "{$prefix}_{$field}";
        }

        $name = $name ?: $field;

        if ($alphabet) {
            asort($locales);
        }

        $uid         = uniqid();
        $value_array = $this->getListValues($value);

        switch ($typeEnum) {
            case "select":
                if ($readonly) {
                    $readonly = "readonly";
                }

                $sHtml = "<script type=\"text/javascript\">
          Main.add(function(){
            var select = \$\$('select[data-select_set=$uid]')[0],
                element = select.previous(),
                tokenField = new TokenField(element, {" . ($onchange ? "onChange: function(){ $onchange }.bind(element)" : "") . "});

            select.observe('change', function(event){
              tokenField.setValues(\$A(select.options).filter(function(o){return o.selected}).pluck('value'));

              element.fire('ui:change');
            });
          });
        </script>";
                $sHtml .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" class=\"$className\" $extra />\n";
                $sHtml .= "<select class=\"$className\" multiple=\"multiple\" size=\"$size\" data-select_set=\"$uid\" $extra $readonly>";

                foreach ($locales as $key => $item) {
                    if (!empty($value_array) && in_array($key, $value_array)) {
                        $selected = " selected=\"selected\"";
                    } else {
                        $selected = "";
                    }

                    $sHtml .= "\n<option value=\"" . CMbString::htmlSpecialChars(
                            $key
                        ) . "\" $selected>" . CMbString::htmlSpecialChars($item) . "</option>";
                }

                $sHtml .= "\n</select>";
                break;

            default:
            case "checkbox":
                $sHtml = "<span id=\"set-container-$uid\">\n";
                $sHtml .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" class=\"$className\" $extra />\n";

                $sHtml    .= "<script type=\"text/javascript\">
          Main.add(function(){
            var cont = \$('set-container-$uid'),
                element = cont.down('input[type=hidden]'),
                tokenField = new TokenField(element, {" . ($onchange ? "onChange: function(){ $onchange }.bind(element)" : "") . "});

            var callback = function(event){
              var elt = Event.element(event);
              tokenField.toggle(elt.value, elt.checked);

              element.fire('ui:change');
            };
            
            cont.select('input[type=checkbox]').invoke('observe', 'click', callback).invoke('observe', 'ui:change', callback);
          });
        </script>";
                $compteur = 0;

                if ($readonly) {
                    $readonly = "disabled";
                }

                foreach ($locales as $key => $item) {
                    $selected = "";

                    if (!empty($value_array) && in_array($key, $value_array)) {
                        $selected = " checked=\"checked\"";
                    }

                    $sHtml .= "\n<label>
              <input type=\"checkbox\" name=\"_{$name}_" . CMbString::htmlSpecialChars(
                            $key
                        ) . "\" value=\"" . CMbString::htmlSpecialChars($key) . "\" class=\"set-checkbox token$uid\" $selected $readonly  />
              " . CMbString::htmlSpecialChars($item) . "</label> ";
                    $compteur++;

                    $modulo = $compteur % $cycle;
                    if ($separator != null && $modulo == 0 && $compteur < count($locales)) {
                        $sHtml .= $separator;
                    }
                }

                $sHtml .= "</span>\n";
        }

        return $sHtml;
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
        return "Liste de valeurs possible séparée par la chaine '|' (pipe). " . parent::getLitteralDescription();
    }
}
