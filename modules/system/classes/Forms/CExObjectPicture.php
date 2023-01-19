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
use Ox\Mediboard\Files\CFile;

/**
 * ExObject picture class
 *
 * Copie de CExClassPicture, associée à CExObject avec les coordonnées choisies par l'utilisateur
 * et une referénce au formulaire qui a pu être déclenché
 */
class CExObjectPicture extends CMbObject implements FormComponentInterface
{
    static $_coord_fields = [
        "coord_top",
        "coord_left",
        "coord_width",
        "coord_height",
        "coord_angle",
    ];

    public $ex_object_picture_id;

    public $ex_class_picture_id;

    // Origin form
    public $ex_class_id;
    public $ex_object_id;

    // Triggered form
    public $triggered_ex_class_id;
    public $triggered_ex_object_id;

    public $comment;

    // Pixel positionned
    public $coord_left;
    public $coord_top;
    public $coord_width;
    public $coord_height;
    public $coord_angle;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CFile */
    public $_ref_drawing;

    /** @var CExObject */
    public $_ref_ex_object;

    /** @var CExClass */
    public $_ref_triggered_ex_class;

    /** @var CExObject */
    public $_ref_triggered_ex_object;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "ex_object_picture";
        $spec->key   = "ex_object_picture_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["ex_class_picture_id"] = "ref notNull class|CExClassPicture back|ex_object_pictures";

        // Origin form
        $props["ex_class_id"] = "ref notNull class|CExClass back|ex_object_pictures";

        // Todo: Do not declare backprops on CExObject
        $props["ex_object_id"] = "ref notNull class|CExObject";

        // Triggered form
        $props["triggered_ex_class_id"] = "ref class|CExClass back|ex_object_pictures_triggering";

        // Todo: Do not declare backprops on CExObject
        $props["triggered_ex_object_id"] = "ref class|CExObject";

        $props["comment"] = "text";

        // Pixel positionned
        $props["coord_left"]   = "num";
        $props["coord_top"]    = "num";
        $props["coord_width"]  = "num min|1";
        $props["coord_height"] = "num min|1";
        $props["coord_angle"]  = "num min|0 max|359";

        return $props;
    }

    /**
     * TODO Ref pour chainer les appels à getPerm (juste appeler getPerm de CExObject)
     *
     * @param int $permType
     *
     * @return bool
     * @throws Exception
     */
    public function getPerm($permType)
    {
        $ex_object = new CExObject($this->ex_class_id);
        $ex_object->load($this->ex_object_id);

        $target = $ex_object->loadTargetObject();

        return $target->getPerm($permType);
    }

    /**
     * @param bool $cache Use cache
     *
     * @return CExClass
     */
    function loadRefTriggeredExClass($cache = true)
    {
        return $this->_ref_triggered_ex_class = $this->loadFwdRef("triggered_ex_class_id", $cache);
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
        return $this->_ref_ex_class = $this->loadFwdRef("ex_class_id", $cache);
    }

    /**
     * Load Ex Object
     *
     * @param bool $cache Use object cache
     *
     * @return CExObject
     */
    function loadRefExObject($cache = true)
    {
        return $this->_ref_ex_object = $this->loadFwdRef("ex_object_id", $cache);
    }

    function loadRefDrawing()
    {
        return $this->_ref_drawing = $this->loadNamedFile("drawing.png");
    }

    /**
     * @param bool $cache Use cache
     *
     * @return CExObject
     */
    function loadRefTriggeredExObject($cache = true)
    {
        return $this->_ref_triggered_ex_object = $this->loadFwdRef("triggered_ex_object_id", $cache);
    }
}
