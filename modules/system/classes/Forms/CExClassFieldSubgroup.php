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
use Ox\Mediboard\Forms\CExClassPicture;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Field subgroup
 *
 * Sous ensemble de champs, imbricable, apparait sous forme de <fieldset>
 */
class CExClassFieldSubgroup extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_subgroup_id;

    public $parent_class;
    public $parent_id;
    public $title;
    public $predicate_id;

    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;

    /** @var CExClassFieldSubgroup|CExClassFieldGroup */
    public $_ref_parent;

    /** @var CExClassFieldPredicate */
    public $_ref_predicate;

    /** @var CExClassFieldSubgroup[] */
    public $_ref_children_groups;

    /** @var CExClassField[] */
    public $_ref_children_fields;

    /** @var CExClassMessage[] */
    public $_ref_children_messages;

    /** @var CExClassHostField[] */
    public $_ref_children_host_fields;

    /** @var CExClassFieldActionButton[] */
    public $_ref_children_action_buttons;

    /** @var CExClassWidget[] */
    public $_ref_children_widgets;

    /** @var CExClassPicture[] */
    public $_ref_children_pictures;

    /** @var CExClassFieldProperty[] */
    public $_ref_properties;

    public $_default_properties;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "ex_class_field_subgroup";
        $spec->key   = "ex_class_field_subgroup_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["parent_class"] = "enum notNull list|CExClassFieldGroup|CExClassFieldSubgroup";
        $props["parent_id"]    = "ref notNull class|CMbObject meta|parent_class cascade back|subgroups";
        $props["title"]        = "str";
        $props["predicate_id"] = "ref class|CExClassFieldPredicate autocomplete|_view|true nullify back|display_subgroups";

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
        $this->_view = ($this->title != "" ? $this->title : "[" . CAppUI::tr("common-Untitled") . "]");
    }

    /**
     * Load parent subgroup (or group)
     *
     * @param bool $cache Use object cache
     *
     * @return CExClassFieldGroup|CExClassFieldSubgroup
     */
    function loadRefParent($cache = true)
    {
        return $this->_ref_parent = $this->loadFwdRef("parent_id", $cache);
    }

    /**
     * Load predicate displaying this
     *
     * @param bool $cache Use object cache
     *
     * @return CExClassFieldPredicate
     */
    function loadRefPredicate($cache = true)
    {
        return $this->_ref_predicate = $this->loadFwdRef("predicate_id", $cache);
    }

    /**
     * Load all children subgroups
     *
     * @param bool $recurse Recurse on subgroups
     *
     * @return CExClassFieldSubgroup[]
     */
    function loadRefsChildrenGroups($recurse = false)
    {
        $this->_ref_children_groups = $this->loadBackRefs("subgroups", "ex_class_field_subgroup_id");

        if ($recurse) {
            $this->loadRefsChildrenFields();
            $this->loadRefsChildrenMessages();
            $this->loadRefsChildrenHostFields();
            $this->loadRefsChildrenPictures();
            $this->loadRefsChildrenActionButtons();
            $this->loadRefsChildrenWidgets();

            foreach ($this->_ref_children_groups as $_subgroup) {
                $_subgroup->loadRefsChildrenGroups($recurse);
            }
        }

        return $this->_ref_children_groups;
    }

    /**
     * @return CExClassFieldGroup
     */
    function getExGroup()
    {
        return $this->loadRefParent()->getExGroup();
    }

    /**
     * Load ExClass
     *
     * @param bool $cache Use object cache
     *
     * @return CExClass
     */
    function loadRefExClass($cache = true)
    {
        return $this->getExGroup()->loadRefExClass($cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClass($cache);
    }

    /**
     * Load children fields
     *
     * @param bool $cache Use object cache
     *
     * @return CExClassField[]
     */
    function loadRefsChildrenFields($cache = true)
    {
        $group  = $this->getExGroup();
        $fields = $group->loadRefsFields($cache);

        foreach ($fields as $_id => $_field) {
            if ($_field->subgroup_id != $this->_id) {
                unset($fields[$_id]);
            }
        }

        return $this->_ref_children_fields = $fields;
    }

    /**
     * Load children pictures
     *
     * @param bool $cache Use object cache
     *
     * @return CExClassPicture[]
     */
    function loadRefsChildrenPictures($cache = true)
    {
        $group    = $this->getExGroup();
        $pictures = $group->loadRefsPictures($cache);

        foreach ($pictures as $_id => $_field) {
            if ($_field->subgroup_id != $this->_id) {
                unset($pictures[$_id]);
            }
        }

        return $this->_ref_children_pictures = $pictures;
    }

    /**
     * Load children messages
     *
     * @param bool $cache Use object cache
     *
     * @return CExClassMessage[]
     */
    function loadRefsChildrenMessages($cache = true)
    {
        $group    = $this->getExGroup();
        $messages = $group->loadRefsMessages($cache);

        foreach ($messages as $_id => $_message) {
            if ($_message->subgroup_id != $this->_id) {
                unset($messages[$_id]);
            }
        }

        return $this->_ref_children_messages = $messages;
    }

    /**
     * Load children host fields
     *
     * @return CExClassHostField[]
     */
    function loadRefsChildrenHostFields()
    {
        $group       = $this->getExGroup();
        $host_fields = $group->loadRefsHostFields();

        foreach ($host_fields as $_id => $_host_field) {
            if ($_host_field->subgroup_id != $this->_id) {
                unset($host_fields[$_id]);
            }
        }

        return $this->_ref_children_host_fields = $host_fields;
    }

    /**
     * Load children action buttons
     *
     * @return CExClassFieldActionButton[]
     */
    function loadRefsChildrenActionButtons()
    {
        $group          = $this->getExGroup();
        $action_buttons = $group->loadRefsActionButtons();

        foreach ($action_buttons as $_id => $_action_button) {
            if ($_action_button->subgroup_id != $this->_id) {
                unset($action_buttons[$_id]);
            }
        }

        return $this->_ref_children_action_buttons = $action_buttons;
    }

    /**
     * Load children widgets
     *
     * @return CExClassWidget[]
     */
    function loadRefsChildrenWidgets()
    {
        $group   = $this->getExGroup();
        $objects = $group->loadRefsWidgets();

        foreach ($objects as $_id => $_object) {
            if ($_object->subgroup_id != $this->_id) {
                unset($objects[$_id]);
            }
        }

        return $this->_ref_children_widgets = $objects;
    }

    /**
     * @return CExClassFieldProperty[]
     */
    function loadRefProperties()
    {
        return $this->_ref_properties = $this->loadBackRefs("properties");
    }

    /**
     * Get properties with default values
     *
     * @param bool $cache Use object cache
     *
     * @return array
     */
    function getDefaultProperties($cache = true)
    {
        if ($cache && $this->_default_properties !== null) {
            return $this->_default_properties;
        }

        return $this->_default_properties = CExClassFieldProperty::getDefaultPropertiesFor($this);
    }

    /**
     * Duplicate subgroups
     *
     * @param CMbObject|CExClassFieldSubgroup $parent Container
     *
     * @return void
     */
    function duplicateSubgroups(CExClass $object, CMbObject $new)
    {
        $object->duplicateExBackRefs($this, "subgroups", "parent_id", $new->_id);

        /** @var self[] $subgroups */
        $subgroups = $this->loadBackRefs("subgroups");

        foreach ($subgroups as $_subgroup) {
            $_new_guid = $object->_duplication_mapping[$_subgroup->_guid];

            $_new = CMbObject::loadFromGuid($_new_guid);

            $_subgroup->duplicateSubgroups($object, $_new);
        }
    }

    function loadRefHostObjects(CExObject $ex_object)
    {
        foreach ($this->_ref_children_host_fields as $_host_field) {
            $_host_field->getHostObject($ex_object);
        }

        foreach ($this->_ref_children_groups as $_sub_group) {
            $_sub_group->loadRefHostObjects($ex_object);
        }
    }

    /**
     * Recursivly load subgroups and their properties
     *
     * @return void
     */
    public function recursiveLoadSubGroupsProperties()
    {
        $children = $this->loadRefsChildrenGroups(true);

        foreach ($children as $_child) {
            $_child->getDefaultProperties();
            $_child->recursiveLoadSubGroupsProperties();
        }
    }
}
