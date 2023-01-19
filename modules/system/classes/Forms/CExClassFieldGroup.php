<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Forms\CExClassPicture;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

class CExClassFieldGroup extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_group_id;

    public $ex_class_id;
    public $name; // != object_class, object_id, ex_ClassName_event_id,
    public $rank;
    public $disabled;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CExClassField[] */
    public $_ref_fields;

    /** @var CExClassMessage[] */
    public $_ref_messages;

    /** @var CExClassPicture[] */
    public $_ref_pictures;

    /** @var CExClassHostField[] */
    public $_ref_host_fields;

    /** @var CExClassFieldActionButton[] */
    public $_ref_action_buttons;

    /** @var CExClassWidget[] */
    public $_ref_widgets;

    /** @var CExClassFieldSubgroup[] */
    public $_ref_subgroups;

    /** @var CExClassField[] */
    public $_ref_root_fields;

    /** @var CExClassMessage[] */
    public $_ref_root_messages;

    /** @var CExClassHostField[] */
    public $_ref_root_host_fields;

    /** @var CExClassFieldActionButton[] */
    public $_ref_root_action_buttons;

    /** @var CExClassWidget[] */
    public $_ref_root_widgets;

    /** @var CExClassPicture[] */
    public $_ref_root_pictures;

    /** @var CExClassField[]|CExClassMessage[] */
    public $_ranked_items = [];

    public $_move;

    /** @var CExClassField[] */
    static $_fields_cache = [];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "ex_class_field_group";
        $spec->key             = "ex_class_field_group_id";
        $spec->uniques["name"] = ["ex_class_id", "name"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["ex_class_id"] = "ref class|CExClass cascade back|field_groups";
        $props["name"]        = "str notNull";
        $props["rank"]        = "num min|0";
        $props["disabled"]    = "bool notNull default|0";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = $this->name;
    }

    /**
     * @param bool $cache
     *
     * @return CExClass
     */
    function loadRefExClass($cache = true)
    {
        return $this->_ref_ex_class = $this->loadFwdRef("ex_class_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClass($cache);
    }

    /**
     * @return CExClassFieldGroup
     */
    function getExGroup()
    {
        return $this;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassField[]
     */
    function loadRefsRootFields($cache = true)
    {
        $fields = $this->loadRefsFields($cache);

        foreach ($fields as $_id => $_field) {
            if ($_field->subgroup_id) {
                unset($fields[$_id]);
            }
        }

        return $this->_ref_root_fields = $fields;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassMessage[]
     */
    function loadRefsRootMessages($cache = true)
    {
        $messages = $this->loadRefsMessages($cache);

        foreach ($messages as $_id => $_message) {
            if ($_message->subgroup_id) {
                unset($messages[$_id]);
            }
        }

        return $this->_ref_root_messages = $messages;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassPicture[]
     */
    function loadRefsRootPictures($cache = true)
    {
        $pictures = $this->loadRefsPictures($cache);

        foreach ($pictures as $_id => $_picture) {
            if ($_picture->subgroup_id) {
                unset($pictures[$_id]);
            }
        }

        return $this->_ref_root_pictures = $pictures;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassPicture[]
     */
    function loadRefsRootHostFields($cache = true)
    {
        $host_fields = $this->loadRefsHostFields($cache);

        foreach ($host_fields as $_id => $_host_field) {
            if ($_host_field->subgroup_id) {
                unset($host_fields[$_id]);
            }
        }

        return $this->_ref_root_host_fields = $host_fields;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassPicture[]
     */
    function loadRefsRootActionButtons($cache = true)
    {
        $action_buttons = $this->loadRefsActionButtons($cache);

        foreach ($action_buttons as $_id => $_action_button) {
            if ($_action_button->subgroup_id) {
                unset($action_buttons[$_id]);
            }
        }

        return $this->_ref_root_action_buttons = $action_buttons;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassWidget[]
     */
    function loadRefsRootWidgets($cache = true)
    {
        $objects = $this->loadRefsWidgets($cache);

        foreach ($objects as $_id => $_object) {
            if ($_object->subgroup_id) {
                unset($objects[$_id]);
            }
        }

        return $this->_ref_root_widgets = $objects;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassField[]
     */
    function loadRefsFields($cache = true)
    {
        if ($cache && isset(self::$_fields_cache[$this->_id])) {
            return $this->_ref_fields = self::$_fields_cache[$this->_id];
        }

        $fields = $this->loadBackRefs(
            "class_fields",
            "IF(tab_index IS NULL, 10000, tab_index), ex_class_field_id",
            null,
            null,
            null,
            null,
            null,
            null,
            false
        );

        self::massLoadFwdRef($fields, "ex_group_id");

        // Remove the notNull if field is disabled
        foreach ($fields as &$_field) {
            if ($_field->disabled) {
                $_field->prop = str_replace('notNull', '', $_field->prop);
            }
        }

        $this->_ref_fields = $fields;


        if ($cache) {
            self::$_fields_cache[$this->_id] = $fields;
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($this->_move && $this->_id) {
            $this->completeField("ex_class_id");
            $groups     = $this->loadRefExClass()->loadRefsGroups();
            $groups_ids = array_keys($groups);
            $self_index = array_search($this->_id, $groups_ids);

            $signs = [
                "before" => -1,
                "after"  => +1,
            ];

            $sign = CValue::read($signs, $this->_move);

            // Si signe valide et que l'index existe
            if ($sign && isset($groups_ids[$self_index + $sign])) {
                [$groups_ids[$self_index + $sign], $groups_ids[$self_index]] = [
                    $groups_ids[$self_index],
                    $groups_ids[$self_index + $sign],
                ];

                $new_groups = [];
                foreach ($groups_ids as $id) {
                    $new_groups[$id] = $groups[$id];
                }

                $i = 1;
                foreach ($new_groups as $_group) {
                    if ($_group->_id == $this->_id) {
                        $this->rank = $i;
                    } else {
                        $_group->rank = $i;
                        $_group->store();
                    }

                    $i++;
                }
            }
        }

        return parent::store();
    }

    /**
     * @return CExClassHostField[]
     */
    function loadRefsHostFields($cache = true)
    {
        if ($cache && $this->_ref_host_fields) {
            return $this->_ref_host_fields;
        }

        return $this->_ref_host_fields = $this->loadBackRefs("host_fields");
    }

    /**
     * @return CExClassFieldActionButton[]
     */
    function loadRefsActionButtons($cache = true)
    {
        if ($cache && $this->_ref_action_buttons) {
            return $this->_ref_action_buttons;
        }

        return $this->_ref_action_buttons = $this->loadBackRefs("action_buttons");
    }

    /**
     * @return CExClassWidget[]
     */
    function loadRefsWidgets($cache = true)
    {
        if ($cache && $this->_ref_widgets) {
            return $this->_ref_widgets;
        }

        return $this->_ref_widgets = $this->loadBackRefs("widgets");
    }

    /**
     * @param bool $cache
     *
     * @return CExClassMessage[]
     */
    function loadRefsMessages($cache = true)
    {
        if ($cache && $this->_ref_messages) {
            return $this->_ref_messages;
        }

        return $this->_ref_messages = $this->loadBackRefs(
            "class_messages",
            "IF(tab_index IS NULL, 10000, tab_index), ex_class_message_id",
            null,
            null,
            null,
            null,
            null,
            null,
            false
        );
    }

    /**
     * @param bool $cache
     *
     * @return CExClassMessage[]
     */
    function getRankedMessages()
    {
        $all_messages = $this->loadRefsMessages();
        $messages     = [];

        foreach ($all_messages as $_message) {
            if ($_message->tab_index !== null) {
                $messages[] = $_message;
            }
        }

        return $messages;
    }

    /**
     * Get host fields configured as ranked items
     *
     * @return array|CExClassHostField[]
     */
    function getRankedHostFields()
    {
        $all_host_fields = $this->loadRefsHostFields();
        $host_fields     = [];

        foreach ($all_host_fields as $_host_field) {
            if ($_host_field->display_in_tab && $_host_field->tab_index !== null) {
                $host_fields[] = $_host_field;
            }
        }

        return $host_fields;
    }

    /**
     * Get ranked items ordered by tab index
     *
     * @return array|CExClassField[]|CExClassMessage[]|CExClassHostField[]
     */
    function getRankedItems()
    {
        $this->_ranked_items = [];

        $_messages    = $this->getRankedMessages();
        $_host_fields = $this->getRankedHostFields();

        foreach ($this->_ref_fields as $_ex_field) {
            foreach ($_messages as $_i => $_message) {
                if ($_ex_field->tab_index != null && $_message->tab_index > $_ex_field->tab_index) {
                    break;
                }

                $this->_ranked_items[] = $_message;

                unset($_messages[$_i]);
            }

            foreach ($_host_fields as $_i => $_host_field) {
                if ($_ex_field->tab_index != null && $_host_field->tab_index > $_ex_field->tab_index) {
                    break;
                }

                $this->_ranked_items[] = $_host_field;

                unset($_host_fields[$_i]);
            }

            $this->_ranked_items[] = $_ex_field;
        }

        return $this->_ranked_items;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassPicture[]
     */
    function loadRefsPictures($cache = true)
    {
        if ($cache && $this->_ref_pictures) {
            return $this->_ref_pictures;
        }

        $this->_ref_pictures = $this->loadBackRefs("class_pictures");

        foreach ($this->_ref_pictures as $_picture) {
            $_picture->loadRefFile();
        }

        return $this->_ref_pictures;
    }

    /**
     * @param bool $recurse
     *
     * @return CExClassFieldSubgroup[]
     */
    function loadRefsSubgroups($recurse = false)
    {
        $this->_ref_subgroups = $this->loadBackRefs("subgroups", "ex_class_field_subgroup_id");

        if ($recurse) {
            foreach ($this->_ref_subgroups as $_subgroup) {
                $_subgroup->loadRefsChildrenGroups($recurse);
            }
        }

        return $this->_ref_subgroups;
    }

    function loadRefHostObjects(CExObject $ex_object)
    {
        if ($ex_object->_ref_ex_class->pixel_positionning) {
            if ($this->_ref_root_host_fields) {
                foreach ($this->_ref_root_host_fields as $_host_field) {
                    $_host_field->getHostObject($ex_object);
                }
            }

            if ($this->_ref_subgroups) {
                foreach ($this->_ref_subgroups as $_sub_group) {
                    $_sub_group->loadRefHostObjects($ex_object);
                }
            }
        } else {
            if ($this->_ref_host_fields) {
                foreach ($this->_ref_host_fields as $_host_field) {
                    $_host_field->getHostObject($ex_object);
                }
            }
        }
    }
}
