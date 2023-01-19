<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Core\FieldSpecs\CDateTimeSpec;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\FieldSpecs\CSetSpec;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Form constraint
 */
class CExClassMandatoryConstraint extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_mandatory_constraint_id;

    public $ex_class_event_id;

    public $field;
    public $operator;
    public $value;

    /** @var string Reference value (ie: NOW, etc.) */
    public $reference_value;

    /** @var string Constraint comment */
    public $comment;

    /** @var string User friendly formulae */
    public $_formulae;

    /** @var CExClassEvent */
    public $_ref_ex_class_event;

    /** @var CMbObject */
    public $_ref_target_object;

    /** @var CMbFieldSpec */
    public $_ref_target_spec;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                        = parent::getSpec();
        $spec->table                 = "ex_class_mandatory_constraint";
        $spec->key                   = "ex_class_mandatory_constraint_id";
        $spec->uniques["constraint"] = ["ex_class_event_id", "field", "value", "reference_value"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["ex_class_event_id"] = "ref notNull class|CExClassEvent back|mandatory_constraints";
        $props["field"]             = "str notNull";
        $props["operator"]          = "enum notNull list|=|!=|>|>=|<|<=|startsWith|endsWith|contains|in default|=";
        $props["value"]             = "str notNull";
        $props["reference_value"]   = "enum list|now";
        $props["comment"]           = "text markdown";

        $props['_formulae'] = 'str';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->loadRefExClassEvent();
        $this->getFormulae();

        $host_class = $this->_ref_ex_class_event->host_class;

        $object = new $host_class();

        $parts    = explode("-", $this->field);
        $subparts = explode(".", $parts[0]);

        if (count($subparts) > 1) {
            // first part
            $this->_view = CAppUI::tr("$host_class-{$subparts[0]}") . " de type " . CAppUI::tr("{$subparts[1]}");
        } else {
            // second part
            if (count($parts) > 1) {
                $this->_view = CAppUI::tr("$host_class-{$parts[0]}");
            } else {
                $this->_view = CAppUI::tr("$host_class-{$this->field}");
            }
        }

        // 2 levels
        if (count($parts) > 1) {
            if (isset($subparts[1])) {
                $class = $subparts[1];
            } else {
                /** @var CRefSpec $_spec */
                $_spec = $object->_specs[$subparts[0]];
                $class = $_spec->class;
            }

            $this->_view .= " / " . CAppUI::tr("{$class}-{$parts[1]}");
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if (!$this->_id || $this->fieldModified('comment')) {
            $this->comment = CMbString::purifyHTML($this->comment);
        }

        return parent::store();
    }

    /**
     * Tells whether a constraint can have a reference value
     *
     * @return bool
     */
    function canHaveReferenceValue()
    {
        $host_class = $this->loadRefExClassEvent()->host_class;
        $host       = new $host_class();

        if (!$host || !$this->field || !isset($host->_specs[$this->field])) {
            return false;
        }

        return ($host->_specs[$this->field] instanceof CDateSpec || $host->_specs[$this->field] instanceof CDateTimeSpec);
    }

    /**
     * Get user friendly formulae
     *
     * @return string
     */
    function getFormulae()
    {
        $this->loadRefExClassEvent();

        $host_class = $this->_ref_ex_class_event->host_class;

        $value = $this->formatValue();

        $this->_formulae = '[' . CAppUI::tr("{$host_class}-{$this->field}") . "] {$this->operator}";

        if ($this->canHaveReferenceValue() && $this->reference_value !== null) {
            $operator = ($this->value < 0) ? '-' : '+';
            $value    = abs($this->value);

            $reference       = CAppUI::tr("{$this->_class}.reference_value.{$this->reference_value}");
            $this->_formulae .= " [{$reference}] {$operator} {$value}";

            if ($this->reference_value === 'now') {
                $this->_formulae .= ' h';
            }
        } else {
            $this->_formulae .= " {$value}";
        }

        return $this->_formulae;
    }

    /**
     * Formats host value
     *
     * @return string
     */
    function formatValue()
    {
        $host_class = $this->loadRefExClassEvent()->host_class;
        $host       = new $host_class();

        if (!$host || !isset($host->_specs[$this->field])) {
            return $this->value;
        }

        switch (true) {
            case $host->_specs[$this->field] instanceof CEnumSpec:
            case $host->_specs[$this->field] instanceof CSetSpec:
                return CAppUI::tr("{$host->_class}.{$this->field}.{$this->value}");

            default:
        }

        return $this->value;
    }

    /**
     * Check constraint
     *
     * @param CMbObject $object Object
     *
     * @return bool
     */
    function checkConstraint(CMbObject $object)
    {
        $this->completeField("field", "value", "reference_value");

        $field = $this->field;

        // cas ou l'objet retrouvé n'a pas le champ (meta objet avec classe differente)
        if (!isset($object->_specs[$field])) {
            return false;
        }

        $value = $object->{$field};

        if ($object->_specs[$field] instanceof CRefSpec) {
            $_obj  = $object->loadFwdRef($field, true);
            $value = $_obj->_guid;
        }

        $value_comp = $this->value;
        if ($this->operator == "in") {
            $value_comp = $this->getInValues();
        }

        if ($this->reference_value !== null) {
            switch ($this->reference_value) {
                case 'now':
                    $operator        = ($value_comp < 0) ? '-' : '+';
                    $_value_comp     = abs($value_comp);
                    $_reference_date = "{$operator} {$_value_comp} hours";

                    $value_comp = ($object->_specs[$field] instanceof CDateSpec) ? CMbDT::date(
                        $_reference_date
                    ) : CMbDT::dateTime($_reference_date);
                    break;

                default:
            }
        }

        return CExClass::compareValues($value, $this->operator, $value_comp);
    }

    /**
     * Get values for the "in" operator
     *
     * @return string[]
     */
    function getInValues()
    {
        return array_map("trim", preg_split("/[\r\n]+/", $this->value));
    }

    /**
     * Load class event object
     *
     * @param bool $cache Use object cache
     *
     * @return CExClassEvent
     */
    function loadRefExClassEvent($cache = true)
    {
        return $this->_ref_ex_class_event = $this->loadFwdRef("ex_class_event_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClassEvent($cache);
    }
}
