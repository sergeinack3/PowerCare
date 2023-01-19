<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\CExClassWidgetDefinition;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Widget class
 *
 * Définit la disposition d'un élement de Mediboard dans un formulaire
 */
class CExClassWidget extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    static $widget_types = [
        "EditAtcdAllergies",
        "ListAtcdTrt",
        "EditTraitements",
        "EditPecInfirmiere"
    ];

    public $ex_class_widget_id;

    public $ex_group_id;
    public $subgroup_id;
    public $predicate_id;

    public $name;
    public $options;

    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;

    public $_no_size = false;

    /** @var CExClassFieldGroup */
    public $_ref_ex_group;

    /** @var CExClassFieldPredicate */
    public $_ref_predicate;

    public $_widget_definition;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "ex_class_widget";
        $spec->key   = "ex_class_widget_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["ex_group_id"]  = "ref notNull class|CExClassFieldGroup cascade back|widgets";
        $props["subgroup_id"]  = "ref class|CExClassFieldSubgroup nullify back|children_widgets";
        $props["predicate_id"] = "ref class|CExClassFieldPredicate autocomplete|_view|true nullify back|display_widgets";

        $props["name"]    = "enum notNull list|" . implode("|", self::$widget_types);
        $props["options"] = "text";

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

        $this->_view = $this->getFormattedValue("name");
    }

    /**
     * Get Widget definition
     *
     * @return CExClassWidgetDefinition|null
     */
    function getWidgetDefinition()
    {
        if (!$this->name) {
            return null;
        }

        if ($this->_widget_definition) {
            return $this->_widget_definition;
        }

        $class = "CExClassWidgetDefinition$this->name";

        return new $class();
    }

    /**
     * Get the list of widget definitions
     *
     * @return CExClassWidgetDefinition[]
     */
    static function getWidgetTypes()
    {
        $children = [];

        foreach (self::$widget_types as $_type) {
            $_class = "CExClassWidgetDefinition$_type";

            $children[$_class] = new $_class;
        }

        return $children;
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
     * @param bool $cache
     *
     * @return CExClassFieldPredicate
     */
    function loadRefPredicate($cache = true)
    {
        return $this->_ref_predicate = $this->loadFwdRef("predicate_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExGroup($cache);
    }
}
