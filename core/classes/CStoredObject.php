<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Closure;
use Exception;
use FineDiff\Diff;
use FineDiff\Granularity\Word;
use FineDiff\Render\Text;
use InvalidArgumentException;
use JsonSerializable;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Exceptions\CanNotMerge;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CFloatSpec;
use Ox\Core\FieldSpecs\CHtmlSpec;
use Ox\Core\FieldSpecs\CPhpSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\FieldSpecs\CTextSpec;
use Ox\Core\FieldSpecs\CXmlSpec;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\Admin\CLogAccessMedicalData;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\CObjectClass;
use Ox\Mediboard\System\CObjectUuid;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;
use ReturnTypeWillChange;

/**
 * Mediboard ORM persistance layer
 * - Persistance: storage, navigation, querying, checking, merging, seeking, cache, userlog, userAction
 * - Configuration: permissions, object configs
 * - Classification: modules
 */
class CStoredObject extends CModelObject implements JsonSerializable
{
    use RequestTrait;

    public const RELATION_IDENTIFIANT = 'identifiants';

    private const COMPRESS_MIN_LEN      = 10;
    private const MAX_SEEKABLE_META_REF = 5;

    public static $useObjectCache = true;
    public static $objectCounts   = [];
    public static $cachableCounts = [];
    public static $deletedCounts  = [];

    /** @var CCanDo */
    public $_can;

    /**
     * @var bool Read permission for the object
     * @deprecated
     */
    public $_canRead;

    /**
     * @var bool Edit permission for the object
     * @deprecated
     */
    public $_canEdit;

    public $_external; // true if object is has remote ids
    public $_totalSeek;
    public $_totalWithPerms;

    // Object recorded by sender
    public $_eai_sender_guid;

    /** @var self[][] Back references collections */
    public $_back = [];

    /** @var int[] Back references counts */
    public $_count = [];

    /** @var self[] Forward references */
    public $_fwd = [];

    /** @var  self[] Array representation of the object's evolution */
    public $_history;

    /**
     * @var CUserLog[]
     */
    public $_ref_logs;
    /**
     * @var CUserLog
     */
    public $_ref_first_log;
    /**
     * @var CUserLog
     */
    public $_ref_last_log;
    /**
     * @var CUserLog Log related to the current store or delete
     */
    public $_ref_current_log;
    /** @var self The object in database */
    public $_old;
    /** @var int Field modified count when storing */
    public $_count_modified;
    public $_merging;
    public $_purge;
    public $_forwardRefMerging;
    public $_mergeDeletion;
    public $_fusion;

    public $_ignore_eai_handlers = false;

    // Behaviour fields
    public $_jsonFields = [
        "_class",
        "_id",
        "_guid",
        "_view",
    ];
    /** @var CUserAction[] */
    private $_ref_user_actions;
    /** @var CUserAction */
    private $_ref_first_user_action;
    /** @var CUserAction */
    private $_ref_last_user_action;
    /** @var CUserAction related to the current store or delete */
    private $_ref_current_user_action;

    /** @var array */
    protected $_external_ids = [];

    /** @var CInteropReceiver */
    public $_receiver;

    /**
     * @var string[]
     */
    static $fulltext_query_language_modes = [
        'boolean' => 'IN BOOLEAN MODE',
        'natural' => 'IN NATURAL LANGUAGE MODE',
    ];

    /**
     * @var string[]
     */
    static $fulltext_query_operators = ['or', 'and'];

    /**
     * Syntactic sugar for CStoredObject::load function
     *
     * @param int $id Object's identifier
     *
     * @return static
     * @throws Exception
     */
    static function find($id)
    {
        $object = new static();

        return $object->load($id);
    }

    /**
     * Syntactic sugar for CStoredObject::load function, with success checking
     *
     * @param int $id Object's identifier
     *
     * @throws CMbModelNotFoundException|Exception
     * @return static
     */
    static function findOrFail($id)
    {
        $object = static::find($id);

        if (!$object || !$object->_id) {
            throw new CMbModelNotFoundException('common-error-Object not found');
        }

        return $object;
    }

    /**
     * Syntactic sugar for CStoredObject::load function, with new object instanciation if model not found
     *
     * @param int $id Object's identifier
     *
     * @return static
     * @throws Exception
     */
    static function findOrNew($id)
    {
        try {
            $object = static::findOrFail($id);
        } catch (CMbModelNotFoundException $e) {
            return new static();
        }

        return $object;
    }

    /**
     * Build and load an object with a given GUID
     *
     * @param string $guid   Object GUID
     * @param bool   $cached Use cache
     *
     * @return CStoredObject|CModelObject|null Loaded object, null if inconsistent Guid
     * @throws Exception
     */
    static function loadFromGuid($guid, $cached = false)
    {
        if (!self::isGuid($guid)) {
            return null;
        }

        [$class, $id] = explode('-', $guid);

        $object = self::getInstance($class);

        if ($object instanceof CStoredObject) {
            if ($id && $id != 'none') {
                return $cached ? $object->getCached($id) : $object->load($id);
            }

            return $object;
        }

        return null;
    }

    /**
     * Check if a string is a GUID
     *
     * @param string $guid
     *
     * @return bool
     */
    static function isGuid($guid)
    {
        // Object class must be a string, object_id must be number or none
        if (is_string($guid) && preg_match('/(?<object_class>\w+)-(?<object_id>\d+|none)/', $guid, $matches)) {
            // Cannot use "class_exists" because of CExObjects
            return (self::getInstance($matches['object_class']) !== null);
        }

        return false;
    }

    /**
     * Loads a list of objects based on given GUIDs
     *
     * @param array $guids GUIDs
     *
     * @return array
     * @throws Exception
     */
    static function loadFromGuids($guids)
    {
        if (!$guids) {
            return [];
        }

        $classes = [];
        foreach ($guids as $_guid) {
            [$_class, $_id] = explode('-', $_guid);

            if (!isset($classes[$_class])) {
                $classes[$_class] = [];
            }

            $classes[$_class][] = $_id;
        }

        $objects = [];
        foreach ($classes as $_class => $_ids) {
            /** @var CStoredObject $_object */
            $_object = static::getInstance($_class);

            if (!$_object) {
                continue;
            }

            $objects[$_class] = $_object->loadAll($_ids);
        }

        return $objects;
    }

    /**
     * Load all objects for given identifiers
     *
     * @param array $ids    List of identifiers
     * @param array $order  Order SQL statement
     * @param bool  $strict If strict, performs some additional checks in the request
     *
     * @return static[] List of objects
     * @throws Exception
     */
    function loadAll($ids, $order = null, bool $strict = true)
    {
        // Trim real ids
        CMbArray::removeValue("", $ids);
        $ids = array_unique($ids);

        // Don't query cached objects
        $cached = [];
        foreach ($ids as $_key => $_id) {
            if ($this->isCached($_id)) {
                $cached[$_id] = $this->getCached($_id);
                unset($ids[$_key]);
            }
        }

        // Only run query when there's something to look for
        $loaded = [];
        if (!empty($ids)) {
            $where[$this->_spec->key] = CSQLDataSource::prepareIn($ids);
            $loaded                   = $this->loadList($where, $order, null, null, null, null, null, $strict);
        }

        // array_merge won't preserve keys
        return $loaded + $cached;
    }

    /**
     * Tell wether object is already cached
     *
     * @param integer $id The actual object identifier
     *
     * @return bool
     **/
    function isCached($id)
    {
        // if layers ever becomes more than INNER, only test existence on INNER
        $cache = new Cache("CStoredObject.cache", "$this->_class-$id", Cache::INNER);

        return $cache->exists();
    }

    /**
     * Retrieve an already registered object from cache if available, performs a standard load otherwise
     *
     * @param integer $id The actual object identifier
     *
     * @return static the retrieved object
     * @throws Exception
     */
    function getCached($id)
    {
        $cache = new Cache("CStoredObject.cache", "$this->_class-$id", Cache::INNER);
        if ($object = $cache->get()) {
            return $object;
        }

        $this->load($id);

        return $this;
    }

    /**
     * Load an object by its identifier
     *
     * @param integer $id [optional] The object's identifier
     *
     * @return static|bool The loaded object if found, false otherwise
     * @throws Exception
     */
    function load($id = null)
    {
        if ($id) {
            $this->_id = $id;
        }

        $spec = $this->_spec;

        if (!$this->_id || !$spec->table || !$spec->key) {
            return false;
        }

        $query = $spec->ds->prepare("SELECT * FROM `{$spec->table}` WHERE `{$spec->key}` = ?;", $this->_id);

        $object = $spec->ds->loadObject($query, $this);

        if (!$object) {
            $this->_id = null;

            return false;
        }

        $this->checkConfidential();
        $this->registerCache();
        $this->updateFormFields();

        return $this;
    }

    /**
     * Register the object into cache
     *
     * @return void
     */
    public function registerCache()
    {
        $class = $this->_class;

        // Object counts
        CMbArray::inc(self::$objectCounts, $class);

        // Cache enabled ?
        if (!self::$useObjectCache) {
            return;
        }

        $cache = new Cache("CStoredObject.cache", "$class-$this->_id", Cache::INNER);

        // if layers ever becomes more than INNER, only test existence on INNER
        if ($cache->exists()) {
            CMbArray::inc(self::$cachableCounts, $class);
        }

        $cache->put($this);
    }

    /**
     * Object list by a request constructor
     *
     * @param array  $where  Where SQL statement
     * @param array  $order  Order SQL statement
     * @param string $limit  Limit SQL statement
     * @param array  $group  Group by SQL statement
     * @param array  $ljoin  Left join SQL statement collection
     * @param array  $index  Force index
     * @param array  $having Having SQL statement
     * @param bool   $strict If strict, performs some additional checks in the request
     *
     * @return static[] List of found objects, null if module is not installed
     * @throws Exception
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
        if (!$this->_ref_module) {
            return null;
        }

        $request = new CRequest($strict);
        $request->addLJoin($ljoin);
        $request->addWhere($where);
        $request->addGroup($group);
        $request->addOrder($order);
        $request->setLimit($limit);
        $request->addHaving($having);
        $request->addForceIndex($index);

        return $this->loadQueryList($request->makeSelect($this), $limit_time);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return $this|null
     * @throws Api\Exceptions\ApiRequestException
     */
    public function loadListFromRequestApi(
        RequestApi $request_api,
        $group = null,
        $ljoin = null,
        $index = null,
        $having = null,
        bool $strict = true
    ) {
        $where = $request_api->getFilterAsSQL($this->getDS());
        $order = $request_api->getSortAsSql();
        $limit = $request_api->getLimitAsSql();

        return $this->loadList($where, $order, $limit, $group, $ljoin, $index, $having, $strict);
    }

    /**
     * Load array of objects from an SQL SELECT query and pass it to the callback
     *
     * @param string  $query    The SQL query
     * @param Closure $callback The callback to pass the array
     *
     * @return mixed
     * @throws Exception
     */
    public function loadQueryChunks($query, Closure $callback)
    {
        $ds = $this->_spec->ds;

        if (!$ds) {
            return call_user_func_array($callback, [[]]);
        }

        $objects = [];

        foreach ($ds->loadList($query) as $_row) {
            /** @var static $newObject */
            $newObject = new static;
            $newObject->bind($_row, false);

            $newObject->checkConfidential();
            $newObject->updateFormFields();

            // Some external classes do not have primary keys
            if ($newObject->_id) {
                $objects[$newObject->_id] = $newObject;
            } else {
                $objects[] = $newObject;
            }
        }

        // Using call_user_func_array instead of call_user_func because we need to pass $objects by reference
        return call_user_func_array($callback, [&$objects]);
    }

    /**
     * Return an array of objects from an SQL SELECT query
     *
     * @param string $query SQL Query
     *
     * @return static[] List of found objects, null if module is not installed
     * @throws Exception
     *
     * @todo To optimize request, only select object ids in $query
     * @todo To replace fetchAssoc, instanciation and bind [while ($newObject = $ds->fetchObject($res, $this->_class))]
     */
    function loadQueryList($query, ?int $limit_time = null)
    {
        global $m, $action;

        $objects = [];
        $rows    = [];

        $ds = $this->_spec->ds;

        if ($ds) {
            $rows = $ds->loadList($query, null, $limit_time);

            foreach ($rows as $_row) {
                /** @var static $newObject */
                $newObject = new static;
                $newObject->bind($_row, false);

                $newObject->checkConfidential();
                $newObject->updateFormFields();
                $newObject->registerCache();

                // Some external classes do not have primary keys
                if ($newObject->_id) {
                    $objects[$newObject->_id] = $newObject;
                } else {
                    $objects[] = $newObject;
                }
            }
        }

        if ((is_countable($objects) && is_countable($rows)) && (count($objects) != count($rows))) {
            trigger_error(
                "Missing group by in $m / $action (rows : " . count($rows) . ", objects : " . count(
                    $objects
                ) . ") : $query",
                E_USER_NOTICE
            );
        }

        return $objects;
    }

    /**
     * Filtering objects by tags
     *
     * @param CMbObject[] $objects  An array of objects
     * @param array       $tags_in  Tags which must be present (IDs)
     * @param array       $tags_out Forbidden tags (IDs)
     *
     * @return void
     * @throws Exception
     */
    static function filterByTags(&$objects, $tags_in = [], $tags_out = [])
    {
        static::filterByBackrefs($objects, 'tag_items', 'tag_id', $tags_in, $tags_out);
    }

    /**
     * Filtering objects by backrefs
     *
     * @param CMbObject[] $objects       An array of objects
     * @param string      $back_name     Backref name
     * @param string      $back_property Backref property to collect
     * @param array       $backs_in      Objects which must be present (IDs)
     * @param array       $backs_out     Forbidden objects (IDs)
     *
     * @return void
     * @throws Exception
     */
    static function filterByBackrefs(&$objects, $back_name, $back_property, $backs_in = [], $backs_out = [])
    {
        if (!$objects) {
            return;
        }

        $backs_by_object = [];

        CStoredObject::massLoadBackRefs($objects, $back_name);

        foreach ($objects as &$_object) {
            $backs_by_object[$_object->_id] = CMbArray::pluck($_object->_back[$back_name], $back_property);
        }

        if ($backs_in) {
            $count = count($backs_in);

            $objects = array_filter(
                $objects,
                function ($_object) use ($backs_in, $count, $backs_by_object) {
                    if (!isset($backs_by_object[$_object->_id])) {
                        return false;
                    }

                    return (count(array_intersect($backs_by_object[$_object->_id], $backs_in)) >= $count);
                }
            );
        }

        if ($backs_out) {
            $objects = array_filter(
                $objects,
                function ($_object) use ($backs_out, $backs_by_object) {
                    if (!isset($backs_by_object[$_object->_id])) {
                        $backs_by_object[$_object->_id] = [];
                    }

                    return (!count(array_intersect($backs_by_object[$_object->_id], $backs_out)));
                }
            );
        }
    }

    /**
     * Mass load mechanism for back reference collections of an object collection
     * Will maintain back collections AND back counts
     *
     * @param self[] $objects     Array of objects
     * @param string $backName    Name of backward reference
     * @param null   $order       Order clause
     * @param array  $where       Additional where clauses
     * @param array  $ljoin       Additionnal ljoin clauses
     * @param string $backNameAlt BackName Alt
     * @param bool   $strict      If strict, performs some additional checks in the request
     *
     * @return int|null|self[] Foundobjects, null if collection is unavailable
     * @throws Exception
     */
    static function massLoadBackRefs(
        $objects,
        $backName,
        $order = null,
        $where = [],
        $ljoin = [],
        $backNameAlt = "",
        bool $strict = true
    ) {
        if ($objects === null) {
            return null;
        }

        if (!count($objects)) {
            return [];
        }

        $object = reset($objects);
        if (!$backSpec = $object->makeBackSpec($backName)) {
            return null;
        }

        // No existing class
        if (!self::classExists($backSpec->class)) {
            return null;
        }

        /** @var self $backObject */
        $backObject = new $backSpec->class;
        $backField  = $backSpec->field;

        // Cas du module non installé
        if (!$backObject->_ref_module) {
            return null;
        }

        // With old versions of mysql, remove '' fields
        $ids = CMbArray::pluck($objects, "_id");
        CMbArray::removeValue("", $ids);

        $backName = $backNameAlt ? $backNameAlt : $backName;

        // Initilize collections and counts
        foreach ($objects as $_object) {
            $_object->_count[$backName] = 0;
            $_object->_back[$backName]  = [];
        }

        // No stored objects
        if (!count($ids)) {
            return 0;
        }

        // Meta objects case
        /** @var CRefSpec $backSpec */
        $backSpec  = $backObject->_specs[$backField];
        $backMeta  = $backSpec->meta;
        $backTable = $backObject->_spec->table;
        if ($backMeta) {
            $_spec = $backObject->_specs[$backMeta];

            if ($_spec instanceof CRefSpec && $_spec->class === 'CObjectClass') {
                $_class = CObjectClass::getID($object->_class);
            } else {
                $_class = $object->_class;
            }

            $where["{$backTable}.{$backMeta}"] = "= '$_class'";
        }

        // Actual load query
        $ds                = $backObject->_spec->ds;
        $where[$backField] = $ds->prepareIn($ids);
        $backObjects       = $backObject->loadList($where, $order, null, null, $ljoin, null, null, $strict);

        // Dispatch back objects into objects collections
        foreach ($backObjects as $_backObject) {
            $object = $objects[$_backObject->$backField];
            $object->_count[$backName]++;
            $object->_back[$backName][$_backObject->_id] = $_backObject;
        }

        // Found objects
        return $backObjects;
    }

