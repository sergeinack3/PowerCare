<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;

class CConstantFilter
{
    /** @var string[] */
    protected $sources = [];

    /** @var CConstantSpec[] */
    protected $spec_identifiers = [];

    /** @var int 1|0 */
    protected $active = 1;

    /** @var int[] */
    protected $user_ids = [];

    /** @var array */
    protected $contexts = [];

    /** @var array */
    protected $datetime = [];

    /** @var string[] */
    protected $types = [];

    /** @var int|null */
    protected $patient_id = null;

    /** @var array ASC|DESC */
    protected $orders = [];

    /** @var array */
    protected $groups = [];

    /** @var string */
    protected $limit = null;
    /** @var bool */
    protected $last = false;

    /**
     * @param string[]|string|null $spec_codes
     * @param int|null             $patient_id
     * @param null|string|int      $limit
     *
     * @return static
     */
    public static function getFilterLast($spec_codes = null, ?int $patient_id = null, $limit = null): self
    {
        $filter = (new self())->addOrdersClause('datetime DESC');
        if ($spec_codes) {
            $filter->addSpecCodes($spec_codes);
        }
        if ($patient_id) {
            $filter->setPatientId($patient_id);
        }
        if ($limit) {
            $filter->setLimit($limit);
        }

        $filter->last = true;

        return $filter;
    }

    /**
     * @param string|string[] $orders
     *
     * @return $this
     */
    public function addOrdersClause($orders, string $prefix = CAbstractConstant::class): self
    {
        if (!is_array($orders)) {
            $orders = [$orders];
        }

        foreach ($orders as $order) {
            if (!isset($this->orders[$prefix])) {
                $this->orders[$prefix] = [];
            }

            if (!in_array($order, $this->orders[$prefix])) {
                $this->orders[$prefix][] = $order;
            }
        }

        return $this;
    }

    /**
     * @param string|string[] $codes
     */
    public function addSpecCodes($codes): self
    {
        if (!is_array($codes)) {
            $codes = [$codes];
        }

        foreach ($codes as $code) {
            if (!is_string($code)) {
                continue;
            }

            if (!$spec = CConstantSpec::getSpecByCode($code)) {
                continue;
            }

            $this->spec_identifiers[$spec->_id] = $spec;
        }

        return $this;
    }

    /**
     * @param int|null $patient_id
     */
    public function setPatientId(?int $patient_id): self
    {
        $this->patient_id = $patient_id;

        return $this;
    }

