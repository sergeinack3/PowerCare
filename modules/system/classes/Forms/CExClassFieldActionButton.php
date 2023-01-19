<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Action Button
 */
class CExClassFieldActionButton extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_action_button_id;

    public $ex_group_id;
    public $subgroup_id;
    public $predicate_id;

    public $ex_class_field_source_id;
    public $ex_class_field_target_id;
    public $trigger_ex_class_id;

    public $action;
    public $icon;
    public $text;

    public $coord_x;
    public $coord_y;

    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;

    public $_no_size = false;

    /** @var CExClassFieldGroup */
    public $_ref_ex_group;

    /** @var CExClassFieldPredicate */
    public $_ref_predicate;

    /** @var CExClassField */
    public $_ref_ex_class_field_source;

    /** @var CExClassField */
    public $_ref_ex_class_field_target;

    /** @var CExClass */
    public $_ref_triggerable_ex_class;

    /** @var array Compat table (source type => compatible target types */
    static $compat = [
        "enum"      => ["enum", "set"],
        "set"       => ["set"],
        "str"       => ["str", "text"],
        "text"      => ["text", "str"],
        "bool"      => ["str", "text"],
        "num"       => ["num", "str", "text", "float", "pct", "currency"],
        "float"     => ["float", "str", "text", "num", "pct", "currency"],
        "date"      => ["date", "str", "text", "dateTime"],
        "time"      => ["time", "str", "text"],
        "dateTime"  => ["dateTime", "date", "str", "text"],
        "pct"       => ["pct", "str", "text", "float", "num", "currency"],
        "phone"     => ["phone", "str", "text"],
        "birthDate" => ["birthDate", "date"],
        "currency"  => ["currency", "str", "text", "float", "num", "pct"],
        "email"     => ["email", "str", "text"],
    ];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table = "ex_class_field_action_button";
        $spec->key   = "ex_class_field_action_button_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["ex_group_id"]  = "ref notNull class|CExClassFieldGroup cascade back|action_buttons";
        $props["subgroup_id"]  = "ref class|CExClassFieldSubgroup nullify back|children_action_buttons";
        $props["predicate_id"] = "ref class|CExClassFieldPredicate autocomplete|_view|true nullify back|display_action_buttons";

        $props["ex_class_field_source_id"] = "ref class|CExClassField cascade back|action_buttons_source";
        $props["ex_class_field_target_id"] = "ref class|CExClassField cascade back|action_buttons_target";
        $props["trigger_ex_class_id"]      = "ref class|CExClass cascade back|action_buttons";

        $props["action"] = "enum notNull list|copy|empty|open default|copy";
        $props["icon"]   = "enum notNull list|cancel|left|up|right|down|new";
        $props["text"]   = "str";

        $props["coord_x"] = "num min|0 max|100";
        $props["coord_y"] = "num min|0 max|100";

        // Pixel positionned
        $props["coord_left"]   = "num";
        $props["coord_top"]    = "num";
        $props["coord_width"]  = "num min|1";
        $props["coord_height"] = "num min|1";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        if (!$this->coord_width && !$this->coord_height) {
            $this->_no_size = true;
        }

        $this->_view = $this->getFormattedValue("action");
    }

    /**
     * @param bool $cache [optional]
     *
     * @return CExClassFieldGroup
     */
    function loadRefExGroup($cache = true)
    {
        return $this->_ref_ex_group = $this->loadFwdRef("ex_group_id", $cache);
    }

    /**
     * @param bool $cache [optional]
     *
     * @return CExClassField
     */
    function loadRefExClassFieldSource($cache = true)
    {
        return $this->_ref_ex_class_field_source = $this->loadFwdRef("ex_class_field_source_id", $cache);
    }

    /**
     * @param bool $cache [optional]
     *
     * @return CExClassField
     */
    function loadRefExClassFieldTarget($cache = true)
    {
        return $this->_ref_ex_class_field_target = $this->loadFwdRef("ex_class_field_target_id", $cache);
    }

    /**
     * @param bool $cache
     *
     * @return CExClassFieldPredicate
     */
    function loadRefPredicate($cache = true)
    {
        return $this->_ref_predicate = $this->loadFwdRef("predicate_id", $cache);
    }

    /**
     * @param bool $cache
     *
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefTriggerableExClass($cache = true)
    {
        return $this->_ref_triggerable_ex_class = $this->loadFwdRef('trigger_ex_class_id', $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExGroup($cache);
    }
}
