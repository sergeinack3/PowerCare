<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder;

use Ox\Core\Elastic\QueryBuilder\Filters\AbstractElasticQueryFilter;

/**
 * Defines the base of an Elasticsearch query
 * With the :
 * - Number of documents
 * - Index requested
 * - Offset
 * - Sorting
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html Elasticsearch's Documentation
 */
class ElasticQueryBuilder
{
    private array $query = [];


    public function __construct(string $index)
    {
        $this->query["index"] = $index;
    }

    public function setIndex(string $index): self
    {
        $this->query["index"] = $index;

        return $this;
    }

    public function setSize(int $size): self
    {
        $this->query["size"] = $size;

        return $this;
    }

    public function setFrom(int $from): self
    {
        $this->query["from"] = $from;

        return $this;
    }

    public function addFilter(AbstractElasticQueryFilter $elastic_query_filter): self
    {
        $data = $elastic_query_filter->build();
        if ($data !== []) {
            $this->query["body"]["query"] = $data;
        }

        return $this;
    }

    public function addAggregation(ElasticQueryAggregation $aggregation): self
    {
        $this->query["body"]["aggs"] = $aggregation->build();

        return $this;
    }

    public function addSort(string $field, string $type): self
    {
        $this->query["body"]["sort"][][$field]["order"] = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->query;
    }
}
