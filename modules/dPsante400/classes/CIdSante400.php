<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * External identifier
 */
class CIdSante400 extends CStoredObject implements IGroupRelated
{
    /** @var string */
    public const RESOURCE_TYPE = "idSante400";

    /** @var string */
    public const FIELDSET_DEFAULT = 'default';

    public $id_sante400_id;

    // DB fields
    public $id400;
    public $tag;
    public $datetime_create;
    public $last_update;

    public $object_class;
    public $object_id;
    public $_ref_object;

    // Derivate fields
    public $_last_id;

    // Filter fields
    public $_start_date;
    public $_end_date;
    public $_type;
    public $_no_synchro_eai = false;

    /**
     * Get matches
     *
     * @param string $object_class Object class
     * @param string $tag          Tag name
     * @param string $value        Idex
     * @param string $object_id    Object ID
     *
     * @return CIdSante400[] The matching externals ID
     */
    static function getMatches($object_class, $tag, $value, $object_id = null)
    {
        $idex               = new self;
        $idex->object_class = $object_class;
        $idex->tag          = $tag;
        $idex->id400        = $value;
        $idex->object_id    = $object_id;

        return $idex->loadMatchingListEsc();
    }

    /**
     * Get idex value
     *
     * @param string $object_class Object class
     * @param string $tag          Tag name
     * @param string $value        Idex
     * @param string $object_id    Object ID
     *
     * @return string Value the matching external ID
     */
    static function getValue($object_class, $tag, $value, $object_id = null)
    {
        return self::getMatch($object_class, $tag, $value, $object_id)->id400;
    }

    /**
     * Get match
     *
     * @param string $object_class Object class
     * @param string $tag          Tag name
     * @param string $value        Idex
     * @param string $object_id    Object ID
     *
     * @return CIdSante400 The matching external ID
     */
    static function getMatch($object_class, $tag, $value, $object_id = null)
    {
        $idex               = new self;
        $idex->object_class = $object_class;
        $idex->tag          = $tag;
        $idex->id400        = $value;
        $idex->object_id    = $object_id;

        $idex->loadMatchingObjectEsc();

        return $idex;
    }

    /**
     * Static shortcut to idex value for a given object
     *
     * @param CMbObject $mbObject Object
     * @param string    $tag      Tag
     *
     * @return string
     */
    static function getValueFor(CMbObject $mbObject, $tag = null)
    {
        return self::getMatchFor($mbObject, $tag)->id400;
    }

    /**
     * Static shortcut to to idex for a given object
     *
     * @param CMbObject $mbObject Object
     * @param string    $tag      Tag
     *
     * @return CIdSante400
     */
    static function getMatchFor(CMbObject $mbObject, $tag = null)
    {
        $idex = new self();
        $idex->loadLatestFor($mbObject, $tag);

        return $idex;
    }

    /**
     * Loads a specific id400 for a given object (and optionnaly tag)
     *
     * @param CMbObject $mbObject Object
     * @param string    $tag      Tag name
     *
     * @return CMbObject Id of the loaded object
     */
    function loadLatestFor($mbObject, $tag = null)
    {
        $object_class = $mbObject->_class;
        if (!$mbObject instanceof CMbObject) {
            trigger_error("Impossible d'associer un identifiant Santé 400 à un objet de classe '$object_class'");
        }

        $this->_id          = null;
        $this->object_class = $object_class;
        $this->object_id    = $mbObject->_id;
        $this->tag          = $tag;

        // Don't load if object is undefined
        if ($mbObject->_id) {
            $this->loadMatchingObject("datetime_create DESC");
        }

        return $mbObject->_id;
    }

