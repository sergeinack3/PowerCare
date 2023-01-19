<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Field Predicate
 */
class CExClassFieldPredicate extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_predicate_id;

    public $ex_class_field_id;
    public $operator;
    public $value;
    public $_value;
    public $_compute_view;

    /** @var CExClassField */
    public $_ref_ex_class_field;

    /** @var CExClassFieldProperty */
    public $_ref_properties;

    static $_load_lite = false;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                   = parent::getSpec();
        $spec->table            = "ex_class_field_predicate";
        $spec->key              = "ex_class_field_predicate_id";
        $spec->uniques["value"] = ["ex_class_field_id", "operator", "value"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["ex_class_field_id"] = "ref notNull class|CExClassField cascade seekable back|predicates";
        $props["operator"]          = "enum notNull list|=|!=|>|>=|<|<=|startsWith|endsWith|contains|hasValue|hasNoValue default|=";
        $props["value"]             = "str notNull seekable";
        $props["_value"]            = "str";
        $props["_compute_view"]     = "bool";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        if (!$this->_id) {
            return;
        }

        $field = $this->loadRefExClassField();
        $field->updateTranslation();

        $this->_value = "";
        if ($this->operator != "hasValue" && $this->operator != "hasNoValue") {
            $_ex_class_id = $field->loadRefExGroup()->ex_class_id;

            $_spec = $field->getSpecObject();
            $_obj  = (object)[
                "_class"     => "CExObject_$_ex_class_id",
                $field->name => $this->value,
            ];

            $this->_value = str_replace(['&lt;', '&gt;'], ['<', '>'], $_spec->getValue($_obj));
        }

        $this->_view = $field->_view . " " . $this->_specs["operator"]->_locales[$this->operator] . " " . $this->_value;
    }

    /**
     * @param bool $cache [optional]
     *
     * @return CExClassField
     */
    function loadRefExClassField($cache = true)
    {
        return $this->_ref_ex_class_field = $this->loadFwdRef("ex_class_field_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClassField($cache);
    }

    /**
     * @inheritDoc
     */
    function getAutocompleteList(
        $keywords,
        $where = null,
        $limit = null,
        $ljoin = null,
        $order = null,
        $group_by = null,
        bool $strict = true
    ) {
        $list = $this->loadList($where, null, null, null, $ljoin, null, null, $strict);

        /** @var self[] $real_list */
        $real_list = [];
        $re        = preg_quote($keywords);
        $re        = CMbString::allowDiacriticsInRegexp($re);
        $re        = str_replace("/", "\\/", $re);
        $re        = "/($re)/i";

        foreach ($list as $_match) {
            if ($keywords == "%" || $keywords == "" || preg_match($re, $_match->_view)) {
                $_match->loadView();
                $real_list[$_match->_id] = $_match;
            }
        }

        $views = CMbArray::pluck($real_list, "_view");
        array_multisort($views, $real_list);

        $empty        = new self;
        $empty->_id   = null;
        $empty->_guid = "$this->_class-$this->_id"; // FIXME
        $empty->_view = " -- ";
        array_unshift($real_list, $empty);

        return $real_list;
    }

    function checkValue($value)
    {
        return CExClass::compareValues($value, $this->operator, $this->value);
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        CExObject::$_locales_cache_enabled = false;

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($this->_compute_view) {
            $this->loadView();
        }

        return null;
    }

    /**
     * @return CExClassFieldProperty[]
     */
    function loadRefProperties()
    {
        return $this->_ref_properties = $this->loadBackRefs("properties");
    }
}
