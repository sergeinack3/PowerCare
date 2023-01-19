<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassEvent;
use Ox\Mediboard\System\Forms\CExClassFieldGroup;
use Ox\Mediboard\System\Forms\CExClassFieldPredicate;
use Ox\Mediboard\System\Forms\CExObjectPicture;
use Ox\Mediboard\System\Forms\FormComponentInterface;

/**
 * Picture class
 *
 * Classe qui définit la position et le comportement d'une image dans un formulaire.
 */
class CExClassPicture extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_picture_id;

    public $ex_group_id;
    public $subgroup_id;
    public $name;
    public $description;
    public $disabled;
    public $show_label;
    public $movable;
    public $drawable;
    public $in_doc_template;
    public $report_class;

    public $predicate_id;
    public $triggered_ex_class_id;

    // Pixel positionned
    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;
    public $coord_angle;

    // Distant fields from CExObjectPicture
    public $_triggered_ex_class_id;
    public $_triggered_ex_object_id;
    public $_comment;

    public $_base64_content;
    public $_file_type;

    /** @var CExClassFieldGroup */
    public $_ref_ex_group;

    /** @var CFile */
    public $_ref_file;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CExClassFieldPredicate */
    public $_ref_predicate;

    /** @var CExClass */
    public $_ref_triggered_ex_class;

    /** @var CExObjectPicture */
    public $_ref_ex_object_picture;

    public $_ex_class_id;

    /** @var CExObjectPicture */
    public $_reported_ex_object_picture;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "ex_class_picture";
        $spec->key   = "ex_class_picture_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                          = parent::getProps();
        $props["ex_group_id"]           = "ref notNull class|CExClassFieldGroup cascade back|class_pictures";
        $props["subgroup_id"]           = "ref class|CExClassFieldSubgroup nullify back|children_pictures";
        $props["name"]                  = "str notNull";
        $props["description"]           = "text";
        $props["disabled"]              = "bool notNull default|0";
        $props["show_label"]            = "bool notNull default|1";
        $props["predicate_id"]          = "ref class|CExClassFieldPredicate autocomplete|_view|true nullify back|display_pictures";
        $props["triggered_ex_class_id"] = "ref class|CExClass autocomplete|_view|true nullify back|ex_class_pictures_triggering";
        $props["movable"]               = "bool notNull default|0";
        $props["drawable"]              = "bool notNull default|0";
        $props["in_doc_template"]       = "bool notNull default|0";
        $props["report_class"]          = "enum list|" . implode("|", CExClassEvent::getReportableClasses());

        // Pixel positionned
        $props["coord_left"]   = "num";
        $props["coord_top"]    = "num";
        $props["coord_width"]  = "num min|1";
        $props["coord_height"] = "num min|1";
        $props["coord_angle"]  = "num min|0 max|359";

        $props["_ex_class_id"] = "ref class|CExClass";

        // For export
        $props['_base64_content'] = 'text';
        $props['_file_type']      = 'str';

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
     * @param bool $cache Use cache
     *
     * @return CExClassFieldPredicate
     */
    function loadRefPredicate($cache = true)
    {
        return $this->_ref_predicate = $this->loadFwdRef("predicate_id", $cache);
    }

    /**
     * @param bool $cache Use cache
     *
     * @return CExClassFieldPredicate
     */
    function loadRefTriggeredExClass($cache = true)
    {
        return $this->_ref_triggered_ex_class = $this->loadFwdRef("triggered_ex_class_id", $cache);
    }

    /**
     * Load ex field group
     *
     * @param bool $cache Use cache
     *
     * @return CExClassFieldGroup
     */
    function loadRefExGroup($cache = true)
    {
        if ($cache && $this->_ref_ex_group && $this->_ref_ex_group->_id) {
            return $this->_ref_ex_group;
        }

        return $this->_ref_ex_group = $this->loadFwdRef("ex_group_id", $cache);
    }

    /**
     * Load Ex Class
     *
     * @param bool $cache Use object cache
     *
     * @return CExClass
     */
    function loadRefExClass($cache = true)
    {
        return $this->_ref_ex_class = $this->loadRefExGroup($cache)->loadRefExClass($cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClass($cache);
    }

    /**
     * Load file
     *
     * @return CFile
     */
    function loadRefFile()
    {
        return $this->_ref_file = $this->loadNamedFile("file.jpg");
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        $reset_position = $this->fieldModified("ex_group_id") || $this->fieldModified("disabled");

        // If we change its group, we need to reset its coordinates
        if ($reset_position) {
            $this->subgroup_id = "";
        }

        $subgroup_modified = $this->fieldModified("subgroup_id");
        if ($reset_position || $subgroup_modified) {
            if (!$this->fieldModified("coord_left")) {
                $this->coord_left = "";
            }

            if (!$this->fieldModified("coord_top")) {
                $this->coord_top = "";
            }
        }

        parent::updatePlainFields();
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ((!$this->_id && $this->description) || ($this->_id && $this->fieldModified('description'))) {
            $this->description = CMbString::removeHtml($this->description);
        }

        return parent::store();
    }

    /**
     * @inheritDoc
     */
    public function getExportableFields($trads = false)
    {
        $fields = parent::getExportableFields($trads);

        $file = $this->loadRefFile();

        if ($file && $file->_id) {
            $_content = $file->getBinaryContent();

            if ($_content) {
                $fields['_base64_content'] = base64_encode($_content);
                $fields['_file_type']      = $file->file_type;
            }
        }

        return $fields;
    }
}
