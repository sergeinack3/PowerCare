<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

class CExClassFieldEnumTranslation extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_enum_translation_id;

    public $ex_class_field_id;
    public $lang;
    public $key;
    public $value;

    /** @var CExClassField */
    public $_ref_ex_class_field;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "ex_class_field_enum_translation";
        $spec->key             = "ex_class_field_enum_translation_id";
        $spec->uniques["lang"] = ["ex_class_field_id", "lang", "key"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["ex_class_field_id"] = "ref notNull class|CExClassField cascade back|enum_translations";
        $props["lang"]              = "enum list|fr|en"; // @todo: en fonction des repertoires
        $props["key"]               = "str";
        $props["value"]             = "str";

        return $props;
    }

    function getKey(CExClassField $base = null)
    {
        $field  = $base ? $base : $this->loadRefExClassField();
        $class  = $base ? $base->loadRefExClass() : $field->loadRefExClass();
        $prefix = "CExObject";

        if ($class->_id) {
            $prefix .= "_{$class->_id}";
        }

        return [$prefix, ".{$field->name}.{$this->key}"];
    }

    function updateLocales(CExClassField $base = null)
    {
        [$prefix, $key] = $this->getKey($base);

        CAppUI::addLocale($prefix, $key, $this->value);
        $this->_view = $this->value;
    }

    function fillIfEmpty()
    {
        if (!$this->_id) {
            $this->value = $this->key;
            $this->updateLocales();
            $this->value = "";
        }
    }

    function loadRefExClassField($cache = true)
    {
        return $this->_ref_ex_class_field = $this->loadFwdRef("ex_class_field_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClassField($cache);
    }
}