    /**
     * Mass load mechanism for forward references of an object collection
     *
     * @param CMbObject[] $objects Array of objects
     * @param string      $tag     Tag
     *
     * @return self[] Loaded collection, null if unavailable, with ids as keys of guids for meta references
     */
    static function massGetMatchFor($objects, $tag)
    {
        if (!count($objects)) {
            return [];
        }

        $object = reset($objects);

        $idex                  = new self();
        $where["object_class"] = " = '$object->_class'";
        $where["tag"]          = " = '$tag'";
        $where["object_id"]    = CSQLDataSource::prepareIn(CMbArray::pluck($objects, "_id"));

        return $idex->loadList($where, 'datetime_create DESC');
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'id_sante400';
        $spec->key      = 'id_sante400_id';
        $spec->loggable = CMbObjectSpec::LOGGABLE_HUMAN;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                    = parent::getProps();
        $props["id400"]           = "str notNull maxLength|80 fieldset|default";
        $props["tag"]             = "str maxLength|80 fieldset|default";
        $props["datetime_create"] = "dateTime notNull fieldset|default";
        $props["last_update"]     = "dateTime notNull fieldset|default";
        $props["object_id"]       = "ref notNull class|CStoredObject meta|object_class cascade back|identifiants fieldset|default";
        $props["object_class"]    = "str notNull class show|0 fieldset|default";

        $props["_start_date"] = "dateTime";
        $props["_end_date"]   = "dateTime";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        if ($this->last_update === "") {
            $this->last_update = CMbDT::dateTime();
        }

        parent::updatePlainFields();
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_shortview = "$this->id400";

        $this->_view = "[$this->id400] ($this->tag)";
    }

    /**
     * @inheritdoc
     */
    function loadMatchingList(
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true
    ) {
        if (!$order) {
            $order = "`last_update` DESC";
        }

        return parent::loadMatchingList($order, $limit, $group, $ljoin, $index, $strict);
    }

    /**
     * Loads list of idex for a given object and a wildcarded tag
     *
     * @param CMbObject $mbObject Object
     * @param string    $tag      Tag name
     *
     * @return array|CMbObject found ideces
     */
    function loadLikeListFor($mbObject, $tag = null)
    {
        $object_class = $mbObject->_class;
        if (!$mbObject instanceof CMbObject) {
            trigger_error("Impossible d'associer un identifiant Santé 400 à un objet de classe '$object_class'");
        }

        $where["object_id"]    = "= '$mbObject->_id'";
        $where["object_class"] = "= '$mbObject->_class'";
        $where["tag"]          = "LIKE '$tag'";

        return $this->loadList($where);
    }

    /**
     * @inheritdoc
     */
    function loadList(
        $where = null,
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        $having = null,
        bool $strict = true,
        ?int $limit_time = null
    ) {
        if (!$order) {
            $order = "`last_update` DESC";
        }

        return parent::loadList($where, $order, $limit, $group, $ljoin, $index, $having, $strict, $limit_time);
    }

    /**
     * Load first idex for a given object and a wildcarded tag
     *
     * @param CMbObject $mbObject Object
     * @param string    $tag      Tag name
     *
     * @return CMbObject found idex
     */
    function loadLikeLatestFor($mbObject, $tag = null)
    {
        $object_class = $mbObject->_class;
        if (!$mbObject instanceof CMbObject) {
            trigger_error("Impossible d'associer un identifiant Santé 400 à un objet de classe '$object_class'");
        }

        $where["object_id"]    = "= '$mbObject->_id'";
        $where["object_class"] = "= '$mbObject->_class'";
        $where["tag"]          = "LIKE '$tag'";

        $this->loadObject($where);
    }

    /**
     * @inheritdoc
     */
    function loadObject(
        $where = null,
        $order = null,
        $group = null,
        $ljoin = null,
        $index = null,
        $having = null,
        bool $strict = true
    ) {
        if (!$order) {
            $order = "`last_update` DESC";
        }

        return parent::loadObject($where, $order, $group, $ljoin, $index, $having, $strict);
    }

