<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Countable;
use Iterator;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\CSQLDataSource;
use ReturnTypeWillChange;
use Symfony\Component\HttpFoundation\Request;

/**
 * Create filters from a Request object
 */
class RequestFilter implements IRequestParameter, Iterator, Countable
{
    /** @var string */
    public const QUERY_KEYWORD_FILTER = 'filter';

    /** @var string */
    public const FILTER_PART_SEPARATOR = '.';

    public const FILTER_SEPARATOR = ',';

    // TODO Add FILTER_BETWEEN AND FILTER_NOT_BETWEEN
    /** @var string */
    public const FILTER_EQUAL = 'equal';

    /** @var string */
    public const FILTER_NOT_EQUAL = 'notEqual';

    /** @var string */
    public const FILTER_LESS = 'less';

    /** @var string */
    public const FILTER_LESS_OR_EQUAL = 'lessOrEqual';

    /** @var string */
    public const FILTER_GREATER = 'greater';

    /** @var string */
    public const FILTER_GREATER_OR_EQUAL = 'greaterOrEqual';

    /** @var string */
    public const FILTER_IN = 'in';

    /** @var string */
    public const FILTER_NOT_IN = 'notIn';

    /** @var string */
    public const FILTER_IS_NULL = 'isNull';

    /** @var string */
    public const FILTER_IS_NOT_NULL = 'isNotNull';

    /** @var string */
    public const FILTER_BEGIN_WITH = 'beginWith';

    /** @var string */
    public const FILTER_DO_NOT_BEGIN_WITH = 'doNotBeginWith';

    /** @var string */
    public const FILTER_CONTAINS = 'contains';

    /** @var string */
    public const FILTER_STRICT_EQUAL = 'strictEqual';

    /** @var string */
    public const FILTER_DO_NOT_CONTAINS = 'doNotContains';

    /** @var string */
    public const FILTER_END_WITH = 'endWith';

    /** @var string */
    public const FILTER_DO_NOT_END_WITH = 'doNotEndWith';

    /** @var string */
    public const FILTER_IS_EMPTY = 'isEmpty';

    /** @var string */
    public const FILTER_IS_NOT_EMPTY = 'isNotEmpty';

    /** @var array */
    public const FILTER_SIMPLE_TYPES = [
        self::FILTER_EQUAL            => '= ?',
        self::FILTER_NOT_EQUAL        => '!= ?',
        self::FILTER_LESS             => '< ?',
        self::FILTER_LESS_OR_EQUAL    => '<= ?',
        self::FILTER_GREATER          => '> ?',
        self::FILTER_GREATER_OR_EQUAL => '>= ?',
    ];

    /** @var array */
    public const FILTER_LIKE_TYPES = [
        self::FILTER_BEGIN_WITH   => '?%',
        self::FILTER_CONTAINS     => '%?%',
        self::FILTER_END_WITH     => '%?',
        self::FILTER_STRICT_EQUAL => '?',
    ];

    /** @var array */
    public const FILTER_NOT_LIKE_TYPES = [
        self::FILTER_DO_NOT_BEGIN_WITH => '?%',
        self::FILTER_DO_NOT_CONTAINS   => '%?%',
        self::FILTER_DO_NOT_END_WITH   => '%?',
    ];

    /** @var array */
    public const FILTER_ARRAY_TYPES = [
        self::FILTER_IN     => 'IN ?',
        self::FILTER_NOT_IN => 'NOT ' . self::FILTER_IN,
    ];

    /** @var array */
    public const FILTER_NO_ARG_TYPES = [
        self::FILTER_IS_NULL      => 'IS NULL',
        self::FILTER_IS_NOT_NULL  => 'IS NOT NULL',
        self::FILTER_IS_EMPTY     => '= ""',
        self::FILTER_IS_NOT_EMPTY => '!= ""',
    ];

    /** @var CSQLDataSource */
    private $ds;

    /** @var Filter[] */
    private $filters = [];

