<?php

/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * References checker for CExClasses
 */
class CExClassRefChecker implements IShortNameAutoloadable
{
    public const PREFIX  = 'ref_check';
    public const PRE_TBL = 'ex_object_';


    public const FIELDS = [
        'object_class'     => 'object_id',
        'reference_class'  => 'reference_id',
        'reference2_class' => 'reference2_id',
    ];

    protected $ex_class_id;
    protected $start;
    protected $step;
    protected $total;
    protected $ended = false;
    protected $time_start;

    /** @var Cache */
    protected $cache;

    /** @var CExObject */
    protected $ex_object;

    /** @var CSQLDataSource */
    protected $ds;

    /** @var array */
    protected $ref_errors = [];

    /**
     * @param int $start Start used for limit
     * @param int $step  Step used for limit
     *
     * @return void
     * @throws Exception
     */
    public function check(int $ex_class_id, int $start = 0, int $step = 100)
    {
        $this->ex_class_id = $ex_class_id;
        $this->ex_object   = new CExObject($ex_class_id);

        $this->ds = $this->ex_object->getDS();

        $this->init($start, $step);

        if ($this->ended) {
            CAppUI::setMsg("CExClassRefChecker-msg-ended-%s", UI_MSG_OK, static::PRE_TBL . $this->ex_class_id);

            return;
        }

        $ex_objects = $this->getExObjectsToCheck();

        if ($ex_objects) {
            $class_ids = $this->getIdsByClass($ex_objects);

            $objects = $this->getObjectsFromIds($class_ids);

            [$classes_missing, $ids_missing] = $this->getMissingObjects($ex_objects, $objects);

            $this->checkRefsFromForms($ex_objects, $classes_missing, $ids_missing);
        }

        if (!$ex_objects || count($ex_objects) < $this->step) {
            $this->ended = true;
            CAppUI::setMsg("CExClassRefChecker-msg-ended-%s", UI_MSG_OK, static::PRE_TBL . $this->ex_class_id);
        }

        $this->putInCache();
    }