    /**
     * @param string|null $limit
     *
     * @return $this
     */
    public function setLimit(?string $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function getOffset(): ?string
    {
        if (!$this->limit) {
            return null;
        }

        $explode = explode(',', $this->limit);

        return count($explode) === 2 ? $explode[0] : null;
    }

    /**
     * @param bool $merge
     *
     * @return array
     * @throws Exception
     */
    public function getResults(bool $merge = false): array
    {
        $objects = $this->getConstantsObjects();

        $limit   = $this->limit;
        $results = [];
        foreach ($objects as $object) {
            $ds    = $object->getDS();
            $table = $object->getSpec()->table;
            $lj    = $this->makeLeftJoin($object);
            $where = $this->makeWhere($object);
            $order = $this->makeOrder($object);
            $group = $this->makeGroup($object);

            if ($this->needMultipleRequest()) {
                $matching_specs = array_filter(
                    $this->spec_identifiers,
                    function ($spec) use ($object) {
                        return $spec->value_class === $object->_class;
                    }
                );

                $constants = [];
                foreach ($matching_specs as $spec) {
                    $sub_where                   = array_merge([], $where);
                    $sub_where["$table.spec_id"] = $ds->prepare('= ?', $spec->_id);
                    $request                     = new CRequest();
                    $request->addLJoin($lj);
                    $request->addWhere($sub_where);
                    $request->addGroup($group);
                    $request->addOrder($order);
                    $request->setLimit($limit);
                    $constants = array_merge($constants, $object->loadQueryList($request->makeSelect($object), null));
                }
            } else {
                $request = new CRequest();
                $request->addLJoin($lj);
                $request->addWhere($where);
                $request->addGroup($group);
                $request->addOrder($order);
                $request->setLimit($limit);

                $constants = $object->loadQueryList($request->makeSelect($object), null);
            }

            if ($merge) {
                $results = array_merge($results, $constants);
            } else {
                $results[$object->_class] = $constants;
            }
        }

        return $results;
    }

    /**
     * @return CAbstractConstant[]
     */
    protected function getConstantsObjects(): array
    {
        $classes = CAbstractConstant::CONSTANT_CLASS;
        if ($this->spec_identifiers) {
            $classes = array_unique(
                array_map(
                    function ($spec) {
                        return $spec->value_class;
                    },
                    $this->spec_identifiers
                )
            );
        }

        return array_map(
            function ($class) {
                return new $class();
            },
            $classes
        );
    }

    /**
     * @param CAbstractConstant $constant
     *
     * @return array|null
     */
    protected function makeLeftJoin(CAbstractConstant $constant): ?array
    {
        if (!$this->hasLeftJoin()) {
            return null;
        }

        $releve              = new CConstantReleve();
        $ds                  = $constant->getDS();
        $releve_table        = $releve->getSpec()->table;
        $releve_primary_id   = $releve->getPrimaryKey();
        $constant_table      = $constant->getSpec()->table;

        $lj = [
            "$releve_table.$releve_primary_id = $constant_table.releve_id",
            "$releve_table.active" . $ds->prepare('= ?', $this->active),
        ];

        // for specific patient
        if ($this->patient_id) {
            $lj[] = "$releve_table.patient_id" . $ds->prepare('= ?', $this->patient_id);
        }

        // for specific user
        if ($this->user_ids) {
            $lj[] = "$releve_table.user_id " . $ds->prepareIn($this->user_ids);
        }

        // specific source
        if ($this->sources) {
            $lj[] = "$releve_table.source " . $ds->prepareIn($this->sources);
        }

        // specific date
        if ($this->datetime) {
            $lj[] = "$releve_table.datetime " . $this->makeDatetime();
        }

        if ($this->contexts) {
            if ($object_ids = CMbArray::get($this->contexts, 'object_id')) {
                $lj[] = "$releve_table.context_id " . $ds->prepareIn($object_ids);
            }

            if ($object_classes = CMbArray::get($this->contexts, 'object_class')) {
                $lj[] = "$releve_table.context_class " . $ds->prepareIn($object_classes);
            }
        }

        // specific type of releve
        if ($this->types) {
            $lj[] = "$releve_table.type " . $ds->prepareIn($this->types);
        }

        return [$releve_table => implode(' AND ', $lj)];
    }

    /**
     * @return bool
     */
    protected function hasLeftJoin(): bool
    {
        $field_for_leftjoin = [$this->user_ids, $this->sources];

        return count(array_filter($field_for_leftjoin)) > 0;
    }

    /**
     * @return string|null
     */
    protected function makeDatetime(): ?string
    {
        $ds = CSQLDataSource::get('std');
        if (!$this->datetime) {
            return null;
        }

        $operator = CMbArray::get($this->datetime, 'operator');
        $dt_start = CMbArray::get($this->datetime, 'dt_start');
        $dt_end   = CMbArray::get($this->datetime, 'dt_end');

        if ($operator === "BETWEEN") {
            return $ds->prepareBetween($dt_start, $dt_end);
        }

        if ($operator === "LIKE" && $dt_start) {
            return $ds->prepareLike($dt_start);
        } elseif ($operator === "LIKE" && $dt_end) {
            return $ds->prepareLike($dt_end);
        }

        if ($dt_end) {
            return $ds->prepare("$operator ?", $dt_end);
        }

        return $ds->prepare("$operator ?", $dt_start);
    }

    protected function makeWhere(CAbstractConstant $constant): ?array
    {
        $releve            = new CConstantReleve();
        $ds                = $constant->getDS();
        $constant_table    = $constant->getSpec()->table;
        $releve_table      = $releve->getSpec()->table;
        $releve_primary_id = $releve->getPrimaryKey();

        // specific state of constant
        $where = [
            "$constant_table.active" => $ds->prepare('= ?', $this->active),
        ];

        // for specific patient
        if ($this->patient_id) {
            $where[$constant_table . ".patient_id"] = $ds->prepare('= ?', $this->patient_id);
        }

        // for specific constant
        if ($this->spec_identifiers && !$this->needMultipleRequest()) {
            $matching_specs = array_filter(
                $this->spec_identifiers,
                function ($spec) use ($constant) {
                    return $spec->value_class === $constant->_class;
                }
            );

            $matching_spec_ids = CMbArray::pluck($matching_specs, '_id');

            $where["$constant_table.spec_id"] = $ds->prepareIn($matching_spec_ids);
        }

        // specific date
        if ($this->datetime) {
            $where["$constant_table.datetime"] = $this->makeDatetime();
        }

        // if leftjoin
        if ($this->hasLeftJoin()) {
            $where["$releve_table.active"]             = $ds->prepare('= ?', $this->active);
            $where["$releve_table.$releve_primary_id"] = "IS NOT NULL";
        }


        return $where;
    }

    /**
     * @return bool
     */
    private function needMultipleRequest(): bool
    {
        return $this->last && count($this->spec_identifiers) > 1;
    }

    /**
     * @return string[]|null
     */
    protected function makeOrder(CAbstractConstant $constant): ?array
    {
        $all_orders = [];
        foreach ($this->orders as $prefix => $orders) {
            if ($prefix === CAbstractConstant::class) {
                $table = $constant->getSpec()->table;
            } else {
                $table = (new CConstantReleve())->getSpec()->table;
            }

            foreach ($orders as $order) {
                $all_orders[] = "$table.$order";
            }
        }

        return $all_orders ?: null;
    }

    /**
     * @return array|null
     */
    protected function makeGroup(CAbstractConstant $constant): ?array
    {
        $all_groups = [];
        foreach ($this->groups as $prefix => $groups) {
            if ($prefix === CAbstractConstant::class) {
                $table = $constant->getSpec()->table;
            } else {
                $table = (new CConstantReleve())->getSpec()->table;
            }

            foreach ($groups as $group) {
                $all_groups[] = "$table.$group";
            }
        }

        return $all_groups;
    }

    /**
     * @param string|string[] $groups
     *
     * @return self
     */
    public function addGroupClause($groups, string $prefix = CAbstractConstant::class): self
    {
        if (!is_array($groups)) {
            $groups = [$groups];
        }

        foreach ($groups as $group) {
            if (!isset($this->groups[$prefix])) {
                $this->groups[$prefix] = [];
            }

            if (!in_array($group, $this->groups[$prefix])) {
                $this->groups[$prefix][] = $group;
            }
        }

        return $this;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function countResults(): int
    {
        $counts = $this->countResultsDetails();
        $sum    = function ($carry, $count) {
            $carry += $count;

            return $carry;
        };

        return array_reduce($counts, $sum, 0);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function countResultsDetails(): array
    {
        $objects = $this->getConstantsObjects();

        $counts = [];
        foreach ($objects as $object) {
            $lj    = $this->makeLeftJoin($object);
            $where = $this->makeWhere($object);
            $group = $this->makeGroup($object);

            $request = new CRequest();
            $request->addLJoin($lj);
            $request->addWhere($where);
            $request->addGroup($group);

            $counts[$object->_class] = $object->_spec->ds->loadResult($request->makeSelectCount($object));
        }

        return $counts;
    }

    /**
     * @param int|int[] $ids
     */
    public function addSpecIds($ids): self
    {
        if (is_int($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            if (!is_int($id)) {
                $id = intval($id);
            }

            if (!$spec = CConstantSpec::getSpecById($id)) {
                continue;
            }

            $this->spec_identifiers[$spec->_id] = $spec;
        }

        return $this;
    }

    /**
     * @param int $active
     */
    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    /**
     * @param string|string[] $sources
     *
     * @return $this
     * @throws CMbException
     */
    public function addSources($sources): self
    {
        if (!is_array($sources)) {
            $sources = [$sources];
        }

        foreach ($sources as $source) {
            $source_name = null;
            switch ($source) {
                case CConstantReleve::FROM_API:
                    $source_name = CConstantReleve::FROM_API;
                    break;
                case CConstantReleve::FROM_DEVICE:
                    $source_name = CConstantReleve::FROM_DEVICE;
                    break;
                case CConstantReleve::FROM_MEDIBOARD:
                    $source_name = CConstantReleve::FROM_MEDIBOARD;
                    break;

                default:
                    throw new CMbException('invalid source name');
            }

            if (!in_array($source_name, $this->sources)) {
                $this->sources[] = $source_name;
            }
        }

        return $this;
    }

    /**
     * @param int[]|int $user_ids
     */
    public function addUserIds($user_ids): self
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }

        foreach ($user_ids as $user_id) {
            if (!in_array($user_id, $this->user_ids)) {
                $user_ids[] = $user_id;
            }
        }

        return $this;
    }

    /**
     * @param string $datetime
     * @param bool   $format_date
     * @param bool   $strict
     *
     * @return $this
     */
    public function getBefore(string $datetime, bool $format_date = false, bool $strict = false): self
    {
        $operator = $strict ? '<' : '<=';

        return $this->getDate($datetime, null, $operator, $format_date);
    }

    /**
     * @param string      $dt_start
     * @param string|null $dt_end
     * @param string      $operator
     * @param bool        $format_date
     *
     * @return $this
     */
    protected function getDate(string $dt_start, ?string $dt_end, string $operator, bool $format_date = false): self
    {
        if ($format_date) {
            $dt_start = CMbDT::roundTime($dt_start, CMbDT::ROUND_DAY);
            if ($dt_end) {
                $dt_end = CMbDT::roundTime($dt_end, CMbDT::ROUND_DAY);
            }
        }


        $this->datetime = [
            'operator' => $operator,
            'dt_start' => $dt_start,
            'dt_end'   => $dt_end,
        ];

        return $this;
    }

    /**
     * @param string|null $like_dt_start
     * @param string|null $like_dt_end
     *
     * @return $this
     */
    public function getForDay(?string $like_dt_start, ?string $like_dt_end = null): self
    {
        return $this->getDate($like_dt_start, $like_dt_end, 'LIKE');
    }

    /**
     * @param string $datetime
     * @param bool   $format_date
     * @param bool   $strict
     *
     * @return $this
     */
    public function getAfter(string $datetime, bool $format_date = false, bool $strict = false): self
    {
        $operator = $strict ? '>' : '>=';

        return $this->getDate($datetime, null, $operator, $format_date);
    }

    /**
     * @param string      $datetime_start
     * @param string|null $datetime_end
     * @param string      $operator
     * @param bool        $format_date
     * @param bool        $strict
     */
    public function getBetween(string $datetime_start, ?string $datetime_end, bool $format_date = false): self
    {
        $operator = 'BETWEEN';

        return $this->getDate($datetime_start, $datetime_end, $operator, $format_date);
    }

    /**
     * @param CStoredObject $object
     */
    public function addContextObject(CStoredObject $object): self
    {
        return $this->addContext($object->_class, $object->_id);
    }

    /**
     * @param object $object_class
     * @param int    $object_id
     *
     * @return $this
     */
    public function addContext(?string $object_class, ?int $object_id = null): self
    {
        if ($object_class !== null) {
            $this->contexts["object_class"][] = $object_class;
        }

        if ($object_id !== null) {
            $this->contexts["object_id"][] = $object_id;
        }

        return $this;
    }

    /**
     * @param string|string[] $types
     *
     * @return $this
     */
    public function addType($types): self
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            if (!in_array($type, $this->types)) {
                $this->types[] = $type;
            }
        }

        return $this;
    }
}
