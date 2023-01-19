<?php
/**
 * @package Mediboard\Populate
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Generators;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CStrSpec;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\System\CFirstNameAssociativeSex;
use ReflectionClass;

/**
 * Parent class for all the generators
 */
abstract class CObjectGenerator
{
    /** @var CMbObject */
    protected $object;
    protected $force = false;

    /** @var bool */
    protected $store = true;
    protected $group_id;
    /** @var string An optional type */
    protected $type;

    static $mb_class;
    static $dependances = [];
    static $ds          = [];

    const TRACE_STORE = 'store';
    const TRACE_LOAD  = 'load';

    static $trace = [
        "load"  => [],
        "store" => [],
    ];

    static $count_cim10;

    /** @var array A list of possible types */
    static $types = [];

    /**
     * CObjectGenerator constructor.
     */
    final public function __construct()
    {
        if (strpos(static::class, 'Ox\\Mediboard\\Populate') !== 0) {
        }
        $this->object = new static::$mb_class();
    }

    /**
     *  Generate an object
     *
     * @return CMbObject
     */
    abstract function generate();

    public function getObject(): CStoredObject
    {
        return $this->object;
    }

    /**
     * Get a random line from DB or create a new Object if countList is smaller than $max_count
     *
     * @param int   $max_count Maximum count of objects in DB
     * @param array $where     Where clause
     *
     * @return CMbObject|mixed|null
     * @throws Exception
     */
    protected function getRandomObject($max_count = null, $where = [])
    {
        $count_exists = $this->object->countList($where);

        if (!$max_count || $count_exists < $max_count) {
            return null;
        }

        $limit = rand(0, $max_count - 1);

        $objs = $this->object->loadList($where, null, "$limit, 1");
        $obj  = reset($objs);

        return $obj;
    }

    /**
     * Get random names from first_names_associative_sex table
     *
     * @param int   $count Number of names to return
     * @param array $where Where SQL clause to filter names
     *
     * @return array
     * @throws Exception
     */
    protected function getRandomNames($count = 1, $where = [])
    {
        $first_name = new CFirstNameAssociativeSex();
        $max        = $first_name->countList($where);

        $names = [];
        if ($max >= $count) {
            for ($i = 0; $i < $count; $i++) {
                $limit   = rand(0, ($max - 1));
                $name    = $first_name->loadList($where, null, "$limit, 1");
                $names[] = reset($name);
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $firstname = new CFirstNameAssociativeSex();
                $firstname->firstname = CStrSpec::randomString(CStrSpec::$chars, 16);
                $firstname->sex = rand(0, 1) ? 'm' : 'f';
                $names[] = $firstname;
            }
        }


        return $names;
    }

    /**
     * Get a random CIM10 that are not in $codes
     *
     * @param array $codes Codes to not retrieve
     *
     * @return array
     * @throws Exception
     */
    protected function getRandomCIM10Code($codes = [])
    {
        $ds = CSQLDataSource::get('cim10');

        $type = CCodeCIM10::getVersion();
        switch ($type) {
            case 'atih':
                $select = ['code', 'libelle'];
                $where  = [
                    'code'     => $ds->prepareNotIn($codes),
                    'type_mco' => "= '0'",
                ];
                $table  = 'codes_atih';
                break;
            case 'oms':
                $select = ['code' => 'abbrev', 'libelle' => 'abbrev'];
                $where  = [
                    'code' => $ds->prepareNotIn($codes),
                    'type' => "= 'S'",
                ];
                $table  = 'master';
                break;
            default:
                return [];
        }

        if (!self::$count_cim10) {
            $request = new CRequest();
            $request->addTable($table);
            $request->addWhere($where);

            self::$count_cim10 = $ds->loadResult($request->makeSelectCount());
        }

        $code_id = rand(1, self::$count_cim10) - 1;
        $request = new CRequest();
        $request->addSelect($select);
        $request->addTable($table);
        $request->addWhere($where);
        $request->setLimit("{$code_id}, 1");

        return $ds->loadHash($request->makeSelect());
    }

    /**
     * Trace an object
     *
     * @param string        $method Method
     * @param CStoredObject $object Object to log
     *
     * @return void
     */
    protected function trace($method, $object = null)
    {
        $object                                              = ($object) ?: $this->object;
        CObjectGenerator::$trace[$method][$object->_class][] = $object->_guid . " " . get_called_class();
    }

    /**
     * Get a random CP from the conf list
     *
     * @return string
     */
    protected function getRandomCP()
    {
        $cps = explode('|', CAppUI::conf("populate zip_codes"));

        return (is_array($cps)) ? $cps[array_rand($cps)] : null;
    }

    /**
     * Get a random commune
     *
     * @return array
     * @throws Exception
     */
    protected function getCommune()
    {
        $ds = CSQLDataSource::get('INSEE');

        $query = new CRequest(false);
        $query->addSelect(
            ['code_postal', 'commune']
        );
        $query->addTable('communes_france');

        $cp = $this->getRandomCP();

        if ($cp) {
            $query->addWhere(
                [
                    'code_postal' => $ds->prepareLike("$cp%"),
                ]
            );
        }

        $query->addOrder('RAND()');
        $query->setLimit(1);

        $obj = $ds->loadList($query->makeSelect());

        return reset($obj);
    }

    /**
     * @return int
     * @throws Exception
     */
    static function getCount()
    {
        if (!static::$mb_class || !class_exists(static::$mb_class)) {
            return 0;
        }

        /** @var CStoredObject $class */
        $class = new static::$mb_class();

        return $class->countList();
    }

    /**
     * @return array
     */
    static function getInfos()
    {
        return [
            "classes" => static::getDependances(),
            "ds"      => static::getDS(),
        ];
    }

    /**
     * @return array
     */
    static function getDependances()
    {
        return static::$dependances;
    }

    /**
     * @return array
     */
    static function getDS()
    {
        return static::$ds;
    }

    /**
     * @param bool $bool Force creation of object or not
     *
     * @return $this
     */
    function setForce($bool)
    {
        $this->force = $bool;

        return $this;
    }

    public function setStore(bool $value): self
    {
        $this->store = $value;
        return $this;
    }

    /**
     * Set the group id
     *
     * @param integer $group_id
     *
     * @return static
     */
    function setGroup($group_id)
    {
        $this->group_id = $group_id;

        return $this;
    }

    /**
     * Set the type
     *
     * @param string $type
     *
     * @return static
     */
    function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public static function getGenerators()
    {
        $children   = CClassMap::getInstance()->getClassChildren(static::class);
        $generators = [];
        foreach ($children as $child) {
            $reflection = new ReflectionClass($child);
            if ($reflection->isInstantiable()) {
                $generators[$child::$mb_class] = $child;
            }
        }

        return $generators;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    protected function getMaxCount()
    {
        $confs = CAppUI::conf('populate');

        $mb_class = CClassMap::getSN(static::$mb_class);

        return (isset($confs["{$mb_class}_max_count"])) ? $confs["{$mb_class}_max_count"] : null;
    }
}