    /**
     * Mass count mechanism for back reference collections of an object collection
     *
     * @param self[] $objects     Array of objects
     * @param string $backName    Name of backward reference
     * @param array  $where       Additional where clauses
     * @param array  $ljoin       Additionnal ljoin clauses
     * @param string $backNameAlt BackName Alt
     *
     * @return int|null Total count among objects, null if collection count is unavailable
     * @throws Exception
     */
    static function massCountBackRefs($objects, $backName, $where = [], $ljoin = [], $backNameAlt = "")
    {
        if (!is_countable($objects) || !count($objects)) {
            return null;
        }

        $object = reset($objects);
        if (!$backSpec = $object->makeBackSpec($backName)) {
            return null;
        }

        // No existing class
        if (!self::classExists($backSpec->class)) {
            return null;
        }

        /** @var self $backObject */
        $backObject = new $backSpec->class;
        $backField  = $backSpec->field;

        // Cas du module non installé
        if (!$backObject->_ref_module) {
            return null;
        }

        // With old versions of mysql, remove '' fields
        $ids = CMbArray::pluck($objects, "_id");
        CMbArray::removeValue("", $ids);

        $backName = $backNameAlt ? $backNameAlt : $backName;

        if (!is_countable($ids) || !count($ids)) {
            foreach ($objects as $_object) {
                $_object->_count[$backName] = 0;
            }

            return 0;
        }

        $backTable = $backObject->_spec->table;
        // TODO Refactor using CRequest
        $query = "SELECT `$backTable`.`$backField`, COUNT({$backObject->_spec->key}) FROM `{$backTable}`";

        if ($ljoin && count($ljoin)) {
            foreach ($ljoin as $table => $condition) {
                $query .= "\nLEFT JOIN `$table` ON $condition ";
            }
        }

        $ds    = $backObject->_spec->ds;
        $query .= "WHERE `$backTable`.`$backField` " . $ds->prepareIn($ids);

        // Additional where clauses
        foreach ($where as $_field => $_clause) {
            $split = explode(".", $_field);
            if (is_string($_field)) {
                $query .= "\nAND " . (count($split) > 1 ? "`$split[0]`.`$split[1]`" : "`$_field`") . " $_clause";
            } else {
                $query .= "\nAND $_clause";
            }
        }

        // Meta objects case
        /** @var CRefSpec $backSpec */
        $backSpec = $backObject->_specs[$backField];
        $backMeta = $backSpec->meta;
        if ($backMeta) {
            $class = $backMeta == 'object_class_id' ? $object->getObjectClassID() : $object->_class;
            $query .= "\nAND `$backTable`. `$backMeta` = '$class'";
        }

        // Group by object key
        $query  .= "\nGROUP BY `$backTable`.`$backField`";
        $counts = $ds->loadHashList($query);

        // Populate object counts
        $total = 0;
        foreach ($objects as $_object) {
            $count = isset($counts[$_object->_id]) ? $counts[$_object->_id] : 0;
            $total += $_object->_count[$backName] = $count;
        }

        // Total count
        return $total;
    }

    /**
     * Mass load named back reference collection IDs
     *
     * @param self   $object   Object instance
     * @param array  $ids      List of ids
     * @param string $backName Name of the collection
     * @param string $limit    MySQL limit clause
     * @param array  $where    Additional where clauses
     * @param bool   $strict   If strict, performs some additional checks in the request
     *
     * @return int[]|null IDs collection, null if colletion is unavailable
     * @throws Exception
     */
    static function massLoadBackIds($object, $ids, $backName, $limit = null, $where = [], bool $strict = true)
    {
        if (!count($ids)) {
            return [];
        }

        if (!$backSpec = $object->makeBackSpec($backName)) {
            return [];
        }

        // No existing class
        if (!self::classExists($backSpec->class)) {
            return [];
        }

        /** @var self $backObject */
        $backObject = new $backSpec->class;

        // Cas du module non installé
        if (!$backObject->_ref_module) {
            return [];
        }

        $backField = $backSpec->field;
        /** @var CRefSpec $fwdSpec */
        $fwdSpec = $backObject->_specs[$backField];

        if ($fwdSpec->nullify || $fwdSpec->unlink) {
            // TODO nullify fields
            return [];
        }

        $backMeta = $fwdSpec->meta;
        if ($backMeta) {
            $class            = $backMeta == 'object_class_id' ? $object->getObjectClassID() : $object->_class;
            $where[$backMeta] = "= '$class'";
        }

        // Actual load query
        $ds                = $backObject->_spec->ds;
        $where[$backField] = $ds->prepareIn($ids);

        // Avoid purging objects from other groups
        if (property_exists($backObject, "group_id") && !($backObject instanceof CPatient)) {
            $where[] = $ds->prepare("(group_id = ? OR group_id IS NULL)", CGroups::loadCurrent()->_id);
        }

        $request = new CRequest($strict);
        $request->addSelect("`{$backObject->_spec->table}`.`{$backObject->_spec->key}`");
        $request->addWhere($where);
        $request->setLimit($limit);

        // Don't use loadIds function because we want an array indexed by key
        return $ds->loadHashList($request->makeSelectIds($backObject));
    }

    /**
     * @inheritdoc
     */
    function __wakeup()
    {
        parent::__wakeup();

        if ($this->_id) {
            $this->registerCache();
        }
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        // Add primary key as ref on self class when existing
        if ($key = $this->_spec->key) {
            $props[$key] = "ref class|$this->_class show|0";
        }

        return $props;
    }

    /**
     * Get all plain fields props
     *
     * @return array
     */
    function getPlainProps()
    {
        $all_specs = $this->getProps();

        $specs = [];
        foreach ($all_specs as $_spec => $_v) {
            if (!preg_match('/^_.+$/', $_spec)) {
                $specs[$_spec] = $_v;
            }
        }

        return $specs;
    }

    /**
     * Check whether object is persistent (ie has a specified table)
     *
     * @return bool
     */
    function hasTable()
    {
        return $this->_spec->table;
    }

    /**
     * Check whether object table is installed
     * Information is outer cached when true
     *
     * @return bool
     */
    function isInstalled()
    {
        return $this->_spec->ds->hasTable($this->_spec->table);
    }

    /**
     * Tell whether a plain field exists in associated SQL table
     * Information is outer cached when true
     *
     * @param string $field Field name
     *
     * @return bool
     */
    function isFieldInstalled($field)
    {
        return $this->_spec->ds->hasField($this->_spec->table, $field);
    }

    /**
     * Check whether object exists in table
     *
     * @param int $id The object's identifier
     *
     * @throws Exception
     * @return bool
     */
    function idExists($id)
    {
        $spec = $this->_spec;

        if (!$id || !$spec->table || !$spec->key || !$spec->ds) {
            return false;
        }

        $query = "SELECT COUNT(*) FROM `{$spec->table}` WHERE `{$spec->key}` = '$id';";

        return $spec->ds->loadResult($query) > 0;
    }

