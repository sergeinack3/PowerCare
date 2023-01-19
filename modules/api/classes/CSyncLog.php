<?php

/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CSyncLog extends CMbObject
{
    public const RESOURCE_TYPE = 'log';
    /** @var string */
    public const FIELDSET_OWNER = 'owner';
    /** @var string */
    public const FIELDSET_TARGET = 'target';

    /** @var string[] */
    public static $owner_classes = [
        'CUser',
        'CFunctions',
        'CGroups',
    ];

    /** @var int Primary key */
    public $sync_log_id;

    // db field
    /** @var string Action date and time */
    public $datetime;
    /** @var string Action performed (create|update|delete|merge) */
    public $action;
    /** @var int */
    public $object_id;
    /** @var string */
    public $object_class;

    /** @var int Owner's class */
    public $owner_class;
    /** @var int Owner's ID */
    public $owner_id;

    /** @var int User's ID */
    public $user_id;
    /** @var int Reference object ID */
    public $reference_id;
    /** @var string Reference object class */
    public $reference_class;
    /** @var string Reference date */
    public $reference_date;

    // form field
    /** @var CUser User's reference */
    public $_ref_user;
    /** @var CStoredObject */
    public $_ref_object;

    /** @var string Action performed */
    public $_action;
    /** @var string Minimal action date and time */
    public $_date_min;
    /** @var string Maximal action date and time */
    public $_date_max;
    /** @var boolean Is the object already deleted? */
    public $_deleted;
    /** @var CStoredObject|null Owner's reference */
    public $_ref_owner;
    /** @var CStoredObject Reference object */
    public $_ref_reference;

    /**
     * Gets all practitioners according to permissions
     *
     * @return array
     */
    public static function getOwnerIDs(): array
    {
        $practitioners = CConsultation::loadPraticiens(PERM_READ);

        if (!$practitioners) {
            return [];
        }

        return CMbArray::pluck($practitioners, '_id');
    }

    /**
     * Gets all practitioners according to permissions
     *
     * @return CMediusers[]
     */
    public static function getOwners(): array
    {
        $practitioners = CMediusers::get()->loadPraticiens(PERM_READ, null, null, false, true, false);

        if (!$practitioners) {
            return [];
        }

        return $practitioners;
    }

    /**
     * Get CFunctions's IDs
     *
     * @return array|null
     */
    public static function getFunctionIDs(): ?array
    {
        $user_id = CUser::get()->_id;

        if (!$user_id) {
            return [];
        }

        $cache = new Cache('CSyncLog.getFunctionIDs', "sync-{$user_id}-function_ids", Cache::INNER_OUTER, 300);

        if ($cache->exists()) {
            return $cache->get();
        }

        $functions = static::getFunctions();

        if (!$functions) {
            return [];
        }

        $function_ids = CMbArray::pluck($functions, '_id');

        return $cache->put($function_ids);
    }

    /**
     * Get CFunctions[]
     *
     * @return CFunctions[]
     * @throws Exception
     */
    public static function getFunctions(): array
    {
        $function = new CFunctions();

        return $function->loadListWithPerms(PERM_READ);
    }

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'sync_log';
        $spec->key      = 'sync_log_id';
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                    = parent::getProps();
        $props['datetime']        = 'dateTime notNull fieldset|default';
        $props['user_id']         = 'ref class|CUser notNull fieldset|target back|sync_logs cascade';
        $props['action']          = 'enum list|create|update|delete|merge fieldset|default default|create notNull';
        $props['owner_class']     = 'enum list|' . implode('|', static::$owner_classes) . ' fieldset|owner';
        $props['owner_id']        = 'ref class|CStoredObject meta|owner_class back|owner_sync_logs fieldset|owner cascade';
        $props['reference_class'] = 'str class fieldset|target';
        $props['reference_id']    = 'ref class|CMbObject meta|reference_class cascade back|reference_sync_logs fieldset|target';
        $props['reference_date']  = 'date fieldset|target';
        $props['object_id']       = 'ref notNull class|CMbObject meta|object_class unlink back|object_sync_logs fieldset|default';
        $props['object_class']    = 'str notNull class show|0 fieldset|default';
        $props['_action']         = 'set list|create|update|delete|merge';
        $props['_date_min']       = 'dateTime';
        $props['_date_max']       = 'dateTime';
        $props['_deleted']        = 'bool';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if (!$this->user_id) {
            $user = CUser::get();

            if (!$user || !$user->_id) {
                return 'common-error-Missing parameter';
            }

            $this->user_id = $user->_id;
        }

        $this->datetime = ($this->datetime) ?: CMbDT::dateTime();

        return parent::store();
    }

    /**
     * Loads reference object
     *
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefReference(): ?CStoredObject
    {
        return $this->_ref_reference = $this->loadFwdRef('reference_id', true);
    }

    /**
     * Load user
     *
     * @return CStoredObject|CUser|null
     * @throws Exception
     */
    public function loadRefUser(): ?CUser
    {
        return $this->_ref_user = $this->loadFwdRef('user_id', true);
    }

    /**
     * Sets owner object
     *
     * @param CStoredObject $object Object
     *
     * @return CStoredObject
     */
    public function setOwner(CStoredObject $object): CStoredObject
    {
        $this->owner_id    = $object->_id;
        $this->owner_class = $object->_class;

        return $this->_ref_owner = $object;
    }

    /**
     * Sets reference object
     *
     * @param CStoredObject $object Object
     *
     * @return CStoredObject
     */
    public function setReferenceObject(CStoredObject $object): CStoredObject
    {
        $this->reference_id    = $object->_id;
        $this->reference_class = $object->_class;

        return $this->_ref_reference = $object;
    }

    /**
     * Sets reference object
     *
     * @param string $date Object date
     *
     * @return string
     */
    public function setReferenceDate(string $date): string
    {
        return $this->reference_date = $date;
    }

    /**
     * Checks if target object has been deleted
     *
     * @return bool|int
     * @throws Exception
     */
    public function isDeleted(): bool
    {
        if ($this->action == 'delete') {
            return $this->_deleted = true;
        }

        $ds = $this->getDS();

        $where = [
            'datetime'     => $ds->prepare('>= ?', $this->datetime),
            'object_class' => $ds->prepare('= ?', $this->object_class),
            'object_id'    => $ds->prepare('= ?', $this->object_id),
            'action'       => "= 'delete'",
        ];

        return $this->_deleted = $this->countList($where);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function getPerm($perm_type)
    {
        $this->loadRefOwner();
        if (!$this->_ref_owner || !$this->_ref_owner->_id) {
            return parent::getPerm($perm_type);
        }

        return $this->_ref_owner->getPerm($perm_type);
    }

    /**
     * Load owner
     *
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefOwner(): ?CStoredObject
    {
        return $this->_ref_owner = $this->loadFwdRef('owner_id', true);
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @deprecated
     */
    public function setObject(CStoredObject $object): void
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    public function loadRefsFwd(): void
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     */
    public function loadTargetObject(bool $cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }
}
