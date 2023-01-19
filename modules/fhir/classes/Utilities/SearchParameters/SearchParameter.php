<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\Filter;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\CSQLDataSource;
use Symfony\Component\HttpFoundation\Request;

class SearchParameter implements ISearchParameter
{
    /** @var AbstractSearchParameter */
    private $type;

    /** @var string */
    private $modifier;

    /** @var mixed */
    private $value;

    /** @var string|null */
    private $prefixValue;

    /** @var string */
    private $operator;

    /**
     * SearchParameter constructor.
     *
     * @param AbstractSearchParameter $type
     * @param mixed                   $value
     * @param string|null             $modifier
     * @param string|null             $prefix
     */
    public function __construct(AbstractSearchParameter $type, $value, ?string $modifier = null, ?string $prefix = null)
    {
        $this->type     = $type;
        if ($modifier && $this->type->isSupportedModifier($modifier)) {
            $this->modifier = $modifier;
        }

        if ($prefix && $this->type->isSupportedPrefix($prefix)) {
            $this->prefixValue = $prefix;
        }

        $this->prepareValue($value);
        $this->prepareOperator();
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->type->getParameterName();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string         $field
     * @param CSQLDataSource $ds
     * @param mixed|null     $value
     * @param array          $sanitize
     *
     * @return string
     * @throws ApiRequestException
     */
    public function getSql(string $field, CSQLDataSource $ds, $value = null, array $sanitize = []): string
    {
        $filter         = $this->getFilter($field, $value);
        $request_filter = new RequestFilter(new Request());

        return $request_filter->getSqlFilter($filter, $ds, $sanitize);
    }

    /**
     * @param string     $field
     * @param null|mixed $value
     *
     * @return Filter
     */
    public function getFilter(string $field, $value = null): Filter
    {
        $filter = $this->prepareFilter($field);
        if ($value) {
            $filter = new Filter($filter->getKey(), $filter->getOperator(), $value);
        }

        return $filter;
    }

    public function toQuery(): string
    {
        $query_parameter = $this->getParameterName();

        if ($this->modifier) {
            $query_parameter .= ":" . $this->modifier;
        }

        $query_parameter .= "=";

        if ($this->prefixValue) {
            $query_parameter .= $this->prefixValue;
        }

        return $query_parameter . urlencode($this->value);
    }

    /**
     * @param string $field
     *
     * @return Filter|null
     */
    protected function prepareFilter(string $field): ?Filter
    {
        return new Filter($field, $this->operator, $this->value);
    }

    /**
     * @param mixed $value
     */
    protected function prepareValue($value): void
    {
        if (!$this->prefixValue) {
            if (($prefix = $this->type->extractPrefixValue($value)) && $this->type->isSupportedPrefix($prefix)) {
                $this->prefixValue = $prefix;
            }
        }

        $this->value = $this->type->extractValue($value);
    }

    /**
     * @param string $operator
     */
    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    protected function prepareOperator(): string
    {
        return $this->operator = $this->type->prepareOperator($this->modifier, $this->value, $this->prefixValue);
    }
}