    /**
     * Nullify modified
     *
     * @return integer Number of fields modified
     * @throws Exception
     */
    function nullifyAlteredFields()
    {
        $count = 0;
        foreach ($this->getPlainFields() as $_field => $_value) {
            if ($this->fieldAltered($_field)) {
                $this->$_field = null;
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check whether a field has been modified FROM a non falsy value
     *
     * @param string $field Field name
     *
     * @return boolean
     * @throws Exception
     */
    function fieldAltered($field)
    {
        return $this->fieldModified($field) && $this->_old->$field;
    }

    /**
     * Check whether a field has been modified
     *
     * @param string $field Field name
     * @param mixed  $value [optional] Check if modified to given value
     *
     * @return boolean
     * @throws Exception
     */
    function fieldModified($field, $value = null)
    {
        // Field is not valued or Nothing in base
        if ($this->$field === null || !$this->_id) {
            return false;
        }

        // Load DB version
        $this->loadOldObject();
        if (!$this->_old->_id) {
            return false;
        }

        $spec = $this->_specs[$field];

        // Not formally deterministic case for floats
        if ($spec instanceof CFloatSpec) {
            return !CFloatSpec::equals($this->$field, $this->_old->$field, $spec);
        }

        // Check against a specific value
        if ($value !== null && $this->$field != $value) {
            return false;
        }

        // Has it finally been modified ?
        // return $this->$field != $this->_old->$field;
        return "{$this->$field}" != "{$this->_old->$field}";
    }

    /**
     * @return CStoredObject
     * @throws Exception
     */
    function loadOldObject()
    {
        if (!$this->_old) {
            $this->_old = new static;
            $this->_old->load($this->_id);
        }

        return $this->_old;
    }

    /**
     * Check whether a field has been modified TO a non falsy value
     *
     * @param string $field Field name
     *
     * @return boolean
     * @throws Exception
     */
    function fieldValued($field)
    {
        return $this->fieldModified($field) && $this->$field;
    }

    /**
     * Check whether a field has been modified FROM a non falsy value
     *
     * @param string $field Field name
     *
     * @return boolean
     * @throws Exception
     */
    function fieldFirstModified($field)
    {
        return $this->fieldModified($field) && $this->_old->$field === null;
    }

    /**
     * Check whether a field has been modified FROM a non falsy value
     *
     * @param string $field Field name
     *
     * @return boolean
     * @throws Exception
     */
    function fieldEmptyValued($field)
    {
        // Field is not valued or Nothing in base
        if (!$this->_id) {
            return false;
        }

        // Load DB version
        $this->loadOldObject();
        if (!$this->_old->_id || $this->_old->$field === null) {
            return false;
        }

        return ($this->$field != $this->_old->$field) && $this->$field === null;
    }

    /**
     * Check whether an object has been modified (that is at least one of its fields
     *
     * @return boolean
     * @throws Exception
     */
    function objectModified()
    {
        foreach ($this->getPlainFields() as $name => $value) {
            if ($this->fieldModified($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an array of fields that have been cleared after previous storing
     *
     * Must be used in a handler, at the onAfterStore event notification
     * because mass using of _old object property, which is cleared at the end of object storing but not yet at the
     * handler
     *
     * @return array
     * @throws Exception
     */
    public function getEmptyValuedFields()
    {
        $this->loadOldObject();

        $emptied_fields = [];
        foreach ($this->getPlainFields() as $_field => &$v) {
            if ($this->fieldEmptyValued($_field)) {
                $emptied_fields[] = $_field;
            }
        }

        return $emptied_fields;
    }

    /**
     * Check whether an object has just been created (no older object)
     *
     * @return boolean
     * @throws Exception
     */
    function objectCreated()
    {
        // Load DB version
        $this->loadOldObject();

        return $this->_old->_id;
    }

    public function initialize()
    {
        parent::initialize();

        // Cannot use $this->_class because of CExObject
        $class  = !class_exists($this->_class)
            ? CClassMap::getInstance()->getClassMap(get_class($this))->short_name
            : $this->_class;

        $this->loadRefModule(self::$module_name[$class]);
    }

    /**
     * Load active module
     *
     * @param string|null $mod_name Name of the module
     *
     * @return CModule|null
     */
    public function loadRefModule(?string $mod_name = null): ?CModule
    {
        return $this->_ref_module = CModule::getActive($mod_name);
    }

    /**
     * Get the configuration of object class for a given conf path
     *
     * @param string $path    The configuration path
     * @param mixed  $context The context
     *
     * @return string|null Null if module is not installed
     */
    function conf($path, $context = null)
    {
        if (!$this->_ref_module) {
            return null;
        }

        $mod_name = $this->_ref_module->mod_name;

        return CAppUI::conf("$mod_name $this->_class $path", $context);
    }

    /**
     * Get the configuration of object class for a given CConfiguration path
     *
     * @param string       $path     The configuration path
     * @param integer|null $group_id The CGroups ID, current is not provided
     *
     * @return string|null Null if module is not installed
     */
    public function gconf($path, $group_id = null)
    {
        $group_id = ($group_id) ?: CGroups::get()->_id;

        return $this->conf($path, "CGroups-{$group_id}");
    }

    /**
     * Load class level can do
     *
     * @return CCanDo
     */
    function canClass()
    {
        $can = new CCanDo();

        // Defined at class level in permissions object for user
        global $userPermsObjects;
        if (isset($userPermsObjects[$this->_class][0])) {
            $perm      = $userPermsObjects[$this->_class][0];
            $can->read = $perm->permission >= PERM_READ;
            $can->edit = $perm->permission >= PERM_EDIT;

            return $can;
        }

        // Otherwise fall back on module definition
        $can_module = CModule::getCanDo(CModelObject::$module_name[$this->_class]);
        $can->read  = $can_module->read;
        $can->edit  = $can_module->admin || $can_module->edit;

        return $can;
    }

    /**
     * Gets the can-read boolean permission
     *
     * @return bool
     * @todo       Should not be used, use canDo()->read instead
     *
     * @deprecated Do not overload, overload getPerm() instead
     */
    function canRead()
    {
        return $this->_canRead = $this->getPerm(PERM_READ);
    }

    /**
     * Permission generic check
     *
     * @param integer $permType Type of permission : PERM_READ|PERM_EDIT|PERM_DENY
     *
     * @return boolean
     */
    function getPerm($permType)
    {
        return CPermObject::getPermObject($this, $permType);
    }

    /**
     * Gets the can-edit boolean permission
     *
     * @return bool
     * @todo       Should not be used, use canDo()->edit instead
     *
     * @deprecated Do not overload, overload getPerm() instead
     */
    function canEdit()
    {
        return $this->_canEdit = $this->getPerm(PERM_EDIT);
    }

    /**
     * Prevents accessing the objects by redirecting to an "access denied page",
     * if the user doesn't have READ access
     *
     * @return void
     */
    function needsRead()
    {
        $can = $this->canDo();

        if (!$can->read) {
            $can->denied();
        }
    }

    /**
     * Gets the can-do object
     *
     * @return CCanDo
     */
    function canDo()
    {
        $can       = new CCanDo;
        $can->read = $this->_canRead = $this->getPerm(PERM_READ);
        $can->edit = $this->_canEdit = $this->getPerm(PERM_EDIT);

        // Do not give too much information on wanted object
        $can->context = $this->_guid;

        return $this->_can = $can;
    }

    /**
     * Prevents accessing the objects by redirecting to an "access denied page",
     * if the user doesn't have EDIT access
     *
     * @return void
     */
    function needsEdit()
    {
        $can = $this->canDo();

        if (!$can->edit) {
            $can->denied();
        }
    }

    /**
     * Permission wise load list alternative, with limit simulation when necessary
     *
     * @param int    $permType One of PERM_READ, PERM_EDIT
     * @param array  $where    Where SQL statement
     * @param array  $order    Order SQL statement
     * @param string $limit    Limit SQL statement
     * @param array  $group    Group by SQL statement
     * @param array  $ljoin    Left join SQL statement collection
     * @param bool   $strict   If strict, performs some additional checks in the request
     *
     * @return static[]
     * @throws Exception
     */
    function loadListWithPerms(
        $permType = PERM_READ,
        $where = null,
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        bool $strict = true
    ) {
        // Filter with permission
        if (!$permType) {
            $this->_totalWithPerms = $this->countList($where, $group, $ljoin);

            return $this->loadList($where, $order, $limit, $group, $ljoin, null, null, $strict);
        }

        // Load with no limit
        $list = $this->loadList($where, $order, null, $group, $ljoin, null, null, $strict);
        self::filterByPerm($list, $permType);
        $this->_totalWithPerms = count($list);

        // We simulate the MySQL LIMIT
        if ($limit) {
            $list = CRequest::artificialLimit($list, $limit);
        }

        return $list;
    }

    /**
     * Object count for given statements
     *
     * @param array        $where  Array of where clauses
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Array of left join clauses
     * @param string       $index  Force the use of specified index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return int The found objects count, null if module is not installed
     * @throws Exception
     */
    function countList(
        $where = null,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true,
        ?int $limit_time = null
    ) {
        if (!$this->_ref_module) {
            return null;
        }

        $request = new CRequest($strict);
        $request->addForceIndex($index);
        $request->addLJoin($ljoin);
        $request->addWhere($where);
        $request->addGroup($group);

        $ds = $this->_spec->ds;

        return $ds->loadResult($request->makeSelectCount($this), $limit_time);
    }

    /**
     * @param RequestApi $request_api
     * @param null        $group
     * @param null        $ljoin
     * @param null        $index
     * @param bool        $strict
     *
     * @return int|null
     * @throws Api\Exceptions\ApiRequestException
     */
    public function countListFromRequestApi(
        RequestApi $request_api,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true
    ) {
        $where = $request_api->getFilterAsSQL($this->getDS());

        return $this->countList($where, $group, $ljoin, $index, $strict);
    }

    /**
     * Filters an object collection according to given permission
     *
     * @param CStoredObject[] $objects  Objects to be filtered
     * @param int             $permType One of PERM_READ, PERM_EDIT
     *
     * @return int Count of filtered objects
     */
    static function filterByPerm(&$objects = [], $permType = PERM_READ)
    {
        $total = count($objects);
        foreach ($objects as $id => $object) {
            if (!$object->getPerm($permType)) {
                unset($objects[$id]);
            }
        }

        return $total - count($objects);
    }

    /**
     * Deletes all objects for given identifiers
     *
     * @param array $ids list of identifiers
     *
     * @return null|string Job done
     * @throws Exception
     */
    function deleteAll($ids)
    {
        $spec = $this->_spec;

        $result = $spec->ds->deleteObjects($spec->table, $spec->key, $ids);

        if (!$result) {
            return CAppUI::tr($this->_class) . CAppUI::tr("CMbObject-msg-delete-failed") . $spec->ds->error();
        }

        return null;
    }

    /**
     * Loads the first object matching defined properties, escaping the values
     *
     * @param array|string $order  Order SQL statement
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return integer The found object's ID
     */
    function loadMatchingObjectEsc($order = null, $group = null, $ljoin = null, bool $strict = true)
    {
        $this->escapeValues();
        $ret = $this->loadMatchingObject($order, $group, $ljoin, null, $strict);
        $this->unescapeValues();

        return $ret;
    }

    /**
     * Escape values for SQL queries
     *
     * @return void
     */
    function escapeValues()
    {
        $values = $this->getPlainFields();
        foreach ($values as $name => $value) {
            if ($value) {
                $this->$name = addslashes($value);
            }
        }
    }

    /**
     * Loads the first object matching defined properties
     *
     * @param array|string $order  Order SQL statement
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param array|string $index  Force index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return int The found object's ID
     * @throws Exception
     */
    function loadMatchingObject($order = null, $group = null, $ljoin = null, $index = null, bool $strict = true)
    {
        $request = new CRequest($strict);
        $request->addLJoin($ljoin);
        $request->addGroup($group);
        $request->addOrder($order);
        $request->addForceIndex($index);

        $this->updatePlainFields();
        $fields = $this->getPlainFields();

        foreach ($fields as $key => $value) {
            if ($value !== null) {
                $request->addWhereClause($key, "= '$value'");
            }
        }

        $this->loadObject(
            $request->where,
            $request->order,
            $request->group,
            $request->ljoin,
            $request->forceindex,
            null,
            $strict
        );

        return $this->_id;
    }

    /**
     * Loads the first object matching the query
     *
     * @param array        $where  Where SQL statement
     * @param array|string $order  Order SQL statement
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param array|string $index  Force index
     * @param array|string $having Having SQL statement
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return boolean True if the object was found
     * @throws Exception
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
        // WHERE is NULL or empty array
        if (!$where) {
            trigger_error('Cannot load object without WHERE clause', E_USER_ERROR);

            return null;
        }

        $list = $this->loadList($where, $order, '0,1', $group, $ljoin, $index, $having, $strict);

        if (!$list) {
            return false;
        }

        foreach ($list as $object) {
            $fields = $object->getPlainFields();
            foreach ($fields as $key => $value) {
                $this->$key = $value;
            }
            $this->updateFormFields();

            return true;
        }

        return true;
    }

    /**
     * Unescape Value for SQL queries
     *
     * @return void
     */
    function unescapeValues()
    {
        $values = $this->getPlainFields();
        foreach ($values as $name => $value) {
            if ($value) {
                $this->$name = stripslashes($value);
            }
        }
    }

    /**
     * Loads the list of objects matching the $this properties, escaping the values
     *
     * @param array|string $order  Order SQL statement
     * @param string       $limit  Limit SQL statement
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param array|string $index  Force index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return self[] The list of objects
     * @throws Exception
     */
    function loadMatchingListEsc(
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true
    ) {
        $this->escapeValues();
        $ret = $this->loadMatchingList($order, $limit, $group, $ljoin, $index, $strict);
        $this->unescapeValues();

        return $ret;
    }

    /**
     * Loads the list of objects matching the $this properties
     *
     * @param array|string $order  Order SQL statement
     * @param string       $limit  Limit SQL statement
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param array|string $index  Force index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return self[] The list of objects
     * @throws Exception
     */
    function loadMatchingList(
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true
    ) {
        $request = new CRequest($strict);
        $request->addLJoin($ljoin);
        $request->addGroup($group);
        $request->addOrder($order);
        $request->setLimit($limit);
        $request->addForceIndex($index);

        $this->updatePlainFields();
        $fields = $this->getPlainFields();
        foreach ($fields as $key => $value) {
            if ($value !== null) {
                $request->addWhereClause($key, "= '$value'");
            }
        }

        return $this->loadList(
            $request->where,
            $request->order,
            $request->limit,
            $request->group,
            $request->ljoin,
            $request->forceindex,
            null,
            $strict
        );
    }

    /**
     * Size of the list of objects matching the $this properties, escaping the values
     *
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param array|string $index  Force index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return int The count
     * @throws Exception
     */
    function countMatchingListEsc($group = null, $ljoin = null, $index = null, bool $strict = true)
    {
        $this->escapeValues();
        $ret = $this->countMatchingList($group, $ljoin, $index, $strict);
        $this->unescapeValues();

        return $ret;
    }

    /**
     * Size of the list of objects matching the $this properties
     *
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Left join SQL statement collection
     * @param array|string $index  Force index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return int The count
     * @throws Exception
     */
    function countMatchingList($group = null, $ljoin = null, $index = null, bool $strict = true)
    {
        $request = new CRequest($strict);
        $request->addLJoin($ljoin);
        $request->addGroup($group);
        $request->addForceIndex($index);

        $this->updatePlainFields();
        $fields = $this->getPlainFields();
        foreach ($fields as $key => $value) {
            if ($value !== null) {
                $request->addWhereClause($key, "= '$value'");
            }
        }

        return $this->countList($request->where, $request->group, $request->ljoin, $request->forceindex, $strict);
    }

    /**
     * Object list for a given group
     *
     * @param array  $where Where SQL statement
     * @param array  $order Order SQL statement
     * @param string $limit Limit SQL statement
     * @param array  $group Group by SQL statement
     * @param array  $ljoin Left join SQL statement collection
     *
     * @return static[] List of found objects, null if module is not installed
     * @throws Exception
     */
    function loadGroupList($where = [], $order = null, $limit = null, $group = null, $ljoin = [])
    {
        if (property_exists($this, "group_id")) {
            $g                                        = CGroups::loadCurrent();
            $where[$this->_spec->table . ".group_id"] = "= '$g->_id'";
        }

        return $this->loadList($where, $order, $limit, $group, $ljoin);
    }

    /**
     * Column for given statements
     *
     * @param string $column Name of the column
     * @param array  $where  Array of where clauses
     * @param array  $ljoin  Array of left join clauses
     * @param string $limit  MySQL limit clause
     * @param bool   $unique Distinct values of the column
     * @param bool   $strict If strict, performs some additional checks in the request
     *
     * @return array Column requested
     * @throws Exception
     */
    public function loadColumn(
        string $column,
        array $where = null,
        array $ljoin = null,
        string $limit = null,
        bool $unique = true,
        bool $strict = true,
        ?string $group_by = null
    ): ?array {
        if (!$this->_ref_module) {
            return null;
        }

        $request = new CRequest($strict);
        $request->addSelect(($unique ? "DISTINCT " : "") . $column);
        $request->addLJoin($ljoin);
        $request->addWhere($where);
        $request->addGroup($group_by);
        $request->setLimit($limit);

        $ds = $this->getDS();

        return $ds->loadColumn($request->makeSelect($this));
    }

    /**
     * Get the object's data source object
     *
     * @return CSQLDataSource|CPDODataSource The datasource object
     */
    function getDS()
    {
        return $this->_spec->ds;
    }

    /**
     * Object count of a multiple list by an SQL request constructor using group-by statement
     *
     * @param array        $where  Array of where clauses
     * @param array|string $order  Order statement
     * @param array|string $group  Group by statement
     * @param array        $ljoin  Array of left join clauses
     * @param array        $fields Append fields to the SELECT
     * @param array|string $index  Force index
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return self[]
     * @throws Exception
     */
    function countMultipleList(
        $where = null,
        $order = null,
        $group = null,
        $ljoin = null,
        $fields = [],
        $index = null,
        bool $strict = true
    ) {
        if (!$this->_ref_module) {
            return null;
        }

        $request = new CRequest($strict);
        $request->addWhere($where);
        $request->addOrder($order);
        $request->addGroup($group);
        $request->addLJoin($ljoin);
        $request->addForceIndex($index);

        $ds = $this->_spec->ds;

        return $ds->loadList($request->makeSelectCount($this, $fields));
    }


    /**
     * Return the number of rows after the application of a group by
     * @throws Exception
     */
    public function countListGroupBy(
        $where = null,
        $order = null,
        $group = null,
        $ljoin = null,
        $fields = [],
        $index = null,
        bool $strict = true
    ): int {
        $result = $this->countMultipleList($where, $order, $group, $ljoin, $fields, $index, $strict);

        return ($result && is_countable($result)) ? count($result) : 0;
    }

    /**
     * Object list by a request object
     *
     * @param CRequest $request Request
     *
     * @return static[] List of found objects, null if module is not installed
     * @throws Exception
     */
    function loadListByReq(CRequest $request)
    {
        if (!$this->_ref_module) {
            return null;
        }

        return $this->loadQueryList($request->makeSelect($this));
    }

    /**
     * References global loader
     *
     * @return     int Object id
     * @deprecated out of control resouce consumption
     */
    function loadRefs()
    {
        if ($this->_id) {
            $this->loadRefsBack();
            $this->loadRefsFwd();
        }

        return $this->_id;
    }

    /**
     * Back references global loader
     *
     * @return     void
     * @deprecated out of control resouce consumption
     */
    function loadRefsBack()
    {
    }

    /**
     * Forward references global loader
     *
     * @return     void
     * @deprecated out of control resouce consumption
     */
    function loadRefsFwd()
    {
    }

    /**
     * Repair all non checking properties when possible
     *
     * @return string[] if the object is ok an array of message for repaired fields
     */
    function repair()
    {
        $repaired = [];

        foreach ($this->getProperties() as $name => $value) {
            if ($value !== null) {
                if ($msg = $this->checkProperty($name)) {
                    $repaired[$name] = $msg;
                    $spec            = $this->_specs[$name];
                    if (!$spec->notNull) {
                        $this->$name = "";
                    }
                }
            }
        }

        return $repaired;
    }

    /**
     * Load last log concerning a given field
     *
     * @param string $fieldName Field name
     * @param bool   $strict    Be strict about the field name
     *
     * @return CUserLog
     * @throws Exception
     */
    function loadLastLogForField($fieldName = null, $strict = false)
    {
        $log      = new CUserLog;
        $logs     = $this->loadLogsForField($fieldName, $strict, 1);
        $last_log = reset($logs);

        if ($last_log) {
            $last_log->loadRefsFwd();

            return $last_log;
        }

        return $log;
    }

    /**
     * Load logs concerning a given field
     *
     * @param string $fieldName          Field name
     * @param bool   $strict             Be strict about the field name
     * @param int    $limit              Limit the number of results
     * @param bool   $require_extra_data Return only logs with extra data
     *
     * @return CUserLog[]
     * @throws Exception
     */
    function loadLogsForField($fieldName = null, $strict = false, $limit = null, $require_extra_data = false)
    {
        // user action
        $user_actions = $this->loadUserActionsForField($fieldName, $strict, $limit);
        CStoredObject::massLoadBackRefs($user_actions, 'user_action_datas');
        $logs_convert = [];
        foreach ($user_actions as $_user_action):
            $_user_action->loadRefUserActionDatas();
            $_log           = new CUserLog();
            $logs_convert[] = $_log->loadFromUserAction($_user_action);
        endforeach;

        $where                 = [];
        $where["object_id"]    = " = '$this->_id'";
        $where["object_class"] = " = '$this->_class'";

        if ($require_extra_data) {
            $where[] = "`extra` IS NOT NULL AND `extra` != '[]' AND `extra` != '{}'";
        }

        $log = new CUserLog();

        if ($strict) {
            $fields = $fieldName;

            if (!is_array($fieldName)) {
                $fields = [$fieldName];
            }

            $whereOr = ["`type` = 'create'"];

            foreach ($fields as $_field) {
                $whereOr[] = "
        `fields` = '$_field' OR 
        `fields` LIKE '$_field %' OR 
        `fields` LIKE '% $_field %' OR 
        `fields` LIKE '% $_field'";
            }

            $where[] = implode(" OR ", $whereOr);
        } else {
            $where["fields"] = " LIKE '%$fieldName%'";
        }
        $logs = $log->loadList($where, "`user_log_id` DESC", $limit, null, null, "object_id");

        // merge and sort and limit
        $logs = array_merge($logs, $logs_convert);
        CMbArray::pluckSort($logs, SORT_DESC, "user_log_id");
        if ($limit) {
            $logs = array_slice($logs, 0, $limit);
        }

        return $logs;
    }

    /**
     * Load user action concerning a given field
     *
     * @param string $fieldName Field name
     * @param bool   $strict    Be strict about the field name
     * @param int    $limit     Limit the number of results
     *
     * @return CUserAction[]
     * @throws Exception
     */
    private function loadUserActionsForField($fieldName = null, $strict = false, $limit = null)
    {
        $where                    = [];
        $where["object_id"]       = " = '$this->_id'";
        $where["object_class_id"] = " = '" . $this->getObjectClassID() . "'";

        $join['user_action_data'] = "user_action.user_action_id = user_action_data.user_action_id";

        $order = "user_action.user_action_id DESC";

        $user_action = new CUserAction();


        if ($strict) {
            $fields = $fieldName;

            if (!is_array($fieldName)) {
                $fields = [$fieldName];
            }

            $whereOr = [];

            foreach ($fields as $_field) {
                $whereOr[] = "user_action_data.field = '$_field' ";
            }

            $where[] = implode(" OR ", $whereOr);
        } else {
            $where["user_action_data.field"] = " = '$fieldName'";
        }

        return $user_action->loadList($where, $order, $limit, null, $join, "object_ref");
    }

    /**
     * @return int
     * @throws Exception
     */
    function getObjectClassID()
    {
        return CObjectClass::getID($this->_class);
    }

    /**
     * Get object's UUID
     *
     * @param bool $generate
     *
     * @return string|null
     * @throws Exception
     */
    public function getUuid(bool $generate = true): ?string
    {
        if ($this->_uuid) {
            return $this->_uuid;
        }

        $uuid               = new CObjectUuid();
        $uuid->object_class = $this->_class;
        $uuid->object_id    = $this->_id;

        if ($uuid->loadMatchingObjectEsc()) {
            return $this->_uuid = (string)$uuid;
        }

        if ($generate) {
            $uuid->uuid = CMbSecurity::generateUUID();
            if ($msg = $uuid->store()) {
                throw new Exception($msg);
            }

            return $this->_uuid = (string)$uuid;
        }

        return null;
    }

    /**
     * Get object from is UUID
     *
     * @param string $uuid
     *
     * @return null|CStoredObject
     * @throws Exception
     */
    public static function loadByUuid(string $uuid): ?CStoredObject
    {
        $uuid_object       = new CObjectUuid();
        $uuid_object->uuid = $uuid;

        if (!$uuid_object->loadMatchingObject()) {
            return null;
        }

        $uuid_object->loadRefObject()->_uuid = $uuid;

        return $uuid_object->loadRefObject();
    }

    /**
     * Load first log concerning a given field
     *
     * @param string $fieldName Field name
     * @param bool   $strict    Be strict about the field name
     *
     * @return CUserLog
     * @throws Exception
     */
    function loadFirstLogForField($fieldName = null, $strict = false)
    {
        $log  = new CUserLog;
        $logs = $this->loadLogsForField($fieldName, $strict);
        /** @var CUserLog $first_log */
        $first_log = end($logs);

        if ($first_log) {
            $first_log->loadRefsFwd();

            return $first_log;
        }

        return $log;
    }

    /**
     * Check wether object has a log more recent than given hours
     *
     * @param int $nb_hours Number of hours
     *
     * @return int
     * @throws Exception
     */
    function hasRecentLog($nb_hours = 1)
    {
        $hasRecentUserAction = $this->hasRecentUserAction($nb_hours);

        $recent                = CMbDT::dateTime("- $nb_hours HOURS");
        $where["object_id"]    = "= '$this->_id'";
        $where["object_class"] = "= '$this->_class'";
        $where["date"]         = "> '$recent'";
        $log                   = new CUserLog();

        $hasRecentUserLog = $log->countList($where, null, null, "object_id");

        return $hasRecentUserAction + $hasRecentUserLog;
    }

    /**
     * Check wether object has a log more recent than given hours
     *
     * @param int $nb_hours Number of hours
     *
     * @return int
     * @throws Exception
     */
    private function hasRecentUserAction($nb_hours = 1)
    {
        $recent                   = CMbDT::dateTime("- $nb_hours HOURS");
        $object_class_id          = $this->getObjectClassID();
        $where["object_id"]       = "= '$this->_id'";
        $where["object_class_id"] = "= '$object_class_id'";
        $where["date"]            = "> '$recent'";
        $user_action              = new CUserAction();

        return $user_action->countList($where, null, null, "object_ref");
    }

    /**
     * Returns the object's latest log
     *
     * @return CUserLog
     * @throws Exception
     */
    function loadLastLog()
    {
        // user_action
        $this->loadLastUserAction();
        $this->_ref_last_log = $this->loadFirstBackRef("user_logs", "user_log_id DESC", null, null, "object_id");

        // merge with user_action
        if ($this->_ref_last_user_action->_id) {
            if (!$this->_ref_last_log->_id || $this->_ref_last_log->date < $this->_ref_last_user_action->date) {
                $this->_ref_last_user_action->loadRefUserActionDatas();
                $log = new CUserLog();
                $log->loadFromUserAction($this->_ref_last_user_action);
                $this->_ref_last_log = $log;
            }
        }

        return $this->_ref_last_log;
    }

    /**
     * Returns the object's latest UserAction
     *
     * @return CUserAction|CMbObject
     * @throws Exception
     */
    private function loadLastUserAction()
    {
        return $this->_ref_last_user_action = $this->loadFirstBackRef(
            "user_actions",
            "user_action_id DESC",
            null,
            null,
            "object_ref"
        );
    }

    /**
     * Try to give the date for when a field had a given value
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string|null
     * @throws Exception
     */
    public function getFirstDateForFieldValue($field, $value): ?string
    {
        if (!$this->_id) {
            return null;
        }

        $logs = $this->loadLogsForField($field);

        uasort(
            $logs,
            function (CUserLog $a, CUserLog $b) {
                return ($a->date <=> $b->date);
            }
        );

        $previous_change_date = $this->loadFirstLog()->date;

        foreach ($logs as $_log) {
            $_log->getOldValues();

            if ($_log->_old_values[$field] == $value) {
                return $previous_change_date;
            } else {
                $previous_change_date = $_log->date;
            }
        }

        // Actual field value is given value
        if ($this->{$field} == $value) {
            return $previous_change_date;
        }

        return null;
    }

    /**
     * Load the first back reference for given collection name
     *
     * @param string       $backName The collection name
     * @param array|string $order    Order SQL statement
     * @param array|string $group    Group by SQL statement
     * @param array        $ljoin    Array of left join clauses
     * @param array        $index    Force index
     * @param bool         $strict   If strict, performs some additional checks in the request
     *
     * @return CMbObject Unique back reference if exist, concrete type empty object otherwise, null if unavailable
     * @throws Exception
     */
    function loadFirstBackRef(
        $backName,
        $order = null,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true,
        ?array $where = null
    ) {
        if (null === $backRefs = $this->loadBackRefs(
                $backName,
                $order,
                "1",
                $group,
                $ljoin,
                $index,
                null,
                $where,
                $strict
            )) {
            return null;
        }

        if (!count($backRefs)) {
            $backSpec = $this->_backSpecs[$backName];

            return new $backSpec->class;
        }

        return reset($backRefs);
    }

    /**
     * Load named back reference collection
     *
     * @param string       $backName    Name of the collection
     * @param array|string $order       Order SQL statement
     * @param string       $limit       MySQL limit clause
     * @param array|string $group       Group by SQL statement
     * @param array        $ljoin       Array of left join clauses
     * @param array        $index       Force index
     * @param string       $backNameAlt BackName Alt
     * @param array        $where       Additional where clauses
     * @param bool         $strict      If strict, performs some additional checks in the request
     *
     * @return self[]|null Total count among objects, null if collection is unavailable
     * @throws Exception
     */
    function loadBackRefs(
        $backName,
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        $backNameAlt = "",
        $where = [],
        bool $strict = true
    ) {
        if (!$backSpec = $this->makeBackSpec($backName)) {
            return null;
        }

        // No existing class
        if (!self::classExists($backSpec->class)) {
            return null;
        }

        /** @var self $backObject */
        $backObject = new $backSpec->class;

        // Module unavailable
        if (!$backObject->_ref_module) {
            return null;
        }

        $backName = $backNameAlt ? $backNameAlt : $backName;

        // Empty object
        if (!$this->_id) {
            return $this->_back[$backName] = [];
        }

        // Precounting optimization: no need to query when we already know array is empty
        if (isset($this->_count[$backName]) && $this->_count[$backName] === 0) {
            return $this->_back[$backName] = [];
        }

        // TODO WARNING : If self::massLoadBackRefs have been called for this object and backName
        // TODO             the result returned will ignore any argument passed (limit, order, where, ...)
        /* Preloading optimization: no need to query when we have already preloaded
    * Back collection and back count in correspondance is the "signature" on mass preloading mechanism
    * So that we can use the mechanism safely with probably no side effects */
        if (self::$useObjectCache
            && array_key_exists($backName, $this->_back)
            && isset($this->_count[$backName])
            && isset($this->_back[$backName]) && is_countable($this->_back[$backName])
            && count($this->_back[$backName]) == $this->_count[$backName]
        ) {
            return $this->_back[$backName];
        }

        // Back reference where clause
        $backField                                         = $backSpec->field;
        $where["{$backObject->_spec->table}.{$backField}"] = "= '$this->_id'";

        // Meta object case
        /** @var CRefSpec $fwdSpec */
        $fwdSpec  = $backObject->_specs[$backField];
        $backMeta = $fwdSpec->meta;
        if ($backMeta) {
            $metaSpec = $backObject->_specs[$backMeta];

            if ($metaSpec instanceof CRefSpec && $metaSpec->class === 'CObjectClass') {
                $_class = CObjectClass::getID($this->_class);
            } else {
                $_class = $this->_class;
            }

            $where[$backMeta] = "= '{$_class}'";
        }

        return $this->_back[$backName] = $backObject->loadList(
            $where,
            $order,
            $limit,
            $group,
            $ljoin,
            $index,
            null,
            $strict
        );
    }

    /**
     * Return the object's (first) creation log
     * Former instances have legacy data with no creation log but later modification log
     * In that case we explicitely don't want it
     *
     * @return CUserLog
     * @throws Exception
     */
    function loadCreationLog()
    {
        $log = $this->loadFirstLog();

        return $log->type == "create" || ($log->type == "store" && !$log->fields) ? $log : new CUserLog();
    }

    /**
     * Returns the object's first log
     *
     * @return CUserLog
     * @throws Exception
     */
    function loadFirstLog()
    {
        // user_action
        $this->loadFirstUserAction();
        $this->_ref_first_log = $this->loadFirstBackRef("user_logs", "user_log_id ASC", null, null, "object_id");

        // merge with user_action
        if ($this->_ref_first_user_action->_id) {
            if (!$this->_ref_first_log->_id || $this->_ref_first_log->date > $this->_ref_first_user_action->date) {
                $this->_ref_first_user_action->loadRefUserActionDatas();
                $log = new CUserLog();
                $log->loadFromUserAction($this->_ref_first_user_action);
                $this->_ref_first_log = $log;
            }
        }

        return $this->_ref_first_log;
    }

    /**
     * Returns the object's first UserAction
     *
     * @return CUserAction|CMbObject
     * @throws Exception
     */
    private function loadFirstUserAction()
    {
        return $this->_ref_first_user_action = $this->loadFirstBackRef(
            "user_actions",
            "user_action_id ASC",
            null,
            null,
            "object_ref"
        );
    }

    /**
     * @param string $date  ISO Date
     * @param string $field Field name
     *
     * @return string
     * @throws Exception
     * @todo refactor after migration log > action need to use undiff_old_Values
     *       Returns a field's value at the specified date
     *
     */
    function getValueAtDate($date, $field)
    {
        if (!$this->_id) {
            return null;
        }

        $user_action = $this->loadUserActionForFieldAtDate($date, $field);
        $user_log    = $this->loadLogForFieldAtDate($date, $field);

        // Witch one ?
        if ($user_action->_id) {
            if (!$user_log->_id || $user_log->date > $user_action->date) {
                $user_log = $user_action;
            }
        }

        $spec = $this->_specs[$field];

        if (($user_log->_id >= CUserLog::USER_ACTION_START_AUTO_INCREMENT) && ($spec instanceof CTextSpec)) {
            $this->loadHistory();
            $_history_key  = array_reverse(array_keys($this->_history));
            $_current_key  = array_search($user_log->_id, $_history_key);
            $_previous_key = $_current_key > 0 ? $_history_key[$_current_key - 1] : false;
            $value         = $_previous_key ? $this->_history[$_previous_key][$field] : null;
        } else {
            $value = CValue::read($user_log->_old_values, $field, $this->$field);
        }

        return $value;
    }

    /**
     * @param String $date
     * @param String $field
     *
     * @return CUserLog|CUserAction
     * @throws Exception
     */
    private function loadUserActionForFieldAtDate($date, $field)
    {
        $object_class_id          = $this->getObjectClassID();
        $object_id                = $this->_id;
        $where                    = [
            "object_class_id" => "= '$object_class_id'",
            "object_id"       => "= '$object_id'",
            "type"            => "IN('store', 'merge')",
            "date"            => ">= '$date'",
        ];
        $join                     = [];
        $join['user_action_data'] = "user_action.user_action_id = user_action_data.user_action_id";

        $where[] = "user_action_data.field = '$field'";

        $user_action = new CUserAction();
        $user_action->loadObject($where, "date ASC", null, $join);

        if ($user_action->_id) {
            $user_action->loadRefUserActionDatas();
            $user_action->getOldValues();
        }

        return $user_action;
    }

    /**
     * @param String $date
     * @param String $field
     *
     * @return CUserLog
     * @throws Exception
     */
    public function loadLogForFieldAtDate($date, $field)
    {
        $where = [
            "object_class" => "= '$this->_class'",
            "object_id"    => "= '$this->_id'",
            "type"         => "IN('store', 'merge')",
            "extra IS NOT NULL AND extra != '[]'",
            "date"         => ">= '$date'",
        ];

        $where[] = "
      fields LIKE '$field' OR 
      fields LIKE '$field %' OR 
      fields LIKE '% $field' OR 
      fields LIKE '% $field %'";

        $user_log = new CUserLog();
        $user_log->loadObject($where, "date ASC");

        if ($user_log->_id) {
            $user_log->getOldValues();
        }

        return $user_log;
    }

    /**
     * @param null|int $history_id (user_log_id | user_action_id)
     *
     * @return array|mixed
     * @throws Exception
     */
    function loadListByHistory($history_id = null)
    {
        if (!$this->_history) {
            $this->loadHistory();
        }

        $instances = [];

        foreach ($this->_history as $_id => $_datas) {
            // hydrate instance
            $_instance = self::getInstance($this->_class);
            foreach ($_datas as $_field => $_value) {
                $_instance->$_field = $_value;
            }
            $instances[$_id] = $_instance;
        }

        return isset($instances[$history_id]) ? $instances[$history_id] : $instances;
    }

    /**
     * Load object state along the time, according to user logs
     *
     * @return void
     * @throws Exception
     */
    function loadHistory()
    {
        $this->_history = [];
        $this->loadLogs();
        $clone = $this->getPlainFields();

        // Special treatment for html diff
        foreach ($this->getSpecs() as $_field_name => $_spec) {
            if ($_spec instanceof CHtmlSpec) {
                $clone[$_field_name] = strip_tags($clone[$_field_name]);
            }
        }

        $render = new Text();

        foreach ($this->_ref_logs as $_log) {
            $this->_history[$_log->_id] = $clone;

            $_log->getOldValues();
            foreach ($_log->_old_values as $_old_field => $_old_value) {
                $_spec = isset($this->_specs[$_old_field]) ? $this->_specs[$_old_field] : false;
                // diff process
                if ($_spec instanceof CTextSpec || $_spec instanceof CHtmlSpec) {
                    $_diff      = $_old_value;
                    $_old_value = $render->process($clone[$_old_field] ?? '', $_diff);
                }

                $clone[$_old_field] = $_old_value;
            }
        }
    }


    /**
     * Load user logs for object
     * @return void
     * @throws Exception
     */
    function loadLogs()
    {
        // user action
        $this->loadUserActions();
        CStoredObject::massLoadBackRefs($this->_ref_user_actions, 'user_action_datas');

        $logs_convert = [];
        foreach ($this->_ref_user_actions as $_user_action) {
            $_user_action->loadRefUserActionDatas();
            $_log                             = new CUserLog();
            $logs_convert[$_user_action->_id] = $_log->loadFromUserAction($_user_action);
        }

        $this->_ref_logs = $this->loadBackRefs("user_logs", "user_log_id DESC", 100, null, null, "object_id");

        CStoredObject::massLoadFwdRef($this->_ref_logs, "user_id");
        /** @var CUserLog $_log */
        foreach ($this->_ref_logs as $_log) {
            $_log->loadRefUser();
            $_log->_ref_object = $this;
        }

        $this->_ref_logs = array_merge($this->_ref_logs, $logs_convert);

        CMbArray::pluckSort($this->_ref_logs, SORT_DESC, "user_log_id");

        // the first is at the end because of the date order !
        $this->_ref_first_log = end($this->_ref_logs);
        $this->_ref_last_log  = reset($this->_ref_logs);
    }

    /**
     * Load user actions for object
     * @return void
     * @throws Exception
     *
     */
    private function loadUserActions()
    {
        $this->_ref_user_actions = $this->loadBackRefs(
            "user_actions",
            "user_action_id DESC",
            100,
            null,
            null,
            "object_ref"
        );

        CStoredObject::massLoadFwdRef($this->_ref_user_actions, "user_id");

        /** @var CUserAction $_user_action */
        foreach ($this->_ref_user_actions as $_user_action) {
            $_user_action->loadRefUser();
            $_user_action->_ref_object = $this;
        }

        // the first is at the end because of the date order !
        $this->_ref_first_user_action = end($this->_ref_user_actions);
        $this->_ref_last_user_action  = reset($this->_ref_user_actions);
    }

    /**
     * Mass load mechanism for forward references of an object collection
     *
     * @param self[] $objects      Array of objects
     * @param string $field        Field to load
     * @param string $object_class Restrict to explicit object class in case of meta reference
     * @param bool   $keep_sorted  Keep the same order as the one in $objects
     *
     * @return self[] Loaded collection, null if unavailable, with ids as keys of guids for meta references
     * @throws Exception
     */
    static function massLoadFwdRef($objects, $field, $object_class = null, $keep_sorted = false)
    {
        if (!is_countable($objects) || !count($objects)) {
            return [];
        }

        $object = reset($objects);
        $spec   = $object->_specs[$field];


        if (!$spec instanceof CRefSpec) {
            trigger_error("Can't mass load not ref '$field' for class '$object->_class'", E_USER_WARNING);

            return null;
        }
        $meta = $spec->meta;
        if ($object_class && !$spec->meta) {
            trigger_error(
                "Mass load with object class is unavailable for non meta ref '$field' in class '$object->_class'",
                E_USER_WARNING
            );

            return null;
        }

        // Delegated mass load forward references by meta class then append in global array with guid as keys
        if ($meta && !$object_class) {
            $objects_by_class = [];
            foreach ($objects as $_object) {
                $_spec = $_object->_specs[$meta];
                if ($_spec instanceof CRefSpec && $_spec->class == 'CObjectClass') {
                    $_class_meta = CObjectClass::getClass($_object->$meta);
                } else {
                    $_class_meta = $_object->$meta;
                }

                if (!isset($objects_by_class[$_class_meta])) {
                    $objects_by_class[$_class_meta] = [];
                }

                $objects_by_class[$_class_meta][] = $_object;
            }

            $fwd_objects = [];
            foreach ($objects_by_class as $_object_class => $_objects_by_class) {
                if (!$_object_class) {
                    continue;
                }

                $fw_refs = self::massLoadFwdRef($_objects_by_class, $field, $_object_class);
                if (is_array($fw_refs)) {
                    // Merge array_values to get rid of non integer keys
                    $fwd_objects = array_merge($fwd_objects, array_values($fw_refs));
                }
            }

            // Final array has guids for keys;
            return array_combine(CMbArray::pluck($fwd_objects, "_guid"), $fwd_objects);
        }

        /** @var self $fwd */
        $class = CValue::first($object_class, $spec->class);

        // No existing class
        if (!self::classExists($class)) {
            return null;
        }

        $fwd = self::getInstance($class);

        // Inactive module
        if (!$fwd->_ref_module) {
            return null;
        }

        // Get the ids
        $fwd_ids = [];
        if ($object_class) {
            foreach ($objects as $_object) {
                $_spec = $_object->_specs[$meta];
                if ($_spec instanceof CRefSpec && $_spec->class == 'CObjectClass') {
                    $_object_class = CObjectClass::getClass($_object->$meta);
                } else {
                    $_object_class = $_object->$meta;
                }
                if ($_object_class == $object_class) {
                    $fwd_ids[] = $_object->$field;
                }
            }
        } else {
            $fwd_ids = CMbArray::pluck($objects, $field);
        }

        $fwd_objects = $fwd->loadAll($fwd_ids);
        foreach ($objects as $_object) {
            $_object->_fwd[$field] = $_object->$field && isset($fwd_objects[$_object->$field]) ? $fwd_objects[$_object->$field] : $fwd;
        }

        if (!$keep_sorted) {
            return $fwd_objects;
        }

        $fwd_objects_sorted = [];
        foreach ($fwd_ids as $_fwd_id) {
            $fwd_objects_sorted[$_fwd_id] = $fwd_objects[$_fwd_id];
        }

        return $fwd_objects_sorted;
    }

    /**
     * Store an object, without creating user log, without properties checking.
     *
     * @return bool
     * @throws Exception
     */
    function rawStore()
    {
        $spec = $this->_spec;
        $vars = $this->getPlainFields();
        if ($this->_id) {
            return $spec->ds->updateObject($spec->table, $vars, $spec->key, $spec->nullifyEmptyStrings);
        } else {
            $key = $spec->incremented ? $spec->key : null;

            return $spec->ds->insertObject($spec->table, $this, $vars, $key, $spec->insert_delayed);
        }
    }

    /**
     * Merge an array of objects.
     *
     * @param self[]    $objects An array of CMbObject to merge.
     * @param bool      $fast    Tell whether to use SQL (fast) or PHP (slow but checked and logged) algorithm.
     * @param CMergeLog $merge_log
     *
     * @throws CouldNotMerge
     * @throws Exception
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        if (!CMediusers::get()->isAdmin() && count($objects) != 1) {
            throw CouldNotMerge::invalidNumberOfObjects();
        }

        foreach ($objects as $object) {
            $this->_merging[$object->_id] = $object;
        }

        // Trigger before event
        $this->notify(ObjectHandlerEvent::BEFORE_MERGE());

        if ($merge_log && $merge_log->_id) {
            $merge_log->logBefore();
        }

        if (!$this->_id && $msg = $this->store()) {
            $this->notify(ObjectHandlerEvent::MERGE_FAILURE());

            throw CouldNotMerge::storeFailure($msg);
        }

        // Cleanup duplicates
        $all_objects = $objects;
        array_unshift($all_objects, $this);

        CLogAccessMedicalData::cleanupDuplicates($all_objects);

        foreach ($objects as &$object) {
            try {
                if ($fast) {
                    $this->fastTransferBackRefsFrom($object, $merge_log);
                } else {
                    $this->transferBackRefsFrom($object, $merge_log);
                }
            } catch (Exception $e) {
                $this->notify(ObjectHandlerEvent::MERGE_FAILURE());

                throw CouldNotMerge::backrefsTransferFailure($e->getMessage());
            }

            $object_id              = $object->_id;
            $object->_mergeDeletion = true;

            if ($msg = $object->delete()) {
                throw CouldNotMerge::deleteFailure($msg);
            }

            // If external IDs are available, we save old objects' id as external IDs
            if (CModule::getInstalled("dPsante400")) {
                $idex = new CIdSante400();
                $idex->setObject($this);
                $idex->tag   = "merged";
                $idex->id400 = $object_id;

                if ($msg = $idex->store()) {
                    throw CouldNotMerge::idexStoreFailure($msg);
                }
            }
        }

        // Trigger after event
        $this->notify(ObjectHandlerEvent::AFTER_MERGE());

        if ($merge_log && $merge_log->_id) {
            $merge_log->logAfter();
        }

        if ($msg = $this->store()) {
            throw CouldNotMerge::storeFailure($msg);
        }
    }

    /**
     * Inserts a new row if id is zero or updates an existing row in the database table
     *
     * @return null|string null if successful otherwise returns and error message
     * @throws Exception
     */
    function store()
    {
        // Properties checking
        $this->updatePlainFields();

        if (CApp::isReadonly()) {
            return CAppUI::tr($this->_class) .
                CAppUI::tr("CMbObject-msg-store-failed") .
                CAppUI::tr("Mode-readonly-msg");
        }

        if ($msg = $this->check()) {
            return CAppUI::tr($this->_class) .
                CAppUI::tr("CMbObject-msg-check-failed") .
                CAppUI::tr($msg);
        }

        // Old object has to be loaded before all notifications
        $this->loadOldObject();

        // Trigger before event
        $this->notify(ObjectHandlerEvent::BEFORE_STORE());

        // Log has to be prepared prior to actual SQL query, for update prevention
        if (CAppUI::conf("activer_user_action")) {
            $this->prepareUserAction();
        } else {
            $this->prepareLog();
        }

        // SQL query
        $spec = $this->_spec;
        $vars = $this->getPlainFields();
        if ($this->_old->_id) {
            // Update prevention when possible to prevent SQL cache invalidation
            // May still be null for non loggable objects
            $ret = $this->_count_modified !== 0 ?
                $spec->ds->updateObject($spec->table, $vars, $spec->key, $spec->nullifyEmptyStrings) :
                true;
        } else {
            $keyToUpdate = $spec->incremented ? $spec->key : null;
            $ret         = $spec->ds->insertObject(
                $spec->table,
                $this,
                $vars,
                $keyToUpdate,
                $spec->insert_delayed/*, count($spec->uniques) > 0*/
            );
        }

        if (!$ret) {
            return CAppUI::tr($this->_class) .
                CAppUI::tr("CMbObject-msg-store-failed") .
                $spec->ds->error();
        }

        // Load the object to get all properties
        $this->load();

        // Log storing after successful SQL query
        if (CAppUI::conf("activer_user_action")) {
            $this->doUserAction();
        } else {
            $this->doLog();
        }

        if ($this->_external_ids) {
            $this->storeExternalIds();
        }

        // Trigger event
        $this->notify(ObjectHandlerEvent::AFTER_STORE());

        $this->_old = null;

        return null;
    }

    /**
     * Check all properties according to specification
     *
     * @return string|null Store-like message, null when no problem
     * @throws Exception
     */
    function check()
    {
        $debug = CAppUI::conf("debug");

        $msg = "";

        // Property level checking
        foreach ($this->_props as $name => $prop) {
            if ($name[0] !== '_') {
                if (!property_exists($this, $name)) {
                    trigger_error(
                        "La spécification cible la propriété '$name' inexistante dans la classe '$this->_class'",
                        E_USER_WARNING
                    );
                } else {
                    $value = $this->$name;
                    if (!$this->_id || $value !== null) {
                        $msgProp = $this->checkProperty($name);

                        $truncated = CMbString::truncate($value);
                        $debugInfo = $debug ? "(val:\"$truncated\", prop:\"$prop\")" : "(valeur: \"$truncated\")";
                        $fieldName = CAppUI::tr("$this->_class-$name");
                        $msg       .= $msgProp ? " &bull; <strong title='$name'>$fieldName</strong> : $msgProp $debugInfo <br/>" : null;
                    }
                }
            }
        }

        if ($this->_merging) {
            return $msg;
        }

        // Class level unique checking
        foreach ($this->_spec->uniques as $unique => $names) {
            /** @var self $other */
            $other  = new static;
            $values = [];
            foreach ($names as $name) {
                $this->completeField($name);
                $other->$name = addslashes($this->$name ?? "");

                $value = "";

                if ($this->_specs[$name] instanceof CRefSpec) {
                    $fwd = $this->loadFwdRef($name);

                    if ($fwd) {
                        $value = $fwd->_view;
                    }
                } else {
                    $value = $this->$name;
                }

                $values[] = $value;
            }

            $other->loadMatchingObject();

            if ($other->_id && $this->_id != $other->_id) {
                return CAppUI::tr("$this->_class-failed-$unique") . " : " . implode(", ", $values);
            }
        }

        // Class-level xor checking
        foreach ($this->_spec->xor as $xor => $names) {
            $n      = 0;
            $fields = [];
            foreach ($names as $name) {
                $this->completeField($name);
                $fields[] = CAppUI::tr("$this->_class-$name");
                if ($this->$name) {
                    $n++;
                }
            }

            if ($n != 1) {
                return CAppUI::tr("$this->_class-xorFailed-$xor") .
                    ": " . implode(", ", $fields) . ")";
            }
        }

        return $msg;
    }

    /**
     * Complete fields with base value if missing
     * @return void
     * @throws Exception
     */
    function completeField()
    {
        if (!$this->_id) {
            return;
        }

        $fields = func_get_args();

        if (isset($fields[0]) && is_array($fields[0])) {
            $fields = $fields[0];
        }

        foreach ($fields as $field) {
            // Field is valued
            if ($this->$field !== null) {
                continue;
            }

            $this->loadOldObject();
            $this->$field = $this->_old->$field;
        }
    }

    /**
     * Load named forward reference
     *
     * @param string $field  Field name
     * @param bool   $cached Use object cache when possible
     *
     * @return self|null concrete loaded object, null if reference unavailable
     * @throws Exception
     */
    function loadFwdRef($field, $cached = false)
    {
        // Object scope cache

        if ($cached && $this->{$field} && isset($this->_fwd[$field]) && $this->_fwd[$field]->_id == $this->{$field}) {
            return $this->_fwd[$field];
        }

        // Not a ref spec
        $spec = $this->_specs[$field];
        if (!$spec instanceof CRefSpec) {
            return null;
        }

        // Undefined class
        if ($spec->meta) {
            $_spec = $this->_specs[$spec->meta];
            if ($_spec instanceof CRefSpec && $_spec->class == 'CObjectClass') {
                $class = CObjectClass::getClass($this->{$spec->meta});
            } else {
                $class = $this->{$spec->meta};
            }
        } else {
            $class = $spec->class;
        }

        if (!$class) {
            return $this->_fwd[$field] = null;
        }

        // Non existing class
        if (!self::classExists($class)) {
            return $this->_fwd[$field] = null;
        }

        /** @var self $fwd */
        $fwd = new $class;

        // Inactive module
        if (!$fwd->_ref_module) {
            return $this->_fwd[$field] = null;
        }

        // Bug if a reference field is declared among getProps
        if (is_object($this->$field)) {
            $fwd = $this->$field;
        } elseif ($cached) {
            // Actual loading or cache fetching
            $fwd = $fwd->getCached($this->$field);
        } else {
            $fwd->load($this->$field);
        }

        return $this->_fwd[$field] = $fwd;
    }

    /**
     * Prepare the user log before object persistence
     *
     * @return CUserAction|null null if not loggable
     * @throws Exception
     * @todo   fix cyclomatic complexity
     */
    protected function prepareUserAction()
    {
        $this->_ref_current_user_action = null;

        // If the object is not loggable
        if (!$this->isLoggable() || $this->_purge) {
            return null;
        }

        // Find changed fields
        $fields = [];
        foreach ($this->getPlainFields() as $name => $value) {
            if ($this->fieldModified($name)) {
                $fields[] = $name;
            }
        }

        // Change field count for SQL update prevention
        $this->_count_modified = count($fields);

        $object_id = $this->_id;
        $old       = $this->_old;

        $type  = "store";
        $datas = [];

        // Creation
        if ($old->_id == null) {
            $type   = "create";
            $fields = [];
        }

        // Merging
        if ($this->_merging) {
            $type = "merge";
        }

        // Deletion
        if ($old->_id && !$this->_id) {
            $type      = "delete";
            $object_id = $old->_id;
            $datas     = $this->getPlainFields();
            $fields    = array_keys($datas);
        }

        if (!count($fields) && $type === "store") {
            $this->_ref_last_user_action = null;

            return null;
        }

        if ($type === "store" || $type === "merge") {
            $granularity        = new Word();
            $diff               = new Diff($granularity);
            $old_values         = [];
            $count_not_loggable = 0;

            foreach ($fields as $_field) {
                $_spec = $this->_specs[$_field];

                // Exclude Spec
                if ($_spec instanceof CPhpSpec || $_spec->loggable === '0' || $_spec instanceof CXmlSpec) {
                    if ($_spec->loggable === '0') {
                        $count_not_loggable++;
                    }
                    continue;
                }

                // Specific spec (compress && diff > from actual to old)
                if ($_spec instanceof CTextSpec || $_spec instanceof CHtmlSpec) {
                    $form_text = $this->$_field;
                    $to_text   = $old->$_field;

                    if ($_spec instanceof CHtmlSpec) {
                        $form_text = strip_tags($form_text);
                        $to_text   = strip_tags($to_text);
                    }

                    $opcodes     = (string)$diff->getOperationCodes($form_text, $to_text);
                    $len_opcodes = strlen($opcodes);

                    if (CAppUI::conf("activer_compression_diff") && $len_opcodes > self::COMPRESS_MIN_LEN) {
                        $value_compressed = gzcompress($opcodes);
                        $len_compress     = strlen($value_compressed);
                        if ($len_compress < $len_opcodes) {
                            $opcodes = $value_compressed;
                        }
                    }
                    $old->$_field = $opcodes;
                }
                $old_values[$_field] = $old->$_field;
            }

            if (count($fields) == $count_not_loggable) {
                return null;
            }

            $datas = $old_values;
        }

        $address = CMbServer::getRemoteAddress();

        $userAction                  = new CUserAction();
        $userAction->user_id         = CAppUI::$instance->user_id;
        $userAction->object_class_id = $this->getObjectClassID();
        $userAction->object_id       = $object_id;
        $userAction->type            = $type;
        $userAction->date            = 'now';
        $userAction->ip_address      = $address["client"] ? inet_pton($address["client"]) : null;
        $userAction->_datas          = $datas;

        $log = new CUserLog();
        $log->loadFromUserAction($userAction);
        $this->_ref_last_log = $log;

        return $this->_ref_last_user_action = $userAction;
    }

    /**
     * Prepare the user log before object persistence
     *
     * @return     CUserLog|null null if not loggable
     * @throws     Exception
     * @deprecated
     */
    protected function prepareLog()
    {
        $this->_ref_current_log = null;

        // If the object is not loggable
        if (!$this->isLoggable() || $this->_purge) {
            return null;
        }

        // Find changed fields
        $fields = [];
        foreach ($this->getPlainFields() as $name => $value) {
            if ($this->fieldModified($name)) {
                $fields[] = $name;
            }
        }

        // Change field count for SQL update prevention
        $this->_count_modified = count($fields);

        $object_id = $this->_id;
        $old       = $this->_old;

        $type  = "store";
        $extra = null;

        // Creation
        if ($old->_id == null) {
            $type   = "create";
            $fields = [];
        }

        // Merging
        if ($this->_merging) {
            $type = "merge";
        }

        // Deletion
        if ($old->_id && !$this->_id) {
            $type      = "delete";
            $object_id = $old->_id;
            $extra     = $old->_view;
            $fields    = [];
        }

        if (!count($fields) && $type === "store") {
            $this->_ref_last_log = null;

            return null;
        }

        if ($type === "store" || $type === "merge") {
            $old_values         = [];
            $count_not_loggable = 0;
            foreach ($fields as $_field) {
                $_spec = $this->_specs[$_field];
                if ($_spec instanceof CTextSpec
                    || $_spec instanceof CHtmlSpec
                    || $_spec instanceof CXmlSpec
                    || $_spec instanceof CPhpSpec
                    || !$this->isLoggable()
                ) {
                    if (!$this->isLoggable()) {
                        $count_not_loggable++;
                    }
                    continue;
                }
                $old_values[$_field] = $old->$_field ? utf8_encode($old->$_field) : "";
            }

            if (count($fields) == $count_not_loggable) {
                return null;
            }

            $extra = json_encode($old_values);
        }

        $address = CMbServer::getRemoteAddress();

        $log               = new CUserLog();
        $log->user_id      = CAppUI::$instance->user_id;
        $log->object_id    = $object_id;
        $log->object_class = $this->_class;
        $log->type         = $type;
        $log->_fields      = $fields;
        $log->date         = CMbDT::dateTime();

        // Champs potentiellement absents
        if (CModule::getInstalled("system")->mod_version > "1.0.19") {
            $log->ip_address = $address["client"] ? inet_pton($address["client"]) : null;
            $log->extra      = $extra;
        }

        return $this->_ref_last_log = $log;
    }

    /**
     * Record the user action after object persistence (store or delete)
     *
     * @return void
     * @throws Exception
     */
    protected function doUserAction()
    {
        $user_action = $this->_ref_last_user_action;

        // Aucun user_action à produire (non loggable, pas de modifications, etc.)
        if (!$user_action) {
            return;
        }

        // Mandatory for create log
        if ($user_action->type == "create") {
            $user_action->object_id = $this->_id;
        }

        // Make it current userAction/log and store it
        $this->_ref_current_user_action = $user_action;
        $user_action->store();

        // Need for transition CUserLog > CUserAction
        $user_log               = new CUserLog();
        $this->_ref_current_log = $user_log->loadFromUserAction($user_action);

        // Migration user_log > user_action
        if (CAppUI::conf("activer_migration_log_to_action")) {
            $proba = CAppUI::conf("migration_log_to_action_probably");
            CApp::doProbably(
                $proba,
                function () {
                    $limit = CAppUI::conf("migration_log_to_action_nbr");
                    $log   = new CUserLog();
                    $log->migrationLogToAction($limit);
                }
            );
        }
    }

    /**
     * Prepares the user log before object persistence (store or delete)
     *
     * @return     void
     * @throws     Exception
     * @deprecated
     */
    protected function doLog()
    {
        $log = $this->_ref_last_log;

        // Aucun log à produire (non loggable, pas de modifications, etc.)
        if (!$log) {
            return;
        }

        // Mandatory for create log
        if ($log->type == "create") {
            $log->object_id = $this->_id;
        }

        // Must set the date to now to avoid bad date with latency
        $log->date = 'now';

        // Make it current log and store it
        $this->_ref_current_log = $log;
        $log->store();
    }

    /**
     * Transfer all back refs from given object of same class using unchecked, unlogged SQL queries.
     *
     * @param CMbObject      $object The object to transfer back objects from
     * @param CMergeLog|null $merge_log
     *
     * @throws Exception
     */
    public function fastTransferBackRefsFrom(CMbObject &$object, ?CMergeLog $merge_log = null): void
    {
        if (!$this->_id) {
            return;
        }

        $detail_merged_relations = [];

        try {
            $this->makeAllBackSpecs();
            $ds = $this->getDS();

            foreach ($this->_backSpecs as $backSpec) {
                // No existing class
                if (!self::classExists($backSpec->class)) {
                    continue;
                }

                /** @var self $backObject */
                $backObject = new $backSpec->class();
                $backField  = $backSpec->field;

                // Cas du module non installé
                if (!$backObject->_ref_module) {
                    continue;
                }

                // Unstored object
                if (!$backObject->_spec->table || !$backObject->_spec->key) {
                    continue;
                }

                $query = "UPDATE `{$backObject->_spec->table}`
        SET `$backField` = '$this->_id'
        WHERE `$backField` = '$object->_id'";

                // Cas des meta objects
                $fwdSpec  =& $backObject->_specs[$backField];
                $backMeta = $fwdSpec->meta;
                if ($backMeta) {
                    $class = $backMeta == 'object_class_id' ? $object->getObjectClassID() : $object->_class;
                    $query .= "\nAND `$backMeta` = '$class'";
                }

                // Do not pass CMergeLog here, because we only want top-level relations logged
                $backObject->fastTransfer($object, $this);

                $ds->exec($query);

                if ($ds->affectedRows() >= 1) {
                    $detail_merged_relations[$backSpec->name] = $ds->affectedRows();
                }
            }
        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($merge_log && $merge_log->_id && $detail_merged_relations) {
                $merge_log->logDetailMergedRelations($detail_merged_relations);
            }
        }
    }

    /**
     * Called by self::fastTransferBackRefsFrom
     *
     * @param CStoredObject $from Source object
     * @param CStoredObject $to   Target object
     *
     * @return void
     */
    function fastTransfer(CStoredObject $from, CStoredObject $to)
    {
    }

    /**
     * Transfer all back refs from given object of same class
     *
     * @param CMbObject $object The object to transfer back objects from
     *
     * @throws Exception
     */
    public function transferBackRefsFrom(CMbObject &$object, ?CMergeLog $merge_log = null): void
    {
        if (!$object->_id) {
            throw CouldNotMerge::objectNotFound();
        }

        if ($object->_class !== $this->_class) {
            throw CouldNotMerge::differentType($object->_class, $this->_class);
        }

        $detail_merged_relations = [];

        try {
            $object->loadAllBackRefs();

            foreach ($object->_back as $backName => $backObjects) {
                /** @var self[] $backObjects */
                if (!count($backObjects)) {
                    continue;
                }

                $backSpec = $this->_backSpecs[$backName];

                $backObject = new $backSpec->class();
                $backField  = $backSpec->field;
                $fwdSpec    = $backObject->_specs[$backField];
                $backMeta   = $fwdSpec->meta;

                $_merged_relations = 0;

                try {
                    // Change back field and store back objects
                    foreach ($backObjects as $backObject) {
                        /** @var self $transferer Dummy tranferer object to prevent checks on all values */
                        $transferer                     = new $backObject->_class();
                        $transferer->_id                = $backObject->_id;
                        $transferer->$backField         = $this->_id;
                        $transferer->_forwardRefMerging = true;

                        // Cas des meta objects
                        if ($backMeta) {
                            // user_log vs user_action
                            $transferer->$backMeta = $backMeta == 'object_class_id' ? $this->getObjectClassID(
                            ) : $this->_class;
                        }

                        if ($msg = $transferer->store()) {
                            throw CouldNotMerge::backrefsTransferFailure($msg);
                        }

                        $_merged_relations++;
                    }
                } catch (Exception $e) {
                    throw $e;
                } finally {
                    if ($_merged_relations) {
                        $detail_merged_relations[$backName] = $_merged_relations;
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($merge_log && $merge_log->_id && $detail_merged_relations) {
                $merge_log->logDetailMergedRelations($detail_merged_relations);
            }
        }
    }

    /**
     * Load and count all back references collections
     *
     * @param string $limit  Limit SQL query option
     * @param bool   $strict If strict, performs some additional checks in the request
     *
     * @return void
     * @throws Exception
     */
    function loadAllBackRefs($limit = null, bool $strict = true)
    {
        foreach ($this->_backProps as $backName => $backProp) {
            $backrefs = $this->loadBackRefs($backName, null, $limit, null, null, null, null, null, $strict);

            $this->_count[$backName] = (is_countable($backrefs)) ? count($backrefs) : 0;

            if ($limit) {
                $this->_count[$backName] = null;
                $this->countBackRefs($backName);
            }
        }
    }

    /**
     * Count number back reference collection object
     *
     * @param string $backName    Name the of the back references to count
     * @param array  $where       Additional where clauses
     * @param array  $ljoin       Additionnal ljoin clauses
     * @param bool   $cache       Cache
     * @param string $backNameAlt BackName Alt
     *
     * @return int|null The count, null if collection count is unavailable
     * @throws Exception
     */
    function countBackRefs($backName, $where = [], $ljoin = [], $cache = true, $backNameAlt = "")
    {
        if (!$backSpec = $this->makeBackSpec($backName)) {
            return null;
        }

        // No existing class
        if (!self::classExists($backSpec->class)) {
            return null;
        }

        $backObject = new $backSpec->class;
        $backField  = $backSpec->field;

        // Cas du module non installé
        if (!$backObject->_ref_module) {
            return null;
        }

        $backName = $backNameAlt ? $backNameAlt : $backName;
        $cache    = $cache && (!count($where) || $backNameAlt);

        // Empty object
        if (!$this->_id || !$backObject->_spec->table || !$backObject->_spec->key) {
            return $this->_count[$backName] = 0;
        }

        // Mass count optimization
        if ($cache && isset($this->_count[$backName])) {
            return $this->_count[$backName];
        }

        $backTable = $backObject->_spec->table;
        // @todo Refactor using CRequest
        $query = "SELECT COUNT({$backObject->_spec->key}) FROM `{$backTable}`";

        if ($ljoin && count($ljoin)) {
            foreach ($ljoin as $table => $condition) {
                $query .= "\nLEFT JOIN `$table` ON $condition ";
            }
        }

        $query .= "WHERE `$backTable`.`$backField` = '$this->_id'";

        // Additional where clauses
        foreach ($where as $_field => $_clause) {
            $split = explode(".", $_field);
            if (is_string($_field)) {
                $query .= "\nAND " . (count($split) > 1 ? "`$split[0]`.`$split[1]`" : "`$_field`") . " $_clause";
            } else {
                $query .= "\nAND $_clause";
            }
        }

        // Cas des meta objects
        $backSpec =& $backObject->_specs[$backField];
        $backMeta = $backSpec->meta;
        if ($backMeta) {
            $class = $backMeta == 'object_class_id' ? $this->getObjectClassID() : $this->_class;
            $query .= "\nAND `$backTable`.`$backMeta` = '$class'";
        }

        // Comptage des backrefs
        return $this->_count[$backName] = $this->_spec->ds->loadResult($query);
    }

    /**
     * Default delete method
     *
     * @return null|string null if successful, an error message otherwise
     * @throws Exception
     * @todo   Fix cyclomatic complexity
     */
    function delete()
    {
        // Delete checking
        if (!$this->_purge) {
            // Préparation du log
            $this->loadOldObject();

            if ($msg = $this->canDeleteEx()) {
                return $msg;
            }
        }

        if (!$this->_id) {
            return CAppUI::tr("noObjectToDelete") . " " . CAppUI::tr($this->_class);
        }

        // Trigger before event
        $this->notify(ObjectHandlerEvent::BEFORE_DELETE());

        $ds = $this->getDS();

        // Deleting backSpecs
        foreach ($this->_backSpecs as $backSpec) {
            /** @var self $backObject */
            $backObject = new $backSpec->class;
            $backField  = $backSpec->field;

            /** @var CRefSpec $fwdSpec */
            $fwdSpec  = $backObject->_specs[$backField];
            $backMeta = $fwdSpec->meta;

            /* Cas du module non installé,
       * Cas de l'interdiction de suppression,
       * Cas de l'interdiction de la non liaison des backRefs */
            if (!$backObject->_ref_module) {
                continue;
            }

            if (!($fwdSpec->cascade || $backSpec->cascade || $fwdSpec->nullify) || $fwdSpec->unlink) {
                continue;
            }

            // Cas de nullification de la collection
            if ($fwdSpec->nullify) {
                if (!$fwdSpec->notNull) {
                    $spec  = $backObject->getSpec();
                    $query = "UPDATE `$spec->table` SET `$backField` = NULL WHERE `$backField` = '$this->_id'";

                    if ($backMeta) {
                        $class = $backMeta == 'object_class_id' ? $this->getObjectClassID() : $this->_class;
                        $query .= " AND `$backMeta` = '$class'";
                    }

                    $ds->exec($query);
                }
            } // Suppression en cascade
            else {
                $where = [
                    $backField => "= '$this->_id'",
                ];

                // Cas des meta objects
                if ($backMeta) {
                    $class            = $backMeta == 'object_class_id' ? $this->getObjectClassID() : $this->_class;
                    $where[$backMeta] = "= '$class'";
                }

                if ($fwdSpec->cascade || $backSpec->cascade) {
                    $_backRefs = $backObject->loadList($where);
                    foreach ($_backRefs as $object) {
                        if ($msg = $object->delete()) {
                            return $msg;
                        }
                    }
                }
            }
        }

        // Actually delete record
        $result = $ds->deleteObject($this->_spec->table, $this->_spec->key, $this->_id);

        if (!$result) {
            return CAppUI::tr($this->_class) .
                CAppUI::tr("CMbObject-msg-delete-failed") .
                $this->_spec->ds->error();
        }

        // Deletion successful
        $this->_id = null;

        // Enregistrement du log une fois le delete terminé
        if (CAppUI::conf("activer_user_action")) {
            $this->prepareUserAction();
            $this->doUserAction();
        } else {
            $this->prepareLog();
            $this->doLog();
        }

        // Event Handlers
        $this->notify(ObjectHandlerEvent::AFTER_DELETE());

        CMbArray::inc(self::$deletedCounts, $this->_class);

        return $this->_old = null;
    }

    /**
     * Check whether the object can be deleted.
     * Default behaviour counts for back reference without cascade
     *
     * @return string|null null if ok, error message otherwise
     * @throws Exception
     */
    function canDeleteEx()
    {
        // Empty object
        if (!$this->_id) {
            return CAppUI::tr("noObjectToDelete") . " " . CAppUI::tr($this->_class);
        }

        // Counting backrefs
        $issues = [];
        $this->makeAllBackSpecs();
        foreach ($this->_backSpecs as $backName => &$backSpec) {
            /** @var self $backObject */
            $backObject = new $backSpec->class;
            $backField  = $backSpec->field;

            /** @var CRefSpec $fwdSpec */
            $fwdSpec  = $backObject->_specs[$backField];
            $backMeta = $fwdSpec->meta;

            // Cas du module non installé
            if (!$backObject->_ref_module) {
                continue;
            }

            // Cas de la nullification
            if ($fwdSpec->nullify) {
                continue;
            }

            // Cas de la suppression en cascade
            if ($fwdSpec->cascade || $backSpec->cascade) {
                // Vérification de la possibilité de supprimer chaque backref
                $backObject->$backField = $this->_id;

                // Cas des meta objects
                if ($backMeta) {
                    $backObject->$backMeta = $backMeta == 'object_class_id' ? $this->getObjectClassID() : $this->_class;
                }

                $subissues          = [];
                $cascadeIssuesCount = 0;
                $cascadeObjects     = $backObject->loadMatchingList();
                foreach ($cascadeObjects as $cascadeObject) {
                    if ($msg = $cascadeObject->canDeleteEx()) {
                        $subissues[] = $msg;
                    }
                }

                if (count($subissues)) {
                    $issues[] = CAppUI::tr("CMbObject-msg-cascade-issues")
                        . " " . $cascadeIssuesCount
                        . "/" . count($cascadeObjects)
                        . " " . CAppUI::tr("$fwdSpec->class-back-$backName")
                        . ": " . implode(", ", $subissues);
                }
                continue;
            }

            // Vérification du nombre de backRefs
            if (!$fwdSpec->unlink) {
                if ($backCount = $this->countBackRefs($backName, [], [], false)) {
                    $issues[] = $backCount
                        . " " . CAppUI::tr("$fwdSpec->class-back-$backName");
                }
            }
        }

        $msg = count($issues) ?
            CAppUI::tr("CMbObject-msg-nodelete-backrefs") . ": " . implode(", ", $issues) :
            null;

        return $msg;
    }

    /**
     * Merges an array of objects.
     *
     * @param self[] $objects An array of objects to merge.
     *
     * @throws CanNotMerge
     */
    public function checkMerge(array $objects = []): void
    {
        $object_class = null;
        foreach ($objects as $object) {
            if (!$object instanceof CMbObject) {
                throw CanNotMerge::invalidObject();
            }

            if (!$object->_id) {
                throw CanNotMerge::objectNotFound();
            }

            if (!$object_class) {
                $object_class = $object->_class;
            } elseif ($object->_class !== $object_class) {
                throw CanNotMerge::differentType();
            }
        }
    }

    /**
     * Load named back reference collection IDs
     *
     * @param string       $backName Name of the collection
     * @param array|string $order    Order SQL statement
     * @param string       $limit    MySQL limit clause
     * @param array|string $group    Group by SQL statement
     * @param array        $ljoin    Array of left join clauses
     * @param array        $where    Additional where clauses
     * @param bool         $strict   If strict, performs some additional checks in the request
     *
     * @return int[]|integer[]|null IDs collection, null if colletion is unavailable
     * @throws Exception
     */
    function loadBackIds(
        $backName,
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $where = [],
        bool $strict = true
    ) {
        if (!$backSpec = $this->makeBackSpec($backName)) {
            return null;
        }

        // No existing class
        if (!self::classExists($backSpec->class)) {
            return null;
        }

        /** @var self $backObject */
        $backObject = new $backSpec->class;

        // Cas du module non installé
        if (!$backObject->_ref_module) {
            return null;
        }

        $backField = $backSpec->field;
        /** @var CRefSpec $fwdSpec */
        $fwdSpec  = $backObject->_specs[$backField];
        $backMeta = $fwdSpec->meta;

        // Cas des meta objects
        if ($backMeta) {
            trigger_error("meta case anavailable", E_USER_ERROR);
        }

        // Empty object
        if (!$this->_id) {
            return [];
        }

        // Vérification de la possibilité de supprimer chaque backref
        $where[$backField] = " = '$this->_id'";

        return $backObject->loadIds($where, $order, $limit, $group, $ljoin, $strict);
    }

    /**
     * Object list for given statements
     *
     * @param array        $where  Array of where clauses
     * @param array|string $order  Order SQL statement
     * @param string       $limit  MySQL limit clause
     * @param array|string $group  Group by SQL statement
     * @param array        $ljoin  Array of left join clauses
     * @param bool         $strict If strict, performs some additional checks in the request
     *
     * @return integer[] List of found IDs, null if module is not installed
     * @throws Exception
     */
    function loadIds($where = null, $order = null, $limit = null, $group = null, $ljoin = null, bool $strict = true)
    {
        if (!$this->_ref_module) {
            return null;
        }

        $request = new CRequest($strict);
        $request->addLJoin($ljoin);
        $request->addWhere($where);
        $request->addGroup($group);
        $request->addOrder($order);
        $request->setLimit($limit);

        $ds = $this->_spec->ds;

        return $ds->loadColumn($request->makeSelectIds($this));
    }

    /**
     * Load the unique back reference for given collection name
     * Will check for uniqueness
     *
     * @param string       $backName    The collection name
     * @param array|string $order       Order SQL statement
     * @param string       $limit       MySQL limit clause
     * @param array|string $group       Group by SQL statement
     * @param array        $ljoin       Array of left join clauses
     * @param string       $backNameAlt BackName Alt
     * @param array        $where       Where clause
     * @param bool         $strict      If strict, performs some additional checks in the request
     *
     * @return CMbObject Unique back reference if exist, concrete type empty object otherwise, null if unavailable
     * @throws Exception
     */
    function loadUniqueBackRef(
        $backName,
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $backNameAlt = "",
        $where = [],
        bool $strict = true
    ) {
        if (null === $backRefs = $this->loadBackRefs(
                $backName,
                $order,
                $limit,
                $group,
                $ljoin,
                null,
                $backNameAlt,
                $where,
                $strict
            )) {
            return null;
        }

        $count = count($backRefs);
        if ($count > 1) {
            $ids = array_keys($backRefs);
            $msg = CAppUI::tr(
                "'%s' back reference should be unique (actually counting %s: %s) for object '%s' of class '%s'",
                $backName,
                $count,
                implode(", ", $ids),
                $this->_view,
                $this->_class
            );
            trigger_error($msg, E_USER_WARNING);
        }

        if (!$count) {
            $backSpec = $this->_backSpecs[$backName];

            return new $backSpec->class;
        }

        return reset($backRefs);
    }

    /**
     * Load the last back reference for given collection name
     *
     * @param string       $backName The collection name
     * @param array|string $order    Order SQL statement
     * @param string       $limit    MySQL limit clause
     * @param array|string $group    Group by SQL statement
     * @param array        $ljoin    Array of left join clauses
     * @param bool         $strict   If strict, performs some additional checks in the request
     *
     * @return CMbObject Unique back reference if exist, concrete type empty object otherwise, null if unavailable
     * @throws Exception
     */
    function loadLastBackRef($backName, $order = null, $limit = null, $group = null, $ljoin = null, bool $strict = true)
    {
        if (null === $backRefs = $this->loadBackRefs(
                $backName,
                $order,
                $limit,
                $group,
                $ljoin,
                null,
                null,
                null,
                $strict
            )) {
            return null;
        }

        if (!count($backRefs)) {
            $backSpec = $this->_backSpecs[$backName];

            return new $backSpec->class;
        }

        return end($backRefs);
    }

    /**
     * Count all back references collections
     *
     * @return void
     * @throws Exception
     */
    function countAllBackRefs()
    {
        foreach ($this->_backProps as $backName => $backProp) {
            $this->_count[$backName] = $this->countBackRefs($backName);
        }
    }

    /**
     * Clear the back reference for given collection name (cache)
     *
     * @param string $backName The collection name
     *
     * @return void
     */
    function clearBackRefCache($backName)
    {
        unset($this->_count[$backName]);
        unset($this->_back[$backName]);
    }

    /**
     * Purge an entire object, including recursive back references
     *
     * @return string Store-like message
     * @throws Exception
     */
    function purge()
    {
        $this->loadAllBackRefs();
        foreach ($this->_back as $backName => $backRefs) {
            foreach ($backRefs as $backRef) {
                /** @var self $backRef */
                $backSpec = $this->_backSpecs[$backName];
                if ($backSpec->_notNull || $backSpec->_purgeable || $backSpec->_cascade || $backSpec->cascade) {
                    if ($msg = $backRef->purge()) {
                        return $msg;
                    }
                } else {
                    $backRef->{$backSpec->field} = "";
                    if ($msg = $backRef->store()) {
                        return $msg;
                    }
                }
                CAppUI::setMsg("$backRef->_class-msg-delete", UI_MSG_ALERT);
            }
        }

        // Make sure delete won't log and won't can delete
        $this->_purge = "1";

        return $this->delete();
    }

    /**
     * Returns a list of objects for autocompleted fields
     *
     * @param int    $permType Type of permission
     * @param string $keywords Autocomplete seek fields
     * @param array  $where    Where statements
     * @param int    $limit    Limit the number of results
     * @param array  $ljoin    Left join statements
     * @param array  $order    Order by
     * @param bool   $strict   If strict, performs some additional checks in the request
     *
     * @return self[]
     * @throws Exception
     */
    function getAutocompleteListWithPerms(
        $permType = PERM_READ,
        $keywords = null,
        $where = null,
        $limit = null,
        $ljoin = null,
        $order = null,
        bool $strict = true
    ) {
        // Filter with permission
        if (!$permType) {
            return $this->getAutocompleteList($keywords, $where, $limit, $ljoin, $order, null, $strict);
        }

        // Load with no limit
        $list = $this->getAutocompleteList($keywords, $where, null, $ljoin, $order, null, $strict);
        self::filterByPerm($list, $permType);

        // We simulate the MySQL LIMIT
        if ($limit) {
            $list = CRequest::artificialLimit($list, $limit);
        }

        return $list;
    }

    /**
     * Returns a list of objects for autocompleted fields
     *
     * @param string $keywords Autocomplete seek fields
     * @param array  $where    Where statements
     * @param int    $limit    Limit the number of results
     * @param array  $ljoin    Left join statements
     * @param array  $order    Order by
     * @param string $group_by Group by
     * @param bool   $strict   If strict, performs some additional checks in the request
     *
     * @return self[]
     * @throws Exception
     */
    function getAutocompleteList(
        $keywords,
        $where = null,
        $limit = null,
        $ljoin = null,
        $order = null,
        $group_by = null,
        bool $strict = true
    ) {
        return $this->seek($keywords, $where, $limit, false, $ljoin, $order, $group_by, $strict);
    }

    /**
     * Generic seek method
     *
     * @param string $keywords   Keywords to search
     * @param array  $where      Where statements
     * @param int    $limit      Limit the number of results
     * @param bool   $countTotal Count the totale number of results (like if $limit == infinite)
     * @param array  $ljoin      Left join statements
     * @param array  $order      Order by
     * @param array  $group_by   Group by
     * @param bool   $strict     If strict, performs some additional checks in the request
     *
     * @return static[] The first 100 records which fits the keywords
     * @throws Exception
     * @todo   Change the order of the arguments so that it matches the loadList method
     * @todo   Function nesting is too high, consider refactoring the method
     */
    function seek(
        $keywords,
        $where = [],
        $limit = 100,
        $countTotal = false,
        $ljoin = null,
        $order = null,
        $group_by = null,
        bool $strict = true
    ) {
        if (!is_array($keywords)) {
            $regex = '/"([^"]+)"/';

            $keywords = trim($keywords);
            $keywords = str_replace('\\"', '"', $keywords);

            // Find quoted strings
            if (preg_match_all($regex, $keywords, $matches)) {
                $keywords = preg_replace($regex, "", $keywords); // ... and remove them
            }

            $keywords = preg_split('/\s+/', $keywords);

            // If there are quoted strings
            if (isset($matches[1])) {
                $quoted_strings = $matches[1];
                foreach ($quoted_strings as &$_quoted) {
                    $_quoted = str_replace(" ", "_", $_quoted);
                }
                $keywords = array_merge($quoted_strings, $keywords);
            }

            $keywords = array_filter($keywords);
        }

        $seekables = $this->getSeekables();

        $query = "FROM `{$this->_spec->table}` ";

        if ($ljoin && count($ljoin)) {
            foreach ($ljoin as $table => $condition) {
                // Complex left join
                if (is_numeric($table)) {
                    $query .= "\nLEFT JOIN $condition";
                } else {
                    $query .= "\nLEFT JOIN `$table` ON $condition";
                }
            }
        }

        $noWhere = true;

        $query .= " WHERE 1";

        // Add specific where clauses
        if ($where && count($where)) {
            $noWhere = false;
            foreach ($where as $col => $value) {
                if (is_string($col)) {
                    $col = str_replace('.', '`.`', $col);
                }
                if (is_numeric($col)) {
                    $query .= " AND ($value)";
                } else {
                    $query .= " AND (`$col` $value)";
                }
            }
        }

        /* Queries using the LIKE syntax return all results if searched term is '%'
     * However using the MATCH AGAINST syntax, no results shall appear,
     * If keywords are empty or '%', the whole clause must be removed
     * */
        $empty_keywords = true;
        foreach ($keywords as $keyword) {
            if (!empty($keyword) && $keyword !== "%") {
                $empty_keywords = false;
                break;
            }
        }

        $seekable_available = $this->canUseMatchAgainst($empty_keywords);

        // Add seek clauses
        if (count($seekables)) {
            if ($seekable_available) {
                $query .= "\nAND ";
                $query .= $this->prepareMatchAll($keywords);
            } elseif (count($keywords)) {
                /* Standard LIKE query syntax */
                foreach ($keywords as $index => $keyword) {
                    $query .= "\nAND (0";
                    foreach ($seekables as $field => $spec) {
                        // Note: a switch won't work du to boolean true value
                        if ($spec->seekable === "equal" and $index == 0) {
                            $query .= "\nOR `{$this->_spec->table}`.`$field` = '$keyword'";
                        }
                        if ($spec->seekable === "begin" and $index == 0) {
                            $query .= "\nOR `{$this->_spec->table}`.`$field` LIKE '$keyword%'";
                        }
                        if ($spec->seekable === "end" and $index == 0) {
                            $query .= "\nOR `{$this->_spec->table}`.`$field` LIKE '%$keyword'";
                        }
                        if ($spec->seekable === true or $spec->seekable === 'order' or $index != 0) {
                            if ($spec instanceof CRefSpec && $spec->class !== $this->_class) {
                                $class = $spec->class;

                                // object_class field (object is empty)
                                if ($spec->meta) {
                                    $meta_field_spec = $this->_specs[$spec->meta];
                                    if ($meta_field_spec instanceof CEnumSpec) {
                                        $class_list = $meta_field_spec->_list;
                                        if (count($class_list) <= static::MAX_SEEKABLE_META_REF) {
                                            $class = $class_list;
                                        }
                                    }
                                }

                                if (!is_array($class)) {
                                    $class = [$class];
                                }

                                foreach ($class as $class_name) {
                                    /** @var self $object */
                                    $object  = new $class_name;
                                    $objects = $object->seek($keywords, null, $limit);
                                    if (count($objects)) {
                                        $ids   = implode(',', array_keys($objects));
                                        $query .= "\nOR `{$this->_spec->table}`.`$field` IN ($ids)";
                                    }
                                }
                            } else {
                                $query .= "\nOR `{$this->_spec->table}`.`$field` LIKE '%$keyword%'";
                            }
                        }
                    }

                    $query .= "\n)";
                }
            }
        } elseif ($noWhere) {
            $query .= "\nAND 0";
        }

        $this->_totalSeek = null;

        if ($countTotal) {
            $ds               = $this->_spec->ds;
            $result           = $ds->query("SELECT COUNT(*) AS _total $query");
            $line             = $ds->fetchAssoc($result);
            $this->_totalSeek = $line["_total"];
            $ds->freeResult($result);
        }

        if ($group_by) {
            $query .= "\nGROUP BY ";

            $group_by_query = is_array($group_by) ? implode(', ', $group_by) : $group_by;

            if ($strict) {
                $this->checkGroupBy($group_by_query);
            }

            $query .= $group_by_query;
        }

        $query .= "\nORDER BY";
        if ($order) {
            $order_by_query = is_array($order) ? implode(', ', $order) : $order;

            if ($strict) {
                $this->checkOrderBy($order_by_query);
            }

            $query .= "\n $order_by_query";
        } else {
            foreach ($seekables as $field => $spec) {
                $query .= "\n`$field`,";
            }

            $query .= "\n `{$this->_spec->table}`.`{$this->_spec->key}`";
        }

        if ($limit) {
            if ($strict) {
                $this->checkLimit($limit);
            }

            $query .= "\n LIMIT $limit";
        }

        return $this->loadQueryList("SELECT `{$this->_spec->table}`.* $query");
    }

    /**
     * @param bool         $empty_keywords
     * @param string|array $values
     *
     * @throws Exception
     */
    private function canUseMatchAgainst(bool $empty_keywords): bool
    {
        return isset($this->_spec->seek) && $this->_spec->seek === 'match' && !$empty_keywords;
    }

    /**
     * Retrieve seekable specs from object
     *
     * @param bool $order
     *
     * @return CMbFieldSpec[]
     */
    function getSeekables(bool $order = false)
    {
        $seekables = [];
        if ($this->_specs) {
            foreach ($this->_specs as $field => $spec) {
                if (isset($spec->seekable)) {
                    if ($order && $spec->seekable === 'order') {
                        $seekables[$field] = $spec;
                    } elseif (!$order) {
                        $seekables[$field] = $spec;
                    }
                }
            }
        }

        return $seekables;
    }

    /**
     * Load similar objects, ie unexpectedly having the same first unique tuple
     *
     * @param string[] $values Unique tuple values
     *
     * @return static[]|null Similar objects, null when unavailable
     * @throws Exception
     */
    function getSimilar($values)
    {
        $spec = $this->_spec;

        if (empty($spec->uniques)) {
            return null;
        }

        $first_unique = reset($spec->uniques);

        if (empty($first_unique)) {
            return null;
        }

        $where = [];
        foreach ($first_unique as $field_name) {
            if (!array_key_exists($field_name, $values)) {
                continue;
            }

            $where[$field_name] = $spec->ds->prepare("=%", $values[$field_name]);
        }

        return $this->loadList($where);
    }

    /**
     * Get IDs from similar object, based on unique tuples
     *
     * @return integer[]|null
     * @throws Exception
     */
    function getSimilarIDs()
    {
        $spec = $this->_spec;

        if (empty($spec->uniques)) {
            return null;
        }

        $first_unique = reset($spec->uniques);

        if (empty($first_unique)) {
            return null;
        }

        $where = [];
        foreach ($first_unique as $field_name) {
            $where[$field_name] = $spec->ds->prepare("=%", $this->$field_name);
        }

        return $this->loadIds($where);
    }

    /**
     * Load object view information
     *
     * @return void
     * @throws Exception
     */
    function loadView()
    {
        $this->loadAllFwdRefs();
        $this->canDo();
    }

    /**
     * Load all forward references
     *
     * @return void
     * @throws Exception
     * @deprecated
     */
    function loadAllFwdRefs()
    {
        foreach ($this->_specs as $field => $spec) {
            // Use cache in order to get objects by reference if available
            $this->loadFwdRef($field, true);
        }
    }

    /**
     * Duplicates the object including backrefs
     *
     * @param string $field_copy_name Field to add " (Copy)"
     * @param array  $backrefs        Backrefs to duplicates
     *
     * @return null|string Store-like message
     * @throws Exception
     */
    function duplicateObject($field_copy_name = null, $backrefs = [])
    {
        if (!$this->_id) {
            return null;
        }

        // Load all field values
        $this->load();

        /** @var CStoredObject $new */
        $new = new static;
        $new->cloneFrom($this);

        if ($field_copy_name) {
            $new->$field_copy_name .= " (Copy)";
        }

        if ($msg = $new->store()) {
            return $msg;
        }

        $msg = "";
        foreach ($backrefs as $backname) {
            $msg .= $this->duplicateBackRefs($this, $backname, $new->_id);
        }

        return $msg;
    }

    /**
     * Duplicate back refs
     *
     * @param CStoredObject $object    Object to duplicate back refs of
     * @param string        $backname  Back reference name
     * @param mixed         $fwd_value Forward field value
     *
     * @return string|null
     * @throws Exception
     */
    function duplicateBackRefs(CStoredObject $object, $backname, $fwd_value)
    {
        if (!CMbArray::get($object->_backProps, $backname)) {
            return null;
        }

        $new = null;

        $fwd_field = CMbArray::get(explode(" ", CMbArray::get($object->_backProps, $backname)), 1);

        $msg = "";
        foreach ($object->loadBackRefs($backname) as $_back) {
            /** @var CMbObject $_back */
            $msg .= $this->duplicateObjectBackRef($_back, $fwd_field, $fwd_value, $new);
        }

        return $msg;
    }

    /**
     * Duplicates an object
     *
     * @param CMbObject $object    The object to duplicate
     * @param string    $fwd_field Forward field
     * @param mixed     $fwd_value Forward value
     * @param CMbObject $new       The new object (input)
     *
     * @return null|string
     * @throws Exception
     */
    function duplicateObjectBackRef(CMbObject $object, $fwd_field, $fwd_value, &$new = null)
    {
        $class = $object->_class;

        /** @var CMbObject $new */
        $new = new $class;
        $new->cloneFrom($object);

        $new->$fwd_field = $fwd_value;

        return $new->store();
    }


    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = [];

        foreach ($this->_props as $_field => $_prop) {
            if ($this->$_field === null) {
                continue;
            }

            $data[$_field] = $this->$_field;
        }

        foreach ($this->getJsonFields() as $_field) {
            if ($this->$_field === null) {
                continue;
            }

            $data[$_field] = $this->$_field;
        }

        return $data;
    }

    /**
     * Get additionnal JSON serialized fields
     *
     * @return array
     */
    function getJsonFields()
    {
        return $this->_jsonFields;
    }

    /**
     * Get the last $limit modifications for the field
     *
     * @param string $field  Name of the field
     * @param int    $limit  Number of result
     * @param bool   $strict If strict, performs some additional checks in the request
     *
     * @return array
     * @throws Exception
     * @todo   Refactor after migration log > action need to use undiff_old_Values
     */
    function getFieldHistory($field, $limit = 20, bool $strict = true)
    {
        if (!$this->_id) {
            return null;
        }

        // action
        $action                   = new CUserAction();
        $ds                       = $this->getDS();
        $where                    = [
            "object_class_id" => $ds->prepare('= ?', $this->getObjectClassID()),
            "object_id"       => $ds->prepare('= ?', $this->_id),
            "type"            => $ds->prepareIn(['store', 'create']),
        ];
        $join                     = [];
        $join['user_action_data'] = "user_action.user_action_id = user_action_data.user_action_id";
        $where[]                  = "user_action_data.field = '$field'";
        $actions                  = $action->loadList($where, 'date DESC', $limit, null, $join, null, null, $strict);
        foreach ($actions as $_id => $_action) {
            $_log = new CUserLog();
            $_action->loadRefUserActionDatas();
            $actions[$_id] = $_log->loadFromUserAction($_action);
        }

        // log
        $log    = new CUserLog();
        $ds     = $this->getDS();
        $return = [];

        $where = [
            'object_class' => $ds->prepare('= ?', $this->_class),
            'object_id'    => $ds->prepare('= ?', $this->_id),
            'type'         => $ds->prepareIn(['store', 'create']),
            'fields'       => $ds->prepareLike("%$field%"),
        ];

        $logs = $log->loadList($where, 'date DESC', $limit, null, null, null, null, $strict);
        $logs = array_merge($logs, $actions);

        $spec = $this->_specs[$field];
        if ($spec instanceof CTextSpec) {
            $this->loadHistory();
            foreach ($logs as $_log) {
                $_history_key        = array_reverse(array_keys($this->_history));
                $_current_key        = array_search($_log->_id, $_history_key);
                $_previous_key       = $_current_key > 0 ? $_history_key[$_current_key - 1] : false;
                $return[$_log->date] = $_previous_key ? $this->_history[$_previous_key][$field] : null;
            }
        } else {
            /** @var CUserLog|CUserAction $_log */
            foreach ($logs as $_log) {
                $_log->getOldValues();
                if (array_key_exists($field, $_log->_old_values)) {
                    $return[$_log->date] = $_log->_old_values[$field];
                }
            }
        }

        return $return;
    }

    /**
     * Load object state along the time, according to user actions
     *
     * @return void
     * @throws Exception
     */
    private function loadHistoryUserAction()
    {
        $this->_history = [];
        $this->loadUserActions();
        $clone = $this->getPlainFields();

        CStoredObject::massLoadBackRefs($this->_ref_user_actions, 'user_action_datas');

        foreach ($this->_ref_user_actions as $_user_action) {
            $_user_action->loadRefUserActionDatas();

            $this->_history[$_user_action->_id] = $clone;

            foreach ($_user_action->_ref_user_action_datas as $_user_action_data) {
                $_field = $_user_action_data->field;
                $_value = $_user_action_data->value;

                if ($_uncompress = @gzuncompress($_value ?? '')) {
                    $_value = $_uncompress;
                }

                $clone[$_field] = $_value;
            }
        }
    }

    /**
     * Load last user_action concerning a given field
     *
     * @param string $fieldName Field name
     * @param bool   $strict    Be strict about the field name
     *
     * @return CUserAction
     * @throws Exception
     */
    private function loadLastUserActionForField($fieldName = null, $strict = false)
    {
        $user_action      = new CUserAction;
        $user_actions     = $this->loadUserActionsForField($fieldName, $strict, 1);
        $last_user_action = reset($user_actions);

        if ($last_user_action) {
            $last_user_action->loadRefUserActionDatas();

            return $last_user_action;
        }

        return $user_action;
    }

    /**
     * Load first User action concerning a given field
     *
     * @param string $fieldName Field name
     * @param bool   $strict    Be strict about the field name
     *
     * @return CUserAction
     * @throws Exception
     */
    private function loadFirstUserActionForField($fieldName = null, $strict = false)
    {
        $user_action       = new CUserAction;
        $user_actions      = $this->loadUserActionsForField($fieldName, $strict);
        $first_user_action = end($user_actions);

        if ($first_user_action) {
            $first_user_action->loadRefUserActionDatas();

            return $first_user_action;
        }

        return $user_action;
    }

    /**
     * Return the object's (first) creation user action
     * Former instances have legacy data with no creation userAction but later modification userAction
     * In that case we explicitely don't want it
     *
     * @return CUserAction
     * @throws Exception
     */
    private function loadCreationUserAction()
    {
        $userAction = $this->loadFirstUserAction();

        return $userAction->type == "create" ? $userAction : new CUserAction();
    }

    /**
     * @param array  $prat_ids Prat ids
     * @param string $date_min Minimum date
     * @param string $date_max Maximum date
     *
     * @return bool
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        return true;
    }


    /**
     * Prepares a MATCH AGAINST clause with given object and value
     *
     * @param array|string $values    The array or string containing space-separated words to search
     * @param string|bool  $operator  Operator
     * @param string       $condition Condition
     * @param bool         $order
     *
     * @return string The prepared match/against clause
     */
    function prepareMatchAll($values, $operator = false, $condition = 'and', bool $order = false)
    {
        if (is_string($values)) {
            $values = explode(' ', $values);
        }

        $fields = [];
        $refs   = [];

        $seekables = $this->getSeekables($order);
        foreach ($seekables as $index => $seekable) {
            if ($seekable instanceof CRefSpec && $seekable->class !== $this->_class) {
                $refs[$index] = $seekable;
            } else {
                $fields[$index] = $seekable;
            }
        }

        $query = '';

        if (!empty($fields)) {
            $query .= self::prepareMatch(
                array_keys($fields),
                $values,
                $operator,
                $condition
            );
            if (!empty($refs)) {
                $query .= ' AND ';
            }
        }

        if (!empty($refs)) {
            $query .= $this->prepareMatchRefs(
                $refs,
                $values
            );
        }

        return $query;
    }

    /**
     * Build additional query based on RefSpec seekables
     *
     * @param array        $fields Fields
     * @param string|array $values Keywords
     *
     * @return string
     */
    function prepareMatchRefs($fields, $values)
    {
        $query                    = '';
        $additional_queries_array = [];

        foreach ($fields as $field_index => $spec) {
            $class = $spec->class;

            // Todo: BUG => Handle CObjectClass here
            if ($spec->meta) {
                $class = $this->{$spec->meta};
            }
            $object = new $class;
            /** @var CStoredObject $object */
            try {
                $objects = $object->seek($values, null, 0);
                if (count($objects)) {
                    $ids                        = implode(',', array_keys($objects));
                    $additional_queries_array[] = "`{$this->_spec->table}`.`$field_index` IN ($ids)";
                }
            } catch (Exception $e) {
                CAppUI::displayMsg($e->getMessage(), UI_MSG_ERROR);
            }
        }

        if (!empty($additional_queries_array)) {
            $additional_queries_array = array_unique($additional_queries_array);
            $query                    .= "\n(0 OR ";
            foreach ($additional_queries_array as $index => $additional_query) {
                if ($index !== 0) {
                    $query .= " AND ";
                }
                $query .= $additional_query;
            }
            $query .= ")\n";
        }

        return $query;
    }

    /**
     * Prepares a MATCH AGAINST clause with given value, field and mode to search
     *
     * @param string|array $fields    Fields
     * @param string|array $values    Keywords
     * @param string|null  $operator  Operator
     * @param string       $condition Condition
     * @param string       $mode      Query language mode (Search modifier)
     *
     * @return string The prepared match/against clause
     * @throws InvalidArgumentException
     */
    public static function prepareMatch(
        $fields,
        $values,
        ?string $operator = null,
        string $condition = 'and',
        string $mode = 'boolean'
    ): string {
        if (!in_array($mode, array_keys(self::$fulltext_query_language_modes))) {
            $msg = "$mode is not a valid query language mode. Allowed values are : "
                . implode(', ', array_keys(self::$fulltext_query_language_modes));
            throw new InvalidArgumentException($msg);
        }

        if (!in_array($condition, self::$fulltext_query_operators)) {
            $msg = "$condition is not a valid query condition. Allowed values are : "
                . implode(', ', self::$fulltext_query_operators);
            throw new InvalidArgumentException($msg);
        }

        if (!$fields) {
            throw new InvalidArgumentException('Fields cannot be null');
        }

        if (!$values) {
            throw new InvalidArgumentException('Values cannot be null');
        }

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        if (is_array($values) && count($values) > 0) {
            $keywords = [];
            foreach ($values as $value) {
                $keywords[] = self::prepareMatchOperatorValue($value, $operator, $condition, $mode);
            }
            $keywords = implode(' ', $keywords);
        } else {
            $keywords = self::prepareMatchOperatorValue($values, $operator, $condition, $mode);
        }

        return sprintf(
            "MATCH (%s) AGAINST('%s' %s)",
            $fields,
            $keywords,
            self::$fulltext_query_language_modes[$mode]
        );
    }

    /**
     * @param string      $value     Value
     * @param string|null $operator  Operator
     * @param string      $condition Condition
     * @param string      $mode      Query language mode (Search modifier)
     *
     * @return string
     */
    public static function prepareMatchOperatorValue(
        string $value,
        ?string $operator = null,
        string $condition = 'and',
        string $mode = 'boolean'
    ): string {
        $condition = ($condition === 'and' ? '+' : '');

        if ($mode === "boolean") {
            switch ($operator) {
                case 'end':
                    return "$condition" . "*$value";
                case 'equals':
                    return "$condition" . "$value";
                case 'begin':
                default:
                    return "$condition" . "$value*";
            }
        }

        return $value;
    }

    /**
     * Make and return usefull template paths for given object
     *
     * @param string $name One of "view" and "complete"
     *
     * @return string|null Path to wanted template, null if module undefined for object
     */
    function makeTemplatePath($name)
    {
        if (null == $module = $this->_ref_module) {
            return null;
        }

        $path = "$module->mod_name/templates/" . $this->_class;

        return "{$path}_{$name}.tpl";
    }

    public function isLoggable(): bool
    {
        // Switch does lose comparison, must not use boolean legacy const in it
        if ($this->_spec->loggable === CMbObjectSpec::LOGGABLE_LEGACY_TRUE) {
            $is_loggable = true;
        } elseif ($this->_spec->loggable === CMbObjectSpec::LOGGABLE_LEGACY_FALSE) {
            $is_loggable = false;
        }

        if (!isset($is_loggable)) {
            switch ($this->_spec->loggable) {
                case CMbObjectSpec::LOGGABLE_NEVER:
                    $is_loggable = false;
                    break;
                case CMbObjectSpec::LOGGABLE_HUMAN:
                    $is_loggable = !(bool)CApp::$is_robot;
                    break;
                case CMbObjectSpec::LOGGABLE_BOT:
                    $is_loggable = (bool)CApp::$is_robot;
                    break;
                case CMbObjectSpec::LOGGABLE_ALWAYS:
                default:
                    $is_loggable = true;
            }
        }

        return $is_loggable;
    }

    /**
     * Get the CIdSante400 attached to this object as a collection.
     *
     * @return Collection|array
     *
     * @throws ApiException
     */
    public function getResourceIdentifiants()
    {
        $idx = $this->loadBackRefs('identifiants');

        // Module identifiant might not be installed
        return $idx ? new Collection($idx) : [];
    }

    /**
     * Create CIdSante400 from the JsonApiItem collection passed.
     * Only id400 and tag fields are allowed.
     *
     * @param JsonApiItem[] $collection
     *
     * @throws RequestContentException|ApiException
     */
    public function setResourceIdentifiants(array $collection): void
    {
        foreach ($collection as $item) {
            if ($item instanceof JsonApiItem) {
                $this->_external_ids[] = $item->createModelObject(CIdSante400::class, true)
                    ->hydrateObject([], ['id400', 'tag'])
                    ->getModelObject();
            }
        }
    }

    /**
     * Store the CIdSante400 objects presents in $this->_external_ids.
     *
     * @throws Exception
     */
    protected function storeExternalIds(): void
    {
        /** @var CIdSante400 $external_id */
        foreach ($this->_external_ids as $external_id) {
            if (!$external_id instanceof CIdSante400) {
                continue;
            }

            $external_id->object_class = $this->_class;
            $external_id->object_id    = $this->_id;

            $external_id->store();
        }
    }

    private function resetLastUserAction(): void
    {
        $this->_ref_last_user_action = null;
    }
}
