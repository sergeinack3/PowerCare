<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Link between a CExObject and a CMbObject
 *
 * Permet de faire la liaison entre un objet de Mediboard et un CExObject.
 * Pour chaque formulaire enregistré, 3 CExLink sont enregistrés,
 * avec le level "object", "ref1", "ref2" et "add" pour les liens additionnels
 */
class CExLink extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    /** @var integer Primary key */
    public $ex_link_id;

    public $ex_class_id;
    public $ex_object_id;
    public $level;
    public $group_id;

    public $datetime_create;
    public $owner_id;

    public $object_class;
    public $object_id;
    public $_ref_object;

    /** @var CExObject */
    public $_ref_ex_object;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CMediusers */
    public $_ref_owner;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "ex_link";
        $spec->key      = "ex_link_id";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["object_id"]    = "ref notNull class|CMbObject meta|object_class cascade back|ex_links_meta";
        $props["object_class"] = "str notNull class show|0";

        $props["ex_class_id"] = "ref notNull class|CExClass back|ex_links";

        // Todo: Do not declare backprops on CExObject
        $props["ex_object_id"] = "ref notNull class|CExObject";

        $props["level"]    = "enum notNull list|object|ref1|ref2|add default|object";
        $props["group_id"] = "ref notNull class|CGroups back|ex_links";

        $props["datetime_create"] = "dateTime";
        $props["owner_id"]        = "ref class|CMediusers back|ex_links";

        return $props;
    }

    /**
     * Load ExObject
     *
     * @param bool $cache Use local cache
     *
     * @return CExObject
     */
    function loadRefExObject($cache = true)
    {
        if ($cache && $this->_ref_ex_object && $this->_ref_ex_object->_id) {
            return $this->_ref_ex_object;
        }

        $ex_object = new CExObject($this->ex_class_id);
        $ex_object->load($this->ex_object_id);

        if ($cache) {
            $this->_ref_ex_object = $ex_object;
        }

        return $ex_object;
    }

    /**
     * @inheritdoc
     *
     * Required because of the ex_object_id field
     */
    function loadFwdRef($field, $cached = false)
    {
        if ($field === "ex_object_id") {
            $ex_object = new CExObject($this->ex_class_id);
            $ex_object->load($this->ex_object_id);

            return $this->_fwd[$field] = $ex_object;
        }

        return parent::loadFwdRef($field, $cached);
    }

    /**
     * Charge l'auteur du formulaire
     *
     * @return CMediusers
     */
    function loadRefOwner(): CMediusers
    {
        return $this->_ref_owner = $this->loadFwdRef("owner_id", true);
    }

    /**
     * Load ExEclass
     *
     * @param bool $cached Use object cache
     *
     * @return CExObject|CStoredObject|null
     */
    function loadRefExClass($cached = true)
    {
        return $this->_ref_ex_class = $this->loadFwdRef('ex_class_id', $cached);
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if (!$this->_id) {
            $this->datetime_create = CMbDT::dateTime();
            $this->owner_id        = CMediusers::get()->_id;
        }

        // Manual treatment of CExObject which are not declared as a proper backprop
        if ($this->_forwardRefMerging) {
            $this->completeField('ex_class_id', 'ex_object_id', 'level');

            CExObject::repairReferences($this->ex_class_id, $this->ex_object_id, null, $this);
        }

        return parent::store();
    }

    /**
     * Mass loading of CExObjects
     *
     * @param self[] $list List of ExLinks
     *
     * @return void
     */
    static function massLoadExObjects(array $list)
    {
        $ex_object_id_by_ex_class = [];
        $ex_links_by_ex_class     = [];

        foreach ($list as $_link) {
            $_ex_class_id = $_link->ex_class_id;

            $ex_object_id_by_ex_class[$_ex_class_id][] = $_link->ex_object_id;
            $ex_links_by_ex_class[$_ex_class_id][]     = $_link;
        }

        foreach ($ex_object_id_by_ex_class as $_ex_class_id => $_ex_object_ids) {
            $_ex_object = new CExObject($_ex_class_id);

            $where = [
                "ex_object_id" => $_ex_object->getDS()->prepareIn($_ex_object_ids),
            ];

            $_ex_objects = $_ex_object->loadList($where);

            /** @var CExLink $_link */
            foreach ($ex_links_by_ex_class[$_ex_class_id] as $_link) {
                $_link->_ref_ex_object               = $_ex_objects[$_link->ex_object_id];
                $_link->_ref_ex_object->_ex_class_id = $_ex_class_id;
            }
        }
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClass($cache);
    }
}