    /**
     * Init vars
     *
     * @return void
     */
    protected function init(int $start, int $step)
    {
        $this->start = $start;
        $this->step  = $step;

        $this->cache = new Cache(static::PREFIX, static::PRE_TBL . $this->ex_class_id, Cache::DISTR);
        if ($data = $this->cache->get()) {
            $this->start = isset($data['start']) ? $data['start'] : 0;
            $this->ended = isset($data['ended']) ? $data['ended'] : false;
            $this->total = isset($data['total']) ? $data['total'] : null;
        }

        $this->time_start = microtime(true);
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getExObjectsToCheck()
    {
        $query = new CRequest();
        $query->addTable($this->ex_object->getTableName());
        $query->addOrder($this->ex_object->_spec->key);

        if ($this->total === null || $this->total < ($this->start + $this->step)) {
            $this->total = $this->ds->loadResult($query->makeSelectCount());
        }

        $query->select = [];
        $query->addSelect($this->ex_object->_spec->key);

        foreach (static::FIELDS as $_class => $_id) {
            $query->addSelect($_class);
            $query->addSelect($_id);
        }

        $query->setLimit("{$this->start},{$this->step}");

        return $this->ds->loadList($query->makeSelect());
    }

    /**
     * @return array
     */
    protected function getIdsByClass(array $ex_objects)
    {
        $classes = [];
        foreach ($ex_objects as $_ex_object) {
            foreach (static::FIELDS as $_class => $_id) {
                if (!isset($classes[$_ex_object[$_class]])) {
                    $classes[$_ex_object[$_class]] = [];
                }

                if (!isset($classes[$_ex_object[$_class]][$_ex_object[$_id]])) {
                    $classes[$_ex_object[$_class]][$_ex_object[$_id]] = true;
                }
            }
        }

        return $classes;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getObjectsFromIds(array $class_ids)
    {
        $objects = [];
        foreach ($class_ids as $_class => $_ids) {
            /** @var CStoredObject $obj */
            $obj = new $_class();
            $ds  = $obj->getDS();

            $query = new CRequest();
            $query->addSelect($obj->_spec->key);
            $query->addTable($obj->_spec->table);
            $query->addWhere([$obj->_spec->key => $ds->prepareIn(array_keys($_ids))]);

            $objects[$_class] = $ds->loadColumn($query->makeSelect());
        }

        return array_map("array_flip", $objects);
    }

    /**
     * @param array $ex_objects Objects to check
     * @param array $objects    Object used for the check
     *
     * @return array
     */
    protected function getMissingObjects(array $ex_objects, array $objects)
    {
        $classes_missing = [];
        $ids_missing     = [];
        foreach ($ex_objects as $_ex_object) {
            foreach (static::FIELDS as $_class => $_id) {
                $class_name = $_ex_object[$_class];
                $obj_id     = $_ex_object[$_id];

                // La classe référencée n'existe plus
                if (!isset($objects[$class_name])) {
                    if (!isset($classes_missing[$class_name])) {
                        $classes_missing[$class_name] = true;
                    }

                    continue;
                }

                // L'identifiant référencé n'existe plus
                if (!isset($objects[$class_name][$obj_id])) {
                    if (!isset($ids_missing[$class_name])) {
                        $ids_missing[$class_name] = [];
                    }

                    if (!isset($ids_missing[$class_name][$obj_id])) {
                        $ids_missing[$class_name][$obj_id] = true;
                    }
                }
            }
        }

        return [
            $classes_missing,
            $ids_missing,
        ];
    }

    /**
     * @param array $ex_objects     Objects to check
     * @param array $class_missings Class missing
     * @param array $ids_missings   Ids missing
     *
     * @return void
     */
    protected function checkRefsFromForms($ex_objects, $class_missings, $ids_missings)
    {
        foreach ($ex_objects as $_ex_object) {
            foreach (static::FIELDS as $_field_class => $_field_id) {
                $class_name = $_ex_object[$_field_class];
                $obj_id     = $_ex_object[$_field_id];

                if (isset($class_missings[$class_name])) {
                    // TODO La classe référencée par $_field_class n'existe plus
                }

                if (isset($ids_missings[$class_name]) && isset($ids_missings[$class_name][$obj_id])) {
                    if (!in_array($_ex_object[$this->ex_object->_spec->key], $this->ref_errors)) {
                        $this->ref_errors[] = $_ex_object[$this->ex_object->_spec->key];
                        CAppUI::setMsg("Erreur de référence", UI_MSG_WARNING);
                    }
                }
            }
        }
    }

    /**
     * Put infos in cache
     *
     * @return void
     */
    protected function putInCache()
    {
        $data = ($this->cache->get()) ?: [
            'start'  => 0,
            'errors' => [],
            'ended'  => false,
            'total'  => 0,
            'step'   => $this->step,
        ];

        $next_start = $this->start + $this->step;

        $end = microtime(true) - $this->time_start;
        if ($end < 60) {
            $this->step *= 2;
        } elseif ($end > 60) {
            $this->step /= 2;
        }

        $data['total'] = $this->total;
        $data['start'] = ($next_start > $this->total) ? $this->total : $next_start;
        $data['ended'] = $this->ended;
        $data['step']  = $this->step;
        foreach ($this->ref_errors as $_ex_object_id) {
            if (!in_array($_ex_object_id, $data['errors'])) {
                $data['errors'][] = $_ex_object_id;
            }
        }

        $this->cache->put($data);
    }

    /**
     * Get the keys from DSHM
     *
     * @param array $ex_classes ExClass to check
     *
     * @return array|false
     */
    public function getKeys(array $ex_classes)
    {
        $keys = [];
        foreach ($ex_classes as $_ex_class_id) {
            $keys[] = CExClassRefChecker::PREFIX . '-' . CExClassRefChecker::PRE_TBL . $_ex_class_id;
        }

        $cache = Cache::getCache(Cache::DISTR);

        $values = $cache->getMultiple($keys);

        if (!is_array($values)) {
            $values = iterator_to_array($values);
        }

        return array_combine($keys, $values);
    }

    public function getNextExClassIdToCheck(): array
    {
        $ex_class_check = CExClassRefChecker::getKeys($this->getAllExClassIds());

        $next_key = null;
        $info     = [];

        // Go to the current ex_class to check
        foreach ($ex_class_check as $_key => $_info) {
            if ($_info === null || $_info === false || $_info['ended'] === false) {
                $next_key = $_key;
                $info     = $_info;
                break;
            }
        }

        $ex_class_id = str_replace(CExClassRefChecker::PREFIX . '-' . CExClassRefChecker::PRE_TBL, '', $next_key);

        return [
            'ex_class_id' => $ex_class_id,
            'start'       => $info['start'] ?? 0,
            'step'        => $info['step'] ?? null,
        ];
    }

    protected function getAllExClassIds(): array
    {
        $ex_class = new CExClass();

        return $ex_class->loadIds();
    }
}