    /** @var int */
    private $position = 0;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if ($_filter = $request->query->get(static::QUERY_KEYWORD_FILTER)) {
            $this->createFilters(explode(self::FILTER_SEPARATOR, $_filter));
        }
    }

    /**
     * @return array
     */
    public function getExistingFilters(): array
    {
        return array_merge(
            self::FILTER_SIMPLE_TYPES,
            self::FILTER_LIKE_TYPES,
            self::FILTER_NOT_LIKE_TYPES,
            self::FILTER_ARRAY_TYPES,
            self::FILTER_NO_ARG_TYPES
        );
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function isEmpty(): bool
    {
        return empty($this->filters);
    }

    /**
     * @param array $query_filters
     *
     * @return void
     */
    private function createFilters(array $query_filters): void
    {
        foreach ($query_filters as $_filter) {
            $filter_parts = array_map(
                function ($data) {
                    return mb_convert_encoding(urldecode($data), 'ISO-8859-1', 'UTF-8');
                },
                $this->getFilterParts($_filter)
            );

            $this->addFilter(
                new Filter(
                    trim(str_replace('`', '', $filter_parts[0])),
                    trim($filter_parts[1]),
                    array_slice($filter_parts, 2)
                )
            );
        }
    }

    /**
     * Get the SQL representation of the filters
     *
     * @param CSQLDataSource $ds
     * @param callable[]     $sanitize
     *
     * @return array
     * @throws ApiRequestException
     */
    public function getSqlFilters(CSQLDataSource $ds, array $sanitize = []): ?array
    {
        $this->ds = $ds;

        $where = [];

        foreach ($this->filters as $_filter) {
            if (($result = $this->getSqlFilter($_filter, $ds, $sanitize)) === null) {
                continue;
            }

            $where[] = $result;
        }

        return $where;
    }

    /**
     * @param Filter         $filter
     * @param CSQLDataSource $ds
     * @param array          $sanitize
     *
     * @return string|null
     * @throws ApiRequestException
     */
    public function getSqlFilter(Filter $filter, CSQLDataSource $ds, array $sanitize = []): ?string
    {
        $this->ds = $ds;

        // Continue on empty filter or empty operator
        if (!$filter->getKey() || !$filter->getOperator()) {
            throw new ApiRequestException('Filter must have a key and an operator');
        }

        $field_name = $filter->getKey();
        $operator   = $filter->getOperator();
        $values     = $filter->getValues();

        // sanitize values
        foreach ($sanitize as $function) {
            foreach ($values as $key => $value) {
                $values[$key] = call_user_func($function, $value);
            }
        }

        // explode table and field_name
        $field_name = $this->prepareFieldName($field_name);

        if (array_key_exists($operator, self::FILTER_SIMPLE_TYPES)) {
            return $this->createSimpleFilter($field_name, $operator, $values);
        } elseif (array_key_exists($operator, self::FILTER_LIKE_TYPES)) {
            return $this->createLikeFilter($field_name, $operator, $values);
        } elseif (array_key_exists($operator, self::FILTER_NOT_LIKE_TYPES)) {
            return $this->createLikeFilter($field_name, $operator, $values, true);
        } elseif (array_key_exists($operator, self::FILTER_ARRAY_TYPES)) {
            return $this->createArrayFilter($field_name, $operator, $values);
        } elseif (array_key_exists($operator, self::FILTER_NO_ARG_TYPES)) {
            return $this->createNoArgFilter($field_name, $operator);
        }

        throw new ApiRequestException("Invalide operator {$operator}");
    }

    /**
     * @param string $field_name
     *
     * @return string
     */
    private function prepareFieldName(string $field_name): string
    {
        $fields = [];
        foreach (explode('.', $field_name, 2) as $element) {
            $fields[] = "`$element`";
        }

        return implode('.', $fields);
    }

    /**
     * @param string $field_name
     * @param string $operator
     * @param array  $values
     *
     * @return string
     */
    private function createSimpleFilter(string $field_name, string $operator, array $values = []): ?string
    {
        if (!$values) {
            return null;
        }

        $sql_operator = self::FILTER_SIMPLE_TYPES[$operator];

        $value = reset($values);

        return "$field_name " . $this->ds->prepare("$sql_operator", trim($value));
    }

    /**
     * @param string $field_name
     * @param string $operator
     * @param array  $values
     * @param bool   $not Negate the like
     *
     * @return string
     */
    private function createLikeFilter(
        string $field_name,
        string $operator,
        array $values = [],
        bool $not = false
    ): ?string {
        if (!$values) {
            return null;
        }

        $sql_operator = ($not) ? self::FILTER_NOT_LIKE_TYPES[$operator] : self::FILTER_LIKE_TYPES[$operator];

        $value = reset($values);

        if ($operator === self::FILTER_STRICT_EQUAL) {
            $query_like = $this->ds->prepareLikeBinary(str_replace('?', trim($value), $sql_operator));
        } else {
            $query_like = $this->ds->prepareLike(str_replace('?', trim($value), $sql_operator));
        }

        return "$field_name " . (($not) ? 'NOT ' : '') . $query_like;
    }

    /**
     * @param string $field_name
     * @param string $operator
     *
     * @return string
     */
    private function createNoArgFilter(string $field_name, string $operator): string
    {
        $sql_operator = self::FILTER_NO_ARG_TYPES[$operator];

        return "$field_name {$sql_operator}";
    }

    /**
     * @param string $field_name
     * @param string $operator
     * @param array  $parts
     *
     * @return string
     */
    private function createArrayFilter(string $field_name, string $operator, array $parts): ?string
    {
        if (empty($parts) || (count($parts) === 1 && $parts[0] === '')) {
            return null;
        }

        $query = "$field_name ";

        array_walk($parts, 'trim');

        if ($operator == self::FILTER_IN) {
            $query .= $this->ds->prepareIn($parts);
        } else {
            $query .= $this->ds->prepareNotIn($parts);
        }

        return $query;
    }

    /**
     * @param string $filter
     *
     * @return array|null
     */
    private function getFilterParts(string $filter): ?array
    {
        return explode(self::FILTER_PART_SEPARATOR, $filter);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->filters[$this->position];
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->filters[$this->position]);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->filters  = array_values($this->filters);
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->filters);
    }

    /**
     * @param Filter $filter
     *
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @param int  $i
     * @param bool $reindex
     *
     * @return void
     * @throws ApiRequestException
     */
    public function removeFilter(int $i, bool $reindex = false): void
    {
        if (!isset($this->filters[$i])) {
            throw new ApiRequestException("No filter at index {$i}");
        }

        unset($this->filters[$i]);

        // Reindex array
        if ($reindex) {
            $this->filters = array_values($this->filters);
        }
    }

    /**
     * @param string|array $operators
     *
     * @return int|Filter|null
     */
    public function getFilter(string $key, $operators = [], bool $get_pos = false)
    {
        $operators = (!is_array($operators)) ? [$operators] : $operators;
        foreach ($this->filters as $filter_pos => $filter) {
            if (
                $filter->getKey() === $key && (empty($operators) || in_array($filter->getOperator(), $operators, true))
            ) {
                return ($get_pos) ? $filter_pos : $filter;
            }
        }

        return null;
    }

    /**
     * @param string       $key
     * @param string|array $operator
     *
     * @return int|null
     */
    public function getFilterPosition(string $key, $operator = []): ?int
    {
        return $this->getFilter($key, $operator, true);
    }
}