    /**
     * Tries to get an already bound object if id400 is not older than delay
     *
     * @param int $delay hours number of cache duration, if null use module config
     *
     * @return CMbObject
     */
    function getCachedObject($delay = null)
    {
        // Get config cache duration
        if (null === $delay) {
            $delay = CAppUI::conf("dPsante400 cache_hours");
        }

        // Look for object
        $this->_id = null;
        $this->loadMatchingObject();
        $this->loadRefsFwd();

        // Check against cache duration
        if (CMbDT::dateTime("+ $delay HOURS", $this->last_update) < CMbDT::dateTime()) {
            $this->_ref_object = new $this->object_class;
        }

        return $this->_ref_object;
    }

    /**
     * @inheritdoc
     */
    function loadMatchingObject($order = null, $group = null, $ljoin = null, $index = null, bool $strict = true)
    {
        if (!$order) {
            $order = "`last_update` DESC";
        }

        return parent::loadMatchingObject($order, $group, $ljoin, $index, $strict);
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadRefObject();
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
     * @throws Exception
     */
    public function loadRefObject(): CStoredObject
    {
        return $this->_ref_object = CMbObject::loadFromGuid("$this->object_class-$this->object_id");
    }

    /**
     * Tries to get an already bound object if idex
     *
     * @return CMbObject
     */
    function getMbObject()
    {
        // Look for object
        $this->_id = null;
        $this->loadMatchingObject();
        $this->loadRefsFwd();

        // Always instanciate
        if (!$this->_ref_object) {
            $this->_ref_object = new $this->object_class;
        }

        return $this->_ref_object;
    }

    /**
     * Binds the idex to an object, and updates the object
     * Will only bind default object properties when it's created
     *
     * @param CMbObject $mbObject        Object
     * @param CMbObject $mbObjectDefault Default object
     * @param bool      $no_synchro_eai
     *
     * @return void
     * @throws Exception
     */
    function bindObject(&$mbObject, $mbObjectDefault = null, $no_synchro_eai = false)
    {
        $object_class = $mbObject->_class;
        if (!$mbObject instanceof CMbObject) {
            trigger_error("Impossible d'associer un identifiant Santé 400 à un objet de classe '$object_class'");
        }

        $this->object_class = $object_class;
        $this->object_id    = $mbObject->_id;
        $this->last_update  = null; // In case already defined
        $this->loadMatchingObject();
        $this->_ref_object = null; // Prevent optimisation errors
        $this->loadRefObject();

        // Object has not been found : never created or deleted since last binding
        if (!@$this->_ref_object->_id && $mbObjectDefault) {
            $mbObjectDefault->nullifyEmptyFields();
            $mbObject->extendsWith($mbObjectDefault);
        }

        // Create/update bound object
        $mbObject->_id = $this->object_id;
        $mbObject->updatePlainFields();
        $mbObject->repair();

        if ($no_synchro_eai) {
            $mbObject->_no_synchro_eai = true;

            // Old object has to be loaded before all notifications
            $old_object = $mbObject->loadOldObject();
        }

        if ($msg = $mbObject->store()) {
            throw new Exception($msg);
        }

        $this->object_id = $mbObject->_id;

        // Create/update the idSante400
        if ($msg = $this->store()) {
            throw new Exception($msg);
        }

        if ($no_synchro_eai) {
            $mbObject->_old            = $old_object;
            $mbObject->_no_synchro_eai = false;
            $mbObject->notify(ObjectHandlerEvent::AFTER_STORE());
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if (!$this->_id) {
            $this->datetime_create = $this->last_update = "now";
        }

        // On modifie la date uniquement si l'idex a été modifié
        if ($this->objectModified()) {
            $this->last_update = "now";
        }

        return parent::store();
    }

    /**
     * Return type if it's special (e.g. IPP/NDA/...)
     *
     * @return string|null
     */
    function getSpecialType()
    {
        return $this->_type = $this->loadRefObject()->getSpecialIdex($this);
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
     * @return CGroups|null
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        $target = $this->loadRefObject();
        if ($target instanceof IGroupRelated) {
            return $target->loadRelGroup();
        }

        return null;
    }
}
