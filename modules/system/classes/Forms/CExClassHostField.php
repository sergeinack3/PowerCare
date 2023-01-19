<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Host field class
 *
 * Définit la disposition de champs de Mediboard dans le formulaire, en lecture seule.
 */
class CExClassHostField extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_host_field_id;

    public $ex_group_id;
    public $subgroup_id;

    //public $host_type;
    public $host_class;
    public $field;

    // Grid positionning
    public $coord_label_x;
    public $coord_label_y;
    public $coord_value_x;
    public $coord_value_y;

    // Pixel positionning
    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;
    public $type;

    public $display_in_tab;
    public $tab_index;

    public $_ref_ex_group;
    public $_ref_host_object;

    public $_no_size = false;

    /** @var string */
    public $_field;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                         = parent::getSpec();
        $spec->table                  = "ex_class_host_field";
        $spec->key                    = "ex_class_host_field_id";
        $spec->uniques["coord_value"] = [
            "ex_group_id",
            "coord_value_x",
            "coord_value_y",
            "coord_label_x",
            "coord_label_y",
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["ex_group_id"] = "ref notNull class|CExClassFieldGroup cascade back|host_fields";
        $props["subgroup_id"] = "ref class|CExClassFieldSubgroup nullify back|children_host_field";

        //$props["host_type"]     = "enum list|host|reference1|reference2 default|host";
        $props["host_class"] = "str notNull";
        $props["field"]      = "str notNull canonical";

        // Grid positionning
        $props["coord_value_x"] = "num min|0 max|100";
        $props["coord_value_y"] = "num min|0 max|100";
        $props["coord_label_x"] = "num min|0 max|100";
        $props["coord_label_y"] = "num min|0 max|100";

        // Pixel positionned
        $props["coord_left"]   = "num";
        $props["coord_top"]    = "num";
        $props["coord_width"]  = "num min|1";
        $props["coord_height"] = "num min|1";
        $props["type"]         = "enum list|label|value";

        // Tab displaying
        $props['display_in_tab'] = 'bool default|0';
        $props['tab_index']      = 'num';

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

        $this->_field = $this->field;

        if (
            $this->host_class === 'CPatient'
            && in_array(
                $this->field,
                ['prenom_2', 'prenom_3', 'prenom_4', 'assure_prenom_2', 'assure_prenom_3', 'assure_prenom_4']
            )
        ) {
            $this->_field = "_{$this->field}";
        }

        $this->_view = $this->_field; // FIXME
    }

    /**
     * Load ExGroup
     *
     * @param bool $cache Use cache
     *
     * @return CExClassFieldGroup
     */
    function loadRefExGroup($cache = true)
    {
        return $this->_ref_ex_group = $this->loadFwdRef("ex_group_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExGroup($cache);
    }

    /**
     * Get the host object of $this
     *
     * @param CExObject $ex_object The ExObject to get the host object of
     *
     * @return CMbObject|null
     */
    function getHostObject(CExObject $ex_object)
    {
        // Load the list of objects
        /** @var CMbObject[] $objects */
        $objects = [];

        // Links are stored
        if ($ex_object->_id) {
            $links = $ex_object->loadRefsLinks(true);

            // Direct reference
            foreach ($links as $_link) {
                $objects[] = $_link->loadTargetObject();
            }
        } else {
            // Or not
            $objects = [
                $ex_object->_ref_object,
                $ex_object->_ref_reference_object_1,
                $ex_object->_ref_reference_object_2,
            ];
        }

        // Direct reference
        foreach ($objects as $_object) {
            if ($this->host_class == $_object->_class) {
                return $this->_ref_host_object = $_object;
            }
        }

        // Indirect references
        foreach ($objects as $_object) {
            $_obj = $_object->getRelatedObjectOfClass($this->host_class);

            if ($_obj && $_obj->_id) {
                return $this->_ref_host_object = $_obj;
            }
        }

        return $this->_ref_host_object = new $this->host_class();
    }
}
