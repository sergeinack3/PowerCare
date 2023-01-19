<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\Content\RequestContent;
use Ox\Core\Api\Resources\AbstractResource;
use Ox\Core\CMbException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Generator for RequestApi.
 * The multiple functions exposed allow a customization of the request that will be generated.
 */
final class RequestApiBuilder
{
    /** @var int */
    private $limit;

    /** @var int */
    private $offset;

    /** @var Sort[] */
    private $sort = [];

    /** @var array */
    private $relations = [];

    /** @var array */
    private $relations_excluded = [];

    /** @var array */
    private $fieldsets = [];

    /** @var Filter[] */
    private $filters = [];

    /** @var string */
    private $uri;

    /** @var bool bool */
    private $with_permissions = false;

    /** @var bool bool */
    private $with_schema = false;

    /** @var string */
    private $content;

    /** @var array */
    private $headers = [];

    /** @var string */
    private $method = 'GET';

    /** @var array */
    private $query = [];

    /**
     * @throws ApiException|ApiRequestException
     */
    public function buildRequestApi(): RequestApi
    {
        $stack = new RequestStack();
        $stack->push($this->buildRequest());

        $request = new RequestApi($stack);

        if ($this->uri) {
            $request->setUri($this->uri);
        }

        return $request;
    }

    private function buildRequest(): Request
    {
        if ($this->limit) {
            $this->buildLimit();
        }

        if ($this->offset) {
            $this->buildOffset();
        }

        if ($this->sort) {
            $this->buildSort();
        }

        if ($this->relations) {
            $this->buildRelations();
        }

        if ($this->relations_excluded) {
            $this->buildRelationsExcluded();
        }

        if ($this->fieldsets) {
            $this->buildFieldsets();
        }

        if ($this->filters) {
            $this->buildFitlers();
        }

        if ($this->with_permissions) {
            $this->buildPermissions();
        }

        if ($this->with_schema) {
            $this->buildSchema();
        }

        $request = new Request($this->query, [], [], [], [], [], $this->content);
        $request->setMethod($this->method);
        $request->headers->add($this->headers);

        return $request;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;


        return $this;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param Sort[] $sorts
     *
     * @throws CMbException
     */
    public function setSort(array $sorts): self
    {
        foreach ($sorts as $sort) {
            if (!$sort instanceof Sort) {
                throw new CMbException('RequestApiGenerator-Error-All-sorts-element-must-be-instanceof-sort');
            }
        }

        $this->sort = $sorts;

        return $this;
    }

    public function addSort(Sort $sort): self
    {
        $this->sort[] = $sort;

        return $this;
    }

    public function setRelations(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    public function addRelation(string $relation): self
    {
        $this->relations[] = $relation;

        return $this;
    }

    public function setRelationsExcluded(array $relations_excluded): self
    {
        $this->relations_excluded = $relations_excluded;

        return $this;
    }

    public function addRelationExcluded(string $relation_excluded): self
    {
        $this->relations_excluded[] = $relation_excluded;

        return $this;
    }

    public function setFieldsets(array $fieldsets): self
    {
        $this->fieldsets = $fieldsets;

        return $this;
    }

    public function addFieldset(string $fieldset): self
    {
        $this->fieldsets[] = $fieldset;

        return $this;
    }

    /**
     * @param Filter[] $filters
     *
     * @throws CMbException
     */
    public function setFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof Filter) {
                throw new CMbException('RequestApiGenerator-Error-All-filters-element-must-be-instanceof-Filter');
            }
        }

        $this->filters = $filters;

        return $this;
    }

    public function addFilter(Filter $filer): self
    {
        $this->filters[] = $filer;

        return $this;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function setWithPermissions(bool $with_permissions): self
    {
        $this->with_permissions = $with_permissions;

        return $this;
    }

    public function setWithSchema(bool $with_schema): self
    {
        $this->with_schema = $with_schema;

        return $this;
    }

    public function setContent(string $content, string $content_type = RequestFormats::FORMAT_JSON_API): self
    {
        $this->content = $content;
        $this->headers[RequestContent::CONTENT_TYPE_KEYWORD] = $content_type;

        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    private function buildLimit(): void
    {
            $this->query[RequestLimit::QUERY_KEYWORD_LIMIT] = $this->limit;
    }

    private function buildOffset(): void
    {
        $this->query[RequestLimit::QUERY_KEYWORD_OFFSET] = $this->offset;
    }

    private function buildSort(): void
    {
        $this->query[RequestSort::QUERY_KEYWORD_SORT] = implode(RequestSort::SORT_SEPARATOR, $this->sort);
    }

    private function buildRelations(): void
    {
        $this->query[RequestRelations::QUERY_KEYWORD_INCLUDE] =
            implode(RequestRelations::RELATION_SEPARATOR, $this->relations);
    }

    private function buildRelationsExcluded(): void
    {
        $this->query[RequestRelations::QUERY_KEYWORD_EXCLUDE] =
            implode(RequestRelations::RELATION_SEPARATOR, $this->relations_excluded);
    }

    private function buildFieldsets(): void
    {
        $this->query[RequestFieldsets::QUERY_KEYWORD] =
            implode(RequestFieldsets::FIELDSETS_SEPARATOR, $this->fieldsets);
    }

    private function buildFitlers(): void
    {
        $this->query[RequestFilter::QUERY_KEYWORD_FILTER] = implode(RequestFilter::FILTER_SEPARATOR, $this->filters);
    }

    private function buildPermissions(): void
    {
        $this->query[AbstractResource::PERMISSIONS_KEYWORD] = $this->with_permissions;
    }

    private function buildSchema(): void
    {
        $this->query[AbstractResource::SCHEMA_KEYWORD] = $this->with_schema;
    }
}
