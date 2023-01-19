<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Readonly text in a form
 *
 * Définit un texte en lecture seule disposé dans un formulaire, sous forme de texte sans formattage, de titre,
 * ou de message informatif, avertissement ou erreur
 */
class CExClassMessage extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_message_id;

    public $ex_group_id;
    public $subgroup_id;
    public $type;
    public $predicate_id;

    public $title;
    public $text;
    public $description;
    public $tab_index;

    public $coord_title_x;
    public $coord_title_y;
    public $coord_text_x;
    public $coord_text_y;

    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;

    public $_ref_ex_group;
    public $_ref_ex_class;
    public $_ref_predicate;
    public $_ref_properties;

    public $_pixel_positionning;

    public $_default_properties;
    public $_no_size = false;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                   = parent::getSpec();
        $spec->table            = "ex_class_message";
        $spec->key              = "ex_class_message_id";
        $spec->uniques["coord"] = ["ex_group_id", "coord_title_x", "coord_title_y", "coord_text_x", "coord_text_y"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["ex_group_id"]  = "ref notNull class|CExClassFieldGroup cascade back|class_messages";
        $props["subgroup_id"]  = "ref class|CExClassFieldSubgroup nullify back|children_messages";
        $props["type"]         = "enum list|title|info|warning|error";
        $props["predicate_id"] = "ref class|CExClassFieldPredicate autocomplete|_view|true nullify back|display_messages";

        $props["title"]       = "str";
        $props["text"]        = "text notNull";
        $props["description"] = "text";
        $props["tab_index"]   = "num";

        $props["coord_title_x"] = "num min|0 max|100";
        $props["coord_title_y"] = "num min|0 max|100";
        $props["coord_text_x"]  = "num min|0 max|100";
        $props["coord_text_y"]  = "num min|0 max|100";

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

        $this->_view    = ($this->title ? $this->title : CMbString::truncate($this->text, 30));
        $this->_no_size = true;
    }

    /**
     * @param bool $cache
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
     * @return CExClass
     */
    function loadRefExClass($cache = true)
    {
        $this->_ref_ex_class       = $this->loadRefExGroup($cache)->loadRefExClass($cache);
        $this->_pixel_positionning = $this->_ref_ex_class->pixel_positionning;

        return $this->_ref_ex_class;
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExGroup($cache);
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
     * @return CExClassFieldProperty[]
     */
    function loadRefProperties()
    {
        return $this->_ref_properties = $this->loadBackRefs("properties");
    }

    /**
     * @param bool $cache
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
}
